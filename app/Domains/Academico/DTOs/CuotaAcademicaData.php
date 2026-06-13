<?php

namespace App\Domains\Academico\DTOs;

final readonly class CuotaAcademicaData
{
    public function __construct(
        public int $matriculaId,
        public ?int $numero,
        public ?string $concepto,
        public float $monto,
        public ?string $fechaVencimiento,
        public ?string $fechaPago,
        public ?string $metodoPago,
        public string $estado,
        public ?string $observacion,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            matriculaId: (int) $data['id_mat'],
            numero: filled($data['nro_cuota'] ?? null) ? (int) $data['nro_cuota'] : null,
            concepto: self::nullable($data['concepto_cuota'] ?? null),
            monto: (float) ($data['monto_cuota'] ?? 0),
            fechaVencimiento: self::nullable($data['fecha_vencimiento_cuota'] ?? null),
            fechaPago: self::nullable($data['fecha_pago_cuota'] ?? null),
            metodoPago: self::nullable($data['metodo_pago_cuota'] ?? null),
            estado: $data['estado_cuota'] ?? 'pendiente',
            observacion: self::nullable($data['observacion_cuota'] ?? null),
        );
    }

    public function toArray(): array
    {
        return [
            'id_mat' => $this->matriculaId,
            'nro_cuota' => $this->numero,
            'concepto_cuota' => $this->concepto,
            'monto_cuota' => $this->monto,
            'fecha_vencimiento_cuota' => $this->fechaVencimiento,
            'fecha_pago_cuota' => $this->fechaPago,
            'metodo_pago_cuota' => $this->metodoPago,
            'estado_cuota' => $this->estado,
            'observacion_cuota' => $this->observacion,
        ];
    }

    private static function nullable(mixed $value): ?string
    {
        return filled($value) ? trim((string) $value) : null;
    }
}
