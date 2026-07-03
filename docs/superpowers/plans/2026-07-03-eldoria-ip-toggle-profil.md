# Eldoria — Bouton copie IP, toggle réutilisable, admin réorganisé, refonte profil — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Ajouter un bouton de copie d'IP avec easter egg façon combo, un composant toggle réutilisable, une réorganisation de la page de config Azuriom, et une refonte de la page profil (avatar navbar, informations enrichies, visualiseur de skin 3D).

**Architecture:** Quatre ajouts indépendants sur la même branche. Un nouveau champ de config `server_ip_display` (texte libre) unifie l'adresse copiée par le bouton "Rejoindre" existant et un nouveau bouton dédié avec info-bulles combo. Un partial Blade réutilisable remplace la case à cocher du customizer. La page de config Azuriom (Bootstrap natif) reçoit des séparateurs visuels et une nouvelle section. La page profil s'enrichit d'informations Azuriom natives (rôle, dernière connexion, solde, email vérifié) et d'un visualiseur de skin 3D (`skinview3d`, chargé uniquement sur cette page via un second point d'entrée Vite).

**Tech Stack:** Laravel/Blade, Tailwind CSS v3, Alpine.js v3, Vite, skinview3d (nouvelle dépendance npm).

## Global Constraints

- Mobile-first : CSS pour `< 640px` en premier, desktop via `min-width`
- Toutes les nouvelles animations respectent `prefers-reduced-motion: reduce` (glow du bouton IP, rotation automatique du skin 3D)
- Taille tactile minimale des boutons : 48px
- Couleurs uniquement via les CSS custom properties existantes — aucune couleur codée en dur
- Toutes les nouvelles chaînes d'interface passent par `theme::theme.*` (FR + EN)
- Le thème livré sur le market contient les fichiers `dist/` déjà compilés — l'acheteur n'exécute jamais `npm`, il installe le thème depuis l'admin Azuriom et ne touche qu'aux formulaires de configuration
- Le nouveau visualiseur de skin 3D ne doit être chargé que sur la page profil, jamais sur le reste du site (bundle séparé, pas de code-splitting dynamique)

---

## Carte des fichiers

```
eldoria/
├── package.json                          ← MODIFY — +skinview3d
├── vite.config.js                        ← MODIFY — +point d'entrée "profile"
├── config.json                           ← MODIFY — +server_ip_display
├── config/
│   ├── rules.php                         ← MODIFY — règle validation server_ip_display
│   └── config.blade.php                  ← MODIFY — réorganisation + section Serveur
├── assets/
│   ├── css/app.css                       ← MODIFY — glow du bouton IP
│   └── js/
│       ├── app.js                        ← MODIFY — +initIpCopy()
│       ├── ip-copy.js                    ← NEW — logique copie IP + combo
│       ├── profile.js                    ← NEW — init skinview3d (point d'entrée dédié)
│       └── customizer.js                 ← MODIFY — serverIpDisplay + liveServerIp()
├── views/
│   ├── home.blade.php                    ← MODIFY — bouton Rejoindre unifié + nouveau badge IP
│   ├── profile/index.blade.php           ← MODIFY — infos enrichies + bloc skin 3D
│   └── partials/
│       ├── navbar.blade.php              ← MODIFY — avatar à côté du pseudo
│       ├── customizer.blade.php          ← MODIFY — champ IP + toggle réutilisable
│       └── _toggle-switch.blade.php      ← NEW — composant toggle réutilisable
├── lang/fr/theme.php, lang/en/theme.php  ← MODIFY — nouvelles clés i18n
```

---

### Task 27 : Champ `server_ip_display` + unification du bouton "Rejoindre"

**Files:**
- Modify: `eldoria/config.json`
- Modify: `eldoria/config/rules.php`
- Modify: `eldoria/views/home.blade.php`
- Modify: `eldoria/views/partials/customizer.blade.php`
- Modify: `eldoria/assets/js/customizer.js`
- Create: `eldoria/assets/js/ip-copy.js`
- Modify: `eldoria/assets/js/app.js`

**Interfaces:**
- Produces: `$displayIp` (variable PHP calculée dans `home.blade.php`, réutilisée par la Task 28), `data-ip`/`data-default-ip` sur `#btn-join` (lus par `ip-copy.js` et `customizer.js`), `initIpCopy()` (fonction exportée, appelée depuis `app.js`, étendue par la Task 28)

- [ ] **Step 1 : Ajouter `server_ip_display` à `eldoria/config.json`**

Remplacer :
```json
    "home_layout": "",
```
par :
```json
    "home_layout": "",
    "server_ip_display": "",
```

- [ ] **Step 2 : Ajouter la règle de validation dans `eldoria/config/rules.php`**

Remplacer :
```php
    'home_layout' => ['nullable', 'json', 'max:5000'],
];
```
par :
```php
    'home_layout' => ['nullable', 'json', 'max:5000'],
    'server_ip_display' => ['nullable', 'string', 'max:255'],
];
```

- [ ] **Step 2 : Calculer `$displayIp` et unifier le bouton "Rejoindre" dans `eldoria/views/home.blade.php`**

Remplacer :
```blade
<?php
        $heroVideoEnabled = theme_config('hero_video_enabled', '0') === '1' && $trailerId !== null;
    ?>
```
par :
```blade
<?php
        $heroVideoEnabled = theme_config('hero_video_enabled', '0') === '1' && $trailerId !== null;
        $displayIp = theme_config('server_ip_display', '') ?: ($homeServer ? $homeServer->fullAddress() : '');
    ?>
```

Remplacer le bouton "Rejoindre" :
```blade
            @if($homeServer)
                <div class="flex items-center gap-2">
                    <span id="server-status-dot"
                          data-online-label="{{ __('theme::theme.home.server_online') }}"
                          data-offline-label="{{ __('theme::theme.home.server_offline') }}"
                          title="{{ __('theme::theme.home.server_online') }}"
                          class="w-3 h-3 rounded-full bg-green-500 flex-shrink-0"></span>
                    <button onclick="navigator.clipboard.writeText('{{ $homeServer->fullAddress() }}')"
                            class="btn-primary relative group min-w-[180px] min-h-[48px]" id="btn-join">
                        <span class="absolute inset-0 rounded-sm animate-ping opacity-30 bg-accent"></span>
                        <span class="relative">{{ __('theme::theme.home.join') }}</span>
                        <span class="relative ml-2 text-xs font-mono opacity-70">{{ $homeServer->fullAddress() }}</span>
                    </button>
                </div>
            @endif
```
par :
```blade
            @if($homeServer)
                <div class="flex items-center gap-2">
                    <span id="server-status-dot"
                          data-online-label="{{ __('theme::theme.home.server_online') }}"
                          data-offline-label="{{ __('theme::theme.home.server_offline') }}"
                          title="{{ __('theme::theme.home.server_online') }}"
                          class="w-3 h-3 rounded-full bg-green-500 flex-shrink-0"></span>
                    <button type="button" id="btn-join"
                            data-ip="{{ $displayIp }}" data-default-ip="{{ $homeServer->fullAddress() }}"
                            class="btn-primary relative group min-w-[180px] min-h-[48px]">
                        <span class="absolute inset-0 rounded-sm animate-ping opacity-30 bg-accent"></span>
                        <span class="relative">{{ __('theme::theme.home.join') }}</span>
                        <span class="relative ml-2 text-xs font-mono opacity-70" data-ip-label>{{ $displayIp }}</span>
                    </button>
                </div>
            @endif
```

> Le bouton n'a plus d'`onclick` inline — la copie est gérée par `ip-copy.js` (Step 6), ce qui évite d'avoir à échapper l'adresse dans un attribut JS et permet la mise à jour live depuis le customizer (Step 5).

- [ ] **Step 3 : Ajouter le champ dans `eldoria/views/partials/customizer.blade.php` (onglet Contenu)**

Remplacer :
```blade
                <div>
                    <label class="block text-xs text-text-secondary uppercase tracking-widest mb-2">{{ __('theme::theme.customizer.trailer_label') }}</label>
```
par :
```blade
                <div>
                    <label class="block text-xs text-text-secondary uppercase tracking-widest mb-2">{{ __('theme::theme.customizer.server_ip_label') }}</label>
                    <input type="text" x-model="serverIpDisplay" @input="liveServerIp()"
                           placeholder="{{ __('theme::theme.customizer.server_ip_placeholder') }}"
                           class="w-full bg-bg-primary border border-accent/20 rounded-sm px-3 py-2 text-text-primary text-sm
                                  focus:outline-none focus:border-accent/60 min-h-[40px]">
                    <p class="text-text-secondary text-xs mt-1">{{ __('theme::theme.customizer.server_ip_help') }}</p>
                </div>

                <div>
                    <label class="block text-xs text-text-secondary uppercase tracking-widest mb-2">{{ __('theme::theme.customizer.trailer_label') }}</label>
```

Remplacer l'initialisation du composant Alpine :
```blade
        heroVideoEnabled: @js(theme_config('hero_video_enabled', '0') === '1'),
        trailerUrl: @js(theme_config('trailer_url', '') ?? ''),
```
par :
```blade
        heroVideoEnabled: @js(theme_config('hero_video_enabled', '0') === '1'),
        trailerUrl: @js(theme_config('trailer_url', '') ?? ''),
        serverIpDisplay: @js(theme_config('server_ip_display', '') ?? ''),
```

- [ ] **Step 4 : Ajouter l'état et `liveServerIp()` dans `eldoria/assets/js/customizer.js`**

Remplacer :
```js
        trailerUrl: initial.trailerUrl ?? '',
```
par :
```js
        trailerUrl: initial.trailerUrl ?? '',
        serverIpDisplay: initial.serverIpDisplay ?? '',
```

Ajouter `liveServerIp()` juste après `liveSlogan()`. Remplacer :
```js
        liveSlogan() {
            document.querySelectorAll('[data-live="hero_slogan"]')
                .forEach(el => { el.textContent = this.slogan })
        },

        liveHeroImage() {
```
par :
```js
        liveSlogan() {
            document.querySelectorAll('[data-live="hero_slogan"]')
                .forEach(el => { el.textContent = this.slogan })
        },

        liveServerIp() {
            const joinBtn = document.getElementById('btn-join')
            if (!joinBtn) return

            const effectiveIp = this.serverIpDisplay || joinBtn.dataset.defaultIp
            joinBtn.dataset.ip = effectiveIp

            const label = joinBtn.querySelector('[data-ip-label]')
            if (label) label.textContent = effectiveIp

            document.dispatchEvent(new CustomEvent('eldoria:ip-updated', { detail: { ip: effectiveIp } }))
        },

        liveHeroImage() {
```

Ajouter l'envoi du champ dans `save()`. Remplacer :
```js
                formData.append('trailer_url', this.trailerUrl)
```
par :
```js
                formData.append('trailer_url', this.trailerUrl)
                formData.append('server_ip_display', this.serverIpDisplay)
```

> `liveServerIp()` émet un événement `eldoria:ip-updated` sur `document` — la Task 28 l'écoutera pour garder le nouveau badge IP synchronisé avec le champ du customizer, sans dépendance directe entre les deux fichiers JS.

- [ ] **Step 5 : Créer `eldoria/assets/js/ip-copy.js`**

```js
export function initIpCopy() {
    const joinBtn = document.getElementById('btn-join')
    if (joinBtn) {
        joinBtn.addEventListener('click', () => {
            navigator.clipboard.writeText(joinBtn.dataset.ip || '')
        })
    }
}
```

> Ce fichier sera étendu par la Task 28 avec le bouton dédié et le système de combo — la structure de base (copie simple pour le bouton Rejoindre) doit fonctionner et être testée avant d'ajouter cette couche.

- [ ] **Step 6 : Appeler `initIpCopy()` depuis `eldoria/assets/js/app.js`**

Remplacer :
```js
import { initServerStatus } from './server-status.js'
```
par :
```js
import { initServerStatus } from './server-status.js'
import { initIpCopy } from './ip-copy.js'
```

Remplacer :
```js
    initAnimations()
    initVotePage()
    initPosts()
    initServerStatus()
})
```
par :
```js
    initAnimations()
    initVotePage()
    initPosts()
    initServerStatus()
    initIpCopy()
})
```

- [ ] **Step 7 : Build + vérification manuelle**

```bash
cd eldoria && npm run build
cd ../local/azuriom-test && php artisan view:clear
```

Sur `http://127.0.0.1:8000/`, cliquer sur le bouton "Rejoindre" (avec un serveur `home_display` configuré) : l'adresse copiée dans le presse-papier doit correspondre à l'adresse affichée à l'écran. En admin, ouvrir le customizer → Contenu → taper une valeur dans le nouveau champ IP : le texte affiché sur le bouton "Rejoindre" doit se mettre à jour en direct, sans recharger. Vider le champ : le texte doit revenir à l'adresse du serveur Azuriom. Enregistrer, recharger la page : la valeur doit persister et être utilisée par le bouton. Vérifier `storage/logs/laravel-*.log`.

- [ ] **Step 8 : Commit**

```bash
git add eldoria/config.json eldoria/config/rules.php eldoria/views/home.blade.php eldoria/views/partials/customizer.blade.php eldoria/assets/js/customizer.js eldoria/assets/js/ip-copy.js eldoria/assets/js/app.js
git commit -m "feat(eldoria): champ IP configurable, unifié entre le bouton Rejoindre et le futur bouton copie rapide"
```

---

### Task 28 : Bouton copie IP dédié avec combo façon League of Legends

**Files:**
- Modify: `eldoria/views/home.blade.php`
- Modify: `eldoria/assets/js/ip-copy.js`
- Modify: `eldoria/assets/css/app.css`

**Interfaces:**
- Consumes: `$displayIp` (Task 27), événement `eldoria:ip-updated` (Task 27)
- Produces: aucune interface consommée par une tâche ultérieure

- [ ] **Step 1 : Ajouter le badge dans `eldoria/views/home.blade.php`**

Remplacer :
```blade
    {{-- Contenu hero --}}
    <div class="relative z-10 text-center px-4 max-w-4xl mx-auto pt-16">
        <p class="text-accent text-sm font-display tracking-[0.4em] uppercase mb-4 opacity-80">
```
par :
```blade
    {{-- Contenu hero --}}
    <div class="relative z-10 text-center px-4 max-w-4xl mx-auto pt-16">
        <div class="relative flex justify-center mb-6 {{ $displayIp !== '' ? '' : 'hidden' }}" data-live-section="ip-copy-badge">
            <button type="button" id="btn-ip-copy"
                    data-ip="{{ $displayIp }}"
                    data-msg1="{{ __('theme::theme.home.ip_copy_1') }}"
                    data-msg2="{{ __('theme::theme.home.ip_copy_2') }}"
                    data-msg3="{{ __('theme::theme.home.ip_copy_3') }}"
                    data-msg4="{{ __('theme::theme.home.ip_copy_4') }}"
                    data-msg5="{{ __('theme::theme.home.ip_copy_5') }}"
                    data-msg-combo="{{ __('theme::theme.home.ip_copy_combo_1') }}|{{ __('theme::theme.home.ip_copy_combo_2') }}|{{ __('theme::theme.home.ip_copy_combo_3') }}"
                    title="{{ __('theme::theme.home.ip_copy_button') }}"
                    class="relative inline-flex items-center gap-2 px-4 py-2 min-h-[48px] rounded-full
                           bg-bg-secondary/80 border border-accent/40 text-text-primary text-sm font-mono
                           hover:border-accent transition-colors">
                <svg class="w-4 h-4 text-accent flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>
                <span id="ip-copy-value">{{ $displayIp }}</span>
                <span id="ip-copy-tooltip"
                      class="pointer-events-none absolute -top-10 left-1/2 -translate-x-1/2 whitespace-nowrap
                             px-3 py-1.5 rounded-sm bg-accent text-bg-primary text-xs font-display tracking-wide uppercase
                             opacity-0"></span>
            </button>
        </div>

        <p class="text-accent text-sm font-display tracking-[0.4em] uppercase mb-4 opacity-80">
```

- [ ] **Step 2 : Ajouter le CSS de la lueur d'attention dans `eldoria/assets/css/app.css`**

Ajouter à la fin du fichier :
```css

/* Légère pulsation d'attention sur le bouton copie IP du hero, désactivée si
   l'utilisateur préfère moins de mouvement. */
@media not (prefers-reduced-motion: reduce) {
    #btn-ip-copy {
        animation: ip-copy-glow 2.4s ease-in-out infinite;
    }
}

@keyframes ip-copy-glow {
    0%, 100% {
        box-shadow: 0 0 0 0 color-mix(in srgb, var(--color-accent) 35%, transparent);
    }
    50% {
        box-shadow: 0 0 0 8px color-mix(in srgb, var(--color-accent) 0%, transparent);
    }
}

#ip-copy-tooltip {
    transition: opacity 200ms ease-out;
}

@media (prefers-reduced-motion: reduce) {
    #ip-copy-tooltip {
        transition: none;
    }
}
```

- [ ] **Step 3 : Ajouter la logique de combo dans `eldoria/assets/js/ip-copy.js`**

Remplacer tout le contenu du fichier par :
```js
const COMBO_RESET_MS = 3000
const TOOLTIP_VISIBLE_MS = 1800

function initIpCopyBadge() {
    const btn = document.getElementById('btn-ip-copy')
    const tooltip = document.getElementById('ip-copy-tooltip')
    const valueEl = document.getElementById('ip-copy-value')
    if (!btn || !tooltip || !valueEl) return

    const messages = [btn.dataset.msg1, btn.dataset.msg2, btn.dataset.msg3, btn.dataset.msg4, btn.dataset.msg5]
    const comboMessages = btn.dataset.msgCombo.split('|')

    let clickCount = 0
    let lastClickTime = 0
    let hideTimeout = null

    btn.addEventListener('click', () => {
        const now = Date.now()
        clickCount = (now - lastClickTime > COMBO_RESET_MS) ? 1 : clickCount + 1
        lastClickTime = now

        navigator.clipboard.writeText(btn.dataset.ip || '')

        const message = clickCount <= messages.length
            ? messages[clickCount - 1]
            : comboMessages[(clickCount - messages.length - 1) % comboMessages.length]

        tooltip.textContent = message
        tooltip.classList.toggle('text-base', clickCount >= 5)
        tooltip.classList.toggle('font-bold', clickCount >= 5)

        clearTimeout(hideTimeout)
        tooltip.classList.remove('opacity-0')
        tooltip.classList.add('opacity-100')
        hideTimeout = setTimeout(() => {
            tooltip.classList.remove('opacity-100')
            tooltip.classList.add('opacity-0')
        }, TOOLTIP_VISIBLE_MS)
    })

    document.addEventListener('eldoria:ip-updated', (event) => {
        btn.dataset.ip = event.detail.ip
        valueEl.textContent = event.detail.ip
        const wrapper = btn.closest('[data-live-section="ip-copy-badge"]')
        if (wrapper) wrapper.classList.toggle('hidden', event.detail.ip === '')
    })
}

export function initIpCopy() {
    const joinBtn = document.getElementById('btn-join')
    if (joinBtn) {
        joinBtn.addEventListener('click', () => {
            navigator.clipboard.writeText(joinBtn.dataset.ip || '')
        })
    }

    initIpCopyBadge()
}
```

- [ ] **Step 4 : Build + vérification manuelle**

```bash
cd eldoria && npm run build
cd ../local/azuriom-test && php artisan view:clear
```

Sur la page d'accueil (avec un `server_ip_display` ou un serveur `home_display` configuré), le nouveau badge doit apparaître au-dessus de l'eyebrow, avec une légère pulsation. Cliquer une fois : info-bulle "IP copiée !" pendant ~1.8s. Cliquer 4 fois rapidement (moins de 3s d'écart) : "Double copie !", "Triple copie !", "Quadra copie !", puis "PENTA COPIE !" en plus gros/gras. Continuer à cliquer : les messages "Domination !"/"Massacre !"/"Légendaire !" doivent tourner. Attendre plus de 3 secondes puis recliquer : retour à "IP copiée !". Vérifier que le presse-papier contient bien la bonne adresse à chaque clic (coller dans un champ texte). Émuler `prefers-reduced-motion: reduce` (DevTools → Rendering) et confirmer que la pulsation du bouton disparaît.

- [ ] **Step 5 : Commit**

```bash
git add eldoria/views/home.blade.php eldoria/assets/js/ip-copy.js eldoria/assets/css/app.css
git commit -m "feat(eldoria): bouton copie IP dédié avec combo façon League of Legends"
```

---

### Task 29 : Composant toggle réutilisable

**Files:**
- Create: `eldoria/views/partials/_toggle-switch.blade.php`
- Modify: `eldoria/views/partials/customizer.blade.php`

**Interfaces:**
- Produces: `partials._toggle-switch` (partial Blade réutilisable, paramètres `$model` (string, nom de propriété Alpine), `$label` (string), `$onChange` (string optionnel, expression Alpine))

- [ ] **Step 1 : Créer `eldoria/views/partials/_toggle-switch.blade.php`**

```blade
<label class="flex items-center justify-between cursor-pointer">
    <span class="text-text-primary text-sm">{{ $label }}</span>
    <span class="relative inline-flex items-center flex-shrink-0">
        <input type="checkbox" class="peer sr-only" x-model="{{ $model }}"
               @if(isset($onChange)) @change="{{ $onChange }}" @endif>
        <span class="w-11 h-6 rounded-full bg-bg-primary border border-accent/30
                     peer-checked:bg-accent peer-checked:border-accent transition-colors duration-200"></span>
        <span class="absolute left-0.5 top-0.5 w-4 h-4 rounded-full bg-text-secondary
                     peer-checked:bg-bg-primary peer-checked:translate-x-5 transition-transform duration-200"></span>
    </span>
</label>
```

> `peer` + `peer-checked:` sont les classes Tailwind standard pour styler un élément en fonction de l'état `:checked` d'une case à cocher sœur — aucun JS supplémentaire n'est nécessaire au-delà du `x-model` déjà utilisé partout ailleurs dans le customizer.

- [ ] **Step 2 : Remplacer la case à cocher existante dans `eldoria/views/partials/customizer.blade.php`**

Remplacer :
```blade
                    <label class="flex items-center justify-between mt-3 cursor-pointer" x-show="trailerUrl">
                        <span class="text-text-primary text-sm">{{ __('theme::theme.customizer.hero_video_toggle') }}</span>
                        <input type="checkbox" x-model="heroVideoEnabled" @change="liveHeroVideo()"
                               class="w-4 h-4 accent-[var(--color-accent)]">
                    </label>
```
par :
```blade
                    <div class="mt-3" x-show="trailerUrl">
                        @include('partials._toggle-switch', ['model' => 'heroVideoEnabled', 'label' => __('theme::theme.customizer.hero_video_toggle'), 'onChange' => 'liveHeroVideo()'])
                    </div>
```

- [ ] **Step 3 : Build + vérification manuelle**

```bash
cd eldoria && npm run build
cd ../local/azuriom-test && php artisan view:clear
```

En admin, customizer → Contenu → renseigner un trailer YouTube pour faire apparaître le toggle "Utiliser le trailer en fond du hero". Le contrôle doit s'afficher comme une piste arrondie avec un curseur, pas une case à cocher classique. Cliquer dessus : le curseur doit glisser à droite et la piste devenir dorée ; le hero doit basculer sur le fond vidéo (comportement déjà existant, `liveHeroVideo()` inchangé). Re-cliquer : retour à l'état initial. Vérifier `storage/logs/laravel-*.log`.

- [ ] **Step 4 : Commit**

```bash
git add eldoria/views/partials/_toggle-switch.blade.php eldoria/views/partials/customizer.blade.php
git commit -m "feat(eldoria): composant toggle réutilisable, remplace la case à cocher du customizer"
```

---

### Task 30 : Réorganisation de la page de config Azuriom

**Files:**
- Modify: `eldoria/config/config.blade.php`

**Interfaces:**
- Consumes: `server_ip_display` (Task 27, clé de config déjà validée)

- [ ] **Step 1 : Ajouter la section "Serveur" et les séparateurs**

Remplacer :
```blade
                    <h6 class="mb-3">Contenu</h6>
```
par :
```blade
                    <hr class="my-4">

                    <h6 class="mb-3">Contenu</h6>
```

Remplacer :
```blade
                    <h6 class="mb-3">Équipe / Staff</h6>
                    <p class="text-muted small mb-3">Jusqu'à 8 membres. Le pseudo doit être un pseudo Minecraft valide (avatar via minotar.net).</p>
```
par :
```blade
                    <hr class="my-4">

                    <h6 class="mb-3">Serveur</h6>

                    <div class="mb-4">
                        <label for="serverIpDisplayInput" class="form-label">Adresse IP à afficher</label>
                        <input type="text" class="form-control @error('server_ip_display') is-invalid @enderror"
                               id="serverIpDisplayInput" name="server_ip_display" placeholder="play.eldoria.fr"
                               value="{{ old('server_ip_display', theme_config('server_ip_display')) }}">
                        <div class="form-text">
                            Utilisée par le bouton de copie rapide et le bouton "Rejoindre" du hero. Laisser vide
                            pour utiliser l'adresse du serveur configuré dans Azuriom (Serveurs → Afficher sur l'accueil).
                        </div>
                        @error('server_ip_display')
                        <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>

                    <hr class="my-4">

                    <h6 class="mb-3">Équipe / Staff</h6>
                    <p class="text-muted small mb-3">Jusqu'à 8 membres. Le pseudo doit être un pseudo Minecraft valide (avatar via minotar.net).</p>
```

Remplacer :
```blade
                    <h6 class="mb-3">Médias &amp; communauté</h6>
```
par :
```blade
                    <hr class="my-4">

                    <h6 class="mb-3">Médias &amp; communauté</h6>
```

Remplacer :
```blade
                    <h6 class="mb-3">Réseaux sociaux (footer)</h6>
```
par :
```blade
                    <hr class="my-4">

                    <h6 class="mb-3">Réseaux sociaux (footer)</h6>
```

- [ ] **Step 2 : Vérification manuelle**

```bash
cd ../local/azuriom-test && php artisan view:clear
```

Se connecter en admin, aller sur `/admin/themes/eldoria` (ou via le lien "Aller à l'admin" du customizer). Confirmer visuellement : une ligne de séparation entre chaque section (Couleurs / Contenu / Serveur / Équipe-Staff / Médias & communauté / Réseaux sociaux), la nouvelle section "Serveur" avec son champ, et que la sauvegarde du formulaire fonctionne toujours normalement (aucun champ existant renommé ni déplacé de section).

- [ ] **Step 3 : Commit**

```bash
git add eldoria/config/config.blade.php
git commit -m "feat(eldoria): réorganisation visuelle de la page de config admin + section Serveur"
```

---

### Task 31 : Avatar navbar + informations enrichies du profil

**Files:**
- Modify: `eldoria/views/partials/navbar.blade.php`
- Modify: `eldoria/views/profile/index.blade.php`

**Interfaces:**
- Consumes: `auth()->user()->getAvatar(int $size): string` (méthode native Azuriom, gère déjà le repli Steve), `auth()->user()->role->getBadgeStyle(): string` (méthode native Azuriom), `format_money()` (helper déjà utilisé ailleurs dans le thème)

- [ ] **Step 1 : Ajouter l'avatar dans la navbar desktop**

Dans `eldoria/views/partials/navbar.blade.php`, remplacer :
```blade
                @auth
                    <a href="{{ route('profile.index') }}"
                       class="text-text-secondary hover:text-accent text-sm transition-colors">
                        {{ auth()->user()->name }}
                    </a>
```
par :
```blade
                @auth
                    <a href="{{ route('profile.index') }}"
                       class="flex items-center gap-2 text-text-secondary hover:text-accent text-sm transition-colors">
                        <img src="{{ auth()->user()->getAvatar(32) }}" alt="{{ auth()->user()->name }}"
                             class="w-6 h-6 rounded-sm flex-shrink-0">
                        {{ auth()->user()->name }}
                    </a>
```

- [ ] **Step 2 : Ajouter l'avatar dans le drawer mobile**

Remplacer :
```blade
            @auth
                <a href="{{ route('profile.index') }}" class="block text-text-secondary hover:text-accent text-sm transition-colors">
                    {{ auth()->user()->name }}
                </a>
```
par :
```blade
            @auth
                <a href="{{ route('profile.index') }}" class="flex items-center gap-2 text-text-secondary hover:text-accent text-sm transition-colors">
                    <img src="{{ auth()->user()->getAvatar(32) }}" alt="{{ auth()->user()->name }}"
                         class="w-6 h-6 rounded-sm flex-shrink-0">
                    {{ auth()->user()->name }}
                </a>
```

- [ ] **Step 3 : Réécrire `eldoria/views/profile/index.blade.php`**

Remplacer tout le contenu du fichier par :
```blade
@extends('layouts.app')

@section('title', __('theme::theme.profile.title'))

@section('content')
<div class="min-h-screen px-4 py-24">
    <div class="max-w-3xl mx-auto space-y-6">

        <div class="text-center mb-8">
            <p class="text-accent text-xs font-display tracking-[0.4em] uppercase mb-2">✦ {{ __('theme::theme.profile.eyebrow') }} ✦</p>
            <h1 class="font-display text-3xl font-bold text-text-primary">{{ __('theme::theme.profile.title') }}</h1>
        </div>

        <div class="card-eldoria p-8">
            <div class="flex items-center gap-4 mb-8 pb-8 border-b border-accent/10">
                <img src="{{ auth()->user()->getAvatar(64) }}" alt="{{ auth()->user()->name }}"
                     class="w-16 h-16 rounded-sm flex-shrink-0">
                <div>
                    <div class="font-display text-text-primary text-lg font-semibold flex items-center gap-2 flex-wrap">
                        {{ auth()->user()->name }}
                        @if(auth()->user()->role)
                            <span class="px-2 py-0.5 rounded-sm text-xs font-display uppercase tracking-wide"
                                  style="{{ auth()->user()->role->getBadgeStyle() }}">
                                {{ auth()->user()->role->name }}
                            </span>
                        @endif
                    </div>
                    <div class="text-text-secondary text-sm">{{ auth()->user()->email }}</div>
                    @if(auth()->user()->email_verified_at === null)
                        <div class="text-red-400 text-xs mt-1">{{ __('theme::theme.profile.email_unverified') }}</div>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
                <div class="flex items-center justify-between sm:flex-col sm:items-start gap-1 p-3 bg-bg-primary/40 rounded-sm">
                    <span class="text-text-secondary uppercase tracking-widest text-xs">{{ __('theme::theme.profile.member_since') }}</span>
                    <span class="text-text-primary">{{ auth()->user()->created_at->format('d/m/Y') }}</span>
                </div>
                <div class="flex items-center justify-between sm:flex-col sm:items-start gap-1 p-3 bg-bg-primary/40 rounded-sm">
                    <span class="text-text-secondary uppercase tracking-widest text-xs">{{ __('theme::theme.profile.last_login_label') }}</span>
                    <span class="text-text-primary">{{ auth()->user()->last_login_at ? auth()->user()->last_login_at->format('d/m/Y à H:i') : __('theme::theme.profile.last_login_never') }}</span>
                </div>
                <div class="flex items-center justify-between sm:flex-col sm:items-start gap-1 p-3 bg-bg-primary/40 rounded-sm">
                    <span class="text-text-secondary uppercase tracking-widest text-xs">{{ __('theme::theme.profile.balance_label') }}</span>
                    <span class="text-accent font-display font-bold">{{ format_money(auth()->user()->money) }}</span>
                </div>
            </div>

            <div class="mt-8 pt-8 border-t border-accent/10 flex flex-col sm:flex-row gap-3">
                @if(Route::has('password.request'))
                <a href="{{ route('password.request') }}"
                   class="flex-1 text-center py-3 border border-accent/30 text-text-secondary hover:text-text-primary
                          text-sm font-display tracking-widest uppercase rounded-sm transition-colors min-h-[48px]
                          flex items-center justify-center">
                    {{ __('theme::theme.profile.change_password') }}
                </a>
                @endif
                <form method="POST" action="{{ route('logout') }}" class="flex-1">
                    @csrf
                    <button type="submit"
                            class="w-full py-3 bg-accent text-bg-primary font-display text-sm tracking-widest uppercase
                                   rounded-sm hover:bg-accent/90 transition-all min-h-[48px]">
                        {{ __('theme::theme.profile.logout') }}
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection
```

> Le bloc du visualiseur de skin 3D est délibérément absent de cette réécriture — il est ajouté par la Task 32, qui aura besoin d'y ajouter un `@push('scripts')`. Réécrire tout le fichier une seconde fois dans la Task 32 (plutôt qu'un simple ajout dessus) éviterait un diff confus ; la Task 32 utilisera donc un remplacement ciblé sur la fin du fichier, pas une réécriture complète.

- [ ] **Step 4 : Build + vérification manuelle**

```bash
cd eldoria && npm run build
cd ../local/azuriom-test && php artisan view:clear
```

Se connecter, vérifier que la navbar (desktop ET menu mobile) affiche une petite tête de skin avant le pseudo (skin Steve par défaut si aucun compte Minecraft lié). Aller sur `/profile` : vérifier la présence du badge de rôle à côté du pseudo (couleur cohérente avec le rôle Azuriom), des 3 nouvelles cartes d'info (inscription/dernière connexion/solde), et — si l'email n'est pas vérifié sur ce compte de test — du message d'alerte. Vérifier `storage/logs/laravel-*.log`.

- [ ] **Step 5 : Commit**

```bash
git add eldoria/views/partials/navbar.blade.php eldoria/views/profile/index.blade.php
git commit -m "feat(eldoria): avatar dans la navbar + informations enrichies sur la page profil"
```

---

### Task 32 : Visualiseur de skin 3D

**Files:**
- Modify: `eldoria/package.json`
- Modify: `eldoria/vite.config.js`
- Create: `eldoria/assets/js/profile.js`
- Modify: `eldoria/views/profile/index.blade.php`

**Interfaces:**
- Consumes: `auth()->user()->game_id` (UUID Minecraft, potentiellement `null`)

- [ ] **Step 1 : Ajouter la dépendance dans `eldoria/package.json`**

Remplacer :
```json
        "sortablejs": "^1.15.2"
    }
```
par :
```json
        "skinview3d": "^3.0.0",
        "sortablejs": "^1.15.2"
    }
```

- [ ] **Step 2 : Installer**

```bash
cd eldoria && npm install
```
Attendu : `node_modules/skinview3d` créé, `package-lock.json` mis à jour.

- [ ] **Step 3 : Ajouter le point d'entrée Vite dédié dans `eldoria/vite.config.js`**

Remplacer :
```js
            input: {
                app: resolve(__dirname, 'assets/js/app.js'),
                style: resolve(__dirname, 'assets/css/app.css'),
            },
```
par :
```js
            input: {
                app: resolve(__dirname, 'assets/js/app.js'),
                style: resolve(__dirname, 'assets/css/app.css'),
                profile: resolve(__dirname, 'assets/js/profile.js'),
            },
```

- [ ] **Step 4 : Créer `eldoria/assets/js/profile.js`**

```js
import { SkinViewer, IdleAnimation } from 'skinview3d'

function initSkinViewer() {
    const canvas = document.getElementById('skin-viewer-canvas')
    if (!canvas) return

    const viewer = new SkinViewer({
        canvas,
        width: 300,
        height: 400,
        skin: canvas.dataset.skinUrl,
    })

    viewer.controls.enableZoom = false

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches
    if (prefersReducedMotion) {
        viewer.autoRotate = false
        viewer.animation = null
    } else {
        viewer.autoRotate = true
        viewer.animation = new IdleAnimation()
    }
}

document.addEventListener('DOMContentLoaded', initSkinViewer)
```

> Ce fichier n'est PAS importé par `app.js` — c'est un point d'entrée Vite séparé (Step 3), donc son propre bundle autonome (`dist/profile.js`), chargé uniquement sur la page profil (Step 5). C'est pourquoi il s'auto-exécute sur `DOMContentLoaded` plutôt que d'exporter une fonction `initX()` consommée ailleurs, à la différence des autres modules JS du thème.

- [ ] **Step 5 : Ajouter le bloc canvas et le script dans `eldoria/views/profile/index.blade.php`**

Remplacer :
```blade
        </div>

    </div>
</div>
@endsection
```
par :
```blade
        </div>

        <div class="card-eldoria p-8">
            <h2 class="font-display text-accent text-sm tracking-widest uppercase mb-6 text-center">
                {{ __('theme::theme.profile.skin_3d_title') }}
            </h2>
            <div class="flex justify-center">
                <canvas id="skin-viewer-canvas"
                        data-skin-url="https://mc-heads.net/skin/{{ auth()->user()->game_id ?? 'c06f8906-4c8a-4911-9c29-ea1dbd1aab82' }}"
                        width="300" height="400"></canvas>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script type="module" src="{{ theme_asset('dist/profile.js') }}" defer></script>
@endpush
```

> `c06f8906-4c8a-4911-9c29-ea1dbd1aab82` est l'UUID de MHF_Steve, le même repli déjà utilisé par le cœur d'Azuriom (`game()->getAvatarUrl()`) quand un compte n'a pas de `game_id` — cohérence totale avec le comportement déjà en place pour les avatars 2D du thème.

- [ ] **Step 6 : Build + vérification manuelle**

```bash
cd eldoria && npm run build
cd ../local/azuriom-test && php artisan view:clear
```

Aller sur `/profile` : un skin 3D doit apparaître dans un nouveau bloc en bas de page, tournant automatiquement sur lui-même (skin Steve si le compte de test n'a pas de compte Minecraft lié). Vérifier via les outils navigateur que `dist/profile.js` n'est chargé QUE sur cette page (vérifier l'onglet Réseau sur `/` : `profile.js` ne doit pas y apparaître). Émuler `prefers-reduced-motion: reduce` et confirmer que la rotation automatique s'arrête (le skin reste affiché, immobile). Vérifier `storage/logs/laravel-*.log`.

- [ ] **Step 7 : Commit**

```bash
git add eldoria/package.json eldoria/package-lock.json eldoria/vite.config.js eldoria/assets/js/profile.js eldoria/views/profile/index.blade.php
git commit -m "feat(eldoria): visualiseur de skin 3D sur la page profil (skinview3d)"
```

---

### Task 33 : Traductions FR/EN + revue finale

**Files:**
- Modify: `eldoria/lang/fr/theme.php`
- Modify: `eldoria/lang/en/theme.php`

**Interfaces:**
- Consumes: toutes les clés `theme::theme.customizer.server_ip_*`, `theme::theme.home.ip_copy_*`, `theme::theme.profile.*` introduites dans les Tasks 27-32

- [ ] **Step 1 : Ajouter les clés dans `eldoria/lang/fr/theme.php`**

Dans le tableau `customizer`, ajouter après `'trailer_help' => "Lien YouTube du trailer — affiché sur l'accueil.",` :
```php
        'server_ip_label' => 'Adresse IP à afficher',
        'server_ip_placeholder' => 'play.eldoria.fr',
        'server_ip_help' => "Utilisée par le bouton de copie rapide et le bouton Rejoindre. Laisser vide pour utiliser l'adresse du serveur configuré dans Azuriom.",
```

Dans le tableau `home`, ajouter après `'server_offline' => 'Hors ligne',` :
```php
        'ip_copy_button' => "Copier l'adresse du serveur",
        'ip_copy_1' => 'IP copiée !',
        'ip_copy_2' => 'Double copie !',
        'ip_copy_3' => 'Triple copie !',
        'ip_copy_4' => 'Quadra copie !',
        'ip_copy_5' => 'PENTA COPIE !',
        'ip_copy_combo_1' => 'Domination !',
        'ip_copy_combo_2' => 'Massacre !',
        'ip_copy_combo_3' => 'Légendaire !',
```

Dans le tableau `profile`, ajouter après `'member_since' => 'Membre depuis',` :
```php
        'last_login_label' => 'Dernière connexion',
        'last_login_never' => 'Jamais',
        'balance_label' => 'Solde',
        'email_unverified' => 'Email non confirmé',
        'skin_3d_title' => 'Mon skin',
```

- [ ] **Step 2 : Ajouter les clés dans `eldoria/lang/en/theme.php`**

Dans le tableau `customizer`, ajouter après `'trailer_help' => 'YouTube link for the trailer — shown on the homepage.',` :
```php
        'server_ip_label' => 'IP address to display',
        'server_ip_placeholder' => 'play.eldoria.com',
        'server_ip_help' => 'Used by the quick-copy button and the Join button. Leave empty to use the server address configured in Azuriom.',
```

Dans le tableau `home`, ajouter après `'server_offline' => 'Offline',` :
```php
        'ip_copy_button' => 'Copy the server address',
        'ip_copy_1' => 'IP copied!',
        'ip_copy_2' => 'Double copy!',
        'ip_copy_3' => 'Triple copy!',
        'ip_copy_4' => 'Quadra copy!',
        'ip_copy_5' => 'PENTA COPY!',
        'ip_copy_combo_1' => 'Domination!',
        'ip_copy_combo_2' => 'Rampage!',
        'ip_copy_combo_3' => 'Legendary!',
```

Dans le tableau `profile`, ajouter après `'member_since' => 'Member since',` :
```php
        'last_login_label' => 'Last login',
        'last_login_never' => 'Never',
        'balance_label' => 'Balance',
        'email_unverified' => 'Email not confirmed',
        'skin_3d_title' => 'My skin',
```

- [ ] **Step 3 : Build final + vérification complète**

```bash
cd eldoria && npm run build
cd ../local/azuriom-test && php artisan view:clear
```

Vérifier en FR puis en EN (basculer la locale du site, voir méthode déjà utilisée dans les tâches précédentes) : aucune clé brute `theme::theme.*` visible pour le bouton IP, ses infobulles combo, le champ customizer, et les nouvelles infos du profil. Refaire un passage complet des 4 fonctionnalités (bouton IP + combo, toggle, page admin réorganisée, profil + skin 3D) une dernière fois pour confirmer qu'aucune régression n'a été introduite par les traductions.

- [ ] **Step 4 : Commit**

```bash
git add eldoria/lang/
git commit -m "feat(eldoria): traductions FR/EN pour le bouton IP, le toggle et le profil enrichi"
```

---

## Notes pour l'implémentation

1. **Ordre d'exécution** : Task 27 doit précéder la Task 28 (le badge dédié dépend du bouton "Rejoindre" déjà unifié et de l'événement `eldoria:ip-updated`). Tasks 29, 30, 31 sont indépendantes entre elles et de la Task 27/28 (aucun fichier partagé). La Task 32 dépend de la Task 31 (modifie le même fichier `profile/index.blade.php`, juste après). La Task 33 doit être la dernière (elle référence les clés de toutes les tâches précédentes).
2. **`data-msg1`..`data-msg5` sans tiret avant le chiffre** : c'est volontaire — un attribut `data-msg-1` (avec tiret) ne se convertirait PAS en `dataset.msg1` en JavaScript (la conversion camelCase ne s'applique qu'avant une lettre, pas un chiffre), il faudrait alors écrire `dataset['msg-1']`. Garder `data-msg1` évite ce piège.
3. **Aucun test automatisé** n'existe pour ce thème Blade/JS (convention déjà établie sur tout le projet) — la vérification se fait uniquement par build + test manuel décrits dans chaque tâche.
