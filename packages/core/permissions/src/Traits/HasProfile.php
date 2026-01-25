<?php

namespace Eduardoks98\Permissions\Traits;

use Eduardoks98\Permissions\Models\Profile;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Trait to add profile-based permissions to a User model.
 *
 * Usage:
 * class User extends Authenticatable
 * {
 *     use HasProfile;
 * }
 */
trait HasProfile
{
    /**
     * Get the profile that the user belongs to.
     */
    public function profile(): BelongsTo
    {
        $foreignKey = config('permissions.profile_foreign_key', 'profile_id');

        return $this->belongsTo(Profile::class, $foreignKey);
    }

    /**
     * Check if the user is an administrator.
     */
    public function isAdmin(): bool
    {
        return $this->profile?->is_admin ?? false;
    }

    /**
     * Check if the user has a specific permission.
     */
    public function hasPermission(string $permission): bool
    {
        if (!$this->profile) {
            return false;
        }

        return $this->profile->hasPermission($permission);
    }

    /**
     * Check if the user has all specified permissions.
     */
    public function hasPermissions(array $permissions): bool
    {
        if (!$this->profile) {
            return false;
        }

        return $this->profile->hasPermissions($permissions);
    }

    /**
     * Check if the user has any of the specified permissions.
     */
    public function hasAnyPermission(array $permissions): bool
    {
        if (!$this->profile) {
            return false;
        }

        return $this->profile->hasAnyPermission($permissions);
    }

    /**
     * Alias for hasPermissions - check if user has all abilities.
     */
    public function hasAbilities(array $abilities, bool $throwException = false): bool
    {
        $result = $this->hasPermissions($abilities);

        if (!$result && $throwException) {
            throw new \Illuminate\Auth\Access\AuthorizationException('This action is unauthorized.');
        }

        return $result;
    }

    /**
     * Alias for hasAnyPermission - check if user has any of the abilities.
     */
    public function hasAnyAbility(array $abilities): bool
    {
        return $this->hasAnyPermission($abilities);
    }

    /**
     * Get all permission names for the user.
     */
    public function getPermissions(): array
    {
        if (!$this->profile) {
            return [];
        }

        return $this->profile->getPermissions();
    }

    /**
     * Get the user's profile name.
     */
    public function getProfileName(): ?string
    {
        return $this->profile?->name;
    }

    /**
     * Assign a profile to the user by name.
     */
    public function assignProfile(string $profileName): bool
    {
        $profile = Profile::where('name', $profileName)->first();

        if (!$profile) {
            return false;
        }

        $foreignKey = config('permissions.profile_foreign_key', 'profile_id');
        $this->{$foreignKey} = $profile->id;

        return $this->save();
    }

    /**
     * Assign a profile to the user by ID.
     */
    public function assignProfileById(int $profileId): bool
    {
        $profile = Profile::find($profileId);

        if (!$profile) {
            return false;
        }

        $foreignKey = config('permissions.profile_foreign_key', 'profile_id');
        $this->{$foreignKey} = $profile->id;

        return $this->save();
    }

    /**
     * Assign the default profile to the user.
     */
    public function assignDefaultProfile(): bool
    {
        $profile = Profile::getDefaultProfile();

        if (!$profile) {
            return false;
        }

        $foreignKey = config('permissions.profile_foreign_key', 'profile_id');
        $this->{$foreignKey} = $profile->id;

        return $this->save();
    }

    /**
     * Check if the user has the default profile.
     */
    public function hasDefaultProfile(): bool
    {
        $defaultProfile = Profile::getDefaultProfile();

        if (!$defaultProfile) {
            return false;
        }

        $foreignKey = config('permissions.profile_foreign_key', 'profile_id');

        return $this->{$foreignKey} === $defaultProfile->id;
    }
}
