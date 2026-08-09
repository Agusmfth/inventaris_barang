<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('asset_disposals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->restrictOnDelete();
            $table->date('disposal_date')->index();
            $table->text('reason');
            $table->enum('disposal_method',['pemusnahan','penjualan','hibah','hilang','lainnya']);
            $table->enum('condition_at_disposal',['baik','rusak_ringan','rusak_berat']);
            $table->text('notes')->nullable();
            $table->string('document_number')->nullable();
            $table->string('document_file')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status',['diajukan','disetujui','ditolak'])->default('diajukan')->index();
            $table->dateTime('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
            $table->index(['asset_id','status']);
        });
    }
    public function down(): void { Schema::dropIfExists('asset_disposals'); }
};
