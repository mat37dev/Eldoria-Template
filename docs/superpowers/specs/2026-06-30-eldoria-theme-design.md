# Eldoria — Azuriom Theme Design Spec

**Date :** 2026-06-30
**Version :** 1.0
**Scope :** v1 MVP — Approche A

---

## 1. Contexte & Objectifs

### Cible
Serveurs Minecraft (vanilla, survival, RPG, modded) débutants à établis. Priorité aux créateurs qui lancent un premier serveur et cherchent un thème qualitatif à prix accessible.

### Positionnement market
- **Prix :** 5–10€ sur market.azuriom.com
- **Différenciateurs :** customizer visuel live, animations immersives, mobile-first
- **Angle :** le seul thème RPG médiéval de qualité à prix d'entrée sur le market Azuriom

### Plugins supportés (v1, par ordre de priorité)
1. **Shop** — pages catégories, produit, panier, checkout
2. **Vote** — liste des sites, statut voté/non-voté, top voteurs
3. **Forum** — catégories, liste sujets, page discussion
4. Pages standards Azuriom (auth, profil, erreurs)

---

## 2. Identité Visuelle

### Nom
**Eldoria** — nom fantasy court, prononçable internationalement, évoque un royaume médiéval.

### Palette par défaut
| Variable CSS | Valeur | Usage |
|---|---|---|
| `--color-bg-primary` | `#0F0D0A` | Fond principal |
| `--color-bg-secondary` | `#1A1612` | Cartes, sections alternées |
| `--color-accent` | `#C9A84C` | Or patiné — accent principal |
| `--color-accent-secondary` | `#7B3F2E` | Bordeaux — accent secondaire |
| `--color-text-primary` | `#E8DCC8` | Texte principal (parchemin) |
| `--color-text-secondary` | `#8A7A62` | Sous-titres, métadonnées |

### Palettes prédéfinies (customizer)
| Nom | Accent principal | Accent secondaire | Ambiance |
|---|---|---|---|
| Eldoria (défaut) | `#C9A84C` | `#7B3F2E` | Or médiéval |
| Forêt Sombre | `#4A7C59` | `#2D4A1E` | Vert nature |
| Abysses | `#3A6EA8` | `#1A3A5C` | Bleu nuit |
| Volcan | `#C0392B` | `#7D2B1A` | Rouge feu |
| Givre | `#7EC8D8` | `#2A5A6E` | Bleu glacier |

### Typographies (Google Fonts)
- **Titres :** `Cinzel` — serif élégant, style gravure médiévale
- **Corps :** `Inter` — moderne, haute lisibilité, bon contraste avec Cinzel

---

## 3. Structure des Pages

### 3.1 Page d'accueil

**Section 1 — Hero plein écran**
- Image ou vidéo de fond configurable (upload ou URL via customizer)
- Overlay sombre + grain subtil pour lisibilité
- Titre du serveur (configurable), slogan (configurable)
- Bouton "Rejoindre" avec IP copiable au clic
- Effet parallaxe sur le fond au scroll (désactivé mobile)
- Particules ambiantes selon la palette active (désactivées mobile)

**Section 2 — Bande de stats live**
- Joueurs en ligne (API Azuriom)
- Total votes du mois
- Membres enregistrés
- Compteurs animés (0 → valeur réelle) à l'apparition dans le viewport

**Section 3 — Aperçu Shop**
- 3–4 produits mis en avant (configurables dans le panel admin Azuriom, pas dans le customizer front-end)
- Cards stylisées avec image, nom, prix, bouton achat
- Lien vers la boutique complète

**Section 4 — Vote (style "quêtes")**
- Liste des sites de vote présentés comme des quêtes à accomplir
- Indicateur visuel voté/non-voté (icône parchemin coché)
- Lien direct vers chaque site

**Section 5 — Communauté / Forum**
- 3 derniers posts du forum
- Bouton d'inscription mis en avant

