<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\DocumentRequest;
use App\Models\Family;
use App\Models\Resident;
use App\Models\Rt;
use App\Models\Rw;
use App\Models\User;
use App\Models\Village;
use App\Services\AiToolService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiToolServiceTest extends TestCase
{
    use RefreshDatabase;

    private AiToolService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(AiToolService::class);
    }

    public function test_dynamic_system_prompt_injects_user_name_role_and_rbac_boundaries(): void
    {
        $village = Village::create(['name' => 'Sukamaju', 'code' => 'SKM01']);
        $rw = Rw::create(['number' => '01', 'village_id' => $village->id]);
        $rt = Rt::create(['number' => '01', 'rw_id' => $rw->id]);

        $wargaUser = User::factory()->create([
            'name' => 'Budi Santoso',
            'username' => '3201010101990001',
            'role' => 'WARGA',
            'rt_id' => $rt->id,
            'rw_id' => $rw->id,
            'village_id' => $village->id,
        ]);

        $prompt = $this->service->buildDynamicSystemPrompt($wargaUser);

        $this->assertStringContainsString('Budi Santoso', $prompt);
        $this->assertStringContainsString('3201010101990001', $prompt);
        $this->assertStringContainsString('WARGA', $prompt);
        $this->assertStringContainsString('BATASAN HAK AKSES DATA & KEWENANGAN (RBAC SYSTEM)', $prompt);
        $this->assertStringContainsString('UU PDP', $prompt);
        $this->assertStringContainsString('FUNCTION CALLING / AI TOOLS', $prompt);
    }

    public function test_warga_cannot_fetch_other_residents_directory(): void
    {
        $village = Village::create(['name' => 'Sukamaju', 'code' => 'SKM01']);
        $rw = Rw::create(['number' => '01', 'village_id' => $village->id]);
        $rt = Rt::create(['number' => '01', 'rw_id' => $rw->id]);

        $wargaUser = User::factory()->create(['role' => 'WARGA']);

        // Coba panggil tool fetch_residents_and_areas sebagai warga
        $result = $this->service->executeTool('fetch_residents_and_areas', [], $wargaUser);

        $this->assertEquals('DENIED', $result['status'] ?? null);
        $this->assertStringContainsString('Akses Ditolak', $result['error'] ?? '');
    }

    public function test_warga_cannot_fetch_audit_logs(): void
    {
        $wargaUser = User::factory()->create(['role' => 'WARGA']);

        $result = $this->service->executeTool('fetch_audit_logs', [], $wargaUser);

        $this->assertEquals('DENIED', $result['status'] ?? null);
        $this->assertStringContainsString('Akses Ditolak', $result['error'] ?? '');
    }

    public function test_rt_can_fetch_residents_only_in_own_rt(): void
    {
        $village = Village::create(['name' => 'Sukamaju', 'code' => 'SKM01']);
        $rw = Rw::create(['number' => '01', 'village_id' => $village->id]);
        $rt1 = Rt::create(['number' => '01', 'rw_id' => $rw->id]);
        $rt2 = Rt::create(['number' => '02', 'rw_id' => $rw->id]);

        $rtUser = User::factory()->create([
            'role' => 'RT',
            'rt_id' => $rt1->id,
            'rw_id' => $rw->id,
            'village_id' => $village->id,
        ]);

        Resident::create([
            'nik' => '3201010000000001',
            'full_name' => 'Warga RT 1',
            'gender' => 'LAKI_LAKI',
            'status' => 'KEPALA_KELUARGA',
            'rt_id' => $rt1->id,
        ]);

        Resident::create([
            'nik' => '3201010000000002',
            'full_name' => 'Warga RT 2',
            'gender' => 'PEREMPUAN',
            'status' => 'KEPALA_KELUARGA',
            'rt_id' => $rt2->id,
        ]);

        // RT 1 fetch warganya
        $result = $this->service->executeTool('fetch_residents_and_areas', [], $rtUser);
        $this->assertEquals(1, $result['total_ditemukan']);
        $this->assertEquals('Warga RT 1', $result['daftar_warga'][0]['nama_lengkap']);

        // RT 1 mencoba query data RT 2 secara eksplisit -> harus DENIED
        $deniedResult = $this->service->executeTool('fetch_residents_and_areas', ['rt_number' => '02'], $rtUser);
        $this->assertEquals('DENIED', $deniedResult['status'] ?? null);
    }

    public function test_warga_fetches_only_their_own_documents(): void
    {
        $village = Village::create(['name' => 'Sukamaju', 'code' => 'SKM01']);
        $rw = Rw::create(['number' => '01', 'village_id' => $village->id]);
        $rt = Rt::create(['number' => '01', 'rw_id' => $rw->id]);

        $res1 = Resident::create([
            'nik' => '3201010000000011',
            'full_name' => 'Pemohon Asli',
            'gender' => 'LAKI_LAKI',
            'status' => 'KEPALA_KELUARGA',
            'rt_id' => $rt->id,
        ]);

        $wargaUser = User::factory()->create([
            'username' => '3201010000000011',
            'role' => 'WARGA',
            'rt_id' => $rt->id,
        ]);

        $res2 = Resident::create([
            'nik' => '3201010000000012',
            'full_name' => 'Warga Lain',
            'gender' => 'PEREMPUAN',
            'status' => 'KEPALA_KELUARGA',
            'rt_id' => $rt->id,
        ]);

        DocumentRequest::create([
            'request_number' => 'REQ-001',
            'resident_id' => $res1->id,
            'type' => 'SKTM',
            'status' => 'PENDING',
            'notes' => 'Surat Bantuan',
        ]);

        DocumentRequest::create([
            'request_number' => 'REQ-002',
            'resident_id' => $res2->id,
            'type' => 'DOMISILI',
            'status' => 'PENDING',
            'notes' => 'Surat Pindah',
        ]);

        $result = $this->service->executeTool('fetch_document_requests', [], $wargaUser);
        $this->assertEquals(1, $result['total']);
        $this->assertEquals('SKTM', $result['daftar_surat'][0]['jenis_surat']);
    }

    public function test_super_admin_has_full_access_to_tools(): void
    {
        $superAdmin = User::factory()->create(['role' => 'SUPER_ADMIN']);

        Announcement::create([
            'author_id' => $superAdmin->id,
            'title' => 'Kerja Bakti',
            'content' => 'Minggu pagi jam 7',
            'is_active' => true,
        ]);

        $dashboard = $this->service->executeTool('fetch_dashboard_data', [], $superAdmin);
        $this->assertEquals('SUPER_ADMIN', $dashboard['role']);

        $announcements = $this->service->executeTool('fetch_announcements', [], $superAdmin);
        $this->assertGreaterThanOrEqual(1, $announcements['total']);
    }

    public function test_chatbot_endpoint_accepts_and_processes_message(): void
    {
        $user = User::factory()->create(['role' => 'WARGA']);

        $response = $this->actingAs($user)->postJson(route('ai.chat'), [
            'message' => 'Halo, apa kabar?',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['message', 'options']);
    }

    public function test_warga_chat_queries_do_not_crash_with_family_relationship(): void
    {
        $village = Village::create(['name' => 'Sukamaju', 'code' => 'SKM01']);
        $rw = Rw::create(['number' => '01', 'village_id' => $village->id]);
        $rt = Rt::create(['number' => '01', 'rw_id' => $rw->id]);

        $family = Family::create([
            'kk_number' => '3201010000000099',
            'address' => 'Jl. Mawar No. 5',
            'rt_id' => $rt->id,
        ]);

        $resident = Resident::create([
            'nik' => '3201010000000088',
            'full_name' => 'Siti Aminah',
            'gender' => 'PEREMPUAN',
            'status' => 'ISTRI',
            'family_id' => $family->id,
            'rt_id' => $rt->id,
        ]);

        $user = User::factory()->create([
            'username' => '3201010000000088',
            'role' => 'WARGA',
            'rt_id' => $rt->id,
            'rw_id' => $rw->id,
            'village_id' => $village->id,
        ]);

        // Uji query 'cek pengajuan surat'
        $resp1 = $this->actingAs($user)->postJson(route('ai.chat'), [
            'message' => 'cek pengajuan surat',
        ]);
        $resp1->assertStatus(200);
        $resp1->assertJsonStructure(['message', 'options']);

        // Uji query 'siapa rt saya'
        $resp2 = $this->actingAs($user)->postJson(route('ai.chat'), [
            'message' => 'siapa rt saya',
        ]);
        $resp2->assertStatus(200);
        $resp2->assertJsonStructure(['message', 'options']);
    }
}
