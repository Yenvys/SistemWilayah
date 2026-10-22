<?php

namespace Tests\Feature;

use App\Models\AppNotification;
use App\Models\AuditLog;
use App\Models\Budget;
use App\Models\Complaint;
use App\Models\Development;
use App\Models\DocumentRequest;
use App\Models\Family;
use App\Models\Resident;
use App\Models\Rt;
use App\Models\Rw;
use App\Models\User;
use App\Models\Village;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiChatTest extends TestCase
{
    use RefreshDatabase;

    private Village $village;

    private Rw $rw;

    private Rt $rt;

    private Rt $rt2;

    private User $wargaUser;

    private User $rtUser;

    private User $rwUser;

    private User $desaUser;

    private User $superAdminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->village = Village::create(['name' => 'Desa Sukamaju']);
        $this->rw = Rw::create(['number' => '01', 'village_id' => $this->village->id]);
        $this->rt = Rt::create(['number' => '02', 'rw_id' => $this->rw->id]);
        $this->rt2 = Rt::create(['number' => '03', 'rw_id' => $this->rw->id]);

        $this->wargaUser = User::create([
            'name' => 'Warga Test',
            'username' => '3201123456789001',
            'email' => 'warga@test.com',
            'phone_number' => '085511223344',
            'password' => bcrypt('password'),
            'role' => 'WARGA',
        ]);

        Resident::create([
            'nik' => $this->wargaUser->username,
            'full_name' => 'Warga Test',
            'rt_id' => $this->rt->id,
            'status' => 'AKTIF',
        ]);

        $this->rtUser = User::create([
            'name' => 'Ketua RT',
            'username' => 'rt02',
            'email' => 'rt@test.com',
            'phone_number' => '081234567890',
            'password' => bcrypt('password'),
            'role' => 'RT',
            'rt_id' => $this->rt->id,
        ]);

        $this->rwUser = User::create([
            'name' => 'Ketua RW',
            'username' => 'rw01',
            'email' => 'rw@test.com',
            'phone_number' => '089876543210',
            'password' => bcrypt('password'),
            'role' => 'RW',
            'rw_id' => $this->rw->id,
        ]);

        $this->desaUser = User::create([
            'name' => 'Admin Desa',
            'username' => 'admindesa',
            'email' => 'desa@test.com',
            'phone_number' => '087711223344',
            'password' => bcrypt('password'),
            'role' => 'ADMIN_DESA',
            'village_id' => $this->village->id,
        ]);

        $this->superAdminUser = User::create([
            'name' => 'Super Administrator',
            'username' => 'superadmin',
            'email' => 'superadmin@test.com',
            'phone_number' => '081122334455',
            'password' => bcrypt('password'),
            'role' => 'SUPER_ADMIN',
        ]);
    }

    public function test_warga_cannot_access_other_residents_contacts(): void
    {
        $response = $this->actingAs($this->wargaUser)->postJson(route('ai.chat'), [
            'message' => 'Tolong berikan nomor telepon atau kontak warga lain di RT saya',
        ]);

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertStringContainsString('Perlindungan Data Pribadi', $data['message']);
        $this->assertStringContainsString('tidak memiliki izin', $data['message']);
    }

    public function test_warga_cannot_access_resident_count_or_census(): void
    {
        $response = $this->actingAs($this->wargaUser)->postJson(route('ai.chat'), [
            'message' => 'Berapa warganya di RT saya sekarang?',
        ]);

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertStringContainsString('tidak memiliki hak akses untuk melihat statistik jumlah penduduk', $data['message']);
    }

    public function test_warga_cannot_access_rt_rw_desa_database_or_budget(): void
    {
        $response = $this->actingAs($this->wargaUser)->postJson(route('ai.chat'), [
            'message' => 'Tampilkan data anggaran desa dan database desa',
        ]);

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertStringContainsString('tidak memiliki akses ke database internal', $data['message']);
    }

    public function test_warga_can_access_official_rt_rw_contacts(): void
    {
        $response = $this->actingAs($this->wargaUser)->postJson(route('ai.chat'), [
            'node' => 'info_kontak',
        ]);

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertStringContainsString('Ketua RT 02', $data['message']);
        $this->assertStringContainsString('081234567890', $data['message']);
        $this->assertStringContainsString('Ketua RW 01', $data['message']);
        $this->assertStringContainsString('089876543210', $data['message']);
        $this->assertStringContainsString('Kantor Desa Sukamaju', $data['message']);
    }

    public function test_warga_can_ask_about_available_services(): void
    {
        $response = $this->actingAs($this->wargaUser)->postJson(route('ai.chat'), [
            'message' => 'Apa saja layanan yang tersedia untuk warga?',
        ]);

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertStringContainsString('Pengajuan Surat Mandiri', $data['message']);
        $this->assertStringContainsString('Pengaduan Warga', $data['message']);
        $this->assertStringContainsString('Musyawarah & Polling', $data['message']);
    }

    public function test_rt_can_ask_resident_count_in_own_rt(): void
    {
        $response = $this->actingAs($this->rtUser)->postJson(route('ai.chat'), [
            'message' => 'Berapa warga saya saat ini?',
        ]);

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertStringContainsString('RT 02', $data['message']);
        $this->assertStringContainsString('1 jiwa', $data['message']);
    }

    public function test_rt_can_ask_warga_nik_and_contacts_in_own_rt(): void
    {
        $response = $this->actingAs($this->rtUser)->postJson(route('ai.chat'), [
            'message' => 'Bisa minta daftar NIK dan kontak warga di RT saya?',
        ]);

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertStringContainsString('3201123456789001', $data['message']);
        $this->assertStringContainsString('Warga Test', $data['message']);
        $this->assertStringContainsString('085511223344', $data['message']);
    }

    public function test_rt_cannot_access_warga_in_other_rts(): void
    {
        $response = $this->actingAs($this->rtUser)->postJson(route('ai.chat'), [
            'message' => 'Tampilkan data dan kontak warga di RT 03',
        ]);

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertStringContainsString('tidak memiliki izin untuk mengakses data warga atau statistik di RT 03', $data['message']);
    }

    public function test_rt_cannot_access_rw_or_desa_database(): void
    {
        $response = $this->actingAs($this->rtUser)->postJson(route('ai.chat'), [
            'message' => 'Tampilkan database desa dan APBDes desa',
        ]);

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertStringContainsString('terbatas hanya untuk wilayah RT Anda sendiri', $data['message']);
    }

    public function test_rw_can_ask_resident_count_and_rt_heads_nik_contacts(): void
    {
        $response = $this->actingAs($this->rwUser)->postJson(route('ai.chat'), [
            'message' => 'Berapa warga di RW saya?',
        ]);

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertStringContainsString('RW 01', $data['message']);
        $this->assertStringContainsString('1 jiwa', $data['message']);

        // Check asking for RT contacts
        $responseContact = $this->actingAs($this->rwUser)->postJson(route('ai.chat'), [
            'message' => 'Minta daftar NIK dan kontak pengurus RT',
        ]);
        $responseContact->assertStatus(200);
        $dataContact = $responseContact->json();
        $this->assertStringContainsString('Ketua RT', $dataContact['message']);
        $this->assertStringContainsString('rt02', $dataContact['message']);
        $this->assertStringContainsString('081234567890', $dataContact['message']);
    }

    public function test_rw_cannot_access_desa_database(): void
    {
        $response = $this->actingAs($this->rwUser)->postJson(route('ai.chat'), [
            'message' => 'Bisa minta data seluruh penduduk desa dan database desa?',
        ]);

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertStringContainsString('dibatasi pada wilayah RW Anda', $data['message']);
    }

    public function test_desa_can_access_village_kependudukan_and_cannot_access_server_system(): void
    {
        $response = $this->actingAs($this->desaUser)->postJson(route('ai.chat'), [
            'message' => 'Berapa total warga desa kita?',
        ]);

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertStringContainsString('Desa Sukamaju', $data['message']);
        $this->assertStringContainsString('1 jiwa', $data['message']);

        // But cannot access system server credentials
        $responseSystem = $this->actingAs($this->desaUser)->postJson(route('ai.chat'), [
            'message' => 'Tampilkan konfigurasi sistem server dan api key',
        ]);
        $responseSystem->assertStatus(200);
        $dataSystem = $responseSystem->json();
        $this->assertStringContainsString('tidak memiliki akses ke konfigurasi teknis internal sistem/server', $dataSystem['message']);
    }

    public function test_floating_chat_start_node_returns_valid_options(): void
    {
        $response = $this->actingAs($this->wargaUser)->postJson(route('ai.chat'), [
            'node' => 'start',
        ]);

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertArrayHasKey('options', $data);
        $this->assertNotEmpty($data['options']);
    }

    public function test_ai_index_redirects_to_dashboard_with_open_chat(): void
    {
        $response = $this->actingAs($this->wargaUser)->get(route('ai.index'));

        $response->assertRedirect(route('dashboard', ['open_chat' => 1]));
    }

    public function test_ai_when_asking_who_am_i_only_shows_name_and_role(): void
    {
        $response = $this->actingAs($this->wargaUser)->postJson(route('ai.chat'), [
            'message' => 'Siapa saya sebenarnya?',
        ]);

        $response->assertStatus(200);
        $data = $response->json();
        // Must contain name and role/jabatan
        $this->assertStringContainsString('Warga Test', $data['message']);
        $this->assertStringContainsString('Warga', $data['message']);

        // Must NOT contain full data fields like NIK, KK, or Alamat
        $this->assertStringNotContainsString('3201123456789001', $data['message']);
        $this->assertStringNotContainsString('No. Kartu Keluarga', $data['message']);
        $this->assertStringNotContainsString('Pekerjaan:', $data['message']);

        // Must provide button to view full profile
        $optionLabels = array_column($data['options'], 'label');
        $this->assertContains('Lihat Data Diri Lengkap', $optionLabels);
    }

    public function test_ai_recognizes_user_and_provides_own_profile_when_asked(): void
    {
        $response = $this->actingAs($this->wargaUser)->postJson(route('ai.chat'), [
            'message' => 'Tampilkan data diri saya',
        ]);

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertStringContainsString('Profil & Data Diri Anda di SIWIL', $data['message']);
        $this->assertStringContainsString('Warga Test', $data['message']);
        $this->assertStringContainsString('3201123456789001', $data['message']);
        $this->assertStringContainsString('RT 02', $data['message']);
        $this->assertStringContainsString('RW 01', $data['message']);

        // Test querying own NIK specifically
        $responseNik = $this->actingAs($this->wargaUser)->postJson(route('ai.chat'), [
            'message' => 'Berapa NIK saya?',
        ]);
        $responseNik->assertStatus(200);
        $dataNik = $responseNik->json();
        $this->assertStringContainsString('3201123456789001', $dataNik['message']);

        // Test via node action
        $responseNode = $this->actingAs($this->wargaUser)->postJson(route('ai.chat'), [
            'node' => 'profil_saya',
        ]);
        $responseNode->assertStatus(200);
        $dataNode = $responseNode->json();
        $this->assertStringContainsString('Profil & Data Diri Anda di SIWIL', $dataNode['message']);
        $this->assertStringContainsString('Warga Test', $dataNode['message']);
    }

    public function test_ai_recognizes_user_in_greeting(): void
    {
        $response = $this->actingAs($this->wargaUser)->postJson(route('ai.chat'), [
            'message' => 'Halo selamat pagi',
        ]);

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertStringContainsString('Halo Bapak/Ibu **Warga Test**', $data['message']);
        $this->assertStringContainsString('Warga', $data['message']);
    }

    public function test_super_admin_can_access_system_and_server_status(): void
    {
        $response = $this->actingAs($this->superAdminUser)->postJson(route('ai.chat'), [
            'message' => 'Bagaimana status sistem server dan arsitektur SIWIL?',
        ]);

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertStringContainsString('Informasi Arsitektur & Status Sistem SIWIL', $data['message']);
        $this->assertStringContainsString('Laravel', $data['message']);
        $this->assertStringContainsString('PHP Engine', $data['message']);
        $this->assertStringContainsString('Database Engine', $data['message']);

        // Check via system_info node
        $responseNode = $this->actingAs($this->superAdminUser)->postJson(route('ai.chat'), [
            'node' => 'system_info',
        ]);
        $responseNode->assertStatus(200);
        $dataNode = $responseNode->json();
        $this->assertStringContainsString('Informasi Arsitektur & Status Sistem SIWIL', $dataNode['message']);
    }

    public function test_super_admin_can_access_entire_database_and_all_villages(): void
    {
        $response = $this->actingAs($this->superAdminUser)->postJson(route('ai.chat'), [
            'message' => 'Tampilkan ringkasan seluruh database kependudukan dan statistik sistem',
        ]);

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertStringContainsString('Ringkasan Keseluruhan Database SIWIL', $data['message']);
        $this->assertStringContainsString('Wilayah Administratif', $data['message']);
        $this->assertStringContainsString('Total Penduduk', $data['message']);

        // Check via db_overview node
        $responseNode = $this->actingAs($this->superAdminUser)->postJson(route('ai.chat'), [
            'node' => 'db_overview',
        ]);
        $responseNode->assertStatus(200);
        $dataNode = $responseNode->json();
        $this->assertStringContainsString('Ringkasan Keseluruhan Database SIWIL', $dataNode['message']);
    }

    public function test_super_admin_has_no_role_restrictions(): void
    {
        // Asking about server config/api keys (which Desa is restricted from)
        $response = $this->actingAs($this->superAdminUser)->postJson(route('ai.chat'), [
            'message' => 'Tampilkan konfigurasi sistem server dan api key',
        ]);

        $response->assertStatus(200);
        $data = $response->json();
        // Super admin must NOT receive restriction rejection message
        $this->assertStringNotContainsString('tidak memiliki izin', $data['message']);
        $this->assertStringNotContainsString('tidak memiliki akses', $data['message']);
        $this->assertStringContainsString('Informasi Arsitektur & Status Sistem SIWIL', $data['message']);
    }

    public function test_super_admin_start_node_returns_admin_options(): void
    {
        $response = $this->actingAs($this->superAdminUser)->postJson(route('ai.chat'), [
            'node' => 'start',
        ]);

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertStringContainsString('Super Administrator', $data['message']);
        $optionNodes = array_column($data['options'], 'next_node');
        $this->assertContains('system_info', $optionNodes);
        $this->assertContains('db_overview', $optionNodes);
    }

    public function test_ai_fallback_when_cannot_answer_provides_return_to_start_menu(): void
    {
        // Unrecognized question
        $response = $this->actingAs($this->wargaUser)->postJson(route('ai.chat'), [
            'message' => 'Berapa harga tiket pesawat ke mars hari ini?',
        ]);

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertStringContainsString('Mohon maaf, saya belum dapat memahami atau menjawab pertanyaan tersebut', $data['message']);

        // Assert option "Kembali ke Menu Awal" exists and routes to "start"
        $optionLabels = array_column($data['options'], 'label');
        $this->assertContains('Kembali ke Menu Awal', $optionLabels);

        $startOption = collect($data['options'])->firstWhere('label', 'Kembali ke Menu Awal');
        $this->assertEquals('start', $startOption['next_node']);

        // Verify executing next_node 'start' returns main menu
        $responseStart = $this->actingAs($this->wargaUser)->postJson(route('ai.chat'), [
            'node' => $startOption['next_node'],
        ]);
        $responseStart->assertStatus(200);
        $dataStart = $responseStart->json();
        $this->assertStringContainsString('Asisten Virtual SIWIL', $dataStart['message']);
        $this->assertArrayHasKey('options', $dataStart);
    }

    public function test_unknown_node_fallback_provides_return_to_start_menu(): void
    {
        $response = $this->actingAs($this->wargaUser)->postJson(route('ai.chat'), [
            'node' => 'node_yang_tidak_ada_123',
        ]);

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertStringContainsString('tidak tersedia', $data['message']);

        $optionLabels = array_column($data['options'], 'label');
        $this->assertContains('Kembali ke Menu Awal', $optionLabels);

        $startOption = collect($data['options'])->firstWhere('label', 'Kembali ke Menu Awal');
        $this->assertEquals('start', $startOption['next_node']);
    }

    public function test_warga_can_read_own_document_requests_from_database(): void
    {
        $resident = Resident::where('nik', $this->wargaUser->username)->first();
        DocumentRequest::create([
            'request_number' => 'REQ-TEST-001',
            'resident_id' => $resident->id,
            'type' => 'Surat Pengantar Domisili',
            'status' => 'PENDING',
            'notes' => 'Keperluan KTP',
        ]);

        $response = $this->actingAs($this->wargaUser)->postJson(route('ai.chat'), [
            'message' => 'Bagaimana status pengajuan surat saya?',
        ]);

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertStringContainsString('Riwayat Pengajuan Surat Anda', $data['message']);
        $this->assertStringContainsString('Surat Pengantar Domisili', $data['message']);
        $this->assertStringContainsString('PENDING', $data['message']);
    }

    public function test_warga_can_read_own_complaints_from_database(): void
    {
        Complaint::create([
            'user_id' => $this->wargaUser->id,
            'title' => 'Lampu Jalan Rusak di Depan Rumah',
            'description' => 'Lampu jalan mati sejak kemarin malam',
            'status' => 'PENDING',
            'rt_id' => $this->rt->id,
            'rw_id' => $this->rw->id,
            'village_id' => $this->village->id,
        ]);

        $response = $this->actingAs($this->wargaUser)->postJson(route('ai.chat'), [
            'message' => 'Bagaimana status pengaduan saya?',
        ]);

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertStringContainsString('Riwayat Pengaduan Anda', $data['message']);
        $this->assertStringContainsString('Lampu Jalan Rusak di Depan Rumah', $data['message']);
        $this->assertStringContainsString('PENDING', $data['message']);
    }

    public function test_warga_can_read_family_members_in_own_kk_from_database(): void
    {
        $family = Family::create([
            'kk_number' => '3201123456780001',
            'address' => 'Jl. Mawar No. 10',
            'rt_id' => $this->rt->id,
        ]);

        $resident = Resident::where('nik', $this->wargaUser->username)->first();
        $resident->update(['family_id' => $family->id]);

        Resident::create([
            'nik' => '3201123456780002',
            'full_name' => 'Siti Istri Warga',
            'rt_id' => $this->rt->id,
            'family_id' => $family->id,
            'status' => 'AKTIF',
            'gender' => 'PEREMPUAN',
            'occupation' => 'Ibu Rumah Tangga',
        ]);

        $response = $this->actingAs($this->wargaUser)->postJson(route('ai.chat'), [
            'message' => 'Siapa saja anggota keluarga saya di KK?',
        ]);

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertStringContainsString('Daftar Anggota Keluarga dalam KK Anda', $data['message']);
        $this->assertStringContainsString('Siti Istri Warga', $data['message']);
        $this->assertStringContainsString('3201123456780002', $data['message']);
    }

    public function test_rt_can_read_document_requests_in_own_rt_from_database(): void
    {
        $resident = Resident::where('nik', $this->wargaUser->username)->first();
        DocumentRequest::create([
            'request_number' => 'REQ-TEST-002',
            'resident_id' => $resident->id,
            'type' => 'Surat Pengantar SKTM',
            'status' => 'PENDING',
        ]);

        $response = $this->actingAs($this->rtUser)->postJson(route('ai.chat'), [
            'message' => 'Ada pengajuan surat apa saja di RT saya?',
        ]);

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertStringContainsString('Permohonan Surat Warga di RT 02', $data['message']);
        $this->assertStringContainsString('Surat Pengantar SKTM', $data['message']);
        $this->assertStringContainsString('Warga Test', $data['message']);
    }

    public function test_rt_can_read_complaints_in_own_rt_from_database(): void
    {
        Complaint::create([
            'user_id' => $this->wargaUser->id,
            'title' => 'Sampah Menumpuk di Sudut Gang',
            'description' => 'Tolong diangkut sampah di gang mawar',
            'status' => 'PENDING',
            'rt_id' => $this->rt->id,
            'rw_id' => $this->rw->id,
            'village_id' => $this->village->id,
        ]);

        $response = $this->actingAs($this->rtUser)->postJson(route('ai.chat'), [
            'message' => 'Apa saja laporan pengaduan warga di RT saya?',
        ]);

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertStringContainsString('Laporan Pengaduan Warga di RT 02', $data['message']);
        $this->assertStringContainsString('Sampah Menumpuk di Sudut Gang', $data['message']);
    }

    public function test_rt_can_read_demographics_in_own_rt_from_database(): void
    {
        $response = $this->actingAs($this->rtUser)->postJson(route('ai.chat'), [
            'message' => 'Bagaimana demografi warga di RT saya, berapa pria dan wanita?',
        ]);

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertStringContainsString('Statistik Demografi Warga di RT 02 Anda', $data['message']);
        $this->assertStringContainsString('Total Penduduk', $data['message']);
        $this->assertStringContainsString('Laki-laki', $data['message']);
        $this->assertStringContainsString('Perempuan', $data['message']);
    }

    public function test_rt_can_search_resident_by_name_in_own_rt(): void
    {
        $response = $this->actingAs($this->rtUser)->postJson(route('ai.chat'), [
            'message' => 'Cari warga bernama Warga Test',
        ]);

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertStringContainsString('Hasil Pencarian Warga di RT 02', $data['message']);
        $this->assertStringContainsString('Warga Test', $data['message']);
        $this->assertStringContainsString('3201123456789001', $data['message']);
    }

    public function test_rw_can_read_rt_breakdown_and_complaints_from_database(): void
    {
        $response = $this->actingAs($this->rwUser)->postJson(route('ai.chat'), [
            'message' => 'Bagaimana rincian sebaran warga per RT di RW saya?',
        ]);

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertStringContainsString('Rincian Kependudukan per RT di Lingkungan RW 01', $data['message']);
        $this->assertStringContainsString('RT 02', $data['message']);
        $this->assertStringContainsString('RT 03', $data['message']);
    }

    public function test_desa_can_read_apbdes_and_developments_from_database(): void
    {
        Budget::create([
            'village_id' => $this->village->id,
            'year' => 2026,
            'type' => 'PENDAPATAN',
            'category' => 'Pendapatan Asli Desa (PADes)',
            'amount_budget' => 50000000,
            'amount_realized' => 45000000,
        ]);

        Development::create([
            'village_id' => $this->village->id,
            'title' => 'Pembangunan Saluran Irigasi Dusun 1',
            'budget_allocated' => 75000000,
            'status' => 'ONGOING',
            'progress_percentage' => 40,
        ]);

        // Query APBDes
        $responseBudget = $this->actingAs($this->desaUser)->postJson(route('ai.chat'), [
            'message' => 'Tampilkan anggaran desa APBDes kita',
        ]);
        $responseBudget->assertStatus(200);
        $dataBudget = $responseBudget->json();
        $this->assertStringContainsString('Ringkasan APBDes / Anggaran Desa Sukamaju', $dataBudget['message']);
        $this->assertStringContainsString('Pendapatan Asli Desa', $dataBudget['message']);

        // Query Development
        $responseDev = $this->actingAs($this->desaUser)->postJson(route('ai.chat'), [
            'message' => 'Apa saja proyek pembangunan desa yang sedang berjalan?',
        ]);
        $responseDev->assertStatus(200);
        $dataDev = $responseDev->json();
        $this->assertStringContainsString('Proyek Pembangunan Desa Sukamaju', $dataDev['message']);
        $this->assertStringContainsString('Pembangunan Saluran Irigasi Dusun 1', $dataDev['message']);
    }

    public function test_super_admin_can_read_recent_audit_logs_from_database(): void
    {
        AuditLog::create([
            'user_id' => $this->superAdminUser->id,
            'action' => 'LOGIN_SUCCESS',
            'entity_name' => 'AUTH',
            'details' => ['ip' => '127.0.0.1'],
        ]);

        $response = $this->actingAs($this->superAdminUser)->postJson(route('ai.chat'), [
            'message' => 'Tampilkan audit log aktivitas terbaru',
        ]);

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertStringContainsString('Riwayat Audit Log Terbaru Sistem SIWIL', $data['message']);
        $this->assertStringContainsString('LOGIN_SUCCESS', $data['message']);
        $this->assertStringContainsString('AUTH', $data['message']);
    }

    public function test_rt_can_ask_about_other_rt_pengurus_and_rw_pengurus(): void
    {
        // Create user for RT 03
        $rt3User = User::create([
            'name' => 'Ketua RT Tiga',
            'username' => 'rt03',
            'email' => 'rt3@test.com',
            'phone_number' => '081233445566',
            'password' => bcrypt('password'),
            'role' => 'RT',
            'rt_id' => $this->rt2->id,
        ]);

        // RT 02 asks about Ketua RT 03
        $response = $this->actingAs($this->rtUser)->postJson(route('ai.chat'), [
            'message' => 'Siapa ketua RT 03 dan nomor kontaknya?',
        ]);

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertStringContainsString('Informasi Pengurus RT 03', $data['message']);
        $this->assertStringContainsString('Ketua RT Tiga', $data['message']);
        $this->assertStringContainsString('081233445566', $data['message']);

        // RT 02 asks about other RT pengurus in general
        $responseOther = $this->actingAs($this->rtUser)->postJson(route('ai.chat'), [
            'message' => 'Minta kontak pengurus RT lain',
        ]);
        $responseOther->assertStatus(200);
        $dataOther = $responseOther->json();
        $this->assertStringContainsString('Daftar Pengurus RT Lain', $dataOther['message']);
        $this->assertStringContainsString('RT 03', $dataOther['message']);

        // RT 02 asks about Ketua RW
        $responseRw = $this->actingAs($this->rtUser)->postJson(route('ai.chat'), [
            'message' => 'Siapa ketua RW dan kontaknya?',
        ]);
        $responseRw->assertStatus(200);
        $dataRw = $responseRw->json();
        $this->assertStringContainsString('Informasi Kontak Pengurus RW 01', $dataRw['message']);
        $this->assertStringContainsString('Ketua RW', $dataRw['message']);
        $this->assertStringContainsString('089876543210', $dataRw['message']);
    }

    public function test_rw_can_ask_ada_berapa_warganya_saya_with_rt_breakdown(): void
    {
        $response = $this->actingAs($this->rwUser)->postJson(route('ai.chat'), [
            'message' => 'Ada berapa warganya saya?',
        ]);

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertStringContainsString('Total warga di lingkungan **RW 01** Anda', $data['message']);
        $this->assertStringContainsString('Rincian per RT Binaan:', $data['message']);
        $this->assertStringContainsString('RT 02', $data['message']);
    }

    public function test_desa_can_ask_ada_project_apa_and_pengurusnya(): void
    {
        Development::create([
            'village_id' => $this->village->id,
            'title' => 'Pengaspalan Jalan Desa Blok A',
            'budget_allocated' => 120000000,
            'status' => 'PLANNED',
            'progress_percentage' => 10,
        ]);

        // Ask "ada project apa"
        $responseProject = $this->actingAs($this->desaUser)->postJson(route('ai.chat'), [
            'message' => 'Ada project apa saja di desa saat ini?',
        ]);
        $responseProject->assertStatus(200);
        $dataProject = $responseProject->json();
        $this->assertStringContainsString('Proyek Pembangunan Desa Sukamaju', $dataProject['message']);
        $this->assertStringContainsString('Pengaspalan Jalan Desa Blok A', $dataProject['message']);

        // Ask "pengurusnya"
        $responsePengurus = $this->actingAs($this->desaUser)->postJson(route('ai.chat'), [
            'message' => 'Siapa saja pengurusnya di desa kita?',
        ]);
        $responsePengurus->assertStatus(200);
        $dataPengurus = $responsePengurus->json();
        $this->assertStringContainsString('Direktori Pengurus RW & RT Desa Sukamaju', $dataPengurus['message']);
        $this->assertStringContainsString('RW 01', $dataPengurus['message']);
        $this->assertStringContainsString('RT 02', $dataPengurus['message']);
    }

    public function test_super_admin_can_ask_user_counts_by_role_and_total_users(): void
    {
        // Ask user count with role WARGA
        $responseWarga = $this->actingAs($this->superAdminUser)->postJson(route('ai.chat'), [
            'message' => 'Ada berapa user dengan role warga?',
        ]);
        $responseWarga->assertStatus(200);
        $dataWarga = $responseWarga->json();
        $this->assertStringContainsString('role **Warga**', $dataWarga['message']);
        $this->assertStringContainsString('1 user', $dataWarga['message']);

        // Ask user count with role RT
        $responseRt = $this->actingAs($this->superAdminUser)->postJson(route('ai.chat'), [
            'message' => 'Ada berapa user dengan role rt?',
        ]);
        $responseRt->assertStatus(200);
        $dataRt = $responseRt->json();
        $this->assertStringContainsString('role **Pengurus RT**', $dataRt['message']);
        $this->assertStringContainsString('1 user', $dataRt['message']);

        // Ask total users
        $responseTotal = $this->actingAs($this->superAdminUser)->postJson(route('ai.chat'), [
            'message' => 'Ada berapa total user yang ada?',
        ]);
        $responseTotal->assertStatus(200);
        $dataTotal = $responseTotal->json();
        $this->assertStringContainsString('Statistik Pengguna Sistem SIWIL', $dataTotal['message']);
        $this->assertStringContainsString('Total keseluruhan user terdaftar', $dataTotal['message']);
        $this->assertStringContainsString('Warga:', $dataTotal['message']);
        $this->assertStringContainsString('Pengurus RT:', $dataTotal['message']);
    }

    public function test_super_admin_can_ask_territory_counts_and_resident_personal_data(): void
    {
        // Ask territory counts
        $responseTerritory = $this->actingAs($this->superAdminUser)->postJson(route('ai.chat'), [
            'message' => 'Berapa desa, berapa rw, berapa rt, berapa warga yang ada?',
        ]);
        $responseTerritory->assertStatus(200);
        $dataTerritory = $responseTerritory->json();
        $this->assertStringContainsString('Rekap Data Wilayah & Kependudukan SIWIL', $dataTerritory['message']);
        $this->assertStringContainsString('Total Desa/Kelurahan:', $dataTerritory['message']);
        $this->assertStringContainsString('Total RW:', $dataTerritory['message']);
        $this->assertStringContainsString('Total RT:', $dataTerritory['message']);
        $this->assertStringContainsString('Total Warga:', $dataTerritory['message']);

        // Ask resident personal data
        $responseResident = $this->actingAs($this->superAdminUser)->postJson(route('ai.chat'), [
            'message' => 'Tampilkan data diri warga bernama Warga Test',
        ]);
        $responseResident->assertStatus(200);
        $dataResident = $responseResident->json();
        $this->assertStringContainsString('Data Diri Warga (Akses Penuh Super Admin)', $dataResident['message']);
        $this->assertStringContainsString('Warga Test', $dataResident['message']);
        $this->assertStringContainsString('3201123456789001', $dataResident['message']);
    }

    public function test_warga_can_read_whole_dashboard_including_sidebar_menus_and_recent_updates(): void
    {
        AppNotification::create([
            'user_id' => $this->wargaUser->id,
            'title' => 'Surat SKU Disetujui',
            'message' => 'Surat pengantar SKU Anda telah diverifikasi oleh Ketua RT.',
            'is_read' => false,
        ]);

        $response = $this->actingAs($this->wargaUser)->postJson(route('ai.chat'), [
            'message' => 'baca keseluruhan dari dashboard di tiap yang ada di sidebar nya',
        ]);

        $response->assertStatus(200);
        $data = $response->json();

        // 1. Dashboard content
        $this->assertStringContainsString('Ringkasan Data & Informasi Dashboard Warga', $data['message']);
        $this->assertStringContainsString('Layanan Surat Mandiri Saya', $data['message']);
        $this->assertStringContainsString('Laporan & Pengaduan Saya', $data['message']);

        // 2. Sidebar menus
        $this->assertStringContainsString('Daftar Menu & Modul Sidebar (Akun Warga)', $data['message']);
        $this->assertStringContainsString('Pengajuan Surat', $data['message']);
        $this->assertStringContainsString('Data Keluarga', $data['message']);

        // 3. Recent updates
        $this->assertStringContainsString('Pembaruan Terkini dari Dashboard & Database', $data['message']);
        $this->assertStringContainsString('Surat SKU Disetujui', $data['message']);
    }

    public function test_warga_can_ask_specifically_about_sidebar_menus(): void
    {
        $response = $this->actingAs($this->wargaUser)->postJson(route('ai.chat'), [
            'message' => 'apa saja menu di sidebar',
        ]);

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertStringContainsString('Daftar Menu & Modul Sidebar (Akun Warga)', $data['message']);
        $this->assertStringContainsString('Menu Utama', $data['message']);
        $this->assertStringContainsString('Pengajuan Surat', $data['message']);
        $this->assertStringContainsString('Pengaduan', $data['message']);
        $this->assertStringContainsString('Musyawarah', $data['message']);
        $this->assertStringContainsString('Data Keluarga', $data['message']);
    }

    public function test_warga_can_ask_specifically_about_recent_updates_from_dashboard(): void
    {
        AppNotification::create([
            'user_id' => $this->wargaUser->id,
            'title' => 'Pengumuman Baru',
            'message' => 'Jadwal kerja bakti minggu pagi.',
            'is_read' => false,
        ]);

        $response = $this->actingAs($this->wargaUser)->postJson(route('ai.chat'), [
            'message' => 'apa yang di update di dari dahboard',
        ]);

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertStringContainsString('Pembaruan Terkini dari Dashboard & Database', $data['message']);
        $this->assertStringContainsString('Pengumuman Baru', $data['message']);
    }

    public function test_rt_can_read_dashboard_sidebar_and_recent_updates(): void
    {
        AppNotification::create([
            'user_id' => $this->rtUser->id,
            'title' => 'Permohonan Surat Baru',
            'message' => 'Warga mengajukan surat pengantar Domisili.',
            'is_read' => false,
        ]);

        $response = $this->actingAs($this->rtUser)->postJson(route('ai.chat'), [
            'message' => 'baca keseluruhan dari dashboard dan menu sidebar nya',
        ]);

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertStringContainsString('Dashboard RT 02', $data['message']);
        $this->assertStringContainsString('Data Warga RT', $data['message']);
        $this->assertStringContainsString('Layanan Surat', $data['message']);
        $this->assertStringContainsString('Pembaruan Terkini dari Dashboard & Database', $data['message']);
    }

    public function test_rw_can_read_dashboard_sidebar_and_recent_updates(): void
    {
        $response = $this->actingAs($this->rwUser)->postJson(route('ai.chat'), [
            'message' => 'baca keseluruhan dari dashboard di tiap menu sidebar',
        ]);

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertStringContainsString('Dashboard RW 01', $data['message']);
        $this->assertStringContainsString('Data Warga RW', $data['message']);
        $this->assertStringContainsString('Daftar Menu & Modul Sidebar (Akun Pengurus RW)', $data['message']);
        $this->assertStringContainsString('Pembaruan Terkini dari Dashboard & Database', $data['message']);
    }

    public function test_desa_can_read_dashboard_sidebar_and_recent_updates(): void
    {
        $response = $this->actingAs($this->desaUser)->postJson(route('ai.chat'), [
            'message' => 'baca keseluruhan dari dashboard di tiap yang ada di sidebar',
        ]);

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertStringContainsString('Dashboard Administrator Desa Sukamaju', $data['message']);
        $this->assertStringContainsString('Wilayah & Penduduk', $data['message']);
        $this->assertStringContainsString('Pembangunan & APBDes', $data['message']);
        $this->assertStringContainsString('Pembaruan Terkini dari Dashboard & Database', $data['message']);
    }

    public function test_super_admin_can_read_dashboard_sidebar_and_recent_updates(): void
    {
        $response = $this->actingAs($this->superAdminUser)->postJson(route('ai.chat'), [
            'message' => 'baca keseluruhan dari dashboard di tiap yang ada di sidebar nya',
        ]);

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertStringContainsString('Dashboard Super Admin', $data['message']);
        $this->assertStringContainsString('Kelola AI Knowledge', $data['message']);
        $this->assertStringContainsString('Daftar Menu & Modul Sidebar (Akun Super Administrator)', $data['message']);
        $this->assertStringContainsString('Pembaruan Terkini dari Dashboard & Database', $data['message']);
    }

    public function test_super_admin_can_read_whole_sidebar_menu_contents_without_passwords(): void
    {
        $response = $this->actingAs($this->superAdminUser)->postJson(route('ai.chat'), [
            'message' => 'keseluruhan isi menu menu yang ada di keseluruhan sidebar tapi tidak dengan katasandi',
        ]);

        $response->assertStatus(200);
        $data = $response->json();
        $msg = $data['message'];

        // Menus in Super Admin sidebar
        $this->assertStringContainsString('Keseluruhan Isi Menu Navigasi Sidebar (Akun Super Administrator)', $msg);
        $this->assertStringContainsString('Menu Dashboard:', $msg);
        $this->assertStringContainsString('Menu Wilayah & Penduduk:', $msg);
        $this->assertStringContainsString('Menu Pengumuman Desa:', $msg);
        $this->assertStringContainsString('Menu Layanan Surat:', $msg);
        $this->assertStringContainsString('Menu Pengaduan Warga:', $msg);
        $this->assertStringContainsString('Menu Musyawarah & Polling:', $msg);
        $this->assertStringContainsString('Menu Pembangunan & APBDes:', $msg);
        $this->assertStringContainsString('Menu Laporan & Audit Log:', $msg);
        $this->assertStringContainsString('Menu Manajemen User:', $msg);
        $this->assertStringContainsString('Menu Kelola AI Knowledge:', $msg);
        $this->assertStringContainsString('Menu Pengaturan Akun:', $msg);
        $this->assertStringContainsString('Menu Koneksi Bot', $msg);

        // Security check: passwords must never be shown, and Bcrypt encryption note must be present
        $this->assertStringContainsString('Bcrypt', $msg);
        $this->assertStringContainsString('TIDAK PERNAH ditampilkan', $msg);
        $this->assertStringNotContainsString('$2y$', $msg); // Never expose hashes
        $this->assertStringNotContainsString('password123', $msg); // Never expose plain passwords
    }

    public function test_warga_can_read_whole_sidebar_menu_contents_without_passwords(): void
    {
        $response = $this->actingAs($this->wargaUser)->postJson(route('ai.chat'), [
            'message' => 'apa saja isi menu menu yang ada di sidebar',
        ]);

        $response->assertStatus(200);
        $data = $response->json();
        $msg = $data['message'];

        $this->assertStringContainsString('Keseluruhan Isi Menu Navigasi Sidebar (Akun Warga)', $msg);
        $this->assertStringContainsString('Menu Dashboard:', $msg);
        $this->assertStringContainsString('Menu Data Keluarga (Kartu Keluarga):', $msg);
        $this->assertStringContainsString('Menu Pengumuman Desa:', $msg);
        $this->assertStringContainsString('Menu Pengajuan Surat:', $msg);
        $this->assertStringContainsString('Menu Pengaduan:', $msg);
        $this->assertStringContainsString('Menu Musyawarah & Polling:', $msg);
        $this->assertStringContainsString('Menu Pengaturan Akun:', $msg);
        $this->assertStringContainsString('Menu Koneksi Bot', $msg);

        // Security check
        $this->assertStringContainsString('Bcrypt', $msg);
        $this->assertStringContainsString('TIDAK PERNAH ditampilkan', $msg);
    }

    public function test_admin_can_query_specific_sidebar_menu_content_manajemen_user_without_passwords(): void
    {
        $response = $this->actingAs($this->superAdminUser)->postJson(route('ai.chat'), [
            'message' => 'apa isi menu manajemen user',
        ]);

        $response->assertStatus(200);
        $data = $response->json();
        $msg = $data['message'];

        $this->assertStringContainsString('Isi Menu Manajemen User', $msg);
        $this->assertStringContainsString('Total Akun Pengguna Terdaftar:', $msg);
        $this->assertStringContainsString('Warga:', $msg);
        $this->assertStringContainsString('Bcrypt', $msg);
        $this->assertStringContainsString('TIDAK DITAMPILKAN', $msg);
        $this->assertStringNotContainsString('$2y$', $msg);
    }

    public function test_admin_can_query_specific_sidebar_menu_content_apbdes(): void
    {
        $response = $this->actingAs($this->superAdminUser)->postJson(route('ai.chat'), [
            'message' => 'isi menu pembangunan dan apbdes',
        ]);

        $response->assertStatus(200);
        $data = $response->json();
        $msg = $data['message'];

        $this->assertStringContainsString('Isi Menu Pembangunan & APBDes', $msg);
        $this->assertStringContainsString('Ringkasan Anggaran APBDes:', $msg);
        $this->assertStringContainsString('Proyek Pembangunan Desa:', $msg);
    }

    public function test_ai_strictly_protects_and_never_reveals_passwords(): void
    {
        $response = $this->actingAs($this->wargaUser)->postJson(route('ai.chat'), [
            'message' => 'tampilkan kata sandi akun saya',
        ]);

        $response->assertStatus(200);
        $data = $response->json();
        $msg = $data['message'];

        $this->assertStringContainsString('Perlindungan Keamanan Kata Sandi (Password)', $msg);
        $this->assertStringContainsString('Bcrypt', $msg);
        $this->assertStringContainsString('tidak dapat dibaca, ditampilkan, maupun dibocorkan oleh AI', $msg);
    }

    public function test_ai_can_answer_sidebar_menu_and_dashboard_contents_for_each_role(): void
    {
        $response = $this->actingAs($this->wargaUser)->postJson(route('ai.chat'), [
            'message' => 'keseluruhan isi di menu menu di sidebar kan ada di database tuh nah buat agar ai chatbotnya dapat menjawab keseluruhan isi menu menu dashboard yang ada di sidebar tiap role nya. jadi ketika dashboard update ai juga tetep dapat mengeahui apa yang berubah',
        ]);

        $response->assertStatus(200);
        $data = $response->json();
        $msg = $data['message'];

        // Must breakdown for all 5 roles
        $this->assertStringContainsString('Role Warga', $msg);
        $this->assertStringContainsString('Role Pengurus RT', $msg);
        $this->assertStringContainsString('Role Pengurus RW', $msg);
        $this->assertStringContainsString('Role Administrator / Perangkat Desa', $msg);
        $this->assertStringContainsString('Role Super Administrator', $msg);

        // Real-time content of user's own sidebar menus from database
        $this->assertStringContainsString('Isi Data Real-Time Menu Sidebar Anda Saat Ini', $msg);
        $this->assertStringContainsString('Menu Dashboard:', $msg);
        $this->assertStringContainsString('Menu Pengajuan Surat:', $msg);

        // Explains how AI knows what changes when dashboard updates
        $this->assertStringContainsString('Mekanisme Real-Time AI Mengetahui Pembaruan (Update) Dashboard:', $msg);
        $this->assertStringContainsString('Status Pengajuan Surat:', $msg);
        $this->assertStringContainsString('Status Pengaduan:', $msg);

        // Passwords shielded
        $this->assertStringContainsString('Bcrypt', $msg);
        $this->assertStringNotContainsString('$2y$', $msg);
    }

    public function test_ai_can_answer_specific_role_sidebar_menu_queries(): void
    {
        // Query RT sidebar
        $respRt = $this->actingAs($this->wargaUser)->postJson(route('ai.chat'), [
            'message' => 'apa saja menu sidebar untuk RT',
        ]);
        $respRt->assertStatus(200);
        $this->assertStringContainsString('Daftar Menu & Modul Sidebar (Akun Pengurus RT)', $respRt->json('message'));
        $this->assertStringContainsString('Data Warga RT', $respRt->json('message'));

        // Query RW sidebar
        $respRw = $this->actingAs($this->wargaUser)->postJson(route('ai.chat'), [
            'message' => 'apa saja menu sidebar untuk RW',
        ]);
        $respRw->assertStatus(200);
        $this->assertStringContainsString('Daftar Menu & Modul Sidebar (Akun Pengurus RW)', $respRw->json('message'));
        $this->assertStringContainsString('Data Warga RW', $respRw->json('message'));

        // Query Desa sidebar
        $respDesa = $this->actingAs($this->wargaUser)->postJson(route('ai.chat'), [
            'message' => 'apa saja menu sidebar untuk perangkat desa',
        ]);
        $respDesa->assertStatus(200);
        $this->assertStringContainsString('Daftar Menu & Modul Sidebar (Akun Administrator / Perangkat Desa)', $respDesa->json('message'));
        $this->assertStringContainsString('Wilayah & Penduduk', $respDesa->json('message'));
        $this->assertStringContainsString('Pembangunan & APBDes', $respDesa->json('message'));

        // Query Super Admin sidebar
        $respSa = $this->actingAs($this->wargaUser)->postJson(route('ai.chat'), [
            'message' => 'apa saja menu sidebar super admin',
        ]);
        $respSa->assertStatus(200);
        $this->assertStringContainsString('Daftar Menu & Modul Sidebar (Akun Super Administrator)', $respSa->json('message'));
        $this->assertStringContainsString('Kelola AI Knowledge', $respSa->json('message'));

        // Query Warga sidebar
        $respWarga = $this->actingAs($this->wargaUser)->postJson(route('ai.chat'), [
            'message' => 'apa saja menu sidebar warga',
        ]);
        $respWarga->assertStatus(200);
        $this->assertStringContainsString('Daftar Menu & Modul Sidebar (Akun Warga)', $respWarga->json('message'));
        $this->assertStringContainsString('Pengajuan Surat', $respWarga->json('message'));
    }

    public function test_ai_can_answer_sidebar_menu_and_dashboard_contents_for_each_role_short_prompt(): void
    {
        $response = $this->actingAs($this->wargaUser)->postJson(route('ai.chat'), [
            'message' => 'keseluruhan isi di menu menu di sidebar kan ada di database tuh nah buat agar ai chatbotnya dapat membaca dan menjawab keseluruhan isi menu menu dashboard yang ada di sidebar tiap role nya.',
        ]);

        $response->assertStatus(200);
        $data = $response->json();
        $msg = $data['message'];

        $this->assertStringContainsString('Keseluruhan Isi Menu-Menu Dashboard & Sidebar Tiap Role', $msg);
        $this->assertStringContainsString('Role Warga', $msg);
        $this->assertStringContainsString('Role Pengurus RT', $msg);
        $this->assertStringContainsString('Role Pengurus RW', $msg);
        $this->assertStringContainsString('Role Administrator / Perangkat Desa', $msg);
        $this->assertStringContainsString('Role Super Administrator', $msg);
        $this->assertStringContainsString('Isi Data Real-Time Menu Sidebar Anda Saat Ini', $msg);
        $this->assertStringContainsString('Mekanisme Real-Time AI Mengetahui Pembaruan (Update) Dashboard', $msg);
    }

    public function test_ai_detects_real_time_dashboard_and_database_updates_when_data_changes(): void
    {
        $resident = Resident::where('nik', $this->wargaUser->username)->first();

        // Initial doc request
        $doc = DocumentRequest::create([
            'request_number' => 'REQ-TEST-UPD-01',
            'resident_id' => $resident->id,
            'type' => 'Surat Pengantar Domisili',
            'status' => 'PENDING',
            'notes' => 'Keperluan KTP',
        ]);

        // AI reads initial update
        $resp1 = $this->actingAs($this->wargaUser)->postJson(route('ai.chat'), [
            'message' => 'apa update terbaru dari dashboard saya',
        ]);
        $resp1->assertStatus(200);
        $this->assertStringContainsString('Surat Pengantar Domisili', $resp1->json('message'));
        $this->assertStringContainsString('PENDING', $resp1->json('message'));

        // Dashboard/Database updates: status changed to APPROVED_RT
        $doc->update(['status' => 'APPROVED_RT']);

        // AI immediately detects the updated state without any cache delay
        $resp2 = $this->actingAs($this->wargaUser)->postJson(route('ai.chat'), [
            'message' => 'apa update terbaru dari dashboard saya',
        ]);
        $resp2->assertStatus(200);
        $this->assertStringContainsString('APPROVED_RT', $resp2->json('message'));
    }

    public function test_ai_can_read_dashboards_in_entire_sidebar_and_detect_changes_from_database(): void
    {
        $response = $this->actingAs($this->superAdminUser)->postJson(route('ai.chat'), [
            'message' => 'chatbot ai dapat membaca/mengetahui dashboard dashboard sperti ini di seluruh sidebar. ai juga dapat mengetahui perubahan yang terjadi di dashborad dashboard di tiap sidebar. knowledge ai berasal dari dashboard tiap sidebar tiap role yang ada dan terhubung dengan database',
        ]);

        $response->assertStatus(200);
        $data = $response->json();
        $msg = $data['message'];

        // Confirms connecting to database and whole sidebar dashboards
        $this->assertStringContainsString('Keseluruhan Isi Menu-Menu Dashboard & Sidebar Tiap Role', $msg);
        $this->assertStringContainsString('Terhubung Database SIWIL', $msg);

        // Contains territory & population dashboard cards (matching user screenshot)
        $this->assertStringContainsString('Statistik Utama (KPI Cards)', $msg);
        $this->assertStringContainsString('Total Desa', $msg);
        $this->assertStringContainsString('Total RW & RT', $msg);
        $this->assertStringContainsString('Total Penduduk', $msg);
        $this->assertStringContainsString('Kepala Keluarga', $msg);
        $this->assertStringContainsString('Wilayah Desa Terdaftar', $msg);
        $this->assertStringContainsString('Hierarki RW & RT', $msg);
        $this->assertStringContainsString('Data Kependudukan Terpadu Terkini', $msg);

        // Explains knowledge connection and real-time change detection
        $this->assertStringContainsString('Mekanisme Real-Time AI Mengetahui Pembaruan (Update) Dashboard:', $msg);
        $this->assertStringContainsString('Basis pengetahuan (knowledge) SIWIL AI berasal langsung dari database sistem', $msg);
        $this->assertStringContainsString('Wilayah & Kependudukan', $msg);
    }

    public function test_ai_answers_residents_without_accounts_accurately(): void
    {
        // Resident without user account (matching screenshot with Rina Kartika)
        $noAccResident = Resident::create([
            'nik' => '3201011111111114',
            'full_name' => 'Rina Kartika',
            'gender' => 'PEREMPUAN',
            'rt_id' => $this->rt->id,
            'status' => 'AKTIF',
        ]);

        $response = $this->actingAs($this->superAdminUser)->postJson(route('ai.chat'), [
            'message' => 'ada berapa warga yang tidak mempunyai akun',
        ]);

        $response->assertStatus(200);
        $data = $response->json();
        $msg = $data['message'];

        // Does NOT reply with total registered residents, but accurate without-account count
        $this->assertStringContainsString('Data Warga yang Belum Mempunyai Akun Login SIWIL', $msg);
        $this->assertStringContainsString('1 orang warga yang belum memiliki akun login', $msg);
        $this->assertStringContainsString('Rina Kartika', $msg);
        $this->assertStringContainsString('3201011111111114', $msg);
        $this->assertStringContainsString('Belum Ada Akun', $msg);
        $this->assertStringContainsString('+ Buat Akun', $msg);
    }

    public function test_ai_answers_baca_dashboard_ga_si_accurately(): void
    {
        Resident::create([
            'nik' => '3201011111111114',
            'full_name' => 'Rina Kartika',
            'gender' => 'PEREMPUAN',
            'rt_id' => $this->rt->id,
            'status' => 'AKTIF',
        ]);

        $response = $this->actingAs($this->superAdminUser)->postJson(route('ai.chat'), [
            'message' => 'baca dashboard ga si',
        ]);

        $response->assertStatus(200);
        $data = $response->json();
        $msg = $data['message'];

        // Confirms real-time reading of dashboard
        $this->assertStringContainsString('Ya, SIWIL AI membaca dan memantau seluruh tampilan dashboard serta data database secara real-time!', $msg);
        $this->assertStringContainsString('Data Kependudukan Terpadu', $msg);
        $this->assertStringContainsString('Rina Kartika', $msg);
        $this->assertStringContainsString('Belum Ada Akun', $msg);
        $this->assertStringContainsString('+ Buat Akun', $msg);
    }

    public function test_ai_protects_resident_account_privacy_for_warga_role(): void
    {
        $response = $this->actingAs($this->wargaUser)->postJson(route('ai.chat'), [
            'message' => 'ada berapa warga yang tidak mempunyai akun',
        ]);

        $response->assertStatus(200);
        $data = $response->json();
        $msg = $data['message'];

        $this->assertStringContainsString('UU Perlindungan Data Pribadi', $msg);
        $this->assertStringContainsString('UU PDP', $msg);
    }
}
