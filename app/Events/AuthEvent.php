<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AuthEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public const ACTION_LOGIN = "Logged In";
    public const ACTION_REGISTER = "Registered";
    public const ACTION_LOGOUT = "Logged Out";
    public const ACTION_TOKEN_REFRESHED = "Access Token Refreshed";
    public const ACTION_SOCIAL_LOGIN = "Logged In with Social Account";
    /**
     * @var User $user
     */
    public $user;

    /**
     * @var string $action
     */
    public $action;
    /**
     * @var array
     */
    public $custom_data;

    /**
     * Create a new event instance.
     *
     * @param array $custom_data
     * @return void
     */
    public function __construct($user, $action, array $custom_data)
    {
        //
        $this->user = $user;
        $this->action = $action;
        $this->custom_data = $custom_data;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
