<?php

namespace Core;

use App\Domain\Contracts\EventDispatcherInterface;
use Config\AppLogger;
use Throwable;

class EventDispatcher implements EventDispatcherInterface
{
    /** @var array<string, callable[]> */
    private array $listeners = [];

    public function listen(string $eventClass, callable $listener): void
    {
        $this->listeners[$eventClass][] = $listener;
    }

    public function dispatch(object $event): void
    {
        $eventClass = $event::class;

        foreach ($this->listeners[$eventClass] ?? [] as $listener) {
            try {
                $listener($event);
            } catch (Throwable $e) {
                AppLogger::getInstance()->warning(
                    'EventDispatcher: listener falló silenciosamente',
                    ['event' => $eventClass, 'error' => $e->getMessage()]
                );
            }
        }
    }
}
