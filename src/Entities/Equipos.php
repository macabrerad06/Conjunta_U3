<?php

declare(strict_types=1);

namespace App\Entities;

class Equipos{
    private int $idEquipo;
    private string $nombre;
    private array $participantes;

    public function __construct(int $idEquipo, string $nombre, array $participantes)
    {
        $this->idEquipo = $idEquipo;
        $this->nombre = $nombre;
        $this->participantes = $participantes;
    }

    // Getters
    public function getIdEquipo(): int
    {
        return $this->idEquipo;
    }

    public function getNombre(): string
    {
        return $this->nombre;
    }

    public function getParticipantes(): array
    {
        return $this->participantes;
    }

    // Setters
    public function setIdEquipo(int $idEquipo): void
    {
        $this->idEquipo = $idEquipo;
    }

    public function setNombre(string $nombre): void
    {
        $this->nombre = $nombre;
    }

    public function setParticipantes(array $participantes): void
    {
        $this->participantes = $participantes;
    }
}