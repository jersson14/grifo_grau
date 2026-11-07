<?php
/**
 * Prueba de roles, menú y auditoría contra el sistema en ejecución.
 * Requiere los datos de demostración. Limpia lo que crea.
 *
 *   php tests/roles.php
 */
if (PHP_SAPI !== 'cli') {
    exit;
}

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
    public function __construct(private string $base) { $this->cookies = tempnam(sys_get_temp_dir(), 'rol'); }
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

const ADM = 'controller/administracion/controlador_';
const USU = 'controller/usuario/controlador_';
const INICIO_TURNO = 'turnos/view_registrar_turno.php';

require_once dirname(__DIR__) . '/model/model_conexion.php';
$pdo = conexionBD::conexionPDO();

$admin = new Cliente($base);
$admin->entrar('admin', 'admin123');
$grifero = new Cliente($base);
$grifero->entrar('grifero1', 'grifero123');
$idAdmin = (int) $pdo->query("SELECT id_usuario FROM usuario WHERE usu_usuario = 'admin'")->fetchColumn();
$idGrifero2 = (int) $pdo->query("SELECT id_usuario FROM usuario WHERE usu_usuario = 'grifero2'")->fetchColumn();
$menuInicial = $pdo->query('SELECT * FROM menu_items')->fetchAll(PDO::FETCH_ASSOC);

$usuario = fn(string $u, string $rol, array $extra = []) => $extra + ['dni' => '1', 'nombre' => 'PRUEBA', 'apelli' => 'ROL', 'correo' => '', 'tele' => '', 'dire' => '', 'usu' => $u, 'contra' => 'clave12345', 'rol' => $rol];
$crear = function (string $u, string $rol) use ($admin, $usuario, $pdo) {
    $admin->pedir(USU . 'registro_usuario.php', $usuario($u, $rol));
    return (int) $pdo->query("SELECT id_usuario FROM usuario WHERE usu_usuario = " . $pdo->quote($u))->fetchColumn();
};

echo "\n1. Roles: administración\n";
foreach (['view/administracion/view_roles.php', 'view/administracion/view_menu.php', 'view/administracion/view_auditoria.php'] as $v) {
    comprobar('el administrador abre ' . basename($v), $admin->pedir($v)['codigo'] === 200);
    comprobar('el grifero no abre ' . basename($v), $grifero->pedir($v)['codigo'] === 403);
}
$r = $admin->pedir(ADM . 'listar_roles.php', []);
comprobar('lista roles y catálogo', count($r['json']['roles'] ?? []) === 4 && isset($r['json']['catalogo']['turnos_registrar']), substr($r['cuerpo'], 0, 300));
comprobar('el catálogo no expone rutas internas', strpos($r['cuerpo'], 'controlador_') === false);
$r = $grifero->pedir(ADM . 'guardar_rol.php', ['nombre' => 'X', 'vista_inicio' => INICIO_TURNO]);
comprobar('un grifero no crea roles', $r['codigo'] === 403);

$r = $admin->pedir(ADM . 'guardar_rol.php', ['nombre' => '', 'vista_inicio' => INICIO_TURNO]);
comprobar('un rol sin nombre se rechaza', ($r['json']['success'] ?? true) === false);
$r = $admin->pedir(ADM . 'guardar_rol.php', ['nombre' => 'X', 'vista_inicio' => '../../secreto.php']);
comprobar('una pantalla de inicio fuera del menú se rechaza', ($r['json']['success'] ?? true) === false, $r['cuerpo']);
$r = $admin->pedir(ADM . 'guardar_rol.php', ['nombre' => 'Vigilante Noche', 'descripcion' => 'prueba', 'vista_inicio' => 'creditos/view_creditos_pendientes.php',
    'capacidades' => json_encode(['creditos_cobrar', 'capacidad_inventada'])]);
comprobar('crea un rol', !empty($r['json']['success']), $r['cuerpo']);
comprobar('genera el código e ignora capacidades inventadas', $pdo->query("SELECT GROUP_CONCAT(capacidad) FROM roles_capacidades WHERE rol = 'VIGILANTE_NOCHE'")->fetchColumn() === 'creditos_cobrar');
$r = $admin->pedir(ADM . 'guardar_rol.php', ['nombre' => 'Vigilante Noche', 'vista_inicio' => INICIO_TURNO]);
comprobar('nombre repetido rechazado', ($r['json']['success'] ?? true) === false);

