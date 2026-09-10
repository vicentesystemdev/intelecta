<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tutores_academicos', function (Blueprint $table): void {
            $table->unsignedBigInteger('personal_id')->nullable()->unique();
            $table->foreign('personal_id')->references('id_personal')->on('personal_institucional')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tutores_academicos', function (Blueprint $table): void {
            $table->dropForeign(['personal_id']);
            $table->dropUnique(['personal_id']);
            $table->dropColumn('personal_id');
        });
    }
};
