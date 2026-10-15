<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** To the seller: a buyer placed an order. */
class NewOrderNotification extends Notification
{
    public function __construct(public readonly Order $order) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $o = $this->order;
        $mail = (new MailMessage)
            ->subject('New order '.$o->number.' · '.$o->formattedTotal())
            ->greeting('You have a new order')
            ->line($o->customer_name.' ordered:');
        foreach ($o->items as $item) {
            $mail->line('• '.$item->product_name.' × '.$item->quantity.' ('.money($item->line_total, $o->currency).')');
        }

        return $mail
            ->line('Total: '.$o->formattedTotal().'. Payment status: waiting for payment.')
            ->action('Open the order', route('dashboard.orders.show', $o))
            ->line('Mark it as paid once the money arrives.');
    }
}
