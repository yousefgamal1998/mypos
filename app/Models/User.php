<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property-read string $avatar_url Display URL (uploaded file or default avatar asset).
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'avatar',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn () => trim("{$this->first_name} {$this->last_name}")
        );
    }

    /**
     * Public URL for the avatar (storage path) or the configured default image when none is stored.
     */
    protected function avatarUrl(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                if ($this->avatar === null || $this->avatar === '') {
                    return asset(config('image.default_avatar'));
                }

                return asset('storage/'.$this->avatar);
            }
        );
    }

    public function hasCustomAvatar(): bool
    {
        return filled($this->avatar);
    }

    /**
     * Backward-compatible alias for the previous permission API used across the app.
     */
    public function hasPermission(string $permission): bool
    {
        return $this->hasPermissionTo($permission);
    }

    /**
     * Backward-compatible alias returning direct + role permissions as a collection.
     */
    public function allPermissions()
    {
        return $this->getAllPermissions();
    }

    /**
     * Keep older seeders and controllers working while using Spatie under the hood.
     *
     * @param  array<int, string>|string  $roles
     */
    public function syncRolesWithoutDetaching(array|string $roles): static
    {
        $requestedRoles = collect(is_array($roles) ? $roles : [$roles])
            ->filter(fn ($role) => $role !== null && $role !== '')
            ->values();

        if ($requestedRoles->isEmpty()) {
            return $this;
        }

        $existingRoleNames = $this->roles()->pluck('name')->all();
        $missingRoles = $requestedRoles
            ->reject(function ($role) use ($existingRoleNames) {
                $roleName = is_object($role) ? ($role->name ?? null) : $role;

                return is_string($roleName) && in_array($roleName, $existingRoleNames, true);
            })
            ->all();

        if ($missingRoles !== []) {
            $this->assignRole($missingRoles);
        }

        return $this;
    }

    /**
     * Backward-compatible alias used by older seeders/tests.
     */
    public function addRole($role): static
    {
        $this->assignRole($role);

        return $this;
    }

    /**
     * Backward-compatible cache flush alias.
     */
    public function flushCache(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
