<?php

namespace App\Domain\Contracts;

interface LoginAttemptRepositoryInterface
{
    public function recordFailure(string $username, string $ip): void;

    public function countByUsername(string $username, int $windowSeconds): int;

    public function countByIp(string $ip, int $windowSeconds): int;

    public function clearForUsername(string $username): void;

    public function purgeOlderThan(int $windowSeconds): void;
}
