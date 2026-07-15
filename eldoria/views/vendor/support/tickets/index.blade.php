@extends('layouts.app')

@section('title', trans('support::messages.title'))

@section('content')
<div class="pt-24 pb-16 px-4">
    <div class="text-center py-16">
        <p class="text-accent text-xs font-display tracking-[0.4em] uppercase mb-2">✦ {{ __('theme::theme.support.eyebrow') }} ✦</p>
        <h1 class="section-title">{{ trans('support::messages.title') }}</h1>
    </div>

    @if($infoText !== null)
        <div class="max-w-3xl mx-auto mb-8">
            <div class="card-eldoria p-6 prose prose-invert prose-a:text-accent max-w-none text-text-secondary text-sm">
                {{ $infoText }}
            </div>
        </div>
    @endif

    <div class="max-w-3xl mx-auto space-y-4">
        @forelse($tickets as $ticket)
            <a href="{{ route('support.tickets.show', $ticket) }}"
               class="card-eldoria p-6 flex items-center justify-between gap-4 min-h-[48px] hover:-translate-y-0.5 transition-transform duration-300">
                <div>
                    <h2 class="font-display text-text-primary font-semibold">{{ $ticket->subject }}</h2>
                    <p class="text-text-secondary text-xs mt-1">
                        {{ $ticket->category->name }} — {{ format_date_compact($ticket->created_at) }}
                    </p>
                </div>

                @include('support::partials._status-badge', ['ticket' => $ticket])
            </a>
        @empty
            <p class="text-text-secondary text-sm text-center">{{ __('theme::theme.support.no_tickets') }}</p>
        @endforelse

        <a href="{{ route('support.tickets.create') }}" class="btn-primary justify-center min-h-[48px] w-full sm:w-auto">
            {{ trans('support::messages.actions.create') }}
        </a>
    </div>
</div>
@endsection
