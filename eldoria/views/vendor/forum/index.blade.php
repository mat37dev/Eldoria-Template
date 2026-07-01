@extends('layouts.app')

@section('title', 'Forum')

@section('content')
<div class="pt-24 pb-16">
    <div class="text-center py-16 px-4">
        <p class="text-accent text-xs font-display tracking-[0.4em] uppercase mb-2">✦ Communauté ✦</p>
        <h1 class="section-title">Forum</h1>
    </div>

    <div class="max-w-4xl mx-auto px-4 space-y-4">
        @foreach($categories ?? [] as $category)
        <a href="{{ route('forum.categories.show', $category) }}"
           class="card-eldoria p-5 flex items-center gap-4 hover:translate-x-1 transition-transform duration-200 block"
           data-aos="fade-up">
            <div class="w-10 h-10 flex items-center justify-center text-accent border border-accent/30 rounded-sm flex-shrink-0 font-display font-bold text-sm">
                {{ $loop->iteration }}
            </div>
            <div class="flex-1 min-w-0">
                <div class="font-display text-text-primary font-semibold">{{ $category->name }}</div>
                <div class="text-text-secondary text-xs mt-0.5 truncate">{{ $category->description }}</div>
            </div>
            <div class="text-right flex-shrink-0 hidden sm:block">
                <div class="text-accent font-display text-sm font-bold">{{ $category->posts_count ?? 0 }}</div>
                <div class="text-text-secondary text-xs uppercase tracking-widest">Sujets</div>
            </div>
        </a>
        @endforeach
    </div>
</div>
@endsection
