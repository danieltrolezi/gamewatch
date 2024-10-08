<?php

namespace Tests\Feature\Http\Controllers;

use App\Enums\Platform;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class AccountControllerTest extends TestCase
{
    use DatabaseMigrations;

    private User $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createUser();
    }

    private function getUserJsonStructure(): array
    {
        return [
            'id',
            'name',
            'email',
            'scopes',
            'created_at',
            'updated_at',
            'settings'
        ];
    }

    public function test_should_return_authenticated_user()
    {
        $response = $this->actingAs($this->user)->get('/api/account/show');

        $response->assertStatus(200);
        $response->assertJsonStructure(
            $this->getUserJsonStructure()
        );

        $response->assertJsonFragment([
            'id' => $this->user->id
        ]);
    }

    public function test_should_register_user()
    {
        $user = $this->createRootUser();
        $response = $this->actingAs($user)->post('/api/account/register', [
            'name'     => 'Test User',
            'email'    => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure(
            $this->getUserJsonStructure()
        );
    }

    public function test_should_update_user()
    {
        $response = $this->actingAs($this->user)->put('/api/account/update', [
            'name' => 'Test User Updated',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(
            $this->getUserJsonStructure()
        );

        $response->assertJson([
            'name' => 'Test User Updated'
        ]);

        $this->assertDatabaseHas('users', [
            'id'   => $this->user->id,
            'name' => 'Test User Updated'
        ]);
    }

    public function test_should_updated_user_settings()
    {
        $platforms = [Platform::PC->value];
        $response = $this->actingAs($this->user)->put('/api/account/settings', [
            'platforms' => $platforms
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(
            $this->getUserJsonStructure()
        );

        $response->assertJson([
            'settings' => [
                'platforms' => $platforms
            ]
        ]);

        $this->assertDatabaseHas('user_settings', [
            'user_id'   => $this->user->id,
            'platforms' => json_encode([Platform::PC->value])
        ]);
    }
}
