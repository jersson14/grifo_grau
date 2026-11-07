/**
 * Tailwind del panel (estilo shadcn/ui). Convive con AdminLTE/Bootstrap: sin reinicio de estilos y con prefijo `tw-`.
 * Compilar con `npm run css`; css/panel.css se versiona.
 */
module.exports = {
  prefix: 'tw-',
  corePlugins: { preflight: false },
  content: ['./view/**/*.php', './js/console_*.js'],
  theme: {
    extend: {
      fontFamily: { sans: ['Inter', 'system-ui', '-apple-system', 'Segoe UI', 'sans-serif'] },
    },
  },
  plugins: [],
};
