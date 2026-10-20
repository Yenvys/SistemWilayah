<?php

namespace Tests\Feature;

use App\Models\DocumentRequest;
use App\Models\Resident;
use App\Models\Rt;
use App\Models\Rw;
use App\Models\User;
use App\Models\Village;
use App\Services\DocumentRequestService;
use App\Services\TelegramService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TelegramDocumentNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.telegram.bot_token' => 'test-token-123']);
    }

    public function test_telegram_notification_sent_when_document_status_updated(): void
    {
        Http::fake([
            'https://api.telegram.org/*' => Http::response(['ok' => true], 200),
        ]);

        $village = Village::create(['id' => (string) \Illuminate\Support\Str::uuid(), 'name' => 'Sukamaju', 'code' => 'SKM']);
        $rw = Rw::create(['id' => (string) \Illuminate\Support\Str::uuid(), 'number' => '01', 'village_id' => $village->id]);
        $rt = Rt::create(['id' => (string) \Illuminate\Support\Str::uuid(), 'number' => '05', 'rw_id' => $rw->id]);

        $citizen = User::factory()->create([
            'username' => '3201123456789001',
            'role' => 'WARGA',
            'telegram_id' => '123456789',
        ]);

        $resident = Resident::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'nik' => $citizen->username,
            'full_name' => 'Ahmad Warga',
            'rt_id' => $rt->id,
        ]);

        $rtOfficer = User::factory()->create([
            'name' => 'Pak RT Budi',
            'role' => 'RT',
            'rt_id' => $rt->id,
        ]);

        $docService = app(DocumentRequestService::class);

        // 1. Create document
        $doc = $docService->store([
            'type' => 'SURAT_KETERANGAN_DOMISILI',
            'notes' => 'Untuk keperluan perbankan',
            'nik' => $citizen->username,
        ], $citizen);

        $this->assertEquals('DIAJUKAN_RT', $doc->status);

        // 2. Update status to DIAJUKAN_RW (approved by RT)
        $docService->updateStatus($doc, [
            'status' => 'DIAJUKAN_RW',
            'notes' => 'Data kependudukan telah diverifikasi RT',
        ], $rtOfficer);

        $this->assertEquals('DIAJUKAN_RW', $doc->fresh()->status);

        // Assert Telegram API was called for the citizen telegram_id
        Http::assertSent(function ($request) use ($doc) {
            $data = $request->data();
            return str_contains($request->url(), 'sendMessage')
                && ($data['chat_id'] ?? null) === '123456789'
                && str_contains($data['text'] ?? '', $doc->request_number)
                && str_contains($data['text'] ?? '', 'Disetujui RT (Diteruskan ke RW)');
        });
    }

    public function test_telegram_notification_sent_when_document_rejected(): void
    {
        Http::fake([
            'https://api.telegram.org/*' => Http::response(['ok' => true], 200),
        ]);

        $citizen = User::factory()->create([
            'username' => '3201123456789002',
            'role' => 'WARGA',
            'telegram_id' => '987654321',
        ]);

        $resident = Resident::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'nik' => $citizen->username,
            'full_name' => 'Siti Aminah',
        ]);

        $officer = User::factory()->create([
            'name' => 'Pak RW Handoko',
            'role' => 'RW',
        ]);

        $doc = DocumentRequest::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'request_number' => 'SRT-20261016-TEST',
            'type' => 'SURAT_KETERANGAN_USAHA',
            'resident_id' => $resident->id,
            'status' => 'DIAJUKAN_RW',
        ]);

        $docService = app(DocumentRequestService::class);
        $docService->updateStatus($doc, [
            'status' => 'DITOLAK',
            'notes' => 'Foto usaha belum dilampirkan',
        ], $officer);

        Http::assertSent(function ($request) use ($doc) {
            $data = $request->data();
            return str_contains($request->url(), 'sendMessage')
                && ($data['chat_id'] ?? null) === '987654321'
                && str_contains($data['text'] ?? '', 'Pengajuan Surat Ditolak')
                && str_contains($data['text'] ?? '', 'Foto usaha belum dilampirkan');
        });
    }
}
