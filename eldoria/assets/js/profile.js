import { SkinViewer, IdleAnimation } from 'skinview3d'

// Skin de secours si le pseudo/UUID du joueur n'a pas de skin connu chez Mojang
// (ex. compte hors-ligne sans vérification premium) : loadSkin() rejette sa
// promesse dans ce cas, on retombe alors sur ce skin par défaut plutôt que de
// laisser le viewer vide.
const FALLBACK_SKIN_URL = 'https://minotar.net/skin/MHF_Steve'

function initSkinViewer() {
    const canvas = document.getElementById('skin-viewer-canvas')
    if (!canvas) return

    const viewer = new SkinViewer({ canvas, width: 300, height: 400 })

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

document.addEventListener('DOMContentLoaded', initSkinViewer)
