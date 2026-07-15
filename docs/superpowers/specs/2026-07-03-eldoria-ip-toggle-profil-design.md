# Eldoria — Bouton copie IP, toggle réutilisable, admin réorganisé, refonte profil — Design Spec

**Date :** 2026-07-03
**Scope :** Quatre ajouts indépendants sur la même branche (`worktree-v1.1-additions`) : (1) un bouton copie-IP avec easter egg façon combo, (2) un composant toggle réutilisable pour le customizer, (3) une réorganisation visuelle de la page de config Azuriom, (4) une refonte de la page profil avec avatar navbar et visualiseur de skin 3D.

---

## 1. Bouton copie IP + easter egg combo

### Nouveau champ de config : `server_ip_display`

Chaîne libre (ex: `play.eldoria.fr`), ajoutée à `config.json`/`config/rules.php` (`nullable|string|max:255`), exposée dans **les deux** formulaires existants (customizer drawer, onglet Contenu ; page admin Azuriom, nouvelle section "Serveur").

### Unification des deux boutons

Le bouton "Rejoindre" existant (Hero) et le nouveau bouton copient désormais la **même valeur** :
```php
$displayIp = theme_config('server_ip_display', '') ?: ($homeServer ? $homeServer->fullAddress() : '');
```
Si l'admin ne renseigne jamais le nouveau champ, rien ne change (repli sur l'adresse technique du serveur Azuriom `home_display`, comportement actuel). Le statut en ligne/hors-ligne et le nombre de joueurs restent calculés à partir du vrai serveur Azuriom, indépendamment de ce champ texte.

### Nouveau bouton (Hero)

