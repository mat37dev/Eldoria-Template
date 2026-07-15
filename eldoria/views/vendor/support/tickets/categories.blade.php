@extends('layouts.app')

@section('title', trans('support::messages.tickets.open'))

@section('content')
<div class="pt-24 pb-16 px-4">
    <div class="text-center py-16">
        <p class="text-accent text-xs font-display tracking-[0.4em] uppercase mb-2">✦ {{ __('theme::theme.support.eyebrow') }} ✦</p>
        <h1 class="section-title">{{ trans('support::messages.tickets.open') }}</h1>
    </div>

    @if($infoText !== null)
        <div class="max-w-3xl mx-auto mb-8">
            <div class="card-eldoria p-6 prose prose-invert prose-a:text-accent max-w-none text-text-secondary text-sm">
                {{ $infoText }}
            </div>
        </div>
    @endif

    <div class="max-w-5xl mx-auto">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            @foreach($categories as $category)
                <div class="card-eldoria p-6 flex flex-col gap-4">
                    <div class="flex items-start gap-4">
                        <svg class="w-10 h-10 text-accent flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 17.25h.007v.008H12v-.008z" />
                        </svg>
                        <div>
                            <h2 class="font-display text-text-primary font-semibold">{{ $category->name }}</h2>
                            @if($category->description)
                                <p class="text-text-secondary text-sm mt-1">{{ $category->description }}</p>
                            @endif
                        </div>
                    </div>

                    <a href="{{ route('support.category.tickets.create', $category) }}"
                       class="btn-primary justify-center min-h-[48px] mt-auto">
                        {{ trans('support::messages.actions.create') }}
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
