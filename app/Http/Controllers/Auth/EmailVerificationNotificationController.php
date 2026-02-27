<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

use OpenApi\Attributes as OA;

class EmailVerificationNotificationController extends Controller
{
    #[OA\Post(
        path: "/api/email/verification-notification",
        summary: "Resend verification email",
        tags: ["Auth"],
        security: [["bearerAuth" => []]]
    )]
    #[OA\Response(response: 200, description: "Verification link sent")]
    #[OA\Response(response: 302, description: "Already verified")]
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended('/dashboard');
        }

        $request->user()->sendEmailVerificationNotification();

        return response()->json(['status' => 'verification-link-sent']);
    }
}
