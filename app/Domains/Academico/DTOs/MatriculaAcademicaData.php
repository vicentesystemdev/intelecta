<?php

namespace App\Domains\Academico\DTOs;

final readonly class MatriculaAcademicaData
{
    public function __construct(
        public int $inscripcionId,
        public ?string $codigo,
        public ?string $fechaMatricula,
        public float $monto,
        public string $estado,
        public ?string $tipoBeneficio,
        public ?string $observacion,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            inscripcionId: (int) $data['id_insc'],
            codigo: self::nullable($data['codigo_mat'] ?? null),
            fechaMatricula: self::nullable($data['fecha_matricula_mat'] ?? null),
            monto: (float) ($data['monto_matricula_mat'] ?? 0),
            estado: $data['estado_matricula_mat'] ?? 'activa',
            tipoBeneficio: self::nullable($data['tipo_beneficio_mat'] ?? null),
            observacion: self::nullable($data['observacion_mat'] ?? null),
        );
    }

    public function toArray(): array
    {
        return [
            'id_insc' => $this->inscripcionId,
            'codigo_mat' => $this->codigo,
            'fecha_matricula_mat' => $this->fechaMatricula,
            'monto_matricula_mat' => $this->monto,
            'estado_matricula_mat' => $this->estado,
            'tipo_beneficio_mat' => $this->tipoBeneficio,
            'observacion_mat' => $this->observacion,
        ];
    }

    private static function nullable(mixed $value): ?string
    {
        return filled($value) ? trim((string) $value) : null;
    }
}
