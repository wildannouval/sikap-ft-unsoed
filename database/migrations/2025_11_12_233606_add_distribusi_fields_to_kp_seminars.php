<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('kp_seminars', function (Blueprint $table) {
            $table->string('distribusi_proof_path')->nullable()->after('ba_scan_path');
            $table->timestamp('distribusi_uploaded_at')->nullable()->after('distribusi_proof_path');
        });
    }

    public function down(): void
    {
        Schema::table('kp_seminars', function (Blueprint $table) {
            $table->dropColumn(['distribusi_proof_path', 'distribusi_uploaded_at']);
        });
    }
};
