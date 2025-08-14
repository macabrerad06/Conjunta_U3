<?php

declare(strict_types=1);

namespace App\Entities;

class RetoExperimental extends Reto
{
    private string $enfoque;

    public function __construct(int $idReto, string $titulo, string $tipo, string $descripcion, string $dificultad, string $estado, array $areasConocimiento, string $enfoque)
    {
        parent::__construct($idReto, $titulo, $tipo, $descripcion, $dificultad, $estado, $areasConocimiento);
        $this->enfoque = $enfoque;
    }

    //getters
    public function getEnfoque(): string
    {
        return $this->enfoque;
    }

    //setters
    public function setEnfoque(string $enfoque): void
    {
        $this->enfoque = $enfoque;
    }
}