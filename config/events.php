<?php

use App\Domain\Contracts\EventDispatcherInterface;
use App\Domain\Events\EmployeeCreated;
use App\Domain\Events\EmployeeDeleted;
use App\Domain\Events\EmployeeUpdated;
use App\Domain\Events\PositionCreated;
use App\Domain\Events\PositionDeleted;
use App\Domain\Events\PositionUpdated;
use App\Domain\Events\PasswordChanged;
use App\Domain\Events\ProfileUpdated;
use App\Domain\Events\UserCreated;
use App\Domain\Events\UserDeleted;
use App\Domain\Events\UserUpdated;
use App\Listeners\AuditListener;
use Core\Container;

return static function (Container $container, EventDispatcherInterface $dispatcher): void {
    $listener = $container->resolve(AuditListener::class);

    $dispatcher->listen(EmployeeCreated::class, [$listener, 'onEmployeeCreated']);
    $dispatcher->listen(EmployeeUpdated::class, [$listener, 'onEmployeeUpdated']);
    $dispatcher->listen(EmployeeDeleted::class, [$listener, 'onEmployeeDeleted']);

    $dispatcher->listen(PositionCreated::class, [$listener, 'onPositionCreated']);
    $dispatcher->listen(PositionUpdated::class, [$listener, 'onPositionUpdated']);
    $dispatcher->listen(PositionDeleted::class, [$listener, 'onPositionDeleted']);

    $dispatcher->listen(UserCreated::class, [$listener, 'onUserCreated']);
    $dispatcher->listen(UserUpdated::class, [$listener, 'onUserUpdated']);
    $dispatcher->listen(UserDeleted::class, [$listener, 'onUserDeleted']);

    $dispatcher->listen(ProfileUpdated::class, [$listener, 'onProfileUpdated']);
    $dispatcher->listen(PasswordChanged::class, [$listener, 'onPasswordChanged']);
};
