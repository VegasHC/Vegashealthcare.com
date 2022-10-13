module.exports = {
    purge: {
      content: [
         './resources/**/*.antlers.html',
          './resources/**/*.antlers.php',
          './resources/**/search.js',
          './resources/**/*.blade.php',
          './content/**/*.md'
      ]
    },
    important: true,
    theme: {
        extend: {
            colors: {
                primary: "var(--color-primary)",
                secondary: "var(--color-secondary)",
                brand: "var(--color-brand)",
                heading: "var(--color-heading)",
                "bg-dark": "var(--color-background-dark)",
                "bg-light": "var(--color-background-light)"
            },
            opacity: {
                '01': '.001',
            }
        },


    },
    variants: {},
    plugins: [
        require('@tailwindcss/forms'),
        require('@tailwindcss/typography'),
    ],
  }
