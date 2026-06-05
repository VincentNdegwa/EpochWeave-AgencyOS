<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\Role;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            ...$this->profileRules(),
            'password' => $this->passwordRules(),
        ])->validate();

        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => $input['password'],
        ]);

        $adminRole = Role::updateOrCreate(
            ['name' => 'admin'],
            [
                'display_name' => 'Administrator',
                'description' => 'System administrator with full access'
            ]
        );

        $workspace = Workspace::create([
            'name' => strtolower(str_replace(' ', '-', $input['name'])) . '-workspace',
            'display_name' => $input['name'] . "'s Workspace",
            'description' => 'Personal workspace for ' . $input['name'],
        ]);

        $user->addRole($adminRole, $workspace);

        return $user;
    }
}
