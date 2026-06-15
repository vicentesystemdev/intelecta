<?php

namespace App\Domains\Resultados\DTOs;

final readonly class EnviarEvaluacionData
{
    /**
     * @param  array<int, RespuestaEvaluacionData>  $respuestas
     */
    public function __construct(
        public array $respuestas,
        public ?int $tiempoTotalSegundos,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            respuestas: array_map(
                fn (array $respuesta) => RespuestaEvaluacionData::fromArray($respuesta),
                $data['respuestas'] ?? [],
            ),
            tiempoTotalSegundos: filled($data['tiempo_total_segundos'] ?? null)
                ? (int) $data['tiempo_total_segundos']
                : null,
        );
    }
}
