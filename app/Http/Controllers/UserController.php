<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class UserController extends Controller
{
    #[OA\Get(
        path: "/api/user",
        summary: "Get currently authenticated user",
        tags: ["Auth"],
        security: [["bearerAuth" => []]]
    )]
    #[OA\Response(
        response: 200,
        description: "Authenticated user details",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status", type: "string", example: "success"),
                new OA\Property(property: "data", type: "object")
            ]
        )
    )]
    public function __invoke(Request $request)
    {
        return $this->successResponse($request->user());
    }
}
