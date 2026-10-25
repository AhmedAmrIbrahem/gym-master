<?php

namespace App\Services;

use App\Models\ChatbotFaq;

class ChatbotKnowledgeService
{
    private function normalize(string $text): string
    {
        $text = mb_strtolower(trim($text), 'UTF-8');
        $text = preg_replace('/[\x{064B}-\x{065F}\x{0670}]/u', '', $text);
        $text = str_replace(['أ', 'إ', 'آ', 'ى', 'ـ', 'ؤ', 'ئ'], ['ا', 'ا', 'ا', 'ي', '', 'و', 'ي'], $text);
        $text = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $text);

        $text = trim(preg_replace('/\s+/u', ' ', $text));

        // Remove harmless conversational wrappers, not words inside the question.
        // Full question equality still decides the answer: no unsafe fuzzy guesses.
        $text = preg_replace('/^(?:(?:يا (?:كوتش|بوت|مدرب)|لو سمحت|بعد اذنك|معلش|طب|ممكن تقولي|ممكن تقوللي) )+/u', '', $text);
        $text = preg_replace('/ (?:يا (?:كوتش|بوت|مدرب)|لو سمحت|بعد اذنك)$/u', '', $text);

        return trim($text);
    }

    public function findAnswer(string $question): ?string
    {
        $needle = $this->normalize($question);
        if ($needle === '') {
            return null;
        }

        // المطابقة التامة فقط، حتى لا نعطي إجابات خاطئة للأسئلة المتشابهة.
        foreach (ChatbotFaq::query()->where('is_active', true)->get() as $faq) {
            $phrases = array_merge([$faq->question], preg_split('/\r\n|\r|\n/u', $faq->aliases ?? '') ?: []);
            foreach ($phrases as $phrase) {
                if ($phrase !== '' && $needle === $this->normalize($phrase)) {
                    return $faq->answer;
                }
            }
        }

        return null;
    }
}
