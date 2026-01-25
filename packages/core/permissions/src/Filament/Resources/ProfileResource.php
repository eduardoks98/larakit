<?php

namespace Eduardoks98\Permissions\Filament\Resources;

use Eduardoks98\Permissions\Filament\Resources\ProfileResource\Pages;
use Eduardoks98\Permissions\Models\Permission;
use Eduardoks98\Permissions\Models\Profile;
use Eduardoks98\Permissions\Services\PermissionService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProfileResource extends Resource
{
    protected static ?string $model = Profile::class;

    public static function getNavigationIcon(): ?string
    {
        return config('permissions.filament.navigation_icon', 'heroicon-o-shield-check');
    }

    public static function getNavigationGroup(): ?string
    {
        return config('permissions.filament.navigation_group', 'Administração');
    }

    public static function getNavigationSort(): ?int
    {
        return config('permissions.filament.navigation_sort', 100);
    }

    public static function getNavigationLabel(): string
    {
        return config('permissions.filament.navigation_label', 'Perfis');
    }

    public static function getModelLabel(): string
    {
        return config('permissions.filament.model_label', 'Perfil');
    }

    public static function getPluralModelLabel(): string
    {
        return config('permissions.filament.plural_model_label', 'Perfis');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return config('permissions.filament.enabled', true);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informações do Perfil')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nome')
                            ->required()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true),

                        Forms\Components\TextInput::make('description')
                            ->label('Descrição')
                            ->maxLength(255),

                        Forms\Components\Toggle::make('is_admin')
                            ->label('Administrador')
                            ->helperText('Administradores têm acesso total ao sistema, ignorando todas as permissões.')
                            ->reactive()
                            ->default(false),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Permissões')
                    ->schema(function () {
                        $service = app(PermissionService::class);
                        $grouped = $service->getPermissionOptionsGroupedForFilament();

                        if (empty($grouped)) {
                            return [
                                Forms\Components\Placeholder::make('no_permissions')
                                    ->label('')
                                    ->content('Nenhuma permissão cadastrada. Execute: php artisan permissions:sync'),
                            ];
                        }

                        $sections = [];

                        foreach ($grouped as $module => $permissions) {
                            $sections[] = Forms\Components\Fieldset::make($module)
                                ->schema([
                                    Forms\Components\CheckboxList::make('permissions')
                                        ->relationship('permissions', 'description')
                                        ->options($permissions)
                                        ->columns(2)
                                        ->bulkToggleable()
                                        ->gridDirection('row'),
                                ])
                                ->columns(1);
                        }

                        return $sections;
                    })
                    ->hidden(fn (Forms\Get $get) => $get('is_admin'))
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('description')
                    ->label('Descrição')
                    ->searchable()
                    ->limit(50),

                Tables\Columns\IconColumn::make('is_admin')
                    ->label('Admin')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),

                Tables\Columns\TextColumn::make('permissions_count')
                    ->label('Permissões')
                    ->counts('permissions')
                    ->sortable(),

                Tables\Columns\TextColumn::make('users_count')
                    ->label('Usuários')
                    ->counts('users')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Atualizado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_admin')
                    ->label('Administrador')
                    ->placeholder('Todos')
                    ->trueLabel('Sim')
                    ->falseLabel('Não'),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
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
            'index' => Pages\ListProfiles::route('/'),
            'create' => Pages\CreateProfile::route('/create'),
            'edit' => Pages\EditProfile::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
