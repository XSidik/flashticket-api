<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

use OpenApi\Attributes as OA;

class PasswordResetLinkController extends Controller
{
    #[OA\Post(
        path: "/api/forgot-password",
        summary: "Send password reset link",
        tags: ["Auth"]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "email", type: "string", example: "user@example.com")
            ]
        )
    )]
    #[OA\Response(response: 200, description: "Reset link sent")]
    #[OA\Response(response: 422, description: "Validation error")]
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status != Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return $this->successResponse(null, __($status));
    }
}