echo "\n2. Un rol nuevo en uso\n";
$r = $admin->pedir(USU . 'registro_usuario.php', $usuario('rol_falso', 'ROL_INEXISTENTE'));
comprobar('no se asigna un rol inexistente', $r['codigo'] === 403, (string) $r['codigo']);
$idVig = $crear('vigilante1', 'VIGILANTE_NOCHE');
comprobar('se asigna el rol nuevo', $idVig > 0);
$vig = new Cliente($base);
$vig->entrar('vigilante1', 'clave12345');
$panel = $vig->pedir('view/index.php')['cuerpo'];
comprobar('entra y ve solo lo permitido', strpos($panel, 'creditos/view_creditos_pendientes') !== false && strpos($panel, 'turnos/view_registrar_turno') === false && strpos($panel, 'usuario/view_usuario') === false);
comprobar('arranca en su pantalla de inicio', strpos($panel, 'var vista_inicio = "creditos\/view_creditos_pendientes.php"') !== false);
comprobar('el navegador recibe sus capacidades', strpos($panel, 'window.CAPACIDADES = ["creditos_cobrar"]') !== false);
comprobar('abre lo permitido', $vig->pedir('controller/creditos/controlador_listar_creditos.php', [])['codigo'] === 200);
comprobar('se le niega lo demás', $vig->pedir('controller/turnos/controlador_listar_turnos.php', [])['codigo'] === 403);

$admin->pedir(ADM . 'guardar_rol.php', ['codigo' => 'VIGILANTE_NOCHE', 'nombre' => 'Vigilante Noche', 'vista_inicio' => 'creditos/view_creditos_pendientes.php', 'capacidades' => json_encode(['creditos_cobrar', 'turnos_historial'])]);
comprobar('un permiso nuevo surte efecto sin volver a entrar', $vig->pedir('controller/turnos/controlador_listar_turnos.php', [])['codigo'] === 200);
$admin->pedir(ADM . 'guardar_rol.php', ['codigo' => 'VIGILANTE_NOCHE', 'nombre' => 'Vigilante Noche', 'vista_inicio' => 'creditos/view_creditos_pendientes.php', 'capacidades' => '[]']);
comprobar('quitar un permiso también', $vig->pedir('controller/creditos/controlador_listar_creditos.php', [])['codigo'] === 403);
$panel = $vig->pedir('view/index.php');
comprobar('un rol sin permisos aún abre el panel', $panel['codigo'] === 200 && strpos($panel['cuerpo'], 'var vista_inicio = "usuario\/view_mi_perfil.php"') !== false, substr($panel['cuerpo'], 0, 100));

$r = $admin->pedir(ADM . 'eliminar_rol.php', ['codigo' => 'VIGILANTE_NOCHE']);
comprobar('no se elimina un rol con usuarios', ($r['json']['success'] ?? true) === false, $r['cuerpo']);
$r = $admin->pedir(USU . 'modificar_usuario.php', $usuario('vigilante1', 'GRIFERO', ['id' => $idVig]));
comprobar('cambia el rol del usuario', trim($r['cuerpo']) === '1', $r['cuerpo']);
comprobar('el cambio de rol surte efecto de inmediato', $vig->pedir('controller/turnos/controlador_tipos_pago.php', [])['codigo'] === 200);
$r = $admin->pedir(ADM . 'eliminar_rol.php', ['codigo' => 'VIGILANTE_NOCHE']);
comprobar('sin usuarios, se elimina', !empty($r['json']['success']), $r['cuerpo']);

$r = $admin->pedir(USU . 'modificar_usuario_estatus.php', ['id' => $idVig, 'estatus' => 'INACTIVO']);
comprobar('desactiva al usuario', trim($r['cuerpo']) === '1', $r['cuerpo']);
comprobar('un usuario desactivado pierde la sesión al instante', $vig->pedir('controller/turnos/controlador_tipos_pago.php', [])['codigo'] === 401);

echo "\n3. Roles protegidos\n";
$r = $admin->pedir(ADM . 'eliminar_rol.php', ['codigo' => 'ADMINISTRADOR']);
comprobar('Administrador no se elimina', ($r['json']['success'] ?? true) === false);
$r = $admin->pedir(ADM . 'eliminar_rol.php', ['codigo' => 'GRIFERO']);
comprobar('Grifero no se elimina', ($r['json']['success'] ?? true) === false);
$admin->pedir(ADM . 'guardar_rol.php', ['codigo' => 'ADMINISTRADOR', 'nombre' => 'Otro', 'vista_inicio' => 'reportes/view_dashboard.php', 'capacidades' => '[]']);
comprobar('el administrador conserva nombre y acceso total', $pdo->query("SELECT nombre FROM roles WHERE codigo = 'ADMINISTRADOR'")->fetchColumn() === 'Administrador' && $admin->pedir(USU . 'listar_usuario.php', [])['codigo'] === 200);

