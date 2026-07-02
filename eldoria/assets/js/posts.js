// Like AJAX (toggle) + confirmation avant suppression de commentaire.
// Le cœur d'Azuriom gère ça via son propre bundle JS, non chargé par notre
// thème (qui a son propre layout) — réimplémentation minimale en fetch().

function initPostLike() {
    const button = document.getElementById('post-like-button')
    if (!button) return

    button.addEventListener('click', () => {
        if (button.disabled) return

        const liked = button.dataset.liked === '1'
        const url = liked ? button.dataset.dislikeUrl : button.dataset.likeUrl
        const method = liked ? 'DELETE' : 'POST'

        button.disabled = true

        fetch(url, {
            method,
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
            },
        })
            .then((res) => {
                if (!res.ok) throw new Error('Like request failed')

                const icon = document.getElementById('post-like-icon')
                const count = document.getElementById('post-like-count')
                const newLiked = !liked

                button.dataset.liked = newLiked ? '1' : '0'
                if (icon) icon.textContent = newLiked ? '♥' : '♡'
                if (count) count.textContent = String(parseInt(count.textContent, 10) + (newLiked ? 1 : -1))
            })
            .catch((e) => console.error('Post like error:', e))
            .finally(() => { button.disabled = false })
    })
}

function initCommentDelete() {
    document.querySelectorAll('.comment-delete-form').forEach((form) => {
        form.addEventListener('submit', (ev) => {
            if (!window.confirm(form.dataset.confirmMessage)) {
                ev.preventDefault()
            }
        })
    })
}

export function initPosts() {
    initPostLike()
    initCommentDelete()
}