Positionné au-dessus du contenu existant du Hero (avant l'eyebrow "✦ SERVEUR MINECRAFT ✦"), sous forme de pastille bien visible affichant l'IP elle-même avec une icône de copie, avec une légère animation d'attention (lueur/pulsation douce, désactivée si `prefers-reduced-motion`). Ne s'affiche pas si `$displayIp` est vide (rien à copier).

### Combo au clic

Un compteur de clics en mémoire JS (pas de state serveur) :
- Clic → `navigator.clipboard.writeText($displayIp)`, incrémente le compteur si le clic précédent date de moins de 3 secondes, sinon le réinitialise à 1.
- Une info-bulle apparaît au-dessus du bouton (fondu, ~1.5-2s puis disparaît) avec un texte dépendant du compteur :

| Compteur | Texte |
|---|---|
| 1 | IP copiée ! |
| 2 | Double copie ! |
| 3 | Triple copie ! |
| 4 | Quadra copie ! |
| 5 | PENTA COPIE ! *(mise en avant : plus grand, doré)* |
| 6+ | Cycle : Domination ! → Massacre ! → Légendaire ! *(puis boucle)* |

Toutes les chaînes passent par `theme::theme.home.ip_copy_*`. L'animation d'apparition de l'info-bulle est désactivée (repli sur un affichage instantané) si `prefers-reduced-motion: reduce`.

### Implémentation

Nouveau fichier `assets/js/ip-copy.js` (vanilla JS, pas Alpine — cohérent avec `vote.js`/`server-status.js`), importé dans `app.js` (poids négligeable, bundle site-wide comme le reste). Live preview côté customizer : taper dans le champ met à jour le `data-*` attribute lu par ce script, sans recharger la page.

---

## 2. Composant toggle réutilisable

Nouveau partial `eldoria/views/partials/_toggle-switch.blade.php`, paramétré par `$model` (nom de la propriété Alpine liée), `$label`, et `$onChange` (optionnel, expression Alpine appelée au changement) :
```blade
@include('partials._toggle-switch', ['model' => 'heroVideoEnabled', 'label' => __('theme::theme.customizer.hero_video_toggle'), 'onChange' => 'liveHeroVideo()'])
```
Rendu : une piste arrondie (`w-11 h-6`, fond `--color-bg-primary`, bordure `--color-accent`/30%) avec un curseur circulaire qui se déplace à droite et devient doré (`--color-accent`) quand actif, gris et à gauche sinon — piloté par `:class` Alpine sur l'état du modèle (pas de trucage CSS `peer-checked`, l'état Alpine suffit puisqu'il est déjà réactif). Remplace la case actuelle "Utiliser le trailer en fond du hero" dans le customizer. Conçu pour être réutilisé par toute future case à cocher du customizer.

---

## 3. Réorganisation de la page de config Azuriom (back-office)

`eldoria/config/config.blade.php` garde son style Bootstrap natif (cohérence avec le reste du panneau admin Azuriom — pas de reskin façon Eldoria ici). Changements purement organisationnels :
- Un `<hr class="my-4">` entre chaque groupe de champs (`Couleurs`, `Contenu`, nouvelle section `Serveur`, `Équipe / Staff`, `Médias & communauté`, `Réseaux sociaux`).
- Nouvelle section **Serveur** (champ `server_ip_display`), insérée juste après `Contenu` et avant `Équipe / Staff`.
- Espacement (`mb-3`/`mb-4`) harmonisé sur l'ensemble du formulaire.

Aucun champ existant n'est renommé ni déplacé de section — uniquement l'ajout de séparateurs, d'espacement cohérent, et de la nouvelle section Serveur.

---

## 4. Refonte du profil

### Navbar

Ajout d'un avatar (tête de skin, `auth()->user()->getAvatar(32)` — méthode native Azuriom, gère déjà le repli Steve si aucun compte Minecraft lié) immédiatement avant le pseudo, dans les deux blocs navbar (desktop et mobile).

### Page profil — informations ajoutées

En plus de pseudo/email/date d'inscription (déjà présents) :
- **Rôle** : badge coloré (`$user->role->name`, couleur `$user->role->color` si disponible côté Azuriom, sinon couleur accent du thème par défaut).
- **Dernière connexion** : `$user->last_login_at`, formatée `d/m/Y à H:i` (ou "Jamais" si `null`).
- **Solde en jeu** : `format_money($user->money)` (même helper que la boutique).
- **Statut email vérifié** : badge d'alerte si `email_verified_at` est `null` ("Email non confirmé"), rien d'affiché si déjà vérifié (pas besoin de célébrer l'état normal).

Mise en page retravaillée en cartes (`card-eldoria`, cohérent avec le reste du thème) plutôt que la liste actuelle.

### Visualiseur de skin 3D

Nouveau bloc dédié sur la page profil : un `<canvas>` avec le skin de l'utilisateur en rotation automatique (zoom désactivé, cohérent avec l'usage de référence chez Deluxe), respectant `prefers-reduced-motion` (rotation automatique désactivée, pose statique si l'utilisateur préfère moins de mouvement).

**Texture** : `https://mc-heads.net/skin/{uuid}`, où `{uuid}` = `$user->game_id` si renseigné, sinon l'UUID Steve déjà utilisé par le cœur d'Azuriom comme repli (`c06f8906-4c8a-4911-9c29-ea1dbd1aab82`) — cohérence totale avec le comportement déjà en place pour les avatars 2D.

**Portée** : page profil uniquement (pas de remplacement des avatars 2D de la section Staff de l'accueil).

---

## 5. Nouvelle dépendance : skinview3d

Ajout de `skinview3d` (npm, pas de fichier vendu à la main) dans `eldoria/package.json`. **Chargé uniquement sur la page profil**, via un second point d'entrée Vite dédié :
```js
// vite.config.js — rollupOptions.input
profile: resolve(__dirname, 'assets/js/profile.js'),
```
qui produit `assets/dist/profile.js`, inclus via `<script src="{{ theme_asset('dist/profile.js') }}" defer></script>` uniquement dans `eldoria/views/profile/index.blade.php` — jamais chargé sur les autres pages du site, cohérent avec le principe déjà en place d'un bundle unique par usage plutôt que du code-splitting dynamique.

Le thème livré sur le market contient déjà `assets/dist/profile.js` compilé — l'acheteur du thème n'exécute jamais `npm`, il installe le thème depuis l'admin Azuriom comme n'importe quel thème et ne touche qu'aux formulaires de configuration.

---

## 6. Notes transverses

- Toutes les nouvelles chaînes d'interface (bouton IP, combo, badges profil) passent par `theme::theme.*` (FR/EN).
- Contrainte 48px : le nouveau bouton IP et le toggle réutilisable respectent la taille tactile minimale.
- `prefers-reduced-motion` : l'animation de la pastille IP et la rotation automatique du skin 3D sont toutes deux désactivées/simplifiées dans ce mode, conformément à la contrainte globale du thème.
- Mobile-first : le bloc skin 3D et les cartes d'information du profil s'empilent verticalement sur mobile (`< 640px` en premier, `sm:` pour le layout desktop en grille).
- Aucune limite de compteur de combo — le cycle Domination/Massacre/Légendaire se répète indéfiniment au-delà de 6.
