<?php

declare(strict_types=1);

namespace App\Entities;

class RetoReal extends Reto
{
    private string $entidadColaboradora;

    public function __construct(int $idReto, string $titulo, string $tipo, string $descripcion, string $dificultad, string $estado, array $areasConocimiento, string $entidadColaboradora)
    {
        parent::__construct($idReto, $titulo, $tipo, $descripcion, $dificultad, $estado, $areasConocimiento);
        $this->entidadColaboradora = $entidadColaboradora;
    }

    //getters
    public function getEntidadColaboradora(): string
    {
        return $this->entidadColaboradora;
    }
    //setters
    public function setEntidadColaboradora(string $entidadColaboradora): void
    {
        $this->entidadColaboradora = $entidadColaboradora;
    }
}