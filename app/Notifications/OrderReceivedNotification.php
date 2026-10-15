<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

/** To the buyer: their order and how to pay. Sent to the email they typed at checkout. */
class OrderReceivedNotification extends Notification
{
    public function __construct(public readonly Order $order) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $o = $this->order;
        $store = $o->store;
        $url = URL::signedRoute('checkout.confirmation', [$store->username, $o->number]);

        return (new MailMessage)
            ->subject('Your order '.$o->number.' at '.$store->display_name)
            ->greeting('Thank you for your order')
            ->line($store->display_name.' has received your order '.$o->number.' ('.$o->formattedTotal().').')
            ->line('Payment instructions and your order details are on your order page. Keep your order number when you pay.')
            ->action('View your order', $url)
            ->line('Questions about your order? Reply to the seller using the contact details on their Kustore page.');
    }
}
