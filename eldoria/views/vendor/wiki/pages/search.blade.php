@extends('layouts.app')

@section('title', trans('wiki::messages.search.results'))

@section('content')
<div class="pt-24 pb-16">
    @include('wiki::partials._header', ['title' => trans('wiki::messages.search.results'), 'search' => $search])

    <div class="max-w-3xl mx-auto px-4 space-y-4">
        @forelse($pages as $page)
            @can('view', $page->category)
                <div class="card-eldoria p-6">
                    <h2 class="font-display text-text-primary font-semibold mb-2">
                        <a href="{{ route('wiki.pages.show', [$page->category, $page]) }}" class="hover:text-accent transition-colors">
                            {{ $page->title }}
                        </a>
                    </h2>
                    <span class="inline-block px-2 py-0.5 rounded-full text-xs bg-accent/10 border border-accent/20 text-accent mb-3">
                        {{ $page->category->name }}
                    </span>
                    <p class="text-text-secondary text-sm">{{ \Illuminate\Support\Str::limit(strip_tags($page->content), 300) }}</p>
                </div>
            @endcan
        @empty
            <p class="text-text-secondary text-sm text-center">{{ trans('wiki::messages.search.empty') }}</p>
        @endforelse

        <div class="pt-4">
            {{ $pages->withQueryString()->links('pagination::tailwind') }}
        </div>
    </div>
</div>
@endsection
