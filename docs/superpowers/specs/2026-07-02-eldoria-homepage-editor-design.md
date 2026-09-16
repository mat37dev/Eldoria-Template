# Eldoria — Éditeur de disposition de la homepage (drag-and-drop) — Design Spec

**Date :** 2026-07-02
**Version :** 1.2 (post v1.1)
**Scope :** Éditeur en direct sur la homepage permettant à l'admin de réorganiser, masquer et (pour deux sections) éditer le texte des sections de l'accueil, inspiré du thème commercial "Deluxe".

---

## 1. Contexte & décisions déjà validées

Le thème Eldoria v1.1 (i18n, news, join-steps, staff, statut live, hero vidéo) est implémenté et en cours de revue sur PR (`worktree-v1.1-additions`). Ce document couvre un ajout supplémentaire sur la même branche : un éditeur de disposition inspiré du thème "Deluxe" (`local aldorya_site`), dont le mécanisme de sauvegarde a été vérifié : il utilise la **même route** `admin.themes.config` qu'Eldoria utilise déjà pour son customizer — aucune limitation d'architecture Azuriom, cette fonctionnalité est réalisable en pur thème (Blade + JS + config), sans code backend custom (les thèmes Azuriom ne peuvent pas déclarer de routes/contrôleurs propres — seul un plugin le pourrait — donc tout doit transiter par ce même endpoint générique).

Décisions validées avec le propriétaire du projet :
- **Hero fixe** en haut de la page, non réorganisable.
- **8 sections réorganisables et masquables** : `stats`, `join_steps`, `trailer`, `news`, `shop`, `vote`, `staff`, `discord`.
- **2 sections à texte éditable** (le titre/sous-titre remplace le texte i18n si renseigné) : `join_steps` (+ le texte des 3 étapes) et `trailer`. Les 6 autres gardent leurs titres traduits automatiquement (FR/EN) — seuls leur ordre et leur visibilité sont modifiables, car leur contenu réel (produits, votes, articles, staff) n'est pas du texte libre.
- **Pas de blocs custom** créés à la volée — uniquement réorganiser/masquer/éditer le catalogue existant.
- **Nouvelle dépendance JS** : SortableJS (~40kb) pour le glisser-déposer — première exception à la règle "zéro dépendance externe" tenue jusqu'ici sur ce projet, jugée justifiée pour cette fonctionnalité précise.
- **Approche retenue** : édition **en direct sur la page** (mode "réorganisation" activé depuis le drawer customizer existant), pas une page admin séparée — cohérent avec le fonctionnement déjà en place du customizer (live preview + sauvegarde AJAX).

---

## 2. Modèle de données

### Nouvelle clé de config : `home_layout`

Une chaîne JSON stockée comme n'importe quel autre champ de config (`config.json`, `config/rules.php`, sauvegardée par `admin.themes.config`). Structure : tableau ordonné des 8 sections.

```json
[
  { "key": "stats", "visible": true },
  { "key": "join_steps", "visible": true, "title": "", "subtitle": "", "steps": [
      { "title": "", "text": "" },
      { "title": "", "text": "" },
      { "title": "", "text": "" }
  ] },
  { "key": "trailer", "visible": true, "title": "", "subtitle": "" },
  { "key": "news", "visible": true },
  { "key": "shop", "visible": true },
  { "key": "vote", "visible": true },
  { "key": "staff", "visible": true },
  { "key": "discord", "visible": true }
]
```

- **L'ordre du tableau = l'ordre d'affichage.**
- `visible: false` masque la section (remplace les toggles `show_section_shop`/`show_section_vote` actuels — voir §6 migration).
- `title`/`subtitle`/`steps` : uniquement présents pour `join_steps` et `trailer`. Une valeur **vide** (`""`) signifie "pas de surcharge" → la section affiche le texte i18n par défaut. Une valeur non-vide remplace le texte traduit. Ce choix (chaîne vide = pas de surcharge, testé avec `?: __(...)` et non `?? __(...)`) permet à l'admin de revenir au texte par défaut en vidant simplement le champ.

