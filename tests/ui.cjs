// Recorre las pantallas en Edge real como lo haría una persona.
// Uso: instala puppeteer-core en una carpeta fuera del repo y ejecuta: node tests/ui.cjs <esa carpeta>
// Requiere la base de demostración sin tanques, ventas ni módulos activos. Deja datos y módulos encendidos.
const path = require('path');
const puppeteer = require(path.join(process.argv[2], 'node_modules', 'puppeteer-core'));
const SALIDA = process.argv[3] || require('os').tmpdir();
const BASE = process.env.GRIFO_BASE || 'http://localhost/grifo_grau';

let fallos = 0, total = 0;
const comprobar = (n, ok, d = '') => { total++; if (!ok) fallos++; console.log((ok ? '  ok    ' : '  FALLA ') + n + (ok || !d ? '' : ' -> ' + d)); };
const espera = ms => new Promise(r => setTimeout(r, ms));

(async () => {
  const navegador = await puppeteer.launch({ executablePath: process.env.NAVEGADOR || 'C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe', headless: 'new', args: ['--window-size=1366,850'] });

  async function sesion(usuario, clave) {
    const ctx = await navegador.createBrowserContext();
    const p = await ctx.newPage();
    await p.setViewport({ width: 1366, height: 850 });
    p.errores = [];
    p.on('dialog', async d => { p.errores.push('DIALOGO: ' + d.message().slice(0, 170)); await d.dismiss(); });
    p.on('pageerror', e => p.errores.push('JS: ' + e.message));
    p.on('console', m => { if (m.type() === 'error' && !/favicon/.test(m.text())) p.errores.push('consola: ' + m.text()); });
    p.on('response', r => { if (r.status() >= 400 && r.url().includes('grifo_grau')) p.errores.push(r.status() + ' ' + r.url().replace(BASE, '')); });
    await p.goto(BASE + '/', { waitUntil: 'networkidle2' });
    await p.type('#txt_usuario', usuario);
    await p.type('#txt_contra', clave);
    await p.evaluate(() => Iniciar_Sesion());
    await p.waitForFunction(() => location.href.includes('view/index.php'), { timeout: 15000 });
    await p.waitForNetworkIdle({ idleTime: 600, timeout: 20000 }).catch(() => {});
    return p;
  }
  const abrir = async (p, vista) => {
    await p.evaluate(v => cargar_contenido('contenido_principal', v), vista);
    await p.waitForNetworkIdle({ idleTime: 500, timeout: 15000 }).catch(() => {});
    await espera(300);
  };
  const cerrarAviso = async p => { await espera(200); await p.evaluate(() => window.Swal && Swal.close()); await espera(500); };
  const clicTexto = (p, sel, texto) => p.evaluate((s, t) => { const b = [...document.querySelectorAll(s)].find(x => x.textContent.includes(t)); if (!b) throw new Error('no hay ' + t); b.click(); }, sel, texto);
  const abrirModalListo = async (p, id) => { await p.waitForFunction(i => document.querySelector(i).classList.contains('show'), { timeout: 5000 }, id); await espera(300); };
  const ocultoModal = async (p, id) => { await p.waitForFunction(i => !document.querySelector(i).classList.contains('show'), { timeout: 5000 }, id); await espera(400); };
  const pantalla = (p, n) => p.screenshot({ path: path.join(SALIDA, n + '.png') });

  console.log('\n1. Administrador: login y pantallas existentes');
  const a = await sesion('admin', 'admin123');
  comprobar('el login por la interfaz entra al panel', a.url().includes('view/index.php'));
  comprobar('el nombre de la sesión aparece', (await a.content()).includes('ADMIN DEMO'));
  for (const v of ['reportes/view_dashboard.php', 'turnos/view_registrar_turno.php', 'turnos/view_historial.php', 'creditos/view_creditos_pendientes.php',
    'reportes/view_ventas_por_fecha.php', 'reportes/view_listado_reportes.php', 'reportes/view_validar_reportes.php',
    'clientes/view_clientes.php', 'productos/view_productos.php', 'surtidores/view_surtidores.php', 'usuario/view_usuario.php', 'usuario/view_mi_perfil.php']) {
    a.errores.length = 0;
    await abrir(a, v);
    comprobar('abre ' + v + ' sin errores', a.errores.length === 0, a.errores.slice(0, 3).join(' | '));
  }

  console.log('\n2. Configuración y módulos');
  a.errores.length = 0;
  await abrir(a, 'configuracion/view_configuracion.php');
  await a.waitForSelector('#modulo_inventario');
  comprobar('lista los módulos', (await a.$$('#lista_modulos input')).length === 10);
  comprobar('carga los valores', await a.$eval('#igv_porcentaje', e => e.value) === '18');
  await a.click('a[href="#tab_operacion"]'); await espera(400);
  await a.evaluate(() => { document.getElementById('credito_dias').value = '45'; });
  await a.evaluate(() => $('#tab_operacion .form_config').trigger('submit'));
  await a.waitForNetworkIdle({ idleTime: 400 }); await cerrarAviso(a);
  await pantalla(a, '01_configuracion');
  const guardado = await a.evaluate(() => $.post('../controller/configuracion/controlador_obtener.php', null, null, 'json'));
  comprobar('guarda desde el formulario', guardado.valores.credito_dias === '45');
  await a.evaluate(() => $.post('../controller/configuracion/controlador_guardar.php', { credito_dias: '30' }));
  for (const m of ['inventario', 'ventas', 'chatbot_ia']) {
    const nav = a.waitForNavigation({ waitUntil: 'networkidle2', timeout: 15000 });
    await a.evaluate(i => { const e = document.getElementById(i); e.click(); }, 'modulo_' + m);
    await nav;
    await a.waitForNetworkIdle({ idleTime: 400 }).catch(() => {});
    if (m !== 'chatbot_ia') { await abrir(a, 'configuracion/view_configuracion.php'); await a.waitForSelector('#modulo_ventas'); }
  }
  const menu = await a.evaluate(() => document.querySelector('.main-sidebar').innerText);
  comprobar('al activar, Ventas e Inventario aparecen en el menú', menu.includes('Ventas') && menu.includes('Inventario'), menu.slice(0, 200));
  comprobar('al activar, aparece el chat', !!(await a.$('#chat_abrir')));
  comprobar('sin errores en Configuración', a.errores.length === 0, a.errores.slice(0, 3).join(' | '));

  console.log('\n3. Tanques');
  a.errores.length = 0;
  await abrir(a, 'inventario/view_tanques.php');
  await a.click('#btn_nuevo_tanque'); await abrirModalListo(a, '#modal_tanque');
  await a.type('#tanque_nombre', 'Tanque Diesel 1');
  await a.waitForFunction(() => document.querySelectorAll('#tanque_producto option').length > 1);
  await a.select('#tanque_producto', await a.$eval('#tanque_producto', s => [...s.options].find(o => o.text.includes('DIESEL')).value));
  await a.type('#tanque_capacidad', '5000');
  await a.$eval('#tanque_minimo', e => e.value = '');  await a.type('#tanque_minimo', '500');
  await a.$eval('#tanque_inicial', e => e.value = ''); await a.type('#tanque_inicial', '2000');
  await pantalla(a, '02_modal_tanque');
  await a.click('#form_tanque button[type=submit]');
  await ocultoModal(a, '#modal_tanque'); await cerrarAviso(a);
  await a.waitForNetworkIdle({ idleTime: 400 });
  const filaT = await a.$eval('#cuerpo_tanques', e => e.innerText);
  comprobar('el tanque aparece en la tabla', filaT.includes('Tanque Diesel 1') && filaT.includes('2,000.000'), filaT);
  comprobar('aparece su tarjeta', (await a.$eval('#tarjetas_tanques', e => e.textContent)).includes('Tanque Diesel 1'));
  await pantalla(a, '03_tanques');
  await a.click('a[href="#tab_asignacion"]'); await espera(500);
  comprobar('lista los surtidores para asignar', (await a.$$('#cuerpo_asignacion tr')).length === 12);
  comprobar('sin errores en Tanques', a.errores.length === 0, a.errores.slice(0, 3).join(' | '));

  console.log('\n4. Proveedor y compra, con un nombre malicioso');
  a.errores.length = 0;
  await abrir(a, 'inventario/view_compras.php');
  await a.click('a[href="#tab_proveedores"]'); await espera(500);
  await a.click('#btn_nuevo_proveedor'); await abrirModalListo(a, '#modal_proveedor');
  await a.type('#prov_ruc', '20100000001');
  await a.type('#prov_razon', '<img src=x onerror="window.__xss=1">PROVEEDOR');
  await a.click('#form_proveedor button[type=submit]');
  await ocultoModal(a, '#modal_proveedor'); await cerrarAviso(a); await a.waitForNetworkIdle({ idleTime: 400 });
  comprobar('un nombre con HTML se muestra como texto', (await a.$eval('#cuerpo_proveedores', e => e.innerText)).includes('<img') && !(await a.evaluate(() => window.__xss)));
  await a.click('a[href="#tab_compras"]'); await espera(500);
  await a.click('#btn_nueva_compra'); await abrirModalListo(a, '#modal_compra');
  await a.select('#compra_proveedor', await a.$eval('#compra_proveedor', s => s.options[1].value));
  await a.type('#compra_numero', 'F001-100');
  await a.type('.linea_galones', '1000');
  await a.type('.linea_costo', '12.5');
  await a.$eval('#compra_percepcion', e => e.value = ''); await a.type('#compra_percepcion', '25');
  comprobar('calcula el total en pantalla', (await a.$eval('#compra_total', e => e.innerText)).includes('12,525.00'), await a.$eval('#compra_total', e => e.innerText));
  await pantalla(a, '04_modal_compra');
  await a.click('#form_compra button[type=submit]');
  await ocultoModal(a, '#modal_compra'); await cerrarAviso(a); await a.waitForNetworkIdle({ idleTime: 400 });
  comprobar('la compra aparece', (await a.$eval('#cuerpo_compras', e => e.innerText)).includes('F001-100'));
  await pantalla(a, '05_compras');
  await clicTexto(a, '#cuerpo_compras button', 'Pagar'); await abrirModalListo(a, '#modal_pago');
  await a.$eval('#pago_monto', e => e.value = ''); await a.type('#pago_monto', '5000');
  await a.click('#form_pago button[type=submit]');
  await ocultoModal(a, '#modal_pago'); await cerrarAviso(a); await a.waitForNetworkIdle({ idleTime: 400 });
  comprobar('el pago actualiza el saldo', (await a.$eval('#cuerpo_compras', e => e.innerText)).includes('7,525.00'));
  await abrir(a, 'inventario/view_tanques.php');
  comprobar('el stock subió con la compra', (await a.$eval('#cuerpo_tanques', e => e.innerText)).includes('3,000.000'));
  await a.click('a[href="#tab_kardex"]'); await espera(400);
  await a.click('#form_kardex button'); await a.waitForNetworkIdle({ idleTime: 400 });
  comprobar('el kardex muestra la compra', (await a.$eval('#cuerpo_kardex', e => e.innerText)).includes('COMPRA'));
  comprobar('sin errores en Compras', a.errores.length === 0, a.errores.slice(0, 3).join(' | '));

  console.log('\n5. Grifero: venta y varillaje');
  const g = await sesion('grifero1', 'grifero123');
  const menuG = await g.evaluate(() => document.querySelector('.main-sidebar').innerText);
  comprobar('ve Ventas y Varillaje', menuG.includes('Ventas') && menuG.includes('Varillaje'));
  comprobar('no ve Configuración ni Tanques', !menuG.includes('Configuración') && !menuG.includes('Tanques'));
  g.errores.length = 0;
  await abrir(g, 'ventas/view_ventas.php');
  await g.waitForFunction(() => document.querySelectorAll('#form_venta input[name=venta_surtidor]').length > 1);
  await g.evaluate(t => [...document.querySelectorAll('#form_venta input[name=venta_surtidor]')].find(r => r.closest('label').innerText.includes(t)).click(), 'DIESEL');
  await g.type('#venta_valor', '150');
  comprobar('calcula galones al escribir', (await g.$eval('#venta_calculo', e => e.innerText)).includes('10.000'), await g.$eval('#venta_calculo', e => e.innerText));
  comprobar('el botón muestra el total a cobrar', (await g.$eval('#venta_registrar', e => e.innerText)).includes('150.00'));
  await g.evaluate(t => [...document.querySelectorAll('#form_venta input[name=venta_pago]')].find(r => r.closest('label').innerText.trim() === t).click(), 'YAPE');
  comprobar('Yape pide código de operación', await g.$eval('#venta_codigo', e => e.required && e.offsetParent !== null));
  await g.type('#venta_codigo', 'OP123');
  await g.type('#venta_placa', 'abc-123');
  await pantalla(g, '06_venta');
  await g.click('#form_venta button[type=submit]');
  await g.waitForNetworkIdle({ idleTime: 400 }); await cerrarAviso(g); await g.waitForNetworkIdle({ idleTime: 400 });
  const ventas = await g.$eval('#cuerpo_ventas', e => e.innerText);
  comprobar('la venta aparece en Mis ventas', ventas.includes('150.00') && ventas.includes('ABC-123'), ventas);
  await pantalla(g, '07_ventas_lista');
  await abrir(g, 'inventario/view_varillaje.php');
  await g.waitForFunction(() => document.querySelectorAll('#var_tanque option').length > 1);
  await g.select('#var_tanque', await g.$eval('#var_tanque', s => s.options[1].value));
  await g.type('#var_galones', '2900');
  await g.click('#form_varillaje button[type=submit]');
  await g.waitForNetworkIdle({ idleTime: 400 }); await espera(300);
  const aviso = await g.evaluate(() => Swal.getTitle() && Swal.getTitle().innerText);
  comprobar('avisa de la diferencia fuera de tolerancia', aviso === 'Diferencia fuera de tolerancia', aviso);
  await cerrarAviso(g); await g.waitForNetworkIdle({ idleTime: 300 });
  const vars = await g.$eval('#cuerpo_varillajes', e => e.innerText);
  comprobar('la medición aparece, sin botón de ajuste para el grifero', vars.includes('2,900.000') && !vars.includes('Ajustar'), vars);
  comprobar('sin errores en pantallas del grifero', g.errores.length === 0, g.errores.slice(0, 3).join(' | '));
  g.errores.length = 0;
  for (const v of ['turnos/view_registrar_turno.php', 'creditos/view_creditos_pendientes.php', 'reportes/view_mis_reportes.php', 'clientes/view_clientes.php', 'usuario/view_mi_perfil.php', 'reportes/view_dashboard.php']) {
    await abrir(g, v);
    comprobar('grifero abre ' + v, g.errores.length === 0, g.errores.slice(0, 3).join(' | '));
    g.errores.length = 0;
  }

  console.log('\n6. Administrador: ajuste de varillaje y chat');
  a.errores.length = 0;
  await abrir(a, 'inventario/view_varillaje.php');
  await a.waitForFunction(() => document.querySelector('#cuerpo_varillajes').innerText.includes('Ajustar'));
  await clicTexto(a, '#cuerpo_varillajes button', 'Ajustar');
  await espera(400);
  await a.evaluate(() => Swal.clickConfirm());
  await a.waitForNetworkIdle({ idleTime: 400 }); await cerrarAviso(a); await a.waitForNetworkIdle({ idleTime: 300 });
  comprobar('aplica el ajuste', (await a.$eval('#cuerpo_varillajes', e => e.innerText)).includes('Ajustado'));
  await a.click('#chat_abrir'); await espera(300);
  comprobar('el chat se abre', await a.$eval('#chat_panel', e => !e.classList.contains('d-none')));
  await a.type('#chat_entrada', '¿Cuánto se vendió hoy?');
  await a.keyboard.press('Enter');
  await a.waitForFunction(() => { const b = document.querySelectorAll('#chat_mensajes .chat_bot'); return b[b.length - 1].innerText !== 'Consultando…'; }, { timeout: 30000 });
  const respuesta = await a.evaluate(() => { const b = document.querySelectorAll('#chat_mensajes .chat_bot'); return b[b.length - 1].innerText; });
  comprobar('sin clave configurada, explica qué falta', respuesta.includes('clave'), respuesta);
  await pantalla(a, '08_chat');
  comprobar('sin errores', a.errores.length === 0, a.errores.slice(0, 3).join(' | '));

  console.log('\n6b. Grifero: turno completo desde el formulario real');
  g.errores.length = 0;
  await abrir(g, 'turnos/view_registrar_turno.php');
  await g.waitForFunction(() => document.querySelectorAll('.lectura-final-input').length === 12, { timeout: 15000 });
  await g.waitForFunction(() => document.querySelector('#txt_numero_documento').value !== '', { timeout: 10000 });
  await g.waitForFunction(() => document.querySelector('#txt_grifero').value !== '' , { timeout: 10000 }).catch(() => {});
  await g.evaluate(() => {
    const poner = (id, v) => { const e = document.getElementById(id); if (!e.value) { e.value = v; } };
    if (!document.getElementById('txt_tipo_turno').value) { document.getElementById('txt_tipo_turno').selectedIndex = 1; }
    poner('txt_hora_inicio', '06:00');
    poner('txt_hora_fin', '14:00');
    poner('txt_fecha_turno', new Date().toISOString().slice(0, 10));
    document.querySelectorAll('.lectura-final-input').forEach(i => {
      i.value = (parseFloat(i.dataset.lecturaInicial) + 10).toFixed(3);
      i.dispatchEvent(new Event('input', { bubbles: true }));
      i.dispatchEvent(new Event('change', { bubbles: true }));
    });
    document.getElementById('txt_monto_efectivo').value = '1760';
    $('#txt_monto_efectivo').trigger('input');
  });
  await espera(400);
  await pantalla(g, '09_turno_formulario');
  const cuadre = await g.$eval('#cuadre_faltante', e => e.innerText).catch(() => '');
  comprobar('el cuadre en pantalla queda en cero', cuadre.includes('CUADRADO'), cuadre);
  await g.evaluate(() => Registrar_Turno_Completo());
  await espera(400);
  await g.evaluate(() => Swal.clickConfirm());
  await g.waitForFunction(() => { const t = Swal.getTitle(); return t && t.innerText !== '¿Registrar turno completo?'; }, { timeout: 15000 });
  const tituloTurno = await g.evaluate(() => Swal.getTitle().innerText + ' | ' + Swal.getHtmlContainer().innerText);
  comprobar('el servidor acepta el turno enviado por la pantalla', tituloTurno.startsWith('Éxito'), tituloTurno);
  await g.evaluate(() => Swal.clickConfirm());
  await g.waitForNetworkIdle({ idleTime: 600, timeout: 20000 }).catch(() => {});
  comprobar('sin errores al registrar el turno', g.errores.length === 0, g.errores.slice(0, 3).join(' | '));
  comprobar('vuelve a Mis Reportes', (await g.content()).includes('view_mis_reportes') || (await g.$eval('#contenido_principal', e => e.innerText)).toUpperCase().includes('REPORTES'));
  await abrir(g, 'creditos/view_creditos_pendientes.php');
  comprobar('el grifero no ve el botón de revertir pagos', !(await g.$eval('#contenido_principal', e => e.innerHTML)).includes('Revertir_Monto_Cliente()'));
  comprobar('sin errores en créditos del grifero', g.errores.length === 0, g.errores.slice(0, 3).join(' | '));
  for (const pagina of await g.browserContext().pages()) { if (pagina !== g) { await pagina.close().catch(() => {}); } }

  console.log('\n6c. Administrador: arqueo, avisos y precios');
  a.errores.length = 0;
  await abrir(a, 'inventario/view_tanques.php');
  comprobar('el turno descontó 40 galones del tanque', (await a.$eval('#cuerpo_tanques', e => e.innerText)).includes('2,860.000'), await a.$eval('#cuerpo_tanques', e => e.innerText));
  await abrir(a, 'ventas/view_ventas.php');
  await a.click('a[href="#tab_conciliacion"]'); await espera(500);
  comprobar('la conciliación muestra lecturas contra ventas', (await a.$eval('#cuerpo_conciliacion', e => e.innerText)).includes('30.000'), await a.$eval('#cuerpo_conciliacion', e => e.innerText));

  await abrir(a, 'configuracion/view_configuracion.php');
  await a.waitForSelector('#modulo_caja');
  const navCaja = a.waitForNavigation({ waitUntil: 'networkidle2', timeout: 15000 });
  await a.evaluate(() => document.getElementById('modulo_caja').click());
  await navCaja;
  await a.waitForNetworkIdle({ idleTime: 400 }).catch(() => {});

  await abrir(a, 'reportes/view_dashboard.php');
  await a.waitForFunction(() => document.querySelectorAll('#panel_alertas .alert').length > 0, { timeout: 10000 });
  const avisos = await a.$eval('#panel_alertas', e => e.innerText);
  comprobar('el Dashboard avisa de arqueos y turnos pendientes', avisos.includes('sin arqueo') && avisos.includes('sin validar'), avisos);
  await pantalla(a, '10_dashboard_avisos');
  comprobar('una fecha sin hora conserva su día en Lima', await a.evaluate(() => Fecha_Local('2026-11-06').getDate() === 6 && Fecha_Local('2026-11-06').getMonth() === 10));
  comprobar('una fecha con hora sigue igual', await a.evaluate(() => Fecha_Local('2026-11-06 15:30:00').getHours() === 15));
  await a.evaluate(() => [...document.querySelectorAll('#panel_alertas a')].find(x => x.innerText.includes('sin arqueo')).click());
  await a.waitForSelector('#cuerpo_arqueos');
  await a.waitForFunction(() => document.querySelector('#cuerpo_arqueos').innerText.includes('Arquear'), { timeout: 10000 });
  comprobar('el aviso lleva a la pantalla de caja', true);
  await clicTexto(a, '#cuerpo_arqueos button', 'Arquear'); await abrirModalListo(a, '#modal_arqueo');
  comprobar('muestra el efectivo esperado', (await a.$eval('#arqueo_esperado', e => e.innerText)).includes('1,760.00'), await a.$eval('#arqueo_esperado', e => e.innerText));
  await a.type('#den_100', '17');
  await a.type('#den_50', '1');
  await a.type('#den_0_5', '1');
  comprobar('calcula la diferencia en pantalla', (await a.$eval('#arqueo_diferencia', e => e.innerText)).includes('faltante') && (await a.$eval('#arqueo_contado', e => e.innerText)).includes('1,750.50'), await a.$eval('#arqueo_diferencia', e => e.innerText));
  await pantalla(a, '11_modal_arqueo');
  await a.click('#form_arqueo button[type=submit]');
  await ocultoModal(a, '#modal_arqueo'); await a.waitForNetworkIdle({ idleTime: 400 });
  comprobar('avisa de la diferencia fuera de tolerancia', await a.evaluate(() => Swal.getTitle().innerText === 'Diferencia fuera de tolerancia'));
  await cerrarAviso(a); await a.waitForNetworkIdle({ idleTime: 400 });
  await clicTexto(a, '#cuerpo_arqueos button', 'Resolver'); await espera(400);
  await a.evaluate(() => Swal.clickConfirm()); await espera(500);
  await a.evaluate(() => { Swal.getInput().value = 'Acordado con el grifero'; Swal.clickConfirm(); });
  await a.waitForNetworkIdle({ idleTime: 500 }); await cerrarAviso(a); await a.waitForNetworkIdle({ idleTime: 400 });
  comprobar('la diferencia queda resuelta', (await a.$eval('#cuerpo_arqueos', e => e.innerText)).includes('DESCONTADO'), await a.$eval('#cuerpo_arqueos', e => e.innerText));
  await pantalla(a, '12_caja');
  await a.click('a[href="#tab_depositos"]'); await espera(500);
  await a.type('#dep_banco', 'BCP'); await a.type('#dep_operacion', 'OP-UI-1'); await a.type('#dep_monto', '1000');
  await a.click('#form_deposito button[type=submit]');
  await a.waitForNetworkIdle({ idleTime: 500 }); await cerrarAviso(a); await a.waitForNetworkIdle({ idleTime: 400 });
  comprobar('el depósito baja el saldo en caja', (await a.$eval('#caja_tarjetas', e => e.innerText)).includes('750.50'), await a.$eval('#caja_tarjetas', e => e.innerText));

  await abrir(a, 'productos/view_productos.php');
  await a.waitForFunction(() => document.querySelectorAll('#prog_producto option').length > 1, { timeout: 10000 });
  await a.select('#prog_producto', await a.$eval('#prog_producto', s => s.options[1].value));
  await a.type('#prog_precio', '17.5');
  await a.evaluate(() => { const d = new Date(Date.now() + 86400000); document.getElementById('prog_fecha').value = new Date(d - d.getTimezoneOffset() * 60000).toISOString().slice(0, 16); });
  await a.click('#form_programar button[type=submit]');
  await a.waitForNetworkIdle({ idleTime: 500 }); await cerrarAviso(a); await a.waitForNetworkIdle({ idleTime: 400 });
  comprobar('programa un cambio de precio', (await a.$eval('#cuerpo_programados', e => e.innerText)).includes('PENDIENTE'));
  await pantalla(a, '13_precios');
  await abrir(a, 'configuracion/view_configuracion.php');
  await a.waitForSelector('#alerta_stock_bajo');
  await a.click('a[href="#tab_inicio"]'); await espera(400);
  comprobar('Configuración permite elegir los avisos', (await a.$$('.alerta_opcion')).length === 12);
  comprobar('sin errores en caja, avisos y precios', a.errores.length === 0, a.errores.slice(0, 3).join(' | '));

  console.log('\n6c2. Facturación electrónica (necesita tests/mock_nubefact.php y NUBEFACT_RUTA en .env)');
  a.errores.length = 0;
  await abrir(a, 'configuracion/view_configuracion.php');
  await a.waitForSelector('#modulo_facturacion');
  const navFact = a.waitForNavigation({ waitUntil: 'networkidle2', timeout: 15000 });
  await a.evaluate(() => document.getElementById('modulo_facturacion').click());
  await navFact;
  await a.waitForNetworkIdle({ idleTime: 400 }).catch(() => {});
  await abrir(a, 'configuracion/view_configuracion.php');
  await a.waitForSelector('#factura_token');
  await a.click('a[href="#tab_integraciones"]'); await espera(500);
  comprobar('la serie por defecto aparece', await a.$eval('#factura_serie_boleta', e => e.value) === 'B001');
  await a.type('#factura_token', 'token-de-prueba');
  await pantalla(a, '18_config_nubefact');
  await a.evaluate(() => $('#factura_token').closest('form').trigger('submit'));
  await a.waitForNetworkIdle({ idleTime: 500 }); await cerrarAviso(a); await a.waitForNetworkIdle({ idleTime: 400 });
  await a.waitForFunction(() => document.getElementById('factura_token_estado').innerText.includes('Configurada'), { timeout: 8000 });
  await a.click('#btn_probar_nubefact');
  await a.waitForFunction(() => window.Swal && Swal.isVisible() && Swal.getTitle().innerText === 'Respuesta de Nubefact', { timeout: 10000 });
  comprobar('la prueba de conexión responde', (await a.evaluate(() => Swal.getHtmlContainer().innerText)).includes('no encontrado'));
  await cerrarAviso(a);

  g.errores.length = 0;
  await g.reload({ waitUntil: 'networkidle2' });
  await abrir(g, 'ventas/view_ventas.php');
  await g.waitForFunction(() => document.querySelectorAll('#form_venta input[name=venta_surtidor]').length > 1);
  await g.evaluate(t => [...document.querySelectorAll('#form_venta input[name=venta_surtidor]')].find(r => r.closest('label').innerText.includes(t)).click(), 'DIESEL');
  await g.type('#venta_valor', '75');
  await g.evaluate(t => [...document.querySelectorAll('#form_venta input[name=venta_pago]')].find(r => r.closest('label').innerText.trim() === t).click(), 'EFECTIVO');
  await g.click('#form_venta input[name=venta_comprobante][value=BOLETA]');
  await g.click('#form_venta button[type=submit]');
  await g.waitForNetworkIdle({ idleTime: 500 }); await cerrarAviso(g); await g.waitForNetworkIdle({ idleTime: 400 });
  const ventasFact = await g.$eval('#cuerpo_ventas', e => e.innerText);
  comprobar('la venta muestra su boleta', ventasFact.includes('BOLETA B001-1'), ventasFact);
  comprobar('sin errores al vender con comprobante', g.errores.length === 0, g.errores.slice(0, 3).join(' | '));
  await pantalla(g, '19_venta_boleta');

  await abrir(a, 'facturacion/view_comprobantes.php');
  await a.waitForFunction(() => document.querySelector('#cuerpo_comprobantes').innerText.includes('B001-1'), { timeout: 10000 });
  const bandeja = await a.$eval('#cuerpo_comprobantes', e => e.innerText);
  comprobar('la bandeja la muestra aceptada y con archivos', bandeja.includes('Aceptado') && bandeja.includes('PDF'), bandeja);
  await pantalla(a, '20_comprobantes');
  await clicTexto(a, '#cuerpo_comprobantes button', 'Anular');
  await espera(400);
  await a.evaluate(() => { Swal.getInput().value = 'ERROR DEL SISTEMA'; Swal.clickConfirm(); });
  await a.waitForNetworkIdle({ idleTime: 500 }); await cerrarAviso(a); await a.waitForNetworkIdle({ idleTime: 400 });
  comprobar('la anulación queda por confirmar', (await a.$eval('#cuerpo_comprobantes', e => e.innerText)).includes('Anulación por confirmar'));
  await a.click('#btn_procesar_cola');
  await a.waitForNetworkIdle({ idleTime: 500 }); await cerrarAviso(a); await a.waitForNetworkIdle({ idleTime: 400 });
  comprobar('el botón de actualizar la confirma', (await a.$eval('#cuerpo_comprobantes', e => e.innerText)).includes('Anulado'));
  comprobar('sin errores en la bandeja', a.errores.length === 0, a.errores.slice(0, 3).join(' | '));

  console.log('\n6d. Roles y menú desde la interfaz');
  a.errores.length = 0;
  await abrir(a, 'administracion/view_roles.php');
  await a.waitForFunction(() => document.querySelector('#cuerpo_roles').innerText.includes('Supervisor'));
  await a.click('#btn_nuevo_rol'); await abrirModalListo(a, '#modal_rol');
  await a.type('#rol_nombre', 'Cajero');
  await a.select('#rol_inicio', 'caja/view_caja.php');
  await a.click('#cap_caja_gestionar');
  await a.click('#cap_ver_todos');
  await pantalla(a, '14_modal_rol');
  await a.click('#form_rol button[type=submit]');
  await ocultoModal(a, '#modal_rol'); await cerrarAviso(a); await a.waitForNetworkIdle({ idleTime: 400 });
  comprobar('crea un rol desde la pantalla', (await a.$eval('#cuerpo_roles', e => e.innerText)).includes('Cajero'));
  await pantalla(a, '15_roles');
  await clicTexto(a, '#cuerpo_roles tr:first-child button', 'Editar'); await abrirModalListo(a, '#modal_rol');
  comprobar('el administrador no muestra permisos que quitar', (await a.$$('.rol_cap')).length === 0);
  await a.evaluate(() => $('#modal_rol').modal('hide')); await ocultoModal(a, '#modal_rol');

  await abrir(a, 'administracion/view_menu.php');
  await a.waitForFunction(() => document.querySelector('#cuerpo_menu').innerText.includes('Dashboard'));
  await pantalla(a, '16_editor_menu');
  await a.evaluate(() => [...document.querySelectorAll('#cuerpo_menu tr')].find(t => t.innerText.includes('Surtidores')).querySelector('[aria-label="Subir Surtidores"]').click());
  await a.waitForNetworkIdle({ idleTime: 400 });
  await a.evaluate(() => [...[...document.querySelectorAll('#cuerpo_menu tr')].find(t => t.innerText.includes('Surtidores')).querySelectorAll('button')].find(b => b.innerText === 'Editar').click());
  await abrirModalListo(a, '#modal_menu');
  await a.$eval('#menu_titulo', e => e.value = ''); await a.type('#menu_titulo', 'Mangueras');
  await a.click('#form_menu button[type=submit]');
  await ocultoModal(a, '#modal_menu'); await cerrarAviso(a); await a.waitForNetworkIdle({ idleTime: 400 });
  await a.reload({ waitUntil: 'networkidle2' });
  const lateral = await a.evaluate(() => document.querySelector('.main-sidebar').innerText);
  comprobar('el menú refleja el cambio y el nuevo orden', lateral.includes('Mangueras') && !lateral.includes('Surtidores') && lateral.indexOf('Mangueras') < lateral.indexOf('Productos'), lateral);
  await abrir(a, 'administracion/view_auditoria.php');
  await a.waitForFunction(() => document.querySelector('#cuerpo_auditoria').innerText.includes('guardar_menu'), { timeout: 10000 });
  comprobar('la auditoría muestra lo que se acaba de hacer', true);
  await pantalla(a, '17_auditoria');
  comprobar('sin errores en las pantallas de administración', a.errores.length === 0, a.errores.slice(0, 3).join(' | '));

  console.log('\n6e. Cada rol abre cada opción de su menú');
  await a.evaluate(async () => {
    for (const [u, r] of [['sup_ui', 'SUPERVISOR'], ['cont_ui', 'CONTADOR'], ['caj_ui', 'CAJERO']]) {
      await $.post('../controller/usuario/controlador_registro_usuario.php', { dni: '1', nombre: 'PRUEBA', apelli: r, correo: '', tele: '', dire: '', usu: u, contra: 'clave12345', rol: r });
    }
  });
  for (const [usuario, clave] of [['grifero1', 'grifero123'], ['sup_ui', 'clave12345'], ['cont_ui', 'clave12345'], ['caj_ui', 'clave12345'], ['admin', 'admin123']]) {
    const p = await sesion(usuario, clave);
    const inicial = p.errores.slice();
    comprobar(usuario + ': la pantalla inicial abre sin errores', inicial.length === 0, inicial.slice(0, 3).join(' | '));
    const vistas = await p.evaluate(() => [...document.querySelectorAll('.nav-sidebar a[onclick]')].map(x => x.getAttribute('onclick').match(/"([^"]+\.php)"/)[1]));
    comprobar(usuario + ': tiene opciones en su menú', vistas.length > 0);
    for (const v of vistas) {
      p.errores.length = 0;
      await abrir(p, v);
      await espera(200);
      comprobar(usuario + ' abre ' + v, p.errores.length === 0, p.errores.slice(0, 4).join(' | '));
    }
    await p.browserContext().close();
  }
  await a.evaluate(() => $.post('../controller/administracion/controlador_mover_menu.php', { id_item: 203, direccion: 'ABAJO' }));
  await a.evaluate(() => $.post('../controller/administracion/controlador_guardar_menu.php', { id_item: 203, titulo: 'Surtidores', icono: 'fas fa-tint', visible: 1, roles: '[]' }));

  console.log('\n6f. Celular (390 px, táctil)');
  {
    const ctx = await navegador.createBrowserContext();
    const m = await ctx.newPage();
    await m.setViewport({ width: 390, height: 844, isMobile: true, hasTouch: true });
    m.errores = [];
    m.on('dialog', async d => { m.errores.push('DIALOGO: ' + d.message().slice(0, 120)); await d.dismiss(); });
    m.on('pageerror', e => m.errores.push('JS: ' + e.message));
    await m.goto(BASE + '/', { waitUntil: 'networkidle2' });
    comprobar('acceso: campos con etiqueta visible', await m.evaluate(() => !!document.querySelector('label[for="txt_usuario"]') && !!document.querySelector('label[for="txt_contra"]')));
    await m.evaluate(() => { localStorage.pass = 'vieja'; localStorage.checkbox = '1'; localStorage.usuario = 'grifero1'; });
    await m.reload({ waitUntil: 'networkidle2' });
    comprobar('acceso: borra una contraseña guardada antes y solo recuerda el usuario', await m.evaluate(() => localStorage.getItem('pass') === null && document.getElementById('txt_usuario').value === 'grifero1' && document.getElementById('txt_contra').value === ''));
    await m.type('#txt_contra', 'grifero123');
    await m.click('#entrar');
    await m.waitForFunction(() => location.href.includes('view/index.php'), { timeout: 15000 });
    await m.waitForNetworkIdle({ idleTime: 600, timeout: 20000 }).catch(() => {});
    comprobar('acceso: el formulario ingresa con Enter o con el botón', m.url().includes('view/index.php'));
    for (const v of ['turnos/view_registrar_turno.php', 'ventas/view_ventas.php', 'inventario/view_varillaje.php', 'creditos/view_creditos_pendientes.php', 'reportes/view_mis_reportes.php']) {
      m.errores.length = 0;
      await abrir(m, v);
      await espera(500);
      const r = await m.evaluate(() => {
        const raiz = document.getElementById('contenido_principal');
        const campos = [...raiz.querySelectorAll('input:not([type=hidden]):not([type=checkbox]):not([type=radio]), select, textarea')].filter(e => e.offsetParent !== null);
        return {
          desborde: document.documentElement.scrollWidth - window.innerWidth,
          sinEtiqueta: campos.filter(e => !(e.id && raiz.querySelector('label[for="' + e.id + '"]')) && !e.getAttribute('aria-label') && !e.closest('label')).map(e => e.id || e.className).slice(0, 3),
          letraChica: campos.filter(e => parseFloat(getComputedStyle(e).fontSize) < 16).length,
        };
      });
      comprobar(v + ': sin desplazamiento horizontal', r.desborde <= 0, String(r.desborde));
      comprobar(v + ': todos los campos con etiqueta', r.sinEtiqueta.length === 0, r.sinEtiqueta.join(', '));
      comprobar(v + ': letra de 16 px en los campos', r.letraChica === 0, String(r.letraChica));
      comprobar(v + ': sin errores ni diálogos', m.errores.length === 0, m.errores.slice(0, 3).join(' | '));
    }
    await abrir(m, 'turnos/view_registrar_turno.php');
    await m.waitForFunction(() => document.querySelectorAll('.lectura-final-input').length > 0, { timeout: 15000 });
    await m.emulateTimezone('America/Lima');
    comprobar('a las 21:30 en Lima la fecha local sigue siendo la del día', await m.evaluate(() => Fecha_ISO_Local(new Date('2026-11-07T21:30:00-05:00')) === '2026-11-07'));
    const pasos1 = await m.evaluate(() => ({ tipo: document.getElementById('txt_tipo_turno').value, inicio: document.getElementById('txt_hora_inicio').value, fin: document.getElementById('txt_hora_fin').value }));
    comprobar('el tipo de turno y sus horas vienen propuestos', (pasos1.tipo === 'DIA' && pasos1.inicio === '07:00' && pasos1.fin === '19:00') || (pasos1.tipo === 'NOCHE' && pasos1.inicio === '19:00' && pasos1.fin === '07:00'), JSON.stringify(pasos1));
    await m.evaluate(() => { const h = document.getElementById('txt_hora_inicio'); h.value = '06:30'; h.dispatchEvent(new Event('input', { bubbles: true })); const t = document.getElementById('txt_tipo_turno'); t.value = t.value === 'DIA' ? 'NOCHE' : 'DIA'; t.dispatchEvent(new Event('change', { bubbles: true })); });
    comprobar('cambiar el tipo no pisa una hora escrita a mano', await m.evaluate(() => document.getElementById('txt_hora_inicio').value === '06:30'));
    comprobar('las lecturas se muestran como tarjetas', await m.evaluate(() => ['block', 'grid'].includes(getComputedStyle(document.querySelector('#maquinas_lecturas tbody tr')).display) && document.querySelector('#maquinas_lecturas table').classList.contains('tabla-apilable')));
    await abrir(m, 'ventas/view_ventas.php');
    await m.waitForFunction(() => document.querySelectorAll('#form_venta input[name=venta_surtidor]').length > 1, { timeout: 15000 });
    comprobar('la venta propone efectivo como medio de pago', await m.evaluate(() => document.querySelector('#form_venta input[name=venta_pago]:checked').closest('label').innerText.trim() === 'EFECTIVO'));
    await m.click('#form_venta button[type=submit]');
    await espera(400);
    comprobar('un formulario incompleto marca los errores junto a cada campo', await m.evaluate(() => document.querySelectorAll('.campo-error').length >= 2 && document.activeElement && document.activeElement.classList.contains('is-invalid')));
    comprobar('el error del surtidor sale una sola vez, debajo del grupo', await m.evaluate(() => document.querySelectorAll('#venta_surtidor_error').length === 1 && document.getElementById('venta_surtidores').nextElementSibling.id === 'venta_surtidor_error'));
    await m.tap('#form_venta input[name=venta_surtidor]');
    comprobar('corregir un campo quita su error', await m.evaluate(() => !document.querySelector('#form_venta input[name=venta_surtidor]').classList.contains('is-invalid') && !document.getElementById('venta_surtidor_error')));
    comprobar('los surtidores se tocan con comodidad en el celular', await m.evaluate(() => document.querySelector('.g-opcion').getBoundingClientRect().height >= 44));
    await pantalla(m, '21_celular_errores');

    // Un nombre con HTML se guarda y luego se muestra en varias pantallas antiguas: nunca debe ejecutarse.
    await m.evaluate(() => $.post('../controller/clientes/controlador_registrar_cliente.php', { nombre: '<img src=x onerror="window.__xss=1">XSS', dni: '88888888', telefono: '', direccion: '' }));
    for (const v of ['clientes/view_clientes.php', 'creditos/view_creditos_pendientes.php', 'turnos/view_registrar_turno.php']) {
      await abrir(m, v);
      await espera(600);
      if (v.includes('registrar')) { await m.evaluate(() => window.Agregar_Fila_Credito_Registro && Agregar_Fila_Credito_Registro()); await espera(500); }
      comprobar(v + ': un nombre con HTML no se ejecuta', await m.evaluate(() => window.__xss !== 1));
    }
    await ctx.close();
  }

  console.log('\n6g. Menú: la pantalla abierta se ve seleccionada tras el clic');
  {
    // Con el foco en el enlace, AdminLTE pintaba texto blanco sobre fondo casi blanco.
    const enlace = await a.evaluateHandle(() => [...document.querySelectorAll('.nav-sidebar .nav-link')].find(x => x.innerText.trim() === 'Clientes'));
    await enlace.asElement().click(); await espera(700);
    const sel = await a.evaluate(() => { const x = document.querySelector('.nav-sidebar .nav-link.g-actual'); const s = x && getComputedStyle(x); return x ? [x.innerText.trim(), s.backgroundColor, s.color] : null; });
    comprobar('la opción del menú queda azul con texto blanco aunque tenga el foco', !!sel && sel[0] === 'Clientes' && sel[1] === 'rgb(37, 99, 235)' && sel[2] === 'rgb(255, 255, 255)', JSON.stringify(sel));
  }

  console.log('\n7. Sesión vencida y cierre');
  await a.evaluate(() => { window.__marca = 1; });
  await g.goto(BASE + '/controller/usuario/controlador_cerrar_sesion.php', { waitUntil: 'networkidle2' });
  comprobar('cerrar sesión vuelve al login', !!(await g.$('#txt_usuario')));
  await g.goto(BASE + '/view/index.php', { waitUntil: 'networkidle2' });
  comprobar('el panel sin sesión redirige al login', !!(await g.$('#txt_usuario')));

  await navegador.close();
  console.log(`\n${total} comprobaciones, ${fallos} fallidas`);
  process.exit(fallos ? 1 : 0);
})().catch(e => { console.error('ERROR', e.message); process.exit(2); });
