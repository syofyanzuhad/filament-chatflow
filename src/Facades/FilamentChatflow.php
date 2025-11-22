<?php

namespace Syofyanzuhad\FilamentChatflow\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Syofyanzuhad\FilamentChatflow\FilamentChatflow
 */
class FilamentChatflow extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Syofyanzuhad\FilamentChatflow\FilamentChatflow::class;
    }
}
