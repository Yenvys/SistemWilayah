<?php

namespace Tests\Feature;

use App\Models\Budget;
use App\Models\Development;
use App\Models\User;
use App\Models\Village;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DevelopmentHubTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Village $village;

    protected function setUp(): void
    {
        parent::setUp();

        $this->village = Village::create(['name' => 'Desa Sukamaju']);

        $this->admin = User::create([
            'name' => 'Admin Desa',
            'username' => 'admindesa',
            'email' => 'admindesa@test.com',
            'password' => bcrypt('password'),
            'role' => 'ADMIN_DESA',
            'village_id' => $this->village->id,
            'status' => 'active',
        ]);
    }

    public function test_can_view_all_three_boxes_on_single_page(): void
    {
        Development::create([
            'village_id' => $this->village->id,
            'title' => 'Pengaspalan Jalan Dusun I',
            'budget_allocated' => 50000000,
            'source_of_fund' => 'Dana Desa',
            'status' => 'ONGOING',
            'progress_percentage' => 45,
        ]);

        Budget::create([
            'village_id' => $this->village->id,
            'year' => 2026,
            'type' => 'PENDAPATAN',
            'category' => 'Pendapatan Asli Desa',
            'amount_budget' => 100000000,
            'amount_realized' => 85000000,
        ]);

        $response = $this->actingAs($this->admin)->get(route('developments.index'));

        $response->assertStatus(200);
        // Box Kiri: APBDes
        $response->assertSee('Keuangan & APBDes', false);
        $response->assertSee('Pendapatan Asli Desa');
        // Box Kanan: Pembangunan
        $response->assertSee('Proyek Pembangunan');
        $response->assertSee('Pengaspalan Jalan Dusun I');
        // Box Bawah: Spasial
        $response->assertSee('Peta & Analitik Spasial Wilayah', false);
        $response->assertSee('spatial-map');
    }

    public function test_spatial_analytics_index_redirects_to_developments_index(): void
    {
        $response = $this->actingAs($this->admin)->get(route('spatial-analytics.index'));

        $response->assertRedirect(route('developments.index'));
    }

    public function test_budgets_index_redirects_to_developments_index(): void
    {
        $response = $this->actingAs($this->admin)->get(route('budgets.index'));

        $response->assertRedirect(route('developments.index'));
    }

    public function test_can_create_development_and_redirects_to_index(): void
    {
        $response = $this->actingAs($this->admin)->post(route('developments.store'), [
            'title' => 'Renovasi Posyandu Mawar',
            'description' => 'Renovasi gedung dan pengadaan fasilitas',
            'budget_allocated' => 25000000,
            'source_of_fund' => 'ADD',
            'status' => 'PLANNED',
            'progress_percentage' => 0,
        ]);

        $response->assertRedirect(route('developments.index'));
        $this->assertDatabaseHas('developments', [
            'title' => 'Renovasi Posyandu Mawar',
        ]);
    }

    public function test_can_create_budget_and_redirects_to_index(): void
    {
        $response = $this->actingAs($this->admin)->post(route('budgets.store'), [
            'year' => 2026,
            'type' => 'BELANJA',
            'category' => 'Belanja Penyelenggaraan Pemerintahan',
            'amount_budget' => 75000000,
            'amount_realized' => 30000000,
        ]);

        $response->assertRedirect(route('developments.index'));
        $this->assertDatabaseHas('budgets', [
            'category' => 'Belanja Penyelenggaraan Pemerintahan',
        ]);
    }
}
