<div class="form-section">
    <h2 class="section-title">Informações do Perfil</h2>

    <div class="form-row">
        <div class="form-group">
            <label for="name" class="form-label">Nome <span class="required">*</span></label>
            <input type="text"
                   id="name"
                   name="name"
                   class="form-control @error('name') is-invalid @enderror"
                   value="{{ old('name', $profile?->name) }}"
                   maxlength="50"
                   required>
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="description" class="form-label">Descrição</label>
            <input type="text"
                   id="description"
                   name="description"
                   class="form-control @error('description') is-invalid @enderror"
                   value="{{ old('description', $profile?->description) }}"
                   maxlength="255">
            @error('description')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="form-group">
        <label class="checkbox-label">
            <input type="hidden" name="is_admin" value="0">
            <input type="checkbox"
                   id="is_admin"
                   name="is_admin"
                   value="1"
                   {{ old('is_admin', $profile?->is_admin) ? 'checked' : '' }}>
            <span class="checkbox-text">Administrador</span>
        </label>
        <p class="form-hint">Administradores têm acesso total ao sistema, ignorando todas as permissões.</p>
    </div>
</div>

<div class="form-section" id="permissionsSection">
    <h2 class="section-title">Permissões</h2>

    @if(empty($permissionsGrouped))
        <div class="empty-permissions">
            <p>Nenhuma permissão cadastrada.</p>
            <p class="text-muted">Execute: <code>php artisan permissions:sync</code></p>
        </div>
    @else
        <div class="permissions-grid">
            @foreach($permissionsGrouped as $module => $permissions)
                <div class="permission-module">
                    <div class="module-header">
                        <label class="checkbox-label module-checkbox">
                            <input type="checkbox" class="module-select-all" data-module="{{ $module }}">
                            <span class="module-name">{{ ucfirst(strtolower($module)) }}</span>
                        </label>
                    </div>
                    <div class="module-permissions">
                        @foreach($permissions as $permission)
                            <label class="checkbox-label permission-item">
                                <input type="checkbox"
                                       name="permissions[]"
                                       value="{{ $permission['id'] }}"
                                       class="permission-checkbox"
                                       data-module="{{ $module }}"
                                       {{ in_array($permission['id'], old('permissions', $profilePermissionIds ?? [])) ? 'checked' : '' }}>
                                <span class="permission-name">{{ $permission['description'] ?? $permission['name'] }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
