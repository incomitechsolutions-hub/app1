<?php

namespace Tests\Feature\Admin;

use App\Models\ModuleState;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ModuleVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_disabled_course_catalog_module_blocks_course_routes(): void
    {
        $user = User::factory()->create();

        ModuleState::query()->updateOrCreate(
            ['module_key' => 'course_catalog'],
            ['enabled' => false]
        );

        $this->actingAs($user)
            ->get(route('admin.course-catalog.courses.index'))
            ->assertForbidden();
    }

    public function test_disabled_course_catalog_module_hides_navigation_entry(): void
    {
        $user = User::factory()->create();

        ModuleState::query()->updateOrCreate(
            ['module_key' => 'course_catalog'],
            ['enabled' => false]
        );

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertDontSee('Kursverwaltung');
    }

    public function test_disabled_localization_module_hides_markets_and_locales_navigation_entries(): void
    {
        $user = User::factory()->create();

        ModuleState::query()->updateOrCreate(
            ['module_key' => 'localization'],
            ['enabled' => false]
        );

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertDontSee('Länder')
            ->assertDontSee('Sprachen');
    }

    public function test_disabled_taxonomy_module_hides_categories_navigation_entries(): void
    {
        $user = User::factory()->create();

        ModuleState::query()->updateOrCreate(
            ['module_key' => 'taxonomy'],
            ['enabled' => false]
        );

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertDontSee('Kategorien')
            ->assertDontSee('Hauptkategorien')
            ->assertDontSee('Unterkategorien')
            ->assertDontSee('CSV-Import');
    }

    public function test_module_toggle_endpoint_returns_json_for_ajax_requests(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->patchJson(route('admin.modules.update', 'course_catalog'), [
                'enabled' => false,
            ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'module_key' => 'course_catalog',
                'enabled' => false,
                'status_label' => 'Inaktiv',
            ]);

        $this->assertDatabaseHas('module_states', [
            'module_key' => 'course_catalog',
            'enabled' => 0,
        ]);
    }

    public function test_module_toggle_endpoint_returns_service_unavailable_when_table_is_missing(): void
    {
        $user = User::factory()->create();
        Schema::drop('module_states');

        $response = $this->actingAs($user)
            ->patchJson(route('admin.modules.update', 'course_catalog'), [
                'enabled' => false,
            ]);

        $response->assertStatus(503)
            ->assertJson([
                'success' => false,
                'message' => 'Modulstatus kann aktuell nicht gespeichert werden. Bitte Migrationen ausführen.',
            ]);
    }
}
