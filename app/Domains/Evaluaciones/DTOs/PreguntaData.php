<?php

namespace App\Domains\Evaluaciones\DTOs;

final readonly class PreguntaData
{
    public function __construct(
        public ?int $idTem,
        public string $enunciadoPreg,
        public string $tipoPreg,
        public ?string $dificultadPreg,
        public float $puntajePreg,
        public ?string $explicacionPreg,
        public string $estadoPreg,
        public array $alternativas,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            filled($data['id_tem'] ?? null) ? (int) $data['id_tem'] : null,
            $data['enunciado_preg'],
            $data['tipo_preg'] ?? 'opcion_multiple',
            filled($data['dificultad_preg'] ?? null) ? $data['dificultad_preg'] : null,
            (float) ($data['puntaje_preg'] ?? 1),
            filled($data['explicacion_preg'] ?? null) ? $data['explicacion_preg'] : null,
            $data['estado_preg'] ?? 'activo',
            array_values($data['alternativas'] ?? []),
        );
    }

    public function questionAttributes(): array
    {
        return [
            'id_tem' => $this->idTem,
            'enunciado_preg' => $this->enunciadoPreg,
            'tipo_preg' => $this->tipoPreg,
            'dificultad_preg' => $this->dificultadPreg,
            'puntaje_preg' => $this->puntajePreg,
            'explicacion_preg' => $this->explicacionPreg,
            'estado_preg' => $this->estadoPreg,
        ];
    }
}
