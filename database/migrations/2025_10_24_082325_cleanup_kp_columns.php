<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('kerja_praktiks', function (Blueprint $table) {
            // --- Lepas FK lama bila ada, lalu drop kolomnya ---

            // komisi_id
            if (Schema::hasColumn('kerja_praktiks', 'komisi_id')) {
                // nama default FK: kerja_praktiks_komisi_id_foreign
                try { $table->dropForeign(['komisi_id']); } catch (\Throwable $e) {}
                $table->dropColumn('komisi_id');
            }

            // pembimbing_id (sudah tidak dipakai; diganti dosen_pembimbing_id)
            if (Schema::hasColumn('kerja_praktiks', 'pembimbing_id')) {
                try { $table->dropForeign(['pembimbing_id']); } catch (\Throwable $e) {}
                $table->dropColumn('pembimbing_id');
            }

            // --- Pastikan kolom dosen_pembimbing_id ada (tanpa FK dulu) ---
            if (! Schema::hasColumn('kerja_praktiks', 'dosen_pembimbing_id')) {
                $table->unsignedBigInteger('dosen_pembimbing_id')->nullable()->after('status');
                // FK akan ditambahkan di migration terpisah agar lebih aman
            }

            // Index status kalau belum ada
            try { $table->index('status'); } catch (\Throwable $e) {}
        });
    }

    public function down(): void
    {
        Schema::table('kerja_praktiks', function (Blueprint $table) {
            // kembalikan kolom lama sebagai nullable (tanpa FK)
            if (! Schema::hasColumn('kerja_praktiks', 'komisi_id')) {
                $table->unsignedBigInteger('komisi_id')->nullable()->after('status');
            }
            if (! Schema::hasColumn('kerja_praktiks', 'pembimbing_id')) {
                $table->unsignedBigInteger('pembimbing_id')->nullable()->after('status');
            }

            // Lepas FK & kolom dosen_pembimbing_id saat rollback
            if (Schema::hasColumn('kerja_praktiks', 'dosen_pembimbing_id')) {
                try { $table->dropForeign(['dosen_pembimbing_id']); } catch (\Throwable $e) {}
                $table->dropColumn('dosen_pembimbing_id');
            }

            // drop index status (ignore jika belum ada)
            try { $table->dropIndex(['status']); } catch (\Throwable $e) {}
        });
    }
};
