# Eldoria — Support du plugin Wiki — Design Spec

**Date :** 2026-07-07
**Scope :** Habillage du plugin officiel Azuriom Wiki (github.com/Azuriom/Plugin-Wiki) aux couleurs du thème, + contenu de démo local pour la fiche market.

---

## 1. Contexte

Recherche menée sur le code source réel (cloné dans `local/azuriom-plugin-wiki`) :
- Modèle `Category` (table `wiki_categories`) : `icon` (classe Bootstrap Icons, ex `bi bi-book` — **le thème n'a aucune police d'icônes chargée**, cette valeur ne sera pas rendue telle quelle, voir §5), `name`, `slug`, `roles` (permissions), `position`, `parent_id` (catégories imbriquées un niveau), `is_enabled`.
- Modèle `Page` (table `wiki_pages`) : `title`, `slug`, `content` (HTML libre, `{!! !!}`), `category_id`, `position`. Pas de flag "activé" propre à la page — visible si sa catégorie l'est.
- 4 routes réelles : `wiki.index` (grille de catégories racine), `wiki.show` (redirige vers la première page de la catégorie), `wiki.pages.show` (affiche une page, avec `$page->category->categories` [sous-catégories] et `$page->category->pages` [pages sœurs] déjà chargées), `wiki.search` (recherche plein texte via le trait `Searchable`, résultats paginés).
- Vue par défaut `pages/show.blade.php` : sidebar Bootstrap (`list-group` + onglets `data-bs-toggle="tab"`) qui pré-rend TOUTES les pages sœurs dans des panneaux cachés et bascule entre elles en JS (`bootstrap.Tab` + `history.pushState`), sans rechargement de page.
- Header partagé `partials/_header.blade.php` : titre + formulaire de recherche (`GET wiki.search`, paramètre `q`).

## 2. Simplification assumée : navigation classique plutôt que bascule JS type SPA

Le comportement Bootstrap original évite un rechargement de page en pré-rendant toutes les pages sœurs et en les basculant côté client (`bootstrap.Tab`). Reproduire ça sans Bootstrap demanderait de réimplémenter la gestion d'historique/titre en JS maison, pour un gain UX marginal (un wiki se navigue naturellement par rechargements de page complets). **Simplification retenue** : chaque lien de la sidebar est un lien classique vers la vraie route `wiki.pages.show` de la page sœur — navigation standard, pas de JS de bascule. Le contenu affiché est uniquement celui de `$page` (la page courante), pas toutes les pages sœurs en même temps.

## 3. Rendu du thème

### Header partagé (`views/vendor/wiki/partials/_header.blade.php`)
En-tête façon Hero (eyebrow doré + `$title`) + champ de recherche stylé Eldoria (bordure dorée, bouton loupe), soumis en `GET` vers `wiki.search`.

### Grille de catégories (`views/vendor/wiki/categories/index.blade.php`)
Grille de `card-eldoria`, une icône SVG parchemin/livre (même icône pour toutes les catégories, voir §5), nom de la catégorie, lien vers `wiki.show`.

### Page d'un article (`views/vendor/wiki/pages/show.blade.php`)
Layout 2 colonnes (empilé sur mobile, `< 640px`) :
- Sidebar : sous-catégories (le cas échéant) puis pages sœurs de la catégorie courante, en liste verticale de liens `card-eldoria`, la page active mise en évidence (bordure/texte accent). Lien "Retour" vers la catégorie parente ou l'index Wiki.
- Contenu principal : `$page->title` en titre, `{!! $page->content !!}` dans un conteneur `prose prose-invert` (réutilise le plugin Typography déjà installé pour le support des pages personnalisées).

### Résultats de recherche (`views/vendor/wiki/pages/search.blade.php`)
Liste de résultats en `card-eldoria` (titre lien + badge catégorie + extrait `Str::limit(strip_tags(...), 300)`), pagination via la vue Tailwind native de Laravel (`pagination::tailwind`, déjà fournie par le framework — pas de vue de pagination à réinventer). État vide géré (réutilise `trans('wiki::messages.search.empty')`).

## 4. Contenu de démo (base de test locale uniquement)

- Lien navbar "Wiki" (`NavbarElement`, `type: plugin`, `value: wiki.index`).
- 1-2 catégories (ex : "Règlement", "Guide de démarrage"), 2-3 pages chacune (contenu HTML simple : titres, paragraphes, listes) — créées via `php artisan tinker`, jamais commitées dans `eldoria/`.

## 5. Icônes de catégorie

Le champ `icon` du plugin attend une classe Bootstrap Icons (`bi bi-xxx`), absente de ce thème (Tailwind pur, aucune police d'icônes chargée). **Décision : ignorer cette valeur et utiliser une icône SVG Eldoria unique** (parchemin/livre, cohérente avec l'univers du thème) pour toutes les catégories, plutôt que d'ajouter Bootstrap Icons comme nouvelle dépendance pour ce seul champ. Pas de perte fonctionnelle réelle : l'admin ne peut de toute façon configurer qu'une classe d'icône texte, jamais un vrai visuel personnalisé.

## 6. Notes transverses

- Toutes les nouvelles chaînes d'interface passent par `theme::theme.wiki.*` (FR + EN), sauf les messages déjà fournis par le plugin (`wiki::messages.title`, `.back`, `.search.results`, `.search.empty`), réutilisés tels quels.
- Mobile-first : sidebar empilée au-dessus du contenu sur mobile pour la page d'un article.
- Taille tactile minimale 48px sur les liens de la sidebar et le bouton de recherche.
- Pas de nouvelle dépendance JS (pas de bascule type SPA, pas de police d'icônes).
