<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('budgets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('village_id')->constrained()->cascadeOnDelete();
            $table->year('year');
            $table->enum('type', ['PENDAPATAN', 'BELANJA', 'PEMBIAYAAN']);
            $table->string('category'); // e.g. Pendapatan Asli Desa, Bidang Pelaksanaan Pembangunan
            $table->string('description')->nullable();
            $table->decimal('amount_budget', 15, 2)->default(0);
            $table->decimal('amount_realized', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budgets');
    }
};
