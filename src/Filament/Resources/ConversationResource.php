<?php

namespace Syofyanzuhad\FilamentChatflow\Filament\Resources;

use Filament\Actions;
use Filament\Forms;
use Filament\Infolists;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Syofyanzuhad\FilamentChatflow\Filament\Resources\ConversationResource\Pages;
use Syofyanzuhad\FilamentChatflow\Models\ChatflowConversation;

class ConversationResource extends Resource
{
    protected static ?string $model = ChatflowConversation::class;

    protected static \BackedEnum | string | null $navigationIcon = 'heroicon-o-chat-bubble-bottom-center-text';

    protected static string | \UnitEnum | null $navigationGroup = 'Chatflow';

    protected static ?int $navigationSort = 2;

    protected static ?string $label = 'Conversation';

    protected static ?string $pluralLabel = 'Conversations';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Select::make('chatflow_id')
                    ->relationship('chatflow', 'name')
                    ->required()
                    ->disabled(),

                Forms\Components\TextInput::make('session_id')
                    ->disabled(),

                Forms\Components\Select::make('status')
                    ->options([
                        'active' => 'Active',
                        'completed' => 'Completed',
                        'abandoned' => 'Abandoned',
                    ])
                    ->disabled(),

                Forms\Components\TextInput::make('user_email')
                    ->email()
                    ->disabled(),

                Forms\Components\TextInput::make('user_name')
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('chatflow.name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('session_id')
                    ->searchable()
                    ->limit(15)
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('user_name')
                    ->searchable()
                    ->default('Anonymous'),

                Tables\Columns\TextColumn::make('user_email')
                    ->searchable()
                    ->default('-'),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'active',
                        'success' => 'completed',
                        'danger' => 'abandoned',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('locale')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('messages_count')
                    ->counts('messages')
                    ->label('Messages')
                    ->sortable(),

                Tables\Columns\TextColumn::make('started_at')
                    ->dateTime()
                    ->sortable()
                    ->since(),

                Tables\Columns\TextColumn::make('duration_in_minutes')
                    ->label('Duration')
                    ->formatStateUsing(fn ($state) => $state ? round($state, 1) . ' min' : '-')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('chatflow')
                    ->relationship('chatflow', 'name'),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'completed' => 'Completed',
                        'abandoned' => 'Abandoned',
                    ]),

                Tables\Filters\SelectFilter::make('locale')
                    ->options([
                        'en' => 'English',
                        'id' => 'Indonesian',
                    ]),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('From'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['created_from'], fn ($q, $date) => $q->whereDate('started_at', '>=', $date))
                            ->when($data['created_until'], fn ($q, $date) => $q->whereDate('started_at', '<=', $date));
                    }),
            ])
            ->recordActions([
                Actions\ViewAction::make(),
            ])
            ->groupedBulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('started_at', 'desc');
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Infolists\Components\Section::make('Conversation Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('chatflow.name')
                            ->label('Chatflow'),

                        Infolists\Components\TextEntry::make('session_id')
                            ->copyable(),

                        Infolists\Components\TextEntry::make('status')
                            ->badge()
                            ->color(fn ($state) => match ($state) {
                                'active' => 'warning',
                                'completed' => 'success',
                                'abandoned' => 'danger',
                            }),

                        Infolists\Components\TextEntry::make('locale')
                            ->badge(),

                        Infolists\Components\TextEntry::make('user_name')
                            ->default('Anonymous'),

                        Infolists\Components\TextEntry::make('user_email')
                            ->default('-'),

                        Infolists\Components\TextEntry::make('started_at')
                            ->dateTime(),

                        Infolists\Components\TextEntry::make('ended_at')
                            ->dateTime()
                            ->default('-'),

                        Infolists\Components\TextEntry::make('duration_in_minutes')
                            ->label('Duration')
                            ->formatStateUsing(fn ($state) => $state ? round($state, 1) . ' minutes' : '-'),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Metadata')
                    ->schema([
                        Infolists\Components\KeyValueEntry::make('metadata')
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),

                Infolists\Components\Section::make('Messages Timeline')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('messages')
                            ->schema([
                                Infolists\Components\TextEntry::make('type')
                                    ->badge()
                                    ->color(fn ($state) => $state === 'bot' ? 'info' : 'success'),

                                Infolists\Components\TextEntry::make('content')
                                    ->columnSpanFull(),

                                Infolists\Components\TextEntry::make('created_at')
                                    ->dateTime()
                                    ->since(),
                            ])
                            ->columns(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListConversations::route('/'),
            'view' => Pages\ViewConversation::route('/{record}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'active')->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}
