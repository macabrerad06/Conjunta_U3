<?php

declare(strict_types=1);

namespace App\Repositories;

use App\interfaces\RepositoryInterface;
use App\config\Database;
use App\entities\MentorTecnico; 
use PDO;

class MentorTecnicoRepository implements RepositoryInterface
{
    private PDO $connection;

    public function __construct()
    {
        $this->connection = Database::getConnection();
    }

    public function create(object $entity): bool
    {
        if (!$entity instanceof MentorTecnico) {
            throw new \InvalidArgumentException('Entity must be an instance of MentorTecnico');
        }

        $sql = "INSERT INTO mentorTecnico (participanteId, tipo, nombre, email, nivelHabilidad, especialidad, experiencia, disponibilidadHoraria)
        VALUES (:participanteId, :tipo, :nombre, :email, :nivelHabilidad, :especialidad, :experiencia, :disponibilidadHoraria)";
        $stmt = $this->connection->prepare($sql);

        return $stmt->execute([
            ':participanteId' => $entity->getParticipanteId(),
            ':tipo' => $entity->getTipo(),
            ':nombre' => $entity->getNombre(),
            ':email' => $entity->getEmail(),
            ':nivelHabilidad' => $entity->getNivelHabilidad(),
            ':especialidad' => $entity->getEspecialidad(),
            ':experiencia' => $entity->getExperiencia(),
            ':disponibilidadHoraria' => $entity->getDisponibilidadHoraria()
        ]);
    }

    public function findById(int $id): ?object
    {
        $sql = "SELECT * FROM mentorTecnico WHERE participanteId = :participanteId";
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
        if (!$entity instanceof MentorTecnico) {
            throw new \InvalidArgumentException('Entity must be an instance of MentorTecnico');
        }

        $sql = "UPDATE mentorTecnico SET
            tipo = :tipo,
            nombre = :nombre,
            email = :email,
            nivelHabilidad = :nivelHabilidad,
            especialidad = :especialidad,
            experiencia = :experiencia,
            disponibilidadHoraria = :disponibilidadHoraria
            WHERE participanteId = :participanteId";
        $stmt = $this->connection->prepare($sql);

        return $stmt->execute([
            ':participanteId' => $entity->getParticipanteId(),
            ':tipo' => $entity->getTipo(),
            ':nombre' => $entity->getNombre(),
            ':email' => $entity->getEmail(),
            ':nivelHabilidad' => $entity->getNivelHabilidad(),
            ':especialidad' => $entity->getEspecialidad(),
            ':experiencia' => $entity->getExperiencia(),
            ':disponibilidadHoraria' => $entity->getDisponibilidadHoraria()
        ]);
    }

    public function delete(int $id): bool
    {
        $sql = "DELETE FROM mentorTecnico WHERE participanteId = :participanteId";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam(':participanteId', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function findAll(): array
    {
        $sql = "SELECT * FROM mentorTecnico";
        $stmt = $this->connection->query($sql);
        $mentores = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $mentores[] = $this->hydrate($row);
        }
        return $mentores;
    }
    
    private function hydrate(array $row): MentorTecnico
    {
        $mentorTecnico = new MentorTecnico(
            $row['participanteId'],
            $row['tipo'],
            $row['nombre'],
            $row['email'],
            $row['nivelHabilidad'],
            $row['especialidad'],
            $row['experiencia'],
            $row['disponibilidadHoraria']
        );
        return $mentorTecnico;
    }
}