### Valeur par défaut (fraîche installation)

Si `home_layout` est absent, vide, ou JSON invalide, le thème utilise un tableau de repli codé en dur reproduisant l'ordre et la visibilité **actuels** du thème (celui livré en v1.1) :
```php
[
    ['key' => 'stats', 'visible' => true],
    ['key' => 'join_steps', 'visible' => true, 'title' => '', 'subtitle' => '', 'steps' => [['title' => '', 'text' => ''], ['title' => '', 'text' => ''], ['title' => '', 'text' => '']]],
    ['key' => 'trailer', 'visible' => true, 'title' => '', 'subtitle' => ''],
    ['key' => 'news', 'visible' => true],
    ['key' => 'shop', 'visible' => true],
    ['key' => 'vote', 'visible' => true],
    ['key' => 'staff', 'visible' => true],
    ['key' => 'discord', 'visible' => true],
]
```
Une installation neuve du thème se comporte donc **exactement comme la v1.1** tant que l'admin n'a pas touché à la disposition.

### Repli en cas de JSON invalide

`home.blade.php` décode `home_layout` avec `json_decode(..., true)`. Si le résultat est `null` (JSON invalide) OU si le tableau décodé ne contient pas exactement les 8 clés attendues (`stats`, `join_steps`, `trailer`, `news`, `shop`, `vote`, `staff`, `discord`, une fois et une seule), on retombe silencieusement sur le tableau par défaut ci-dessus. Aucune erreur n'est levée côté utilisateur — c'est un repli tolérant, pas une validation bloquante (la validation stricte se fait à la sauvegarde, voir §5).

### Migration des toggles existants

`show_section_shop` et `show_section_vote` (v1.1) sont **remplacés** par le `visible` de leurs entrées respectives dans `home_layout`. Ces deux anciennes clés de config restent dans `config.json`/`rules.php` pour la compatibilité ascendante (une install v1.1 qui upgrade ne doit pas planter), mais ne sont plus lues par `home.blade.php` — uniquement `home_layout` fait foi désormais. Elles pourront être retirées dans une version ultérieure.

---

## 3. Découpage en partials

Chacune des 8 sections réorganisables est extraite dans son propre fichier `eldoria/views/partials/home/{key}.blade.php` :
- `partials/home/stats.blade.php`
- `partials/home/join-steps.blade.php`
- `partials/home/trailer.blade.php`
- `partials/home/news.blade.php`
- `partials/home/shop.blade.php`
- `partials/home/vote.blade.php`
- `partials/home/staff.blade.php`
- `partials/home/discord.blade.php`

