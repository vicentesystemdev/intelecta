<?php

namespace App\Domains\Academico\DTOs;

final readonly class ProgramaAcademicoData
{
    public function __construct(
        public string $nombre,
        public ?string $codigo,
        public ?string $universidadObjetivo,
        public ?string $carreraArea,
        public ?string $modalidad,
        public ?string $fechaInicio,
        public ?string $fechaFin,
        public ?string $descripcion,
        public string $estado,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            nombre: $data['nombre_prog'],
            codigo: self::nullable($data['codigo_prog'] ?? null),
            universidadObjetivo: self::nullable($data['universidad_objetivo_prog'] ?? null),
            carreraArea: self::nullable($data['carrera_area_prog'] ?? null),
            modalidad: self::nullable($data['modalidad_prog'] ?? null),
            fechaInicio: self::nullable($data['fecha_inicio_prog'] ?? null),
            fechaFin: self::nullable($data['fecha_fin_prog'] ?? null),
            descripcion: self::nullable($data['descripcion_prog'] ?? null),
            estado: $data['estado_prog'] ?? 'activo',
        );
    }

    public function toArray(): array
    {
        return [
            'nombre_prog' => $this->nombre,
            'codigo_prog' => $this->codigo,
            'universidad_objetivo_prog' => $this->universidadObjetivo,
            'carrera_area_prog' => $this->carreraArea,
            'modalidad_prog' => $this->modalidad,
            'fecha_inicio_prog' => $this->fechaInicio,
            'fecha_fin_prog' => $this->fechaFin,
            'descripcion_prog' => $this->descripcion,
            'estado_prog' => $this->estado,
        ];
    }

    private static function nullable(mixed $value): ?string
    {
        return filled($value) ? (string) $value : null;
    }
}
