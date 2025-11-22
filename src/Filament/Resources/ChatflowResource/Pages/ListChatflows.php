<?php

namespace Syofyanzuhad\FilamentChatflow\Filament\Resources\ChatflowResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Syofyanzuhad\FilamentChatflow\Filament\Resources\ChatflowResource;

class ListChatflows extends ListRecords
{
    protected static string $resource = ChatflowResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
