<?php

namespace App\Domains\Academico\DTOs;

final readonly class TutorAcademicoData
{
    public function __construct(
        public int $personalId,
        public ?string $especialidad,
        public ?string $formacion,
        public ?string $experiencia,
        public string $estado,
        public ?string $observacion,
        public ?array $personal = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            personalId: (int) $data['personal_id'],
            especialidad: self::nullable($data['especialidad_tutor'] ?? null),
            formacion: self::nullable($data['formacion_tutor'] ?? null),
            experiencia: self::nullable($data['experiencia_tutor'] ?? null),
            estado: $data['estado_tutor'] ?? 'activo',
            observacion: self::nullable($data['observacion_tutor'] ?? null),
            personal: $data['personal'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'personal_id' => $this->personalId,
            'especialidad_tutor' => $this->especialidad,
            'formacion_tutor' => $this->formacion,
            'experiencia_tutor' => $this->experiencia,
            'estado_tutor' => $this->estado,
            'observacion_tutor' => $this->observacion,
        ];
    }

    private static function nullable(mixed $value): ?string
    {
        return filled($value) ? trim((string) $value) : null;
    }
}
