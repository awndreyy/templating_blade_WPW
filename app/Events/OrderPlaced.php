<?php

namespace App\Events;

use App\Models\Transaction;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderPlaced implements ShouldBroadcastNow, ShouldDispatchAfterCommit
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public readonly int $orderId;

    public readonly int $cashierId;

    public readonly string $invoiceNumber;

    public readonly string $cashierName;

    public readonly float $total;

    public function __construct(Transaction $order)
    {
        $this->orderId = $order->id;
        $this->cashierId = $order->user_id;
        $this->invoiceNumber = $order->invoice_number;
        $this->cashierName = $order->cashier_name;
        $this->total = (float) $order->total_amount;
    }

    /** @return array<int, PrivateChannel> */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.'.$this->cashierId),
            new PrivateChannel('orders.admin'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'order.placed';
    }

    /** @return array{order_id: int, invoice_number: string, cashier_name: string, total: float} */
    public function broadcastWith(): array
    {
        return [
            'order_id' => $this->orderId,
            'invoice_number' => $this->invoiceNumber,
            'cashier_name' => $this->cashierName,
            'total' => $this->total,
        ];
    }
}
