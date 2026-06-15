<?php

namespace App\Domains\Resultados\DTOs;

final readonly class RespuestaEvaluacionData
{
    public function __construct(
        public int $preguntaId,
        public ?int $alternativaId,
        public ?string $respuestaTexto,
        public ?int $tiempoSegundos,
        public int $intentos = 1,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            preguntaId: (int) $data['id_preg'],
            alternativaId: filled($data['id_alt'] ?? null) ? (int) $data['id_alt'] : null,
            respuestaTexto: filled($data['respuesta_texto'] ?? null)
                ? trim((string) $data['respuesta_texto'])
                : null,
            tiempoSegundos: filled($data['tiempo_segundos'] ?? null)
                ? (int) $data['tiempo_segundos']
                : null,
            intentos: max(1, (int) ($data['intentos'] ?? 1)),
        );
    }
}
