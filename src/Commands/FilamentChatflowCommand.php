<?php

namespace Syofyanzuhad\FilamentChatflow\Commands;

use Illuminate\Console\Command;

class FilamentChatflowCommand extends Command
{
    public $signature = 'filament-chatflow';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
