<?php
require_once 'model_conexion.php';

class Modelo_Turnos extends conexionBD {
    
    // VERIFICAR SI HAY TURNO ABIERTO DEL USUARIO
    public function Verificar_Turno_Abierto($id_usuario) {
        try {
            $c = conexionBD::conexionPDO();
            
            if ($c === null) {
                error_log("Error: No se pudo establecer conexión a la base de datos");
                return 0;
            }
            
            $sql = "SELECT COUNT(*) as total FROM reportes_turno 
                    WHERE id_usuario = ? AND estado = 'ABIERTO'";
            $query = $c->prepare($sql);
            $query->execute(array($id_usuario));
            $resultado = $query->fetch(PDO::FETCH_ASSOC);
            
            error_log("Verificar turno abierto - Usuario: $id_usuario, Total: " . $resultado['total']);
            return $resultado['total'];
        } catch (PDOException $e) {
            error_log("Excepción al verificar turno abierto: " . $e->getMessage());
            return 0;
        }
    }

    // VERIFICAR SI HAY ALGÚN TURNO ABIERTO EN EL SISTEMA
    public function Verificar_Turno_Abierto_Sistema() {
        try {
            $c = conexionBD::conexionPDO();
            
            if ($c === null) {
                error_log("Error: No se pudo establecer conexión a la base de datos");
                return 0;
            }
            
            $sql = "SELECT COUNT(*) as total FROM reportes_turno WHERE estado = 'ABIERTO'";
            $query = $c->prepare($sql);
            $query->execute();
            $resultado = $query->fetch(PDO::FETCH_ASSOC);
            
            error_log("Verificar turno abierto en sistema - Total: " . $resultado['total']);
            return $resultado['total'];
        } catch (PDOException $e) {
            error_log("Excepción al verificar turno abierto en sistema: " . $e->getMessage());
            return 0;
        }
    }

    // OBTENER INFORMACIÓN DEL TURNO ABIERTO EN EL SISTEMA
    public function Obtener_Info_Turno_Abierto_Sistema() {
        try {
            $c = conexionBD::conexionPDO();
            
            if ($c === null) {
                return null;
            }
            
            $sql = "SELECT rt.*, CONCAT(u.usu_nombre, ' ', u.usu_apellido) as grifero_nombre
                    FROM reportes_turno rt
                    INNER JOIN usuario u ON rt.id_usuario = u.id_usuario
                    WHERE rt.estado = 'ABIERTO'
                    LIMIT 1";
            $query = $c->prepare($sql);
            $query->execute();
            return $query->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Excepción al obtener info de turno abierto: " . $e->getMessage());
            return null;
        }
    }

    // GENERAR NÚMERO DE DOCUMENTO AUTOMÁTICO
    public function Generar_Numero_Documento() {
        $c = conexionBD::conexionPDO();
        $sql = "SELECT COALESCE(MAX(CAST(SUBSTRING(numero_documento, 5) AS UNSIGNED)), 0) + 1 as siguiente 
                FROM reportes_turno";
        $query = $c->prepare($sql);
        $query->execute();
        $resultado = $query->fetch(PDO::FETCH_ASSOC);
        $numero = str_pad($resultado['siguiente'], 4, '0', STR_PAD_LEFT);
        return 'DOC-' . $numero;
        conexionBD::cerrar_conexion();
    }

