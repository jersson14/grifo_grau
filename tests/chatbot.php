<?php
/**
 * Prueba del módulo y del chatbot contra la API simulada. Requiere:
 *   - Datos de demostración y ANTHROPIC_BASE_URL=http://127.0.0.1:8765 en .env
 *   - php -S 127.0.0.1:8765 tests/mock_anthropic.php (con MOCK_LOG apuntando al registro)
 *
 *   php tests/chatbot.php <ruta del registro>
 */
if (PHP_SAPI !== 'cli') {
    exit;
}

$registro = $argv[1];
$base = rtrim(getenv('GRIFO_BASE') ?: 'http://localhost/grifo_grau', '/');
$fallos = 0; $total = 0;

function comprobar(string $nombre, bool $ok, string $detalle = ''): void
{
    global $fallos, $total;
    $total++;
    if (!$ok) { $fallos++; }
    echo ($ok ? '  ok    ' : '  FALLA ') . $nombre . ($ok || $detalle === '' ? '' : " -> $detalle") . "\n";
}

class Cliente
{
    private string $cookies;
    public string $csrf = '';
    public function __construct(private string $base) { $this->cookies = tempnam(sys_get_temp_dir(), 'bot'); }
    public function pedir(string $ruta, ?array $post = null): array
    {
        $curl = curl_init($this->base . '/' . $ruta);
        curl_setopt_array($curl, [CURLOPT_RETURNTRANSFER => true, CURLOPT_COOKIEJAR => $this->cookies, CURLOPT_COOKIEFILE => $this->cookies,
            CURLOPT_HTTPHEADER => $this->csrf ? ['X-CSRF-Token: ' . $this->csrf] : [], CURLOPT_TIMEOUT => 60]);
        if ($post !== null) { curl_setopt($curl, CURLOPT_POST, true); curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post)); }
        $cuerpo = (string) curl_exec($curl);
        return ['codigo' => curl_getinfo($curl, CURLINFO_HTTP_CODE), 'cuerpo' => $cuerpo, 'json' => json_decode($cuerpo, true)];
    }
    public function entrar(string $u, string $c): void
    {
        $this->pedir('controller/usuario/controlador_iniciar_sesion.php', ['u' => $u, 'c' => $c]);
        if (preg_match('/X-CSRF-Token\': "([a-f0-9]+)"/', $this->pedir('view/index.php')['cuerpo'], $m)) { $this->csrf = $m[1]; }
    }
}

const CLAVE = 'clave-de-prueba-123';
const PREGUNTAR = 'controller/ia/controlador_preguntar.php';
const GUARDAR = 'controller/configuracion/controlador_guardar.php';
const MODULO = 'controller/configuracion/controlador_cambiar_modulo.php';

$admin = new Cliente($base);
$admin->entrar('admin', 'admin123');
$grifero = new Cliente($base);
$grifero->entrar('grifero1', 'grifero123');

echo "\n1. Configuración\n";
$r = $admin->pedir('view/configuracion/view_configuracion.php');
comprobar('el administrador abre Configuración', $r['codigo'] === 200);
$r = $grifero->pedir('view/configuracion/view_configuracion.php');
comprobar('el grifero no abre Configuración', $r['codigo'] === 403, (string) $r['codigo']);
$r = $grifero->pedir(GUARDAR, ['igv_porcentaje' => '0']);
comprobar('el grifero no puede guardar', $r['codigo'] === 403, (string) $r['codigo']);
$r = $admin->pedir(GUARDAR, ['igv_porcentaje' => '500']);
comprobar('un IGV fuera de rango se rechaza', ($r['json']['success'] ?? true) === false, $r['cuerpo']);
$r = $admin->pedir(GUARDAR, ['clave_inventada' => 'x', 'credito_dias' => '45']);
comprobar('una clave válida se guarda', ($r['json']['success'] ?? false) === true, $r['cuerpo']);
$r = $admin->pedir('controller/configuracion/controlador_obtener.php', []);
comprobar('el valor guardado se lee', ($r['json']['valores']['credito_dias'] ?? '') === '45', $r['cuerpo']);
comprobar('una clave no permitida se ignora', !isset($r['json']['valores']['clave_inventada']));
$admin->pedir(GUARDAR, ['credito_dias' => '30']);

