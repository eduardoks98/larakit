<?php

namespace Eduardoks98\AdsAdsense\Filament\Resources\AdUnitResource\Pages;

use Eduardoks98\AdsAdsense\Filament\Resources\AdUnitResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAdUnits extends ListRecords
{
    protected static string $resource = AdUnitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
