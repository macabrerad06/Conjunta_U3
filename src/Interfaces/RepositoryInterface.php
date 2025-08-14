<?php

declare(strict_types=1);

namespace App\Interfaces;

interface RepositoryInterface
{
    public function create(object $entity): bool;
    public function findById(int $id): ?object;

    public function update(object $entity): bool;
    public function delete(int $id): bool;

    public function findAll(): array;
}
