@extends('layouts.app')
@section('title', '403 — Accès refusé')
@section('content')
<div class="min-h-screen flex items-center justify-center text-center px-4">
    <div>
        <div class="font-display text-accent text-[8rem] font-black leading-none mb-4 opacity-20">403</div>
        <h1 class="font-display text-3xl text-text-primary mb-4">Accès refusé</h1>
        <p class="text-text-secondary mb-8">Tu n'as pas les permissions pour accéder à cette zone.</p>
        <a href="{{ route('home') }}" class="btn-primary">Retourner à l'accueil</a>
    </div>
</div>
@endsection
