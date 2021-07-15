<?php

namespace App\Listeners;

use App\Events\AuthEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class AuthListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  AuthEvent  $event
     * @return void
     */
    public function handle(AuthEvent $event): void
    {
        $user = $event->user;
        $action = $event->action;
        $custom = $event->custom_data;

        activity()
            ->inLog("Auth")
            ->withProperties($custom)
            ->by($user)->on($user)
            ->log($action);
    }
}
