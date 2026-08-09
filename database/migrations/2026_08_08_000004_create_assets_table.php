<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('asset_code')->unique();
            $table->string('name');
            $table->foreignId('category_id')->constrained('asset_categories')->restrictOnDelete();
            $table->foreignId('location_id')->constrained('asset_locations')->restrictOnDelete();
            $table->foreignId('funding_source_id')->nullable()->constrained('funding_sources')->nullOnDelete();
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('serial_number')->nullable()->index();
            $table->unsignedSmallInteger('acquisition_year');
            $table->date('acquisition_date')->nullable();
            $table->decimal('acquisition_price', 18, 2)->default(0);
            $table->unsignedInteger('quantity')->default(1);
            $table->enum('condition', ['baik', 'rusak_ringan', 'rusak_berat'])->default('baik')->index();
            $table->enum('status', ['tersedia', 'dipinjam', 'perawatan', 'dihapus'])->default('tersedia')->index();
            $table->string('photo')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['category_id', 'condition']);
            $table->index(['location_id', 'status']);
            $table->index('acquisition_year');
        });
    }
    public function down(): void { Schema::dropIfExists('assets'); }
};
