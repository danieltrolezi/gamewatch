<?php

namespace App\Providers;

use App\Repositories\UserRepository;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Auth\Authenticatable;

class FirestoreUserProvider implements UserProvider
{
    public function __construct(
        private UserRepository $userRepository
    ) {
    }

    public function retrieveById($identifier)
    {
        return $this->userRepository->findById($identifier);
    }

    public function retrieveByToken($identifier, $token)
    {
        // If needed, implement token-based retrieval here
    }

    public function updateRememberToken(Authenticatable $user, $token)
    {
        // If you're using remember tokens, update it in Firestore here
    }

    public function retrieveByCredentials(array $credentials)
    {
        return $this->userRepository->findByEmail($credentials['email']);
    }

    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        return password_verify($credentials['password'], $user->password);
    }

    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false)
    {
        // If needed, implement password rehashing logic here
    }
}
