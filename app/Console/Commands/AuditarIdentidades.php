<?php

namespace App\Console\Commands;

use App\Domains\Postulantes\Services\AuditoriaIdentidadService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class AuditarIdentidades extends Command
{
    protected $signature = 'intelecta:auditar-identidades {--salida= : Directorio privado NUEVO para JSON y matriz Markdown}';

    protected $description = 'Inventario completo de expedientes y cuentas Estudiante, sin escribir en la BD';

    public function handle(AuditoriaIdentidadService $audit): int
    {
        // No invoca login, seeders, sincronización financiera ni endpoints POST.
        config(['cache.default' => 'array']);
        $db = DB::connection();
        $db->beginTransaction();
        try {
            if ($db->getDriverName() === 'pgsql') {
                $db->statement('SET TRANSACTION ISOLATION LEVEL REPEATABLE READ, READ ONLY');
            }
            $report = $audit->generar();
        } finally {
            $db->rollBack();
        }
        $this->line(json_encode($report['resumen'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
        if ($directory = $this->option('salida')) {
            if (file_exists($directory)) {
                throw new RuntimeException('Usa un directorio nuevo; no se sobrescriben informes previos.');
            }
            if (! mkdir($directory, 0700, true)) {
                throw new RuntimeException('No se pudo crear el directorio privado.');
            }
            $json = fn ($data) => json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
            $this->write($directory.'/inventario.json', $json($report));
            $manifest = ['destino' => $report['destino'], 'fuentes_sha256' => $report['fuentes_sha256'],
                'filas' => array_values(array_filter($report['filas'], fn ($row) => $row['clasificacion'] === 'A' && $row['user_id_actual'] === null))];
            $this->write($directory.'/propuesta.json', $json($manifest));
            $md = "# Matriz completa de identidad — pendiente de autorización\n\n".$report['limite_origen']."\n\n";
            $md .= "| user_id propuesto | id_post | Nombre | Correo | Cuenta | Expediente | Matrícula / inscripción | Habilitación / evaluación | Clase | Evidencia | Acción / confianza / revisión |\n|---|---|---|---|---|---|---|---|---|---|---|\n";
            foreach ($report['filas'] as $row) {
                $cell = fn ($value) => str_replace(["\r", "\n", '|'], [' ', ' ', '/'], (string) $value);
                $mat = collect($row['matriculas'] ?? [])->map(fn ($m) => $m['codigo_mat'].' ('.$m['estado_matricula_mat'].'), insc '.$m['id_insc'])->implode('; ');
                $hab = collect($row['habilitaciones'] ?? [])->pluck('estado_hab')->implode(', ').' / '.(($row['evaluacion_administrativa'] ?? false) ? 'permitida' : 'denegada');
                $md .= '| '.implode(' | ', array_map($cell, [$row['user_id'] ?? '—', $row['id_post'] ?? '—', $row['nombre'], $row['correo'], $row['estado_usuario'] ?? 'sin cuenta', $row['estado_postulante'] ?? '—', $mat, $hab, $row['clasificacion'], $row['evidencia'], $row['accion_propuesta'].' / '.$row['confianza'].' / '.$row['revision']]))." |\n";
            }
            $this->write($directory.'/matriz.md', $md);
            $this->info('Informes privados: '.$directory);
        }

        return self::SUCCESS;
    }

    private function write(string $path, string $content): void
    {
        if (file_put_contents($path, $content, LOCK_EX) === false) {
            throw new RuntimeException('No se pudo escribir el informe: '.$path);
        }
        @chmod($path, 0600);
    }
}
