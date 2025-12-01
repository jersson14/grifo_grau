<?php
require_once '../../model/model_conexion.php';

$id_reporte = htmlspecialchars($_POST['id_reporte'], ENT_QUOTES, 'UTF-8');

// Obtener conexión MySQLi
$host = "localhost";
$port = "3307";
$usuario = "root";
$contrasena = "";
$bdName = "grifo_grau";

$conn = new mysqli($host, $usuario, $contrasena, $bdName, $port);

try {
    // 1. Calcular el faltante/sobrante
    $sql_cuadre = "SELECT 
                    r.total_ventas,
                    r.monto_descuentos,
                    r.monto_otros_gastos,
                    r.monto_efectivo,
                    COALESCE((SELECT SUM(rp.monto) FROM reporte_pagos rp 
                             WHERE rp.id_reporte = r.id_reporte 
                             AND rp.id_tipo_pago IN (1,2,3)), 0) as total_pagos,
                    COALESCE((SELECT SUM(c.monto) FROM creditos c 
                             WHERE c.id_reporte = r.id_reporte), 0) as total_creditos
                   FROM reportes r
                   WHERE r.id_reporte = ?";
    
    $stmt = $conn->prepare($sql_cuadre);
    $stmt->bind_param("i", $id_reporte);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    
    $total_ventas = $data['total_ventas'];
    $descuentos = $data['monto_descuentos'];
    $otros_gastos = $data['monto_otros_gastos'];
    $efectivo = $data['monto_efectivo'];
    $total_pagos = $data['total_pagos'];
    $total_creditos = $data['total_creditos'];
    
    $total_justificado = $total_pagos + $total_creditos + $otros_gastos + $efectivo;
    $total_neto_ventas = $total_ventas - $descuentos;
    $diferencia = $total_justificado - $total_neto_ventas;
    
    // 2. Actualizar el reporte con el estado CERRADO y el faltante
    $sql_cerrar = "UPDATE reportes 
                   SET estado = 'CERRADO',
                       monto_faltante = ?,
                       fecha_cierre = NOW()
                   WHERE id_reporte = ?";
    $stmt_cerrar = $conn->prepare($sql_cerrar);
    $stmt_cerrar->bind_param("di", $diferencia, $id_reporte);
    $stmt_cerrar->execute();
    
    // 3. Actualizar las lecturas actuales de los surtidores
    $sql_actualizar_surtidores = "UPDATE surtidores s
                                  INNER JOIN reporte_lecturas rl ON s.id_surtidor = rl.id_surtidor
                                  SET s.lectura_actual = rl.lectura_actual
                                  WHERE rl.id_reporte = ?";
    $stmt_surt = $conn->prepare($sql_actualizar_surtidores);
    $stmt_surt->bind_param("i", $id_reporte);
    $stmt_surt->execute();
    
    echo 1;
    
} catch (Exception $e) {
    error_log("Error al cerrar turno manual: " . $e->getMessage());
    echo 0;
}

$conn->close();
?>
