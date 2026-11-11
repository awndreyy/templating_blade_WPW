<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Snap;

class MidtransController extends Controller{
    public function __construct(){
        Config::$serverKey = config('midtrans.serverKey');
        Config::$isProduction = config('midtrans.isProduction');
        Config::$isSanitized = config('midtrans.isSanitized');
        Config::$is3ds = config('midtrans.is3ds');
    }

    public function index(){
        return view('midtrans.checkout');
    }

    public function checkout(Request $request){
        $price = 50000;
        $orderId = 'ORDER-' . Str::uuid();

        $order = Order::create([
            'order_id' => $orderId,
            'product_name' => 'Produk Digital',
            'gross_amount' => $price,
            'status' => 'pending',
        ]);

        try {
            $params = [
                'transaction_details' => [
                    'order_id' => $orderId,
                    'gross_amount' => $price,
                ],
                'item_details' => [
                    [
                        'id' => 'DIGITAL-001',
                        'price' => $price,
                        'quantity' => 1,
                        'name' => 'Produk Digital',
                    ],
                ],
            ];

            $snapToken = Snap::getSnapToken($params);

            $order->update([
                'snap_token' => $snapToken,
            ]);

            return response()->json([
                'success' => true,
                'snap_token' => $snapToken,
                'order_id' => $orderId,
            ]);

        } catch (\Throwable $e) {
            Log::error('Midtrans checkout gagal', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);

            $order->update(['status' => 'error']);

            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat pembayaran Midtrans.',
            ], 502);
        }
    }

    public function notification(Request $request){
        $data = $request->all();

        $orderId = $data['order_id'] ?? '';
        $statusCode = $data['status_code'] ?? '';
        $grossAmount = $data['gross_amount'] ?? '';
        $signature = $data['signature_key'] ?? '';

        if (!is_string($orderId)
            || !is_scalar($statusCode)
            || !is_scalar($grossAmount)
            || !is_string($signature)) {
            return response()->json([
                'message' => 'Invalid notification',
            ], 400);
        }

        $expectedSignature = hash(
            'sha512',
            $orderId
            . $statusCode
            . $grossAmount
            . config('midtrans.serverKey')
        );

        if (!hash_equals($expectedSignature, $signature)) {
            return response()->json([
                'message' => 'Invalid signature',
            ], 403);
        }

        $order = Order::where('order_id', $orderId)->first();

        if (!$order) {
            return response()->json([
                'message' => 'Order not found',
            ], 404);
        }

        if (!is_numeric($grossAmount)
            || (float) $grossAmount != (float) $order->gross_amount) {
            return response()->json([
                'message' => 'Invalid amount',
            ], 400);
        }

        $transactionStatus = $data['transaction_status'] ?? '';
        $fraudStatus = $data['fraud_status'] ?? '';

        $newStatus = match ($transactionStatus) {
            'settlement' => 'paid',
            'capture' => $fraudStatus === 'accept'
                ? 'paid'
                : 'pending',
            'pending' => 'pending',
            'deny', 'cancel', 'expire', 'failure' => 'failed',
            default => null,
        };

        // Jangan mengubah pembayaran yang sudah paid
        // menjadi pending/gagal akibat notifikasi terlambat.
        if ($newStatus !== null
            && !($order->status === 'paid' && $newStatus !== 'paid')) {
            $order->update(['status' => $newStatus]);
        }

        return response()->json([
            'message' => 'Notification handled',
        ]);
    }

    public function status(string $orderId){
        $order = Order::where('order_id', $orderId)
            ->firstOrFail();

        return response()->json([
            'order_id' => $order->order_id,
            'status' => $order->status,
        ]);
    }
}
