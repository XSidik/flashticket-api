<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;

use OpenApi\Attributes as OA;

class NewPasswordController extends Controller
{
    #[OA\Post(
        path: "/api/reset-password",
        summary: "Reset user password",
        tags: ["Auth"]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "token", type: "string"),
                new OA\Property(property: "email", type: "string", example: "user@example.com"),
                new OA\Property(property: "password", type: "string", example: "new_password"),
                new OA\Property(property: "password_confirmation", type: "string", example: "new_password")
            ]
        )
    )]
    #[OA\Response(response: 200, description: "Password reset successful")]
    #[OA\Response(response: 422, description: "Validation error")]
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->string('password')),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status != Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return $this->successResponse(null, __($status));
    }
}
