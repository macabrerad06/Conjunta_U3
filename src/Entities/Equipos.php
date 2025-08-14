<?php

declare(strict_types=1);

namespace App\Entities;

class Equipos{
    private int $idEquipo;
    private string $nombre;
    private string $hackathon;
    private array $participantes;

    public function __construct(int $idEquipo, string $nombre, string $hackathon, array $participantes)
    {
        $this->idEquipo = $idEquipo;
        $this->nombre = $nombre;
        $this->hackathon = $hackathon;
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

    public function getHackathon(): string
    {
        return $this->hackathon;
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
    public function setHackathon(string $hackathon): void
    {
        $this->hackathon = $hackathon;
    }
}