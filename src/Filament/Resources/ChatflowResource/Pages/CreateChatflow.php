<?php

namespace Syofyanzuhad\FilamentChatflow\Filament\Resources\ChatflowResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Syofyanzuhad\FilamentChatflow\Filament\Resources\ChatflowResource;

class CreateChatflow extends CreateRecord
{
    protected static string $resource = ChatflowResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('builder', ['record' => $this->getRecord()]);
    }
}
