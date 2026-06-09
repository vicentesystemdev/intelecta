<?php

namespace App\Domains\Evaluaciones\DTOs;

final readonly class PlantillaEvaluacionData
{
    public function __construct(
        public string $nombrePlan,
        public ?string $descripcionPlan,
        public ?string $objetivoPlan,
        public ?int $duracionMinutosPlan,
        public ?string $dificultadPlan,
        public string $estadoPlan,
        public array $preguntas,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            $data['nombre_plan'],
            filled($data['descripcion_plan'] ?? null) ? $data['descripcion_plan'] : null,
            filled($data['objetivo_plan'] ?? null) ? $data['objetivo_plan'] : null,
            filled($data['duracion_minutos_plan'] ?? null) ? (int) $data['duracion_minutos_plan'] : null,
            filled($data['dificultad_plan'] ?? null) ? $data['dificultad_plan'] : null,
            $data['estado_plan'] ?? 'activa',
            array_values($data['preguntas'] ?? []),
        );
    }

    public function templateAttributes(): array
    {
        return [
            'nombre_plan' => $this->nombrePlan,
            'descripcion_plan' => $this->descripcionPlan,
            'objetivo_plan' => $this->objetivoPlan,
            'duracion_minutos_plan' => $this->duracionMinutosPlan,
            'dificultad_plan' => $this->dificultadPlan,
            'estado_plan' => $this->estadoPlan,
        ];
    }
}
