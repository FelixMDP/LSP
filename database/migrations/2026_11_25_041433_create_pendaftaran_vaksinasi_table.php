
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pendaftaran_vaksinasi', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('jadwal_id')
                ->constrained('jadwal_vaksinasi')
                ->restrictOnDelete();

            $table->string('status_pendaftaran')->default('pending');

            $table->text('alasan_penolakan')->nullable();

            $table->foreignId('verified_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('verified_at')->nullable();

            $table->timestamps();

            $table->unique(['user_id', 'jadwal_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pendaftaran_vaksinasi');
    }
};
