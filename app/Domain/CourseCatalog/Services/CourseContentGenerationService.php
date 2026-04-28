<?php

namespace App\Domain\CourseCatalog\Services;

use App\Domain\CourseCatalog\Enums\DeliveryFormat;
use App\Services\Ai\AiProviderFactory;

class CourseContentGenerationService
{
    public function __construct(
        private readonly AiProviderFactory $providerFactory,
        private readonly TaxonomyMatchingService $taxonomyMatching,
    ) {}

    /**
     * @param  array<string, mixed>  $input
     * @param  list<string>  $selectedKeywords
     * @return array<string, mixed>
     */
    public function generate(array $input, array $selectedKeywords): array
    {
        $provider = $this->providerFactory->make();
        if ($provider) {
            $result = $provider->generateCourseContent([
                'topic' => $input['topic'] ?? '',
                'subtopics' => $input['subtopics'] ?? [],
                'target_audience' => $input['target_audience'] ?? '',
                'level' => $input['level'] ?? '',
                'duration_days' => $input['duration_days'] ?? null,
                'focus' => $input['focus'] ?? '',
                'selected_keywords' => $selectedKeywords,
            ]);
            if ($result !== []) {
                return $this->mergeDefaults($result, $input, $selectedKeywords);
            }
        }

        return $this->heuristicGenerate($input, $selectedKeywords);
    }

    /**
     * @param  array<string, mixed>  $generated
     * @param  array<string, mixed>  $input
     * @param  list<string>  $selectedKeywords
     * @return array<string, mixed>
     */
    private function mergeDefaults(array $generated, array $input, array $selectedKeywords): array
    {
        $defaults = $this->heuristicGenerate($input, $selectedKeywords);

        return [
            'seo' => array_merge($defaults['seo'], is_array($generated['seo'] ?? null) ? $generated['seo'] : []),
            'base' => array_merge($defaults['base'], is_array($generated['base'] ?? null) ? $generated['base'] : []),
            'details' => array_merge($defaults['details'], is_array($generated['details'] ?? null) ? $generated['details'] : []),
        ];
    }

    /**
     * @param  array<string, mixed>  $input
     * @param  list<string>  $selectedKeywords
     * @return array<string, mixed>
     */
    private function heuristicGenerate(array $input, array $selectedKeywords): array
    {
        $topic = trim((string) ($input['topic'] ?? ''));
        $primary = $selectedKeywords[0] ?? $topic.' schulung';
        $slug = \Illuminate\Support\Str::slug($topic !== '' ? $topic : $primary);
        $title = $topic !== '' ? $topic.' Training' : ucfirst($primary);
        $subTitle = 'Praxisorientierte Schulung fuer Unternehmen';
        $tax = $this->taxonomyMatching->match($selectedKeywords, $topic);
        $duration = (float) ($input['duration_days'] ?? 1);
        $hours = max(1.0, $duration * 8.0);

        return [
            'seo' => [
                'seo_title' => $title.' | '.$primary,
                'meta_description' => 'Lernen Sie '.$topic.' strukturiert und praxisnah. Ideal fuer Teams und Unternehmen.',
                'focus_keyword' => $primary,
                'tags_csv' => implode(', ', array_slice($selectedKeywords, 0, 8)),
                'og_title' => $title,
                'og_description' => 'KI-gestuetzte Schulung: '.$topic,
                'schema_json' => json_encode([
                    '@context' => 'https://schema.org',
                    '@type' => 'Course',
                    'name' => $title,
                    'description' => 'Kurs zu '.$topic,
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'landing_page_url' => '/kurse/'.$slug,
                'canonical_url' => '',
                'robots_index' => '1',
                'robots_follow' => '1',
            ],
            'base' => [
                'status' => 'draft',
                'published_at' => now()->format('Y-m-d\TH:i'),
                'author_name' => '',
                'content_version' => '1.0',
                'title' => $title,
                'subtitle' => $subTitle,
                'slug' => $slug,
                'language_code' => 'de',
                'duration_hours' => $hours,
                'delivery_formats' => array_map(fn ($f) => $f->value, DeliveryFormat::cases()),
                'primary_category_id' => $tax['category_id'],
                'tag_ids' => $tax['tag_ids'],
                'audience_ids' => $tax['audience_ids'],
                'min_participants' => 6,
                'price' => null,
            ],
            'details' => [
                'short_description' => $topic.' kompakt und praxisnah fuer den direkten Einsatz im Unternehmen.',
                'long_description' => "Dieser Kurs vermittelt {$topic} strukturiert von Grundlagen bis Umsetzung.",
                'target_audience_text' => (string) ($input['target_audience'] ?? 'Fachkraefte, Teams, Projektverantwortliche'),
                'prerequisites_text' => 'Grundlegende Computerkenntnisse sind hilfreich.',
                'modules' => [
                    ['title' => 'Einführung', 'description' => 'Ziele und Grundlagen', 'duration_hours' => 2, 'sort_order' => 0],
                    ['title' => 'Praxis', 'description' => 'Anwendungsfaelle und Uebungen', 'duration_hours' => 3, 'sort_order' => 1],
                    ['title' => 'Transfer', 'description' => 'Umsetzung im Unternehmen', 'duration_hours' => 3, 'sort_order' => 2],
                ],
                'objectives' => [
                    ['objective_text' => $topic.' sicher anwenden', 'sort_order' => 0],
                    ['objective_text' => 'Best Practices kennen', 'sort_order' => 1],
                ],
                'prerequisites' => [
                    ['prerequisite_text' => 'Interesse am Thema '.$topic, 'sort_order' => 0],
                ],
                'faqs' => [
                    ['question' => 'Ist der Kurs fuer Einsteiger geeignet?', 'answer' => 'Ja, mit praxisnahen Beispielen.', 'sort_order' => 0],
                ],
            ],
        ];
    }
}

