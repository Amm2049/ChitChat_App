<?php

namespace App\Listeners;

use App\Events\SendMessageEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendMessageListener implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(SendMessageEvent $event)
    {
        broadcast(new SendMessageEvent($event->message))->toOthers();
    }
}
