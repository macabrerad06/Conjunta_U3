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
        string $tipo,
        string $nombre,
        string $email,
        string $nivelHabilidad,
        int $grado,
        string $institucion,
        int $tiempoDisponibleSemanal,
        array $habilidades
    ) {
        parent::__construct($tipo, $nombre, $email, $nivelHabilidad);
        $this->grado = $grado;
        $this->institucion = $institucion;
        $this->tiempoDisponibleSemanal = $tiempoDisponibleSemanal;
        $this->habilidades = $habilidades;
    }

    //getters
    

    //setters


}