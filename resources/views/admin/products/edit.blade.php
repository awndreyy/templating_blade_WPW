@extends('admin.layout')

@section('title', 'Edit Produk')

@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit Produk</h1>
        <a href="{{ route('products.index') }}" class="btn btn-secondary btn-sm shadow-sm">
            <i class="fas fa-arrow-left fa-sm mr-1"></i> Kembali
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-warning">
                <i class="fas fa-edit mr-1"></i> Form Edit Produk: <strong>{{ $product->name }}</strong>
            </h6>
        </div>
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <h6 class="font-weight-bold mb-1"><i class="fas fa-exclamation-triangle mr-1"></i> Terjadi Kesalahan Input:</h6>
                    <ul class="mb-0 pl-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <form action="{{ route('products.update', $product) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-8">
                        <!-- Nama Produk -->
                        <div class="form-group">
                            <label for="name" class="font-weight-bold">
                                Nama Produk <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                    id="name"
                                    name="name"
                                    class="form-control @error('name') is-invalid @enderror"
                                    value="{{ old('name', $product->name) }}"
                                    placeholder="Masukkan nama produk..."
                                    required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Kategori -->
                        <div class="form-group">
                            <label for="category" class="font-weight-bold">Kategori</label>
                            <input type="text"
                                    id="category"
                                    name="category"
                                    class="form-control @error('category') is-invalid @enderror"
                                    value="{{ old('category', $product->category) }}"
                                    placeholder="Masukkan kategori produk...">
                            @error('category')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Deskripsi -->
                        <div class="form-group">
                            <label for="description" class="font-weight-bold">Deskripsi</label>
                            <textarea id="description"
                                        name="description"
                                        class="form-control @error('description') is-invalid @enderror"
                                        rows="4"
                                        placeholder="Masukkan deskripsi produk...">{{ old('description', $product->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Harga & Stok -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="price" class="font-weight-bold">
                                        Harga (Rp) <span class="text-danger">*</span>
                                    </label>
                                    <input type="number"
                                            id="price"
                                            name="price"
                                            class="form-control @error('price') is-invalid @enderror"
                                            value="{{ old('price', $product->price) }}"
                                            min="0"
                                            step="0.01"
                                            required>
                                    @error('price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="stock" class="font-weight-bold">
                                        Stok <span class="text-danger">*</span>
                                    </label>
                                    <input type="number"
                                            id="stock"
                                            name="stock"
                                            class="form-control @error('stock') is-invalid @enderror"
                                            value="{{ old('stock', $product->stock) }}"
                                            min="0"
                                            required>
                                    @error('stock')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Status Aktif -->
                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox"
                                        class="custom-control-input"
                                        id="is_active"
                                        name="is_active"
                                        value="1"
                                        {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                                <label class="custom-control-label font-weight-bold" for="is_active">
                                    Produk Aktif
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <!-- Upload Gambar -->
                        <div class="form-group">
                            <label for="image" class="font-weight-bold">Foto Produk</label>
                            <div class="text-center mb-2">
                                <img id="imagePreview"
                                        src="{{ $product->image ? Storage::url($product->image) : '' }}"
                                        alt="{{ $product->name }}"
                                        class="img-fluid rounded border"
                                        style="max-height:200px;object-fit:contain;width:100%;"
                                        onerror="this.src='data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyMDAiIGhlaWdodD0iMjAwIj48cmVjdCB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgZmlsbD0iI2VlZSIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBmb250LXNpemU9IjE4IiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBkeT0iLjNlbSIgZmlsbD0iIzk5OSI+Tm8gSW1hZ2U8L3RleHQ+PC9zdmc+'">
                            </div>
                            <input type="file"
                                    id="image"
                                    name="image"
                                    class="form-control-file @error('image') is-invalid @enderror"
                                    accept="image/jpeg,image/png,image/jpg,image/webp"
                                    onchange="previewImage(this)">
                                <small class="text-muted">Biarkan kosong jika tidak ingin mengubah foto.</small>
                            @error('image')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <hr>
                <div class="text-right">
                    <a href="{{ route('products.index') }}" class="btn btn-secondary mr-2">Batal</a>
                    <a href="{{ route('products.show', $product) }}" class="btn btn-info mr-2">
                        <i class="fas fa-eye mr-1"></i> Lihat Detail
                    </a>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save mr-1"></i> Update Produk
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function previewImage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('imagePreview').src = e.target.result;
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endpush
