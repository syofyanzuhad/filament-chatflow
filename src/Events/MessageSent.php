<?php

namespace Syofyanzuhad\FilamentChatflow\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Syofyanzuhad\FilamentChatflow\Models\ChatflowMessage;

class MessageSent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public ChatflowMessage $message
    ) {}
}
