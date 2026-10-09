<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KasirTransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_kasir_can_checkout_with_tunai(): void
    {
        $kasir = User::factory()->create(['role' => 'kasir']);
        $product = Product::factory()->create([
            'price' => 10000,
            'stock' => 20,
            'is_active' => true,
        ]);

        $payload = [
            'customer_type' => 'Umum',
            'customer_phone' => '08123456789',
            'discount_percent' => 0,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'other_fees' => 0,
            'payment_method' => 'Tunai',
            'cash_paid' => 20000,
            'items' => [
                [
                    'id' => $product->id,
                    'qty' => 1,
                    'price' => 10000,
                ],
            ],
        ];

        $response = $this->actingAs($kasir)->postJson(route('kasir.transaksi.store'), $payload);

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.transaction.payment_method', 'Tunai');
        $this->assertEquals(19, $product->fresh()->stock);
    }

    public function test_kasir_can_checkout_with_qris_and_other_methods(): void
    {
        $kasir = User::factory()->create(['role' => 'kasir']);
        $product = Product::factory()->create([
            'price' => 25000,
            'stock' => 10,
            'is_active' => true,
        ]);

        foreach (['QRIS', 'Debit', 'Transfer', 'Kredit', 'E-Wallet'] as $method) {
            $payload = [
                'customer_type' => 'Member',
                'customer_phone' => '08123456789',
                'discount_percent' => 0,
                'discount_amount' => 0,
                'tax_amount' => 0,
                'other_fees' => 0,
                'payment_method' => $method,
                'cash_paid' => 25000,
                'items' => [
                    [
                        'id' => $product->id,
                        'qty' => 1,
                        'price' => 25000,
                    ],
                ],
            ];

            $response = $this->actingAs($kasir)->postJson(route('kasir.transaksi.store'), $payload);

            $response->assertOk();
            $response->assertJsonPath('success', true);
            $response->assertJsonPath('data.transaction.payment_method', $method);
        }
    }
}
