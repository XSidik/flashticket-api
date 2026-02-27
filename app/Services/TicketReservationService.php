<?php

namespace App\Services;

use Illuminate\Support\Facades\Redis;

class TicketReservationService
{
    public function syncQuotaToRedis($categoryId, $quota)
    {
        Redis::set("ticket_quota:{$categoryId}", $quota);
    }

    public function getQuota(int $categoryId): int
    {
        // We use (int) cast because Redis returns a string or null
        $quota = Redis::connection()->get("ticket_quota:{$categoryId}");

        return (int) ($quota ?? 0);
    }

    public function reserveInRedis($categoryId)
    {
        $key = "ticket_quota:{$categoryId}";

        // DECR is an atomic operation. If 1000 users enter concurrently,
        // Redis will process them one by one sequentially.
        $remaining = Redis::decr($key);

        if ($remaining < 0) {
            // Restore quota if it's exhausted to prevent it from going negative continuously
            Redis::incr($key);
            return false;
        }

        return true;
    }

    public function releaseToRedis($categoryId)
    {
        Redis::incr("ticket_quota:{$categoryId}");
    }
}
