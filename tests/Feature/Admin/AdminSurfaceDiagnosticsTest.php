<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Guards the URLs and asset wiring described in docs/operations/workflows.md (Admin UI diagnostics).
 */
class AdminSurfaceDiagnosticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_is_at_slash_admin_not_categories(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Dashboard', false);
    }

    public function test_category_management_is_at_admin_categories(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.taxonomy.categories.index'))
            ->assertOk()
            ->assertSee('Kategorien', false);
    }

    public function test_admin_dashboard_html_references_vite_assets_when_build_manifest_exists(): void
    {
        if (! is_file(public_path('build/manifest.json'))) {
            $this->markTestSkipped('Run npm run build to generate public/build/manifest.json (CI should build front-end before phpunit).');
        }

        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('admin.dashboard'));
        $response->assertOk();

        $html = $response->getContent();
        $this->assertNotFalse($html);
        $referencesBuild = str_contains($html, '/build/');
        $referencesDevServer = str_contains($html, '5173') || str_contains($html, 'localhost');

        $this->assertTrue(
            $referencesBuild || $referencesDevServer,
            'Admin layout should load Vite assets: expect /build/ (production) or dev-server URL when public/hot is present.'
        );
    }
}
