@extends('admin.layout')

@section('title', 'Detail Produk')

@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Detail Produk</h1>
        <div>
            <a href="{{ route('products.edit', $product) }}" class="btn btn-warning btn-sm shadow-sm mr-2">
                <i class="fas fa-edit fa-sm mr-1"></i> Edit
            </a>
            <a href="{{ route('products.index') }}" class="btn btn-secondary btn-sm shadow-sm">
                <i class="fas fa-arrow-left fa-sm mr-1"></i> Kembali
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Foto Produk -->
        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-body text-center">
                    @if($product->image)
                        <img src="{{ Storage::url($product->image) }}"
                                alt="{{ $product->name }}"
                                class="img-fluid rounded"
                                style="max-height:300px;object-fit:contain;">
                    @else
                        <div class="bg-light rounded d-flex align-items-center justify-content-center"
                                style="height:250px;">
                            <div>
                                <i class="fas fa-image fa-4x text-secondary mb-2"></i>
                                <p class="text-muted">Tidak ada foto</p>
                            </div>
                        </div>
                    @endif
                    <hr>
                    <div class="mt-2">
                        @if($product->is_active)
                            <span class="badge badge-success badge-pill px-3 py-2">
                                <i class="fas fa-check-circle mr-1"></i> Aktif
                            </span>
                        @else
                            <span class="badge badge-danger badge-pill px-3 py-2">
                                <i class="fas fa-times-circle mr-1"></i> Nonaktif
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Hapus Produk -->
            <div class="card border-left-danger shadow mb-4">
                <div class="card-body">
                    <h6 class="font-weight-bold text-danger">
                        <i class="fas fa-exclamation-triangle mr-1"></i> Danger Zone
                    </h6>
                    <p class="text-muted small mb-3">Menghapus produk bersifat permanen dan tidak dapat dikembalikan.</p>
                    <form action="{{ route('products.destroy', $product) }}"
                            method="POST"
                            onsubmit="return confirm('Yakin ingin menghapus produk {{ addslashes($product->name) }}? Tindakan ini tidak dapat dibatalkan!')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-block">
                            <i class="fas fa-trash mr-1"></i> Hapus Produk Ini
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Info Produk -->
        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle mr-1"></i> Informasi Produk
                    </h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tbody>
                            <tr>
                                <th width="160" class="text-muted">ID Produk</th>
                                <td><span class="badge badge-secondary">#{{ $product->id }}</span></td>
                            </tr>
                            <tr>
                                <th class="text-muted">Nama Produk</th>
                                <td class="font-weight-bold">{{ $product->name }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Kategori</th>
                                <td>
                                    @if($product->category)
                                        <span class="badge badge-info">{{ $product->category }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="text-muted">Harga</th>
                                <td class="font-weight-bold text-success h5">
                                    Rp {{ number_format($product->price, 0, ',', '.') }}
                                </td>
                            </tr>
                            <tr>
                                <th class="text-muted">Stok</th>
                                <td>
                                    <span class="badge badge-{{ $product->stock > 10 ? 'success' : ($product->stock > 0 ? 'warning' : 'danger') }} badge-pill px-3">
                                        {{ $product->stock }} unit
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th class="text-muted">Status</th>
                                <td>
                                    @if($product->is_active)
                                        <span class="badge badge-success">Aktif</span>
                                    @else
                                        <span class="badge badge-danger">Nonaktif</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="text-muted">Dibuat</th>
                                <td>{{ $product->created_at->format('d M Y, H:i') }} WIB</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Diperbarui</th>
                                <td>{{ $product->updated_at->format('d M Y, H:i') }} WIB</td>
                            </tr>
                        </tbody>
                    </table>

                    @if($product->description)
                        <hr>
                        <h6 class="font-weight-bold text-muted">Deskripsi Produk</h6>
                        <p class="text-justify">{{ $product->description }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