echo "\n4. Sin escaladas ni bloqueos\n";
$r = $admin->pedir(USU . 'modificar_usuario_estatus.php', ['id' => $idAdmin, 'estatus' => 'INACTIVO']);
comprobar('nadie se desactiva a sí mismo', $r['codigo'] === 403, (string) $r['codigo']);
$r = $admin->pedir(USU . 'modificar_usuario.php', $usuario('admin', 'GRIFERO', ['id' => $idAdmin, 'nombre' => 'ADMIN', 'apelli' => 'DEMO']));
comprobar('no se puede quitar el rol al último administrador', $r['codigo'] === 403, (string) $r['codigo']);

$admin->pedir(ADM . 'guardar_rol.php', ['nombre' => 'Jefe Personal', 'vista_inicio' => 'usuario/view_usuario.php', 'capacidades' => json_encode(['usuarios_gestionar'])]);
$idJefe = $crear('jefe1', 'JEFE_PERSONAL');
$jefe = new Cliente($base);
$jefe->entrar('jefe1', 'clave12345');
comprobar('quien gestiona usuarios ve la lista', $jefe->pedir(USU . 'listar_usuario.php', [])['codigo'] === 200);
$r = $jefe->pedir(USU . 'registro_usuario.php', $usuario('admin_falso', 'ADMINISTRADOR'));
comprobar('pero no puede crear administradores', $r['codigo'] === 403, (string) $r['codigo']);
$r = $jefe->pedir(USU . 'modificar_usuario.php', $usuario('jefe1', 'ADMINISTRADOR', ['id' => $idJefe]));
comprobar('ni ascenderse', $r['codigo'] === 403, (string) $r['codigo']);
comprobar('ni cambiar la clave de un administrador', $jefe->pedir(USU . 'modificar_usuario_contra.php', ['id' => $idAdmin, 'con' => 'hackeada'])['codigo'] === 403);
comprobar('ni desactivar a un administrador', $jefe->pedir(USU . 'modificar_usuario_estatus.php', ['id' => $idAdmin, 'estatus' => 'INACTIVO'])['codigo'] === 403);
comprobar('la pantalla no le ofrece ese rol', strpos($jefe->pedir('view/usuario/view_usuario.php')['cuerpo'], 'value="ADMINISTRADOR"') === false);
comprobar('no puede editar roles', $jefe->pedir(ADM . 'guardar_rol.php', ['codigo' => 'JEFE_PERSONAL', 'nombre' => 'x', 'vista_inicio' => INICIO_TURNO, 'capacidades' => '["ver_todos"]'])['codigo'] === 403);
comprobar('la cuenta del administrador sigue intacta', password_verify('admin123', $pdo->query("SELECT usu_contrasenia FROM usuario WHERE id_usuario = $idAdmin")->fetchColumn()));

echo "\n5. Alcance de los datos\n";
$sup = $crear('sup1', 'SUPERVISOR');
$supervisor = new Cliente($base);
$supervisor->entrar('sup1', 'clave12345');
$numero = 'ROL-' . random_int(1000, 9999);
$pdo->exec("INSERT INTO reportes_turno (numero_documento, id_usuario, turno, fecha_reporte, estado) VALUES ('$numero', $idGrifero2, 'DIA', CURDATE(), 'CERRADO')");
$idTurno = (int) $pdo->lastInsertId();
$ids = fn(Cliente $c) => array_column($c->pedir('controller/turnos/controlador_listar_turnos.php', [])['json']['data'] ?? [], 'id_reporte');
comprobar('quien tiene «ver todos» ve turnos ajenos', in_array((string) $idTurno, array_map('strval', $ids($supervisor)), true));
comprobar('un grifero no', !in_array((string) $idTurno, array_map('strval', $ids($grifero)), true));
comprobar('el supervisor valida pero no elimina', $supervisor->pedir('controller/turnos/controlador_validar_reporte.php', ['id_reporte' => $idTurno])['codigo'] === 200 && $supervisor->pedir('controller/turnos/controlador_eliminar_turno.php', ['id_reporte' => $idTurno, 'motivo' => 'x'])['codigo'] === 403);
$pdo->exec("DELETE FROM reportes_turno WHERE id_reporte = $idTurno");

