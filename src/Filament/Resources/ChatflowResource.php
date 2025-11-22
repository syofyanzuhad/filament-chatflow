<?php

namespace Syofyanzuhad\FilamentChatflow\Filament\Resources;

use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Syofyanzuhad\FilamentChatflow\Filament\Resources\ChatflowResource\Pages;
use Syofyanzuhad\FilamentChatflow\Models\Chatflow;

class ChatflowResource extends Resource
{
    protected static ?string $model = Chatflow::class;

    protected static \BackedEnum | string | null $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static string | \UnitEnum | null $navigationGroup = 'Chatflow';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('description')
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->inline(false),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Forms\Components\Section::make('Welcome Message')
                    ->schema([
                        Forms\Components\KeyValue::make('welcome_message')
                            ->label('Multi-language Welcome Messages')
                            ->keyLabel('Language Code')
                            ->valueLabel('Welcome Message')
                            ->default([
                                'en' => 'Hello! How can I help you today?',
                                'id' => 'Halo! Ada yang bisa saya bantu?',
                            ])
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),

                Forms\Components\Section::make('Widget Settings')
                    ->schema([
                        Forms\Components\Select::make('position')
                            ->options([
                                'bottom-right' => 'Bottom Right',
                                'bottom-left' => 'Bottom Left',
                                'top-right' => 'Top Right',
                                'top-left' => 'Top Left',
                            ])
                            ->default('bottom-right')
                            ->required(),

                        Forms\Components\ColorPicker::make('settings.theme_color')
                            ->label('Theme Color')
                            ->default('#3b82f6'),

                        Forms\Components\Toggle::make('settings.sound_enabled')
                            ->label('Enable Sound Notifications')
                            ->default(true)
                            ->inline(false),

                        Forms\Components\Toggle::make('settings.show_badge')
                            ->label('Show Notification Badge')
                            ->default(true)
                            ->inline(false),

                        Forms\Components\Toggle::make('settings.auto_open')
                            ->label('Auto Open on Page Load')
                            ->default(false)
                            ->inline(false),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Forms\Components\Section::make('Email Settings')
                    ->schema([
                        Forms\Components\Toggle::make('settings.email_enabled')
                            ->label('Send Email Transcript')
                            ->default(true)
                            ->inline(false)
                            ->reactive(),

                        Forms\Components\TagsInput::make('settings.email_recipients')
                            ->label('Additional Email Recipients')
                            ->placeholder('Add email addresses')
                            ->visible(fn (Forms\Get $get) => $get('settings.email_enabled'))
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('description')
                    ->limit(50)
                    ->searchable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('conversations_count')
                    ->counts('conversations')
                    ->label('Total Conversations')
                    ->sortable(),

                Tables\Columns\TextColumn::make('completion_rate')
                    ->label('Completion Rate')
                    ->formatStateUsing(fn ($record) => $record->completion_rate . '%')
                    ->sortable()
                    ->color(fn ($record) => $record->completion_rate >= 70 ? 'success' : ($record->completion_rate >= 50 ? 'warning' : 'danger')),

                Tables\Columns\TextColumn::make('position')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status')
                    ->placeholder('All')
                    ->trueLabel('Active Only')
                    ->falseLabel('Inactive Only'),

                Tables\Filters\SelectFilter::make('position')
                    ->options([
                        'bottom-right' => 'Bottom Right',
                        'bottom-left' => 'Bottom Left',
                        'top-right' => 'Top Right',
                        'top-left' => 'Top Left',
                    ]),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\Action::make('duplicate')
                        ->icon('heroicon-o-document-duplicate')
                        ->requiresConfirmation()
                        ->action(function (Chatflow $record) {
                            $builder = app(\Syofyanzuhad\FilamentChatflow\Services\ChatflowBuilder::class);
                            $builder->duplicateFlow($record, $record->name . ' (Copy)');
                        }),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('activate')
                        ->icon('heroicon-o-check-circle')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->update(['is_active' => true])),
                    Tables\Actions\BulkAction::make('deactivate')
                        ->icon('heroicon-o-x-circle')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->update(['is_active' => false])),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListChatflows::route('/'),
            'create' => Pages\CreateChatflow::route('/create'),
            'edit' => Pages\EditChatflow::route('/{record}/edit'),
            'builder' => Pages\BuilderChatflow::route('/{record}/builder'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('is_active', true)->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
}
