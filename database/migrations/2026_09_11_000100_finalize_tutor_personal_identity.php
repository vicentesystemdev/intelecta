<?php

use App\Domains\Academico\Services\MigrarIdentidadTutorService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // PostgreSQL keeps the service's table locks until this migration transaction commits.
        app(MigrarIdentidadTutorService::class)->assertReadyToFinalize();
        Schema::table('tutores_academicos', function (Blueprint $table): void {
            $table->unsignedBigInteger('personal_id')->nullable(false)->change();
            $table->dropForeign(['user_id']);
            $table->dropUnique(['user_id']);
            $table->dropUnique(['ci_tutor']);
            $table->dropColumn(['user_id', ...array_keys(MigrarIdentidadTutorService::FIELDS)]);
        });
    }

    public function down(): void
    {
        // Schema rollback reconstructs a snapshot of CURRENT Personal, not an old historical identity.
        Schema::table('tutores_academicos', function (Blueprint $table): void {
            $table->unsignedBigInteger('personal_id')->nullable()->change();
            $table->foreignId('user_id')->nullable()->unique()->constrained('users')->nullOnDelete();
            foreach (array_keys(MigrarIdentidadTutorService::FIELDS) as $field) {
                $table->string($field)->nullable();
            }
            $table->unique('ci_tutor');
        });
        foreach (DB::table('tutores_academicos')->get() as $tutor) {
            $person = DB::table('personal_institucional')->where('id_personal', $tutor->personal_id)->first();
            $values = ['user_id' => $person->user_id];
            foreach (MigrarIdentidadTutorService::FIELDS as $legacy => $field) {
                $values[$legacy] = $person->{$field};
            }
            DB::table('tutores_academicos')->where('id_tutor', $tutor->id_tutor)->update($values);
        }
        Schema::table('tutores_academicos', function (Blueprint $table): void {
            $table->string('nombres_tutor')->nullable(false)->change();
            $table->string('apellidos_tutor')->nullable(false)->change();
        });
    }
};
