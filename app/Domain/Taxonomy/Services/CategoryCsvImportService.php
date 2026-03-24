<?php

namespace App\Domain\Taxonomy\Services;

use App\Domain\Localization\Models\Locale;
use App\Domain\Taxonomy\Models\Category;
use App\Domain\Taxonomy\Models\CategoryTranslation;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use SplFileObject;

class CategoryCsvImportService
{
    /**
     * @return array<string, mixed>
     */
    public function preview(UploadedFile $file, string $delimiter, bool $hasHeader): array
    {
        $token = (string) Str::uuid();
        $storedPath = $file->storeAs(
            'imports/categories',
            $token.'-'.$file->getClientOriginalName(),
            'local'
        );

        if ($storedPath === false) {
            throw new RuntimeException('CSV-Datei konnte nicht gespeichert werden.');
        }

        $fullPath = Storage::disk('local')->path($storedPath);
        $rows = $this->readCsv($fullPath, $delimiter);
        $headers = $this->resolveHeaders($rows, $hasHeader);
        $dataRows = $hasHeader ? array_slice($rows, 1) : $rows;
        $sampleRows = array_slice($dataRows, 0, 5);

        $preview = [
            'token' => $token,
            'file_name' => $file->getClientOriginalName(),
            'path' => $storedPath,
            'delimiter' => $delimiter,
            'has_header' => $hasHeader,
            'headers' => $headers,
            'row_count' => count($dataRows),
            'sample_rows' => $sampleRows,
        ];

        Cache::put($this->cacheKey($token), $preview, now()->addHours(2));

        return $preview;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function import(array $payload): array
    {
        $token = (string) ($payload['upload_token'] ?? '');
        $preview = Cache::get($this->cacheKey($token));
        if (! is_array($preview)) {
            throw ValidationException::withMessages([
                'upload_token' => __('Die Import-Vorschau ist abgelaufen. Bitte CSV erneut hochladen.'),
            ]);
        }

        $mapping = $this->normalizeMapping($payload['mapping'] ?? []);
        $fallbackStatus = (string) $payload['fallback_status'];
        $duplicateStrategy = (string) $payload['duplicate_strategy'];
        $importLocaleCode = (string) ($payload['import_locale_code'] ?? 'de');
        $localeId = Locale::query()->where('code', $importLocaleCode)->value('id');
        $rows = $this->readCsv(Storage::disk('local')->path((string) $preview['path']), (string) $preview['delimiter']);
        $dataRows = (bool) $preview['has_header'] ? array_slice($rows, 1) : $rows;
        $maxColumns = $this->maxColumns($rows);

        $this->assertRequiredMapping($mapping, $maxColumns);

        $summary = [
            'total_rows' => count($dataRows),
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => 0,
        ];
        $errors = [];

        DB::transaction(function () use (
            $dataRows,
            $mapping,
            $fallbackStatus,
            $duplicateStrategy,
            $localeId,
            $preview,
            &$summary,
            &$errors
        ): void {
            $pendingParents = [];
            $parentFieldMapped = $mapping['parent_id'] !== null || $mapping['parent_slug'] !== null;

            foreach ($dataRows as $index => $row) {
                $lineNumber = ((bool) $preview['has_header'] ? 2 : 1) + $index;
                $rowValues = $this->normalizeRow($row);

                $name = trim($this->mapValue($rowValues, $mapping['name']));
                $slug = Str::of(trim($this->mapValue($rowValues, $mapping['slug'])))->lower()->toString();
                $description = trim($this->mapValue($rowValues, $mapping['description']));
                $statusRaw = trim($this->mapValue($rowValues, $mapping['status']));
                $status = $statusRaw !== '' ? $statusRaw : $fallbackStatus;
                $parentIdRaw = trim($this->mapValue($rowValues, $mapping['parent_id']));
                $parentSlug = Str::of(trim($this->mapValue($rowValues, $mapping['parent_slug'])))->lower()->toString();

                if ($name === '') {
                    $errors[] = "Zeile {$lineNumber}: Name fehlt.";
                    $summary['errors']++;

                    continue;
                }

                if ($slug === '') {
                    $errors[] = "Zeile {$lineNumber}: Slug fehlt.";
                    $summary['errors']++;

                    continue;
                }

                if (! preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
                    $errors[] = "Zeile {$lineNumber}: Slug-Format ist ungültig.";
                    $summary['errors']++;

                    continue;
                }

                if (! in_array($status, ['draft', 'published', 'archived'], true)) {
                    $errors[] = "Zeile {$lineNumber}: Status '{$status}' ist ungültig.";
                    $summary['errors']++;

                    continue;
                }

                $existing = Category::query()->where('slug', $slug)->first();
                if ($existing !== null && $duplicateStrategy === 'fail') {
                    throw new RuntimeException("Import abgebrochen in Zeile {$lineNumber}: Slug '{$slug}' existiert bereits.");
                }

                if ($existing !== null && $duplicateStrategy === 'skip') {
                    $summary['skipped']++;

                    continue;
                }

                $attributes = [
                    'name' => $name,
                    'slug' => $slug,
                    'description' => $description !== '' ? $description : null,
                    'status' => $status,
                ];

                if ($existing !== null) {
                    $existing->fill($attributes)->save();
                    $category = $existing;
                    $summary['updated']++;
                } else {
                    $category = Category::query()->create(array_merge($attributes, ['parent_id' => null]));
                    $summary['created']++;
                }

                if ($localeId !== null) {
                    CategoryTranslation::query()->updateOrCreate(
                        [
                            'category_id' => $category->id,
                            'locale_id' => $localeId,
                        ],
                        [
                            'name' => $name,
                            'slug' => $slug,
                            'description' => $description !== '' ? $description : null,
                        ]
                    );
                }

                if (! $parentFieldMapped) {
                    continue;
                }

                $pendingParents[(int) $category->getKey()] = [
                    'line' => $lineNumber,
                    'parent_id' => $parentIdRaw,
                    'parent_slug' => $parentSlug,
                ];
            }

            $this->resolveParents($pendingParents, $errors, $summary);
        });

        $summary['errors'] = count($errors);

        return [
            'preview' => $preview,
            'summary' => $summary,
            'errors' => $errors,
            'duplicate_strategy' => $duplicateStrategy,
        ];
    }

    /**
     * @param  array<int, array<int, string>>  $rows
     * @return array<int, string>
     */
    private function resolveHeaders(array $rows, bool $hasHeader): array
    {
        if ($rows === []) {
            return [];
        }

        $headerSource = $hasHeader ? $rows[0] : $rows[0];
        $headers = [];
        foreach ($headerSource as $index => $value) {
            $value = trim((string) $value);
            if ($value === '') {
                $value = 'Spalte '.($index + 1);
            }

            $headers[] = $value;
        }

        return $headers;
    }

    /**
     * @return array<int, array<int, string>>
     */
    private function readCsv(string $path, string $delimiter): array
    {
        if (! is_file($path)) {
            throw new RuntimeException('CSV-Datei wurde nicht gefunden.');
        }

        $file = new SplFileObject($path);
        $file->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE);
        $file->setCsvControl($delimiter);

        $rows = [];
        foreach ($file as $row) {
            if (! is_array($row)) {
                continue;
            }
            if ($row === [null]) {
                continue;
            }

            $normalized = $this->normalizeRow($row);
            if ($this->rowIsEmpty($normalized)) {
                continue;
            }

            $rows[] = $normalized;
        }

        return $rows;
    }

