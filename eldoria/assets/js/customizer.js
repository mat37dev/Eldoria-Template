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
        slogan: initial.slogan ?? '',
        heroImage: initial.heroImage ?? '',
        showShop: initial.showShop ?? true,
        showVote: initial.showVote ?? true,
        trailerUrl: initial.trailerUrl ?? '',
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
                formData.append('show_section_shop', this.showShop ? '1' : '0')
                formData.append('show_section_vote', this.showVote ? '1' : '0')
                formData.append('trailer_url', this.trailerUrl)
                formData.append('hero_video_enabled', this.heroVideoEnabled ? '1' : '0')
                formData.append('discord_server_id', this.discordId)
                formData.append('footer_discord', this.footerDiscord)
                formData.append('footer_twitter', this.footerTwitter)

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
            // body.reorder-mode ne doit exister QUE pendant que l'onglet Disposition
            // est actif ET le drawer ouvert — jamais laissé "collé" après un changement
            // d'onglet ou une fermeture du drawer.
            this.$watch('activeTab', (value) => {
                document.body.classList.toggle('reorder-mode', value === 'layout')
            })
            this.$watch('open', (value) => {
                if (!value) document.body.classList.remove('reorder-mode')
            })
        },

        enterLayoutTab() {
            this.activeTab = 'layout'
            this.$nextTick(() => this.initSortable())
        },

        initSortable() {
            const container = this.findSectionsContainer()
            if (!container || this.sortableInstance) return

            this.sortableInstance = Sortable.create(container, {
                handle: '.drag-handle',
                animation: 150,
                onEnd: () => {},
            })

            container.querySelectorAll('[data-section-key]').forEach((section) => {
                const toggle = section.querySelector('.section-visibility-toggle')
                if (!toggle || toggle.dataset.bound) return
                toggle.dataset.bound = 'true'

                toggle.addEventListener('click', () => {
                    const isHidden = section.classList.toggle('section-manually-hidden')
                    toggle.querySelector('.eye-visible').classList.toggle('hidden', isHidden)
                    toggle.querySelector('.eye-hidden').classList.toggle('hidden', !isHidden)
                })
            })
        },

        findSectionsContainer() {
            const firstSection = document.querySelector('[data-section-key]')
            return firstSection ? firstSection.parentElement : null
        },
    }
}
