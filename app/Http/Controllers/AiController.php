<?php

namespace App\Http\Controllers;

use App\Models\AiKnowledge;
use App\Models\Announcement;
use App\Models\AppNotification;
use App\Models\AuditLog;
use App\Models\Budget;
use App\Models\Complaint;
use App\Models\Development;
use App\Models\DocumentRequest;
use App\Models\Emergency;
use App\Models\Family;
use App\Models\Poll;
use App\Models\PollVote;
use App\Models\Resident;
use App\Models\Rt;
use App\Models\Rw;
use App\Models\SystemConfiguration;
use App\Models\User;
use App\Models\Village;
use App\Services\AiToolService;
use App\Services\DocumentRequestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AiController extends Controller
{
    public function __construct(
        protected ?AiToolService $aiToolService = null
    ) {
        $this->aiToolService = $this->aiToolService ?? app(AiToolService::class);
    }

    public function index()
    {
        return redirect()->route('dashboard', ['open_chat' => 1]);
    }

    public function chat(Request $request)
    {
        $request->validate([
            'node' => 'nullable|string',
            'data' => 'nullable|array',
            'message' => 'nullable|string',
        ]);

        $user = Auth::user();

        if ($request->has('message') && ! empty($request->message)) {
            return response()->json($this->processGemini($request->message, $user));
        }

        $node = $request->node ?? 'start';
        $data = $request->data ?? [];

        return response()->json($this->processNode($node, $data, $user));
    }

    private function processGemini(string $message, User $user): array
    {
        return $this->processAi($message, $user);
    }

    private function processAi(string $message, User $user): array
    {
        // 1. Strict deterministic Role Guardrail check
        $restriction = $this->checkRoleRestrictions($message, $user);
        if ($restriction) {
            return [
                'message' => $restriction,
                'options' => [['label' => 'Kembali ke Menu Awal', 'icon' => 'home', 'next_node' => 'start']],
            ];
        }

        $scopedData = $this->buildScopedContext($user, $message);

        // Inject dynamic system prompt with user identity, RBAC boundaries, and function calling tools
        $systemPrompt = $this->aiToolService->buildDynamicSystemPrompt($user, $scopedData['context_text'] ?? null);

        $systemConfig = SystemConfiguration::current();
        $provider = $systemConfig?->ai_provider ?: (config('services.ai.provider') ?? env('AI_PROVIDER', 'gemini'));
        $apiKeys = SystemConfiguration::getAiApiKeys();
        $model = $systemConfig?->ai_model ?: (config('services.ai.model') ?? env('AI_MODEL') ?? config('services.gemini.model') ?? env('GEMINI_MODEL', 'gemini-2.5-flash'));
        $customBaseUrl = $systemConfig?->ai_base_url ?: (config('services.ai.base_url') ?? env('AI_BASE_URL'));

        if (empty($apiKeys)) {
            return $this->handleSmartFallback($message, $user, $scopedData);
        }

        $userPrompt = "Konteks Sistem Wilayah SIWIL (Database Berdasarkan Role Pengguna):\n".($scopedData['context_text'] ?? '')."\n\nPertanyaan Pengguna: ".$message;

        $reply = null;
        foreach ($apiKeys as $index => $apiKey) {
            $apiKey = trim((string) $apiKey);
            if ($apiKey === '') {
                continue;
            }

            try {
                if ($provider === 'gemini') {
                    $reply = $this->callGeminiApi($apiKey, $model, $systemPrompt, $userPrompt, $user);
                } else {
                    $reply = $this->callOpenAiCompatibleApi($provider, $apiKey, $model, $systemPrompt, $userPrompt, $user, $customBaseUrl);
                }

                if ($reply) {
                    break;
                }

                Log::warning('AI key #'.($index + 1).' did not yield an answer. Retrying next key...');
            } catch (\Throwable $e) {
                Log::warning('AI key #'.($index + 1).' encountered error: '.$e->getMessage().'. Retrying fallback key...');
            }
        }

        if ($reply) {
            return [
                'message' => $reply,
                'options' => [['label' => 'Kembali ke Menu Awal', 'icon' => 'home', 'next_node' => 'start']],
            ];
        }

        return $this->handleSmartFallback($message, $user, $scopedData);
    }

    private function callGeminiApi(string $apiKey, string $model, string $systemPrompt, string $userPrompt, User $user): ?string
    {
        $candidateModels = array_values(array_unique([
            $model,
            'gemini-2.5-flash',
            'gemini-2.0-flash',
            'gemini-1.5-flash',
        ]));

        $tools = [
            [
                'functionDeclarations' => $this->aiToolService->getGeminiTools($user),
            ],
        ];

        $payload = [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        ['text' => $userPrompt],
                    ],
                ],
            ],
            'systemInstruction' => [
                'parts' => [
                    ['text' => $systemPrompt],
                ],
            ],
            'tools' => $tools,
        ];

        foreach ($candidateModels as $candidateModel) {
            try {
                $url = "https://generativelanguage.googleapis.com/v1beta/models/{$candidateModel}:generateContent?key={$apiKey}";
                $response = Http::timeout(15)->post($url, $payload);
                if ($response->successful()) {
                    $data = $response->json();
                    $candidate = $data['candidates'][0] ?? null;
                    $content = $candidate['content'] ?? null;
                    $parts = $content['parts'] ?? [];

                    // Check for functionCall execution
                    $hasFunctionCall = false;
                    foreach ($parts as $part) {
                        if (isset($part['functionCall'])) {
                            $hasFunctionCall = true;
                            $fnName = $part['functionCall']['name'];
                            $fnArgs = $part['functionCall']['args'] ?? [];
                            $toolResult = $this->aiToolService->executeTool($fnName, $fnArgs, $user);

                            // Append model's functionCall turn
                            $payload['contents'][] = $content;

                            // Append function response turn
                            $payload['contents'][] = [
                                'role' => 'function',
                                'parts' => [
                                    [
                                        'functionResponse' => [
                                            'name' => $fnName,
                                            'response' => [
                                                'name' => $fnName,
                                                'content' => $toolResult,
                                            ],
                                        ],
                                    ],
                                ],
                            ];

                            // Follow-up request to get final conversational answer
                            $followUp = Http::timeout(15)->post($url, $payload);
                            if ($followUp->successful()) {
                                $followUpData = $followUp->json();
                                $finalText = $followUpData['candidates'][0]['content']['parts'][0]['text'] ?? null;
                                if ($finalText) {
                                    return trim($finalText);
                                }
                            }
                            break;
                        }
                    }

                    if (! $hasFunctionCall) {
                        $reply = $parts[0]['text'] ?? null;
                        if ($reply) {
                            return trim($reply);
                        }
                    }
                } else {
                    $status = $response->status();
                    $body = $response->body();
                    Log::warning("Gemini API call failed with model {$candidateModel}: HTTP {$status} - {$body}");

                    // Rate limit or quota exhausted or auth issue - break immediately to trigger fallback key
                    if ($status === 429 || $status === 401 || $status === 403 || str_contains($body, 'RESOURCE_EXHAUSTED') || str_contains($body, 'quota')) {
                        throw new \RuntimeException("Gemini rate limit or quota exceeded (HTTP {$status})");
                    }
                }
            } catch (\RuntimeException $re) {
                throw $re;
            } catch (\Throwable $e) {
                Log::warning("Gemini API call failed with model {$candidateModel}: ".$e->getMessage());

                continue;
            }
        }

        return null;
    }

    private function callOpenAiCompatibleApi(string $provider, string $apiKey, string $model, string $systemPrompt, string $userPrompt, User $user, ?string $customBaseUrl = null): ?string
    {
        $endpoint = match ($provider) {
            'deepseek' => 'https://api.deepseek.com/chat/completions',
            'groq' => 'https://api.groq.com/openai/v1/chat/completions',
            'xai' => 'https://api.x.ai/v1/chat/completions',
            'openai' => 'https://api.openai.com/v1/chat/completions',
            'custom' => $customBaseUrl ?: 'https://api.openai.com/v1/chat/completions',
            default => 'https://api.groq.com/openai/v1/chat/completions',
        };

        if ($provider === 'custom' && $customBaseUrl && ! str_contains($customBaseUrl, '/chat/completions')) {
            $endpoint = rtrim($customBaseUrl, '/').'/chat/completions';
        }

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userPrompt],
        ];

        $tools = $this->aiToolService->getOpenAiTools($user);

        $payload = [
            'model' => $model,
            'messages' => $messages,
            'tools' => $tools,
            'tool_choice' => 'auto',
            'temperature' => 0.5,
        ];

        try {
            $response = Http::timeout(18)
                ->withHeaders([
                    'Authorization' => 'Bearer '.trim($apiKey),
                    'Content-Type' => 'application/json',
                ])
                ->post($endpoint, $payload);

            if ($response->successful()) {
                $data = $response->json();
                $choice = $data['choices'][0] ?? null;
                $msg = $choice['message'] ?? null;

                if (! empty($msg['tool_calls'])) {
                    $messages[] = $msg;
                    foreach ($msg['tool_calls'] as $toolCall) {
                        $callId = $toolCall['id'] ?? 'call_'.uniqid();
                        $fnName = $toolCall['function']['name'] ?? '';
                        $fnArgs = json_decode($toolCall['function']['arguments'] ?? '{}', true) ?: [];

                        $toolResult = $this->aiToolService->executeTool($fnName, $fnArgs, $user);

                        $messages[] = [
                            'role' => 'tool',
                            'tool_call_id' => $callId,
                            'name' => $fnName,
                            'content' => json_encode($toolResult, JSON_UNESCAPED_UNICODE),
                        ];
                    }

                    $followUp = Http::timeout(18)->withHeaders([
                        'Authorization' => 'Bearer '.trim($apiKey),
                        'Content-Type' => 'application/json',
                    ])->post($endpoint, [
                        'model' => $model,
                        'messages' => $messages,
                        'temperature' => 0.5,
                    ]);

                    if ($followUp->successful()) {
                        $followUpData = $followUp->json();
                        $reply = $followUpData['choices'][0]['message']['content'] ?? null;
                        if ($reply) {
                            return trim($reply);
                        }
                    }
                }

                if (! empty($msg['content'])) {
                    return trim($msg['content']);
                }
            } else {
                $status = $response->status();
                $body = $response->body();
                Log::warning("OpenAI API call failed ({$provider}) HTTP {$status}: {$body}");
                if ($status === 429 || $status === 401 || $status === 403 || str_contains($body, 'rate_limit') || str_contains($body, 'quota')) {
                    throw new \RuntimeException("{$provider} rate limit or auth error (HTTP {$status})");
                }
            }
        } catch (\RuntimeException $re) {
            throw $re;
        } catch (\Throwable $e) {
            Log::warning("AI Provider {$provider} exception: ".$e->getMessage());
            throw $e;
        }

        return null;
    }

    /**
     * Enforce strict role-based data boundaries (Warga, RT, RW, Desa).
     */
    private function checkRoleRestrictions(string $message, User $user): ?string
    {
        $role = $user->role;
        $lower = strtolower($message);
        $clean = preg_replace('/[^\w\s]/', ' ', $lower);

        if ($role === 'SUPER_ADMIN') {
            // Super Admin has full, unrestricted access to the entire system and entire database
            return null;
        }

        // Allow inquiries about dashboard, sidebar menus, services, notifications, and recent updates
        $isAskingDashboardOrSidebar = (bool) preg_match('/(dashboard|sidebar|menu|fitur|layanan|update|pembaruan|terbaru|terkini|notifikasi|kabar|aktivitas)/i', $clean);

        if ($role === 'WARGA') {
            $isAskingOfficialContact = (bool) preg_match('/(ketua\s*rt|ketua\s*rw|pengurus|kantor\s*desa|layanan\s*desa|kontak\s*rt|kontak\s*rw|kontak\s*desa|nomor\s*rt|nomor\s*rw|no\s*hp\s*rt|no\s*wa\s*rt|no\s*wa\s*rw|email\s*desa|kepala\s*desa)/i', $clean);

            // Allow user asking for their own data, profile, or who they are
            $isAskingSelf = (bool) (
                preg_match('/^(profil|biodata|data\s*diri)$/i', trim($clean)) ||
                (
                    preg_match('/(siapa\s+(saya|aku)|profil\s*(saya|ku|akun|pribadi)|data\s*(diri|saya|ku|pribadi)|tentang\s*(saya|aku|akun)|informasi\s*(diri|akun|saya)|(nik|no(mor)?\s*(hp|wa)|alamat|nama|ktp)\s+(saya|ku|milik\s*saya))/i', $clean)
                    && ! preg_match('/(warga\s+lain|tetangga|penduduk|orang\s*lain)/i', $clean)
                )
            );

            // Allow user asking about their own family members in KK
            $isAskingFamily = (bool) preg_match('/(keluarga|anggota\s*(keluarga|kk)|kk\s*(saya|ku)|kartu\s*keluarga(\s*(saya|ku))?)/i', $clean);

            // Prohibit querying other residents' contacts, address, phone number, NIK, or account credentials
            if (! $isAskingSelf && ! $isAskingFamily && ! $isAskingOfficialContact && preg_match('/(?:kontak|nomor|no\s*hp|wa|telepon|nohp|alamat|data|biodata|nik|akun|status\s*akun)\s+(?:warga|tetangga|penduduk|orang\s*lain)|(?:warga|tetangga|penduduk).*(?:tidak|belum|tanpa|sudah)?\s*(?:punya|memiliki|ada|mempunyai)?\s*akun/i', $clean)) {
                return "Mohon maaf, demi perlindungan privasi dan kerahasiaan data kependudukan (UU Perlindungan Data Pribadi / UU PDP), akun **Warga** tidak memiliki izin untuk mengakses nomor kontak, NIK, alamat, status akun login, atau data warga lain.\n\nAnda hanya dapat mengakses informasi kontak resmi pengurus RT/RW, data diri Anda sendiri, dan layanan kantor desa.";
            }

            if (! $isAskingSelf && ! $isAskingFamily && ! $isAskingOfficialContact && preg_match('/(siapa\s+saja\s+warga|daftar\s+warga|nama\s+nama\s+warga|siapa\s+tetangga|nomor\s+tetangga|nik\s+tetangga)/i', $clean)) {
                return 'Mohon maaf, daftar nama, NIK, dan kontak warga lain bersifat rahasia dan dilindungi privasi. Akun Warga tidak memiliki izin mengakses direktori kependudukan warga lain.';
            }

            // Prohibit querying resident count / census ("berapa warganya", "berapa jumlah warga", etc.)
            if (! $isAskingDashboardOrSidebar && preg_match('/(berapa\s+warga|berapa\s+jumlah\s+warga|berapa\s+banyak\s+warga|jumlah\s+penduduk|berapa\s+penduduk|total\s+warga|total\s+penduduk|berapa\s+kk|jumlah\s+kk|berapa\s+jiwa)/i', $clean)) {
                return 'Mohon maaf, akun **Warga** tidak memiliki hak akses untuk melihat statistik jumlah penduduk maupun sensus kependudukan wilayah. Statistik kependudukan hanya dapat diakses oleh Pengurus RT, RW, dan Desa.';
            }

            // Prohibit querying RT/RW/Desa database or budget
            if (! $isAskingDashboardOrSidebar && preg_match('/(database\s*(rt|rw|desa)|anggaran\s+desa|apbdes|kas\s+desa|dana\s+desa|laporan\s+keuangan)/i', $clean)) {
                return 'Mohon maaf, akun **Warga** tidak memiliki akses ke database internal RT, RW, maupun Desa. Jika memerlukan informasi anggaran publik, silakan hubungi Kantor Desa atau ikuti Musyawarah Desa terbuka.';
            }
        } elseif ($role === 'RT') {
            $userRt = $user->rt_id ? Rt::find($user->rt_id) : null;
            $isAskingOfficialContact = (bool) preg_match('/(ketua|pengurus|kontak|nomor|no\s*hp|wa|telepon|email|siapa\s*ketua|siapa\s*pengurus)/i', $clean);
            $isAskingResidentData = (bool) preg_match('/(warga|penduduk|orang|nik|kk|keluarga|sensus|biodata\s*warga|data\s*warga)/i', $clean);

            // RT cannot access general database of other RTs, RW wide database, or village database
            if (! $isAskingDashboardOrSidebar && preg_match('/(database\s*(rt|rw|desa)|apbdes|anggaran\s+desa|dana\s+desa|total\s+penduduk\s+desa|seluruh\s+warga\s+desa|warga\s+se\s*desa)/i', $clean)) {
                return 'Mohon maaf, kewenangan akun **Pengurus RT** terbatas hanya untuk wilayah RT Anda sendiri. Anda tidak memiliki otoritas untuk mengakses database RT lain, RW, maupun Desa.';
            }

            // Check if RT queries another specific RT number
            if ($userRt && preg_match('/rt\s*0?(\d+)/i', $clean, $m)) {
                $requestedRtNum = ltrim($m[1], '0');
                $myRtNum = ltrim($userRt->number, '0');
                if ($requestedRtNum !== $myRtNum) {
                    $formattedTarget = str_pad($requestedRtNum, 2, '0', STR_PAD_LEFT);

                    // If asking for residents/data of that other RT, prohibit!
                    if ($isAskingResidentData || ! $isAskingOfficialContact) {
                        return "Mohon maaf, akun Anda adalah Pengurus RT {$userRt->number}. Anda tidak memiliki izin untuk mengakses data warga atau statistik di RT {$formattedTarget}.";
                    }
                }
            }

            // If query is accessing residents/data of other RTs without asking for official contacts
            if ($isAskingResidentData && preg_match('/(rt\s+lain|rw\s+lain)/i', $clean)) {
                return 'Mohon maaf, kewenangan akun **Pengurus RT** terbatas hanya untuk wilayah RT Anda sendiri. Anda tidak memiliki otoritas untuk mengakses data warga RT lain, RW, maupun Desa.';
            }

            if (! $isAskingOfficialContact && preg_match('/(rt\s+lain|rw\s+lain)/i', $clean)) {
                return 'Mohon maaf, kewenangan akun **Pengurus RT** terbatas hanya untuk wilayah RT Anda sendiri. Anda tidak memiliki otoritas untuk mengakses database RT lain, RW, maupun Desa.';
            }
        } elseif ($role === 'RW') {
            // RW cannot access village database or other RWs
            if (! $isAskingDashboardOrSidebar && preg_match('/(rw\s+lain|database\s+desa|apbdes|anggaran\s+desa|dana\s+desa|seluruh\s+penduduk\s+desa|arsip\s+desa)/i', $clean)) {
                return 'Mohon maaf, kewenangan akun **Pengurus RW** dibatasi pada wilayah RW Anda beserta RT-RT binaan. Anda tidak memiliki izin untuk mengakses database tingkat Desa secara menyeluruh atau wilayah RW lain.';
            }

            // Check if RW queries another specific RW number
            $userRw = $user->rw_id ? Rw::find($user->rw_id) : null;
            if ($userRw && preg_match('/rw\s*0?(\d+)/i', $clean, $m)) {
                $requestedRwNum = ltrim($m[1], '0');
                $myRwNum = ltrim($userRw->number, '0');
                if ($requestedRwNum !== $myRwNum) {
                    $formattedTarget = str_pad($requestedRwNum, 2, '0', STR_PAD_LEFT);

                    return "Mohon maaf, akun Anda adalah Pengurus RW {$userRw->number}. Anda tidak memiliki izin untuk mengakses data di RW {$formattedTarget}.";
                }
            }
        } elseif (in_array($role, ['ADMIN_DESA', 'DESA'])) {
            // Desa has full access to the village, but cannot access system server internals
            if (preg_match('/(konfigurasi\s+sistem|server|api\s*key|password\s+database|source\s+code|\.env|credentials|audit\s+log\s+sistem|superadmin\s+sistem)/i', $clean)) {
                return 'Mohon maaf, kewenangan Administrator Desa mencakup administrasi kependudukan dan tata kelola desa, dan tidak memiliki akses ke konfigurasi teknis internal sistem/server SIWIL.';
            }
        }

        return null;
    }

    /**
     * Get structured profile data for the active user.
     */
    private function getUserProfileData(User $user): array
    {
        $resident = Resident::with(['family', 'rt.rw.village'])->where('nik', $user->username)->first();
        if (! $resident && $user->resident) {
            $resident = $user->resident;
            $resident->loadMissing(['family', 'rt.rw.village']);
        }
        if (! $resident && ! empty($user->nik)) {
            $resident = Resident::with(['family', 'rt.rw.village'])->where('nik', $user->nik)->first();
        }

        $rt = $user->rt ?? ($resident?->rt ?? ($resident?->family?->rt ?? null));
        if ($rt && ! $rt->relationLoaded('rw')) {
            $rt->load('rw.village');
        }
        $rw = $user->rw ?? ($rt?->rw ?? null);
        if ($rw && ! $rw->relationLoaded('village')) {
            $rw->load('village');
        }
        $village = $user->village ?? ($rw?->village ?? null);

        $roleLabel = match ($user->role) {
            'WARGA' => 'Warga',
            'RT' => 'Pengurus RT '.($rt?->number ?? ''),
            'RW' => 'Pengurus RW '.($rw?->number ?? ''),
            'ADMIN_DESA', 'DESA' => 'Perangkat / Admin Desa '.($village?->name ?? ''),
            'SUPER_ADMIN' => 'Super Administrator',
            default => str_replace('_', ' ', $user->role),
        };

        $fullName = $resident?->full_name ?? $user->name ?? $user->username;
        $nik = $resident?->nik ?? $user->username;
        $phone = $user->phone_number ?? 'Belum terdaftar';
        $email = $user->email ?? 'Belum terdaftar';
        $rtNum = $rt?->number ?? '-';
        $rwNum = $rw?->number ?? '-';
        $villageName = $village?->name ?? '-';
        $address = $user->address ?: ($resident?->family?->address ?? '-');
        $kkNumber = $resident?->family?->kk_number ?? $user->kk_number ?? '-';
        $occupation = $resident?->occupation ?? '-';
        $genderFormatted = match (strtoupper($resident?->gender ?? '')) {
            'LAKI_LAKI', 'L', 'MALE' => 'Laki-laki',
            'PEREMPUAN', 'P', 'FEMALE' => 'Perempuan',
            default => $resident?->gender ?? '-',
        };
        $birthPlace = $resident?->birth_place ?? '-';
        $birthDate = $resident?->birth_date ? $resident->birth_date->format('d-m-Y') : '-';
        $birthInfo = ($birthPlace !== '-' && $birthDate !== '-') ? "{$birthPlace}, {$birthDate}" : ($birthPlace !== '-' ? $birthPlace : $birthDate);
        $religion = $resident?->religion ? ucfirst(strtolower($resident->religion)) : '-';
        $maritalStatus = $resident?->marital_status ? ucfirst(strtolower($resident->marital_status)) : '-';
        $education = $resident?->education ?? '-';
        $familyStatus = $resident?->is_head_of_family ? 'Kepala Keluarga' : ($resident?->status ?? 'Anggota Keluarga');
        $status = $resident?->status ?? $user->status ?? 'Aktif';

        return [
            'name' => $fullName,
            'nik' => $nik,
            'role' => $user->role,
            'role_label' => $roleLabel,
            'phone' => $phone,
            'email' => $email,
            'rt' => $rtNum,
            'rw' => $rwNum,
            'village' => $villageName,
            'address' => $address,
            'kk_number' => $kkNumber,
            'occupation' => $occupation,
            'gender' => $genderFormatted,
            'birth_place' => $birthPlace,
            'birth_date' => $birthDate,
            'birth_info' => $birthInfo,
            'religion' => $religion,
            'marital_status' => $maritalStatus,
            'education' => $education,
            'family_status' => $familyStatus,
            'status' => $status,
        ];
    }

    /**
     * Get structured sidebar menu navigation list tailored to user role.
     */
    private function getSidebarMenuList(string $role): string
    {
        return match ($role) {
            'WARGA' => "📌 **Daftar Menu & Modul Sidebar (Akun Warga):**\n\n".
                "1. 📂 **Menu Utama:**\n".
                "   • **Dashboard**: Ringkasan data pengajuan surat Anda, riwayat pengaduan, pengumuman desa, dan tombol darurat SOS.\n".
                "   • **Pengumuman Desa**: Pengumuman resmi dan surat edaran terkini dari pemerintah desa.\n\n".
                "2. 🏛️ **Layanan & Warga:**\n".
                "   • **Pengajuan Surat**: Layanan pembuatan surat pengantar mandiri online (Domisili, SKTM, SKU, dll).\n".
                "   • **Pengaduan**: Pelaporan keluhan, masalah infrastruktur, kebersihan, atau keamanan lingkungan.\n".
                "   • **Musyawarah**: Partisipasi aktif dalam musyawarah warga dan pemungutan suara (polling).\n".
                "   • **Data Keluarga**: Melihat daftar anggota keluarga resmi dalam Kartu Keluarga (KK) Anda.\n\n".
                "3. ⚙️ **Administrasi & Sistem:**\n".
                "   • **Pengaturan Akun**: Kelola profil pengguna, nomor kontak/telepon, dan ubah kata sandi.\n\n".
                "4. 🤖 **Koneksi Bot:**\n".
                '   • **WhatsApp & Telegram Bot**: Notifikasi otomatis dan layanan mandiri melalui WhatsApp dan Telegram.',

            'RT' => "📌 **Daftar Menu & Modul Sidebar (Akun Pengurus RT):**\n\n".
                "1. 📂 **Menu Utama:**\n".
                "   • **Dashboard RT**: Rekapitulasi statistik warga, KK, permohonan surat masuk, dan pengaduan di RT Anda.\n".
                "   • **Data Warga RT**: Direktori data kependudukan seluruh warga di wilayah RT binaan Anda.\n".
                "   • **Pengumuman Desa**: Arsip dan edaran resmi dari pemerintah desa.\n\n".
                "2. 🏛️ **Layanan Desa:**\n".
                "   • **Layanan Surat**: Verifikasi dan persetujuan pengajuan surat pengantar warga di RT Anda.\n".
                "   • **Pengaduan Warga**: Pemantauan dan tindak lanjut laporan keluhan dari warga RT.\n".
                "   • **Musyawarah**: Pengawasan dan pembuatan agenda musyawarah/polling warga.\n\n".
                "3. 📊 **Pemerintahan & Laporan:**\n".
                "   • **Laporan & Audit Log**: Rekap laporan kegiatan persuratan, pengaduan, dan riwayat audit wilayah.\n\n".
                "4. ⚙️ **Administrasi & Sistem:**\n".
                "   • **Pengaturan Akun**: Pengaturan profil dan keamanan akun pengurus RT.\n\n".
                "5. 🤖 **Koneksi Bot:**\n".
                '   • **WhatsApp & Telegram**: Notifikasi real-time persuratan dan panggilan darurat.',

            'RW' => "📌 **Daftar Menu & Modul Sidebar (Akun Pengurus RW):**\n\n".
                "1. 📂 **Menu Utama:**\n".
                "   • **Dashboard RW**: Ringkasan data kependudukan dan operasional seluruh RT binaan di RW Anda.\n".
                "   • **Data Warga RW**: Monitoring sebaran RT, jumlah penduduk, dan data keluarga per RT binaan.\n".
                "   • **Pengumuman Desa**: Informasi dan edaran resmi tingkat desa.\n\n".
                "2. 🏛️ **Layanan Desa:**\n".
                "   • **Layanan Surat**: Verifikasi tahap kedua pengajuan surat warga yang telah disetujui Ketua RT.\n".
                "   • **Pengaduan Warga**: Pengawasan dan penanganan laporan pengaduan masyarakat se-RW.\n".
                "   • **Musyawarah**: Pemantauan agenda musyawarah dan pemungutan suara warga se-RW.\n\n".
                "3. 📊 **Pemerintahan & Laporan:**\n".
                "   • **Laporan & Audit Log**: Akses laporan rekap kependudukan, arsip surat, dan log audit RW.\n\n".
                "4. ⚙️ **Administrasi & Sistem:**\n".
                "   • **Pengaturan Akun**: Kelola profil dan kredensial akun pengurus RW.\n\n".
                "5. 🤖 **Koneksi Bot:**\n".
                '   • **WhatsApp & Telegram**: Penerimaan notifikasi verifikasi berkas dan sinyal SOS.',

            'ADMIN_DESA', 'DESA' => "📌 **Daftar Menu & Modul Sidebar (Akun Administrator / Perangkat Desa):**\n\n".
                "1. 📂 **Menu Utama:**\n".
                "   • **Dashboard Desa**: Rekapitulasi menyeluruh data kependudukan desa, surat, pengaduan, dan keuangan.\n".
                "   • **Wilayah & Penduduk**: Manajemen master data RW, RT, kepala keluarga, dan seluruh penduduk desa.\n".
                "   • **Pengumuman Desa**: Publikasi pengumuman publik, berita kegiatan, dan surat edaran desa.\n\n".
                "2. 🏛️ **Layanan Desa:**\n".
                "   • **Layanan Surat**: Verifikasi akhir, penandatanganan elektronik (TTE Desa), dan penerbitan surat resmi.\n".
                "   • **Pengaduan Desa**: Manajemen penanganan keluhan masyarakat desa terpadu.\n".
                "   • **Musyawarah**: Penyelenggaraan musyawarah desa terbuka dan polling kebijakan publik.\n\n".
                "3. 📊 **Pemerintahan & Laporan:**\n".
                "   • **Pembangunan & APBDes**: Monitoring proyek pembangunan desa, anggaran, dan realisasi APBDes.\n".
                "   • **Laporan & Audit Log**: Rekapitulasi laporan berkala desa dan rekam jejak audit sistem.\n\n".
                "4. ⚙️ **Administrasi & Sistem:**\n".
                "   • **Manajemen User**: Pengelolaan akun login seluruh warga, pengurus RT, RW, dan staf desa.\n".
                "   • **Pengaturan Akun**: Pengaturan profil desa dan kredensial akun administrator.\n\n".
                "5. 🤖 **Koneksi Bot:**\n".
                '   • **WhatsApp & Telegram Gateway**: Integrasi pesan notifikasi dan broadcast desa.',

            'SUPER_ADMIN' => "📌 **Daftar Menu & Modul Sidebar (Akun Super Administrator):**\n\n".
                "1. 📂 **Menu Utama:**\n".
                "   • **Dashboard Super Admin**: Monitoring arsitektur sistem, kesehatan database, server, dan statistik global.\n".
                "   • **Wilayah & Penduduk**: Akses penuh master data hierarki desa, RW, RT, dan seluruh data penduduk.\n".
                "   • **Pengumuman Desa**: Monitoring dan pengawasan publikasi informasi desa.\n\n".
                "2. 🏛️ **Layanan Desa:**\n".
                "   • **Layanan Surat**: Pengawasan alur persuratan digital di seluruh tingkatan wilayah.\n".
                "   • **Pengaduan Warga**: Monitoring tiket laporan pengaduan se-sistem beserta status solusinya.\n".
                "   • **Musyawarah**: Pengawasan pelaksanaan polling dan musyawarah warga.\n\n".
                "3. 📊 **Pemerintahan & Laporan:**\n".
                "   • **Pembangunan & APBDes**: Monitoring data alokasi dana dan proyek fisik desa.\n".
                "   • **Laporan & Audit Log**: Audit trail menyeluruh atas seluruh mutasi data dan aktivitas sistem.\n\n".
                "4. ⚙️ **Administrasi & Sistem:**\n".
                "   • **Manajemen User**: Manajemen otorisasi pengguna, aktivasi akun, dan reset kata sandi.\n".
                "   • **Kelola AI Knowledge**: Manajemen basis pengetahuan (knowledge base) SIWIL AI.\n".
                "   • **Pengaturan Akun**: Pengaturan konfigurasi teknis dan akun super admin.\n\n".
                "5. 🤖 **Koneksi Bot:**\n".
                '   • **WhatsApp & Telegram**: Pengawasan integrasi gateway webhook bot.',

            default => '📌 **Menu Sidebar:** Dashboard, Layanan Surat, Pengaduan, Musyawarah, dan Pengaturan Akun.',
        };
    }

    /**
     * Get complete structured sidebar menu navigation list for all roles in SIWIL.
     */
    private function getSidebarMenuListForAllRoles(): string
    {
        return "📌 **Daftar Struktur Menu Sidebar & Modul Dashboard Tiap Role Beserta Sumber Databasenya:**\n\n".
            "1. 👤 **Role Warga (WARGA):**\n".
            "   • **Dashboard Warga:** Rekapitulasi surat, aduan, polling aktif, pengumuman, dan status darurat SOS.\n".
            "   • **Pengumuman Desa:** Edaran dan berita resmi pemerintah desa _(Tabel: `announcements`)_.\n".
            "   • **Pengajuan Surat:** Permohonan surat pengantar mandiri online _(Tabel: `document_requests`)_.\n".
            "   • **Pengaduan:** Pelaporan keluhan dan masalah lingkungan _(Tabel: `complaints`)_.\n".
            "   • **Musyawarah & Polling:** Partisipasi pemungutan suara warga _(Tabel: `polls`, `poll_votes`)_.\n".
            "   • **Data Keluarga:** Susunan resmi Kartu Keluarga (KK) Anda _(Tabel: `families`, `residents`)_.\n".
            "   • **Pengaturan Akun & Koneksi Bot:** Profil diri, nomor kontak, kata sandi (Bcrypt), dan integrasi WA/Telegram.\n".
            "   • **Fitur Khusus SOS:** Tombol panggilan darurat instan dan lokasi GPS _(Tabel: `emergencies`)_.\n\n".
            "2. 🛡️ **Role Pengurus RT (RT):**\n".
            "   • **Dashboard RT:** Rekap statistik kependudukan warga RT, KK, surat masuk, dan aduan.\n".
            "   • **Data Warga RT:** Direktori lengkap kependudukan dan KK di RT binaan _(Tabel: `residents`, `families`)_.\n".
            "   • **Layanan Surat RT:** Verifikasi dan persetujuan pengajuan surat pengantar warga RT.\n".
            "   • **Pengaduan Warga RT:** Monitoring dan tindak lanjut laporan aduan warga RT.\n".
            "   • **Musyawarah:** Pengawasan agenda musyawarah dan polling warga di lingkup RT.\n".
            "   • **Laporan & Audit Log:** Rekap data dan riwayat aktivitas wilayah _(Tabel: `audit_logs`)_.\n".
            "   • **Pengaturan Akun & Koneksi Bot:** Kelola profil pengurus RT dan notifikasi bot.\n\n".
            "3. 🏛️ **Role Pengurus RW (RW):**\n".
            "   • **Dashboard RW:** Rekapitulasi agregat seluruh RT binaan di lingkungan RW.\n".
            "   • **Data Warga RW:** Monitoring hierarki sebaran RT binaan dan jumlah penduduk se-RW.\n".
            "   • **Layanan Surat RW:** Verifikasi tahap kedua surat pengantar warga yang telah disetujui RT.\n".
            "   • **Pengaduan Warga RW:** Pengawasan dan koordinasi penanganan laporan aduan se-RW.\n".
            "   • **Musyawarah:** Monitoring musyawarah dan pemungutan suara warga se-RW.\n".
            "   • **Laporan & Audit Log:** Rekapitulasi wilayah RW dan rekam jejak audit sistem.\n".
            "   • **Pengaturan Akun & Koneksi Bot:** Pengaturan akun pengurus RW dan webhook bot.\n\n".
            "4. 🏢 **Role Administrator / Perangkat Desa (ADMIN_DESA):**\n".
            "   • **Dashboard Desa:** Matriks kependudukan desa, keuangan APBDes, persuratan, dan aduan.\n".
            "   • **Wilayah & Penduduk:** Monitoring master data desa, hierarki RW/RT, dan data kependudukan terpadu _(Tabel: `villages`, `rws`, `rts`, `residents`, `families`)_.\n".
            "   • **Pengumuman Desa:** Publikasi berita, edaran resmi, dan pengumuman desa.\n".
            "   • **Layanan Surat:** Verifikasi akhir, TTE elektronik desa, dan penerbitan surat resmi.\n".
            "   • **Pengaduan Desa:** Manajemen respon dan penyelesaian keluhan warga desa.\n".
            "   • **Musyawarah Desa:** Penyelenggaraan musyawarah desa terbuka dan polling kebijakan.\n".
            "   • **Pembangunan & APBDes:** Proyek pembangunan fisik dan realisasi anggaran _(Tabel: `budgets`, `developments`)_.\n".
            "   • **Laporan & Audit Log:** Laporan berkala desa dan rekam jejak audit trail.\n".
            "   • **Manajemen User:** Otorisasi akun seluruh warga dan aparatur desa _(Tabel: `users`)_.\n".
            "   • **Pengaturan Akun & Koneksi Bot:** Pengaturan profil desa dan gateway bot.\n\n".
            "5. ⚡ **Role Super Administrator (SUPER_ADMIN):**\n".
            "   • **Dashboard Super Admin:** Monitoring arsitektur global, integritas database, dan kesehatan server.\n".
            "   • **Wilayah & Penduduk:** Akses menyeluruh data desa, hierarki RW/RT, dan direktori kependudukan terpadu.\n".
            "   • **Layanan Surat & Pengaduan:** Pengawasan alur persuratan dan tiket keluhan warga lintas wilayah.\n".
            "   • **Pembangunan & APBDes:** Monitoring keuangan dan pembangunan desa se-sistem.\n".
            "   • **Laporan & Audit Log:** Audit trail global atas seluruh aktivitas dan perubahan data sistem.\n".
            "   • **Manajemen User:** Manajemen akun, aktivasi hak akses, dan reset kredensial pengguna.\n".
            "   • **Kelola AI Knowledge:** Manajemen basis pengetahuan mandiri AI SIWIL _(Tabel: `ai_knowledge`)_.\n".
            '   • **Pengaturan Akun & Koneksi Bot:** Konfigurasi teknis sistem dan bot gateway.';
    }

    /**
     * Get real-time data contents for all active menus in the user's sidebar.
     * Guaranteed never to include passwords, adhering strictly to system security policies.
     */
    private function getWholeSidebarMenuContents(User $user): string
    {
        $role = $user->role;
        $sections = [];

        // 1. Dashboard Content
        $sections[] = "1. 📊 **Menu Dashboard:**\n".$this->getDashboardSummary($user);

        // 2. Wilayah & Penduduk / Data Warga / Data Keluarga
        if ($role === 'WARGA') {
            $sections[] = "2. 👨‍👩‍👧‍👦 **Menu Data Keluarga (Kartu Keluarga):**\n".$this->getFamilyMenuContent($user);
        } else {
            $secTitle = $role === 'RT' ? 'Data Warga RT' : ($role === 'RW' ? 'Data Warga RW' : 'Wilayah & Penduduk');
            $sections[] = "2. 👥 **Menu {$secTitle}:**\n".$this->getTerritoryResidentMenuContent($user);
        }

        // 3. Pengumuman Desa
        $sections[] = "3. 📢 **Menu Pengumuman Desa:**\n".$this->getAnnouncementMenuContent($user);

        // 4. Layanan Surat
        $docTitle = $role === 'WARGA' ? 'Pengajuan Surat' : 'Layanan Surat';
        $sections[] = "4. 📄 **Menu {$docTitle}:**\n".$this->getDocumentMenuContent($user);

        // 5. Pengaduan Warga
        $compTitle = $role === 'WARGA' ? 'Pengaduan' : 'Pengaduan Warga';
        $sections[] = "5. 💬 **Menu {$compTitle}:**\n".$this->getComplaintMenuContent($user);

        // 6. Musyawarah & Polling
        $sections[] = "6. 🗳️ **Menu Musyawarah & Polling:**\n".$this->getPollMenuContent($user);

        $step = 7;

        // 7. Pembangunan & APBDes (Admin Desa & Super Admin)
        if (in_array($role, ['SUPER_ADMIN', 'ADMIN_DESA', 'DESA'])) {
            $sections[] = "{$step}. 🏗️ **Menu Pembangunan & APBDes:**\n".$this->getBudgetDevelopmentMenuContent($user);
            $step++;
        }

        // 8. Laporan & Audit Log (Non-Warga)
        if ($role !== 'WARGA') {
            $sections[] = "{$step}. 📑 **Menu Laporan & Audit Log:**\n".$this->getAuditReportMenuContent($user);
            $step++;
        }

        // 9. Manajemen User (Admin Desa & Super Admin)
        if (in_array($role, ['SUPER_ADMIN', 'ADMIN_DESA', 'DESA'])) {
            $sections[] = "{$step}. 🛡️ **Menu Manajemen User:**\n".$this->getUserManagementMenuContent($user);
            $step++;
        }

        // 10. Kelola AI Knowledge (Super Admin)
        if ($role === 'SUPER_ADMIN') {
            $sections[] = "{$step}. 🧠 **Menu Kelola AI Knowledge:**\n".$this->getAiKnowledgeMenuContent($user);
            $step++;
        }

        // 11. Pengaturan Akun
        $sections[] = "{$step}. ⚙️ **Menu Pengaturan Akun:**\n".$this->getAccountSettingsMenuContent($user);
        $step++;

        // 12. Koneksi Bot
        $sections[] = "{$step}. 🤖 **Menu Koneksi Bot (WhatsApp & Telegram):**\n".$this->getBotConnectionMenuContent($user);
        $step++;

        // 13. Tombol Darurat SOS (Khusus Warga)
        if ($role === 'WARGA') {
            $isEmergency = Emergency::where('user_id', $user->id)->where('status', '!=', 'RESOLVED')->exists();
            $sosStatus = $isEmergency ? '🚨 **SOS Sedang Aktif!**' : '✅ **Siaga / Tidak ada darurat aktif**';
            $sections[] = "{$step}. 🚨 **Fitur Tombol Darurat (SOS Warga):**\n".
                "• Status SOS Anda: {$sosStatus}\n".
                '• Fungsi: Mengirimkan sinyal darurat instan beserta koordinat lokasi GPS Anda kepada pengurus RT/RW dan perangkat desa.';
            $step++;
        }

        // 14. Pembaruan & Update Terkini dari Dashboard & Database
        $sections[] = "{$step}. 🔄 **Pembaruan & Update Terkini dari Dashboard & Database:**\n".$this->getRecentUpdatesSummary($user);

        $header = "📋 **Keseluruhan Isi Menu Navigasi Sidebar (Akun {$this->getRoleDisplayName($role)}):**\n\n".
            "Berikut adalah isi dan ringkasan data real-time dari seluruh modul menu yang aktif di sidebar Anda:\n\n";

        $footer = "\n\n🔒 **Catatan Keamanan & Privasi Data:**\n".
            '• Sesuai standar keamanan sistem SIWIL dan UU Perlindungan Data Pribadi, seluruh kata sandi (password) dienkripsi satu arah menggunakan hashing kriptografis (**Bcrypt**) dan **TIDAK PERNAH ditampilkan atau dibaca** oleh AI kepada siapa pun demi menjamin kerahasiaan akun.';

        return $header.implode("\n\n---\n\n", $sections).$footer;
    }

    /**
     * Get complete sidebar menu breakdown for all roles, along with real-time database contents
     * and live dashboard updates so AI always knows what has changed.
     */
    private function getSidebarAndDashboardForAllRoles(User $user): string
    {
        $allRolesList = $this->getSidebarMenuListForAllRoles();
        $myMenuContent = $this->getWholeSidebarMenuContents($user);
        $recentUpdates = $this->getRecentUpdatesSummary($user);

        return "📋 **Keseluruhan Isi Menu-Menu Dashboard & Sidebar Tiap Role (Terhubung Database SIWIL):**\n\n".
            "Ya, SIWIL AI terhubung langsung secara real-time ke database untuk membaca dan menjawab seluruh isi dashboard di tiap menu sidebar pada setiap role (Warga, Pengurus RT, Pengurus RW, Administrator Desa, dan Super Admin).\n\n".
            $allRolesList."\n\n".
            "---\n\n".
            "📊 **Isi Data Real-Time Menu Sidebar Anda Saat Ini ({$this->getRoleDisplayName($user->role)}):**\n".
            "_(Termasuk modul Wilayah & Kependudukan, Layanan Surat, Pengaduan, Musyawarah, Pembangunan/APBDes, dll)_\n\n".
            $myMenuContent."\n\n".
            "---\n\n".
            "🔄 **Mekanisme Real-Time AI Mengetahui Pembaruan (Update) Dashboard:**\n".
            "Basis pengetahuan (knowledge) SIWIL AI berasal langsung dari database sistem melalui query Eloquent real-time. Ketika terjadi penambahan atau perubahan data di dashboard mana pun di tiap sidebar, AI langsung mengetahuinya seketika:\n".
            "• **Wilayah & Kependudukan:** Penambahan desa baru, pemekaran RW/RT, registrasi warga, atau mutasi keluarga langsung tersinkronisasi.\n".
            "• **Status Pengajuan Surat:** Status baru (misal: verifikasi RT/RW/Desa) langsung terdeteksi.\n".
            "• **Status Pengaduan:** Tindak lanjut laporan aduan warga langsung tercatat seketika.\n".
            "• **Pengumuman Baru:** Edaran resmi desa yang baru dipublikasikan langsung masuk basis data AI.\n".
            "• **Musyawarah & Polling:** Agenda musyawarah aktif dan hasil suara warga langsung terhitung.\n".
            "• **Pembangunan & APBDes:** Progres fisik proyek dan realisasi anggaran desa selalu mutakhir.\n".
            "• **Data Kependudukan:** Penambahan warga baru atau mutasi keluarga langsung tersinkronisasi.\n".
            "• **Notifikasi & Audit Log:** Peringatan sistem dan rekam jejak audit terkini terbaca otomatis.\n\n".
            $recentUpdates;
    }

    private function getUserManagementMenuContent(User $user): string
    {
        $totalUsers = User::count();
        $rolesCount = User::selectRaw('role, count(*) as total')->groupBy('role')->pluck('total', 'role');
        $warga = $rolesCount['WARGA'] ?? 0;
        $rt = $rolesCount['RT'] ?? 0;
        $rw = $rolesCount['RW'] ?? 0;
        $desa = ($rolesCount['ADMIN_DESA'] ?? 0) + ($rolesCount['DESA'] ?? 0);
        $sa = $rolesCount['SUPER_ADMIN'] ?? 0;

        $recentUsers = User::latest()->take(3)->get();
        $recentList = [];
        foreach ($recentUsers as $u) {
            $roleLabel = match ($u->role) {
                'WARGA' => 'Warga',
                'RT' => 'Pengurus RT',
                'RW' => 'Pengurus RW',
                'ADMIN_DESA', 'DESA' => 'Admin Desa',
                'SUPER_ADMIN' => 'Super Admin',
                default => $u->role,
            };
            $contact = $u->email ?: ($u->phone_number ?: $u->username);
            $recentList[] = "  • **{$u->name}** ({$roleLabel}) — `{$contact}`";
        }

        return "• **Total Akun Pengguna Terdaftar:** **{$totalUsers} Akun**\n".
            "• **Rincian Akun per Role:**\n".
            "  - Warga: **{$warga}** akun\n".
            "  - Pengurus RT: **{$rt}** akun\n".
            "  - Pengurus RW: **{$rw}** akun\n".
            "  - Perangkat / Admin Desa: **{$desa}** akun\n".
            "  - Super Administrator: **{$sa}** akun\n".
            "• **Akun Terdaftar Terbaru:**\n".(empty($recentList) ? "  _Belum ada data akun_\n" : implode("\n", $recentList)."\n").
            '• 🔒 **Proteksi Kata Sandi:** Seluruh kata sandi pengguna dienkripsi satu arah (**Bcrypt**) dan **TIDAK DITAMPILKAN** demi menjaga privasi dan keamanan akun.';
    }

    private function getAccountSettingsMenuContent(User $user): string
    {
        $profile = $this->getUserProfileData($user);

        return "• **Profil Pengguna Login:** **{$profile['name']}**\n".
            "• **NIK:** `{$profile['nik']}`\n".
            "• **Peran / Jabatan:** {$profile['role_label']}\n".
            "• **Nomor Kontak / WhatsApp:** {$profile['phone']}\n".
            "• **Email Akun:** {$profile['email']}\n".
            "• **Wilayah:** RT {$profile['rt']} / RW {$profile['rw']}, Desa {$profile['village']}\n".
            "• **Alamat:** {$profile['address']}\n".
            '• 🔒 **Status Kata Sandi:** Terenkripsi secara aman (Bcrypt) & terlindungi. Pembaruan kata sandi dapat dilakukan melalui formulir Pengaturan Akun.';
    }

    private function getBudgetDevelopmentMenuContent(User $user): string
    {
        $villageId = $user->village_id ?: Village::value('id');
        $queryBudget = Budget::query();
        $queryDev = Development::query();
        if ($user->role !== 'SUPER_ADMIN' && $villageId) {
            $queryBudget->where('village_id', $villageId);
            $queryDev->where('village_id', $villageId);
        }

        $totalIncomeBudget = (clone $queryBudget)->where('type', 'PENDAPATAN')->sum('amount_budget');
        $totalIncomeRealized = (clone $queryBudget)->where('type', 'PENDAPATAN')->sum('amount_realized');
        $totalSpendBudget = (clone $queryBudget)->where('type', 'BELANJA')->sum('amount_budget');
        $totalSpendRealized = (clone $queryBudget)->where('type', 'BELANJA')->sum('amount_realized');

        $devCount = (clone $queryDev)->count();
        $ongoingDevs = (clone $queryDev)->where('status', 'ONGOING')->count();
        $completedDevs = (clone $queryDev)->where('status', 'COMPLETED')->count();

        $recentDevs = (clone $queryDev)->latest()->take(2)->get();
        $devItems = [];
        foreach ($recentDevs as $d) {
            $bud = number_format($d->budget_allocated, 0, ',', '.');
            $devItems[] = "  • **{$d->title}** — Anggaran: Rp {$bud} | Progres: **{$d->progress_percentage}%** ({$d->status})";
        }

        return "• 💰 **Ringkasan Anggaran APBDes:**\n".
            '  - Target Pendapatan: **Rp '.number_format($totalIncomeBudget, 0, ',', '.').'** (Realisasi: Rp '.number_format($totalIncomeRealized, 0, ',', '.').")\n".
            '  - Alokasi Belanja: **Rp '.number_format($totalSpendBudget, 0, ',', '.').'** (Realisasi: Rp '.number_format($totalSpendRealized, 0, ',', '.').")\n".
            "• 🏗️ **Proyek Pembangunan Desa:**\n".
            "  - Total Proyek: **{$devCount} Proyek** ({$ongoingDevs} Berjalan, {$completedDevs} Selesai)\n".
            (empty($devItems) ? "  - _Belum ada proyek pembangunan terdaftar_\n" : implode("\n", $devItems));
    }

    private function getAuditReportMenuContent(User $user): string
    {
        $totalLogs = AuditLog::count();
        $recentLogs = AuditLog::with('user')->latest()->take(3)->get();
        $logItems = [];
        foreach ($recentLogs as $log) {
            $actor = $log->user?->name ?? 'Sistem';
            $time = $log->created_at ? $log->created_at->format('d M Y H:i') : '-';
            $logItems[] = "  • `{$log->action}` pada **{$log->entity_name}** oleh **{$actor}** _({$time})_";
        }

        return "• **Total Rekam Audit Log:** **{$totalLogs} aktivitas tercatat**\n".
            "• **Aktivitas Audit Sistem Terbaru:**\n".
            (empty($logItems) ? "  _Belum ada riwayat aktivitas audit_\n" : implode("\n", $logItems));
    }

    private function getAiKnowledgeMenuContent(User $user): string
    {
        $count = AiKnowledge::count();
        $topics = AiKnowledge::latest()->take(4)->pluck('topic')->implode(', ');

        return "• **Total Basis Pengetahuan AI:** **{$count} Dokumen / Topik Pengetahuan**\n".
            '• **Topik Utama AI Terdaftar:** '.($topics ?: 'Regulasi Desa, SOP Surat, Layanan Pengaduan');
    }

    private function getBotConnectionMenuContent(User $user): string
    {
        $botWa = config('services.whatsapp.bot_number', env('WHATSAPP_BOT_NUMBER', '628981295171'));
        $botTele = config('services.telegram.bot_username', env('TELEGRAM_BOT_USERNAME', 'cevitBot'));

        $waConnected = ! empty($user->whatsapp_number) || ! empty($user->whatsapp_lid);
        $teleConnected = ! empty($user->telegram_id);

        $waStatus = $waConnected ? '✅ **Terhubung**' : '⚪ **Belum Ditautkan**';
        $teleStatus = $teleConnected ? '✅ **Terhubung**' : '⚪ **Belum Ditautkan**';

        return "• 🟢 **WhatsApp Chat Bot:** Nomor Layanan `+{$botWa}`\n".
            "  - Status Akun Anda: {$waStatus}\n".
            "• 🔵 **Telegram Bot:** Akun `@{$botTele}`\n".
            "  - Status Akun Anda: {$teleStatus}\n".
            '• **Fungsi Bot:** Notifikasi otomatis status surat, peringatan aduan darurat, pengumuman desa, dan interaksi cerdas mandiri melalui chat.';
    }

    private function getAnnouncementMenuContent(User $user): string
    {
        $announcements = Announcement::latest()->take(3)->get();
        if ($announcements->isEmpty()) {
            return '• _Belum ada pengumuman desa yang dipublikasikan saat ini._';
        }

        $items = [];
        foreach ($announcements as $a) {
            $date = $a->created_at ? $a->created_at->format('d M Y') : '-';
            $items[] = "  • **{$a->title}** _({$date})_ — ".Str::limit(strip_tags($a->content ?? ''), 75);
        }

        return "• **Pengumuman Resmi Terkini:**\n".implode("\n", $items);
    }

    private function getDocumentMenuContent(User $user): string
    {
        $role = $user->role;
        if ($role === 'WARGA') {
            $resident = $user->resident ?: Resident::where('nik', $user->username)->first();
            $myDocs = $resident ? DocumentRequest::where('resident_id', $resident->id)->latest()->take(3)->get() : collect();
            $totalMyDocs = $resident ? DocumentRequest::where('resident_id', $resident->id)->count() : 0;

            if ($myDocs->isEmpty()) {
                return "• **Total Pengajuan Surat Saya:** **0 berkas**\n  _Anda belum memiliki riwayat pengajuan surat._";
            }

            $items = [];
            foreach ($myDocs as $d) {
                $time = $d->created_at ? $d->created_at->format('d M Y') : '-';
                $items[] = "  • **{$d->type}** — Status: `{$d->status}` _({$time})_";
            }

            return "• **Total Pengajuan Surat Saya:** **{$totalMyDocs} berkas**\n• **Pengajuan Surat Terkini:**\n".implode("\n", $items);
        }

        // RT / RW / Desa / Super Admin
        $query = DocumentRequest::query();
        if ($role === 'RT' && $user->rt_id) {
            $query->whereHas('resident', fn ($q) => $q->where('rt_id', $user->rt_id));
        } elseif ($role === 'RW' && $user->rw_id) {
            $query->whereHas('resident.rt', fn ($q) => $q->where('rw_id', $user->rw_id));
        } elseif (in_array($role, ['ADMIN_DESA', 'DESA']) && $user->village_id) {
            $query->whereHas('resident.rt.rw', fn ($q) => $q->where('village_id', $user->village_id));
        }

        $totalDocs = (clone $query)->count();
        $pendingDocs = (clone $query)->whereIn('status', ['PENDING', 'SUBMITTED', 'WAITING_RT', 'APPROVED_RT', 'WAITING_RW', 'APPROVED_RW'])->count();
        $completedDocs = (clone $query)->whereIn('status', ['COMPLETED', 'APPROVED_VILLAGE', 'SIGNED'])->count();

        return "• **Total Permohonan Surat:** **{$totalDocs} permohonan**\n".
            "• **Menunggu Verifikasi:** **{$pendingDocs} berkas**\n".
            "• **Selesai / Terbit:** **{$completedDocs} berkas**";
    }

    private function getComplaintMenuContent(User $user): string
    {
        $role = $user->role;
        if ($role === 'WARGA') {
            $myComplaints = Complaint::where('user_id', $user->id)->latest()->take(3)->get();
            $totalMy = Complaint::where('user_id', $user->id)->count();

            if ($myComplaints->isEmpty()) {
                return "• **Total Pengaduan Saya:** **0 laporan**\n  _Anda belum pernah mengajukan laporan pengaduan._";
            }

            $items = [];
            foreach ($myComplaints as $c) {
                $items[] = "  • **{$c->title}** — Status: `{$c->status}`";
            }

            return "• **Total Pengaduan Saya:** **{$totalMy} laporan**\n• **Riwayat Aduan Terkini:**\n".implode("\n", $items);
        }

        $query = Complaint::query();
        if ($role === 'RT' && $user->rt_id) {
            $query->where('rt_id', $user->rt_id);
        } elseif ($role === 'RW' && $user->rw_id) {
            $query->where('rw_id', $user->rw_id);
        } elseif (in_array($role, ['ADMIN_DESA', 'DESA']) && $user->village_id) {
            $query->where('village_id', $user->village_id);
        }

        $total = (clone $query)->count();
        $pending = (clone $query)->whereIn('status', ['PENDING', 'OPEN'])->count();
        $process = (clone $query)->where('status', 'IN_PROGRESS')->count();
        $resolved = (clone $query)->where('status', 'RESOLVED')->count();

        return "• **Total Pengaduan Masuk:** **{$total} laporan**\n".
            "• **Status Aduan:** {$pending} Menunggu, {$process} Diproses, {$resolved} Selesai Ditangani";
    }

    private function getPollMenuContent(User $user): string
    {
        $activePolls = Poll::where('status', 'ACTIVE')->with('options')->latest()->take(2)->get();
        $totalPolls = Poll::count();

        if ($activePolls->isEmpty()) {
            return "• **Total Agenda Musyawarah / Polling:** **{$totalPolls} agenda**\n".
                '• _Saat ini tidak ada polling atau musyawarah yang sedang berlangsung._';
        }

        $items = [];
        foreach ($activePolls as $p) {
            $opts = $p->options->pluck('label')->implode(', ');
            $votes = PollVote::where('poll_id', $p->id)->count();
            $items[] = "  • **{$p->title}** (Partisipasi: **{$votes} suara**)\n    _Pilihan: {$opts}_";
        }

        return "• **Total Musyawarah:** **{$totalPolls} agenda**\n• **Polling Warga yang Sedang Aktif:**\n".implode("\n", $items);
    }

    private function getTerritoryResidentMenuContent(User $user): string
    {
        $role = $user->role;
        if ($role === 'SUPER_ADMIN') {
            $vCount = Village::count();
            $rwCount = Rw::count();
            $rtCount = Rt::count();
            $resCount = Resident::count();
            $famCount = Family::count();
            $pria = Resident::where(fn ($q) => $q->where('gender', 'LAKI_LAKI')->orWhere('gender', 'Laki-laki')->orWhere('gender', 'L'))->count();
            $wanita = Resident::where(fn ($q) => $q->where('gender', 'PEREMPUAN')->orWhere('gender', 'Perempuan')->orWhere('gender', 'P'))->count();

            // 1. Wilayah Desa
            $villages = Village::withCount(['rws', 'users'])->get();
            $villageList = [];
            foreach ($villages as $v) {
                $code = $v->code ?? '-';
                $villageList[] = "  • **{$v->name}** (Kode: `{$code}`) — **{$v->rws_count} RW**, **{$v->users_count} Akun**";
            }

            // 2. Hierarki RW & RT
            $rws = Rw::with('rts')->get();
            $hierarchyList = [];
            foreach ($rws as $rw) {
                $rtNums = $rw->rts->pluck('number')->map(fn ($n) => "RT {$n}")->implode(', ') ?: 'Belum ada RT';
                $hierarchyList[] = "  • **RW {$rw->number}** ({$rw->rts->count()} RT): {$rtNums}";
            }

            // 3. Data Kependudukan Terpadu
            $residents = Resident::with(['user', 'rt.rw'])->latest()->take(10)->get();
            $totHasAccount = Resident::has('user')->count();
            $totNoAccount = Resident::doesntHave('user')->count();
            $noAccountResidents = Resident::doesntHave('user')->with('rt.rw')->get();

            $resList = [];
            foreach ($residents as $r) {
                $hp = $r->user?->phone_number ? " | Kontak: `{$r->user->phone_number}`" : '';
                $accountStatus = $r->user ? 'Akun Aktif' : '⚠️ Belum Ada Akun (+ Buat Akun)';
                $resList[] = "  • **{$r->full_name}** (NIK: `{$r->nik}`) — {$r->gender}, RT {$r->rt?->number}/RW {$r->rt?->rw?->number}, Status: `{$r->status}`, Akun: [{$accountStatus}]{$hp}";
            }

            $noAccountText = '';
            if ($noAccountResidents->isNotEmpty()) {
                $noNames = $noAccountResidents->map(fn ($nr) => "**{$nr->full_name}** (RT {$nr->rt?->number}/RW {$nr->rt?->rw?->number})")->implode(', ');
                $noAccountText = "  - Warga Belum Ada Akun: **{$totNoAccount} orang** ({$noNames}) [Tombol: + Buat Akun]\n";
            } else {
                $noAccountText = "  - Warga Belum Ada Akun: **0 orang** (Semua warga memiliki akun)\n";
            }

            return "• 🏢 **Statistik Utama (KPI Cards):**\n".
                "  - Total Desa: **{$vCount} Desa**\n".
                "  - Total RW & RT: **{$rwCount} RW | {$rtCount} RT**\n".
                "  - Total Penduduk: **{$resCount} Jiwa** ({$pria} Laki-laki • {$wanita} Perempuan)\n".
                "  - Kepala Keluarga: **{$famCount} KK**\n".
                "  - Status Akun Login Warga: **{$totHasAccount} Memiliki Akun** | **{$totNoAccount} Belum Ada Akun**\n".
                $noAccountText."\n".
                "• 🗺️ **Wilayah Desa Terdaftar:**\n".(empty($villageList) ? "  _Belum ada desa_\n" : implode("\n", $villageList)."\n")."\n".
                "• 📍 **Hierarki RW & RT:**\n".(empty($hierarchyList) ? "  _Belum ada data hierarki_\n" : implode("\n", $hierarchyList)."\n")."\n".
                "• 👥 **Data Kependudukan Terpadu Terkini:**\n".(empty($resList) ? "  _Belum ada warga terdaftar_\n" : implode("\n", $resList));
        }

        if (in_array($role, ['ADMIN_DESA', 'DESA'])) {
            $vId = $user->village_id ?: Village::value('id');
            $v = Village::with('rws.rts')->find($vId) ?: Village::first();
            $rwCount = $v?->rws?->count() ?? 0;
            $rtCount = $v?->rws?->flatMap->rts->count() ?? 0;
            $resCount = Resident::whereHas('rt.rw', fn ($q) => $q->where('village_id', $vId))->count();
            $famCount = Family::whereHas('rt.rw', fn ($q) => $q->where('village_id', $vId))->count();
            $pria = Resident::whereHas('rt.rw', fn ($q) => $q->where('village_id', $vId))->where(fn ($q) => $q->where('gender', 'LAKI_LAKI')->orWhere('gender', 'Laki-laki')->orWhere('gender', 'L'))->count();
            $wanita = Resident::whereHas('rt.rw', fn ($q) => $q->where('village_id', $vId))->where(fn ($q) => $q->where('gender', 'PEREMPUAN')->orWhere('gender', 'Perempuan')->orWhere('gender', 'P'))->count();

            $rws = $v ? $v->rws()->with('rts')->get() : collect();
            $hierarchyList = [];
            foreach ($rws as $rw) {
                $rtNums = $rw->rts->pluck('number')->map(fn ($n) => "RT {$n}")->implode(', ') ?: 'Belum ada RT';
                $hierarchyList[] = "  • **RW {$rw->number}** ({$rw->rts->count()} RT): {$rtNums}";
            }

            $residents = Resident::whereHas('rt.rw', fn ($q) => $q->where('village_id', $vId))->with(['user', 'rt.rw'])->latest()->take(5)->get();
            $resList = [];
            foreach ($residents as $r) {
                $hp = $r->user?->phone_number ? " | Kontak: `{$r->user->phone_number}`" : '';
                $resList[] = "  • **{$r->full_name}** (NIK: `{$r->nik}`) — {$r->gender}, RT {$r->rt?->number}/RW {$r->rt?->rw?->number}, Status: `{$r->status}`{$hp}";
            }

            $vName = $v?->name ?? 'Desa';
            $vCode = $v?->code ?? '-';
            $vUsers = $v ? $v->users()->count() : 0;

            return "• 🏢 **Statistik Utama Wilayah {$vName} (KPI Cards):**\n".
                "  - Total Desa: **1 Desa** ({$vName}, Kode: `{$vCode}`, **{$vUsers} Akun**)\n".
                "  - Total RW & RT: **{$rwCount} RW | {$rtCount} RT**\n".
                "  - Total Penduduk: **{$resCount} Jiwa** ({$pria} Laki-laki • {$wanita} Perempuan)\n".
                "  - Kepala Keluarga: **{$famCount} KK**\n\n".
                "• 📍 **Hierarki RW & RT di {$vName}:**\n".(empty($hierarchyList) ? "  _Belum ada data hierarki_\n" : implode("\n", $hierarchyList)."\n")."\n".
                "• 👥 **Data Kependudukan Terpadu Terkini:**\n".(empty($resList) ? "  _Belum ada warga terdaftar_\n" : implode("\n", $resList));
        }

        if ($role === 'RW') {
            $rw = $user->rw_id ? Rw::with('rts')->find($user->rw_id) : null;
            $rtCount = $rw?->rts?->count() ?? 0;
            $resCount = Resident::whereHas('rt', fn ($q) => $q->where('rw_id', $user->rw_id))->count();
            $famCount = Family::whereHas('rt', fn ($q) => $q->where('rw_id', $user->rw_id))->count();
            $pria = Resident::whereHas('rt', fn ($q) => $q->where('rw_id', $user->rw_id))->where(fn ($q) => $q->where('gender', 'LAKI_LAKI')->orWhere('gender', 'Laki-laki')->orWhere('gender', 'L'))->count();
            $wanita = Resident::whereHas('rt', fn ($q) => $q->where('rw_id', $user->rw_id))->where(fn ($q) => $q->where('gender', 'PEREMPUAN')->orWhere('gender', 'Perempuan')->orWhere('gender', 'P'))->count();

            $rtNums = $rw?->rts->pluck('number')->map(fn ($n) => "RT {$n}")->implode(', ') ?: 'Belum ada RT';

            $residents = Resident::whereHas('rt', fn ($q) => $q->where('rw_id', $user->rw_id))->with(['user', 'rt'])->latest()->take(5)->get();
            $resList = [];
            foreach ($residents as $r) {
                $hp = $r->user?->phone_number ? " | Kontak: `{$r->user->phone_number}`" : '';
                $resList[] = "  • **{$r->full_name}** (NIK: `{$r->nik}`) — {$r->gender}, RT {$r->rt?->number}, Status: `{$r->status}`{$hp}";
            }

            return "• 🏢 **Statistik Utama Wilayah RW {$rw?->number} (KPI Cards):**\n".
                "  - Total RT Binaan: **{$rtCount} RT** ({$rtNums})\n".
                "  - Total Warga: **{$resCount} Jiwa** ({$pria} Laki-laki • {$wanita} Perempuan)\n".
                "  - Total Kepala Keluarga: **{$famCount} KK**\n\n".
                "• 👥 **Daftar Warga Terkini di RW {$rw?->number}:**\n".(empty($resList) ? "  _Belum ada warga terdaftar_\n" : implode("\n", $resList));
        }

        if ($role === 'RT') {
            $rt = $user->rt_id ? Rt::find($user->rt_id) : null;
            $resCount = Resident::where('rt_id', $user->rt_id)->count();
            $famCount = Family::where('rt_id', $user->rt_id)->count();
            $pria = Resident::where('rt_id', $user->rt_id)->where(fn ($q) => $q->where('gender', 'LAKI_LAKI')->orWhere('gender', 'Laki-laki')->orWhere('gender', 'L'))->count();
            $wanita = Resident::where('rt_id', $user->rt_id)->where(fn ($q) => $q->where('gender', 'PEREMPUAN')->orWhere('gender', 'Perempuan')->orWhere('gender', 'P'))->count();

            $residents = Resident::where('rt_id', $user->rt_id)->with('user')->latest()->take(5)->get();
            $resList = [];
            foreach ($residents as $r) {
                $hp = $r->user?->phone_number ? " | Kontak: `{$r->user->phone_number}`" : '';
                $resList[] = "  • **{$r->full_name}** (NIK: `{$r->nik}`) — {$r->gender}, Status: `{$r->status}`{$hp}";
            }

            return "• 🏢 **Statistik Utama Wilayah RT {$rt?->number} (KPI Cards):**\n".
                "  - Total Penduduk: **{$resCount} Jiwa** ({$pria} Laki-laki • {$wanita} Perempuan)\n".
                "  - Total Kepala Keluarga: **{$famCount} KK**\n\n".
                "• 👥 **Daftar Warga Terkini di RT {$rt?->number}:**\n".(empty($resList) ? "  _Belum ada warga terdaftar_\n" : implode("\n", $resList));
        }

        return '';
    }

    private function getFamilyMenuContent(User $user): string
    {
        $resident = $user->resident ?: Resident::with(['family.members'])->where('nik', $user->username)->first();
        $family = $resident?->family;

        if (! $family) {
            return '• _Data anggota keluarga belum tertaut pada Kartu Keluarga (KK) Anda._';
        }

        $members = $family->members()->get();
        $memberItems = [];
        foreach ($members as $m) {
            $status = $m->status ?: 'Anggota';
            $memberItems[] = "  • **{$m->full_name}** ({$status}) — NIK: `{$m->nik}`";
        }

        return "• **No. Kartu Keluarga (KK):** `{$family->kk_number}`\n".
            "• **Daftar Anggota Keluarga Terdaftar:**\n".implode("\n", $memberItems);
    }

    private function getRoleDisplayName(string $role): string
    {
        return match ($role) {
            'WARGA' => 'Warga',
            'RT' => 'Pengurus RT',
            'RW' => 'Pengurus RW',
            'ADMIN_DESA', 'DESA' => 'Administrator Desa',
            'SUPER_ADMIN' => 'Super Administrator',
            default => $role,
        };
    }

    /**
     * Get real-time dashboard summary matching what the user sees on their role dashboard.
     */
    private function getDashboardSummary(User $user): string
    {
        $role = $user->role;

        if ($role === 'WARGA') {
            $resident = Resident::where('nik', $user->username)->first() ?: $user->resident;
            $totalDocs = DocumentRequest::accessibleBy($user)->count();
            $pendingDocs = DocumentRequest::accessibleBy($user)->whereIn('status', ['PENDING', 'DIAJUKAN_RT', 'DIAJUKAN_RW', 'DIAJUKAN_DESA'])->count();
            $completedDocs = DocumentRequest::accessibleBy($user)->whereIn('status', ['SELESAI', 'DISETUJUI', 'COMPLETED'])->count();

            $totalComplaints = Complaint::where('user_id', $user->id)->count();
            $processingComplaints = Complaint::where('user_id', $user->id)->whereIn('status', ['PENDING', 'PROCESSING', 'DIPROSES'])->count();
            $resolvedComplaints = Complaint::where('user_id', $user->id)->whereIn('status', ['RESOLVED', 'SELESAI'])->count();

            $activePollsCount = Poll::where('status', 'ACTIVE')->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))->count();
            $announcementsCount = Announcement::where('is_active', true)->count();
            $hasActiveEmergency = Emergency::where('user_id', $user->id)->where('status', 'ACTIVE')->exists();
            $sosText = $hasActiveEmergency ? '🚨 Anda memiliki panggilan darurat (SOS) aktif!' : '✅ Status aman (0 SOS aktif).';

            $kkNum = $resident?->family?->kk_number ?? $user->kk_number ?? '-';
            $familyCount = $resident?->family_id ? Resident::where('family_id', $resident->family_id)->count() : 1;

            return "📊 **Ringkasan Data & Informasi Dashboard Warga:**\n\n".
                "1. **Layanan Surat Mandiri Saya:**\n".
                "   • Total Pengajuan: **{$totalDocs} berkas**\n".
                "   • Menunggu / Diproses: **{$pendingDocs} berkas**\n".
                "   • Selesai / Terbit: **{$completedDocs} berkas**\n\n".
                "2. **Laporan & Pengaduan Saya:**\n".
                "   • Total Pengaduan: **{$totalComplaints} laporan**\n".
                "   • Sedang Diproses: **{$processingComplaints} laporan**\n".
                "   • Selesai Ditangani: **{$resolvedComplaints} laporan**\n\n".
                "3. **Musyawarah & Polling Warga:**\n".
                "   • Polling Aktif: **{$activePollsCount} agenda**\n\n".
                "4. **Pengumuman & Informasi Desa:**\n".
                "   • Pengumuman Resmi: **{$announcementsCount} pengumuman aktif**\n\n".
                "5. **Data Keluarga Terdaftar:**\n".
                "   • No. KK: `{$kkNum}` ({$familyCount} anggota keluarga)\n\n".
                "6. **Tombol Bantuan Darurat (SOS):**\n".
                "   • {$sosText}";
        }

        if ($role === 'RT') {
            $rt = $user->rt_id ? Rt::find($user->rt_id) : null;
            $rtNum = $rt?->number ?? '-';
            $totWarga = $rt ? Resident::where('rt_id', $rt->id)->count() : 0;
            $totPria = $rt ? Resident::where('rt_id', $rt->id)->where(fn ($q) => $q->where('gender', 'LAKI_LAKI')->orWhere('gender', 'Laki-laki')->orWhere('gender', 'L'))->count() : 0;
            $totWanita = $rt ? Resident::where('rt_id', $rt->id)->where(fn ($q) => $q->where('gender', 'PEREMPUAN')->orWhere('gender', 'Perempuan')->orWhere('gender', 'P'))->count() : 0;
            $totKk = $rt ? Family::where('rt_id', $rt->id)->count() : 0;

            $totalDocs = DocumentRequest::accessibleBy($user)->count();
            $pendingDocs = DocumentRequest::accessibleBy($user)->whereIn('status', ['DIAJUKAN_RT', 'PENDING'])->count();

            $totalComplaints = $rt ? Complaint::where('rt_id', $rt->id)->count() : 0;
            $pendingComplaints = $rt ? Complaint::where('rt_id', $rt->id)->whereIn('status', ['PENDING', 'PROCESSING'])->count() : 0;

            $activePollsCount = Poll::where('status', 'ACTIVE')->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))->count();
            $activeEmergencies = Emergency::where('status', 'ACTIVE')->count();
            $sosText = $activeEmergencies > 0 ? "🚨 Terdapat {$activeEmergencies} panggilan darurat (SOS) aktif!" : '✅ Situasi aman (0 SOS aktif).';

            return "📊 **Ringkasan Data & Informasi Dashboard RT {$rtNum}:**\n\n".
                "1. **Statistik Kependudukan RT {$rtNum}:**\n".
                "   • Total Penduduk: **{$totWarga} Jiwa** ({$totPria} Laki-laki, {$totWanita} Perempuan)\n".
                "   • Total Kartu Keluarga: **{$totKk} KK**\n\n".
                "2. **Layanan Surat Warga:**\n".
                "   • Total Permohonan Surat: **{$totalDocs} berkas**\n".
                "   • Menunggu Verifikasi RT: **{$pendingDocs} berkas**\n\n".
                "3. **Pengaduan Warga RT:**\n".
                "   • Total Pengaduan Masuk: **{$totalComplaints} laporan**\n".
                "   • Pending / Diproses: **{$pendingComplaints} laporan**\n\n".
                "4. **Musyawarah & Polling:**\n".
                "   • Polling Aktif: **{$activePollsCount} agenda**\n\n".
                "5. **Status Panggilan Darurat (SOS):**\n".
                "   • {$sosText}";
        }

        if ($role === 'RW') {
            $rw = $user->rw_id ? Rw::with('rts')->find($user->rw_id) : null;
            $rwNum = $rw?->number ?? '-';
            $totalRts = $rw?->rts->count() ?? 0;
            $totalWargaRw = $rw ? Resident::whereHas('rt', fn ($q) => $q->where('rw_id', $rw->id))->count() : 0;
            $totalKkRw = $rw ? Family::whereHas('rt', fn ($q) => $q->where('rw_id', $rw->id))->count() : 0;

            $totalDocs = DocumentRequest::accessibleBy($user)->count();
            $pendingDocs = DocumentRequest::accessibleBy($user)->where('status', 'APPROVED_RT')->count();

            $totalComplaints = $rw ? Complaint::where('rw_id', $rw->id)->count() : 0;
            $pendingComplaints = $rw ? Complaint::where('rw_id', $rw->id)->whereIn('status', ['PENDING', 'PROCESSING'])->count() : 0;

            $activeEmergencies = Emergency::where('status', 'ACTIVE')->count();
            $sosText = $activeEmergencies > 0 ? "🚨 Terdapat {$activeEmergencies} panggilan darurat (SOS) aktif!" : '✅ Situasi aman (0 SOS aktif).';

            return "📊 **Ringkasan Data & Informasi Dashboard RW {$rwNum}:**\n\n".
                "1. **Statistik Wilayah RW {$rwNum}:**\n".
                "   • Total Penduduk: **{$totalWargaRw} Jiwa** dalam **{$totalKkRw} KK** di **{$totalRts} RT binaan**\n\n".
                "2. **Layanan Surat RW:**\n".
                "   • Total Pengajuan Surat Masuk: **{$totalDocs} berkas**\n".
                "   • Menunggu Verifikasi RW: **{$pendingDocs} berkas**\n\n".
                "3. **Pengaduan Warga RW:**\n".
                "   • Total Pengaduan Masuk: **{$totalComplaints} laporan**\n".
                "   • Pending / Sedang Ditangani: **{$pendingComplaints} laporan**\n\n".
                "4. **Status Panggilan Darurat (SOS):**\n".
                "   • {$sosText}";
        }

        if (in_array($role, ['ADMIN_DESA', 'DESA'])) {
            $village = $user->village_id ? Village::with('rws.rts')->find($user->village_id) : Village::with('rws.rts')->first();
            $vLabel = $village ? (str_starts_with(strtolower(trim($village->name)), 'desa ') ? trim($village->name) : 'Desa '.trim($village->name)) : 'Desa';
            $totalRw = $village?->rws->count() ?? 0;
            $totalRt = $village?->rws->flatMap->rts->count() ?? 0;
            $totalWarga = $village ? Resident::whereHas('rt.rw', fn ($q) => $q->where('village_id', $village->id))->count() : 0;
            $totalKk = $village ? Family::whereHas('rt.rw', fn ($q) => $q->where('village_id', $village->id))->count() : 0;
            $totalPria = $village ? Resident::whereHas('rt.rw', fn ($q) => $q->where('village_id', $village->id))->where(fn ($q) => $q->where('gender', 'LAKI_LAKI')->orWhere('gender', 'Laki-laki')->orWhere('gender', 'L'))->count() : 0;
            $totalWanita = $village ? Resident::whereHas('rt.rw', fn ($q) => $q->where('village_id', $village->id))->where(fn ($q) => $q->where('gender', 'PEREMPUAN')->orWhere('gender', 'Perempuan')->orWhere('gender', 'P'))->count() : 0;

            $totalComplaints = $village ? Complaint::where('village_id', $village->id)->count() : 0;
            $totalDocs = $village ? DocumentRequest::whereHas('resident.rt.rw', fn ($q) => $q->where('village_id', $village->id))->count() : 0;
            $pendingDesaDocs = $village ? DocumentRequest::whereHas('resident.rt.rw', fn ($q) => $q->where('village_id', $village->id))->where('status', 'APPROVED_RW')->count() : 0;

            $activeEmergencies = Emergency::where('status', 'ACTIVE')->count();
            $sosText = $activeEmergencies > 0 ? "🚨 Terdapat {$activeEmergencies} panggilan darurat (SOS) aktif di wilayah desa!" : '✅ Situasi aman (0 SOS aktif).';

            return "📊 **Ringkasan Data & Informasi Dashboard Administrator {$vLabel}:**\n\n".
                "1. **Statistik Demografi Penduduk Desa:**\n".
                "   • Total Wilayah: **{$totalRw} RW** dan **{$totalRt} RT**\n".
                "   • Total Penduduk: **{$totalWarga} Jiwa** ({$totalPria} Pria, {$totalWanita} Wanita)\n".
                "   • Total Kartu Keluarga: **{$totalKk} KK**\n\n".
                "2. **Layanan Surat Desa:**\n".
                "   • Total Permohonan Surat: **{$totalDocs} berkas**\n".
                "   • Menunggu Persetujuan / TTE Desa: **{$pendingDesaDocs} berkas**\n\n".
                "3. **Pengaduan Masyarakat Desa:**\n".
                "   • Total Laporan Masuk: **{$totalComplaints} laporan**\n\n".
                "4. **Status Panggilan Darurat (SOS):**\n".
                "   • {$sosText}";
        }

        // SUPER_ADMIN
        $totalVillages = Village::count();
        $totalRws = Rw::count();
        $totalRts = Rt::count();
        $totalResidents = Resident::count();
        $totalPria = Resident::where(fn ($q) => $q->where('gender', 'LAKI_LAKI')->orWhere('gender', 'Laki-laki')->orWhere('gender', 'L'))->count();
        $totalWanita = Resident::where(fn ($q) => $q->where('gender', 'PEREMPUAN')->orWhere('gender', 'Perempuan')->orWhere('gender', 'P'))->count();
        $totalFamilies = Family::count();
        $totalUsers = User::count();
        $totalComplaints = Complaint::count();
        $totalDocs = DocumentRequest::count();
        $totalPolls = Poll::count();
        $totalAnnouncements = Announcement::count();
        $totHasAcc = Resident::has('user')->count();
        $totNoAcc = Resident::doesntHave('user')->count();
        $activeEmergenciesCount = Emergency::where('status', 'ACTIVE')->count();
        $sosText = $activeEmergenciesCount > 0 ? "🚨 {$activeEmergenciesCount} Panggilan Darurat SOS Aktif!" : '✅ Aman (0 SOS Aktif).';

        return "📊 **Ringkasan Data & Informasi Dashboard Super Admin:**\n\n".
            "1. **Statistik Wilayah & Kependudukan Global:**\n".
            "   • Master Wilayah: **{$totalVillages} Desa/Kelurahan**, **{$totalRws} RW**, **{$totalRts} RT**\n".
            "   • Total Penduduk: **{$totalResidents} Jiwa** ({$totalPria} Pria, {$totalWanita} Wanita) dalam **{$totalFamilies} KK**\n".
            "   • Status Akun Login Warga: **{$totHasAcc} memiliki akun**, **{$totNoAcc} belum ada akun**\n\n".
            "2. **Statistik Pengguna & Keamanan:**\n".
            "   • Total Pengguna Terdaftar: **{$totalUsers} Akun**\n".
            "   • Status Darurat (SOS): **{$sosText}**\n\n".
            "3. **Statistik Layanan Sistem:**\n".
            "   • Total Pengajuan Surat: **{$totalDocs} permohonan**\n".
            "   • Total Laporan Pengaduan: **{$totalComplaints} tiket**\n".
            "   • Musyawarah & Polling: **{$totalPolls} agenda**\n".
            "   • Pengumuman Resmi: **{$totalAnnouncements} pengumuman**";
    }

    /**
     * Get recent updates from the dashboard and database for the user.
     */
    private function getRecentUpdatesSummary(User $user): string
    {
        $role = $user->role;
        $updates = [];

        // 1. In-App Notifications
        $notifs = AppNotification::where('user_id', $user->id)->latest()->take(5)->get();
        if ($notifs->isNotEmpty()) {
            $notifItems = [];
            foreach ($notifs as $n) {
                $statusRead = $n->is_read ? '✓ Dibaca' : '🔔 Baru';
                $time = $n->created_at->format('d M Y H:i');
                $notifItems[] = "• [{$statusRead}] **{$n->title}**: {$n->message} _({$time})_";
            }
            $updates[] = "📬 **Notifikasi Sistem Terkini:**\n".implode("\n", $notifItems);
        }

        // 2. Role-specific recent updates from database
        if ($role === 'WARGA') {
            $resident = Resident::where('nik', $user->username)->first() ?: $user->resident;
            if ($resident) {
                $recentDocs = DocumentRequest::where('resident_id', $resident->id)->latest('updated_at')->take(3)->get();
                if ($recentDocs->isNotEmpty()) {
                    $docItems = [];
                    foreach ($recentDocs as $d) {
                        $time = $d->updated_at ? $d->updated_at->format('d M Y H:i') : $d->created_at->format('d M Y H:i');
                        $docItems[] = "• **{$d->type}** — Status: `{$d->status}` _(Pembaruan: {$time})_";
                    }
                    $updates[] = "📄 **Pembaruan Status Pengajuan Surat Anda:**\n".implode("\n", $docItems);
                }
            }

            $recentComplaints = Complaint::where('user_id', $user->id)->latest('updated_at')->take(3)->get();
            if ($recentComplaints->isNotEmpty()) {
                $compItems = [];
                foreach ($recentComplaints as $c) {
                    $time = $c->updated_at ? $c->updated_at->format('d M Y H:i') : $c->created_at->format('d M Y H:i');
                    $compItems[] = "• **{$c->title}** — Status: `{$c->status}` _(Pembaruan: {$time})_";
                }
                $updates[] = "📢 **Pembaruan Status Pengaduan Anda:**\n".implode("\n", $compItems);
            }

            $recentAnnounce = Announcement::where('is_active', true)->latest('created_at')->take(2)->get();
            if ($recentAnnounce->isNotEmpty()) {
                $anItems = [];
                foreach ($recentAnnounce as $a) {
                    $time = $a->created_at->format('d M Y');
                    $anItems[] = "• **{$a->title}** _(Diterbitkan: {$time})_";
                }
                $updates[] = "📣 **Pengumuman Baru Desa:**\n".implode("\n", $anItems);
            }

            $recentPolls = Poll::where('status', 'ACTIVE')->latest('created_at')->take(2)->get();
            if ($recentPolls->isNotEmpty()) {
                $pItems = [];
                foreach ($recentPolls as $p) {
                    $exp = $p->expires_at ? $p->expires_at->format('d M Y') : 'Tanpa batas waktu';
                    $pItems[] = "• **{$p->title}** _(Batas: {$exp})_";
                }
                $updates[] = "🗳️ **Musyawarah/Polling Baru:**\n".implode("\n", $pItems);
            }
        } elseif ($role === 'RT') {
            $rtId = $user->rt_id;
            if ($rtId) {
                $recentDocs = DocumentRequest::whereHas('resident', fn ($q) => $q->where('rt_id', $rtId))->latest('updated_at')->take(3)->get();
                if ($recentDocs->isNotEmpty()) {
                    $docItems = [];
                    foreach ($recentDocs as $d) {
                        $pName = $d->resident?->full_name ?? 'Warga';
                        $time = $d->updated_at ? $d->updated_at->format('d M Y H:i') : $d->created_at->format('d M Y H:i');
                        $docItems[] = "• **{$d->type}** (Pemohon: {$pName}) — Status: `{$d->status}` _(Update: {$time})_";
                    }
                    $updates[] = "📄 **Pembaruan Pengajuan Surat Warga RT:**\n".implode("\n", $docItems);
                }

                $recentComplaints = Complaint::where('rt_id', $rtId)->latest('updated_at')->take(3)->get();
                if ($recentComplaints->isNotEmpty()) {
                    $compItems = [];
                    foreach ($recentComplaints as $c) {
                        $pelapor = $c->user?->name ?? 'Warga';
                        $time = $c->updated_at ? $c->updated_at->format('d M Y H:i') : $c->created_at->format('d M Y H:i');
                        $compItems[] = "• **{$c->title}** (Pelapor: {$pelapor}) — Status: `{$c->status}` _(Update: {$time})_";
                    }
                    $updates[] = "📢 **Pembaruan Pengaduan Warga RT:**\n".implode("\n", $compItems);
                }

                $newResidents = Resident::where('rt_id', $rtId)->latest('created_at')->take(2)->get();
                if ($newResidents->isNotEmpty()) {
                    $resItems = [];
                    foreach ($newResidents as $r) {
                        $resItems[] = "• **{$r->full_name}** (NIK: {$r->nik}) _(Terdaftar: {$r->created_at->format('d M Y')})_";
                    }
                    $updates[] = "👥 **Warga Baru Terdaftar di RT:**\n".implode("\n", $resItems);
                }
            }
        } elseif ($role === 'RW') {
            $rwId = $user->rw_id;
            if ($rwId) {
                $recentDocs = DocumentRequest::whereHas('resident.rt', fn ($q) => $q->where('rw_id', $rwId))->latest('updated_at')->take(3)->get();
                if ($recentDocs->isNotEmpty()) {
                    $docItems = [];
                    foreach ($recentDocs as $d) {
                        $pName = $d->resident?->full_name ?? 'Warga';
                        $rtNum = $d->resident?->rt?->number ?? '-';
                        $time = $d->updated_at ? $d->updated_at->format('d M Y H:i') : $d->created_at->format('d M Y H:i');
                        $docItems[] = "• **{$d->type}** (Pemohon: {$pName}, RT {$rtNum}) — Status: `{$d->status}` _(Update: {$time})_";
                    }
                    $updates[] = "📄 **Pembaruan Surat di Lingkungan RW:**\n".implode("\n", $docItems);
                }

                $recentComplaints = Complaint::where('rw_id', $rwId)->latest('updated_at')->take(3)->get();
                if ($recentComplaints->isNotEmpty()) {
                    $compItems = [];
                    foreach ($recentComplaints as $c) {
                        $time = $c->updated_at ? $c->updated_at->format('d M Y H:i') : $c->created_at->format('d M Y H:i');
                        $compItems[] = "• **{$c->title}** — Status: `{$c->status}` _(Update: {$time})_";
                    }
                    $updates[] = "📢 **Pembaruan Pengaduan di Wilayah RW:**\n".implode("\n", $compItems);
                }
            }
        } elseif (in_array($role, ['ADMIN_DESA', 'DESA'])) {
            $vId = $user->village_id ?: Village::value('id');
            if ($vId) {
                $recentDocs = DocumentRequest::whereHas('resident.rt.rw', fn ($q) => $q->where('village_id', $vId))->latest('updated_at')->take(3)->get();
                if ($recentDocs->isNotEmpty()) {
                    $docItems = [];
                    foreach ($recentDocs as $d) {
                        $time = $d->updated_at ? $d->updated_at->format('d M Y H:i') : $d->created_at->format('d M Y H:i');
                        $docItems[] = "• **{$d->type}** ({$d->resident?->full_name}) — Status: `{$d->status}` _(Update: {$time})_";
                    }
                    $updates[] = "📄 **Pembaruan Surat Tingkat Desa:**\n".implode("\n", $docItems);
                }

                $recentComplaints = Complaint::where('village_id', $vId)->latest('updated_at')->take(3)->get();
                if ($recentComplaints->isNotEmpty()) {
                    $compItems = [];
                    foreach ($recentComplaints as $c) {
                        $time = $c->updated_at ? $c->updated_at->format('d M Y H:i') : $c->created_at->format('d M Y H:i');
                        $compItems[] = "• **{$c->title}** — Status: `{$c->status}` _(Update: {$time})_";
                    }
                    $updates[] = "📢 **Pembaruan Pengaduan Desa:**\n".implode("\n", $compItems);
                }

                $recentDevs = Development::where('village_id', $vId)->latest('updated_at')->take(2)->get();
                if ($recentDevs->isNotEmpty()) {
                    $devItems = [];
                    foreach ($recentDevs as $dev) {
                        $devItems[] = "• **{$dev->title}** (Status: {$dev->status}, Progres: {$dev->progress_percentage}%)";
                    }
                    $updates[] = "🏗️ **Pembaruan Pembangunan Desa:**\n".implode("\n", $devItems);
                }

                $recentAudit = AuditLog::whereHas('user', fn ($q) => $q->where('village_id', $vId))->latest()->take(3)->get();
                if ($recentAudit->isNotEmpty()) {
                    $auditItems = [];
                    foreach ($recentAudit as $log) {
                        $auditItems[] = "• [{$log->created_at->format('d/m H:i')}] {$log->action} pada {$log->entity_name}";
                    }
                    $updates[] = "📝 **Aktivitas Audit Log Terbaru Desa:**\n".implode("\n", $auditItems);
                }
            }
        } else {
            // SUPER_ADMIN
            $recentAudit = AuditLog::with('user')->latest()->take(5)->get();
            if ($recentAudit->isNotEmpty()) {
                $auditItems = [];
                foreach ($recentAudit as $log) {
                    $actor = $log->user?->name ?? 'Sistem';
                    $auditItems[] = "• [{$log->created_at->format('d/m/Y H:i')}] **{$actor}**: {$log->action} pada {$log->entity_name}";
                }
                $updates[] = "📝 **Log Aktivitas Audit Sistem Terkini:**\n".implode("\n", $auditItems);
            }

            $recentUsers = User::latest('created_at')->take(3)->get();
            if ($recentUsers->isNotEmpty()) {
                $userItems = [];
                foreach ($recentUsers as $u) {
                    $userItems[] = "• **{$u->username}** ({$u->name}) — Role: `{$u->role}` _(Daftar: {$u->created_at->format('d M Y')})_";
                }
                $updates[] = "👤 **Akun Pengguna Baru Terdaftar:**\n".implode("\n", $userItems);
            }

            $recentDocs = DocumentRequest::with('resident')->latest('updated_at')->take(3)->get();
            if ($recentDocs->isNotEmpty()) {
                $docItems = [];
                foreach ($recentDocs as $d) {
                    $time = $d->updated_at ? $d->updated_at->format('d M Y H:i') : $d->created_at->format('d M Y H:i');
                    $docItems[] = "• **{$d->type}** ({$d->resident?->full_name}) — Status: `{$d->status}` _(Update: {$time})_";
                }
                $updates[] = "📄 **Pembaruan Surat Terkini:**\n".implode("\n", $docItems);
            }
        }

        if (empty($updates)) {
            return "📌 **Pembaruan Terkini dari Dashboard & Database:**\n\n".
                'Belum ada pembaruan aktivitas atau perubahan data baru yang tercatat saat ini. Seluruh data dashboard dalam kondisi mutakhir.';
        }

        return "🔄 **Pembaruan Terkini dari Dashboard & Database:**\n\n".implode("\n\n", $updates);
    }

    /**
     * Build context and strict system prompts scoped by user role.
     */
    private function buildScopedContext(User $user, ?string $message = null): array
    {
        $role = $user->role;
        $profile = $this->getUserProfileData($user);

        $context = "Role Pengguna: {$role}\n".
            "PROFIL & DATA DIRI PENGGUNA YANG SEDANG BERTANYA (TERDATA RESMI DI DATABASE SIWIL):\n".
            "- Nama Lengkap: {$profile['name']}\n".
            "- NIK: {$profile['nik']}\n".
            "- Status/Peran: {$profile['role_label']}\n".
            "- Jenis Kelamin: {$profile['gender']}\n".
            "- Tempat, Tanggal Lahir: {$profile['birth_info']}\n".
            "- Agama: {$profile['religion']}\n".
            "- Status Perkawinan: {$profile['marital_status']}\n".
            "- Pendidikan Terakhir: {$profile['education']}\n".
            "- Pekerjaan: {$profile['occupation']}\n".
            "- Status Kependudukan: {$profile['status']} ({$profile['family_status']})\n".
            "- No. Kartu Keluarga (KK): {$profile['kk_number']}\n".
            "- Alamat Lengkap: {$profile['address']}\n".
            "- Wilayah Administratif: RT {$profile['rt']} / RW {$profile['rw']}, Desa {$profile['village']}\n".
            "- No. Telepon/WA: {$profile['phone']}\n".
            "- Email: {$profile['email']}\n\n";

        $contactsText = '';
        $extraData = ['profile' => $profile];
        $systemPrompt = "Kamu adalah SIWIL AI, Asisten Customer Service Sistem Informasi Wilayah Terpadu. Bersikaplah ramah, sopan, ringkas, dan jelas.\n".
            "IDENTITAS DIRI AI:\n".
            "- Nama kamu adalah SIWIL AI, asisten virtual cerdas Sistem Informasi Wilayah Terpadu (SIWIL).\n".
            "- Jika pengguna bertanya 'siapa kamu', 'kamu siapa', 'siapa anda', 'siapa dirimu', atau menanyakan identitasmu, jelaskan dengan ramah dan lugas bahwa kamu adalah SIWIL AI yang bertugas membantu warga dan pengurus dalam mengakses informasi kependudukan, pengajuan surat, pengaduan, kontak pengurus wilayah, dan data sistem secara cepat dan akurat.\n\n".
            "PENGENALAN PENGGUNA:\n".
            "- Kamu MENGENALI PENGGUNA yang sedang bertanya: {$profile['name']} ({$profile['role_label']}). Seluruh data diri pengguna di atas telah terdata resmi di database SIWIL.\n".
            "- ATURAN PENGENALAN IDENTITAS & DATA DIRI:\n".
            "  * Jika pengguna HANYA menanyakan 'siapa saya', 'siapa aku', atau bukan meminta data diri lengkap, HANYA sebutkan Nama Lengkap dan Jabatan/Peran pengguna saja (contoh: 'Anda terdaftar sebagai {$profile['name']} dengan jabatan/peran {$profile['role_label']}').\n".
            "  * Jika pengguna meminta 'data diri', 'profil', 'biodata', atau menanyakan data spesifik dirinya (seperti tempat/tanggal lahir, agama, status perkawinan, pendidikan, pekerjaan, alamat, NIK, KK, nomor HP), JAWAB LENGKAP DAN DETAIL sesuai data diri resmi pengguna di database yang tertera di atas.\n\n";
        $systemPrompt .= "PRINSIP UTAMA: MENYESUAIKAN PERSIS DENGAN PROMPT / PERTANYAAN USER (JANGAN DILEBIH-LEBIHKAN / DIKURANGKAN):\n".
            "- BACA & CERNA INPUT USER TERLEBIH DAHULU dengan teliti sebelum menyusun jawaban.\n".
            "- Jawab HANYA apa yang ditanyakan user secara proporsional. JANGAN melebih-lebihkan (menambahkan informasi yang tidak relevan / tidak diminta) dan JANGAN mengurangkan (memotong atau menghilangkan informasi penting yang memang diminta).\n".
            "- KETENTUAN KHUSUS BERDASARKAN JENIS PERTANYAAN:\n".
            "  * Jika user HANYA menanyakan nama (contoh: 'siapa saja warganya', 'sebutkan nama-nama warga'), sebutkan daftar namanya saja (disertai wilayah RT/RW secara ringkas). JANGAN membeberkan NIK, No. KK, agama, tanggal lahir, pekerjaan, atau alamat lengkap jika tidak diminta.\n".
            "  * Jika user menanyakan jumlah/total (contoh: 'berapa jumlah warga', 'ada berapa akun'), jawab langsung angkanya secara lugas dan jelas, JANGAN melampirkan seluruh daftar biodata.\n".
            "  * Jika user menanyakan data spesifik atau data lengkap seseorang (contoh: 'data lengkap Budi', 'biodata NIK X', 'apakah akun Budi aktif'), barulah jawab detail sesuai yang diminta mengenai orang tersebut.\n".
            "  * Jika user bertanya 'siapa RT saya', jawab HANYA mengenai Ketua RT-nya. JANGAN mencantumkan Ketua RW atau Kantor Desa kecuali ditanyakan.\n".
            "- HINDARI basa-basi berlebihan, pengantar panjang, atau kalimat penutup berulang yang klise. Bersikaplah lugas, akurat, faktual berdasarkan database SIWIL, dan langsung menjawab inti pertanyaan.\n\n";

        // General knowledge
        $knowledgeList = AiKnowledge::all();
        $knowledgeText = '';
        if ($knowledgeList->isNotEmpty()) {
            $knowledgeText .= "Basis Pengetahuan Wilayah:\n";
            foreach ($knowledgeList as $kn) {
                $knowledgeText .= "- {$kn->topic}: {$kn->content}\n";
            }
        }
        $extraData['knowledge_list'] = $knowledgeList;

        if ($role === 'WARGA') {
            $resident = Resident::where('nik', $user->username)->first();
            if (! $resident && $user->resident) {
                $resident = $user->resident;
            }
            $rt = null;
            if ($resident) {
                $rt = $resident->rt ?? ($resident->family->rt ?? null);
            }
            if ($rt && ! $rt->relationLoaded('rw')) {
                $rt->load('rw.village');
            }

            $context .= 'Nama Warga: '.($resident->full_name ?? $user->name ?? $user->username)."\n";
            if ($rt) {
                $context .= "Wilayah: RT {$rt->number}, RW ".($rt->rw->number ?? '-').', Desa '.($rt->rw->village->name ?? '-')."\n";

                // Resolve official contacts only
                $ketuaRt = User::with('resident')->where('role', 'RT')->where('rt_id', $rt->id)->first();
                $ketuaRw = $rt->rw ? User::with('resident')->where('role', 'RW')->where('rw_id', $rt->rw->id)->first() : null;
                $adminDesa = ($rt->rw && $rt->rw->village) ? User::where('role', 'ADMIN_DESA')->where('village_id', $rt->rw->village->id)->first() : null;

                $namaRt = $ketuaRt ? ($ketuaRt->resident->full_name ?? $ketuaRt->name ?? 'Ketua RT') : 'Belum terdaftar';
                $hpRt = $ketuaRt ? ($ketuaRt->phone_number ?? 'Belum ada No HP') : '-';
                $emailRt = $ketuaRt ? ($ketuaRt->email ?? '-') : '-';

                $namaRw = $ketuaRw ? ($ketuaRw->resident->full_name ?? $ketuaRw->name ?? 'Ketua RW') : 'Belum terdaftar';
                $hpRw = $ketuaRw ? ($ketuaRw->phone_number ?? 'Belum ada No HP') : '-';
                $emailRw = $ketuaRw ? ($ketuaRw->email ?? '-') : '-';

                $desaName = $rt->rw->village->name ?? 'Desa';
                $desaLabel = str_starts_with(strtolower(trim($desaName)), 'desa ') ? trim($desaName) : 'Desa '.trim($desaName);
                $desaEmail = $adminDesa ? ($adminDesa->email ?? 'desa@siwil.id') : 'desa@siwil.id';
                $desaHp = $adminDesa ? ($adminDesa->phone_number ?? '-') : '-';

                $extraData['rt_info'] = [
                    'number' => $rt->number,
                    'name' => $namaRt,
                    'phone' => $hpRt,
                    'email' => $emailRt,
                    'rw_number' => $rt->rw->number ?? '-',
                    'village' => $desaLabel,
                ];
                $extraData['rw_info'] = [
                    'number' => $rt->rw->number ?? '-',
                    'name' => $namaRw,
                    'phone' => $hpRw,
                    'email' => $emailRw,
                    'village' => $desaLabel,
                ];
                $extraData['desa_info'] = [
                    'name' => $desaLabel,
                    'phone' => $desaHp,
                    'email' => $desaEmail,
                ];

                $contactsText = "• **Ketua RT {$rt->number}:** {$namaRt}\n  No. WA/Telp: `{$hpRt}` | Email: {$emailRt}\n\n".
                    '• **Ketua RW '.($rt->rw->number ?? '-').":** {$namaRw}\n  No. WA/Telp: `{$hpRw}` | Email: {$emailRw}\n\n".
                    "• **Kantor {$desaLabel}:**\n  Telp/WA: `{$desaHp}` | Email: {$desaEmail}\n";

                $context .= "Kontak Resmi Pengurus Wilayah:\n".$contactsText;
            }

            // Database reading for Warga: Family members in their own KK
            $familyMembers = collect();
            $familyText = '';
            if ($resident && $resident->family_id) {
                $familyMembers = Resident::where('family_id', $resident->family_id)->get();
                $familyText = "Daftar Anggota Keluarga dalam KK Anda:\n";
                foreach ($familyMembers as $fm) {
                    $familyText .= "- {$fm->full_name} | NIK: {$fm->nik} | Hubungan/Status: {$fm->status} | Jenis Kelamin: {$fm->gender} | Pekerjaan: {$fm->occupation}\n";
                }
            }
            $extraData['family_members'] = $familyMembers;
            $extraData['family_text'] = $familyText;

            // Database reading for Warga: Their own document requests
            $myDocs = collect();
            $docsText = '';
            if ($resident) {
                $myDocs = DocumentRequest::where('resident_id', $resident->id)->latest()->take(5)->get();
                if ($myDocs->isNotEmpty()) {
                    $docsText = "Riwayat Pengajuan Surat Anda:\n";
                    foreach ($myDocs as $doc) {
                        $docsText .= "- {$doc->type} | Status: {$doc->status} | Tanggal: {$doc->created_at->format('d M Y')}\n";
                    }
                }
            }
            $extraData['my_docs'] = $myDocs;
            $extraData['docs_text'] = $docsText;

            // Database reading for Warga: Their own complaints
            $myComplaints = Complaint::where('user_id', $user->id)->latest()->take(5)->get();
            $complaintsText = '';
            if ($myComplaints->isNotEmpty()) {
                $complaintsText = "Riwayat Pengaduan/Keluhan Anda:\n";
                foreach ($myComplaints as $c) {
                    $complaintsText .= "- [{$c->status}] {$c->title} ({$c->created_at->format('d M Y')})\n";
                }
            }
            $extraData['my_complaints'] = $myComplaints;
            $extraData['complaints_text'] = $complaintsText;

            // Database reading for Warga: Announcements
            $announcements = Announcement::where('is_active', true)->latest()->take(3)->get();
            $announcementsText = '';
            if ($announcements->isNotEmpty()) {
                $announcementsText = "Pengumuman Resmi Aktif:\n";
                foreach ($announcements as $an) {
                    $announcementsText .= "- {$an->title}: {$an->content}\n";
                }
            }
            $extraData['announcements'] = $announcements;
            $extraData['announcements_text'] = $announcementsText;

            // Database reading for Warga: Active Polls
            $activePolls = Poll::with(['options.votes'])->where('status', 'ACTIVE')->latest()->take(3)->get();
            $pollsText = '';
            if ($activePolls->isNotEmpty()) {
                $pollsText = "Musyawarah & Polling Aktif:\n";
                foreach ($activePolls as $poll) {
                    $hasVoted = $poll->votes->where('user_id', $user->id)->isNotEmpty();
                    $votedStr = $hasVoted ? 'Sudah Vote' : 'Belum Vote';
                    $pollsText .= "- {$poll->title} ({$votedStr}) | Batas: ".($poll->expires_at ? $poll->expires_at->format('d M Y') : 'Tanpa Batas')."\n";
                }
            }
            $extraData['active_polls'] = $activePolls;
            $extraData['polls_text'] = $pollsText;

            if ($familyText) {
                $context .= "\n".$familyText;
            }
            if ($docsText) {
                $context .= "\n".$docsText;
            }
            if ($complaintsText) {
                $context .= "\n".$complaintsText;
            }
            if ($announcementsText) {
                $context .= "\n".$announcementsText;
            }
            if ($pollsText) {
                $context .= "\n".$pollsText;
            }

            $systemPrompt .= "BATASAN AKSES KETAT UNTUK ROLE WARGA:\n".
                "- Pengguna adalah WARGA biasa.\n".
                "- IZINKAN MENJAWAB: Seluruh informasi dan data dashboard Warga, daftar menu dan fitur di sidebar navigasi, pembaruan / update terkini dari dashboard (notifikasi terbaru, status surat, tindak lanjut pengaduan, pengumuman desa baru, dan polling musyawarah), informasi kontak resmi pengurus (Ketua RT, Ketua RW, Kantor Desa), panduan pembuatan surat pengantar (Domisili, SKTM, SKU), alur pengaduan, status dokumen/pengaduannya sendiri, data diri dan anggota keluarganya sendiri dalam satu KK, pengumuman wilayah, dan musyawarah/polling aktif.\n".
                "- DILARANG KERAS: Memberikan informasi kontak, nomor telepon, alamat, NIK, atau data warga lain siapapun di luar KK miliknya sendiri.\n".
                "- DILARANG KERAS: Memberikan statistik sensus jumlah warga di luar wilayah dashboard warganya sendiri.\n".
                "- DILARANG KERAS: Memberikan database internal rahasia RT, RW, Desa, atau APBDes.\n".
                "- Jika ditanya hal-hal yang dilarang di atas, KAMU WAJIB MENOLAK DENGAN RAMAH dan tegaskan bahwa data warga lain dan direktori kependudukan dilindungi privasi (UU PDP) dan tidak dapat diakses akun Warga.\n";

        } elseif ($role === 'RT') {
            $rt = $user->rt_id ? Rt::with('rw.village')->find($user->rt_id) : null;
            if ($rt) {
                $context .= "Wilayah Tugas: RT {$rt->number}, RW ".($rt->rw->number ?? '-').', Desa '.($rt->rw->village->name ?? '-')."\n";

                // Scoped RT statistics
                $totalKk = Family::where('rt_id', $rt->id)->count();
                $totalWarga = Resident::where('rt_id', $rt->id)->count();
                $totalPria = Resident::where('rt_id', $rt->id)->where('gender', 'LAKI_LAKI')->count();
                $totalWanita = Resident::where('rt_id', $rt->id)->where('gender', 'PEREMPUAN')->count();
                $pendingComplaints = Complaint::where('rt_id', $rt->id)->where('status', 'PENDING')->count();
                $pendingDocs = DocumentRequest::where('status', 'PENDING')->whereHas('resident', fn ($q) => $q->where('rt_id', $rt->id))->count();

                $extraData['rt_number'] = $rt->number;
                $extraData['total_warga'] = $totalWarga;
                $extraData['total_kk'] = $totalKk;
                $extraData['total_pria'] = $totalPria;
                $extraData['total_wanita'] = $totalWanita;
                $extraData['pending_complaints'] = $pendingComplaints;
                $extraData['pending_docs'] = $pendingDocs;

                // Resident directory within this RT only (including NIK, Phone, Address, Status, Occupation)
                $rtResidents = Resident::with(['user', 'family'])->where('rt_id', $rt->id)->get();
                $rtResidentsList = '';
                foreach ($rtResidents as $res) {
                    $hp = $res->user?->phone_number ?? 'Belum ada No HP';
                    $addr = $res->family?->address ?? '-';
                    $rtResidentsList .= "• {$res->full_name} | NIK: {$res->nik} | No. WA/HP: {$hp} | Status: {$res->status} | JK: {$res->gender} | Pekerjaan: {$res->occupation} | Alamat: {$addr}\n";
                }
                $extraData['rt_residents'] = $rtResidents;
                $extraData['rt_residents_list'] = $rtResidentsList;

                // Families (KK) in RT
                $rtFamilies = Family::with('members')->withCount('members')->where('rt_id', $rt->id)->get();
                $rtFamiliesList = '';
                foreach ($rtFamilies as $fam) {
                    $head = $fam->members->firstWhere('status', 'KEPALA_KELUARGA')?->full_name ?? ($fam->members->first()?->full_name ?? '-');
                    $rtFamiliesList .= "• No. KK: {$fam->kk_number} | Kepala Keluarga: {$head} | Anggota: {$fam->members_count} jiwa | Alamat: {$fam->address}\n";
                }
                $extraData['rt_families'] = $rtFamilies;
                $extraData['rt_families_list'] = $rtFamiliesList;

                // Document requests in RT
                $rtDocs = DocumentRequest::with('resident')->whereHas('resident', fn ($q) => $q->where('rt_id', $rt->id))->latest()->take(10)->get();
                $rtDocsList = '';
                foreach ($rtDocs as $doc) {
                    $pemohon = $doc->resident?->full_name ?? 'Warga';
                    $rtDocsList .= "• [{$doc->status}] {$doc->type} diajukan oleh {$pemohon} ({$doc->created_at->format('d M Y')})\n";
                }
                $extraData['rt_docs'] = $rtDocs;
                $extraData['rt_docs_list'] = $rtDocsList;

                // Complaints in RT
                $rtComplaints = Complaint::with('user')->where('rt_id', $rt->id)->latest()->take(10)->get();
                $rtComplaintsList = '';
                foreach ($rtComplaints as $comp) {
                    $pelapor = $comp->user?->name ?? 'Warga';
                    $rtComplaintsList .= "• [{$comp->status}] {$comp->title} oleh {$pelapor} ({$comp->created_at->format('d M Y')})\n";
                }
                $extraData['rt_complaints'] = $rtComplaints;
                $extraData['rt_complaints_list'] = $rtComplaintsList;

                $context .= "Statistik Wilayah RT {$rt->number} (Hanya untuk wilayah RT ini):\n".
                    "- Jumlah KK di RT {$rt->number}: {$totalKk}\n".
                    "- Jumlah Warga di RT {$rt->number}: {$totalWarga} jiwa (Laki-laki: {$totalPria}, Perempuan: {$totalWanita})\n".
                    "- Pengaduan PENDING di RT {$rt->number}: {$pendingComplaints}\n".
                    "- Pengajuan Surat PENDING di RT {$rt->number}: {$pendingDocs}\n\n".
                    "Daftar Warga & Kontak di RT {$rt->number} Anda:\n".
                    ($rtResidentsList ?: "- Belum ada warga terdaftar di RT ini.\n")."\n".
                    "Daftar Kartu Keluarga (KK) di RT {$rt->number}:\n".
                    ($rtFamiliesList ?: "- Belum ada KK terdaftar di RT ini.\n")."\n".
                    "Daftar Pengajuan Surat Warga di RT {$rt->number}:\n".
                    ($rtDocsList ?: "- Belum ada permohonan surat.\n")."\n".
                    "Daftar Pengaduan Warga di RT {$rt->number}:\n".
                    ($rtComplaintsList ?: "- Belum ada pengaduan.\n");

                // Dynamic resident lookup within RT
                if ($message && preg_match('/(?:warga|biodata|profil|nik|nama|cari)\s+(?:bernama|nama|dengan\s+nik)?\s*([a-zA-Z0-9\s]{2,})/i', $message, $sm)) {
                    $searchQ = trim($sm[1]);
                    $foundRtRes = Resident::with(['user', 'family'])
                        ->where('rt_id', $rt->id)
                        ->where(function ($b) use ($searchQ) {
                            $b->where('nik', 'like', "%{$searchQ}%")
                                ->orWhere('full_name', 'like', "%{$searchQ}%");
                        })
                        ->take(5)
                        ->get();
                    if ($foundRtRes->isNotEmpty()) {
                        $context .= "\nHasil Pencarian Warga di RT {$rt->number} untuk '{$searchQ}':\n";
                        foreach ($foundRtRes as $fr) {
                            $uHp = $fr->user?->phone_number ?? 'Belum ada No HP';
                            $fAddr = $fr->family?->address ?? '-';
                            $context .= "• {$fr->full_name} | NIK: {$fr->nik} | HP: {$uHp} | JK: {$fr->gender} | Pekerjaan: {$fr->occupation} | Alamat: {$fAddr}\n";
                        }
                    }
                }

                // Contact RW and other RTs in same RW
                $ketuaRw = $rt->rw ? User::with('resident')->where('role', 'RW')->where('rw_id', $rt->rw->id)->first() : null;
                $adminDesa = ($rt->rw && $rt->rw->village) ? User::where('role', 'ADMIN_DESA')->where('village_id', $rt->rw->village->id)->first() : null;

                $otherRts = $rt->rw_id ? Rt::where('rw_id', $rt->rw_id)->where('id', '!=', $rt->id)->get() : collect();
                $otherRtHeads = User::with('resident')->where('role', 'RT')->whereIn('rt_id', $otherRts->pluck('id'))->get();
                $otherRtList = '';
                foreach ($otherRts as $oRt) {
                    $head = $otherRtHeads->firstWhere('rt_id', $oRt->id);
                    $headName = $head?->resident?->full_name ?? $head?->name ?? 'Belum terdaftar';
                    $headPhone = $head?->phone_number ?? '-';
                    $headEmail = $head?->email ?? '-';
                    $otherRtList .= "• RT {$oRt->number}: Ketua {$headName} (No. WA/HP: {$headPhone}, Email: {$headEmail})\n";
                }
                $extraData['other_rts_list'] = $otherRtList;
                $extraData['other_rts'] = $otherRts;
                $extraData['other_rt_heads'] = $otherRtHeads;
                $extraData['ketua_rw_user'] = $ketuaRw;

                if ($otherRtList) {
                    $context .= "\nDaftar Pengurus RT Lain di RW {$rt->rw->number} (Koordinasi Pengurus):\n".$otherRtList;
                }
                if ($ketuaRw) {
                    $context .= "\nKontak Ketua RW {$rt->rw->number}: ".($ketuaRw->resident->full_name ?? $ketuaRw->name).' ('.($ketuaRw->phone_number ?? '-').")\n";
                }
                if ($adminDesa) {
                    $context .= 'Kontak Kantor Desa: '.($adminDesa->email ?? 'desa@siwil.id').' ('.($adminDesa->phone_number ?? '-').")\n";
                }
            }

            $systemPrompt .= "ATURAN AKSES DAN KEWENANGAN ROLE RT:\n".
                "- Pengguna adalah Pengurus RT ({$user->name}, RT {$rt?->number}).\n".
                "- Pengurus RT BERHAK mengetahui jumlah warganya sendiri di RT {$rt?->number} (yaitu: {$extraData['total_warga']} jiwa dari {$extraData['total_kk']} KK, {$extraData['total_pria']} laki-laki, {$extraData['total_wanita']} perempuan).\n".
                "- Pengurus RT BERHAK mengetahui NIK, nomor HP/WA, dan data kependudukan warga di wilayah RT {$rt?->number} miliknya sendiri.\n".
                "- Pengurus RT BERHAK mengetahui daftar KK, daftar permohonan surat masuk, dan daftar pengaduan warga di RT-nya.\n".
                "- Pengurus RT BERHAK mengetahui informasi kontak pengurus RT lain (selain dirinya sendiri) dan pengurus RW untuk koordinasi wilayah.\n".
                "- DILARANG MEMBERIKAN data kependudukan warga di RT lain (seperti daftar warga atau NIK warga RT lain), database tingkat RW secara umum, atau database Desa (seperti APBDes, total penduduk desa, arsip desa di luar RT).\n".
                "- Jika RT bertanya tentang warganya, data kependudukan, pengajuan surat, atau pengaduan di RT {$rt?->number}, berikan informasi yang sesuai dari data RT {$rt?->number}.\n".
                "- Jika RT bertanya tentang pengurus RT lain atau pengurus RW, berikan informasi nama dan kontak pengurus terkait.\n".
                "- Jika ditanya tentang data kependudukan warga di luar RT-nya, tolak dengan sopan bahwa kewenangan akun RT terbatas pada wilayah RT {$rt?->number}.\n";

        } elseif ($role === 'RW') {
            $rw = $user->rw_id ? Rw::with(['rts', 'village'])->find($user->rw_id) : null;
            if ($rw) {
                $rtsList = $rw->rts->pluck('number')->implode(', ');
                $totalRts = $rw->rts->count();
                $totalWargaRw = Resident::whereHas('rt', fn ($q) => $q->where('rw_id', $rw->id))->count();
                $totalKkRw = Family::whereHas('rt', fn ($q) => $q->where('rw_id', $rw->id))->count();
                $pendingComplaints = Complaint::where('rw_id', $rw->id)->where('status', 'PENDING')->count();
                $pendingDocs = DocumentRequest::whereHas('resident.rt', fn ($q) => $q->where('rw_id', $rw->id))->where('status', 'APPROVED_RT')->count();

                $extraData['rw_number'] = $rw->number;
                $extraData['total_rts'] = $totalRts;
                $extraData['total_warga_rw'] = $totalWargaRw;
                $extraData['total_kk_rw'] = $totalKkRw;
                $extraData['pending_complaints'] = $pendingComplaints;
                $extraData['pending_docs'] = $pendingDocs;

                // Breakdown per RT under RW
                $rtsBreakdown = '';
                foreach ($rw->rts as $r) {
                    $wCount = Resident::where('rt_id', $r->id)->count();
                    $kCount = Family::where('rt_id', $r->id)->count();
                    $rtsBreakdown .= "• RT {$r->number}: {$wCount} jiwa, {$kCount} KK\n";
                }
                $extraData['rts_breakdown'] = $rtsBreakdown;

                // RT heads directory with NIK and contacts within this RW
                $rtHeads = User::with('resident')->where('role', 'RT')->whereIn('rt_id', $rw->rts->pluck('id'))->get();
                $rtHeadsList = '';
                foreach ($rw->rts as $r) {
                    $head = $rtHeads->firstWhere('rt_id', $r->id);
                    $headName = $head?->resident?->full_name ?? $head?->name ?? 'Belum terdaftar';
                    $headNik = $head?->username ?? '-';
                    $headHp = $head?->phone_number ?? '-';
                    $headEmail = $head?->email ?? '-';
                    $rtHeadsList .= "• RT {$r->number}: Ketua: {$headName} | NIK: {$headNik} | No. WA/HP: {$headHp} | Email: {$headEmail}\n";
                }
                $extraData['rt_heads_list'] = $rtHeadsList;

                // Document requests in RW
                $rwDocs = DocumentRequest::with('resident.rt')->whereHas('resident.rt', fn ($q) => $q->where('rw_id', $rw->id))->latest()->take(10)->get();
                $rwDocsList = '';
                foreach ($rwDocs as $doc) {
                    $pemohon = $doc->resident?->full_name ?? 'Warga';
                    $rtNum = $doc->resident?->rt?->number ?? '-';
                    $rwDocsList .= "• [{$doc->status}] {$doc->type} (Pemohon: {$pemohon}, RT {$rtNum})\n";
                }
                $extraData['rw_docs'] = $rwDocs;
                $extraData['rw_docs_list'] = $rwDocsList;

                // Complaints in RW
                $rwComplaints = Complaint::with(['user', 'rt'])->where('rw_id', $rw->id)->latest()->take(10)->get();
                $rwComplaintsList = '';
                foreach ($rwComplaints as $comp) {
                    $rtNum = $comp->rt?->number ?? '-';
                    $rwComplaintsList .= "• [{$comp->status}] {$comp->title} (RT {$rtNum}) - {$comp->created_at->format('d M Y')}\n";
                }
                $extraData['rw_complaints'] = $rwComplaints;
                $extraData['rw_complaints_list'] = $rwComplaintsList;

                $context .= "Wilayah Tugas: RW {$rw->number}, Desa ".($rw->village->name ?? '-')."\n".
                    "Daftar RT Binaan: {$rtsList} (Total {$totalRts} RT)\n".
                    "Statistik Lingkungan RW {$rw->number}:\n".
                    "- Total KK di RW {$rw->number}: {$totalKkRw}\n".
                    "- Total Warga di RW {$rw->number}: {$totalWargaRw} jiwa\n".
                    "- Pengaduan PENDING di RW: {$pendingComplaints}\n".
                    "- Pengajuan Surat Menunggu Verifikasi RW: {$pendingDocs}\n\n".
                    "Rincian Warga per RT Binaan:\n".
                    ($rtsBreakdown ?: "- Belum ada data RT binaan.\n")."\n".
                    "Daftar Pengurus RT (NIK & Kontak) di RW {$rw->number}:\n".
                    ($rtHeadsList ?: "- Belum ada data pengurus RT.\n")."\n".
                    "Daftar Pengajuan Surat di RW {$rw->number}:\n".
                    ($rwDocsList ?: "- Belum ada pengajuan surat.\n")."\n".
                    "Daftar Pengaduan di RW {$rw->number}:\n".
                    ($rwComplaintsList ?: "- Belum ada pengaduan.\n");

                $adminDesa = $rw->village ? User::where('role', 'ADMIN_DESA')->where('village_id', $rw->village->id)->first() : null;
                if ($adminDesa) {
                    $context .= "\nKontak Kantor Desa: ".($adminDesa->email ?? 'desa@siwil.id').' ('.($adminDesa->phone_number ?? '-').")\n";
                }
            }

            $systemPrompt .= "ATURAN AKSES DAN KEWENANGAN ROLE RW:\n".
                "- Pengguna adalah Pengurus RW ({$user->name}, RW {$rw?->number}).\n".
                "- Pengurus RW BERHAK mengetahui jumlah warga di lingkungan RW {$rw?->number} (yaitu: {$extraData['total_warga_rw']} jiwa dari {$extraData['total_kk_rw']} KK di {$extraData['total_rts']} RT binaan), beserta rincian sebaran per RT.\n".
                "- Pengurus RW BERHAK mengetahui NIK dan kontak nomor pengurus RT di bawah naungannya.\n".
                "- Pengurus RW BERHAK mengetahui rekap pengaduan dan surat di lingkup RW {$rw?->number}.\n".
                "- DILARANG MEMBERIKAN database tingkat Desa menyeluruh (seperti APBDes, arsip desa di luar kewenangan RW) atau data RW lain.\n".
                "- Jika ditanya database tingkat Desa atau RW lain, tolak secara sopan sesuai batas wilayah kerja RW.\n";

        } elseif ($role === 'SUPER_ADMIN') {
            $totalVillages = Village::count();
            $totalRws = Rw::count();
            $totalRts = Rt::count();
            $totalResidents = Resident::count();
            $totalPria = Resident::where(fn ($q) => $q->where('gender', 'LAKI_LAKI')->orWhere('gender', 'Laki-laki')->orWhere('gender', 'L'))->count();
            $totalWanita = Resident::where(fn ($q) => $q->where('gender', 'PEREMPUAN')->orWhere('gender', 'Perempuan')->orWhere('gender', 'P'))->count();
            $totalFamilies = Family::count();
            $totalUsers = User::count();
            $totalComplaints = Complaint::count();
            $totalDocs = DocumentRequest::count();
            $totalPolls = Poll::count();
            $totalAnnouncements = Announcement::count();
            $activeEmergenciesCount = Emergency::where('status', 'ACTIVE')->count();

            $usersByRole = User::selectRaw('role, count(*) as count')->groupBy('role')->pluck('count', 'role')->toArray();
            $wargaCount = $usersByRole['WARGA'] ?? 0;
            $rtCount = $usersByRole['RT'] ?? 0;
            $rwCount = $usersByRole['RW'] ?? 0;
            $desaCount = ($usersByRole['ADMIN_DESA'] ?? 0) + ($usersByRole['DESA'] ?? 0);
            $saCount = $usersByRole['SUPER_ADMIN'] ?? 0;
            $usersSummary = "WARGA: {$wargaCount}, RT: {$rtCount}, RW: {$rwCount}, PERANGKAT/ADMIN DESA: {$desaCount}, SUPER_ADMIN: {$saCount}";

            $systemInfo = [
                'app_name' => config('app.name'),
                'environment' => app()->environment(),
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'db_connection' => config('database.default'),
                'cache_driver' => config('cache.default'),
                'server_time' => now()->toDateTimeString(),
            ];

            // Recent Audit Logs
            $recentAudit = AuditLog::with('user')->latest()->take(10)->get();
            $auditText = '';
            foreach ($recentAudit as $log) {
                $uName = $log->user?->name ?? 'System';
                $auditText .= "• [{$log->created_at->format('d/m/Y H:i')}] {$uName}: {$log->action} on {$log->entity_name}\n";
            }
            $extraData['audit_text'] = $auditText;
            $extraData['recent_audit'] = $recentAudit;
            $extraData['all_residents'] = Resident::with(['user', 'family', 'rt.rw.village'])->orderBy('full_name')->get();
            $extraData['all_users'] = User::with('resident')->orderBy('role')->orderBy('username')->get();

            // Direktori Kependudukan Seluruh Warga untuk Super Admin
            $residentDirectoryText = "DIREKTORI DATA DIRI WARGA DI DATABASE SIWIL (AKSES SUPER ADMIN):\n";
            foreach ($extraData['all_residents'] as $idx => $fr) {
                $num = $idx + 1;
                $uHp = $fr->user?->phone_number ?? 'Belum ada No HP';
                $uEm = $fr->user?->email ?? '-';
                $rNum = $fr->rt?->number ?? '-';
                $wNum = $fr->rt?->rw?->number ?? '-';
                $vNm = $fr->rt?->rw?->village?->name ?? '-';
                $fAddr = $fr->user?->address ?: ($fr->family?->address ?? '-');
                $fKk = $fr->family?->kk_number ?? '-';
                $bDate = $fr->birth_date ? $fr->birth_date->format('d/m/Y') : '-';
                $bPlace = $fr->birth_place ?? '-';
                $accInfo = $fr->user
                    ? "Akun SIWIL: {$fr->user->username} (Status: {$fr->user->status})"
                    : 'Belum Ada Akun SIWIL';

                $residentDirectoryText .= "{$num}. {$fr->full_name} | NIK: {$fr->nik} | KK: {$fKk} | JK: {$fr->gender} | TTL: {$bPlace}, {$bDate} | Agama: {$fr->religion} | Pekerjaan: {$fr->occupation} | Wilayah: RT {$rNum}/RW {$wNum}, Desa {$vNm} | HP: {$uHp} | Email: {$uEm} | Alamat: {$fAddr} | {$accInfo}\n";
            }

            // Direktori Akun Login Pengguna (Kecuali Kata Sandi / Password yang terenkripsi)
            $usersDirectoryText = "DIREKTORI AKUN LOGIN PENGGUNA TERDAFTAR (KATA SANDI TERENKRIPSI HASH BCRYPT):\n";
            foreach ($extraData['all_users'] as $idx => $au) {
                $num = $idx + 1;
                $resName = $au->resident?->full_name ?: ($au->name ?: $au->username);
                $usersDirectoryText .= "{$num}. Username: {$au->username} | Nama: {$resName} | Role: {$au->role} | Status: {$au->status} | Email: ".($au->email ?? '-').' | HP: '.($au->phone_number ?? '-')."\n";
            }

            // Dynamic resident lookup across entire database for Super Admin
            $dynamicSuperSearch = '';
            if ($message) {
                if (preg_match('/(?:warga|biodata|profil|nik|nama|cari|siapa|tentang)\s+(?:bernama|nama|dengan\s+nik)?\s*([a-zA-Z0-9\s]{2,})/i', $message, $sm)) {
                    $searchQ = trim($sm[1]);
                    $foundResidents = Resident::with(['user', 'family', 'rt.rw.village'])
                        ->where('nik', 'like', "%{$searchQ}%")
                        ->orWhere('full_name', 'like', "%{$searchQ}%")
                        ->take(5)
                        ->get();
                    if ($foundResidents->isNotEmpty()) {
                        $dynamicSuperSearch .= "\nHASIL PENCARIAN DATA DIRI WARGA DARI SELURUH DATABASE (KATA KUNCI: '{$searchQ}'):\n";
                        foreach ($foundResidents as $fr) {
                            $uHp = $fr->user?->phone_number ?? 'Belum ada No HP';
                            $uEm = $fr->user?->email ?? '-';
                            $rNum = $fr->rt?->number ?? '-';
                            $wNum = $fr->rt?->rw?->number ?? '-';
                            $vNm = $fr->rt?->rw?->village?->name ?? '-';
                            $fAddr = $fr->family?->address ?? '-';
                            $fKk = $fr->family?->kk_number ?? '-';
                            $dynamicSuperSearch .= "• {$fr->full_name} | NIK: {$fr->nik} | KK: {$fKk} | Wilayah: RT {$rNum}/RW {$wNum}, Desa {$vNm} | HP: {$uHp} | Email: {$uEm} | JK: {$fr->gender} | Pekerjaan: {$fr->occupation} | Alamat: {$fAddr}\n";
                        }
                    }
                }
            }

            // Dynamic RT lookup if message asks about a specific RT
            if ($message && preg_match('/(?:di\s*)?rt\s*0?(\d+)/i', $message, $rtm)) {
                $targetNum = str_pad(ltrim($rtm[1], '0'), 2, '0', STR_PAD_LEFT);
                $targetRts = Rt::with('rw.village')->where('number', $targetNum)->get();
                if ($targetRts->isNotEmpty()) {
                    $dynamicSuperSearch .= "\nHASIL PENCARIAN DATA KEPENDUDUKAN RT {$targetNum} DI DATABASE:\n";
                    foreach ($targetRts as $trt) {
                        $totWarga = Resident::where('rt_id', $trt->id)->count();
                        $totPria = Resident::where('rt_id', $trt->id)->where(fn ($q) => $q->where('gender', 'LAKI_LAKI')->orWhere('gender', 'Laki-laki')->orWhere('gender', 'L'))->count();
                        $totWanita = Resident::where('rt_id', $trt->id)->where(fn ($q) => $q->where('gender', 'PEREMPUAN')->orWhere('gender', 'Perempuan')->orWhere('gender', 'P'))->count();
                        $totKk = Family::where('rt_id', $trt->id)->count();
                        $dynamicSuperSearch .= "• RT {$trt->number} (RW {$trt->rw?->number}, Desa {$trt->rw?->village?->name}): Total {$totWarga} jiwa ({$totPria} Laki-laki/Pria, {$totWanita} Perempuan/Wanita), {$totKk} KK\n";
                    }
                }
            }

            $extraData['super_admin'] = true;
            $extraData['total_villages'] = $totalVillages;
            $extraData['total_rws'] = $totalRws;
            $extraData['total_rts'] = $totalRts;
            $extraData['total_residents'] = $totalResidents;
            $extraData['total_families'] = $totalFamilies;
            $extraData['total_users'] = $totalUsers;
            $recentComplaints = Complaint::with('user.resident')->latest()->take(5)->get();
            $complaintsSummaryText = '';
            if ($recentComplaints->isNotEmpty()) {
                $complaintsSummaryText = "Laporan Pengaduan Warga Terbaru di Database:\n";
                foreach ($recentComplaints as $c) {
                    $uName = $c->user?->resident?->full_name ?: ($c->user?->name ?? 'Warga');
                    $complaintsSummaryText .= "• [{$c->status}] {$c->title} oleh {$uName} ({$c->created_at->format('d M Y')})\n";
                }
            }

            $recentDocs = DocumentRequest::with('resident')->latest()->take(5)->get();
            $docsSummaryText = '';
            if ($recentDocs->isNotEmpty()) {
                $docsSummaryText = "Permohonan Surat Terbaru di Database:\n";
                foreach ($recentDocs as $d) {
                    $rName = $d->resident?->full_name ?? 'Warga';
                    $docsSummaryText .= "• [{$d->status}] {$d->type} - Pemohon: {$rName} ({$d->created_at->format('d M Y')})\n";
                }
            }

            $recentPolls = Poll::with('options')->latest()->take(5)->get();
            $pollsSummaryText = '';
            if ($recentPolls->isNotEmpty()) {
                $pollsSummaryText = "Musyawarah & Polling di Database:\n";
                foreach ($recentPolls as $p) {
                    $pollsSummaryText .= "• [{$p->status}] {$p->title}\n";
                }
            }

            $recentAnnouncements = Announcement::latest()->take(5)->get();
            $announcementsSummaryText = '';
            if ($recentAnnouncements->isNotEmpty()) {
                $announcementsSummaryText = "Pengumuman Resmi di Database:\n";
                foreach ($recentAnnouncements as $a) {
                    $announcementsSummaryText .= "• {$a->title}: {$a->content}\n";
                }
            }

            $extraData['recent_complaints'] = $recentComplaints;
            $extraData['recent_docs'] = $recentDocs;
            $extraData['recent_polls'] = $recentPolls;
            $extraData['recent_announcements'] = $recentAnnouncements;
            $extraData['total_complaints'] = $totalComplaints;
            $extraData['total_docs'] = $totalDocs;
            $extraData['total_polls'] = $totalPolls;
            $extraData['total_announcements'] = $totalAnnouncements;
            $extraData['total_pria'] = $totalPria;
            $extraData['total_wanita'] = $totalWanita;
            $extraData['active_emergencies'] = $activeEmergenciesCount;
            $extraData['users_summary'] = $usersSummary;
            $extraData['users_by_role'] = $usersByRole;
            $extraData['system_info'] = $systemInfo;

            $recentResidentsPreview = Resident::latest()->take(5)->pluck('full_name')->implode(', ');

            $context .= "DATA DASHBOARD WEBSITE (TAMPILAN DASHBOARD SUPER ADMIN SAAT INI):\n".
                "- Statistik Kartu Utama: {$totalVillages} Desa/Kelurahan, {$totalRws} RW, {$totalRts} RT, {$totalResidents} Penduduk/Jiwa ({$totalPria} Laki-laki, {$totalWanita} Perempuan), {$totalFamilies} Kepala Keluarga (KK), {$totalUsers} Akun Pengguna, {$totalComplaints} Laporan Pengaduan.\n".
                '- Status Sinyal Darurat (SOS): '.($activeEmergenciesCount > 0 ? "{$activeEmergenciesCount} Panggilan Darurat SOS Aktif" : 'Aman (0 SOS Aktif)')."\n".
                '- Warga Baru Terdaftar di Dashboard: '.($recentResidentsPreview ?: 'Belum ada')."\n".
                "- Modul & Menu di Dashboard: Data Kependudukan Terpadu, Layanan Pengajuan Surat, Manajemen Pengaduan, Musyawarah & Polling, Kelola Pengguna, dan Audit Log.\n\n".
                "KESELURUHAN SISTEM & DATABASE SIWIL (AKSES SUPER ADMIN):\n".
                "- Status Aplikasi: {$systemInfo['app_name']} (Laravel v{$systemInfo['laravel_version']}, PHP {$systemInfo['php_version']}, Env: {$systemInfo['environment']})\n".
                "- Database: Engine `{$systemInfo['db_connection']}`, Cache Driver: `{$systemInfo['cache_driver']}`\n".
                "- Statistik Wilayah: {$totalVillages} Desa/Kelurahan, {$totalRws} RW, {$totalRts} RT\n".
                "- Statistik Kependudukan: {$totalResidents} Warga/Jiwa terdaftar dalam {$totalFamilies} Kartu Keluarga (KK)\n".
                "- Statistik Pengguna: Total {$totalUsers} Akun terdaftar (Rincian per role: Warga: {$wargaCount}, Pengurus RT: {$rtCount}, Pengurus RW: {$rwCount}, Perangkat/Admin Desa: {$desaCount}, Super Admin: {$saCount})\n".
                "- Layanan Publik: {$totalDocs} Permohonan Surat, {$totalComplaints} Pengaduan, {$totalPolls} Polling, {$totalAnnouncements} Pengumuman\n\n".
                $residentDirectoryText."\n".
                $usersDirectoryText."\n".
                ($complaintsSummaryText ? $complaintsSummaryText."\n" : '').
                ($docsSummaryText ? $docsSummaryText."\n" : '').
                ($pollsSummaryText ? $pollsSummaryText."\n" : '').
                ($announcementsSummaryText ? $announcementsSummaryText."\n" : '').
                ($dynamicSuperSearch ?: '')."\n".
                "Riwayat Audit Log Terbaru:\n".
                ($auditText ?: "- Belum ada catatan log aktivitas.\n")."\n";

            $systemPrompt .= "ATURAN AKSES DAN KEWENANGAN ROLE SUPER ADMIN:\n".
                "- Pengguna adalah SUPER ADMINISTRATOR yang memiliki otoritas tertinggi atas seluruh ekosistem sistem dan database SIWIL.\n".
                "- Super Admin MEMILIKI AKSES PENUH ke seluruh database (semua desa, RW, RT, data kependudukan, pengguna, pengaduan, surat, audit log) dan seluruh informasi arsitektur/teknis sistem aplikasi.\n".
                "- SANGAT PENTING: Selalu sesuaikan jawaban persis dengan apa yang di-prompt/ditanyakan oleh Super Admin. Baca inputnya terlebih dahulu, jangan dilebih-lebihkan dan jangan dikurangkan:\n".
                "  * JANGAN PERNAH menampilkan judul atau template kaku seperti 'Data Demografi di Database (Akses Penuh Super Admin)' atau sejenisnya. Jawablah secara langsung, natural, dan to the point.\n".
                "  * Jika Super Admin bertanya tentang apa yang ada di dashboard, ada apa saja di dashboard, data dashboard, atau isi dashboard, jelaskan seluruh data statistik real-time, status darurat SOS, daftar warga terbaru, dan modul layanan yang tampil pada dashboard website secara rapi, jelas, dan akurat.\n".
                "  * Jika Super Admin HANYA menanyakan satu hal/angka spesifik (misal 'ada berapa perempuan di RT 01', 'berapa laki-laki di RT 02', 'berapa KK di RT 01'), JAWAB HANYA ANGKA/METRIK YANG DITANYAKAN TERSEBUT (contoh: 'Di RT 01 terdapat 3 orang perempuan.'). JANGAN menambahkan rincian total warga, laki-laki, atau KK jika tidak ditanyakan!\n".
                "  * Jika Super Admin HANYA menanyakan nama warga (misal 'siapa saja warganya', 'sebutkan nama warga'), sebutkan daftar namanya saja secara ringkas tanpa mengumbar biodata lengkap yang tidak diminta.\n".
                "  * Jika Super Admin menanyakan data spesifik atau data lengkap seseorang (misal 'data warga Budi'), barulah berikan rincian data dirinya secara lengkap.\n".
                "  * Jika Super Admin menanyakan jumlah/angka statistik, jawab langsung angkanya secara lugas.\n".
                "  * Jika Super Admin menanyakan akun, sebutkan informasi akun yang diminta.\n".
                "- ATURAN KEAMANAN KATA SANDI (PASSWORD):\n".
                "  * Seluruh kata sandi pengguna di database dienkripsi satu arah menggunakan hashing kriptografis (Bcrypt) dan TIDAK PERNAH disimpan atau disajikan dalam bentuk teks terbaca demi kepatuhan standar keamanan aplikasi.\n".
                "  * Jika Super Admin bertanya mengenai kata sandi, jelaskan dengan sopan dan lugas bahwa kata sandi di-hash secara aman dan tidak dapat dilihat oleh AI maupun database, namun Super Admin dapat membantu warga mereset kata sandi melalui menu Kelola Pengguna.\n".
                "- Jawab seluruh pertanyaan terkait statistik sistem, database, audit, kependudukan lintas wilayah, konfigurasi teknis, maupun status operasional sistem secara presisi sesuai yang diminta.\n";

        } else {
            // ADMIN_DESA or DESA
            $village = $user->village_id ? Village::with('rws.rts')->find($user->village_id) : Village::with('rws.rts')->first();
            if ($village) {
                $totalRw = $village->rws->count();
                $totalRt = $village->rws->flatMap->rts->count();
                $totalWarga = Resident::whereHas('rt.rw', fn ($q) => $q->where('village_id', $village->id))->count();
                $totalKk = Family::whereHas('rt.rw', fn ($q) => $q->where('village_id', $village->id))->count();
                $totalPria = Resident::whereHas('rt.rw', fn ($q) => $q->where('village_id', $village->id))->where(fn ($q) => $q->where('gender', 'LAKI_LAKI')->orWhere('gender', 'Laki-laki')->orWhere('gender', 'L'))->count();
                $totalWanita = Resident::whereHas('rt.rw', fn ($q) => $q->where('village_id', $village->id))->where(fn ($q) => $q->where('gender', 'PEREMPUAN')->orWhere('gender', 'Perempuan')->orWhere('gender', 'P'))->count();
                $totalComplaints = Complaint::where('village_id', $village->id)->count();
                $totalDocs = DocumentRequest::whereHas('resident.rt.rw', fn ($q) => $q->where('village_id', $village->id))->count();
                $pendingDesaDocs = DocumentRequest::whereHas('resident.rt.rw', fn ($q) => $q->where('village_id', $village->id))->where('status', 'APPROVED_RW')->count();

                $extraData['village_name'] = $village->name;
                $extraData['total_rw'] = $totalRw;
                $extraData['total_rt'] = $totalRt;
                $extraData['total_warga_desa'] = $totalWarga;
                $extraData['total_kk_desa'] = $totalKk;
                $extraData['total_pria_desa'] = $totalPria;
                $extraData['total_wanita_desa'] = $totalWanita;
                $extraData['total_complaints_desa'] = $totalComplaints;
                $extraData['total_docs_desa'] = $totalDocs;
                $extraData['pending_desa_docs'] = $pendingDesaDocs;

                // Breakdown per RW & per RT in village
                $villageRwBreakdown = '';
                $villageRtDemographics = '';
                foreach ($village->rws as $w) {
                    $wResidents = Resident::whereHas('rt', fn ($q) => $q->where('rw_id', $w->id));
                    $wCount = (clone $wResidents)->count();
                    $wPria = (clone $wResidents)->where(fn ($q) => $q->where('gender', 'LAKI_LAKI')->orWhere('gender', 'Laki-laki')->orWhere('gender', 'L'))->count();
                    $wWanita = (clone $wResidents)->where(fn ($q) => $q->where('gender', 'PEREMPUAN')->orWhere('gender', 'Perempuan')->orWhere('gender', 'P'))->count();
                    $rtCount = $w->rts->count();
                    $villageRwBreakdown .= "• RW {$w->number}: {$rtCount} RT, {$wCount} jiwa ({$wPria} Pria, {$wWanita} Wanita)\n";

                    foreach ($w->rts as $t) {
                        $tTotal = Resident::where('rt_id', $t->id)->count();
                        $tPria = Resident::where('rt_id', $t->id)->where(fn ($q) => $q->where('gender', 'LAKI_LAKI')->orWhere('gender', 'Laki-laki')->orWhere('gender', 'L'))->count();
                        $tWanita = Resident::where('rt_id', $t->id)->where(fn ($q) => $q->where('gender', 'PEREMPUAN')->orWhere('gender', 'Perempuan')->orWhere('gender', 'P'))->count();
                        $tKk = Family::where('rt_id', $t->id)->count();
                        $villageRtDemographics .= "• RT {$t->number} (RW {$w->number}): Total {$tTotal} jiwa ({$tPria} Laki-laki/Pria, {$tWanita} Perempuan/Wanita), {$tKk} KK\n";
                    }
                }
                $extraData['village_rw_breakdown'] = $villageRwBreakdown;
                $extraData['village_rt_demographics'] = $villageRtDemographics;

                // List of RW & RT heads
                $allRtIds = $village->rws->flatMap->rts->pluck('id');
                $rtHeads = User::with('resident')->where('role', 'RT')->whereIn('rt_id', $allRtIds)->get();
                $rwHeads = User::with('resident')->where('role', 'RW')->whereIn('rw_id', $village->rws->pluck('id'))->get();

                $vLabel = str_starts_with(strtolower(trim($village->name)), 'desa ') ? trim($village->name) : 'Desa '.trim($village->name);
                $directoryText = "Direktori Pengurus RW & RT {$vLabel}:\n";
                foreach ($village->rws as $w) {
                    $rwHead = $rwHeads->firstWhere('rw_id', $w->id);
                    $rwHeadName = $rwHead?->resident?->full_name ?? $rwHead?->name ?? 'Belum ada pengurus';
                    $directoryText .= "• RW {$w->number} (Ketua: {$rwHeadName}, NIK: {$rwHead?->username}, HP: {$rwHead?->phone_number})\n";
                    foreach ($w->rts as $t) {
                        $rtHead = $rtHeads->firstWhere('rt_id', $t->id);
                        $rtHeadName = $rtHead?->resident?->full_name ?? $rtHead?->name ?? 'Belum ada pengurus';
                        $directoryText .= "  - RT {$t->number}: {$rtHeadName} (NIK: {$rtHead?->username}, HP: {$rtHead?->phone_number})\n";
                    }
                }
                $extraData['village_directory'] = $directoryText;

                // Budgets (APBDes)
                $budgets = Budget::where('village_id', $village->id)->latest()->take(10)->get();
                $budgetsText = '';
                if ($budgets->isNotEmpty()) {
                    foreach ($budgets as $b) {
                        $budgetsText .= "• [{$b->type}] {$b->category}: Anggaran Rp ".number_format($b->amount_budget, 0, ',', '.').' (Realisasi: Rp '.number_format($b->amount_realized, 0, ',', '.').") Thn {$b->year}\n";
                    }
                }
                $extraData['budgets'] = $budgets;
                $extraData['budgets_text'] = $budgetsText;

                // Developments (Proyek Pembangunan Desa)
                $devs = Development::where('village_id', $village->id)->latest()->take(5)->get();
                $devsText = '';
                if ($devs->isNotEmpty()) {
                    foreach ($devs as $d) {
                        $devsText .= "• {$d->title} | Status: {$d->status} ({$d->progress_percentage}%) | Anggaran: Rp ".number_format($d->budget_allocated, 0, ',', '.').' ('.($d->source_of_fund ?? 'DD').")\n";
                    }
                }
                $extraData['developments'] = $devs;
                $extraData['devs_text'] = $devsText;

                // Document requests in village
                $desaDocs = DocumentRequest::with('resident')->whereHas('resident.rt.rw', fn ($q) => $q->where('village_id', $village->id))->latest()->take(10)->get();
                $desaDocsList = '';
                foreach ($desaDocs as $doc) {
                    $pemohon = $doc->resident?->full_name ?? 'Warga';
                    $desaDocsList .= "• [{$doc->status}] {$doc->type} oleh {$pemohon} ({$doc->created_at->format('d M Y')})\n";
                }
                $extraData['desa_docs'] = $desaDocs;
                $extraData['desa_docs_list'] = $desaDocsList;

                // Complaints in village
                $desaComplaints = Complaint::with('user')->where('village_id', $village->id)->latest()->take(10)->get();
                $desaComplaintsList = '';
                foreach ($desaComplaints as $c) {
                    $desaComplaintsList .= "• [{$c->status}] {$c->title} ({$c->created_at->format('d M Y')})\n";
                }
                $extraData['desa_complaints'] = $desaComplaints;
                $extraData['desa_complaints_list'] = $desaComplaintsList;

                // Emergencies in village
                $emergencies = Emergency::whereHas('user', fn ($q) => $q->where('village_id', $village->id))->latest()->take(5)->get();
                $emergenciesText = '';
                foreach ($emergencies as $em) {
                    $emergenciesText .= "• [{$em->status}] Darurat {$em->type} ({$em->created_at->format('d M Y H:i')})\n";
                }
                $extraData['emergencies'] = $emergencies;
                $extraData['emergencies_text'] = $emergenciesText;

                $context .= "Wilayah: Desa {$village->name}\n".
                    "Statistik Desa: Total {$totalRw} RW, {$totalRt} RT, {$totalWarga} Warga ({$totalPria} Pria, {$totalWanita} Wanita), {$totalKk} KK, {$totalDocs} Surat, {$totalComplaints} Pengaduan.\n\n".
                    "Sebaran per RW:\n".
                    ($villageRwBreakdown ?: "- Belum ada data RW.\n")."\n".
                    "Rincian Demografi & Jumlah Warga per RT di {$vLabel}:\n".
                    ($villageRtDemographics ?: "- Belum ada data RT.\n")."\n".
                    $directoryText."\n".
                    "Anggaran Desa (APBDes):\n".
                    ($budgetsText ?: "- Belum ada data anggaran terdaftar.\n")."\n".
                    "Proyek Pembangunan Desa:\n".
                    ($devsText ?: "- Belum ada data pembangunan.\n")."\n".
                    "Pengajuan Surat di Desa:\n".
                    ($desaDocsList ?: "- Belum ada surat terdaftar.\n")."\n".
                    "Pengaduan di Desa:\n".
                    ($desaComplaintsList ?: "- Belum ada pengaduan terdaftar.\n")."\n";
            }

            $systemPrompt .= "ATURAN AKSES DAN KEWENANGAN ROLE DESA:\n".
                "- Pengguna adalah Administrator / Perangkat Desa.\n".
                "- Memiliki akses kependudukan penuh ke seluruh wilayah Desa {$village?->name} (seluruh RW, RT, dan warga desa, termasuk NIK dan kontak pengurus/warga).\n".
                "- Perangkat Desa BERHAK mengetahui rincian demografi kependudukan per RT (jumlah warga, laki-laki/pria, perempuan/wanita, dan jumlah KK per RT). Ketika ditanya data jenis kelamin atau warga pada RT tertentu (misal: 'ada berapa laki laki di rt 01'), berikan angka pasti dari data kependudukan per RT yang ada di konteks.\n".
                "- Memiliki akses informasi publik desa: APBDes, proyek pembangunan, permohonan persuratan desa, laporan pengaduan, dan keadaan darurat desa.\n".
                "- DILARANG MEMBERIKAN informasi konfigurasi teknis sistem/server (seperti API keys, database credentials, server env, source code, audit log internal sistem).\n";
        }

        if (! empty($knowledgeText)) {
            $context .= "\n".$knowledgeText;
        }

        $sidebarMenuText = $this->getSidebarMenuList($role);
        $sidebarAllRolesText = $this->getSidebarMenuListForAllRoles();
        $wholeSidebarContentText = $this->getWholeSidebarMenuContents($user);
        $dashboardSummaryText = $this->getDashboardSummary($user);
        $recentUpdatesText = $this->getRecentUpdatesSummary($user);

        $context .= "\nDATA & STATISTIK REAL-TIME DASHBOARD (ROLE: {$role}):\n".$dashboardSummaryText."\n\n";
        $context .= "MENU & MODUL SIDEBAR RESMI PENGGUNA (ROLE: {$role}):\n".$sidebarMenuText."\n\n";
        $context .= "STRUKTUR MENU SIDEBAR & DASHBOARD UNTUK SELURUH ROLE (WARGA, RT, RW, ADMIN DESA, SUPER ADMIN):\n".$sidebarAllRolesText."\n\n";
        $context .= "KESELURUHAN ISI LENGKAP REAL-TIME TIAP MENU SIDEBAR PENGGUNA (DARI DATABASE):\n".$wholeSidebarContentText."\n\n";
        $context .= "PEMBARUAN / UPDATE TERKINI DARI DASHBOARD & DATABASE:\n".$recentUpdatesText."\n\n";

        $systemPrompt .= "\nKEMAMPUAN MEMBACA KESELURUHAN MENU SIDEBAR, DASHBOARD, & PEMBARUAN TERKINI (SEMUA ROLE):\n".
            "- Kamu memiliki pengetahuan lengkap mengenai struktur menu sidebar dan modul dashboard untuk TIAP ROLE di sistem SIWIL (Warga, Pengurus RT, Pengurus RW, Administrator Desa, dan Super Admin).\n".
            "- Kamu memiliki akses membaca KESELURUHAN ISI LENGKAP dari setiap menu sidebar pengguna saat ini langsung dari database real-time SIWIL di atas.\n".
            "- Ketika pengguna menanyakan isi menu-menu di sidebar untuk role tertentu atau untuk TIAP / SEMUA ROLE, jelaskan daftar menu dan fungsinya sesuai kewenangan masing-masing role.\n".
            "- Ketika pengguna menanyakan apa saja isi menu sidebar mereka, jabarkan seluruh isi modul menu yang aktif dari database secara komprehensif.\n".
            "- Setiap kali dashboard atau database di-update (perubahan status verifikasi surat, perkembangan laporan aduan, pengumuman baru, partisipasi polling/musyawarah, proyek pembangunan & APBDes, pendaftaran warga baru, atau notifikasi), kamu secara otomatis mengetahui perubahan data tersebut secara real-time langsung dari database dan dapat merincikan apa saja yang berubah.\n".
            "- KEAMANAN: Seluruh kata sandi dienkripsi kriptografis (Bcrypt) dan TIDAK PERNAH dibocorkan atau ditampilkan oleh AI.\n\n".
            "AKURASI DATA KEPENDUDUKAN & KEPEMILIKAN AKUN WARGA (DATA KEPENDUDUKAN TERPADU):\n".
            "- Pada dashboard Data Kependudukan Terpadu, warga terdaftar memiliki status akun login SIWIL (apakah sudah memiliki akun atau belum/tidak memiliki akun).\n".
            "- Warga yang belum memiliki akun login SIWIL ditandai dengan badge 'Belum Ada Akun' dan tombol aksi '+ Buat Akun' untuk membuatkan akun baru.\n".
            "- Ketika pengguna menanyakan 'ada berapa warga yang tidak mempunyai akun' / 'belum punya akun' atau mengonfirmasi bacaan dashboard ('baca dashboard ga si'), JANGAN PERNAH menjawab dengan total keseluruhan warga terdaftar! Jawab dengan jumlah pasti warga yang belum memiliki akun beserta nama-nama warganya (misal: Rina Kartika), rincian RT/RW, dan jelaskan tombol '+ Buat Akun' di tabel dashboard.\n\n";

        $extraData['sidebar_menu_text'] = $sidebarMenuText;
        $extraData['sidebar_all_roles_text'] = $sidebarAllRolesText;
        $extraData['whole_sidebar_content_text'] = $wholeSidebarContentText;
        $extraData['dashboard_summary_text'] = $dashboardSummaryText;
        $extraData['recent_updates_text'] = $recentUpdatesText;

        return [
            'context_text' => $context,
            'system_prompt' => $systemPrompt,
            'contacts_text' => $contactsText,
            'extra_data' => $extraData,
        ];
    }

    /**
     * Handle direct query confirming if AI is reading the active dashboard
     * e.g., "baca dashboard ga si", "baca dashboard gak sih", "apakah baca dashboard", "kok 13 jiwa kan nanya yang tidak punya akun"
     */
    private function handleDirectDashboardConfirmationQuery(string $clean, string $lower, User $user): ?array
    {
        $isAskingDashboardReading = (bool) (
            preg_match('/(?:kamu\s+|apakah\s*(?:kamu\s+)?|bisa\s+|apakah\s*bisa\s+)?baca\s+dashboard(?:\s*(?:ga|gak|nggak|kah|ta|si|sih|ya|bro|min|bot|ai\b))*$/i', trim($lower))
            || preg_match('/baca\s+dashboard\s+(?:ga|gak|nggak|kah|si|sih)/i', $lower)
            || preg_match('/(?:kok|kenapa)\s*(?:dijawab\s*)?13(?:\s*jiwa)?/i', $lower)
            || (preg_match('/baca\s+dashboard/i', $lower) && preg_match('/(?:ga|gak|nggak|kah|si|sih)/i', $lower))
        );

        if (! $isAskingDashboardReading) {
            return null;
        }

        $role = $user->role;
        if ($role === 'WARGA') {
            return [
                'message' => "📊 **Ya, SIWIL AI membaca dan memantau seluruh data dashboard serta database secara real-time!**\n\n".
                    "Pada dashboard akun Warga Anda saat ini:\n".
                    "• **Layanan Surat:** Memantau status pengajuan surat mandiri Anda secara langsung.\n".
                    "• **Pengaduan:** Memantau tindak lanjut laporan pengaduan lingkungan Anda.\n".
                    "• **Musyawarah & Polling:** Mengikuti perkembangan voting warga desa.\n".
                    "• **Pengumuman Resmi & Kontak:** Menampilkan informasi resmi pengurus RT, RW, dan Desa terkini.\n\n".
                    '_Catatan: Sesuai UU Perlindungan Data Pribadi (UU PDP), rincian akun login warga lain hanya dapat diakses oleh Administrator Wilayah (RT, RW, Desa, Super Admin)._',
                'options' => [
                    ['label' => 'Buat Surat Baru', 'icon' => 'document', 'next_node' => 'surat_pilih_jenis'],
                    ['label' => 'Lapor Pengaduan', 'icon' => 'alert', 'next_node' => 'lapor_mulai'],
                    ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                ],
            ];
        }

        // Administrative roles (SUPER_ADMIN, ADMIN_DESA, RW, RT)
        $resQuery = Resident::accessibleBy($user);
        $totalResidents = (clone $resQuery)->count();
        $maleResidents = (clone $resQuery)->where(fn ($q) => $q->where('gender', 'LAKI_LAKI')->orWhere('gender', 'Laki-laki')->orWhere('gender', 'L'))->count();
        $femaleResidents = (clone $resQuery)->where(fn ($q) => $q->where('gender', 'PEREMPUAN')->orWhere('gender', 'Perempuan')->orWhere('gender', 'P'))->count();
        $hasAccountCount = (clone $resQuery)->has('user')->count();
        $noAccountResidents = (clone $resQuery)->doesntHave('user')->with('rt.rw.village')->get();
        $noAccountCount = $noAccountResidents->count();

        $noAccountList = [];
        foreach ($noAccountResidents as $idx => $r) {
            $num = $idx + 1;
            $rtNum = $r->rt?->number ?? '-';
            $rwNum = $r->rt?->rw?->number ?? '-';
            $gender = match (strtoupper($r->gender ?? '')) {
                'PEREMPUAN', 'P', 'FEMALE' => 'Perempuan',
                'LAKI_LAKI', 'L', 'MALE' => 'Laki-laki',
                default => $r->gender ?? '-',
            };
            $status = $r->status ?? 'Aktif';
            $noAccountList[] = "  {$num}. **{$r->full_name}**\n".
                "     • NIK: `{$r->nik}`\n".
                "     • Jenis Kelamin: {$gender}\n".
                "     • Wilayah: RT {$rtNum} / RW {$rwNum}\n".
                "     • Status Warga: {$status}\n".
                "     • Status Akun di Tabel: ⚠️ Badge **[Belum Ada Akun]**\n".
                '     • Tombol Aksi Tersedia: **[+ Buat Akun]**';
        }

        $noAccountDetails = ! empty($noAccountList)
            ? implode("\n", $noAccountList)
            : '  _Tidak ada warga tanpa akun. Semua warga terdaftar telah memiliki akun login SIWIL._';

        $message = "📊 **Ya, SIWIL AI membaca dan memantau seluruh tampilan dashboard serta data database secara real-time!**\n\n".
            "Mohon maaf atas kekurangtepatan jawaban sebelumnya yang menyebutkan total keseluruhan warga ({$totalResidents} jiwa) saat Anda menanyakan warga yang belum mempunyai akun.\n\n".
            "Berdasarkan halaman dan tabel **Data Kependudukan Terpadu** pada dashboard saat ini:\n".
            "• **Total Penduduk Terdaftar:** **{$totalResidents} jiwa** ({$maleResidents} Laki-laki, {$femaleResidents} Perempuan)\n".
            "• **Warga yang SUDAH Memiliki Akun:** **{$hasAccountCount} akun** (aktif dengan No. HP/Email login)\n".
            "• **Warga yang BELUM Memiliki Akun:** **{$noAccountCount} orang** (bertanda badge **\"Belum Ada Akun\"**)\n\n".
            "**Rincian Warga yang Belum Memiliki Akun:**\n".
            $noAccountDetails."\n\n".
            "💡 **Aksi Tindak Lanjut pada Dashboard:**\n".
            'Pada tabel **Data Kependudukan Terpadu**, Anda dapat langsung mengklik tombol **[+ Buat Akun]** pada baris warga bersangkutan untuk men-generate akun login SIWIL baru secara instan.';

        return [
            'message' => trim($message),
            'options' => [
                ['label' => 'Data Kependudukan Terpadu', 'icon' => 'document', 'next_node' => 'db_overview'],
                ['label' => 'Ringkasan Database', 'icon' => 'search', 'next_node' => 'db_overview'],
                ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
            ],
        ];
    }

    /**
     * Handle specific queries about residents without/with login accounts.
     * e.g., "ada berapa warga yang tidak mempunyai akun", "berapa warga belum punya akun", "warga tanpa akun", "siapa yang belum punya akun"
     */
    private function handleResidentAccountStatusQuery(string $clean, string $lower, User $user): ?array
    {
        $isAskingWithoutAccount = (bool) (
            preg_match('/(?:warga|penduduk|jiwa).*(?:tidak|belum|tanpa|bukan)\s*(?:mempunyai|memiliki|ada|punya)?\s*akun/i', $clean)
            || preg_match('/(?:tidak|belum|tanpa)\s*(?:mempunyai|memiliki|ada|punya)?\s*akun.*(?:warga|penduduk|jiwa)/i', $clean)
            || preg_match('/belum\s+ada\s+akun/i', $clean)
            || preg_match('/tanpa\s+akun/i', $clean)
            || preg_match('/tidak\s+(?:mempunyai|memiliki|ada|punya)\s+akun/i', $clean)
            || preg_match('/belum\s+(?:mempunyai|memiliki|ada|punya)\s+akun/i', $clean)
        );

        $isAskingWithAccount = (bool) (
            ! $isAskingWithoutAccount && (
                preg_match('/(?:warga|penduduk|jiwa).*(?:sudah|telah|memiliki|mempunyai)\s*(?:ada\s+)?akun/i', $clean)
                || preg_match('/(?:sudah|telah)\s*(?:mempunyai|memiliki|ada|punya)\s*akun.*(?:warga|penduduk|jiwa)/i', $clean)
                || (preg_match('/(?:berapa|jumlah|total)\s+akun\s+(?:warga|penduduk)/i', $clean))
            )
        );

        if (! $isAskingWithoutAccount && ! $isAskingWithAccount) {
            return null;
        }

        $role = $user->role;
        if ($role === 'WARGA') {
            return [
                'message' => "🔒 **Perlindungan Privasi Data Kependudukan (UU PDP):**\n\n".
                    "Sesuai ketentuan keamanan sistem SIWIL dan UU Perlindungan Data Pribadi (UU PDP), rincian status kepemilikan akun login warga lain bersifat rahasia dan hanya dapat diakses oleh Administrator Wilayah (RT, RW, Desa, atau Super Admin).\n\n".
                    'Untuk akun Anda pribadi, Anda telah terdaftar dan login dengan akun aktif di SIWIL.',
                'options' => [
                    ['label' => 'Data Diri Saya', 'icon' => 'user', 'next_node' => 'profil_saya'],
                    ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                ],
            ];
        }

        // Parse scope if specific RT or RW is specified in query
        $targetRt = null;
        $targetRw = null;
        if (preg_match('/(?:di\s*)?rt\s*0?(\d+)/i', $lower, $mRt)) {
            $targetRt = str_pad(ltrim($mRt[1], '0'), 2, '0', STR_PAD_LEFT);
        }
        if (preg_match('/(?:di\s*)?rw\s*0?(\d+)/i', $lower, $mRw)) {
            $targetRw = str_pad(ltrim($mRw[1], '0'), 2, '0', STR_PAD_LEFT);
        }

        $resQuery = Resident::accessibleBy($user);
        $scopeLabel = '';
        if ($targetRt) {
            $resQuery->whereHas('rt', fn ($q) => $q->where('number', $targetRt));
            $scopeLabel = " di RT {$targetRt}";
        } elseif ($targetRw) {
            $resQuery->whereHas('rt.rw', fn ($q) => $q->where('number', $targetRw));
            $scopeLabel = " di RW {$targetRw}";
        }

        $totalResidents = (clone $resQuery)->count();
        $noAccountResidents = (clone $resQuery)->doesntHave('user')->with('rt.rw.village')->get();
        $noAccountCount = $noAccountResidents->count();
        $hasAccountCount = (clone $resQuery)->has('user')->count();

        if ($isAskingWithoutAccount) {
            if ($noAccountCount > 0) {
                $listItems = [];
                foreach ($noAccountResidents as $idx => $r) {
                    $num = $idx + 1;
                    $rtNum = $r->rt?->number ?? '-';
                    $rwNum = $r->rt?->rw?->number ?? '-';
                    $gender = match (strtoupper($r->gender ?? '')) {
                        'PEREMPUAN', 'P', 'FEMALE' => 'Perempuan',
                        'LAKI_LAKI', 'L', 'MALE' => 'Laki-laki',
                        default => $r->gender ?? '-',
                    };
                    $status = $r->status ?? 'Aktif';
                    $listItems[] = "{$num}. **{$r->full_name}**\n".
                        "   • NIK: `{$r->nik}`\n".
                        "   • Jenis Kelamin: {$gender}\n".
                        "   • Wilayah: RT {$rtNum} / RW {$rwNum}\n".
                        "   • Status Warga: {$status}\n".
                        "   • Status Akun: ⚠️ Badge **[Belum Ada Akun]**\n".
                        '   • Aksi Dashboard: Tersedia tombol **[+ Buat Akun]**';
                }

                $msg = "👥 **Data Warga yang Belum Mempunyai Akun Login SIWIL{$scopeLabel}:**\n\n".
                    "Berdasarkan tabel **Data Kependudukan Terpadu** pada dashboard:\n".
                    "• Dari total **{$totalResidents} warga terdaftar**{$scopeLabel}, terdapat **{$noAccountCount} orang warga yang belum memiliki akun login**.\n".
                    "• Sebanyak **{$hasAccountCount} orang warga lainnya sudah memiliki akun login** aktif.\n\n".
                    "**Daftar Warga Belum Ada Akun:**\n\n".
                    implode("\n\n", $listItems)."\n\n".
                    '💡 _Petunjuk: Anda dapat membuatkan akun login SIWIL bagi warga di atas dengan mengklik tombol **[+ Buat Akun]** pada kolom Akun Login di halaman Data Kependudukan Terpadu._';
            } else {
                $msg = "✅ **Semua Warga Sudah Memiliki Akun Login{$scopeLabel}:**\n\n".
                    "Berdasarkan data dashboard **Data Kependudukan Terpadu**, seluruh warga terdaftar (**{$totalResidents} jiwa**{$scopeLabel}) telah memiliki akun login SIWIL aktif (tidak ada warga tanpa akun).";
            }

            return [
                'message' => trim($msg),
                'options' => [
                    ['label' => 'Data Kependudukan Terpadu', 'icon' => 'document', 'next_node' => 'db_overview'],
                    ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                ],
            ];
        }

        // Asking with account
        $msg = "👥 **Data Warga yang Sudah Memiliki Akun Login SIWIL{$scopeLabel}:**\n\n".
            "Berdasarkan tabel **Data Kependudukan Terpadu** pada dashboard:\n".
            "• Terdapat **{$hasAccountCount} orang warga yang sudah memiliki akun login** aktif (dari total {$totalResidents} warga terdaftar{$scopeLabel}).\n".
            "• Terdapat **{$noAccountCount} orang warga yang belum memiliki akun** (berstatus badge **Belum Ada Akun**).";

        return [
            'message' => trim($msg),
            'options' => [
                ['label' => 'Warga Tanpa Akun', 'icon' => 'search', 'next_node' => 'db_overview'],
                ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
            ],
        ];
    }

    /**
     * Smart local fallback responder when external Gemini API is unreachable or unset.
     */
    private function handleSmartFallback(string $message, User $user, array $scopedData): array
    {
        $lower = strtolower($message);
        $role = $user->role;
        $extra = $scopedData['extra_data'] ?? [];

        // Greetings
        if (preg_match('/^(halo|hai|hi|pagi|siang|sore|malam|assalamu|permisi|tes)/i', $lower)) {
            $profile = $extra['profile'] ?? $this->getUserProfileData($user);

            return [
                'message' => "Halo Bapak/Ibu **{$profile['name']}** ({$profile['role_label']})! 👋 Saya Asisten Virtual SIWIL. Ada yang bisa saya bantu hari ini terkait layanan mandiri, data kependudukan, pengurusan surat, pengaduan, atau kontak wilayah Anda?",
                'options' => [
                    ['label' => 'Data Diri Saya', 'icon' => 'search', 'next_node' => 'profil_saya'],
                    ['label' => 'Buat Surat Baru', 'icon' => 'document', 'next_node' => 'surat_pilih_jenis'],
                    ['label' => 'Lapor/Pengaduan', 'icon' => 'alert', 'next_node' => 'lapor_mulai'],
                    ['label' => 'Info Kontak Pengurus', 'icon' => 'phone', 'next_node' => 'info_kontak'],
                ],
            ];
        }

        $clean = preg_replace('/[^\w\s]/', ' ', $lower);

        // 0. User asking "siapa kamu" / "kamu siapa" / "siapa anda" / "siapa dirimu" / AI identity
        if (preg_match('/(?:siapa\s*(?:sih\s*)?(?:kamu|anda|dirimu|lu|ente|bot|ai\b)|(?:kamu|anda|dirimu|lu|ente|bot|ai\b)\s*(?:itu\s*)?siapa|apa\s*itu\s*siwil\s*ai|tentang\s*(?:kamu|dirimu|siwil\s*ai)|kenalan)/i', $clean)) {
            return [
                'message' => "Saya adalah **SIWIL AI**, Asisten Virtual Cerdas Sistem Informasi Wilayah Terpadu (SIWIL). 🤖\n\n".
                    "Saya bertugas untuk membantu Anda dalam mengakses informasi dan layanan kependudukan, seperti:\n".
                    "• Pengecekan data kependudukan dan statistik wilayah resmi\n".
                    "• Informasi panduan dan pembuatan surat pengantar mandiri\n".
                    "• Layanan pelaporan pengaduan dan keluhan warga\n".
                    "• Informasi kontak resmi pengurus RT, RW, dan Kantor Desa\n".
                    "• Pemantauan pengumuman resmi dan musyawarah/polling warga\n\n".
                    'Ada yang bisa saya bantu terkait layanan SIWIL saat ini?',
                'options' => [
                    ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                ],
            ];
        }

        // 1. User asking "siapa saya" / who am I / non-complete personal data: ONLY show Nama & Jabatan
        $isAskingWhoAmI = (bool) preg_match('/(siapa\s+(saya|aku)|nama\s*(saya|ku)|jabatan\s*(saya|ku)|peran\s*(saya|ku)|posisi\s*(saya|ku)|(saya|aku)\s*(sebagai\s*apa|siapa))/i', $clean)
            && ! preg_match('/(lengkap|profil|biodata|detail|seluruh|semua|kartu\s*keluarga|kk|alamat|nik|data\s*diri)/i', $clean);

        if ($isAskingWhoAmI) {
            $profile = $extra['profile'] ?? $this->getUserProfileData($user);
            $msg = "👤 Anda terdaftar di SIWIL sebagai **{$profile['name']}** dengan jabatan / peran sebagai **{$profile['role_label']}**.\n\n".
                '_Untuk melihat data diri lengkap (NIK, alamat, KK, nomor kontak), silakan pilih menu di bawah ini:_';

            return [
                'message' => $msg,
                'options' => [
                    ['label' => 'Lihat Data Diri Lengkap', 'icon' => 'search', 'next_node' => 'profil_saya'],
                    ['label' => 'Kembali ke Menu Awal', 'icon' => 'home', 'next_node' => 'start'],
                ],
            ];
        }

        // 2. User asking explicitly for complete personal data / profil / NIK / biodata / specific personal attributes
        $isAskingFullProfile = (
            preg_match('/^(profil|biodata|data\s*diri|profil\s*saya|data\s*diri\s*saya|biodata\s*saya)$/i', trim($clean)) ||
            (
                preg_match('/(data\s*(?:diri|pribadi)\s*(?:saya|ku|milik\s*saya|pribadi)|profil\s*(?:lengkap|saya|ku|akun|pribadi)|tentang\s*(?:saya|aku|akun)|informasi\s*(?:diri|akun|saya)|(?:nik|no(?:mor)?\s*(?:hp|wa)|alamat|ktp|tempat\s*lahir|tanggal\s*lahir|kapan\s*lahir|agama|pendidikan|status\s*perkawinan|pekerjaan)\s+(?:saya|ku|milik\s*saya)|nik\s*(?:saya|ku)|alamat\s*(?:saya|ku)|agama\s*(?:saya|ku)|tempat\s*lahir\s*(?:saya|ku)|tanggal\s*lahir\s*(?:saya|ku)|pendidikan\s*(?:saya|ku)|pekerjaan\s*(?:saya|ku)|kartu\s*keluarga\s*(?:saya|ku)|kk\s*(?:saya|ku))/i', $clean)
                && ! preg_match('/(warga|penduduk|pengurus|ketua|anggota|tetangga|semua|daftar|bernama)/i', $clean)
            )
        );

        if ($isAskingFullProfile) {
            $profile = $extra['profile'] ?? $this->getUserProfileData($user);
            $msg = "👤 **Profil & Data Diri Anda di SIWIL:**\n\n".
                "• **Nama Lengkap:** {$profile['name']}\n".
                "• **NIK:** `{$profile['nik']}`\n".
                "• **Peran / Jabatan:** {$profile['role_label']}\n".
                "• **Jenis Kelamin:** {$profile['gender']}\n".
                "• **Tempat, Tanggal Lahir:** {$profile['birth_info']}\n".
                "• **Agama:** {$profile['religion']}\n".
                "• **Status Perkawinan:** {$profile['marital_status']}\n".
                "• **Pendidikan Terakhir:** {$profile['education']}\n".
                "• **Pekerjaan:** {$profile['occupation']}\n".
                "• **Status Hubungan Keluarga:** {$profile['family_status']} ({$profile['status']})\n".
                "• **No. Kartu Keluarga (KK):** {$profile['kk_number']}\n".
                "• **Alamat Lengkap:** {$profile['address']}\n".
                "• **Wilayah:** RT {$profile['rt']} / RW {$profile['rw']}, Desa {$profile['village']}\n".
                "• **No. HP/WA:** {$profile['phone']}\n".
                "• **Email:** {$profile['email']}\n";

            return [
                'message' => $msg,
                'options' => [
                    ['label' => 'Cek Status Layanan', 'icon' => 'search', 'next_node' => 'cek_status_pilih'],
                    ['label' => 'Kembali ke Menu Awal', 'icon' => 'home', 'next_node' => 'start'],
                ],
            ];
        }

        // Available services question (for Warga or anyone)
        if (preg_match('/(layanan\s*(yang)?\s*tersedia|layanan\s*apa|ada\s*layanan|fasilitas|fitur\s*(apa)?)/i', $lower)) {
            $servicesMsg = "🏛️ **Layanan yang Tersedia di SIWIL:**\n\n".
                "1. 📄 **Pengajuan Surat Mandiri**: Pembuatan Surat Pengantar Domisili, SKTM, dan SKU secara online langsung ke pengurus RT/RW.\n".
                "2. 📢 **Pengaduan Warga**: Layanan pelaporan masalah infrastruktur jalan, kebersihan lingkungan, keamanan, dan ketertiban.\n".
                "3. 🗳️ **Musyawarah & Polling**: Partisipasi aktif dalam musyawarah pengambilan keputusan dan pemungutan suara warga.\n".
                "4. 🔍 **Cek Status Layanan**: Memeriksa status verifikasi surat dan perkembangan tindak lanjut pengaduan Anda.\n".
                '5. 📞 **Informasi Kontak Pengurus**: Layanan informasi kontak resmi Ketua RT, Ketua RW, dan Kantor Desa.';

            return [
                'message' => $servicesMsg,
                'options' => [
                    ['label' => 'Buat Surat Baru', 'icon' => 'document', 'next_node' => 'surat_pilih_jenis'],
                    ['label' => 'Lapor Pengaduan', 'icon' => 'alert', 'next_node' => 'lapor_mulai'],
                    ['label' => 'Kontak Pengurus', 'icon' => 'phone', 'next_node' => 'info_kontak'],
                ],
            ];
        }

        // 3. Universal Password Shield: protect passwords from ever being leaked or displayed
        $isAskingWithoutPassword = (bool) preg_match('/(?:tidak\s+(?:dengan|termasuk)|tanpa|kecuali|bukan)\s*(?:kata\s*sandi|password|pin)/i', $clean);
        $isAskingPassword = ! $isAskingWithoutPassword && (bool) preg_match('/(?:apa\s+(?:saja\s+)?(?:kata\s*sandi|password|pin)|(?:tampilkan|lihat|bocorkan|minta|berikan|cari|cek)\s+(?:.*)?(?:kata\s*sandi|password|pin)|(?:kata\s*sandi|password|pin)\s+(?:saya|dia|warga|user|pengguna|akun|admin|rt|rw|desa)|^(?:kata\s*sandi|password|pin)$)/i', $clean);

        if ($isAskingPassword) {
            return [
                'message' => "🔒 **Perlindungan Keamanan Kata Sandi (Password):**\n\n".
                    "Sesuai standar keamanan sistem SIWIL dan kebijakan privasi data kependudukan:\n".
                    "• Seluruh kata sandi pengguna di database dienkripsi satu arah menggunakan algoritma hashing kriptografis (**Bcrypt**) dan **tidak pernah disimpan dalam bentuk teks biasa**.\n".
                    "• Oleh karena itu, kata sandi asli **tidak dapat dibaca, ditampilkan, maupun dibocorkan oleh AI kepada siapa pun** demi menjaga kerahasiaan dan keamanan akun.\n\n".
                    ($role === 'SUPER_ADMIN'
                        ? 'Sebagai **Super Administrator**, Anda dapat membantu mereset kata sandi pengguna yang membutuhkan pembaruan akses melalui menu **Manajemen User**.'
                        : 'Jika Anda membutuhkan pembaruan kata sandi, silakan gunakan fitur ganti kata sandi pada menu **Pengaturan Akun** atau hubungi Administrator wilayah Anda.'),
                'options' => [
                    ['label' => 'Lihat Menu Sidebar', 'icon' => 'home', 'next_node' => 'start'],
                    ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                ],
            ];
        }

        // Direct confirmation if AI is reading the active dashboard (e.g. "baca dashboard ga si")
        $dashboardDirectResponse = $this->handleDirectDashboardConfirmationQuery($clean, $lower, $user);
        if ($dashboardDirectResponse !== null) {
            return $dashboardDirectResponse;
        }

        // Specific Resident Account Status Query (e.g. "ada berapa warga yang tidak mempunyai akun", "berapa warga belum punya akun")
        $accountStatusResponse = $this->handleResidentAccountStatusQuery($clean, $lower, $user);
        if ($accountStatusResponse !== null) {
            return $accountStatusResponse;
        }

        // 4A. User asking for Sidebar menus & dashboard modules for EACH / ALL ROLES or dashboards in entire sidebar
        $isAskingSidebarForEachRole = (
            (
                preg_match('/(?:tiap|semua|masing\s*masing|setiap|antar|per)\s*(?:role|peran|tingkat|tingkatan)(?:nya)?/i', $clean)
                || preg_match('/(?:dashboard\s*(?:dashboard)?\s*(?:sperti|seperti)\s*ini|seluruh\s*sidebar|tiap\s*sidebar|sidebar\s*tiap\s*role)/i', $clean)
                || (str_contains($clean, 'dashboard') && str_contains($clean, 'sidebar') && (str_contains($clean, 'knowledge') || str_contains($clean, 'database') || str_contains($clean, 'perubahan')))
            )
            && (
                preg_match('/(?:sidebar|menu|dashboard|modul|isi|fitur|navigasi)/i', $clean)
                || str_contains($clean, 'apa')
                || str_contains($clean, 'baca')
                || str_contains($clean, 'knowledge')
                || str_contains($clean, 'database')
            )
        );

        if ($isAskingSidebarForEachRole) {
            return [
                'message' => $this->getSidebarAndDashboardForAllRoles($user),
                'options' => [
                    ['label' => 'Lihat Menu Sidebar Saya', 'icon' => 'home', 'next_node' => 'start'],
                    ['label' => 'Pembaruan Terkini', 'icon' => 'search', 'next_node' => 'start'],
                    ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                ],
            ];
        }

        // 4B. User asking specifically about a particular role's sidebar menus (e.g. "menu sidebar untuk RT", "menu sidebar RW", etc.)
        if (preg_match('/(?:menu|sidebar|dashboard).*(?:role\s+|untuk\s+|bagi\s+|akun\s+|pengurus\s+|sidebar\s+)?rt\b|(?:role|untuk|bagi|pengurus|akun)\s*rt\b.*(?:menu|sidebar|dashboard)/i', $clean)) {
            return [
                'message' => $this->getSidebarMenuList('RT'),
                'options' => [['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start']],
            ];
        }
        if (preg_match('/(?:menu|sidebar|dashboard).*(?:role\s+|untuk\s+|bagi\s+|akun\s+|pengurus\s+|sidebar\s+)?rw\b|(?:role|untuk|bagi|pengurus|akun)\s*rw\b.*(?:menu|sidebar|dashboard)/i', $clean)) {
            return [
                'message' => $this->getSidebarMenuList('RW'),
                'options' => [['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start']],
            ];
        }
        if (preg_match('/(?:menu|sidebar|dashboard).*(?:role\s+warga|untuk\s+warga|bagi\s+warga|akun\s+warga|sidebar\s+warga)|(?:role\s+warga|untuk\s+warga|bagi\s+warga|akun\s+warga).*(?:menu|sidebar|dashboard)/i', $clean)) {
            return [
                'message' => $this->getSidebarMenuList('WARGA'),
                'options' => [['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start']],
            ];
        }
        if (preg_match('/(?:menu|sidebar|dashboard).*(?:role\s+(?:admin\s+)?desa|untuk\s+(?:perangkat\s+|admin\s+)?desa|bagi\s+(?:perangkat\s+|admin\s+)?desa|akun\s+desa|admin\s+desa|perangkat\s+desa|sidebar\s+desa)|(?:role\s+(?:admin\s+)?desa|untuk\s+(?:perangkat\s+|admin\s+)?desa|bagi\s+(?:perangkat\s+|admin\s+)?desa|admin\s+desa|perangkat\s+desa).*(?:menu|sidebar|dashboard)/i', $clean)) {
            return [
                'message' => $this->getSidebarMenuList('ADMIN_DESA'),
                'options' => [['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start']],
            ];
        }
        if (preg_match('/(?:menu|sidebar|dashboard).*(?:role\s+super\s*admin|untuk\s+super\s*admin|super\s*admin|sidebar\s+super\s*admin)|(?:role\s+super\s*admin|super\s*admin).*(?:menu|sidebar|dashboard)/i', $clean)) {
            return [
                'message' => $this->getSidebarMenuList('SUPER_ADMIN'),
                'options' => [['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start']],
            ];
        }

        // 4C. User asking for the ENTIRE REAL CONTENTS of all sidebar menus
        $isAskingWholeSidebarMenuContent = (
            preg_match('/(?:keseluruhan|semua|seluruh|lengkap|detail|baca|apa\s+saja)?\s*(?:isi\s+)?menu\s*menu.*sidebar|isi\s+(?:seluruh|semua|keseluruhan|dari\s+setiap|tiap)?\s*menu\s*(?:di\s*)?sidebar|isi\s+(?:dari\s+)?(?:semua\s+)?menu\s*sidebar|keseluruhan\s+isi\s+menu/i', $clean)
            || (str_contains($clean, 'isi') && str_contains($clean, 'menu') && (str_contains($clean, 'sidebar') || str_contains($clean, 'keseluruhan')))
        );

        if ($isAskingWholeSidebarMenuContent) {
            return [
                'message' => $this->getWholeSidebarMenuContents($user),
                'options' => [
                    ['label' => 'Ringkasan Dashboard', 'icon' => 'home', 'next_node' => 'start'],
                    ['label' => 'Pembaruan Terkini', 'icon' => 'search', 'next_node' => 'start'],
                    ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                ],
            ];
        }

        // 5. Specific Sidebar Menu Inquiries (Manajemen User, APBDes, Audit Log, AI Knowledge, Pengaturan Akun, Koneksi Bot)
        if (preg_match('/(?:isi\s+menu.*user|menu\s*(?:di\s*)?sidebar.*user|menu\s+manajemen\s+user|menu\s+user)/i', $clean)) {
            if (! in_array($role, ['SUPER_ADMIN', 'ADMIN_DESA', 'DESA'])) {
                return [
                    'message' => "Mohon maaf, menu **Manajemen User** tidak tersedia untuk akun {$this->getRoleDisplayName($role)} (hanya dapat diakses oleh Administrator Desa dan Super Admin).",
                    'options' => [['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start']],
                ];
            }

            return [
                'message' => "🛡️ **Isi Menu Manajemen User (Akun {$this->getRoleDisplayName($role)}):**\n\n".$this->getUserManagementMenuContent($user),
                'options' => [
                    ['label' => 'Lihat Menu Sidebar', 'icon' => 'home', 'next_node' => 'start'],
                    ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                ],
            ];
        }

        if (preg_match('/(?:isi\s+menu.*(?:apbdes|pembangunan)|menu\s+apbdes|menu\s+pembangunan)/i', $clean) && ! in_array($role, ['WARGA', 'RT', 'RW'])) {
            return [
                'message' => "🏗️ **Isi Menu Pembangunan & APBDes (Akun {$this->getRoleDisplayName($role)}):**\n\n".$this->getBudgetDevelopmentMenuContent($user),
                'options' => [
                    ['label' => 'Lihat Menu Sidebar', 'icon' => 'home', 'next_node' => 'start'],
                    ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                ],
            ];
        }

        if (preg_match('/(?:isi\s+menu.*audit|menu\s+audit|menu\s+laporan)/i', $clean) && $role !== 'WARGA') {
            return [
                'message' => "📑 **Isi Menu Laporan & Audit Log (Akun {$this->getRoleDisplayName($role)}):**\n\n".$this->getAuditReportMenuContent($user),
                'options' => [
                    ['label' => 'Lihat Menu Sidebar', 'icon' => 'home', 'next_node' => 'start'],
                    ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                ],
            ];
        }

        if (preg_match('/(?:isi\s+(?:menu\s+)?)?(?:kelola\s+)?ai\s*knowledge|basis\s*pengetahuan/i', $clean) && $role === 'SUPER_ADMIN') {
            return [
                'message' => "🧠 **Isi Menu Kelola AI Knowledge (Akun Super Admin):**\n\n".$this->getAiKnowledgeMenuContent($user),
                'options' => [
                    ['label' => 'Lihat Menu Sidebar', 'icon' => 'home', 'next_node' => 'start'],
                    ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                ],
            ];
        }

        if (preg_match('/(?:isi\s+(?:menu\s+)?)?pengaturan\s+akun/i', $clean)) {
            return [
                'message' => "⚙️ **Isi Menu Pengaturan Akun (Akun {$this->getRoleDisplayName($role)}):**\n\n".$this->getAccountSettingsMenuContent($user),
                'options' => [
                    ['label' => 'Lihat Menu Sidebar', 'icon' => 'home', 'next_node' => 'start'],
                    ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                ],
            ];
        }

        if (preg_match('/(?:isi\s+(?:menu\s+)?)?koneksi\s+bot|bot\s*whatsapp|bot\s*telegram/i', $clean)) {
            return [
                'message' => "🤖 **Isi Menu Koneksi Bot (Akun {$this->getRoleDisplayName($role)}):**\n\n".$this->getBotConnectionMenuContent($user),
                'options' => [
                    ['label' => 'Lihat Menu Sidebar', 'icon' => 'home', 'next_node' => 'start'],
                    ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                ],
            ];
        }

        // 6. User asking to read the WHOLE dashboard / dashboard + sidebar / all modules & updates
        $isAskingWholeDashboard = (
            preg_match('/(?:keseluruhan|seluruh|semua|lengkap|detail)\s+(?:.*)?dashboard|dashboard\s+(?:.*)?(?:keseluruhan|seluruh|semua|lengkap|detail|sidebar)|baca\s+(?:keseluruhan|semua|seluruh)\s+(?:dari\s+)?dashboard|dashboard\s+(?:dan|beserta|dengan)\s+sidebar|sidebar\s+(?:dan|beserta|dengan)\s+dashboard/i', $clean)
            || (str_contains($clean, 'dashboard') && (str_contains($clean, 'sidebar') || str_contains($clean, 'keseluruhan')))
        );

        if ($isAskingWholeDashboard) {
            $dashMsg = $this->getDashboardSummary($user);
            $sideMsg = $this->getSidebarMenuList($role);
            $updateMsg = $this->getRecentUpdatesSummary($user);

            return [
                'message' => trim($dashMsg."\n\n".$sideMsg."\n\n".$updateMsg),
                'options' => [
                    ['label' => 'Lihat Menu Sidebar', 'icon' => 'home', 'next_node' => 'start'],
                    ['label' => 'Pembaruan Terkini', 'icon' => 'search', 'next_node' => 'start'],
                    ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                ],
            ];
        }

        // 7. User asking specifically about Sidebar Menus / Modules (without whole dashboard or contents)
        if (preg_match('/(?:sidebar|menu\s*(?:di\s*)?sidebar|fitur\s*(?:di\s*)?sidebar|daftar\s+menu|ada\s+menu\s+apa|menu\s+(?:apa\s+saja|yang\s+ada)|modul\s+sidebar)/i', $clean)) {
            return [
                'message' => $this->getSidebarMenuList($role),
                'options' => [
                    ['label' => 'Lihat Ringkasan Dashboard', 'icon' => 'home', 'next_node' => 'start'],
                    ['label' => 'Kembali ke Menu Awal', 'icon' => 'home', 'next_node' => 'start'],
                ],
            ];
        }

        // 8. User asking about Recent Updates / Perubahan / Pembaruan from Dashboard/Database
        $isAskingDashboardUpdates = (
            ! str_contains($lower, 'audit') && (
                preg_match('/(?:apa\s+(?:saja\s+)?(?:yang\s+)?(?:di\s*)?update|update(?:\s+terbaru|\s+terkini|\s+dari\s+dashboard)?|pembaruan(?:\s+terbaru|\s+terkini|\s+dari\s+dashboard)?|perubahan(?:\s+terbaru|\s+terkini|\s+data|\s+dashboard)?|apa\s+(?:saja\s+)?(?:yang\s+)?(?:baru|berubah)|ada\s+(?:yang\s+)?berubah|kabar\s+terbaru|notifikasi\s+terbaru|aktivitas\s+terbaru|riwayat\s+pembaruan)/i', $clean)
                || (str_contains($clean, 'dashboard') && (str_contains($clean, 'berubah') || str_contains($clean, 'update') || str_contains($clean, 'pembaruan')))
            )
        );

        if ($isAskingDashboardUpdates) {
            return [
                'message' => $this->getRecentUpdatesSummary($user),
                'options' => [
                    ['label' => 'Lihat Ringkasan Dashboard', 'icon' => 'home', 'next_node' => 'start'],
                    ['label' => 'Lihat Menu Sidebar', 'icon' => 'home', 'next_node' => 'start'],
                    ['label' => 'Kembali ke Menu Awal', 'icon' => 'home', 'next_node' => 'start'],
                ],
            ];
        }

        // ==================== ROLE WARGA HANDLERS ====================
        if ($role === 'WARGA') {
            $rtInfo = $extra['rt_info'] ?? null;
            $rwInfo = $extra['rw_info'] ?? null;
            $desaInfo = $extra['desa_info'] ?? null;

            // 0. Dashboard query for Warga: "apa yang ada di dashboard", "data dashboard", "baca dashboard", etc.
            if (preg_match('/(?:apa\s+(?:yang\s+ada|saja\s+ada|ada|isi)|ada\s+apa(?:\s+saja)?|isi|data|ringkasan|statistik|rekap|tampilan|menu|informasi|status|baca|lihat|tampilkan|cek|keseluruhan|semua|detail)\s+(?:.*)?dashboard|dashboard\s+(?:ada\s+apa|isinya\s+apa|menampilkan\s+apa|itu\s+apa)|^dashboard$|baca\s+(?:keseluruhan|semua)\s+(?:dari\s+)?dashboard/i', $clean)) {
                $dashMsg = $this->getDashboardSummary($user);
                $sideMsg = $this->getSidebarMenuList($role);
                $updateMsg = $this->getRecentUpdatesSummary($user);

                $fullMsg = $dashMsg."\n\n".$sideMsg."\n\n".$updateMsg;

                return [
                    'message' => trim($fullMsg),
                    'options' => [
                        ['label' => 'Buat Surat Baru', 'icon' => 'document', 'next_node' => 'surat_pilih_jenis'],
                        ['label' => 'Lapor Pengaduan', 'icon' => 'alert', 'next_node' => 'lapor_mulai'],
                        ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                    ],
                ];
            }

            // 1. Pertanyaan spesifik mengenai Ketua RT ("siapa rt saya", "ketua rt", "siapa nama rt nya", dll)
            if (preg_match('/(?:siapa\s*(?:nama\s*)?(?:ketua\s*)?rt|ketua\s*rt|nama\s*rt|kontak\s*rt|nomor\s*rt|no\s*(?:hp|wa)\s*rt|wa\s*rt|rt\s*saya)/i', $lower)) {
                if ($rtInfo) {
                    $msg = "Ketua RT Anda di **RT {$rtInfo['number']} / RW {$rtInfo['rw_number']} ({$rtInfo['village']})** adalah **{$rtInfo['name']}**.\n\n".
                        "Berikut kontak resmi Ketua RT Anda:\n".
                        "• 📞 **WhatsApp / Telp**: `{$rtInfo['phone']}`\n".
                        "• ✉️ **Email**: {$rtInfo['email']}\n".
                        "• 📍 **Wilayah**: RT {$rtInfo['number']} / RW {$rtInfo['rw_number']}, {$rtInfo['village']}\n\n".
                        '_Silakan hubungi Ketua RT untuk keperluan surat pengantar atau koordinasi lingkungan._';

                    return [
                        'message' => $msg,
                        'options' => [
                            ['label' => 'Buat Surat Pengantar', 'icon' => 'document', 'next_node' => 'surat_pilih_jenis'],
                            ['label' => 'Lihat Kontak RW', 'icon' => 'phone', 'next_node' => 'info_kontak'],
                            ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                        ],
                    ];
                }
            }

            // 2. Pertanyaan spesifik mengenai Ketua RW ("siapa rw saya", "ketua rw", "siapa nama rw nya", dll)
            if (preg_match('/(?:siapa\s*(?:nama\s*)?(?:ketua\s*)?rw|ketua\s*rw|nama\s*rw|kontak\s*rw|nomor\s*rw|no\s*(?:hp|wa)\s*rw|wa\s*rw|rw\s*saya)/i', $lower)) {
                if ($rwInfo) {
                    $msg = "Ketua RW Anda di **RW {$rwInfo['number']} ({$rwInfo['village']})** adalah **{$rwInfo['name']}**.\n\n".
                        "Berikut kontak resmi Ketua RW Anda:\n".
                        "• 📞 **WhatsApp / Telp**: `{$rwInfo['phone']}`\n".
                        "• ✉️ **Email**: {$rwInfo['email']}\n".
                        "• 📍 **Wilayah**: RW {$rwInfo['number']}, {$rwInfo['village']}\n\n".
                        '_Silakan hubungi Ketua RW untuk persetujuan surat lanjutan atau kegiatan tingkat RW._';

                    return [
                        'message' => $msg,
                        'options' => [
                            ['label' => 'Lihat Kontak RT', 'icon' => 'phone', 'next_node' => 'info_kontak'],
                            ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                        ],
                    ];
                }
            }

            // 3. Pertanyaan spesifik mengenai Kantor Desa
            if (preg_match('/(kantor\s*desa|kontak\s*desa|nomor\s*desa|kepala\s*desa|admin\s*desa|no\s*(hp|wa)\s*desa|desa\s*saya)/i', $lower)) {
                if ($desaInfo) {
                    $msg = "Berikut adalah informasi kontak resmi **Kantor {$desaInfo['name']}**:\n\n".
                        "• 🏢 **Instansi**: Kantor {$desaInfo['name']}\n".
                        "• 📞 **WhatsApp / Telp**: `{$desaInfo['phone']}`\n".
                        "• ✉️ **Email**: {$desaInfo['email']}\n".
                        "• 🕒 **Jam Pelayanan**: Senin – Jumat (08:00 – 15:00 WIB)\n\n".
                        '_Untuk layanan surat keterangan desa, pastikan telah melengkapi surat pengantar dari RT/RW setempat._';

                    return [
                        'message' => $msg,
                        'options' => [
                            ['label' => 'Buat Surat Mandiri', 'icon' => 'document', 'next_node' => 'surat_pilih_jenis'],
                            ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                        ],
                    ];
                }
            }

            // 4. Pertanyaan kontak seluruh pengurus wilayah
            if (preg_match('/(kontak\s*pengurus|pengurus\s*wilayah|semua\s*kontak|daftar\s*kontak|nomor\s*pengurus)/i', $lower)) {
                $msg = "Berikut adalah daftar kontak resmi pengurus wilayah Anda:\n\n";
                if ($rtInfo) {
                    $msg .= "1. 🏠 **Ketua RT {$rtInfo['number']}**: {$rtInfo['name']}\n".
                        "   • WA/Telp: `{$rtInfo['phone']}` | Email: {$rtInfo['email']}\n\n";
                }
                if ($rwInfo) {
                    $msg .= "2. 🏛️ **Ketua RW {$rwInfo['number']}**: {$rwInfo['name']}\n".
                        "   • WA/Telp: `{$rwInfo['phone']}` | Email: {$rwInfo['email']}\n\n";
                }
                if ($desaInfo) {
                    $msg .= "3. 🏢 **Kantor {$desaInfo['name']}**:\n".
                        "   • WA/Telp: `{$desaInfo['phone']}` | Email: {$desaInfo['email']}\n";
                }

                return [
                    'message' => trim($msg),
                    'options' => [
                        ['label' => 'Buat Surat Pengantar', 'icon' => 'document', 'next_node' => 'surat_pilih_jenis'],
                        ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                    ],
                ];
            }

            // Document requests: "status surat", "surat saya", "pengajuan surat", etc.
            if (preg_match('/(status\s*surat|surat\s*(saya|ku)|riwayat\s*surat|pengajuan\s*surat|cek\s*surat)/i', $lower)) {
                $myDocs = $extra['my_docs'] ?? collect();
                if ($myDocs->isNotEmpty()) {
                    $msg = "📄 **Riwayat Pengajuan Surat Anda di SIWIL:**\n\n";
                    foreach ($myDocs as $doc) {
                        $badge = match ($doc->status) {
                            'PENDING' => '🟡 Menunggu Verifikasi RT',
                            'APPROVED_RT' => '🔵 Disetujui RT (Menunggu RW)',
                            'APPROVED_RW' => '🟣 Disetujui RW (Menunggu Desa)',
                            'COMPLETED' => '🟢 Selesai / Terbit',
                            'REJECTED' => '🔴 Ditolak',
                            default => $doc->status,
                        };
                        $tgl = $doc->created_at ? $doc->created_at->format('d M Y') : '-';
                        $msg .= "• **{$doc->type}**\n  Status: `{$doc->status}` ({$badge})\n  Tanggal: {$tgl}\n\n";
                    }

                    return [
                        'message' => trim($msg),
                        'options' => [
                            ['label' => 'Buat Surat Baru', 'icon' => 'document', 'next_node' => 'surat_pilih_jenis'],
                            ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                        ],
                    ];
                }

                return [
                    'message' => 'Anda belum pernah mengajukan surat di SIWIL. Ingin membuat permohonan surat baru?',
                    'options' => [
                        ['label' => 'Buat Surat Baru', 'icon' => 'document', 'next_node' => 'surat_pilih_jenis'],
                        ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                    ],
                ];
            }

            // Complaints: "status pengaduan", "pengaduan saya", "laporan saya", "keluhan saya"
            if (preg_match('/(status\s*pengaduan|pengaduan\s*(saya|ku)|laporan\s*(saya|ku)|keluhan\s*(saya|ku)|cek\s*laporan)/i', $lower)) {
                $myComplaints = $extra['my_complaints'] ?? collect();
                if ($myComplaints->isNotEmpty()) {
                    $msg = "📢 **Riwayat Pengaduan Anda di SIWIL:**\n\n";
                    foreach ($myComplaints as $c) {
                        $statusIcon = match ($c->status) {
                            'PENDING' => '🟡 PENDING',
                            'PROCESSING' => '🔵 SEDANG DIPROSES',
                            'RESOLVED' => '🟢 SELESAI',
                            'REJECTED' => '🔴 DITOLAK',
                            default => $c->status,
                        };
                        $tgl = $c->created_at ? $c->created_at->format('d M Y') : '-';
                        $msg .= "• {$statusIcon} **{$c->title}**\n  Status: `{$c->status}` — {$tgl}\n\n";
                    }

                    return [
                        'message' => trim($msg),
                        'options' => [
                            ['label' => 'Buat Pengaduan Baru', 'icon' => 'alert', 'next_node' => 'lapor_mulai'],
                            ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                        ],
                    ];
                }

                return [
                    'message' => 'Anda belum pernah membuat laporan pengaduan di SIWIL.',
                    'options' => [
                        ['label' => 'Buat Pengaduan Baru', 'icon' => 'alert', 'next_node' => 'lapor_mulai'],
                        ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                    ],
                ];
            }

            // Family members in their KK
            if (preg_match('/(keluarga|anggota\s*(keluarga|kk)|siapa\s*saja\s*(di|dalam)\s*kk|kartu\s*keluarga(\s*(saya|ku))?)/i', $lower)) {
                $familyMembers = $extra['family_members'] ?? collect();
                $kkNum = $extra['profile']['kk_number'] ?? '-';
                if ($familyMembers->isNotEmpty()) {
                    $msg = "👨‍👩‍👧‍👦 **Daftar Anggota Keluarga dalam KK Anda (No. KK: {$kkNum}):**\n\n";
                    foreach ($familyMembers as $fm) {
                        $msg .= "• **{$fm->full_name}** | NIK: `{$fm->nik}`\n  Hubungan/Status: {$fm->status} | JK: {$fm->gender} | Pekerjaan: {$fm->occupation}\n\n";
                    }

                    return [
                        'message' => trim($msg),
                        'options' => [
                            ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                        ],
                    ];
                }

                return [
                    'message' => 'Data keluarga untuk KK Anda belum terhubung di sistem SIWIL.',
                    'options' => [
                        ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                    ],
                ];
            }

            // Announcements
            if (preg_match('/(pengumuman|kegiatan|agenda|informasi\s*terbaru|berita\s*desa)/i', $lower)) {
                $announcements = $extra['announcements'] ?? collect();
                if ($announcements->isNotEmpty()) {
                    $msg = "📢 **Pengumuman Resmi Aktif di Wilayah Anda:**\n\n";
                    foreach ($announcements as $an) {
                        $msg .= "• **{$an->title}**\n  {$an->content}\n\n";
                    }

                    return [
                        'message' => trim($msg),
                        'options' => [
                            ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                        ],
                    ];
                }
            }
        }

        // ==================== ROLE RT HANDLERS ====================
        if ($role === 'RT') {
            $rtNum = $extra['rt_number'] ?? ($user->rt?->number ?? '-');
            $totalWarga = $extra['total_warga'] ?? 0;
            $totalKk = $extra['total_kk'] ?? 0;
            $totalPria = $extra['total_pria'] ?? 0;
            $totalWanita = $extra['total_wanita'] ?? 0;
            $rtResidentsList = $extra['rt_residents_list'] ?? '';
            $rtResidents = $extra['rt_residents'] ?? collect();

            // 0. Dashboard query for RT: "apa yang ada di dashboard", "data dashboard", "baca dashboard", etc.
            if (preg_match('/(?:apa\s+(?:yang\s+ada|saja\s+ada|ada|isi)|ada\s+apa(?:\s+saja)?|isi|data|ringkasan|statistik|rekap|tampilan|menu|informasi|status|baca|lihat|tampilkan|cek|keseluruhan|semua|detail)\s+(?:.*)?dashboard|dashboard\s+(?:ada\s+apa|isinya\s+apa|menampilkan\s+apa|itu\s+apa)|^dashboard$|baca\s+(?:keseluruhan|semua)\s+(?:dari\s+)?dashboard/i', $clean)) {
                $dashMsg = $this->getDashboardSummary($user);
                $sideMsg = $this->getSidebarMenuList($role);
                $updateMsg = $this->getRecentUpdatesSummary($user);

                $fullMsg = $dashMsg."\n\n".$sideMsg."\n\n".$updateMsg;

                return [
                    'message' => trim($fullMsg),
                    'options' => [
                        ['label' => 'Daftar Pengajuan Surat', 'icon' => 'document', 'next_node' => 'start'],
                        ['label' => 'Daftar Pengaduan', 'icon' => 'alert', 'next_node' => 'start'],
                        ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                    ],
                ];
            }

            // Demographics breakdown (only when explicitly asked: demografi, statistik, sebaran, or both pria and wanita)
            if (preg_match('/(demografi|statistik|sebaran\s*penduduk|(?:pria|laki[\s\-]*laki).*(?:wanita|perempuan)|(?:wanita|perempuan).*(?:pria|laki[\s\-]*laki))/i', $clean)) {
                return [
                    'message' => "👥 **Statistik Demografi Warga di RT {$rtNum} Anda:**\n\n".
                        "• **Total Penduduk:** {$totalWarga} jiwa\n".
                        "• **Laki-laki:** {$totalPria} jiwa\n".
                        "• **Perempuan:** {$totalWanita} jiwa\n".
                        "• **Total Kartu Keluarga (KK):** {$totalKk} KK",
                    'options' => [
                        ['label' => 'Lihat Daftar Warga', 'icon' => 'search', 'next_node' => 'start'],
                        ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                    ],
                ];
            }

            // Specific single metric queries for RT
            if (preg_match('/(?:ada\s+)?(?:berapa|jumlah|total)\s+(perempuan|wanita)/i', $lower)) {
                return [
                    'message' => "Di wilayah **RT {$rtNum}** Anda terdapat **{$totalWanita} orang perempuan**.",
                    'options' => [
                        ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                    ],
                ];
            }

            if (preg_match('/(?:ada\s+)?(?:berapa|jumlah|total)\s+(laki[\s\-]*laki|pria)/i', $lower)) {
                return [
                    'message' => "Di wilayah **RT {$rtNum}** Anda terdapat **{$totalPria} orang laki-laki**.",
                    'options' => [
                        ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                    ],
                ];
            }

            if (preg_match('/(?:ada\s+)?(?:berapa|jumlah|total)\s+(?:kk|kartu\s*keluarga)/i', $lower)) {
                return [
                    'message' => "Di wilayah **RT {$rtNum}** Anda terdapat **{$totalKk} Kartu Keluarga (KK)**.",
                    'options' => [
                        ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                    ],
                ];
            }

            if (preg_match('/(?:ada\s+)?(?:berapa|jumlah|total)\s+(?:warga|penduduk|jiwa)/i', $lower)) {
                return [
                    'message' => "Di wilayah **RT {$rtNum}** Anda terdapat **{$totalWarga} jiwa** ({$totalWarga} orang warga).",
                    'options' => [
                        ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                    ],
                ];
            }

            // 1. Proportional list of resident names in RT: "siapa saja warganya", "daftar warga", "nama nama warga", "sebutkan warganya"
            if (preg_match('/(?:siapa\s+saja\s+(?:nama\s+)?warga|daftar\s+warga|nama\s+nama\s+warga|sebutkan\s+(?:nama\s+)?warga|warga\s+siapa\s+saja)/i', $lower)) {
                $rtRes = Resident::where('rt_id', $user->rt_id)->orderBy('full_name')->get();
                $msg = "📋 **Daftar Nama Warga di RT {$rtNum} Anda:**\n\n";
                if ($rtRes->isEmpty()) {
                    $msg .= "Belum ada warga terdaftar di RT {$rtNum}.";
                } else {
                    foreach ($rtRes as $idx => $r) {
                        $num = $idx + 1;
                        $gender = match (strtoupper($r->gender ?? '')) {
                            'LAKI_LAKI', 'L', 'MALE' => 'Laki-laki',
                            'PEREMPUAN', 'P', 'FEMALE' => 'Perempuan',
                            default => $r->gender ?? '-',
                        };
                        $msg .= "{$num}. **{$r->full_name}** ({$gender})\n";
                    }
                }

                return [
                    'message' => trim($msg),
                    'options' => [
                        ['label' => 'Kontak & NIK Warga RT', 'icon' => 'phone', 'next_node' => 'start'],
                        ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                    ],
                ];
            }

            // 2. Specific resident personal data / biodata lookup: "cari warga [nama]", "data lengkap [nama]", "biodata [nama]"
            if (preg_match('/(?:cari\s+warga(?:\s+bernama|\s+nama)?|data\s+(?:lengkap\s+|diri\s+)?warga(?:\s+bernama|\s+nama)?|biodata\s+warga(?:\s+bernama|\s+nama)?|warga\s+(?:bernama|nama))\s+([a-zA-Z0-9\s]+)/i', $lower, $m)) {
                $searchName = trim($m[1]);
                $matched = Resident::with(['user', 'family', 'rt.rw.village'])
                    ->where('rt_id', $user->rt_id)
                    ->where(function ($q) use ($searchName) {
                        $q->where('nik', $searchName)->orWhere('full_name', 'like', "%{$searchName}%");
                    })
                    ->first();

                if ($matched) {
                    $hp = $matched->user?->phone_number ?? 'Belum ada No HP';
                    $addr = $matched->user?->address ?: ($matched->family?->address ?? '-');
                    $kkNum = $matched->family?->kk_number ?? '-';
                    $tgl = $matched->birth_date ? $matched->birth_date->format('d-m-Y') : '-';
                    $ttl = ($matched->birth_place && $tgl !== '-') ? "{$matched->birth_place}, {$tgl}" : ($matched->birth_place ?: $tgl);
                    $agama = $matched->religion ? ucfirst(strtolower($matched->religion)) : '-';
                    $pendidikan = $matched->education ?? '-';
                    $perkawinan = $matched->marital_status ? ucfirst(strtolower($matched->marital_status)) : '-';
                    $gender = match (strtoupper($matched->gender ?? '')) {
                        'LAKI_LAKI', 'L', 'MALE' => 'Laki-laki',
                        'PEREMPUAN', 'P', 'FEMALE' => 'Perempuan',
                        default => $matched->gender ?? '-',
                    };
                    $famStatus = $matched->is_head_of_family ? 'Kepala Keluarga' : ($matched->status ?? 'Anggota Keluarga');
                    $accStatus = $matched->user ? "{$matched->user->username} ({$matched->user->status})" : 'Belum Ada Akun';

                    $msg = "🔍 **Hasil Pencarian Warga di RT {$rtNum}: Data Lengkap {$matched->full_name}**\n\n".
                        "• **Nama Lengkap:** {$matched->full_name}\n".
                        "• **NIK:** `{$matched->nik}`\n".
                        "• **No. KK:** {$kkNum}\n".
                        "• **Jenis Kelamin:** {$gender}\n".
                        "• **TTL:** {$ttl}\n".
                        "• **Agama:** {$agama}\n".
                        "• **Status Perkawinan:** {$perkawinan}\n".
                        "• **Pendidikan Terakhir:** {$pendidikan}\n".
                        "• **Pekerjaan:** {$matched->occupation}\n".
                        "• **Status Hubungan Keluarga:** {$famStatus}\n".
                        "• **Alamat:** {$addr}\n".
                        "• **No. HP/WA:** {$hp}\n".
                        "• **Status Akun SIWIL:** {$accStatus}";

                    return [
                        'message' => trim($msg),
                        'options' => [
                            ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                        ],
                    ];
                }

                // Check if citizen is in another RT in database
                $outsideRes = Resident::with('rt')->where(function ($q) use ($searchName) {
                    $q->where('nik', $searchName)->orWhere('full_name', 'like', "%{$searchName}%");
                })->first();

                if ($outsideRes) {
                    $outRtNum = $outsideRes->rt?->number ?? '-';

                    return [
                        'message' => "Warga **{$outsideRes->full_name}** terdaftar di RT {$outRtNum} (di luar RT {$rtNum} Anda). Sesuai regulasi privasi UU PDP dan batas kewenangan wilayah, akun RT hanya dapat mengakses data kependudukan warga di RT Anda sendiri.",
                        'options' => [['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start']],
                    ];
                }

                return [
                    'message' => "Data warga dengan kata kunci \"{$searchName}\" tidak ditemukan di wilayah RT {$rtNum} Anda.",
                    'options' => [
                        ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                    ],
                ];
            }

            // 3. Asking specifically for NIK or contact numbers of residents in their RT
            if (preg_match('/(nik|kontak|nomor\s*hp|no\s*hp|wa|telepon)\s+(warga|penduduk)/i', $lower)) {
                $listMsg = "Berikut adalah daftar warga beserta NIK dan nomor kontak di wilayah **RT {$rtNum}** Anda:\n\n".
                    ($rtResidentsList ?: "• Belum ada warga terdaftar di RT {$rtNum}.\n").
                    "\n_Catatan: Akses NIK dan kontak ini hanya mencakup warga di lingkup RT {$rtNum} Anda._";

                return [
                    'message' => $listMsg,
                    'options' => [
                        ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                    ],
                ];
            }

            // 4. Village Head query for RT: "siapa kepala desa", "kades", "kontak desa"
            if (preg_match('/(?:siapa|kontak|nomor|no\s*hp|wa)\s*(?:kepala\s*desa|kades|admin\s*desa|kantor\s*desa)/i', $lower)) {
                $desaAdmin = User::whereIn('role', ['ADMIN_DESA', 'DESA'])->first();
                $vName = $user->rt?->rw?->village?->name ?? 'Desa';
                $name = $desaAdmin ? $desaAdmin->name : 'Kantor Desa';
                $phone = $desaAdmin?->phone_number ? " (No. WA/HP: {$desaAdmin->phone_number})" : '';

                return [
                    'message' => "Kepala / Admin {$vName} adalah **{$name}**{$phone}.",
                    'options' => [['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start']],
                ];
            }

            // Families / KK list in RT
            if (preg_match('/(daftar\s*kk|kepala\s*keluarga|data\s*kk|kartu\s*keluarga)/i', $lower)) {
                $famList = $extra['rt_families_list'] ?? '';

                return [
                    'message' => "📋 **Daftar Kartu Keluarga (KK) di RT {$rtNum} Anda:**\n\n".
                        ($famList ?: "• Belum ada KK terdaftar di RT {$rtNum}.\n"),
                    'options' => [
                        ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                    ],
                ];
            }

            // Document requests incoming to RT
            if (preg_match('/(surat|pengajuan\s*surat|permohonan|verifikasi\s*surat)/i', $lower)) {
                $docsList = $extra['rt_docs_list'] ?? '';
                $pDocs = $extra['pending_docs'] ?? 0;

                return [
                    'message' => "📄 **Permohonan Surat Warga di RT {$rtNum} (Pending: {$pDocs}):**\n\n".
                        ($docsList ?: "• Belum ada permohonan surat masuk di RT Anda.\n"),
                    'options' => [
                        ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                    ],
                ];
            }

            // Complaints in RT
            if (preg_match('/(pengaduan|keluhan|laporan\s*warga)/i', $lower)) {
                $compList = $extra['rt_complaints_list'] ?? '';
                $pComp = $extra['pending_complaints'] ?? 0;

                return [
                    'message' => "📢 **Laporan Pengaduan Warga di RT {$rtNum} (Pending: {$pComp}):**\n\n".
                        ($compList ?: "• Belum ada laporan pengaduan masuk di RT Anda.\n"),
                    'options' => [
                        ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                    ],
                ];
            }

            // Asking about specific other RT leader: e.g. "siapa ketua rt 03", "kontak rt 03", "ketua rt 03"
            if (preg_match('/(?:ketua|pengurus|kontak|nomor|no\s*hp|wa|telepon|siapa)\s*(?:ketua\s*)?rt\s*0?(\d+)/i', $lower, $m)) {
                $targetRtNum = ltrim($m[1], '0');
                $formattedTarget = str_pad($targetRtNum, 2, '0', STR_PAD_LEFT);
                $targetRt = Rt::with('rw.village')->where('number', $formattedTarget)->first();
                if ($targetRt) {
                    $targetHead = User::with('resident')->where('role', 'RT')->where('rt_id', $targetRt->id)->first();
                    $targetHeadName = $targetHead?->resident?->full_name ?? $targetHead?->name ?? 'Belum terdaftar';
                    $targetHeadPhone = $targetHead?->phone_number ?? 'Belum ada No HP';
                    $targetHeadEmail = $targetHead?->email ?? '-';

                    return [
                        'message' => "📋 **Informasi Pengurus RT {$formattedTarget}:**\n\n".
                            "• **Ketua RT:** {$targetHeadName}\n".
                            "• **No. WA/HP:** {$targetHeadPhone}\n".
                            "• **Email:** {$targetHeadEmail}\n".
                            '• **Wilayah:** RW '.($targetRt->rw?->number ?? '-').', Desa '.($targetRt->rw?->village?->name ?? '-')."\n\n".
                            "_Catatan: Sebagai Pengurus RT, Anda berhak mengetahui kontak koordinasi antar-pengurus RT. Data kependudukan/warga di RT {$formattedTarget} tetap dilindungi privasi._",
                        'options' => [
                            ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                        ],
                    ];
                }
            }

            // Asking about RT heads in general: "siapa nama rt nya", "daftar rt", "pengurus rt lain", etc.
            if (preg_match('/(?:siapa|daftar|sebutkan)\s*(?:saja\s+)?(?:nama\s+)?(?:ketua\s*)?rt(?:\s*nya)?/i', $lower) ||
                preg_match('/nama\s*(?:ketua\s*)?rt(?:\s*nya)?/i', $lower) ||
                preg_match('/(pengurus|ketua|kontak|daftar)\s*rt\s*(lain|selain)/i', $lower) ||
                preg_match('/rt\s*(lain|selain)/i', $lower)) {
                $rwId = $user->rt?->rw_id;
                $rts = $rwId ? Rt::where('rw_id', $rwId)->orderBy('number')->get() : collect();
                if ($rts->isNotEmpty()) {
                    $rwNum = $user->rt?->rw?->number ?? '-';
                    $msg = "Berikut Daftar Pengurus RT Lain di lingkungan **RW {$rwNum}**:\n\n";
                    foreach ($rts as $idx => $rt) {
                        $num = $idx + 1;
                        $head = User::with('resident')->where('role', 'RT')->where('rt_id', $rt->id)->first();
                        $headName = $head ? ($head->resident?->full_name ?: ($head->name ?: $head->username)) : 'Belum terdaftar';
                        $phone = $head?->phone_number ? " — {$head->phone_number}" : '';
                        $isYou = ($rt->id === $user->rt_id) ? ' *(Anda)*' : '';
                        $msg .= "{$num}. **RT {$rt->number}**: **{$headName}**{$isYou}{$phone}\n";
                    }
                    $msg .= "\n_Catatan: Akses ini ditujukan untuk koordinasi antar-pengurus RT. Data warga di RT lain tetap dilindungi privasi._";

                    return [
                        'message' => trim($msg),
                        'options' => [['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start']],
                    ];
                }

                $otherRtsList = $extra['other_rts_list'] ?? '';

                return [
                    'message' => "🤝 **Daftar Pengurus RT Lain (Koordinasi Wilayah):**\n\n".
                        ($otherRtsList ?: "• Belum ada pengurus RT lain terdaftar di RW Anda.\n")."\n".
                        '_Catatan: Akses ini ditujukan untuk koordinasi antar-pengurus RT. Data warga di RT lain tetap dilindungi privasi._',
                    'options' => [
                        ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                    ],
                ];
            }

            // Asking about Ketua RW: "siapa ketua rw", "kontak rw", "pengurus rw", "no wa rw"
            if (preg_match('/(ketua\s*rw|pengurus\s*rw|kontak\s*rw|nomor\s*rw|no\s*hp\s*rw|wa\s*rw|siapa\s*rw)/i', $lower)) {
                $rwHead = $extra['ketua_rw_user'] ?? null;
                $rwNum = $user->rt?->rw?->number ?? '-';
                $rwHeadName = $rwHead?->resident?->full_name ?? $rwHead?->name ?? 'Belum terdaftar';
                $rwHeadPhone = $rwHead?->phone_number ?? '-';
                $rwHeadEmail = $rwHead?->email ?? '-';

                return [
                    'message' => "🏛️ **Informasi Kontak Pengurus RW {$rwNum}:**\n\n".
                        "• **Ketua RW:** {$rwHeadName}\n".
                        "• **No. WA/HP:** {$rwHeadPhone}\n".
                        "• **Email:** {$rwHeadEmail}\n\n".
                        '_Silakan hubungi Ketua RW untuk koordinasi kegiatan atau permohonan yang memerlukan persetujuan tingkat RW._',
                    'options' => [
                        ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                    ],
                ];
            }
        }

        // ==================== ROLE RW HANDLERS ====================
        if ($role === 'RW') {
            $rwNum = $extra['rw_number'] ?? ($user->rw?->number ?? '-');
            $totalWargaRw = $extra['total_warga_rw'] ?? 0;
            $totalKkRw = $extra['total_kk_rw'] ?? 0;
            $totalRts = $extra['total_rts'] ?? 0;
            $rtsBreakdown = $extra['rts_breakdown'] ?? '';
            $rtHeadsList = $extra['rt_heads_list'] ?? '';

            // 0. Dashboard query for RW: "apa yang ada di dashboard", "data dashboard", "baca dashboard", etc.
            if (preg_match('/(?:apa\s+(?:yang\s+ada|saja\s+ada|ada|isi)|ada\s+apa(?:\s+saja)?|isi|data|ringkasan|statistik|rekap|tampilan|menu|informasi|status|baca|lihat|tampilkan|cek|keseluruhan|semua|detail)\s+(?:.*)?dashboard|dashboard\s+(?:ada\s+apa|isinya\s+apa|menampilkan\s+apa|itu\s+apa)|^dashboard$|baca\s+(?:keseluruhan|semua)\s+(?:dari\s+)?dashboard/i', $clean)) {
                $dashMsg = $this->getDashboardSummary($user);
                $sideMsg = $this->getSidebarMenuList($role);
                $updateMsg = $this->getRecentUpdatesSummary($user);

                $fullMsg = $dashMsg."\n\n".$sideMsg."\n\n".$updateMsg;

                return [
                    'message' => trim($fullMsg),
                    'options' => [
                        ['label' => 'Rincian per RT Binaan', 'icon' => 'search', 'next_node' => 'start'],
                        ['label' => 'Verifikasi Surat Masuk', 'icon' => 'document', 'next_node' => 'start'],
                        ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                    ],
                ];
            }

            // Specific RT demographic query within RW: "ada berapa laki-laki di rt 01", "berapa perempuan di rt 02", "berapa warga di rt 01"
            if (preg_match('/(?:ada\s+)?(?:berapa|jumlah|total)\s+(laki[\s\-]*laki|pria|perempuan|wanita|warga|penduduk|jiwa|kk|kartu\s*keluarga)\s+(?:yang\s+ada\s+)?(?:di\s*)?rt\s*0?(\d+)/i', $lower, $m) ||
                preg_match('/(?:di\s*)?rt\s*0?(\d+)\s+(?:ada\s+)?(?:berapa|jumlah|total)\s+(laki[\s\-]*laki|pria|perempuan|wanita|warga|penduduk|jiwa|kk|kartu\s*keluarga)/i', $lower, $mReverse)) {

                $targetGenderOrType = isset($m[1]) ? strtolower(trim($m[1])) : strtolower(trim($mReverse[2]));
                $rawRtNum = isset($m[2]) ? $m[2] : $mReverse[1];
                $formattedTarget = str_pad(ltrim($rawRtNum, '0'), 2, '0', STR_PAD_LEFT);

                $targetRt = Rt::where('rw_id', $user->rw_id)->where('number', $formattedTarget)->first();
                if ($targetRt) {
                    $totWarga = Resident::where('rt_id', $targetRt->id)->count();
                    $totPria = Resident::where('rt_id', $targetRt->id)->where(fn ($q) => $q->where('gender', 'LAKI_LAKI')->orWhere('gender', 'Laki-laki')->orWhere('gender', 'L'))->count();
                    $totWanita = Resident::where('rt_id', $targetRt->id)->where(fn ($q) => $q->where('gender', 'PEREMPUAN')->orWhere('gender', 'Perempuan')->orWhere('gender', 'P'))->count();
                    $totKk = Family::where('rt_id', $targetRt->id)->count();

                    if (str_contains($targetGenderOrType, 'laki') || str_contains($targetGenderOrType, 'pria')) {
                        $msg = "Di **RT {$targetRt->number}** lingkungan RW {$rwNum} Anda terdapat **{$totPria} orang laki-laki**.";
                    } elseif (str_contains($targetGenderOrType, 'perempuan') || str_contains($targetGenderOrType, 'wanita')) {
                        $msg = "Di **RT {$targetRt->number}** lingkungan RW {$rwNum} Anda terdapat **{$totWanita} orang perempuan**.";
                    } elseif (str_contains($targetGenderOrType, 'kk') || str_contains($targetGenderOrType, 'keluarga')) {
                        $msg = "Di **RT {$targetRt->number}** lingkungan RW {$rwNum} Anda terdapat **{$totKk} Kartu Keluarga (KK)**.";
                    } else {
                        $msg = "Di **RT {$targetRt->number}** lingkungan RW {$rwNum} Anda terdapat **{$totWarga} orang warga/jiwa**.";
                    }

                    return [
                        'message' => $msg,
                        'options' => [
                            ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                        ],
                    ];
                }

                return [
                    'message' => "Data untuk RT {$formattedTarget} tidak ditemukan dalam binaan RW {$rwNum} Anda.",
                    'options' => [
                        ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                    ],
                ];
            }

            // Single metric queries for entire RW
            if (preg_match('/(?:ada\s+)?(?:berapa|jumlah|total)\s+(laki[\s\-]*laki|pria)/i', $lower)) {
                $rtIds = Rt::where('rw_id', $user->rw_id)->pluck('id');
                $totPria = Resident::whereIn('rt_id', $rtIds)->where(fn ($q) => $q->where('gender', 'LAKI_LAKI')->orWhere('gender', 'Laki-laki')->orWhere('gender', 'L'))->count();

                return [
                    'message' => "Di lingkungan **RW {$rwNum}** Anda terdapat **{$totPria} orang laki-laki**.",
                    'options' => [['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start']],
                ];
            }

            if (preg_match('/(?:ada\s+)?(?:berapa|jumlah|total)\s+(perempuan|wanita)/i', $lower)) {
                $rtIds = Rt::where('rw_id', $user->rw_id)->pluck('id');
                $totWanita = Resident::whereIn('rt_id', $rtIds)->where(fn ($q) => $q->where('gender', 'PEREMPUAN')->orWhere('gender', 'Perempuan')->orWhere('gender', 'P'))->count();

                return [
                    'message' => "Di lingkungan **RW {$rwNum}** Anda terdapat **{$totWanita} orang perempuan**.",
                    'options' => [['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start']],
                ];
            }

            if (preg_match('/(?:ada\s+)?(?:berapa|jumlah|total)\s+(?:kk|kartu\s*keluarga)/i', $lower)) {
                return [
                    'message' => "Di lingkungan **RW {$rwNum}** Anda terdapat **{$totalKkRw} Kartu Keluarga (KK)**.",
                    'options' => [['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start']],
                ];
            }

            if (preg_match('/(?:ada\s+)?(?:berapa|jumlah|total)\s+(?:warga|penduduk|jiwa)/i', $lower)) {
                return [
                    'message' => "Total warga di lingkungan **RW {$rwNum}** Anda saat ini adalah **{$totalWargaRw} jiwa**.\n\nRincian per RT Binaan:\n".($rtsBreakdown ?: "• Belum ada data RT binaan.\n"),
                    'options' => [
                        ['label' => 'Rincian per RT Binaan', 'icon' => 'search', 'next_node' => 'start'],
                        ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                    ],
                ];
            }

            // Breakdown per RT
            if (preg_match('/(rincian\s*(rt|per\s*rt)|sebaran\s*rt|daftar\s*rt|per\s*rt)/i', $lower)) {
                return [
                    'message' => "📊 **Rincian Kependudukan per RT di Lingkungan RW {$rwNum}:**\n\n".
                        ($rtsBreakdown ?: "• Belum ada data RT binaan.\n"),
                    'options' => [
                        ['label' => 'Lihat Kontak Pengurus RT', 'icon' => 'phone', 'next_node' => 'start'],
                        ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                    ],
                ];
            }

            // Specific RT leader query in RW: "siapa ketua rt 01", "siapa nama rt 01", "kontak rt 01"
            if (preg_match('/(?:siapa|kontak|nomor|no\s*hp|wa)\s*(?:nama\s*)?(?:ketua\s*)?rt\s*0?(\d+)/i', $lower, $mRtHead) ||
                preg_match('/(?:di\s*)?rt\s*0?(\d+)\s+(?:siapa|kontak|nomor|no\s*hp|wa)\s*(?:nama\s*)?(?:ketua)?/i', $lower, $mRtHeadRev)) {
                $rawNum = isset($mRtHead[1]) ? $mRtHead[1] : $mRtHeadRev[1];
                $rtNumTarget = str_pad(ltrim($rawNum, '0'), 2, '0', STR_PAD_LEFT);
                $targetRt = Rt::where('rw_id', $user->rw_id)->where('number', $rtNumTarget)->first();
                if ($targetRt) {
                    $head = User::with('resident')->where('role', 'RT')->where('rt_id', $targetRt->id)->first();
                    $headName = $head ? ($head->resident?->full_name ?: ($head->name ?: $head->username)) : 'Belum terdaftar';
                    $phone = $head?->phone_number ? " (No. WA/HP: {$head->phone_number})" : '';

                    return [
                        'message' => "Ketua **RT {$targetRt->number}** di lingkungan RW {$rwNum} Anda adalah **{$headName}**{$phone}.",
                        'options' => [['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start']],
                    ];
                }

                return [
                    'message' => "Data RT {$rtNumTarget} tidak ditemukan dalam binaan RW {$rwNum} Anda.",
                    'options' => [['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start']],
                ];
            }

            // General RT leader list in RW: "siapa nama rt nya", "siapa saja ketua rt", "daftar ketua rt"
            if (preg_match('/(?:siapa|daftar|sebutkan)\s*(?:saja\s+)?(?:nama\s+)?(?:ketua\s*)?rt(?:\s*nya)?/i', $lower) ||
                preg_match('/nama\s*(?:ketua\s*)?rt(?:\s*nya)?/i', $lower)) {
                $rts = Rt::where('rw_id', $user->rw_id)->orderBy('number')->get();
                if ($rts->isNotEmpty()) {
                    $msg = "Berikut daftar Ketua RT di lingkungan **RW {$rwNum}** Anda:\n\n";
                    foreach ($rts as $idx => $rt) {
                        $num = $idx + 1;
                        $head = User::with('resident')->where('role', 'RT')->where('rt_id', $rt->id)->first();
                        $headName = $head ? ($head->resident?->full_name ?: ($head->name ?: $head->username)) : 'Belum terdaftar';
                        $phone = $head?->phone_number ? " — {$head->phone_number}" : '';
                        $msg .= "{$num}. **RT {$rt->number}**: **{$headName}**{$phone}\n";
                    }

                    return [
                        'message' => trim($msg),
                        'options' => [['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start']],
                    ];
                }

                return [
                    'message' => "Belum ada data RT terdaftar di lingkungan RW {$rwNum} Anda.",
                    'options' => [['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start']],
                ];
            }

            // RT contacts / NIK / pengurus RT
            if (preg_match('/(nik|kontak|nomor|no\s*hp|wa|telepon|siapa\s*pengurus|pengurus)\s*(pengurus)?\s*rt/i', $lower) || preg_match('/siapa\s+(?:saja\s+)?pengurus(nya)?/i', $lower)) {
                return [
                    'message' => "Berikut adalah daftar pengurus RT beserta NIK dan kontak nomor HP di lingkungan **RW {$rwNum}** Anda:\n\n".
                        ($rtHeadsList ?: "• Belum ada pengurus RT terdaftar.\n"),
                    'options' => [
                        ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                    ],
                ];
            }

            // Document requests under RW
            if (preg_match('/(surat|pengajuan\s*surat|persuratan|verifikasi)/i', $lower)) {
                $rwDocsList = $extra['rw_docs_list'] ?? '';
                $pDocs = $extra['pending_docs'] ?? 0;

                return [
                    'message' => "📄 **Pengajuan Surat Masuk ke RW {$rwNum} (Menunggu Verifikasi: {$pDocs}):**\n\n".
                        ($rwDocsList ?: "• Belum ada surat yang memerlukan verifikasi RW saat ini.\n"),
                    'options' => [
                        ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                    ],
                ];
            }

            // Complaints under RW
            if (preg_match('/(pengaduan|keluhan|laporan)/i', $lower)) {
                $rwCompList = $extra['rw_complaints_list'] ?? '';
                $pComp = $extra['pending_complaints'] ?? 0;

                return [
                    'message' => "📢 **Laporan Pengaduan di Lingkungan RW {$rwNum} (Pending: {$pComp}):**\n\n".
                        ($rwCompList ?: "• Belum ada pengaduan warga di RW {$rwNum}.\n"),
                    'options' => [
                        ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                    ],
                ];
            }

            // Proportional list of resident names in RW: "siapa saja warganya", "daftar warga", "nama nama warga", "sebutkan warganya"
            if (preg_match('/(?:siapa\s+saja\s+(?:nama\s+)?warga|daftar\s+warga|nama\s+nama\s+warga|sebutkan\s+(?:nama\s+)?warga|warga\s+siapa\s+saja)/i', $lower)) {
                $rtIds = Rt::where('rw_id', $user->rw_id)->pluck('id');
                $residents = Resident::with('rt')->whereIn('rt_id', $rtIds)->orderBy('full_name')->get();
                $msg = "📋 **Daftar Nama Warga di Lingkungan RW {$rwNum} Anda:**\n\n";
                if ($residents->isEmpty()) {
                    $msg .= "Belum ada warga terdaftar di lingkungan RW {$rwNum}.";
                } else {
                    foreach ($residents as $idx => $r) {
                        $num = $idx + 1;
                        $rtStr = 'RT '.($r->rt?->number ?? '-');
                        $msg .= "{$num}. **{$r->full_name}** ({$rtStr})\n";
                    }
                }

                return [
                    'message' => trim($msg),
                    'options' => [['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start']],
                ];
            }

            // Specific resident personal data lookup in RW: "cari warga [nama]", "data lengkap [nama]", "biodata [nama]"
            if (preg_match('/(?:cari\s+warga(?:\s+bernama|\s+nama)?|data\s+(?:lengkap\s+|diri\s+)?warga(?:\s+bernama|\s+nama)?|biodata\s+warga(?:\s+bernama|\s+nama)?|warga\s+(?:bernama|nama))\s+([a-zA-Z0-9\s]+)/i', $lower, $m)) {
                $searchName = trim($m[1]);
                $rtIds = Rt::where('rw_id', $user->rw_id)->pluck('id');
                $matched = Resident::with(['user', 'family', 'rt.rw.village'])
                    ->whereIn('rt_id', $rtIds)
                    ->where(function ($q) use ($searchName) {
                        $q->where('nik', $searchName)->orWhere('full_name', 'like', "%{$searchName}%");
                    })
                    ->first();

                if ($matched) {
                    $hp = $matched->user?->phone_number ?? 'Belum ada No HP';
                    $addr = $matched->user?->address ?: ($matched->family?->address ?? '-');
                    $kkNum = $matched->family?->kk_number ?? '-';
                    $tgl = $matched->birth_date ? $matched->birth_date->format('d-m-Y') : '-';
                    $ttl = ($matched->birth_place && $tgl !== '-') ? "{$matched->birth_place}, {$tgl}" : ($matched->birth_place ?: $tgl);
                    $agama = $matched->religion ? ucfirst(strtolower($matched->religion)) : '-';
                    $gender = match (strtoupper($matched->gender ?? '')) {
                        'LAKI_LAKI', 'L', 'MALE' => 'Laki-laki',
                        'PEREMPUAN', 'P', 'FEMALE' => 'Perempuan',
                        default => $matched->gender ?? '-',
                    };
                    $rtNumRes = $matched->rt?->number ?? '-';

                    $msg = "👤 **Data Lengkap Warga - {$matched->full_name} (RT {$rtNumRes} / RW {$rwNum}):**\n\n".
                        "• **Nama Lengkap:** {$matched->full_name}\n".
                        "• **NIK:** `{$matched->nik}`\n".
                        "• **No. KK:** {$kkNum}\n".
                        "• **Jenis Kelamin:** {$gender}\n".
                        "• **TTL:** {$ttl}\n".
                        "• **Agama:** {$agama}\n".
                        "• **Pekerjaan:** {$matched->occupation}\n".
                        "• **Alamat:** {$addr}\n".
                        "• **No. HP/WA:** {$hp}";

                    return [
                        'message' => trim($msg),
                        'options' => [['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start']],
                    ];
                }

                // Check if citizen is in another RW
                $outsideRes = Resident::with('rt.rw')->where(function ($q) use ($searchName) {
                    $q->where('nik', $searchName)->orWhere('full_name', 'like', "%{$searchName}%");
                })->first();

                if ($outsideRes) {
                    $outRwNum = $outsideRes->rt?->rw?->number ?? '-';

                    return [
                        'message' => "Warga **{$outsideRes->full_name}** terdaftar di RW {$outRwNum} (di luar RW {$rwNum} Anda). Sesuai batas kewenangan wilayah dan UU PDP, akun RW hanya dapat mengakses data warga binaan RW Anda sendiri.",
                        'options' => [['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start']],
                    ];
                }

                return [
                    'message' => "Data warga dengan kata kunci \"{$searchName}\" tidak ditemukan di lingkungan RW {$rwNum} Anda.",
                    'options' => [['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start']],
                ];
            }

            // Village Head query for RW: "siapa kepala desa", "kades", "kontak desa"
            if (preg_match('/(?:siapa|kontak|nomor|no\s*hp|wa)\s*(?:kepala\s*desa|kades|admin\s*desa|kantor\s*desa)/i', $lower)) {
                $desaAdmin = User::whereIn('role', ['ADMIN_DESA', 'DESA'])->first();
                $vName = $user->rw?->village?->name ?? 'Desa';
                $name = $desaAdmin ? $desaAdmin->name : 'Kantor Desa';
                $phone = $desaAdmin?->phone_number ? " (No. WA/HP: {$desaAdmin->phone_number})" : '';

                return [
                    'message' => "Kepala / Admin {$vName} adalah **{$name}**{$phone}.",
                    'options' => [['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start']],
                ];
            }
        }

        // ==================== ROLE SUPER_ADMIN HANDLERS ====================
        if ($role === 'SUPER_ADMIN') {
            $sys = $extra['system_info'] ?? [];
            $totalVillages = $extra['total_villages'] ?? Village::count();
            $totalRws = $extra['total_rws'] ?? Rw::count();
            $totalRts = $extra['total_rts'] ?? Rt::count();
            $totalResidents = $extra['total_residents'] ?? Resident::count();
            $totalFamilies = $extra['total_families'] ?? Family::count();
            $totalUsers = $extra['total_users'] ?? User::count();
            $totalDocs = $extra['total_docs'] ?? DocumentRequest::count();
            $totalComplaints = $extra['total_complaints'] ?? Complaint::count();
            $usersSummary = $extra['users_summary'] ?? '';
            $auditText = $extra['audit_text'] ?? '';

            // 0. Dashboard overview query: "apa yang ada di dashboard", "ada apa saja di dashboard", "data dashboard", "isi dashboard", etc.
            if (preg_match('/(?:apa\s+(?:yang\s+ada|saja\s+ada|ada|isi)|ada\s+apa(?:\s+saja)?|isi|data|ringkasan|statistik|rekap|tampilan|menu|informasi|status|baca|lihat|tampilkan|cek|keseluruhan|semua|detail)\s+(?:.*)?dashboard|dashboard\s+(?:ada\s+apa|isinya\s+apa|menampilkan\s+apa|itu\s+apa)|^dashboard$|baca\s+(?:keseluruhan|semua)\s+(?:dari\s+)?dashboard/i', $clean)) {
                $totVillages = $extra['total_villages'] ?? Village::count();
                $totRws = $extra['total_rws'] ?? Rw::count();
                $totRts = $extra['total_rts'] ?? Rt::count();
                $totResidents = $extra['total_residents'] ?? Resident::count();
                $totMale = Resident::where(fn ($q) => $q->where('gender', 'LAKI_LAKI')->orWhere('gender', 'Laki-laki')->orWhere('gender', 'L'))->count();
                $totFemale = Resident::where(fn ($q) => $q->where('gender', 'PEREMPUAN')->orWhere('gender', 'Perempuan')->orWhere('gender', 'P'))->count();
                $totFamilies = $extra['total_families'] ?? Family::count();
                $totUsers = $extra['total_users'] ?? User::count();
                $totComplaints = $extra['total_complaints'] ?? Complaint::count();
                $activeEmergencies = Emergency::where('status', 'ACTIVE')->count();
                $recentResidents = Resident::with(['rt.rw.village'])->latest()->take(5)->get();

                $recentResidentsText = '';
                if ($recentResidents->isNotEmpty()) {
                    foreach ($recentResidents as $idx => $r) {
                        $num = $idx + 1;
                        $rtNum = $r->rt?->number ?? '-';
                        $rwNum = $r->rt?->rw?->number ?? '-';
                        $vName = $r->rt?->rw?->village?->name ?? '-';
                        $recentResidentsText .= "  {$num}. **{$r->full_name}** (RT {$rtNum}/RW {$rwNum}, Desa {$vName})\n";
                    }
                } else {
                    $recentResidentsText = "  _Belum ada data warga terdaftar._\n";
                }

                $sosStatus = $activeEmergencies > 0
                    ? "🚨 Terdapat **{$activeEmergencies} panggilan darurat (SOS) aktif** yang memerlukan perhatian!"
                    : '✅ Situasi aman, tidak ada sinyal darurat (SOS) aktif.';

                $msg = "📊 **Data & Informasi pada Dashboard Super Admin:**\n\n".
                    "Halaman Dashboard Super Admin menampilkan ringkasan data real-time dari database sebagai berikut:\n\n".
                    "1. **Statistik Utama Wilayah & Kependudukan:**\n".
                    "• Total Desa/Kelurahan: **{$totVillages} Desa**\n".
                    "• Total Rukun Warga: **{$totRws} RW**\n".
                    "• Total Rukun Tetangga: **{$totRts} RT**\n".
                    "• Total Penduduk: **{$totResidents} Jiwa** ({$totMale} Laki-laki, {$totFemale} Perempuan)\n".
                    "• Total Kepala Keluarga: **{$totFamilies} KK**\n".
                    "• Total Pengguna Sistem: **{$totUsers} Akun**\n".
                    "• Total Laporan Pengaduan: **{$totComplaints} Laporan**\n\n".
                    "2. **Status Panggilan Darurat (SOS):**\n".
                    "• {$sosStatus}\n\n".
                    "3. **Pendaftaran Warga Terbaru:**\n".
                    $recentResidentsText."\n".
                    "4. **Struktur Wilayah & Modul Layanan:**\n".
                    "• Pohon struktur kewilayahan (Desa Sukamaju ➔ RW ➔ RT beserta total warga & KK)\n".
                    '• Akses cepat: Data Kependudukan Terpadu, Layanan Pengajuan Surat, Manajemen Pengaduan, Musyawarah & Polling, Kelola Pengguna, dan Audit Log.';

                $dashMsg = trim($msg);
                if (preg_match('/(?:sidebar|keseluruhan|semua|update|pembaruan)/i', $clean)) {
                    $sideMsg = $this->getSidebarMenuList($role);
                    $updateMsg = $this->getRecentUpdatesSummary($user);
                    $dashMsg .= "\n\n".$sideMsg."\n\n".$updateMsg;
                }

                return [
                    'message' => $dashMsg,
                    'options' => [
                        ['label' => 'Ringkasan Database', 'icon' => 'document', 'next_node' => 'db_overview'],
                        ['label' => 'Status Sistem & Server', 'icon' => 'search', 'next_node' => 'system_info'],
                        ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                    ],
                ];
            }

            // 1. Specific query: count users by role (e.g. "ada berapa role dengan role warga", "ada berapa user dengan role rt", "berapa user role desa", "user role superadmin")
            if (preg_match('/(?:berapa|jumlah|total)?\s*(?:user|pengguna|akun|role)?\s*(?:dengan\s+role|role)\s+([a-zA-Z_\s]+)/i', $lower, $m) ||
                preg_match('/(?:user|pengguna|akun)\s+(?:dengan\s+role|role)\s+([a-zA-Z_\s]+)/i', $lower, $m) ||
                preg_match('/(?:berapa|jumlah|total)\s+(?:user|pengguna|akun)\s+(warga|rt|rw|desa|kelurahan|superadmin|admin)/i', $lower, $m)) {
                $roleInput = strtolower(trim($m[1]));
                $targetRole = null;
                $roleLabel = '';

                if (str_contains($roleInput, 'warga')) {
                    $targetRole = 'WARGA';
                    $roleLabel = 'Warga';
                } elseif (str_contains($roleInput, 'rt')) {
                    $targetRole = 'RT';
                    $roleLabel = 'Pengurus RT';
                } elseif (str_contains($roleInput, 'rw')) {
                    $targetRole = 'RW';
                    $roleLabel = 'Pengurus RW';
                } elseif (str_contains($roleInput, 'desa') || str_contains($roleInput, 'kelurahan')) {
                    $targetRole = ['ADMIN_DESA', 'DESA'];
                    $roleLabel = 'Perangkat / Admin Desa';
                } elseif (str_contains($roleInput, 'super')) {
                    $targetRole = 'SUPER_ADMIN';
                    $roleLabel = 'Super Administrator';
                }

                if ($targetRole) {
                    $count = is_array($targetRole)
                        ? User::whereIn('role', $targetRole)->count()
                        : User::where('role', $targetRole)->count();

                    return [
                        'message' => "👥 Terdapat **{$count} user** terdaftar dengan role **{$roleLabel}** di sistem SIWIL.",
                        'options' => [
                            ['label' => 'Total Semua User', 'icon' => 'search', 'next_node' => 'db_overview'],
                            ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                        ],
                    ];
                }
            }

            // 2. Total users query: "ada berapa total user yang ada", "berapa total user", "berapa user"
            if (preg_match('/(?:berapa|jumlah|total)\s+(?:total\s+)?(?:user|pengguna|akun)(?:\s+(?:yang\s+ada|terdaftar))?/i', $lower)) {
                $usersByRole = User::selectRaw('role, count(*) as count')->groupBy('role')->pluck('count', 'role')->toArray();
                $wargaCount = $usersByRole['WARGA'] ?? 0;
                $rtCount = $usersByRole['RT'] ?? 0;
                $rwCount = $usersByRole['RW'] ?? 0;
                $desaCount = ($usersByRole['ADMIN_DESA'] ?? 0) + ($usersByRole['DESA'] ?? 0);
                $saCount = $usersByRole['SUPER_ADMIN'] ?? 0;

                return [
                    'message' => "👥 **Statistik Pengguna Sistem SIWIL (Super Admin):**\n\n".
                        "Total keseluruhan user terdaftar saat ini adalah **{$totalUsers} akun**.\n\n".
                        "**Rincian Akun Berdasarkan Role:**\n".
                        "• **Warga:** {$wargaCount} akun\n".
                        "• **Pengurus RT:** {$rtCount} akun\n".
                        "• **Pengurus RW:** {$rwCount} akun\n".
                        "• **Perangkat / Admin Desa:** {$desaCount} akun\n".
                        "• **Super Admin:** {$saCount} akun",
                    'options' => [
                        ['label' => 'Ringkasan Database', 'icon' => 'document', 'next_node' => 'db_overview'],
                        ['label' => 'Status Sistem & Server', 'icon' => 'search', 'next_node' => 'system_info'],
                        ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                    ],
                ];
            }

            // 3. Specific RT demographic query: "ada berapa perempuan di rt 01", "berapa laki-laki di rt 01", "ada berapa kk di rt 01", "berapa warga di rt 01"
            if (preg_match('/(?:ada\s+)?(?:berapa|jumlah|total)\s+(laki[\s\-]*laki|pria|perempuan|wanita|warga|penduduk|jiwa|kk|kartu\s*keluarga)\s+(?:yang\s+ada\s+)?(?:di\s*)?rt\s*0?(\d+)/i', $lower, $m) ||
                preg_match('/(?:di\s*)?rt\s*0?(\d+)\s+(?:ada\s+)?(?:berapa|jumlah|total)\s+(laki[\s\-]*laki|pria|perempuan|wanita|warga|penduduk|jiwa|kk|kartu\s*keluarga)/i', $lower, $mReverse)) {

                $targetGenderOrType = isset($m[1]) ? strtolower(trim($m[1])) : strtolower(trim($mReverse[2]));
                $rawRtNum = isset($m[2]) ? $m[2] : $mReverse[1];
                $formattedTarget = str_pad(ltrim($rawRtNum, '0'), 2, '0', STR_PAD_LEFT);

                $targetRts = Rt::with('rw.village')->where('number', $formattedTarget)->get();
                if ($targetRts->isNotEmpty()) {
                    $responses = [];
                    foreach ($targetRts as $trt) {
                        $vName = $trt->rw?->village?->name ?? '-';
                        $rwNum = $trt->rw?->number ?? '-';
                        $locLabel = $targetRts->count() > 1 ? "RT {$trt->number} (RW {$rwNum}, Desa {$vName})" : "RT {$trt->number}";

                        if (str_contains($targetGenderOrType, 'perempuan') || str_contains($targetGenderOrType, 'wanita')) {
                            $totWanita = Resident::where('rt_id', $trt->id)->where(fn ($q) => $q->where('gender', 'PEREMPUAN')->orWhere('gender', 'Perempuan')->orWhere('gender', 'P'))->count();
                            $responses[] = "Di {$locLabel} terdapat **{$totWanita} orang perempuan**.";
                        } elseif (str_contains($targetGenderOrType, 'laki') || str_contains($targetGenderOrType, 'pria')) {
                            $totPria = Resident::where('rt_id', $trt->id)->where(fn ($q) => $q->where('gender', 'LAKI_LAKI')->orWhere('gender', 'Laki-laki')->orWhere('gender', 'L'))->count();
                            $responses[] = "Di {$locLabel} terdapat **{$totPria} orang laki-laki**.";
                        } elseif (str_contains($targetGenderOrType, 'kk') || str_contains($targetGenderOrType, 'keluarga')) {
                            $totKk = Family::where('rt_id', $trt->id)->count();
                            $responses[] = "Di {$locLabel} terdapat **{$totKk} Kartu Keluarga (KK)**.";
                        } else {
                            $totWarga = Resident::where('rt_id', $trt->id)->count();
                            $responses[] = "Di {$locLabel} terdapat **{$totWarga} orang warga/jiwa**.";
                        }
                    }

                    return [
                        'message' => implode("\n", $responses),
                        'options' => [
                            ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                        ],
                    ];
                }

                return [
                    'message' => "Data untuk RT {$formattedTarget} tidak ditemukan di database.",
                    'options' => [
                        ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                    ],
                ];
            }

            // 4. Specific RW demographic query: "ada berapa perempuan di rw 01", "berapa warga di rw 01", etc.
            if (preg_match('/(?:ada\s+)?(?:berapa|jumlah|total)\s+(laki[\s\-]*laki|pria|perempuan|wanita|warga|penduduk|jiwa|kk|kartu\s*keluarga)\s+(?:yang\s+ada\s+)?(?:di\s*)?rw\s*0?(\d+)/i', $lower, $mRw) ||
                preg_match('/(?:di\s*)?rw\s*0?(\d+)\s+(?:ada\s+)?(?:berapa|jumlah|total)\s+(laki[\s\-]*laki|pria|perempuan|wanita|warga|penduduk|jiwa|kk|kartu\s*keluarga)/i', $lower, $mRwRev)) {

                $targetGenderOrType = isset($mRw[1]) ? strtolower(trim($mRw[1])) : strtolower(trim($mRwRev[2]));
                $rawRwNum = isset($mRw[2]) ? $mRw[2] : $mRwRev[1];
                $formattedRwTarget = str_pad(ltrim($rawRwNum, '0'), 2, '0', STR_PAD_LEFT);

                $targetRws = Rw::with('village')->where('number', $formattedRwTarget)->get();
                if ($targetRws->isNotEmpty()) {
                    $responses = [];
                    foreach ($targetRws as $trw) {
                        $vName = $trw->village?->name ?? '-';
                        $locLabel = $targetRws->count() > 1 ? "RW {$trw->number} (Desa {$vName})" : "RW {$trw->number}";
                        $rtIds = Rt::where('rw_id', $trw->id)->pluck('id');

                        if (str_contains($targetGenderOrType, 'perempuan') || str_contains($targetGenderOrType, 'wanita')) {
                            $totWanita = Resident::whereIn('rt_id', $rtIds)->where(fn ($q) => $q->where('gender', 'PEREMPUAN')->orWhere('gender', 'Perempuan')->orWhere('gender', 'P'))->count();
                            $responses[] = "Di {$locLabel} terdapat **{$totWanita} orang perempuan**.";
                        } elseif (str_contains($targetGenderOrType, 'laki') || str_contains($targetGenderOrType, 'pria')) {
                            $totPria = Resident::whereIn('rt_id', $rtIds)->where(fn ($q) => $q->where('gender', 'LAKI_LAKI')->orWhere('gender', 'Laki-laki')->orWhere('gender', 'L'))->count();
                            $responses[] = "Di {$locLabel} terdapat **{$totPria} orang laki-laki**.";
                        } elseif (str_contains($targetGenderOrType, 'kk') || str_contains($targetGenderOrType, 'keluarga')) {
                            $totKk = Family::whereIn('rt_id', $rtIds)->count();
                            $responses[] = "Di {$locLabel} terdapat **{$totKk} Kartu Keluarga (KK)**.";
                        } else {
                            $totWarga = Resident::whereIn('rt_id', $rtIds)->count();
                            $responses[] = "Di {$locLabel} terdapat **{$totWarga} orang warga/jiwa**.";
                        }
                    }

                    return [
                        'message' => implode("\n", $responses),
                        'options' => [
                            ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                        ],
                    ];
                }

                return [
                    'message' => "Data untuk RW {$formattedRwTarget} tidak ditemukan di database.",
                    'options' => [
                        ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                    ],
                ];
            }

            // 5. Full Demographics / Rincian RT query ONLY if user explicitly asks for rincian/demografi: "demografi rt 01", "rincian rt 01"
            if (preg_match('/(?:demografi|rincian|sebaran)\s+(?:di\s*)?rt\s*0?(\d+)/i', $lower, $mDem) ||
                preg_match('/(?:di\s*)?rt\s*0?(\d+)\s+(?:demografi|rincian|sebaran)/i', $lower, $mDemRev)) {
                $rawRtNum = isset($mDem[1]) ? $mDem[1] : $mDemRev[1];
                $formattedTarget = str_pad(ltrim($rawRtNum, '0'), 2, '0', STR_PAD_LEFT);

                $targetRts = Rt::with('rw.village')->where('number', $formattedTarget)->get();
                if ($targetRts->isNotEmpty()) {
                    $responses = [];
                    foreach ($targetRts as $trt) {
                        $totWarga = Resident::where('rt_id', $trt->id)->count();
                        $totPria = Resident::where('rt_id', $trt->id)->where(fn ($q) => $q->where('gender', 'LAKI_LAKI')->orWhere('gender', 'Laki-laki')->orWhere('gender', 'L'))->count();
                        $totWanita = Resident::where('rt_id', $trt->id)->where(fn ($q) => $q->where('gender', 'PEREMPUAN')->orWhere('gender', 'Perempuan')->orWhere('gender', 'P'))->count();
                        $totKk = Family::where('rt_id', $trt->id)->count();
                        $vName = $trt->rw?->village?->name ?? '-';
                        $rwNum = $trt->rw?->number ?? '-';

                        $responses[] = "• **RT {$trt->number} (RW {$rwNum}, Desa {$vName})**:\n".
                            "  - Total Penduduk: **{$totWarga} jiwa**\n".
                            "  - Laki-laki: **{$totPria} orang**\n".
                            "  - Perempuan: **{$totWanita} orang**\n".
                            "  - Kartu Keluarga (KK): **{$totKk} KK**";
                    }

                    return [
                        'message' => "Rincian data kependudukan RT {$formattedTarget}:\n\n".implode("\n\n", $responses),
                        'options' => [
                            ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                        ],
                    ];
                }
            }

            // 6. Specific system-wide counts: "ada berapa warga", "berapa kk", "berapa desa", "berapa rw", "berapa rt" (without RT/RW specified)
            if (! preg_match('/(?:di\s*)?(?:rt|rw)\s*0?\d+/i', $lower)) {
                if (preg_match('/(?:rekap|rekapitulasi|ringkasan|ikhtisar)\s+(?:wilayah|kependudukan|data)/i', $lower) || (preg_match('/(?:berapa|jumlah)\s+desa/i', $lower) && preg_match('/(?:berapa|jumlah)\s+(?:rw|rt|warga)/i', $lower))) {
                    return [
                        'message' => "📊 **Rekap Data Wilayah & Kependudukan SIWIL:**\n\n".
                            "• **Total Desa/Kelurahan:** {$totalVillages} desa\n".
                            "• **Total RW:** {$totalRws} RW\n".
                            "• **Total RT:** {$totalRts} RT\n".
                            "• **Total Warga:** {$totalResidents} jiwa terdaftar\n".
                            "• **Total Kartu Keluarga (KK):** {$totalFamilies} KK",
                        'options' => [
                            ['label' => 'Ringkasan Database', 'icon' => 'document', 'next_node' => 'db_overview'],
                            ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                        ],
                    ];
                }

                if (preg_match('/(?:ada\s+)?(?:berapa|jumlah|total)\s+(?:total\s+)?(?:desa|kelurahan)/i', $lower)) {
                    return [
                        'message' => "Total desa/kelurahan di sistem SIWIL adalah **{$totalVillages} desa**.",
                        'options' => [['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start']],
                    ];
                }
                if (preg_match('/(?:ada\s+)?(?:berapa|jumlah|total)\s+(?:total\s+)?rw(?:\b|\s)/i', $lower)) {
                    return [
                        'message' => "Total RW di sistem SIWIL adalah **{$totalRws} RW**.",
                        'options' => [['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start']],
                    ];
                }
                if (preg_match('/(?:ada\s+)?(?:berapa|jumlah|total)\s+(?:total\s+)?rt(?:\b|\s)/i', $lower)) {
                    return [
                        'message' => "Total RT di sistem SIWIL adalah **{$totalRts} RT**.",
                        'options' => [['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start']],
                    ];
                }
                if ((preg_match('/(?:ada\s+)?(?:berapa|jumlah|total)\s+(?:total\s+)?(?:warga|penduduk|jiwa)(?:\s+(?:terdaftar|di\s+sistem|keseluruhan|semua))?$/i', trim($lower)) || preg_match('/(?:ada\s+)?(?:berapa|jumlah|total)\s+(?:total\s+)?(?:warga|penduduk|jiwa)/i', $lower)) && ! preg_match('/(?:akun|user|pengguna|tidak|belum|tanpa)/i', $lower)) {
                    return [
                        'message' => "Total warga terdaftar di sistem SIWIL adalah **{$totalResidents} jiwa**.",
                        'options' => [['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start']],
                    ];
                }
                if (preg_match('/(?:ada\s+)?(?:berapa|jumlah|total)\s+(?:total\s+)?(?:kk|kartu\s*keluarga)/i', $lower)) {
                    return [
                        'message' => "Total Kartu Keluarga (KK) terdaftar di sistem SIWIL adalah **{$totalFamilies} KK**.",
                        'options' => [['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start']],
                    ];
                }
            }

            // Specific RT leader / official query: "siapa ketua rt 01", "siapa nama rt 01", "kontak rt 01"
            if (preg_match('/(?:siapa|kontak|nomor|no\s*hp|wa)\s*(?:nama\s*)?(?:ketua\s*)?rt\s*0?(\d+)/i', $lower, $mRtHead) ||
                preg_match('/(?:di\s*)?rt\s*0?(\d+)\s+(?:siapa|kontak|nomor|no\s*hp|wa)\s*(?:nama\s*)?(?:ketua)?/i', $lower, $mRtHeadRev)) {
                $rawNum = isset($mRtHead[1]) ? $mRtHead[1] : $mRtHeadRev[1];
                $rtNum = str_pad(ltrim($rawNum, '0'), 2, '0', STR_PAD_LEFT);
                $targetRts = Rt::with(['rw.village'])->where('number', $rtNum)->get();
                if ($targetRts->isNotEmpty()) {
                    $responses = [];
                    foreach ($targetRts as $trt) {
                        $vName = $trt->rw?->village?->name ?? '-';
                        $rwNum = $trt->rw?->number ?? '-';
                        $locLabel = $targetRts->count() > 1 ? "RT {$trt->number} (RW {$rwNum}, Desa {$vName})" : "RT {$trt->number}";
                        $head = User::with('resident')->where('role', 'RT')->where('rt_id', $trt->id)->first();
                        $headName = $head ? ($head->resident?->full_name ?: ($head->name ?: $head->username)) : 'Belum terdaftar';
                        $responses[] = "Ketua {$locLabel} adalah **{$headName}**.";
                    }

                    return [
                        'message' => implode("\n", $responses),
                        'options' => [
                            ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                        ],
                    ];
                }

                return [
                    'message' => "Data RT {$rtNum} tidak ditemukan di database.",
                    'options' => [['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start']],
                ];
            }

            // General RT leader list: "siapa nama rt nya", "siapa ketua rt nya", "siapa saja ketua rt", "siapa nama ketua rt", "daftar ketua rt", "nama nama rt"
            if (preg_match('/(?:siapa|daftar|sebutkan)\s*(?:saja\s+)?(?:nama\s+)?(?:ketua\s*)?rt(?:\s*nya)?/i', $lower) ||
                preg_match('/nama\s*(?:ketua\s*)?rt(?:\s*nya)?/i', $lower)) {
                $rts = Rt::with(['rw.village'])->orderBy('number')->get();
                if ($rts->isNotEmpty()) {
                    $msg = "Berikut daftar Ketua RT yang terdaftar di database:\n\n";
                    foreach ($rts as $idx => $rt) {
                        $num = $idx + 1;
                        $head = User::with('resident')->where('role', 'RT')->where('rt_id', $rt->id)->first();
                        $headName = $head ? ($head->resident?->full_name ?: ($head->name ?: $head->username)) : 'Belum terdaftar';
                        $rwNum = $rt->rw?->number ?? '-';
                        $vName = $rt->rw?->village?->name ?? '-';
                        $msg .= "{$num}. **RT {$rt->number}** (RW {$rwNum}, Desa {$vName}): **{$headName}**\n";
                    }

                    return [
                        'message' => trim($msg),
                        'options' => [
                            ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                        ],
                    ];
                }

                return [
                    'message' => 'Belum ada data RT yang terdaftar di database.',
                    'options' => [['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start']],
                ];
            }

            // Specific RW leader query: "siapa ketua rw 01", "siapa nama rw 01", "kontak rw 01"
            if (preg_match('/(?:siapa|kontak|nomor|no\s*hp|wa)\s*(?:nama\s*)?(?:ketua\s*)?rw\s*0?(\d+)/i', $lower, $mRwHead) ||
                preg_match('/(?:di\s*)?rw\s*0?(\d+)\s+(?:siapa|kontak|nomor|no\s*hp|wa)\s*(?:nama\s*)?(?:ketua)?/i', $lower, $mRwHeadRev)) {
                $rawRwNum = isset($mRwHead[1]) ? $mRwHead[1] : $mRwHeadRev[1];
                $rwNum = str_pad(ltrim($rawRwNum, '0'), 2, '0', STR_PAD_LEFT);
                $targetRws = Rw::with('village')->where('number', $rwNum)->get();
                if ($targetRws->isNotEmpty()) {
                    $responses = [];
                    foreach ($targetRws as $trw) {
                        $vName = $trw->village?->name ?? '-';
                        $locLabel = $targetRws->count() > 1 ? "RW {$trw->number} (Desa {$vName})" : "RW {$trw->number}";
                        $head = User::with('resident')->where('role', 'RW')->where('rw_id', $trw->id)->first();
                        $headName = $head ? ($head->resident?->full_name ?: ($head->name ?: $head->username)) : 'Belum terdaftar';
                        $responses[] = "Ketua {$locLabel} adalah **{$headName}**.";
                    }

                    return [
                        'message' => implode("\n", $responses),
                        'options' => [
                            ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                        ],
                    ];
                }

                return [
                    'message' => "Data RW {$rwNum} tidak ditemukan di database.",
                    'options' => [['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start']],
                ];
            }

            // General RW leader list: "siapa nama rw nya", "siapa ketua rw nya", "siapa saja ketua rw", "daftar ketua rw"
            if (preg_match('/(?:siapa|daftar|sebutkan)\s*(?:saja\s+)?(?:nama\s+)?(?:ketua\s*)?rw(?:\s*nya)?/i', $lower) ||
                preg_match('/nama\s*(?:ketua\s*)?rw(?:\s*nya)?/i', $lower)) {
                $rws = Rw::with(['village'])->orderBy('number')->get();
                if ($rws->isNotEmpty()) {
                    $msg = "Berikut daftar Ketua RW yang terdaftar di database:\n\n";
                    foreach ($rws as $idx => $rw) {
                        $num = $idx + 1;
                        $head = User::with('resident')->where('role', 'RW')->where('rw_id', $rw->id)->first();
                        $headName = $head ? ($head->resident?->full_name ?: ($head->name ?: $head->username)) : 'Belum terdaftar';
                        $vName = $rw->village?->name ?? '-';
                        $msg .= "{$num}. **RW {$rw->number}** (Desa {$vName}): **{$headName}**\n";
                    }

                    return [
                        'message' => trim($msg),
                        'options' => [
                            ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                        ],
                    ];
                }

                return [
                    'message' => 'Belum ada data RW yang terdaftar di database.',
                    'options' => [['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start']],
                ];
            }

            if (preg_match('/(?:siapa|kontak|nomor|no\s*hp|wa)\s*(?:kepala\s*desa|kades|admin\s*desa)/i', $lower)) {
                $desaAdmins = User::whereIn('role', ['ADMIN_DESA', 'DESA'])->with('village')->get();
                if ($desaAdmins->isNotEmpty()) {
                    $responses = [];
                    foreach ($desaAdmins as $da) {
                        $vName = $da->village?->name ?? 'Desa';
                        $responses[] = "Kepala / Admin {$vName} adalah **{$da->name}**.";
                    }

                    return [
                        'message' => implode("\n", $responses),
                        'options' => [['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start']],
                    ];
                }
            }

            // 7. Resident personal data lookup across all database: "data lengkap [nama/nik]", "biodata [nama/nik]", "data warga [nama/nik]"
            if (preg_match('/(?:data\s*(?:lengkap|diri)?|biodata|profil|cari\s*warga)\s*(?:warga)?\s*(?:bernama|nama|nik|dengan\s*nik)?\s+([a-zA-Z0-9\s]+)/i', $lower, $m)) {
                $searchQuery = trim($m[1]);
                if (! in_array(strtolower($searchQuery), ['saya', 'ku', 'pribadi', 'akun', 'warga', 'dashboard', 'sistem', 'database', 'wilayah', 'kependudukan', 'desa', 'rt', 'rw'])) {
                    $matchedResident = Resident::with(['user', 'family', 'rt.rw.village'])
                        ->where('nik', $searchQuery)
                        ->orWhere('full_name', 'like', "%{$searchQuery}%")
                        ->first();

                    if ($matchedResident) {
                        $uPhone = $matchedResident->user?->phone_number ?? 'Belum ada No HP';
                        $uEmail = $matchedResident->user?->email ?? '-';
                        $rtNum = $matchedResident->rt?->number ?? '-';
                        $rwNum = $matchedResident->rt?->rw?->number ?? '-';
                        $vName = $matchedResident->rt?->rw?->village?->name ?? '-';
                        $addr = $matchedResident->user?->address ?: ($matchedResident->family?->address ?? '-');
                        $kkNum = $matchedResident->family?->kk_number ?? '-';
                        $gender = match (strtoupper($matchedResident->gender ?? '')) {
                            'LAKI_LAKI', 'L', 'MALE' => 'Laki-laki',
                            'PEREMPUAN', 'P', 'FEMALE' => 'Perempuan',
                            default => $matchedResident->gender ?? '-',
                        };
                        $bPlace = $matchedResident->birth_place ?? '-';
                        $bDate = $matchedResident->birth_date ? $matchedResident->birth_date->format('d-m-Y') : '-';
                        $ttl = ($bPlace !== '-' && $bDate !== '-') ? "{$bPlace}, {$bDate}" : ($bPlace !== '-' ? $bPlace : $bDate);
                        $religion = $matchedResident->religion ? ucfirst(strtolower($matchedResident->religion)) : '-';
                        $marital = $matchedResident->marital_status ? ucfirst(strtolower($matchedResident->marital_status)) : '-';
                        $education = $matchedResident->education ?? '-';
                        $famStatus = $matchedResident->is_head_of_family ? 'Kepala Keluarga' : ($matchedResident->status ?? 'Anggota Keluarga');
                        $status = $matchedResident->status ?? 'Aktif';
                        $accStatus = $matchedResident->user ? "{$matchedResident->user->username} ({$matchedResident->user->status})" : 'Belum Ada Akun';

                        return [
                            'message' => "👤 **Data Diri Warga (Akses Penuh Super Admin) - {$matchedResident->full_name}:**\n\n".
                                "• **Nama Lengkap:** {$matchedResident->full_name}\n".
                                "• **NIK:** `{$matchedResident->nik}`\n".
                                "• **No. Kartu Keluarga (KK):** {$kkNum}\n".
                                "• **Jenis Kelamin:** {$gender}\n".
                                "• **Tempat, Tanggal Lahir:** {$ttl}\n".
                                "• **Agama:** {$religion}\n".
                                "• **Status Perkawinan:** {$marital}\n".
                                "• **Pendidikan Terakhir:** {$education}\n".
                                "• **Pekerjaan:** {$matchedResident->occupation}\n".
                                "• **Status Hubungan Keluarga:** {$famStatus} ({$status})\n".
                                "• **Alamat Lengkap:** {$addr}\n".
                                "• **Wilayah:** RT {$rtNum} / RW {$rwNum}, Desa {$vName}\n".
                                "• **No. HP/WA:** {$uPhone}\n".
                                "• **Email:** {$uEmail}\n".
                                "• **Status Akun SIWIL:** {$accStatus}",
                            'options' => [
                                ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                            ],
                        ];
                    }

                    return [
                        'message' => "Data warga dengan kata kunci \"{$searchQuery}\" tidak ditemukan di database.",
                        'options' => [
                            ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                        ],
                    ];
                }
            }

            // Password / kata sandi query
            if (preg_match('/(?:kata\s*sandi|password|pin)/i', $lower)) {
                return [
                    'message' => "🔒 **Keamanan & Kebijakan Kata Sandi (Password) Akun Warga:**\n\n".
                        "Sesuai standar keamanan sistem dan perlindungan data:\n".
                        "• Seluruh kata sandi pengguna di database dienkripsi satu arah menggunakan algoritma hashing kriptografis (**Bcrypt**) dan **tidak pernah disimpan dalam bentuk teks biasa**.\n".
                        "• Oleh karena itu, kata sandi asli tidak dapat dibaca oleh sistem AI maupun ditampilkan di layar demi menjaga keamanan akun pengguna.\n\n".
                        'Sebagai **Super Administrator**, Anda dapat membantu mereset kata sandi pengguna atau warga yang membutuhkan pembaruan login secara langsung melalui menu **Kelola Pengguna**.',
                    'options' => [
                        ['label' => 'Daftar Akun Warga', 'icon' => 'user', 'next_node' => 'start'],
                        ['label' => 'Ringkasan Database', 'icon' => 'document', 'next_node' => 'db_overview'],
                        ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                    ],
                ];
            }

            // List of citizen accounts query: "daftar akun", "siapa saja akun warga", "akun warga", "user warga"
            if (preg_match('/(?:daftar\s+akun|akun\s+warga|siapa\s+(?:saja\s+)?(?:yang\s+punya\s+)?akun|user\s+warga|data\s+akun\s+warga)/i', $lower)) {
                $citizenUsers = User::with('resident')->where('role', 'WARGA')->orderBy('name')->take(20)->get();
                $totalCitizenUsers = User::where('role', 'WARGA')->count();
                $msg = "👥 **Daftar Akun Login Warga Terdaftar:**\n\n".
                    "Total Akun Warga: **{$totalCitizenUsers} akun**\n\n";

                if ($citizenUsers->isEmpty()) {
                    $msg .= 'Belum ada akun warga yang terdaftar di aplikasi.';
                } else {
                    foreach ($citizenUsers as $idx => $cu) {
                        $num = $idx + 1;
                        $rName = $cu->resident?->full_name ?: ($cu->name ?: $cu->username);
                        $statusBadge = match ($cu->status) {
                            'active' => '🟢 Aktif',
                            'pending' => '🟡 Menunggu Verifikasi (Pending)',
                            'inactive' => '⚪ Nonaktif',
                            'rejected' => '🔴 Ditolak',
                            default => $cu->status,
                        };
                        $email = $cu->email ?: '-';
                        $phone = $cu->phone_number ?: '-';
                        $msg .= "{$num}. **{$rName}**\n".
                            "   • Username / NIK: `{$cu->username}`\n".
                            "   • Status Akun: {$statusBadge}\n".
                            "   • Kontak: {$phone} | {$email}\n\n";
                    }
                    if ($totalCitizenUsers > 20) {
                        $msg .= '_Menampilkan 20 akun pertama. Anda dapat melihat dan mengelola seluruh akun secara lengkap di menu **Kelola Pengguna**._';
                    }
                }

                return [
                    'message' => trim($msg),
                    'options' => [
                        ['label' => 'Daftar Nama Warga', 'icon' => 'search', 'next_node' => 'start'],
                        ['label' => 'Ringkasan Database', 'icon' => 'document', 'next_node' => 'db_overview'],
                        ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                    ],
                ];
            }

            // List of residents query: "siapa saja warganya", "daftar warga", "nama nama warga", "sebutkan warganya"
            if (preg_match('/(?:siapa\s+saja\s+(?:nama\s+)?warga|daftar\s+warga|nama\s+nama\s+warga|sebutkan\s+(?:nama\s+)?warga|data\s+warga\s+siapa|ada\s+warga\s+siapa|siapa\s+saja\s+namanya)/i', $lower)) {
                $residentsList = Resident::with(['user', 'family', 'rt.rw.village'])->orderBy('full_name')->take(20)->get();
                $totWarga = Resident::count();
                $msg = "📋 **Daftar Nama Warga Terdaftar:**\n\n";

                if ($residentsList->isEmpty()) {
                    $msg .= 'Belum ada data warga yang terdaftar di database.';
                } else {
                    foreach ($residentsList as $idx => $res) {
                        $num = $idx + 1;
                        $rtRw = 'RT '.($res->rt?->number ?? '-').' / RW '.($res->rt?->rw?->number ?? '-');
                        $msg .= "{$num}. **{$res->full_name}** ({$rtRw})\n";
                    }
                    if ($totWarga > 20) {
                        $msg .= "\n_Menampilkan 20 warga pertama dari total {$totWarga} warga._";
                    }
                }

                return [
                    'message' => trim($msg),
                    'options' => [
                        ['label' => 'Daftar Akun Warga', 'icon' => 'user', 'next_node' => 'start'],
                        ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                    ],
                ];
            }

            // Database / overall data across whole system
            if (preg_match('/(database|kependudukan|seluruh\s+data|semua\s+data|total\s+data|data\s+desa|seluruh\s+warga|semua\s+warga|total\s+warga|jumlah\s+warga|rekap)/i', $lower)) {
                $msg = "🗄️ **Ringkasan Keseluruhan Database SIWIL (Super Admin):**\n\n".
                    "• **Wilayah Administratif:** {$totalVillages} Desa/Kelurahan, {$totalRws} RW, {$totalRts} RT\n".
                    "• **Total Penduduk:** {$totalResidents} jiwa terdaftar\n".
                    "• **Total Kartu Keluarga:** {$totalFamilies} KK\n".
                    "• **Total Akun Pengguna:** {$totalUsers} akun ({$usersSummary})\n".
                    "• **Total Pengajuan Surat:** {$totalDocs} berkas\n".
                    "• **Total Pengaduan Warga:** {$totalComplaints} laporan\n\n".
                    'Sebagai Super Admin, Anda memiliki hak istimewa penuh untuk mengelola, memantau, dan mengaudit seluruh entitas database di atas tanpa batasan wilayah.';

                return [
                    'message' => $msg,
                    'options' => [
                        ['label' => 'Status Sistem & Server', 'icon' => 'search', 'next_node' => 'system_info'],
                        ['label' => 'Audit Log Terbaru', 'icon' => 'document', 'next_node' => 'start'],
                        ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                    ],
                ];
            }

            // Audit Log
            if (preg_match('/(audit|log|riwayat\s*aktivitas|aktivitas\s*terbaru)/i', $lower)) {
                return [
                    'message' => "📋 **Riwayat Audit Log Terbaru Sistem SIWIL:**\n\n".
                        ($auditText ?: "• Belum ada catatan aktivitas dalam audit log.\n"),
                    'options' => [
                        ['label' => 'Ringkasan Database', 'icon' => 'document', 'next_node' => 'db_overview'],
                        ['label' => 'Status Sistem & Server', 'icon' => 'search', 'next_node' => 'system_info'],
                        ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                    ],
                ];
            }

            // System / server / technical overview
            if (preg_match('/(sistem|server|teknis|konfigurasi|spesifikasi|env|environment|status|arsitektur|api\s*key)/i', $lower)) {
                $phpVer = $sys['php_version'] ?? PHP_VERSION;
                $laravelVer = $sys['laravel_version'] ?? app()->version();
                $env = $sys['environment'] ?? app()->environment();
                $dbConn = $sys['db_connection'] ?? config('database.default');
                $cache = $sys['cache_driver'] ?? config('cache.default');
                $time = $sys['server_time'] ?? now()->toDateTimeString();

                $msg = "🖥️ **Informasi Arsitektur & Status Sistem SIWIL (Super Admin):**\n\n".
                    "• **Nama Aplikasi:** SIWIL (Sistem Informasi Wilayah Terpadu)\n".
                    "• **Framework:** Laravel {$laravelVer}\n".
                    "• **PHP Engine:** {$phpVer}\n".
                    "• **Environment:** `{$env}`\n".
                    "• **Database Engine:** `{$dbConn}` (Koneksi Aktif)\n".
                    "• **Cache Driver:** `{$cache}`\n".
                    "• **Waktu Server:** {$time}\n".
                    "• **Total Pengguna Aktif:** {$totalUsers} akun ({$usersSummary})\n".
                    "• **Konektivitas:** Berjalan Normal & Siap Layanan\n\n".
                    '_Sebagai Super Admin, Anda memiliki hak akses penuh ke audit log, manajemen server, dan pengaturan sistem._';

                return [
                    'message' => $msg,
                    'options' => [
                        ['label' => 'Ringkasan Database', 'icon' => 'document', 'next_node' => 'db_overview'],
                        ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                    ],
                ];
            }

            // Complaints query for Super Admin: "berapa pengaduan", "daftar pengaduan", "pengaduan warga"
            if (preg_match('/(?:ada\s+)?(?:berapa|jumlah|total)\s+(?:laporan\s+)?(?:pengaduan|keluhan)/i', $lower)) {
                return [
                    'message' => "Total laporan pengaduan warga di database SIWIL saat ini adalah **{$totalComplaints} laporan**.",
                    'options' => [
                        ['label' => 'Ringkasan Database', 'icon' => 'document', 'next_node' => 'db_overview'],
                        ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                    ],
                ];
            }
            if (preg_match('/(?:daftar|data|rincian|semua|riwayat|ada)?\s*(?:laporan\s+)?(?:pengaduan|keluhan)(?:\s+(?:warga|masuk|terbaru))?/i', $lower)) {
                $recentComplaints = $extra['recent_complaints'] ?? Complaint::with('user.resident')->latest()->take(5)->get();
                if ($recentComplaints->isNotEmpty()) {
                    $msg = "📢 **Laporan Pengaduan Warga di Database SIWIL:**\n\n";
                    foreach ($recentComplaints as $idx => $c) {
                        $num = $idx + 1;
                        $uName = $c->user?->resident?->full_name ?: ($c->user?->name ?? 'Warga');
                        $msg .= "{$num}. **{$c->title}**\n   • Status: `{$c->status}` | Pelapor: {$uName}\n   • Tanggal: {$c->created_at->format('d M Y H:i')}\n\n";
                    }

                    return [
                        'message' => trim($msg),
                        'options' => [
                            ['label' => 'Ringkasan Database', 'icon' => 'document', 'next_node' => 'db_overview'],
                            ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                        ],
                    ];
                }

                return [
                    'message' => 'Belum ada laporan pengaduan warga di database.',
                    'options' => [['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start']],
                ];
            }

            // Document requests query for Super Admin: "berapa surat", "pengajuan surat", "permohonan surat"
            if (preg_match('/(?:ada\s+)?(?:berapa|jumlah|total)\s+(?:permohonan\s+|pengajuan\s+)?surat/i', $lower)) {
                return [
                    'message' => "Total permohonan surat yang terdaftar di database SIWIL saat ini adalah **{$totalDocs} berkas**.",
                    'options' => [
                        ['label' => 'Ringkasan Database', 'icon' => 'document', 'next_node' => 'db_overview'],
                        ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                    ],
                ];
            }
            if (preg_match('/(?:daftar|data|rincian|semua|riwayat|ada)?\s*(?:permohonan|pengajuan)\s*surat|surat\s+(?:masuk|warga|pengantar)/i', $lower)) {
                $recentDocs = $extra['recent_docs'] ?? DocumentRequest::with('resident')->latest()->take(5)->get();
                if ($recentDocs->isNotEmpty()) {
                    $msg = "📄 **Permohonan Surat Masuk di Database SIWIL:**\n\n";
                    foreach ($recentDocs as $idx => $d) {
                        $num = $idx + 1;
                        $rName = $d->resident?->full_name ?? 'Warga';
                        $msg .= "{$num}. **{$d->type}**\n   • Pemohon: {$rName} | Status: `{$d->status}`\n   • Tanggal: {$d->created_at->format('d M Y')}\n\n";
                    }

                    return [
                        'message' => trim($msg),
                        'options' => [
                            ['label' => 'Ringkasan Database', 'icon' => 'document', 'next_node' => 'db_overview'],
                            ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                        ],
                    ];
                }

                return [
                    'message' => 'Belum ada permohonan surat di database.',
                    'options' => [['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start']],
                ];
            }

            // Polls query for Super Admin: "ada polling apa", "daftar polling", "musyawarah"
            if (preg_match('/(?:ada\s+)?(?:berapa|jumlah|total)\s+(?:polling|musyawarah|voting)/i', $lower)) {
                return [
                    'message' => "Total polling/musyawarah warga di database SIWIL adalah **{$totalPolls} kegiatan**.",
                    'options' => [['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start']],
                ];
            }
            if (preg_match('/(polling|musyawarah|voting|jajak\s*pendapat)/i', $lower)) {
                $recentPolls = $extra['recent_polls'] ?? Poll::with('options')->latest()->take(5)->get();
                if ($recentPolls->isNotEmpty()) {
                    $msg = "🗳️ **Musyawarah & Polling di Database SIWIL:**\n\n";
                    foreach ($recentPolls as $idx => $p) {
                        $num = $idx + 1;
                        $msg .= "{$num}. **{$p->title}** (Status: `{$p->status}`)\n";
                    }

                    return [
                        'message' => trim($msg),
                        'options' => [
                            ['label' => 'Ringkasan Database', 'icon' => 'document', 'next_node' => 'db_overview'],
                            ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                        ],
                    ];
                }

                return [
                    'message' => 'Belum ada agenda polling atau musyawarah di database.',
                    'options' => [['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start']],
                ];
            }

            // Announcements query for Super Admin: "ada pengumuman apa", "pengumuman", "kabar desa"
            if (preg_match('/(?:ada\s+)?(?:berapa|jumlah|total)\s+pengumuman/i', $lower)) {
                return [
                    'message' => "Total pengumuman resmi di database SIWIL adalah **{$totalAnnouncements} pengumuman**.",
                    'options' => [['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start']],
                ];
            }
            if (preg_match('/(pengumuman|kabar\s*desa|informasi\s*desa)/i', $lower)) {
                $recentAnnouncements = $extra['recent_announcements'] ?? Announcement::latest()->take(5)->get();
                if ($recentAnnouncements->isNotEmpty()) {
                    $msg = "📢 **Pengumuman Resmi di Database SIWIL:**\n\n";
                    foreach ($recentAnnouncements as $idx => $a) {
                        $num = $idx + 1;
                        $msg .= "{$num}. **{$a->title}**\n   {$a->content}\n\n";
                    }

                    return [
                        'message' => trim($msg),
                        'options' => [
                            ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                        ],
                    ];
                }

                return [
                    'message' => 'Saat ini belum ada pengumuman resmi yang aktif di sistem SIWIL.',
                    'options' => [['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start']],
                ];
            }
        }

        // ==================== ROLE DESA HANDLERS ====================
        if (in_array($role, ['ADMIN_DESA', 'DESA'])) {
            $desaRaw = $extra['village_name'] ?? 'Desa';
            $desaLabel = str_starts_with(strtolower(trim($desaRaw)), 'desa ') ? trim($desaRaw) : 'Desa '.trim($desaRaw);
            $totalWargaDesa = $extra['total_warga_desa'] ?? 0;
            $totalRw = $extra['total_rw'] ?? 0;
            $totalRt = $extra['total_rt'] ?? 0;
            $totalKkDesa = $extra['total_kk_desa'] ?? 0;
            $totalPriaDesa = $extra['total_pria_desa'] ?? 0;
            $totalWanitaDesa = $extra['total_wanita_desa'] ?? 0;
            $directory = $extra['village_directory'] ?? '';

            // 0. Dashboard query for Desa: "apa yang ada di dashboard", "data dashboard", "baca dashboard", etc.
            if (preg_match('/(?:apa\s+(?:yang\s+ada|saja\s+ada|ada|isi)|ada\s+apa(?:\s+saja)?|isi|data|ringkasan|statistik|rekap|tampilan|menu|informasi|status|baca|lihat|tampilkan|cek|keseluruhan|semua|detail)\s+(?:.*)?dashboard|dashboard\s+(?:ada\s+apa|isinya\s+apa|menampilkan\s+apa|itu\s+apa)|^dashboard$|baca\s+(?:keseluruhan|semua)\s+(?:dari\s+)?dashboard/i', $clean)) {
                $activeEmergencies = Emergency::where('status', 'ACTIVE')->count();
                $sosStatus = $activeEmergencies > 0
                    ? "🚨 Terdapat **{$activeEmergencies} panggilan darurat (SOS) aktif**!"
                    : '✅ Situasi aman, tidak ada sinyal darurat aktif.';

                $msg = "📊 **Data & Informasi pada Dashboard Administrator Desa ({$desaLabel}):**\n\n".
                    "Halaman Dashboard Desa menampilkan ringkasan data kependudukan dan operasional seluruh desa:\n\n".
                    "1. **Statistik Wilayah & Kependudukan {$desaLabel}:**\n".
                    "• Total RW: **{$totalRw} RW**\n".
                    "• Total RT: **{$totalRt} RT**\n".
                    "• Total Penduduk: **{$totalWargaDesa} Jiwa** ({$totalPriaDesa} Laki-laki, {$totalWanitaDesa} Perempuan)\n".
                    "• Total Kepala Keluarga: **{$totalKkDesa} KK**\n".
                    '• Total Pengajuan Surat Masuk: **'.($extra['total_docs_desa'] ?? 0)." berkas**\n".
                    '• Total Laporan Pengaduan: **'.($extra['total_complaints_desa'] ?? 0)." laporan**\n\n".
                    "2. **Status Panggilan Darurat (SOS):**\n".
                    "• {$sosStatus}\n\n".
                    "3. **Struktur Wilayah & Modul Desa:**\n".
                    "• Pohon struktur kewilayahan RW dan RT binaan desa\n".
                    '• Layanan persuratan TTE, musyawarah desa, APBDes, dan proyek pembangunan.';

                $dashMsg = trim($msg);
                if (preg_match('/(?:sidebar|keseluruhan|semua|update|pembaruan)/i', $clean)) {
                    $sideMsg = $this->getSidebarMenuList($role);
                    $updateMsg = $this->getRecentUpdatesSummary($user);
                    $dashMsg .= "\n\n".$sideMsg."\n\n".$updateMsg;
                }

                return [
                    'message' => $dashMsg,
                    'options' => [
                        ['label' => 'Sebaran per RW', 'icon' => 'search', 'next_node' => 'start'],
                        ['label' => 'Direktori Pengurus', 'icon' => 'phone', 'next_node' => 'start'],
                        ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                    ],
                ];
            }

            // 1. Specific RT demographic query: "ada berapa laki-laki di rt 01", "berapa perempuan di rt 02", "berapa warga di rt 01", "ada berapa kk di rt 01"
            if (preg_match('/(?:ada\s+)?(?:berapa|jumlah|total)\s+(laki[\s\-]*laki|pria|perempuan|wanita|warga|penduduk|jiwa|kk|kartu\s*keluarga)\s+(?:yang\s+ada\s+)?(?:di\s*)?rt\s*0?(\d+)/i', $lower, $m) ||
                preg_match('/(?:di\s*)?rt\s*0?(\d+)\s+(?:ada\s+)?(?:berapa|jumlah|total)\s+(laki[\s\-]*laki|pria|perempuan|wanita|warga|penduduk|jiwa|kk|kartu\s*keluarga)/i', $lower, $mReverse)) {

                $targetGenderOrType = isset($m[1]) ? strtolower(trim($m[1])) : strtolower(trim($mReverse[2]));
                $rawRtNum = isset($m[2]) ? $m[2] : $mReverse[1];
                $formattedTarget = str_pad(ltrim($rawRtNum, '0'), 2, '0', STR_PAD_LEFT);

                $villageId = $extra['village_id'] ?? ($user->village_id ?? Village::first()?->id);
                $targetRt = Rt::with('rw.village')
                    ->whereHas('rw', fn ($q) => $q->where('village_id', $villageId))
                    ->where('number', $formattedTarget)
                    ->first();

                if ($targetRt) {
                    $totWarga = Resident::where('rt_id', $targetRt->id)->count();
                    $totPria = Resident::where('rt_id', $targetRt->id)->where(fn ($q) => $q->where('gender', 'LAKI_LAKI')->orWhere('gender', 'Laki-laki')->orWhere('gender', 'L'))->count();
                    $totWanita = Resident::where('rt_id', $targetRt->id)->where(fn ($q) => $q->where('gender', 'PEREMPUAN')->orWhere('gender', 'Perempuan')->orWhere('gender', 'P'))->count();
                    $totKk = Family::where('rt_id', $targetRt->id)->count();
                    $rwNum = $targetRt->rw?->number ?? '-';

                    if (str_contains($targetGenderOrType, 'laki') || str_contains($targetGenderOrType, 'pria')) {
                        $msg = "Di **RT {$targetRt->number} (RW {$rwNum})** terdapat **{$totPria} orang laki-laki**.";
                    } elseif (str_contains($targetGenderOrType, 'perempuan') || str_contains($targetGenderOrType, 'wanita')) {
                        $msg = "Di **RT {$targetRt->number} (RW {$rwNum})** terdapat **{$totWanita} orang perempuan**.";
                    } elseif (str_contains($targetGenderOrType, 'kk') || str_contains($targetGenderOrType, 'keluarga')) {
                        $msg = "Di **RT {$targetRt->number} (RW {$rwNum})** terdapat **{$totKk} Kartu Keluarga (KK)**.";
                    } else {
                        $msg = "Di **RT {$targetRt->number} (RW {$rwNum})** terdapat **{$totWarga} orang warga/jiwa**.";
                    }

                    return [
                        'message' => $msg,
                        'options' => [
                            ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                        ],
                    ];
                }

                return [
                    'message' => "Data untuk RT {$formattedTarget} tidak ditemukan di wilayah {$desaLabel}.",
                    'options' => [
                        ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                    ],
                ];
            }

            // 2. Breakdown per RT in village: "rincian rt", "sebaran rt", "daftar rt", "demografi rt"
            if (preg_match('/(rincian\s*rt|sebaran\s*rt|daftar\s*rt|demografi\s*rt|data\s*rt|per\s*rt)/i', $lower)) {
                $rtDemographics = $extra['village_rt_demographics'] ?? '';

                return [
                    'message' => "📊 **Rincian Demografi & Kependudukan per RT di {$desaLabel}:**\n\n".
                        ($rtDemographics ?: "• Belum ada data RT terdaftar.\n"),
                    'options' => [
                        ['label' => 'Sebaran per RW', 'icon' => 'search', 'next_node' => 'start'],
                        ['label' => 'Direktori Pengurus', 'icon' => 'phone', 'next_node' => 'start'],
                        ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                    ],
                ];
            }

            // Proportional resident name list across village: "siapa saja warganya", "daftar warga", "nama nama warga"
            if (preg_match('/(?:siapa\s+saja\s+(?:nama\s+)?warga|daftar\s+warga|nama\s+nama\s+warga|sebutkan\s+(?:nama\s+)?warga|warga\s+siapa\s+saja)/i', $lower)) {
                $villageId = $extra['village_id'] ?? ($user->village_id ?? Village::first()?->id);
                $residents = Resident::with(['rt.rw'])->whereHas('rt.rw', fn ($q) => $q->where('village_id', $villageId))->orderBy('full_name')->take(20)->get();
                $totDesa = Resident::whereHas('rt.rw', fn ($q) => $q->where('village_id', $villageId))->count();
                $msg = "📋 **Daftar Nama Warga di {$desaLabel}:**\n\n";
                if ($residents->isEmpty()) {
                    $msg .= "Belum ada data warga terdaftar di {$desaLabel}.";
                } else {
                    foreach ($residents as $idx => $r) {
                        $num = $idx + 1;
                        $rtRw = 'RT '.($r->rt?->number ?? '-').' / RW '.($r->rt?->rw?->number ?? '-');
                        $msg .= "{$num}. **{$r->full_name}** ({$rtRw})\n";
                    }
                    if ($totDesa > 20) {
                        $msg .= "\n_Menampilkan 20 warga pertama dari total {$totDesa} warga di {$desaLabel}._";
                    }
                }

                return [
                    'message' => trim($msg),
                    'options' => [['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start']],
                ];
            }

            // Resident count & demographics: "berapa warganya", "jumlah warga desa", "total warga"
            if (preg_match('/(?:berapa\s*warga(?:nya)?|\bjumlah\s*warga\b|\btotal\s*warga\b|\bkependudukan\b|\bdemografi\b|\bjumlah\s*penduduk\b)/i', $lower)) {
                return [
                    'message' => "Total kependudukan di wilayah **{$desaLabel}** saat ini adalah **{$totalWargaDesa} jiwa** yang tersebar di **{$totalRw} RW** dan **{$totalRt} RT**.\n\n".
                        "• **Total Kartu Keluarga:** {$totalKkDesa} KK\n".
                        "• **Laki-laki:** {$totalPriaDesa} jiwa\n".
                        "• **Perempuan:** {$totalWanitaDesa} jiwa\n\n".
                        '_Sebagai Administrator Desa, Anda memiliki otoritas memantau seluruh wilayah desa._',
                    'options' => [
                        ['label' => 'Lihat Sebaran RW', 'icon' => 'search', 'next_node' => 'start'],
                        ['label' => 'Direktori Pengurus', 'icon' => 'phone', 'next_node' => 'start'],
                        ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                    ],
                ];
            }

            // Breakdown per RW in village
            if (preg_match('/(rincian\s*rw|sebaran\s*rw|daftar\s*rw)/i', $lower)) {
                $rwBreakdown = $extra['village_rw_breakdown'] ?? '';

                return [
                    'message' => "📊 **Sebaran Wilayah per RW di {$desaLabel}:**\n\n".
                        ($rwBreakdown ?: "• Belum ada data RW terdaftar.\n"),
                    'options' => [
                        ['label' => 'Direktori Pengurus', 'icon' => 'phone', 'next_node' => 'start'],
                        ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                    ],
                ];
            }

            // Directory of RW & RT: "pengurusnya", "siapa pengurusnya", "daftar pengurus"
            if (preg_match('/(pengurus(nya)?|direktori|siapa\s*pengurus|kontak\s*(rt|rw|pengurus))/i', $lower)) {
                return [
                    'message' => $directory ?: "Belum ada pengurus RT/RW terdaftar di {$desaLabel}.",
                    'options' => [
                        ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                    ],
                ];
            }

            // Budgets / APBDes
            if (preg_match('/(apbdes|anggaran|dana\s*desa|keuangan|kas\s*desa|belanja|pendapatan)/i', $lower)) {
                $budgetsText = $extra['budgets_text'] ?? '';

                return [
                    'message' => "💰 **Ringkasan APBDes / Anggaran {$desaLabel}:**\n\n".
                        ($budgetsText ?: "• Belum ada data anggaran terdaftar di sistem.\n"),
                    'options' => [
                        ['label' => 'Proyek Pembangunan', 'icon' => 'document', 'next_node' => 'start'],
                        ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                    ],
                ];
            }

            // Developments / Village Projects: "ada project apa", "proyek desa", "ada proyek apa", "project apa"
            if (preg_match('/(project|proyek|pembangunan|infrastruktur|pekerjaan)/i', $lower)) {
                $devsText = $extra['devs_text'] ?? '';

                return [
                    'message' => "🏗️ **Proyek Pembangunan {$desaLabel}:**\n\n".
                        ($devsText ?: "• Belum ada data proyek pembangunan terdaftar.\n"),
                    'options' => [
                        ['label' => 'Anggaran Desa', 'icon' => 'document', 'next_node' => 'start'],
                        ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                    ],
                ];
            }

            // Document requests in village
            if (preg_match('/(surat|persuratan|pengajuan\s*surat)/i', $lower)) {
                $desaDocsList = $extra['desa_docs_list'] ?? '';
                $pDocs = $extra['pending_desa_docs'] ?? 0;
                $totDocs = $extra['total_docs_desa'] ?? 0;

                return [
                    'message' => "📄 **Layanan Persuratan {$desaLabel} (Total: {$totDocs}, Menunggu TTE/Desa: {$pDocs}):**\n\n".
                        ($desaDocsList ?: "• Belum ada dokumen pengajuan surat.\n"),
                    'options' => [
                        ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                    ],
                ];
            }

            // Complaints across village
            if (preg_match('/(pengaduan|keluhan|laporan)/i', $lower)) {
                $desaCompList = $extra['desa_complaints_list'] ?? '';
                $totComp = $extra['total_complaints_desa'] ?? 0;

                return [
                    'message' => "📢 **Laporan Pengaduan Warga {$desaLabel} (Total: {$totComp}):**\n\n".
                        ($desaCompList ?: "• Belum ada laporan pengaduan masuk.\n"),
                    'options' => [
                        ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                    ],
                ];
            }

            // Emergencies
            if (preg_match('/(darurat|emergency|kebakaran|medis|bencana)/i', $lower)) {
                $emText = $extra['emergencies_text'] ?? '';

                return [
                    'message' => "🚨 **Laporan Kejadian Darurat {$desaLabel}:**\n\n".
                        ($emText ?: "• Tidak ada laporan darurat aktif saat ini. Situasi aman.\n"),
                    'options' => [
                        ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                    ],
                ];
            }

            // Specific RT leader query for Desa: "siapa ketua rt 01", "kontak rt 01"
            if (preg_match('/(?:siapa|kontak|nomor|no\s*hp|wa)\s*(?:nama\s*)?(?:ketua\s*)?rt\s*0?(\d+)/i', $lower, $mRtHead) ||
                preg_match('/(?:di\s*)?rt\s*0?(\d+)\s+(?:siapa|kontak|nomor|no\s*hp|wa)\s*(?:nama\s*)?(?:ketua)?/i', $lower, $mRtHeadRev)) {
                $rawNum = isset($mRtHead[1]) ? $mRtHead[1] : $mRtHeadRev[1];
                $rtNumTarget = str_pad(ltrim($rawNum, '0'), 2, '0', STR_PAD_LEFT);
                $villageId = $extra['village_id'] ?? ($user->village_id ?? Village::first()?->id);
                $targetRt = Rt::with('rw')->whereHas('rw', fn ($q) => $q->where('village_id', $villageId))->where('number', $rtNumTarget)->first();
                if ($targetRt) {
                    $head = User::with('resident')->where('role', 'RT')->where('rt_id', $targetRt->id)->first();
                    $headName = $head ? ($head->resident?->full_name ?: ($head->name ?: $head->username)) : 'Belum terdaftar';
                    $phone = $head?->phone_number ? " (No. WA/HP: {$head->phone_number})" : '';
                    $rwNum = $targetRt->rw?->number ?? '-';

                    return [
                        'message' => "Ketua **RT {$targetRt->number}** (RW {$rwNum}) di {$desaLabel} adalah **{$headName}**{$phone}.",
                        'options' => [['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start']],
                    ];
                }

                return [
                    'message' => "Data RT {$rtNumTarget} tidak ditemukan di {$desaLabel}.",
                    'options' => [['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start']],
                ];
            }

            // Specific RW leader query for Desa: "siapa ketua rw 01", "kontak rw 01"
            if (preg_match('/(?:siapa|kontak|nomor|no\s*hp|wa)\s*(?:nama\s*)?(?:ketua\s*)?rw\s*0?(\d+)/i', $lower, $mRwHead) ||
                preg_match('/(?:di\s*)?rw\s*0?(\d+)\s+(?:siapa|kontak|nomor|no\s*hp|wa)\s*(?:nama\s*)?(?:ketua)?/i', $lower, $mRwHeadRev)) {
                $rawRwNum = isset($mRwHead[1]) ? $mRwHead[1] : $mRwHeadRev[1];
                $rwNumTarget = str_pad(ltrim($rawRwNum, '0'), 2, '0', STR_PAD_LEFT);
                $villageId = $extra['village_id'] ?? ($user->village_id ?? Village::first()?->id);
                $targetRw = Rw::where('village_id', $villageId)->where('number', $rwNumTarget)->first();
                if ($targetRw) {
                    $head = User::with('resident')->where('role', 'RW')->where('rw_id', $targetRw->id)->first();
                    $headName = $head ? ($head->resident?->full_name ?: ($head->name ?: $head->username)) : 'Belum terdaftar';
                    $phone = $head?->phone_number ? " (No. WA/HP: {$head->phone_number})" : '';

                    return [
                        'message' => "Ketua **RW {$targetRw->number}** di {$desaLabel} adalah **{$headName}**{$phone}.",
                        'options' => [['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start']],
                    ];
                }

                return [
                    'message' => "Data RW {$rwNumTarget} tidak ditemukan di {$desaLabel}.",
                    'options' => [['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start']],
                ];
            }

            // General RT leader list for Desa: "siapa nama rt nya", "siapa saja ketua rt", "daftar ketua rt"
            if (preg_match('/(?:siapa|daftar|sebutkan)\s*(?:saja\s+)?(?:nama\s+)?(?:ketua\s*)?rt(?:\s*nya)?/i', $lower) ||
                preg_match('/nama\s*(?:ketua\s*)?rt(?:\s*nya)?/i', $lower)) {
                $villageId = $extra['village_id'] ?? ($user->village_id ?? Village::first()?->id);
                $rts = Rt::with('rw')->whereHas('rw', fn ($q) => $q->where('village_id', $villageId))->orderBy('number')->get();
                if ($rts->isNotEmpty()) {
                    $msg = "Berikut daftar Ketua RT di wilayah **{$desaLabel}**:\n\n";
                    foreach ($rts as $idx => $rt) {
                        $num = $idx + 1;
                        $head = User::with('resident')->where('role', 'RT')->where('rt_id', $rt->id)->first();
                        $headName = $head ? ($head->resident?->full_name ?: ($head->name ?: $head->username)) : 'Belum terdaftar';
                        $rwNum = $rt->rw?->number ?? '-';
                        $phone = $head?->phone_number ? " — {$head->phone_number}" : '';
                        $msg .= "{$num}. **RT {$rt->number}** (RW {$rwNum}): **{$headName}**{$phone}\n";
                    }

                    return [
                        'message' => trim($msg),
                        'options' => [['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start']],
                    ];
                }
            }

            // General RW leader list for Desa: "siapa nama rw nya", "siapa saja ketua rw", "daftar ketua rw"
            if (preg_match('/(?:siapa|daftar|sebutkan)\s*(?:saja\s+)?(?:nama\s+)?(?:ketua\s*)?rw(?:\s*nya)?/i', $lower) ||
                preg_match('/nama\s*(?:ketua\s*)?rw(?:\s*nya)?/i', $lower)) {
                $villageId = $extra['village_id'] ?? ($user->village_id ?? Village::first()?->id);
                $rws = Rw::where('village_id', $villageId)->orderBy('number')->get();
                if ($rws->isNotEmpty()) {
                    $msg = "Berikut daftar Ketua RW di wilayah **{$desaLabel}**:\n\n";
                    foreach ($rws as $idx => $rw) {
                        $num = $idx + 1;
                        $head = User::with('resident')->where('role', 'RW')->where('rw_id', $rw->id)->first();
                        $headName = $head ? ($head->resident?->full_name ?: ($head->name ?: $head->username)) : 'Belum terdaftar';
                        $phone = $head?->phone_number ? " — {$head->phone_number}" : '';
                        $msg .= "{$num}. **RW {$rw->number}**: **{$headName}**{$phone}\n";
                    }

                    return [
                        'message' => trim($msg),
                        'options' => [['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start']],
                    ];
                }
            }

            // Specific resident personal data lookup in village: "cari warga [nama]", "data lengkap [nama]", "biodata [nama]"
            if (preg_match('/(?:cari\s+warga(?:\s+bernama|\s+nama)?|data\s+(?:lengkap\s+|diri\s+)?warga(?:\s+bernama|\s+nama)?|biodata\s+warga(?:\s+bernama|\s+nama)?|warga\s+(?:bernama|nama))\s+([a-zA-Z0-9\s]+)/i', $lower, $m)) {
                $searchName = trim($m[1]);
                $villageId = $extra['village_id'] ?? ($user->village_id ?? Village::first()?->id);
                $matched = Resident::with(['user', 'family', 'rt.rw'])
                    ->whereHas('rt.rw', fn ($q) => $q->where('village_id', $villageId))
                    ->where(function ($q) use ($searchName) {
                        $q->where('nik', $searchName)->orWhere('full_name', 'like', "%{$searchName}%");
                    })
                    ->first();

                if ($matched) {
                    $hp = $matched->user?->phone_number ?? 'Belum ada No HP';
                    $addr = $matched->user?->address ?: ($matched->family?->address ?? '-');
                    $kkNum = $matched->family?->kk_number ?? '-';
                    $tgl = $matched->birth_date ? $matched->birth_date->format('d-m-Y') : '-';
                    $ttl = ($matched->birth_place && $tgl !== '-') ? "{$matched->birth_place}, {$tgl}" : ($matched->birth_place ?: $tgl);
                    $agama = $matched->religion ? ucfirst(strtolower($matched->religion)) : '-';
                    $gender = match (strtoupper($matched->gender ?? '')) {
                        'LAKI_LAKI', 'L', 'MALE' => 'Laki-laki',
                        'PEREMPUAN', 'P', 'FEMALE' => 'Perempuan',
                        default => $matched->gender ?? '-',
                    };
                    $rtNumRes = $matched->rt?->number ?? '-';
                    $rwNumRes = $matched->rt?->rw?->number ?? '-';

                    $msg = "👤 **Data Lengkap Warga - {$matched->full_name} (RT {$rtNumRes}/RW {$rwNumRes}):**\n\n".
                        "• **Nama Lengkap:** {$matched->full_name}\n".
                        "• **NIK:** `{$matched->nik}`\n".
                        "• **No. KK:** {$kkNum}\n".
                        "• **Jenis Kelamin:** {$gender}\n".
                        "• **TTL:** {$ttl}\n".
                        "• **Agama:** {$agama}\n".
                        "• **Pekerjaan:** {$matched->occupation}\n".
                        "• **Alamat:** {$addr}\n".
                        "• **No. HP/WA:** {$hp}";

                    return [
                        'message' => trim($msg),
                        'options' => [['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start']],
                    ];
                }

                return [
                    'message' => "Data warga dengan kata kunci \"{$searchName}\" tidak ditemukan di wilayah {$desaLabel}.",
                    'options' => [['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start']],
                ];
            }

            // Village head query for Desa: "siapa kepala desa", "admin desa", "kontak desa"
            if (preg_match('/(?:siapa|kontak|nomor|no\s*hp|wa)\s*(?:kepala\s*desa|kades|admin\s*desa|kantor\s*desa)/i', $lower)) {
                $desaAdmin = User::whereIn('role', ['ADMIN_DESA', 'DESA'])->first();
                $name = $desaAdmin ? $desaAdmin->name : 'Administrator Desa';
                $phone = $desaAdmin?->phone_number ? " (No. WA/HP: {$desaAdmin->phone_number})" : '';

                return [
                    'message' => "Kepala / Administrator {$desaLabel} adalah **{$name}**{$phone}.",
                    'options' => [['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start']],
                ];
            }
        }

        // ==================== GENERAL FALLBACKS ====================
        // Specific RT check fallback
        if (preg_match('/(siapa\s*(ketua\s*)?rt|ketua\s*rt|kontak\s*rt|nomor\s*rt|no\s*(hp|wa)\s*rt|wa\s*rt|rt\s*saya)/i', $lower)) {
            $rtInfo = $extra['rt_info'] ?? null;
            if ($rtInfo) {
                return [
                    'message' => "Ketua RT Anda di **RT {$rtInfo['number']} / RW {$rtInfo['rw_number']} ({$rtInfo['village']})** adalah **{$rtInfo['name']}**.\n\n".
                        "Berikut kontak resmi Ketua RT Anda:\n".
                        "• 📞 **WhatsApp / Telp**: `{$rtInfo['phone']}`\n".
                        "• ✉️ **Email**: {$rtInfo['email']}\n".
                        "• 📍 **Wilayah**: RT {$rtInfo['number']} / RW {$rtInfo['rw_number']}, {$rtInfo['village']}\n\n".
                        '_Silakan hubungi Ketua RT untuk keperluan surat pengantar atau koordinasi lingkungan._',
                    'options' => [
                        ['label' => 'Buat Surat Pengantar', 'icon' => 'document', 'next_node' => 'surat_pilih_jenis'],
                        ['label' => 'Lihat Kontak RW', 'icon' => 'phone', 'next_node' => 'info_kontak'],
                        ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                    ],
                ];
            }
        }

        // Specific RW check fallback
        if (preg_match('/(siapa\s*(ketua\s*)?rw|ketua\s*rw|kontak\s*rw|nomor\s*rw|no\s*(hp|wa)\s*rw|wa\s*rw|rw\s*saya)/i', $lower)) {
            $rwInfo = $extra['rw_info'] ?? null;
            if ($rwInfo) {
                return [
                    'message' => "Ketua RW Anda di **RW {$rwInfo['number']} ({$rwInfo['village']})** adalah **{$rwInfo['name']}**.\n\n".
                        "Berikut kontak resmi Ketua RW Anda:\n".
                        "• 📞 **WhatsApp / Telp**: `{$rwInfo['phone']}`\n".
                        "• ✉️ **Email**: {$rwInfo['email']}\n".
                        "• 📍 **Wilayah**: RW {$rwInfo['number']}, {$rwInfo['village']}\n\n".
                        '_Silakan hubungi Ketua RW untuk persetujuan surat lanjutan atau kegiatan tingkat RW._',
                    'options' => [
                        ['label' => 'Lihat Kontak RT', 'icon' => 'phone', 'next_node' => 'info_kontak'],
                        ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                    ],
                ];
            }
        }

        // Specific Kantor Desa fallback
        if (preg_match('/(kantor\s*desa|kontak\s*desa|nomor\s*desa|kepala\s*desa|admin\s*desa|no\s*(hp|wa)\s*desa|desa\s*saya)/i', $lower)) {
            $desaInfo = $extra['desa_info'] ?? null;
            if ($desaInfo) {
                return [
                    'message' => "Berikut adalah informasi kontak resmi **Kantor {$desaInfo['name']}**:\n\n".
                        "• 🏢 **Instansi**: Kantor {$desaInfo['name']}\n".
                        "• 📞 **WhatsApp / Telp**: `{$desaInfo['phone']}`\n".
                        "• ✉️ **Email**: {$desaInfo['email']}\n".
                        "• 🕒 **Jam Pelayanan**: Senin – Jumat (08:00 – 15:00 WIB)\n\n".
                        '_Untuk layanan surat keterangan desa, pastikan telah melengkapi surat pengantar dari RT/RW setempat._',
                    'options' => [
                        ['label' => 'Buat Surat Mandiri', 'icon' => 'document', 'next_node' => 'surat_pilih_jenis'],
                        ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                    ],
                ];
            }
        }

        // Official contacts fallback
        if (preg_match('/(kontak|nomor|no\s*hp|no\s*wa|\bwa\b|whatsapp|telepon|email|pengurus)/i', $lower)) {
            if (! empty($scopedData['contacts_text'])) {
                return [
                    'message' => "Berikut adalah daftar kontak resmi pengurus wilayah Anda:\n\n".$scopedData['contacts_text'],
                    'options' => [
                        ['label' => 'Buat Surat Baru', 'icon' => 'document', 'next_node' => 'surat_pilih_jenis'],
                        ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                    ],
                ];
            }

            return [
                'message' => 'Untuk melihat kontak resmi pengurus RT/RW Anda, silakan klik menu di bawah ini:',
                'options' => [
                    ['label' => 'Lihat Kontak Pengurus', 'icon' => 'phone', 'next_node' => 'info_kontak'],
                    ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                ],
            ];
        }

        // Generic letter requests
        if (preg_match('/(surat|domisili|sktm|sku|pengantar)/i', $lower)) {
            return [
                'message' => "📄 Layanan pengajuan surat mandiri SIWIL menyediakan:\n• Surat Pengantar Domisili\n• Surat Keterangan Tidak Mampu (SKTM)\n• Surat Keterangan Usaha (SKU)\n\nSilakan pilih surat yang ingin Anda ajukan di bawah ini:",
                'options' => [
                    ['label' => 'Pilih Jenis Surat', 'icon' => 'document', 'next_node' => 'surat_pilih_jenis'],
                    ['label' => 'Cek Status Surat Saya', 'icon' => 'search', 'next_node' => 'cek_status_surat'],
                ],
            ];
        }

        // Generic complaints
        if (preg_match('/(lapor|pengaduan|keluhan|rusak|sampah|jalan|masalah)/i', $lower)) {
            return [
                'message' => '📢 Anda dapat melaporkan keluhan atau permasalahan lingkungan (infrastruktur jalan, sampah, keamanan, dll) langsung melalui SIWIL.',
                'options' => [
                    ['label' => 'Buat Laporan Baru', 'icon' => 'alert', 'next_node' => 'lapor_mulai'],
                    ['label' => 'Cek Status Laporan Saya', 'icon' => 'search', 'next_node' => 'cek_status_pengaduan'],
                ],
            ];
        }

        // Generic Polling
        if (preg_match('/(polling|voting|musyawarah|suara)/i', $lower)) {
            return [
                'message' => '🗳️ Anda dapat berpartisipasi dalam musyawarah warga dan memberikan suara pada polling aktif.',
                'options' => [
                    ['label' => 'Lihat Polling Aktif', 'icon' => 'vote', 'next_node' => 'musyawarah_list'],
                    ['label' => 'Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                ],
            ];
        }

        // Generic Status check
        if (preg_match('/(status|cek|pengajuan|berkas)/i', $lower)) {
            return [
                'message' => '🔍 Silakan pilih status layanan yang ingin Anda periksa:',
                'options' => [
                    ['label' => 'Cek Status Surat', 'icon' => 'document', 'next_node' => 'cek_status_surat'],
                    ['label' => 'Cek Status Pengaduan', 'icon' => 'alert', 'next_node' => 'cek_status_pengaduan'],
                ],
            ];
        }

        // Knowledge Base check: if question matches topic in AiKnowledge
        $knowledgeList = $extra['knowledge_list'] ?? AiKnowledge::all();
        foreach ($knowledgeList as $kn) {
            $topicWords = array_filter(explode(' ', strtolower($kn->topic)), fn ($w) => strlen($w) > 3);
            foreach ($topicWords as $word) {
                if (str_contains($lower, $word)) {
                    return [
                        'message' => "💡 **Informasi Terkait {$kn->topic}:**\n\n{$kn->content}",
                        'options' => [
                            ['label' => 'Kembali ke Menu Awal', 'icon' => 'home', 'next_node' => 'start'],
                        ],
                    ];
                }
            }
        }

        // Default fallback when AI cannot understand or answer the question
        return [
            'message' => "Mohon maaf, saya belum dapat memahami atau menjawab pertanyaan tersebut. 🙏\n\nSebagai Asisten Layanan SIWIL, saya dapat membantu Anda terkait:\n• Pengajuan Surat Pengantar Mandiri (Domisili, SKTM, SKU)\n• Pelaporan Masalah & Pengaduan Lingkungan\n• Musyawarah & Polling Warga\n• Informasi Kontak Resmi Pengurus RT/RW/Desa\n• Pengecekan Data Diri & Profil Kependudukan\n\nSilakan pilih menu bantuan di bawah ini atau kembali ke menu awal:",
            'options' => [
                ['label' => 'Kembali ke Menu Awal', 'icon' => 'home', 'next_node' => 'start'],
                ['label' => 'Info Kontak Pengurus', 'icon' => 'phone', 'next_node' => 'info_kontak'],
                ['label' => 'Buat Surat Baru', 'icon' => 'document', 'next_node' => 'surat_pilih_jenis'],
                ['label' => 'Lapor/Pengaduan', 'icon' => 'alert', 'next_node' => 'lapor_mulai'],
                ['label' => 'Cek Status Layanan', 'icon' => 'search', 'next_node' => 'cek_status_pilih'],
            ],
        ];
    }

    private function processNode($node, $data, $user)
    {
        $resident = Resident::where('nik', $user->username)->first();

        switch ($node) {
            case 'start':
                $profile = $this->getUserProfileData($user);

                if ($user->role === 'SUPER_ADMIN') {
                    return [
                        'message' => "Halo Bapak/Ibu **{$profile['name']}** ({$profile['role_label']})! 👑\n\nSelamat datang di Konsol Asisten Virtual SIWIL. Anda memiliki hak akses penuh ke seluruh arsitektur sistem dan database wilayah SIWIL. Ada yang ingin Anda pantau atau kelola?",
                        'options' => [
                            ['label' => 'Ringkasan Database', 'icon' => 'document', 'next_node' => 'db_overview'],
                            ['label' => 'Status Sistem & Server', 'icon' => 'search', 'next_node' => 'system_info'],
                            ['label' => 'Data Diri Saya', 'icon' => 'user', 'next_node' => 'profil_saya'],
                            ['label' => 'Info Kontak Pengurus', 'icon' => 'phone', 'next_node' => 'info_kontak'],
                            ['label' => 'Cek Status Layanan', 'icon' => 'search', 'next_node' => 'cek_status_pilih'],
                        ],
                    ];
                }

                if (in_array($user->role, ['ADMIN_DESA', 'DESA'])) {
                    return [
                        'message' => "Halo Bapak/Ibu **{$profile['name']}** ({$profile['role_label']})! 🏢\n\nSelamat datang di Layanan Asisten Virtual Administrator Desa. Anda dapat memantau data kependudukan seluruh desa, surat masuk, pengaduan warga, APBDes, serta struktur wilayah RW & RT. Ada yang bisa saya bantu?",
                        'options' => [
                            ['label' => 'Data Diri Saya', 'icon' => 'user', 'next_node' => 'profil_saya'],
                            ['label' => 'Cek Status Surat Desa', 'icon' => 'document', 'next_node' => 'cek_status_surat'],
                            ['label' => 'Cek Pengaduan Warga', 'icon' => 'alert', 'next_node' => 'cek_status_lapor'],
                            ['label' => 'Direktori Pengurus RT/RW', 'icon' => 'phone', 'next_node' => 'info_kontak'],
                            ['label' => 'Musyawarah & Polling', 'icon' => 'vote', 'next_node' => 'musyawarah_list'],
                        ],
                    ];
                }

                if ($user->role === 'RW') {
                    return [
                        'message' => "Halo Bapak/Ibu **{$profile['name']}** ({$profile['role_label']})! 🏛️\n\nSelamat datang di Layanan Asisten Pengurus RW. Anda dapat mengakses data kependudukan seluruh RT binaan, verifikasi surat masuk tingkat RW, pengaduan warga, serta koordinasi RT. Ada yang bisa saya bantu?",
                        'options' => [
                            ['label' => 'Data Diri Saya', 'icon' => 'user', 'next_node' => 'profil_saya'],
                            ['label' => 'Verifikasi Surat Masuk', 'icon' => 'document', 'next_node' => 'cek_status_surat'],
                            ['label' => 'Cek Pengaduan RW', 'icon' => 'alert', 'next_node' => 'cek_status_lapor'],
                            ['label' => 'Kontak Pengurus RT Binaan', 'icon' => 'phone', 'next_node' => 'info_kontak'],
                            ['label' => 'Musyawarah & Polling', 'icon' => 'vote', 'next_node' => 'musyawarah_list'],
                        ],
                    ];
                }

                if ($user->role === 'RT') {
                    return [
                        'message' => "Halo Bapak/Ibu **{$profile['name']}** ({$profile['role_label']})! 🏠\n\nSelamat datang di Layanan Asisten Pengurus RT. Anda memiliki kewenangan penuh memantau kependudukan warga RT Anda, memverifikasi permohonan surat masuk, dan menangani pengaduan lingkungan. Ada yang bisa saya bantu?",
                        'options' => [
                            ['label' => 'Data Diri Saya', 'icon' => 'user', 'next_node' => 'profil_saya'],
                            ['label' => 'Verifikasi Surat Masuk', 'icon' => 'document', 'next_node' => 'cek_status_surat'],
                            ['label' => 'Cek Pengaduan RT', 'icon' => 'alert', 'next_node' => 'cek_status_lapor'],
                            ['label' => 'Koordinasi Pengurus (RW & RT Lain)', 'icon' => 'phone', 'next_node' => 'info_kontak'],
                            ['label' => 'Musyawarah & Polling', 'icon' => 'vote', 'next_node' => 'musyawarah_list'],
                        ],
                    ];
                }

                // Default: WARGA
                return [
                    'message' => "Halo Bapak/Ibu **{$profile['name']}** ({$profile['role_label']})! 👋\n\nSaya Asisten Virtual SIWIL. Silakan pilih menu layanan mandiri di bawah ini atau tanyakan langsung pertanyaan Anda:",
                    'options' => [
                        ['label' => 'Data Diri Saya', 'icon' => 'search', 'next_node' => 'profil_saya'],
                        ['label' => 'Buat Surat Baru', 'icon' => 'document', 'next_node' => 'surat_pilih_jenis'],
                        ['label' => 'Lapor/Pengaduan', 'icon' => 'alert', 'next_node' => 'lapor_mulai'],
                        ['label' => 'Musyawarah / Polling', 'icon' => 'vote', 'next_node' => 'musyawarah_list'],
                        ['label' => 'Cek Status Saya', 'icon' => 'search', 'next_node' => 'cek_status_pilih'],
                        ['label' => 'Info Kontak Pengurus', 'icon' => 'phone', 'next_node' => 'info_kontak'],
                    ],
                ];

            case 'system_info':
                $usersByRole = User::selectRaw('role, count(*) as count')->groupBy('role')->pluck('count', 'role')->toArray();
                $usersSummary = implode(', ', array_map(fn ($r, $c) => "{$r}: {$c}", array_keys($usersByRole), $usersByRole));
                $laravelVer = app()->version();
                $phpVer = PHP_VERSION;
                $env = app()->environment();
                $dbConn = config('database.default');
                $cache = config('cache.default');
                $time = now()->toDateTimeString();
                $totalUsers = User::count();

                $msg = "🖥️ **Informasi Arsitektur & Status Sistem SIWIL (Super Admin):**\n\n".
                    "• **Nama Aplikasi:** SIWIL (Sistem Informasi Wilayah Terpadu)\n".
                    "• **Framework:** Laravel {$laravelVer}\n".
                    "• **PHP Engine:** {$phpVer}\n".
                    "• **Environment:** `{$env}`\n".
                    "• **Database Engine:** `{$dbConn}` (Koneksi Aktif)\n".
                    "• **Cache Driver:** `{$cache}`\n".
                    "• **Waktu Server:** {$time}\n".
                    "• **Total Pengguna Aktif:** {$totalUsers} akun ({$usersSummary})\n".
                    "• **Konektivitas:** Berjalan Normal & Siap Layanan\n\n".
                    '_Sebagai Super Admin, Anda memiliki hak akses penuh ke audit log, manajemen server, dan pengaturan sistem._';

                return [
                    'message' => $msg,
                    'options' => [
                        ['label' => 'Ringkasan Database', 'icon' => 'document', 'next_node' => 'db_overview'],
                        ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                    ],
                ];

            case 'db_overview':
                $totalVillages = Village::count();
                $totalRws = Rw::count();
                $totalRts = Rt::count();
                $totalResidents = Resident::count();
                $totalFamilies = Family::count();
                $totalUsers = User::count();
                $totalDocs = DocumentRequest::count();
                $totalComplaints = Complaint::count();
                $usersByRole = User::selectRaw('role, count(*) as count')->groupBy('role')->pluck('count', 'role')->toArray();
                $usersSummary = implode(', ', array_map(fn ($r, $c) => "{$r}: {$c}", array_keys($usersByRole), $usersByRole));

                $msg = "🗄️ **Ringkasan Keseluruhan Database SIWIL (Super Admin):**\n\n".
                    "• **Wilayah Administratif:** {$totalVillages} Desa/Kelurahan, {$totalRws} RW, {$totalRts} RT\n".
                    "• **Total Penduduk:** {$totalResidents} jiwa terdaftar\n".
                    "• **Total Kartu Keluarga:** {$totalFamilies} KK\n".
                    "• **Total Akun Pengguna:** {$totalUsers} akun ({$usersSummary})\n".
                    "• **Total Pengajuan Surat:** {$totalDocs} berkas\n".
                    "• **Total Pengaduan Warga:** {$totalComplaints} laporan\n\n".
                    'Sebagai Super Admin, Anda memiliki hak istimewa penuh untuk mengelola, memantau, dan mengaudit seluruh entitas database di atas tanpa batasan wilayah.';

                return [
                    'message' => $msg,
                    'options' => [
                        ['label' => 'Status Sistem & Server', 'icon' => 'search', 'next_node' => 'system_info'],
                        ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                    ],
                ];

            case 'profil_saya':
                $profile = $this->getUserProfileData($user);
                $msg = "👤 **Profil & Data Diri Anda di SIWIL:**\n\n".
                    "• **Nama Lengkap:** {$profile['name']}\n".
                    "• **NIK:** `{$profile['nik']}`\n".
                    "• **Peran / Status:** {$profile['role_label']}\n".
                    "• **Jenis Kelamin:** {$profile['gender']}\n".
                    "• **Tempat, Tanggal Lahir:** {$profile['birth_info']}\n".
                    "• **Agama:** {$profile['religion']}\n".
                    "• **Status Perkawinan:** {$profile['marital_status']}\n".
                    "• **Pendidikan Terakhir:** {$profile['education']}\n".
                    "• **Pekerjaan:** {$profile['occupation']}\n".
                    "• **Status Hubungan Keluarga:** {$profile['family_status']} ({$profile['status']})\n".
                    "• **No. Kartu Keluarga (KK):** {$profile['kk_number']}\n".
                    "• **Alamat Lengkap:** {$profile['address']}\n".
                    "• **Wilayah:** RT {$profile['rt']} / RW {$profile['rw']}, Desa {$profile['village']}\n".
                    "• **No. HP/WA:** {$profile['phone']}\n".
                    "• **Email:** {$profile['email']}\n";

                return [
                    'message' => $msg,
                    'options' => [
                        ['label' => 'Cek Status Surat/Laporan', 'icon' => 'search', 'next_node' => 'cek_status_pilih'],
                        ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                    ],
                ];

                // ========== ALUR PEMBUATAN SURAT ==========
            case 'surat_pilih_jenis':
                return [
                    'message' => 'Surat apa yang ingin Anda buat?',
                    'options' => [
                        ['label' => 'Surat Pengantar Domisili', 'icon' => 'file', 'next_node' => 'surat_konfirmasi', 'data' => ['jenis' => 'Surat Pengantar Domisili']],
                        ['label' => 'Surat Keterangan Tidak Mampu (SKTM)', 'icon' => 'file', 'next_node' => 'surat_konfirmasi', 'data' => ['jenis' => 'SKTM']],
                        ['label' => 'Surat Keterangan Usaha (SKU)', 'icon' => 'file', 'next_node' => 'surat_konfirmasi', 'data' => ['jenis' => 'SKU']],
                        ['label' => 'Kembali ke Menu Utama', 'icon' => 'back', 'next_node' => 'start'],
                    ],
                ];

            case 'surat_konfirmasi':
                $jenis = $data['jenis'] ?? 'Surat';

                return [
                    'message' => "Apakah Anda yakin ingin mengajukan **{$jenis}**? Pengajuan akan dikirim ke RT/RW Anda untuk disetujui.",
                    'options' => [
                        ['label' => 'Ya, Ajukan Sekarang', 'icon' => 'check', 'next_node' => 'surat_proses', 'data' => ['jenis' => $jenis]],
                        ['label' => 'Batal', 'icon' => 'close', 'next_node' => 'start'],
                    ],
                ];

            case 'surat_proses':
                if (! $resident) {
                    return [
                        'message' => 'Maaf, data kependudukan Anda tidak ditemukan. Harap lengkapi profil terlebih dahulu.',
                        'options' => [['label' => 'Kembali', 'icon' => 'back', 'next_node' => 'start']],
                    ];
                }

                $jenis = $data['jenis'] ?? 'Surat';

                try {
                    $service = app(DocumentRequestService::class);
                    $service->store([
                        'type' => $jenis,
                        'notes' => 'Diajukan melalui Asisten SIWIL',
                    ], $user);

                    return [
                        'message' => "✅ Pengajuan **{$jenis}** berhasil dibuat! Saat ini berstatus PENDING dan sedang menunggu persetujuan pengurus RT/RW.",
                        'options' => [
                            ['label' => 'Buat Surat Lain', 'icon' => 'document', 'next_node' => 'surat_pilih_jenis'],
                            ['label' => 'Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                        ],
                    ];
                } catch (\Exception $e) {
                    return [
                        'message' => 'Maaf, terjadi kesalahan saat membuat surat: '.$e->getMessage(),
                        'options' => [['label' => 'Kembali', 'icon' => 'back', 'next_node' => 'start']],
                    ];
                }

                // ========== ALUR LAPOR / PENGADUAN ==========
            case 'lapor_mulai':
                return [
                    'message' => "📝 Anda ingin membuat laporan pengaduan.\n\nSilakan pilih **kategori** pengaduan Anda:",
                    'options' => [
                        ['label' => 'Infrastruktur (Jalan, Jembatan, Saluran Air)', 'icon' => 'alert', 'next_node' => 'lapor_isi_judul', 'data' => ['kategori' => 'Infrastruktur']],
                        ['label' => 'Kebersihan & Lingkungan', 'icon' => 'alert', 'next_node' => 'lapor_isi_judul', 'data' => ['kategori' => 'Kebersihan & Lingkungan']],
                        ['label' => 'Keamanan & Ketertiban', 'icon' => 'alert', 'next_node' => 'lapor_isi_judul', 'data' => ['kategori' => 'Keamanan & Ketertiban']],
                        ['label' => 'Pelayanan Publik', 'icon' => 'alert', 'next_node' => 'lapor_isi_judul', 'data' => ['kategori' => 'Pelayanan Publik']],
                        ['label' => 'Lainnya', 'icon' => 'alert', 'next_node' => 'lapor_isi_judul', 'data' => ['kategori' => 'Lainnya']],
                        ['label' => 'Kembali', 'icon' => 'back', 'next_node' => 'start'],
                    ],
                ];

            case 'lapor_isi_judul':
                $kategori = $data['kategori'] ?? 'Umum';

                return [
                    'message' => "Kategori: **{$kategori}**\n\nSilakan ketik **judul singkat** pengaduan Anda di kolom pesan di bawah.\n\n_Contoh: \"Jalan rusak di depan balai desa\"_",
                    'input_mode' => 'text',
                    'input_target' => 'lapor_isi_deskripsi',
                    'input_data' => ['kategori' => $kategori],
                    'options' => [
                        ['label' => 'Batal', 'icon' => 'close', 'next_node' => 'start'],
                    ],
                ];

            case 'lapor_isi_deskripsi':
                $kategori = $data['kategori'] ?? 'Umum';
                $judul = $data['input_text'] ?? ($data['judul'] ?? '');

                if (empty($judul)) {
                    return [
                        'message' => '⚠️ Judul tidak boleh kosong. Silakan ketik judul pengaduan Anda.',
                        'input_mode' => 'text',
                        'input_target' => 'lapor_isi_deskripsi',
                        'input_data' => ['kategori' => $kategori],
                        'options' => [
                            ['label' => 'Batal', 'icon' => 'close', 'next_node' => 'start'],
                        ],
                    ];
                }

                return [
                    'message' => "Judul: **{$judul}**\n\nSekarang ketik **deskripsi detail** pengaduan Anda di kolom pesan di bawah.\n\n_Jelaskan lokasi, kondisi, dan dampak yang terjadi._",
                    'input_mode' => 'text',
                    'input_target' => 'lapor_konfirmasi',
                    'input_data' => ['kategori' => $kategori, 'judul' => $judul],
                    'options' => [
                        ['label' => 'Batal', 'icon' => 'close', 'next_node' => 'start'],
                    ],
                ];

            case 'lapor_konfirmasi':
                $kategori = $data['kategori'] ?? 'Umum';
                $judul = $data['judul'] ?? '';
                $deskripsi = $data['input_text'] ?? ($data['deskripsi'] ?? '');

                if (empty($deskripsi)) {
                    return [
                        'message' => '⚠️ Deskripsi tidak boleh kosong. Silakan ketik deskripsi pengaduan Anda.',
                        'input_mode' => 'text',
                        'input_target' => 'lapor_konfirmasi',
                        'input_data' => ['kategori' => $kategori, 'judul' => $judul],
                        'options' => [
                            ['label' => 'Batal', 'icon' => 'close', 'next_node' => 'start'],
                        ],
                    ];
                }

                return [
                    'message' => "📋 **Ringkasan Pengaduan:**\n\n• Kategori: **{$kategori}**\n• Judul: **{$judul}**\n• Deskripsi: {$deskripsi}\n\nApakah Anda yakin ingin mengirim pengaduan ini?",
                    'options' => [
                        ['label' => 'Ya, Kirim Pengaduan', 'icon' => 'check', 'next_node' => 'lapor_proses', 'data' => ['kategori' => $kategori, 'judul' => $judul, 'deskripsi' => $deskripsi]],
                        ['label' => 'Batal', 'icon' => 'close', 'next_node' => 'start'],
                    ],
                ];

            case 'lapor_proses':
                $judul = $data['judul'] ?? '';
                $deskripsi = $data['deskripsi'] ?? '';
                $kategori = $data['kategori'] ?? 'Umum';

                if (empty($judul) || empty($deskripsi)) {
                    return [
                        'message' => '⚠️ Data pengaduan tidak lengkap. Silakan ulangi proses.',
                        'options' => [['label' => 'Ulangi', 'icon' => 'refresh', 'next_node' => 'lapor_mulai']],
                    ];
                }

                try {
                    Complaint::create([
                        'user_id' => $user->id,
                        'title' => "[{$kategori}] {$judul}",
                        'description' => $deskripsi,
                        'status' => 'PENDING',
                        'rt_id' => $resident->rt_id ?? null,
                        'rw_id' => $resident->rw_id ?? null,
                        'village_id' => $resident->village_id ?? null,
                    ]);

                    return [
                        'message' => "✅ Pengaduan Anda berhasil dikirim!\n\n• Judul: **[{$kategori}] {$judul}**\n• Status: **PENDING**\n\nPengaduan akan ditindaklanjuti oleh pengurus RT/RW.",
                        'options' => [
                            ['label' => 'Buat Pengaduan Lain', 'icon' => 'alert', 'next_node' => 'lapor_mulai'],
                            ['label' => 'Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                        ],
                    ];
                } catch (\Exception $e) {
                    return [
                        'message' => 'Maaf, terjadi kesalahan saat mengirim pengaduan: '.$e->getMessage(),
                        'options' => [['label' => 'Kembali', 'icon' => 'back', 'next_node' => 'start']],
                    ];
                }

                // ========== ALUR INFO KONTAK ==========
            case 'info_kontak':
                $rt = null;
                if ($user->rt_id) {
                    $rt = Rt::with('rw.village')->find($user->rt_id);
                } elseif ($resident) {
                    $rt = $resident->rt ?? ($resident->family->rt ?? null);
                    if ($rt && ! $rt->relationLoaded('rw')) {
                        $rt->load('rw.village');
                    }
                }

                $msg = "Berikut adalah daftar kontak resmi pengurus wilayah Anda:\n\n";
                if ($rt) {
                    $ketuaRt = User::with('resident')->where('role', 'RT')->where('rt_id', $rt->id)->first();
                    if ($ketuaRt) {
                        $namaRt = $ketuaRt->resident->full_name ?? $ketuaRt->name ?? $ketuaRt->username;
                        $hpRt = $ketuaRt->phone_number ?? 'Belum ada No HP';
                        $emailRt = $ketuaRt->email ?? '-';
                        $msg .= "1. 🏠 **Ketua RT {$rt->number}:** {$namaRt}\n   • WA/Telp: `{$hpRt}` | Email: {$emailRt}\n\n";
                    } else {
                        $msg .= "1. 🏠 **Ketua RT {$rt->number}:** Belum ada pengurus terdaftar.\n\n";
                    }

                    if ($rt->rw) {
                        $ketuaRw = User::with('resident')->where('role', 'RW')->where('rw_id', $rt->rw->id)->first();
                        if ($ketuaRw) {
                            $namaRw = $ketuaRw->resident->full_name ?? $ketuaRw->name ?? $ketuaRw->username;
                            $hpRw = $ketuaRw->phone_number ?? 'Belum ada No HP';
                            $emailRw = $ketuaRw->email ?? '-';
                            $msg .= "2. 🏛️ **Ketua RW {$rt->rw->number}:** {$namaRw}\n   • WA/Telp: `{$hpRw}` | Email: {$emailRw}\n\n";
                        } else {
                            $msg .= "2. 🏛️ **Ketua RW {$rt->rw->number}:** Belum ada pengurus terdaftar.\n\n";
                        }

                        if ($rt->rw->village) {
                            $desa = $rt->rw->village;
                            $desaLabel = str_starts_with(strtolower(trim($desa->name)), 'desa ') ? trim($desa->name) : 'Desa '.trim($desa->name);
                            $adminDesa = User::where('role', 'ADMIN_DESA')->where('village_id', $desa->id)->first();
                            $desaEmail = $adminDesa->email ?? 'desa@siwil.id';
                            $desaHp = $adminDesa->phone_number ?? '-';
                            $msg .= "3. 🏢 **Kantor {$desaLabel}:**\n   • WA/Telp: `{$desaHp}` | Email: {$desaEmail}\n";
                        }
                    }
                } else {
                    $msg .= "Wilayah RT Anda belum terdata. Harap lengkapi profil kependudukan Anda.\n";
                }

                return [
                    'message' => trim($msg),
                    'options' => [
                        ['label' => 'Buat Surat Pengantar', 'icon' => 'document', 'next_node' => 'surat_pilih_jenis'],
                        ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                    ],
                ];

                // ========== ALUR CEK STATUS ==========
            case 'cek_status_pilih':
                return [
                    'message' => 'Apa yang ingin Anda cek?',
                    'options' => [
                        ['label' => 'Status Surat Saya', 'icon' => 'document', 'next_node' => 'cek_status_surat'],
                        ['label' => 'Status Pengaduan Saya', 'icon' => 'alert', 'next_node' => 'cek_status_pengaduan'],
                        ['label' => 'Kembali', 'icon' => 'back', 'next_node' => 'start'],
                    ],
                ];

            case 'cek_status_surat':
                if (! $resident) {
                    return ['message' => 'Data warga tidak ditemukan.', 'options' => [['label' => 'Kembali', 'icon' => 'back', 'next_node' => 'start']]];
                }
                $docs = DocumentRequest::where('resident_id', $resident->id)->latest()->take(3)->get();
                if ($docs->isEmpty()) {
                    $msg = 'Anda belum pernah mengajukan surat.';
                } else {
                    $msg = "Berikut status 3 pengajuan surat terakhir Anda:\n";
                    foreach ($docs as $doc) {
                        $msg .= "• **{$doc->type}** - Status: `{$doc->status}`\n";
                    }
                }

                return [
                    'message' => $msg,
                    'options' => [['label' => 'Kembali', 'icon' => 'back', 'next_node' => 'start']],
                ];

            case 'cek_status_pengaduan':
                $complaints = Complaint::where('user_id', $user->id)->latest()->take(5)->get();
                if ($complaints->isEmpty()) {
                    $msg = 'Anda belum pernah membuat pengaduan.';
                } else {
                    $msg = "Berikut status pengaduan terakhir Anda:\n\n";
                    foreach ($complaints as $complaint) {
                        $statusIcon = match ($complaint->status) {
                            'PENDING' => '🟡',
                            'PROCESSING' => '🔵',
                            'RESOLVED' => '🟢',
                            'REJECTED' => '🔴',
                            default => '⚪',
                        };
                        $msg .= "• {$statusIcon} **{$complaint->title}**\n  Status: `{$complaint->status}` — {$complaint->created_at->format('d M Y')}\n\n";
                    }
                }

                return [
                    'message' => $msg,
                    'options' => [
                        ['label' => 'Buat Pengaduan Baru', 'icon' => 'alert', 'next_node' => 'lapor_mulai'],
                        ['label' => 'Kembali', 'icon' => 'back', 'next_node' => 'start'],
                    ],
                ];

                // ========== ALUR MUSYAWARAH / POLLING ==========
            case 'musyawarah_list':
                $activePolls = Poll::with(['options.votes', 'creator'])
                    ->where('status', 'ACTIVE')
                    ->where(function ($query) {
                        $query->whereNull('expires_at')
                            ->orWhere('expires_at', '>', now());
                    })
                    ->latest()
                    ->take(5)
                    ->get();

                if ($activePolls->isEmpty()) {
                    return [
                        'message' => '📊 Saat ini **tidak ada musyawarah/polling aktif**. Silakan cek kembali nanti.',
                        'options' => [
                            ['label' => 'Kembali ke Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                        ],
                    ];
                }

                $msg = "📊 Berikut daftar **musyawarah/polling aktif** saat ini:\n\n";
                $options = [];
                foreach ($activePolls as $index => $poll) {
                    $totalVotes = $poll->votes->count();
                    $optionCount = $poll->options->count();
                    $batas = $poll->expires_at ? $poll->expires_at->format('d M Y') : 'Tanpa Batas';

                    $userVoted = $poll->votes->where('user_id', $user->id)->first();
                    $statusLabel = $userVoted ? '✅ Sudah vote' : '🗳️ Belum vote';

                    $msg .= ($index + 1).". **{$poll->title}**\n";
                    $msg .= "   {$optionCount} opsi • {$totalVotes} suara • Batas: {$batas}\n";
                    $msg .= "   {$statusLabel}\n\n";

                    $options[] = [
                        'label' => $poll->title,
                        'icon' => 'vote',
                        'next_node' => 'musyawarah_detail',
                        'data' => ['poll_id' => $poll->id],
                    ];
                }

                $options[] = ['label' => 'Kembali ke Menu Utama', 'icon' => 'back', 'next_node' => 'start'];

                return [
                    'message' => $msg,
                    'options' => $options,
                ];

            case 'musyawarah_detail':
                $pollId = $data['poll_id'] ?? null;
                if (! $pollId) {
                    return [
                        'message' => '⚠️ Polling tidak ditemukan.',
                        'options' => [['label' => 'Kembali', 'icon' => 'back', 'next_node' => 'musyawarah_list']],
                    ];
                }

                $poll = Poll::with(['options.votes', 'creator'])->find($pollId);
                if (! $poll) {
                    return [
                        'message' => '⚠️ Polling tidak ditemukan.',
                        'options' => [['label' => 'Kembali', 'icon' => 'back', 'next_node' => 'musyawarah_list']],
                    ];
                }

                $isExpired = $poll->expires_at && $poll->expires_at->isPast();
                $isActive = $poll->status === 'ACTIVE' && ! $isExpired;
                $totalVotes = $poll->votes->count();
                $userVote = $poll->votes->where('user_id', $user->id)->first();

                $msg = "📊 **{$poll->title}**\n\n";
                if ($poll->description) {
                    $msg .= "{$poll->description}\n\n";
                }
                $msg .= 'Dibuat oleh: **'.($poll->creator->name ?? '-')."**\n";
                $msg .= 'Batas waktu: **'.($poll->expires_at ? $poll->expires_at->format('d M Y') : 'Tanpa Batas')."**\n";
                $msg .= "Total suara: **{$totalVotes}**\n";
                $msg .= 'Status: **'.($isActive ? 'AKTIF' : 'DITUTUP')."**\n\n";

                // Show results with vote counts
                $msg .= "**Hasil Sementara:**\n";
                foreach ($poll->options as $option) {
                    $voteCount = $option->votes->count();
                    $percent = $totalVotes > 0 ? round(($voteCount / $totalVotes) * 100) : 0;
                    $isSelected = $userVote && $userVote->poll_option_id === $option->id;
                    $marker = $isSelected ? ' ← Pilihan Anda' : '';
                    $bar = str_repeat('█', (int) ($percent / 10)).str_repeat('░', 10 - (int) ($percent / 10));
                    $msg .= "• {$option->text}: {$bar} {$percent}% ({$voteCount}){$marker}\n";
                }

                $options = [];

                if ($isActive && ! $userVote) {
                    $msg .= "\n🗳️ Anda **belum memberikan suara**. Pilih opsi di bawah untuk vote:";
                    foreach ($poll->options as $option) {
                        $options[] = [
                            'label' => "Vote: {$option->text}",
                            'icon' => 'check',
                            'next_node' => 'musyawarah_vote',
                            'data' => ['poll_id' => $poll->id, 'option_id' => $option->id, 'option_text' => $option->text],
                        ];
                    }
                } elseif ($userVote) {
                    $votedOption = $poll->options->where('id', $userVote->poll_option_id)->first();
                    $msg .= "\n✅ Anda sudah memilih: **".($votedOption->text ?? '-').'**';
                } else {
                    $msg .= "\n🔒 Polling ini sudah ditutup.";
                }

                $options[] = ['label' => 'Lihat Polling Lain', 'icon' => 'vote', 'next_node' => 'musyawarah_list'];
                $options[] = ['label' => 'Menu Utama', 'icon' => 'home', 'next_node' => 'start'];

                return [
                    'message' => $msg,
                    'options' => $options,
                ];

            case 'musyawarah_vote':
                $pollId = $data['poll_id'] ?? null;
                $optionId = $data['option_id'] ?? null;
                $optionText = $data['option_text'] ?? '';

                if (! $pollId || ! $optionId) {
                    return [
                        'message' => '⚠️ Data voting tidak valid.',
                        'options' => [['label' => 'Kembali', 'icon' => 'back', 'next_node' => 'musyawarah_list']],
                    ];
                }

                $poll = Poll::with('options.votes')->find($pollId);
                if (! $poll) {
                    return [
                        'message' => '⚠️ Polling tidak ditemukan.',
                        'options' => [['label' => 'Kembali', 'icon' => 'back', 'next_node' => 'musyawarah_list']],
                    ];
                }

                $isExpired = $poll->expires_at && $poll->expires_at->isPast();
                if ($poll->status !== 'ACTIVE' || $isExpired) {
                    return [
                        'message' => '🔒 Polling ini sudah **ditutup**. Anda tidak bisa memberikan suara.',
                        'options' => [['label' => 'Kembali', 'icon' => 'back', 'next_node' => 'musyawarah_list']],
                    ];
                }

                if ($poll->votes()->where('user_id', $user->id)->exists()) {
                    return [
                        'message' => '⚠️ Anda sudah memberikan suara pada polling ini sebelumnya.',
                        'options' => [
                            ['label' => 'Lihat Detail', 'icon' => 'search', 'next_node' => 'musyawarah_detail', 'data' => ['poll_id' => $pollId]],
                            ['label' => 'Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                        ],
                    ];
                }

                // Verify option belongs to this poll
                $validOption = $poll->options->where('id', $optionId)->first();
                if (! $validOption) {
                    return [
                        'message' => '⚠️ Opsi yang dipilih tidak valid.',
                        'options' => [['label' => 'Kembali', 'icon' => 'back', 'next_node' => 'musyawarah_detail', 'data' => ['poll_id' => $pollId]]],
                    ];
                }

                return [
                    'message' => "Anda akan memilih: **{$optionText}**\n\nPada polling: **{$poll->title}**\n\n⚠️ Suara tidak dapat diubah setelah dikirim. Apakah Anda yakin?",
                    'options' => [
                        ['label' => 'Ya, Kirim Suara Saya', 'icon' => 'check', 'next_node' => 'musyawarah_vote_proses', 'data' => ['poll_id' => $pollId, 'option_id' => $optionId, 'option_text' => $optionText]],
                        ['label' => 'Batal, Pilih Opsi Lain', 'icon' => 'back', 'next_node' => 'musyawarah_detail', 'data' => ['poll_id' => $pollId]],
                    ],
                ];

            case 'musyawarah_vote_proses':
                $pollId = $data['poll_id'] ?? null;
                $optionId = $data['option_id'] ?? null;

                if (! $pollId || ! $optionId) {
                    return [
                        'message' => '⚠️ Data voting tidak valid.',
                        'options' => [['label' => 'Kembali', 'icon' => 'back', 'next_node' => 'musyawarah_list']],
                    ];
                }

                $poll = Poll::with('options.votes')->find($pollId);
                if (! $poll) {
                    return [
                        'message' => '⚠️ Polling tidak ditemukan.',
                        'options' => [['label' => 'Kembali', 'icon' => 'back', 'next_node' => 'musyawarah_list']],
                    ];
                }

                $isExpired = $poll->expires_at && $poll->expires_at->isPast();
                if ($poll->status !== 'ACTIVE' || $isExpired) {
                    return [
                        'message' => '🔒 Polling ini sudah **ditutup**.',
                        'options' => [['label' => 'Kembali', 'icon' => 'back', 'next_node' => 'musyawarah_list']],
                    ];
                }

                if ($poll->votes()->where('user_id', $user->id)->exists()) {
                    return [
                        'message' => '⚠️ Anda sudah memberikan suara sebelumnya.',
                        'options' => [
                            ['label' => 'Lihat Hasil', 'icon' => 'search', 'next_node' => 'musyawarah_detail', 'data' => ['poll_id' => $pollId]],
                            ['label' => 'Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                        ],
                    ];
                }

                try {
                    PollVote::create([
                        'poll_id' => $pollId,
                        'poll_option_id' => $optionId,
                        'user_id' => $user->id,
                    ]);

                    // Reload to get updated counts
                    $poll->load('options.votes');
                    $totalVotes = $poll->votes->count();
                    $votedOption = $poll->options->where('id', $optionId)->first();

                    $msg = "✅ **Suara Anda berhasil dikirim!**\n\n";
                    $msg .= "Anda memilih: **{$votedOption->text}**\n\n";
                    $msg .= "**Hasil terkini:**\n";
                    foreach ($poll->options as $option) {
                        $voteCount = $option->votes->count();
                        $percent = $totalVotes > 0 ? round(($voteCount / $totalVotes) * 100) : 0;
                        $isSelected = $option->id === $optionId;
                        $marker = $isSelected ? ' ← Pilihan Anda' : '';
                        $bar = str_repeat('█', (int) ($percent / 10)).str_repeat('░', 10 - (int) ($percent / 10));
                        $msg .= "• {$option->text}: {$bar} {$percent}% ({$voteCount}){$marker}\n";
                    }

                    return [
                        'message' => $msg,
                        'options' => [
                            ['label' => 'Lihat Polling Lain', 'icon' => 'vote', 'next_node' => 'musyawarah_list'],
                            ['label' => 'Menu Utama', 'icon' => 'home', 'next_node' => 'start'],
                        ],
                    ];
                } catch (\Exception $e) {
                    return [
                        'message' => 'Maaf, terjadi kesalahan saat mengirim suara: '.$e->getMessage(),
                        'options' => [['label' => 'Kembali', 'icon' => 'back', 'next_node' => 'musyawarah_list']],
                    ];
                }

            default:
                return [
                    'message' => 'Mohon maaf, pilihan layanan tersebut tidak tersedia atau belum dapat diproses.',
                    'options' => [['label' => 'Kembali ke Menu Awal', 'icon' => 'home', 'next_node' => 'start']],
                ];
        }
    }
}
