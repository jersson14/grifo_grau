<?php
require_once 'model_conexion.php';

class Modelo_Clientes_Grifo extends conexionBD {
    
    // LISTAR CLIENTES
    public function Listar_Clientes() {
        $c = conexionBD::conexionPDO();
        $sql = "SELECT 
                    c.id_cliente,
                    c.nombre_completo,
                    c.dni_ruc,
                    c.telefono,
                    c.direccion,
                    c.estado,
                    c.created_at,
                    CONCAT(u.usu_nombre, ' ', u.usu_apellido) as usuario_registro,
                    COALESCE(SUM(CASE WHEN vc.estado = 'PENDIENTE' THEN vc.saldo_pendiente ELSE 0 END), 0) as saldo_pendiente
                FROM clientes c
                LEFT JOIN usuario u ON c.id_usuario = u.id_usuario
                LEFT JOIN ventas_credito vc ON c.id_cliente = vc.id_cliente
                GROUP BY c.id_cliente
                ORDER BY c.id_cliente DESC";
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

    // REGISTRAR CLIENTE
    public function Registrar_Cliente($nombre, $dni, $telefono, $direccion, $id_usuario) {
        $c = conexionBD::conexionPDO();
        $sql = "INSERT INTO clientes (nombre_completo, dni_ruc, telefono, direccion, estado, created_at, id_usuario) 
                VALUES (?, ?, ?, ?, 'ACTIVO', NOW(), ?)";
        $query = $c->prepare($sql);
        $resultado = $query->execute(array($nombre, $dni, $telefono, $direccion, $id_usuario));
        if ($resultado) {
            return 1;
        } else {
            return 0;
        }
        conexionBD::cerrar_conexion();
    }

    // MODIFICAR CLIENTE
    public function Modificar_Cliente($id, $nombre, $dni, $telefono, $direccion) {
        $c = conexionBD::conexionPDO();
        $sql = "UPDATE clientes SET 
                    nombre_completo = ?,
                    dni_ruc = ?,
                    telefono = ?,
                    direccion = ?,
                    updated_at = NOW()
                WHERE id_cliente = ?";
        $query = $c->prepare($sql);
        $resultado = $query->execute(array($nombre, $dni, $telefono, $direccion, $id));
        if ($resultado) {
            return 1;
        } else {
            return 0;
        }
        conexionBD::cerrar_conexion();
    }

    // CAMBIAR ESTADO
    public function Cambiar_Estado_Cliente($id, $estado) {
        $c = conexionBD::conexionPDO();
        $sql = "UPDATE clientes SET estado = ?, updated_at = NOW() WHERE id_cliente = ?";
        $query = $c->prepare($sql);
        $resultado = $query->execute(array($estado, $id));
        if ($resultado) {
            return 1;
        } else {
            return 0;
        }
        conexionBD::cerrar_conexion();
    }

    // LISTAR CLIENTES ACTIVOS (para select)
    public function Listar_Clientes_Activos() {
        $c = conexionBD::conexionPDO();
        $sql = "SELECT id_cliente, nombre_completo, dni_ruc 
                FROM clientes 
                WHERE estado = 'ACTIVO' 
                ORDER BY nombre_completo ASC";
        $query = $c->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
        conexionBD::cerrar_conexion();
    }

    // VER DETALLE CLIENTE CON CRÉDITOS
    public function Ver_Detalle_Cliente($id) {
        $c = conexionBD::conexionPDO();
        $sql = "SELECT 
                    c.*,
                    COUNT(vc.id_credito) as total_creditos,
                    COALESCE(SUM(vc.monto), 0) as monto_total,
                    COALESCE(SUM(vc.saldo_pendiente), 0) as saldo_pendiente,
                    COALESCE(SUM(vc.monto - vc.saldo_pendiente), 0) as monto_pagado
                FROM clientes c
                LEFT JOIN ventas_credito vc ON c.id_cliente = vc.id_cliente AND vc.estado = 'PENDIENTE'
                WHERE c.id_cliente = ?
                GROUP BY c.id_cliente";
        $query = $c->prepare($sql);
        $query->execute(array($id));
        return $query->fetch(PDO::FETCH_ASSOC);
        conexionBD::cerrar_conexion();
    }

    // LISTAR CRÉDITOS DE UN CLIENTE
    public function Listar_Creditos_Cliente($id_cliente) {
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
                    rt.numero_documento,
                    rt.fecha_reporte,
                    rt.turno
                FROM ventas_credito vc
                INNER JOIN reportes_turno rt ON vc.id_reporte = rt.id_reporte
                WHERE vc.id_cliente = ?
                ORDER BY vc.created_at DESC";
        $arreglo = array();
        $query = $c->prepare($sql);
        $query->execute(array($id_cliente));
        $resultado = $query->fetchAll(PDO::FETCH_ASSOC);
        foreach ($resultado as $resp) {
            $arreglo["data"][] = $resp;
        }
        return $arreglo;
        conexionBD::cerrar_conexion();
    }
}
?>
