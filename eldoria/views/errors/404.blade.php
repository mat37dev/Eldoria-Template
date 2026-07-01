@extends('layouts.app')
@section('title', '404 — Page introuvable')
@section('content')
<div class="min-h-screen flex items-center justify-center text-center px-4">
    <div>
        <div class="font-display text-accent text-[8rem] font-black leading-none mb-4 opacity-20">404</div>
        <h1 class="font-display text-3xl text-text-primary mb-4">Cette page n'existe pas</h1>
        <p class="text-text-secondary mb-8">La page que tu cherches s'est perdue dans les brumes du royaume.</p>
        <a href="{{ route('home') }}" class="btn-primary">Retourner à l'accueil</a>
    </div>
</div>
@endsection
