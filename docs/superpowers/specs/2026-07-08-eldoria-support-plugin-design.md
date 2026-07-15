# Eldoria — Support du plugin Support — Design Spec

**Date :** 2026-07-08
**Scope :** Habillage du plugin officiel Azuriom Support (github.com/Azuriom/Plugin-Support) aux couleurs du thème, + contenu de démo local pour la fiche market.

---

## 1. Contexte

Recherche menée sur le code source réel (cloné dans `local/azuriom-plugin-support`) :

- Modèle `Category` (table `support_categories`) : `name`, `icon` (classe Bootstrap Icons — même problème que Wiki, voir §4), `description`, relation `fields` (champs personnalisés).
- Modèle `Field` (table `support_fields`) : `name`, `description`, `type` (`text`, `number`, `email`, `textarea`, `checkbox`, `dropdown`), `is_required`, `options` (pour `dropdown`). Rattaché à une catégorie. **Si une catégorie a des champs, ils remplacent le champ « contenu » libre du formulaire de création** — leurs valeurs sont fusionnées en Markdown (`## Nom du champ\n\nValeur`) pour former le contenu du premier commentaire (`TicketRequest::prepareForValidation()`). Il n'y a donc jamais de zone de texte libre ET de champs personnalisés en même temps sur le formulaire de création.
- Modèle `Ticket` (table `support_tickets`) : `subject`, `author_id`, `category_id`, `assignee_id` (staff, non affiché côté client), `closed_at`. Méthodes `status()` (`open`/`replied`/`closed`), `statusMessage()`, `isClosed()`.
- Modèle `Comment` (table `support_comments`) : `content` (Markdown, parsé via `parseContent()` → HTML), `author_id`, `ticket_id`. Les pièces jointes (captures d'écran) sont insérées **par l'éditeur Markdown lui-même** (glisser-déposer → upload AJAX → insertion d'une image Markdown dans le texte) : il n'existe aucune UI de pièce jointe séparée à reproduire.
- 4 routes front réelles : `support.tickets.create` (liste des catégories, vue `tickets/categories.blade.php`), `support.category.tickets.create` / `support.category.tickets.store` (formulaire d'une catégorie, vue `tickets/create.blade.php`), `support.tickets.index` (mes tickets, vue `tickets/index.blade.php`), `support.tickets.show` (fil de discussion, vue `tickets/show.blade.php`), `support.tickets.close` / `support.tickets.open` (POST), `support.tickets.comments.store` (POST réponse).
- Politique d'accès (`TicketPolicy`) : un utilisateur ne voit et ne modifie que ses propres tickets. Pas de vue « liste globale » côté client — la modération complète (assignation, tous les tickets) est réservée à l'admin, hors scope de ce thème.
- Un ticket fermé (`isClosed()`) n'affiche plus le formulaire de réponse, seulement un message + un bouton « Rouvrir » si `setting('support.reopen')` est activé côté admin.

## 2. Bug réel découvert : stacks CSS/JS manquantes pour l'éditeur Markdown

L'élément core partagé `elements/markdown-editor.blade.php` (utilisé par `tickets/create.blade.php` et `tickets/show.blade.php`, pas un fichier du plugin) pousse son CSS via `@push('styles')` et son JS d'initialisation via `@push('footer-scripts')`. Le layout actuel du thème (`eldoria/views/layouts/app.blade.php`) ne déclare que `@stack('head')` et `@stack('scripts')` — **ces deux stacks n'existent nulle part dans le layout**, donc tout contenu poussé dessus est silencieusement perdu : sans correctif, l'éditeur ne s'initialiserait même pas (pas de barre d'outils, pas d'upload d'image, juste un `<textarea>` brut invisible sous son propre CSS manquant).

**Correctif** : ajouter `@stack('styles')` juste avant la fermeture de `<head>` et `@stack('footer-scripts')` juste avant `@stack('scripts')` en fin de `<body>`, dans `eldoria/views/layouts/app.blade.php`. Ce correctif est nécessaire même indépendamment du réhabillage visuel (§4) — sans lui, la fonctionnalité de création de ticket est cassée.

## 3. Décision validée : réhabillage complet de l'éditeur Markdown

Une fois les stacks corrigées, l'éditeur (EasyMDE) s'afficherait avec son style par défaut, qui dépend de variables CSS Bootstrap (`--bs-border-radius`, `--bs-body-bg`, `--bs-border-color`, etc.) qu'Eldoria ne définit jamais → rendu clair/blanc par défaut, incohérent au milieu d'une page sombre.

**Décision (validée par l'utilisateur, avec pour contrainte explicite qu'aucune installation supplémentaire ne doit être demandée à l'acheteur du thème)** : le thème surcharge `views/elements/markdown-editor.blade.php` (élément core partagé, convention de surcharge identique à celle utilisée pour les vues plugin) pour :
- Remplacer le bloc `<style>` inline : fond `var(--color-bg-secondary)`, bordure `var(--color-accent)/20`, texte `var(--color-text-primary)`, la barre d'outils et son bouton actif en accent doré.
- Conserver la police **Bootstrap Icons** pour les icônes de la barre d'outils (gras, italique, lien, image, tableau, etc.) : elle est déjà livrée par le core Azuriom (`vendor/bootstrap-icons/bootstrap-icons.css`, chargée par `layouts/base.blade.php` pour l'admin) — l'ajouter au layout du thème ne demande aucune dépendance ni installation nouvelle côté acheteur, contrairement à l'ajout d'une police tierce.
- Conserver 100% du JS d'initialisation EasyMDE (upload d'image, autosave, textes traduits) tel quel — seul l'habillage visuel change, aucune logique.

## 4. Rendu du thème

### Grille de catégories (`views/vendor/support/tickets/categories.blade.php`)
En-tête (eyebrow doré + titre), puis grille de `card-eldoria` (une par catégorie) : icône SVG fixe (parchemin+point d'interrogation, unique pour toutes les catégories — même décision que pour Wiki §5 de sa propre spec : le champ `icon` attend une classe Bootstrap Icons, ignoré ici pour éviter une nouvelle dépendance de police pour ce seul champ), nom, description si présente, bouton « Ouvrir un ticket » vers `support.category.tickets.create`.

### Formulaire de création (`views/vendor/support/tickets/create.blade.php`)
Carte `card-eldoria` : champ Sujet (texte, obligatoire) en haut, puis :
- Si la catégorie n'a aucun champ personnalisé : l'éditeur Markdown réhabillé (§3).
- Sinon : les champs personnalisés de la catégorie, rendus dans l'ordre avec les composants de formulaire Eldoria déjà établis (bordure dorée au focus, `min-h-[48px]`) — texte/nombre/email en `<input>`, `textarea` en zone de texte, `checkbox` en case à cocher stylée, `dropdown` en `<select>` avec les options du champ. Astérisque doré si `is_required`. Description du champ affichée sous le champ si présente.

### Mes tickets (`views/vendor/support/tickets/index.blade.php`)
Liste de cartes `card-eldoria` (pas de tableau HTML brut) : une carte par ticket avec sujet (lien vers `support.tickets.show`), nom de catégorie, badge de statut (voir palette ci-dessous), date de création (`format_date_compact`). Bouton « Ouvrir un ticket » en fin de liste vers `support.tickets.create`. État vide si aucun ticket.

**Palette des statuts** — reste dans le système à 2 accents personnalisables du thème plutôt que d'introduire du vert/rouge Bootstrap fixe (qui jurerait avec les palettes non-dorées comme Abysses ou Givre) :
- `open` (ouvert) → `--color-accent`
- `replied` (réponse reçue) → `--color-accent-secondary`
- `closed` (fermé) → `--color-text-secondary`, sans accent

### Fil de discussion (`views/vendor/support/tickets/show.blade.php`)
En-tête : sujet en titre, badge de statut, texte d'info (auteur/catégorie/date, `support::messages.tickets.info`). Puis la liste des commentaires en cartes `card-eldoria` empilées : avatar (`getAvatar()`), nom auteur + date, contenu Markdown parsé (`$comment->parseContent()`) dans un conteneur `prose prose-invert` (réutilise le plugin Typography déjà installé, cf. Task 36 du plan Wiki). Si le ticket est ouvert : formulaire de réponse (éditeur Markdown réhabillé) + bouton fermer. Si fermé : message « ce ticket est fermé » + bouton rouvrir si `$canReopen`.

## 5. Contenu de démo (base de test locale uniquement)

- Lien navbar « Support » (`NavbarElement`, `type: plugin`, `value: support.tickets.create`).
- 2 catégories : une simple sans champ (ex. « Question générale »), une avec champs personnalisés (ex. « Signaler un bug » : un champ texte « Résumé » + un dropdown « Plateforme » avec options Java/Bedrock) — pour illustrer les deux rendus du formulaire de création.
- 2-3 tickets de démo avec un petit fil de réponses (2-3 commentaires chacun), un ticket fermé pour illustrer ce statut — créés via `php artisan tinker`, jamais commités dans `eldoria/`.

## 6. Notes transverses

- Toutes les nouvelles chaînes d'interface passent par `theme::theme.support.*` (FR + EN), sauf les messages déjà fournis par le plugin (`support::messages.*`), réutilisés tels quels.
- Mobile-first, taille tactile minimale 48px sur tous les boutons/liens interactifs.
- Aucune nouvelle dépendance JS. Bootstrap Icons est la seule police ajoutée au layout — déjà livrée par le core Azuriom, zéro installation pour l'acheteur.
- Pas de vue de modération/admin à styliser (liste globale des tickets, assignation) — hors scope, réservé à l'admin Azuriom natif.
