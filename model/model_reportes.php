<?php
require_once 'model_conexion.php';

class Modelo_Reportes extends conexionBD {
    
    // REPORTE DIARIO CONSOLIDADO
    public function Reporte_Diario($fecha) {
        $c = conexionBD::conexionPDO();
        $sql = "SELECT 
                    rt.id_reporte,
                    rt.numero_documento,
                    rt.turno,
                    rt.fecha_reporte,
                    rt.hora_inicio,
                    rt.hora_fin,
                    CONCAT(u.usu_nombre, ' ', u.usu_apellido) as grifero_nombre,
                    rt.total_diesel,
                    rt.total_regular,
                    rt.total_premium,
                    rt.total_ventas,
                    rt.galones_diesel,
                    rt.galones_regular,
                    rt.galones_premium,
                    rt.total_galones,
                    rt.monto_yape,
                    rt.monto_bcp,
                    rt.monto_visa,
                    rt.monto_efectivo,
                    rt.monto_credito,
                    rt.total_pagos,
                    rt.monto_faltante,
                    rt.estado
                FROM reportes_turno rt
                INNER JOIN usuario u ON rt.id_usuario = u.id_usuario
                WHERE rt.fecha_reporte = ?
                ORDER BY rt.turno DESC";
        $query = $c->prepare($sql);
        $query->execute(array($fecha));
        return $query->fetchAll(PDO::FETCH_ASSOC);
        conexionBD::cerrar_conexion();
    }

    // VENTAS POR COMBUSTIBLE (ÚLTIMOS 7 DÍAS)
    public function Ventas_Ultimos_7_Dias() {
        $c = conexionBD::conexionPDO();
        $sql = "SELECT 
                    fecha_reporte,
                    SUM(total_diesel) as total_diesel,
                    SUM(total_regular) as total_regular,
                    SUM(total_premium) as total_premium,
                    SUM(total_ventas) as total_ventas
                FROM reportes_turno
                WHERE fecha_reporte >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                AND estado = 'CERRADO'
                GROUP BY fecha_reporte
                ORDER BY fecha_reporte ASC";
        $query = $c->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
        conexionBD::cerrar_conexion();
    }

    // VENTAS POR COMBUSTIBLE (MES ACTUAL)
    public function Ventas_Mes_Actual() {
        $c = conexionBD::conexionPDO();
        $sql = "SELECT 
                    SUM(total_diesel) as total_diesel,
                    SUM(total_regular) as total_regular,
                    SUM(total_premium) as total_premium,
                    SUM(total_ventas) as total_ventas,
                    SUM(galones_diesel) as galones_diesel,
                    SUM(galones_regular) as galones_regular,
                    SUM(galones_premium) as galones_premium,
                    SUM(total_galones) as total_galones
                FROM reportes_turno
                WHERE MONTH(fecha_reporte) = MONTH(CURDATE())
                AND YEAR(fecha_reporte) = YEAR(CURDATE())
                AND estado = 'CERRADO'";
        $query = $c->prepare($sql);
        $query->execute();
        return $query->fetch(PDO::FETCH_ASSOC);
        conexionBD::cerrar_conexion();
    }

    // VENTAS POR TURNO (MES ACTUAL)
    public function Ventas_Por_Turno_Mes() {
        $c = conexionBD::conexionPDO();
        $sql = "SELECT 
                    turno,
                    COUNT(*) as total_turnos,
                    SUM(total_ventas) as total_ventas,
                    AVG(total_ventas) as promedio_ventas
                FROM reportes_turno
                WHERE MONTH(fecha_reporte) = MONTH(CURDATE())
                AND YEAR(fecha_reporte) = YEAR(CURDATE())
                AND estado = 'CERRADO'
                GROUP BY turno";
        $query = $c->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
        conexionBD::cerrar_conexion();
    }

    // DESEMPEÑO POR GRIFERO (MES ACTUAL)
    public function Desempeno_Por_Grifero_Mes() {
        $c = conexionBD::conexionPDO();
        $sql = "SELECT 
                    u.id_usuario,
                    CONCAT(u.usu_nombre, ' ', u.usu_apellido) as grifero_nombre,
                    COUNT(rt.id_reporte) as total_turnos,
                    SUM(rt.total_ventas) as total_ventas,
                    AVG(rt.total_ventas) as promedio_ventas,
                    SUM(rt.monto_faltante) as total_faltante
                FROM reportes_turno rt
                INNER JOIN usuario u ON rt.id_usuario = u.id_usuario
                WHERE MONTH(rt.fecha_reporte) = MONTH(CURDATE())
                AND YEAR(rt.fecha_reporte) = YEAR(CURDATE())
                AND rt.estado = 'CERRADO'
                GROUP BY u.id_usuario
                ORDER BY total_ventas DESC";
        $query = $c->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
        conexionBD::cerrar_conexion();
    }

