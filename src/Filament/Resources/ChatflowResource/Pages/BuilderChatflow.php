<?php

namespace Syofyanzuhad\FilamentChatflow\Filament\Resources\ChatflowResource\Pages;

use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Syofyanzuhad\FilamentChatflow\Filament\Resources\ChatflowResource;
use Syofyanzuhad\FilamentChatflow\Models\Chatflow;
use Syofyanzuhad\FilamentChatflow\Models\ChatflowStep;
use Syofyanzuhad\FilamentChatflow\Services\ChatflowBuilder;

class BuilderChatflow extends Page
{
    protected static string $resource = ChatflowResource::class;

    protected string $view = 'filament-chatflow::filament.pages.chatflow-builder';

    public ?array $data = [];

    public Chatflow $record;

    public function mount(int | string $record): void
    {
        $this->record = Chatflow::findOrFail($record);

        $this->form->fill([
            'steps' => $this->record->steps()
                ->with('children')
                ->whereNull('parent_id')
                ->orderBy('order')
                ->get()
                ->toArray(),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Repeater::make('steps')
                    ->label('Chatflow Steps')
                    ->schema([
                        Forms\Components\Select::make('type')
                            ->options([
                                ChatflowStep::TYPE_MESSAGE => 'Message',
                                ChatflowStep::TYPE_QUESTION => 'Question (with options)',
                                ChatflowStep::TYPE_END => 'End',
                            ])
                            ->required()
                            ->reactive()
                            ->columnSpanFull(),

                        Grid::make(2)
                            ->schema([
                                Forms\Components\Textarea::make('content.en')
                                    ->label('Content (English)')
                                    ->required()
                                    ->rows(3),

                                Forms\Components\Textarea::make('content.id')
                                    ->label('Content (Indonesian)')
                                    ->required()
                                    ->rows(3),
                            ])
                            ->columnSpanFull(),

                        Forms\Components\Repeater::make('options')
                            ->label('Options')
                            ->schema([
                                Forms\Components\TextInput::make('value')
                                    ->required()
                                    ->maxLength(255),

                                Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('label.en')
                                            ->label('Label (English)')
                                            ->required(),

                                        Forms\Components\TextInput::make('label.id')
                                            ->label('Label (Indonesian)')
                                            ->required(),
                                    ]),
                            ])
                            ->visible(fn (Get $get) => $get('type') === ChatflowStep::TYPE_QUESTION)
                            ->defaultItems(0)
                            ->columnSpanFull()
                            ->collapsible(),

                        Forms\Components\Repeater::make('children')
                            ->label('Child Steps (nested)')
                            ->schema([
                                Forms\Components\Select::make('type')
                                    ->options([
                                        ChatflowStep::TYPE_MESSAGE => 'Message',
                                        ChatflowStep::TYPE_QUESTION => 'Question',
                                        ChatflowStep::TYPE_END => 'End',
                                    ])
                                    ->required()
                                    ->reactive(),

                                Grid::make(2)
                                    ->schema([
                                        Forms\Components\Textarea::make('content.en')
                                            ->label('Content (English)')
                                            ->required()
                                            ->rows(2),

                                        Forms\Components\Textarea::make('content.id')
                                            ->label('Content (Indonesian)')
                                            ->required()
                                            ->rows(2),
                                    ])
                                    ->columnSpanFull(),

                                Forms\Components\Repeater::make('options')
                                    ->label('Options')
                                    ->schema([
                                        Forms\Components\TextInput::make('value')
                                            ->required(),
                                        Forms\Components\TextInput::make('label.en')
                                            ->label('Label (EN)')
                                            ->required(),
                                        Forms\Components\TextInput::make('label.id')
                                            ->label('Label (ID)')
                                            ->required(),
                                    ])
                                    ->visible(fn (Get $get) => $get('type') === ChatflowStep::TYPE_QUESTION)
                                    ->defaultItems(0)
                                    ->columnSpanFull(),
                            ])
                            ->visible(fn (Get $get) => $get('type') !== ChatflowStep::TYPE_END)
                            ->defaultItems(0)
                            ->columnSpanFull()
                            ->collapsible()
                            ->collapsed(),
                    ])
                    ->defaultItems(1)
                    ->collapsible()
                    ->itemLabel(fn (array $state): ?string => $state['content']['en'] ?? null)
                    ->columnSpanFull()
                    ->reorderable()
                    ->orderColumn('order'),
            ])
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('validate')
                ->label('Validate Flow')
                ->icon('heroicon-o-check-circle')
                ->color('info')
                ->action(function () {
                    $builder = app(ChatflowBuilder::class);
                    $errors = $builder->validateFlow($this->record);

                    if (empty($errors)) {
                        Notification::make()
                            ->success()
                            ->title('Validation Passed')
                            ->body('Your chatflow has no validation errors!')
                            ->send();
                    } else {
                        Notification::make()
                            ->danger()
                            ->title('Validation Failed')
                            ->body('Found ' . count($errors) . ' error(s): ' . implode(', ', $errors))
                            ->send();
                    }
                }),

            Actions\Action::make('save')
                ->label('Save Flow')
                ->icon('heroicon-o-arrow-down-tray')
                ->action('save'),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        // Delete existing steps
        $this->record->steps()->delete();

        // Create new steps recursively
        $this->createSteps($data['steps'], null, 0);

        Notification::make()
            ->success()
            ->title('Flow Saved')
            ->body('Your chatflow has been saved successfully!')
            ->send();
    }

    protected function createSteps(array $steps, ?int $parentId, int $order): void
    {
        foreach ($steps as $index => $stepData) {
            $step = ChatflowStep::create([
                'chatflow_id' => $this->record->id,
                'parent_id' => $parentId,
                'type' => $stepData['type'],
                'content' => $stepData['content'] ?? [],
                'options' => $stepData['options'] ?? null,
                'order' => $order + $index,
                'position_x' => ($order + $index) * 200,
                'position_y' => $parentId ? 200 : 100,
            ]);

            // Create child steps recursively
            if (isset($stepData['children']) && ! empty($stepData['children'])) {
                $this->createSteps($stepData['children'], $step->id, 0);
            }
        }
    }

    public function getTitle(): string
    {
        return 'Flow Builder: ' . $this->record->name;
    }
}
