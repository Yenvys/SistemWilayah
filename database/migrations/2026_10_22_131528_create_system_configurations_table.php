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
        Schema::create('system_configurations', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // 1. AI Configuration
            $table->string('ai_provider')->default('gemini');
            $table->string('ai_model')->default('gemini-2.5-flash');
            $table->json('ai_api_keys')->nullable(); // Array of fallback API keys
            $table->string('ai_base_url')->nullable();

            // 2. WhatsApp Gateway Configuration
            $table->string('whatsapp_bot_url')->nullable();
            $table->string('whatsapp_bot_number')->nullable();
            $table->string('whatsapp_api_token')->nullable();

            // 3. Telegram Gateway Configuration
            $table->string('telegram_bot_token')->nullable();
            $table->string('telegram_bot_username')->nullable();

            // 4. Email (SMTP) Configuration
            $table->string('mail_mailer')->default('smtp');
            $table->string('mail_host')->nullable();
            $table->integer('mail_port')->default(587);
            $table->string('mail_username')->nullable();
            $table->text('mail_password')->nullable();
            $table->string('mail_encryption')->nullable();
            $table->string('mail_from_address')->nullable();
            $table->string('mail_from_name')->nullable();

            // Extra / Extended options
            $table->json('extra')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_configurations');
    }
};
