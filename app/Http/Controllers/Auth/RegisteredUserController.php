<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

use Illuminate\Http\JsonResponse;

use OpenApi\Attributes as OA;

class RegisteredUserController extends Controller
{
    #[OA\Post(
        path: "/api/register",
        summary: "Register a new user",
        tags: ["Auth"]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "name", type: "string", example: "John Doe"),
                new OA\Property(property: "email", type: "string", example: "john@example.com"),
                new OA\Property(property: "password", type: "string", example: "password"),
                new OA\Property(property: "password_confirmation", type: "string", example: "password")
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: "User registered successfully",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status", type: "string", example: "success"),
                new OA\Property(property: "message", type: "string", example: "User registered successfully"),
                new OA\Property(property: "data", type: "object", properties: [
                    new OA\Property(property: "token", type: "string"),
                    new OA\Property(property: "token_type", type: "string")
                ])
            ]
        )
    )]
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->string('password')),
        ]);

        event(new Registered($user));

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->successResponse([
            'token' => $token,
            'token_type' => 'Bearer',
        ], 'User registered successfully', 201);
    }
}