**Footer**
- Logo, liens de navigation principaux
- IP du serveur
- Réseaux sociaux (configurables)
- Copyright

### 3.2 Pages Shop (plugin Azuriom Shop)
- **Catégories** : grille de cards filtrables par catégorie, prix affiché, image
- **Produit** : grand visuel, description complète, bouton achat stylisé
- **Panier / Checkout** : design propre intégré à la palette du thème

### 3.3 Page Vote (plugin Azuriom Vote)
- Liste de tous les sites de vote
- Statut en temps réel (voté / pas encore voté)
- Barre de progression animée "streak de votes"
- Classement top voteurs du mois

### 3.4 Pages Forum (plugin Azuriom Forum)
- **Liste des catégories** : icônes, description, dernier post
- **Liste des sujets** : titre, auteur, date, nombre de réponses
- **Page discussion** : posts paginés, éditeur de réponse, réactions

### 3.5 Pages standards
- Login, Register : formulaires centrés, background hero simplifié
- Profil utilisateur : avatar, stats, historique d'achats, votes
- Pages d'erreur (404, 403, 500) : design immersif avec message thématique

---

## 4. Customizer Visuel

### Accès
Bouton flottant "Personnaliser" visible **uniquement pour les administrateurs** sur le front-end du site (non rendu pour les visiteurs).

### Interface
Drawer latéral glissant (côté droit) qui s'ouvre sans recharger la page. Sur mobile, s'ouvre depuis le bas de l'écran.

### Options exposées

**Onglet Couleurs**
- 5 palettes prédéfinies en grille cliquable avec preview
- Picker couleur libre pour l'accent principal et secondaire
- Preview en temps réel via injection de CSS custom properties dans un `<style>` tag dynamique

**Onglet Contenu**
- Slogan du hero (texte libre)
- Image de fond du hero (upload ou URL)
- Toggle activation/désactivation de chaque section homepage

**Sauvegarde**
- Bouton "Enregistrer" — envoi AJAX vers le système de settings de thème Azuriom
- Bouton "Annuler" — reset au dernier état sauvegardé
- Feedback visuel (toast notification) à la sauvegarde

### Technique
- Les couleurs sont stockées en CSS custom properties (`--color-accent`, `--color-accent-secondary`, etc.)
- Le customizer écrit ces variables en JS pour le preview live
- Persistance via l'API settings d'Azuriom (fichier `config/theme.json`)
- Aucun besoin de modifier des fichiers pour l'utilisateur final

---

## 5. Animations & Micro-interactions

### Niveau B — Base

| Élément | Animation | Déclencheur |
|---|---|---|
| Sections homepage | Fade-in + montée (translateY 20px → 0) | Entrée dans le viewport |
| Hero background | Parallaxe (vitesse 0.5x) | Scroll |
| Particules ambiantes | Flottement aléatoire | Continu, en arrière-plan |
| Cards shop/forum | Élévation + lueur accent au survol | Hover |

### Éléments Niveau C ciblés

| Élément | Animation | Impact |
|---|---|---|
| Compteurs stats | Incrémentation 0 → valeur réelle (500ms) | Fort — visuel immédiat |
| Bouton "Rejoindre" | Pulse lumineux continu (accent color) | Attire l'œil sans agresser |
| Barre vote | Remplissage progressif à l'apparition | Gamification visuelle |

### Bibliothèques
- **AOS** (Animate On Scroll) pour les scroll reveals — légère, ~2kb gzipped
- **GSAP** pour les animations complexes (compteurs, parallaxe) — tree-shakeable
- Particules : implémentation vanilla JS canvas, pas de bibliothèque tierce

### Accessibilité
- Toutes les animations respectent `prefers-reduced-motion: reduce`
- Particules désactivées sur mobile
- Parallaxe désactivée sur mobile (problèmes de performance iOS Safari)

---

## 6. Expérience Mobile

