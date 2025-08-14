<?php

declare(strict_types=1);

namespace App\Entities;

abstract class Reto
{
    private int $idReto;
    private string $titulo;
    private string $tipo;
    private string $descripcion;
    private string $dificultad;
    private string $estado;
    private array $areasConocimiento;

    public function __construct(int $idReto, string $titulo, string $tipo, string $descripcion, string $dificultad, string $estado, array $areasConocimiento)
    {
        $this->idReto = $idReto;
        $this->titulo = $titulo;
        $this->tipo = $tipo;
        $this->descripcion = $descripcion;
        $this->dificultad = $dificultad;
        $this->areasConocimiento = $areasConocimiento;
        $this->estado = $estado;
    }

    //getters
    public function getIdReto(): int
    {
        return $this->idReto;
    }
    public function getTitulo(): string
    {
        return $this->titulo;
    }
    public function getTipo(): string
    {
        return $this->tipo;
    }
    public function getDificultad(): string
    {
        return $this->dificultad;
    }
    public function getEstado(): string
    {
        return $this->estado;
    }
    public function getDescripcion(): string
    {
        return $this->descripcion;
    }
    public function getAreasConocimiento(): array
    {
        return $this->areasConocimiento;
    }

    //setters
    public function setIdReto(int $idReto): void
    {
        $this->idReto = $idReto;
    }
    public function setTitulo(string $titulo): void
    {
        $this->titulo = $titulo;
    }
    public function setTipo(string $tipo): void
    {
        $this->tipo = $tipo;
    }
    public function setDificultad(string $dificultad): void
    {
        $this->dificultad = $dificultad;
    }
    public function setEstado(string $estado): void
    {
        $this->estado = $estado;
    }
    public function setDescripcion(string $descripcion): void
    {
        $this->descripcion = $descripcion;
    }
    public function setAreasConocimiento(array $areasConocimiento): void
    {
        $this->areasConocimiento = $areasConocimiento;
    }
}
