<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductCodeBarcodeSeeder extends Seeder
{
    public function run(): void
    {
        Product::whereNull('code')->orWhereNull('barcode')->chunk(100, function ($products) {
            foreach ($products as $p) {
                if (! $p->code) {
                    $p->code = 'PRD-'.str_pad($p->id, 5, '0', STR_PAD_LEFT);
                }
                if (! $p->barcode) {
                    $p->barcode = '899'.str_pad($p->id, 9, '0', STR_PAD_LEFT);
                }
                $p->save();
            }
        });
    }
}
