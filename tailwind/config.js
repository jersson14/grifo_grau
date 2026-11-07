/** Tailwind de la página pública (estilo shadcn/ui). Compilar con `npm run css`; css/publico.css se versiona. */
module.exports = {
  content: ['./publico/**/*.php', './js/publico.js'],
  theme: {
    extend: {
      fontFamily: { sans: ['Inter', 'system-ui', '-apple-system', 'Segoe UI', 'sans-serif'] },
    },
  },
  plugins: [],
};
