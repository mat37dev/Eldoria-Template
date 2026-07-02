@extends('layouts.app')

@section('title', $post->title)
@section('description', $post->description)

@section('content')
<div class="pt-24 pb-16 max-w-3xl mx-auto px-4">

    @unless($post->isPublished())
        <div class="mb-6 border border-accent/40 bg-accent/10 text-accent text-sm rounded-sm px-4 py-3">
            {{ __('theme::theme.posts.unpublished_notice') }}
        </div>
    @endunless

    <div class="mb-6">
        <h1 class="font-display text-3xl md:text-4xl font-bold text-text-primary">{{ $post->title }}</h1>
    </div>

    @if($post->hasImage())
        <img src="{{ $post->imageUrl() }}" alt="{{ $post->title }}" class="w-full rounded-sm mb-6">
    @endif

    <div class="card-eldoria p-8 mb-8">
        <div class="prose prose-invert text-text-secondary text-sm max-w-none mb-6">
            {!! $post->content !!}
        </div>

        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 pt-6 border-t border-accent/10">
            <button type="button"
                    id="post-like-button"
                    data-like-url="{{ route('posts.like', $post) }}"
                    data-dislike-url="{{ route('posts.dislike', $post) }}"
                    data-liked="{{ $post->isLiked() ? '1' : '0' }}"
                    @guest disabled @endguest
                    class="btn-primary min-h-[48px] disabled:opacity-50">
                <span id="post-like-icon">{{ $post->isLiked() ? '♥' : '♡' }}</span>
                {{ __('theme::theme.posts.like') }}
                (<span id="post-like-count">{{ $post->likes->count() }}</span>)
            </button>

            <p class="text-text-secondary text-xs">
                {{ __('theme::theme.posts.posted_by', ['user' => $post->author->name, 'date' => format_date($post->published_at)]) }}
            </p>
        </div>
    </div>

    <section id="comments" class="space-y-4 mb-8">
        <h2 class="font-display text-accent text-sm tracking-widest uppercase">{{ __('theme::theme.posts.comments_title') }}</h2>

        @foreach($post->comments as $comment)
            <div class="card-eldoria p-4 flex gap-3" data-comment-id="{{ $comment->id }}">
                <img src="{{ $comment->author->getAvatar() }}" alt="{{ $comment->author->name }}" class="w-10 h-10 rounded-sm flex-shrink-0">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between gap-2">
                        <p class="text-text-secondary text-xs">
                            {{ __('theme::theme.posts.posted_by', ['user' => $comment->author->name, 'date' => format_date($comment->created_at, true)]) }}
                        </p>
                        @can('delete', $comment)
                            <form action="{{ route('posts.comments.destroy', [$post, $comment]) }}" method="POST"
                                  data-confirm-message="{{ __('theme::theme.posts.comment_delete_confirm') }}"
                                  class="comment-delete-form">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-text-secondary hover:text-red-400 text-xs">{{ __('theme::theme.posts.comment_delete') }}</button>
                            </form>
                        @endcan
                    </div>
                    <div class="text-text-primary text-sm mt-1">{{ $comment->parseContent() }}</div>
                </div>
            </div>
        @endforeach
    </section>

    @can('create', \Azuriom\Models\Comment::class)
        <div class="card-eldoria p-6">
            <h3 class="font-display text-accent text-sm tracking-widest uppercase mb-4">{{ __('theme::theme.posts.comment_form_title') }}</h3>
            <form action="{{ route('posts.comments.store', $post) }}" method="POST">
                @csrf
                <label class="block text-xs text-text-secondary uppercase tracking-widest mb-2" for="content">
                    {{ __('theme::theme.posts.comment_content_label') }}
                </label>
                <textarea id="content" name="content" rows="4" required
                          class="w-full bg-bg-primary border border-accent/20 rounded-sm px-4 py-3 text-text-primary text-sm
                                 focus:outline-none focus:border-accent/60 resize-none mb-4"></textarea>
                @error('content')
                    <p class="text-red-400 text-xs mb-4">{{ $message }}</p>
                @enderror
                <button type="submit" class="btn-primary min-h-[48px]">{{ __('theme::theme.posts.comment_submit') }}</button>
            </form>
        </div>
    @else
        @guest
            <div class="text-center py-6 text-text-secondary text-sm">
                {{ __('theme::theme.posts.comment_guest') }}
            </div>
        @endguest
    @endcan
</div>
@endsection
