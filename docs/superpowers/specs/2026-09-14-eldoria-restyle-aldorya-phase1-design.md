# Eldoria — Reskin visuel "Aldorya" — Phase 1 (socle + navbar/footer/accueil) — Design Spec

**Date :** 2026-09-14
**Scope :** Remplacement radical du langage visuel du thème (gothique sombre parchemin/or → MMORPG cartoon lumineux, inspiré du projet de référence fourni par l'utilisateur), en conservant strictement la structure des pages, le mécanisme du customizer et les systèmes fonctionnels (drag-and-drop, sections activables). Cette spec couvre uniquement le **socle de design + navbar + footer + page d'accueil**. Les autres pages/plugins (boutique, vote, FAQ, wiki, support, changelog) suivront dans des specs séparées une fois ce socle validé.

**Travail isolé** sur la branche `worktree-style-aldorya` (worktree dédié), indépendante de `v1.1-additions` (travail plugins en cours). Si le nouveau style ne convient pas, cette branche est abandonnée sans impact sur le reste.

---

## 1. Contexte

Analyse du projet de référence (`C:\Users\mathi\Downloads\aldorya sitweb (1)\project`, Next.js + Tailwind, assets fournis par l'utilisateur qui en détient les droits) :

- **Palette** : fond bleu ciel (`hsl(196 78% 87%)`), panneaux "bois" (dégradés bruns `#b9794c`→`#9d5c38`), panneaux "ciel" (dégradés bleus `#54c6ed`→`#36aae0`), accents dorés (`#ffd532`→`#ff9f0b` sur les boutons), bordeaux/marron pour le texte sur fond clair (`#402314`, `#542d1d`).
- **Typo** : Cinzel (titres) + Inter (corps) — **déjà utilisées telles quelles dans Eldoria**, aucun changement nécessaire.
- **Langage de composant** : bordures épaisses colorées (3-4px) sur tous les panneaux/cartes, coins arrondis modérés (10-16px), **effet "bouton pressé" en 3D** (ombre interne + bordure basse plus foncée simulant un bouton physique, `box-shadow: inset 0 -3px/-4px ..., 0 3px/4px/6px 0 ...`), dégradés verticaux légers sur boutons et panneaux plutôt que des à-plats.
- **Illustrations** : mascottes/personnages (chevalier, mage, archer, barde, assassin) qui débordent des panneaux (`position: absolute`, légèrement pivotés), fonds d'écran illustrés pleine page (`background: ... url(...) center top / cover fixed`).
- **Animations** : `float`, `float-slow`, `wave`, `shimmer`, `glow-pulse`, `drift` — mouvements doux et ludiques plutôt que des braises/particules sombres.

## 2. Ce qui NE change PAS (contrainte explicite de l'utilisateur : garder le format)

- Le mécanisme du customizer reste **strictement identique** : 2 propriétés CSS personnalisables (`--color-accent`, `--color-accent-secondary`), même formulaire admin, même `config.json`/`config/rules.php`, aucun nouveau champ.
- Les **6 CSS custom properties** existantes restent les seules variables de couleur du thème (`--color-bg-primary`, `--color-bg-secondary`, `--color-accent`, `--color-accent-secondary`, `--color-text-primary`, `--color-text-secondary`) — seules leurs **valeurs par défaut** changent.
- La structure des sections de la page d'accueil reste identique et dans le même ordre : hero → barre de stats (compteurs) → présentation → trailer → fonctionnalités → aperçu boutique → aperçu vote → équipe → discord.
- La navbar reste une **barre fixe pleine largeur** en haut de page (pas de navbar flottante en pilule comme la référence — ce serait un changement de structure, pas seulement de style). Le footer reste une grille 3 colonnes en bas de page.
- Le drag-and-drop de réorganisation des sections (drawer admin) n'est pas touché.
- `prefers-reduced-motion`, mobile-first, taille tactile 48px : toujours respectés.

## 3. Nouvelle palette de couleurs

### Palette par défaut ("Eldoria" — renommée en conservant son statut de palette par défaut)

| Variable | Ancienne valeur | Nouvelle valeur | Usage |
|---|---|---|---|
| `--color-bg-primary` | `#0F0D0A` (noir) | `#7BC4E8` (bleu ciel) | Fond de page |
| `--color-bg-secondary` | `#1A1612` (brun très sombre) | `#FFF4D7` (parchemin clair) | Cartes, panneaux clairs |
| `--color-accent` | `#C9A84C` (or patiné) | `#E9A62D` (or/miel) | Accent principal — boutons, titres |
| `--color-accent-secondary` | `#7B3F2E` (bordeaux sombre) | `#9D5C38` (bois) | Accent secondaire — panneaux bois, bordures |
| `--color-text-primary` | `#E8DCC8` (parchemin clair) | `#402314` (brun profond) | Texte principal (désormais sur fond clair) |
| `--color-text-secondary` | `#8A7A62` (brun taupe) | `#765338` (brun moyen) | Sous-titres, texte secondaire |

**Changement de polarité assumé** : le thème passe d'un texte clair sur fond sombre à un texte sombre sur fond clair, comme la référence. C'est un changement de contraste global, pas seulement de teinte — répercuté dans `@layer base` (`body { @apply bg-bg-primary text-text-primary }` reste correct techniquement, mais toutes les classes `text-text-primary`/`text-text-secondary` du reste du thème doivent rester lisibles avec ce nouveau sens ; à vérifier composant par composant pendant l'implémentation).

### Palettes prédéfinies (retintées dans le même esprit, remplace les 5 actuelles)

| Nom | Accent | Secondaire | Esprit |
|---|---|---|---|
| Eldoria (défaut) | `#E9A62D` | `#9D5C38` | Or et bois — identique à la référence |
| Prairie | `#6FAF52` | `#3E7A34` | Vert prairie/forêt clair |
| Océan | `#3AA0D8` | `#1E6FA8` | Bleu profond (au lieu du sombre "Abysses") |
| Braise | `#E2683A` | `#A8431F` | Orange automne/braise (au lieu du rouge "Volcan") |
| Givre | `#7EC8D8` | `#3E7A8A` | Bleu glacé clair, conservé dans le même esprit |

Ces 5 palettes remplacent le tableau `PALETTES` dans `eldoria/assets/js/customizer.js` — même structure de données (`{ name, accent, secondary }`), seules les valeurs changent.

## 4. Composants visuels

### Boutons (`.btn-primary`)
Remplace le style plat actuel par l'effet "bouton pressé" 3D de la référence :
```css
.btn-primary {
    /* dégradé vertical accent → accent-secondary, bordure basse plus foncée,
       ombre portée simulant l'épaisseur, translateY(-2px) au survol */
}
```
Conserve `min-h-[48px]`, `font-display`, `uppercase`, `tracking-wider` (identité typographique inchangée), seul l'habillage (dégradé, ombre, radius) change.

### Cartes (`.card-eldoria`)
Remplace la carte plate à bordure fine 20%-opacité par un panneau à bordure épaisse (3px) et fond dégradé clair (façon "parchemin"), avec une ombre portée basse simulant l'épaisseur — même esprit que `.wood-panel`/`.highlight-card` de la référence. Le hover passe d'un simple changement de couleur de bordure à un léger soulèvement (`translateY(-2px)`).

### Titres de section (`.section-title`)
Garde sa position/taille/tracking actuels ; la couleur suit la nouvelle valeur de `--color-accent`. Le soulignement dégradé existant (`::after`) est conservé tel quel — fonctionne avec n'importe quelle valeur d'accent.

### Overlay de grain (`body::before`)
**Supprimé.** Ce grain de bruit subtil servait l'esthétique gothique-parchemin ; il n'a plus de sens sur un fond illustré cartoon lumineux et serait visuellement parasite.

## 5. Navbar

Reste une barre fixe pleine largeur (`eldoria/views/partials/navbar.blade.php`, structure HTML inchangée). Changements visuels uniquement :
- Fond : dégradé bleu (`--color-accent` décliné vers une teinte ciel fixe, ou nouvelle variable `--color-bg-primary` si le fond de navbar suit le fond de page) au lieu du fond sombre translucide actuel.
- Bordure basse : plus marquée, dans un ton clair (façon `.portal-nav` de la référence), au lieu de `border-accent/10`.
- Liens : texte blanc/clair sur fond bleu (au lieu de `text-text-secondary` brun sur fond clair — la navbar garde un fond distinct du reste de la page, comme la référence), hover doré.
- Bouton d'inscription : suit le nouveau `.btn-primary`.

## 6. Footer

Reste une grille 3 colonnes (`eldoria/views/partials/footer.blade.php`, structure HTML inchangée). Devient un panneau "bois" (dégradé brun, bordure épaisse claire) au lieu du bandeau plat sombre actuel — même traitement que les panneaux du corps de page, pour une cohérence visuelle de haut en bas.

## 7. Page d'accueil

Même ordre de sections (§2), recolorées et re-encadrées avec les nouveaux composants :
- **Hero** : fond illustré (voir §8) au lieu du dégradé sombre actuel ; titre en Cinzel avec l'ombre portée "gravée" caractéristique de la référence (`text-shadow` multiple, pas juste une couleur).
- **Barre de stats** : reste un bandeau contrasté (fond `--color-bg-secondary` ou panneau bois), chiffres en accent doré.
- **Présentation / fonctionnalités** : cartes `.card-eldoria` mises à jour (§4), disposées en grille comme actuellement.
- **Aperçu boutique/vote/équipe** : héritent automatiquement du nouveau `.card-eldoria`/`.btn-primary` sans changement de structure.
- **Discord** : devient un panneau contrasté (bois ou bleu) avec bouton `.btn-primary`, dans le même esprit que `.discord-section` de la référence.

## 8. Assets réutilisés

L'utilisateur ayant confirmé être propriétaire de l'ensemble des images du projet de référence, les fichiers suivants sont copiés dans `eldoria/assets/images/` pour cette phase :
- Un fond illustré pour le hero (`hero-landscape.webp` ou équivalent).
- Les illustrations de personnages pertinentes pour les sections traitées ici (ex. une mascotte pour le panneau de bienvenue/présentation), si leur intégration reste raisonnable en taille de page.

Le fichier `hero-default.svg` actuel (fallback existant si `hero_image` n'est pas configuré côté admin) est remplacé par un équivalent dans le nouvel esprit visuel ou par l'image illustrée ci-dessus.

## 9. Animations

Les 3 différenciateurs du thème (particules, parallaxe hero, compteurs animés) sont **conservés en fonctionnalité**, adaptés en apparence :
- Particules (`particles.js`) : passent d'un rendu façon braises/embres sombres à un rendu plus léger (étincelles dorées ou feuilles), sans changer la mécanique canvas existante.
- Parallaxe GSAP sur le hero : inchangée dans son fonctionnement.
- Compteurs animés : inchangés, la couleur suit `--color-accent`.
- Nouvelles animations d'ambiance optionnelles (`float`, `wave`) peuvent être ajoutées ponctuellement sur les illustrations de personnages, en respectant `prefers-reduced-motion: reduce` comme le reste du thème.

## 10. Notes transverses

- Mobile-first, 48px de taille tactile minimale : inchangé.
- `prefers-reduced-motion: reduce` : toute nouvelle animation (dégradés de bouton au survol compris s'ils sont animés, `float`/`wave` sur mascottes) doit être neutralisée dans ce mode, même pattern que l'existant (classes de transition nommées + bloc media query en fin de fichier CSS).
- Aucune nouvelle dépendance JS. Les polices (Cinzel/Inter) sont déjà chargées, aucun changement à l'import Google Fonts.
- Le fichier `config.json`/`config/rules.php` (schéma du customizer) n'est pas modifié dans cette phase — seules les valeurs par défaut dans `views/layouts/app.blade.php` (fallback `theme_config('color_accent', '#C9A84C')` etc.) et dans `config.json` changent.
