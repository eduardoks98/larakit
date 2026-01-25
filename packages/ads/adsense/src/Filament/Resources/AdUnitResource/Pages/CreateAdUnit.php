<?php

namespace Eduardoks98\AdsAdsense\Filament\Resources\AdUnitResource\Pages;

use Eduardoks98\AdsAdsense\Filament\Resources\AdUnitResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAdUnit extends CreateRecord
{
    protected static string $resource = AdUnitResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