echo "\n6. Menú\n";
$panel = $admin->pedir('view/index.php')['cuerpo'];
comprobar('el administrador ve todas sus secciones', strpos($panel, 'MENÚ PRINCIPAL') !== false && strpos($panel, 'administracion/view_roles') !== false);
comprobar('los módulos apagados no aparecen', strpos($panel, 'ventas/view_ventas') === false);
$panelG = $grifero->pedir('view/index.php')['cuerpo'];
comprobar('el grifero conserva su menú', strpos($panelG, 'turnos/view_registrar_turno') !== false && strpos($panelG, 'reportes/view_mis_reportes') !== false && strpos($panelG, 'creditos/view_creditos_pendientes') !== false && strpos($panelG, 'administracion/') === false);
comprobar('un grupo de un solo enlace se aplana', substr_count($panelG, 'class="nav-item has-treeview"') === 0, (string) substr_count($panelG, 'class="nav-item has-treeview"'));
comprobar('«Mis Reportes» no aparece al administrador', strpos($panel, 'reportes/view_mis_reportes') === false);

$r = $admin->pedir(ADM . 'guardar_menu.php', ['id_item' => 201, 'titulo' => '<script>x</script>Socios', 'icono' => 'fas fa-users', 'visible' => 1, 'roles' => '[]']);
comprobar('cambia el título', !empty($r['json']['success']), $r['cuerpo']);
$panel = $admin->pedir('view/index.php')['cuerpo'];
comprobar('un título con HTML se muestra como texto', strpos($panel, '&lt;script&gt;x&lt;/script&gt;Socios') !== false && strpos($panel, '<script>x</script>') === false);
$r = $admin->pedir(ADM . 'guardar_menu.php', ['id_item' => 201, 'titulo' => 'Clientes', 'icono' => 'x" onclick="alert(1)', 'visible' => 1, 'roles' => '[]']);
comprobar('un ícono malicioso se rechaza', ($r['json']['success'] ?? true) === false, $r['cuerpo']);
$admin->pedir(ADM . 'guardar_menu.php', ['id_item' => 201, 'titulo' => 'Clientes', 'icono' => 'fas fa-user-friends', 'visible' => 0, 'roles' => '[]']);
comprobar('ocultar lo quita del menú', strpos($grifero->pedir('view/index.php')['cuerpo'], 'clientes/view_clientes') === false);
comprobar('pero no da ni quita permisos', $grifero->pedir('view/clientes/view_clientes.php')['codigo'] === 200);
$admin->pedir(ADM . 'guardar_menu.php', ['id_item' => 201, 'titulo' => 'Clientes', 'icono' => 'fas fa-user-friends', 'visible' => 1, 'roles' => json_encode(['ADMINISTRADOR'])]);
comprobar('limitar por rol lo oculta a los demás', strpos($grifero->pedir('view/index.php')['cuerpo'], 'clientes/view_clientes') === false);
$r = $admin->pedir(ADM . 'guardar_menu.php', ['id_item' => 207, 'titulo' => 'Editar menú', 'icono' => 'fas fa-bars', 'visible' => 0, 'roles' => '[]']);
comprobar('el editor del menú no se puede ocultar', ($r['json']['success'] ?? true) === false, $r['cuerpo']);
$r = $grifero->pedir(ADM . 'guardar_menu.php', ['id_item' => 201, 'titulo' => 'x', 'icono' => '', 'visible' => 1, 'roles' => '[]']);
comprobar('un grifero no edita el menú', $r['codigo'] === 403);

$antes = strpos($admin->pedir('view/index.php')['cuerpo'], 'productos/view_productos') < strpos($admin->pedir('view/index.php')['cuerpo'], 'surtidores/view_surtidores');
$admin->pedir(ADM . 'mover_menu.php', ['id_item' => 203, 'direccion' => 'ARRIBA']);
$panel = $admin->pedir('view/index.php')['cuerpo'];
comprobar('reordena', $antes && strpos($panel, 'surtidores/view_surtidores') < strpos($panel, 'productos/view_productos'));
$pdo->exec("UPDATE menu_items SET orden = 1 WHERE id_item = 100");
$admin->pedir(ADM . 'mover_menu.php', ['id_item' => 100, 'direccion' => 'ARRIBA']);
comprobar('mover más allá del extremo no hace nada', (int) $pdo->query("SELECT orden FROM menu_items WHERE id_item = 100")->fetchColumn() === 1);

