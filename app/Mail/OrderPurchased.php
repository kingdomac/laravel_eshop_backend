<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderPurchased extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $order;
    public $url;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Order $order, string $url)
    {
        $this->order = $order;
        $this->url = $url;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('mail.order-purchased');
    }
}
