<?php

namespace Tests\Feature\Http\Controllers;

use App\Enums\Frequency;
use App\Models\User;
use Tests\TestCase;

class AccountControllerTest extends TestCase
{
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
            'settings' => [
                'platforms',
                'genres',
                'period',
                'frequency'
            ],
            'created_at',
            'updated_at'
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
        $data = [
            'name' => 'Test User Updated',
        ];

        $response = $this->actingAs($this->user)->put('/api/account/update', $data);

        $response->assertStatus(200);
        $response->assertJsonStructure(
            $this->getUserJsonStructure()
        );

        $response->assertJson($data);

        $this->assertDatabaseHas('users', [
            'id'   => $this->user->id,
            'name' => $data['name']
        ]);
    }

    public function test_should_update_settings()
    {
        $data = [
            'settings' => [
                'frequency' => Frequency::Weekly->value
            ],
        ];

        $response = $this->actingAs($this->user)->put('/api/account/update', $data);

        $response->assertStatus(200);
        $response->assertJsonStructure(
            $this->getUserJsonStructure()
        );

        $response->assertJson($data);

        $this->assertDatabaseHas('users', [
            'id'                 => $this->user->id,
            'settings.frequency' => $data['settings']['frequency']
        ]);
    }
}
