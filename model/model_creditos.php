<?php
require_once 'model_conexion.php';

class Modelo_Creditos extends conexionBD {
    
    // LISTAR CRÉDITOS PENDIENTES
    public function Listar_Creditos_Pendientes() {
        $c = conexionBD::conexionPDO();
        $sql = "SELECT 
                    vc.id_credito,
                    vc.numero_vale,
                    vc.monto,
                    vc.saldo_pendiente,
                    (vc.monto - vc.saldo_pendiente) as monto_pagado,
                    vc.estado,
                    vc.fecha_vencimiento,
                    vc.created_at,
                    c.id_cliente,
                    c.nombre_completo as cliente_nombre,
                    c.dni_ruc,
                    c.telefono,
                    rt.numero_documento,
                    rt.fecha_reporte,
                    rt.turno,
                    CONCAT(u.usu_nombre, ' ', u.usu_apellido) as grifero_nombre,
                    DATEDIFF(CURDATE(), vc.fecha_vencimiento) as dias_vencido
                FROM ventas_credito vc
                INNER JOIN clientes c ON vc.id_cliente = c.id_cliente
                INNER JOIN reportes_turno rt ON vc.id_reporte = rt.id_reporte
                INNER JOIN usuario u ON rt.id_usuario = u.id_usuario
                WHERE vc.estado = 'PENDIENTE'
                ORDER BY vc.fecha_vencimiento ASC, vc.created_at DESC";
        $arreglo = array();
        $query = $c->prepare($sql);
        $query->execute();
        $resultado = $query->fetchAll(PDO::FETCH_ASSOC);
        foreach ($resultado as $resp) {
            $arreglo["data"][] = $resp;
        }
        return $arreglo;
        conexionBD::cerrar_conexion();
    }

    // LISTAR TODOS LOS CRÉDITOS (CON FILTROS)
    public function Listar_Todos_Creditos($filtro_cliente = null, $filtro_estado = null) {
        $c = conexionBD::conexionPDO();
        $sql = "SELECT 
                    vc.id_credito,
                    vc.numero_vale,
                    vc.monto,
                    vc.saldo_pendiente,
                    (vc.monto - vc.saldo_pendiente) as monto_pagado,
                    vc.estado,
                    vc.fecha_vencimiento,
                    vc.created_at,
                    c.id_cliente,
                    c.nombre_completo as cliente_nombre,
                    rt.numero_documento,
                    rt.fecha_reporte,
                    rt.turno,
                    DATEDIFF(CURDATE(), vc.fecha_vencimiento) as dias_vencido
                FROM ventas_credito vc
                INNER JOIN clientes c ON vc.id_cliente = c.id_cliente
                INNER JOIN reportes_turno rt ON vc.id_reporte = rt.id_reporte
                WHERE 1=1";
        
        $params = array();
        
        if ($filtro_cliente) {
            $sql .= " AND vc.id_cliente = ?";
            $params[] = $filtro_cliente;
        }
        
        if ($filtro_estado) {
            $sql .= " AND vc.estado = ?";
            $params[] = $filtro_estado;
        }
        
        $sql .= " ORDER BY vc.created_at DESC";
        
        $arreglo = array();
        $query = $c->prepare($sql);
        $query->execute($params);
        $resultado = $query->fetchAll(PDO::FETCH_ASSOC);
        foreach ($resultado as $resp) {
            $arreglo["data"][] = $resp;
        }
        return $arreglo;
        conexionBD::cerrar_conexion();
    }

    // OBTENER DETALLE DE CRÉDITO
    public function Obtener_Detalle_Credito($id_credito) {
        $c = conexionBD::conexionPDO();
        $sql = "SELECT 
                    vc.*,
                    c.nombre_completo as cliente_nombre,
                    c.dni_ruc,
                    c.telefono,
                    c.direccion,
                    rt.numero_documento,
                    rt.fecha_reporte,
                    rt.turno,
                    CONCAT(u.usu_nombre, ' ', u.usu_apellido) as grifero_nombre
                FROM ventas_credito vc
                INNER JOIN clientes c ON vc.id_cliente = c.id_cliente
                INNER JOIN reportes_turno rt ON vc.id_reporte = rt.id_reporte
                INNER JOIN usuario u ON rt.id_usuario = u.id_usuario
                WHERE vc.id_credito = ?";
        $query = $c->prepare($sql);
        $query->execute(array($id_credito));
        return $query->fetch(PDO::FETCH_ASSOC);
        conexionBD::cerrar_conexion();
    }

    // REGISTRAR PAGO DE CRÉDITO
    public function Registrar_Pago_Credito($id_credito, $id_tipo_pago, $codigo_operacion, $monto_pagado, $id_usuario, $observaciones) {
        $c = conexionBD::conexionPDO();
        
        // Obtener saldo actual
        $sql_saldo = "SELECT saldo_pendiente FROM ventas_credito WHERE id_credito = ?";
        $query_saldo = $c->prepare($sql_saldo);
        $query_saldo->execute(array($id_credito));
        $credito = $query_saldo->fetch(PDO::FETCH_ASSOC);
        $saldo_anterior = $credito['saldo_pendiente'];
        
        // Validar que el monto no sea mayor al saldo
        if ($monto_pagado > $saldo_anterior) {
            return -1; // Monto mayor al saldo
        }
        
        // Calcular nuevo saldo
        $saldo_nuevo = $saldo_anterior - $monto_pagado;
        
        // Registrar el pago en historial
        $sql_historial = "INSERT INTO historial_pagos_credito (
                            id_credito, id_tipo_pago, codigo_operacion, monto_pagado, 
                            saldo_anterior, saldo_nuevo, fecha_pago, id_usuario_registro, observaciones
                          ) VALUES (?, ?, ?, ?, ?, ?, NOW(), ?, ?)";
        $query_historial = $c->prepare($sql_historial);
        $resultado = $query_historial->execute(array(
            $id_credito, $id_tipo_pago, $codigo_operacion, $monto_pagado,
            $saldo_anterior, $saldo_nuevo, $id_usuario, $observaciones
        ));
        
