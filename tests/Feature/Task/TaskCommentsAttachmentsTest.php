<?php

namespace Tests\Feature\Task;

use App\Models\Attachment;
use App\Models\Comment;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TaskCommentsAttachmentsTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Workspace $workspace;

    private Task $task;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->workspace = Workspace::factory()->create();
        $this->user->workspaces()->attach($this->workspace->id, ['role' => 'owner']);

        $project = Project::factory()->create(['workspace_id' => $this->workspace->id]);
        $status = TaskStatus::factory()->create(['workspace_id' => $this->workspace->id]);
        $this->task = Task::factory()->create([
            'workspace_id' => $this->workspace->id,
            'project_id' => $project->id,
            'task_status_id' => $status->id,
        ]);

        $this->actingAs($this->user);
    }

    public function test_user_can_add_comment_to_task()
    {
        $response = $this->withHeaders(['X-Workspace-Id' => $this->workspace->id])
            ->post(route('comments.store'), [
                'commentable_type' => 'task',
                'commentable_id' => $this->task->id,
                'body' => 'This is a test comment.',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('comments', [
            'commentable_id' => $this->task->id,
            'commentable_type' => Task::class,
            'body' => 'This is a test comment.',
            'user_id' => $this->user->id,
        ]);
    }

    public function test_comment_body_is_required()
    {
        $response = $this->withHeaders(['X-Workspace-Id' => $this->workspace->id])
            ->post(route('comments.store'), [
                'commentable_type' => 'task',
                'commentable_id' => $this->task->id,
                'body' => '',
            ]);

        $response->assertSessionHasErrors('body');
    }

    public function test_user_can_delete_own_comment()
    {
        $comment = Comment::factory()->create([
            'workspace_id' => $this->workspace->id,
            'commentable_id' => $this->task->id,
            'commentable_type' => Task::class,
            'user_id' => $this->user->id,
        ]);

        $response = $this->withHeaders(['X-Workspace-Id' => $this->workspace->id])
            ->delete(route('comments.destroy', $comment));

        $response->assertRedirect();
        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    }

    public function test_user_can_upload_attachment_to_task()
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('document.png');

        $response = $this->withHeaders(['X-Workspace-Id' => $this->workspace->id])
            ->post(route('tasks.attachments.store', $this->task), [
                'file' => $file,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('attachments', [
            'attachable_id' => $this->task->id,
            'attachable_type' => Task::class,
            'original_name' => 'document.png',
        ]);
    }

    public function test_attachment_file_is_required()
    {
        $response = $this->withHeaders(['X-Workspace-Id' => $this->workspace->id])
            ->post(route('tasks.attachments.store', $this->task), []);

        $response->assertSessionHasErrors('file');
    }

    public function test_user_can_delete_attachment()
    {
        Storage::fake('public');

        $attachment = Attachment::factory()->create([
            'workspace_id' => $this->workspace->id,
            'attachable_id' => $this->task->id,
            'attachable_type' => Task::class,
            'disk' => 'public',
            'path' => 'attachments/test.png',
        ]);

        $response = $this->withHeaders(['X-Workspace-Id' => $this->workspace->id])
            ->delete(route('tasks.attachments.destroy', [$this->task, $attachment]));

        $response->assertRedirect();
        $this->assertDatabaseMissing('attachments', ['id' => $attachment->id]);
    }
}
