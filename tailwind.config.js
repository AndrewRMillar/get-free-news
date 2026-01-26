/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        "./reader.php",
        "./**/*.php"
    ],
    theme: {
        extend: {
            typography: {
                DEFAULT: {
                    css: {
                        maxWidth: '800px',
                        color: '#000',
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
            },
        },
    },
    plugins: [
        require('@tailwindcss/typography'),
    ],
};
