<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $permissionIds = collect($this->permissionNames())
            ->map(function (string $permissionName) {
                $label = ucwords(str_replace('_', ' ', $permissionName));

                return Permission::firstOrCreate(
                    [
                        'name' => $permissionName,
                        'guard_name' => 'web',
                    ],
                    [
                        'display_name' => $label,
                        'description' => $label,
                    ]
                )->id;
            })
            ->all();

        foreach (['super_admin', 'admin'] as $roleName) {
            $role = Role::query()
                ->where('name', $roleName)
                ->where('guard_name', 'web')
                ->first();

            if ($role !== null) {
                $role->permissions()->syncWithoutDetaching($permissionIds);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Permission::query()
            ->whereIn('name', $this->permissionNames())
            ->get()
            ->each
            ->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * @return list<string>
     */
    private function permissionNames(): array
    {
        return [
            'products_create',
            'products_read',
            'products_update',
            'products_delete',
            'categories_create',
            'categories_read',
            'categories_update',
            'categories_delete',
        ];
    }
};