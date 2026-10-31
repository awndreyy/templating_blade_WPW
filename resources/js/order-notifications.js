const notificationContainer = document.querySelector('[data-order-broadcast-channel]');

if (notificationContainer && window.Echo) {
    const channel = notificationContainer.dataset.orderBroadcastChannel;

    window.Echo.private(channel).listen('.order.placed', (event) => {
        console.info('Order Placed:', event);

        const alert = document.createElement('div');
        alert.className = 'alert alert-success alert-dismissible shadow-sm';
        alert.setAttribute('role', 'status');

        const message = document.createElement('span');
        const total = new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            maximumFractionDigits: 0,
        }).format(Number(event.total));

        message.textContent = `Transaksi ${event.invoice_number} oleh ${event.cashier_name} berhasil. Total: ${total}.`;

        const closeButton = document.createElement('button');
        closeButton.type = 'button';
        closeButton.className = 'close';
        closeButton.setAttribute('aria-label', 'Tutup notifikasi');
        closeButton.textContent = '×';
        closeButton.addEventListener('click', () => alert.remove());

        alert.append(message, closeButton);
        notificationContainer.prepend(alert);

        setTimeout(() => alert.remove(), 8000);
    });
}
