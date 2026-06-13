<?php

namespace App\Domains\Academico\DTOs;

final readonly class HabilitacionAcademicaData
{
    public function __construct(
        public string $estado,
        public ?string $motivo,
        public ?string $fechaInicio,
        public ?string $fechaFin,
        public bool $evaluaciones,
        public bool $simulacros,
        public bool $reportes,
        public ?string $observacion,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            estado: $data['estado_hab'] ?? 'habilitado',
            motivo: self::nullable($data['motivo_hab'] ?? null),
            fechaInicio: self::nullable($data['fecha_inicio_hab'] ?? null),
            fechaFin: self::nullable($data['fecha_fin_hab'] ?? null),
            evaluaciones: filter_var($data['habilitado_evaluaciones_hab'] ?? true, FILTER_VALIDATE_BOOL),
            simulacros: filter_var($data['habilitado_simulacros_hab'] ?? true, FILTER_VALIDATE_BOOL),
            reportes: filter_var($data['habilitado_reportes_hab'] ?? true, FILTER_VALIDATE_BOOL),
            observacion: self::nullable($data['observacion_hab'] ?? null),
        );
    }

    public function toArray(): array
    {
        return [
            'estado_hab' => $this->estado,
            'motivo_hab' => $this->motivo,
            'fecha_inicio_hab' => $this->fechaInicio,
            'fecha_fin_hab' => $this->fechaFin,
            'habilitado_evaluaciones_hab' => $this->evaluaciones,
            'habilitado_simulacros_hab' => $this->simulacros,
            'habilitado_reportes_hab' => $this->reportes,
            'observacion_hab' => $this->observacion,
        ];
    }

    private static function nullable(mixed $value): ?string
    {
        return filled($value) ? trim((string) $value) : null;
    }
}
