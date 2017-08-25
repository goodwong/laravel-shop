<?php

namespace Goodwong\LaravelShop\Events;

use Goodwong\LaravelShop\Entities\Order;
use Goodwong\LaravelShop\Entities\OrderPayment;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class OrderPaid
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * order
     * 
     * @var  Order
     */
    public $order = null;

    /**
     * payment
     * 
     * @var  OrderPayment
     */
    public $payment = null;

    /**
     * Create a new event instance.
     *
     * @param  Order  $order
     * @param  OrderPayment  $payment
     * @return void
     */
    public function __construct(Order $order, OrderPayment $payment)
    {
        $this->order = $order;
        $this->payment = $payment;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
