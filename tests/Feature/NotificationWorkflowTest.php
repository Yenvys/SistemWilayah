<?php

namespace Tests\Feature;

use App\Models\AppNotification;
use App\Models\Complaint;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationWorkflowTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     */
    public function test_updating_complaint_status_creates_notification_for_citizen(): void
    {
        $citizen = User::factory()->create();
        $officer = User::factory()->create(['role' => 'SUPER_ADMIN']);

        $complaint = Complaint::create([
            'user_id' => $citizen->id,
            'title' => 'Lampu Jalan Rusak',
            'description' => 'Lampu jalan di gang mawar mati',
            'status' => 'PENDING',
        ]);

        $this->actingAs($officer)
            ->patch(route('complaints.update-status', $complaint->id), [
                'status' => 'PROCESSING',
                'admin_notes' => 'Sedang dicek oleh teknisi RT',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('complaints', [
            'id' => $complaint->id,
            'status' => 'PROCESSING',
        ]);

        $this->assertDatabaseHas('app_notifications', [
            'user_id' => $citizen->id,
            'type' => 'PENGADUAN',
            'reference_id' => $complaint->id,
            'is_read' => false,
        ]);
    }

    public function test_citizen_can_view_and_mark_notification_as_read(): void
    {
        $citizen = User::factory()->create();

        $notification = AppNotification::create([
            'user_id' => $citizen->id,
            'title' => 'Pembaruan Pengaduan',
            'message' => 'Pengaduan telah selesai ditindaklanjuti',
            'type' => 'PENGADUAN',
            'url' => route('complaints.index'),
            'is_read' => false,
        ]);

        $this->actingAs($citizen)
            ->get(route('notifications.index'))
            ->assertStatus(200)
            ->assertSee('Pembaruan Pengaduan');

        $this->actingAs($citizen)
            ->post(route('notifications.read', $notification->id))
            ->assertRedirect();

        $this->assertTrue($notification->fresh()->is_read);
        $this->assertNotNull($notification->fresh()->read_at);
    }
}
