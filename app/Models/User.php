<?php

namespace App\Models;

use App\Enums\Scope;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Auth\Authenticatable as AutenticatableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use OpenApi\Attributes as OA;

#[OA\Schema()]
class User extends Firestore implements Authenticatable
{
    use AutenticatableTrait;
    use HasFactory;

    #[OA\Property(property: 'id', type: 'string')]
    #[OA\Property(property: 'name', type: 'string')]
    #[OA\Property(property: 'email', type: 'string')]
    #[OA\Property(
        property: 'discord',
        type: 'object',
        properties: [
            new OA\Property(property: 'user_id', type: 'string'),
            new OA\Property(property: 'username', type: 'string'),
            new OA\Property(property: 'channel_id', type: 'string'),
        ]
    )]
    #[OA\Property(
        property: 'settings',
        type: 'object',
        properties: [
            new OA\Property(
                property: 'platforms',
                type: 'array',
                items: new OA\Items(
                    type: 'string',
                    enum: 'App\Enums\Platform'
                ),
            ),
            new OA\Property(
                property: 'genres',
                type: 'array',
                items: new OA\Items(
                    type: 'string',
                    enum: 'App\Enums\Rawg\RawgGenre'
                ),
            ),
            new OA\Property(
                property: 'period',
                type: 'string',
                enum: 'App\Enums\Period'
            ),
            new OA\Property(
                property: 'frequency',
                type: 'string',
                enum: 'App\Enums\Frequency'
            )
        ]
    )]
    #[OA\Property(property: 'created_at', type: 'datetime')]
    #[OA\Property(property: 'updated_at', type: 'datetime')]

    public string $name;
    public string $email;
    public string $password;
    public array $scopes;
    public array $discord;
    public array $settings;

    protected static array $persist = [
        'name',
        'email',
        'password',
        'scopes',
        'discord',
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
