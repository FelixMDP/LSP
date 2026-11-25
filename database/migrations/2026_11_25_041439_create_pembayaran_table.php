
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembayaran', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pendaftaran_id')
                ->unique()
                ->constrained('pendaftaran_vaksinasi')
                ->cascadeOnDelete();

            $table->decimal('jumlah_pembayaran', 12, 2);

            $table->string('bukti_pembayaran', 255);

            $table->string('status_pembayaran')->default('pending');

            $table->text('alasan_penolakan')->nullable();

            $table->foreignId('verified_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('verified_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayaran');
    }
};
