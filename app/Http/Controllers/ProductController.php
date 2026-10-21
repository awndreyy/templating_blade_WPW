<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::latest()->paginate(10);
        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        return view('admin.products.create');
    }

    private function validateProduct(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'image_rotation' => 'nullable|integer|in:0,90,180,270',
        ]);
    }

    private function processImage(Request $request): string
    {
        $manager = new ImageManager(new Driver());
        $image = $manager->read($request->file('image')->getRealPath());

        $rotation = (int) $request->input('image_rotation', 0);
        if ($rotation !== 0) {
            $image->rotate($rotation);
        }

        $image->cover(800, 800);

        $watermarkPath = public_path('img/brand-watermark.png');
        if (is_file($watermarkPath)) {
            $image->place($watermarkPath, 'bottom-right', 20, 20, 35);
        }

        $path = 'products/'.Str::uuid().'.jpg';
        $encoded = $image->toJpeg(85);
        Storage::disk('public')->put($path, (string) $encoded);

        return $path;
    }

    public function store(Request $request)
    {
        $data = $this->validateProduct($request);
        unset($data['image_rotation']);
        $data['is_active'] = $request->boolean('is_active');

        $newPath = null;
        try {
            if ($request->hasFile('image')) {
                $newPath = $this->processImage($request);
                $data['image'] = $newPath;
            }
            Product::create($data);
        } catch (\Throwable $e) {
            if ($newPath) {
                Storage::disk('public')->delete($newPath);
            }
            throw $e;
        }

        return redirect()->route('products.index')->with('success', 'Produk berhasil ditambahkan!');
    }

    public function show(Product $product)
    {
        return view('admin.products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        return view('admin.products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $this->validateProduct($request);
        unset($data['image_rotation']);
        $data['is_active'] = $request->boolean('is_active');

        $newPath = null;
        $oldPath = $product->image;
        try {
            if ($request->hasFile('image')) {
                $newPath = $this->processImage($request);
                $data['image'] = $newPath;
            } else {
                unset($data['image']);
            }
            $product->update($data);
        } catch (\Throwable $e) {
            if ($newPath) {
                Storage::disk('public')->delete($newPath);
            }
            throw $e;
        }

        if ($newPath && $oldPath && $oldPath !== $newPath) {
            Storage::disk('public')->delete($oldPath);
        }

        return redirect()->route('products.index')->with('success', 'Produk berhasil diperbarui!');
    }

    public function destroy(Product $product)
    {
        $imagePath = $product->image;
        $product->delete();
        if ($imagePath) {
            Storage::disk('public')->delete($imagePath);
        }

        return redirect()->route('products.index')->with('success', 'Produk berhasil dihapus!');
    }
}
