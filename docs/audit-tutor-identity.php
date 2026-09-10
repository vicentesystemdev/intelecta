<?php

// Read-only inventory. Hashes and IDs only; never exports credentials or personal/contact values.
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
set_exception_handler(function (Throwable $exception): void {
    fwrite(STDERR, $exception->getMessage().PHP_EOL);
    exit(1);
});
if (DB::getDriverName() !== 'pgsql') {
    throw new RuntimeException('Requiere PostgreSQL.');
}
DB::beginTransaction();
DB::statement('SET TRANSACTION ISOLATION LEVEL REPEATABLE READ READ ONLY');
try {
    $report = ['database' => DB::connection()->getDatabaseName(), 'tables' => []];
    foreach (DB::select("SELECT tablename FROM pg_tables WHERE schemaname='public' ORDER BY tablename") as $row) {
        $report['tables'][$row->tablename] = DB::selectOne('SELECT count(*) AS count, md5(string_agg(row_to_json(t)::text, \'\' ORDER BY row_to_json(t)::text)) AS fingerprint FROM public."'.$row->tablename.'" t');
    }
    $report['professional_fingerprint'] = DB::selectOne("SELECT md5(string_agg(row_to_json(t)::text, '' ORDER BY id_tutor)) AS hash FROM (SELECT id_tutor,especialidad_tutor,formacion_tutor,experiencia_tutor,estado_tutor,observacion_tutor,created_at,updated_at,deleted_at FROM tutores_academicos) t")->hash;
    $hasPersonal = Schema::hasColumn('tutores_academicos', 'personal_id');
    $hasLegacy = Schema::hasColumn('tutores_academicos', 'user_id');
    $report['legacy'] = $hasLegacy;
    $report['map'] = $hasPersonal
        ? DB::select('SELECT t.id_tutor,t.personal_id,p.user_id,t.estado_tutor,t.deleted_at,p.cargo_id,p.estado AS estado_personal FROM tutores_academicos t LEFT JOIN personal_institucional p ON p.id_personal=t.personal_id ORDER BY t.id_tutor')
        : DB::select('SELECT id_tutor,user_id,estado_tutor,deleted_at FROM tutores_academicos ORDER BY id_tutor');
    $report['incoming_fks'] = DB::select("SELECT conrelid::regclass::text AS tabla,conname,pg_get_constraintdef(oid) AS definition FROM pg_constraint WHERE contype='f' AND confrelid='tutores_academicos'::regclass ORDER BY conname");
    $report['tutor_constraints'] = DB::select("SELECT conname,pg_get_constraintdef(oid) AS definition FROM pg_constraint WHERE conrelid='tutores_academicos'::regclass ORDER BY conname");
    if ($hasPersonal) {
        $report['missing_personal'] = DB::table('tutores_academicos')->whereNull('personal_id')->count();
        $report['nullable'] = DB::selectOne("SELECT is_nullable FROM information_schema.columns WHERE table_schema='public' AND table_name='tutores_academicos' AND column_name='personal_id'")->is_nullable;
        if ($hasLegacy) {
            $report['copy_mismatches'] = DB::selectOne('SELECT count(*) AS total FROM tutores_academicos t JOIN personal_institucional p ON p.id_personal=t.personal_id WHERE t.user_id IS DISTINCT FROM p.user_id OR t.nombres_tutor IS DISTINCT FROM p.nombres OR t.apellidos_tutor IS DISTINCT FROM p.apellidos OR (t.ci_tutor IS NOT NULL AND t.ci_tutor IS DISTINCT FROM p.ci) OR (t.celular_tutor IS NOT NULL AND t.celular_tutor IS DISTINCT FROM p.celular) OR (t.correo_tutor IS NOT NULL AND t.correo_tutor IS DISTINCT FROM p.correo_contacto)')->total;
        }
    }
    echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR), PHP_EOL;
} finally {
    DB::rollBack();
}
