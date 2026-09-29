<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categories = [
            'Makanan' => [
                'Indomie Goreng', 'Indomie Kuah Soto', 'Sedaap Goreng', 'Pop Mie Ayam',
                'Biskuit Roma Kelapa', 'Oreo Vanilla', 'Tango Cokelat', 'Chitato Sapi Panggang',
                'Qtela Singkong', 'Kusuka Keripik Singkong', 'Roti Tawar Kupas', 'Silverqueen Cashew',
                'Beng-Beng Regular', 'Nabati Wafer Keju', 'Kacang Garuda Rosta', 'Malkist Abon',
            ],
            'Minuman' => [
                'Teh Botol Sosro 350ml', 'Pocari Sweat 500ml', 'Aqua 600ml', 'Le Minerale 600ml',
                'Ultra Milk Cokelat 250ml', 'Indomilk Full Cream', 'Kopi Good Day Cappuccino', 'Kopi Kapal Api Spesial Mix',
                'Floridina Orange 350ml', 'Fruit Tea Apel', 'Hydro Coco 250ml', 'Minute Maid Pulpy Orange',
                'Nutrisari Jeruk Peras', 'Teh Pucuk Harum 350ml', 'Ichitan Brown Sugar Milk', 'Nescafe Can Original',
            ],
            'Perlengkapan Mandi' => [
                'Sabun Lifebuoy Total 10', 'Shampoo Pantene Anti Ketombe', 'Pasta Gigi Pepsodent 190g', 'Sikat Gigi Formula',
                'Sabun Cair Biore Pure Mild 450ml', 'Shampoo Clear Men Cool Sport', 'Pembersih Wajah Garnier Men', 'Pond\'s White Beauty',
                'Deodorant Rexona Men Roll On', 'Minyak Rambut Gatsby Pomade', 'Hand Soap Dettol 200ml', 'Listerine Cool Mint 250ml',
            ],
            'Kebutuhan Rumah' => [
                'Minyak Goreng Bimoli 2L', 'Minyak Goreng SunCo 2L', 'Gula Pasir Gulaku 1kg', 'Beras Ramos Super 5kg',
                'Kecap Manis Bango 520ml', 'Saus Sambal ABC 335ml', 'Garam Dapur Cap Kapal 250g', 'Masako Rasa Ayam 100g',
                'Royco Rasa Sapi 100g', 'Deterjen Rinso Molto 770g', 'Pewangi Downy Mystique 650ml', 'Pembersih Lantai Super Pell 770ml',
                'Sabun Cuci Piring Sunlight 750ml', 'Baygon Anti Nyamuk Spray 600ml', 'Tisu Wajah Paseo 250s', 'Spons Cuci Piring Scotch-Brite',
            ],
            'Alat Tulis & Kantor' => [
                'Pulpen Standard AE7 Hitam', 'Buku Tulis Sinar Dunia 38 Lembar', 'Pensil 2B Faber-Castell', 'Penghapus Joyko',
                'Tipe-X Kertas Joyko Correction Tape', 'Map Kertas Folio', 'Gunting Sedang Joyko', 'Lem Kertas Fox PVAc 150g',
                'Spidol Snowman Whiteboard', 'Sticky Notes Pastel', 'Penggaris Plastik 30cm', 'Baterai ABC Alkaline AA Isi 2',
            ],
        ];

        $category = fake()->randomElement(array_keys($categories));
        $baseName = fake()->randomElement($categories[$category]);
        $name = $baseName.' '.fake()->unique()->numberBetween(1, 10000);

        return [
            'name' => $name,
            'category' => $category,
            'description' => fake()->sentence(12),
            'price' => fake()->randomElement([2000, 3500, 5000, 7500, 10000, 12500, 15000, 18500, 25000, 35000, 50000, 75000, 95000]),
            'stock' => fake()->numberBetween(5, 150),
            'image' => null,
            'is_active' => fake()->boolean(90), // 90% aktif
        ];
    }
}
