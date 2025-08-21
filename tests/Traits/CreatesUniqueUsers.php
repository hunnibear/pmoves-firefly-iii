<?php

declare(strict_types=1);

namespace Tests\Traits;

use FireflyIII\User;
use Illuminate\Support\Facades\Hash;

trait CreatesUniqueUsers
{
    /**
     * Create a test user with a unique email to avoid collisions with seeded data.
     *
     * @param array $attributes
     * @return User
     */
    protected function createUniqueUser(array $attributes = []): User
    {
        $email = $attributes['email'] ?? 'test+' . uniqid('', true) . '@firefly';
        $password = $attributes['password'] ?? 'secret';

        $data = array_merge(['email' => $email, 'password' => Hash::make($password)], $attributes);

        return User::create($data);
    }
}
