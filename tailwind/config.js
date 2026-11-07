/** Tailwind del grifo. Compilar con `npm run css`; el resultado (css/publico.css) se versiona. */
module.exports = {
  content: ['./publico/**/*.php', './js/publico.js'],
  theme: {
    extend: {
      colors: {
        cabina: { DEFAULT: '#0b2a4a', profunda: '#071d34', clara: '#123a63', linea: '#2b5582' },
        hueso: { DEFAULT: '#f4f1ea', oscuro: '#e4ded1' },
        reflectivo: { DEFAULT: '#d7261e', tinta: '#a8170f' },
        seguridad: { DEFAULT: '#f6b800', oscuro: '#c99400' },
        cromo: { DEFAULT: '#c9ced4', oscuro: '#8b939c', claro: '#eef1f4' },
        caucho: { DEFAULT: '#16181b', borde: '#2a2e33' },
        tinta: '#0d1117',
      },
      fontFamily: {
        rotulo: ['"Big Shoulders Display"', 'Barlow Semi Condensed', 'sans-serif'],
        sans: ['"Barlow Semi Condensed"', 'system-ui', 'sans-serif'],
      },
      letterSpacing: { rotulo: '0.01em' },
    },
  },
  plugins: [],
};
