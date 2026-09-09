<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            // Frozen schema values: later enum changes must have their own migration.
            $table->enum('estado_cuenta', ['pendiente', 'activa', 'bloqueada'])->default('pendiente');
            $table->unsignedInteger('version_acceso')->default(0);
        });
        DB::table('users')->whereNotNull('email_verified_at')->update(['estado_cuenta' => 'activa']);
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE users ADD CONSTRAINT users_activa_verificada_check CHECK (estado_cuenta <> 'activa' OR email_verified_at IS NOT NULL)");
            DB::statement('ALTER TABLE users ADD CONSTRAINT users_version_acceso_check CHECK (version_acceso >= 0)');
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE users DROP CONSTRAINT users_activa_verificada_check');
            DB::statement('ALTER TABLE users DROP CONSTRAINT users_version_acceso_check');
        }
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['estado_cuenta', 'version_acceso']);
        });
    }
};
