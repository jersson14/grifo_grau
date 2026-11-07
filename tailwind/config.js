/** Tailwind del grifo. Compilar con `npm run css`; el resultado (css/publico.css) se versiona. */
module.exports = {
  content: ['./publico/**/*.php', './js/publico.js'],
  theme: {
    extend: {
      colors: require('./colores'),
      fontFamily: {
        rotulo: ['"Big Shoulders Display"', 'Barlow Semi Condensed', 'sans-serif'],
        sans: ['"Barlow Semi Condensed"', 'system-ui', 'sans-serif'],
      },
      letterSpacing: { rotulo: '0.01em' },
    },
  },
  plugins: [],
};
