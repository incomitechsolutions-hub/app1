<?php

namespace Tests\Feature\Admin;

use App\Domain\Localization\Models\Locale;
use App\Domain\Taxonomy\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class CategoryCsvImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_csv_import_defaults_duplicate_strategy_to_skip_when_missing(): void
    {
        $user = User::factory()->create();
        $token = $this->previewCsv($user, <<<CSV
name;slug;status
Ohne Strategie;ohne-strategie-feld;draft
CSV);

        $payload = [
            'upload_token' => $token,
            'mapping' => [
                'name' => '0',
                'slug' => '1',
                'status' => '2',
                'description' => '',
                'parent_id' => '',
                'parent_slug' => '',
            ],
            'fallback_status' => 'draft',
        ];

        $this->actingAs($user)
            ->post(route('admin.taxonomy.categories.import.execute'), $payload)
            ->assertRedirect(route('admin.taxonomy.categories.import'));

        $this->assertDatabaseHas('categories', [
            'name' => 'Ohne Strategie',
            'slug' => 'ohne-strategie-feld',
            'status' => 'draft',
        ]);
    }

    public function test_csv_import_skip_strategy_creates_new_and_skips_existing_slug(): void
    {
        $user = User::factory()->create();
        Category::query()->create([
            'name' => 'Bestehend',
            'slug' => 'bestehend',
            'status' => 'draft',
        ]);

        $token = $this->previewCsv($user, <<<CSV
name;slug;status
Neu;neu;published
Soll Ignoriert Werden;bestehend;published
CSV);

        $response = $this->actingAs($user)->post(route('admin.taxonomy.categories.import.execute'), [
            'upload_token' => $token,
            'mapping' => [
                'name' => '0',
                'slug' => '1',
                'status' => '2',
                'description' => '',
                'parent_id' => '',
                'parent_slug' => '',
            ],
            'fallback_status' => 'draft',
            'duplicate_strategy' => 'skip',
        ]);

        $response->assertRedirect(route('admin.taxonomy.categories.import'));

        $this->assertDatabaseHas('categories', [
            'name' => 'Neu',
            'slug' => 'neu',
            'status' => 'published',
        ]);
        $this->assertDatabaseHas('categories', [
            'name' => 'Bestehend',
            'slug' => 'bestehend',
            'status' => 'draft',
        ]);
    }

    public function test_csv_import_update_strategy_updates_existing_slug(): void
    {
        $user = User::factory()->create();
        Category::query()->create([
            'name' => 'Alt',
            'slug' => 'update-slug',
            'status' => 'draft',
        ]);

        $token = $this->previewCsv($user, <<<CSV
name;slug;status
Neu Name;update-slug;archived
CSV);

        $response = $this->actingAs($user)->post(route('admin.taxonomy.categories.import.execute'), [
            'upload_token' => $token,
            'mapping' => [
                'name' => '0',
                'slug' => '1',
                'status' => '2',
                'description' => '',
                'parent_id' => '',
                'parent_slug' => '',
            ],
            'fallback_status' => 'draft',
            'duplicate_strategy' => 'update',
        ]);

        $response->assertRedirect(route('admin.taxonomy.categories.import'));
        $this->assertDatabaseHas('categories', [
            'name' => 'Neu Name',
            'slug' => 'update-slug',
            'status' => 'archived',
        ]);
    }

    public function test_csv_import_persists_category_translations_for_import_locale(): void
    {
        Locale::query()->firstOrCreate(
            ['code' => 'de'],
            ['name' => 'Deutsch', 'is_active' => true, 'sort_order' => 10]
        );

        $user = User::factory()->create();
        $token = $this->previewCsv($user, <<<CSV
name;slug;status
CSV Import;csv-import-locale;published
CSV);

        $this->actingAs($user)->post(route('admin.taxonomy.categories.import.execute'), [
            'upload_token' => $token,
            'mapping' => [
                'name' => '0',
                'slug' => '1',
                'status' => '2',
                'description' => '',
                'parent_id' => '',
                'parent_slug' => '',
            ],
            'fallback_status' => 'draft',
            'duplicate_strategy' => 'skip',
            'import_locale_code' => 'de',
        ])->assertRedirect(route('admin.taxonomy.categories.import'));

        $category = Category::query()->where('slug', 'csv-import-locale')->first();
        $this->assertNotNull($category);
        $localeId = Locale::query()->where('code', 'de')->value('id');
        $this->assertDatabaseHas('category_translations', [
            'category_id' => $category->id,
            'locale_id' => $localeId,
            'name' => 'CSV Import',
            'slug' => 'csv-import-locale',
        ]);
    }

    public function test_csv_import_fail_strategy_rolls_back_on_duplicate_slug(): void
    {
        $user = User::factory()->create();
        Category::query()->create([
            'name' => 'Vorhanden',
            'slug' => 'vorhanden',
            'status' => 'draft',
        ]);

        $token = $this->previewCsv($user, <<<CSV
name;slug;status
Neue Kategorie;neu-kat;draft
Konflikt;vorhanden;published
CSV);

        $response = $this->from(route('admin.taxonomy.categories.import'))
            ->actingAs($user)
            ->post(route('admin.taxonomy.categories.import.execute'), [
                'upload_token' => $token,
                'mapping' => [
                    'name' => '0',
                    'slug' => '1',
                    'status' => '2',
                    'description' => '',
                    'parent_id' => '',
                    'parent_slug' => '',
                ],
                'fallback_status' => 'draft',
                'duplicate_strategy' => 'fail',
            ]);

        $response->assertRedirect(route('admin.taxonomy.categories.import'))
            ->assertSessionHasErrors('import');

        $this->assertDatabaseMissing('categories', [
            'slug' => 'neu-kat',
        ]);
    }

    private function previewCsv(User $user, string $csvContent): string
    {
        $response = $this->actingAs($user)->post(route('admin.taxonomy.categories.import.preview'), [
            'csv_file' => UploadedFile::fake()->createWithContent('categories.csv', $csvContent),
            'delimiter' => ';',
            'has_header' => '1',
        ]);

        $response->assertRedirect(route('admin.taxonomy.categories.import'))
            ->assertSessionHas('category_import_preview');

        $preview = session('category_import_preview');
        $this->assertIsArray($preview);
        $this->assertArrayHasKey('token', $preview);

        return (string) $preview['token'];
    }
}