    // MÉTODOS DE PAGO (MES ACTUAL)
    public function Metodos_Pago_Mes() {
        $c = conexionBD::conexionPDO();
        $sql = "SELECT 
                    SUM(monto_yape) as total_yape,
                    SUM(monto_bcp) as total_bcp,
                    SUM(monto_visa) as total_visa,
                    SUM(monto_efectivo) as total_efectivo,
                    SUM(monto_credito) as total_credito
                FROM reportes_turno
                WHERE MONTH(fecha_reporte) = MONTH(CURDATE())
                AND YEAR(fecha_reporte) = YEAR(CURDATE())
                AND estado = 'CERRADO'";
        $query = $c->prepare($sql);
        $query->execute();
        return $query->fetch(PDO::FETCH_ASSOC);
        conexionBD::cerrar_conexion();
    }

    // CRÉDITOS DEL MES
    public function Creditos_Mes() {
        $c = conexionBD::conexionPDO();
        $sql = "SELECT 
                    COUNT(*) as total_creditos,
                    SUM(monto) as monto_total,
                    SUM(saldo_pendiente) as saldo_pendiente,
                    SUM(monto - saldo_pendiente) as monto_pagado
                FROM ventas_credito
                WHERE MONTH(created_at) = MONTH(CURDATE())
                AND YEAR(created_at) = YEAR(CURDATE())";
        $query = $c->prepare($sql);
        $query->execute();
        return $query->fetch(PDO::FETCH_ASSOC);
        conexionBD::cerrar_conexion();
    }

    // TOTALES DEL DÍA
    public function Totales_Dia($fecha = null) {
        $c = conexionBD::conexionPDO();
        if (!$fecha) {
            $fecha = date('Y-m-d');
        }
        $sql = "SELECT 
                    COUNT(*) as total_turnos,
                    SUM(total_ventas) as total_ventas,
                    SUM(total_pagos) as total_pagos,
                    SUM(monto_faltante) as total_faltante
                FROM reportes_turno
                WHERE fecha_reporte = ?
                AND estado = 'CERRADO'";
        $query = $c->prepare($sql);
        $query->execute(array($fecha));
        return $query->fetch(PDO::FETCH_ASSOC);
        conexionBD::cerrar_conexion();
    }

    // TURNOS ACTIVOS HOY
    public function Turnos_Activos_Hoy() {
        $c = conexionBD::conexionPDO();
        $sql = "SELECT COUNT(*) as total 
                FROM reportes_turno 
                WHERE fecha_reporte = CURDATE() 
                AND estado = 'ABIERTO'";
        $query = $c->prepare($sql);
        $query->execute();
        $resultado = $query->fetch(PDO::FETCH_ASSOC);
        return $resultado['total'];
        conexionBD::cerrar_conexion();
    }

    // CRÉDITOS PENDIENTES TOTAL
    public function Total_Creditos_Pendientes() {
        $c = conexionBD::conexionPDO();
        $sql = "SELECT COUNT(*) as total 
                FROM ventas_credito 
                WHERE estado = 'PENDIENTE'";
        $query = $c->prepare($sql);
        $query->execute();
        $resultado = $query->fetch(PDO::FETCH_ASSOC);
        return $resultado['total'];
        conexionBD::cerrar_conexion();
    }

    // REPORTES PENDIENTES DE VALIDACIÓN
    public function Total_Reportes_Pendientes() {
        $c = conexionBD::conexionPDO();
        $sql = "SELECT COUNT(*) as total 
                FROM reportes_turno 
                WHERE estado = 'CERRADO' 
                AND id_administrador IS NULL";
        $query = $c->prepare($sql);
        $query->execute();
        $resultado = $query->fetch(PDO::FETCH_ASSOC);
        return $resultado['total'];
        conexionBD::cerrar_conexion();
    }

    // REPORTE MENSUAL COMPLETO
    public function Reporte_Mensual($mes, $anio) {
        $c = conexionBD::conexionPDO();
        $sql = "SELECT 
                    DATE(fecha_reporte) as fecha,
                    COUNT(*) as total_turnos,
                    SUM(total_ventas) as total_ventas,
                    SUM(total_diesel) as total_diesel,
                    SUM(total_regular) as total_regular,
                    SUM(total_premium) as total_premium,
                    SUM(total_galones) as total_galones
                FROM reportes_turno
                WHERE MONTH(fecha_reporte) = ?
                AND YEAR(fecha_reporte) = ?
                AND estado = 'CERRADO'
                GROUP BY DATE(fecha_reporte)
                ORDER BY fecha ASC";
        $query = $c->prepare($sql);
        $query->execute(array($mes, $anio));
        return $query->fetchAll(PDO::FETCH_ASSOC);
        conexionBD::cerrar_conexion();
    }
}
?>
