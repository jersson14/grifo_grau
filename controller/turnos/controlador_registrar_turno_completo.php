<?php
require_once '../../model/model_turnos.php';

// Recibir datos del formulario
$numero_documento = htmlspecialchars($_POST['numero_documento'], ENT_QUOTES, 'UTF-8');
$id_usuario = htmlspecialchars($_POST['id_usuario'], ENT_QUOTES, 'UTF-8');
$turno = htmlspecialchars($_POST['turno'], ENT_QUOTES, 'UTF-8');
$fecha = htmlspecialchars($_POST['fecha'], ENT_QUOTES, 'UTF-8');
$hora_inicio = htmlspecialchars($_POST['hora_inicio'], ENT_QUOTES, 'UTF-8');
$hora_fin = htmlspecialchars($_POST['hora_fin'], ENT_QUOTES, 'UTF-8');

$lecturas = json_decode($_POST['lecturas'], true);
$pagos = json_decode($_POST['pagos'], true);
$creditos = json_decode($_POST['creditos'], true);

$descuentos = floatval($_POST['descuentos']);
$otros_gastos = floatval($_POST['otros_gastos']);
$monto_efectivo = floatval($_POST['monto_efectivo']);

// Validar datos obligatorios
if (empty($numero_documento) || empty($id_usuario) || empty($turno) || empty($fecha) || empty($hora_inicio) || empty($hora_fin)) {
    echo json_encode([
        'success' => false,
        'message' => 'Faltan datos obligatorios'
    ]);
    exit;
}

if (empty($lecturas) || !is_array($lecturas)) {
    echo json_encode([
        'success' => false,
        'message' => 'Debe registrar al menos una lectura'
    ]);
    exit;
}

