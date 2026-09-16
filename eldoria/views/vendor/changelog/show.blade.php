@extends('layouts.app')

@section('title', $category->name ?? $title)

@section('content')
<div class="pt-24 pb-16">
    <div class="text-center py-16 px-4">
        <p class="section-eyebrow">✦ {{ __('theme::theme.changelog.eyebrow') }} ✦</p>
        <h1 class="section-title">{{ $category->name ?? $title }}</h1>
    </div>

    <div class="max-w-6xl mx-auto px-4 grid grid-cols-1 lg:grid-cols-4 gap-8">
        <div class="lg:col-span-1">
            @include('changelog::sidebar')
        </div>

        <div class="lg:col-span-3 space-y-4">
            @forelse($updates as $update)
                <div class="card-eldoria p-6" data-aos="fade-up">
                    <h2 class="font-display text-text-primary text-lg font-semibold mb-3">{{ $update->name }}</h2>

                    <div class="flex flex-wrap items-center gap-2 mb-4">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs border bg-accent/10 border-accent/40 text-accent">
                            {{ $update->category->name }}
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs border bg-bg-primary border-text-secondary/30 text-text-secondary">
                            {{ format_date($update->created_at) }}
                        </span>
                    </div>

                    <div class="prose max-w-none text-text-primary text-sm">
                        {!! $update->description !!}
                    </div>
                </div>
            @empty
                <div class="card-eldoria p-6 text-center text-text-secondary text-sm">
                    {{ trans('changelog::messages.empty') }}
                </div>
            @endforelse

            <div class="pt-4">
                {{ $updates->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
