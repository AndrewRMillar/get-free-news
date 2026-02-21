/** @type {import('tailwindcss').Config} */
import typography from '@tailwindcss/typography';

export default {
    darkMode: 'class',

    content: [
        "./**/*.php",
        "!./node_modules/**/*.*"
    ],

    theme: {
        extend: {
            typography: {
                DEFAULT: {
                    css: {
                        maxWidth: '800px',
                        color: 'inherit', // belangrijk voor dark mode
                        a: {
                            color: '#1d4ed8',
                            textDecoration: 'underline',
                        },
                        h1: {
                            fontWeight: '700',
                        },
                        img: {
                            marginTop: '1.5em',
                            marginBottom: '1.5em',
                        },
                    },
                },
                invert: {
                    css: {
                        color: 'inherit',
                    }
                }
            },
        },
    },

    plugins: [
        typography,
    ],
};
