<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class LaporanController extends Controller
{
    public function produk(): Response
    {
        $products = Product::orderBy('name')->get();

        $pdf = Pdf::loadView('laporan.produk', [
            'products' => $products,
            'tanggal' => now()->format('d-m-Y H:i'),
            'totalProduk' => $products->count(),
            'totalStok' => $products->sum('stock'),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('laporan-produk-'.now()->format('Ymd').'.pdf');
    }
}
