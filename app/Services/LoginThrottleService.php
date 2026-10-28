<?php

namespace App\Services;

use App\Domain\Contracts\LoginAttemptRepositoryInterface;

class LoginThrottleService
{
    public const WINDOW_SECONDS = 900;
    public const MAX_PER_USERNAME = 5;
    public const MAX_PER_IP = 20;

    private LoginAttemptRepositoryInterface $attempts;

    public function __construct(LoginAttemptRepositoryInterface $attempts)
    {
        $this->attempts = $attempts;
    }

    public function isLocked(string $username, string $ip): bool
    {
        $username = $this->normalize($username);

        return $this->attempts->countByUsername($username, self::WINDOW_SECONDS) >= self::MAX_PER_USERNAME
            || $this->attempts->countByIp($ip, self::WINDOW_SECONDS) >= self::MAX_PER_IP;
    }

    public function recordFailure(string $username, string $ip): void
    {
        $this->attempts->purgeOlderThan(self::WINDOW_SECONDS);
        $this->attempts->recordFailure($this->normalize($username), $ip);
    }

    public function reset(string $username): void
    {
        $this->attempts->clearForUsername($this->normalize($username));
    }

    // Se limita por el texto tecleado, exista o no el usuario, para no revelar cuentas válidas.
    private function normalize(string $username): string
    {
        return mb_substr(mb_strtolower(trim($username)), 0, 255);
    }
}
