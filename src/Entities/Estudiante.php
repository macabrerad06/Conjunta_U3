<?php

declare(strict_types=1);

namespace App\Entities;

class Estudiante extends Participante
{
    private int $grado;
    private string $institucion;
    private int $tiempoDisponibleSemanal;
    private array $habilidades;

    public function __construct(
        int $idParticipante,
        string $tipo,
        string $nombre,
        string $email,
        string $nivelHabilidad,
        int $grado,
        string $institucion,
        int $tiempoDisponibleSemanal,
        array $habilidades
    ) {
        parent::__construct($idParticipante, $tipo, $nombre, $email, $nivelHabilidad);
        $this->grado = $grado;
        $this->institucion = $institucion;
        $this->tiempoDisponibleSemanal = $tiempoDisponibleSemanal;
        $this->habilidades = $habilidades;
    }

    //getters
    public function getGrado(): int
    {
        return $this->grado;
    }
    public function getInstitucion(): string
    {
        return $this->institucion;
    }
    public function getTiempoDisponibleSemanal(): int
    {
        return $this->tiempoDisponibleSemanal;
    }
    public function getHabilidades(): array
    {
        return $this->habilidades;
    }

    //setters
    public function setGrado(int $grado): void
    {
        $this->grado = $grado;
    }

    public function setInstitucion(string $institucion): void
    {
        $this->institucion = $institucion;
    }

    public function setTiempoDisponibleSemanal(int $tiempoDisponibleSemanal): void
    {
        $this->tiempoDisponibleSemanal = $tiempoDisponibleSemanal;
    }

    public function setHabilidades(array $habilidades): void
    {
        $this->habilidades = $habilidades;
    }

}