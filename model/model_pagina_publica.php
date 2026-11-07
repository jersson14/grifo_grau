<?php
require_once __DIR__ . '/model_conexion.php';
require_once __DIR__ . '/model_precios.php';

/**
 * Contenido de la página pública: datos editables, precios en vivo, servicios, promociones
 * y solicitudes de crédito para flotas.
 */
class Modelo_Pagina_Publica
{
    /** Campos editables y su validación. Ningún otro se guarda. */
    public const CLAVES = [
        'frase'           => ['tipo' => 'texto', 'max' => 60, 'requerido' => true],
        'descripcion'     => ['tipo' => 'texto', 'max' => 220],
        'horario'         => ['tipo' => 'texto', 'max' => 80],
        'direccion'       => ['tipo' => 'texto', 'max' => 160],
        'referencia'      => ['tipo' => 'texto', 'max' => 160],
        'mapa_url'        => ['tipo' => 'url'],
        'whatsapp'        => ['tipo' => 'telefono'],
        'telefono'        => ['tipo' => 'telefono'],
        'facebook'        => ['tipo' => 'url'],
        'instagram'       => ['tipo' => 'url'],
        'tiktok'          => ['tipo' => 'url'],
        'mostrar_precios' => ['tipo' => 'bandera'],
        'flota_titulo'    => ['tipo' => 'texto', 'max' => 60],
        'flota_texto'     => ['tipo' => 'texto', 'max' => 220],
    ];

    /** Iconos que el administrador puede elegir para un servicio (Font Awesome 5, sólidos). */
    public const ICONOS = [
        'gas-pump' => 'Surtidor', 'file-invoice' => 'Vale o crédito', 'mobile-alt' => 'Pago con celular',
        'receipt' => 'Comprobante', 'store' => 'Tienda', 'shopping-basket' => 'Market', 'oil-can' => 'Lubricantes',
        'tools' => 'Taller', 'soap' => 'Lavado', 'wind' => 'Aire', 'restroom' => 'Servicios higiénicos',
        'coffee' => 'Cafetería', 'wifi' => 'Wifi', 'parking' => 'Estacionamiento', 'truck' => 'Transporte pesado',
        'credit-card' => 'Tarjetas',
    ];

    public const ESTADOS_SOLICITUD = ['NUEVA', 'EN_PROCESO', 'ATENDIDA', 'DESCARTADA'];

    private function c(): PDO
    {
        $c = conexionBD::conexionPDO();
        if ($c === null) {
            throw new RuntimeException('Sin conexión a la base de datos');
        }
        return $c;
    }

    public function Datos(): array
    {
        $datos = array_fill_keys(array_keys(self::CLAVES), '');
        foreach ($this->c()->query('SELECT clave, valor FROM pagina_publica')->fetchAll(PDO::FETCH_KEY_PAIR) as $clave => $valor) {
            if (array_key_exists($clave, $datos)) {
                $datos[$clave] = (string) $valor;
            }
        }
        return $datos;
    }

