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
        <p class="text-accent text-xs font-display tracking-[0.4em] uppercase mb-2">✦ {{ __('theme::theme.support.eyebrow') }} ✦</p>
        <h1 class="section-title">{{ $category->name }}</h1>
    </div>

    <div class="max-w-2xl mx-auto">
        <div class="card-eldoria p-8">
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
