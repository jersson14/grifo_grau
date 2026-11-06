<?php
require_once 'model_conexion.php';
require_once 'model_configuracion.php';
require_once 'model_inventario.php';

class Modelo_Caja extends conexionBD
{
    /** Denominaciones en circulación en soles. */
    public const DENOMINACIONES = ['200', '100', '50', '20', '10', '5', '2', '1', '0.5', '0.2', '0.1'];

    /**
     * Turnos cerrados con su efectivo. Efectivo esperado = ventas netas menos pagos electrónicos, créditos y gastos,
     * que equivale a lo declarado menos el faltante calculado al cerrar (monto_faltante negativo es faltante).
     */
    public function Listar_Turnos(string $desde, string $hasta): array
    {
        $c = conexionBD::conexionPDO();
        $query = $c->prepare("SELECT rt.id_reporte, rt.numero_documento, rt.fecha_reporte, rt.turno,
                                     CONCAT(u.usu_nombre, ' ', u.usu_apellido) AS grifero,
                                     rt.monto_efectivo AS efectivo_declarado,
                                     ROUND(rt.monto_efectivo - rt.monto_faltante, 2) AS efectivo_esperado,
                                     a.id_arqueo, a.efectivo_contado, a.diferencia, a.resolucion, a.nota_resolucion, a.denominaciones, a.observaciones
                              FROM reportes_turno rt
                              INNER JOIN usuario u ON rt.id_usuario = u.id_usuario
                              LEFT JOIN arqueos a ON a.id_reporte = rt.id_reporte
                              WHERE rt.estado = 'CERRADO' AND rt.fecha_reporte BETWEEN ? AND ?
                              ORDER BY rt.fecha_reporte DESC, rt.id_reporte DESC LIMIT 500");
        $query->execute([$desde, $hasta]);
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Guarda el conteo. El total lo calcula el servidor a partir de las cantidades por denominación. */
    public function Registrar_Arqueo(int $idReporte, array $cantidades, string $observaciones, int $idUsuario): array
    {
        $contado = 0.0;
        $limpias = [];
        foreach (self::DENOMINACIONES as $denominacion) {
            $cantidad = $cantidades[$denominacion] ?? 0;
            if (filter_var($cantidad, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 1000000]]) === false) {
                throw new DomainException('Las cantidades deben ser números enteros, sin negativos.');
            }
            $limpias[$denominacion] = (int) $cantidad;
            $contado += (float) $denominacion * (int) $cantidad;
        }
        $contado = round($contado, 2);

        $c = conexionBD::conexionPDO();
        $c->beginTransaction();
        try {
            $query = $c->prepare("SELECT ROUND(monto_efectivo - monto_faltante, 2) FROM reportes_turno WHERE id_reporte = ? AND estado = 'CERRADO'");
            $query->execute([$idReporte]);
            $esperado = $query->fetchColumn();
            if ($esperado === false) {
                throw new DomainException('Solo se puede arquear un turno cerrado.');
            }
            $query = $c->prepare('SELECT resolucion FROM arqueos WHERE id_reporte = ? FOR UPDATE');
            $query->execute([$idReporte]);
            $previo = $query->fetchColumn();
            if ($previo !== false && $previo !== 'PENDIENTE') {
                throw new DomainException('La diferencia de este arqueo ya fue resuelta y no se puede modificar.');
            }

            $diferencia = round($contado - (float) $esperado, 2);
            $c->prepare('INSERT INTO arqueos (id_reporte, denominaciones, efectivo_contado, efectivo_esperado, diferencia, id_usuario, observaciones)
                         VALUES (?, ?, ?, ?, ?, ?, ?)
                         ON DUPLICATE KEY UPDATE denominaciones = VALUES(denominaciones), efectivo_contado = VALUES(efectivo_contado),
                             efectivo_esperado = VALUES(efectivo_esperado), diferencia = VALUES(diferencia), id_usuario = VALUES(id_usuario),
                             observaciones = VALUES(observaciones), actualizado_en = NOW()')
              ->execute([$idReporte, json_encode($limpias), $contado, $esperado, $diferencia, $idUsuario, mb_substr($observaciones, 0, 255)]);
            $this->Auditar($c, $idUsuario, $previo === false ? 'ARQUEO' : 'ARQUEO_MODIFICADO', "turno $idReporte, contado $contado, diferencia $diferencia");
            $c->commit();
        } catch (Throwable $e) {
            $c->rollBack();
            throw $e;
        }

        $config = new Modelo_Configuracion();
        $tolerancia = (float) $config->Obtener('turno_tolerancia_soles', '1');
        return ['contado' => $contado, 'esperado' => (float) $esperado, 'diferencia' => $diferencia, 'dentro_tolerancia' => abs($diferencia) <= $tolerancia];
    }

    /** Decide qué pasa con una diferencia: se descuenta al grifero, se condona o se justifica. */
    public function Resolver_Diferencia(int $idArqueo, string $resolucion, string $nota, int $idUsuario): void
    {
        if (!in_array($resolucion, ['DESCONTADO', 'CONDONADO', 'JUSTIFICADO'], true) || $nota === '') {
            throw new DomainException('Indique la resolución y una nota que la explique.');
        }
        $c = conexionBD::conexionPDO();
        $query = $c->prepare("UPDATE arqueos SET resolucion = ?, nota_resolucion = ?, actualizado_en = NOW()
                              WHERE id_arqueo = ? AND resolucion = 'PENDIENTE' AND diferencia <> 0");
        $query->execute([$resolucion, mb_substr($nota, 0, 255), $idArqueo]);
        if ($query->rowCount() === 0) {
            throw new DomainException('Este arqueo no tiene una diferencia pendiente.');
        }
        $this->Auditar($c, $idUsuario, 'ARQUEO_RESUELTO', "arqueo $idArqueo: $resolucion");
    }

    /** Compara, por medio de pago, lo declarado al cerrar con las ventas individuales del turno. */
    public function Comparar_Medios(int $idReporte): array
    {
        $c = conexionBD::conexionPDO();
        $query = $c->prepare("SELECT tp.nombre AS medio,
                                     CASE WHEN tp.codigo = 'EFECTIVO' THEN (SELECT monto_efectivo FROM reportes_turno WHERE id_reporte = :r1)
                                          ELSE (SELECT COALESCE(SUM(pr.monto), 0) FROM pagos_reporte pr WHERE pr.id_reporte = :r2 AND pr.id_tipo_pago = tp.id_tipo_pago) END AS declarado,
                                     (SELECT COALESCE(SUM(v.total), 0) FROM ventas v WHERE v.id_reporte = :r3 AND v.estado = 'REGISTRADA' AND v.id_tipo_pago = tp.id_tipo_pago) AS ventas
                              FROM tipos_pago tp
                              WHERE tp.codigo NOT IN ('DESCUENTO', 'OTROS_GASTOS')
                              ORDER BY tp.id_tipo_pago");
        $query->execute(['r1' => $idReporte, 'r2' => $idReporte, 'r3' => $idReporte]);
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function Registrar_Deposito(string $fecha, string $banco, string $operacion, float $monto, string $observaciones, int $idUsuario): void
    {
        $monto = round($monto, 2);
        if (!Modelo_Inventario::Fecha_Valida($fecha) || $fecha > date('Y-m-d') || $banco === '' || $operacion === '' || $monto <= 0) {
            throw new DomainException('Revise la fecha, el banco, el número de operación y el monto.');
        }
        $c = conexionBD::conexionPDO();
        $duplicado = $c->prepare("SELECT COUNT(*) FROM depositos WHERE banco = ? AND numero_operacion = ? AND estado = 'REGISTRADO'");
        $duplicado->execute([$banco, $operacion]);
        if ($duplicado->fetchColumn()) {
            throw new DomainException('Ya existe un depósito con ese número de operación en este banco.');
        }
        $c->prepare('INSERT INTO depositos (fecha, banco, numero_operacion, monto, id_usuario, observaciones) VALUES (?, ?, ?, ?, ?, ?)')
          ->execute([$fecha, mb_substr($banco, 0, 60), mb_substr($operacion, 0, 60), $monto, $idUsuario, mb_substr($observaciones, 0, 255)]);
    }

    public function Listar_Depositos(string $desde, string $hasta): array
    {
        $c = conexionBD::conexionPDO();
        $query = $c->prepare('SELECT d.*, u.usu_usuario AS usuario FROM depositos d LEFT JOIN usuario u ON d.id_usuario = u.id_usuario
                              WHERE d.fecha BETWEEN ? AND ? ORDER BY d.fecha DESC, d.id_deposito DESC LIMIT 500');
        $query->execute([$desde, $hasta]);
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function Anular_Deposito(int $id, string $motivo, int $idUsuario): void
    {
        if ($motivo === '') {
            throw new DomainException('Indique el motivo de la anulación.');
        }
        $c = conexionBD::conexionPDO();
        $query = $c->prepare("UPDATE depositos SET estado = 'ANULADO', motivo_anulacion = ? WHERE id_deposito = ? AND estado = 'REGISTRADO'");
        $query->execute([mb_substr($motivo, 0, 255), $id]);
        if ($query->rowCount() === 0) {
            throw new DomainException('El depósito no existe o ya está anulado.');
        }
        $this->Auditar($c, $idUsuario, 'DEPOSITO_ANULADO', "depósito $id: $motivo");
    }

    /** Totales del periodo y diferencias por grifero. El saldo en caja se calcula desde el inicio de los registros. */
    public function Resumen(string $desde, string $hasta): array
    {
        $c = conexionBD::conexionPDO();
        $contado = $c->prepare("SELECT COALESCE(SUM(a.efectivo_contado), 0) FROM arqueos a INNER JOIN reportes_turno rt ON a.id_reporte = rt.id_reporte WHERE rt.fecha_reporte BETWEEN ? AND ?");
        $contado->execute([$desde, $hasta]);
        $depositado = $c->prepare("SELECT COALESCE(SUM(monto), 0) FROM depositos WHERE estado = 'REGISTRADO' AND fecha BETWEEN ? AND ?");
        $depositado->execute([$desde, $hasta]);
        $saldo = $c->query("SELECT (SELECT COALESCE(SUM(efectivo_contado), 0) FROM arqueos) - (SELECT COALESCE(SUM(monto), 0) FROM depositos WHERE estado = 'REGISTRADO')")->fetchColumn();

        $griferos = $c->prepare("SELECT CONCAT(u.usu_nombre, ' ', u.usu_apellido) AS grifero, COUNT(*) AS arqueos,
                                        SUM(CASE WHEN a.diferencia < 0 THEN a.diferencia ELSE 0 END) AS faltantes,
                                        SUM(CASE WHEN a.diferencia > 0 THEN a.diferencia ELSE 0 END) AS sobrantes,
                                        SUM(CASE WHEN a.diferencia <> 0 AND a.resolucion = 'PENDIENTE' THEN a.diferencia ELSE 0 END) AS pendiente
                                 FROM arqueos a
                                 INNER JOIN reportes_turno rt ON a.id_reporte = rt.id_reporte
                                 INNER JOIN usuario u ON rt.id_usuario = u.id_usuario
                                 WHERE rt.fecha_reporte BETWEEN ? AND ?
                                 GROUP BY u.id_usuario, u.usu_nombre, u.usu_apellido
                                 ORDER BY faltantes ASC");
        $griferos->execute([$desde, $hasta]);

        return ['contado' => (float) $contado->fetchColumn(), 'depositado' => (float) $depositado->fetchColumn(),
                'saldo_caja' => (float) $saldo, 'griferos' => $griferos->fetchAll(PDO::FETCH_ASSOC)];
    }

    private function Auditar(PDO $c, int $idUsuario, string $accion, string $detalle): void
    {
        $c->prepare('INSERT INTO auditoria (id_usuario, usuario, accion, detalle, ip) VALUES (?, ?, ?, ?, ?)')
          ->execute([$idUsuario, $_SESSION['S_USU'] ?? null, $accion, $detalle, $_SERVER['REMOTE_ADDR'] ?? null]);
    }
}