Chaque partial reçoit une variable `$sectionData` (l'entrée correspondante du tableau `home_layout`, déjà résolu avec repli). Chaque partial garde exactement le HTML/logique déjà existant en v1.1 (aucune régression visuelle), à trois différences près :
1. La balise `<section>` racine gagne un attribut `data-section-key="{{ $sectionData['key'] }}"` (permet au JS de lire l'ordre/visibilité courants depuis le DOM).
2. Pour `join-steps.blade.php` et `trailer.blade.php` : les titres/sous-titres utilisent `{{ $sectionData['title'] ?: __('theme::theme.home.xxx_title') }}` au lieu de `{{ __('theme::theme.home.xxx_title') }}` directement (idem pour `subtitle`, et pour les 3 étapes de `join-steps`).
3. La classe `hidden` conditionnelle (déjà utilisée en v1.1 pour `show_section_shop`/`show_section_vote`/`trailer`) est pilotée par `$sectionData['visible']` au lieu des anciennes clés de config.

`home.blade.php` devient : Hero (inchangé, fixe) suivi d'une boucle :
```blade
@foreach($homeLayout as $sectionData)
    @include('partials.home.' . str_replace('_', '-', $sectionData['key']), ['sectionData' => $sectionData])
@endforeach
```
(la conversion `_` → `-` gère la différence entre la clé `join_steps` et le nom de fichier `join-steps.blade.php`, cohérent avec la convention de nommage de fichiers déjà utilisée dans le thème — ex: `categories/_sidebar.blade.php`).

Chaque partial gère lui-même sa visibilité interne (`class="... {{ $sectionData['visible'] ? '' : 'hidden' }}"`) plutôt que d'être conditionnellement inclus, pour que le mode réorganisation (§4) puisse basculer la visibilité en JS sans recharger la page.

**Coexistence avec les gardes déjà existantes (v1.1) :** `news.blade.php` garde son `@if($latestPosts->isNotEmpty())` (rien à afficher si aucun article publié), et `shop.blade.php`/`vote.blade.php` gardent leurs `@if(class_exists(...))` (rien à afficher si le plugin n'est pas installé). Le nouveau `visible` de `home_layout` s'ajoute à ces conditions existantes, il ne les remplace pas — une section reste invisible si son plugin est absent ou si elle n'a pas de contenu, **même si** `visible: true`. Seules `join_steps`, `trailer`, `stats`, `staff`, `discord` n'ont pas de garde de contenu préexistante et dépendent uniquement de `visible`.

**Variable partagée `$trailerId` :** le calcul de `$trailerId` (extraction de l'ID YouTube depuis `trailer_url`, ajouté en Task 20 v1.1) reste en tête de `home.blade.php` — il est utilisé à la fois par le Hero (fond vidéo, §2 de la spec v1.1) et par `partials/home/trailer.blade.php` (section Trailer elle-même). Ce calcul n'est pas déplacé dans le partial ; il continue d'être calculé une seule fois au niveau du fichier parent et reste disponible dans le partial via la portée de variable Blade standard (les `@include` héritent des variables du scope appelant).

---

## 4. Mode réorganisation (admin uniquement)

### Activation

Un 3ᵉ onglet **"Disposition"** est ajouté au drawer customizer existant (`customizer.blade.php`), à côté de "Couleurs" et "Contenu". Cliquer sur cet onglet active un mode réorganisation sur la page derrière le drawer (le drawer reste ouvert à côté).

### État actif

Quand l'onglet "Disposition" est actif (`activeTab === 'layout'`) :
- Une classe `reorder-mode` est ajoutée sur `<body>` par un `x-effect` (ou `$watch`) du composant Alpine `customizer` dès que `activeTab === 'layout'` devient vrai, retirée sinon. `<body>` est choisi comme point d'ancrage (plutôt qu'un nouveau conteneur autour des 8 sections) car il ne nécessite aucune restructuration du DOM de `home.blade.php` — `document.body.classList.toggle('reorder-mode', this.activeTab === 'layout')`.
- Chaque section `[data-section-key]` affiche, en overlay (coin supérieur droit, `position: absolute` sur un conteneur `position: relative` ajouté à la section), une petite barre d'outils : une poignée de glisser-déposer (icône ⋮⋮), un bouton œil (masquer/afficher), et — uniquement pour `join_steps` et `trailer` — un bouton crayon.
- Cette barre d'outils est cachée par défaut (`display: none`) et n'apparaît que si `body.reorder-mode [data-section-key]` matche en CSS — donc invisible pour les visiteurs normaux et même pour l'admin hors mode réorganisation.
- SortableJS est initialisé sur le conteneur des 8 sections **uniquement quand le mode est activé** (pas au chargement de la page, pour ne jamais gêner un visiteur/admin qui ne l'utilise pas).

### Réorganisation

Glisser une section dans la liste change son ordre dans le DOM (SortableJS le fait nativement). Aucune sauvegarde serveur tant que l'admin n'a pas cliqué "Enregistrer".

### Masquer/afficher

Cliquer sur l'icône œil d'une section bascule sa classe `hidden` immédiatement (retour visuel instantané, cohérent avec le reste du live-preview du customizer) et met à jour un indicateur visuel sur l'icône elle-même (œil barré si masqué).

### Édition de texte (join_steps et trailer)

Cliquer sur le crayon d'une de ces deux sections fait basculer le drawer customizer de l'onglet "Disposition" vers un sous-panneau d'édition dédié à cette section, avec :
- **Trailer** : champ titre, champ sous-titre (2 champs).
- **Comment nous rejoindre** : champ titre, champ sous-titre, puis 3 groupes de champs titre+texte (une étape = 2 champs), soit 8 champs au total.

Chaque champ est pré-rempli avec la valeur courante (surcharge existante, ou vide si aucune surcharge). Un bouton "Retour" ramène à la liste "Disposition". Ces valeurs sont conservées en mémoire (état Alpine) et ne sont écrites sur la page (via `liveSlogan()`-style live preview) qu'au moment de quitter le sous-panneau ou en continu à la saisie (`@input`), pour un retour visuel immédiat cohérent avec le reste du drawer.

### Sauvegarde

Le bouton "Enregistrer" du drawer (déjà existant, inchangé dans son emplacement/comportement) est étendu : en plus des champs déjà envoyés (couleurs, slogan, etc.), `save()` construit désormais l'objet `home_layout` à partir :
1. De l'ordre courant des éléments `[data-section-key]` dans le DOM (lu via `document.querySelectorAll`).
2. De l'état visible/masqué de chacun (présence de la classe `hidden`).
3. Des surcharges de texte en mémoire pour `join_steps`/`trailer` (état Alpine, initialisé au chargement depuis `theme_config('home_layout')` côté serveur, modifié par les sous-panneaux d'édition).

Cet objet est sérialisé en JSON (`JSON.stringify`) et ajouté au `FormData` existant sous la clé `home_layout`, envoyé par le même `fetch()` déjà en place. Le message d'erreur détaillé (déjà livré en v1.1) s'applique aussi à ce nouveau champ si sa validation échoue côté serveur.

### Annuler

Le bouton "Annuler" existant (qui recharge la page) annule aussi tout réordonnancement/masquage/édition de texte non sauvegardé, puisque rien n'a été persisté — comportement déjà cohérent, aucun changement nécessaire.

---

## 5. Validation serveur

`config/rules.php` reçoit une nouvelle règle :
```php
'home_layout' => ['nullable', 'json', 'max:5000'],
```
La règle Laravel `json` vérifie que la chaîne est un JSON valide (mais pas sa structure interne — la structure exacte, ex. présence des 8 clés attendues, est vérifiée côté lecture dans `home.blade.php` avec repli tolérant, pas côté validation stricte, pour éviter qu'une future section ajoutée par une mise à jour du thème casse la sauvegarde d'une config existante).

---

## 6. Nouvelle dépendance : SortableJS

Ajout de `sortablejs` (^1.15) dans `eldoria/package.json`. Importé uniquement dans `customizer.js` (`import Sortable from 'sortablejs'`) — le code qui l'utilise n'est instancié que si le drawer customizer existe sur la page (admin uniquement), donc aucun visiteur normal ne charge ce code inutilement au runtime même s'il fait partie du même bundle `app.js` (le bundle JS du thème est unique, sans code-splitting, comme le reste du thème depuis la v1.0).

---

## 7. Notes transverses

- Toutes les nouvelles chaînes d'interface (libellés de l'onglet "Disposition", boutons de la barre d'outils, sous-panneaux d'édition) passent par `theme::theme.*`, cohérent avec le système i18n de la v1.1.
- Contrainte 48px : les boutons de la barre d'outils overlay (poignée, œil, crayon) respectent la taille tactile minimale du thème.
- `prefers-reduced-motion` : SortableJS anime le déplacement des éléments pendant le glisser — cette animation est déjà un retour direct au geste de l'utilisateur (pas une animation automatique/ambiante), donc **non concernée** par la contrainte `prefers-reduced-motion` du thème (qui vise les animations automatiques, pas les manipulations directes).
- Aucune limite specifique sur le nombre de fois qu'un admin peut réorganiser — pas de historique/undo au-delà du bouton "Annuler" (recharge la page).
