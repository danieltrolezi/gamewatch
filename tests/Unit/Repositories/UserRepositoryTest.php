<?php

namespace Tests\Unit\Repositories;

use App\Enums\Frequency;
use App\Enums\Period;
use App\Enums\Platform;
use App\Enums\Rawg\RawgGenre;
use App\Enums\Scope;
use App\Models\User;
use App\Repositories\UserRepository;
use Tests\TestCase;

class UserRepositoryTest extends TestCase
{
    private UserRepository $repository;

    public function setUp(): void
    {
        parent::setUp();
        $this->repository = resolve(UserRepository::class);
    }

    public function test_should_create_user(): void
    {
        $user = $this->repository->create([
            'name'     => 'Test User',
            'email'    => 'test@example.com',
            'password' => $this->faker->password(),
        ]);

        $this->assertInstanceOf(User::class, $user);

        $this->assertDatabaseHas('users', [
            'id'                 => $user->id,
            'name'               => $user->name,
            'email'              => $user->email,
            'scopes'             => [Scope::Default->value],
            'settings.platforms' => Platform::values(),
            'settings.genres'    => RawgGenre::values(),
            'settings.period'    => Period::Next_30_Days->value,
            'settings.frequency' => Frequency::Monthly->value
        ]);
    }

    public function test_should_not_create_more_than_one_root_user()
    {
        $result1 = $this->repository->createRoot();
        $result2 = $this->repository->createRoot();

        $this->assertTrue($result1);
        $this->assertFalse($result2);

        $this->assertDatabaseCount('users', 1);
    }

    public function test_should_update_user(): void
    {
        $user = $this->createUser();

        $result = $this->repository->update($user, [
            'name' => 'Updated User',
        ]);

        $this->assertDatabaseHas('users', [
            'id'   => $user->id,
            'name' => 'Updated User',
        ]);

        $this->assertInstanceOf(User::class, $result);
    }

    public function test_should_update_user_settings()
    {
        $user = $this->createUser();
        $platforms = [Platform::PC->value];

        $result = $this->repository->update($user, [
            'settings' => [
                'platforms' => $platforms
            ]
        ]);

        $this->assertDatabaseHas('users', [
            'id'                 => $user->id,
            'settings.platforms' => $platforms
        ]);

        $this->assertInstanceOf(User::class, $result);
    }
}
