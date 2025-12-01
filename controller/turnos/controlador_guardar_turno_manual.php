<?php
require_once '../../model/model_conexion.php';

$id_reporte = htmlspecialchars($_POST['id_reporte'], ENT_QUOTES, 'UTF-8');
$lecturas = json_decode($_POST['lecturas'], true);
$pagos = json_decode($_POST['pagos'], true);
$creditos = json_decode($_POST['creditos'], true);

// Obtener conexión MySQLi
$host = "localhost";
$port = "3307";
$usuario = "root";
$contrasena = "";
$bdName = "grifo_grau";

$conn = new mysqli($host, $usuario, $contrasena, $bdName, $port);
$conn->begin_transaction();

try {
    // 1. ACTUALIZAR LECTURAS
    foreach ($lecturas as $lectura) {
        $id_lectura = $lectura['id_lectura'];
        $lectura_actual = $lectura['lectura_actual'];
        
        $sql = "UPDATE reporte_lecturas SET lectura_actual = ? WHERE id_lectura = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("di", $lectura_actual, $id_lectura);
        $stmt->execute();
        
        // Calcular galones y total
        $sql_calc = "UPDATE reporte_lecturas 
                     SET galones_vendidos = lectura_actual - lectura_anterior,
                         total_soles = (lectura_actual - lectura_anterior) * precio_galon
                     WHERE id_lectura = ?";
        $stmt_calc = $conn->prepare($sql_calc);
        $stmt_calc->bind_param("i", $id_lectura);
        $stmt_calc->execute();
    }
    
    // 2. ELIMINAR PAGOS ANTERIORES Y REGISTRAR NUEVOS
    $sql_delete_pagos = "DELETE FROM reporte_pagos WHERE id_reporte = ?";
    $stmt_delete = $conn->prepare($sql_delete_pagos);
    $stmt_delete->bind_param("i", $id_reporte);
    $stmt_delete->execute();
    
    // Mapeo de tipos de pago
    $tipo_pago_map = [
        'YAPE' => 1,
        'BCP' => 2,
        'VISA' => 3,
        'EFECTIVO' => 4,
        'DESCUENTO' => 5,
        'OTROS_GASTOS' => 6
    ];
    
    foreach ($pagos as $pago) {
        $tipo = $pago['tipo'];
        $monto = $pago['monto'];
        $codigo = $pago['codigo'];
        
        if (isset($tipo_pago_map[$tipo])) {
            $id_tipo_pago = $tipo_pago_map[$tipo];
            
            $sql_pago = "INSERT INTO reporte_pagos (id_reporte, id_tipo_pago, codigo_operacion, monto, fecha_registro) 
                         VALUES (?, ?, ?, ?, NOW())";
            $stmt_pago = $conn->prepare($sql_pago);
            $stmt_pago->bind_param("iisd", $id_reporte, $id_tipo_pago, $codigo, $monto);
            $stmt_pago->execute();
        }
    }
    
    // 3. ELIMINAR CRÉDITOS ANTERIORES Y REGISTRAR NUEVOS
    $sql_delete_creditos = "DELETE FROM creditos WHERE id_reporte = ?";
    $stmt_delete_c = $conn->prepare($sql_delete_creditos);
    $stmt_delete_c->bind_param("i", $id_reporte);
    $stmt_delete_c->execute();
    
    foreach ($creditos as $credito) {
        $id_cliente = $credito['id_cliente'];
        $monto = $credito['monto'];
        $numero_vale = $credito['numero_vale'];
        
        $sql_credito = "INSERT INTO creditos (id_cliente, id_reporte, numero_vale, monto, estado, fecha_registro) 
                        VALUES (?, ?, ?, ?, 'PENDIENTE', NOW())";
        $stmt_credito = $conn->prepare($sql_credito);
        $stmt_credito->bind_param("iisd", $id_cliente, $id_reporte, $numero_vale, $monto);
        $stmt_credito->execute();
    }
    
    // 4. ACTUALIZAR TOTALES DEL REPORTE
    $sql_totales = "UPDATE reportes r
                    SET r.total_diesel = (SELECT COALESCE(SUM(rl.total_soles), 0) 
                                         FROM lecturas_turno rl 
                                         INNER JOIN surtidores s ON rl.id_surtidor = s.id_surtidor
                                         INNER JOIN productos p ON s.id_producto = p.id_producto
                                         WHERE rl.id_reporte = r.id_reporte AND p.nombre LIKE '%DIESEL%'),
                        r.total_regular = (SELECT COALESCE(SUM(rl.total_soles), 0) 
                                          FROM lecturas_turno rl 
                                          INNER JOIN surtidores s ON rl.id_surtidor = s.id_surtidor
                                          INNER JOIN productos p ON s.id_producto = p.id_producto
                                          WHERE rl.id_reporte = r.id_reporte AND p.nombre LIKE '%REGULAR%'),
                        r.total_premium = (SELECT COALESCE(SUM(rl.total_soles), 0) 
                                          FROM lecturas_turno rl 
                                          INNER JOIN surtidores s ON rl.id_surtidor = s.id_surtidor
                                          INNER JOIN productos p ON s.id_producto = p.id_producto
                                          WHERE rl.id_reporte = r.id_reporte AND p.nombre LIKE '%PREMIUM%'),
                        r.total_ventas = (SELECT COALESCE(SUM(rl.total_soles), 0) 
                                         FROM lecturas_turno rl 
                                         WHERE rl.id_reporte = r.id_reporte),
                        r.monto_descuentos = (SELECT COALESCE(SUM(rp.monto), 0) 
                                             FROM reporte_pagos rp 
                                             WHERE rp.id_reporte = r.id_reporte AND rp.id_tipo_pago = 5),
                        r.monto_otros_gastos = (SELECT COALESCE(SUM(rp.monto), 0) 
                                               FROM reporte_pagos rp 
                                               WHERE rp.id_reporte = r.id_reporte AND rp.id_tipo_pago = 6),
                        r.monto_efectivo = (SELECT COALESCE(SUM(rp.monto), 0) 
                                           FROM reporte_pagos rp 
                                           WHERE rp.id_reporte = r.id_reporte AND rp.id_tipo_pago = 4)
                    WHERE r.id_reporte = ?";
    $stmt_totales = $conn->prepare($sql_totales);
    $stmt_totales->bind_param("i", $id_reporte);
    $stmt_totales->execute();
    
    // Commit
    $conn->commit();
    echo 1;
    
} catch (Exception $e) {
    $conn->rollback();
    error_log("Error al guardar turno manual: " . $e->getMessage());
    echo 0;
}

$conn->close();
?>
