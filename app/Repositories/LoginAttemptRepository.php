<?php

namespace App\Repositories;

use App\Domain\Contracts\LoginAttemptRepositoryInterface;
use PDO;

class LoginAttemptRepository implements LoginAttemptRepositoryInterface
{
    private PDO $connection;

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

    public function recordFailure(string $username, string $ip): void
    {
        $statement = $this->connection->prepare(
            "INSERT INTO `login_attempts` (username, ip) VALUES (:username, :ip)"
        );
        $statement->execute([':username' => $username, ':ip' => $ip]);
    }

    public function countByUsername(string $username, int $windowSeconds): int
    {
        $statement = $this->connection->prepare(
            "SELECT COUNT(*) FROM `login_attempts`
             WHERE username = :username
               AND attempted_at >= (NOW() - INTERVAL :window SECOND)"
        );
        $statement->bindValue(':username', $username, PDO::PARAM_STR);
        $statement->bindValue(':window', $windowSeconds, PDO::PARAM_INT);
        $statement->execute();
        return (int)$statement->fetchColumn();
    }

    public function countByIp(string $ip, int $windowSeconds): int
    {
        $statement = $this->connection->prepare(
            "SELECT COUNT(*) FROM `login_attempts`
             WHERE ip = :ip
               AND attempted_at >= (NOW() - INTERVAL :window SECOND)"
        );
        $statement->bindValue(':ip', $ip, PDO::PARAM_STR);
        $statement->bindValue(':window', $windowSeconds, PDO::PARAM_INT);
        $statement->execute();
        return (int)$statement->fetchColumn();
    }

    public function clearForUsername(string $username): void
    {
        $statement = $this->connection->prepare(
            "DELETE FROM `login_attempts` WHERE username = :username"
        );
        $statement->execute([':username' => $username]);
    }

    public function purgeOlderThan(int $windowSeconds): void
    {
        $statement = $this->connection->prepare(
            "DELETE FROM `login_attempts` WHERE attempted_at < (NOW() - INTERVAL :window SECOND)"
        );
        $statement->bindValue(':window', $windowSeconds, PDO::PARAM_INT);
        $statement->execute();
    }
}
