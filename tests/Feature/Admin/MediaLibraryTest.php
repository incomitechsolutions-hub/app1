<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaLibraryTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_media_library(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.media.index'))
            ->assertOk()
            ->assertSee('Medienverwaltung');
    }

    public function test_user_can_upload_image_to_media_library(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $file = UploadedFile::fake()->image('test-banner.png', 120, 80);

        $this->actingAs($user)
            ->post(route('admin.media.store'), [
                'file' => $file,
                'alt_text' => 'Test banner',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('media_assets', [
            'file_name' => 'test-banner.png',
            'alt_text' => 'Test banner',
        ]);
    }
}
