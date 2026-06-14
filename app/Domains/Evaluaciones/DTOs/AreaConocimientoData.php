<?php

namespace App\Domains\Evaluaciones\DTOs;

final readonly class AreaConocimientoData
{
    public function __construct(
        public int $materiaId,
        public string $nombreArea,
        public ?string $descripcionArea,
        public string $estadoArea,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            (int) $data['id_mat'],
            $data['nombre_area'],
            filled($data['descripcion_area'] ?? null) ? $data['descripcion_area'] : null,
            $data['estado_area'] ?? 'activo',
        );
    }

    public function toArray(): array
    {
        return [
            'id_mat' => $this->materiaId,
            'nombre_area' => $this->nombreArea,
            'descripcion_area' => $this->descripcionArea,
            'estado_area' => $this->estadoArea,
        ];
    }
}
