<?php

declare(strict_types=1);

namespace App\Repositories;

use App\interfaces\RepositoryInterface;
use App\config\Database;
use App\entities\RetoReal; 
use PDO;

class RetoRealRepository implements RepositoryInterface
{
    private PDO $connection;

    public function __construct()
    {
        $this->connection = Database::getConnection();
    }

    public function create(object $entity): bool
    {
        if (!$entity instanceof RetoReal) {
            throw new \InvalidArgumentException('Entity must be an instance of RetoReal');
        }

        $sql = "INSERT INTO retoReal (retoId, tipo, titulo , descripcion, dificultad, areasConocimiento, entidadColaboradora)
        VALUES (:retoId, :tipo, :titulo , :descripcion, :dificultad, :areasConocimiento, :entidadColaboradora)";
        $stmt = $this->connection->prepare($sql);

        return $stmt->execute([
            ':retoId' => $entity->getRetoId(),
            ':tipo' => $entity->getTipo(),
            ':titulo' => $entity->getTitulo(),
            ':descripcion' => $entity->getDescripcion(),
            ':dificultad' => $entity->getDificultad(),
            ':areasConocimiento' => $entity->getAreasConocimiento(),
            ':entidadColaboradora' => $entity->getEntidadColaboradora() 
        ]);
    }

    public function findById(int $id): ?object
    {
        $sql = "SELECT * FROM retoReal WHERE retoId = :retoId"; 
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
        if (!$entity instanceof RetoReal) {
            throw new \InvalidArgumentException('Entity must be an instance of RetoReal');
        }

        $sql = "UPDATE retoReal SET
            tipo = :tipo,
            titulo = :titulo,
            descripcion = :descripcion,
            dificultad = :dificultad,
            areasConocimiento = :areasConocimiento,
            entidadColaboradora = :entidadColaboradora
            WHERE retoId = :retoId";
        $stmt = $this->connection->prepare($sql);

        return $stmt->execute([
            ':retoId' => $entity->getRetoId(),
            ':tipo' => $entity->getTipo(),
            ':titulo' => $entity->getTitulo(),
            ':descripcion' => $entity->getDescripcion(),
            ':dificultad' => $entity->getDificultad(),
            ':areasConocimiento' => $entity->getAreasConocimiento(),
            ':entidadColaboradora' => $entity->getEntidadColaboradora() 
        ]);
    }

    public function delete(int $id): bool
    {
        $sql = "DELETE FROM retoReal WHERE retoId = :retoId";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam(':retoId', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function findAll(): array
    {
        $sql = "SELECT * FROM retoReal";
        $stmt = $this->connection->query($sql);
        $retos = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $retos[] = $this->hydrate($row);
        }
        return $retos;
    }

    private function hydrate(array $row): RetoReal
    {
        $retoReal = new RetoReal(
            $row['retoId'],
            $row['tipo'],
            $row['titulo'],
            $row['descripcion'],
            $row['dificultad'],
            $row['areasConocimiento'],
            $row['entidadColaboradora']
        );
        return $retoReal;
    }
}
