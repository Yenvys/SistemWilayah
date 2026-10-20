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
        Schema::create('developments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('village_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('budget_allocated', 15, 2)->default(0);
            $table->string('source_of_fund')->nullable(); // DD, ADD, PADes, dll
            $table->enum('status', ['PLANNED', 'ONGOING', 'COMPLETED'])->default('PLANNED');
            $table->integer('progress_percentage')->default(0);
            $table->string('location_lat')->nullable();
            $table->string('location_lng')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('image_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('developments');
    }
};
