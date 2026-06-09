<?php

namespace App\Domains\Postulantes\DTOs;

final readonly class PostulanteData
{
    public function __construct(
        public string $nombresPost,
        public string $apellidosPost,
        public ?string $ciPost,
        public ?string $emailPost,
        public ?string $celularPost,
        public ?int $edadPost,
        public ?int $idCol,
        public ?int $idCar,
        public ?string $turnoPost,
        public int $gestionPost,
        public string $estadoPost,
        public ?string $observacionesPost,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            nombresPost: $data['nombres_post'],
            apellidosPost: $data['apellidos_post'],
            ciPost: self::nullableString($data['ci_post'] ?? null),
            emailPost: self::nullableString($data['email_post'] ?? null),
            celularPost: self::nullableString($data['celular_post'] ?? null),
            edadPost: self::nullableInteger($data['edad_post'] ?? null),
            idCol: self::nullableInteger($data['id_col'] ?? null),
            idCar: self::nullableInteger($data['id_car'] ?? null),
            turnoPost: self::nullableString($data['turno_post'] ?? null),
            gestionPost: (int) $data['gestion_post'],
            estadoPost: $data['estado_post'] ?? 'activo',
            observacionesPost: self::nullableString($data['observaciones_post'] ?? null),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'nombres_post' => $this->nombresPost,
            'apellidos_post' => $this->apellidosPost,
            'ci_post' => $this->ciPost,
            'email_post' => $this->emailPost,
            'celular_post' => $this->celularPost,
            'edad_post' => $this->edadPost,
            'id_col' => $this->idCol,
            'id_car' => $this->idCar,
            'turno_post' => $this->turnoPost,
            'gestion_post' => $this->gestionPost,
            'estado_post' => $this->estadoPost,
            'observaciones_post' => $this->observacionesPost,
        ];
    }

    private static function nullableString(mixed $value): ?string
    {
        return filled($value) ? (string) $value : null;
    }

    private static function nullableInteger(mixed $value): ?int
    {
        return filled($value) ? (int) $value : null;
    }
}
