<div
    x-data="chatWidget"
    @chat-widget-toggle.window="$wire.toggle()"
    class="fixed z-[{{ config('chatflow.widget.z_index', 9999) }}] {{ $chatflow->position }}"
    :class="{
        'bottom-4 right-4': '{{ $chatflow->position }}' === 'bottom-right',
        'bottom-4 left-4': '{{ $chatflow->position }}' === 'bottom-left',
        'top-4 right-4': '{{ $chatflow->position }}' === 'top-right',
        'top-4 left-4': '{{ $chatflow->position }}' === 'top-left'
    }"
>
    {{-- Chat Button --}}
    <button
        @click="$wire.toggle()"
        x-show="!{{ $isOpen ? 'true' : 'false' }}"
        class="flex items-center justify-center w-14 h-14 rounded-full shadow-lg transition-transform hover:scale-110 focus:outline-none focus:ring-2 focus:ring-offset-2"
        style="background-color: {{ $chatflow->settings['theme_color'] ?? config('chatflow.widget.theme_color') }}"
        aria-label="{{ __('chatflow::chatflow.actions.start_chat') }}"
    >
        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
        </svg>

        @if($hasNewMessage)
        <span class="absolute top-0 right-0 w-3 h-3 bg-red-500 rounded-full animate-pulse"></span>
        @endif
    </button>

    {{-- Chat Window --}}
    <div
        x-show="{{ $isOpen ? 'true' : 'false' }}"
        x-transition
        class="flex flex-col bg-white dark:bg-gray-800 rounded-lg shadow-2xl overflow-hidden"
        style="width: 380px; height: {{ $isMinimized ? '60px' : '600px' }}; max-height: calc(100vh - 120px);"
    >
        {{-- Header --}}
        <div
            class="flex items-center justify-between px-4 py-3 text-white"
            style="background-color: {{ $chatflow->settings['theme_color'] ?? config('chatflow.widget.theme_color') }}"
        >
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold">{{ __('chatflow::chatflow.widget.title') }}</h3>
                    <p class="text-xs text-white/80">{{ __('chatflow::chatflow.widget.subtitle') }}</p>
                </div>
            </div>
            <div class="flex items-center space-x-2">
                @if(!$isMinimized)
                <button
                    wire:click="minimize"
                    class="p-1 hover:bg-white/20 rounded transition"
                    aria-label="{{ __('chatflow::chatflow.widget.minimize') }}"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                    </svg>
                </button>
                @else
                <button
                    wire:click="maximize"
                    class="p-1 hover:bg-white/20 rounded transition"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                    </svg>
                </button>
                @endif
                <button
                    wire:click="toggle"
                    class="p-1 hover:bg-white/20 rounded transition"
                    aria-label="{{ __('chatflow::chatflow.widget.close') }}"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        @if(!$isMinimized)
        {{-- Messages --}}
        <div
            x-ref="messagesContainer"
            class="flex-1 overflow-y-auto p-4 space-y-4 bg-gray-50 dark:bg-gray-900"
        >
            @forelse($messages as $message)
            <div class="flex {{ $message['type'] === 'user' ? 'justify-end' : 'justify-start' }}">
                <div
                    class="max-w-[80%] px-4 py-2 rounded-lg {{ $message['type'] === 'user' ? 'bg-blue-500 text-white' : 'bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100' }}"
                >
                    <p class="text-sm">{{ $message['content'] }}</p>

                    @if(isset($message['options']) && !empty($message['options']) && $loop->last)
                    <div class="mt-3 space-y-2">
                        @foreach($message['options'] as $option)
                        <button
                            wire:click="selectOption('{{ $option['value'] }}')"
                            class="block w-full text-left px-3 py-2 text-sm bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded transition"
                            @if($isLoading) disabled @endif
                        >
                            {{ is_array($option['label']) ? ($option['label'][app()->getLocale()] ?? $option['label']['en']) : $option['label'] }}
                        </button>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
            @empty
            <div class="text-center text-gray-500 dark:text-gray-400 text-sm">
                {{ __('chatflow::chatflow.messages.welcome') }}
            </div>
            @endforelse

            @if($isLoading)
            <div class="flex justify-start">
                <div class="bg-white dark:bg-gray-800 px-4 py-2 rounded-lg">
                    <div class="flex space-x-2">
                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"></div>
                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.4s"></div>
                    </div>
                </div>
            </div>
            @endif
        </div>

        {{-- Input Area --}}
        @if(!$isCompleted && $sessionId)
        <div class="p-4 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
            @if(!$currentOptions)
            <form wire:submit="sendMessage" class="flex items-center space-x-2">
                <input
                    wire:model="userMessage"
                    type="text"
                    class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                    placeholder="{{ __('chatflow::chatflow.widget.placeholder') }}"
                    @if($isLoading) disabled @endif
                    maxlength="{{ config('chatflow.security.max_message_length', 1000) }}"
                >
                <button
                    type="submit"
                    class="p-2 text-white rounded-lg transition disabled:opacity-50"
                    style="background-color: {{ $chatflow->settings['theme_color'] ?? config('chatflow.widget.theme_color') }}"
                    @if($isLoading) disabled @endif
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                    </svg>
                </button>
            </form>
            @endif
        </div>
        @elseif($isCompleted)
        <div class="p-4 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 text-center">
            <button
                wire:click="restartConversation"
                class="px-4 py-2 text-sm text-white rounded-lg transition"
                style="background-color: {{ $chatflow->settings['theme_color'] ?? config('chatflow.widget.theme_color') }}"
            >
                {{ __('chatflow::chatflow.actions.restart') }}
            </button>
        </div>
        @endif

        {{-- Footer --}}
        <div class="px-4 py-2 bg-gray-100 dark:bg-gray-900 text-center text-xs text-gray-500 dark:text-gray-400">
            {{ __('chatflow::chatflow.widget.powered_by') }}
        </div>
        @endif
    </div>
</div>

@script
<script>
Alpine.data('chatWidget', () => ({
    init() {
        this.$watch('$wire.messages', () => {
            this.$nextTick(() => this.scrollToBottom());
        });

        Livewire.on('scrollToBottom', () => {
            this.scrollToBottom();
        });

        Livewire.on('playNotificationSound', () => {
            this.playSound('{{ config('chatflow.widget.notification_sound') }}');
        });
    },

    scrollToBottom() {
        const container = this.$refs.messagesContainer;
        if (container) {
            container.scrollTop = container.scrollHeight;
        }
    },

    playSound(soundFile) {
        if (!{{ config('chatflow.widget.sound_enabled', true) ? 'true' : 'false' }}) return;

        const audio = new Audio(`/vendor/filament-chatflow/sounds/${soundFile}`);
        audio.play().catch(e => console.log('Sound play failed:', e));
    }
}));
</script>
@endscript
