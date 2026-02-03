<div class="form-section">
    <h2 class="section-title">Informacoes do Ad Unit</h2>

    <div class="form-row">
        <div class="form-group">
            <label for="name" class="form-label">Nome <span class="required">*</span></label>
            <input type="text"
                   id="name"
                   name="name"
                   class="form-control @error('name') is-invalid @enderror"
                   value="{{ old('name', $adUnit->name ?? '') }}"
                   maxlength="100"
                   placeholder="ex: Homepage Banner"
                   required>
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="slot_id" class="form-label">Slot ID <span class="required">*</span></label>
            <input type="text"
                   id="slot_id"
                   name="slot_id"
                   class="form-control @error('slot_id') is-invalid @enderror"
                   value="{{ old('slot_id', $adUnit->slot_id ?? '') }}"
                   maxlength="50"
                   placeholder="ex: 1234567890"
                   required>
            <p class="form-hint">O ID do slot do AdSense (apenas os numeros)</p>
            @error('slot_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="format" class="form-label">Formato <span class="required">*</span></label>
            <select id="format"
                    name="format"
                    class="form-control @error('format') is-invalid @enderror"
                    required>
                @foreach($formats as $value => $label)
                    <option value="{{ $value }}" {{ old('format', $adUnit->format->value ?? 'responsive') === $value ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
            @error('format')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="position" class="form-label">Posicao</label>
            <input type="text"
                   id="position"
                   name="position"
                   class="form-control @error('position') is-invalid @enderror"
                   value="{{ old('position', $adUnit->position ?? '') }}"
                   maxlength="50"
                   placeholder="ex: header, sidebar, between_matches">
            <p class="form-hint">Identificador da posicao para uso no frontend</p>
            @error('position')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    @if($hasGames)
        <div class="form-group">
            <label for="game_id" class="form-label">Jogo</label>
            <select id="game_id"
                    name="game_id"
                    class="form-control @error('game_id') is-invalid @enderror">
                <option value="">Global (todos os jogos)</option>
                @foreach($games as $game)
                    <option value="{{ $game->id }}" {{ old('game_id', $adUnit->game_id ?? '') == $game->id ? 'selected' : '' }}>
                        {{ $game->name }}
                    </option>
                @endforeach
            </select>
            <p class="form-hint">Deixe vazio para ad unit global</p>
            @error('game_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    @endif

    <div class="form-group">
        <label class="checkbox-label">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox"
                   id="is_active"
                   name="is_active"
                   value="1"
                   {{ old('is_active', $adUnit->is_active ?? true) ? 'checked' : '' }}>
            <span class="checkbox-text">Ativo</span>
        </label>
        <p class="form-hint">Ad Units inativos nao serao exibidos no site.</p>
    </div>
</div>

<div class="form-section">
    <h2 class="section-title">Preview</h2>
    <div class="preview-container" id="adPreview">
        <div class="preview-placeholder">
            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
            </svg>
            <p>O preview do anuncio aparecera aqui baseado no formato selecionado</p>
        </div>
    </div>
</div>
