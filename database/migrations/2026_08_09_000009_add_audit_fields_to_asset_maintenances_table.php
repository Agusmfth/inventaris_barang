<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('asset_maintenances', function (Blueprint $table) {
            $table->dateTime('started_at')->nullable()->after('start_date');
            $table->foreignId('started_by')->nullable()->after('started_at')->constrained('users')->nullOnDelete();
            $table->dateTime('cancelled_at')->nullable()->after('completed_by');
            $table->foreignId('cancelled_by')->nullable()->after('cancelled_at')->constrained('users')->nullOnDelete();
        });
        DB::table('asset_maintenances')->whereNotNull('start_date')->update(['started_at'=>DB::raw('start_date'), 'started_by'=>DB::raw('created_by')]);
    }

    public function down(): void
    {
        Schema::table('asset_maintenances', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cancelled_by');
            $table->dropColumn('cancelled_at');
            $table->dropConstrainedForeignId('started_by');
            $table->dropColumn('started_at');
        });
    }
};
