<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Note;
use App\Models\Role;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NoteControllerTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsWorkspaceUser(Workspace $workspace): User
    {
        $user = User::factory()->create();
        $role = Role::create([
            'name' => 'admin',
            'display_name' => 'Administrator',
        ]);
        $user->addRole($role, $workspace);

        return $user;
    }

    public function test_user_can_store_a_note_on_an_account(): void
    {
        $workspace = Workspace::factory()->create();
        $user = $this->actingAsWorkspaceUser($workspace);
        $account = Account::factory()->create(['workspace_id' => $workspace->id]);

        $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->from('/')
            ->post(route('notes.store'), [
                'noteable_type' => 'account',
                'noteable_id' => $account->id,
                'body' => 'This is a note.',
                'is_internal' => true,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('notes', [
            'workspace_id' => $workspace->id,
            'noteable_type' => Account::class,
            'noteable_id' => $account->id,
            'user_id' => $user->id,
            'body' => 'This is a note.',
            'is_internal' => true,
        ]);
    }

    public function test_user_can_update_a_note(): void
    {
        $workspace = Workspace::factory()->create();
        $user = $this->actingAsWorkspaceUser($workspace);
        $account = Account::factory()->create(['workspace_id' => $workspace->id]);
        $note = Note::factory()->create([
            'workspace_id' => $workspace->id,
            'noteable_type' => Account::class,
            'noteable_id' => $account->id,
            'user_id' => $user->id,
            'body' => 'Original note.',
        ]);

        $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->from('/')
            ->put(route('notes.update', $note), [
                'body' => 'Updated note.',
                'is_internal' => false,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('notes', [
            'id' => $note->id,
            'body' => 'Updated note.',
            'is_internal' => false,
        ]);
    }

    public function test_user_can_delete_a_note(): void
    {
        $workspace = Workspace::factory()->create();
        $user = $this->actingAsWorkspaceUser($workspace);
        $account = Account::factory()->create(['workspace_id' => $workspace->id]);
        $note = Note::factory()->create([
            'workspace_id' => $workspace->id,
            'noteable_type' => Account::class,
            'noteable_id' => $account->id,
            'user_id' => $user->id,
        ]);

        $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->from('/')
            ->delete(route('notes.destroy', $note))
            ->assertRedirect();

        $this->assertDatabaseMissing('notes', [
            'id' => $note->id,
        ]);
    }

    public function test_user_cannot_delete_note_from_other_workspace(): void
    {
        $workspace = Workspace::factory()->create();
        $otherWorkspace = Workspace::factory()->create();
        $user = $this->actingAsWorkspaceUser($workspace);
        $account = Account::factory()->create(['workspace_id' => $otherWorkspace->id]);
        $note = Note::factory()->create([
            'workspace_id' => $otherWorkspace->id,
            'noteable_type' => Account::class,
            'noteable_id' => $account->id,
        ]);

        $this->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->delete(route('notes.destroy', $note))
            ->assertNotFound();
    }
}
