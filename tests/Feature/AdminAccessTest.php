<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Event;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->customer = User::factory()->create(['role' => 'customer']);
    }

    public function test_customer_cannot_access_admin_events()
    {
        $response = $this->actingAs($this->customer)
            ->getJson('/api/admin/events');

        $response->assertStatus(403);
    }

    public function test_admin_can_access_admin_events()
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/api/admin/events');

        $response->assertStatus(200);
    }

    public function test_admin_can_create_event()
    {
        $eventData = [
            'name' => 'Legacy Festival',
            'start_time' => '2025-01-01 20:00:00',
        ];

        $response = $this->actingAs($this->admin)
            ->postJson('/api/admin/events', $eventData);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'message' => 'Event created'
            ]);

        $this->assertDatabaseHas('events', [
            'name' => 'Legacy Festival'
        ]);

        // Check if slug was generated
        $event = Event::where('name', 'Legacy Festival')->first();
        $this->assertNotNull($event->slug);
    }
}
