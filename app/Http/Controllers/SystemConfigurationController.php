<?php

namespace App\Http\Controllers;

use App\Models\AiKnowledge;
use App\Models\SystemConfiguration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SystemConfigurationController extends Controller
{
    public function index()
    {
        $config = SystemConfiguration::current();

        $rawKeys = (array) ($config->ai_api_keys ?? []);
        $maskedKeys = array_map(fn ($k) => SystemConfiguration::maskSecret($k, 6, 4), $rawKeys);

        $maskedTelegramToken = SystemConfiguration::maskSecret($config->telegram_bot_token, 8, 4);
        $maskedWhatsappToken = SystemConfiguration::maskSecret($config->whatsapp_api_token, 6, 4);
        $maskedMailPassword = SystemConfiguration::maskSecret($config->mail_password, 2, 2);

        $knowledgeCount = AiKnowledge::count();

        return view('dashboard.configurations.index', compact(
            'config',
            'rawKeys',
            'maskedKeys',
            'maskedTelegramToken',
            'maskedWhatsappToken',
            'maskedMailPassword',
            'knowledgeCount'
        ));
    }

    public function update(Request $request)
    {
        $request->validate([
            // AI Configuration
            'ai_provider' => 'required|string|in:gemini,deepseek,groq,xai,openai,custom',
            'ai_model' => 'required|string|max:100', // Plain text input, manual type
            'ai_base_url' => 'nullable|string|max:255',
            'ai_api_keys' => 'nullable|array',
            'ai_api_keys.*' => 'nullable|string|max:255',

            // WhatsApp Configuration
            'whatsapp_bot_url' => 'nullable|string|max:255',
            'whatsapp_bot_number' => 'nullable|string|max:30',
            'whatsapp_api_token' => 'nullable|string|max:255',

            // Telegram Configuration
            'telegram_bot_token' => 'nullable|string|max:255',
            'telegram_bot_username' => 'nullable|string|max:100',

            // Email (SMTP) Configuration
            'mail_mailer' => 'nullable|string|max:50',
            'mail_host' => 'nullable|string|max:255',
            'mail_port' => 'nullable|numeric',
            'mail_username' => 'nullable|string|max:255',
            'mail_password' => 'nullable|string|max:255',
            'mail_encryption' => 'nullable|string|max:20',
            'mail_from_address' => 'nullable|email|max:255',
            'mail_from_name' => 'nullable|string|max:255',
        ]);

        $config = SystemConfiguration::current();

        // 1. Process AI API Keys (preserving masked values)
        $existingKeys = (array) ($config->ai_api_keys ?? []);
        $savedKeys = [];
        $submittedKeys = (array) $request->input('ai_api_keys', []);

        foreach ($submittedKeys as $index => $keyVal) {
            $keyVal = trim((string) $keyVal);
            if (empty($keyVal)) {
                continue;
            }

            // If input is masked, retain the existing secret from database
            if (str_contains($keyVal, '•') && isset($existingKeys[$index])) {
                $savedKeys[] = $existingKeys[$index];
            } else {
                $savedKeys[] = $keyVal;
            }
        }

        // 2. Process Telegram Bot Token
        $telegramToken = trim((string) $request->input('telegram_bot_token'));
        if (str_contains($telegramToken, '•') || empty($telegramToken)) {
            $telegramToken = $config->telegram_bot_token;
        }

        // 3. Process WhatsApp API Token
        $whatsappToken = trim((string) $request->input('whatsapp_api_token'));
        if (str_contains($whatsappToken, '•')) {
            $whatsappToken = $config->whatsapp_api_token;
        }

        // 4. Process Mail Password
        $mailPassword = (string) $request->input('mail_password');
        if (str_contains($mailPassword, '•') || $mailPassword === '') {
            $mailPassword = $config->mail_password;
        }

        // Update database record
        $config->update([
            'ai_provider' => $request->input('ai_provider'),
            'ai_model' => trim($request->input('ai_model')),
            'ai_api_keys' => array_values(array_unique($savedKeys)),
            'ai_base_url' => trim((string) $request->input('ai_base_url')) ?: null,

            'whatsapp_bot_url' => rtrim(trim((string) $request->input('whatsapp_bot_url')), '/'),
            'whatsapp_bot_number' => preg_replace('/[^0-9]/', '', (string) $request->input('whatsapp_bot_number')),
            'whatsapp_api_token' => $whatsappToken ?: null,

            'telegram_bot_token' => $telegramToken ?: null,
            'telegram_bot_username' => ltrim(trim((string) $request->input('telegram_bot_username')), '@'),

            'mail_mailer' => $request->input('mail_mailer') ?: 'smtp',
            'mail_host' => trim((string) $request->input('mail_host')) ?: null,
            'mail_port' => (int) ($request->input('mail_port') ?: 1025),
            'mail_username' => trim((string) $request->input('mail_username')) ?: null,
            'mail_password' => $mailPassword ?: null,
            'mail_encryption' => in_array($request->input('mail_encryption'), ['none', 'null', ''], true) ? null : ($request->input('mail_encryption') ?: null),
            'mail_from_address' => trim((string) $request->input('mail_from_address')) ?: null,
            'mail_from_name' => trim((string) $request->input('mail_from_name')) ?: null,
        ]);

        // Sync runtime configs
        $config->syncToRuntimeConfig();

        // Sync core variables to .env file for daemon processes
        $this->syncEnvVariables($config);

        return redirect()->route('configuration.index')->with('success', 'Konfigurasi sistem (AI, WhatsApp, Telegram, Email) berhasil disimpan ke database!');
    }

    public function testAi(Request $request)
    {
        $provider = $request->input('ai_provider', 'gemini');
        $model = trim((string) $request->input('ai_model', 'gemini-2.5-flash'));
        $baseUrl = trim((string) $request->input('ai_base_url'));
        $inputKeys = (array) $request->input('ai_api_keys', []);

        $config = SystemConfiguration::current();
        $existingKeys = (array) ($config->ai_api_keys ?? []);

        // Resolve unmasked keys
        $keysToTest = [];
        foreach ($inputKeys as $idx => $k) {
            $k = trim((string) $k);
            if (empty($k)) {
                continue;
            }
            if (str_contains($k, '•') && isset($existingKeys[$idx])) {
                $keysToTest[] = $existingKeys[$idx];
            } else {
                $keysToTest[] = $k;
            }
        }

        if (empty($keysToTest)) {
            $keysToTest = SystemConfiguration::getAiApiKeys();
        }

        if (empty($keysToTest)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada API Key yang dapat diuji. Harap isi minimal 1 API Key.',
            ], 422);
        }

        $startTime = microtime(true);
        $lastError = 'Semua kunci API gagal.';

        foreach ($keysToTest as $i => $apiKey) {
            $keyIndex = $i + 1;
            try {
                if ($provider === 'gemini') {
                    $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";
                    $payload = [
                        'contents' => [
                            [
                                'role' => 'user',
                                'parts' => [
                                    ['text' => 'Balas hanya 1 kata: SIWIL_OK'],
                                ],
                            ],
                        ],
                    ];
                    $response = Http::timeout(20)->post($url, $payload);
                } else {
                    $endpoint = match ($provider) {
                        'deepseek' => 'https://api.deepseek.com/chat/completions',
                        'groq' => 'https://api.groq.com/openai/v1/chat/completions',
                        'xai' => 'https://api.x.ai/v1/chat/completions',
                        'openai' => 'https://api.openai.com/v1/chat/completions',
                        'custom' => $baseUrl ?: 'https://api.openai.com/v1/chat/completions',
                        default => 'https://api.groq.com/openai/v1/chat/completions',
                    };

                    if ($provider === 'custom' && $baseUrl && ! str_contains($baseUrl, '/chat/completions')) {
                        $endpoint = rtrim($baseUrl, '/').'/chat/completions';
                    }

                    $response = Http::timeout(20)
                        ->withHeaders([
                            'Authorization' => 'Bearer '.$apiKey,
                            'Content-Type' => 'application/json',
                        ])
                        ->post($endpoint, [
                            'model' => $model,
                            'messages' => [
                                ['role' => 'user', 'content' => 'Balas hanya 1 kata: SIWIL_OK'],
                            ],
                            'max_tokens' => 10,
                        ]);
                }

                $latency = round((microtime(true) - $startTime) * 1000);

                if ($response->successful()) {
                    $maskedKey = SystemConfiguration::maskSecret($apiKey, 6, 4);

                    return response()->json([
                        'success' => true,
                        'message' => "✓ Berhasil terhubung ke [{$provider}] model [{$model}] menggunakan Kunci #{$keyIndex} ({$maskedKey})! (Latensi: {$latency} ms)",
                    ]);
                }

                $status = $response->status();
                $errBody = $response->json('error.message') ?? $response->body();
                $lastError = "Kunci #{$keyIndex} gagal (HTTP {$status}): ".Str::limit($errBody, 150);
                Log::warning("Test AI: {$lastError}. Mencoba kunci fallback berikutnya...");
            } catch (\Throwable $e) {
                $rawMsg = $e->getMessage();
                if (str_contains($rawMsg, 'timed out') || str_contains($rawMsg, 'cURL error 28')) {
                    $lastError = "Kunci #{$keyIndex} timeout setelah 20 detik. Pastikan nama model valid (misal: 'gemini-2.5-flash', 'gemini-2.0-flash', atau 'gemini-1.5-flash') dan koneksi internet stabil.";
                } else {
                    $lastError = "Kunci #{$keyIndex} error: ".Str::limit($rawMsg, 150);
                }
                Log::warning("Test AI: {$lastError}. Mencoba kunci fallback berikutnya...");
            }
        }

        return response()->json([
            'success' => false,
            'message' => "Gagal terhubung ke AI. {$lastError}",
        ], 400);
    }

    public function testTelegram(Request $request)
    {
        $token = trim((string) $request->input('telegram_bot_token'));
        $config = SystemConfiguration::current();

        if (str_contains($token, '•') || empty($token)) {
            $token = $config->telegram_bot_token;
        }

        if (empty($token)) {
            return response()->json([
                'success' => false,
                'message' => 'Token Bot Telegram belum diisi.',
            ], 422);
        }

        try {
            $response = Http::timeout(8)->get("https://api.telegram.org/bot{$token}/getMe");

            if ($response->successful()) {
                $bot = $response->json('result');
                $username = $bot['username'] ?? '-';
                $name = $bot['first_name'] ?? 'Bot';

                return response()->json([
                    'success' => true,
                    'message' => "✓ Terhubung dengan Bot Telegram: {$name} (@{$username})",
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Gagal terhubung ke Telegram. Pastikan Bot Token valid.',
            ], 400);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Koneksi ke server Telegram gagal: '.$e->getMessage(),
            ], 500);
        }
    }

    public function testWhatsapp(Request $request)
    {
        $config = SystemConfiguration::current();
        $url = rtrim(trim((string) $request->input('whatsapp_bot_url')), '/');
        if (empty($url)) {
            $url = $config->whatsapp_bot_url ?: 'http://127.0.0.1:3000';
        }

        try {
            $response = Http::timeout(6)->get("{$url}/status");

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'message' => "✓ WhatsApp Gateway Server aktif di {$url}!",
                ]);
            }

            // Secondary ping test
            $ping = Http::timeout(4)->get($url);
            if ($ping->successful() || $ping->status() === 404) {
                return response()->json([
                    'success' => true,
                    'message' => "✓ Server WhatsApp Gateway terdeteksi aktif pada {$url}.",
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => "Server WhatsApp Gateway merespon dengan status HTTP {$response->status()}.",
            ], 400);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => "Tidak dapat menghubungi WhatsApp Gateway di {$url}: ".$e->getMessage(),
            ], 500);
        }
    }

    public function testEmail(Request $request)
    {
        $user = Auth::user();
        $targetEmail = $user->email;

        if (empty($targetEmail)) {
            return response()->json([
                'success' => false,
                'message' => 'Akun Super Admin Anda belum memiliki alamat email terdaftar untuk menerima tes kirim.',
            ], 422);
        }

        $config = SystemConfiguration::current();

        $host = trim((string) $request->input('mail_host')) ?: ($config->mail_host ?: '127.0.0.1');
        $port = (int) ($request->input('mail_port') ?: ($config->mail_port ?: 1025));
        $encryption = $request->input('mail_encryption', $config->mail_encryption);
        if (in_array($encryption, ['none', 'null', ''], true)) {
            $encryption = null;
        }
        $username = trim((string) $request->input('mail_username')) ?: $config->mail_username;
        $password = $request->input('mail_password');
        if (str_contains((string) $password, '•') || empty($password)) {
            $password = $config->mail_password;
        }
        $fromAddress = trim((string) $request->input('mail_from_address')) ?: ($config->mail_from_address ?: 'noreply@siwil.id');
        $fromName = trim((string) $request->input('mail_from_name')) ?: ($config->mail_from_name ?: 'SIWIL AI');

        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => $host,
            'mail.mailers.smtp.port' => $port,
            'mail.mailers.smtp.encryption' => $encryption,
            'mail.mailers.smtp.username' => $username,
            'mail.mailers.smtp.password' => $password,
            'mail.from.address' => $fromAddress,
            'mail.from.name' => $fromName,
        ]);

        Mail::purge('smtp');

        try {
            Mail::raw("Halo {$user->name},\n\nIni adalah email uji coba dari Pusat Konfigurasi Sistem Informasi Wilayah Terpadu (SIWIL AI).\nPengaturan SMTP Email Anda telah berhasil terhubung dan berfungsi dengan baik.\n\nWaktu Kirim: ".now()->format('d M Y H:i:s'), function ($msg) use ($targetEmail, $fromAddress, $fromName) {
                $msg->from($fromAddress, $fromName)
                    ->to($targetEmail)
                    ->subject('Uji Coba Konfigurasi Email SIWIL AI');
            });

            $destNote = (str_contains($host, '127.0.0.1') || str_contains($host, 'localhost')) ? ' (atau Mailpit pada port 8025)' : '';

            return response()->json([
                'success' => true,
                'message' => "✓ Email uji coba berhasil dikirim ke {$targetEmail}! Silakan cek kotak masuk Anda{$destNote}.",
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim email: '.$e->getMessage(),
            ], 500);
        }
    }

    private function syncEnvVariables(SystemConfiguration $config): void
    {
        $path = base_path('.env');
        if (! file_exists($path)) {
            return;
        }

        $env = file_get_contents($path);

        $keys = (array) ($config->ai_api_keys ?? []);
        $primaryKey = ! empty($keys) ? $keys[0] : '';

        $env = $this->setEnvValue($env, 'AI_PROVIDER', $config->ai_provider ?: 'gemini');
        $env = $this->setEnvValue($env, 'AI_MODEL', $config->ai_model ?: 'gemini-2.5-flash');
        if (! empty($primaryKey)) {
            $env = $this->setEnvValue($env, 'AI_API_KEY', $primaryKey);
            $env = $this->setEnvValue($env, 'GEMINI_API_KEY', $primaryKey);
        }
        $env = $this->setEnvValue($env, 'GEMINI_MODEL', $config->ai_model ?: 'gemini-2.5-flash');
        $env = $this->setEnvValue($env, 'AI_BASE_URL', $config->ai_base_url ?: '');

        if ($config->whatsapp_bot_url) {
            $env = $this->setEnvValue($env, 'WHATSAPP_BOT_URL', $config->whatsapp_bot_url);
        }
        if ($config->whatsapp_bot_number) {
            $env = $this->setEnvValue($env, 'WHATSAPP_BOT_NUMBER', $config->whatsapp_bot_number);
        }
        if ($config->telegram_bot_token) {
            $env = $this->setEnvValue($env, 'TELEGRAM_BOT_TOKEN', $config->telegram_bot_token);
        }
        if ($config->telegram_bot_username) {
            $env = $this->setEnvValue($env, 'TELEGRAM_BOT_USERNAME', $config->telegram_bot_username);
        }

        if ($config->mail_host) {
            $env = $this->setEnvValue($env, 'MAIL_HOST', $config->mail_host);
            $env = $this->setEnvValue($env, 'MAIL_PORT', (string) $config->mail_port);
            $env = $this->setEnvValue($env, 'MAIL_USERNAME', $config->mail_username ?: '');
            if ($config->mail_password) {
                $env = $this->setEnvValue($env, 'MAIL_PASSWORD', $config->mail_password);
            }
            $env = $this->setEnvValue($env, 'MAIL_ENCRYPTION', $config->mail_encryption ?: 'tls');
            $env = $this->setEnvValue($env, 'MAIL_FROM_ADDRESS', $config->mail_from_address ?: 'noreply@siwil.desa.id');
            $env = $this->setEnvValue($env, 'MAIL_FROM_NAME', $config->mail_from_name ?: 'SIWIL AI');
        }

        file_put_contents($path, $env);
        Artisan::call('optimize:clear');
    }

    private function setEnvValue(string $env, string $key, string $value): string
    {
        $escaped = (str_contains($value, ' ') || str_contains($value, '#') || str_contains($value, '$'))
            ? '"'.addcslashes($value, '"').'"'
            : $value;

        if (preg_match("/^{$key}=/m", $env)) {
            return preg_replace("/^{$key}=.*/m", "{$key}={$escaped}", $env);
        }

        return rtrim($env)."\n{$key}={$escaped}\n";
    }
}
