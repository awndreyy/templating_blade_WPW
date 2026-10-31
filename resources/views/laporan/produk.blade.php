<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Data Produk</title>
    <style>
        @page { margin: 20mm 15mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #222; }
        h2 { margin: 0 0 6px; text-align: center; }
        .subtitle { text-align: center; margin-bottom: 18px; color: #555; }
        .summary { margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #555; padding: 7px; }
        th { background-color: #eeeeee; text-align: center; }
        .center { text-align: center; }
        .right { text-align: right; }
        tr { page-break-inside: avoid; }
        .footer { margin-top: 16px; font-size: 10px; color: #555; }
    </style>
</head>
<body>
    <h2>LAPORAN DATA PRODUK</h2>
    <div class="subtitle">Minimarket | Dicetak: {{ $tanggal }}</div>
    <div class="summary">
        Total produk: <strong>{{ $totalProduk }}</strong> &nbsp; | &nbsp;
        Total stok: <strong>{{ number_format($totalStok, 0, ',', '.') }}</strong>
    </div>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Kode Produk</th>
                <th>Nama Produk</th>
                <th>Kategori</th>
                <th>Harga</th>
                <th>Stok</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($products as $product)
                <tr>
                    <td class="center">{{ $loop->iteration }}</td>
                    <td>{{ $product->code ?? '-' }}</td>
                    <td>{{ $product->name }}</td>
                    <td>{{ $product->category ?? '-' }}</td>
                    <td class="right">Rp {{ number_format((float) $product->price, 0, ',', '.') }}</td>
                    <td class="center">{{ $product->stock }}</td>
                    <td class="center">{{ $product->is_active ? 'Aktif' : 'Nonaktif' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="center">Belum ada data produk.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <div class="footer">Laporan ini dibuat otomatis dari data produk pada sistem minimarket.</div>
</body>
</html>
