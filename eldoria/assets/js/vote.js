// Réimplémentation vanilla-JS (sans axios) du flux de vote officiel du plugin Vote,
// avec le même contrat AJAX (routes vote.verify-user / vote.done) mais l'habillage Eldoria.

function voteToggleStep(step) {
    document.querySelectorAll('[data-vote-step]').forEach((el) => el.classList.add('hidden'))

    const current = document.querySelector(`[data-vote-step="${step}"]`)
    if (current) {
        current.classList.remove('hidden')
    }
}

function voteClearAlert() {
    const el = document.getElementById('vote-status-message')
    if (el) el.innerHTML = ''
}

function voteDisplayAlert(message, level) {
    const el = document.getElementById('vote-status-message')
    if (!el) return

    const colors = {
        success: 'bg-accent/10 border-accent/40 text-accent',
        danger: 'bg-red-500/10 border-red-500/40 text-red-400',
        info: 'bg-bg-primary border-accent/20 text-text-secondary',
    }

    el.innerHTML = `<div class="border rounded-sm px-4 py-3 text-sm mt-4 ${colors[level] || colors.info}">${message}</div>`
}

function voteCatchError(error) {
    voteDisplayAlert(error?.message || String(error), 'danger')
}

function voteGetTimeDifference(date) {
    const difference = date - Date.now()
    const hours = Math.floor(difference / (1000 * 60 * 60))
    const minutes = Math.floor((difference % (1000 * 60 * 60)) / (1000 * 60))
    const seconds = Math.floor((difference % (1000 * 60)) / 1000)

    return (hours < 10 ? '0' : '') + hours
        + ':' + (minutes < 10 ? '0' : '') + minutes
        + ':' + (seconds < 10 ? '0' : '') + seconds
}

function voteUpdateLink(link) {
    const nextVoteTime = link.dataset.voteTime
    const timerEl = link.querySelector('.vote-timer')

    if (!nextVoteTime) return

    if (nextVoteTime > Date.now()) {
        if (timerEl) timerEl.innerText = voteGetTimeDifference(nextVoteTime)
    } else {
        link.classList.remove('opacity-40', 'pointer-events-none')
        if (timerEl) timerEl.innerText = ''
        link.removeAttribute('data-vote-time')
    }
}

const voteDoneCallbacks = []

function voteUpdateGoalProgress(goal) {
    const container = document.getElementById('vote-goal')
    if (!container || !goal || goal.target <= 0) return

    const bar = container.querySelector('[data-goal-bar]')
    const text = container.querySelector('#vote-goal-text')

    if (bar) bar.style.width = Math.min(Math.round((goal.progress / goal.target) * 100), 100) + '%'
    if (text) text.textContent = goal.text
}

function voteInit() {
    document.querySelectorAll('[data-vote-url]').forEach((el) => {
        const voteTime = el.dataset.voteTime
        const url = el.getAttribute('href')

        if (voteTime && voteTime > Date.now()) {
            el.classList.add('opacity-40', 'pointer-events-none')
            voteUpdateLink(el)

            const timer = setInterval(() => voteUpdateLink(el), 1000)
            voteDoneCallbacks.push(() => clearInterval(timer))
        }

        if (url.includes('{player}')) {
            el.setAttribute('href', url.replace('{player}', window.eldoriaVoteUsername))
        }

        const clickListener = (ev) => {
            const middleClickCode = 1
            if (ev.type === 'auxclick' && ev.button !== middleClickCode) return

            if ((voteTime && voteTime > Date.now()) || el.classList.contains('opacity-40')) {
                ev.preventDefault()
                return
            }

            voteClearAlert()
            el.classList.add('opacity-40', 'pointer-events-none')
            document.getElementById('vote-card')?.classList.add('is-voting')

            voteRefresh(el.dataset.voteUrl)
        }

        el.addEventListener('click', clickListener)
        el.addEventListener('auxclick', clickListener)
        voteDoneCallbacks.push(() => {
            el.removeEventListener('click', clickListener)
            el.removeEventListener('auxclick', clickListener)
        })
    })
}

function voteSetupTimers(name) {
    const form = document.getElementById('voteNameForm')
    const loader = form?.querySelector('.vote-load-spinner')
    loader?.classList.remove('hidden')

    const url = form.dataset.verifyUrlTemplate.replace('__USER__', encodeURIComponent(name))

    fetch(url, { headers: { Accept: 'application/json' } })
        .then((res) => {
            if (!res.ok) return res.json().then((data) => Promise.reject(new Error(data.message || 'Erreur')))
            return res.json()
        })
        .then((data) => {
            voteToggleStep(2)
            window.eldoriaVoteUsername = name

            for (const id in data.sites) {
                const el = document.querySelector(`[data-vote-id="${id}"]`)
                if (el && data.sites[id]) {
                    el.classList.remove('opacity-40', 'pointer-events-none')
                    el.setAttribute('data-vote-time', data.sites[id])
                }
            }

            voteUpdateGoalProgress(data.goal)
            voteInit()
        })
        .catch(voteCatchError)
        .finally(() => loader?.classList.add('hidden'))
}

function voteRefresh(url) {
    setTimeout(() => {
        fetch(`${url}/done`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
            },
            body: JSON.stringify({ user: window.eldoriaVoteUsername }),
        })
            .then((res) => res.json().then((data) => (res.ok ? data : Promise.reject(new Error(data.message || 'Erreur')))))
            .then((data) => {
                if (data.status === 'pending') {
                    voteRefresh(url)
                    return
                }

                document.getElementById('vote-card')?.classList.remove('is-voting')

                if (data.status === 'select_server') {
                    voteShowServerSelect(url, data.servers)
                    return
                }

                voteRewardDelivered(data.message)
            })
            .catch((error) => {
                document.getElementById('vote-card')?.classList.remove('is-voting')
                voteCatchError(error)
            })
    }, 5000)
}

function voteRewardDelivered(message) {
    voteDisplayAlert(message, 'success')
    voteDoneCallbacks.forEach((cb) => cb())
    voteSetupTimers(window.eldoriaVoteUsername)
}

function voteShowServerSelect(baseUrl, servers) {
    const container = document.getElementById('vote-server-select')
    if (!container) return

    container.innerHTML = ''

    Object.entries(servers).forEach(([serverId, serverName]) => {
        const button = document.createElement('button')
        button.type = 'button'
        button.className = 'btn-primary w-full mb-2'
        button.innerText = serverName

        button.addEventListener('click', () => {
            document.getElementById('vote-card')?.classList.add('is-voting')

            fetch(`${baseUrl}/done`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                },
                body: JSON.stringify({ user: window.eldoriaVoteUsername, server: serverId }),
            })
                .then((res) => res.json().then((data) => (res.ok ? data : Promise.reject(new Error(data.message || 'Erreur')))))
                .then((data) => {
                    voteRewardDelivered(data.message)
                    container.innerHTML = ''
                })
                .catch(voteCatchError)
                .finally(() => document.getElementById('vote-card')?.classList.remove('is-voting'))
        })

        container.appendChild(button)
    })

    voteToggleStep('server')
}

export function initVotePage() {
    const voteNameForm = document.getElementById('voteNameForm')

    if (voteNameForm) {
        voteNameForm.addEventListener('submit', (ev) => {
            ev.preventDefault()
            const name = document.getElementById('stepNameInput').value
            voteClearAlert()
            voteSetupTimers(name)
        })
    }

    if (window.eldoriaVoteUsername) {
        voteInit()
    }
}
