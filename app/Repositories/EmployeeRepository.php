<?php

namespace App\Repositories;

use App\Domain\Contracts\EmployeeRepositoryInterface;
use App\Domain\Models\Employee;
use App\Domain\Models\Position;
use PDO;

class EmployeeRepository implements EmployeeRepositoryInterface
{
    private PDO $connection;

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

    /** @return array<Employee> */
    public function listAllWithPosition(): array
    {
        $statement = $this->connection->prepare(
            "SELECT e.*, p.Nombredelpuesto as puesto
             FROM `tbl-empleados` e
             LEFT JOIN `tbl-puestos` p ON p.ID = e.Idpuesto
             ORDER BY e.ID DESC"
        );
        $statement->execute();
        return array_map(
            fn(array $row) => Employee::fromRow($row),
            $statement->fetchAll(PDO::FETCH_ASSOC)
        );
    }

    /** @return array<Position> */
    public function listPositions(): array
    {
        $statement = $this->connection->prepare(
            "SELECT ID, Nombredelpuesto
             FROM `tbl-puestos`
             ORDER BY Nombredelpuesto ASC"
        );
        $statement->execute();
        return array_map(
            fn(array $row) => Position::fromRow($row),
            $statement->fetchAll(PDO::FETCH_ASSOC)
        );
    }

    public function findById(int $id): ?Employee
    {
        $statement = $this->connection->prepare(
            "SELECT * FROM `tbl-empleados` WHERE ID = :ID LIMIT 1"
        );
        $statement->bindParam(':ID', $id, PDO::PARAM_INT);
        $statement->execute();
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        return $row === false ? null : Employee::fromRow($row);
    }

    public function findByIdWithPosition(int $id): ?Employee
    {
        $statement = $this->connection->prepare(
            "SELECT e.*, p.Nombredelpuesto as puesto
             FROM `tbl-empleados` e
             LEFT JOIN `tbl-puestos` p ON p.ID = e.Idpuesto
             WHERE e.ID = :ID
             LIMIT 1"
        );
        $statement->bindParam(':ID', $id, PDO::PARAM_INT);
        $statement->execute();
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        return $row === false ? null : Employee::fromRow($row);
    }

    public function findFilesById(int $id): ?array
    {
        $statement = $this->connection->prepare(
            "SELECT Foto, CV FROM `tbl-empleados` WHERE ID = :ID LIMIT 1"
        );
        $statement->bindParam(':ID', $id, PDO::PARAM_INT);
        $statement->execute();
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        return $row === false ? null : $row;
    }

    public function create(array $data): bool
    {
        $statement = $this->connection->prepare(
            "INSERT INTO `tbl-empleados` (
                ID, Primernombre, Segundonombre, Primerapellido, Segundoapellido, Foto, CV, Idpuesto, Fecha
            ) VALUES (
                NULL, :Primernombre, :Segundonombre, :Primerapellido, :Segundoapellido, :Foto, :CV, :Idpuesto, :Fecha
            )"
        );

        return $statement->execute([
            ':Primernombre' => $data['Primernombre'],
            ':Segundonombre' => $data['Segundonombre'],
            ':Primerapellido' => $data['Primerapellido'],
            ':Segundoapellido' => $data['Segundoapellido'],
            ':Foto' => $data['Foto'],
            ':CV' => $data['CV'],
            ':Idpuesto' => $data['Idpuesto'],
            ':Fecha' => $data['Fecha']
        ]);
    }

    public function update(int $id, array $data): bool
    {
        $statement = $this->connection->prepare(
            "UPDATE `tbl-empleados`
             SET Primernombre = :Primernombre,
                 Segundonombre = :Segundonombre,
                 Primerapellido = :Primerapellido,
                 Segundoapellido = :Segundoapellido,
                 Foto = :Foto,
                 CV = :CV,
                 Idpuesto = :Idpuesto,
                 Fecha = :Fecha
             WHERE ID = :ID"
        );

        return $statement->execute([
            ':Primernombre' => $data['Primernombre'],
            ':Segundonombre' => $data['Segundonombre'],
            ':Primerapellido' => $data['Primerapellido'],
            ':Segundoapellido' => $data['Segundoapellido'],
            ':Foto' => $data['Foto'],
            ':CV' => $data['CV'],
            ':Idpuesto' => $data['Idpuesto'],
            ':Fecha' => $data['Fecha'],
            ':ID' => (int)$id
        ]);
    }

    public function deleteById(int $id): bool
    {
        $statement = $this->connection->prepare(
            "DELETE FROM `tbl-empleados` WHERE ID = :ID"
        );
        $statement->bindParam(':ID', $id, PDO::PARAM_INT);
        return $statement->execute();
    }

    public function countAll(): int
    {
        $statement = $this->connection->prepare(
            "SELECT COUNT(*) FROM `tbl-empleados`"
        );
        $statement->execute();
        return (int)$statement->fetchColumn();
    }

    /** @return array<array{puesto: string, total: int}> */
    public function countByPosition(): array
    {
        $statement = $this->connection->prepare(
            "SELECT p.Nombredelpuesto AS puesto, COUNT(e.ID) AS total
             FROM `tbl-puestos` p
             LEFT JOIN `tbl-empleados` e ON e.Idpuesto = p.ID
             GROUP BY p.ID, p.Nombredelpuesto
             ORDER BY total DESC, p.Nombredelpuesto ASC"
        );
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
}
