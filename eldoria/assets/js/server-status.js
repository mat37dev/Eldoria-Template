// Poll GET /api/servers (endpoint public d'Azuriom, aucune authentification)
// toutes les 30s pour mettre à jour le compteur de joueurs en ligne et la
// pastille de statut du hero, sans re-déclencher l'animation de comptage.

const POLL_INTERVAL_MS = 30000

function applyServerStatus(servers) {
    const online = servers.some((s) => s.online)
    const totalPlayers = servers.reduce((sum, s) => sum + (s.online ? (s.players ?? 0) : 0), 0)

    const dot = document.getElementById('server-status-dot')
    if (dot) {
        dot.classList.toggle('bg-green-500', online)
        dot.classList.toggle('bg-red-500', !online)
        dot.setAttribute('title', online
            ? dot.dataset.onlineLabel
            : dot.dataset.offlineLabel)
    }

    const counter = document.getElementById('counter-online')
    if (counter) {
        // Le premier appel met juste à jour data-target avant que l'animation
        // de comptage (animations.js) ne se déclenche au scroll. Les appels
        // suivants (après que l'animation a déjà tourné une fois) écrivent
        // directement le texte, sans recompter depuis 0.
        const alreadyAnimated = counter.dataset.animated === 'true'
        counter.dataset.target = String(totalPlayers)

        if (alreadyAnimated) {
            counter.textContent = String(totalPlayers)
        }
    }
}

function fetchServerStatus() {
    fetch('/api/servers', { headers: { Accept: 'application/json' } })
        .then((res) => res.json())
        .then((data) => applyServerStatus(data.servers ?? []))
        .catch((e) => console.error('Server status fetch error:', e))
}

export function initServerStatus() {
    if (!document.getElementById('counter-online') && !document.getElementById('server-status-dot')) {
        return
    }

    fetchServerStatus()
    setInterval(fetchServerStatus, POLL_INTERVAL_MS)
}
