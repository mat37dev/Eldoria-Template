import { SkinViewer, IdleAnimation } from 'skinview3d'

// Voir profile.js : skin de secours si loadSkin() échoue (pseudo/UUID inconnu
// de Mojang, courant en mode hors-ligne pour un joueur sans compte premium).
const FALLBACK_SKIN_URL = 'https://minotar.net/skin/MHF_Steve'

function initPodiumViewer(canvas) {
    const viewer = new SkinViewer({ canvas, width: 160, height: 220 })

    viewer.loadSkin(canvas.dataset.skinUrl).catch(() => viewer.loadSkin(FALLBACK_SKIN_URL))

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

function initVotePodium() {
    document.querySelectorAll('.podium-skin-canvas').forEach(initPodiumViewer)
}

document.addEventListener('DOMContentLoaded', initVotePodium)
