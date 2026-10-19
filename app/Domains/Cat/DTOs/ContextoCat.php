<?php

namespace App\Domains\Cat\DTOs;

final readonly class ContextoCat
{
    public function __construct(
        public int $id_intento_cat,
        public int $revision,
        public array $snapshot,
        public array $historial,
        public ?string $versionMotor,
    ) {}
}
