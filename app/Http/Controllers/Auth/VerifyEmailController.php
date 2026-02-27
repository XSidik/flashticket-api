<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

use OpenApi\Attributes as OA;

class VerifyEmailController extends Controller
{
    #[OA\Get(
        path: "/api/verify-email/{id}/{hash}",
        summary: "Verify user email",
        tags: ["Auth"],
        security: [["bearerAuth" => []]]
    )]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "string"))]
    #[OA\Parameter(name: "hash", in: "path", required: true, schema: new OA\Schema(type: "string"))]
    #[OA\Response(response: 302, description: "Redirect to dashboard")]
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(
                config('app.frontend_url') . '/dashboard?verified=1'
            );
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return redirect()->intended(
            config('app.frontend_url') . '/dashboard?verified=1'
        );
    }
}
