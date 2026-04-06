<?php

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRedirectFilter;
use Mcamara\LaravelLocalization\Middleware\LaravelLocalizationViewPath;
use Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect;

beforeEach(function () {
    $this->withoutMiddleware([
        LocaleSessionRedirect::class,
        LaravelLocalizationRedirectFilter::class,
        LaravelLocalizationViewPath::class,
    ]);

    foreach (array_values(config('permissions.users')) as $permissionName) {
        $label = ucwords(str_replace('_', ' ', $permissionName));

        Permission::firstOrCreate(
            ['name' => $permissionName],
            [
                'display_name' => $label,
                'description' => $label,
            ]
        );
    }
});

function managedUser(array $permissions = [], array $attributes = []): User
{
    static $sequence = 1;

    $user = User::create(array_merge([
        'first_name' => 'User',
        'last_name' => (string) $sequence,
        'email' => 'user'.$sequence.'@example.com',
        'password' => 'password',
    ], $attributes));

    $sequence++;

    $user->syncPermissions($permissions);

    return $user->fresh();
}

it('allows any authenticated user to view the user index', function () {
    $user = managedUser([], [
        'first_name' => 'No',
        'last_name' => 'Permissions',
        'email' => 'nopermissions@example.com',
    ]);

    $this->actingAs($user)->get(route('dashboard.users.index'))->assertOk();
});

it('allows a read-only user to view the user index but blocks restricted actions', function () {
    $reader = managedUser([config('permissions.users.read')], [
        'first_name' => 'Read',
        'last_name' => 'Only',
        'email' => 'reader@example.com',
    ]);
    $target = managedUser([], [
        'first_name' => 'Target',
        'last_name' => 'User',
        'email' => 'target@example.com',
    ]);

    $indexResponse = $this->actingAs($reader)->get(route('dashboard.users.index'));

    $indexResponse->assertOk();
    $indexResponse->assertDontSee(route('dashboard.users.create'));
    $indexResponse->assertDontSee(route('dashboard.users.edit', $target));
    $indexResponse->assertDontSee(route('dashboard.users.destroy', $target));

    $this->actingAs($reader)->get(route('dashboard.users.create'))->assertForbidden();
    $this->actingAs($reader)->post(route('dashboard.users.store'), [
        'first_name' => 'Blocked',
        'last_name' => 'User',
        'email' => 'blocked@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'permissions' => [config('permissions.users.read')],
    ])->assertForbidden();
    $this->actingAs($reader)->get(route('dashboard.users.edit', $target))->assertForbidden();
    $this->actingAs($reader)->put(route('dashboard.users.update', $target), [
        'first_name' => $target->first_name,
        'last_name' => $target->last_name,
        'email' => $target->email,
        'password' => '',
        'password_confirmation' => '',
        'permissions' => [config('permissions.users.read')],
    ])->assertForbidden();
    $this->actingAs($reader)->delete(route('dashboard.users.destroy', $target))->assertForbidden();
});

it('removes the admin role and syncs direct permissions when updating a managed user', function () {
    $manager = managedUser([config('permissions.users.read'), config('permissions.users.update')], [
        'first_name' => 'Manager',
        'last_name' => 'User',
        'email' => 'manager@example.com',
    ]);
    $target = managedUser([], [
        'first_name' => 'Legacy',
        'last_name' => 'Admin',
        'email' => 'legacy-admin@example.com',
    ]);

    $adminRole = Role::create([
        'name' => 'admin',
        'display_name' => 'Admin',
        'description' => 'Admin',
    ]);
    $target->addRole($adminRole);
    $target->syncPermissions([config('permissions.users.delete')]);

    $this->actingAs($manager)->put(route('dashboard.users.update', $target), [
        'first_name' => 'Legacy',
        'last_name' => 'Admin',
        'email' => $target->email,
        'password' => '',
        'password_confirmation' => '',
        'permissions' => [config('permissions.users.read')],
    ])->assertRedirect(route('dashboard.users.index'));

    $target->refresh();

    expect($target->hasRole('admin'))->toBeFalse();
    expect($target->permissions->pluck('name')->all())->toBe([config('permissions.users.read')]);
    expect($target->can(config('permissions.users.read')))->toBeTrue();
    expect($target->can(config('permissions.users.delete')))->toBeFalse();
});

it('forbids deleting your own account even with users_delete', function () {
    $deleter = managedUser([config('permissions.users.delete')], [
        'first_name' => 'Self',
        'last_name' => 'Delete',
        'email' => 'self-delete@example.com',
    ]);

    $this->actingAs($deleter)->delete(route('dashboard.users.destroy', $deleter))->assertForbidden();
});
