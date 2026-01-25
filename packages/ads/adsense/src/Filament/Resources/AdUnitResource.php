<?php

namespace Eduardoks98\AdsAdsense\Filament\Resources;

use Eduardoks98\AdsAdsense\Filament\Resources\AdUnitResource\Pages;
use Eduardoks98\AdsAdsense\Models\AdUnit;
use Eduardoks98\AdsAdsense\Enums\AdFormat;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AdUnitResource extends Resource
{
    protected static ?string $model = AdUnit::class;

    public static function getNavigationIcon(): ?string
    {
        return config('adsense.filament.navigation_icon', 'heroicon-o-rectangle-stack');
    }

    public static function getNavigationGroup(): ?string
    {
        return config('adsense.filament.navigation_group', 'Monetização');
    }

    public static function getNavigationSort(): ?int
    {
        return config('adsense.filament.navigation_sort', 50);
    }

    public static function getNavigationLabel(): string
    {
        return 'Ad Units';
    }

    public static function getModelLabel(): string
    {
        return 'Ad Unit';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Ad Units';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return config('adsense.filament.enabled', true);
    }

    public static function form(Form $form): Form
    {
        $gameModel = config('adsense.game_model');
        $hasGames = $gameModel && class_exists($gameModel);

        return $form
            ->schema([
                Forms\Components\Section::make('Informações do Ad Unit')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nome')
                            ->required()
                            ->maxLength(100)
                            ->placeholder('ex: Homepage Banner'),

                        Forms\Components\TextInput::make('slot_id')
                            ->label('Slot ID')
                            ->required()
                            ->maxLength(50)
                            ->placeholder('ex: 1234567890')
                            ->helperText('O ID do slot do AdSense (apenas os números)'),

                        $hasGames
                            ? Forms\Components\Select::make('game_id')
                                ->label('Jogo')
                                ->relationship('game', 'name')
                                ->nullable()
                                ->searchable()
                                ->preload()
                                ->helperText('Deixe vazio para ad unit global')
                            : Forms\Components\Hidden::make('game_id'),

                        Forms\Components\Select::make('format')
                            ->label('Formato')
                            ->options(AdFormat::options())
                            ->default('responsive')
                            ->required(),

                        Forms\Components\TextInput::make('position')
                            ->label('Posição')
                            ->maxLength(50)
                            ->placeholder('ex: header, sidebar, between_matches')
                            ->helperText('Identificador da posição para uso no frontend'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Ativo')
                            ->default(true),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Metadados')
                    ->schema([
                        Forms\Components\KeyValue::make('metadata')
                            ->label('Configurações Adicionais')
                            ->keyLabel('Chave')
                            ->valueLabel('Valor')
                            ->addActionLabel('Adicionar configuração')
                            ->reorderable(),
                    ])
                    ->collapsed()
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        $gameModel = config('adsense.game_model');
        $hasGames = $gameModel && class_exists($gameModel);

        $columns = [
            Tables\Columns\TextColumn::make('name')
                ->label('Nome')
                ->searchable()
                ->sortable(),

            Tables\Columns\TextColumn::make('slot_id')
                ->label('Slot ID')
                ->searchable()
                ->copyable()
                ->copyMessage('Slot ID copiado!'),
        ];

        if ($hasGames) {
            $columns[] = Tables\Columns\TextColumn::make('game.name')
                ->label('Jogo')
                ->placeholder('Global')
                ->sortable();
        }

        $columns = array_merge($columns, [
            Tables\Columns\TextColumn::make('format')
                ->label('Formato')
                ->badge()
                ->formatStateUsing(fn (AdFormat $state): string => $state->label()),

            Tables\Columns\TextColumn::make('position')
                ->label('Posição')
                ->searchable()
                ->placeholder('-'),

            Tables\Columns\IconColumn::make('is_active')
                ->label('Ativo')
                ->boolean()
                ->trueIcon('heroicon-o-check-circle')
                ->falseIcon('heroicon-o-x-circle')
                ->trueColor('success')
                ->falseColor('danger'),

            Tables\Columns\TextColumn::make('created_at')
                ->label('Criado em')
                ->dateTime('d/m/Y H:i')
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ]);

        $filters = [
            Tables\Filters\TernaryFilter::make('is_active')
                ->label('Ativo')
                ->placeholder('Todos')
                ->trueLabel('Ativos')
                ->falseLabel('Inativos'),

            Tables\Filters\SelectFilter::make('format')
                ->label('Formato')
                ->options(AdFormat::options()),
        ];

        if ($hasGames) {
            $filters[] = Tables\Filters\SelectFilter::make('game_id')
                ->label('Jogo')
                ->relationship('game', 'name')
                ->searchable()
                ->preload()
                ->placeholder('Todos os jogos');

            $filters[] = Tables\Filters\TernaryFilter::make('global')
                ->label('Global')
                ->placeholder('Todos')
                ->trueLabel('Apenas globais')
                ->falseLabel('Com jogo associado')
                ->queries(
                    true: fn ($query) => $query->whereNull('game_id'),
                    false: fn ($query) => $query->whereNotNull('game_id'),
                );
        }

        return $table
            ->columns($columns)
            ->filters($filters)
            ->actions([
                Tables\Actions\Action::make('preview')
                    ->label('Preview')
                    ->icon('heroicon-o-eye')
                    ->modalHeading('Preview do Ad Unit')
                    ->modalContent(fn (AdUnit $record) => view('adsense::preview', ['adUnit' => $record]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Fechar'),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('toggle')
                    ->label(fn (AdUnit $record) => $record->is_active ? 'Desativar' : 'Ativar')
                    ->icon(fn (AdUnit $record) => $record->is_active ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->color(fn (AdUnit $record) => $record->is_active ? 'danger' : 'success')
                    ->requiresConfirmation()
                    ->action(fn (AdUnit $record) => $record->update(['is_active' => !$record->is_active])),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Ativar selecionados')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn ($records) => $records->each->update(['is_active' => true]))
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Desativar selecionados')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(fn ($records) => $records->each->update(['is_active' => false]))
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('name');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAdUnits::route('/'),
            'create' => Pages\CreateAdUnit::route('/create'),
            'edit' => Pages\EditAdUnit::route('/{record}/edit'),
        ];
    }
}
