<?php

declare(strict_types=1);

namespace App\Entities;

class RetoReal extends Reto
{
    private string $entidad;

    public function __construct(int $idReto, string $titulo, string $tipo, string $descripcion, string $dificultad, string $estado, array $areasConocimiento, string $entidad)
    {
        parent::__construct($idReto, $titulo, $tipo, $descripcion, $dificultad, $estado, $areasConocimiento);
        $this->entidad = $entidad;
    }

    //getters
    public function getEntidad(): string
    {
        return $this->entidad;
    }
    //setters
    public function setEntidad(string $entidad): void
    {
        $this->entidad = $entidad;
    }
}