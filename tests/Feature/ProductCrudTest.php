<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductCrudTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::firstOrCreate(
            ['email' => 'admin@minimarket.test'],
            ['name' => 'Admin User', 'password' => bcrypt('password'), 'role' => 'admin']
        );
    }

    /**
     * 1. Menampilkan product
     */
    public function test_1_menampilkan_product(): void
    {
        Product::create([
            'name' => 'Indomie Goreng Spesial',
            'category' => 'Makanan',
            'price' => 3500,
            'stock' => 50,
            'description' => 'Mi instan lezat dan gurih',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->get(route('products.index'));

        $response->assertStatus(200);
        $response->assertSee('Daftar Produk');
        $response->assertSee('Indomie Goreng Spesial');
        $response->assertSee('Makanan');
        $response->assertSee('3.500');
    }

    /**
     * 2. Tambah product
     */
    public function test_2_tambah_product(): void
    {
        Storage::fake('public');

        $response = $this->actingAs($this->admin)->post(route('products.store'), [
            'name' => 'Teh Botol Sosro',
            'category' => 'Minuman',
            'description' => 'Teh melati dalam botol',
            'price' => 5000,
            'stock' => 30,
            'is_active' => '1',
            'image' => UploadedFile::fake()->image('teh.jpg'),
        ]);

        $response->assertRedirect(route('products.index'));
        $response->assertSessionHas('success', 'Produk berhasil ditambahkan!');

        $this->assertDatabaseHas('products', [
            'name' => 'Teh Botol Sosro',
            'category' => 'Minuman',
            'price' => 5000,
            'stock' => 30,
            'is_active' => 1,
        ]);
    }

    /**
     * 3. Melihat detail product
     */
    public function test_3_melihat_detail_product(): void
    {
        $product = Product::create([
            'name' => 'Susu Kotak UHT Cokelat',
            'category' => 'Minuman',
            'price' => 6000,
            'stock' => 20,
            'description' => 'Susu segar rasa cokelat kaya kalsium',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->get(route('products.show', $product));

        $response->assertStatus(200);
        $response->assertSee('Detail Produk');
        $response->assertSee('Susu Kotak UHT Cokelat');
        $response->assertSee('Susu segar rasa cokelat kaya kalsium');
        $response->assertSee('6.000');
    }

    /**
     * 4. Edit product
     */
    public function test_4_edit_product(): void
    {
        $product = Product::create([
            'name' => 'Biskuit Roma Kelapa',
            'category' => 'Makanan',
            'price' => 8000,
            'stock' => 15,
            'description' => 'Biskuit kelapa renyah',
            'is_active' => true,
        ]);

        // Cek halaman form edit
        $editFormResponse = $this->actingAs($this->admin)->get(route('products.edit', $product));
        $editFormResponse->assertStatus(200);
        $editFormResponse->assertSee('Biskuit Roma Kelapa');

        // Update data produk
        $response = $this->actingAs($this->admin)->put(route('products.update', $product), [
            'name' => 'Biskuit Roma Kelapa 300g',
            'category' => 'Snack & Biskuit',
            'price' => 9500,
            'stock' => 25,
            'description' => 'Biskuit kelapa kemasan baru',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('products.index'));
        $response->assertSessionHas('success', 'Produk berhasil diperbarui!');

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Biskuit Roma Kelapa 300g',
            'category' => 'Snack & Biskuit',
            'price' => 9500,
            'stock' => 25,
        ]);
    }

    /**
     * 5. Hapus product
     */
    public function test_5_hapus_product(): void
    {
        $product = Product::create([
            'name' => 'Sabun Mandi Wangi',
            'category' => 'Kebutuhan Rumah',
            'price' => 4000,
            'stock' => 10,
            'description' => 'Sabun mandi keluarga',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('products.destroy', $product));

        $response->assertRedirect(route('products.index'));
        $response->assertSessionHas('success', 'Produk berhasil dihapus!');

        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
        ]);
    }

    /**
     * 6. Validasi input
     */
    public function test_6_validasi_input_required_dan_tipe_data(): void
    {
        // Test submit kosong (nama, harga, stok wajib)
        $response = $this->actingAs($this->admin)->post(route('products.store'), [
            'name' => '',
            'price' => '',
            'stock' => '',
        ]);

        $response->assertSessionHasErrors(['name', 'price', 'stock']);

        // Test harga negatif atau non-numerik
        $responseInvalidPrice = $this->actingAs($this->admin)->post(route('products.store'), [
            'name' => 'Produk Salah',
            'price' => -5000,
            'stock' => 'bukan-angka',
        ]);

        $responseInvalidPrice->assertSessionHasErrors(['price', 'stock']);
    }

    /**
     * 7. Pagination
     */
    public function test_7_pagination_produk(): void
    {
        // Buat 15 produk
        for ($i = 1; $i <= 15; $i++) {
            $product = Product::create([
                'name' => 'Produk Item '.str_pad($i, 2, '0', STR_PAD_LEFT),
                'category' => 'Kategori '.$i,
                'price' => 1000 * $i,
                'stock' => 10,
                'is_active' => true,
            ]);
            $product->created_at = now()->subMinutes(20 - $i);
            $product->saveQuietly();
        }

        // Halaman 1 menampilkan 10 produk pertama (terbaru)
        $responsePage1 = $this->actingAs($this->admin)->get(route('products.index'));
        $responsePage1->assertStatus(200);
        $responsePage1->assertSee('Produk Item 15');
        $responsePage1->assertSee('Menampilkan 1-10');
        $responsePage1->assertSee('dari 15 produk');
        $responsePage1->assertSee('page=2');

        // Halaman 2 menampilkan 5 produk sisa
        $responsePage2 = $this->actingAs($this->admin)->get(route('products.index', ['page' => 2]));
        $responsePage2->assertStatus(200);
        $responsePage2->assertSee('Produk Item 01');
        $responsePage2->assertSee('Menampilkan 11-15');
        $responsePage2->assertSee('dari 15 produk');
    }
}
