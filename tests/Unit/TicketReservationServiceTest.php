<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\TicketReservationService;
use Illuminate\Support\Facades\Redis;

class TicketReservationServiceTest extends TestCase
{
    protected $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TicketReservationService();
    }

    public function test_sync_quota_to_redis()
    {
        Redis::shouldReceive('set')
            ->once()
            ->with('ticket_quota:1', 100);

        $this->service->syncQuotaToRedis(1, 100);
    }

    public function test_get_quota_from_redis()
    {
        Redis::shouldReceive('connection->get')
            ->once()
            ->with('ticket_quota:1')
            ->andReturn('50');

        $result = $this->service->getQuota(1);

        $this->assertEquals(50, $result);
    }

    public function test_reserve_in_redis_success()
    {
        Redis::shouldReceive('decr')
            ->once()
            ->with('ticket_quota:1')
            ->andReturn(99);

        $result = $this->service->reserveInRedis(1);

        $this->assertTrue($result);
    }

    public function test_reserve_in_redis_fails_when_exhausted()
    {
        Redis::shouldReceive('decr')
            ->once()
            ->with('ticket_quota:1')
            ->andReturn(-1);

        Redis::shouldReceive('incr')
            ->once()
            ->with('ticket_quota:1');

        $result = $this->service->reserveInRedis(1);

        $this->assertFalse($result);
    }

    public function test_release_to_redis()
    {
        Redis::shouldReceive('incr')
            ->once()
            ->with('ticket_quota:1');

        $this->service->releaseToRedis(1);
    }
}
