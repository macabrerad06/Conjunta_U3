<?php

declare(strict_types=1);

namespace App\Entities;

abstract class Participante
{
    private int $idParticipante;
    private string $tipo;
    private string $nombre;
    private string $email;
    private String $nivelHabilidad;

    public function __construct(int $idParticipante,string $tipo, string $nombre, string $email, string $nivelHabilidad)
    {
        $this->tipo = $tipo;
        $this->nombre = $nombre;
        $this->email = $email;
        $this->nivelHabilidad = $nivelHabilidad;
    }


    //getters
    public function getIdParticipante(): int
    {
        return $this->idParticipante;
    }
    public function getTipo(): string
    {
        return $this->tipo;
    }
    public function getNombre(): string
    {
        return $this->nombre;
    }
    public function getEmail(): string
    {
        return $this->email;
    }
    public function getNivelHabilidad(): string
    {
        return $this->nivelHabilidad;
    }

    //setters
    public function setIdParticipante(int $idParticipante): void
    {
        $this->idParticipante = $idParticipante;
    }
    public function setTipo(string $tipo): void
    {
        $this->tipo = $tipo;
    }
    public function setNombre(string $nombre): void
    {
        $this->nombre = $nombre;
    }
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }
    public function setNivelHabilidad(string $nivelHabilidad): void
    {
        $this->nivelHabilidad = $nivelHabilidad;
    }
}