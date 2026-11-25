<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pendaftaran_vaksinasi', function (Blueprint $table) {
            $table->index('user_id', 'pendaftaran_vaksinasi_user_id_index');
            $table->dropUnique('pendaftaran_vaksinasi_user_id_jadwal_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pendaftaran_vaksinasi', function (Blueprint $table) {
            $table->unique(['user_id', 'jadwal_id']);
            $table->dropIndex('pendaftaran_vaksinasi_user_id_index');
        });
    }
};