$r = $admin->pedir(GUARDAR, ['ia_api_key' => CLAVE]);
$r = $admin->pedir('controller/configuracion/controlador_obtener.php', []);
comprobar('la clave figura como configurada', ($r['json']['valores']['ia_api_key_configurado'] ?? false) === true);
comprobar('la clave nunca se devuelve', strpos($r['cuerpo'], CLAVE) === false);
require_once dirname(__DIR__) . '/model/model_configuracion.php';
$pdo = conexionBD::conexionPDO();
$guardada = $pdo->query("SELECT valor FROM configuracion WHERE clave = 'ia_api_key'")->fetchColumn();
comprobar('la clave está cifrada en la base', strpos((string) $guardada, CLAVE) === false && strlen((string) $guardada) > 28);
$admin->pedir(GUARDAR, ['ia_api_key' => '']);
comprobar('dejarla en blanco la conserva', $pdo->query("SELECT valor FROM configuracion WHERE clave = 'ia_api_key'")->fetchColumn() === $guardada);

echo "\n2. Interruptor del módulo\n";
$admin->pedir(MODULO, ['codigo' => 'chatbot_ia', 'activo' => '0']);
$r = $admin->pedir(PREGUNTAR, ['pregunta' => 'ventas']);
comprobar('desactivado: el chatbot no responde', $r['codigo'] === 403, (string) $r['codigo']);
comprobar('desactivado: el chat no aparece', strpos($admin->pedir('view/index.php')['cuerpo'], 'chat_panel') === false);
$r = $admin->pedir('controller/turnos/controlador_tipos_pago.php', []);
comprobar('desactivado: el resto sigue operando', $r['codigo'] === 200, (string) $r['codigo']);
$r = $grifero->pedir(MODULO, ['codigo' => 'chatbot_ia', 'activo' => '1']);
comprobar('un grifero no puede activarlo', $r['codigo'] === 403, (string) $r['codigo']);
$admin->pedir(MODULO, ['codigo' => 'chatbot_ia', 'activo' => '1']);
comprobar('activado: el chat aparece', strpos($admin->pedir('view/index.php')['cuerpo'], 'chat_panel') !== false);

echo "\n3. Permisos del chatbot\n";
$admin->pedir(GUARDAR, ['ia_roles' => 'ADMINISTRADOR']);
$r = $grifero->pedir(PREGUNTAR, ['pregunta' => 'ventas']);
comprobar('un rol sin acceso recibe 403', $r['codigo'] === 403, (string) $r['codigo']);
comprobar('el chat no aparece a ese rol', strpos($grifero->pedir('view/index.php')['cuerpo'], 'chat_panel') === false);
$r = $grifero->pedir('controller/ia/controlador_historial.php', []);
comprobar('un rol sin acceso no lee el historial', $r['codigo'] === 403, (string) $r['codigo']);
$pdo->exec('DELETE FROM ia_consultas');

echo "\n4. Ciclo con herramientas\n";
file_put_contents($registro, '');
$r = $admin->pedir(PREGUNTAR, ['pregunta' => '¿Cuánto vendimos hoy?']);
comprobar('responde con el dato de la herramienta', ($r['json']['ok'] ?? false) && strpos($r['json']['respuesta'], 'RESULTADO') !== false, $r['cuerpo']);
$peticiones = array_map(fn($l) => json_decode($l, true), array_filter(explode("\n", file_get_contents($registro))));
comprobar('hizo dos llamadas: pedir herramienta y responder', count($peticiones) === 2, (string) count($peticiones));
$p = $peticiones[0] ?? [];
comprobar('usa la clave descifrada', ($p['api_key'] ?? '') === CLAVE, $p['api_key'] ?? '');
comprobar('usa el modelo configurado', ($p['peticion']['model'] ?? '') === 'claude-opus-5-5', $p['peticion']['model'] ?? '');
comprobar('envía el esfuerzo configurado', ($p['peticion']['output_config']['effort'] ?? '') === 'low', json_encode($p['peticion']['output_config'] ?? null));
comprobar('activa el reintento por rechazo', ($p['peticion']['fallbacks'] ?? '') === 'default' && strpos($p['beta'] ?? '', 'server-side-fallback') !== false, json_encode([$p['peticion']['fallbacks'] ?? null, $p['beta'] ?? '']));
comprobar('activa la caché', isset($p['peticion']['cache_control']));
comprobar('no fuerza el uso de herramientas', ($p['peticion']['tool_choice']['type'] ?? '') === 'auto');
comprobar('las herramientas van con esquema estricto', ($p['peticion']['tools'][0]['strict'] ?? false) === true && isset($p['peticion']['tools'][0]['input_schema']));
$segundo = json_encode($peticiones[1]['peticion']['messages'] ?? []);
comprobar('devuelve el resultado con tool_use_id', strpos($segundo, '"tool_use_id":"toolu_prueba"') !== false, $segundo);
$todo = file_get_contents($registro);
comprobar('no envía hashes ni contraseñas', strpos($todo, '$2y$') === false && strpos($todo, 'usu_contrasenia') === false);