    /**
     * @param  array<string, mixed>  $mapping
     * @return array<string, int|null>
     */
    private function normalizeMapping(array $mapping): array
    {
        $keys = ['name', 'slug', 'description', 'parent_id', 'parent_slug', 'status'];
        $normalized = [];

        foreach ($keys as $key) {
            $value = $mapping[$key] ?? '';
            if ($value === '' || $value === null) {
                $normalized[$key] = null;

                continue;
            }

            $normalized[$key] = is_numeric($value) ? (int) $value : null;
        }

        return $normalized;
    }

    /**
     * @param  array<string, int|null>  $mapping
     */
    private function assertRequiredMapping(array $mapping, int $maxColumns): void
    {
        if ($mapping['name'] === null || $mapping['slug'] === null) {
            throw ValidationException::withMessages([
                'mapping' => __('Bitte mindestens Name und Slug auf CSV-Spalten mappen.'),
            ]);
        }

        foreach ($mapping as $field => $index) {
            if ($index === null) {
                continue;
            }
            if ($index < 0 || $index >= $maxColumns) {
                throw ValidationException::withMessages([
                    "mapping.{$field}" => __('Ungültiges Mapping für Feld :field.', ['field' => $field]),
                ]);
            }
        }
    }

    /**
     * @param  array<int, string>  $row
     */
    private function mapValue(array $row, ?int $index): string
    {
        if ($index === null) {
            return '';
        }

        return trim((string) ($row[$index] ?? ''));
    }

