<?php

namespace App\Domains\Cat\DTOs;

final readonly class DecisionCat
{
    public function __construct(
        public ?int $siguienteId,
        public bool $finalizada,
        public float $theta,
        public float $sd,
        public array $intervalo,
        public ?string $motivo,
        public string $versionMotor,
    ) {}

    public function metricas(): array
    {
        return ['theta' => $this->theta, 'sd' => $this->sd, 'intervalo' => $this->intervalo,
            'motivo' => $this->motivo, 'version_motor' => $this->versionMotor, 'experimental' => true];
    }
}
