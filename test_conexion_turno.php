<?php
// Script de prueba para verificar la conexión y la tabla reportes_turno
require_once 'model/model_conexion.php';

echo "<h2>Prueba de Conexión y Tabla reportes_turno</h2>";

// Probar conexión
$conexion = conexionBD::conexionPDO();

if ($conexion === null) {
    echo "<p style='color: red;'>❌ Error: No se pudo conectar a la base de datos</p>";
    exit;
}

echo "<p style='color: green;'>✅ Conexión exitosa a la base de datos</p>";

// Verificar si existe la tabla reportes_turno
try {
    $sql = "SHOW TABLES LIKE 'reportes_turno'";
    $query = $conexion->prepare($sql);
    $query->execute();
    $tabla_existe = $query->fetch();
    
    if ($tabla_existe) {
        echo "<p style='color: green;'>✅ La tabla 'reportes_turno' existe</p>";
        
        // Mostrar estructura de la tabla
        $sql = "DESCRIBE reportes_turno";
        $query = $conexion->prepare($sql);
        $query->execute();
        $columnas = $query->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>Estructura de la tabla reportes_turno:</h3>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        foreach ($columnas as $col) {
            echo "<tr>";
            echo "<td>" . $col['Field'] . "</td>";
            echo "<td>" . $col['Type'] . "</td>";
            echo "<td>" . $col['Null'] . "</td>";
            echo "<td>" . $col['Key'] . "</td>";
            echo "<td>" . $col['Default'] . "</td>";
            echo "<td>" . $col['Extra'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Verificar si hay turnos abiertos
        $sql = "SELECT COUNT(*) as total FROM reportes_turno WHERE estado = 'ABIERTO'";
        $query = $conexion->prepare($sql);
        $query->execute();
        $resultado = $query->fetch(PDO::FETCH_ASSOC);
        
        echo "<p>Total de turnos abiertos: <strong>" . $resultado['total'] . "</strong></p>";
        
        // Mostrar últimos 5 turnos
        $sql = "SELECT * FROM reportes_turno ORDER BY id_reporte DESC LIMIT 5";
        $query = $conexion->prepare($sql);
        $query->execute();
        $turnos = $query->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($turnos) > 0) {
            echo "<h3>Últimos 5 turnos:</h3>";
            echo "<table border='1' cellpadding='5'>";
            echo "<tr><th>ID</th><th>Número Doc</th><th>Usuario</th><th>Turno</th><th>Fecha</th><th>Estado</th></tr>";
            foreach ($turnos as $turno) {
                echo "<tr>";
                echo "<td>" . $turno['id_reporte'] . "</td>";
                echo "<td>" . $turno['numero_documento'] . "</td>";
                echo "<td>" . $turno['id_usuario'] . "</td>";
                echo "<td>" . $turno['turno'] . "</td>";
                echo "<td>" . $turno['fecha_reporte'] . "</td>";
                echo "<td>" . $turno['estado'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No hay turnos registrados</p>";
        }
        
    } else {
        echo "<p style='color: red;'>❌ La tabla 'reportes_turno' NO existe</p>";
    }
    
    // Verificar tabla surtidores
    $sql = "SELECT COUNT(*) as total FROM surtidores WHERE estado = 'ACTIVO'";
    $query = $conexion->prepare($sql);
    $query->execute();
    $resultado = $query->fetch(PDO::FETCH_ASSOC);
    
    echo "<p>Total de surtidores activos: <strong>" . $resultado['total'] . "</strong></p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>
