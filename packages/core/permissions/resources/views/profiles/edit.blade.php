@extends(config('permissions.admin_layout', 'layouts.admin'))

@section('title', 'Editar Perfil')

@push('styles')
<link rel="stylesheet" href="{{ asset('vendor/permissions/css/profiles.css') }}">
@endpush

@section('content')
<div class="admin-container">
    <div class="admin-breadcrumb">
        <a href="{{ route('admin.profiles.index') }}" class="breadcrumb-link">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Voltar para Perfis
        </a>
    </div>

    <div class="admin-header">
        <h1 class="admin-title">Editar Perfil: {{ $profile->name }}</h1>
    </div>

    <form action="{{ route('admin.profiles.update', $profile) }}" method="POST" class="profile-form">
        @csrf
        @method('PUT')

        @include('permissions::profiles._form', ['profile' => $profile])

        <div class="form-actions">
            <a href="{{ route('admin.profiles.index') }}" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Salvar Alterações</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="{{ asset('vendor/permissions/js/profiles.js') }}"></script>
@endpush
