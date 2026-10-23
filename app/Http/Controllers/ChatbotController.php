<?php

namespace App\Http\Controllers;

use App\Services\ChatbotResponseService;
use Illuminate\Http\Request;

class ChatbotController extends Controller
{
    public function sendMessage(Request $request, ChatbotResponseService $responses)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:1000',
            'history' => 'nullable|array|max:30',
            'history.*.sender' => 'required_with:history|string|in:user,bot',
            'history.*.text' => 'required_with:history|string|max:5000',
        ]);

        $user = $request->user();
        abort_unless($user !== null, 401);

        return response()->json($responses->respond(
            $user,
            $validated['message'],
            $validated['history'] ?? [],
        ));
    }
}
