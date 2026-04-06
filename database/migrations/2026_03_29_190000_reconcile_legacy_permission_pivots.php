<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('permission_user') && Schema::hasTable('model_has_permissions') && Schema::hasTable('users')) {
            $permissionRows = DB::table('permission_user')
                ->join('users', 'users.id', '=', 'permission_user.user_id')
                ->select(['permission_user.permission_id', 'permission_user.user_id as model_id', 'permission_user.user_type as model_type'])
                ->get()
                ->map(fn ($row) => [
                    'permission_id' => $row->permission_id,
                    'model_id' => $row->model_id,
                    'model_type' => $row->model_type ?: App\Models\User::class,
                ])
                ->all();

            if ($permissionRows !== []) {
                DB::table('model_has_permissions')->insertOrIgnore($permissionRows);
            }
        }

        if (Schema::hasTable('role_user') && Schema::hasTable('model_has_roles') && Schema::hasTable('users')) {
            $roleRows = DB::table('role_user')
                ->join('users', 'users.id', '=', 'role_user.user_id')
                ->select(['role_user.role_id', 'role_user.user_id as model_id', 'role_user.user_type as model_type'])
                ->get()
                ->map(fn ($row) => [
                    'role_id' => $row->role_id,
                    'model_id' => $row->model_id,
                    'model_type' => $row->model_type ?: App\Models\User::class,
                ])
                ->all();

            if ($roleRows !== []) {
                DB::table('model_has_roles')->insertOrIgnore($roleRows);
            }
        }

        if (Schema::hasTable('permission_role') && Schema::hasTable('role_has_permissions')) {
            $rolePermissionRows = DB::table('permission_role')
                ->select(['permission_id', 'role_id'])
                ->get()
                ->map(fn ($row) => [
                    'permission_id' => $row->permission_id,
                    'role_id' => $row->role_id,
                ])
                ->all();

            if ($rolePermissionRows !== []) {
                DB::table('role_has_permissions')->insertOrIgnore($rolePermissionRows);
            }
        }
    }

    public function down(): void
    {
        //
    }
};
