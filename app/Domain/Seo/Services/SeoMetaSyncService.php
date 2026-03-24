<?php

namespace App\Domain\Seo\Services;

use App\Domain\CourseCatalog\Models\Course;
use App\Domain\Taxonomy\Models\Category;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;

class SeoMetaSyncService
{
    /**
     * @param  array<string, mixed>  $input
     */
    public function sync(Model $owner, array $input): void
    {
        $canonical = $this->defaultCanonical($owner);

        $schemaJson = $this->normalizeSchemaJson($input['schema_json'] ?? null);

        $payload = [
            'seo_title' => $this->nullIfEmptyString($input['seo_title'] ?? null),
            'meta_description' => $this->nullIfEmptyString($input['meta_description'] ?? null),
            'focus_keyword' => $this->nullIfEmptyString($input['focus_keyword'] ?? null),
            'tags_csv' => $this->nullIfEmptyString($input['tags_csv'] ?? null),
            'preview_image_url' => $this->nullIfEmptyString($input['preview_image_url'] ?? null),
            'landing_page_url' => $this->nullIfEmptyString($input['landing_page_url'] ?? null),
            'canonical_url' => $this->canonicalValue($input['canonical_url'] ?? null, $canonical),
            'robots_index' => array_key_exists('robots_index', $input) ? $this->boolish($input['robots_index']) : true,
            'robots_follow' => array_key_exists('robots_follow', $input) ? $this->boolish($input['robots_follow']) : true,
            'og_title' => $this->nullIfEmptyString($input['og_title'] ?? null),
            'og_description' => $this->nullIfEmptyString($input['og_description'] ?? null),
            'og_image_media_asset_id' => $this->nullIfEmpty($input['og_image_media_asset_id'] ?? null),
            'schema_json' => $schemaJson,
        ];

        $owner->seoMeta()->updateOrCreate([], $payload);
    }

    private function defaultCanonical(Model $owner): ?string
    {
        if ($owner instanceof Course) {
            return URL::route('public.courses.show', ['slug' => $owner->slug], true);
        }
        if ($owner instanceof Category) {
            return URL::route('public.categories.show', ['slug' => $owner->slug], true);
        }

        return null;
    }

    private function canonicalValue(mixed $input, ?string $fallback): ?string
    {
        if ($input === null || $input === '') {
            return $fallback;
        }

        return is_string($input) ? trim($input) : $fallback;
    }

    private function nullIfEmptyString(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return is_string($value) ? trim($value) : null;
    }

    private function nullIfEmpty(mixed $value): mixed
    {
        if ($value === null || $value === '') {
            return null;
        }

        return $value;
    }

    private function boolish(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        $s = (string) $value;

        return $s === '1' || $s === 'true';
    }

    private function normalizeSchemaJson(mixed $value): ?array
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_array($value)) {
            return $value === [] ? null : $value;
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);

            return is_array($decoded) && json_last_error() === JSON_ERROR_NONE ? $decoded : null;
        }

        return null;
    }
}
