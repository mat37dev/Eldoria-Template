@extends('layouts.app')

@section('title', __('theme::theme.faq.title'))

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (!window.location.hash) {
                return;
            }

            const target = document.getElementById(window.location.hash.substring(1));
            if (!target) {
                return;
            }

            const button = target.querySelector('[data-faq-toggle]');
            if (button) {
                button.click();
            }
        });
    </script>
@endpush

@section('content')
<div class="pt-24 pb-16 px-4">
    <div class="text-center py-16">
        <p class="text-accent text-xs font-display tracking-[0.4em] uppercase mb-2">✦ {{ __('theme::theme.faq.eyebrow') }} ✦</p>
        <h1 class="section-title">{{ __('theme::theme.faq.title') }}</h1>
    </div>

    <div class="max-w-3xl mx-auto">
        @if($questions->isEmpty())
            <p class="text-text-secondary text-sm text-center">{{ trans('faq::messages.empty') }}</p>
        @else
            <div class="space-y-4" x-data="{ open: {{ $questions->first()->id }} }">
                @foreach($questions as $question)
                    <div class="card-eldoria overflow-hidden" id="{{ \Illuminate\Support\Str::slug($question->name) }}">
                        <button type="button" data-faq-toggle
                                @click="open = (open === {{ $question->id }} ? null : {{ $question->id }})"
                                class="w-full flex items-center justify-between gap-4 p-6 min-h-[48px] text-left">
                            <span class="font-display text-text-primary font-semibold">{{ $question->name }}</span>
                            <svg class="faq-chevron w-5 h-5 text-accent flex-shrink-0"
                                 :class="open === {{ $question->id }} ? 'rotate-180' : ''"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="open === {{ $question->id }}"
                             x-transition:enter="faq-answer-transition"
                             x-transition:enter-start="opacity-0"
                             x-transition:enter-end="opacity-100"
                             x-transition:leave="faq-answer-transition"
                             x-transition:leave-start="opacity-100"
                             x-transition:leave-end="opacity-0"
                             class="px-6 pb-6 prose prose-invert prose-a:text-accent max-w-none text-text-secondary text-sm">
                            {!! $question->answer !!}
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
