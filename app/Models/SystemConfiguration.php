<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemConfiguration extends Model
{
    use HasFactory, HasUuids;

    protected $guarded = [];

    protected $casts = [
        'ai_api_keys' => 'array',
        'extra' => 'array',
    ];

    /**
     * Retrieve the active singleton configuration row, or initialize from environment defaults.
     */
    public static function current(): self
    {
        $config = self::first();

        if (! $config) {
            $initialKeys = [];
            $envKey = env('AI_API_KEY') ?: env('GEMINI_API_KEY');
            if (! empty($envKey)) {
                $initialKeys[] = trim($envKey);
            }

            $config = self::create([
                // AI Settings
                'ai_provider' => env('AI_PROVIDER', 'gemini'),
                'ai_model' => env('AI_MODEL', env('GEMINI_MODEL', 'gemini-2.5-flash')),
                'ai_api_keys' => $initialKeys,
                'ai_base_url' => env('AI_BASE_URL'),

                // WhatsApp Settings
                'whatsapp_bot_url' => env('WHATSAPP_BOT_URL', 'http://127.0.0.1:3000'),
                'whatsapp_bot_number' => env('WHATSAPP_BOT_NUMBER', '628981295171'),
                'whatsapp_api_token' => env('WHATSAPP_API_TOKEN'),

                // Telegram Settings
                'telegram_bot_token' => env('TELEGRAM_BOT_TOKEN', '8720264415:AAH0GF1lUXQxSpwEoeKDJawe0MkGs6mdRsk'),
                'telegram_bot_username' => env('TELEGRAM_BOT_USERNAME', 'cevltBot'),

                // Mail Settings
                'mail_mailer' => env('MAIL_MAILER', 'smtp'),
                'mail_host' => env('MAIL_HOST', '127.0.0.1'),
                'mail_port' => (int) env('MAIL_PORT', 1025),
                'mail_username' => env('MAIL_USERNAME'),
                'mail_password' => env('MAIL_PASSWORD'),
                'mail_encryption' => env('MAIL_ENCRYPTION', null),
                'mail_from_address' => env('MAIL_FROM_ADDRESS', 'noreply@siwil.id'),
                'mail_from_name' => env('MAIL_FROM_NAME', 'SIWIL AI'),
            ]);
        }

        return $config;
    }

    /**
     * Get clean array of AI API Keys with fallback to .env.
     *
     * @return array<int, string>
     */
    public static function getAiApiKeys(): array
    {
        $config = self::first();
        $keys = $config?->ai_api_keys ?? [];

        if (empty($keys)) {
            $envKey = env('AI_API_KEY') ?: env('GEMINI_API_KEY');
            if (! empty($envKey)) {
                $keys[] = trim($envKey);
            }
        }

        return array_values(array_filter(array_map('trim', (array) $keys)));
    }

    /**
     * Mask sensitive strings like API keys or tokens for safe frontend presentation.
     */
    public static function maskSecret(?string $secret, int $visibleStart = 6, int $visibleEnd = 4): string
    {
        if (empty($secret)) {
            return '';
        }

        $length = strlen($secret);
        if ($length <= ($visibleStart + $visibleEnd)) {
            return str_repeat('•', max(8, $length));
        }

        $start = substr($secret, 0, $visibleStart);
        $end = substr($secret, -$visibleEnd);
        $dots = str_repeat('•', min(14, max(8, $length - ($visibleStart + $visibleEnd))));

        return "{$start}{$dots}{$end}";
    }

    /**
     * Apply configuration values into Laravel runtime config dynamically.
     */
    public function syncToRuntimeConfig(): void
    {
        // 1. AI Config
        $keys = (array) ($this->ai_api_keys ?? []);
        $primaryKey = ! empty($keys) ? $keys[0] : null;

        config([
            'services.ai.provider' => $this->ai_provider ?: 'gemini',
            'services.ai.model' => $this->ai_model ?: 'gemini-2.5-flash',
            'services.ai.base_url' => $this->ai_base_url,
            'services.ai.api_key' => $primaryKey,
            'services.gemini.model' => $this->ai_model ?: 'gemini-2.5-flash',
            'services.gemini.api_key' => $primaryKey,
        ]);

        // 2. WhatsApp Config
        config([
            'services.whatsapp.bot_url' => $this->whatsapp_bot_url,
            'services.whatsapp.bot_number' => $this->whatsapp_bot_number,
        ]);

        // 3. Telegram Config
        config([
            'services.telegram.bot_token' => $this->telegram_bot_token,
            'services.telegram.bot_username' => $this->telegram_bot_username,
        ]);

        // 4. Mail Config
        $mailEncryption = $this->mail_encryption;
        if (in_array($mailEncryption, ['none', 'null', ''], true)) {
            $mailEncryption = null;
        }

        config([
            'mail.default' => $this->mail_mailer ?: 'smtp',
            'mail.mailers.smtp.host' => $this->mail_host,
            'mail.mailers.smtp.port' => $this->mail_port,
            'mail.mailers.smtp.username' => $this->mail_username,
            'mail.mailers.smtp.password' => $this->mail_password,
            'mail.mailers.smtp.encryption' => $mailEncryption,
            'mail.from.address' => $this->mail_from_address,
            'mail.from.name' => $this->mail_from_name,
        ]);
    }
}
