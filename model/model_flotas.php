<?php
require_once __DIR__ . '/model_conexion.php';

/**
 * Flotas: clientes con línea de crédito, días de pago y placas autorizadas. Sus vales son los créditos
 * de siempre (ventas_credito); aquí se controlan contra el límite y las placas, sin bloquear el turno.
 */
class Modelo_Flotas
{
    /** Placa comparable: mayúsculas, sin guiones ni espacios. */
    private const PLACA_SQL = "UPPER(REPLACE(REPLACE(COALESCE(%s, ''), '-', ''), ' ', ''))";

    private function c(): PDO
    {
        $c = conexionBD::conexionPDO();
        if ($c === null) {
            throw new RuntimeException('Sin conexión a la base de datos');
        }
        return $c;
    }

    public static function normalizar_placa(string $placa): string
    {
        return strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $placa));
    }

    private static function placa_sql(string $columna): string
    {
        return sprintf(self::PLACA_SQL, $columna);
    }

    private static function inicio_mes(string $mes): string
    {
        return $mes . '-01';
    }

    private static function mes_valido(string $mes): bool
    {
        return (bool) preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $mes);
    }

    /** Flotas con deuda, consumo del mes, uso del límite y alertas. */
    public function Listar(): array
    {
        $placa = self::placa_sql('v.placa');
        $filas = $this->c()->query("SELECT f.id_cliente, c.nombre_completo, c.dni_ruc, f.limite_credito, f.dias_credito, f.estado,
                f.solo_placas_autorizadas, f.contacto, f.telefono,
                (SELECT COALESCE(SUM(v.saldo_pendiente), 0) FROM ventas_credito v WHERE v.id_cliente = f.id_cliente AND v.estado = 'PENDIENTE') AS deuda,
                (SELECT COALESCE(SUM(v.saldo_pendiente), 0) FROM ventas_credito v WHERE v.id_cliente = f.id_cliente AND v.estado = 'PENDIENTE'
                    AND v.fecha_vencimiento IS NOT NULL AND v.fecha_vencimiento < CURDATE()) AS vencido,
                (SELECT COALESCE(SUM(v.monto), 0) FROM ventas_credito v WHERE v.id_cliente = f.id_cliente AND v.estado <> 'ANULADO'
                    AND v.created_at >= DATE_FORMAT(CURDATE(), '%Y-%m-01')) AS consumo_mes,
                (SELECT COUNT(*) FROM flota_placas p WHERE p.id_cliente = f.id_cliente AND p.activo = 1) AS placas,
                (SELECT COUNT(*) FROM ventas_credito v WHERE v.id_cliente = f.id_cliente AND v.estado <> 'ANULADO' AND f.solo_placas_autorizadas = 1
                    AND v.created_at >= DATE_FORMAT(CURDATE(), '%Y-%m-01')
                    AND NOT EXISTS (SELECT 1 FROM flota_placas p WHERE p.id_cliente = v.id_cliente AND p.activo = 1 AND p.placa = $placa)) AS vales_no_autorizados
            FROM flotas f INNER JOIN clientes c ON c.id_cliente = f.id_cliente
            ORDER BY c.nombre_completo")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($filas as &$f) {
            $f['deuda'] = round((float) $f['deuda'], 2);
            $f['vencido'] = round((float) $f['vencido'], 2);
            $f['consumo_mes'] = round((float) $f['consumo_mes'], 2);
            $f['uso_limite'] = $f['limite_credito'] !== null && (float) $f['limite_credito'] > 0 ? round($f['deuda'] * 100 / (float) $f['limite_credito'], 1) : null;
            $f['sobre_limite'] = $f['limite_credito'] !== null && $f['deuda'] > (float) $f['limite_credito'];
        }
        return $filas;
    }

    /** Clientes activos que aún no son flota, para convertirlos. */
    public function Clientes_Sin_Flota(): array
    {
        return $this->c()->query("SELECT c.id_cliente, c.nombre_completo, c.dni_ruc FROM clientes c
            WHERE c.estado = 'ACTIVO' AND NOT EXISTS (SELECT 1 FROM flotas f WHERE f.id_cliente = c.id_cliente)
            ORDER BY c.nombre_completo LIMIT 1000")->fetchAll(PDO::FETCH_ASSOC);
    }

    private static function validar_datos(array $d): array
    {
        $limite = trim((string) ($d['limite_credito'] ?? ''));
        $dias = trim((string) ($d['dias_credito'] ?? '30'));
        $correo = trim((string) ($d['correo'] ?? ''));
        $telefono = preg_replace('/[\s-]/', '', (string) ($d['telefono'] ?? ''));
        $contacto = trim((string) ($d['contacto'] ?? ''));
        $obs = trim((string) ($d['observaciones'] ?? ''));
        if ($limite !== '' && (!is_numeric($limite) || (float) $limite < 0 || (float) $limite > 100000000)) {
            throw new DomainException('El límite de crédito debe ser un monto válido, o vacío para no tener límite.');
        }
        if (!ctype_digit($dias) || (int) $dias > 180) {
            throw new DomainException('Los días de crédito van de 0 a 180.');
        }
        if ($correo !== '' && !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            throw new DomainException('Revise el correo de la flota.');
        }
        if ($telefono !== '' && !preg_match('/^\+?\d{6,15}$/', $telefono)) {
            throw new DomainException('El teléfono solo lleva números.');
        }
        if (mb_strlen($contacto) > 120 || mb_strlen($obs) > 400) {
            throw new DomainException('El contacto tiene como máximo 120 caracteres y las observaciones 400.');
        }
        return [
            'limite_credito' => $limite === '' ? null : round((float) $limite, 2),
            'dias_credito'   => (int) $dias,
            'solo_placas'    => ($d['solo_placas_autorizadas'] ?? '1') === '1' ? 1 : 0,
            'contacto'       => $contacto ?: null,
            'telefono'       => $telefono ?: null,
            'correo'         => $correo ?: null,
            'observaciones'  => $obs ?: null,
            'estado'         => ($d['estado'] ?? 'ACTIVA') === 'SUSPENDIDA' ? 'SUSPENDIDA' : 'ACTIVA',
        ];
    }

    /**
     * Crea o actualiza una flota. Con id_cliente de un cliente que no es flota, lo convierte;
     * sin id_cliente, crea el cliente con nombre y RUC.
     */
    public function Guardar(array $d, int $idUsuario): int
    {
        $datos = self::validar_datos($d);
        $c = $this->c();
        $idCliente = (int) ($d['id_cliente'] ?? 0);

        if ($idCliente) {
            $q = $c->prepare('SELECT 1 FROM clientes WHERE id_cliente = ?');
            $q->execute([$idCliente]);
            if (!$q->fetchColumn()) {
                throw new DomainException('El cliente no existe.');
            }
        } else {
            $idCliente = $this->Crear_Cliente((string) ($d['nombre'] ?? ''), (string) ($d['ruc'] ?? ''), (string) ($datos['telefono'] ?? ''), $idUsuario);
        }

        $q = $c->prepare('SELECT 1 FROM flotas WHERE id_cliente = ?');
        $q->execute([$idCliente]);
        if ($q->fetchColumn()) {
            if (($d['nueva'] ?? '0') === '1') {
                throw new DomainException('Ese cliente ya es una flota.');
            }
            $c->prepare('UPDATE flotas SET limite_credito = ?, dias_credito = ?, solo_placas_autorizadas = ?, contacto = ?, telefono = ?, correo = ?,
                                observaciones = ?, estado = ?, actualizado_en = NOW() WHERE id_cliente = ?')
                ->execute([$datos['limite_credito'], $datos['dias_credito'], $datos['solo_placas'], $datos['contacto'], $datos['telefono'], $datos['correo'],
                    $datos['observaciones'], $datos['estado'], $idCliente]);
        } else {
            $c->prepare('INSERT INTO flotas (id_cliente, limite_credito, dias_credito, solo_placas_autorizadas, contacto, telefono, correo, observaciones, estado, id_solicitud, id_usuario)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)')
                ->execute([$idCliente, $datos['limite_credito'], $datos['dias_credito'], $datos['solo_placas'], $datos['contacto'], $datos['telefono'], $datos['correo'],
                    $datos['observaciones'], $datos['estado'], ($d['id_solicitud'] ?? null) ?: null, $idUsuario]);
        }
        return $idCliente;
    }

    private function Crear_Cliente(string $nombre, string $ruc, string $telefono, int $idUsuario): int
    {
        $nombre = trim($nombre);
        $ruc = preg_replace('/\D/', '', $ruc);
        if ($nombre === '' || mb_strlen($nombre) > 200) {
            throw new DomainException('Escriba el nombre o razón social de la empresa.');
        }
        if ($ruc !== '' && !preg_match('/^(10|15|17|20)\d{9}$/', $ruc)) {
            throw new DomainException('El RUC tiene 11 dígitos y empieza con 10 o 20.');
        }
        $c = $this->c();
        if ($ruc !== '') {
            $q = $c->prepare('SELECT id_cliente FROM clientes WHERE dni_ruc = ? LIMIT 1');
            $q->execute([$ruc]);
            if ($existente = $q->fetchColumn()) {
                return (int) $existente;
            }
        }
        $c->prepare("INSERT INTO clientes (nombre_completo, dni_ruc, telefono, estado, id_usuario) VALUES (?, ?, ?, 'ACTIVO', ?)")
            ->execute([$nombre, $ruc ?: null, $telefono ?: null, $idUsuario]);
        return (int) $c->lastInsertId();
    }

    /** Convierte una solicitud de la página pública en flota y la marca como atendida. */
    public function Desde_Solicitud(int $idSolicitud, array $d, int $idUsuario): int
    {
        $c = $this->c();
        $q = $c->prepare('SELECT * FROM solicitudes_flota WHERE id_solicitud = ?');
        $q->execute([$idSolicitud]);
        $s = $q->fetch(PDO::FETCH_ASSOC);
        if (!$s) {
            throw new DomainException('La solicitud no existe.');
        }
        $idCliente = $this->Guardar($d + [
            'nombre' => $s['empresa'], 'ruc' => (string) $s['ruc'], 'contacto' => $s['contacto'], 'telefono' => $s['telefono'],
            'correo' => (string) $s['correo'], 'id_solicitud' => $idSolicitud, 'nueva' => '1',
        ], $idUsuario);
        $c->prepare("UPDATE solicitudes_flota SET estado = 'ATENDIDA', nota = CONCAT_WS(' · ', NULLIF(nota, ''), 'Convertida en flota'), atendido_por = ?, atendido_en = NOW()
                     WHERE id_solicitud = ?")->execute([$idUsuario, $idSolicitud]);
        return $idCliente;
    }

    // ---------- Placas ----------

    public function Guardar_Placa(int $idCliente, ?int $idPlaca, string $placa, string $vehiculo, string $conductor, bool $activo): int
    {
        $placa = self::normalizar_placa($placa);
        $vehiculo = trim($vehiculo);
        $conductor = trim($conductor);
        if (!preg_match('/^[A-Z0-9]{5,8}$/', $placa)) {
            throw new DomainException('Escriba la placa con letras y números, por ejemplo ABC-123.');
        }
        if (mb_strlen($vehiculo) > 80 || mb_strlen($conductor) > 120) {
            throw new DomainException('El vehículo tiene como máximo 80 caracteres y el conductor 120.');
        }
        $c = $this->c();
        $q = $c->prepare('SELECT 1 FROM flotas WHERE id_cliente = ?');
        $q->execute([$idCliente]);
        if (!$q->fetchColumn()) {
            throw new DomainException('La flota no existe.');
        }
        $q = $c->prepare('SELECT id_placa FROM flota_placas WHERE id_cliente = ? AND placa = ? AND id_placa <> ?');
        $q->execute([$idCliente, $placa, (int) $idPlaca]);
        if ($q->fetchColumn()) {
            throw new DomainException('Esa placa ya está registrada en la flota.');
        }
        if ($idPlaca) {
            $c->prepare('UPDATE flota_placas SET placa = ?, vehiculo = ?, conductor = ?, activo = ? WHERE id_placa = ? AND id_cliente = ?')
                ->execute([$placa, $vehiculo ?: null, $conductor ?: null, (int) $activo, $idPlaca, $idCliente]);
            return $idPlaca;
        }
        $c->prepare('INSERT INTO flota_placas (id_cliente, placa, vehiculo, conductor, activo) VALUES (?, ?, ?, ?, ?)')
            ->execute([$idCliente, $placa, $vehiculo ?: null, $conductor ?: null, (int) $activo]);
        return (int) $c->lastInsertId();
    }

    /** Ficha y estado de cuenta de un mes: vales, consumo por placa, pagos y totales. */
    public function Detalle(int $idCliente, string $mes): array
    {
        if (!self::mes_valido($mes)) {
            $mes = date('Y-m');
        }
        $c = $this->c();
        $q = $c->prepare('SELECT f.*, c.nombre_completo, c.dni_ruc, c.direccion FROM flotas f INNER JOIN clientes c ON c.id_cliente = f.id_cliente WHERE f.id_cliente = ?');
        $q->execute([$idCliente]);
        $flota = $q->fetch(PDO::FETCH_ASSOC);
        if (!$flota) {
            throw new DomainException('La flota no existe.');
        }
        $q = $c->prepare('SELECT id_placa, placa, vehiculo, conductor, activo FROM flota_placas WHERE id_cliente = ? ORDER BY activo DESC, placa');
        $q->execute([$idCliente]);
        $placas = $q->fetchAll(PDO::FETCH_ASSOC);
        $autorizadas = array_column(array_filter($placas, fn($p) => (int) $p['activo'] === 1), 'placa');

        $desde = self::inicio_mes($mes);
        $hasta = date('Y-m-d', strtotime($desde . ' +1 month'));
        $q = $c->prepare("SELECT v.id_credito, DATE(v.created_at) AS fecha, v.numero_vale, v.placa, v.monto, v.saldo_pendiente, v.estado, v.fecha_vencimiento,
                                 rt.numero_documento AS turno
                          FROM ventas_credito v LEFT JOIN reportes_turno rt ON rt.id_reporte = v.id_reporte
                          WHERE v.id_cliente = ? AND v.created_at >= ? AND v.created_at < ? AND v.estado <> 'ANULADO'
                          ORDER BY v.created_at, v.id_credito");
        $q->execute([$idCliente, $desde, $hasta]);
        $vales = $q->fetchAll(PDO::FETCH_ASSOC);
        $porPlaca = [];
        foreach ($vales as &$v) {
            $norm = self::normalizar_placa((string) $v['placa']);
            $v['placa_normalizada'] = $norm;
            $v['autorizada'] = !(int) $flota['solo_placas_autorizadas'] || in_array($norm, $autorizadas, true);
            $v['vencido'] = $v['estado'] === 'PENDIENTE' && $v['fecha_vencimiento'] !== null && $v['fecha_vencimiento'] < date('Y-m-d');
            $clave = $norm !== '' ? $norm : 'SIN PLACA';
            $porPlaca[$clave] = $porPlaca[$clave] ?? ['placa' => $clave, 'vales' => 0, 'total' => 0.0, 'autorizada' => $v['autorizada']];
            $porPlaca[$clave]['vales']++;
            $porPlaca[$clave]['total'] = round($porPlaca[$clave]['total'] + (float) $v['monto'], 2);
        }
        unset($v);
        usort($porPlaca, fn($a, $b) => $b['total'] <=> $a['total']);

        $q = $c->prepare("SELECT h.fecha_pago, h.monto_pagado, h.descuento, h.codigo_operacion, t.nombre AS medio, v.numero_vale
                          FROM historial_pagos_credito h
                          INNER JOIN ventas_credito v ON v.id_credito = h.id_credito
                          LEFT JOIN tipos_pago t ON t.id_tipo_pago = h.id_tipo_pago
                          WHERE v.id_cliente = ? AND h.fecha_pago >= ? AND h.fecha_pago < ?
                          ORDER BY h.fecha_pago, h.id_pago_credito");
        $q->execute([$idCliente, $desde, $hasta]);
        $pagos = $q->fetchAll(PDO::FETCH_ASSOC);

        $q = $c->prepare("SELECT COALESCE(SUM(saldo_pendiente), 0) AS deuda,
                                 COALESCE(SUM(CASE WHEN fecha_vencimiento IS NOT NULL AND fecha_vencimiento < CURDATE() THEN saldo_pendiente ELSE 0 END), 0) AS vencido
                          FROM ventas_credito WHERE id_cliente = ? AND estado = 'PENDIENTE'");
        $q->execute([$idCliente]);
        $saldo = $q->fetch(PDO::FETCH_ASSOC);
        $deuda = round((float) $saldo['deuda'], 2);
        $limite = $flota['limite_credito'] !== null ? (float) $flota['limite_credito'] : null;

        return [
            'flota'    => $flota,
            'mes'      => $mes,
            'placas'   => $placas,
            'vales'    => $vales,
            'por_placa' => array_values($porPlaca),
            'pagos'    => $pagos,
            'totales'  => [
                'consumo_mes'  => round(array_sum(array_map(fn($v) => (float) $v['monto'], $vales)), 2),
                'pagado_mes'   => round(array_sum(array_map(fn($p) => (float) $p['monto_pagado'] + (float) $p['descuento'], $pagos)), 2),
                'deuda'        => $deuda,
                'vencido'      => round((float) $saldo['vencido'], 2),
                'disponible'   => $limite !== null ? round($limite - $deuda, 2) : null,
                'no_autorizados' => count(array_filter($vales, fn($v) => !$v['autorizada'])),
            ],
        ];
    }

    /** Aviso para el formulario del turno: si el cliente es flota, si la placa está autorizada y si excede el límite. No bloquea. */
    public function Verificar(int $idCliente, string $placa, float $monto): array
    {
        $c = $this->c();
        $q = $c->prepare("SELECT f.limite_credito, f.solo_placas_autorizadas, f.estado,
                                 (SELECT COALESCE(SUM(saldo_pendiente), 0) FROM ventas_credito v WHERE v.id_cliente = f.id_cliente AND v.estado = 'PENDIENTE') AS deuda
                          FROM flotas f WHERE f.id_cliente = ?");
        $q->execute([$idCliente]);
        $f = $q->fetch(PDO::FETCH_ASSOC);
        if (!$f) {
            return ['es_flota' => false];
        }
        $avisos = [];
        if ($f['estado'] === 'SUSPENDIDA') {
            $avisos[] = 'La flota está suspendida.';
        }
        $norm = self::normalizar_placa($placa);
        if ((int) $f['solo_placas_autorizadas'] === 1 && $norm !== '') {
            $q = $c->prepare('SELECT 1 FROM flota_placas WHERE id_cliente = ? AND placa = ? AND activo = 1');
            $q->execute([$idCliente, $norm]);
            if (!$q->fetchColumn()) {
                $avisos[] = 'La placa ' . $placa . ' no está autorizada para esta flota.';
            }
        }
        if ($f['limite_credito'] !== null && (float) $f['deuda'] + $monto > (float) $f['limite_credito']) {
            $avisos[] = 'Con este vale la deuda (S/ ' . number_format((float) $f['deuda'] + $monto, 2) . ') supera el límite de S/ ' . number_format((float) $f['limite_credito'], 2) . '.';
        }
        return ['es_flota' => true, 'avisos' => $avisos];
    }
}
