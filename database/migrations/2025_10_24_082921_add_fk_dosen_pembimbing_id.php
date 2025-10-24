<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Pastikan kolomnya ada dulu
        if (Schema::hasColumn('kerja_praktiks', 'dosen_pembimbing_id')) {
            Schema::table('kerja_praktiks', function (Blueprint $table) {
                // Lepas FK lama kalau pernah ada (agar tidak double)
                try { $table->dropForeign(['dosen_pembimbing_id']); } catch (\Throwable $e) {}
                // Tambahkan FK ke tabel dosens, on delete -> set null
                $table->foreign('dosen_pembimbing_id')
                      ->references('id')->on('dosens')
                      ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::table('kerja_praktiks', function (Blueprint $table) {
            try { $table->dropForeign(['dosen_pembimbing_id']); } catch (\Throwable $e) {}
        });
    }
};
