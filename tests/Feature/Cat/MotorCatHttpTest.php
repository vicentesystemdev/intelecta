<?php

namespace Tests\Feature\Cat;

use App\Domains\Cat\DTOs\ContextoCat;
use App\Domains\Cat\Exceptions\MotorCatNoDisponible;
use App\Domains\Cat\Services\ContratoCat;
use App\Domains\Cat\Services\MotorCatHttp;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\ContextoCatHttp;
use Tests\TestCase;

class MotorCatHttpTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['services.cat.url' => 'http://127.0.0.1:8010', 'services.cat.token' => 'credencial-ficticia-exclusiva-unitarias']);
        Http::preventStrayRequests();
    }

    private function response(ContextoCat $c): array
    {
        $r = app(ContratoCat::class)->solicitud($c);

        return ['contrato_version' => '1', 'id_intento_cat' => $c->id_intento_cat, 'revision' => $c->revision,
            'motor_version' => ContratoCat::MOTOR, 'banco_version' => $r['banco']['banco_version'],
            'parametros_version' => ContratoCat::PARAMETROS, 'theta' => 0.0, 'sd' => 1.0,
            'intervalo' => [-1.96, 1.96], 'respondidas' => count($c->historial), 'siguiente_id_preg' => 8,
            'terminado' => false, 'motivo' => null];
    }

    public function test_canonical_hash_matches_official_manifest_and_is_distinct_from_content(): void
    {
        $c = ContextoCatHttp::crear();
        $contrato = app(ContratoCat::class);
        $request = $contrato->solicitud($c);
        $this->assertSame(ContratoCat::HASH, $request['banco']['parametros_hash']);
        $this->assertSame(ContratoCat::HASH, $contrato->parametrosHash(array_reverse($request['banco']['items'])));
        $this->assertNotSame($request['banco']['parametros_hash'], $request['banco']['banco_version']);
        $s = $c->snapshot;
        $s['items'][0]['enunciado'] = 'Otro contenido';
        $other = $contrato->solicitud(new ContextoCat(501, 0, $s, [], null));
        $this->assertSame($request['banco']['parametros_hash'], $other['banco']['parametros_hash']);
        $this->assertNotSame($request['banco']['banco_version'], $other['banco']['banco_version']);
    }

    public function test_exact_request_and_header_without_private_content(): void
    {
        $c = ContextoCatHttp::crear();
        Http::fake(['127.0.0.1:8010/*' => Http::response($this->response($c))]);
        $d = app(MotorCatHttp::class)->calcular($c);
        $this->assertSame(8, $d->siguienteId);
        Http::assertSent(function ($r) {
            $this->assertSame('http://127.0.0.1:8010/api/integracion/cat/evaluar', $r->url());
            $this->assertTrue($r->hasHeader('X-Service-Token', 'credencial-ficticia-exclusiva-unitarias'));
            $this->assertSame(['contrato_version', 'id_intento_cat', 'revision', 'motor_version', 'banco', 'config', 'historial'], array_keys($r->data()));
            foreach ($r['banco']['items'] as $item) {
                $this->assertSame(['id_preg', 'a', 'b', 'c'], array_keys($item));
            }
            foreach (['Clave privada', 'Enunciado privado', 'alternativas', 'id_alt', 'snapshot'] as $private) {
                $this->assertStringNotContainsString($private, $r->body());
            }

            return true;
        });
        Http::assertSentCount(1);
    }

    public function test_changed_parameters_are_rejected_before_network(): void
    {
        Http::fake();
        $c = ContextoCatHttp::crear();
        $s = $c->snapshot;
        $s['items'][0]['parametros']['a'] = 2;
        try {
            app(MotorCatHttp::class)->calcular(new ContextoCat(501, 0, $s, [], null));
            $this->fail('Debió rechazar el manifiesto');
        } catch (MotorCatNoDisponible $e) {
            $this->assertSame('parametros_hash_incompatible', $e->codigo);
        }
        Http::assertNothingSent();
    }

    #[DataProvider('errores')]
    public function test_remote_errors_and_transport_do_not_disclose_token(int $status): void
    {
        Http::fake(['*' => $status === 0 ? Http::failedConnection() : Http::response(['error' => ['codigo' => 'prueba', 'mensaje' => config('services.cat.token')]], $status)]);
        try {
            app(MotorCatHttp::class)->calcular(ContextoCatHttp::crear());
            $this->fail('Debió quedar recuperable');
        } catch (MotorCatNoDisponible $e) {
            $this->assertStringNotContainsString(config('services.cat.token'), (string) $e);
            $this->assertNull($e->getPrevious());
            $this->assertSame($status === 0 ? 'fallo_transporte' : 'http_'.$status, $e->codigo);
        }
        Http::assertSentCount(1);
    }

    public static function errores(): array
    {
        return [[0], [401], [503], [409], [422], [413], [302]];
    }

    #[DataProvider('camposInvalidos')]
    public function test_rejects_invalid_echo_and_metric_fields(string $field, mixed $value): void
    {
        $c = ContextoCatHttp::crear();
        $r = $this->response($c);
        $r[$field] = $value;
        Http::fake(['*' => Http::response($r)]);
        $this->expectException(MotorCatNoDisponible::class);
        app(MotorCatHttp::class)->calcular($c);
    }

    public static function camposInvalidos(): array
    {
        return [['id_intento_cat', 502], ['revision', 1], ['revision', '0'], ['contrato_version', '2'],
            ['motor_version', 'otro'], ['banco_version', 'otra'], ['parametros_version', 'otra'], ['respondidas', 1],
            ['theta', '0'], ['sd', -1], ['intervalo', [2, 1]], ['siguiente_id_preg', 17],
            ['siguiente_id_preg', '8'], ['terminado', true], ['motivo', 'motivo inesperado']];
    }

    public function test_missing_token_fails_closed_before_network(): void
    {
        config(['services.cat.token' => '']);
        Http::fake();
        try {
            app(MotorCatHttp::class)->calcular(ContextoCatHttp::crear());
            $this->fail('Debió exigir token');
        } catch (MotorCatNoDisponible $e) {
            $this->assertSame('servicio_no_configurado', $e->codigo);
        }
        Http::assertNothingSent();
    }
}
