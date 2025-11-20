<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('room_number', 50);   // nomor ruangan (mis. 201, A-301)
            $table->string('building', 100);     // nama/gedung (mis. Gedung A)
            $table->string('notes')->nullable(); // opsional catatan
            $table->timestamps();

            $table->unique(['room_number', 'building']); // kombinasi unik
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
