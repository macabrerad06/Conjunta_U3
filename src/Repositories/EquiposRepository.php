<?php

declare(strict_types=1);

namespace App\Repositories;

use App\interfaces\RepositoryInterface;
use App\config\Database;
use App\entities\Equipos; 
use PDO;

class EquiposRepository implements RepositoryInterface
{
    private PDO $connection;

    public function __construct()
    {
        $this->connection = Database::getConnection();
    }

    public function create(object $entity): bool
    {
        if (!$entity instanceof Equipos) {
            throw new \InvalidArgumentException('Entity must be an instance of Equipos');
        }
        $participantesJson = json_encode($entity->getParticipantes());
        if ($participantesJson === false) {
            error_log("Error al serializar participantes para el equipo: " . $entity->getNombre());
            return false;
        }

        $sql = "INSERT INTO equipos (idEquipo, nombre, hackathon, participantes)
                VALUES (:idEquipo, :nombre, :hackathon, :participantes)";
        $stmt = $this->connection->prepare($sql);

        return $stmt->execute([
            ':idEquipo' => $entity->getIdEquipo(),
            ':nombre' => $entity->getNombre(),
            ':hackathon' => $entity->getHackathon(),
            ':participantes' => $participantesJson 
        ]);
    }

    public function findById(int $id): ?object
    {
        $sql = "SELECT * FROM equipos WHERE idEquipo = :idEquipo";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam(':idEquipo', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return $this->hydrate($row);
    }

    public function update(object $entity): bool
    {
        if (!$entity instanceof Equipos) {
            throw new \InvalidArgumentException('Entity must be an instance of Equipos');
        }

        $participantesJson = json_encode($entity->getParticipantes());
        if ($participantesJson === false) {
            error_log("Error al serializar participantes para el equipo: " . $entity->getNombre());
            return false;
        }

        $sql = "UPDATE equipos SET
            nombre = :nombre,
            hackathon = :hackathon,
            participantes = :participantes
            WHERE idEquipo = :idEquipo";
        $stmt = $this->connection->prepare($sql);

        return $stmt->execute([
            ':idEquipo' => $entity->getIdEquipo(),
            ':nombre' => $entity->getNombre(),
            ':hackathon' => $entity->getHackathon(),
            ':participantes' => $participantesJson 
        ]);
    }

    public function delete(int $id): bool
    {
        $sql = "DELETE FROM equipos WHERE idEquipo = :idEquipo";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam(':idEquipo', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function findAll(): array
    {
        $sql = "SELECT * FROM equipos";
        $stmt = $this->connection->query($sql);
        $equipos = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $equipos[] = $this->hydrate($row);
        }
        return $equipos;
    }

    private function hydrate(array $row): Equipos
    {
        $participantes = json_decode($row['participantes'], true);
        if ($participantes === null && json_last_error() !== JSON_ERROR_NONE) {
            error_log("Error al deserializar participantes para el equipo ID: " . $row['idEquipo'] . " - " . json_last_error_msg());
            $participantes = []; 
        }
        
        $equipo = new Equipos(
            $row['idEquipo'],
            $row['nombre'],
            $row['hackathon'],
            $participantes 
        );
        return $equipo;
    }
}
