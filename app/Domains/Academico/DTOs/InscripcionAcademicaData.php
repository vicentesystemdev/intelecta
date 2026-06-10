<?php

namespace App\Domains\Academico\DTOs;

final readonly class InscripcionAcademicaData
{
    public function __construct(
        public int $programaId,
        public ?int $grupoId,
        public int $postulanteId,
        public ?string $fechaInscripcion,
        public string $estado,
        public ?string $observacion,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            programaId: (int) $data['id_prog'],
            grupoId: filled($data['id_grupo'] ?? null) ? (int) $data['id_grupo'] : null,
            postulanteId: (int) $data['id_post'],
            fechaInscripcion: filled($data['fecha_inscripcion'] ?? null) ? (string) $data['fecha_inscripcion'] : null,
            estado: $data['estado_inscripcion'] ?? 'activo',
            observacion: filled($data['observacion_inscripcion'] ?? null) ? (string) $data['observacion_inscripcion'] : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id_prog' => $this->programaId,
            'id_grupo' => $this->grupoId,
            'id_post' => $this->postulanteId,
            'fecha_inscripcion' => $this->fechaInscripcion,
            'estado_inscripcion' => $this->estado,
            'observacion_inscripcion' => $this->observacion,
        ];
    }
}
