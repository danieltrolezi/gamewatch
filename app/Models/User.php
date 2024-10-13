<?php

namespace App\Models;

use App\Enums\Scope;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Auth\Authenticatable as AutenticatableTrait;
use OpenApi\Attributes as OA;

#[OA\Schema()]
class User extends Firestore implements Authenticatable
{
    use AutenticatableTrait;

    // @TODO Test Swagger
    #[OA\Property(property: 'id', type: 'integer')]
    #[OA\Property(property: 'name', type: 'string')]
    #[OA\Property(property: 'email', type: 'string')]
    #[OA\Property(property: 'discord_user_id', type: 'string')]
    #[OA\Property(property: 'discord_username', type: 'string')]
    #[OA\Property(property: 'discord_channel_id', type: 'string')]
    #[OA\Property(property: 'created_at', type: 'datetime')]
    #[OA\Property(property: 'updated_at', type: 'datetime')]

    #[OA\Property(property: 'settings', ref: '#/components/schemas/UserSetting')]

    #[OA\Property(property: 'id', type: 'integer')]
    #[OA\Property(property: 'user_id', type: 'integer')]
    #[OA\Property(
        property: 'platforms',
        type: 'array',
        items: new OA\Items(
            type: 'string',
            enum: 'App\Enums\Platform'
        ),
    )]
    #[OA\Property(
        property: 'genres',
        type: 'array',
        items: new OA\Items(
            type: 'string',
            enum: 'App\Enums\Rawg\RawgGenre'
        ),
    )]
    #[OA\Property(
        property: 'period',
        type: 'string',
        enum: 'App\Enums\Period'
    )]
    #[OA\Property(
        property: 'frequency',
        type: 'string',
        enum: 'App\Enums\Frequency'
    )]

    public string $name;
    public string $email;
    public string $password;
    public array $scopes;
    // TODO transform discord_* in array
    public ?string $discord_user_id = null;
    public ?string $discord_username = null;
    public ?string $discord_channel_id = null;
    public array $settings;

    protected static array $persist = [
        'name',
        'email',
        'password',
        'scopes',
        'discord_user_id',
        'discord_username',
        'discord_channel_id',
        'settings',
    ];

    protected static array $hidden = [
        'password',
    ];

    /**
     * @return string
     */
    public function getKeyName(): string
    {
        return 'id';
    }

    /**
     * @return boolean
     */
    public function isRoot(): bool
    {
        return in_array(Scope::Root->value, $this->scopes);
    }
}
