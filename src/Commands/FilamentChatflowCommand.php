<?php

namespace Syofyanzuhad\FilamentChatflow\Commands;

use Illuminate\Console\Command;
use Syofyanzuhad\FilamentChatflow\Database\Seeders\ChatflowSeeder;

class FilamentChatflowCommand extends Command
{
    public $signature = 'filament-chatflow:seed';

    public $description = 'Seed sample chatflow data';

    public function handle(): int
    {
        $this->info('Seeding sample chatflow data...');

        // Directly instantiate and run the seeder
        $seederClass = \Syofyanzuhad\FilamentChatflow\Database\Seeders\ChatflowSeeder::class;

        if (! class_exists($seederClass)) {
            $this->error('Seeder class not found. Please run: composer dump-autoload');

            return self::FAILURE;
        }

        $seeder = new $seederClass();
        $seeder->setCommand($this);
        $seeder->__invoke();

        $this->info('Sample chatflow created successfully!');

        return self::SUCCESS;
    }
}
