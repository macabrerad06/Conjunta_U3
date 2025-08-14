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
        // Implementation for creating an Estudiante entity
    }

    public function findById(int $id): ?object
    {
        // Implementation for finding an Estudiante by ID
    }

    public function update(object $entity): bool
    {
        // Implementation for updating an Estudiante entity
    }

    public function delete(int $id): bool
    {
        // Implementation for deleting an Estudiante by ID
    }

    public function findAll(): array
    {
        // Implementation for finding all Estudiantes
    }

}