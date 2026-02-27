<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\TicketReservationService;
use OpenApi\Attributes as OA;
use Illuminate\Support\Str;

class TicketController extends Controller
{
    protected $ticketService;

    public function __construct(TicketReservationService $ticketService)
    {
        $this->ticketService = $ticketService;
    }

    #[OA\Get(
        path: "/api/tickets/quota/{categoryId}",
        summary: "Check remaining ticket quota in Redis",
        tags: ["Discovery"]
    )]
    #[OA\Parameter(
        name: "categoryId",
        description: "Ticket Category ID",
        in: "path",
        required: true,
        schema: new OA\Schema(type: "integer")
    )]
    #[OA\Response(
        response: 200,
        description: "Quota found",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status", type: "string", example: "success"),
                new OA\Property(property: "message", type: "string", example: "Success"),
                new OA\Property(property: "data", type: "object", properties: [
                    new OA\Property(property: "category_id", type: "integer"),
                    new OA\Property(property: "remaining_quota", type: "integer")
                ])
            ]
        )
    )]
    public function getQuota($categoryId)
    {
        $quota = $this->ticketService->getQuota((int) $categoryId);

        return $this->successResponse([
            'category_id' => $categoryId,
            'remaining_quota' => $quota ?? 0
        ]);
    }

    #[OA\Get(
        path: "/api/bookings/my",
        summary: "Get my booking history",
        tags: ["Booking"],
        security: [["bearerAuth" => []]]
    )]
    #[OA\Response(
        response: 200,
        description: "List of bookings",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status", type: "string", example: "success"),
                new OA\Property(property: "data", type: "array", items: new OA\Items(type: "object"))
            ]
        )
    )]
    public function myBookings(Request $request)
    {
        // User ID is obtained from auth, for now we use the authenticated user
        $userId = $request->user()->id;
        $bookings = \App\Models\Booking::where('user_id', $userId)
            ->with('ticketCategory.event')
            ->latest()
            ->get();

        return $this->successResponse($bookings);
    }

    #[OA\Post(
        path: "/api/bookings",
        summary: "Perform ticket reservation (War Mode)",
        tags: ["Booking"],
        security: [["bearerAuth" => []]]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "ticket_category_id", type: "integer", example: 1)
            ]
        )
    )]
    #[OA\Response(response: 201, description: "Booking Successful")]
    #[OA\Response(response: 429, description: "Sold Out / High Traffic")]
    public function store(Request $request)
    {
        $request->validate([
            'ticket_category_id' => 'required|integer'
        ]);

        $categoryId = $request->ticket_category_id;
        $user = $request->user();

        // STEP 1: Try to secure quota in Redis (Atomic)
        $isReserved = $this->ticketService->reserveInRedis($categoryId);

        if (!$isReserved) {
            return $this->errorResponse('Tickets sold out!', 429);
        }

        try {
            // STEP 2: Save to Database (PostgreSQL)
            $booking = \DB::transaction(function () use ($user, $categoryId) {
                // Decrement quota in database
                \App\Models\TicketCategory::where('id', $categoryId)
                    ->decrement('remaining_quota');

                return \App\Models\Booking::create([
                    'user_id' => $user->id,
                    'ticket_category_id' => $categoryId,
                    'booking_code' => 'TIX-' . strtoupper(Str::random(10)),
                    'status' => 'reserved',
                    'expires_at' => now()->addMinutes(10),
                ]);
            });

            return $this->successResponse($booking, 'Booking successfully secured!', 201);

        } catch (\Exception $e) {
            // STEP 3: Rollback Redis if DB Error (Very Important!)
            $this->ticketService->releaseToRedis($categoryId);

            return $this->errorResponse('System error occurred', 500);
        }
    }
}
