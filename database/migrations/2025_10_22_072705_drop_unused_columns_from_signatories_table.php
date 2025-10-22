<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('signatories', function (Blueprint $table) {
            // 1) Lepas FK created_by (gunakan nama constraint atau array kolom)
            if (Schema::hasColumn('signatories', 'created_by')) {
                // Kedua cara di bawah valid; yang pertama pakai nama default Laravel.
                // $table->dropForeign('signatories_created_by_foreign');
                $table->dropForeign(['created_by']);
            }

            // 2) Drop kolom-kolom kalau ada
            $drops = [];
            if (Schema::hasColumn('signatories', 'signature_path')) $drops[] = 'signature_path';
            if (Schema::hasColumn('signatories', 'valid_until'))    $drops[] = 'valid_until';
            if (Schema::hasColumn('signatories', 'created_by'))     $drops[] = 'created_by';

            if (!empty($drops)) {
                $table->dropColumn($drops);
            }
        });
    }

    public function down(): void
    {
        Schema::table('signatories', function (Blueprint $table) {
            // Kembalikan kolom-kolom
            if (!Schema::hasColumn('signatories', 'signature_path')) {
                $table->string('signature_path')->nullable()->after('nip');
            }
            if (!Schema::hasColumn('signatories', 'valid_until')) {
                $table->date('valid_until')->nullable()->after('valid_from');
            }
            if (!Schema::hasColumn('signatories', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('is_active');

                // Tambahkan kembali FK ke users (opsional)
                $table->foreign('created_by')
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete();
            }
        });
    }
};