    /**
     * @param  array<int, mixed>  $row
     * @return array<int, string>
     */
    private function normalizeRow(array $row): array
    {
        return array_map(static fn ($value) => trim((string) ($value ?? '')), array_values($row));
    }

    /**
     * @param  array<int, string>  $row
     */
    private function rowIsEmpty(array $row): bool
    {
        foreach ($row as $value) {
            if ($value !== '') {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  array<int, array{line:int,parent_id:string,parent_slug:string}>  $pendingParents
     * @param  array<int, string>  $errors
     * @param  array<string, int>  $summary
     */
    private function resolveParents(array $pendingParents, array &$errors, array &$summary): void
    {
        if ($pendingParents === []) {
            return;
        }

        $remaining = $pendingParents;
        $maxIterations = count($remaining) + 1;

        for ($iteration = 0; $iteration < $maxIterations && $remaining !== []; $iteration++) {
            $progress = false;
            $slugToId = $this->buildSlugMap();

            foreach ($remaining as $categoryId => $entry) {
                $line = $entry['line'];
                $parentIdRaw = trim($entry['parent_id']);
                $parentSlugRaw = trim($entry['parent_slug']);

                $targetParentId = null;
                if ($parentIdRaw !== '') {
                    if (! ctype_digit($parentIdRaw)) {
                        $errors[] = "Zeile {$line}: parent_id ist keine gültige Zahl.";
                        unset($remaining[$categoryId]);
                        $summary['errors']++;
                        $progress = true;

                        continue;
                    }
                    $candidate = (int) $parentIdRaw;
                    if (! Category::query()->whereKey($candidate)->exists()) {
                        continue;
                    }
                    $targetParentId = $candidate;
                } elseif ($parentSlugRaw !== '') {
                    $targetParentId = $slugToId[Str::lower($parentSlugRaw)] ?? null;
                    if ($targetParentId === null) {
                        continue;
                    }
                }

                if ($targetParentId === null) {
                    Category::query()->whereKey($categoryId)->update(['parent_id' => null]);
                    unset($remaining[$categoryId]);
                    $progress = true;

                    continue;
                }

                if ($targetParentId === $categoryId) {
                    $errors[] = "Zeile {$line}: Kategorie kann nicht ihr eigener Parent sein.";
                    unset($remaining[$categoryId]);
                    $summary['errors']++;
                    $progress = true;

                    continue;
                }

                if ($this->isDescendantOf($targetParentId, $categoryId)) {
                    $errors[] = "Zeile {$line}: Zirkuläre Parent-Zuordnung erkannt.";
                    unset($remaining[$categoryId]);
                    $summary['errors']++;
                    $progress = true;

                    continue;
                }

                Category::query()->whereKey($categoryId)->update(['parent_id' => $targetParentId]);
                unset($remaining[$categoryId]);
                $progress = true;
            }

            if (! $progress) {
                break;
            }
        }

        foreach ($remaining as $entry) {
            $line = $entry['line'];
            $errors[] = "Zeile {$line}: Parent konnte nicht aufgelöst werden.";
            $summary['errors']++;
        }
    }

    /**
     * @return array<string, int>
     */
    private function buildSlugMap(): array
    {
        $map = [];
        foreach (Category::query()->get(['id', 'slug']) as $category) {
            $map[Str::lower((string) $category->slug)] = (int) $category->id;
        }

        return $map;
    }

    private function isDescendantOf(int $candidateParentId, int $categoryId): bool
    {
        $cursor = Category::query()->find($candidateParentId);
        $visited = [];

        while ($cursor !== null) {
            if ((int) $cursor->id === $categoryId) {
                return true;
            }
            if (isset($visited[(int) $cursor->id])) {
                return true;
            }
            $visited[(int) $cursor->id] = true;

            if ($cursor->parent_id === null) {
                return false;
            }

            $cursor = Category::query()->find((int) $cursor->parent_id);
        }

        return false;
    }

    /**
     * @param  array<int, array<int, string>>  $rows
     */
    private function maxColumns(array $rows): int
    {
        $max = 0;
        foreach ($rows as $row) {
            $max = max($max, count($row));
        }

        return $max;
    }

    private function cacheKey(string $token): string
    {
        return 'taxonomy_category_import_'.$token;
    }
}
