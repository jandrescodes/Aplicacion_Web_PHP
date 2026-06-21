<?php

namespace App\Domain\Contracts;

interface EventDispatcherInterface
{
    public function listen(string $eventClass, callable $listener): void;

    public function dispatch(object $event): void;
}
