<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\User;
use App\Services\AvatarImageProcessor;
use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    private const MANAGED_ROLE_BLACKLIST = ['admin'];

    private const PROTECTED_ROLE_NAMES = ['super_admin'];

    private const PROTECTED_USER_EMAILS = ['admin@admin.com', 'super_admin@app.com'];

    private const PERMISSION_ACTION_ORDER = ['create', 'read', 'update', 'delete'];

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));

        $users = User::query()
            ->select(['id', 'first_name', 'last_name', 'email', 'avatar'])
            ->whereNotIn('email', self::PROTECTED_USER_EMAILS)
            ->whereDoesntHave('roles', function ($query) {
                $query->whereIn('name', self::PROTECTED_ROLE_NAMES);
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($searchQuery) use ($search) {
                    $searchQuery->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest('id')
            ->paginate(2)
            ->withQueryString();

        return view('Dashboard.users.index', compact('users', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorizePermission(config('permissions.users.create'));

        return view('Dashboard.users.create', $this->permissionFormData());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Container $container)
    {
        $this->authorizePermission(config('permissions.users.create'));

        $validated = $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed|min:6',
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::exists('permissions', 'name')],
        ]);

        $avatarPath = null;
        if ($request->hasFile('avatar')) {
            $avatarPath = $container->make(AvatarImageProcessor::class)->storeUploadedAvatar($request->file('avatar'));
        }

        $user = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'avatar' => $avatarPath,
            'password' => $validated['password'],
        ]);

        $permissions = $this->permissionNamesFromRequest($request);
        $this->removeBlacklistedRoles($user);
        $user->syncPermissions($permissions);
        $user->flushCache();

        session()->flash('success', __('site.added_successfully'));

        return redirect()->route('dashboard.users.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $this->authorizePermission(config('permissions.users.update'));

        abort_if($this->isProtectedUser($user), 403);

        $user->loadMissing('roles', 'permissions');

        return view('Dashboard.users.edit', array_merge(
            ['user' => $user],
            $this->permissionFormData(
                $user->allPermissions()->pluck('name')->unique()->sort()->values()->all()
            )
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user, Container $container)
    {
        $this->authorizePermission(config('permissions.users.update'));

        abort_if($this->isProtectedUser($user), 403);

        $validated = $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'password' => 'nullable|confirmed|min:6',
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::exists('permissions', 'name')],
        ]);

        $data = [
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
        ];

        if (! empty($validated['password'])) {
            $data['password'] = $validated['password'];
        }

        if ($request->hasFile('avatar')) {
            $processor = $container->make(AvatarImageProcessor::class);
            $processor->deletePublicAvatar($user->avatar);
            $data['avatar'] = $processor->storeUploadedAvatar($request->file('avatar'));
        }

        $user->update($data);
        $permissions = $this->permissionNamesFromRequest($request);
        $this->removeBlacklistedRoles($user);
        $user->syncPermissions($permissions);
        $user->flushCache();

        session()->flash('success', __('site.updated_successfully'));

        return redirect()->route('dashboard.users.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user, Container $container)
    {
        $this->authorizePermission(config('permissions.users.delete'));

        abort_if($user->is(auth()->user()), 403);

        abort_if($this->isProtectedUser($user), 403);

        $container->make(AvatarImageProcessor::class)->deletePublicAvatar($user->avatar);

        $user->delete();

        session()->flash('success', __('site.deleted_successfully'));

        return redirect()->route('dashboard.users.index');
    }

    private function authorizePermission(string $permission): void
    {
        abort_unless(auth()->user()?->hasPermission($permission), 403);
    }

    private function removeBlacklistedRoles(User $user): void
    {
        foreach (self::MANAGED_ROLE_BLACKLIST as $roleName) {
            if ($user->hasRole($roleName)) {
                $user->removeRole($roleName);
            }
        }
    }

    /**
     * Read permissions[] from the request and keep only names that exist in DB (avoids trusting $validated-only keys).
     */
    private function permissionNamesFromRequest(Request $request): array
    {
        $names = $request->input('permissions', []);
        if (! is_array($names)) {
            return [];
        }

        $names = array_values(array_unique(array_filter($names, fn ($n) => is_string($n) && $n !== '')));

        return Permission::query()
            ->whereIn('name', $names)
            ->pluck('name')
            ->all();
    }

    private function isProtectedUser(User $user): bool
    {
        if (in_array($user->email, self::PROTECTED_USER_EMAILS, true)) {
            return true;
        }

        foreach (self::PROTECTED_ROLE_NAMES as $roleName) {
            if ($user->hasRole($roleName)) {
                return true;
            }
        }

        return false;
    }

    private function permissionFormData(array $selectedPermissions = []): array
    {
        $permissionMatrix = $this->permissionMatrix();
        $permissionGroups = array_keys($permissionMatrix);
        $activePermissionGroup = collect($permissionGroups)->first(function (string $group) use ($permissionMatrix, $selectedPermissions) {
            foreach ($permissionMatrix[$group] as $permission) {
                if (in_array($permission['name'], $selectedPermissions, true)) {
                    return true;
                }
            }

            return false;
        }) ?? ($permissionGroups[0] ?? null);

        return compact('permissionMatrix', 'selectedPermissions', 'activePermissionGroup');
    }

    private function permissionMatrix(): array
    {
        $actionOrder = array_flip(self::PERMISSION_ACTION_ORDER);

        return Permission::query()
            ->orderBy('name')
            ->get(['name'])
            ->groupBy(fn (Permission $permission) => Str::before($permission->name, '_'))
            ->map(function ($permissions) use ($actionOrder) {
                return $permissions
                    ->sortBy(fn (Permission $permission) => $actionOrder[Str::after($permission->name, '_')] ?? PHP_INT_MAX)
                    ->map(fn (Permission $permission) => [
                        'name' => $permission->name,
                        'action' => Str::after($permission->name, '_'),
                    ])
                    ->values()
                    ->all();
            })
            ->toArray();
    }
}