try {
    $modelo = new Modelo_Turnos();
    $c = conexionBD::conexionPDO();
    
    if ($c === null) {
        throw new Exception("No se pudo establecer conexión a la base de datos");
    }
    
    $c->beginTransaction();
    
    // 1. CREAR EL TURNO DIRECTAMENTE EN ESTADO CERRADO
    $sql_turno = "INSERT INTO reportes_turno (
                    numero_documento, id_usuario, turno, fecha_reporte, 
                    hora_inicio, hora_fin, estado, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, 'CERRADO', NOW())";
    $query_turno = $c->prepare($sql_turno);
    $resultado = $query_turno->execute(array($numero_documento, $id_usuario, $turno, $fecha, $hora_inicio, $hora_fin));
    
    if (!$resultado) {
        throw new Exception("Error al crear el turno");
    }
    
    $id_reporte = $c->lastInsertId();
    error_log("Turno creado con ID: $id_reporte");
    
    // 2. REGISTRAR LECTURAS
    $sql_lectura = "INSERT INTO lecturas_turno (
                        id_reporte, id_surtidor, lectura_anterior, lectura_actual, 
                        galones_vendidos, precio_galon, total_soles, created_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
    $stmt_lectura = $c->prepare($sql_lectura);
    
    $totales_combustible = [
        'DIESEL' => 0,
        'REGULAR' => 0,
        'PREMIUM' => 0
    ];
    $galones_combustible = [
        'DIESEL' => 0,
        'REGULAR' => 0,
        'PREMIUM' => 0
    ];
    
    foreach ($lecturas as $lectura) {
        $id_surtidor = $lectura['id_surtidor'];
        $lectura_inicial = floatval($lectura['lectura_inicial']);
        $lectura_final = floatval($lectura['lectura_final']);
        $precio = floatval($lectura['precio']);
        
        $galones = $lectura_final - $lectura_inicial;
        $total = $galones * $precio;
        
        $stmt_lectura->execute(array(
            $id_reporte,
            $id_surtidor,
            $lectura_inicial,
            $lectura_final,
            $galones,
            $precio,
            $total
        ));
        
        // Obtener tipo de producto para acumular totales
        $sql_producto = "SELECT p.tipo FROM surtidores s 
                        INNER JOIN productos p ON s.id_producto = p.id_producto 
                        WHERE s.id_surtidor = ?";
        $query_producto = $c->prepare($sql_producto);
        $query_producto->execute(array($id_surtidor));
        $producto = $query_producto->fetch(PDO::FETCH_ASSOC);
        
        if ($producto && isset($totales_combustible[$producto['tipo']])) {
            $totales_combustible[$producto['tipo']] += $total;
            $galones_combustible[$producto['tipo']] += $galones;
        }
    }
    
    $total_ventas = array_sum($totales_combustible);
    $total_galones = array_sum($galones_combustible);
    
    // 3. REGISTRAR PAGOS
    $sql_pago = "INSERT INTO pagos_reporte (id_reporte, id_tipo_pago, codigo_operacion, monto, observaciones, created_at) 
                VALUES (?, ?, ?, ?, ?, NOW())";
    $stmt_pago = $c->prepare($sql_pago);
    
    $total_pagos = 0;
    $monto_yape = 0;
    $monto_bcp = 0;
    $monto_visa = 0;
    
    if (!empty($pagos) && is_array($pagos)) {
        foreach ($pagos as $pago) {
            $id_tipo_pago = $pago['id_tipo_pago'];
            $codigo_operacion = $pago['codigo_operacion'] ?? '';
            $monto = floatval($pago['monto']);
            $observaciones = $pago['observaciones'] ?? '';
            
            $stmt_pago->execute(array(
                $id_reporte,
                $id_tipo_pago,
                $codigo_operacion,
                $monto,
                $observaciones
            ));
            
            $total_pagos += $monto;
            
            // Obtener código del tipo de pago para acumular por método
            $sql_tipo = "SELECT codigo FROM tipos_pago WHERE id_tipo_pago = ?";
            $query_tipo = $c->prepare($sql_tipo);
            $query_tipo->execute(array($id_tipo_pago));
            $tipo = $query_tipo->fetch(PDO::FETCH_ASSOC);
            
            if ($tipo) {
                $codigo = strtoupper($tipo['codigo']);
                if ($codigo == 'YAPE') $monto_yape += $monto;
                elseif ($codigo == 'BCP') $monto_bcp += $monto;
                elseif ($codigo == 'VISA') $monto_visa += $monto;
            }
        }
    }
    
    // 4. REGISTRAR CRÉDITOS
    $sql_credito = "INSERT INTO ventas_credito (
                        id_reporte, id_cliente, numero_vale, monto, saldo_pendiente, 
                        estado, fecha_vencimiento, observaciones, created_at
                    ) VALUES (?, ?, ?, ?, ?, 'PENDIENTE', ?, '', NOW())";
    $stmt_credito = $c->prepare($sql_credito);
    
    $total_creditos = 0;
    
    if (!empty($creditos) && is_array($creditos)) {
        foreach ($creditos as $credito) {
            $id_cliente = $credito['id_cliente'];
            $numero_vale = $credito['numero_vale'] ?? '';
            $monto = floatval($credito['monto']);
            $fecha_vencimiento = $credito['fecha_vencimiento'] ?? null;
            
            $stmt_credito->execute(array(
                $id_reporte,
                $id_cliente,
                $numero_vale,
                $monto,
                $monto, // saldo_pendiente inicial = monto
                $fecha_vencimiento
            ));
            
            $total_creditos += $monto;
        }
    }
    
    // 5. CALCULAR FALTANTE
    $total_justificado = $total_pagos + $total_creditos + $otros_gastos + $monto_efectivo;
    $total_neto_ventas = $total_ventas - $descuentos;
    $faltante = $total_justificado - $total_neto_ventas;
    
    // 6. ACTUALIZAR TOTALES DEL REPORTE
    $sql_update = "UPDATE reportes_turno SET 
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
                    updated_at = NOW()
                WHERE id_reporte = ?";
    
    $query_update = $c->prepare($sql_update);
    $resultado_update = $query_update->execute(array(
        $totales_combustible['DIESEL'],
        $totales_combustible['REGULAR'],
        $totales_combustible['PREMIUM'],
        $total_ventas,
        $galones_combustible['DIESEL'],
        $galones_combustible['REGULAR'],
        $galones_combustible['PREMIUM'],
        $total_galones,
        $descuentos,
        $otros_gastos,
        $monto_yape,
        $monto_bcp,
        $monto_visa,
        $monto_efectivo,
        $total_creditos,
        $total_pagos,
        $faltante,
        $id_reporte
    ));
    
    if (!$resultado_update) {
        throw new Exception("Error al actualizar totales del turno");
    }
    
    // 7. ACTUALIZAR LECTURAS ACTUALES DE LOS SURTIDORES
    $sql_update_surtidor = "UPDATE surtidores s
                           INNER JOIN lecturas_turno lt ON s.id_surtidor = lt.id_surtidor
                           SET s.lectura_actual = lt.lectura_actual, s.updated_at = NOW()
                           WHERE lt.id_reporte = ?";
    $query_update_surtidor = $c->prepare($sql_update_surtidor);
    $query_update_surtidor->execute(array($id_reporte));
    
    // COMMIT
    $c->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Turno registrado correctamente',
        'id_reporte' => $id_reporte
    ]);
    
} catch (Exception $e) {
    if (isset($c)) {
        $c->rollBack();
    }
    error_log("Error al registrar turno completo: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error al registrar el turno: ' . $e->getMessage()
    ]);
} finally {
    if (isset($c)) {
        $c = null;
    }
}
?>
