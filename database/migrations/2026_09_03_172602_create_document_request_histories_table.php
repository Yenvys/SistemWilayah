<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_request_histories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('document_request_id');
            $table->string('status');
            $table->text('notes')->nullable();
            $table->uuid('actor_id');
            $table->timestamps();

            $table->foreign('document_request_id')->references('id')->on('document_requests')->onDelete('cascade');
            $table->foreign('actor_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_request_histories');
    }
};
