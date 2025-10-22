<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('signatories', function (Blueprint $table) {
            $drops = [];
            if (Schema::hasColumn('signatories', 'is_active'))  $drops[] = 'is_active';
            if (Schema::hasColumn('signatories', 'valid_from')) $drops[] = 'valid_from';

            if (!empty($drops)) {
                $table->dropColumn($drops);
            }
        });
    }

    public function down(): void
    {
        Schema::table('signatories', function (Blueprint $table) {
            if (!Schema::hasColumn('signatories', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('nip');
            }
            if (!Schema::hasColumn('signatories', 'valid_from')) {
                $table->date('valid_from')->nullable()->after('is_active');
            }
        });
    }
};
