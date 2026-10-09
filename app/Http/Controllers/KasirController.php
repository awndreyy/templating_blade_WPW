<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransactionRequest;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class KasirController extends Controller
{
    /**
     * Tampilkan halaman POS Dashboard Kasir
     */
    public function dashboard(): View
    {
        $products = collect();
        try {
            $products = Product::where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'code', 'barcode', 'name', 'category', 'price', 'stock', 'is_active']);
        } catch (\Throwable $e) {
            Log::warning('KasirController: Could not fetch products from database: '.$e->getMessage());
        }

        return view('kasir.dashboard', compact('products'));
    }

    /**
     * Proses Checkout / Simpan Transaksi ke Database & Kurangi Stok Produk
     */
    public function storeTransaction(StoreTransactionRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $items = $validated['items'];

        try {
            return DB::transaction(function () use ($validated, $items) {
                $productIds = collect($items)->pluck('id')->all();
                $products = Product::whereIn('id', $productIds)->lockForUpdate()->get()->keyBy('id');

                $subtotal = 0;
                $detailsData = [];

                foreach ($items as $item) {
                    $product = $products->get($item['id']);
                    if (! $product) {
                        return response()->json([
                            'success' => false,
                            'message' => "Produk dengan ID {$item['id']} tidak ditemukan.",
                        ], 404);
                    }

                    $qty = (int) $item['qty'];
                    if ($product->stock < $qty) {
                        return response()->json([
                            'success' => false,
                            'message' => "Stok produk '{$product->name}' tidak mencukupi. Tersedia: {$product->stock}, Diminta: {$qty}.",
                        ], 422);
                    }

                    $itemPrice = (float) $product->price;
                    $itemSubtotal = $itemPrice * $qty;
                    $subtotal += $itemSubtotal;

                    $detailsData[] = [
                        'product_id' => $product->id,
                        'product_code' => $product->code,
                        'product_barcode' => $product->barcode,
                        'product_name' => $product->name,
                        'price' => $itemPrice,
                        'quantity' => $qty,
                        'subtotal' => $itemSubtotal,
                    ];
                }

                // 2. Hitung diskon, pajak, biaya lain, dan total akhir
                $discountPercent = isset($validated['discount_percent']) ? (float) $validated['discount_percent'] : 0;
                $discountAmount = isset($validated['discount_amount']) ? (float) $validated['discount_amount'] : 0;

                // Jika diskon persen diisi tapi nominal tidak dihitung
                if ($discountPercent > 0 && $discountAmount <= 0) {
                    $discountAmount = ($subtotal * $discountPercent) / 100;
                }

                $taxAmount = isset($validated['tax_amount']) ? (float) $validated['tax_amount'] : 0;
                $otherFees = isset($validated['other_fees']) ? (float) $validated['other_fees'] : 0;

                $totalAmount = max(0, $subtotal - $discountAmount + $taxAmount + $otherFees);

                $paymentMethod = $validated['payment_method'];
                $cashPaid = (float) $validated['cash_paid'];

                // Pembayaran non-tunai otomatis set nominal pas jika kurang
                if ($paymentMethod !== 'Tunai' && $cashPaid < $totalAmount) {
                    $cashPaid = $totalAmount;
                }

                $changeAmount = $cashPaid - $totalAmount;
                if ($changeAmount < 0) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Nominal uang yang dibayarkan kurang dari total belanja.',
                    ], 422);
                }

                // 3. Buat nomor faktur / invoice unik
                $todayPrefix = 'TRX-'.date('Ymd').'-';
                $todayCount = Transaction::where('invoice_number', 'like', $todayPrefix.'%')->count() + 1;
                $invoiceNumber = $todayPrefix.str_pad($todayCount, 4, '0', STR_PAD_LEFT);

                // 4. Simpan Header Transaksi
                $transaction = Transaction::create([
                    'invoice_number' => $invoiceNumber,
                    'user_id' => Auth::id(),
                    'cashier_name' => Auth::user()->name ?? 'Kasir Petugas',
                    'customer_type' => $validated['customer_type'] ?? 'Umum',
                    'customer_phone' => $validated['customer_phone'] ?? null,
                    'subtotal' => $subtotal,
                    'discount_percent' => $discountPercent,
                    'discount_amount' => $discountAmount,
                    'tax_amount' => $taxAmount,
                    'other_fees' => $otherFees,
                    'total_amount' => $totalAmount,
                    'payment_method' => $paymentMethod,
                    'cash_paid' => $cashPaid,
                    'change_amount' => $changeAmount,
                    'status' => 'completed',
                    'notes' => $validated['notes'] ?? null,
                ]);

                // 5. Simpan Detail Transaksi dan Kurangi Stok Produk
                $savedDetails = [];
                $updatedStocks = [];

                foreach ($detailsData as $detail) {
                    $detail['transaction_id'] = $transaction->id;
                    $createdDetail = TransactionDetail::create($detail);
                    $savedDetails[] = $createdDetail;

                    // Pengurangan stok
                    $product = $products->get($detail['product_id']);
                    $product->decrement('stock', $detail['quantity']);
                    $updatedStocks[$product->id] = $product->fresh()->stock;
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Transaksi berhasil disimpan dan stok produk telah diperbarui!',
                    'data' => [
                        'transaction' => $transaction,
                        'details' => $savedDetails,
                        'invoice_number' => $invoiceNumber,
                        'created_at' => $transaction->created_at->format('d/m/Y H:i'),
                        'updated_stocks' => $updatedStocks,
                    ],
                ]);
            });
        } catch (\Throwable $e) {
            Log::error('KasirController Store Transaction Error: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem saat memproses transaksi: '.$e->getMessage(),
            ], 500);
        }
    }
}
