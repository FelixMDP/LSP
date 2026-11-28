<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('berita_kesehatan', function (Blueprint $table) {
            $table->id();
            $table->date('published_on')->unique();
            $table->string('category', 100);
            $table->string('title');
            $table->text('summary');
            $table->text('context');
            $table->string('source_name');
            $table->string('source_url', 512)->unique();
            $table->date('source_published_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('berita_kesehatan');
    }
};
