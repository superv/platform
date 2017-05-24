<?php namespace SuperV\Platform\Contracts;

interface Dispatcher
{
    public function dispatch($event, $payload = []);

    public function listen($events, $listener);
}
