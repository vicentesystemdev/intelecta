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
        public ?string $fechaNacimientoPost,
        public ?int $idCol,
        public ?int $idUni,
        public ?int $idCar,
        public ?string $turnoPost,
        public int $gestionPost,
        public string $estadoPost,
        public ?string $observacionesPost,
        public bool $fechaNacimientoProvided,
        public bool $crearOtroColegio,
        public ?string $otroColegioNombre,
        public bool $crearOtraUniversidad,
        public ?string $otraUniversidadNombre,
        public ?string $otraUniversidadSigla,
        public bool $crearOtraCarrera,
        public ?string $otraCarreraNombre,
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
            fechaNacimientoPost: self::nullableString($data['fecha_nacimiento_post'] ?? null),
            idCol: self::nullableInteger($data['id_col'] ?? null),
            idUni: self::nullableInteger($data['id_uni'] ?? null),
            idCar: self::nullableInteger($data['id_car'] ?? null),
            turnoPost: self::nullableString($data['turno_post'] ?? null),
            gestionPost: (int) $data['gestion_post'],
            estadoPost: $data['estado_post'] ?? 'activo',
            observacionesPost: self::nullableString($data['observaciones_post'] ?? null),
            fechaNacimientoProvided: array_key_exists('fecha_nacimiento_post', $data),
            crearOtroColegio: (bool) ($data['crear_otro_colegio'] ?? false),
            otroColegioNombre: self::nullableString($data['otro_colegio_nombre'] ?? null),
            crearOtraUniversidad: (bool) ($data['crear_otra_universidad'] ?? false),
            otraUniversidadNombre: self::nullableString($data['otra_universidad_nombre'] ?? null),
            otraUniversidadSigla: self::nullableString($data['otra_universidad_sigla'] ?? null),
            crearOtraCarrera: (bool) ($data['crear_otra_carrera'] ?? false),
            otraCarreraNombre: self::nullableString($data['otra_carrera_nombre'] ?? null),
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
            // Omitted on update means keep the stored date; never overwrite the legacy age.
            ...($this->fechaNacimientoProvided ? ['fecha_nacimiento_post' => $this->fechaNacimientoPost] : []),
            'id_col' => $this->idCol,
            'id_car' => $this->idCar,
            'turno_post' => $this->turnoPost,
            'gestion_post' => $this->gestionPost,
            'estado_post' => $this->estadoPost,
            'observaciones_post' => $this->observacionesPost,
        ];
    }

    public function withCatalogs(?int $colegioId, ?int $carreraId): self
    {
        return new self(
            nombresPost: $this->nombresPost,
            apellidosPost: $this->apellidosPost,
            ciPost: $this->ciPost,
            emailPost: $this->emailPost,
            celularPost: $this->celularPost,
            fechaNacimientoPost: $this->fechaNacimientoPost,
            idCol: $colegioId,
            idUni: $this->idUni,
            idCar: $carreraId,
            turnoPost: $this->turnoPost,
            gestionPost: $this->gestionPost,
            estadoPost: $this->estadoPost,
            observacionesPost: $this->observacionesPost,
            fechaNacimientoProvided: $this->fechaNacimientoProvided,
            crearOtroColegio: $this->crearOtroColegio,
            otroColegioNombre: $this->otroColegioNombre,
            crearOtraUniversidad: $this->crearOtraUniversidad,
            otraUniversidadNombre: $this->otraUniversidadNombre,
            otraUniversidadSigla: $this->otraUniversidadSigla,
            crearOtraCarrera: $this->crearOtraCarrera,
            otraCarreraNombre: $this->otraCarreraNombre,
        );
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
