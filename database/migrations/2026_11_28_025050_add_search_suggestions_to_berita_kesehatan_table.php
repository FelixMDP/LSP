<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('berita_kesehatan', function (Blueprint $table): void {
            $table->longText('search_suggestions')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('berita_kesehatan', function (Blueprint $table): void {
            $table->dropColumn('search_suggestions');
        });
    }
};
