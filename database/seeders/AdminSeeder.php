<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $permissionNames = collect(config('permissions', []))
            ->flatMap(fn (array $permissions) => array_values($permissions))
            ->unique()
            ->values();

        $permissionIds = $permissionNames
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

        foreach ([
            'super_admin' => [
                'display_name' => 'Super Admin',
                'description' => 'Super Admin',
            ],
            'admin' => [
                'display_name' => 'Admin',
                'description' => 'Admin',
            ],
        ] as $roleName => $attributes) {
            $role = Role::firstOrCreate(
                [
                    'name' => $roleName,
                    'guard_name' => 'web',
                ],
                $attributes
            );
            $role->permissions()->syncWithoutDetaching($permissionIds);
            $role->flushCache();
        }

        $user = User::updateOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'first_name' => 'Admin',
                'last_name' => 'Admin',
                'password' => Hash::make('123456'),
            ]
        );

        $user->syncRolesWithoutDetaching(['super_admin']);
        $user->flushCache();

        foreach ($this->emailsFromEnv('LARATRUST_ADMIN_EMAILS') as $email) {
            if (in_array(strtolower($email), ['admin@admin.com', 'super_admin@app.com'], true)) {
                continue;
            }

            $extra = User::where('email', $email)->first();
            if ($extra === null) {
                continue;
            }

            $extra->syncRolesWithoutDetaching(['admin']);
            $extra->flushCache();
        }
    }

    /**
     * @return list<string>
     */
    private function emailsFromEnv(string $key): array
    {
        $raw = (string) env($key, '');

        return array_values(array_unique(array_filter(
            array_map('trim', explode(',', $raw)),
            fn (string $e) => $e !== ''
        )));
    }
}