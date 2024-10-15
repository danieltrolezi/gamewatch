<?php

namespace Database\Factories;

use App\Enums\Frequency;
use App\Enums\Period;
use App\Enums\Platform;
use App\Enums\Rawg\RawgGenre;
use App\Enums\Scope;
use DateTime;
use Google\Cloud\Core\Timestamp;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $now = new Timestamp(new DateTime());

        return [
            'name'               => fake()->name(),
            'email'              => fake()->unique()->safeEmail(),
            'password'           => static::$password ??= Hash::make('password'),
            'scopes'             => [Scope::Default->value],
            'settings'           => [
                'platforms' => Platform::values(),
                'genres'    => RawgGenre::values(),
                'period'    => Period::Next_30_Days->value,
                'frequency' => Frequency::Monthly->value,
            ],
            'created_at'         => $now,
            'updated_at'         => $now,
        ];
    }
}
