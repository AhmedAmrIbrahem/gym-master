<?php

namespace Tests\Unit;

use App\Services\GeminiReplyExtractor;
use PHPUnit\Framework\TestCase;

class GeminiReplyExtractorTest extends TestCase
{
    public function test_it_joins_text_parts_and_skips_thought_parts(): void
    {
        $parts = [
            ['text' => 'أهلاً '],
            ['text' => 'internal', 'thought' => true],
            ['text' => 'بيك'],
            ['text' => ['unexpected']],
        ];

        $this->assertSame('أهلاً بيك', (new GeminiReplyExtractor)->extract($parts));
    }

    public function test_it_returns_empty_text_for_invalid_or_missing_parts(): void
    {
        $extractor = new GeminiReplyExtractor;

        $this->assertSame('', $extractor->extract(null));
        $this->assertSame('', $extractor->extract([null, ['thought' => true, 'text' => 'hidden'], ['text' => 123]]));
    }
}
