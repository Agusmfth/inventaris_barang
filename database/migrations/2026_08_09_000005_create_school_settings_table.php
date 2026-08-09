<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('school_settings', function (Blueprint $table) {
            $table->id();
            $table->string('school_name');
            $table->string('npsn', 30)->nullable();
            $table->text('address')->nullable();
            $table->string('village')->nullable();
            $table->string('district')->nullable();
            $table->string('city')->nullable();
            $table->string('province')->nullable();
            $table->string('postal_code', 10)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('principal_name')->nullable();
            $table->string('logo')->nullable();
            $table->string('inventory_label_title')->nullable();
            $table->string('inventory_label_footer')->nullable();
            $table->timestamps();
        });
        DB::table('school_settings')->insert([
            'school_name'=>'SD Negeri Sayar', 'inventory_label_title'=>'BARANG INVENTARIS',
            'inventory_label_footer'=>'JAGA & GUNAKAN DENGAN BAIK', 'created_at'=>now(), 'updated_at'=>now(),
        ]);
    }
    public function down(): void { Schema::dropIfExists('school_settings'); }
};
