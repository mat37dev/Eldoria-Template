# Eldoria — Podium de vote 3D, contenu de démo, style des pages — Design Spec

**Date :** 2026-07-07
**Scope :** Cinq ajouts sur la même branche (`worktree-v1.1-additions`) : un podium 3D sur la page Vote, un polish visuel des cartes boutique, un rendu de thème pour les pages Azuriom personnalisées (CGU etc.), et du contenu de démonstration (boutique, articles, navbar) créé uniquement dans l'installation de test locale pour les captures d'écran de la fiche market — pas dans le thème lui-même.

---

## 1. Contexte & principe directeur

**Distinction essentielle, validée avec le propriétaire du projet :** un thème Azuriom ne peut exécuter aucun code d'installation (pas de seeder, pas de migration, pas de route custom — contrainte déjà connue de ce projet). Le contenu de démo (catégories boutique, articles, liens navbar) ne peut donc **pas** être livré avec le thème : il est créé une seule fois, à la main (tinker), dans la base de données de l'installation Azuriom locale (`local/azuriom-test`), uniquement pour prendre les captures d'écran de la fiche market. Seul le **code d'affichage** (podium Vote, style des cartes boutique, template des pages) fait partie du thème livré et sera versionné.

---

## 2. Podium 3D + classement Vote

### Podium (nouveau)

Nouveau bloc ajouté en haut de `eldoria/views/vendor/vote/index.blade.php`, avant le classement top 10 existant (inchangé, simplement repositionné en dessous) :
- **3 socles** disposés comme un podium : 2ᵉ place à gauche, 1ʳᵉ place au centre (socle plus haut), 3ᵉ place à droite — ordre visuel classique, cohérent avec la référence Deluxe.
- Chaque socle affiche un **skin 3D rotatif** (réutilise `skinview3d`, déjà utilisé sur la page profil via `assets/js/profile.js`) et le **pseudo** du joueur au-dessus.
- Données : `$votes[1]`, `$votes[2]`, `$votes[3]` (déjà fournies par le contrôleur Vote réel — chaque entrée a `->user` et `->votes`).
- **Cases vides** (moins de 3 votants ce mois-ci) : le socle affiche une texture de skin **noire unie** (nouvel asset PNG 64×64, généré une fois et livré avec le thème) avec un **"?"** superposé par-dessus le canvas via une icône HTML/CSS positionnée en absolu — pas besoin d'intégrer le "?" dans la texture Minecraft elle-même, plus simple et plus robuste.

### Classement (inchangé)

Le tableau top 10 avec médailles (🥇🥈🥉) déjà existant reste identique dans son contenu et sa logique — seule sa position dans la page change (sous le nouveau podium au lieu d'être la première chose affichée).

### Implémentation technique

Nouveau point d'entrée Vite dédié `assets/js/vote-podium.js` (même principe que `profile.js` : bundle séparé, chargé uniquement sur la page Vote via `theme_asset('dist/vote-podium.js')`), instanciant 3 `SkinViewer` indépendants (un par canvas), zoom désactivé, rotation automatique respectant `prefers-reduced-motion` (identique au réglage déjà en place pour le profil).

---

## 3. Polish visuel des cartes boutique

Retouche CSS uniquement (pas de changement de structure/données) sur `eldoria/views/vendor/shop/categories/show.blade.php` (cartes de la grille) et `eldoria/views/vendor/shop/packages/show.blade.php` (détail d'un article) : bordures et effet de survol cohérents avec `card-eldoria` (déjà utilisé ailleurs dans le thème), mise en valeur de l'image/icône du produit, prix plus visible (couleur accent, typographie `font-display`). Aucun nouveau champ, aucune nouvelle donnée.

---

## 4. Style des pages Azuriom personnalisées

Nouveau fichier `eldoria/views/pages/show.blade.php` (n'existe pas actuellement dans le thème — Azuriom utilise son rendu par défaut : un `<h1>` brut et une carte Bootstrap générique, sans aucun style Eldoria bien que la navbar/footer/couleurs soient déjà héritées via `layouts.app`). Nouveau rendu :
- En-tête façon Hero : eyebrow doré + titre de la page (`$page->title`) en `font-display`.
- Contenu (`$page->content`, HTML libre saisi par l'admin) affiché dans une `card-eldoria`, avec une classe de typographie dédiée assurant une lisibilité correcte des balises HTML courantes qu'un admin pourrait utiliser (titres, paragraphes, listes, liens, gras/italique) sans que l'admin ait à écrire du HTML avec des classes Tailwind lui-même.

---

## 5. Contenu de démo (base de données locale uniquement, non livré avec le thème)

Créé une seule fois via `php artisan tinker` dans `local/azuriom-test`, jamais commité dans `eldoria/` :
- **Boutique** : 2 catégories ("Monnaie", "Rangs"), 3 articles chacune (ex : 500/1200/3000 rubis à prix croissants pour "Monnaie" ; VIP/VIP+/Légende pour "Rangs"), chacun avec une icône SVG maison (voir §6).
- **Articles** : 2 posts ("Bienvenue sur Eldoria", "Mise à jour 1.21") avec contenu générique transposable à n'importe quel serveur.
- **Navbar** : 3 entrées `NavbarElement` (Boutique → `shop.home`, Vote → `vote.home`, Actus → `posts.index`).
- **Page CGU** : une page Azuriom de démo pour vérifier le rendu de la Task 4.

---

## 6. Icônes SVG maison (boutique + podium)

Petites icônes SVG (rubis, diamant, couronne, pièce...) dessinées aux couleurs du thème (`--color-accent`, `--color-accent-secondary`), livrées comme assets du thème (`eldoria/assets/images/shop/`) — réutilisables par n'importe quel acheteur du thème pour ses propres articles, pas seulement pour la démo. Pas de dépendance à un service externe ni de risque de droits d'auteur.

---

## 7. Notes transverses

- Toutes les nouvelles chaînes d'interface (le cas échéant) passent par `theme::theme.*` (FR + EN).
- Mobile-first : le podium empile ses 3 socles verticalement sur mobile (`< 640px`), passe en ligne horizontale à partir de `sm:`.
- `prefers-reduced-motion` : la rotation automatique des skins du podium est désactivée dans ce mode, identique au traitement déjà en place pour le skin du profil.
- Le nouveau bundle `vote-podium.js` suit le même principe que `profile.js` : jamais chargé ailleurs que sur la page Vote.
- Aucune limite de version imposée sur la charte des icônes SVG — elles suivent simplement les couleurs CSS custom properties existantes.