        if ($resultado) {
            // Actualizar saldo en ventas_credito
            $nuevo_estado = ($saldo_nuevo == 0) ? 'PAGADO' : 'PENDIENTE';
            $sql_update = "UPDATE ventas_credito SET 
                            saldo_pendiente = ?,
                            estado = ?,
                            updated_at = NOW()
                          WHERE id_credito = ?";
            $query_update = $c->prepare($sql_update);
            $query_update->execute(array($saldo_nuevo, $nuevo_estado, $id_credito));
            
            return 1;
        } else {
            return 0;
        }
        conexionBD::cerrar_conexion();
    }

    // LISTAR HISTORIAL DE PAGOS DE UN CRÉDITO
    public function Listar_Historial_Pagos_Credito($id_credito) {
        $c = conexionBD::conexionPDO();
        $sql = "SELECT 
                    hpc.id_pago_credito,
                    hpc.monto_pagado,
                    hpc.saldo_anterior,
                    hpc.saldo_nuevo,
                    hpc.codigo_operacion,
                    hpc.fecha_pago,
                    hpc.observaciones,
                    tp.nombre as tipo_pago_nombre,
                    tp.codigo as tipo_pago_codigo,
                    CONCAT(u.usu_nombre, ' ', u.usu_apellido) as registrado_por
                FROM historial_pagos_credito hpc
                INNER JOIN tipos_pago tp ON hpc.id_tipo_pago = tp.id_tipo_pago
                INNER JOIN usuario u ON hpc.id_usuario_registro = u.id_usuario
                WHERE hpc.id_credito = ?
                ORDER BY hpc.fecha_pago DESC";
        $arreglo = array();
        $query = $c->prepare($sql);
        $query->execute(array($id_credito));
        $resultado = $query->fetchAll(PDO::FETCH_ASSOC);
        foreach ($resultado as $resp) {
            $arreglo["data"][] = $resp;
        }
        return $arreglo;
        conexionBD::cerrar_conexion();
    }

    // OBTENER RESUMEN DE CRÉDITOS
    public function Obtener_Resumen_Creditos() {
        $c = conexionBD::conexionPDO();
        $sql = "SELECT 
                    COUNT(*) as total_creditos,
                    SUM(monto) as monto_total,
                    SUM(saldo_pendiente) as saldo_pendiente,
                    SUM(monto - saldo_pendiente) as monto_pagado,
                    SUM(CASE WHEN estado = 'PENDIENTE' THEN 1 ELSE 0 END) as creditos_pendientes,
                    SUM(CASE WHEN estado = 'PAGADO' THEN 1 ELSE 0 END) as creditos_pagados,
                    SUM(CASE WHEN estado = 'PENDIENTE' AND fecha_vencimiento < CURDATE() THEN 1 ELSE 0 END) as creditos_vencidos
                FROM ventas_credito";
        $query = $c->prepare($sql);
        $query->execute();
        return $query->fetch(PDO::FETCH_ASSOC);
        conexionBD::cerrar_conexion();
    }

    // TOP CLIENTES CON MÁS DEUDA
    public function Top_Clientes_Deuda($limite = 10) {
        $c = conexionBD::conexionPDO();
        $sql = "SELECT 
                    c.id_cliente,
                    c.nombre_completo,
                    c.dni_ruc,
                    c.telefono,
                    COUNT(vc.id_credito) as total_creditos,
                    SUM(vc.monto) as monto_total,
                    SUM(vc.saldo_pendiente) as saldo_pendiente,
                    SUM(vc.monto - vc.saldo_pendiente) as monto_pagado
                FROM clientes c
                INNER JOIN ventas_credito vc ON c.id_cliente = vc.id_cliente
                WHERE vc.estado = 'PENDIENTE'
                GROUP BY c.id_cliente
                HAVING saldo_pendiente > 0
                ORDER BY saldo_pendiente DESC
                LIMIT ?";
        $query = $c->prepare($sql);
        $query->execute(array($limite));
        return $query->fetchAll(PDO::FETCH_ASSOC);
        conexionBD::cerrar_conexion();
    }

    // ANULAR CRÉDITO
    public function Anular_Credito($id_credito, $motivo) {
        $c = conexionBD::conexionPDO();
        $sql = "UPDATE ventas_credito SET 
                    estado = 'ANULADO',
                    observaciones = CONCAT(COALESCE(observaciones, ''), ' | ANULADO: ', ?),
                    updated_at = NOW()
                WHERE id_credito = ?";
        $query = $c->prepare($sql);
        $resultado = $query->execute(array($motivo, $id_credito));
        if ($resultado) {
            return 1;
        } else {
            return 0;
        }
        conexionBD::cerrar_conexion();
    }
}
?>
