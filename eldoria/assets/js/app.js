import Alpine from 'alpinejs'
import persist from '@alpinejs/persist'
import AOS from 'aos'
import 'aos/dist/aos.css'
import { initAnimations } from './animations.js'
import { initParticles } from './particles.js'
import { customizerComponent } from './customizer.js'
import { initVotePage } from './vote.js'
import { initPosts } from './posts.js'
import { initServerStatus } from './server-status.js'
import { initIpCopy } from './ip-copy.js'

window.Alpine = Alpine
Alpine.plugin(persist)
Alpine.data('customizer', customizerComponent)
Alpine.start()

document.addEventListener('DOMContentLoaded', () => {
    AOS.init({
        duration: 700,
        easing: 'ease-out-cubic',
        once: true,
        offset: 60,
        disable: () => window.matchMedia('(prefers-reduced-motion: reduce)').matches,
    })

    const isMobile = window.innerWidth < 640
    if (!isMobile) {
        initParticles()
    }

    initAnimations()
    initVotePage()
    initPosts()
    initServerStatus()
    initIpCopy()
})
