<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;

class EventController extends Controller
{
    #[OA\Get(
        path: "/api/admin/events",
        summary: "List all events",
        tags: ["Admin - Event"],
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
        return $this->successResponse(Event::with('categories')->get());
    }

    #[OA\Post(
        path: "/api/admin/events",
        summary: "Create new event",
        tags: ["Admin - Event"],
        security: [["bearerAuth" => []]]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "name", type: "string", example: "Music Festival 2024"),
                new OA\Property(property: "start_time", type: "string", format: "date-time", example: "2024-12-31 20:00:00")
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: "Event created",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status", type: "string", example: "success"),
                new OA\Property(property: "data", type: "object")
            ]
        )
    )]
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_time' => 'required|date',
        ]);

        $validated['slug'] = Str::slug($validated['name']) . '-' . rand(100, 999);
        $event = Event::create($validated);

        return $this->successResponse($event, 'Event created', 201);
    }

    #[OA\Get(
        path: "/api/admin/events/{event}",
        summary: "Get specific event details",
        tags: ["Admin - Event"],
        security: [["bearerAuth" => []]]
    )]
    #[OA\Parameter(
        name: "event",
        in: "path",
        required: true,
        description: "Event ID",
        schema: new OA\Schema(type: "integer")
    )]
    #[OA\Response(response: 200, description: "Success")]
    #[OA\Response(response: 404, description: "Event not found")]
    public function show(Event $event)
    {
        return $this->successResponse($event->load('categories'));
    }

    #[OA\Put(
        path: "/api/admin/events/{event}",
        summary: "Update an event",
        tags: ["Admin - Event"],
        security: [["bearerAuth" => []]]
    )]
    #[OA\Parameter(
        name: "event",
        in: "path",
        required: true,
        description: "Event ID",
        schema: new OA\Schema(type: "integer")
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "name", type: "string", example: "Updated Music Festival"),
                new OA\Property(property: "start_time", type: "string", format: "date-time", example: "2024-12-31 21:00:00"),
                new OA\Property(property: "is_active", type: "boolean", example: true)
            ]
        )
    )]
    #[OA\Response(response: 200, description: "Event updated")]
    public function update(Request $request, Event $event)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_time' => 'required|date',
            'is_active' => 'required|bool'
        ]);

        $validated['slug'] = Str::slug($validated['name']) . '-' . rand(100, 999);

        $event->update($validated);
        return $this->successResponse($event, 'Event updated');
    }

    #[OA\Delete(
        path: "/api/admin/events/{event}",
        summary: "Delete an event",
        tags: ["Admin - Event"],
        security: [["bearerAuth" => []]]
    )]
    #[OA\Parameter(
        name: "event",
        in: "path",
        required: true,
        description: "Event ID",
        schema: new OA\Schema(type: "integer")
    )]
    #[OA\Response(response: 200, description: "Event deleted")]
    public function destroy(Event $event)
    {
        $event->delete();
        return $this->successResponse(null, 'Event deleted');
    }
}
