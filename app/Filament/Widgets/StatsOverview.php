<?php

namespace App\Filament\Widgets;

use App\Models\Category;
use App\Models\Product;
use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Kategori', Category::count())
                ->description('Kategori produk terdaftar')
                ->descriptionIcon('heroicon-m-tag')->color('info'),
            Stat::make('Total Produk', Product::count())
                ->description('Seluruh produk dalam sistem')
                ->descriptionIcon('heroicon-m-cube')->color('success'),
            Stat::make('Stok Menipis (≤ 5)', Product::where('stock', '<=', 5)->count())
                ->description('Produk yang perlu diperiksa')
                ->descriptionIcon('heroicon-m-exclamation-triangle')->color('danger'),
            Stat::make('Total Transaksi', Transaction::count())
                ->description('Transaksi yang tercatat')
                ->descriptionIcon('heroicon-m-shopping-cart')->color('primary'),
        ];
    }
}
