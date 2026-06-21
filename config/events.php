<?php

use App\Domain\Contracts\EventDispatcherInterface;
use App\Domain\Events\EmployeeCreated;
use App\Domain\Events\EmployeeDeleted;
use App\Domain\Events\EmployeeUpdated;
use App\Listeners\AuditListener;
use Core\Container;

return static function (Container $container, EventDispatcherInterface $dispatcher): void {
    $listener = $container->resolve(AuditListener::class);

    $dispatcher->listen(EmployeeCreated::class, [$listener, 'onEmployeeCreated']);
    $dispatcher->listen(EmployeeUpdated::class, [$listener, 'onEmployeeUpdated']);
    $dispatcher->listen(EmployeeDeleted::class, [$listener, 'onEmployeeDeleted']);
};
