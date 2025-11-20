<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('dosens', function (Blueprint $table) {
            $table->bigIncrements('dosen_id');

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('dosen_name', 120);
            $table->string('dosen_nip', 50)->nullable()->unique();

            $table->foreignId('jurusan_id')
                ->nullable()
                ->constrained('jurusans')
                ->nullOnDelete();

            $table->boolean('is_komisi_kp')->default(false);

            $table->timestamps();

            $table->index(['dosen_name', 'dosen_nip']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dosens');
    }
};
