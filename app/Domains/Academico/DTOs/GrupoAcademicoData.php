<?php

namespace App\Domains\Academico\DTOs;

final readonly class GrupoAcademicoData
{
    public function __construct(
        public int $programaId,
        public string $nombre,
        public ?string $codigo,
        public ?string $turno,
        public ?string $aula,
        public int $capacidad,
        public ?string $nivel,
        public ?string $tutorResponsable,
        public string $estado,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            programaId: (int) $data['id_prog'],
            nombre: $data['nombre_grupo'],
            codigo: self::nullable($data['codigo_grupo'] ?? null),
            turno: self::nullable($data['turno_grupo'] ?? null),
            aula: self::nullable($data['aula_grupo'] ?? null),
            capacidad: (int) ($data['capacidad_grupo'] ?? 30),
            nivel: self::nullable($data['nivel_grupo'] ?? null),
            tutorResponsable: self::nullable($data['tutor_responsable_grupo'] ?? null),
            estado: $data['estado_grupo'] ?? 'activo',
        );
    }

    public function toArray(): array
    {
        return [
            'id_prog' => $this->programaId,
            'nombre_grupo' => $this->nombre,
            'codigo_grupo' => $this->codigo,
            'turno_grupo' => $this->turno,
            'aula_grupo' => $this->aula,
            'capacidad_grupo' => $this->capacidad,
            'nivel_grupo' => $this->nivel,
            'tutor_responsable_grupo' => $this->tutorResponsable,
            'estado_grupo' => $this->estado,
        ];
    }

    private static function nullable(mixed $value): ?string
    {
        return filled($value) ? (string) $value : null;
    }
}
