export function initParticles() {
    const canvas = document.getElementById('particles-canvas')
    if (!canvas) return

    const ctx = canvas.getContext('2d')
    let particles = []
    let animationId

    function resize() {
        canvas.width = window.innerWidth
        canvas.height = window.innerHeight
    }

    function getAccentColor() {
        return getComputedStyle(document.documentElement)
            .getPropertyValue('--color-accent').trim() || '#C9A84C'
    }

    function hexToRgb(hex) {
        const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex)
        return result ? {
            r: parseInt(result[1], 16),
            g: parseInt(result[2], 16),
            b: parseInt(result[3], 16)
        } : { r: 201, g: 168, b: 76 }
    }

    function createParticle() {
        return {
            x: Math.random() * canvas.width,
            y: Math.random() * canvas.height,
            size: Math.random() * 2 + 0.5,
            speedX: (Math.random() - 0.5) * 0.3,
            speedY: -Math.random() * 0.5 - 0.2,
            opacity: Math.random() * 0.5 + 0.1,
            life: 0,
            maxLife: Math.random() * 300 + 200,
        }
    }

    function init() {
        resize()
        particles = Array.from({ length: 60 }, createParticle)
    }

    function draw() {
        ctx.clearRect(0, 0, canvas.width, canvas.height)
        const accent = hexToRgb(getAccentColor())

        particles.forEach((p, i) => {
            p.x += p.speedX
            p.y += p.speedY
            p.life++

            const lifeRatio = p.life / p.maxLife
            const currentOpacity = p.opacity * (1 - lifeRatio)

            ctx.beginPath()
            ctx.arc(p.x, p.y, p.size, 0, Math.PI * 2)
            ctx.fillStyle = `rgba(${accent.r}, ${accent.g}, ${accent.b}, ${currentOpacity})`
            ctx.fill()

            if (p.life >= p.maxLife || p.y < -10) {
                particles[i] = createParticle()
                particles[i].y = canvas.height + 10
            }
        })

        animationId = requestAnimationFrame(draw)
    }

    // Respecter prefers-reduced-motion
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return

    init()
    draw()
    window.addEventListener('resize', resize)
}
