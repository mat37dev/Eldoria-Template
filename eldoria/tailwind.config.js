/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './views/**/*.blade.php',
    ],
    theme: {
        extend: {
            colors: {
                'bg-primary': 'var(--color-bg-primary)',
                'bg-secondary': 'var(--color-bg-secondary)',
                'accent': 'var(--color-accent)',
                'accent-secondary': 'var(--color-accent-secondary)',
                'text-primary': 'var(--color-text-primary)',
                'text-secondary': 'var(--color-text-secondary)',
            },
            fontFamily: {
                'display': ['Cinzel', 'serif'],
                'body': ['Inter', 'sans-serif'],
            },
        },
    },
    plugins: [],
}
