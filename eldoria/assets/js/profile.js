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
