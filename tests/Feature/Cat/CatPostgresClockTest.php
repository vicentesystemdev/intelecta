<?php

namespace Tests\Feature\Cat;

use App\Domains\Cat\Models\IntentoCat;
use App\Domains\Cat\Models\RespuestaCat;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CatPostgresClockTest extends TestCase
{
    public function test_cat_instants_survive_postgres_la_paz_session_without_four_hour_drift(): void
    {
        if (getenv('CAT_PGSQL_CLOCK_TESTS') !== '1') {
            $this->markTestSkipped('Requiere PostgreSQL local; solo usa tablas temporales y rollback.');
        }

        $this->assertNotEmpty(getenv('CAT_PGSQL_DATABASE'), 'Indicar CAT_PGSQL_DATABASE; no se ejecutan migraciones.');
        config(['database.connections.cat_clock_pgsql' => array_replace(config('database.connections.pgsql'), [
            'database' => getenv('CAT_PGSQL_DATABASE'), 'url' => null,
        ])]);

        // Sin migraciones, fixtures ni secuencias de las tablas reales.
        $db = DB::connection('cat_clock_pgsql');
        $db->beginTransaction();
        try {
            $db->statement("SET LOCAL TIME ZONE 'America/La_Paz'");
            $db->statement('CREATE TEMP TABLE cat_reloj_prueba (id_intento_cat bigint primary key, iniciado_en timestamptz, vence_en timestamptz, finalizado_en timestamptz, created_at timestamp, updated_at timestamp) ON COMMIT DROP');
            $db->statement('CREATE TEMP TABLE cat_reloj_respuesta_prueba (id_respuesta_cat bigint primary key, respondida_en timestamptz) ON COMMIT DROP');
            $inicio = CarbonImmutable::parse('2026-10-19T17:21:02Z');
            $intento = (new IntentoCat)->setConnection('cat_clock_pgsql')->setTable('cat_reloj_prueba');
            $intento->forceFill(['id_intento_cat' => 1, 'iniciado_en' => $inicio,
                'vence_en' => $inicio->addMinutes(30), 'finalizado_en' => null])->save();
            $respuesta = (new RespuestaCat)->setConnection('cat_clock_pgsql')->setTable('cat_reloj_respuesta_prueba');
            $respuesta->forceFill(['id_respuesta_cat' => 1, 'respondida_en' => $inicio->addSeconds(9)])->save();

            $this->assertSame($inicio->getTimestamp(), $intento->fresh()->iniciado_en->getTimestamp());
            $this->assertSame(1800.0, $intento->fresh()->iniciado_en->diffInSeconds($intento->fresh()->vence_en));
            $this->assertSame($inicio->getTimestamp() + 1800, $intento->fresh()->vence_en->getTimestamp());
            $this->assertSame($inicio->getTimestamp() + 9, $respuesta->fresh()->respondida_en->getTimestamp());

            // Reproduce el fallo anterior: la misma hora sin offset gana 14400 segundos.
            $naive = $db->selectOne('select extract(epoch from (?::timestamptz)) as instante', ['2026-10-19 17:21:02']);
            $this->assertSame(14400, (int) $naive->instante - $inicio->getTimestamp());

            $db->statement("SET LOCAL TIME ZONE 'UTC'");
            $this->assertSame($inicio->getTimestamp(), $intento->fresh()->iniciado_en->getTimestamp());
            $intento->finalizado_en = $inicio->addMinutes(30);
            $intento->save();
            $db->statement("SET LOCAL TIME ZONE 'America/La_Paz'");
            $this->assertSame($inicio->getTimestamp() + 1800, $intento->fresh()->finalizado_en->getTimestamp());
        } finally {
            $db->rollBack();
        }
    }
}
