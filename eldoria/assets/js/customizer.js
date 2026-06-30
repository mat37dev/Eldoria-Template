const PALETTES = [
    { name: 'Eldoria',       accent: '#C9A84C', secondary: '#7B3F2E' },
    { name: 'Forêt Sombre',  accent: '#4A7C59', secondary: '#2D4A1E' },
    { name: 'Abysses',       accent: '#3A6EA8', secondary: '#1A3A5C' },
    { name: 'Volcan',        accent: '#C0392B', secondary: '#7D2B1A' },
    { name: 'Givre',         accent: '#7EC8D8', secondary: '#2A5A6E' },
]

export function customizerComponent() {
    return {
        open: false,
        saving: false,
        saved: false,
        saveError: false,
        activeTab: 'colors',
        accent: getComputedStyle(document.documentElement).getPropertyValue('--color-accent').trim() || '#C9A84C',
        accentSecondary: getComputedStyle(document.documentElement).getPropertyValue('--color-accent-secondary').trim() || '#7B3F2E',
        palettes: PALETTES,

        applyColors(accent, secondary) {
            this.accent = accent
            this.accentSecondary = secondary
            document.documentElement.style.setProperty('--color-accent', accent)
            document.documentElement.style.setProperty('--color-accent-secondary', secondary)
        },

        applyPalette(palette) {
            this.applyColors(palette.accent, palette.secondary)
        },

        async save() {
            this.saving = true
            try {
                const formData = new FormData()
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').content)
                formData.append('color_accent', this.accent)
                formData.append('color_accent_secondary', this.accentSecondary)

                // Récupérer les valeurs des toggles de sections
                const toggles = this.$root.querySelectorAll('[data-setting]')
                toggles.forEach(toggle => {
                    formData.append(toggle.dataset.setting, toggle.checked ? '1' : '0')
                })

                // Récupérer le slogan
                const sloganInput = this.$root.querySelector('[data-setting-slogan]')
                if (sloganInput) {
                    formData.append('hero_slogan', sloganInput.value)
                }

                const response = await fetch('/admin/settings/theme', {
                    method: 'POST',
                    body: formData,
                })

                if (!response.ok) throw new Error('Save failed')

                this.saved = true
                setTimeout(() => { this.saved = false }, 3000)
            } catch (e) {
                this.saveError = true
                setTimeout(() => { this.saveError = false }, 4000)
                console.error('Customizer save error:', e)
            } finally {
                this.saving = false
            }
        },

        cancel() {
            // Recharger la page pour revenir à l'état sauvegardé
            window.location.reload()
        }
    }
}
