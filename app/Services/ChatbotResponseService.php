<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Throwable;

/** Coordinates local chatbot responses before falling back to Gemini. */
class ChatbotResponseService
{
    public function __construct(
        private readonly CalorieCalculatorService $calculator,
        private readonly ChatbotMemberDataService $memberData,
        private readonly ChatbotKnowledgeService $knowledge,
        private readonly GeminiService $gemini,
    ) {}

    /** @return array{reply: string, calorie_result?: array|null} */
    public function respond(User $user, string $message, array $history = []): array
    {
        try {
            // Keep explicit calorie calculation ahead of member-data lookups.
            if ($this->calculator->isExplicitCalculationRequest($message)) {
                $answer = $this->calculator->answer($user, $message, $history);
                if ($answer !== null) {
                    return ['reply' => $answer, 'calorie_result' => $this->calculator->resultData()];
                }
            }

            $answer = $this->memberData->answer($user, $message);
            if ($answer !== null) {
                return ['reply' => $answer];
            }

            // Preserve calculator follow-up handling and calorie card payload.
            $answer = $this->calculator->answer($user, $message, $history);
            if ($answer !== null) {
                return ['reply' => $answer, 'calorie_result' => $this->calculator->resultData()];
            }

            $answer = $this->knowledge->findAnswer($message);
            if ($answer !== null) {
                return ['reply' => $answer];
            }
        } catch (Throwable $exception) {
            Log::error('Chatbot local response failed', [
                'user_id' => $user->getKey(),
                'exception' => get_class($exception),
                'message' => $exception->getMessage(),
            ]);

            return ['reply' => 'حصل خطأ أثناء قراءة بيانات الكوتش من النظام. راجع سجل Laravel لمعرفة السبب.'];
        }

        // Preserve existing behavior: Gemini is called outside the local handler try/catch.
        return ['reply' => $this->gemini->askCoach($user, $message, $history)];
    }
}
