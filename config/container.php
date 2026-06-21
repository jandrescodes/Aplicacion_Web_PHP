<?php

use App\Domain\Contracts\AuditRepositoryInterface;
use App\Domain\Contracts\EmployeeRepositoryInterface;
use App\Domain\Contracts\EventDispatcherInterface;
use App\Domain\Contracts\PositionRepositoryInterface;
use App\Domain\Contracts\UserRepositoryInterface;
use App\Repositories\AuditRepository;
use App\Repositories\EmployeeRepository;
use App\Repositories\PositionRepository;
use App\Repositories\UserRepository;
use Config\Database;
use Core\Container;
use Core\EventDispatcher;

return static function (Container $container): void {
    $container->singleton(PDO::class, fn() => Database::getConnection());
    $container->singleton(EventDispatcherInterface::class, EventDispatcher::class);

    $container->bind(AuditRepositoryInterface::class, AuditRepository::class);
    $container->bind(EmployeeRepositoryInterface::class, EmployeeRepository::class);
    $container->bind(PositionRepositoryInterface::class, PositionRepository::class);
    $container->bind(UserRepositoryInterface::class, UserRepository::class);
};
