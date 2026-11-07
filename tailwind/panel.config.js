/**
 * Tailwind del panel. Convive con AdminLTE/Bootstrap: sin reinicio de estilos y con prefijo `tw-`.
 * Compilar con `npm run css`; el resultado (css/panel.css) se versiona.
 */
module.exports = {
  prefix: 'tw-',
  corePlugins: { preflight: false },
  content: ['./view/**/*.php', './js/console_*.js'],
  theme: {
    extend: {
      colors: require('./colores'),
      fontFamily: {
        rotulo: ['"Big Shoulders Display"', 'Barlow Semi Condensed', 'sans-serif'],
        sans: ['"Barlow Semi Condensed"', 'system-ui', 'sans-serif'],
      },
    },
  },
  plugins: [],
};
