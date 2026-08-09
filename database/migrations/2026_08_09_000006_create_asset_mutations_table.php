<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void { Schema::create('asset_mutations', function(Blueprint $table){ $table->id(); $table->foreignId('asset_id')->constrained('assets')->restrictOnDelete(); $table->foreignId('from_location_id')->constrained('asset_locations')->restrictOnDelete(); $table->foreignId('to_location_id')->constrained('asset_locations')->restrictOnDelete(); $table->date('mutation_date')->index(); $table->string('reason')->nullable(); $table->text('notes')->nullable(); $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete(); $table->timestamps(); $table->index(['asset_id','mutation_date']); }); }
    public function down(): void { Schema::dropIfExists('asset_mutations'); }
};
