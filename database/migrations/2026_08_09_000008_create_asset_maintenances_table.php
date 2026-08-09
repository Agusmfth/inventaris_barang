<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('asset_maintenances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->restrictOnDelete();
            $table->date('reported_date')->index();
            $table->text('issue');
            $table->enum('initial_condition', ['baik', 'rusak_ringan', 'rusak_berat']);
            $table->enum('maintenance_status', ['menunggu', 'diproses', 'selesai', 'dibatalkan'])->default('menunggu')->index();
            $table->string('service_location')->nullable();
            $table->string('technician')->nullable();
            $table->decimal('estimated_cost', 15, 2)->default(0);
            $table->decimal('actual_cost', 15, 2)->default(0);
            $table->date('start_date')->nullable();
            $table->date('completed_date')->nullable();
            $table->text('action_taken')->nullable();
            $table->enum('final_condition', ['baik', 'rusak_ringan', 'rusak_berat'])->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['asset_id', 'maintenance_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_maintenances');
    }
};
