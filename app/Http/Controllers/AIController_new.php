<?php

declare(strict_types=1);

namespace FireflyIII\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AIController extends Controller
{
    public function index()
    {
        return view('ai.index');
    }

    public function chat()
    {
        return view('ai.chat');
    }

    public function handleChat(Request $request): JsonResponse
    {
        return response()->json(['message' => 'AI service temporarily unavailable']);
    }

    public function testConnection(): JsonResponse
    {
        return response()->json(['status' => 'Service temporarily unavailable']);
    }
}
