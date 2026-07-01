# Eldoria — Projet Azuriom Theme

## C'est quoi ce projet ?

Un thème payant pour [Azuriom](https://azuriom.com) (CMS pour serveurs de jeux), ciblant les serveurs **Minecraft**. Nom du thème : **Eldoria**. Univers RPG médiéval, vendu ~9€ sur market.azuriom.com.

**3 différenciateurs clés :**
1. Customizer visuel live front-end (palettes de couleurs, pas de code)
2. Animations immersives (particules, parallaxe, compteurs animés)
3. Mobile-first

## Spec & Plan

- **Spec design complète :** [`docs/superpowers/specs/2026-06-30-eldoria-theme-design.md`](docs/superpowers/specs/2026-06-30-eldoria-theme-design.md)
- **Plan d'implémentation :** [`docs/superpowers/plans/2026-06-30-eldoria-theme.md`](docs/superpowers/plans/2026-06-30-eldoria-theme.md)

Lire ces deux fichiers avant de toucher quoi que ce soit.

## Stack technique

| Technologie | Usage |
|---|---|
| Laravel + Blade | Templates (standard Azuriom) |
| Tailwind CSS v3 | Design system via CSS custom properties |
| Alpine.js v3 | Interactions légères (drawer, customizer, toggles) |
| GSAP 3 | Parallaxe hero, compteurs animés |
| AOS | Scroll reveal sur les sections |
| Vite | Bundler |
| Vanilla JS Canvas | Particles ambiantes |

## Structure du thème

Le thème vit dans le dossier `eldoria/` à la racine du repo. Voir la carte des fichiers complète dans le plan d'implémentation. Points clés :

```
eldoria/
├── theme.json           ← métadonnées Azuriom
├── assets/js/
│   ├── app.js           ← entrée Alpine + AOS
│   ├── customizer.js    ← Alpine component du drawer
│   ├── particles.js     ← canvas particles
│   └── animations.js    ← GSAP parallaxe + compteurs
├── assets/css/app.css   ← Tailwind + CSS custom properties
├── views/
│   ├── layouts/app.blade.php   ← layout principal
│   ├── partials/               ← navbar, footer, customizer, particles
│   ├── home.blade.php          ← page d'accueil
│   └── vendor/                 ← overrides plugins (shop/, vote/)
└── config/theme.json    ← settings exposés au customizer
```

## CSS Custom Properties (système de couleurs)

Toutes les couleurs passent par ces variables CSS :

| Variable | Défaut | Usage |
|---|---|---|
| `--color-bg-primary` | `#0F0D0A` | Fond principal |
| `--color-bg-secondary` | `#1A1612` | Cartes, sections |
| `--color-accent` | `#C9A84C` | Or patiné — accent principal |
| `--color-accent-secondary` | `#7B3F2E` | Bordeaux — accent secondaire |
| `--color-text-primary` | `#E8DCC8` | Texte (parchemin) |
| `--color-text-secondary` | `#8A7A62` | Sous-titres |

Le customizer modifie uniquement `--color-accent` et `--color-accent-secondary`. Les fonds et textes restent fixes.

## Palettes prédéfinies

| Nom | Accent | Secondaire |
|---|---|---|
| Eldoria (défaut) | `#C9A84C` | `#7B3F2E` |
| Forêt Sombre | `#4A7C59` | `#2D4A1E` |
| Abysses | `#3A6EA8` | `#1A3A5C` |
| Volcan | `#C0392B` | `#7D2B1A` |
| Givre | `#7EC8D8` | `#2A5A6E` |

## Règles importantes

- **Mobile-first** : CSS pour `< 640px` en premier, desktop via `min-width`
- **Particules et parallaxe désactivées sur mobile** (performance)
- **Toutes les animations** respectent `prefers-reduced-motion: reduce`
- **Le bouton Personnaliser** du customizer : rendu uniquement si `auth()->user()->isAdmin()`
- **Tailwind** : les couleurs Tailwind sont mappées sur les CSS vars (`bg-accent` = `var(--color-accent)`)
- Taille tactile minimale des boutons : **48px**

## Plugins supportés (v1)

1. **Shop** (priorité 1) — catégories, produit, panier
2. **Vote** (priorité 2) — liste sites, statut voté, top voteurs

Hors scope v1 : Whitelist, News, Maintenance, autres jeux.

> **Il n'existe pas de plugin "Forum" officiel chez Azuriom** (vérifié sur les dépôts GitHub officiels : Shop, Vote, Support, FAQ, Wiki, CloudflareSupport, DedipassPayment — pas de Forum). Le thème ne doit pas prétendre le supporter.

## Développement local

```bash
cd eldoria
npm install
npm run dev      # watch + hot reload
npm run build    # production
```

Copier `eldoria/` dans `resources/themes/` de ton installation Azuriom locale pour tester.

## Points vérifiés en conditions réelles (installation Azuriom locale + plugins)

- **Helper de config du thème** : `theme_config($key, $default)`, pas `theme_setting()`. Le fichier de defaults est `config.json` à la racine du thème (pas `config/theme.json`). Le formulaire admin est `config/config.blade.php` + `config/rules.php`, soumis en POST vers la route `admin.themes.config`.
- **Assets du thème** : Azuriom ne connaît pas le manifest Vite de l'app — les vues chargent les fichiers buildés directement via `theme_asset('dist/...')`, pas via la directive `@vite()`. `vite.config.js` doit donc sortir des noms de fichiers fixes (pas de hash).
- **Override des vues plugin** : le chemin doit être le miroir exact de la structure interne du plugin (`views/vendor/{id}/...`), pas une structure inventée. Pour Shop : `categories/index.blade.php`, `categories/show.blade.php`, `packages/show.blade.php`, `cart/index.blade.php` (pas de vue "checkout" séparée — le flux passe par `offers/select` puis `payments/pay`). Pour Vote : `index.blade.php` à la racine.
- **Routes réelles** : `shop.home` (catalogue), `shop.categories.show`, `shop.packages.show`, `shop.cart.index` — pas de `shop.packages.index`. `vote.home`, `vote.vote` (POST, pas un lien direct), `vote.verify-user`, `vote.done`.
- **Variables Vote** : le contrôleur passe `$sites`, `$votes` (classement des top voteurs, PAS une liste de sites déjà votés), `$goalEnabled`/`$goalTarget`/`$goalProgress`/`$goalPercentage` (pas `$monthlyVotes`/`$monthlyGoal`). Le statut voté/non-voté par site est calculé côté client en AJAX via `vote.verify-user`, pas côté serveur.
- **Stats homepage** (joueurs en ligne, membres) : pas de helpers globaux — passer par les modèles directement (`Azuriom\Models\Server::where('home_display', true)->get()->sum(fn($s) => $s->getOnlinePlayers())`, `Azuriom\Models\User::count()`).

## Contexte marché

- Prix : **9€** au lancement
- Plateforme : [market.azuriom.com](https://market.azuriom.com)
- Cible : petits serveurs Minecraft qui démarrent
- Angle : seul thème RPG médiéval de qualité à prix d'entrée sur le market
- Stratégie : volume + support réactif pour accumuler les avis rapidement
- Futur : support d'autres jeux (Hytale notamment) dans les versions suivantes
