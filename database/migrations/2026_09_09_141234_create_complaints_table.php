<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('complaints', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description');
            $table->string('location')->nullable();
            $table->string('photo_path')->nullable();
            $table->enum('status', ['PENDING', 'PROCESSING', 'RESOLVED', 'REJECTED'])->default('PENDING');
            $table->foreignUuid('rt_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('rw_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('village_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('complaints');
    }
};
