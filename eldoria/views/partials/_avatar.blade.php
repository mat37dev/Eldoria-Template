{{--
    Avatar d'un joueur (tête Minecraft ou image uploadée), réutilisable partout où le
    thème affiche un avatar : navbar, profil, commentaires, listes de joueurs...

    Reproduit la logique de User::getAvatar() (avatar uploadé en priorité, sinon skin
    Minecraft) mais interroge minotar.net au lieu de mc-heads.net pour la partie skin :
    mc-heads.net s'est avéré renvoyer un skin par défaut pour de vrais comptes premium
    (vérifié en comparant le hash de sa réponse à celui du skin réel côté Mojang), alors
    que minotar.net renvoie le bon skin (hash identique au skin officiel) et un vrai 404
    quand le pseudo/UUID n'existe pas, plutôt qu'un faux positif silencieux.

    Props :
    - user (required) : le modèle User dont on affiche l'avatar
    - size (required) : taille en pixels (carrée)
    - class (optional) : classes CSS de l'<img>
    - alt (optional) : texte alternatif, par défaut le pseudo
--}}
@php
    $avatarMinecraftId = game()->id() === 'mc-offline'
        ? rawurlencode($user->name)
        : ($user->game_id ?? 'MHF_Steve');

    $avatarUrl = $user->avatar === null
        ? "https://minotar.net/avatar/{$avatarMinecraftId}/{$size}"
        : ($user->hasUploadedAvatar() ? $user->imageUrl() : str_replace('{size}', $size, $user->avatar));
@endphp
<img src="{{ $avatarUrl }}" alt="{{ $alt ?? $user->name }}" class="{{ $class ?? '' }}"
     onerror="this.onerror=null;this.src='https://minotar.net/avatar/MHF_Steve/{{ $size }}';">
