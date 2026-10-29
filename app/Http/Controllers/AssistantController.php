<?php

namespace App\Http\Controllers;

use App\Services\SmartAssistant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssistantController extends Controller
{
    public function __invoke(Request $request, SmartAssistant $assistant): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:300'],
        ]);

        return response()->json(
            $assistant->answer($request->user(), $validated['message'])
        );
    }
}