<?php

namespace App\Http\Controllers;

use App\Http\Requests\DiscordInteractionRequest;
use App\Services\Discord\DiscordInteractionsService;
use Illuminate\Http\JsonResponse;

class DiscordController extends Controller
{
    public function __construct(
        private DiscordInteractionsService $interactionsService
    ) {
    }

    public function interactions(DiscordInteractionRequest $request): JsonResponse
    {
        $payload = $request->validated();

        return response()->json(
            $this->interactionsService->handleInteractions($payload)
        );
    }
}
