<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;
use Spatie\Permission\PermissionRegistrar;

class Role extends SpatieRole
{
    protected $attributes = [
        'guard_name' => 'web',
    ];

    protected $guarded = [];

    public function flushCache(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
