<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rws', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('number');
            $table->uuid('village_id');
            $table->timestamps();

            $table->unique(['number', 'village_id']);
            $table->foreign('village_id')->references('id')->on('villages')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rws');
    }
};
