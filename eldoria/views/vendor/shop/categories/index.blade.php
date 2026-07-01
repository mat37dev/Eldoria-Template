@extends('layouts.app')

@section('title', 'Boutique')

@section('content')
<div class="pt-24 pb-16">
    <div class="text-center py-16 px-4">
        <p class="text-accent text-xs font-display tracking-[0.4em] uppercase mb-2">✦ Boutique ✦</p>
        <h1 class="section-title">Soutiens le serveur</h1>
        <p class="section-subtitle">Obtiens des avantages exclusifs et aide-nous à grandir</p>
    </div>

    <div class="max-w-6xl mx-auto px-4 grid grid-cols-1 lg:grid-cols-4 gap-8">
        <div class="lg:col-span-1">
            @include('shop::categories._sidebar')
        </div>

        <div class="lg:col-span-3">
            <div class="card-eldoria p-6 text-text-secondary text-sm leading-relaxed">
                {{ $welcome }}
            </div>
        </div>
    </div>
</div>
@endsection
