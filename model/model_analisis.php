<?php
require_once __DIR__ . '/model_conexion.php';

/**
 * Análisis gerencial: rentabilidad por combustible, ranking de griferos, morosidad y ventas por horario.
 * Solo lee. Los turnos cuentan cuando están cerrados.
 */
class Modelo_Analisis
{
    /** Días de compras con los que se estima el costo por galón, contados hacia atrás desde el fin del periodo. */
    public const DIAS_COSTO = 90;
    public const DIAS = [1 => 'Domingo', 2 => 'Lunes', 3 => 'Martes', 4 => 'Miércoles', 5 => 'Jueves', 6 => 'Viernes', 7 => 'Sábado'];

    private function c(): PDO
    {
        $c = conexionBD::conexionPDO();
        if ($c === null) {
            throw new RuntimeException('Sin conexión a la base de datos');
        }
        return $c;
    }

    /** Valida el periodo: fechas reales, en orden y de un año como máximo. */
    public static function Periodo(string $desde, string $hasta): array
    {
        $valida = fn($f) => preg_match('/^\d{4}-\d{2}-\d{2}$/', $f) && checkdate((int) substr($f, 5, 2), (int) substr($f, 8, 2), (int) substr($f, 0, 4));
        if (!$valida($desde) || !$valida($hasta)) {
            throw new DomainException('Revise las fechas del periodo.');
        }
        if ($desde > $hasta) {
            throw new DomainException('La fecha inicial es posterior a la final.');
        }
        if ((strtotime($hasta) - strtotime($desde)) / 86400 > 366) {
            throw new DomainException('El periodo puede ser de hasta un año.');
        }
        return [$desde, $hasta];
    }

