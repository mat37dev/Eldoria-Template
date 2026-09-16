@extends('layouts.app')

@section('title', trans('support::messages.tickets.open'))

@if($category->fields->isEmpty())
    @include('elements.markdown-editor', [
        'imagesUploadUrl' => route('support.comments.attachments.pending', $pendingId),
        'autosaveId' => 'support_ticket',
    ])
@endif

@section('content')
<div class="pt-24 pb-16 px-4">
    <div class="text-center py-16">
        <p class="section-eyebrow">✦ {{ __('theme::theme.support.eyebrow') }} ✦</p>
        <h1 class="section-title">{{ $category->name }}</h1>

        <div class="flex flex-wrap items-center justify-center gap-x-6 gap-y-2 mt-4 text-text-secondary text-xs uppercase tracking-widest">
            <span class="inline-flex items-center gap-2">
                <span aria-hidden="true">⏱</span> {{ __('theme::theme.support.reassurance_response') }}
            </span>
            <span class="inline-flex items-center gap-2">
                <span aria-hidden="true">🛡</span> {{ __('theme::theme.support.reassurance_team') }}
            </span>
            <span class="inline-flex items-center gap-2">
                <span aria-hidden="true">📎</span> {{ __('theme::theme.support.reassurance_attachments') }}
            </span>
        </div>
    </div>

    <div class="max-w-2xl mx-auto">
        <div class="card-eldoria p-8">
            <div class="flex items-center gap-4 mb-6">
                <div class="w-14 h-14 rounded-full flex items-center justify-center flex-shrink-0 border-2 border-accent/40"
                     style="background: linear-gradient(135deg, var(--color-accent) 0%, var(--color-accent-secondary) 100%)">
                    <svg class="w-7 h-7 text-text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 17.25h.007v.008H12v-.008z" />
                    </svg>
                </div>
                <a href="{{ route('support.tickets.create') }}"
                   class="text-text-secondary text-xs uppercase tracking-widest hover:text-text-primary transition-colors">
                    ← {{ __('theme::theme.support.back_to_categories') }}
                </a>
            </div>

            <form action="{{ route('support.category.tickets.store', $category) }}" method="POST" class="space-y-6">
                @csrf

                <input type="hidden" name="pending_id" value="{{ $pendingId }}">

                <div>
                    <label class="block text-xs text-text-secondary uppercase tracking-widest mb-2" for="subjectInput">
                        {{ trans('support::messages.fields.subject') }}
                    </label>
                    <input type="text" id="subjectInput" name="subject" value="{{ old('subject') }}" required
                           class="w-full bg-bg-primary border border-accent/20 rounded-sm px-4 py-3 text-text-primary text-sm
                                  focus:outline-none focus:border-accent/60 transition-colors min-h-[48px]">
                    @error('subject')
                        <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                @if($category->fields->isEmpty())
                    <div>
                        <label class="block text-xs text-text-secondary uppercase tracking-widest mb-2" for="contentInput">
                            {{ trans('messages.fields.content') }}
                        </label>
                        <textarea id="contentInput" name="content" rows="6"
                                  class="markdown-editor w-full bg-bg-primary border border-accent/20 rounded-sm px-4 py-3 text-text-primary text-sm">{{ old('content') }}</textarea>
                        @error('content')
                            <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                @else
                    @foreach($category->fields as $field)
                        <div>
                            @if($field->type === 'checkbox')
                                <label class="flex items-center gap-2 text-text-secondary cursor-pointer">
                                    <input type="checkbox" name="{{ $field->inputName() }}" id="{{ $field->inputName() }}"
                                           class="accent-[var(--color-accent)]"
                                           @required($field->is_required) @checked(old($field->inputName()))>
                                    {{ $field->name }}
                                    @if($field->is_required)
                                        <span class="text-accent">*</span>
                                    @endif
                                </label>
                            @else
                                <label class="block text-xs text-text-secondary uppercase tracking-widest mb-2" for="{{ $field->inputName() }}">
                                    {{ $field->name }}
                                    @if($field->is_required)
                                        <span class="text-accent">*</span>
                                    @endif
                                </label>

                                @if($field->type === 'dropdown')
                                    <select name="{{ $field->inputName() }}" id="{{ $field->inputName() }}"
                                            @required($field->is_required)
                                            class="w-full bg-bg-primary border border-accent/20 rounded-sm px-4 py-3 text-text-primary text-sm
                                                   focus:outline-none focus:border-accent/60 transition-colors min-h-[48px]">
                                        @foreach($field->options as $option)
                                            <option @selected(old($field->inputName()) === $option)>{{ $option }}</option>
                                        @endforeach
                                    </select>
                                @elseif($field->type === 'textarea')
                                    <textarea name="{{ $field->inputName() }}" id="{{ $field->inputName() }}" rows="4"
                                              @required($field->is_required)
                                              class="w-full bg-bg-primary border border-accent/20 rounded-sm px-4 py-3 text-text-primary text-sm
                                                     focus:outline-none focus:border-accent/60 transition-colors">{{ old($field->inputName()) }}</textarea>
                                @else
                                    <input type="{{ $field->type }}" name="{{ $field->inputName() }}" id="{{ $field->inputName() }}"
                                           value="{{ old($field->inputName()) }}"
                                           @required($field->is_required)
                                           class="w-full bg-bg-primary border border-accent/20 rounded-sm px-4 py-3 text-text-primary text-sm
                                                  focus:outline-none focus:border-accent/60 transition-colors min-h-[48px]">
                                @endif
                            @endif

                            @error($field->inputName())
                                <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                            @enderror

                            @if($field->description)
                                <p class="text-text-secondary text-xs mt-1">{{ $field->description }}</p>
                            @endif
                        </div>
                    @endforeach
                @endif

                <button type="submit" class="btn-primary w-full justify-center py-4 min-h-[48px]">
                    {{ trans('messages.actions.send') }}
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
