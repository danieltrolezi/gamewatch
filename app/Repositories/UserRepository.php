<?php

namespace App\Repositories;

use App\Enums\Frequency;
use App\Enums\Period;
use App\Enums\Platform;
use App\Enums\Rawg\RawgGenre;
use App\Enums\Scope;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\LazyCollection;

class UserRepository
{
    /**
     * @param array $settings
     * @return array
     */
    private function getDefaultSettings(array $overrideDefaults = []): array
    {
        return [
            'platforms' => Arr::get($overrideDefaults, 'platforms', Platform::values()),
            'genres'    => Arr::get($overrideDefaults, 'genres', RawgGenre::values()),
            'period'    => Arr::get($overrideDefaults, 'period', Period::Next_30_Days->value),
            'frequency' => Arr::get($overrideDefaults, 'frequency', Frequency::Monthly->value),
        ];
    }

    /**
     * @param array $data
     * @return User
     */
    public function create(array $data): User
    {
        $user = new User();
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->password = bcrypt($data['password']);
        $user->scopes = [Scope::Default->value];

        if (!empty($data['discord_user_id'])) {
            $user->discord_user_id = $data['discord_user_id'];
        }

        $user->settings = $this->getDefaultSettings();
        $user->save();

        return $user;
    }

    public function createRoot(): bool
    {
        $user = User::where('email', config('auth.root.email'))->first();

        if ($user) {
            return false;
        }

        $user = new User();
        $user->name = config('auth.root.name');
        $user->email = config('auth.root.email');
        $user->password = bcrypt(config('auth.root.password'));
        $user->scopes = Scope::values();
        $user->discord_user_id = config('auth.root.discord_user_id');
        $user->settings = $this->getDefaultSettings();
        $user->save();

        return true;
    }

    /**
     * @param array $data
     * @param array $settings
     * @return User
     */
    public function createDiscord(array $data): User
    {
        $user = new User();
        $user->name = $data['name'];
        $user->scopes = [Scope::Default->value];
        $user->discord_user_id = $data['discord_user_id'];
        $user->discord_username = $data['discord_username'];
        $user->discord_channel_id = $data['discord_channel_id'];
        $user->settings = $this->getDefaultSettings();
        $user->save();

        return $user;
    }

    public function findById(string $id): ?User
    {
        return User::find($id);
    }

    /**
     * @param string $email
     * @return User|null
     */
    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    /**
     * @param string $discordUserId
     * @return User|null
     */
    public function findByDiscordId(string $discordUserId): ?User
    {
        return User::where('discord_user_id', $discordUserId)->first();
    }

    /**
     * @param User $user
     * @param array $data
     * @return User
     */
    public function update(User $user, array $data): User
    {
        if (!empty($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }

        $user->update($data);

        return $user;
    }

    /**
     * @return LazyCollection
     */
    public function getDiscordUsersAndSettings(): LazyCollection
    {
        return User::whereNotNull('discord_user_id')
            ->with('settings')
            ->lazy();
    }
}
