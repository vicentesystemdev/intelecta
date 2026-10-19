<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('instrumentos_cat', function (Blueprint $table) {
            $table->id('id_instrumento_cat');
            $table->string('codigo')->unique();
            $table->string('nombre');
            $table->string('version');
            $table->string('version_parametros');
            $table->boolean('demo')->default(true);
            $table->enum('estado', ['activo', 'retirado'])->default('activo');
            $table->unsignedSmallInteger('duracion_minutos');
            $table->jsonb('configuracion');
            $table->timestamps();
        });
        Schema::create('instrumento_cat_preguntas', function (Blueprint $table) {
            $table->foreignId('id_instrumento_cat')->constrained('instrumentos_cat', 'id_instrumento_cat')->restrictOnDelete();
            $table->foreignId('id_preg')->constrained('preguntas', 'id_preg')->restrictOnDelete();
            $table->double('a');
            $table->double('b');
            $table->double('c');
            $table->string('origen');
            $table->primary(['id_instrumento_cat', 'id_preg']);
        });
        Schema::create('intentos_cat', function (Blueprint $table) {
            $table->id('id_intento_cat');
            $table->foreignId('id_instrumento_cat')->constrained('instrumentos_cat', 'id_instrumento_cat')->restrictOnDelete();
            $table->foreignId('id_post')->constrained('postulantes', 'id_post')->restrictOnDelete();
            $table->enum('estado', ['en_progreso', 'finalizada', 'vencida'])->default('en_progreso');
            $table->jsonb('snapshot');
            $table->string('huella_snapshot', 64);
            $table->timestampTz('iniciado_en');
            $table->timestampTz('vence_en');
            $table->timestampTz('finalizado_en')->nullable();
            $table->unsignedInteger('revision')->default(0);
            $table->unsignedBigInteger('id_preg_actual')->nullable();
            $table->boolean('calculo_pendiente')->default(true);
            $table->string('error_motor')->nullable();
            $table->string('version_motor')->nullable();
            $table->jsonb('resultado_cat')->nullable();
            $table->timestamps();
            $table->index(['id_post', 'iniciado_en']);
        });
        Schema::create('respuestas_cat', function (Blueprint $table) {
            $table->id('id_respuesta_cat');
            $table->foreignId('id_intento_cat')->constrained('intentos_cat', 'id_intento_cat')->restrictOnDelete();
            // Referencias al snapshot: no FK a alternativas editables/recreadas.
            $table->unsignedBigInteger('id_preg');
            $table->unsignedBigInteger('id_alt');
            $table->unsignedInteger('revision');
            $table->boolean('correcta');
            $table->timestampTz('respondida_en');
            $table->jsonb('metricas_cat')->nullable();
            $table->unique(['id_intento_cat', 'id_preg']);
            $table->unique(['id_intento_cat', 'revision']);
        });
        DB::statement("CREATE UNIQUE INDEX intentos_cat_abierto_unique ON intentos_cat (id_post, id_instrumento_cat) WHERE estado = 'en_progreso'");
        if (DB::getDriverName() === 'pgsql') {
            DB::unprepared(<<<'SQL'
                CREATE FUNCTION intelecta_cat_intento_inmutable() RETURNS trigger LANGUAGE plpgsql AS $$
                BEGIN
                    IF NEW.snapshot IS DISTINCT FROM OLD.snapshot
                       OR NEW.huella_snapshot IS DISTINCT FROM OLD.huella_snapshot
                       OR NEW.id_post IS DISTINCT FROM OLD.id_post
                       OR NEW.id_instrumento_cat IS DISTINCT FROM OLD.id_instrumento_cat
                       OR NEW.iniciado_en IS DISTINCT FROM OLD.iniciado_en
                       OR NEW.vence_en IS DISTINCT FROM OLD.vence_en
                       OR (OLD.version_motor IS NOT NULL AND NEW.version_motor IS DISTINCT FROM OLD.version_motor)
                    THEN RAISE EXCEPTION 'El snapshot, identidad, plazo y motor fijado del intento CAT son inmutables';
                    END IF;
                    RETURN NEW;
                END;
                $$;
                CREATE TRIGGER intentos_cat_inmutable BEFORE UPDATE ON intentos_cat
                FOR EACH ROW EXECUTE FUNCTION intelecta_cat_intento_inmutable();
                ALTER TABLE instrumentos_cat ADD CONSTRAINT instrumentos_cat_duracion_check CHECK (duracion_minutos BETWEEN 1 AND 480);
                ALTER TABLE instrumento_cat_preguntas ADD CONSTRAINT instrumento_cat_parametros_check CHECK (a > 0 AND c >= 0 AND c < 1);
                ALTER TABLE intentos_cat ADD CONSTRAINT intentos_cat_plazo_check CHECK (vence_en > iniciado_en AND revision BETWEEN 0 AND 10);
                ALTER TABLE respuestas_cat ADD CONSTRAINT respuestas_cat_revision_check CHECK (revision BETWEEN 1 AND 10);
                SQL);
        }
    }

    public function down(): void
    {
        // Reversión exclusivamente de las tablas nuevas; nunca ejecutarla con intentos que deban conservarse.
        Schema::dropIfExists('respuestas_cat');
        Schema::dropIfExists('intentos_cat');
        Schema::dropIfExists('instrumento_cat_preguntas');
        Schema::dropIfExists('instrumentos_cat');
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('DROP FUNCTION IF EXISTS intelecta_cat_intento_inmutable()');
        }
    }
};
