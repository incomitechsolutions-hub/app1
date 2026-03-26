<?php

namespace App\Domain\CourseCatalog\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class WebsiteCrawlExtractionService
{
    /**
     * @return array{
     *     source_url:string,
     *     title:string,
     *     h1:string,
     *     meta_description:string,
     *     headings:list<string>,
     *     body_excerpt:string,
     *     seo_keywords:list<string>
     * }
     */
    public function extractSinglePage(string $url): array
    {
        $response = Http::timeout(20)
            ->retry(1, 250)
            ->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (compatible; CourseBot/1.0; +https://app1.hostn.de)',
                'Accept-Language' => 'de,en;q=0.9',
            ])
            ->get($url);

        if (! $response->successful()) {
            throw new RuntimeException(__('Quellseite konnte nicht geladen werden (HTTP :code).', [
                'code' => $response->status(),
            ]));
        }

        $html = (string) $response->body();
        if (trim($html) === '') {
            throw new RuntimeException(__('Quellseite enthält keinen verwertbaren Inhalt.'));
        }

        $title = $this->extractFirstMatch($html, '/<title[^>]*>(.*?)<\/title>/is');
        $h1 = $this->extractFirstMatch($html, '/<h1[^>]*>(.*?)<\/h1>/is');
        $metaDescription = $this->extractMetaDescription($html);
        $headings = $this->extractAllMatches($html, '/<h[2-3][^>]*>(.*?)<\/h[2-3]>/is', 20);
        $bodyExcerpt = $this->extractBodyExcerpt($html, 5000);
        $keywords = $this->extractKeywords([$title, $h1, $metaDescription, ...$headings], 20);

        return [
            'source_url' => $url,
            'title' => $title,
            'h1' => $h1,
            'meta_description' => $metaDescription,
            'headings' => $headings,
            'body_excerpt' => $bodyExcerpt,
            'seo_keywords' => $keywords,
        ];
    }

    private function extractFirstMatch(string $html, string $pattern): string
    {
        if (preg_match($pattern, $html, $matches) !== 1) {
            return '';
        }

        return $this->cleanHtmlText($matches[1] ?? '');
    }

    /**
     * @return list<string>
     */
    private function extractAllMatches(string $html, string $pattern, int $limit): array
    {
        if (preg_match_all($pattern, $html, $matches) !== 1 || ! isset($matches[1])) {
            return [];
        }

        $items = [];
        foreach ($matches[1] as $raw) {
            $value = $this->cleanHtmlText((string) $raw);
            if ($value === '') {
                continue;
            }
            $items[] = $value;
            if (count($items) >= $limit) {
                break;
            }
        }

        return $items;
    }

    private function extractMetaDescription(string $html): string
    {
        if (preg_match('/<meta[^>]+name=["\']description["\'][^>]*content=["\']([^"\']*)["\']/i', $html, $m) === 1) {
            return $this->cleanHtmlText($m[1] ?? '');
        }
        if (preg_match('/<meta[^>]+content=["\']([^"\']*)["\'][^>]*name=["\']description["\']/i', $html, $m) === 1) {
            return $this->cleanHtmlText($m[1] ?? '');
        }

        return '';
    }

    private function extractBodyExcerpt(string $html, int $maxChars): string
    {
        $withoutScripts = preg_replace('/<script\b[^>]*>.*?<\/script>/is', ' ', $html) ?? $html;
        $withoutStyles = preg_replace('/<style\b[^>]*>.*?<\/style>/is', ' ', $withoutScripts) ?? $withoutScripts;
        $text = strip_tags($withoutStyles);
        $text = html_entity_decode((string) $text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/u', ' ', $text) ?? '';
        $text = trim($text);

        if ($text === '') {
            return '';
        }

        return Str::limit($text, $maxChars, '');
    }

    /**
     * @param  list<string>  $candidates
     * @return list<string>
     */
    private function extractKeywords(array $candidates, int $max): array
    {
        $joined = mb_strtolower(implode(' ', $candidates));
        $joined = preg_replace('/[^a-z0-9äöüß\-\s]+/u', ' ', $joined) ?? '';
        $parts = preg_split('/\s+/u', trim($joined)) ?: [];

        $stop = [
            'und', 'oder', 'mit', 'für', 'der', 'die', 'das', 'ein', 'eine', 'von', 'in', 'zu', 'auf',
            'im', 'am', 'an', 'is', 'are', 'the', 'a', 'to', 'of', 'for', 'your', 'you',
        ];
        $stopSet = array_fill_keys($stop, true);

        $seen = [];
        $keywords = [];
        foreach ($parts as $p) {
            $token = trim($p);
            if ($token === '' || isset($stopSet[$token]) || mb_strlen($token) < 3) {
                continue;
            }
            if (isset($seen[$token])) {
                continue;
            }
            $seen[$token] = true;
            $keywords[] = $token;
            if (count($keywords) >= $max) {
                break;
            }
        }

        return $keywords;
    }

    private function cleanHtmlText(string $raw): string
    {
        $text = strip_tags($raw);
        $text = html_entity_decode((string) $text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/u', ' ', $text) ?? '';

        return trim($text);
    }
}

