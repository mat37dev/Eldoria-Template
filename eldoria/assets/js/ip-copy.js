const COMBO_RESET_MS = 3000
const TOOLTIP_VISIBLE_MS = 1800

function initIpCopyBadge() {
    const btn = document.getElementById('btn-ip-copy')
    const tooltip = document.getElementById('ip-copy-tooltip')
    const valueEl = document.getElementById('ip-copy-value')
    if (!btn || !tooltip || !valueEl) return

    const messages = [btn.dataset.msg1, btn.dataset.msg2, btn.dataset.msg3, btn.dataset.msg4, btn.dataset.msg5]
    const comboMessages = btn.dataset.msgCombo.split('|')

    let clickCount = 0
    let lastClickTime = 0
    let hideTimeout = null

    btn.addEventListener('click', () => {
        const now = Date.now()
        clickCount = (now - lastClickTime > COMBO_RESET_MS) ? 1 : clickCount + 1
        lastClickTime = now

        navigator.clipboard.writeText(btn.dataset.ip || '')

        const message = clickCount <= messages.length
            ? messages[clickCount - 1]
            : comboMessages[(clickCount - messages.length - 1) % comboMessages.length]

        tooltip.textContent = message
        tooltip.classList.toggle('text-base', clickCount >= 5)
        tooltip.classList.toggle('font-bold', clickCount >= 5)

        clearTimeout(hideTimeout)
        tooltip.classList.remove('opacity-0')
        tooltip.classList.add('opacity-100')
        hideTimeout = setTimeout(() => {
            tooltip.classList.remove('opacity-100')
            tooltip.classList.add('opacity-0')
        }, TOOLTIP_VISIBLE_MS)
    })

    document.addEventListener('eldoria:ip-updated', (event) => {
        btn.dataset.ip = event.detail.ip
        valueEl.textContent = event.detail.ip
        const wrapper = btn.closest('[data-live-section="ip-copy-badge"]')
        if (wrapper) wrapper.classList.toggle('hidden', event.detail.ip === '')
    })
}

export function initIpCopy() {
    const joinBtn = document.getElementById('btn-join')
    if (joinBtn) {
        joinBtn.addEventListener('click', () => {
            navigator.clipboard.writeText(joinBtn.dataset.ip || '')
        })
    }

    initIpCopyBadge()
}
