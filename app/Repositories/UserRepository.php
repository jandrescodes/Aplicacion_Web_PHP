<?php

namespace App\Repositories;

use App\Domain\Contracts\UserRepositoryInterface;
use App\Domain\Models\User;
use PDO;

class UserRepository implements UserRepositoryInterface
{
    private PDO $connection;

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

    public function findByUsername(string $username): ?User
    {
        $statement = $this->connection->prepare(
            "SELECT ID, Nombreusuario, Password, Correo, is_admin
             FROM `tbl-usuarios`
             WHERE Nombreusuario = :Nombreusuario
              LIMIT 1"
        );

        $statement->bindParam(':Nombreusuario', $username);
        $statement->execute();

        $user = $statement->fetch(PDO::FETCH_ASSOC);
        return $user === false ? null : User::fromRow($user);
    }

    /** @return array<User> */
    public function listAll(): array
    {
        $statement = $this->connection->prepare(
            "SELECT ID, Nombreusuario, Correo, is_admin
             FROM `tbl-usuarios`
             ORDER BY ID DESC"
        );
        $statement->execute();
        return array_map(
            fn(array $row) => User::fromRow($row),
            $statement->fetchAll(PDO::FETCH_ASSOC)
        );
    }

    public function findById(int $id): ?User
    {
        $statement = $this->connection->prepare(
            "SELECT ID, Nombreusuario, Password, Correo, is_admin
             FROM `tbl-usuarios`
             WHERE ID = :ID
             LIMIT 1"
        );
        $statement->bindParam(':ID', $id, PDO::PARAM_INT);
        $statement->execute();
        $user = $statement->fetch(PDO::FETCH_ASSOC);
        return $user === false ? null : User::fromRow($user);
    }

    public function create(array $data): bool
    {
        $statement = $this->connection->prepare(
            "INSERT INTO `tbl-usuarios` (ID, Nombreusuario, Password, Correo)
             VALUES (NULL, :Nombreusuario, :Password, :Correo)"
        );
        return $statement->execute([
            ':Nombreusuario' => $data['Nombreusuario'],
            ':Password' => $data['Password'],
            ':Correo' => $data['Correo']
        ]);
    }

    public function update(int $id, array $data): bool
    {
        $statement = $this->connection->prepare(
            "UPDATE `tbl-usuarios`
             SET Nombreusuario = :Nombreusuario,
                 Password = :Password,
                 Correo = :Correo
             WHERE ID = :ID"
        );
        return $statement->execute([
            ':Nombreusuario' => $data['Nombreusuario'],
            ':Password' => $data['Password'],
            ':Correo' => $data['Correo'],
            ':ID' => (int)$id
        ]);
    }

    public function deleteById(int $id): bool
    {
        $statement = $this->connection->prepare(
            "DELETE FROM `tbl-usuarios` WHERE ID = :ID"
        );
        $statement->bindParam(':ID', $id, PDO::PARAM_INT);
        return $statement->execute();
    }

    public function updatePasswordHash(int $id, string $passwordHash): bool
    {
        $statement = $this->connection->prepare(
            "UPDATE `tbl-usuarios`
             SET Password = :Password
             WHERE ID = :ID"
        );
        return $statement->execute([
            ':Password' => $passwordHash,
            ':ID' => (int)$id
        ]);
    }

    public function findByIdWithRememberToken(int $id): ?User
    {
        $statement = $this->connection->prepare(
            "SELECT ID, Nombreusuario, remember_token, remember_token_expires
             FROM `tbl-usuarios`
             WHERE ID = :ID
             LIMIT 1"
        );
        $statement->bindParam(':ID', $id, PDO::PARAM_INT);
        $statement->execute();
        $user = $statement->fetch(PDO::FETCH_ASSOC);
        return $user === false ? null : User::fromRow($user);
    }

    public function setRememberToken(int $id, string $tokenHash, string $expiresAt): bool
    {
        $statement = $this->connection->prepare(
            "UPDATE `tbl-usuarios`
             SET remember_token = :hash, remember_token_expires = :expires
             WHERE ID = :ID"
        );
        return $statement->execute([
            ':hash'    => $tokenHash,
            ':expires' => $expiresAt,
            ':ID'      => $id,
        ]);
    }

    public function clearRememberToken(int $id): bool
    {
        $statement = $this->connection->prepare(
            "UPDATE `tbl-usuarios`
             SET remember_token = NULL, remember_token_expires = NULL
             WHERE ID = :ID"
        );
        $statement->bindParam(':ID', $id, PDO::PARAM_INT);
        return $statement->execute();
    }

    public function emailExists(string $email, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM `tbl-usuarios` WHERE Correo = :Correo";
        if ($excludeId !== null) {
            $sql .= " AND ID != :ExcludeId";
        }
        $statement = $this->connection->prepare($sql);
        $statement->bindParam(':Correo', $email);
        if ($excludeId !== null) {
            $statement->bindParam(':ExcludeId', $excludeId, PDO::PARAM_INT);
        }
        $statement->execute();
        return (int)$statement->fetchColumn() > 0;
    }

    public function usernameExistsExcluding(string $username, int $excludeId): bool
    {
        $statement = $this->connection->prepare(
            "SELECT COUNT(*) FROM `tbl-usuarios`
             WHERE Nombreusuario = :Nombreusuario AND ID != :ExcludeId"
        );
        $statement->bindParam(':Nombreusuario', $username);
        $statement->bindParam(':ExcludeId', $excludeId, PDO::PARAM_INT);
        $statement->execute();
        return (int)$statement->fetchColumn() > 0;
    }

    public function countAll(): int
    {
        $statement = $this->connection->prepare(
            "SELECT COUNT(*) FROM `tbl-usuarios`"
        );
        $statement->execute();
        return (int)$statement->fetchColumn();
    }
}
