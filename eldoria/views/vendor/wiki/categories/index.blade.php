@extends('layouts.app')

@section('title', trans('wiki::messages.title'))

@section('content')
<div class="pt-24 pb-16">
    @include('wiki::partials._header', ['title' => trans('wiki::messages.title')])

    <div class="max-w-6xl mx-auto px-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($categories as $category)
                <a href="{{ route('wiki.show', $category) }}"
                   class="card-eldoria p-6 flex flex-col items-center text-center gap-3 hover:-translate-y-1 transition-transform duration-300 min-h-[48px]">
                    <svg class="w-10 h-10 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/>
                    </svg>
                    <h2 class="font-display text-text-primary font-semibold">{{ $category->name }}</h2>
                </a>
            @endforeach
        </div>
    </div>
</div>
@endsection
