<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VolunteerGroupHomesUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $updatedVolunteerGroupHomesData;

    /**
     * Create a new event instance.
     */
    public function __construct($updatedVolunteerGroupHomesData)
    {
        return $this->updatedVolunteerGroupHomesData=$updatedVolunteerGroupHomesData;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('updated-volunteergrouphomes'),
        ];
    }

    public function broadcastAs()
    {
        return 'updated-volunteergrouphomes-data';
    }
}
