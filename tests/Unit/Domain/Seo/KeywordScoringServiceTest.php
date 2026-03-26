<?php

namespace Tests\Unit\Domain\Seo;

use App\Domain\Seo\Services\KeywordScoringService;
use PHPUnit\Framework\TestCase;

class KeywordScoringServiceTest extends TestCase
{
    public function test_rank_is_sorted_by_score_desc_then_keyword(): void
    {
        $svc = new KeywordScoringService;
        $idea = 'Python Grundlagen für Teams';
        $ranked = $svc->rank(['zz', 'python schulung', 'python'], $idea);

        $this->assertSame('python schulung', $ranked[0]['keyword']);
        $this->assertGreaterThanOrEqual($ranked[0]['score'], $ranked[1]['score']);
    }

    public function test_single_word_gets_generic_penalty(): void
    {
        $svc = new KeywordScoringService;
        $s = $svc->score('allgemein', 'Python Kurs');
        $this->assertContains('Sehr generisch (ein Wort).', $s['reasons']);
    }
}
