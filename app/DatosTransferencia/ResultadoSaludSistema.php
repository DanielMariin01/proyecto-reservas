<?php

declare(strict_types=1);

namespace App\DatosTransferencia;

final readonly class ResultadoSaludSistema
{
    public function __construct(
        public bool $baseDatos,
        public bool $colas,
        public bool $almacenamiento,
        public bool $correo,
    ) {}

    public function saludable(): bool
    {
        return $this->baseDatos && $this->colas && $this->almacenamiento && $this->correo;
    }

    /**
     * @return array{baseDatos: bool, colas: bool, almacenamiento: bool, correo: bool}
     */
    public function comoArreglo(): array
    {
        return [
            'baseDatos' => $this->baseDatos,
            'colas' => $this->colas,
            'almacenamiento' => $this->almacenamiento,
            'correo' => $this->correo,
        ];
    }
}
