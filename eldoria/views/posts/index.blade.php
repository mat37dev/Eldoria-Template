@extends('layouts.app')

@section('title', __('theme::theme.posts.index_title'))

@section('content')
<div class="pt-24 pb-16">
    <div class="text-center py-16 px-4">
        <p class="text-accent text-xs font-display tracking-[0.4em] uppercase mb-2">✦ {{ __('theme::theme.posts.index_title') }} ✦</p>
        <h1 class="section-title">{{ __('theme::theme.posts.index_title') }}</h1>
    </div>

    <div class="max-w-5xl mx-auto px-4">
        <form action="{{ route('posts.index') }}" method="GET" role="search" class="max-w-sm mx-auto mb-10">
            <label class="sr-only" for="postsSearchInput">{{ __('theme::theme.posts.search_placeholder') }}</label>
            <div class="flex gap-2">
                <input type="search" id="postsSearchInput" name="q" value="{{ $search ?? '' }}"
                       placeholder="{{ __('theme::theme.posts.search_placeholder') }}"
                       class="flex-1 bg-bg-primary border border-accent/20 rounded-sm px-4 py-3 text-text-primary text-sm min-h-[48px]">
                <button type="submit" class="btn-primary min-h-[48px] px-4">🔍</button>
            </div>
        </form>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            @forelse($posts as $post)
                <div class="card-eldoria overflow-hidden flex flex-col" data-aos="fade-up">
                    @if($post->hasImage())
                        <img src="{{ $post->imageUrl() }}" alt="{{ $post->title }}" class="w-full h-40 object-cover">
                    @endif
                    <div class="p-6 flex flex-col flex-1">
                        <h3 class="font-display text-text-primary font-semibold mb-2">
                            <a href="{{ route('posts.show', $post) }}" class="hover:text-accent transition-colors">{{ $post->title }}</a>
                        </h3>
                        <p class="text-text-secondary text-sm mb-4 flex-1">{{ Str::limit(strip_tags($post->content), 200) }}</p>
                        <div class="flex items-center justify-between">
                            <a href="{{ route('posts.show', $post) }}" class="btn-primary text-xs py-2 px-4">
                                {{ __('theme::theme.posts.read_more') }}
                            </a>
                            <span class="text-text-secondary text-xs">
                                {{ __('theme::theme.posts.posted_by', ['user' => $post->author->name, 'date' => format_date($post->published_at)]) }}
                            </span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="sm:col-span-2 text-center py-12 text-text-secondary">
                    {{ __('theme::theme.posts.no_posts') }}
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