$fila = $pdo->query('SELECT * FROM ia_consultas ORDER BY id_consulta DESC LIMIT 1')->fetch(PDO::FETCH_ASSOC);
comprobar('queda en la bitácora con costo', (float) $fila['costo_usd'] > 0 && $fila['herramientas'] === 'ventas_por_periodo', json_encode($fila));

$r = $admin->pedir(PREGUNTAR, ['pregunta' => 'Y de ayer?']);
$peticiones = array_map(fn($l) => json_decode($l, true), array_filter(explode("\n", file_get_contents($registro))));
$mensajes = $peticiones[2]['peticion']['messages'] ?? [];
comprobar('recuerda la conversación', count($mensajes) === 3 && $mensajes[1]['role'] === 'assistant', (string) count($mensajes));

echo "\n4b. Historial por usuario y avance\n";
$r = $admin->pedir('controller/ia/controlador_historial.php', []);
$h = $r['json']['data'] ?? [];
comprobar('el historial trae las dos preguntas en orden', count($h) === 2 && $h[0]['pregunta'] === '¿Cuánto vendimos hoy?' && $h[1]['pregunta'] === 'Y de ayer?', $r['cuerpo']);
comprobar('el historial trae la respuesta y la hora', strpos($h[0]['respuesta'] ?? '', 'RESULTADO') !== false && preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $h[0]['creado_en'] ?? ''), json_encode($h[0] ?? null));
$otro = new Cliente($base);
$otro->entrar('admin', 'admin123');
$r = $otro->pedir('controller/ia/controlador_historial.php', []);
comprobar('el historial sigue en otra sesión del mismo usuario', count($r['json']['data'] ?? []) === 2, $r['cuerpo']);

$pdo->exec("INSERT INTO ia_consultas (id_usuario, pregunta, respuesta, estado) SELECT id_usuario, 'pregunta ajena', 'dato ajeno', 'OK' FROM usuario WHERE usu_usuario = 'grifero1'");
$r = $admin->pedir('controller/ia/controlador_historial.php', []);
comprobar('no muestra conversaciones de otro usuario', strpos($r['cuerpo'], 'ajen') === false, $r['cuerpo']);

file_put_contents($registro, '');
$r = $admin->pedir(PREGUNTAR, ['pregunta' => '¿Cuánto vendimos hoy?', 'flujo' => '1']);
$lineas = array_values(array_filter(explode("\n", $r['cuerpo'])));
$datos = array_map(fn($l) => json_decode($l, true), $lineas);
comprobar('en modo flujo envía el avance antes de la respuesta', count($datos) === 2 && ($datos[0]['avance'] ?? '') === 'Consultando las ventas', $r['cuerpo']);
comprobar('la última línea es la respuesta', strpos($datos[1]['respuesta'] ?? '', 'RESULTADO') !== false && ($datos[1]['ok'] ?? false) === true, $r['cuerpo']);
$peticiones = array_map(fn($l) => json_decode($l, true), array_filter(explode("\n", file_get_contents($registro))));
comprobar('el contexto sale de la base: lleva las preguntas anteriores', count($peticiones[0]['peticion']['messages'] ?? []) === 5, (string) count($peticiones[0]['peticion']['messages'] ?? []));

