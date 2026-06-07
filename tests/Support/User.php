<?php

namespace Ccast\TagixoPrimix\Tests\Support;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * Minimal authenticatable user model used to drive the Primix admin auth
 * guard in the test suite. Not part of the package's runtime surface.
 */
class User extends Authenticatable
{
    use HasFactory;

    protected $table = 'users';

    protected $guarded = [];

    public $timestamps = true;

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }
}
