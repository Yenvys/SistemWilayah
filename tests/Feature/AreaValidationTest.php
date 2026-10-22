<?php

namespace Tests\Feature;

use App\Models\Rt;
use App\Models\Rw;
use App\Models\User;
use App\Models\Village;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AreaValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_cannot_create_duplicate_rt_in_same_rw(): void
    {
        $user = User::factory()->create(['role' => 'SUPER_ADMIN']);
        $village = Village::create([
            'name' => 'Desa Sukamaju',
            'code' => 'SKM01',
        ]);
        $rw = Rw::create([
            'number' => '01',
            'village_id' => $village->id,
        ]);

        // Simpan RT 01
        Rt::create([
            'number' => '01',
            'rw_id' => $rw->id,
        ]);

        // Coba simpan '01' lagi -> harus gagal validasi duplikat
        $response = $this->actingAs($user)->post(route('areas.rt.store'), [
            'number' => '01',
            'rw_id' => $rw->id,
        ]);

        $response->assertSessionHasErrors(['number']);

        // Coba simpan 'RT 01' -> dinormalisasi menjadi '01' dan tetap gagal validasi duplikat
        $responseWithPrefix = $this->actingAs($user)->post(route('areas.rt.store'), [
            'number' => 'RT 01',
            'rw_id' => $rw->id,
        ]);

        $responseWithPrefix->assertSessionHasErrors(['number']);

        // Simpan 'RT 02' -> dinormalisasi menjadi '02' dan berhasil disimpan
        $validResponse = $this->actingAs($user)->post(route('areas.rt.store'), [
            'number' => 'RT 02',
            'rw_id' => $rw->id,
        ]);

        $validResponse->assertRedirect(route('areas.index'));
        $this->assertDatabaseHas('rts', [
            'number' => '02',
            'rw_id' => $rw->id,
        ]);
    }

    public function test_cannot_create_duplicate_rw_in_same_village(): void
    {
        $user = User::factory()->create(['role' => 'SUPER_ADMIN']);
        $village = Village::create([
            'name' => 'Desa Sukamaju',
            'code' => 'SKM02',
        ]);

        Rw::create([
            'number' => '01',
            'village_id' => $village->id,
        ]);

        // Coba simpan RW '01' lagi
        $response = $this->actingAs($user)->post(route('areas.rw.store'), [
            'number' => '01',
            'village_id' => $village->id,
        ]);
        $response->assertSessionHasErrors(['number']);

        // Coba simpan RW 'RW 01'
        $responseWithPrefix = $this->actingAs($user)->post(route('areas.rw.store'), [
            'number' => 'RW 01',
            'village_id' => $village->id,
        ]);
        $responseWithPrefix->assertSessionHasErrors(['number']);

        // Simpan RW baru 'RW 2' -> normalisasi ke '02'
        $validResponse = $this->actingAs($user)->post(route('areas.rw.store'), [
            'number' => 'RW 2',
            'village_id' => $village->id,
        ]);
        $validResponse->assertRedirect(route('areas.index'));
        $this->assertDatabaseHas('rws', [
            'number' => '02',
            'village_id' => $village->id,
        ]);
    }
}
