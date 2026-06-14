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
                LEFT JOIN reportes_turno rt ON vc.id_reporte = rt.id_reporte
                LEFT JOIN usuario u ON rt.id_usuario = u.id_usuario
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
                    vc.observaciones,
                    vc.created_at,
                    c.id_cliente,
                    c.nombre_completo as cliente_nombre,
                    rt.numero_documento,
                    rt.fecha_reporte,
                    rt.turno,
                    DATEDIFF(CURDATE(), vc.fecha_vencimiento) as dias_vencido
                FROM ventas_credito vc
                INNER JOIN clientes c ON vc.id_cliente = c.id_cliente
                LEFT JOIN reportes_turno rt ON vc.id_reporte = rt.id_reporte
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
        
        $query = $c->prepare($sql);
        $query->execute($params);
        $resultado = $query->fetchAll(PDO::FETCH_ASSOC);
        return $resultado;
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
                LEFT JOIN reportes_turno rt ON vc.id_reporte = rt.id_reporte
                LEFT JOIN usuario u ON rt.id_usuario = u.id_usuario
                WHERE vc.id_credito = ?";
        $query = $c->prepare($sql);
        $query->execute(array($id_credito));
        return $query->fetch(PDO::FETCH_ASSOC);
        conexionBD::cerrar_conexion();
    }

    // REGISTRAR PAGO DE CRÉDITO
    public function Registrar_Pago_Credito($id_credito, $id_tipo_pago, $codigo_operacion, $monto_pagado, $descuento = 0, $fecha_pago = '', $id_usuario = 0, $observaciones = '') {
        $c = conexionBD::conexionPDO();

        $monto_pagado = round(floatval($monto_pagado), 2);
        $descuento    = round(floatval($descuento), 2);
        if ($descuento < 0) $descuento = 0;

        // Obtener saldo actual
        $sql_saldo = "SELECT saldo_pendiente FROM ventas_credito WHERE id_credito = ?";
        $query_saldo = $c->prepare($sql_saldo);
        $query_saldo->execute(array($id_credito));
        $credito = $query_saldo->fetch(PDO::FETCH_ASSOC);
        $saldo_anterior = round(floatval($credito['saldo_pendiente']), 2);

        // Validar que monto + descuento no supere el saldo
        if (($monto_pagado + $descuento) > $saldo_anterior) {
            return -1;
        }

        // Calcular nuevo saldo
        $saldo_nuevo = round($saldo_anterior - $monto_pagado - $descuento, 2);
        if ($saldo_nuevo < 0) $saldo_nuevo = 0;

        // Registrar el pago en historial (incluye descuento)
        $fecha_pago_db = !empty($fecha_pago) ? $fecha_pago : date('Y-m-d');
        $sql_historial = "INSERT INTO historial_pagos_credito (
                            id_credito, id_tipo_pago, codigo_operacion, monto_pagado, descuento,
                            saldo_anterior, saldo_nuevo, fecha_pago, id_usuario_registro, observaciones
                          ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $query_historial = $c->prepare($sql_historial);
        $resultado = $query_historial->execute(array(
            $id_credito, $id_tipo_pago, $codigo_operacion, $monto_pagado, $descuento,
            $saldo_anterior, $saldo_nuevo, $fecha_pago_db, $id_usuario, $observaciones
        ));

        if ($resultado) {
            // Actualizar saldo en ventas_credito
            $nuevo_estado = ($saldo_nuevo <= 0) ? 'PAGADO' : 'PENDIENTE';
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

    // LISTAR CRÉDITOS AGRUPADOS POR CLIENTE
    public function Listar_Creditos_Por_Cliente($filtro_estado = 'PENDIENTE') {
        $c = conexionBD::conexionPDO();
        
        if ($filtro_estado == 'PENDIENTE') {
            // Solo mostrar clientes con saldo pendiente
            $sql = "SELECT 
                        c.id_cliente,
                        c.nombre_completo,
                        c.dni_ruc,
                        c.telefono,
                        COUNT(vc.id_credito) as total_vales,
                        COALESCE(SUM(vc.monto), 0) as monto_total,
                        COALESCE(SUM(vc.saldo_pendiente), 0) as saldo_pendiente,
                        COALESCE(SUM(vc.monto - vc.saldo_pendiente), 0) as monto_pagado,
                        MIN(vc.fecha_vencimiento) as fecha_vencimiento_mas_antigua,
                        MAX(DATEDIFF(CURDATE(), vc.fecha_vencimiento)) as dias_vencido_max
                    FROM clientes c
                    INNER JOIN ventas_credito vc ON c.id_cliente = vc.id_cliente
                    WHERE vc.estado = 'PENDIENTE'
                    GROUP BY c.id_cliente, c.nombre_completo, c.dni_ruc, c.telefono
                    HAVING saldo_pendiente > 0
                    ORDER BY dias_vencido_max DESC, saldo_pendiente DESC";
            $query = $c->prepare($sql);
            $query->execute();
        } else if ($filtro_estado == 'PAGADO') {
            // Mostrar clientes con créditos pagados
            $sql = "SELECT 
                        c.id_cliente,
                        c.nombre_completo,
                        c.dni_ruc,
                        c.telefono,
                        COUNT(vc.id_credito) as total_vales,
                        COALESCE(SUM(vc.monto), 0) as monto_total,
                        COALESCE(SUM(vc.saldo_pendiente), 0) as saldo_pendiente,
                        COALESCE(SUM(vc.monto - vc.saldo_pendiente), 0) as monto_pagado,
                        MIN(vc.fecha_vencimiento) as fecha_vencimiento_mas_antigua,
                        0 as dias_vencido_max
                    FROM clientes c
                    INNER JOIN ventas_credito vc ON c.id_cliente = vc.id_cliente
                    WHERE vc.estado = 'PAGADO'
                    GROUP BY c.id_cliente, c.nombre_completo, c.dni_ruc, c.telefono
                    ORDER BY c.nombre_completo ASC";
            $query = $c->prepare($sql);
            $query->execute();
        } else {
            // Mostrar todos los clientes con créditos
            $sql = "SELECT 
                        c.id_cliente,
                        c.nombre_completo,
                        c.dni_ruc,
                        c.telefono,
                        COUNT(vc.id_credito) as total_vales,
                        COALESCE(SUM(vc.monto), 0) as monto_total,
                        COALESCE(SUM(CASE WHEN vc.estado = 'PENDIENTE' THEN vc.saldo_pendiente ELSE 0 END), 0) as saldo_pendiente,
                        COALESCE(SUM(vc.monto - vc.saldo_pendiente), 0) as monto_pagado,
                        MIN(vc.fecha_vencimiento) as fecha_vencimiento_mas_antigua,
                        MAX(DATEDIFF(CURDATE(), vc.fecha_vencimiento)) as dias_vencido_max
                    FROM clientes c
                    INNER JOIN ventas_credito vc ON c.id_cliente = vc.id_cliente
                    GROUP BY c.id_cliente, c.nombre_completo, c.dni_ruc, c.telefono
                    ORDER BY saldo_pendiente DESC, c.nombre_completo ASC";
            $query = $c->prepare($sql);
            $query->execute();
        }
        
        // Inicializar el array con la clave 'data' vacía
        $arreglo = array("data" => array());
        $resultado = $query->fetchAll(PDO::FETCH_ASSOC);
        
        // Solo agregar datos si hay resultados
        if (count($resultado) > 0) {
            foreach ($resultado as $resp) {
                $arreglo["data"][] = $resp;
            }
        }
        
        return $arreglo;
        conexionBD::cerrar_conexion();
    }

    // LISTAR VALES DE UN CLIENTE ESPECÍFICO
    public function Listar_Vales_Cliente($id_cliente, $filtro_estado = null) {
        $c = conexionBD::conexionPDO();
        $sql = "SELECT
                    vc.id_credito,
                    vc.numero_vale,
                    vc.placa,
                    vc.monto,
                    vc.saldo_pendiente,
                    (vc.monto - vc.saldo_pendiente) as monto_pagado,
                    vc.estado,
                    vc.fecha_vencimiento,
                    vc.created_at,
                    vc.observaciones,
                    rt.numero_documento,
                    rt.fecha_reporte,
                    rt.turno,
                    CONCAT(u.usu_nombre, ' ', u.usu_apellido) as grifero_nombre,
                    DATEDIFF(CURDATE(), vc.fecha_vencimiento) as dias_vencido,
                    (SELECT fecha_pago FROM historial_pagos_credito
                     WHERE id_credito = vc.id_credito
                     ORDER BY id_pago_credito DESC LIMIT 1) AS fecha_ultimo_pago,
                    (SELECT codigo_operacion FROM historial_pagos_credito
                     WHERE id_credito = vc.id_credito
                     ORDER BY id_pago_credito DESC LIMIT 1) AS ultimo_codigo_operacion,
                    (SELECT descuento FROM historial_pagos_credito
                     WHERE id_credito = vc.id_credito
                     ORDER BY id_pago_credito DESC LIMIT 1) AS ultimo_descuento
                FROM ventas_credito vc
                LEFT JOIN reportes_turno rt ON vc.id_reporte = rt.id_reporte
                LEFT JOIN usuario u ON rt.id_usuario = u.id_usuario
                WHERE vc.id_cliente = ?";
        
        $params = array($id_cliente);
        
        // Si se especifica un estado, filtrar por él
        if ($filtro_estado && $filtro_estado != '') {
            $sql .= " AND vc.estado = ?";
            $params[] = $filtro_estado;
        }
        
        $sql .= " ORDER BY CASE WHEN vc.estado = 'PENDIENTE' THEN 0 WHEN vc.estado = 'PAGADO' THEN 1 ELSE 2 END ASC, vc.created_at ASC, vc.numero_vale ASC";
        
        $arreglo = array();
        $query = $c->prepare($sql);
        $query->execute($params);
        $resultado = $query->fetchAll(PDO::FETCH_ASSOC);
        
        error_log("Listar_Vales_Cliente - ID Cliente: $id_cliente, Estado: $filtro_estado, Resultados: " . count($resultado));
        
        foreach ($resultado as $resp) {
            $arreglo["data"][] = $resp;
        }
        return $arreglo;
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
                LIMIT " . intval($limite);
        $query = $c->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
        conexionBD::cerrar_conexion();
    }

    // ANULAR PAGO: borra historial y revierte el crédito a PENDIENTE
    public function Anular_Pago_Credito($id_credito) {
        $c = conexionBD::conexionPDO();
        $c->beginTransaction();
        try {
            // Obtener el monto original del crédito
            $sql_monto = "SELECT monto FROM ventas_credito WHERE id_credito = ?";
            $query_monto = $c->prepare($sql_monto);
            $query_monto->execute(array($id_credito));
            $credito = $query_monto->fetch(PDO::FETCH_ASSOC);
            if (!$credito) {
                $c->rollBack();
                return 0;
            }
            $monto_original = $credito['monto'];

            // Eliminar todo el historial de pagos de este crédito
            $sql_del = "DELETE FROM historial_pagos_credito WHERE id_credito = ?";
            $query_del = $c->prepare($sql_del);
            $query_del->execute(array($id_credito));

            // Revertir el crédito a PENDIENTE con saldo completo
            $sql_update = "UPDATE ventas_credito SET
                                saldo_pendiente = ?,
                                estado = 'PENDIENTE',
                                updated_at = NOW()
                           WHERE id_credito = ?";
            $query_update = $c->prepare($sql_update);
            $query_update->execute(array($monto_original, $id_credito));

            $c->commit();
            return 1;
        } catch (Exception $e) {
            $c->rollBack();
            return 0;
        }
        conexionBD::cerrar_conexion();
    }

    // REVERTIR MONTO DE PAGOS DE UN CLIENTE (más recientes primero)
    public function Revertir_Monto_Cliente($id_cliente, $monto_a_revertir) {
        $c = conexionBD::conexionPDO();
        $c->beginTransaction();
        try {
            $monto_restante = round(floatval($monto_a_revertir), 2);
            if ($monto_restante <= 0) {
                $c->rollBack();
                return ['success' => false, 'message' => 'Monto inválido'];
            }

            // Pagos del cliente ordenados del más reciente al más antiguo
            $q = $c->prepare("SELECT hpc.id_pago_credito, hpc.id_credito, hpc.monto_pagado, hpc.descuento
                               FROM historial_pagos_credito hpc
                               INNER JOIN ventas_credito vc ON hpc.id_credito = vc.id_credito
                               WHERE vc.id_cliente = ?
                               ORDER BY hpc.id_pago_credito DESC");
            $q->execute([$id_cliente]);
            $pagos = $q->fetchAll(PDO::FETCH_ASSOC);

            if (empty($pagos)) {
                $c->rollBack();
                return ['success' => false, 'message' => 'No hay pagos registrados para este cliente'];
            }

            $total_disponible = 0;
            foreach ($pagos as $p) {
                $total_disponible += floatval($p['monto_pagado']) + floatval($p['descuento']);
            }
            if ($monto_restante > round($total_disponible, 2) + 0.01) {
                $c->rollBack();
                return ['success' => false, 'message' => 'El monto a revertir (S/. ' . number_format($monto_a_revertir, 2) . ') supera el total pagado (S/. ' . number_format($total_disponible, 2) . ')'];
            }

            $vales_afectados = 0;

            foreach ($pagos as $pago) {
                if ($monto_restante <= 0) break;

                $monto_pago = round(floatval($pago['monto_pagado']) + floatval($pago['descuento']), 2);
                if ($monto_pago <= 0) continue;

                // Obtener saldo actual y monto original del vale
                $qv = $c->prepare("SELECT saldo_pendiente, monto FROM ventas_credito WHERE id_credito = ?");
                $qv->execute([$pago['id_credito']]);
                $vale = $qv->fetch(PDO::FETCH_ASSOC);

                if ($monto_restante >= $monto_pago) {
                    // Eliminar este pago completo
                    $c->prepare("DELETE FROM historial_pagos_credito WHERE id_pago_credito = ?")->execute([$pago['id_pago_credito']]);

                    $nuevo_saldo = round(floatval($vale['saldo_pendiente']) + $monto_pago, 2);
                    if ($nuevo_saldo > floatval($vale['monto'])) $nuevo_saldo = floatval($vale['monto']);

                    $c->prepare("UPDATE ventas_credito SET saldo_pendiente = ?, estado = 'PENDIENTE', updated_at = NOW() WHERE id_credito = ?")->execute([$nuevo_saldo, $pago['id_credito']]);

                    $monto_restante = round($monto_restante - $monto_pago, 2);
                } else {
                    // Revertir parcialmente: reducir monto_pagado en historial
                    $nuevo_mp = round(floatval($pago['monto_pagado']) - $monto_restante, 2);
                    if ($nuevo_mp < 0) $nuevo_mp = 0;

                    $c->prepare("UPDATE historial_pagos_credito SET monto_pagado = ?, saldo_nuevo = saldo_nuevo + ? WHERE id_pago_credito = ?")->execute([$nuevo_mp, $monto_restante, $pago['id_pago_credito']]);

                    $nuevo_saldo = round(floatval($vale['saldo_pendiente']) + $monto_restante, 2);
                    if ($nuevo_saldo > floatval($vale['monto'])) $nuevo_saldo = floatval($vale['monto']);

                    $c->prepare("UPDATE ventas_credito SET saldo_pendiente = ?, estado = 'PENDIENTE', updated_at = NOW() WHERE id_credito = ?")->execute([$nuevo_saldo, $pago['id_credito']]);

                    $monto_restante = 0;
                }
                $vales_afectados++;
            }

            $c->commit();
            return ['success' => true, 'message' => 'Se revirtieron S/. ' . number_format($monto_a_revertir, 2) . ' de los pagos más recientes.', 'vales_afectados' => $vales_afectados];
        } catch (Exception $e) {
            $c->rollBack();
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
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

    // ELIMINAR CRÉDITO (HARD DELETE)
    public function Eliminar_Credito($id_credito) {
        $c = conexionBD::conexionPDO();
        $c->beginTransaction();
        try {
            // Primero eliminar el historial de pagos asociado
            $sql_historial = "DELETE FROM historial_pagos_credito WHERE id_credito = ?";
            $query_historial = $c->prepare($sql_historial);
            $query_historial->execute(array($id_credito));

            // Luego eliminar el crédito
            $sql = "DELETE FROM ventas_credito WHERE id_credito = ?";
            $query = $c->prepare($sql);
            $query->execute(array($id_credito));
            
            $c->commit();
            return 1;
        } catch (Exception $e) {
            $c->rollBack();
            return 0;
        }
        conexionBD::cerrar_conexion();
    }

    // AGREGAR CRÉDITO MANUAL (SIN TURNO)
    public function Agregar_Credito_Manual($id_cliente, $numero_vale, $monto, $fecha_registro, $observaciones, $placa = '') {
        $c = conexionBD::conexionPDO();
        
        // Si no hay fecha de registro, usar hoy
        if (empty($fecha_registro)) {
            $fecha_registro = date('Y-m-d');
        }
        
        // Calcular fecha de vencimiento: 30 días desde la fecha de registro
        $fecha_vencimiento = date('Y-m-d', strtotime($fecha_registro . ' +30 days'));
        
        // Usar la tabla 'ventas_credito' con id_reporte = NULL para créditos manuales
        // Verificar si la columna 'placa' existe antes de incluirla en el INSERT
        $check_placa = $c->query("SHOW COLUMNS FROM ventas_credito LIKE 'placa'");
        $placa_existe = ($check_placa && $check_placa->fetch() !== false);

        if ($placa_existe) {
            $sql = "INSERT INTO ventas_credito (
                        id_cliente, id_reporte, numero_vale, placa, monto, saldo_pendiente,
                        estado, fecha_vencimiento, observaciones, created_at
                    ) VALUES (?, NULL, ?, ?, ?, ?, 'PENDIENTE', ?, ?, ?)";
            $query = $c->prepare($sql);
            $resultado = $query->execute(array($id_cliente, $numero_vale, $placa, $monto, $monto, $fecha_vencimiento, $observaciones, $fecha_registro));
        } else {
            $sql = "INSERT INTO ventas_credito (
                        id_cliente, id_reporte, numero_vale, monto, saldo_pendiente,
                        estado, fecha_vencimiento, observaciones, created_at
                    ) VALUES (?, NULL, ?, ?, ?, 'PENDIENTE', ?, ?, ?)";
            $query = $c->prepare($sql);
            $resultado = $query->execute(array($id_cliente, $numero_vale, $monto, $monto, $fecha_vencimiento, $observaciones, $fecha_registro));
        }

        if ($resultado) {
            return $c->lastInsertId();
        } else {
            return 0;
        }
        conexionBD::cerrar_conexion();
    }

    // EDITAR CRÉDITO
    public function Editar_Credito($id_credito, $numero_vale, $monto, $fecha_vencimiento, $observaciones, $estado) {
        $c = conexionBD::conexionPDO();
        
        // Construir la consulta dinámicamente según los campos que se envíen
        $campos = array();
        $valores = array();
        
        if ($numero_vale !== null) {
            $campos[] = "numero_vale = ?";
            $valores[] = $numero_vale;
        }
        
        if ($monto !== null) {
            // Si se cambia el monto, recalcular el saldo
            $campos[] = "monto = ?";
            $valores[] = $monto;
            
            // Obtener el monto pagado actual
            $sql_pagado = "SELECT (monto - saldo_pendiente) as monto_pagado FROM ventas_credito WHERE id_credito = ?";
            $query_pagado = $c->prepare($sql_pagado);
            $query_pagado->execute(array($id_credito));
            $resultado_pagado = $query_pagado->fetch(PDO::FETCH_ASSOC);
            $monto_pagado = $resultado_pagado['monto_pagado'];
            
            // Nuevo saldo = Nuevo monto - Monto pagado
            $nuevo_saldo = $monto - $monto_pagado;
            $campos[] = "saldo_pendiente = ?";
            $valores[] = $nuevo_saldo;
        }
        
        if ($fecha_vencimiento !== null) {
            $campos[] = "fecha_vencimiento = ?";
            $valores[] = $fecha_vencimiento;
        }
        
        if ($observaciones !== null) {
            $campos[] = "observaciones = ?";
            $valores[] = $observaciones;
        }
        
        if ($estado !== null) {
            $campos[] = "estado = ?";
            $valores[] = $estado;
        }
        
        if (empty($campos)) {
            return 0; // No hay nada que actualizar
        }
        
        $campos[] = "updated_at = NOW()";
        $valores[] = $id_credito;
        
        $sql = "UPDATE ventas_credito SET " . implode(", ", $campos) . " WHERE id_credito = ?";
        $query = $c->prepare($sql);
        $resultado = $query->execute($valores);
        
        if ($resultado) {
            return 1;
        } else {
            return 0;
        }
        conexionBD::cerrar_conexion();
    }

    // PAGAR TODO - TODAS LAS DEUDAS PENDIENTES DE UN CLIENTE
    public function Pagar_Todo_Cliente($id_cliente, $id_tipo_pago, $codigo_operacion, $monto_pagado, $descuento = 0, $fecha_pago = '', $id_usuario = 0, $observaciones = '') {
        $c = conexionBD::conexionPDO();

        $monto_pagado = round(floatval($monto_pagado), 2);
        $descuento    = round(floatval($descuento), 2);
        if ($descuento < 0) $descuento = 0;

        try {
            $c->beginTransaction();

            $sql_creditos = "SELECT id_credito, saldo_pendiente
                            FROM ventas_credito
                            WHERE id_cliente = ? AND estado = 'PENDIENTE' AND saldo_pendiente > 0
                            ORDER BY fecha_vencimiento ASC, created_at ASC";
            $query_creditos = $c->prepare($sql_creditos);
            $query_creditos->execute(array($id_cliente));
            $creditos = $query_creditos->fetchAll(PDO::FETCH_ASSOC);

            if (empty($creditos)) {
                $c->rollBack();
                return 0;
            }

            // Calcular saldo total
            $saldo_total = round(array_sum(array_column($creditos, 'saldo_pendiente')), 2);

            // Validar: monto + descuento no puede superar el saldo total
            if (($monto_pagado + $descuento) > $saldo_total) {
                $c->rollBack();
                return -1;
            }

            // El monto efectivo incluye el descuento (dinero + descuento = total a aplicar)
            $monto_efectivo_restante = $monto_pagado + $descuento;
            $descuento_restante      = $descuento;
            $fecha_pago_db = !empty($fecha_pago) ? $fecha_pago : date('Y-m-d');
            $creditos_pagados = 0;

            foreach ($creditos as $credito) {
                if ($monto_efectivo_restante <= 0) break;

                $id_credito     = $credito['id_credito'];
                $saldo_anterior = round(floatval($credito['saldo_pendiente']), 2);

                // Cuánto se aplica a este vale (dinero + descuento proporcional)
                $aplicado = round(min($monto_efectivo_restante, $saldo_anterior), 2);

                // Descuento proporcional para este vale
                $desc_vale   = round(min($descuento_restante, $aplicado), 2);
                $pagado_vale = round($aplicado - $desc_vale, 2);

                $saldo_nuevo = round($saldo_anterior - $aplicado, 2);
                if ($saldo_nuevo < 0) $saldo_nuevo = 0;

                $obs_pago = "PAGO TOTAL CLIENTE" . ($observaciones ? " - $observaciones" : "");

                $sql_historial = "INSERT INTO historial_pagos_credito (
                                    id_credito, id_tipo_pago, codigo_operacion,
                                    monto_pagado, descuento,
                                    saldo_anterior, saldo_nuevo,
                                    fecha_pago, id_usuario_registro, observaciones
                                  ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $query_historial = $c->prepare($sql_historial);
                $query_historial->execute(array(
                    $id_credito, $id_tipo_pago, $codigo_operacion,
                    $pagado_vale, $desc_vale,
                    $saldo_anterior, $saldo_nuevo,
                    $fecha_pago_db, $id_usuario, $obs_pago
                ));

                $nuevo_estado = ($saldo_nuevo <= 0) ? 'PAGADO' : 'PENDIENTE';
                $sql_update = "UPDATE ventas_credito SET saldo_pendiente = ?, estado = ?, updated_at = NOW() WHERE id_credito = ?";
                $query_update = $c->prepare($sql_update);
                $query_update->execute(array($saldo_nuevo, $nuevo_estado, $id_credito));

                $monto_efectivo_restante -= $aplicado;
                $descuento_restante      -= $desc_vale;
                $creditos_pagados++;
            }
            
            // Confirmar transacción
            $c->commit();
            return $creditos_pagados;
            
        } catch (Exception $e) {
            // Revertir transacción en caso de error
            $c->rollBack();
            error_log("Error en Pagar_Todo_Cliente: " . $e->getMessage());
            return 0;
        }
        
        conexionBD::cerrar_conexion();
    }
}
?>
