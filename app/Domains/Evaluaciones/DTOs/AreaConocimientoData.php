<?php

namespace App\Domains\Evaluaciones\DTOs;

final readonly class AreaConocimientoData
{
    public function __construct(
        public string $nombreArea,
        public ?string $descripcionArea,
        public string $estadoArea,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            $data['nombre_area'],
            filled($data['descripcion_area'] ?? null) ? $data['descripcion_area'] : null,
            $data['estado_area'] ?? 'activo',
        );
    }

    public function toArray(): array
    {
        return [
            'nombre_area' => $this->nombreArea,
            'descripcion_area' => $this->descripcionArea,
            'estado_area' => $this->estadoArea,
        ];
    }
}