$r = $admin->pedir('controller/ia/controlador_limpiar_conversacion.php', []);
$r = $admin->pedir('controller/ia/controlador_historial.php', []);
comprobar('«Nueva conversación» vacía el historial visible', ($r['json']['data'] ?? null) === [], $r['cuerpo']);
comprobar('pero la bitácora de consumo conserva las consultas', (int) $pdo->query("SELECT COUNT(*) FROM ia_consultas WHERE pregunta LIKE '%vendimos%'")->fetchColumn() === 2);
file_put_contents($registro, '');
$admin->pedir(PREGUNTAR, ['pregunta' => 'ventas']);
$peticiones = array_map(fn($l) => json_decode($l, true), array_filter(explode("\n", file_get_contents($registro))));
comprobar('tras la nueva conversación no arrastra el contexto', count($peticiones[0]['peticion']['messages'] ?? []) === 1, (string) count($peticiones[0]['peticion']['messages'] ?? []));
comprobar('el grifero conserva su propia conversación', (int) $pdo->query("SELECT COUNT(*) FROM ia_consultas WHERE pregunta = 'pregunta ajena' AND oculta = 0")->fetchColumn() === 1);
$pdo->exec("DELETE FROM ia_consultas WHERE pregunta = 'pregunta ajena'");

echo "\n5. Límites por rol dentro del chat\n";
$admin->pedir(GUARDAR, ['ia_roles' => 'ADMINISTRADOR,GRIFERO']);
file_put_contents($registro, '');
$r = $grifero->pedir(PREGUNTAR, ['pregunta' => 'RANKING de griferos']);
$peticiones = array_map(fn($l) => json_decode($l, true), array_filter(explode("\n", file_get_contents($registro))));
$nombres = array_column($peticiones[0]['peticion']['tools'] ?? [], 'name');
comprobar('al grifero no se le ofrece el ranking', !in_array('ranking_griferos', $nombres, true) && $nombres, json_encode($nombres));
comprobar('si el modelo lo pide igual, se rechaza', strpos($r['json']['respuesta'] ?? '', 'no permitida') !== false, $r['cuerpo']);
$admin->pedir(GUARDAR, ['ia_roles' => 'ADMINISTRADOR']);

echo "\n6. Fallos del servicio\n";
$r = $admin->pedir(PREGUNTAR, ['pregunta' => 'FORZAR_RECHAZO']);
comprobar('un rechazo muestra un mensaje claro', strpos($r['json']['respuesta'] ?? '', 'No puedo ayudar') !== false, $r['cuerpo']);
$r = $admin->pedir(PREGUNTAR, ['pregunta' => 'FORZAR_ERROR']);
comprobar('un error muestra un mensaje seguro', ($r['json']['ok'] ?? true) === false && strpos($r['cuerpo'], 'fallo simulado') === false, $r['cuerpo']);
$r = $admin->pedir('controller/turnos/controlador_tipos_pago.php', []);
comprobar('el resto sigue funcionando tras el error', $r['codigo'] === 200);
$r = $admin->pedir(PREGUNTAR, ['pregunta' => str_repeat('x', 1001)]);
comprobar('una pregunta demasiado larga se rechaza', ($r['json']['ok'] ?? true) === false, $r['cuerpo']);

echo "\n7. Límite de gasto\n";
$admin->pedir(GUARDAR, ['ia_limite_mensual_usd' => '0']);
file_put_contents($registro, '');
$r = $admin->pedir(PREGUNTAR, ['pregunta' => 'ventas']);
comprobar('al alcanzar el límite no llama a la API', strpos($r['json']['respuesta'] ?? '', 'límite') !== false && filesize($registro) === 0, $r['cuerpo']);
$admin->pedir(GUARDAR, ['ia_limite_mensual_usd' => '20']);

echo "\n8. Limpieza\n";
$pdo->exec("DELETE FROM ia_consultas");
$pdo->exec("UPDATE configuracion SET valor = NULL WHERE clave = 'ia_api_key'");
$admin->pedir(MODULO, ['codigo' => 'chatbot_ia', 'activo' => '0']);
comprobar('el módulo queda desactivado por defecto', !Modelo_Configuracion::Modulo_Activo('chatbot_ia'));

echo "\n$total comprobaciones, $fallos fallidas\n";
exit($fallos ? 1 : 0);
