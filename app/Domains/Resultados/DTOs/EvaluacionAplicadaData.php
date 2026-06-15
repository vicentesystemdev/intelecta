<?php

namespace App\Domains\Resultados\DTOs;

final readonly class EvaluacionAplicadaData
{
    public function __construct(
        public int $postulanteId,
        public int $plantillaId,
        public ?int $simulacroId,
        public ?string $tipo,
        public float $puntajeMaximo,
    ) {}

    public function toArray(): array
    {
        return [
            'id_post' => $this->postulanteId,
            'id_plantilla' => $this->plantillaId,
            'id_sim' => $this->simulacroId,
            'tipo_eval_apl' => $this->tipo,
            'fecha_inicio_eval_apl' => now(),
            'estado_eval_apl' => 'en_progreso',
            'puntaje_maximo_eval_apl' => $this->puntajeMaximo,
            'intentos_eval_apl' => 1,
        ];
    }
}
