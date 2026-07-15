import Sortable from 'sortablejs'

const PALETTES = [
    { name: 'Eldoria',       accent: '#C9A84C', secondary: '#7B3F2E' },
    { name: 'Forêt Sombre',  accent: '#4A7C59', secondary: '#2D4A1E' },
    { name: 'Abysses',       accent: '#3A6EA8', secondary: '#1A3A5C' },
    { name: 'Volcan',        accent: '#C0392B', secondary: '#7D2B1A' },
    { name: 'Givre',         accent: '#7EC8D8', secondary: '#2A5A6E' },
]

export function ytVideoId(url) {
    const match = (url || '').match(/(?:youtube\.com\/(?:watch\?v=|embed\/|shorts\/)|youtu\.be\/)([\w-]{11})/)
    return match ? match[1] : null
}

export function customizerComponent(initial = {}) {
    return {
        open: false,
        saving: false,
        saved: false,
        saveError: false,
        saveErrorMessage: '',
        activeTab: 'colors',
        sortableInstance: null,
        accent: getComputedStyle(document.documentElement).getPropertyValue('--color-accent').trim() || '#C9A84C',
        accentSecondary: getComputedStyle(document.documentElement).getPropertyValue('--color-accent-secondary').trim() || '#7B3F2E',
        palettes: PALETTES,

        // Contenu éditable — initialisé depuis la config serveur (voir customizer.blade.php)
        homeLayout: initial.homeLayout ?? [],
        editingSection: null,
        sectionTextOverrides: initial.sectionTextOverrides ?? {
            join_steps: { title: '', subtitle: '', steps: [{ title: '', text: '' }, { title: '', text: '' }, { title: '', text: '' }] },
            trailer: { title: '', subtitle: '' },
        },
        slogan: initial.slogan ?? '',
        heroImage: initial.heroImage ?? '',
        trailerUrl: initial.trailerUrl ?? '',
        serverIpDisplay: initial.serverIpDisplay ?? '',
        heroVideoEnabled: initial.heroVideoEnabled ?? false,
        discordId: initial.discordId ?? '',
        footerDiscord: initial.footerDiscord ?? '',
        footerTwitter: initial.footerTwitter ?? '',

        applyColors(accent, secondary) {
            this.accent = accent
            this.accentSecondary = secondary
            document.documentElement.style.setProperty('--color-accent', accent)
            document.documentElement.style.setProperty('--color-accent-secondary', secondary)
        },

        applyPalette(palette) {
            this.applyColors(palette.accent, palette.secondary)
        },

        // ===== Live preview : la page reflète chaque champ sans rechargement =====

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
            const heroBg = document.getElementById('hero-bg')
            if (!heroBg) return
            heroBg.style.backgroundImage = `url('${this.heroImage || heroBg.dataset.defaultImage}')`
        },

        liveSection(key, visible) {
            document.querySelectorAll(`[data-live-section="${key}"]`)
                .forEach(el => el.classList.toggle('hidden', !visible))
        },

        liveTrailer() {
            const id = ytVideoId(this.trailerUrl)
            document.querySelectorAll('[data-trailer-iframe]')
                .forEach(f => { f.src = id ? `https://www.youtube-nocookie.com/embed/${id}` : '' })
            this.liveSection('trailer', id !== null)
        },

        liveHeroVideo() {
            const heroBg = document.getElementById('hero-bg')
            const heroVideoContainer = document.getElementById('hero-video-container')
            if (!heroBg || !heroVideoContainer) return

            const id = ytVideoId(this.trailerUrl)
            const shouldShowVideo = this.heroVideoEnabled && id !== null

            heroBg.classList.toggle('hidden', shouldShowVideo)
            heroVideoContainer.classList.toggle('hidden', !shouldShowVideo)

            if (shouldShowVideo) {
                const iframe = heroVideoContainer.querySelector('iframe')
                if (iframe) {
                    iframe.src = `https://www.youtube-nocookie.com/embed/${id}?autoplay=1&mute=1&loop=1&controls=0&playlist=${id}&modestbranding=1&playsinline=1`
                }
            }
        },

        liveDiscord() {
            const id = (this.discordId || '').trim()
            document.querySelectorAll('[data-discord-iframe]')
                .forEach(f => { f.src = id ? `https://discord.com/widget?id=${id}&theme=dark` : '' })
            this.liveSection('discord', id !== '')
        },

        liveFooterLink(key, url) {
            document.querySelectorAll(`[data-live-href="${key}"]`).forEach(a => {
                a.href = url || '#'
                a.classList.toggle('hidden', !url)
            })
        },

        buildHomeLayoutPayload() {
            const container = this.findSectionsContainer()
            if (!container) return null

            const domEntries = Array.from(container.querySelectorAll('[data-section-key]')).map((section) => {
                const key = section.dataset.sectionKey
                const visible = !section.classList.contains('section-manually-hidden')
                const entry = { key, visible }

                if (key === 'join_steps') {
                    entry.title = this.sectionTextOverrides.join_steps.title
                    entry.subtitle = this.sectionTextOverrides.join_steps.subtitle
                    entry.steps = this.sectionTextOverrides.join_steps.steps
                }

                if (key === 'trailer') {
                    entry.title = this.sectionTextOverrides.trailer.title
                    entry.subtitle = this.sectionTextOverrides.trailer.subtitle
                }

                return entry
            })

            // Une section sans contenu (ex: "staff" sans membre configuré, "news"
            // sans article) n'apparaît pas dans le DOM et serait sinon absente du
            // JSON envoyé — le repli de home.blade.php exige les 8 clés et
            // rejetterait alors TOUT le home_layout. On complète avec le dernier
            // état connu de ces sections, ajouté à la fin.
            const domKeys = domEntries.map((entry) => entry.key)
            const missingEntries = this.homeLayout.filter((entry) => !domKeys.includes(entry.key))

            return [...domEntries, ...missingEntries]
        },

        async save() {
            this.saving = true
            try {
                const formData = new FormData()
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').content)
                // Mode "append" : fusionne dans la config existante au lieu de la
                // remplacer — les champs absents de ce formulaire sont préservés.
                formData.append('append', '1')

                formData.append('color_accent', this.accent)
                formData.append('color_accent_secondary', this.accentSecondary)
                formData.append('hero_slogan', this.slogan)
                formData.append('hero_image', this.heroImage)
                formData.append('trailer_url', this.trailerUrl)
                formData.append('server_ip_display', this.serverIpDisplay)
                formData.append('hero_video_enabled', this.heroVideoEnabled ? '1' : '0')
                formData.append('discord_server_id', this.discordId)
                formData.append('footer_discord', this.footerDiscord)
                formData.append('footer_twitter', this.footerTwitter)

                const homeLayoutPayload = this.buildHomeLayoutPayload()
                if (homeLayoutPayload !== null) {
                    formData.append('home_layout', JSON.stringify(homeLayoutPayload))
                }

                const response = await fetch(this.$root.dataset.saveUrl, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
                    body: formData,
                })

                if (!response.ok) {
                    // Laravel renvoie le détail de la validation en JSON (422) — on
                    // l'affiche tel quel plutôt qu'un "Erreur" générique sans piste.
                    const data = await response.json().catch(() => null)
                    const firstError = data?.errors ? Object.values(data.errors)[0]?.[0] : null
                    throw new Error(firstError || data?.message || `Échec de la sauvegarde (${response.status})`)
                }

                this.saved = true
                setTimeout(() => { this.saved = false }, 3000)
            } catch (e) {
                this.saveError = true
                this.saveErrorMessage = e.message || 'Erreur inconnue'
                setTimeout(() => { this.saveError = false }, 6000)
                console.error('Customizer save error:', e)
            } finally {
                this.saving = false
            }
        },

        cancel() {
            // Recharger la page pour revenir à l'état sauvegardé
            window.location.reload()
        },

        // ===== Mode réorganisation de la homepage (onglet Disposition) =====

        init() {
            // body.reorder-mode dérive d'un seul prédicat (drawer ouvert ET onglet
            // Disposition) surveillé sur les deux propriétés — un watcher par champ
            // laissait un angle mort : rouvrir le drawer avec l'onglet Disposition
            // déjà actif ne re-déclenchait jamais le watcher d'activeTab (valeur
            // inchangée), et le mode restait éteint sans moyen de le rallumer.
            this.$watch('activeTab', () => this.syncReorderMode())
            this.$watch('open', () => this.syncReorderMode())
        },

        syncReorderMode() {
            document.body.classList.toggle('reorder-mode', this.open && this.activeTab === 'layout')
        },

        enterLayoutTab() {
            this.activeTab = 'layout'
            this.syncReorderMode()
            this.$nextTick(() => this.initSortable())
        },

        initSortable() {
            const container = this.findSectionsContainer()
            if (!container || this.sortableInstance) return

            // L'état persisté "visible: false" arrive du serveur via la classe
            // `hidden`, mais l'éditeur (œil + sauvegarde) travaille sur
            // `section-manually-hidden`. Sans cette synchro à la première entrée,
            // une section masquée puis rechargée serait relue comme visible et
            // ré-affichée silencieusement à la prochaine sauvegarde.
            this.homeLayout.forEach((entry) => {
                if (entry.visible !== false) return
                const section = container.querySelector(`[data-section-key="${entry.key}"]`)
                if (!section) return
                section.classList.add('section-manually-hidden')
                const toggle = section.querySelector('.section-visibility-toggle')
                if (toggle) {
                    toggle.querySelector('.eye-visible').classList.add('hidden')
                    toggle.querySelector('.eye-hidden').classList.remove('hidden')
                }
            })

            this.sortableInstance = Sortable.create(container, {
                handle: '.drag-handle',
                animation: 150,
                onEnd: () => {},
            })

            container.querySelectorAll('[data-section-key]').forEach((section) => {
                const toggle = section.querySelector('.section-visibility-toggle')
                if (toggle && !toggle.dataset.bound) {
                    toggle.dataset.bound = 'true'
                    toggle.addEventListener('click', () => {
                        const isHidden = section.classList.toggle('section-manually-hidden')
                        toggle.querySelector('.eye-visible').classList.toggle('hidden', isHidden)
                        toggle.querySelector('.eye-hidden').classList.toggle('hidden', !isHidden)
                    })
                }

                const editBtn = section.querySelector('.edit-text-toggle')
                if (editBtn && !editBtn.dataset.bound) {
                    editBtn.dataset.bound = 'true'
                    editBtn.addEventListener('click', () => {
                        this.editingSection = section.dataset.sectionKey
                    })
                }
            })
        },

        backToLayoutList() {
            this.editingSection = null
        },

        liveJoinStepsText() {
            const o = this.sectionTextOverrides.join_steps
            const section = document.querySelector('[data-section-key="join_steps"]')
            if (!section) return

            const titleEl = section.querySelector('.section-title')
            const subtitleEl = section.querySelector('.section-subtitle')
            if (titleEl) titleEl.textContent = o.title || titleEl.dataset.defaultText
            if (subtitleEl) subtitleEl.textContent = o.subtitle || subtitleEl.dataset.defaultText

            const stepCards = section.querySelectorAll('.card-eldoria')
            o.steps.forEach((step, i) => {
                const card = stepCards[i]
                if (!card) return
                const stepTitleEl = card.querySelector('h3')
                const stepTextEl = card.querySelector('p')
                if (stepTitleEl) stepTitleEl.textContent = step.title || stepTitleEl.dataset.defaultText
                if (stepTextEl) stepTextEl.textContent = step.text || stepTextEl.dataset.defaultText
            })
        },

        liveTrailerSectionText() {
            const o = this.sectionTextOverrides.trailer
            const section = document.querySelector('[data-section-key="trailer"]')
            if (!section) return

            const titleEl = section.querySelector('.section-title')
            const subtitleEl = section.querySelector('.section-subtitle')
            if (titleEl) titleEl.textContent = o.title || titleEl.dataset.defaultText
            if (subtitleEl) subtitleEl.textContent = o.subtitle || subtitleEl.dataset.defaultText
        },

        findSectionsContainer() {
            const firstSection = document.querySelector('[data-section-key]')
            return firstSection ? firstSection.parentElement : null
        },
    }
}
