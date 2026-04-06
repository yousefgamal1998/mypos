<?php

namespace App\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;
use Spatie\Permission\PermissionRegistrar;

class Permission extends SpatiePermission
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
