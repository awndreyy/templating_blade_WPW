<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <meta name="csrf-token"
          content="{{ csrf_token() }}">

    <title>Checkout Midtrans - Toko Anak Lanang</title>

    <script
        src="https://app.sandbox.midtrans.com/snap/snap.js"
        data-client-key="{{ config('midtrans.clientKey') }}">
    </script>

    <style>
        * { box-sizing: border-box; }

        body {
            font-family: Arial, sans-serif;
            background: #f3f4f6;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }

        .card {
            width: 400px;
            max-width: calc(100% - 32px);
            background: white;
            padding: 30px;
            border-radius: 18px;
            box-shadow: 0 8px 30px #00000012;
        }

        h2 { margin-top: 0; }

        .price {
            font-size: 30px;
            font-weight: bold;
            color: #047857;
            margin: 20px 0;
        }

        button {
            width: 100%;
            background: #047857;
            color: white;
            border: none;
            border-radius: 10px;
            padding: 14px;
            font-size: 16px;
            cursor: pointer;
        }

        button:disabled {
            opacity: .6;
            cursor: wait;
        }

        #status {
            margin-top: 18px;
            font-size: 14px;
        }
    </style>
</head>

<body>
    <div class="card">
        <h2>Toko Anak Lanang</h2>

        <p>Simulasi Pembelian Produk Digital</p>

        <hr>

        <h3>Produk Digital</h3>
        <p>Jumlah: 1</p>

        <div class="price">Rp50.000</div>

        <button id="pay-button">
            Bayar Sekarang
        </button>

        <p id="status">Belum melakukan pembayaran.</p>
    </div>

    <script>
        const button = document.getElementById('pay-button');
        const statusText = document.getElementById('status');

        let orderId = null;

        async function cekStatus() {
            if (!orderId) return;

            const url = "{{ url('/midtrans/status') }}/"
                + encodeURIComponent(orderId);

            try {
                const response = await fetch(url, {
                    headers: {
                        'Accept': 'application/json'
                    }
                });

                if (!response.ok) {
                    throw new Error('Gagal membaca status');
                }

                const data = await response.json();

                statusText.textContent =
                    'Status pembayaran: ' + data.status;
            } catch (error) {
                statusText.textContent =
                    'Status belum dapat diperiksa.';
            }
        }

        button.addEventListener('click', async () => {
            button.disabled = true;
            statusText.textContent = 'Membuat transaksi...';

            try {
                const response = await fetch(
                    "{{ route('midtrans.checkout') }}",
                    {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN':
                                document.querySelector(
                                    'meta[name="csrf-token"]'
                                ).content
                        }
                    }
                );

                const data = await response.json();

                if (!response.ok || !data.success) {
                    throw new Error(
                        data.message || 'Checkout gagal'
                    );
                }

                orderId = data.order_id;
                statusText.textContent =
                    'Order dibuat: ' + orderId;

                window.snap.pay(data.snap_token, {
                    onSuccess: function () {
                        statusText.textContent =
                            'Menunggu konfirmasi pembayaran...';
                        cekStatus();
                    },
                    onPending: function () {
                        statusText.textContent =
                            'Pembayaran menunggu penyelesaian.';
                        cekStatus();
                    },
                    onError: function () {
                        statusText.textContent =
                            'Proses pembayaran mengalami kesalahan.';
                        cekStatus();
                    },
                    onClose: function () {
                        cekStatus();
                    }
                });

            } catch (error) {
                statusText.textContent = error.message;
            } finally {
                button.disabled = false;
            }
        });
    </script>
</body>
</html>
