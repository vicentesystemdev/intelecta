<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grupos_academicos', function (Blueprint $table) {
            $table->foreignId('id_tutor_responsable')
                ->nullable()
                ->after('nivel_grupo')
                ->constrained('tutores_academicos', 'id_tutor')
                ->nullOnDelete();
        });

        DB::statement('DROP INDEX IF EXISTS cargos_nombre_normalizado_unique');

        Schema::table('materias', function (Blueprint $table) {
            $table->string('codigo_mat', 60)->change();
        });

        $driver = DB::connection()->getDriverName();
        if ($driver === 'pgsql') {
            DB::statement("CREATE UNIQUE INDEX cargos_nombre_normalizado_unique ON cargos (LOWER(REGEXP_REPLACE(BTRIM(nombre_cargo), '\\s+', ' ', 'g'))) ");
            DB::statement("CREATE UNIQUE INDEX colegios_nombre_normalizado_unique ON colegios (LOWER(REGEXP_REPLACE(BTRIM(nombre_col), '\\s+', ' ', 'g'))) ");
            DB::statement("CREATE UNIQUE INDEX universidades_nombre_normalizado_unique ON universidades (LOWER(REGEXP_REPLACE(BTRIM(nombre_uni), '\\s+', ' ', 'g'))) ");
            DB::statement("CREATE UNIQUE INDEX carreras_universidad_nombre_normalizado_unique ON carreras (id_uni, LOWER(REGEXP_REPLACE(BTRIM(nombre_car), '\\s+', ' ', 'g'))) ");
            DB::statement('CREATE UNIQUE INDEX materias_codigo_normalizado_unique ON materias (LOWER(BTRIM(codigo_mat)))');
            DB::statement("CREATE UNIQUE INDEX materias_nombre_normalizado_unique ON materias (LOWER(REGEXP_REPLACE(BTRIM(nombre_mat), '\\s+', ' ', 'g'))) ");
        } else {
            DB::statement('CREATE UNIQUE INDEX cargos_nombre_normalizado_unique ON cargos (LOWER(TRIM(nombre_cargo)))');
            DB::statement('CREATE UNIQUE INDEX colegios_nombre_normalizado_unique ON colegios (LOWER(TRIM(nombre_col)))');
            DB::statement('CREATE UNIQUE INDEX universidades_nombre_normalizado_unique ON universidades (LOWER(TRIM(nombre_uni)))');
            DB::statement('CREATE UNIQUE INDEX carreras_universidad_nombre_normalizado_unique ON carreras (id_uni, LOWER(TRIM(nombre_car)))');
            DB::statement('CREATE UNIQUE INDEX materias_codigo_normalizado_unique ON materias (LOWER(TRIM(codigo_mat)))');
            DB::statement('CREATE UNIQUE INDEX materias_nombre_normalizado_unique ON materias (LOWER(TRIM(nombre_mat)))');
        }
    }

    public function down(): void
    {
        foreach ([
            'cargos_nombre_normalizado_unique',
            'colegios_nombre_normalizado_unique',
            'universidades_nombre_normalizado_unique',
            'carreras_universidad_nombre_normalizado_unique',
            'materias_codigo_normalizado_unique',
            'materias_nombre_normalizado_unique',
        ] as $index) {
            DB::statement("DROP INDEX IF EXISTS {$index}");
        }

        Schema::table('grupos_academicos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('id_tutor_responsable');
        });

        DB::statement('CREATE UNIQUE INDEX cargos_nombre_normalizado_unique ON cargos (LOWER(nombre_cargo))');

        Schema::table('materias', function (Blueprint $table) {
            $table->string('codigo_mat', 10)->change();
        });
    }
};
