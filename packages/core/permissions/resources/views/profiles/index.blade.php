@extends(config('permissions.admin_layout', 'layouts.admin'))

@section('title', 'Perfis')

@push('styles')
<link rel="stylesheet" href="{{ asset('vendor/permissions/css/profiles.css') }}">
@endpush

@section('content')
<div class="admin-container">
    <div class="admin-header">
        <h1 class="admin-title">Perfis</h1>
        <a href="{{ route('admin.profiles.create') }}" class="btn btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Novo Perfil
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="admin-stats">
        <div class="stat-card">
            <div class="stat-icon stat-icon--primary">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-value">{{ $stats['total'] }}</span>
                <span class="stat-label">Total de Perfis</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon stat-icon--secondary">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-value">{{ $stats['admins'] }}</span>
                <span class="stat-label">Administradores</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon stat-icon--success">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-value">{{ $stats['with_permissions'] }}</span>
                <span class="stat-label">Com Permissões</span>
            </div>
        </div>
    </div>

    <div class="profiles-grid">
        @forelse($profiles as $profile)
            <div class="profile-card {{ $profile->is_admin ? 'profile-card--admin' : '' }}">
                <div class="profile-header">
                    <h3 class="profile-name">{{ $profile->name }}</h3>
                    @if($profile->is_admin)
                        <span class="badge badge-admin">Admin</span>
                    @endif
                </div>

                @if($profile->description)
                    <p class="profile-description">{{ $profile->description }}</p>
                @endif

                <div class="profile-stats">
                    <div class="profile-stat">
                        <span class="profile-stat-value">{{ $profile->users_count }}</span>
                        <span class="profile-stat-label">Usuários</span>
                    </div>
                    <div class="profile-stat">
                        <span class="profile-stat-value">{{ $profile->is_admin ? 'Todas' : $profile->permissions_count }}</span>
                        <span class="profile-stat-label">Permissões</span>
                    </div>
                </div>

                <div class="profile-actions">
                    <a href="{{ route('admin.profiles.edit', $profile) }}" class="btn btn-sm btn-secondary">
                        Editar
                    </a>
                    <form action="{{ route('admin.profiles.destroy', $profile) }}" method="POST" class="delete-form" onsubmit="return confirm('Tem certeza que deseja excluir este perfil?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger">
                            Excluir
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="empty-state">
                <p>Nenhum perfil cadastrado.</p>
                <a href="{{ route('admin.profiles.create') }}" class="btn btn-primary">Criar primeiro perfil</a>
            </div>
        @endforelse
    </div>
</div>
@endsection
