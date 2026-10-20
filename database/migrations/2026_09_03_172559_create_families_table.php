<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('families', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('kk_number')->unique();
            $table->string('address');
            $table->uuid('rt_id');
            $table->timestamps();

            $table->foreign('rt_id')->references('id')->on('rts')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('families');
    }
};
