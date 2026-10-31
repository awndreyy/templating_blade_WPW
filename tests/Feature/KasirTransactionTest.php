<?php

namespace Tests\Feature;

use App\Events\OrderPlaced;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
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

        Event::fake([OrderPlaced::class]);

        $response = $this->actingAs($kasir)->postJson(route('kasir.transaksi.store'), $payload);

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.transaction.payment_method', 'Tunai');
        $this->assertEquals(19, $product->fresh()->stock);
        Event::assertDispatched(OrderPlaced::class, function (OrderPlaced $event) use ($kasir): bool {
            return $event->cashierId === $kasir->id && $event->total === 10000.0;
        });
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

    public function test_checkout_with_insufficient_stock_does_not_broadcast_order(): void
    {
        $kasir = User::factory()->create(['role' => 'kasir']);
        $product = Product::factory()->create([
            'price' => 10000,
            'stock' => 0,
            'is_active' => true,
        ]);

        Event::fake([OrderPlaced::class]);

        $response = $this->actingAs($kasir)->postJson(route('kasir.transaksi.store'), [
            'payment_method' => 'Tunai',
            'cash_paid' => 20000,
            'items' => [
                ['id' => $product->id, 'qty' => 1, 'price' => 10000],
            ],
        ]);

        $response->assertUnprocessable();
        $this->assertDatabaseCount('transactions', 0);
        Event::assertNotDispatched(OrderPlaced::class);
    }
}
