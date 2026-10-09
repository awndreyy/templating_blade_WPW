<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Sistem Kasir Modern Minimarket POS">
    <meta name="author" content="TOKO ANAK LANANG">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Kasir - TOKO ANAK LANANG</title>

    <!-- Custom fonts for this template-->
    <link href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700;800&family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap core styles -->
    <link href="{{ asset('css/sb-admin-2.min.css') }}" rel="stylesheet">

    <!-- Dedicated Light POS Custom Styles -->
    <link href="{{ asset('css/pos-kasir.css') }}" rel="stylesheet">
</head>

<body id="page-top">

    <!-- POS Fullscreen Application Wrapper (No Sidebar) -->
    <div class="pos-wrapper">

        <!-- Top Navigation Bar -->
        <nav class="navbar navbar-expand-lg navbar-light bg-white pos-topbar sticky-top">
            <div class="container-fluid px-2 px-md-3">

                <!-- Left: Brand / Store Badge -->
                <div class="d-flex align-items-center">
                    <a class="d-flex align-items-center text-decoration-none mr-3" href="{{ route('kasir.dashboard') }}">
                        <span class="pos-store-badge d-flex align-items-center">
                            <i class="fas fa-cash-register mr-2"></i> POS KASIR
                        </span>
                    </a>
                    <div class="d-none d-md-block">
                        <span class="font-weight-bold text-dark">TOKO ANAK LANANG</span>
                        <span class="text-muted small ml-2">&bull; Terminal Kasir #01</span>
                    </div>
                </div>

                <!-- Center/Right Menu Quick Action Tools -->
                <div class="d-flex align-items-center ml-auto">

                    <!-- Quick Menu: Katalog Barang -->
                    <button class="btn btn-sm btn-outline-primary font-weight-bold mr-2" data-toggle="modal" data-target="#modalCariBarang" title="Katalog Barang [F4]">
                        <i class="fas fa-boxes mr-1"></i> <span class="d-none d-sm-inline">Katalog (F4)</span>
                    </button>

                    <!-- Quick Menu: Pesanan Tertahan -->
                    <button class="btn btn-sm btn-outline-warning font-weight-bold mr-2 text-dark" onclick="showHeldOrdersModal()" title="Pesanan Tertahan">
                        <i class="fas fa-clock mr-1 text-warning"></i> <span class="d-none d-sm-inline">Tertahan</span>
                        <span class="badge badge-warning ml-1" id="heldCountBadge">0</span>
                    </button>

                    <!-- Keyboard Shortcut Help -->
                    <button class="btn btn-sm btn-outline-info font-weight-bold mr-3" data-toggle="modal" data-target="#modalShortcutHelp" title="Bantuan Shortcut Keyboard [F1]">
                        <i class="fas fa-keyboard mr-1"></i> <span class="d-none d-sm-inline">Shortcut (F1)</span>
                    </button>

                    <div class="topbar-divider d-none d-sm-block"></div>

                    <!-- Nav Item - User Information -->
                    <div class="dropdown">
                        <a class="nav-link dropdown-toggle text-dark p-0 d-flex align-items-center text-decoration-none" href="#" id="userDropdown" role="button"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <span class="mr-2 d-none d-lg-inline text-dark small font-weight-bold">
                                {{ Auth::user()->name ?? 'Kasir Petugas' }}
                                <span class="badge badge-success ml-1">Kasir</span>
                            </span>
                            <i class="fas fa-user-circle fa-2x text-secondary"></i>
                        </a>
                        <!-- Dropdown - User Information -->
                        <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in mt-2"
                            aria-labelledby="userDropdown">
                            <div class="dropdown-header text-dark font-weight-bold">Petugas Kasir</div>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item text-danger font-weight-bold" href="#" data-toggle="modal" data-target="#logoutModal">
                                <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-danger"></i>
                                Logout
                            </a>
                        </div>
                    </div>

                </div>

            </div>
        </nav>
        <!-- End of Topbar -->

        <!-- Begin Page Content: POS Main Terminal (Fullscreen Width) -->
        <main class="flex-grow-1 p-2">
            <div class="container-fluid px-0">

                <!-- POS TERMINAL WRAPPER -->
                <div class="pos-terminal-container" id="posMainTerminal">

                    <!-- 1. HEADER SECTION (Store Branding & Invoice Info) -->
                    <div class="pos-header">
                        <div class="row align-items-center">
                            <div class="col-md-6 mb-1 mb-md-0">
                                <div class="pos-store-title text-uppercase d-flex align-items-center">
                                    <i class="fas fa-store text-success mr-2"></i>
                                    <span>TOKO ANAK LANANG</span>
                                </div>
                                <div class="text-muted small mono">
                                    Jl. In Aja Dulu No. 123 &bull; Telp: (0331) 333333
                                </div>
                            </div>
                            <div class="col-md-6 text-md-right">
                                <div class="d-inline-flex flex-column align-items-md-end">
                                    <div class="pos-meta-tag mono font-weight-bold mb-1">
                                        <i class="fas fa-receipt mr-1 text-warning"></i> <span id="currentInvoiceNo">TRX-{{ date('Ymd') }}-0001</span>
                                    </div>
                                    <div class="text-muted small mono" id="liveClockDisplay">
                                        {{ date('d/m/Y H:i:s') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 2. MAIN POS WORKSPACE (2 Columns Grid) -->
                    <div class="p-2 p-md-3">
                        <div class="row">

                            <!-- LEFT COLUMN: Tambah Barang, Pelanggan, Keranjang Belanja -->
                            <div class="col-lg-7 col-xl-8 pr-lg-2">

                                <!-- SECTION: Tambah Barang & Pelanggan -->
                                <div class="pos-card mb-2">
                                    <div class="row">
                                        <!-- Tambah Barang / Barcode Input with Live Dropdown -->
                                        <div class="col-md-6 mb-2 mb-md-0 position-relative">
                                            <label class="pos-card-title mb-1">
                                                <span><i class="fas fa-barcode mr-1 text-primary"></i> Tambah Barang</span>
                                                <span class="small font-weight-normal text-muted">[F2] Cari Cepat</span>
                                            </label>
                                            <div class="input-group">
                                                <input type="text" id="inputBarcodeSearch" class="form-control pos-input"
                                                    placeholder="Ketik nama produk atau scan barcode..." autocomplete="off">
                                                <div class="input-group-append">
                                                    <button class="btn btn-sm btn-primary px-3 font-weight-bold" type="button" id="btnCariBarang" data-toggle="modal" data-target="#modalCariBarang" title="Buka Katalog Lengkap (F4)">
                                                        <i class="fas fa-search mr-1"></i> [Katalog]
                                                    </button>
                                                </div>
                                            </div>
                                            <small class="text-muted mt-1 d-block" style="font-size: 0.72rem;">
                                                Ketik nama/barcode &bull; Pilih dari list yang muncul atau tekan <strong>[Katalog]</strong>.
                                            </small>

                                            <!-- Live Search Dropdown Suggestions -->
                                            <div id="searchResultsDropdown" class="pos-search-dropdown d-none shadow">
                                                <div class="p-1 px-2 border-bottom d-flex justify-content-between align-items-center bg-light">
                                                    <span class="small font-weight-bold text-muted" id="searchResultCount" style="font-size: 0.75rem;">Hasil Pencarian Produk:</span>
                                                    <button type="button" class="close text-muted" style="font-size: 1rem; line-height: 1;" onclick="hideSearchDropdown()">&times;</button>
                                                </div>
                                                <div id="searchResultsList" class="pos-search-list">
                                                    <!-- Injected dynamically by JS -->
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Pelanggan & No Member/HP -->
                                        <div class="col-md-3 col-6">
                                            <label class="pos-card-title mb-1">
                                                <span><i class="fas fa-user-tag mr-1 text-info"></i> Pelanggan</span>
                                            </label>
                                            <select id="selectCustomerType" class="form-control pos-select mono">
                                                <option value="Umum" selected>Umum</option>
                                                <option value="Member">Member</option>
                                                <option value="VIP">VIP (Disc 5%)</option>
                                                <option value="Grosir">Grosir (Disc 10%)</option>
                                            </select>
                                        </div>

                                        <div class="col-md-3 col-6">
                                            <label class="pos-card-title mb-1">
                                                <span><i class="fas fa-id-card mr-1 text-secondary"></i> No Member/HP</span>
                                            </label>
                                            <input type="text" id="inputCustomerPhone" class="form-control pos-input mono"
                                                placeholder="0812xxxx">
                                        </div>
                                    </div>
                                </div>

                                <!-- SECTION: Keranjang Belanja Table -->
                                <div class="pos-card mb-2">
                                    <div class="pos-card-title mb-2">
                                        <span>
                                            <i class="fas fa-shopping-cart mr-2 text-success"></i> KERANJANG BELANJA
                                        </span>
                                        <div>
                                            <span class="badge badge-light border text-dark mono font-weight-bold px-2 py-1" id="cartBadgeCount">0 Jenis Produk</span>
                                        </div>
                                    </div>

                                    <!-- Cart Table with scroll -->
                                    <div class="cart-table-wrapper mb-2">
                                        <table class="pos-table" id="tableCart">
                                            <thead>
                                                <tr>
                                                    <th style="width: 5%; text-align: center;">No</th>
                                                    <th style="width: 35%;">Barang</th>
                                                    <th style="width: 20%; text-align: right;">Harga</th>
                                                    <th style="width: 18%; text-align: center;">Qty</th>
                                                    <th style="width: 22%; text-align: right;">Subtotal</th>
                                                    <th style="width: 5%; text-align: center;">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody id="cartTableBody" class="mono">
                                                <!-- Dynamic Cart Items via JavaScript -->
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- Quick Info below table -->
                                    <div class="d-flex justify-content-between align-items-center text-muted small px-1 pt-1" style="font-size: 0.75rem;">
                                        <span><i class="fas fa-info-circle mr-1 text-primary"></i> Tekan <strong>ESC</strong> untuk batal / kosongkan keranjang.</span>
                                        <button class="btn btn-sm btn-outline-danger py-0 px-2 font-weight-bold" style="font-size: 0.75rem;" onclick="clearCart(true)">
                                            <i class="fas fa-trash-alt mr-1"></i> Kosongkan Keranjang
                                        </button>
                                    </div>
                                </div>

                            </div>

                            <!-- RIGHT COLUMN: Ringkasan Pembayaran & Checkout (Ultra-Compact) -->
                            <div class="col-lg-5 col-xl-4 pl-lg-2">

                                <!-- SECTION: Ringkasan Pembayaran -->
                                <div class="pos-card mb-2 p-2">
                                    <div class="pos-card-title mb-1 pb-1 border-bottom">
                                        <span><i class="fas fa-file-invoice-dollar mr-1 text-primary"></i> RINGKASAN PEMBAYARAN</span>
                                        <span class="badge badge-light border text-muted" id="summaryTotalItemsBadge"><span id="summaryTotalItems">0</span> Item</span>
                                    </div>

                                    <div class="mono small" style="font-size: 0.775rem;">
                                        <div class="d-flex justify-content-between py-0 mb-1">
                                            <span class="text-muted">Subtotal:</span>
                                            <span class="font-weight-bold text-dark" id="summarySubtotal">Rp 0</span>
                                        </div>

                                        <!-- Diskon % dan Diskon Nominal Rp -->
                                        <div class="row no-gutters mb-1 align-items-center">
                                            <div class="col-6 pr-1">
                                                <div class="d-flex align-items-center justify-content-between bg-light rounded p-1 border">
                                                    <span class="text-muted" style="font-size: 0.7rem;">Disc %:</span>
                                                    <div class="d-flex align-items-center">
                                                        <input type="number" id="inputDiscountPercent" class="form-control pos-input text-right p-0 font-weight-bold"
                                                            style="width: 38px; height: 22px; font-size: 0.75rem;" value="0" min="0" max="100" oninput="onDiscountPercentChange()">
                                                        <span class="ml-1 text-muted" style="font-size: 0.7rem;">%</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-6 pl-1">
                                                <div class="d-flex align-items-center justify-content-between bg-light rounded p-1 border">
                                                    <span class="text-muted" style="font-size: 0.7rem;">Disc Rp:</span>
                                                    <input type="number" id="inputDiscountNominal" class="form-control pos-input text-right p-0 font-weight-bold"
                                                        style="width: 58px; height: 22px; font-size: 0.75rem;" value="0" min="0" oninput="onDiscountNominalChange()">
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Pajak PPN dan Biaya Lain -->
                                        <div class="row no-gutters mb-1 align-items-center">
                                            <div class="col-6 pr-1">
                                                <div class="d-flex align-items-center justify-content-between bg-light rounded p-1 border">
                                                    <span class="text-muted" style="font-size: 0.7rem;">PPN (Rp):</span>
                                                    <input type="number" id="inputTaxRp" class="form-control pos-input text-right p-0 font-weight-bold"
                                                        style="width: 55px; height: 22px; font-size: 0.75rem;" value="0" min="0" oninput="calculateTotal()">
                                                </div>
                                            </div>
                                            <div class="col-6 pl-1">
                                                <div class="d-flex align-items-center justify-content-between bg-light rounded p-1 border">
                                                    <span class="text-muted" style="font-size: 0.7rem;">Lain (Rp):</span>
                                                    <input type="number" id="inputOtherFees" class="form-control pos-input text-right p-0 font-weight-bold"
                                                        style="width: 55px; height: 22px; font-size: 0.75rem;" value="0" min="0" oninput="calculateTotal()">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="d-flex justify-content-between py-0 text-muted" style="font-size: 0.72rem;">
                                            <span>Potongan Diskon:</span>
                                            <span class="text-danger font-weight-bold" id="summaryDiscountRp">Rp 0</span>
                                        </div>
                                    </div>

                                    <!-- TOTAL AKHIR COMPACT LCD BOX -->
                                    <div class="total-display-box mt-2 mb-0 py-1 px-2 text-right">
                                        <div class="title text-left d-flex justify-content-between align-items-center" style="font-size: 0.68rem; margin-bottom: 2px;">
                                            <span>TOTAL AKHIR</span>
                                            <span class="badge badge-success px-1 py-0 font-weight-bold" style="font-size: 0.65rem;">IDR</span>
                                        </div>
                                        <div class="amount" id="displayTotalAkhir" style="font-size: 1.35rem; line-height: 1.1;">Rp 0</div>
                                    </div>
                                </div>

                                <!-- SECTION: Uang Dibayar & Metode Pembayaran -->
                                <div class="pos-card mb-0 p-2">
                                    <div class="pos-card-title mb-1">
                                        <span><i class="fas fa-money-bill-wave mr-1 text-success"></i> PEMBAYARAN</span>
                                        <span class="small font-weight-normal text-muted" style="font-size: 0.7rem;">[F8] Pas</span>
                                    </div>

                                    <!-- Cash Input Compact -->
                                    <div class="input-group mb-1">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-light border text-dark mono font-weight-bold py-0 px-2" style="font-size: 0.75rem; height: 30px;">Rp</span>
                                        </div>
                                        <input type="number" id="inputUangDibayar" class="form-control pos-input mono font-weight-bold text-dark py-0"
                                            style="font-size: 1rem; height: 30px;" placeholder="0" value="0" oninput="calculateKembalian()">
                                    </div>

                                    <!-- Quick Cash Buttons Compact -->
                                    <div class="d-flex flex-wrap mb-2" id="quickCashContainer" style="gap: 3px;">
                                        <button type="button" class="quick-cash-chip py-0 px-1" onclick="setExactAmount()">[Pas]</button>
                                        <button type="button" class="quick-cash-chip py-0 px-1" onclick="addCashAmount(10000)">+10k</button>
                                        <button type="button" class="quick-cash-chip py-0 px-1" onclick="addCashAmount(20000)">+20k</button>
                                        <button type="button" class="quick-cash-chip py-0 px-1" onclick="addCashAmount(50000)">+50k</button>
                                        <button type="button" class="quick-cash-chip py-0 px-1" onclick="setCashAmount(100000)">100k</button>
                                        <button type="button" class="quick-cash-chip py-0 px-1" onclick="setCashAmount(200000)">200k</button>
                                    </div>

                                    <label class="pos-card-title mb-1" style="font-size: 0.7rem;">Metode Pembayaran</label>
                                    <div class="row no-gutters mb-1" style="gap: 3px;">
                                        <button type="button" class="btn-payment-method active py-1" id="btnMethodTunai" onclick="selectPaymentMethod('Tunai')">
                                            Tunai
                                        </button>
                                        <button type="button" class="btn-payment-method py-1" id="btnMethodQRIS" onclick="selectPaymentMethod('QRIS')">
                                            QRIS
                                        </button>
                                        <button type="button" class="btn-payment-method py-1" id="btnMethodDebit" onclick="selectPaymentMethod('Debit')">
                                            Debit
                                        </button>
                                    </div>
                                    <div class="row no-gutters mb-2" style="gap: 3px;">
                                        <button type="button" class="btn-payment-method py-1" id="btnMethodTransfer" onclick="selectPaymentMethod('Transfer')">
                                            Transfer
                                        </button>
                                        <button type="button" class="btn-payment-method py-1" id="btnMethodKredit" onclick="selectPaymentMethod('Kredit')">
                                            Kredit
                                        </button>
                                        <button type="button" class="btn-payment-method py-1" id="btnMethodE-Wallet" onclick="selectPaymentMethod('E-Wallet')">
                                            E-Wallet
                                        </button>
                                    </div>

                                    <div class="kembalian-display-box positive mono py-1 px-2 mb-2" id="boxKembalian">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="text-uppercase text-muted font-weight-bold" style="font-size: 0.7rem;" id="labelKembalian">KEMBALIAN</span>
                                            <span class="font-weight-bold text-success" style="font-size: 1rem;" id="displayKembalian">Rp 0</span>
                                        </div>
                                    </div>

                                    <div class="row mb-1 no-gutters">
                                        <div class="col-6 pr-1">
                                            <button type="button" class="btn btn-sm btn-warning btn-block font-weight-bold py-1 mono text-dark" style="font-size: 0.75rem;" onclick="holdCurrentOrder()">
                                                <i class="fas fa-pause mr-1"></i> [Tahan]
                                            </button>
                                        </div>
                                        <div class="col-6 pl-1">
                                            <button type="button" class="btn btn-sm btn-outline-danger btn-block font-weight-bold py-1 mono" style="font-size: 0.75rem;" onclick="clearCart(true)">
                                                <i class="fas fa-times mr-1"></i> [Batal (ESC)]
                                            </button>
                                        </div>
                                    </div>

                                    <button type="button" class="btn-checkout mono mt-1 py-2 font-weight-bold" style="font-size: 0.95rem;" id="btnBayarCetak" onclick="processCheckout()">
                                        <i class="fas fa-print mr-2"></i> [ BAYAR & CETAK (F9) ]
                                    </button>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-white border-top py-2 mt-auto">
            <div class="container-fluid px-3">
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center text-muted small" style="font-size: 0.75rem;">
                    <div>
                        <strong>TOKO ANAK LANANG</strong> &copy; {{ date('Y') }} &bull; Sistem Kasir Minimarket
                    </div>
                    <div class="mt-1 mt-sm-0">
                        <span class="badge badge-light border text-secondary mr-2">Mode: Siap Melayani</span>
                        <span>Shortcut Keyboard: <strong>[F1]</strong> Bantuan &bull; <strong>[ESC]</strong> Batal</span>
                    </div>
                </div>
            </div>
        </footer>

    </div>

    <!-- MODAL 1: CARI BARANG / KATALOG PRODUK (LIGHT THEME) -->
    <div class="modal fade" id="modalCariBarang" tabindex="-1" role="dialog" aria-labelledby="modalCariBarangLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white py-2 px-3">
                    <h5 class="modal-title font-weight-bold" id="modalCariBarangLabel" style="font-size: 1rem;">
                        <i class="fas fa-boxes mr-2"></i>Katalog & Pencarian Produk
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-3">
                    <!-- Search Input in modal -->
                    <div class="input-group mb-2">
                        <input type="text" id="modalFilterInput" class="form-control pos-input mono"
                            placeholder="Ketik nama produk, kode, barcode, atau kategori..." onkeyup="filterProductList()">
                        <div class="input-group-append">
                            <span class="input-group-text bg-light border text-secondary py-1 px-2">
                                <i class="fas fa-search"></i>
                            </span>
                        </div>
                    </div>

                    <!-- Product Table in modal -->
                    <div style="max-height: 360px; overflow-y: auto;" class="border rounded">
                        <table class="table table-sm table-hover table-striped mb-0 mono" style="font-size: 0.825rem;">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width: 14%;">Kode</th>
                                    <th style="width: 16%;">Barcode</th>
                                    <th>Nama Barang</th>
                                    <th style="width: 16%;">Kategori</th>
                                    <th class="text-right" style="width: 18%;">Harga</th>
                                    <th class="text-center" style="width: 10%;">Stok</th>
                                    <th class="text-center" style="width: 12%;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="modalProductListTable">
                                @forelse($products as $prod)
                                    <tr class="product-search-row"
                                        data-name="{{ strtolower($prod->name) }}"
                                        data-code="{{ strtolower($prod->code ?? '') }}"
                                        data-barcode="{{ $prod->barcode ?? '' }}"
                                        data-category="{{ strtolower($prod->category ?? '') }}">
                                        <td><code>{{ $prod->code ?? '-' }}</code></td>
                                        <td><code>{{ $prod->barcode ?? '-' }}</code></td>
                                        <td class="font-weight-bold text-dark">{{ $prod->name }}</td>
                                        <td><span class="product-badge-category">{{ $prod->category ?? 'Umum' }}</span></td>
                                        <td class="text-right text-success font-weight-bold">Rp {{ number_format($prod->price, 0, ',', '.') }}</td>
                                        <td class="text-center font-weight-bold {{ $prod->stock <= 5 ? 'text-danger' : 'text-dark' }}" id="modalStock-{{ $prod->id }}">{{ $prod->stock ?? 0 }}</td>
                                        <td class="text-center">
                                            <button class="btn btn-xs btn-success px-2 py-0 font-weight-bold" onclick='selectProductFromModal(@json($prod))'>
                                                + Pilih
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-3">Tidak ada data produk.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2 px-3">
                    <button type="button" class="btn btn-sm btn-secondary font-weight-bold" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL 2: STRUK PEMBAYARAN & CETAK (THERMAL RECEIPT) -->
    <div class="modal fade" id="modalStruk" tabindex="-1" role="dialog" aria-labelledby="modalStrukLabel" aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 420px;" role="document">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-success text-white py-2 px-3">
                    <h5 class="modal-title font-weight-bold" id="modalStrukLabel" style="font-size: 1rem;">
                        <i class="fas fa-receipt mr-2"></i>Struk Transaksi Kasir
                    </h5>
                    <button type="button" class="close text-white" onclick="startNewTransaction()" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body bg-light p-3">
                    <div class="thermal-receipt" id="printableReceipt">
                        <div class="text-center">
                            <h5 class="font-weight-bold mb-0">TOKO ANAK LANANG</h5>
                            <div>Jl. In Aja Dulu No. 123, Indonesia</div>
                            <div>Telp: (0331) 333333</div>
                        </div>

                        <div class="receipt-divider"></div>

                        <div class="d-flex justify-content-between">
                            <span>No: <strong id="receiptInvoice">TRX-20260911-0001</strong></span>
                            <span id="receiptDate">{{ date('d/m/Y H:i') }}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Kasir: {{ Auth::user()->name ?? 'Kasir Petugas' }}</span>
                            <span>Pelanggan: <strong id="receiptCustomer">Umum</strong></span>
                        </div>

                        <div class="receipt-divider"></div>

                        <div id="receiptItemsList">
                        </div>

                        <div class="receipt-divider"></div>

                        <div class="d-flex justify-content-between">
                            <span>Total Item:</span>
                            <span id="receiptTotalItems">0</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Subtotal:</span>
                            <span id="receiptSubtotal">Rp 0</span>
                        </div>
                        <div class="d-flex justify-content-between" id="receiptDiscountRow">
                            <span>Diskon:</span>
                            <span id="receiptDiscount">Rp 0</span>
                        </div>
                        <div class="d-flex justify-content-between" id="receiptTaxRow">
                            <span>Pajak (PPN):</span>
                            <span id="receiptTax">Rp 0</span>
                        </div>
                        <div class="d-flex justify-content-between" id="receiptFeeRow">
                            <span>Biaya Lain:</span>
                            <span id="receiptFee">Rp 0</span>
                        </div>

                        <div class="receipt-divider"></div>

                        <div class="d-flex justify-content-between font-weight-bold" style="font-size: 1.05rem;">
                            <span>TOTAL:</span>
                            <span id="receiptTotalAkhir">Rp 0</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Bayar (<span id="receiptPaymentMethod">Tunai</span>):</span>
                            <span id="receiptUangDibayar">Rp 0</span>
                        </div>
                        <div class="d-flex justify-content-between font-weight-bold text-success">
                            <span>KEMBALIAN:</span>
                            <span id="receiptKembalian">Rp 0</span>
                        </div>

                        <div class="receipt-divider"></div>

                        <div class="text-center mt-2 small text-muted" style="font-size: 0.72rem;">
                            <div>*** TERIMA KASIH ***</div>
                            <div>Barang yang dibeli tidak dapat ditukar</div>
                            <div>Layanan Konsumen: 0893-3333-3333</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-between bg-white py-2 px-3">
                    <button type="button" class="btn btn-sm btn-secondary font-weight-bold" onclick="startNewTransaction()">Tutup</button>
                    <div>
                        <button type="button" class="btn btn-sm btn-primary font-weight-bold mr-1" onclick="window.print()">
                            <i class="fas fa-print mr-1"></i> Cetak Struk
                        </button>
                        <button type="button" class="btn btn-sm btn-success font-weight-bold" onclick="startNewTransaction()">
                            <i class="fas fa-plus-circle mr-1"></i> Transaksi Baru
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL 3: PESANAN TERTAHAN -->
    <div class="modal fade" id="modalHeldOrders" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-warning text-dark py-2 px-3">
                    <h5 class="modal-title font-weight-bold" style="font-size: 1rem;">
                        <i class="fas fa-pause-circle mr-2"></i>Daftar Transaksi Tertahan
                    </h5>
                    <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-3">
                    <div id="heldOrdersListContainer">
                    </div>
                </div>
                <div class="modal-footer bg-light py-2 px-3">
                    <button type="button" class="btn btn-sm btn-secondary font-weight-bold" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL 4: SHORTCUT HELP -->
    <div class="modal fade" id="modalShortcutHelp" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-info text-white py-2 px-3">
                    <h5 class="modal-title font-weight-bold" style="font-size: 1rem;"><i class="fas fa-keyboard mr-2"></i>Shortcut Keyboard Kasir</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-3">
                    <table class="table table-sm table-bordered mb-0" style="font-size: 0.85rem;">
                        <tbody>
                            <tr>
                                <th style="width: 35%;"><kbd>F1</kbd></th>
                                <td>Buka panduan shortcut keyboard ini</td>
                            </tr>
                            <tr>
                                <th><kbd>F2</kbd></th>
                                <td>Fokus langsung ke kolom cari / barcode barang</td>
                            </tr>
                            <tr>
                                <th><kbd>F4</kbd></th>
                                <td>Buka modal pencarian katalog produk</td>
                            </tr>
                            <tr>
                                <th><kbd>F8</kbd></th>
                                <td>Set jumlah uang dibayar sesuai uang pas</td>
                            </tr>
                            <tr>
                                <th><kbd>F9</kbd></th>
                                <td>Proses checkout [BAYAR & CETAK]</td>
                            </tr>
                            <tr>
                                <th><kbd>ESC</kbd></th>
                                <td>Batal / kosongkan keranjang / tutup pencarian</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer bg-light py-2 px-3">
                    <button type="button" class="btn btn-sm btn-primary font-weight-bold" data-dismiss="modal">Mengerti</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Logout-->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="logoutModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-danger text-white py-2 px-3">
                    <h5 class="modal-title font-weight-bold" id="logoutModalLabel" style="font-size: 1rem;"><i class="fas fa-sign-out-alt mr-2"></i>Konfirmasi Logout</h5>
                    <button class="close text-white" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body p-3">
                    Apakah Anda yakin ingin keluar dari sistem kasir POS?
                </div>
                <div class="modal-footer bg-light py-2 px-3">
                    <button class="btn btn-sm btn-secondary font-weight-bold" type="button" data-dismiss="modal">Batal</button>
                    <form action="{{ route('logout') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-danger font-weight-bold">Ya, Logout</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap core JavaScript-->
    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('vendor/jquery-easing/jquery.easing.min.js') }}"></script>

    <!-- Kasir POS Application Logic -->
    <script>
        // Inisialisasi Keranjang Kosong
        let cart = [];
        let catalogProducts = @json($products);
        let selectedPaymentMethod = 'Tunai';
        let currentTotalAkhir = 0;
        let heldOrders = [];

        function formatRupiah(amount) {
            return 'Rp ' + Number(amount || 0).toLocaleString('id-ID');
        }

        function updateLiveClock() {
            const now = new Date();
            const dateStr = String(now.getDate()).padStart(2, '0') + '/' +
                            String(now.getMonth() + 1).padStart(2, '0') + '/' +
                            now.getFullYear() + ' ' +
                            String(now.getHours()).padStart(2, '0') + ':' +
                            String(now.getMinutes()).padStart(2, '0') + ':' +
                            String(now.getSeconds()).padStart(2, '0');
            const el = document.getElementById('liveClockDisplay');
            if (el) el.innerText = dateStr;
        }
        setInterval(updateLiveClock, 1000);
        updateLiveClock();

        function renderCartTable() {
            const tbody = document.getElementById('cartTableBody');
            tbody.innerHTML = '';

            if (cart.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">
                            <i class="fas fa-shopping-basket fa-2x mb-2 d-block text-secondary opacity-50"></i>
                            Keranjang belanja kosong. Cari atau scan barcode barang di atas.
                        </td>
                    </tr>
                `;
                document.getElementById('cartBadgeCount').innerText = '0 Jenis Produk';
                calculateTotal();
                return;
            }

            document.getElementById('cartBadgeCount').innerText = `${cart.length} Jenis Produk`;

            cart.forEach((item, index) => {
                const subtotal = item.price * item.qty;
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="text-center text-muted">${index + 1}</td>
                    <td>
                        <div class="font-weight-bold text-dark">${escapeHtml(item.name)}</div>
                        <small class="text-muted">${item.code ? `<code>${escapeHtml(item.code)}</code> &bull; ` : ''}${item.barcode ? `${escapeHtml(item.barcode)}` : ''}</small>
                    </td>
                    <td class="text-right text-secondary font-weight-bold">${Number(item.price).toLocaleString('id-ID')}</td>
                    <td class="text-center">
                        <div class="d-inline-flex align-items-center">
                            <button type="button" class="qty-btn" onclick="updateItemQty(${item.id}, ${item.qty - 1})">-</button>
                            <input type="number" class="qty-input mx-1" min="1" max="${item.stock || 9999}" value="${item.qty}" onchange="updateItemQty(${item.id}, this.value)">
                            <button type="button" class="qty-btn" onclick="updateItemQty(${item.id}, ${item.qty + 1})">+</button>
                        </div>
                    </td>
                    <td class="text-right font-weight-bold text-success" style="font-size: 0.95rem;">${Number(subtotal).toLocaleString('id-ID')}</td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm text-danger p-0" onclick="removeItemFromCart(${item.id})" title="Hapus">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </td>
                `;
                tbody.appendChild(tr);
            });

            calculateTotal();
        }

        function addProductToCart(product) {
            const existingIndex = cart.findIndex(item => item.id == product.id || (product.barcode && item.barcode == product.barcode));
            const availableStock = parseInt(product.stock) || 0;

            if (existingIndex > -1) {
                if (cart[existingIndex].qty + 1 > availableStock) {
                    alert(`Stok produk '${product.name}' tidak mencukupi (Tersedia: ${availableStock}).`);
                    return;
                }
                cart[existingIndex].qty += 1;
            } else {
                if (availableStock <= 0) {
                    alert(`Stok produk '${product.name}' habis!`);
                    return;
                }
                cart.push({
                    id: product.id,
                    code: product.code || '',
                    name: product.name,
                    barcode: product.barcode || '',
                    price: parseFloat(product.price),
                    stock: availableStock,
                    qty: 1
                });
            }
            renderCartTable();
        }

        function updateItemQty(productId, newQty) {
            const qty = parseInt(newQty);
            if (isNaN(qty) || qty <= 0) {
                removeItemFromCart(productId);
                return;
            }
            const item = cart.find(i => i.id == productId);
            if (item) {
                if (qty > item.stock) {
                    alert(`Stok produk '${item.name}' hanya tersedia ${item.stock} item.`);
                    item.qty = item.stock;
                } else {
                    item.qty = qty;
                }
                renderCartTable();
            }
        }

        function removeItemFromCart(productId) {
            cart = cart.filter(item => item.id != productId);
            renderCartTable();
        }

        function clearCart(confirmPrompt = true) {
            if (confirmPrompt && cart.length > 0) {
                if (!confirm('Apakah Anda yakin ingin membatalkan transaksi dan mengosongkan keranjang?')) {
                    return;
                }
            }
            cart = [];
            renderCartTable();
        }

        // Perubahan Diskon %
        function onDiscountPercentChange() {
            const percent = parseFloat(document.getElementById('inputDiscountPercent').value) || 0;
            let subtotal = 0;
            cart.forEach(item => subtotal += (item.price * item.qty));

            const discountNominal = Math.round((subtotal * percent) / 100);
            document.getElementById('inputDiscountNominal').value = discountNominal;
            calculateTotal();
        }

        // Perubahan Diskon Nominal Rp
        function onDiscountNominalChange() {
            const nominal = parseFloat(document.getElementById('inputDiscountNominal').value) || 0;
            let subtotal = 0;
            cart.forEach(item => subtotal += (item.price * item.qty));

            const percent = subtotal > 0 ? ((nominal / subtotal) * 100).toFixed(1) : 0;
            document.getElementById('inputDiscountPercent').value = percent;
            calculateTotal();
        }

        // Hitung Total, Diskon, Pajak, Biaya Lain, dan Kembalian
        function calculateTotal() {
            let totalItemsCount = 0;
            let subtotal = 0;

            cart.forEach(item => {
                totalItemsCount += parseInt(item.qty);
                subtotal += (item.price * item.qty);
            });

            // Baca diskon nominal
            let discountNominal = parseFloat(document.getElementById('inputDiscountNominal').value) || 0;
            const discountPercent = parseFloat(document.getElementById('inputDiscountPercent').value) || 0;

            if (discountNominal <= 0 && discountPercent > 0) {
                discountNominal = (subtotal * discountPercent) / 100;
            }

            const taxRp = parseFloat(document.getElementById('inputTaxRp').value) || 0;
            const otherFees = parseFloat(document.getElementById('inputOtherFees').value) || 0;

            currentTotalAkhir = Math.max(0, subtotal - discountNominal + taxRp + otherFees);

            document.getElementById('summaryTotalItems').innerText = totalItemsCount;
            document.getElementById('summarySubtotal').innerText = formatRupiah(subtotal);
            document.getElementById('summaryDiscountRp').innerText = formatRupiah(discountNominal);
            document.getElementById('displayTotalAkhir').innerText = formatRupiah(currentTotalAkhir);

            if (selectedPaymentMethod !== 'Tunai') {
                document.getElementById('inputUangDibayar').value = currentTotalAkhir;
            }

            calculateKembalian();
        }

        // Kembalian
        function calculateKembalian() {
            const kembalianEl = document.getElementById('displayKembalian');
            const kembalianBox = document.getElementById('boxKembalian');
            const labelKembalian = document.getElementById('labelKembalian');

            if (selectedPaymentMethod !== 'Tunai') {
                labelKembalian.innerText = 'METODE BAYAR';
                labelKembalian.className = 'text-uppercase text-muted font-weight-bold';
                kembalianEl.innerText = `Lunas (${selectedPaymentMethod})`;
                kembalianEl.className = 'font-weight-bold text-success';
                kembalianBox.className = 'kembalian-display-box positive mono py-1 px-2 mb-2';
                return;
            }

            const uangDibayar = parseFloat(document.getElementById('inputUangDibayar').value) || 0;
            const kembalian = uangDibayar - currentTotalAkhir;

            if (kembalian >= 0) {
                labelKembalian.innerText = 'KEMBALIAN';
                labelKembalian.className = 'text-uppercase text-muted font-weight-bold';
                kembalianEl.innerText = formatRupiah(kembalian);
                kembalianEl.className = 'font-weight-bold text-success';
                kembalianBox.className = 'kembalian-display-box positive mono py-1 px-2 mb-2';
            } else {
                labelKembalian.innerText = 'KURANG BAYAR';
                labelKembalian.className = 'text-uppercase text-danger font-weight-bold';
                kembalianEl.innerText = formatRupiah(Math.abs(kembalian));
                kembalianEl.className = 'font-weight-bold text-danger';
                kembalianBox.className = 'kembalian-display-box negative mono py-1 px-2 mb-2';
            }
        }

        function setExactAmount() {
            document.getElementById('inputUangDibayar').value = currentTotalAkhir;
            calculateKembalian();
        }

        function setCashAmount(val) {
            if (selectedPaymentMethod !== 'Tunai') {
                selectPaymentMethod('Tunai');
            }
            document.getElementById('inputUangDibayar').value = val;
            calculateKembalian();
        }

        function addCashAmount(val) {
            if (selectedPaymentMethod !== 'Tunai') {
                selectPaymentMethod('Tunai');
            }
            const current = parseFloat(document.getElementById('inputUangDibayar').value) || 0;
            document.getElementById('inputUangDibayar').value = current + val;
            calculateKembalian();
        }

        // Select Payment Method (Tunai, QRIS, Debit, Transfer, Kredit, E-Wallet)
        function selectPaymentMethod(method) {
            selectedPaymentMethod = method;
            const methods = ['Tunai', 'QRIS', 'Debit', 'Transfer', 'Kredit', 'E-Wallet'];
            methods.forEach(m => {
                const btn = document.getElementById(`btnMethod${m}`);
                if (btn) {
                    if (m === method) {
                        btn.classList.add('active');
                    } else {
                        btn.classList.remove('active');
                    }
                }
            });

            const cashInput = document.getElementById('inputUangDibayar');
            const quickCash = document.getElementById('quickCashContainer');

            if (method !== 'Tunai') {
                cashInput.value = currentTotalAkhir;
                cashInput.readOnly = true;
                cashInput.classList.add('bg-light');
                if (quickCash) quickCash.style.opacity = '0.5';
            } else {
                cashInput.readOnly = false;
                cashInput.classList.remove('bg-light');
                if (quickCash) quickCash.style.opacity = '1';
            }

            calculateKembalian();
        }

        // Live Search Dropdown Logic
        let activeDropdownIndex = -1;
        let currentFilteredProducts = [];

        const searchInput = document.getElementById('inputBarcodeSearch');
        const searchDropdown = document.getElementById('searchResultsDropdown');
        const searchResultsList = document.getElementById('searchResultsList');
        const searchResultCount = document.getElementById('searchResultCount');

        function hideSearchDropdown() {
            if (searchDropdown) {
                searchDropdown.classList.add('d-none');
                activeDropdownIndex = -1;
            }
        }

        function showSearchDropdown() {
            if (searchDropdown) {
                searchDropdown.classList.remove('d-none');
            }
        }

        function escapeHtml(str) {
            if (!str) return '';
            return String(str).replace(/[&<>"']/g, function(m) {
                return {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'}[m];
            });
        }

        searchInput.addEventListener('input', function () {
            const query = this.value.trim().toLowerCase();
            if (!query) {
                hideSearchDropdown();
                return;
            }

            currentFilteredProducts = catalogProducts.filter(p =>
                (p.name && p.name.toLowerCase().includes(query)) ||
                (p.code && p.code.toLowerCase().includes(query)) ||
                (p.barcode && p.barcode.toLowerCase().includes(query)) ||
                (p.category && p.category.toLowerCase().includes(query))
            );

            activeDropdownIndex = -1;
            renderSearchDropdown(query);
        });

        searchInput.addEventListener('focus', function () {
            const query = this.value.trim().toLowerCase();
            if (query && currentFilteredProducts.length > 0) {
                showSearchDropdown();
            }
        });

        function renderSearchDropdown(query) {
            searchResultsList.innerHTML = '';

            if (currentFilteredProducts.length === 0) {
                searchResultCount.innerText = 'Hasil Pencarian:';
                searchResultsList.innerHTML = `
                    <div class="p-3 text-center text-muted small">
                        <i class="fas fa-search mb-1 d-block opacity-50"></i>
                        Tidak ada barang yang cocok dengan "<strong>${escapeHtml(query)}</strong>".
                    </div>
                `;
                showSearchDropdown();
                return;
            }

            searchResultCount.innerText = `Ditemukan ${currentFilteredProducts.length} produk (klik / gunakan panah untuk pilih):`;

            currentFilteredProducts.forEach((prod, index) => {
                const itemDiv = document.createElement('div');
                itemDiv.className = 'pos-search-item';
                itemDiv.id = `searchItem-${index}`;
                itemDiv.innerHTML = `
                    <div class="d-flex flex-column pr-2" style="line-height: 1.25;">
                        <div class="font-weight-bold text-dark" style="font-size: 0.85rem;">
                            ${escapeHtml(prod.name)}
                            ${prod.code ? `<code class="ml-1 text-primary small">[${escapeHtml(prod.code)}]</code>` : ''}
                            ${prod.barcode ? `<code class="ml-1 text-muted small">[${escapeHtml(prod.barcode)}]</code>` : ''}
                        </div>
                        <div class="small text-muted mt-1">
                            <span class="product-badge-category mr-1">${escapeHtml(prod.category || 'Umum')}</span>
                            <span>Stok: <strong>${prod.stock ?? 0}</strong></span>
                        </div>
                    </div>
                    <div class="text-right d-flex flex-column align-items-end justify-content-center">
                        <div class="font-weight-bold text-success" style="font-size: 0.875rem;">${formatRupiah(prod.price)}</div>
                        <button type="button" class="btn btn-sm btn-success font-weight-bold px-2 py-0 mt-1" style="font-size: 0.725rem;">
                            + Pilih
                        </button>
                    </div>
                `;

                itemDiv.addEventListener('click', function () {
                    selectProductFromLiveSearch(prod);
                });

                searchResultsList.appendChild(itemDiv);
            });

            showSearchDropdown();
        }

        function selectProductFromLiveSearch(product) {
            addProductToCart(product);
            searchInput.value = '';
            hideSearchDropdown();
            searchInput.focus();
        }

        searchInput.addEventListener('keydown', function (e) {
            if (searchDropdown.classList.contains('d-none')) {
                if (e.key === 'ArrowDown') {
                    const query = this.value.trim().toLowerCase();
                    if (query) {
                        this.dispatchEvent(new Event('input'));
                    }
                }
                return;
            }

            const items = searchResultsList.querySelectorAll('.pos-search-item');
            if (items.length === 0) {
                if (e.key === 'Escape') hideSearchDropdown();
                return;
            }

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                activeDropdownIndex = (activeDropdownIndex + 1) % items.length;
                updateActiveDropdownItem(items);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                activeDropdownIndex = (activeDropdownIndex - 1 + items.length) % items.length;
                updateActiveDropdownItem(items);
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (activeDropdownIndex >= 0 && activeDropdownIndex < currentFilteredProducts.length) {
                    selectProductFromLiveSearch(currentFilteredProducts[activeDropdownIndex]);
                } else if (currentFilteredProducts.length === 1) {
                    selectProductFromLiveSearch(currentFilteredProducts[0]);
                } else {
                    const query = this.value.trim().toLowerCase();
                    const exactMatch = catalogProducts.find(p =>
                        (p.barcode && p.barcode.toLowerCase() === query) ||
                        (p.code && p.code.toLowerCase() === query)
                    );
                    if (exactMatch) {
                        selectProductFromLiveSearch(exactMatch);
                    }
                }
            } else if (e.key === 'Escape') {
                hideSearchDropdown();
            }
        });

        function updateActiveDropdownItem(items) {
            items.forEach((it, idx) => {
                if (idx === activeDropdownIndex) {
                    it.classList.add('active');
                    it.scrollIntoView({ block: 'nearest' });
                } else {
                    it.classList.remove('active');
                }
            });
        }

        document.addEventListener('click', function (e) {
            if (!searchInput.contains(e.target) && !searchDropdown.contains(e.target)) {
                hideSearchDropdown();
            }
        });

        function filterProductList() {
            const val = document.getElementById('modalFilterInput').value.toLowerCase();
            const rows = document.querySelectorAll('#modalProductListTable tr.product-search-row');
            rows.forEach(r => {
                const name = r.getAttribute('data-name') || '';
                const code = r.getAttribute('data-code') || '';
                const barcode = r.getAttribute('data-barcode') || '';
                const cat = r.getAttribute('data-category') || '';
                if (name.includes(val) || code.includes(val) || barcode.includes(val) || cat.includes(val)) {
                    r.style.display = '';
                } else {
                    r.style.display = 'none';
                }
            });
        }

        function selectProductFromModal(prod) {
            addProductToCart(prod);
            $('#modalCariBarang').modal('hide');
        }

        document.getElementById('selectCustomerType').addEventListener('change', function () {
            if (this.value === 'VIP') {
                document.getElementById('inputDiscountPercent').value = 5;
            } else if (this.value === 'Grosir') {
                document.getElementById('inputDiscountPercent').value = 10;
            } else {
                document.getElementById('inputDiscountPercent').value = 0;
            }
            onDiscountPercentChange();
        });

        function holdCurrentOrder() {
            if (cart.length === 0) {
                alert('Keranjang masih kosong, tidak ada transaksi untuk ditahan.');
                return;
            }
            const orderId = 'HOLD-' + Date.now().toString().slice(-4);
            const customer = document.getElementById('selectCustomerType').value;
            const phone = document.getElementById('inputCustomerPhone').value || '-';

            heldOrders.push({
                orderId: orderId,
                timestamp: new Date().toLocaleTimeString(),
                customer: customer,
                phone: phone,
                items: JSON.parse(JSON.stringify(cart))
            });

            updateHeldCountBadge();
            clearCart(false);
            alert(`Transaksi berhasil ditahan dengan nomor ID: ${orderId}`);
        }

        function updateHeldCountBadge() {
            document.getElementById('heldCountBadge').innerText = heldOrders.length;
        }

        function showHeldOrdersModal() {
            const container = document.getElementById('heldOrdersListContainer');
            if (heldOrders.length === 0) {
                container.innerHTML = '<div class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x mb-2 d-block opacity-50"></i>Tidak ada transaksi tertahan saat ini.</div>';
            } else {
                let html = '<div class="list-group">';
                heldOrders.forEach((order, idx) => {
                    let total = order.items.reduce((acc, cur) => acc + (cur.price * cur.qty), 0);
                    html += `
                        <div class="list-group-item list-group-item-action bg-light text-dark border d-flex justify-content-between align-items-center mb-2 rounded p-2">
                            <div>
                                <h6 class="mb-1 font-weight-bold text-primary">${order.orderId} (${order.timestamp})</h6>
                                <small class="text-muted font-weight-bold">Pelanggan: ${order.customer} | ${order.items.length} Barang | Total: ${formatRupiah(total)}</small>
                            </div>
                            <div>
                                <button class="btn btn-sm btn-success mr-1 font-weight-bold" onclick="restoreHeldOrder(${idx})"><i class="fas fa-play mr-1"></i> Lanjutkan</button>
                                <button class="btn btn-sm btn-outline-danger" onclick="deleteHeldOrder(${idx})"><i class="fas fa-trash"></i></button>
                            </div>
                        </div>
                    `;
                });
                html += '</div>';
                container.innerHTML = html;
            }
            $('#modalHeldOrders').modal('show');
        }

        function restoreHeldOrder(index) {
            if (cart.length > 0) {
                if (!confirm('Keranjang saat ini berisi barang. Apakah Anda ingin menimpa keranjang ini?')) {
                    return;
                }
            }
            const order = heldOrders.splice(index, 1)[0];
            cart = order.items;
            document.getElementById('selectCustomerType').value = order.customer;
            document.getElementById('inputCustomerPhone').value = order.phone === '-' ? '' : order.phone;
            updateHeldCountBadge();
            renderCartTable();
            $('#modalHeldOrders').modal('hide');
        }

        function deleteHeldOrder(index) {
            if (confirm('Hapus transaksi tertahan ini?')) {
                heldOrders.splice(index, 1);
                updateHeldCountBadge();
                showHeldOrdersModal();
            }
        }

        function processCheckout() {
            if (cart.length === 0) {
                alert('Keranjang belanja kosong! Silakan tambahkan barang terlebih dahulu.');
                return;
            }

            const uangDibayar = parseFloat(document.getElementById('inputUangDibayar').value) || 0;

            if (selectedPaymentMethod === 'Tunai' && uangDibayar < currentTotalAkhir) {
                alert(`Uang yang dibayarkan masih kurang ${formatRupiah(currentTotalAkhir - uangDibayar)}. Silakan periksa kembali nominal uang!`);
                document.getElementById('inputUangDibayar').focus();
                return;
            }

            const discountPercent = parseFloat(document.getElementById('inputDiscountPercent').value) || 0;
            const discountNominal = parseFloat(document.getElementById('inputDiscountNominal').value) || 0;
            const taxRpVal = parseFloat(document.getElementById('inputTaxRp').value) || 0;
            const otherFeeVal = parseFloat(document.getElementById('inputOtherFees').value) || 0;
            const customerType = document.getElementById('selectCustomerType').value;
            const customerPhone = document.getElementById('inputCustomerPhone').value || null;

            const payload = {
                customer_type: customerType,
                customer_phone: customerPhone,
                discount_percent: discountPercent,
                discount_amount: discountNominal,
                tax_amount: taxRpVal,
                other_fees: otherFeeVal,
                payment_method: selectedPaymentMethod,
                cash_paid: selectedPaymentMethod === 'Tunai' ? uangDibayar : currentTotalAkhir,
                items: cart.map(i => ({ id: i.id, qty: i.qty, price: i.price }))
            };

            const btnBayar = document.getElementById('btnBayarCetak');
            const originalBtnHtml = btnBayar.innerHTML;
            btnBayar.disabled = true;
            btnBayar.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Memproses...';

            fetch('{{ route("kasir.transaksi.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(payload)
            })
            .then(async response => {
                let resData = {};
                try {
                    resData = await response.json();
                } catch (e) {
                    throw new Error('Terjadi kesalahan server (Status ' + response.status + ' ' + response.statusText + '). Silakan cek koneksi atau login kembali.');
                }
                if (!response.ok) {
                    throw new Error(resData.message || 'Gagal memproses transaksi.');
                }
                return resData;
            })
            .then(res => {
                btnBayar.disabled = false;
                btnBayar.innerHTML = originalBtnHtml;

                const trxData = res.data;
                const trx = trxData.transaction;
                const details = trxData.details;

                // Update stok di memory & modal
                if (trxData.updated_stocks) {
                    Object.keys(trxData.updated_stocks).forEach(productId => {
                        const newStock = trxData.updated_stocks[productId];
                        const prod = catalogProducts.find(p => p.id == productId);
                        if (prod) prod.stock = newStock;
                        const stockEl = document.getElementById(`modalStock-${productId}`);
                        if (stockEl) {
                            stockEl.innerText = newStock;
                            if (newStock <= 5) {
                                stockEl.className = 'text-center font-weight-bold text-danger';
                            }
                        }
                    });
                }

                // Isi Struk Resmi
                document.getElementById('receiptInvoice').innerText = trx.invoice_number;
                document.getElementById('receiptDate').innerText = trxData.created_at;
                document.getElementById('receiptCustomer').innerText = trx.customer_type + (trx.customer_phone ? ` (${trx.customer_phone})` : '');
                document.getElementById('receiptTotalItems').innerText = details.reduce((acc, c) => acc + parseInt(c.quantity), 0);
                document.getElementById('receiptSubtotal').innerText = formatRupiah(trx.subtotal);
                document.getElementById('receiptDiscount').innerText = formatRupiah(trx.discount_amount);
                document.getElementById('receiptTax').innerText = formatRupiah(trx.tax_amount);
                document.getElementById('receiptFee').innerText = formatRupiah(trx.other_fees);
                document.getElementById('receiptTotalAkhir').innerText = formatRupiah(trx.total_amount);
                document.getElementById('receiptPaymentMethod').innerText = trx.payment_method;
                document.getElementById('receiptUangDibayar').innerText = formatRupiah(trx.cash_paid);
                document.getElementById('receiptKembalian').innerText = formatRupiah(trx.change_amount);

                // Populate Items list in Receipt
                const receiptListContainer = document.getElementById('receiptItemsList');
                receiptListContainer.innerHTML = '';
                details.forEach(item => {
                    const row = document.createElement('div');
                    row.className = 'mb-1';
                    row.innerHTML = `
                        <div class="font-weight-bold">${escapeHtml(item.product_name)}</div>
                        <div class="d-flex justify-content-between text-muted">
                            <span>${item.quantity} x ${Number(item.price).toLocaleString('id-ID')}</span>
                            <span>${Number(item.subtotal).toLocaleString('id-ID')}</span>
                        </div>
                    `;
                    receiptListContainer.appendChild(row);
                });

                // Tampilkan Struk
                $('#modalStruk').modal('show');

                // Kosongkan keranjang untuk transaksi berikutnya
                cart = [];
                renderCartTable();
            })
            .catch(err => {
                btnBayar.disabled = false;
                btnBayar.innerHTML = originalBtnHtml;
                alert(err.message || 'Terjadi kesalahan sistem saat menyimpan transaksi.');
            });
        }

        function startNewTransaction() {
            $('#modalStruk').modal('hide');
            cart = [];
            renderCartTable();

            // Reset Form Input
            document.getElementById('inputDiscountPercent').value = 0;
            document.getElementById('inputDiscountNominal').value = 0;
            document.getElementById('inputTaxRp').value = 0;
            document.getElementById('inputOtherFees').value = 0;
            document.getElementById('inputUangDibayar').value = 0;
            document.getElementById('inputCustomerPhone').value = '';
            document.getElementById('selectCustomerType').value = 'Umum';
            selectPaymentMethod('Tunai');
            calculateTotal();

            setTimeout(() => {
                document.getElementById('inputBarcodeSearch').focus();
            }, 400);
        }

        // Keyboard Shortcuts handler (Termasuk ESC untuk batal)
        document.addEventListener('keydown', function (e) {
            // ESC: Tutup pencarian / modal / Batal transaksi
            if (e.key === 'Escape') {
                if (searchDropdown && !searchDropdown.classList.contains('d-none')) {
                    hideSearchDropdown();
                    return;
                }
                if ($('.modal.show').length > 0) {
                    $('.modal.show').modal('hide');
                    return;
                }
                if (cart.length > 0) {
                    clearCart(true);
                    return;
                }
            }
            // F1: Help modal
            else if (e.key === 'F1') {
                e.preventDefault();
                $('#modalShortcutHelp').modal('show');
            }
            // F2: Focus Barcode Search
            else if (e.key === 'F2') {
                e.preventDefault();
                document.getElementById('inputBarcodeSearch').focus();
            }
            // F4: Search Catalog Modal
            else if (e.key === 'F4') {
                e.preventDefault();
                $('#modalCariBarang').modal('show');
            }
            // F8: Uang Pas
            else if (e.key === 'F8') {
                e.preventDefault();
                setExactAmount();
            }
            // F9: Bayar & Cetak
            else if (e.key === 'F9') {
                e.preventDefault();
                processCheckout();
            }
        });

        // Initialize on load
        document.addEventListener('DOMContentLoaded', function () {
            renderCartTable();
            calculateTotal();
        });
    </script>
</body>

</html>
