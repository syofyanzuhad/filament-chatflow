<x-mail::message>
# {{ __('chatflow::chatflow.email.greeting') }}{{ $conversation->user_name ? ', ' . $conversation->user_name : '' }}

{{ __('chatflow::chatflow.email.intro') }}

## {{ __('chatflow::chatflow.email.conversation_details') }}

<x-mail::panel>
**{{ __('chatflow::chatflow.email.started_at') }}:** {{ $conversation->started_at->format('M d, Y h:i A') }}
**{{ __('chatflow::chatflow.email.ended_at') }}:** {{ $conversation->ended_at ? $conversation->ended_at->format('M d, Y h:i A') : '-' }}
**{{ __('chatflow::chatflow.email.duration') }}:** {{ $conversation->duration_in_minutes ? round($conversation->duration_in_minutes, 1) . ' minutes' : '-' }}
</x-mail::panel>

## {{ __('chatflow::chatflow.email.messages') }}

@foreach($messages as $message)
<x-mail::panel>
**{{ $message->isBot() ? __('chatflow::chatflow.email.bot') : __('chatflow::chatflow.email.you') }}** - {{ $message->created_at->format('h:i A') }}

{{ $message->content }}

@if($message->options)
@foreach($message->options as $option)
- {{ is_array($option) ? ($option['label'] ?? $option['value']) : $option }}
@endforeach
@endif
</x-mail::panel>
@endforeach

---

{{ __('chatflow::chatflow.email.footer') }}

Thanks,
{{ config('app.name') }}
</x-mail::message>
