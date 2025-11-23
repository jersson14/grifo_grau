<?php
require_once 'model/model_creditos.php';

echo "<h2>Prueba: Listar Vales de un Cliente</h2>";

// Obtener ID de cliente desde URL o usar uno de prueba
$id_cliente = isset($_GET['id']) ? $_GET['id'] : 0;

if ($id_cliente == 0) {
    // Mostrar lista de clientes con créditos
    require_once 'model/model_conexion.php';
    $conexion = conexionBD::conexionPDO();
    
    $sql = "SELECT DISTINCT c.id_cliente, c.nombre_completo, COUNT(vc.id_credito) as total_vales
            FROM clientes c
            INNER JOIN ventas_credito vc ON c.id_cliente = vc.id_cliente
            GROUP BY c.id_cliente, c.nombre_completo
            ORDER BY c.nombre_completo";
    
    $query = $conexion->prepare($sql);
    $query->execute();
    $clientes = $query->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Selecciona un cliente:</h3>";
    echo "<ul>";
    foreach ($clientes as $cliente) {
        echo "<li><a href='?id=" . $cliente['id_cliente'] . "'>" . $cliente['nombre_completo'] . " (" . $cliente['total_vales'] . " vales)</a></li>";
    }
    echo "</ul>";
    exit;
}

$MCreditos = new Modelo_Creditos();

// Obtener información del cliente
require_once 'model/model_conexion.php';
$conexion = conexionBD::conexionPDO();
$sql = "SELECT * FROM clientes WHERE id_cliente = ?";
$query = $conexion->prepare($sql);
$query->execute(array($id_cliente));
$cliente = $query->fetch(PDO::FETCH_ASSOC);

echo "<h3>Cliente: " . $cliente['nombre_completo'] . "</h3>";
echo "<p><a href='test_vales_cliente.php'>← Volver a lista de clientes</a></p>";

// Probar con diferentes filtros
echo "<h4>1. Todos los vales (sin filtro):</h4>";
$vales_todos = $MCreditos->Listar_Vales_Cliente($id_cliente, '');
echo "<pre>";
print_r($vales_todos);
echo "</pre>";

echo "<h4>2. Solo vales PENDIENTES:</h4>";
$vales_pendientes = $MCreditos->Listar_Vales_Cliente($id_cliente, 'PENDIENTE');
echo "<pre>";
print_r($vales_pendientes);
echo "</pre>";

echo "<h4>3. Solo vales PAGADOS:</h4>";
$vales_pagados = $MCreditos->Listar_Vales_Cliente($id_cliente, 'PAGADO');
echo "<pre>";
print_r($vales_pagados);
echo "</pre>";

// Mostrar en tabla
if (isset($vales_todos['data']) && count($vales_todos['data']) > 0) {
    echo "<h4>Tabla de Vales:</h4>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr style='background-color:#6f42c1; color:white'>";
    echo "<th>Vale</th><th>Fecha</th><th>Monto</th><th>Pagado</th><th>Saldo</th><th>Estado</th><th>Vencimiento</th>";
    echo "</tr>";
    
    foreach ($vales_todos['data'] as $vale) {
        $bg = $vale['estado'] == 'PAGADO' ? 'background-color:#d4edda' : '';
        echo "<tr style='$bg'>";
        echo "<td>" . $vale['numero_vale'] . "</td>";
        echo "<td>" . date('d/m/Y', strtotime($vale['created_at'])) . "</td>";
        echo "<td>S/. " . number_format($vale['monto'], 2) . "</td>";
        echo "<td>S/. " . number_format($vale['monto_pagado'], 2) . "</td>";
        echo "<td>S/. " . number_format($vale['saldo_pendiente'], 2) . "</td>";
        echo "<td><span style='padding:3px 8px; border-radius:3px; background-color:" . ($vale['estado'] == 'PAGADO' ? '#28a745' : '#ffc107') . "; color:white'>" . $vale['estado'] . "</span></td>";
        echo "<td>" . ($vale['fecha_vencimiento'] ? date('d/m/Y', strtotime($vale['fecha_vencimiento'])) : '-') . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "<p style='color:red'>No se encontraron vales para este cliente</p>";
}
?>
