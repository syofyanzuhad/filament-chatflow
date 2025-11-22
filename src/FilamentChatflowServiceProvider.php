<?php

namespace Syofyanzuhad\FilamentChatflow;

use Filament\Support\Assets\AlpineComponent;
use Filament\Support\Assets\Asset;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Filesystem\Filesystem;
use Livewire\Features\SupportTesting\Testable;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Syofyanzuhad\FilamentChatflow\Commands\FilamentChatflowCommand;
use Syofyanzuhad\FilamentChatflow\Testing\TestsFilamentChatflow;

class FilamentChatflowServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-chatflow';

    public static string $viewNamespace = 'filament-chatflow';

    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package->name(static::$name)
            ->hasCommands($this->getCommands())
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->publishConfigFile()
                    ->publishMigrations()
                    ->askToRunMigrations()
                    ->askToStarRepoOnGitHub('syofyanzuhad/filament-chatflow');
            });

        $configFileName = $package->shortName();

        if (file_exists($package->basePath("/../config/{$configFileName}.php"))) {
            $package->hasConfigFile();
        }

        if (file_exists($package->basePath('/../database/migrations'))) {
            $package->hasMigrations($this->getMigrations());
        }

        if (file_exists($package->basePath('/../resources/lang'))) {
            $package->hasTranslations();
        }

        if (file_exists($package->basePath('/../resources/views'))) {
            $package->hasViews(static::$viewNamespace);
        }
    }

    public function packageRegistered(): void
    {
        // Register Livewire components
        \Livewire\Livewire::component('chatflow-widget', \Syofyanzuhad\FilamentChatflow\Livewire\ChatWidget::class);

        // Register event listeners
        $this->app['events']->listen(
            \Syofyanzuhad\FilamentChatflow\Events\ConversationStarted::class,
            \Syofyanzuhad\FilamentChatflow\Listeners\LogConversationStart::class
        );

        $this->app['events']->listen(
            \Syofyanzuhad\FilamentChatflow\Events\ConversationEnded::class,
            [\Syofyanzuhad\FilamentChatflow\Listeners\SendConversationEmail::class, \Syofyanzuhad\FilamentChatflow\Listeners\UpdateAnalytics::class]
        );

        $this->app['events']->listen(
            \Syofyanzuhad\FilamentChatflow\Events\MessageSent::class,
            \Syofyanzuhad\FilamentChatflow\Listeners\LogMessage::class
        );
    }

    public function packageBooted(): void
    {
        // Asset Registration
        FilamentAsset::register(
            $this->getAssets(),
            $this->getAssetPackageName()
        );

        FilamentAsset::registerScriptData(
            $this->getScriptData(),
            $this->getAssetPackageName()
        );

        // Icon Registration
        FilamentIcon::register($this->getIcons());

        // Handle Stubs
        if (app()->runningInConsole()) {
            foreach (app(Filesystem::class)->files(__DIR__ . '/../stubs/') as $file) {
                $this->publishes([
                    $file->getRealPath() => base_path("stubs/filament-chatflow/{$file->getFilename()}"),
                ], 'filament-chatflow-stubs');
            }
        }

        // Testing
        Testable::mixin(new TestsFilamentChatflow);
    }

    protected function getAssetPackageName(): ?string
    {
        return 'syofyanzuhad/filament-chatflow';
    }

    /**
     * @return array<Asset>
     */
    protected function getAssets(): array
    {
        return [
            // AlpineComponent::make('filament-chatflow', __DIR__ . '/../resources/dist/components/filament-chatflow.js'),
            Css::make('filament-chatflow-styles', __DIR__ . '/../resources/dist/filament-chatflow.css'),
            Js::make('filament-chatflow-scripts', __DIR__ . '/../resources/dist/filament-chatflow.js'),
        ];
    }

    /**
     * @return array<class-string>
     */
    protected function getCommands(): array
    {
        return [
            FilamentChatflowCommand::class,
        ];
    }

    /**
     * @return array<string>
     */
    protected function getIcons(): array
    {
        return [];
    }

    /**
     * @return array<string>
     */
    protected function getRoutes(): array
    {
        return [
            'api',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getScriptData(): array
    {
        return [];
    }

    /**
     * @return array<string>
     */
    protected function getMigrations(): array
    {
        return [
            'create_chatflow_table',
            'create_chatflow_steps_table',
            'create_chatflow_conversations_table',
            'create_chatflow_messages_table',
            'create_chatflow_analytics_table',
        ];
    }
}
