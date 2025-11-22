<?php

namespace Syofyanzuhad\FilamentChatflow\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Syofyanzuhad\FilamentChatflow\Models\ChatflowConversation;
use Syofyanzuhad\FilamentChatflow\Models\ChatflowStep;

class StepCompleted
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public ChatflowConversation $conversation,
        public ChatflowStep $step
    ) {}
}
