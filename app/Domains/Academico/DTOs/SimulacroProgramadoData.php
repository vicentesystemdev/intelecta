<?php

namespace App\Domains\Academico\DTOs;

final readonly class SimulacroProgramadoData
{
    public function __construct(
        public int $programaId,
        public ?int $grupoId,
        public ?int $plantillaId,
        public string $titulo,
        public ?string $fecha,
        public ?string $horaInicio,
        public ?string $horaFin,
        public ?string $modalidad,
        public string $estado,
        public ?string $observacion,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            programaId: (int) $data['id_prog'],
            grupoId: filled($data['id_grupo'] ?? null) ? (int) $data['id_grupo'] : null,
            plantillaId: filled($data['id_plantilla'] ?? null) ? (int) $data['id_plantilla'] : null,
            titulo: $data['titulo_sim'],
            fecha: self::nullable($data['fecha_sim'] ?? null),
            horaInicio: self::nullable($data['hora_inicio_sim'] ?? null),
            horaFin: self::nullable($data['hora_fin_sim'] ?? null),
            modalidad: self::nullable($data['modalidad_sim'] ?? null),
            estado: $data['estado_sim'] ?? 'programado',
            observacion: self::nullable($data['observacion_sim'] ?? null),
        );
    }

    public function toArray(): array
    {
        return [
            'id_prog' => $this->programaId,
            'id_grupo' => $this->grupoId,
            'id_plantilla' => $this->plantillaId,
            'titulo_sim' => $this->titulo,
            'fecha_sim' => $this->fecha,
            'hora_inicio_sim' => $this->horaInicio,
            'hora_fin_sim' => $this->horaFin,
            'modalidad_sim' => $this->modalidad,
            'estado_sim' => $this->estado,
            'observacion_sim' => $this->observacion,
        ];
    }

    private static function nullable(mixed $value): ?string
    {
        return filled($value) ? (string) $value : null;
    }
}
