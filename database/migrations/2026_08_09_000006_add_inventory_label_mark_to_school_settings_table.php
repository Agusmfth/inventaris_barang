<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('school_settings', function (Blueprint $table) {
            $table->string('inventory_label_mark', 60)->default('INVENTARIS SEKOLAH')->after('inventory_label_title');
        });
    }

    public function down(): void
    {
        Schema::table('school_settings', function (Blueprint $table) {
            $table->dropColumn('inventory_label_mark');
        });
    }
};
