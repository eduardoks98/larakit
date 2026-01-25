<?php

namespace Eduardoks98\Permissions\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Eduardoks98\Permissions\Models\Profile;
use Eduardoks98\Permissions\Models\Permission;
use Eduardoks98\Permissions\Services\PermissionService;

class ProfileController extends Controller
{
    public function __construct(
        protected PermissionService $permissionService
    ) {}

    /**
     * Display a listing of profiles.
     */
    public function index()
    {
        $profiles = Profile::withCount(['users', 'permissions'])->get();

        $stats = [
            'total' => $profiles->count(),
            'admins' => $profiles->where('is_admin', true)->count(),
            'with_permissions' => $profiles->where('permissions_count', '>', 0)->count(),
        ];

        return view('permissions::profiles.index', compact('profiles', 'stats'));
    }

    /**
     * Show the form for creating a new profile.
     */
    public function create()
    {
        $permissionsGrouped = Permission::getAllGroupedByModule();

        return view('permissions::profiles.create', compact('permissionsGrouped'));
    }

    /**
     * Store a newly created profile.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:profiles,name',
            'description' => 'nullable|string|max:255',
            'is_admin' => 'boolean',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $profile = Profile::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_admin' => $validated['is_admin'] ?? false,
        ]);

        // Sync permissions if not admin
        if (!$profile->is_admin && !empty($validated['permissions'])) {
            $profile->permissions()->sync($validated['permissions']);
        }

        return redirect()
            ->route('admin.profiles.index')
            ->with('success', 'Perfil criado com sucesso.');
    }

    /**
     * Show the form for editing the specified profile.
     */
    public function edit(Profile $profile)
    {
        $profile->load('permissions');
        $permissionsGrouped = Permission::getAllGroupedByModule();
        $profilePermissionIds = $profile->permissions->pluck('id')->toArray();

        return view('permissions::profiles.edit', compact('profile', 'permissionsGrouped', 'profilePermissionIds'));
    }

    /**
     * Update the specified profile.
     */
    public function update(Request $request, Profile $profile)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:profiles,name,' . $profile->id,
            'description' => 'nullable|string|max:255',
            'is_admin' => 'boolean',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $profile->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_admin' => $validated['is_admin'] ?? false,
        ]);

        // Sync permissions: if admin, detach all; otherwise sync selected
        if ($profile->is_admin) {
            $profile->permissions()->detach();
        } else {
            $profile->permissions()->sync($validated['permissions'] ?? []);
        }

        return redirect()
            ->route('admin.profiles.index')
            ->with('success', 'Perfil atualizado com sucesso.');
    }

    /**
     * Remove the specified profile.
     */
    public function destroy(Profile $profile)
    {
        // Check if profile has users
        if ($profile->users()->count() > 0) {
            return redirect()
                ->route('admin.profiles.index')
                ->with('error', 'Não é possível excluir um perfil que possui usuários vinculados.');
        }

        $profile->delete();

        return redirect()
            ->route('admin.profiles.index')
            ->with('success', 'Perfil excluído com sucesso.');
    }
}
