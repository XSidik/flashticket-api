<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class AuthenticatedSessionController extends Controller
{
    #[OA\Post(
        path: "/api/login",
        summary: "Login to the application",
        tags: ["Auth"]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "email", type: "string", example: "test@example.com"),
                new OA\Property(property: "password", type: "string", example: "password")
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: "Login successful",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status", type: "string", example: "success"),
                new OA\Property(property: "message", type: "string", example: "Login successful"),
                new OA\Property(property: "data", type: "object", properties: [
                    new OA\Property(property: "token", type: "string"),
                    new OA\Property(property: "token_type", type: "string")
                ])
            ]
        )
    )]
    #[OA\Response(response: 422, description: "Invalid credentials")]
    public function store(LoginRequest $request)
    {
        $request->authenticate();

        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->successResponse([
            'token' => $token,
            'token_type' => 'Bearer',
        ], 'Login successful');
    }

    #[OA\Post(
        path: "/api/logout",
        summary: "Logout from the application",
        tags: ["Auth"],
        security: [["bearerAuth" => []]]
    )]
    #[OA\Response(response: 200, description: "Logout successful")]
    public function destroy(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->successResponse(null, 'Logout successful');
    }
}
