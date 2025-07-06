<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RemainingTickets implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $remaining_tickets;

    /**
     * Create a new event instance.
     */
    public function __construct($remaining_tickets)
    {
        return $this->remaining_tickets=$remaining_tickets;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('tickets-remaining'),
        ];
    }

    public function broadcastAs()
    {
        return 'tickets-remaining-count';
    }
}
