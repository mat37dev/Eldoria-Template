@extends('layouts.app')

@section('title', $page->title)
@section('description', $page->description)

@section('content')
<div class="pt-24 pb-16 px-4">
    <div class="text-center py-16">
        <p class="text-accent text-xs font-display tracking-[0.4em] uppercase mb-2">✦ {{ site_name() }} ✦</p>
        <h1 class="section-title">{{ $page->title }}</h1>
    </div>

    <div class="max-w-3xl mx-auto">
        <div class="card-eldoria p-8 prose prose-invert max-w-none
                    prose-headings:font-display prose-headings:text-accent
                    prose-a:text-accent prose-a:no-underline hover:prose-a:underline
                    prose-strong:text-text-primary
                    text-text-secondary text-sm leading-relaxed">
            {!! $page->content !!}
        </div>
    </div>
</div>
@endsection
