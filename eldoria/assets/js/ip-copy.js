export function initIpCopy() {
    const joinBtn = document.getElementById('btn-join')
    if (joinBtn) {
        joinBtn.addEventListener('click', () => {
            navigator.clipboard.writeText(joinBtn.dataset.ip || '')
        })
    }
}