### Stratégie
**Mobile-first** — CSS écrit pour mobile en premier, desktop via media queries `min-width`.

### Breakpoints
| Nom | Largeur |
|---|---|
| Mobile | < 640px |
| Tablette | 640px – 1024px |
| Desktop | > 1024px |

### Adaptations par composant
- **Hero** : texte recentré, bouton et IP avec taille tactile min 48px, image recadrée sur portrait
- **Stats** : colonne unique sur mobile, ligne sur desktop
- **Cards Shop** : 1 colonne mobile, 2 tablette, 3–4 desktop
- **Navigation** : burger menu + drawer latéral animé, logo centré
- **Customizer** : drawer depuis le bas de l'écran sur mobile, palettes en grille 3×2
- **Forum** : titres tronqués (1 ligne), auteur + date sur ligne compacte

### Ce qui est désactivé sur mobile
- Particules ambiantes
- Parallaxe hero
- Hover effects (remplacés par focus/active states)

---

## 7. Architecture Technique

### Stack
- **Laravel + Blade** (standard Azuriom)
- **Tailwind CSS** — utilitaires + configuration des tokens de design
- **Alpine.js** — interactions légères (drawer, toggles, customizer)
- **GSAP + AOS** — animations
- **Vite** — bundler (standard Azuriom récent)

### Structure des fichiers thème
```
eldoria/
├── assets/
│   ├── css/
│   │   └── app.css          # Tailwind + custom properties
│   ├── js/
│   │   ├── app.js           # Entrée principale
│   │   ├── customizer.js    # Logic du customizer
│   │   ├── particles.js     # Canvas particles
│   │   └── animations.js    # GSAP + AOS init
│   └── images/
│       └── hero-default.jpg # Image hero par défaut
├── views/
│   ├── layouts/
│   │   └── app.blade.php    # Layout principal
│   ├── partials/
│   │   ├── navbar.blade.php
│   │   ├── footer.blade.php
│   │   ├── customizer.blade.php
│   │   └── particles.blade.php
│   ├── home/
│   │   └── index.blade.php  # Page d'accueil
│   ├── shop/                # Vues plugin Shop
│   ├── vote/                # Vues plugin Vote
│   └── forum/               # Vues plugin Forum
├── config/
│   └── theme.json           # Déclaration settings customizer
└── theme.json               # Métadonnées du thème (nom, version, auteur)
```

### Système de settings (customizer)
Les settings sont déclarés dans `config/theme.json` au format Azuriom standard. Le customizer écrit et lit ces valeurs via les routes settings du CMS. Les CSS custom properties sont injectées dans le `<head>` via Blade à partir des settings sauvegardés.

---

## 8. Mise sur le Market Azuriom

### Prérequis
1. Créer un compte sur market.azuriom.com
2. Respecter la structure de fichiers officielle Azuriom
3. Tester sur une installation Azuriom fraîche avec les plugins Shop, Vote, Forum installés
4. Préparer des screenshots/preview de qualité (fort impact sur les ventes)
5. Soumettre pour review — l'équipe Azuriom valide qualité et sécurité

### Stratégie de lancement
- **Screenshots soignés** : desktop + mobile, toutes les palettes, pages clés
- **Description claire** : lister les palettes, les plugins supportés, la feature customizer
- **Prix d'attaque** : 9€ au lancement pour accumuler rapidement les premiers avis
- **Support actif** : répondre aux questions dans les 24h les premières semaines (booste le ranking)

---

## 9. Hors scope (v1)

Les éléments suivants sont explicitement exclus de la v1 et pourront être ajoutés dans des updates :

- Support d'autres plugins (Whitelist, News, Maintenance...)
- Curseur personnalisé (élément C)
- Hover 3D sur les cards (élément C)
- Animations de transition entre pages (page transitions)
- Support d'autres jeux (FiveM, Hytale...)
- Version multilingue du thème lui-même (les traductions Azuriom restent fonctionnelles)
