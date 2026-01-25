@extends(config('adsense.admin_layout', 'layouts.admin'))

@section('title', 'Editar Ad Unit')

@push('styles')
<link rel="stylesheet" href="{{ asset('vendor/adsense/css/ad-units.css') }}">
@endpush

@section('content')
<div class="admin-container">
    <div class="admin-breadcrumb">
        <a href="{{ route('admin.ad-units.index') }}" class="breadcrumb-link">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Voltar para Ad Units
        </a>
    </div>

    <div class="admin-header">
        <h1 class="admin-title">Editar Ad Unit: {{ $adUnit->name }}</h1>
    </div>

    <form action="{{ route('admin.ad-units.update', $adUnit) }}" method="POST" class="ad-unit-form">
        @csrf
        @method('PUT')

        @include('adsense::ad-units._form', ['adUnit' => $adUnit])

        <div class="form-actions">
            <a href="{{ route('admin.ad-units.index') }}" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Salvar Alteracoes</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="{{ asset('vendor/adsense/js/ad-units.js') }}"></script>
@endpush
