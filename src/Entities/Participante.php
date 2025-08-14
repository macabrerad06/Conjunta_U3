<?php

declare(strict_types=1);

namespace App\Entities;

abstract class Participante
{
    private string $tipo;
    private string $nombre;
    private string $email;
    private String $nivelHabilidad;

    public function __construct(string $tipo, string $nombre, string $email, string $nivelHabilidad)
    {
        $this->tipo = $tipo;
        $this->nombre = $nombre;
        $this->email = $email;
        $this->nivelHabilidad = $nivelHabilidad;
    }


    //getters
    

    //setters
}