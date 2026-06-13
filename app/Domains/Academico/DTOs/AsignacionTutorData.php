<?php

namespace App\Domains\Academico\DTOs;

final readonly class AsignacionTutorData
{
    public function __construct(
        public int $tutorId,
        public ?int $programaId,
        public ?int $grupoId,
        public ?string $materiaReferencia,
        public ?string $rol,
        public ?string $fechaInicio,
        public ?string $fechaFin,
        public string $estado,
        public ?string $observacion,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            tutorId: (int) $data['id_tutor'],
            programaId: filled($data['id_prog'] ?? null) ? (int) $data['id_prog'] : null,
            grupoId: filled($data['id_grupo'] ?? null) ? (int) $data['id_grupo'] : null,
            materiaReferencia: self::nullable($data['materia_referencia_asig'] ?? null),
            rol: self::nullable($data['rol_asig'] ?? null),
            fechaInicio: self::nullable($data['fecha_inicio_asig'] ?? null),
            fechaFin: self::nullable($data['fecha_fin_asig'] ?? null),
            estado: $data['estado_asig'] ?? 'activo',
            observacion: self::nullable($data['observacion_asig'] ?? null),
        );
    }

    public function toArray(): array
    {
        return [
            'id_tutor' => $this->tutorId,
            'id_prog' => $this->programaId,
            'id_grupo' => $this->grupoId,
            'materia_referencia_asig' => $this->materiaReferencia,
            'rol_asig' => $this->rol,
            'fecha_inicio_asig' => $this->fechaInicio,
            'fecha_fin_asig' => $this->fechaFin,
            'estado_asig' => $this->estado,
            'observacion_asig' => $this->observacion,
        ];
    }

    private static function nullable(mixed $value): ?string
    {
        return filled($value) ? trim((string) $value) : null;
    }
}
