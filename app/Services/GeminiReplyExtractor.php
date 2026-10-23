<?php

namespace App\Services;

/** Extracts user-facing text from Gemini's response parts. */
class GeminiReplyExtractor
{
    public function extract(mixed $parts): string
    {
        return collect(is_array($parts) ? $parts : [])
            ->filter(fn ($part) => is_array($part) && empty($part['thought']))
            ->pluck('text')
            ->filter(fn ($text) => is_string($text))
            ->implode('');
    }
}
