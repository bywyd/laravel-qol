<?php

namespace Bywyd\LaravelQol\Tests\Fixtures;

use Bywyd\LaravelQol\Traits\HasRoles;
use Bywyd\LaravelQol\Traits\HasIntegrations;
use Bywyd\LaravelQol\Traits\HasSettings;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasRoles;
    use HasIntegrations;
    use HasSettings;

    protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
}
