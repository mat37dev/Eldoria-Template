import { SkinViewer, IdleAnimation } from 'skinview3d'

function initPodiumViewer(canvas) {
    const viewer = new SkinViewer({
        canvas,
        width: 160,
        height: 220,
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

function initVotePodium() {
    document.querySelectorAll('.podium-skin-canvas').forEach(initPodiumViewer)
}

document.addEventListener('DOMContentLoaded', initVotePodium)
