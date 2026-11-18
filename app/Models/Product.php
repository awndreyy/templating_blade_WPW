<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'barcode',
        'name',
        'category',
        'category_id',
        'description',
        'price',
        'stock',
        'image',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'stock' => 'integer',
        'is_active' => 'boolean',
    ];

    public function categoryMaster(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    protected static function booted(): void
    {
        static::saving(function (Product $product): void {
            // Sinkronkan relasi kategori dengan kolom teks lama yang masih dipakai
            // halaman kasir, laporan, Blade, dan API.
            if ($product->isDirty('category_id')) {
                $product->category = $product->category_id
                    ? Category::query()->whereKey($product->category_id)->value('name')
                    : null;

                return;
            }

            // Produk yang dibuat dari form/API lama tetap masuk ke master kategori.
            if ($product->isDirty('category')) {
                if (blank($product->category)) {
                    $product->category_id = null;

                    return;
                }

                $slug = Str::slug($product->category) ?: Str::lower(Str::random(8));
                $category = Category::firstOrCreate(
                    ['name' => $product->category],
                    ['slug' => $slug],
                );

                $product->category_id = $category->getKey();
            }
        });
    }

    public function transactionDetails(): HasMany
    {
        return $this->hasMany(TransactionDetail::class);
    }
}
