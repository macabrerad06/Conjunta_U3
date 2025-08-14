<?php

declare(strict_types=1);

namespace App\Entities;

class RetoExperimental extends Reto
{
    private string $enfoquePedagogico;

    public function __construct(int $idReto, string $titulo, string $tipo, string $descripcion, string $dificultad, string $estado, array $areasConocimiento, string $enfoquePedagogico)
    {
        parent::__construct($idReto, $titulo, $tipo, $descripcion, $dificultad, $estado, $areasConocimiento);
        $this->enfoquePedagogico = $enfoquePedagogico;
    }

    //getters
    public function getEnfoquePedagogico(): string
    {
        return $this->enfoquePedagogico;
    }

    //setters
    public function setEnfoquePedagogico(string $enfoquePedagogico): void
    {
        $this->enfoquePedagogico = $enfoquePedagogico;
    }
}