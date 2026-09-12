<?php

namespace App\Domains\Academico\DTOs;

final readonly class AsistenciaAcademicaData
{
    public function __construct(
        public ?int $programaId,
        public int $grupoId,
        public int $postulanteId,
        public ?int $tutorId,
        public ?string $fecha,
        public string $sesion,
        public string $estado,
        public ?string $observacion,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            programaId: filled($data['id_prog'] ?? null) ? (int) $data['id_prog'] : null,
            grupoId: (int) $data['id_grupo'],
            postulanteId: (int) $data['id_post'],
            tutorId: filled($data['id_tutor'] ?? null) ? (int) $data['id_tutor'] : null,
            fecha: filled($data['fecha_asist'] ?? null) ? (string) $data['fecha_asist'] : null,
            sesion: trim($data['sesion_asist'] ?? '') ?: 'General',
            estado: $data['estado_asist'] ?? 'presente',
            observacion: filled($data['observacion_asist'] ?? null)
                ? trim($data['observacion_asist'])
                : null,
        );
    }

    public function withProgram(int $programaId): self
    {
        return $this->withContext($programaId, $this->tutorId);
    }

    public function withContext(int $programaId, ?int $tutorId): self
    {
        return new self(
            programaId: $programaId,
            grupoId: $this->grupoId,
            postulanteId: $this->postulanteId,
            tutorId: $tutorId,
            fecha: $this->fecha,
            sesion: $this->sesion,
            estado: $this->estado,
            observacion: $this->observacion,
        );
    }

    public function withDate(string $fecha): self
    {
        return new self(
            programaId: $this->programaId,
            grupoId: $this->grupoId,
            postulanteId: $this->postulanteId,
            tutorId: $this->tutorId,
            fecha: $fecha,
            sesion: $this->sesion,
            estado: $this->estado,
            observacion: $this->observacion,
        );
    }

    public function toArray(): array
    {
        return [
            'id_prog' => $this->programaId,
            'id_grupo' => $this->grupoId,
            'id_post' => $this->postulanteId,
            'id_tutor' => $this->tutorId,
            'fecha_asist' => $this->fecha,
            'sesion_asist' => $this->sesion,
            'estado_asist' => $this->estado,
            'observacion_asist' => $this->observacion,
        ];
    }
}
