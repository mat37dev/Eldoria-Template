@extends('layouts.app')

@section('title', $post->name ?? 'Discussion')

@section('content')
<div class="pt-24 pb-16 max-w-3xl mx-auto px-4">

    <div class="mt-8 mb-6">
        <p class="text-accent text-xs font-display uppercase tracking-widest mb-2">
            {{ $post->category->name ?? 'Forum' }}
        </p>
        <h1 class="font-display text-2xl md:text-3xl font-bold text-text-primary">{{ $post->name ?? '' }}</h1>
    </div>

    <div class="space-y-4">
        {{-- Post original --}}
        <div class="card-eldoria p-6">
            <div class="flex items-center gap-3 mb-4 pb-4 border-b border-accent/10">
                <div class="w-10 h-10 rounded-full bg-accent/20 flex items-center justify-center font-display text-accent font-bold text-sm flex-shrink-0">
                    {{ strtoupper(substr($post->author->name ?? '?', 0, 1)) }}
                </div>
                <div>
                    <div class="font-display text-text-primary text-sm font-semibold">{{ $post->author->name ?? 'Inconnu' }}</div>
                    <div class="text-text-secondary text-xs">{{ $post->created_at->format('d/m/Y à H:i') }}</div>
                </div>
            </div>
            <div class="prose prose-invert text-text-secondary text-sm max-w-none">
                {!! $post->content !!}
            </div>
        </div>

        {{-- Commentaires --}}
        @foreach($post->comments ?? [] as $comment)
        <div class="card-eldoria p-6 ml-4">
            <div class="flex items-center gap-3 mb-4 pb-4 border-b border-accent/10">
                <div class="w-8 h-8 rounded-full bg-accent/10 flex items-center justify-center font-display text-accent/70 font-bold text-xs flex-shrink-0">
                    {{ strtoupper(substr($comment->author->name ?? '?', 0, 1)) }}
                </div>
                <div>
                    <div class="font-display text-text-primary text-sm font-semibold">{{ $comment->author->name ?? 'Inconnu' }}</div>
                    <div class="text-text-secondary text-xs">{{ $comment->created_at->format('d/m/Y à H:i') }}</div>
                </div>
            </div>
            <div class="text-text-secondary text-sm">
                {!! $comment->content !!}
            </div>
        </div>
        @endforeach
    </div>

    {{-- Formulaire de réponse --}}
    @auth
    <div class="card-eldoria p-6 mt-8">
        <h3 class="font-display text-accent text-sm tracking-widest uppercase mb-4">Répondre</h3>
        <form action="{{ route('forum.comments.store', $post) }}" method="POST">
            @csrf
            <textarea name="content"
                      class="w-full bg-bg-primary border border-accent/20 rounded-sm px-4 py-3 text-text-primary text-sm
                             focus:outline-none focus:border-accent/60 resize-none mb-4"
                      rows="4"
                      placeholder="Votre réponse..." required></textarea>
            <button type="submit" class="btn-primary">Publier</button>
        </form>
    </div>
    @else
    <div class="text-center py-8">
        <a href="{{ route('login') }}" class="btn-primary">Connectez-vous pour répondre</a>
    </div>
    @endauth

</div>
@endsection