    /** Por combustible: galones e ingresos según lecturas, costo promedio de compras recientes y margen bruto estimado. */
    public function Rentabilidad(string $desde, string $hasta): array
    {
        [$desde, $hasta] = self::Periodo($desde, $hasta);
        $c = $this->c();
        $q = $c->prepare("SELECT p.id_producto, p.nombre, ROUND(SUM(lt.galones_vendidos), 3) AS galones, ROUND(SUM(lt.total_soles), 2) AS ingresos
                          FROM lecturas_turno lt
                          INNER JOIN reportes_turno rt ON rt.id_reporte = lt.id_reporte AND rt.estado = 'CERRADO' AND rt.fecha_reporte BETWEEN ? AND ?
                          INNER JOIN surtidores s ON s.id_surtidor = lt.id_surtidor
                          INNER JOIN productos p ON p.id_producto = s.id_producto
                          GROUP BY p.id_producto, p.nombre HAVING galones > 0 ORDER BY ingresos DESC");
        $q->execute([$desde, $hasta]);
        $productos = $q->fetchAll(PDO::FETCH_ASSOC);

        // Costo promedio ponderado de las compras no anuladas de los últimos DIAS_COSTO días del periodo (sin percepción).
        $q = $c->prepare("SELECT t.id_producto, SUM(d.galones * d.costo_galon) / NULLIF(SUM(d.galones), 0) AS costo, SUM(d.galones) AS galones_comprados
                          FROM compras_detalle d
                          INNER JOIN compras co ON co.id_compra = d.id_compra AND co.estado <> 'ANULADA'
                          INNER JOIN tanques t ON t.id_tanque = d.id_tanque
                          WHERE co.fecha_compra BETWEEN DATE_SUB(?, INTERVAL " . self::DIAS_COSTO . " DAY) AND ?
                          GROUP BY t.id_producto");
        $q->execute([$hasta, $hasta]);
        $costos = [];
        foreach ($q->fetchAll(PDO::FETCH_ASSOC) as $f) {
            $costos[(int) $f['id_producto']] = $f['costo'] !== null ? (float) $f['costo'] : null;
        }

        $tot = ['galones' => 0.0, 'ingresos' => 0.0, 'costo' => 0.0, 'margen' => 0.0, 'ingresos_con_costo' => 0.0];
        foreach ($productos as &$p) {
            $p['galones'] = (float) $p['galones'];
            $p['ingresos'] = (float) $p['ingresos'];
            $p['precio_promedio'] = $p['galones'] > 0 ? round($p['ingresos'] / $p['galones'], 3) : null;
            $costo = $costos[(int) $p['id_producto']] ?? null;
            $p['costo_galon'] = $costo !== null ? round($costo, 4) : null;
            $p['costo_total'] = $costo !== null ? round($costo * $p['galones'], 2) : null;
            $p['margen'] = $costo !== null ? round($p['ingresos'] - $p['costo_total'], 2) : null;
            $p['margen_pct'] = $costo !== null && $p['ingresos'] > 0 ? round($p['margen'] / $p['ingresos'] * 100, 1) : null;
            $tot['galones'] += $p['galones'];
            $tot['ingresos'] += $p['ingresos'];
            if ($costo !== null) {
                $tot['costo'] += $p['costo_total'];
                $tot['margen'] += $p['margen'];
                $tot['ingresos_con_costo'] += $p['ingresos'];
            }
        }
        unset($p);
        $tot = array_map(fn($v) => round($v, 3), $tot);
        $tot['margen_pct'] = $tot['ingresos_con_costo'] > 0 ? round($tot['margen'] / $tot['ingresos_con_costo'] * 100, 1) : null;
        $tot['sin_costo'] = count(array_filter($productos, fn($p) => $p['costo_galon'] === null));

        // La tienda guarda el costo de cada línea: su margen es exacto.
        $tienda = null;
        if ($c->query("SELECT activo FROM modulos WHERE codigo = 'tienda'")->fetchColumn()) {
            $q = $c->prepare("SELECT COALESCE(SUM(d.subtotal), 0) AS ingresos, COALESCE(SUM(d.cantidad * d.costo_unitario), 0) AS costo
                              FROM tienda_ventas_detalle d INNER JOIN tienda_ventas v ON v.id_venta = d.id_venta AND v.estado = 'ACTIVA'
                              WHERE DATE(v.fecha) BETWEEN ? AND ?");
            $q->execute([$desde, $hasta]);
            $t = $q->fetch(PDO::FETCH_ASSOC);
            $tienda = ['ingresos' => round((float) $t['ingresos'], 2), 'costo' => round((float) $t['costo'], 2), 'margen' => round((float) $t['ingresos'] - (float) $t['costo'], 2)];
            $tienda['margen_pct'] = $tienda['ingresos'] > 0 ? round($tienda['margen'] / $tienda['ingresos'] * 100, 1) : null;
        }
        return ['productos' => $productos, 'totales' => $tot, 'tienda' => $tienda, 'dias_costo' => self::DIAS_COSTO];
    }

    /** Ranking de griferos por ventas de sus turnos cerrados, con el resultado del cuadre. */
    public function Griferos(string $desde, string $hasta): array
    {
        [$desde, $hasta] = self::Periodo($desde, $hasta);
        // monto_faltante negativo es faltante y positivo es sobrante.
        $q = $this->c()->prepare("SELECT u.id_usuario, TRIM(CONCAT(COALESCE(u.usu_nombre, ''), ' ', COALESCE(u.usu_apellido, ''))) AS grifero,
                                         COUNT(*) AS turnos, ROUND(SUM(rt.total_galones), 3) AS galones, ROUND(SUM(rt.total_ventas), 2) AS ventas,
                                         ROUND(SUM(CASE WHEN rt.monto_faltante < 0 THEN -rt.monto_faltante ELSE 0 END), 2) AS faltantes,
                                         ROUND(SUM(CASE WHEN rt.monto_faltante > 0 THEN rt.monto_faltante ELSE 0 END), 2) AS sobrantes,
                                         SUM(CASE WHEN ABS(COALESCE(rt.monto_faltante, 0)) < 0.01 THEN 1 ELSE 0 END) AS cuadrados,
                                         ROUND(SUM(rt.monto_credito), 2) AS creditos
                                  FROM reportes_turno rt INNER JOIN usuario u ON u.id_usuario = rt.id_usuario
                                  WHERE rt.estado = 'CERRADO' AND rt.fecha_reporte BETWEEN ? AND ?
                                  GROUP BY u.id_usuario, grifero ORDER BY ventas DESC");
        $q->execute([$desde, $hasta]);
        $filas = $q->fetchAll(PDO::FETCH_ASSOC);
        foreach ($filas as $i => &$f) {
            $f['puesto'] = $i + 1;
            foreach (['galones', 'ventas', 'faltantes', 'sobrantes', 'creditos'] as $k) {
                $f[$k] = (float) $f[$k];
            }
            $f['turnos'] = (int) $f['turnos'];
            $f['promedio_turno'] = $f['turnos'] ? round($f['ventas'] / $f['turnos'], 2) : 0;
            $f['cuadre_pct'] = $f['turnos'] ? round((int) $f['cuadrados'] / $f['turnos'] * 100) : 0;
        }
        return ['griferos' => $filas];
    }

    /** Créditos con saldo por cliente y antigüedad de la deuda vencida a la fecha de corte (hoy). */
    public function Morosidad(): array
    {
        $q = $this->c()->query("SELECT c.id_cliente, c.nombre_completo, c.dni_ruc, c.telefono, COUNT(*) AS vales,
                ROUND(SUM(vc.saldo_pendiente), 2) AS pendiente,
                ROUND(SUM(CASE WHEN vc.fecha_vencimiento IS NULL OR vc.fecha_vencimiento >= CURDATE() THEN vc.saldo_pendiente ELSE 0 END), 2) AS por_vencer,
                ROUND(SUM(CASE WHEN DATEDIFF(CURDATE(), vc.fecha_vencimiento) BETWEEN 1 AND 30 THEN vc.saldo_pendiente ELSE 0 END), 2) AS d1_30,
                ROUND(SUM(CASE WHEN DATEDIFF(CURDATE(), vc.fecha_vencimiento) BETWEEN 31 AND 60 THEN vc.saldo_pendiente ELSE 0 END), 2) AS d31_60,
                ROUND(SUM(CASE WHEN DATEDIFF(CURDATE(), vc.fecha_vencimiento) BETWEEN 61 AND 90 THEN vc.saldo_pendiente ELSE 0 END), 2) AS d61_90,
                ROUND(SUM(CASE WHEN DATEDIFF(CURDATE(), vc.fecha_vencimiento) > 90 THEN vc.saldo_pendiente ELSE 0 END), 2) AS d90,
                MAX(CASE WHEN vc.fecha_vencimiento < CURDATE() THEN DATEDIFF(CURDATE(), vc.fecha_vencimiento) END) AS dias_max
            FROM ventas_credito vc INNER JOIN clientes c ON c.id_cliente = vc.id_cliente
            WHERE vc.estado = 'PENDIENTE' AND vc.saldo_pendiente > 0
            GROUP BY c.id_cliente, c.nombre_completo, c.dni_ruc, c.telefono");
        $filas = $q->fetchAll(PDO::FETCH_ASSOC);
        $tot = ['pendiente' => 0.0, 'por_vencer' => 0.0, 'd1_30' => 0.0, 'd31_60' => 0.0, 'd61_90' => 0.0, 'd90' => 0.0];
        foreach ($filas as &$f) {
            foreach (array_keys($tot) as $k) {
                $f[$k] = (float) $f[$k];
                $tot[$k] += $f[$k];
            }
            $f['vencido'] = round($f['pendiente'] - $f['por_vencer'], 2);
            $f['dias_max'] = $f['dias_max'] !== null ? (int) $f['dias_max'] : null;
        }
        unset($f);
        // Primero quien debe más vencido, luego quien debe más.
        usort($filas, fn($a, $b) => [$b['vencido'], $b['pendiente']] <=> [$a['vencido'], $a['pendiente']]);
        $tot = array_map(fn($v) => round($v, 2), $tot);
        $tot['vencido'] = round($tot['pendiente'] - $tot['por_vencer'], 2);
        $tot['clientes'] = count($filas);
        return ['clientes' => $filas, 'totales' => $tot, 'corte' => date('Y-m-d')];
    }

    /**
     * Ventas por horario. Por hora solo hay datos con ventas individuales (módulo de ventas);
     * por día de la semana y turno, siempre, desde los turnos cerrados.
     */
    public function Horario(string $desde, string $hasta): array
    {
        [$desde, $hasta] = self::Periodo($desde, $hasta);
        $c = $this->c();
        $q = $c->prepare("SELECT DAYOFWEEK(creado_en) AS dia, HOUR(creado_en) AS hora, COUNT(*) AS ventas, ROUND(SUM(total), 2) AS total, ROUND(SUM(galones), 3) AS galones
                          FROM ventas WHERE estado = 'REGISTRADA' AND creado_en >= ? AND creado_en < DATE_ADD(?, INTERVAL 1 DAY)
                          GROUP BY dia, hora");
        $q->execute([$desde, $hasta]);
        $celdas = [];
        $porHora = array_fill(0, 24, ['ventas' => 0, 'total' => 0.0]);
        foreach ($q->fetchAll(PDO::FETCH_ASSOC) as $f) {
            $celdas[] = ['dia' => (int) $f['dia'], 'hora' => (int) $f['hora'], 'ventas' => (int) $f['ventas'], 'total' => (float) $f['total'], 'galones' => (float) $f['galones']];
            $porHora[(int) $f['hora']]['ventas'] += (int) $f['ventas'];
            $porHora[(int) $f['hora']]['total'] += (float) $f['total'];
        }

        $q = $c->prepare("SELECT DAYOFWEEK(fecha_reporte) AS dia, turno, COUNT(*) AS turnos, ROUND(AVG(total_ventas), 2) AS promedio, ROUND(SUM(total_ventas), 2) AS total
                          FROM reportes_turno WHERE estado = 'CERRADO' AND fecha_reporte BETWEEN ? AND ?
                          GROUP BY dia, turno");
        $q->execute([$desde, $hasta]);
        $turnos = array_map(fn($f) => ['dia' => (int) $f['dia'], 'turno' => $f['turno'], 'turnos' => (int) $f['turnos'], 'promedio' => (float) $f['promedio'], 'total' => (float) $f['total']],
            $q->fetchAll(PDO::FETCH_ASSOC));

        $hora = null;
        foreach ($porHora as $h => $v) {
            if ($v['total'] > 0 && ($hora === null || $v['total'] > $porHora[$hora]['total'])) {
                $hora = $h;
            }
        }
        return ['celdas' => $celdas, 'por_hora' => $porHora, 'hora_pico' => $hora, 'turnos' => $turnos, 'dias' => self::DIAS];
    }
}
