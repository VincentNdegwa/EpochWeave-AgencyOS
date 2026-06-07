<?php

namespace Tests\Feature\Upload;

use App\Models\Role;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UploadControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_upload_and_delete_a_temporary_file(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $role = Role::create([
            'name' => 'admin',
            'display_name' => 'Administrator',
        ]);

        $user->addRole($role, $workspace);

        $file = UploadedFile::fake()->image('logo.png');

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->post(route('uploads.store'), [
                'files' => [
                    'logo' => $file,
                ],
            ]);

        $response->assertOk();
        $response->assertJsonStructure(['logo']);

        $logoUrl = $response->json('logo');
        $this->assertIsString($logoUrl);

        $path = str_replace(Storage::disk('public')->url(''), '', $logoUrl);
        Storage::disk('public')->assertExists($path);

        $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->delete(route('uploads.destroy', ['key' => 'logo']))
            ->assertOk();

        Storage::disk('public')->assertMissing($path);
    }

    public function test_temporary_logo_is_moved_to_workspace_logo_on_save(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $workspace = Workspace::factory()->create([
            'domain' => 'example.com',
        ]);
        $role = Role::create([
            'name' => 'admin',
            'display_name' => 'Administrator',
        ]);

        $user->addRole($role, $workspace);

        $file = UploadedFile::fake()->image('logo.png');

        $uploadResponse = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->post(route('uploads.store'), [
                'files' => [
                    'logo' => $file,
                ],
            ]);

        $logoUrl = $uploadResponse->json('logo');

        $this->assertIsString($logoUrl);

        $response = $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->patch(route('workspace-settings.general.update'), [
                'name' => 'New Workspace Name',
                'white_label' => false,
                'logo_url' => $logoUrl,
                'primary_color' => '#0F172A',
            ]);

        $response->assertRedirect(route('workspace-settings.general'));

        $workspace->refresh();

        $this->assertNotSame($logoUrl, $workspace->logo_url);
        $this->assertStringContainsString(
            "/storage/uploads/workspaces/{$workspace->id}/logo/",
            $workspace->logo_url,
        );

        $this->assertCount(
            0,
            Storage::disk('public')->allFiles('uploads/tmp/'.$user->id),
        );
        Storage::disk('public')->assertExists(
            str_replace(Storage::disk('public')->url(''), '', $workspace->logo_url),
        );
    }
}
