<?php

namespace Tests\Unit\Domain\Seo;

use App\Domain\Seo\Services\KeywordSelectionService;
use PHPUnit\Framework\TestCase;

class KeywordSelectionServiceTest extends TestCase
{
    public function test_fallback_when_no_ranked_rows(): void
    {
        $svc = new KeywordSelectionService;
        $sel = $svc->select('Machine Learning', []);

        $this->assertStringContainsString('Machine Learning', $sel['primary_keyword']);
        $this->assertStringContainsString('schulung', $sel['primary_keyword']);
        $this->assertCount(3, $sel['keyword_variants']);
    }

    public function test_deduplicates_keywords(): void
    {
        $svc = new KeywordSelectionService;
        $ranked = [
            ['keyword' => 'Foo Bar', 'score' => 10, 'reasons' => []],
            ['keyword' => 'foo bar', 'score' => 9, 'reasons' => []],
        ];
        $sel = $svc->select('Foo', $ranked);

        $this->assertSame('Foo Bar', $sel['primary_keyword']);
        $this->assertCount(1, $sel['all_keywords']);
    }
}
