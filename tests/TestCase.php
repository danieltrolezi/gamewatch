<?php

namespace Tests;

use App\Enums\Scope;
use App\Models\Game;
use App\Models\User;
use Faker\Factory;
use Faker\Generator;
use Illuminate\Foundation\Testing\TestCase as LaravelTestCase;
use Illuminate\Support\Collection;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Stream;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Redis;
use Mockery;
use Override;
use Tests\Utils\FirestoreTestUtils;

abstract class TestCase extends LaravelTestCase
{
    protected FirestoreTestUtils $firestore;
    protected Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->firestore = $this->app->make(FirestoreTestUtils::class);
        $this->faker = Factory::create();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->firestore->clearData();
    }

    /**
     * @param string|null $password
     * @return User|Authenticatable
     */
    protected function createUser(?string $password = null)
    {
        $data = !empty($password)
            ? ['password' => bcrypt($password)]
            : [];

        return User::factory()
            ->make($data)
            ->save();
    }

    /**
     * @return User||Authenticatable
     */
    protected function createRootUser()
    {
        $data = [
            'scopes' => Scope::values()
        ];

        return User::factory()
            ->make($data)
            ->save();
    }

    protected function createGameCollection(int $total): Collection
    {
        $games = [];

        for ($i = 0; $i < $total; $i++) {
            $games[] = Game::factory()->make();
        }

        return collect($games);
    }

    protected function prepRawgForUnitTesting(): void
    {
        Config::set('services.rawg.host', $this->faker->url());
        Config::set('services.rawg.api_key', $this->faker->password(8, 12));

        $this->mockRedis();
    }

    protected function mockRedis(): void
    {
        Redis::shouldReceive('get')
        ->once()
        ->andReturn(null);

        Redis::shouldReceive('setEx')
        ->once()
        ->andReturn(true);
    }

    protected function createClientMock(string $file)
    {
        $contents = file_get_contents(
            storage_path("tests/$file")
        );

        $body = Mockery::mock(Stream::class)->makePartial();
        $body->shouldReceive('getContents')->andReturn($contents);

        $response = Mockery::mock(Response::class)->makePartial();
        $response->shouldReceive('getBody')->andReturn($body);

        $client = Mockery::mock(Client::class)->makePartial();
        $client->shouldReceive('request')->andReturn($response);

        return $client;
    }

    #[Override]
    public function actingAs(Authenticatable $user, $guard = null)
    {
        if ($this->firestore->findById('users', $user->id)) {
            $this->be($user, $guard);
        } else {
            throw new \Exception('User not found in Firestore');
        }

        return $this;
    }

    #[Override]
    protected function assertDatabaseHas($collection, array $data = [], $connection = null)
    {
        $found = $this->firestore->findByConditions($collection, $data);

        $this->assertTrue(
            $found,
            "Found no record in the [{$collection}] " .
            "collection matching the attributes."
        );
    }

    #[Override]
    protected function assertDatabaseCount($collection, int $count, $connection = null)
    {
        $countInDb = $this->firestore->countRows($collection);

        $this->assertEquals(
            $count,
            $countInDb,
            "Failed asserting that Firestore has [{$count}] " .
            "records in the [{$collection}] collection."
        );
    }
}
