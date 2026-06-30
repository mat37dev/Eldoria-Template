import { gsap } from 'gsap'
import { ScrollTrigger } from 'gsap/ScrollTrigger'

gsap.registerPlugin(ScrollTrigger)

const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches
const isMobile = window.innerWidth < 640

export function initAnimations() {
    if (prefersReducedMotion) return

    initParallax()
    initCounters()
}

function initParallax() {
    if (isMobile) return

    const heroBg = document.getElementById('hero-bg')
    if (!heroBg) return

    gsap.to(heroBg, {
        yPercent: 30,
        ease: 'none',
        scrollTrigger: {
            trigger: '#hero',
            start: 'top top',
            end: 'bottom top',
            scrub: true,
        }
    })
}

function initCounters() {
    const counters = document.querySelectorAll('[id^="counter-"]')
    if (!counters.length) return

    counters.forEach(counter => {
        const target = parseInt(counter.dataset.target) || 0

        ScrollTrigger.create({
            trigger: counter,
            start: 'top 90%',
            once: true,
            onEnter: () => {
                gsap.fromTo(
                    { val: 0 },
                    { val: target, duration: 1.5, ease: 'power2.out',
                      onUpdate: function() {
                          counter.textContent = Math.round(this.targets()[0].val).toLocaleString()
                      }
                    }
                )
            }
        })
    })
}
