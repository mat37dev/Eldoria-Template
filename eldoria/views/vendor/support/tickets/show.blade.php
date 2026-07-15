@extends('layouts.app')

@section('title', $ticket->subject)

@if(! $ticket->isClosed())
    @include('elements.markdown-editor', [
        'imagesUploadUrl' => route('support.comments.attachments.pending', $pendingId),
        'autosaveId' => 'support_ticket_'.$ticket->id,
    ])
@endif

@section('content')
<div class="pt-24 pb-16 px-4">
    <div class="max-w-3xl mx-auto">
        <div class="text-center py-12">
            <h1 class="font-display text-2xl sm:text-3xl font-bold text-text-primary mb-4">{{ $ticket->subject }}</h1>
            @include('support::partials._status-badge', ['ticket' => $ticket])
            <p class="text-text-secondary text-sm mt-4">
                @lang('support::messages.tickets.info', ['author' => e($ticket->author->name), 'category' => e($ticket->category->name), 'date' => format_date($ticket->created_at)])
            </p>
        </div>

        <div class="space-y-4">
            @foreach($ticket->comments as $comment)
                <div class="card-eldoria p-6 flex gap-4">
                    <img src="{{ $comment->author->getAvatar(48) }}" alt="{{ $comment->author->name }}" class="w-12 h-12 rounded-sm flex-shrink-0">
                    <div class="flex-1 min-w-0">
                        <p class="text-text-secondary text-xs mb-2">
                            @lang('messages.comments.author', ['user' => e($comment->author->name), 'date' => format_date($comment->created_at, true)])
                        </p>
                        <div class="prose prose-invert prose-a:text-accent max-w-none text-text-primary text-sm">
                            {{ $comment->parseContent() }}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if($ticket->isClosed())
            <div class="card-eldoria p-6 mt-4 text-center">
                <p class="text-text-secondary text-sm">{{ trans('support::messages.tickets.closed') }}</p>

                @if($canReopen)
                    <form action="{{ route('support.tickets.open', $ticket) }}" method="POST" class="mt-4">
                        @csrf
                        <button type="submit" class="btn-primary min-h-[48px]">
                            {{ trans('support::messages.actions.reopen') }}
                        </button>
                    </form>
                @endif
            </div>
        @else
            <div class="card-eldoria p-6 mt-4 space-y-4">
                <form action="{{ route('support.tickets.comments.store', $ticket) }}" method="POST">
                    @csrf

                    <input type="hidden" name="pending_id" value="{{ $pendingId }}">

                    <label class="block text-xs text-text-secondary uppercase tracking-widest mb-2" for="content">
                        {{ trans('support::messages.fields.comment') }}
                    </label>
                    <textarea id="content" name="content" rows="4"
                              class="markdown-editor w-full bg-bg-primary border border-accent/20 rounded-sm px-4 py-3 text-text-primary text-sm">{{ old('content') }}</textarea>
                    @error('content')
                        <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                    @enderror

                    <button type="submit" class="btn-primary min-h-[48px] mt-4">
                        {{ trans('messages.actions.comment') }}
                    </button>
                </form>

                <form action="{{ route('support.tickets.close', $ticket) }}" method="POST">
                    @csrf
                    <button type="submit" class="inline-flex items-center justify-center px-6 py-3 min-h-[48px] border border-accent/40 text-text-secondary font-display text-sm tracking-widest uppercase hover:border-accent hover:text-accent transition-all duration-300 rounded-sm">
                        {{ trans('messages.actions.close') }}
                    </button>
                </form>
            </div>
        @endif
    </div>
</div>
@endsection
