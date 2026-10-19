<?php

namespace Tests\Support;

use App\Domains\Cat\Contracts\MotorCat;
use App\Domains\Cat\DTOs\ContextoCat;
use App\Domains\Cat\DTOs\DecisionCat;
use App\Domains\Cat\Exceptions\MotorCatNoDisponible;
use Closure;

// Doble determinista de pruebas; NO implementa TRI ni se carga desde la aplicación.
class MotorCatSimulado implements MotorCat
{
    public bool $caido = false;

    public int $llamadas = 0;

    public int $pararEn = 10;

    public ?Closure $interceptar = null;

    public array $contextos = [];

    public function calcular(ContextoCat $contexto): DecisionCat
    {
        $this->llamadas++;
        $this->contextos[] = $contexto;
        if ($this->caido) {
            throw new MotorCatNoDisponible('Caída simulada');
        }
        if ($this->interceptar) {
            return ($this->interceptar)($contexto);
        }
        $n = count($contexto->historial);
        $fin = $n >= $this->pararEn;

        return new DecisionCat($fin ? null : $n + 1, $fin, 0.25, $fin ? 0.4 : 0.8,
            [-0.5, 1.0], $fin ? 'Parada simulada de prueba' : null, 'solo-pruebas-v1');
    }
}
