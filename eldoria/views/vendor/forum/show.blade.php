@extends('layouts.app')

@section('title', $category->name ?? 'Forum')

@section('content')
<div class="pt-24 pb-16">
    <div class="text-center py-12 px-4">
        <p class="text-accent text-xs font-display tracking-[0.4em] uppercase mb-2">Forum</p>
        <h1 class="section-title">{{ $category->name ?? '' }}</h1>
    </div>

    <div class="max-w-4xl mx-auto px-4">

        @auth
        <div class="mb-6 text-right">
            <a href="{{ route('forum.posts.create', ['category' => $category->id ?? '']) }}" class="btn-primary text-sm py-2 px-4">
                + Nouveau sujet
            </a>
        </div>
        @endauth

        <div class="space-y-3">
            @forelse($posts ?? [] as $post)
            <a href="{{ route('forum.posts.show', $post) }}"
               class="card-eldoria p-4 flex items-center gap-4 hover:translate-x-1 transition-transform duration-200 block">
                <div class="flex-1 min-w-0">
                    <div class="font-display text-text-primary font-semibold truncate">{{ $post->name }}</div>
                    <div class="text-text-secondary text-xs mt-1">
                        {{ $post->author->name ?? 'Inconnu' }} · {{ $post->created_at->diffForHumans() }}
                    </div>
                </div>
                <div class="text-right flex-shrink-0 hidden sm:block">
                    <div class="text-accent/70 text-sm font-display">{{ $post->comments_count ?? 0 }}</div>
                    <div class="text-text-secondary text-xs uppercase tracking-widest">Réponses</div>
                </div>
            </a>
            @empty
            <div class="text-center py-12 text-text-secondary">
                Aucun sujet dans cette catégorie.
            </div>
            @endforelse
        </div>

        @if(isset($posts) && method_exists($posts, 'links'))
        <div class="mt-8">{{ $posts->links() }}</div>
        @endif
    </div>
</div>
@endsection
