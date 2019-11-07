<?php


namespace InstagramAPI\Listeners;

use InstagramAPI\Events\InstagramEvent;

interface EventListener
{
    public function handle(InstagramEvent $event);
}