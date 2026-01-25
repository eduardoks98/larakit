<?php

namespace Eduardoks98\Permissions\Filament\Resources\ProfileResource\Pages;

use Eduardoks98\Permissions\Filament\Resources\ProfileResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProfile extends CreateRecord
{
    protected static string $resource = ProfileResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
