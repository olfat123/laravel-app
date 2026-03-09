<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderStatusChangedNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order,
        public string $oldStatus,
        public string $newStatus,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('Order #:id Status Changed: :old → :new', [
                'id'  => $this->order->id,
                'old' => ucfirst($this->oldStatus),
                'new' => ucfirst($this->newStatus),
            ]),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.orders.admin_status_changed',
        );
    }
}
