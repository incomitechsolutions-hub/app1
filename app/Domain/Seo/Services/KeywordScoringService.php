<?php

namespace App\Domain\Seo\Services;

class KeywordScoringService
{
    private const TRAINING_TERMS = ['kurs', 'training', 'schulung'];

    private const BUSINESS_TERMS = ['unternehmen', 'firma'];

    /**
     * @return array{score: int, reasons: array<int, string>}
     */
    public function score(string $keyword, string $courseIdea): array
    {
        $keyword = KeywordNormalizer::trimDisplay($keyword);
        $reasons = [];
        $score = 0;

        if ($keyword === '') {
            return ['score' => 0, 'reasons' => []];
        }

        $lower = mb_strtolower($keyword, 'UTF-8');

        foreach (self::TRAINING_TERMS as $term) {
            if (str_contains($lower, $term)) {
                $score += 5;
                $reasons[] = 'Enthält Begriff aus Kurs-/Training-Kontext ('.$term.').';

                break;
            }
        }

        foreach (self::BUSINESS_TERMS as $term) {
            if (str_contains($lower, $term)) {
                $score += 4;
                $reasons[] = 'Enthält Unternehmensbezug ('.$term.').';

                break;
            }
        }

        $wc = KeywordNormalizer::wordCount($keyword);
        if ($wc >= 3) {
            $score += 3;
            $reasons[] = 'Longtail (mindestens drei Wörter).';
        }

        $ideaTokens = KeywordNormalizer::ideaTokens($courseIdea);
        if ($ideaTokens !== []) {
            $overlap = 0;
            foreach ($ideaTokens as $tok) {
                if (str_contains($lower, $tok)) {
                    $overlap++;
                }
            }
            if ($overlap > 0) {
                $score += 2;
                $reasons[] = 'Überlappung mit der Kursidee ('.$overlap.' Begriffe).';
            }
        }

        if ($wc === 1) {
            $score -= 2;
            $reasons[] = 'Sehr generisch (ein Wort).';
        }

        return ['score' => $score, 'reasons' => $reasons];
    }

    /**
     * @param  array<int, string>  $keywords
     * @return array<int, array{keyword: string, score: int, reasons: array<int, string>}>
     */
    public function rank(array $keywords, string $courseIdea): array
    {
        $rows = [];
        foreach ($keywords as $kw) {
            $kw = KeywordNormalizer::trimDisplay((string) $kw);
            if ($kw === '') {
                continue;
            }
            $s = $this->score($kw, $courseIdea);
            $rows[] = [
                'keyword' => $kw,
                'score' => $s['score'],
                'reasons' => $s['reasons'],
            ];
        }

        usort($rows, function (array $a, array $b): int {
            if ($a['score'] !== $b['score']) {
                return $b['score'] <=> $a['score'];
            }

            return strcmp($a['keyword'], $b['keyword']);
        });

        return array_values($rows);
    }
}
