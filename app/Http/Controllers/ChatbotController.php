<?php

namespace App\Http\Controllers;

use App\Models\FoodPhotoDraft;
use App\Services\ChatbotResponseService;
use App\Services\FoodPhotoAnalysisService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ChatbotController extends Controller
{
    public function sendMessage(Request $request, ChatbotResponseService $responses, FoodPhotoAnalysisService $photos)
    {
        $validated = $request->validate([
            'message' => 'required_without:photo|nullable|string|max:1000',
            'photo' => 'nullable|file|image|mimetypes:image/jpeg,image/png,image/webp|max:5120|dimensions:max_width=10000,max_height=10000',
            'history' => 'nullable|array|max:30',
            'history.*.sender' => 'required_with:history|string|in:user,bot',
            'history.*.text' => 'required_with:history|string|max:5000',
        ]);

        $user = $request->user();
        abort_unless($user !== null, 401);

        if ($request->hasFile('photo')) {
            $result = $photos->analyze(
                $request->file('photo'),
                $validated['message'] ?? '',
            );
            if ($user->role === 'user' && isset($result['food_estimate'])) {
                $draft = FoodPhotoDraft::create([
                    'id' => (string) Str::uuid(), 'user_id' => $user->id,
                    'items' => $result['food_estimate'], 'expires_at' => now('UTC')->addDay(),
                ]);
                $result['meal_draft'] = ['id' => $draft->id, 'items' => $draft->items,
                    'date' => now('Africa/Cairo')->toDateString(), 'max_date' => now('Africa/Cairo')->toDateString(), 'meal_type' => 'غداء'];
            }
            unset($result['food_estimate']);

            return response()->json($result)->header('Cache-Control', 'private, no-store');
        }

        return response()->json($responses->respond(
            $user,
            $validated['message'],
            $validated['history'] ?? [],
        ));
    }
}
