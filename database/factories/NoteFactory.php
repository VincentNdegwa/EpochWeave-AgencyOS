<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Note;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Note>
 */
class NoteFactory extends Factory
{
    protected $model = Note::class;

    public function definition(): array
    {
        return [
            'workspace_id' => Workspace::factory(),
            'noteable_type' => Account::class,
            'noteable_id' => Account::factory(),
            'user_id' => User::factory(),
            'body' => fake()->paragraph(),
            'is_internal' => fake()->boolean(20),
            'pinned_at' => null,
        ];
    }
}
