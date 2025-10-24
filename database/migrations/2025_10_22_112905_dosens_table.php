<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('dosens', function (Blueprint $table) {
            $table->id();
            // Jika setiap dosen punya akun login sendiri, map ke users:
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nama');
            $table->string('nip')->nullable();
            $table->string('nidn')->nullable();
            $table->string('jabatan')->nullable(); // mis: Lektor, Kaprodi, dsb.
            $table->string('email')->nullable();
            $table->string('telepon')->nullable();
            $table->timestamps();

            $table->index(['nama', 'nip', 'nidn']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('dosens');
    }
};
