<?php

namespace Database\Factories;

use App\Models\Attachment;
use App\Models\Comment;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Attachment>
 */
class AttachmentFactory extends Factory
{
    protected $model = Attachment::class;

    public function definition(): array
    {
        return [
            'workspace_id' => Workspace::factory(),
            'attachable_type' => Comment::class,
            'attachable_id' => Comment::factory(),
            'uploaded_by' => User::factory(),
            'disk' => 'public',
            'path' => 'attachments/'.Str::random(40).'.txt',
            'original_name' => fake()->words(2, true).'.txt',
            'extension' => 'txt',
            'mime_type' => 'text/plain',
            'size' => fake()->numberBetween(100, 10_000),
            'meta' => ['description' => fake()->sentence()],
        ];
    }
}
