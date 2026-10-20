<?php

namespace App\Http\Controllers;

use App\Models\AiKnowledge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;

class AiKnowledgeController extends Controller
{
    public function index()
    {
        $knowledge = AiKnowledge::with('updater')->latest()->paginate(10);

        return view('dashboard.ai-knowledge.index', compact('knowledge'));
    }

    public function create()
    {
        return view('dashboard.ai-knowledge.form');
    }

    public function store(Request $request)
    {
        $request->validate([
            'topic' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        AiKnowledge::create([
            'topic' => $request->topic,
            'content' => $request->content,
            'last_updated_by' => auth()->id(),
        ]);

        return redirect()->route('ai-knowledge.index')->with('success', 'Data AI Knowledge berhasil ditambahkan.');
    }

    public function edit(AiKnowledge $aiKnowledge)
    {
        return view('dashboard.ai-knowledge.form', compact('aiKnowledge'));
    }

    public function update(Request $request, AiKnowledge $aiKnowledge)
    {
        $request->validate([
            'topic' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $aiKnowledge->update([
            'topic' => $request->topic,
            'content' => $request->content,
            'last_updated_by' => auth()->id(),
        ]);

        return redirect()->route('ai-knowledge.index')->with('success', 'Data AI Knowledge berhasil diperbarui.');
    }

    public function destroy(AiKnowledge $aiKnowledge)
    {
        $aiKnowledge->delete();

        return redirect()->route('ai-knowledge.index')->with('success', 'Data AI Knowledge berhasil dihapus.');
    }

    public function updateConfig(Request $request)
    {
        $request->validate([
            'ai_provider' => 'required|string|in:gemini,deepseek,groq,xai,openai,custom',
            'ai_model' => 'required|string|max:100',
            'ai_api_key' => 'required|string',
            'ai_base_url' => 'nullable|string|max:255',
        ]);

        $provider = $request->ai_provider;
        $model = trim($request->ai_model);
        $apiKey = trim($request->ai_api_key);
        $baseUrl = trim($request->ai_base_url ?? '');

        $path = base_path('.env');
        if (file_exists($path)) {
            $env = file_get_contents($path);

            $env = $this->setEnvValue($env, 'AI_PROVIDER', $provider);
            $env = $this->setEnvValue($env, 'AI_MODEL', $model);
            $env = $this->setEnvValue($env, 'AI_API_KEY', $apiKey);
            $env = $this->setEnvValue($env, 'AI_BASE_URL', $baseUrl);

            // Backward compatibility for legacy Gemini configs
            if ($provider === 'gemini') {
                $env = $this->setEnvValue($env, 'GEMINI_API_KEY', $apiKey);
                $env = $this->setEnvValue($env, 'GEMINI_MODEL', $model);
            }

            file_put_contents($path, $env);
            Artisan::call('optimize:clear');
        }

        return redirect()->back()->with('success', "Konfigurasi AI Provider [{$provider}] & Model [{$model}] berhasil disimpan!");
    }

    public function testConnection(Request $request)
    {
        $request->validate([
            'ai_provider' => 'required|string|in:gemini,deepseek,groq,xai,openai,custom',
            'ai_model' => 'required|string|max:100',
            'ai_api_key' => 'required|string',
            'ai_base_url' => 'nullable|string|max:255',
        ]);

        $provider = $request->ai_provider;
        $model = trim($request->ai_model);
        $apiKey = trim($request->ai_api_key);
        $baseUrl = trim($request->ai_base_url ?? '');

        $startTime = microtime(true);

        try {
            if ($provider === 'gemini') {
                $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";
                $payload = [
                    'contents' => [
                        [
                            'role' => 'user',
                            'parts' => [
                                ['text' => 'Halo! Balas hanya dengan satu kata: SIWIL_OK']
                            ]
                        ]
                    ]
                ];
                $response = Http::timeout(10)->post($url, $payload);
            } else {
                $endpoint = match ($provider) {
                    'deepseek' => 'https://api.deepseek.com/chat/completions',
                    'groq' => 'https://api.groq.com/openai/v1/chat/completions',
                    'xai' => 'https://api.x.ai/v1/chat/completions',
                    'openai' => 'https://api.openai.com/v1/chat/completions',
                    'custom' => $baseUrl ?: 'https://api.openai.com/v1/chat/completions',
                    default => 'https://api.groq.com/openai/v1/chat/completions',
                };

                if ($provider === 'custom' && $baseUrl && !str_contains($baseUrl, '/chat/completions')) {
                    $endpoint = rtrim($baseUrl, '/') . '/chat/completions';
                }

                $payload = [
                    'model' => $model,
                    'messages' => [
                        ['role' => 'user', 'content' => 'Halo! Balas hanya dengan satu kata: SIWIL_OK']
                    ],
                    'max_tokens' => 20,
                ];

                $response = Http::timeout(10)
                    ->withHeaders([
                        'Authorization' => 'Bearer ' . $apiKey,
                        'Content-Type' => 'application/json',
                    ])
                    ->post($endpoint, $payload);
            }

            $latency = round((microtime(true) - $startTime) * 1000);

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'message' => "✓ Berhasil terhubung ke {$provider} [{$model}]! (Respon: {$latency} ms)",
                ]);
            } else {
                $status = $response->status();
                $body = $response->json();
                $err = $body['error']['message'] ?? ($body['error'] ?? 'Autentikasi gagal atau model tidak ditemukan.');
                if (is_array($err)) {
                    $err = json_encode($err);
                }

                return response()->json([
                    'success' => false,
                    'message' => "Gagal terhubung (HTTP {$status}): {$err}",
                ], 400);
            }
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghubungi server API: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function setEnvValue(string $env, string $key, string $value): string
    {
        $escaped = (str_contains($value, ' ') || str_contains($value, '#') || str_contains($value, '$'))
            ? '"' . addcslashes($value, '"') . '"'
            : $value;

        if (preg_match("/^{$key}=/m", $env)) {
            return preg_replace("/^{$key}=.*/m", "{$key}={$escaped}", $env);
        }

        return rtrim($env) . "\n{$key}={$escaped}\n";
    }
}
