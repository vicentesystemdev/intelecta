<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('postulantes', function (Blueprint $table) {
            // Unknown historical birth dates remain NULL. Never backfill from edad_post.
            $table->date('fecha_nacimiento_post')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('postulantes', function (Blueprint $table) {
            $table->dropColumn('fecha_nacimiento_post');
        });
    }
};