echo "\n7. Auditoría\n";
$pdo->exec("DELETE FROM auditoria WHERE accion IN ('modificar_usuario_contra', 'guardar', 'registrar_cliente')");
$admin->pedir(USU . 'modificar_usuario_contra.php', ['id' => $sup, 'con' => 'ClaveSecreta123']);
$admin->pedir('controller/configuracion/controlador_guardar.php', ['ia_api_key' => 'claveapi-secreta', 'credito_dias' => '30']);
$fila = $pdo->query("SELECT * FROM auditoria WHERE accion = 'modificar_usuario_contra' ORDER BY id_auditoria DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
comprobar('registra la acción y al usuario', $fila && $fila['usuario'] === 'admin' && strpos($fila['detalle'], (string) $sup) !== false, json_encode($fila));
comprobar('nunca guarda contraseñas', strpos((string) ($fila['detalle'] ?? ''), 'ClaveSecreta') === false);
$cfg = (string) $pdo->query("SELECT detalle FROM auditoria WHERE accion = 'guardar' ORDER BY id_auditoria DESC LIMIT 1")->fetchColumn();
comprobar('nunca guarda claves de API', $cfg !== '' && strpos($cfg, 'claveapi-secreta') === false && strpos($cfg, '***') !== false, $cfg);
$todo = (string) $pdo->query("SELECT GROUP_CONCAT(detalle) FROM auditoria WHERE detalle LIKE '%clave12345%' OR detalle LIKE '%admin123%'")->fetchColumn();
comprobar('tampoco contraseñas de alta de usuario ni de login', $todo === '', $todo);
$r = $grifero->pedir('controller/clientes/controlador_registrar_cliente.php', ['nombre' => 'CLIENTE AUDITADO', 'dni' => '22222222', 'telefono' => '', 'direccion' => '', 'id_usuario' => 0]);
comprobar('registra acciones de griferos', (int) $pdo->query("SELECT COUNT(*) FROM auditoria WHERE accion = 'registrar_cliente' AND usuario = 'grifero1'")->fetchColumn() === 1);
$r = $admin->pedir('controller/administracion/controlador_listar_auditoria.php', ['texto' => 'CLIENTE AUDITADO']);
comprobar('se consulta y filtra por texto', count($r['json']['data'] ?? []) === 1 && $r['json']['data'][0]['usuario'] === 'grifero1', substr($r['cuerpo'], 0, 300));
comprobar('filtra por usuario', count($admin->pedir('controller/administracion/controlador_listar_auditoria.php', ['usuario' => 'nadie'])['json']['data']) === 0);
comprobar('listar no genera registros', (int) $pdo->query("SELECT COUNT(*) FROM auditoria WHERE accion LIKE 'listar%'")->fetchColumn() === 0);
comprobar('un grifero no la consulta', $grifero->pedir('controller/administracion/controlador_listar_auditoria.php', [])['codigo'] === 403);
$contador = new Cliente($base);
$idCont = $crear('cont1', 'CONTADOR');
$contador->entrar('cont1', 'clave12345');
comprobar('el contador sí', $contador->pedir('controller/administracion/controlador_listar_auditoria.php', [])['codigo'] === 200);

echo "\n8. Limpieza\n";
$pdo->exec("DELETE FROM clientes WHERE nombre_completo = 'CLIENTE AUDITADO'");
$pdo->exec("DELETE FROM usuario WHERE usu_usuario IN ('vigilante1', 'jefe1', 'sup1', 'cont1', 'admin_falso', 'rol_falso')");
$pdo->exec("DELETE FROM roles WHERE codigo IN ('VIGILANTE_NOCHE', 'JEFE_PERSONAL')");
$pdo->exec("UPDATE configuracion SET valor = NULL WHERE clave = 'ia_api_key'");
$restaurar = $pdo->prepare('UPDATE menu_items SET titulo = ?, icono = ?, orden = ?, visible = ?, roles = ? WHERE id_item = ?');
foreach ($menuInicial as $m) {
    $restaurar->execute([$m['titulo'], $m['icono'], $m['orden'], $m['visible'], $m['roles'], $m['id_item']]);
}
comprobar('el menú vuelve a su estado', strpos($admin->pedir('view/index.php')['cuerpo'], 'clientes/view_clientes') !== false);

echo "\n$total comprobaciones, $fallos fallidas\n";
exit($fallos ? 1 : 0);
