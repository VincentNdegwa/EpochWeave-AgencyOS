<?php

namespace Tests\Feature\Product;

use App\Models\Role;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductUnitControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_redirects_back_to_referer(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $role = Role::create([
            'name' => 'admin',
            'display_name' => 'Administrator',
        ]);
        $user->addRole($role, $workspace);

        $response = $this->actingAs($user)
            ->from('/products')
            ->post('/product-units', [
                'name' => 'Hour',
                'abbreviation' => 'hr',
            ]);

        $response->assertRedirect('/products');

        $this->assertDatabaseHas('product_units', [
            'workspace_id' => $workspace->id,
            'name' => 'Hour',
            'abbreviation' => 'hr',
        ]);
    }
}
