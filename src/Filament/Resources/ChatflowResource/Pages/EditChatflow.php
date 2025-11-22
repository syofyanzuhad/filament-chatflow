<?php

namespace Syofyanzuhad\FilamentChatflow\Filament\Resources\ChatflowResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Syofyanzuhad\FilamentChatflow\Filament\Resources\ChatflowResource;

class EditChatflow extends EditRecord
{
    protected static string $resource = ChatflowResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('builder')
                ->label('Flow Builder')
                ->icon('heroicon-o-puzzle-piece')
                ->url(fn ($record) => ChatflowResource::getUrl('builder', ['record' => $record])),
            Actions\DeleteAction::make(),
        ];
    }
}
