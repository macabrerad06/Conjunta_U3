<?php

declare(strict_types=1);

namespace App\Repositories;

use App\interfaces\RepositoryInterface;
use App\config\Database;
use App\entities\RetoExperimental;
use PDO;

class RetoExperimentalRepository implements RepositoryInterface
{
    private PDO $connection;

    public function __construct()
    {
        $this->connection = Database::getConnection();
    }

    public function create(object $entity): bool
    {
        if (!$entity instanceof RetoExperimental) {
            throw new \InvalidArgumentException('Entity must be an instance of RetoExperimental');
        }

        $sql = "INSERT INTO retoExperimental (retoId, tipo, titulo , descripcion, dificultad, areasConocimiento, enfoquePedagogico)
        VALUES (:retoId, :tipo, :titulo , :descripcion, :dificultad, :areasConocimiento, :enfoquePedagogico)";
        $stmt = $this->connection->prepare($sql);

        return $stmt->execute([
            ':retoId' => $entity->getRetoId(),
            ':tipo' => $entity->getTipo(),
            ':titulo' => $entity->getTitulo(),
            ':descripcion' => $entity->getDescripcion(),
            ':dificultad' => $entity->getDificultad(),
            ':areasConocimiento' => $entity->getAreasConocimiento(),
            ':enfoquePedagogico' => $entity->getEnfoquePedagogico()
        ]);
    }

    public function findById(int $id): ?object
    {
        $sql = "SELECT * FROM retoExperimental WHERE retoId = :retoId";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam(':retoId', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return $this->hydrate($row);
    }

    public function update(object $entity): bool
    {
        if (!$entity instanceof RetoExperimental) {
            throw new \InvalidArgumentException('Entity must be an instance of RetoExperimental');
        }

        $sql = "UPDATE retoExperimental SET
            tipo = :tipo,
            titulo = :titulo,
            descripcion = :descripcion,
            dificultad = :dificultad,
            areasConocimiento = :areasConocimiento,
            enfoquePedagogico = :enfoquePedagogico
            WHERE retoId = :retoId";
        $stmt = $this->connection->prepare($sql);

        return $stmt->execute([
            ':retoId' => $entity->getRetoId(),
            ':tipo' => $entity->getTipo(),
            ':titulo' => $entity->getTitulo(),
            ':descripcion' => $entity->getDescripcion(),
            ':dificultad' => $entity->getDificultad(),
            ':areasConocimiento' => $entity->getAreasConocimiento(),
            ':enfoquePedagogico' => $entity->getEnfoquePedagogico()
        ]);
    }

    public function delete(int $id): bool
    {
        $sql = "DELETE FROM retoExperimental WHERE retoId = :retoId";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam(':retoId', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function findAll(): array
    {
        $sql = "SELECT * FROM retoExperimental";
        $stmt = $this->connection->query($sql);
        $retos = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $retos[] = $this->hydrate($row);
        }
        return $retos;
    }

    private function hydrate(array $row): RetoExperimental
    {
        $retoExperimental = new RetoExperimental(
            $row['retoId'],
            $row['tipo'],
            $row['titulo'],
            $row['descripcion'],
            $row['dificultad'],
            $row['areasConocimiento'],
            $row['enfoquePedagogico']
        );
        return $retoExperimental;
    }
}