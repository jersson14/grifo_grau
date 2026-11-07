// Mide el contraste (WCAG AA) de los textos de cada pantalla y comprueba que ningún dato se ejecute como HTML.
// Uso: node tests/contraste.cjs <carpeta con puppeteer-core instalado> [carpeta de capturas]
// Requiere los datos de demostración y, para medir todas las pantallas, los módulos encendidos.
const path = require('path');
const fs = require('fs');
const puppeteer = require(path.join(process.argv[2], 'node_modules', 'puppeteer-core'));
const SALIDA = process.argv[3] || require('os').tmpdir();
fs.mkdirSync(SALIDA, { recursive: true });
const BASE = process.env.GRIFO_BASE || 'http://localhost/grifo_grau';
const espera = ms => new Promise(r => setTimeout(r, ms));
const VISTAS = ['reportes/view_dashboard.php', 'turnos/view_registrar_turno.php', 'turnos/view_historial.php', 'creditos/view_creditos_pendientes.php', 'clientes/view_clientes.php',
  'productos/view_productos.php', 'surtidores/view_surtidores.php', 'usuario/view_usuario.php', 'reportes/view_ventas_por_fecha.php', 'reportes/view_listado_reportes.php',
  'reportes/view_validar_reportes.php', 'usuario/view_mi_perfil.php', 'ventas/view_ventas.php', 'caja/view_caja.php', 'configuracion/view_configuracion.php',
  'gastos_grifo/view_gastos.php', 'flotas/view_flotas.php', 'tienda/view_tienda.php', 'personal/view_personal.php', 'reportes/view_analisis.php'];

const medir = () => {
  const lum = c => { const v = c.map(x => { x /= 255; return x <= 0.03928 ? x / 12.92 : Math.pow((x + 0.055) / 1.055, 2.4); }); return 0.2126 * v[0] + 0.7152 * v[1] + 0.0722 * v[2]; };
  const rgba = s => { const m = s.match(/rgba?\(([^)]+)\)/); if (!m) { return null; } const p = m[1].split(',').map(x => parseFloat(x)); return { c: p.slice(0, 3), a: p.length > 3 ? p[3] : 1 }; };
  const fondo = el => {
    for (let e = el; e; e = e.parentElement) {
      const cs = getComputedStyle(e);
      if (cs.backgroundImage && cs.backgroundImage !== 'none') { return null; }
      const b = rgba(cs.backgroundColor);
      if (b && b.a > 0.5) { return b.c; }
    }
    return [255, 255, 255];
  };
  const fallos = [];
  const raiz = document.querySelector('.content-wrapper');
  for (const el of raiz.querySelectorAll('*')) {
    if (!el.offsetParent || el.closest('.swal2-container')) { continue; }
    const propio = [...el.childNodes].some(n => n.nodeType === 3 && n.textContent.trim().length > 1);
    if (!propio) { continue; }
    const cs = getComputedStyle(el);
    if (cs.visibility === 'hidden' || parseFloat(cs.opacity) < 0.5) { continue; }
    const f = rgba(cs.color); const b = fondo(el);
    if (!f || !b) { continue; }
    const l1 = lum(f.c), l2 = lum(b);
    const ratio = (Math.max(l1, l2) + 0.05) / (Math.min(l1, l2) + 0.05);
    const grande = parseFloat(cs.fontSize) >= 24 || (parseFloat(cs.fontSize) >= 18.66 && parseInt(cs.fontWeight, 10) >= 700);
    const minimo = grande ? 3 : 4.5;
    if (ratio < minimo && !el.closest('input, select, textarea, [disabled]')) {
      fallos.push(ratio.toFixed(2) + ' «' + el.textContent.trim().replace(/\s+/g, ' ').slice(0, 28) + '» ' + cs.color + ' / rgb(' + b.join(',') + ')');
    }
  }
  return fallos;
};

(async () => {
  const nav = await puppeteer.launch({ executablePath: process.env.NAVEGADOR || 'C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe', headless: 'new', protocolTimeout: 60000 });
  const p = await (await nav.createBrowserContext()).newPage();
  await p.setViewport({ width: 1366, height: 850 });
  const dialogos = [];
  p.on('dialog', async d => { dialogos.push(d.message().slice(0, 80)); await d.dismiss(); });
  await p.goto(BASE + '/login.php', { waitUntil: 'networkidle2' });
  await p.type('#txt_usuario', 'admin'); await p.type('#txt_contra', 'admin123');
  await p.evaluate(() => Iniciar_Sesion());
  await p.waitForFunction(() => location.href.includes('view/index.php'), { timeout: 15000 });
  await p.waitForNetworkIdle({ idleTime: 600 }).catch(() => {});
  let totalFallos = 0;
  for (const v of VISTAS) {
    await p.evaluate(x => cargar_contenido('contenido_principal', x), v);
    await p.waitForNetworkIdle({ idleTime: 700, timeout: 20000 }).catch(() => {});
    await espera(500);
    const fallos = await p.evaluate(medir);
    const xss = await p.evaluate(() => window.__xss === 1);
    totalFallos += fallos.length;
    console.log(`${v.padEnd(42)} contraste bajo: ${String(fallos.length).padEnd(3)} html ejecutado: ${xss ? 'SI' : 'no'}${fallos.length ? '\n    ' + fallos.slice(0, 4).join('\n    ') : ''}`);
    await p.screenshot({ path: path.join(SALIDA, v.replace(/[\/.]/g, '_') + '.png') });
  }
  console.log(`\ntotal textos con contraste bajo: ${totalFallos}; diálogos: ${dialogos.length}`);
  await nav.close();
})().catch(e => { console.error('ERROR', e.message); process.exit(2); });