    /** Devuelve la lista de campos con error; guarda solo si no hay ninguno. */
    public function Guardar(array $entrada): array
    {
        $limpios = [];
        $errores = [];
        foreach (self::CLAVES as $clave => $regla) {
            if (!array_key_exists($clave, $entrada)) {
                continue;
            }
            $valor = trim((string) $entrada[$clave]);
            switch ($regla['tipo']) {
                case 'texto':
                    if (($regla['requerido'] ?? false) && $valor === '' || mb_strlen($valor) > $regla['max']) {
                        $errores[$clave] = ($valor === '' ? 'Es obligatorio.' : 'Use como máximo ' . $regla['max'] . ' caracteres.');
                        continue 2;
                    }
                    break;
                case 'url':
                    if ($valor !== '' && (!filter_var($valor, FILTER_VALIDATE_URL) || !preg_match('#^https://#i', $valor))) {
                        $errores[$clave] = 'Debe ser un enlace que empiece con https://';
                        continue 2;
                    }
                    break;
                case 'telefono':
                    $valor = preg_replace('/[\s-]/', '', $valor);
                    if ($valor !== '' && !preg_match('/^\+?\d{6,15}$/', $valor)) {
                        $errores[$clave] = 'Escriba solo números, por ejemplo 51987654321.';
                        continue 2;
                    }
                    break;
                case 'bandera':
                    $valor = in_array($valor, ['1', 'true', 'on'], true) ? '1' : '0';
                    break;
            }
            $limpios[$clave] = $valor;
        }
        if ($errores) {
            return $errores;
        }
        $query = $this->c()->prepare('INSERT INTO pagina_publica (clave, valor, actualizado_en) VALUES (?, ?, NOW())
            ON DUPLICATE KEY UPDATE valor = VALUES(valor), actualizado_en = NOW()');
        foreach ($limpios as $clave => $valor) {
            $query->execute([$clave, $valor]);
        }
        return [];
    }

    public function Empresa(): array
    {
        $fila = $this->c()->query('SELECT nombre, telefono, direccion, email, logo FROM empresa ORDER BY id_empresa LIMIT 1')->fetch(PDO::FETCH_ASSOC) ?: [];
        $logo = $fila['logo'] ?? '';
        return [
            'nombre'    => trim((string) ($fila['nombre'] ?? '')) ?: 'Grifo',
            'telefono'  => (string) ($fila['telefono'] ?? ''),
            'direccion' => (string) ($fila['direccion'] ?? ''),
            'correo'    => (string) ($fila['email'] ?? ''),
            'logo'      => ($logo !== '' && is_file(dirname(__DIR__) . '/' . $logo)) ? $logo : '',
        ];
    }

    /**
     * Combustibles activos con su precio vigente, cuánto cambió hoy y desde cuándo rige.
     * Aplica antes los precios programados que ya vencieron.
     */
    public function Precios(): array
    {
        $c = $this->c();
        Modelo_Precios::Aplicar_Vencidos($c);
        $filas = $c->query("SELECT p.id_producto, p.nombre, p.tipo, p.precio_actual,
                (SELECT h.precio_anterior FROM precios_historial h
                  WHERE h.id_producto = p.id_producto AND h.creado_en >= CURDATE()
                  ORDER BY h.id_historial ASC LIMIT 1) AS precio_ayer,
                (SELECT MAX(h.creado_en) FROM precios_historial h WHERE h.id_producto = p.id_producto) AS desde
            FROM productos p
            WHERE p.estado = 'ACTIVO'
            ORDER BY p.id_producto")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($filas as &$f) {
            $f['precio_actual'] = round((float) $f['precio_actual'], 2);
            $f['cambio'] = $f['precio_ayer'] !== null ? round($f['precio_actual'] - (float) $f['precio_ayer'], 2) : 0.0;
            unset($f['precio_ayer']);
        }
        return $filas;
    }

    // ---------- Servicios ----------

    public function Servicios(bool $soloActivos): array
    {
        $sql = 'SELECT id_servicio, titulo, descripcion, icono, orden, activo FROM pagina_servicios'
            . ($soloActivos ? ' WHERE activo = 1' : '') . ' ORDER BY orden, id_servicio';
        return $this->c()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function Guardar_Servicio(?int $id, string $titulo, string $descripcion, string $icono, bool $activo): int
    {
        $titulo = trim($titulo);
        $descripcion = trim($descripcion);
        if ($titulo === '' || mb_strlen($titulo) > 80) {
            throw new DomainException('El nombre del servicio es obligatorio y tiene como máximo 80 caracteres.');
        }
        if (mb_strlen($descripcion) > 255) {
            throw new DomainException('La descripción tiene como máximo 255 caracteres.');
        }
        if (!isset(self::ICONOS[$icono])) {
            throw new DomainException('Elija un icono de la lista.');
        }
        $c = $this->c();
        if ($id) {
            $c->prepare('UPDATE pagina_servicios SET titulo = ?, descripcion = ?, icono = ?, activo = ? WHERE id_servicio = ?')
                ->execute([$titulo, $descripcion, $icono, (int) $activo, $id]);
            return $id;
        }
        $orden = (int) $c->query('SELECT COALESCE(MAX(orden), 0) + 10 FROM pagina_servicios')->fetchColumn();
        $c->prepare('INSERT INTO pagina_servicios (titulo, descripcion, icono, orden, activo) VALUES (?, ?, ?, ?, ?)')
            ->execute([$titulo, $descripcion, $icono, $orden, (int) $activo]);
        return (int) $c->lastInsertId();
    }

    public function Eliminar_Servicio(int $id): void
    {
        $this->c()->prepare('DELETE FROM pagina_servicios WHERE id_servicio = ?')->execute([$id]);
    }

    /** Intercambia el orden con el vecino de arriba o de abajo. */
    public function Mover_Servicio(int $id, string $direccion): void
    {
        $lista = $this->Servicios(false);
        $ids = array_map('intval', array_column($lista, 'id_servicio'));
        $pos = array_search($id, $ids, true);
        $otro = $direccion === 'ARRIBA' ? $pos - 1 : $pos + 1;
        if ($pos === false || !isset($ids[$otro])) {
            return;
        }
        [$ids[$pos], $ids[$otro]] = [$ids[$otro], $ids[$pos]];
        $query = $this->c()->prepare('UPDATE pagina_servicios SET orden = ? WHERE id_servicio = ?');
        foreach ($ids as $i => $idServicio) {
            $query->execute([($i + 1) * 10, $idServicio]);
        }
    }

    // ---------- Promociones ----------

    public function Promociones_Vigentes(): array
    {
        return $this->c()->query('SELECT id_promocion, titulo, detalle, desde, hasta FROM promociones
            WHERE activo = 1 AND desde <= CURDATE() AND (hasta IS NULL OR hasta >= CURDATE())
            ORDER BY desde DESC, id_promocion DESC LIMIT 6')->fetchAll(PDO::FETCH_ASSOC);
    }

    public function Promociones(): array
    {
        return $this->c()->query("SELECT id_promocion, titulo, detalle, desde, hasta, activo,
                CASE WHEN activo = 0 THEN 'INACTIVA'
                     WHEN desde > CURDATE() THEN 'PROGRAMADA'
                     WHEN hasta IS NOT NULL AND hasta < CURDATE() THEN 'VENCIDA'
                     ELSE 'VIGENTE' END AS estado
            FROM promociones ORDER BY activo DESC, desde DESC, id_promocion DESC")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function Guardar_Promocion(?int $id, string $titulo, string $detalle, string $desde, string $hasta, bool $activo, int $idUsuario): int
    {
        $titulo = trim($titulo);
        $detalle = trim($detalle);
        if ($titulo === '' || mb_strlen($titulo) > 100) {
            throw new DomainException('El título es obligatorio y tiene como máximo 100 caracteres.');
        }
        if (mb_strlen($detalle) > 400) {
            throw new DomainException('El detalle tiene como máximo 400 caracteres.');
        }
        $fecha = fn($v) => preg_match('/^\d{4}-\d{2}-\d{2}$/', $v) && checkdate((int) substr($v, 5, 2), (int) substr($v, 8, 2), (int) substr($v, 0, 4));
        if (!$fecha($desde)) {
            throw new DomainException('Indique desde qué fecha rige la promoción.');
        }
        if ($hasta !== '' && (!$fecha($hasta) || $hasta < $desde)) {
            throw new DomainException('La fecha final no puede ser anterior a la inicial.');
        }
        $c = $this->c();
        $hasta = $hasta === '' ? null : $hasta;
        if ($id) {
            $c->prepare('UPDATE promociones SET titulo = ?, detalle = ?, desde = ?, hasta = ?, activo = ? WHERE id_promocion = ?')
                ->execute([$titulo, $detalle, $desde, $hasta, (int) $activo, $id]);
            return $id;
        }
        $c->prepare('INSERT INTO promociones (titulo, detalle, desde, hasta, activo, id_usuario) VALUES (?, ?, ?, ?, ?, ?)')
            ->execute([$titulo, $detalle, $desde, $hasta, (int) $activo, $idUsuario]);
        return (int) $c->lastInsertId();
    }

    public function Eliminar_Promocion(int $id): void
    {
        $this->c()->prepare('DELETE FROM promociones WHERE id_promocion = ?')->execute([$id]);
    }

    // ---------- Solicitudes de flota ----------

    /** Registra una solicitud enviada desde la página pública. Límite: 3 por hora desde una misma IP. */
    public function Registrar_Solicitud(array $d, string $ip): void
    {
        $empresa = trim((string) ($d['empresa'] ?? ''));
        $ruc = preg_replace('/\D/', '', (string) ($d['ruc'] ?? ''));
        $contacto = trim((string) ($d['contacto'] ?? ''));
        $telefono = preg_replace('/[\s-]/', '', (string) ($d['telefono'] ?? ''));
        $correo = trim((string) ($d['correo'] ?? ''));
        $unidades = trim((string) ($d['unidades'] ?? ''));
        $galones = trim((string) ($d['galones_mes'] ?? ''));
        $mensaje = trim((string) ($d['mensaje'] ?? ''));

        $errores = [];
        if ($empresa === '' || mb_strlen($empresa) > 150) { $errores['empresa'] = 'Escribe el nombre de la empresa.'; }
        if ($ruc !== '' && !preg_match('/^(10|15|17|20)\d{9}$/', $ruc)) { $errores['ruc'] = 'El RUC tiene 11 dígitos y empieza con 10 o 20.'; }
        if ($contacto === '' || mb_strlen($contacto) > 120) { $errores['contacto'] = 'Dinos con quién hablamos.'; }
        if ($telefono === '') { $errores['telefono'] = 'Déjanos un número para llamarte.'; }
        elseif (!preg_match('/^\+?\d+$/', $telefono)) { $errores['telefono'] = 'Usa solo números, sin letras ni símbolos.'; }
        elseif (!preg_match('/^\+?\d{7,15}$/', $telefono)) { $errores['telefono'] = 'El número es muy corto: un celular tiene 9 dígitos y un fijo, 7.'; }
        if ($correo !== '' && (!filter_var($correo, FILTER_VALIDATE_EMAIL) || mb_strlen($correo) > 150)) { $errores['correo'] = 'Revisa el correo: falta la arroba o el dominio.'; }
        if ($unidades !== '' && (!ctype_digit($unidades) || (int) $unidades < 1 || (int) $unidades > 5000)) { $errores['unidades'] = 'Indica cuántas unidades tiene la flota, de 1 a 5000.'; }
        if ($galones !== '' && (!ctype_digit($galones) || (int) $galones > 10000000)) { $errores['galones_mes'] = 'Indica los galones al mes con números enteros.'; }
        if (mb_strlen($mensaje) > 600) { $errores['mensaje'] = 'Use como máximo 600 caracteres.'; }
        if ($errores) {
            throw new Solicitud_Invalida($errores);
        }

        $c = $this->c();
        $q = $c->prepare('SELECT COUNT(*) FROM solicitudes_flota WHERE ip = ? AND creado_en >= NOW() - INTERVAL 1 HOUR');
        $q->execute([$ip]);
        if ((int) $q->fetchColumn() >= 3) {
            throw new DomainException('Ya recibimos tus solicitudes. Te escribiremos pronto; si es urgente, llámanos.');
        }
        $c->prepare('INSERT INTO solicitudes_flota (empresa, ruc, contacto, telefono, correo, unidades, galones_mes, mensaje, ip)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)')
            ->execute([$empresa, $ruc ?: null, $contacto, $telefono, $correo ?: null,
                $unidades === '' ? null : (int) $unidades, $galones === '' ? null : (int) $galones, $mensaje ?: null, $ip]);
    }

    public function Solicitudes(string $estado): array
    {
        $sql = 'SELECT s.*, CONCAT(u.usu_nombre, " ", u.usu_apellido) AS atendido_nombre
            FROM solicitudes_flota s LEFT JOIN usuario u ON u.id_usuario = s.atendido_por';
        $params = [];
        if (in_array($estado, self::ESTADOS_SOLICITUD, true)) {
            $sql .= ' WHERE s.estado = ?';
            $params[] = $estado;
        }
        $q = $this->c()->prepare($sql . ' ORDER BY s.creado_en DESC LIMIT 300');
        $q->execute($params);
        return array_map(function ($f) { unset($f['ip']); return $f; }, $q->fetchAll(PDO::FETCH_ASSOC));
    }

    public function Solicitudes_Nuevas(): int
    {
        return (int) $this->c()->query("SELECT COUNT(*) FROM solicitudes_flota WHERE estado = 'NUEVA'")->fetchColumn();
    }

    public function Cambiar_Estado_Solicitud(int $id, string $estado, string $nota, int $idUsuario): void
    {
        if (!in_array($estado, self::ESTADOS_SOLICITUD, true)) {
            throw new DomainException('Estado no válido.');
        }
        if (mb_strlen(trim($nota)) > 400) {
            throw new DomainException('La nota tiene como máximo 400 caracteres.');
        }
        $q = $this->c()->prepare('UPDATE solicitudes_flota SET estado = ?, nota = ?, atendido_por = ?, atendido_en = NOW() WHERE id_solicitud = ?');
        $q->execute([$estado, trim($nota) ?: null, $idUsuario, $id]);
        if ($q->rowCount() === 0 && !$this->Existe_Solicitud($id)) {
            throw new DomainException('La solicitud no existe.');
        }
    }

    private function Existe_Solicitud(int $id): bool
    {
        $q = $this->c()->prepare('SELECT 1 FROM solicitudes_flota WHERE id_solicitud = ?');
        $q->execute([$id]);
        return (bool) $q->fetchColumn();
    }
}

/** Errores de validación por campo de una solicitud de flota. */
class Solicitud_Invalida extends DomainException
{
    public function __construct(public readonly array $errores)
    {
        parent::__construct('Revisa los datos marcados.');
    }
}
