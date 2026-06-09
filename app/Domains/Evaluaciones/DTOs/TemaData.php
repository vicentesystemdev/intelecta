<?php

namespace App\Domains\Evaluaciones\DTOs;

final readonly class TemaData
{
    public function __construct(
        public int $idArea,
        public string $nombreTem,
        public ?string $descripcionTem,
        public ?string $nivelTem,
        public string $estadoTem,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            (int) $data['id_area'],
            $data['nombre_tem'],
            filled($data['descripcion_tem'] ?? null) ? $data['descripcion_tem'] : null,
            filled($data['nivel_tem'] ?? null) ? $data['nivel_tem'] : null,
            $data['estado_tem'] ?? 'activo',
        );
    }

    public function toArray(): array
    {
        return [
            'id_area' => $this->idArea,
            'nombre_tem' => $this->nombreTem,
            'descripcion_tem' => $this->descripcionTem,
            'nivel_tem' => $this->nivelTem,
            'estado_tem' => $this->estadoTem,
        ];
    }
}
