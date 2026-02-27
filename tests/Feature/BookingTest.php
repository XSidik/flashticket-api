<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Event;
use App\Models\TicketCategory;
use Illuminate\Support\Facades\Redis;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $event;
    protected $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['role' => 'customer']);
        $this->event = Event::create([
            'name' => 'Music Fest',
            'slug' => 'music-fest-' . rand(1, 100),
            'start_time' => now()->addDays(10),
        ]);
        $this->category = TicketCategory::create([
            'event_id' => $this->event->id,
            'name' => 'VIP',
            'total_quota' => 10,
            'remaining_quota' => 10,
            'price' => 100000,
        ]);
    }

    public function test_guests_cannot_book()
    {
        $response = $this->postJson('/api/bookings', [
            'ticket_category_id' => $this->category->id,
        ]);

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_book_successfully()
    {
        Redis::shouldReceive('decr')
            ->once()
            ->andReturn(9);

        $response = $this->actingAs($this->user)
            ->postJson('/api/bookings', [
                'ticket_category_id' => $this->category->id,
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'message' => 'Booking successfully secured!'
            ]);

        $this->assertDatabaseHas('bookings', [
            'user_id' => $this->user->id,
            'ticket_category_id' => $this->category->id,
        ]);

        $this->assertEquals(9, $this->category->fresh()->remaining_quota);
    }

    public function test_user_cannot_book_when_sold_out()
    {
        Redis::shouldReceive('decr')
            ->once()
            ->andReturn(-1);

        Redis::shouldReceive('incr')
            ->once();

        $response = $this->actingAs($this->user)
            ->postJson('/api/bookings', [
                'ticket_category_id' => $this->category->id,
            ]);

        $response->assertStatus(429)
            ->assertJson([
                'status' => 'error',
                'message' => 'Tickets sold out!'
            ]);
    }

    public function test_user_can_see_their_bookings()
    {
        \App\Models\Booking::create([
            'user_id' => $this->user->id,
            'ticket_category_id' => $this->category->id,
            'booking_code' => 'TIX-TEST123',
            'status' => 'reserved',
            'expires_at' => now()->addMinutes(10),
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/bookings/my');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }
}