    // ABRIR TURNO
    public function Abrir_Turno($numero_documento, $id_usuario, $turno, $fecha, $hora_inicio, $hora_fin) {
        $c = conexionBD::conexionPDO();
        
        try {
            if ($c === null) {
                error_log("Error: No se pudo establecer conexión a la base de datos");
                return 0;
            }
            
            $c->beginTransaction();
            
            // Insertar el reporte de turno
            $sql = "INSERT INTO reportes_turno (
                        numero_documento, id_usuario, turno, fecha_reporte, 
                        hora_inicio, hora_fin, estado, created_at
                    ) VALUES (?, ?, ?, ?, ?, ?, 'ABIERTO', NOW())";
            $query = $c->prepare($sql);
            $resultado = $query->execute(array($numero_documento, $id_usuario, $turno, $fecha, $hora_inicio, $hora_fin));
            
            if ($resultado) {
                $id_reporte = $c->lastInsertId();
                error_log("Turno insertado correctamente con ID: $id_reporte");
                
                // Registrar lecturas iniciales dentro de la misma transacción
                $this->Registrar_Lecturas_Iniciales($id_reporte, $c);
                
                $c->commit();
                return $id_reporte;
            } else {
                $errorInfo = $query->errorInfo();
                error_log("Error al insertar turno: " . print_r($errorInfo, true));
                $c->rollBack();
                return 0;
            }
        } catch (Exception $e) {
            error_log("Excepción al abrir turno: " . $e->getMessage());
            if ($c) $c->rollBack();
            return 0;
        } finally {
            $c = null;
        }
    }

    // REGISTRAR LECTURAS INICIALES DEL TURNO
    public function Registrar_Lecturas_Iniciales($id_reporte, $c = null) {
        $local_connection = false;
        if ($c === null) {
            $c = conexionBD::conexionPDO();
            $local_connection = true;
        }
        
        try {
            if ($c === null) {
                error_log("Error: No se pudo establecer conexión a la base de datos");
                return 0;
            }
            
            // Obtener todos los surtidores activos con sus lecturas actuales
            $sql_surtidores = "SELECT s.id_surtidor, s.lectura_actual, p.precio_actual
                              FROM surtidores s
                              INNER JOIN productos p ON s.id_producto = p.id_producto
                              WHERE s.estado = 'ACTIVO' AND p.estado = 'ACTIVO'
                              ORDER BY s.numero_maquina, s.codigo";
            $query = $c->prepare($sql_surtidores);
            $query->execute();
            $surtidores = $query->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($surtidores) == 0) {
                error_log("Advertencia: No se encontraron surtidores activos");
                return 1; // No es un error crítico
            }
            
            // Insertar las lecturas iniciales
            $sql_insert = "INSERT INTO lecturas_turno (
                            id_reporte, id_surtidor, lectura_anterior, lectura_actual, 
                            galones_vendidos, precio_galon, total_soles, created_at
                        ) VALUES (?, ?, ?, ?, 0, ?, 0, NOW())";
            $stmt_insert = $c->prepare($sql_insert);
            
            foreach ($surtidores as $surtidor) {
                $stmt_insert->execute(array(
                    $id_reporte,
                    $surtidor['id_surtidor'],
                    $surtidor['lectura_actual'], // Lectura anterior del turno = Lectura actual del surtidor
                    $surtidor['lectura_actual'], // Lectura actual del turno (inicial) = Lectura actual del surtidor
                    $surtidor['precio_actual']
                ));
            }
            
            return 1;
        } catch (PDOException $e) {
            error_log("Error al registrar lecturas iniciales: " . $e->getMessage());
            // Si la conexión es local (no pasada por parámetro), hacemos rollback aquí si estuviéramos en transacción
            // Pero como es helper, mejor lanzar la excepción para que el padre haga rollback
            throw $e; 
        } finally {
            if ($local_connection) {
                $c = null;
            }
        }
    }


    // OBTENER TURNO ABIERTO DEL USUARIO
    public function Obtener_Turno_Abierto($id_usuario) {
        $c = conexionBD::conexionPDO();
        $sql = "SELECT * FROM reportes_turno 
                WHERE id_usuario = ? AND estado = 'ABIERTO'
                ORDER BY id_reporte DESC LIMIT 1";
        $query = $c->prepare($sql);
        $query->execute(array($id_usuario));
        return $query->fetch(PDO::FETCH_ASSOC);
        conexionBD::cerrar_conexion();
    }

    // OBTENER LECTURAS DEL TURNO
    public function Obtener_Lecturas_Turno($id_reporte) {
        $c = conexionBD::conexionPDO();
        $sql = "SELECT 
                    lt.*,
                    s.numero_maquina,
                    s.codigo,
                    p.nombre as producto_nombre,
                    p.tipo as producto_tipo
                FROM lecturas_turno lt
                INNER JOIN surtidores s ON lt.id_surtidor = s.id_surtidor
                INNER JOIN productos p ON s.id_producto = p.id_producto
                WHERE lt.id_reporte = ?
                ORDER BY 
                    s.numero_maquina,
                    CASE 
                        WHEN s.codigo LIKE '%DS1%' OR s.codigo LIKE '%BS1%' THEN 1
                        WHEN s.codigo LIKE '%DS2%' OR s.codigo LIKE '%BS2%' THEN 2
                        WHEN s.codigo LIKE '%R1%' THEN 3
                        WHEN s.codigo LIKE '%R2%' THEN 4
                        WHEN s.codigo LIKE '%P1%' THEN 5
                        WHEN s.codigo LIKE '%P2%' THEN 6
                        ELSE 7
                    END";
        $query = $c->prepare($sql);
        $query->execute(array($id_reporte));
        return $query->fetchAll(PDO::FETCH_ASSOC);
        conexionBD::cerrar_conexion();
    }

    // ACTUALIZAR LECTURA ACTUAL DE UN SURTIDOR EN EL TURNO
    public function Actualizar_Lectura_Turno($id_lectura, $lectura_actual) {
        $c = conexionBD::conexionPDO();
        
        // Obtener datos de la lectura
        $sql_get = "SELECT lectura_anterior, precio_galon FROM lecturas_turno WHERE id_lectura = ?";
        $query_get = $c->prepare($sql_get);
        $query_get->execute(array($id_lectura));
        $datos = $query_get->fetch(PDO::FETCH_ASSOC);
        
        // Calcular galones vendidos y total
        $galones_vendidos = $lectura_actual - $datos['lectura_anterior'];
        $total_soles = $galones_vendidos * $datos['precio_galon'];
        
        // Actualizar la lectura
        $sql = "UPDATE lecturas_turno SET 
                    lectura_actual = ?,
                    galones_vendidos = ?,
                    total_soles = ?
                WHERE id_lectura = ?";
        $query = $c->prepare($sql);
        $resultado = $query->execute(array($lectura_actual, $galones_vendidos, $total_soles, $id_lectura));
        
        if ($resultado) {
            return 1;
        } else {
            return 0;
        }
        conexionBD::cerrar_conexion();
    }

    // REGISTRAR PAGO DEL REPORTE
    public function Registrar_Pago_Reporte($id_reporte, $id_tipo_pago, $codigo_operacion, $monto, $observaciones) {
        $c = conexionBD::conexionPDO();
        $sql = "INSERT INTO pagos_reporte (id_reporte, id_tipo_pago, codigo_operacion, monto, observaciones, created_at) 
                VALUES (?, ?, ?, ?, ?, NOW())";
        $query = $c->prepare($sql);
        $resultado = $query->execute(array($id_reporte, $id_tipo_pago, $codigo_operacion, $monto, $observaciones));
        if ($resultado) {
            return 1;
        } else {
            return 0;
        }
        conexionBD::cerrar_conexion();
    }

    // ELIMINAR PAGO DEL REPORTE
    public function Eliminar_Pago_Reporte($id_pago) {
        $c = conexionBD::conexionPDO();
        $sql = "DELETE FROM pagos_reporte WHERE id_pago_reporte = ?";
        $query = $c->prepare($sql);
        $resultado = $query->execute(array($id_pago));
        if ($resultado) {
            return 1;
        } else {
            return 0;
        }
        conexionBD::cerrar_conexion();
    }

    // OBTENER PAGOS DEL REPORTE
    public function Obtener_Pagos_Reporte($id_reporte) {
        $c = conexionBD::conexionPDO();
        $sql = "SELECT 
                    pr.*,
                    tp.nombre as tipo_pago_nombre,
                    tp.codigo as tipo_pago_codigo
                FROM pagos_reporte pr
                INNER JOIN tipos_pago tp ON pr.id_tipo_pago = tp.id_tipo_pago
                WHERE pr.id_reporte = ?
                ORDER BY pr.created_at";
        $arreglo = array();
        $query = $c->prepare($sql);
        $query->execute(array($id_reporte));
        $resultado = $query->fetchAll(PDO::FETCH_ASSOC);
        foreach ($resultado as $resp) {
            $arreglo["data"][] = $resp;
        }
        return $arreglo;
        conexionBD::cerrar_conexion();
    }

    // REGISTRAR CRÉDITO
    public function Registrar_Credito($id_reporte, $id_cliente, $numero_vale, $monto, $fecha_vencimiento, $observaciones) {
        $c = conexionBD::conexionPDO();
        $sql = "INSERT INTO ventas_credito (
                    id_reporte, id_cliente, numero_vale, monto, saldo_pendiente, 
                    estado, fecha_vencimiento, observaciones, created_at
                ) VALUES (?, ?, ?, ?, ?, 'PENDIENTE', ?, ?, NOW())";
        $query = $c->prepare($sql);
        $resultado = $query->execute(array($id_reporte, $id_cliente, $numero_vale, $monto, $monto, $fecha_vencimiento, $observaciones));
        if ($resultado) {
            return 1;
        } else {
            return 0;
        }
        conexionBD::cerrar_conexion();
    }

    // ELIMINAR CRÉDITO
    public function Eliminar_Credito($id_credito) {
        $c = conexionBD::conexionPDO();
        $sql = "DELETE FROM ventas_credito WHERE id_credito = ?";
        $query = $c->prepare($sql);
        $resultado = $query->execute(array($id_credito));
        if ($resultado) {
            return 1;
        } else {
            return 0;
        }
        conexionBD::cerrar_conexion();
    }

    // OBTENER CRÉDITOS DEL REPORTE
    public function Obtener_Creditos_Reporte($id_reporte) {
        $c = conexionBD::conexionPDO();
        $sql = "SELECT 
                    vc.*,
                    c.nombre_completo as cliente_nombre
                FROM ventas_credito vc
                INNER JOIN clientes c ON vc.id_cliente = c.id_cliente
                WHERE vc.id_reporte = ?
                ORDER BY vc.created_at";
        $arreglo = array();
        $query = $c->prepare($sql);
        $query->execute(array($id_reporte));
        $resultado = $query->fetchAll(PDO::FETCH_ASSOC);
        foreach ($resultado as $resp) {
            $arreglo["data"][] = $resp;
        }
        return $arreglo;
        conexionBD::cerrar_conexion();
    }

    // CERRAR TURNO
    public function Cerrar_Turno($id_reporte, $descuentos, $otros_gastos, $monto_efectivo) {
        $c = conexionBD::conexionPDO();
        
        try {
            $c->beginTransaction();

            // Calcular totales (pasando la conexión)
            $totales = $this->Calcular_Totales_Turno($id_reporte, $c);
            
            // Calcular el faltante correctamente
            // Fórmula: (Pagos + Créditos + Otros Gastos + Efectivo) - (Ventas - Descuentos)
            $total_justificado = $totales['total_pagos'] + $totales['total_creditos'] + $otros_gastos + $monto_efectivo;
            $total_neto_ventas = $totales['total_ventas'] - $descuentos;
            $faltante = $total_justificado - $total_neto_ventas;
            
            // Actualizar el reporte
            $sql = "UPDATE reportes_turno SET 
                        total_diesel = ?,
                        total_regular = ?,
                        total_premium = ?,
                        total_ventas = ?,
                        galones_diesel = ?,
                        galones_regular = ?,
                        galones_premium = ?,
                        total_galones = ?,
                        monto_descuentos = ?,
                        monto_otros_gastos = ?,
                        monto_yape = ?,
                        monto_bcp = ?,
                        monto_visa = ?,
                        monto_efectivo = ?,
                        monto_credito = ?,
                        total_pagos = ?,
                        monto_faltante = ?,
                        estado = 'CERRADO',
                        updated_at = NOW()
                    WHERE id_reporte = ?";
            
            $query = $c->prepare($sql);
            $resultado = $query->execute(array(
                $totales['total_diesel'],
                $totales['total_regular'],
                $totales['total_premium'],
                $totales['total_ventas'],
                $totales['galones_diesel'],
                $totales['galones_regular'],
                $totales['galones_premium'],
                $totales['total_galones'],
                $descuentos,
                $otros_gastos,
                $totales['monto_yape'],
                $totales['monto_bcp'],
                $totales['monto_visa'],
                $monto_efectivo,
                $totales['total_creditos'],
                $totales['total_pagos'],
                $faltante,
                $id_reporte
            ));
            
            if (!$resultado) {
                throw new Exception("Error al actualizar el reporte");
            }
            
            // Actualizar las lecturas actuales de los surtidores (usando la misma conexión)
            $this->Actualizar_Lecturas_Surtidores($id_reporte, $c);
            
            $c->commit();
            return 1;
        } catch (Exception $e) {
            $c->rollBack();
            error_log("Error en Cerrar_Turno: " . $e->getMessage());
            return 0;
        } finally {
            $c = null;
        }
    }

    // CALCULAR TOTALES DEL TURNO
    private function Calcular_Totales_Turno($id_reporte, $c = null) {
        if ($c === null) $c = conexionBD::conexionPDO();
        
        // Totales por tipo de combustible
        $sql = "SELECT 
                    p.tipo,
                    SUM(lt.galones_vendidos) as galones,
                    SUM(lt.total_soles) as total
                FROM lecturas_turno lt
                INNER JOIN surtidores s ON lt.id_surtidor = s.id_surtidor
                INNER JOIN productos p ON s.id_producto = p.id_producto
                WHERE lt.id_reporte = ?
                GROUP BY p.tipo";
        $query = $c->prepare($sql);
        $query->execute(array($id_reporte));
        $resultados = $query->fetchAll(PDO::FETCH_ASSOC);
        
        $totales = array(
            'total_diesel' => 0,
            'total_regular' => 0,
            'total_premium' => 0,
            'galones_diesel' => 0,
            'galones_regular' => 0,
            'galones_premium' => 0
        );
        
        foreach ($resultados as $row) {
            if ($row['tipo'] == 'DIESEL') {
                $totales['total_diesel'] = $row['total'];
                $totales['galones_diesel'] = $row['galones'];
            } elseif ($row['tipo'] == 'REGULAR') {
                $totales['total_regular'] = $row['total'];
                $totales['galones_regular'] = $row['galones'];
            } elseif ($row['tipo'] == 'PREMIUM') {
                $totales['total_premium'] = $row['total'];
                $totales['galones_premium'] = $row['galones'];
            }
        }
        
        $totales['total_ventas'] = $totales['total_diesel'] + $totales['total_regular'] + $totales['total_premium'];
        $totales['total_galones'] = $totales['galones_diesel'] + $totales['galones_regular'] + $totales['galones_premium'];
        
        // Total de pagos por método
        $sql_pagos = "SELECT 
                        tp.codigo,
                        COALESCE(SUM(pr.monto), 0) as total
                      FROM pagos_reporte pr
                      INNER JOIN tipos_pago tp ON pr.id_tipo_pago = tp.id_tipo_pago
                      WHERE pr.id_reporte = ?
                      GROUP BY tp.codigo";
        $query_pagos = $c->prepare($sql_pagos);
        $query_pagos->execute(array($id_reporte));
        $pagos = $query_pagos->fetchAll(PDO::FETCH_ASSOC);
        
        // Inicializar montos por método de pago
        $totales['monto_efectivo'] = 0;
        $totales['monto_yape'] = 0;
        $totales['monto_bcp'] = 0;
        $totales['monto_visa'] = 0;
        $totales['total_pagos'] = 0;
        
        // Asignar montos según el código del tipo de pago
        foreach ($pagos as $pago) {
            $codigo = strtoupper($pago['codigo']);
            $monto = $pago['total'];
            
            if ($codigo == 'EFECTIVO') {
                $totales['monto_efectivo'] = $monto;
            } elseif ($codigo == 'YAPE') {
                $totales['monto_yape'] = $monto;
            } elseif ($codigo == 'BCP') {
                $totales['monto_bcp'] = $monto;
            } elseif ($codigo == 'VISA') {
                $totales['monto_visa'] = $monto;
            }
            
            $totales['total_pagos'] += $monto;
        }
        
        // Total de créditos
        $sql_creditos = "SELECT COALESCE(SUM(monto), 0) as total FROM ventas_credito WHERE id_reporte = ?";
        $query_creditos = $c->prepare($sql_creditos);
        $query_creditos->execute(array($id_reporte));
        $creditos = $query_creditos->fetch(PDO::FETCH_ASSOC);
        $totales['total_creditos'] = $creditos['total'];
        
        // Faltante: NO se calcula aquí porque necesita descuentos y otros_gastos que vienen del formulario
        // Se calculará en Cerrar_Turno
        $totales['faltante'] = 0;
        
        return $totales;
    }

    // ACTUALIZAR LECTURAS DE SURTIDORES
    private function Actualizar_Lecturas_Surtidores($id_reporte, $c = null) {
        if ($c === null) $c = conexionBD::conexionPDO();
        
        $sql = "UPDATE surtidores s
                INNER JOIN lecturas_turno lt ON s.id_surtidor = lt.id_surtidor
                SET s.lectura_actual = lt.lectura_actual, s.updated_at = NOW()
                WHERE lt.id_reporte = ?";
        $query = $c->prepare($sql);
        $query->execute(array($id_reporte));
    }

    // LISTAR TURNOS
    public function Listar_Turnos($filtro_fecha_inicio = null, $filtro_fecha_fin = null, $filtro_usuario = null, $filtro_estado = null, $filtro_validacion = null) {
        $c = conexionBD::conexionPDO();
        $sql = "SELECT 
                    rt.*,
                    CONCAT(u.usu_nombre, ' ', u.usu_apellido) as grifero_nombre,
                    CASE 
                        WHEN rt.id_administrador IS NOT NULL AND rt.fecha_aprobacion IS NOT NULL THEN 'VALIDADO'
                        WHEN rt.estado = 'CERRADO' THEN 'PENDIENTE'
                        ELSE 'N/A'
                    END as estado_validacion,
                    CONCAT(adm.usu_nombre, ' ', adm.usu_apellido) as validado_por
                FROM reportes_turno rt
                INNER JOIN usuario u ON rt.id_usuario = u.id_usuario
                LEFT JOIN usuario adm ON rt.id_administrador = adm.id_usuario
                WHERE 1=1";
        
        $params = array();
        
        if ($filtro_fecha_inicio && $filtro_fecha_fin) {
            $sql .= " AND rt.fecha_reporte BETWEEN ? AND ?";
            $params[] = $filtro_fecha_inicio;
            $params[] = $filtro_fecha_fin;
        }
        
        if ($filtro_usuario) {
            $sql .= " AND rt.id_usuario = ?";
            $params[] = $filtro_usuario;
        }
        
        if ($filtro_estado) {
            $sql .= " AND rt.estado = ?";
            $params[] = $filtro_estado;
        }
        
        if ($filtro_validacion) {
            if ($filtro_validacion == 'VALIDADO') {
                $sql .= " AND rt.id_administrador IS NOT NULL AND rt.fecha_aprobacion IS NOT NULL";
            } elseif ($filtro_validacion == 'PENDIENTE') {
                $sql .= " AND rt.estado = 'CERRADO' AND (rt.id_administrador IS NULL OR rt.fecha_aprobacion IS NULL)";
            } elseif ($filtro_validacion == 'N/A') {
                $sql .= " AND rt.estado = 'ABIERTO'";
            }
        }
        
        $sql .= " ORDER BY rt.fecha_reporte DESC, rt.turno DESC";
        
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
}
?>
