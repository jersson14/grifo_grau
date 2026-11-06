<?php
require_once 'model_conexion.php';

class Modelo_Ventas extends conexionBD
{
    /**
     * Registra un despacho. El precio sale del producto del surtidor, nunca del navegador.
     * $modo indica si $valor son soles o galones.
     */
    public function Registrar_Venta(array $d, int $idUsuario): array
    {
        $modo = $d['modo'] ?? 'SOLES';
        $valor = (float) ($d['valor'] ?? 0);
        $comprobante = $d['tipo_comprobante'] ?? 'NINGUNO';
        $idCliente = (int) ($d['id_cliente'] ?? 0);
        $codigo = trim((string) ($d['codigo_operacion'] ?? ''));

        if ($valor <= 0 || !in_array($modo, ['SOLES', 'GALONES'], true) || !in_array($comprobante, ['NINGUNO', 'BOLETA', 'FACTURA'], true)) {
            throw new DomainException('Indique un monto o una cantidad mayor que cero.');
        }

        $c = conexionBD::conexionPDO();
        $query = $c->prepare("SELECT s.id_producto, p.precio_actual FROM surtidores s
                              INNER JOIN productos p ON s.id_producto = p.id_producto
                              WHERE s.id_surtidor = ? AND s.estado = 'ACTIVO' AND p.estado = 'ACTIVO'");
        $query->execute([(int) ($d['id_surtidor'] ?? 0)]);
        $surtidor = $query->fetch(PDO::FETCH_ASSOC);
        if (!$surtidor) {
            throw new DomainException('Seleccione un surtidor activo.');
        }
        $precio = (float) $surtidor['precio_actual'];
        if ($precio <= 0) {
            throw new DomainException('El combustible de este surtidor no tiene precio.');
        }

        $query = $c->prepare("SELECT requiere_codigo FROM tipos_pago WHERE id_tipo_pago = ? AND estado = 'ACTIVO'");
        $query->execute([(int) ($d['id_tipo_pago'] ?? 0)]);
        $requiereCodigo = $query->fetchColumn();
        if ($requiereCodigo === false) {
            throw new DomainException('Seleccione un medio de pago válido.');
        }
        if ($requiereCodigo === 'SI' && $codigo === '') {
            throw new DomainException('Este medio de pago requiere el código de operación.');
        }

        if ($idCliente > 0) {
            $query = $c->prepare("SELECT dni_ruc FROM clientes WHERE id_cliente = ? AND estado = 'ACTIVO'");
            $query->execute([$idCliente]);
            $documento = $query->fetchColumn();
            if ($documento === false) {
                throw new DomainException('Seleccione un cliente activo.');
            }
        } else {
            $documento = '';
        }
        if ($comprobante === 'FACTURA' && !preg_match('/^\d{11}$/', (string) $documento)) {
            throw new DomainException('Para emitir factura, seleccione un cliente con RUC de 11 dígitos.');
        }

        if ($modo === 'SOLES') {
            $total = round($valor, 2);
            $galones = round($total / $precio, 3);
        } else {
            $galones = round($valor, 3);
            $total = round($galones * $precio, 2);
        }

        $c->prepare('INSERT INTO ventas (id_usuario, id_surtidor, id_producto, galones, precio_galon, total, id_cliente, placa, id_tipo_pago, codigo_operacion, tipo_comprobante)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)')
          ->execute([$idUsuario, (int) $d['id_surtidor'], $surtidor['id_producto'], $galones, $precio, $total, $idCliente ?: null,
                     strtoupper(mb_substr(trim((string) ($d['placa'] ?? '')), 0, 20)), (int) $d['id_tipo_pago'], mb_substr($codigo, 0, 60), $comprobante]);

        return ['id_venta' => (int) $c->lastInsertId(), 'galones' => $galones, 'total' => $total];
    }

    /** $idUsuario limita el listado a un usuario. El controlador lo fija para quien no es administrador. */
    public function Listar_Ventas(string $desde, string $hasta, ?int $idUsuario): array
    {
        $c = conexionBD::conexionPDO();
        $sql = "SELECT v.id_venta, v.creado_en, v.galones, v.precio_galon, v.total, v.placa, v.tipo_comprobante, v.estado, v.motivo_anulacion, v.codigo_operacion,
                       p.nombre AS producto, s.numero_maquina, s.codigo AS surtidor, c.nombre_completo AS cliente, tp.nombre AS medio_pago,
                       u.usu_usuario AS usuario, rt.numero_documento AS turno
                FROM ventas v
                LEFT JOIN productos p ON v.id_producto = p.id_producto
                LEFT JOIN surtidores s ON v.id_surtidor = s.id_surtidor
                LEFT JOIN clientes c ON v.id_cliente = c.id_cliente
                LEFT JOIN tipos_pago tp ON v.id_tipo_pago = tp.id_tipo_pago
                LEFT JOIN usuario u ON v.id_usuario = u.id_usuario
                LEFT JOIN reportes_turno rt ON v.id_reporte = rt.id_reporte
                WHERE v.creado_en >= ? AND v.creado_en < DATE_ADD(?, INTERVAL 1 DAY)";
        $params = [$desde, $hasta];
        if ($idUsuario !== null) {
            $sql .= ' AND v.id_usuario = ?';
            $params[] = $idUsuario;
        }
        $query = $c->prepare($sql . ' ORDER BY v.id_venta DESC LIMIT 1000');
        $query->execute($params);
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function Anular_Venta(int $id, string $motivo): void
    {
        if ($motivo === '') {
            throw new DomainException('Indique el motivo de la anulación.');
        }
        $c = conexionBD::conexionPDO();
        $query = $c->prepare("UPDATE ventas SET estado = 'ANULADA', motivo_anulacion = ? WHERE id_venta = ? AND estado = 'REGISTRADA'");
        $query->execute([mb_substr($motivo, 0, 255), $id]);
        if ($query->rowCount() === 0) {
            throw new DomainException('La venta no existe o ya está anulada.');
        }
    }

    /** Compara, por turno y combustible, los galones de las lecturas con los de las ventas registradas. */
    public function Conciliacion(string $desde, string $hasta): array
    {
        $c = conexionBD::conexionPDO();
        $query = $c->prepare("SELECT rt.id_reporte, rt.numero_documento, rt.fecha_reporte, rt.turno, CONCAT(u.usu_nombre, ' ', u.usu_apellido) AS grifero,
                                     p.nombre AS producto, l.galones_lecturas, COALESCE(v.galones_ventas, 0) AS galones_ventas,
                                     ROUND(l.galones_lecturas - COALESCE(v.galones_ventas, 0), 3) AS diferencia
                              FROM reportes_turno rt
                              INNER JOIN usuario u ON rt.id_usuario = u.id_usuario
                              INNER JOIN (SELECT lt.id_reporte, s.id_producto, SUM(lt.galones_vendidos) AS galones_lecturas
                                          FROM lecturas_turno lt INNER JOIN surtidores s ON lt.id_surtidor = s.id_surtidor
                                          GROUP BY lt.id_reporte, s.id_producto) l ON l.id_reporte = rt.id_reporte
                              INNER JOIN productos p ON l.id_producto = p.id_producto
                              LEFT JOIN (SELECT id_reporte, id_producto, SUM(galones) AS galones_ventas FROM ventas
                                         WHERE estado = 'REGISTRADA' AND id_reporte IS NOT NULL
                                         GROUP BY id_reporte, id_producto) v ON v.id_reporte = rt.id_reporte AND v.id_producto = l.id_producto
                              WHERE rt.estado = 'CERRADO' AND rt.fecha_reporte BETWEEN ? AND ?
                              ORDER BY rt.fecha_reporte DESC, rt.id_reporte DESC, p.nombre");
        $query->execute([$desde, $hasta]);
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }
}
