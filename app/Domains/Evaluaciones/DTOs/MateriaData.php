<?php

namespace App\Domains\Evaluaciones\DTOs;

final readonly class MateriaData
{
    public function __construct(
        public string $codigo,
        public string $nombre,
        public string $descripcion,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            codigo: $data['codigo_mat'],
            nombre: $data['nombre_mat'],
            descripcion: $data['descripcion_mat'],
        );
    }

    public function toArray(): array
    {
        return [
            'codigo_mat' => $this->codigo,
            'nombre_mat' => $this->nombre,
            'descripcion_mat' => $this->descripcion,
        ];
    }
}
