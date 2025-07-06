<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UncheckedInTickets implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $notCheckedInTicketsData;

    /**
     * Create a new event instance.
     */
    public function __construct($notCheckedInTicketsData)
    {
        return $this->notCheckedInTicketsData=$notCheckedInTicketsData;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('uncheckedin-tickets'),
        ];
    }

    public function broadcastAs()
    {
        return 'uncheckedin-tickets-data';
    }
}
