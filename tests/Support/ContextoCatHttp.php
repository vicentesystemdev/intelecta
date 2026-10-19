<?php

namespace Tests\Support;

use App\Domains\Cat\DTOs\ContextoCat;

class ContextoCatHttp
{
    public static function crear(array $historial = []): ContextoCat
    {
        $manifest = json_decode(file_get_contents(base_path('resources/cat/laravel_mat_demo_v1.json')), true, flags: JSON_THROW_ON_ERROR);
        $items = array_map(fn ($p) => ['id_preg' => $p['id_preg'], 'enunciado' => 'Enunciado privado '.$p['id_preg'],
            'parametros' => ['a' => $p['a'], 'b' => $p['b'], 'c' => $p['c']],
            'alternativas' => [['id_alt' => $p['id_preg'] * 5, 'texto' => 'Clave privada', 'correcta' => true]]], $manifest['items']);

        return new ContextoCat(501, count($historial), ['nombre' => 'Matemática CAT DEMO', 'version' => '1',
            'version_parametros' => 'simulados-demo-v1', 'demo' => true,
            'configuracion' => ['minimum' => 8, 'maximum' => 10, 'threshold' => 0.45, 'D' => 1, 'calibrado' => false],
            'items' => $items], $historial, null);
    }
}
