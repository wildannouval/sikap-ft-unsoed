<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('signatories', function (Blueprint $table) {
            $table->id();
            $table->string('name');                // Nama lengkap penandatangan
            $table->string('position');            // Jabatan
            $table->string('nip')->nullable();     // NIP (jika ada)
            $table->string('signature_path')->nullable(); // path tanda tangan
            $table->boolean('is_active')->default(true);
            $table->date('valid_from')->nullable();
            $table->date('valid_until')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('signatories');
    }
};
