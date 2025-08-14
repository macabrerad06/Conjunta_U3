<?php

declare(strict_types=1);

namespace App\Repositories;

use App\interfaces\RepositoryInterface;
use App\config\Database;
use App\entities\Estudiante; 
use PDO;

class EstudianteRepository implements RepositoryInterface
{
    private PDO $connection;

    public function __construct()
    {
        $this->connection = Database::getConnection();
    }

    public function create(object $entity): bool
    {
        if (!$entity instanceof Estudiante) {
            throw new \InvalidArgumentException('Entity must be an instance of Estudiante');
        }

        $sql = "INSERT INTO estudiante (participanteId, tipo, nombre, email, nivelHabilidad, grado, instituto, tiempoDisponibleSemanal)
        VALUES (:participanteId, :tipo, :nombre, :email, :nivelHabilidad, :grado, :instituto, :tiempoDisponibleSemanal)";
        $stmt = $this->connection->prepare($sql);

        return $stmt->execute([
            ':participanteId' => $entity->getParticipanteId(),
            ':tipo' => $entity->getTipo(),
            ':nombre' => $entity->getNombre(),
            ':email' => $entity->getEmail(),
            ':nivelHabilidad' => $entity->getNivelHabilidad(),
            ':grado' => $entity->getGrado(),
            ':instituto' => $entity->getInstituto(),
            ':tiempoDisponibleSemanal' => $entity->getTiempoDisponibleSemanal()
        ]);
    }

    public function findById(int $id): ?object
    {
        $sql = "SELECT * FROM estudiante WHERE participanteId = :participanteId";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam(':participanteId', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return $this->hydrate($row);
    }

    public function update(object $entity): bool
    {
        if (!$entity instanceof Estudiante) {
            throw new \InvalidArgumentException('Entity must be an instance of Estudiante');
        }

        $sql = "UPDATE estudiante SET
            tipo = :tipo,
            nombre = :nombre,
            email = :email,
            nivelHabilidad = :nivelHabilidad,
            grado = :grado,
            instituto = :instituto,
            tiempoDisponibleSemanal = :tiempoDisponibleSemanal
            WHERE participanteId = :participanteId";
        $stmt = $this->connection->prepare($sql);

        return $stmt->execute([
            ':participanteId' => $entity->getParticipanteId(),
            ':tipo' => $entity->getTipo(),
            ':nombre' => $entity->getNombre(),
            ':email' => $entity->getEmail(),
            ':nivelHabilidad' => $entity->getNivelHabilidad(),
            ':grado' => $entity->getGrado(),
            ':instituto' => $entity->getInstituto(),
            ':tiempoDisponibleSemanal' => $entity->getTiempoDisponibleSemanal()
        ]);
    }

    public function delete(int $id): bool
    {
        $sql = "DELETE FROM estudiante WHERE participanteId = :participanteId";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam(':participanteId', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function findAll(): array
    {
        $sql = "SELECT * FROM estudiante";
        $stmt = $this->connection->query($sql);
        $estudiantes = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $estudiantes[] = $this->hydrate($row);
        }
        return $estudiantes;
    }

    private function hydrate(array $row): Estudiante
    {
        $estudiante = new Estudiante(
            $row['participanteId'],
            $row['tipo'],
            $row['nombre'],
            $row['email'],
            $row['nivelHabilidad'],
            $row['grado'],
            $row['instituto'],
            $row['tiempoDisponibleSemanal']
        );
        return $estudiante;
    }
}
