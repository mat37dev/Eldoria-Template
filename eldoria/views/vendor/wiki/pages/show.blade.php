@extends('layouts.app')

@section('title', $page->title)

@section('content')
<div class="pt-24 pb-16">
    @include('wiki::partials._header', ['title' => $page->category->name])

    <div class="max-w-6xl mx-auto px-4 grid grid-cols-1 lg:grid-cols-4 gap-8">
        <div class="lg:col-span-1 space-y-2">
            @if(! $page->category->categories->isEmpty())
                @foreach($page->category->categories as $subCategory)
                    @can('view', $subCategory)
                        <a href="{{ route('wiki.show', $subCategory) }}"
                           class="card-eldoria p-4 flex items-center gap-2 text-text-secondary hover:text-accent text-sm min-h-[48px]">
                            {{ $subCategory->name }}
                        </a>
                    @endcan
                @endforeach
            @endif

            @foreach($page->category->pages as $catPage)
                <a href="{{ route('wiki.pages.show', [$page->category, $catPage]) }}"
                   class="card-eldoria p-4 flex items-center gap-2 text-sm min-h-[48px] {{ $page->is($catPage) ? 'border-accent text-accent' : 'text-text-secondary hover:text-accent' }}">
                    {{ $catPage->title }}
                </a>
            @endforeach

            <a href="{{ $page->category->parent !== null ? route('wiki.show', $page->category->parent) : route('wiki.index') }}"
               class="flex items-center gap-2 text-text-secondary hover:text-accent text-sm min-h-[48px] px-4">
                ← {{ trans('wiki::messages.back') }}
            </a>
        </div>

        <div class="lg:col-span-3">
            <div class="card-eldoria p-8 prose prose-invert prose-headings:font-display prose-headings:text-accent prose-a:text-accent max-w-none">
                {!! $page->content !!}
            </div>
        </div>
    </div>
</div>
@endsection
