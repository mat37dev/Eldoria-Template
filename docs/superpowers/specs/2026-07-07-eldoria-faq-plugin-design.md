# Eldoria — Support du plugin FAQ — Design Spec

**Date :** 2026-07-07
**Scope :** Habillage du plugin officiel Azuriom FAQ (github.com/Azuriom/Plugin-FAQ) aux couleurs du thème, + contenu de démo local pour la fiche market.

---

## 1. Contexte

Recherche menée sur le code source réel du plugin (cloné dans `local/azuriom-plugin-faq`) :
- Modèle `Azuriom\Plugin\FAQ\Models\Question` (table `faq_questions`), champs `name` (question), `answer` (réponse, HTML libre), `position`. **Aucune notion de catégorie, d'icône ou de statut activé/désactivé** — une liste plate ordonnée.
- Route unique `faq.index`, contrôleur `QuestionController::index()` qui passe une seule variable à la vue : `$questions` (collection Eloquent, triée par `position`).
- Vue par défaut du plugin : accordéon Bootstrap (`.accordion`), réponse affichée en HTML brut (`{!! $question->answer !!}`), premier élément ouvert par défaut, ancre par question (`Str::slug($question->name)`), script d'auto-ouverture si l'URL contient un hash correspondant.
- Chemin de surcharge theme : `views/vendor/faq/index.blade.php` (fichier unique, pas de sous-dossier).

## 2. Rendu du thème

Nouveau fichier `eldoria/views/vendor/faq/index.blade.php` :
- En-tête façon Hero (eyebrow doré + titre `theme::theme.faq.title`).
- Chaque question devient un accordéon `card-eldoria` : bouton cliquable (question + chevron SVG qui pivote à l'ouverture), réponse repliable dans un conteneur `prose prose-invert` (réutilise le plugin Tailwind Typography déjà installé — cf. Task 36) pour un rendu propre du HTML libre.
- Comportement d'ouverture : implémentation Alpine.js légère (`x-data="{ open: null }"` au niveau du conteneur, chaque item compare son id à `open`) — **un seul accordéon ouvert à la fois**, cohérent avec le comportement Bootstrap par défaut du plugin.
- Ancres par question conservées (`id="{{ Str::slug($question->name) }}"`) pour la compatibilité avec le script d'auto-ouverture du plugin (lien direct vers une question depuis l'extérieur) — script réécrit en vanilla JS pour ouvrir le bon accordéon Alpine au chargement si l'URL contient un hash correspondant.
- État vide : message centré, cohérent avec les autres pages (`text-text-secondary`), réutilisant la traduction du plugin si possible (`trans('faq::messages.empty')`) plutôt qu'une nouvelle clé.

## 3. Contenu de démo (base de test locale uniquement)

- Un lien navbar "FAQ" (`NavbarElement`, `type: plugin`, `value: faq.index`) — créé uniquement dans la base de test locale, comme les navbar links précédents.
- 4-5 questions/réponses génériques (ex : "Comment rejoindre le serveur ?", "Comment fonctionne la boutique ?", "Puis-je récupérer mon pseudo ?", "Comment signaler un joueur ?") créées via `php artisan tinker` — jamais commitées dans `eldoria/`.

## 4. Notes transverses

- Toutes les nouvelles chaînes d'interface passent par `theme::theme.faq.*` (FR + EN).
- Mobile-first, 48px minimum sur les boutons d'accordéon.
- `prefers-reduced-motion` : la rotation du chevron et l'animation d'ouverture/fermeture sont désactivées dans ce mode (transition CSS retirée, l'état s'applique instantanément).
