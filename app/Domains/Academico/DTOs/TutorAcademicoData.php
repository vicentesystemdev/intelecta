<?php

namespace App\Domains\Academico\DTOs;

final readonly class TutorAcademicoData
{
    public function __construct(
        public ?int $userId,
        public string $nombres,
        public string $apellidos,
        public ?string $ci,
        public ?string $celular,
        public ?string $correo,
        public ?string $especialidad,
        public ?string $formacion,
        public ?string $experiencia,
        public string $estado,
        public ?string $observacion,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            userId: filled($data['user_id'] ?? null) ? (int) $data['user_id'] : null,
            nombres: trim($data['nombres_tutor']),
            apellidos: trim($data['apellidos_tutor']),
            ci: self::nullable($data['ci_tutor'] ?? null),
            celular: self::nullable($data['celular_tutor'] ?? null),
            correo: self::nullable($data['correo_tutor'] ?? null),
            especialidad: self::nullable($data['especialidad_tutor'] ?? null),
            formacion: self::nullable($data['formacion_tutor'] ?? null),
            experiencia: self::nullable($data['experiencia_tutor'] ?? null),
            estado: $data['estado_tutor'] ?? 'activo',
            observacion: self::nullable($data['observacion_tutor'] ?? null),
        );
    }

    public function toArray(): array
    {
        return [
            'user_id' => $this->userId,
            'nombres_tutor' => $this->nombres,
            'apellidos_tutor' => $this->apellidos,
            'ci_tutor' => $this->ci,
            'celular_tutor' => $this->celular,
            'correo_tutor' => $this->correo,
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
