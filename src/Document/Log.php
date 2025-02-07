<?php

namespace App\Document;

use DateTime;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

#[MongoDB\Document]
class Log
{
    #[MongoDB\Id]
    private string $id;
    #[MongoDB\Field(type: 'string')]
    private string $mensaje;
    #[MongoDB\Field(type: 'date')]
    private DateTime $fecha;

    public function __construct(string $mensaje)
    {
        $this->mensaje = $mensaje;
        $this->fecha = new DateTime();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getMensaje(): string
    {
        return $this->mensaje;
    }

    public function getFecha(): DateTime
    {
        return $this->fecha;
    }
}
