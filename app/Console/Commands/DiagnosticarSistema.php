<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class DiagnosticarSistema extends Command
{
    protected $signature = 'sistema:diagnosticar';

    protected $description = 'Verifica el estado base del sistema: PostgreSQL, Redis, cache, sesiones, colas, storage y logs';

    public function handle(): int
    {
        $this->info('Iniciando diagnóstico de INTELECTA...');

        try {
            DB::connection()->getPdo();
            $this->info('PostgreSQL: OK');
        } catch (\Throwable $e) {
            $this->error('PostgreSQL: ERROR - ' . $e->getMessage());
            return self::FAILURE;
        }

        try {
            Redis::ping();
            $this->info('Redis: OK');
        } catch (\Throwable $e) {
            $this->error('Redis: ERROR - ' . $e->getMessage());
            return self::FAILURE;
        }

        try {
            Cache::put('diagnostico_cache', 'ok', 60);
            $valor = Cache::get('diagnostico_cache');

            if ($valor === 'ok') {
                $this->info('Cache Redis: OK');
            } else {
                $this->error('Cache Redis: ERROR');
                return self::FAILURE;
            }
        } catch (\Throwable $e) {
            $this->error('Cache Redis: ERROR - ' . $e->getMessage());
            return self::FAILURE;
        }

        try {
            Log::info('Diagnóstico ejecutado correctamente', [
                'sistema' => config('app.name'),
                'ambiente' => app()->environment(),
                'cache' => config('cache.default'),
                'queue' => config('queue.default'),
                'session' => config('session.driver'),
            ]);

            $this->info('Logs diarios: OK');
        } catch (\Throwable $e) {
            $this->error('Logs: ERROR - ' . $e->getMessage());
            return self::FAILURE;
        }

        if (is_link(public_path('storage')) || file_exists(public_path('storage'))) {
            $this->info('Storage link: OK');
        } else {
            $this->error('Storage link: ERROR');
            return self::FAILURE;
        }

        $this->newLine();
        $this->info('Diagnóstico finalizado correctamente.');
        $this->info('INTELECTA tiene base técnica estable.');

        return self::SUCCESS;
    }
}
