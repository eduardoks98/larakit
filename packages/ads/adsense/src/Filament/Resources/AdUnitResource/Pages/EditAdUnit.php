<?php

namespace Eduardoks98\AdsAdsense\Filament\Resources\AdUnitResource\Pages;

use Eduardoks98\AdsAdsense\Filament\Resources\AdUnitResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAdUnit extends EditRecord
{
    protected static string $resource = AdUnitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
