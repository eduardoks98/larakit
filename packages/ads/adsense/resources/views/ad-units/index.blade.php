@extends(config('adsense.admin_layout', 'layouts.admin'))

@section('title', 'Ad Units')

@push('styles')
<link rel="stylesheet" href="{{ asset('vendor/adsense/css/ad-units.css') }}">
@endpush

@section('content')
<div class="admin-container">
    <div class="admin-header">
        <h1 class="admin-title">Ad Units</h1>
        <a href="{{ route('admin.ad-units.create') }}" class="btn btn-primary">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            Novo Ad Unit
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-error">
            {{ session('error') }}
        </div>
    @endif

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value">{{ $stats['total'] }}</div>
            <div class="stat-label">Total Ad Units</div>
        </div>
        <div class="stat-card">
            <div class="stat-value stat-success">{{ $stats['active'] }}</div>
            <div class="stat-label">Ativos</div>
        </div>
        <div class="stat-card">
            <div class="stat-value stat-info">{{ $stats['global'] }}</div>
            <div class="stat-label">Globais</div>
        </div>
    </div>

    @if($adUnits->isEmpty())
        <div class="empty-state">
            <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
            </svg>
            <h3>Nenhum Ad Unit cadastrado</h3>
            <p>Crie seu primeiro Ad Unit para comecar a exibir anuncios.</p>
            <a href="{{ route('admin.ad-units.create') }}" class="btn btn-primary">Criar Ad Unit</a>
        </div>
    @else
        <div class="ad-units-grid">
            @foreach($adUnits as $adUnit)
                <div class="ad-unit-card {{ !$adUnit->is_active ? 'inactive' : '' }}">
                    <div class="ad-unit-header">
                        <h3 class="ad-unit-name">{{ $adUnit->name }}</h3>
                        <div class="ad-unit-status {{ $adUnit->is_active ? 'status-active' : 'status-inactive' }}">
                            {{ $adUnit->is_active ? 'Ativo' : 'Inativo' }}
                        </div>
                    </div>

                    <div class="ad-unit-info">
                        <div class="info-row">
                            <span class="info-label">Slot ID:</span>
                            <span class="info-value slot-id">{{ $adUnit->slot_id }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Formato:</span>
                            <span class="info-value format-badge">{{ $adUnit->format->label() }}</span>
                        </div>
                        @if($adUnit->position)
                            <div class="info-row">
                                <span class="info-label">Posicao:</span>
                                <span class="info-value">{{ $adUnit->position }}</span>
                            </div>
                        @endif
                        @if($hasGames)
                            <div class="info-row">
                                <span class="info-label">Jogo:</span>
                                <span class="info-value">{{ $adUnit->game?->name ?? 'Global' }}</span>
                            </div>
                        @endif
                    </div>

                    <div class="ad-unit-actions">
                        <button type="button" class="btn btn-icon btn-preview" data-ad-unit-id="{{ $adUnit->id }}" title="Preview">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>

                        <form action="{{ route('admin.ad-units.toggle', $adUnit) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="btn btn-icon {{ $adUnit->is_active ? 'btn-warning' : 'btn-success' }}" title="{{ $adUnit->is_active ? 'Desativar' : 'Ativar' }}">
                                @if($adUnit->is_active)
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                @else
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                @endif
                            </button>
                        </form>

                        <a href="{{ route('admin.ad-units.edit', $adUnit) }}" class="btn btn-icon btn-secondary" title="Editar">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </a>

                        <form action="{{ route('admin.ad-units.destroy', $adUnit) }}" method="POST" class="delete-form inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-icon btn-danger" title="Excluir">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </form>
                    </div>

                    <!-- Preview Modal Content (Hidden) -->
                    <div class="ad-unit-preview-content" id="preview-{{ $adUnit->id }}" style="display: none;">
                        <pre class="code-preview"><code>{{ $adUnit->toHtml() }}</code></pre>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<!-- Preview Modal -->
<div id="previewModal" class="modal" style="display: none;">
    <div class="modal-backdrop"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Preview do Ad Unit</h3>
            <button type="button" class="modal-close" id="closeModal">&times;</button>
        </div>
        <div class="modal-body" id="previewContent">
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('vendor/adsense/js/ad-units.js') }}"></script>
@endpush
