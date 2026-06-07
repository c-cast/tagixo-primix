<?php

namespace Ccast\TagixoPrimix\Tests\Support;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name' => 'Test User',
            'email' => Str::random(8).'@example.test',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ];
    }
}
