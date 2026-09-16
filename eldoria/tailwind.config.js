/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './views/**/*.blade.php',
    ],
    theme: {
        extend: {
            colors: {
                // Format rgb(var(--x-rgb) / <alpha-value>) plutôt qu'un simple var(--x) :
                // c'est le seul format que Tailwind sait moduler avec les classes
                // d'opacité (bg-accent/10, border-accent/20, etc.) — un var() opaque
                // pointant vers une chaîne hex ne peut pas recevoir de canal alpha.
                'bg-primary': 'rgb(var(--color-bg-primary-rgb) / <alpha-value>)',
                'bg-secondary': 'rgb(var(--color-bg-secondary-rgb) / <alpha-value>)',
                'accent': 'rgb(var(--color-accent-rgb) / <alpha-value>)',
                'accent-secondary': 'rgb(var(--color-accent-secondary-rgb) / <alpha-value>)',
                'text-primary': 'rgb(var(--color-text-primary-rgb) / <alpha-value>)',
                'text-secondary': 'rgb(var(--color-text-secondary-rgb) / <alpha-value>)',
            },
            fontFamily: {
                'display': ['Cinzel', 'serif'],
                'body': ['Inter', 'sans-serif'],
            },
        },
    },
    plugins: [require('@tailwindcss/typography')],
}
