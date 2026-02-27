<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TicketCategory;
use App\Services\TicketReservationService;
use OpenApi\Attributes as OA;

class TicketCategoryController extends Controller
{
    protected $ticketService;

    public function __construct(TicketReservationService $ticketService)
    {
        $this->ticketService = $ticketService;
    }

    #[OA\Get(
        path: "/api/admin/ticket-categories",
        summary: "List all ticket-categories",
        tags: ["Admin - Ticket Category"],
        security: [["bearerAuth" => []]]
    )]
    #[OA\Response(
        response: 200,
        description: "Success",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status", type: "string", example: "success"),
                new OA\Property(property: "data", type: "array", items: new OA\Items(type: "object"))
            ]
        )
    )]
    public function index()
    {
        return $this->successResponse(TicketCategory::get());
    }

    #[OA\Post(
        path: "/api/admin/ticket-categories",
        summary: "Create category & sync to Redis",
        tags: ["Admin - Ticket Category"],
        security: [["bearerAuth" => []]]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "event_id", type: "integer", example: 1),
                new OA\Property(property: "name", type: "string", example: "VIP"),
                new OA\Property(property: "total_quota", type: "integer", example: 100),
                new OA\Property(property: "price", type: "number", example: 500000)
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: "Category created",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status", type: "string", example: "success"),
                new OA\Property(property: "message", type: "string", example: "Ticket category created"),
                new OA\Property(property: "data", type: "object")
            ]
        )
    )]
    public function store(Request $request)
    {
        $validated = $request->validate([
            'event_id' => 'required|exists:events,id',
            'name' => 'required|string',
            'total_quota' => 'required|integer|min:1',
            'price' => 'required|numeric',
        ]);

        $validated['remaining_quota'] = $validated['total_quota'];
        $category = TicketCategory::create($validated);

        // SYNC TO REDIS: So it can be used immediately for the "War"
        $this->ticketService->syncQuotaToRedis($category->id, $category->total_quota);

        return $this->successResponse($category, 'Ticket category created', 201);
    }
}
