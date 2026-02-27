<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponse;
use OpenApi\Attributes as OA;

#[OA\Info(
    title: "Laravel-flashTicket API",
    version: "1.0.0",
    description: "API Documentation for FlashTicket System"
)]
#[OA\Server(
    url: "http://localhost:8000",
    description: "Local Server"
)]
#[OA\SecurityScheme(
    securityScheme: "bearerAuth",
    type: "http",
    scheme: "bearer",
    bearerFormat: "JWT"
)]
abstract class Controller
{
    use ApiResponse;
}
