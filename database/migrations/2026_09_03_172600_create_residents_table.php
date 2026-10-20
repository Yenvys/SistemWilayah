<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('residents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nik')->unique();
            $table->string('full_name');
            $table->uuid('family_id')->nullable();
            $table->boolean('is_head_of_family')->default(false);
            $table->string('birth_place')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('gender')->nullable();
            $table->string('religion')->nullable();
            $table->string('marital_status')->nullable();
            $table->string('education')->nullable();
            $table->string('occupation')->nullable();
            $table->string('status')->default('AKTIF');
            $table->uuid('rt_id')->nullable();
            $table->timestamps();

            $table->foreign('family_id')->references('id')->on('families')->onDelete('set null');
            $table->foreign('rt_id')->references('id')->on('rts')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('residents');
    }
};
