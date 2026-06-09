<?php

namespace App\Domains\Evaluaciones\DTOs;

final readonly class PreguntaData
{
    public function __construct(
        public ?int $idTem,
        public ?string $subtemaPreg,
        public string $enunciadoPreg,
        public string $tipoPreg,
        public ?string $dificultadPreg,
        public ?string $exigenciaPreg,
        public ?string $habilidadPreg,
        public ?int $tiempoEstimadoSegPreg,
        public ?string $relacionIngenieriaPreg,
        public float $puntajePreg,
        public ?string $explicacionPreg,
        public string $estadoPreg,
        public array $alternativas,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            filled($data['id_tem'] ?? null) ? (int) $data['id_tem'] : null,
            filled($data['subtema_preg'] ?? null) ? $data['subtema_preg'] : null,
            $data['enunciado_preg'],
            $data['tipo_preg'] ?? 'opcion_multiple',
            filled($data['dificultad_preg'] ?? null) ? $data['dificultad_preg'] : null,
            filled($data['exigencia_preg'] ?? null) ? $data['exigencia_preg'] : null,
            filled($data['habilidad_preg'] ?? null) ? $data['habilidad_preg'] : null,
            filled($data['tiempo_estimado_seg_preg'] ?? null) ? (int) $data['tiempo_estimado_seg_preg'] : null,
            filled($data['relacion_ingenieria_preg'] ?? null) ? $data['relacion_ingenieria_preg'] : null,
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
            'subtema_preg' => $this->subtemaPreg,
            'enunciado_preg' => $this->enunciadoPreg,
            'tipo_preg' => $this->tipoPreg,
            'dificultad_preg' => $this->dificultadPreg,
            'exigencia_preg' => $this->exigenciaPreg,
            'habilidad_preg' => $this->habilidadPreg,
            'tiempo_estimado_seg_preg' => $this->tiempoEstimadoSegPreg,
            'relacion_ingenieria_preg' => $this->relacionIngenieriaPreg,
            'puntaje_preg' => $this->puntajePreg,
            'explicacion_preg' => $this->explicacionPreg,
            'estado_preg' => $this->estadoPreg,
        ];
    }
}
