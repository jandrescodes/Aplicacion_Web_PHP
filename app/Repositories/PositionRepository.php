<?php

namespace App\Repositories;

use App\Domain\Contracts\PositionRepositoryInterface;
use App\Domain\Models\Position;
use PDO;

class PositionRepository implements PositionRepositoryInterface
{
    private $connection;

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

    /** @return array<Position> */
    public function listAll(): array
    {
        $statement = $this->connection->prepare(
            "SELECT ID, Nombredelpuesto
             FROM `tbl-puestos`
             ORDER BY ID DESC"
        );
        $statement->execute();
        return array_map(
            fn(array $row) => Position::fromRow($row),
            $statement->fetchAll(PDO::FETCH_ASSOC)
        );
    }

    public function findById(int $id): ?Position
    {
        $statement = $this->connection->prepare(
            "SELECT ID, Nombredelpuesto
             FROM `tbl-puestos`
             WHERE ID = :ID
             LIMIT 1"
        );
        $statement->bindParam(':ID', $id, PDO::PARAM_INT);
        $statement->execute();
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        return $row === false ? null : Position::fromRow($row);
    }

    public function create(string $name): bool
    {
        $statement = $this->connection->prepare(
            "INSERT INTO `tbl-puestos` (ID, Nombredelpuesto)
             VALUES (NULL, :Nombredelpuesto)"
        );
        return $statement->execute([
            ':Nombredelpuesto' => $name
        ]);
    }

    public function update(int $id, string $name): bool
    {
        $statement = $this->connection->prepare(
            "UPDATE `tbl-puestos`
             SET Nombredelpuesto = :Nombredelpuesto
             WHERE ID = :ID"
        );
        return $statement->execute([
            ':Nombredelpuesto' => $name,
            ':ID' => (int)$id
        ]);
    }

    public function deleteById(int $id): bool
    {
        $statement = $this->connection->prepare(
            "DELETE FROM `tbl-puestos` WHERE ID = :ID"
        );
        $statement->bindParam(':ID', $id, PDO::PARAM_INT);
        return $statement->execute();
    }

    public function countAll(): int
    {
        $statement = $this->connection->prepare(
            "SELECT COUNT(*) FROM `tbl-puestos`"
        );
        $statement->execute();
        return (int)$statement->fetchColumn();
    }
}
