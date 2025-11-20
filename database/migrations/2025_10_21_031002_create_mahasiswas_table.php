<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('mahasiswas', function (Blueprint $table) {
            // PK kustom
            $table->bigIncrements('mahasiswa_id');

            // FK ke users(id)
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // FK ke jurusans(id)
            $table->foreignId('jurusan_id')
                ->nullable()
                ->constrained('jurusans')
                ->nullOnDelete();

            // Kolom identitas mahasiswa (pakai nama yang kamu inginkan)
            $table->string('mahasiswa_name', 120);
            $table->string('mahasiswa_nim', 30)->unique();
            $table->year('mahasiswa_tahun_angkatan')->nullable();

            $table->timestamps();

            $table->index(['mahasiswa_name', 'mahasiswa_nim']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mahasiswas');
    }
};
