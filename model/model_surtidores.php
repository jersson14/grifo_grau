<?php
require_once 'model_conexion.php';

class Modelo_Surtidores extends conexionBD {
    
    // LISTAR SURTIDORES
    public function Listar_Surtidores() {
        $c = conexionBD::conexionPDO();
        $sql = "SELECT 
                    s.id_surtidor,
                    s.numero_maquina,
                    s.codigo,
                    s.lectura_actual,
                    s.estado,
                    s.created_at,
                    s.updated_at,
                    p.id_producto,
                    p.nombre as producto_nombre,
                    p.tipo as producto_tipo,
                    p.precio_actual
                FROM surtidores s
                INNER JOIN productos p ON s.id_producto = p.id_producto
                ORDER BY s.numero_maquina ASC, s.codigo ASC";
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

    // REGISTRAR SURTIDOR
    public function Registrar_Surtidor($numero_maquina, $codigo, $id_producto, $lectura_inicial) {
        $c = conexionBD::conexionPDO();
        $sql = "INSERT INTO surtidores (numero_maquina, codigo, id_producto, lectura_actual, estado, created_at) 
                VALUES (?, ?, ?, ?, 'ACTIVO', NOW())";
        $query = $c->prepare($sql);
        $resultado = $query->execute(array($numero_maquina, $codigo, $id_producto, $lectura_inicial));
        if ($resultado) {
            return 1;
        } else {
            return 0;
        }
        conexionBD::cerrar_conexion();
    }

    // MODIFICAR SURTIDOR
    public function Modificar_Surtidor($id, $numero_maquina, $codigo, $id_producto) {
        $c = conexionBD::conexionPDO();
        $sql = "UPDATE surtidores SET 
                    numero_maquina = ?,
                    codigo = ?,
                    id_producto = ?,
                    updated_at = NOW()
                WHERE id_surtidor = ?";
        $query = $c->prepare($sql);
        $resultado = $query->execute(array($numero_maquina, $codigo, $id_producto, $id));
        if ($resultado) {
            return 1;
        } else {
            return 0;
        }
        conexionBD::cerrar_conexion();
    }

    // ACTUALIZAR LECTURA ACTUAL
    public function Actualizar_Lectura($id, $lectura) {
        $c = conexionBD::conexionPDO();
        $sql = "UPDATE surtidores SET 
                    lectura_actual = ?,
                    updated_at = NOW()
                WHERE id_surtidor = ?";
        $query = $c->prepare($sql);
        $resultado = $query->execute(array($lectura, $id));
        if ($resultado) {
            return 1;
        } else {
            return 0;
        }
        conexionBD::cerrar_conexion();
    }

    // CAMBIAR ESTADO
    public function Cambiar_Estado_Surtidor($id, $estado) {
        $c = conexionBD::conexionPDO();
        $sql = "UPDATE surtidores SET estado = ?, updated_at = NOW() WHERE id_surtidor = ?";
        $query = $c->prepare($sql);
        $resultado = $query->execute(array($estado, $id));
        if ($resultado) {
            return 1;
        } else {
            return 0;
        }
        conexionBD::cerrar_conexion();
    }

    // OBTENER SURTIDORES ACTIVOS (para turnos)
    public function Obtener_Surtidores_Activos() {
        $c = conexionBD::conexionPDO();
        $sql = "SELECT 
                    s.id_surtidor,
                    s.numero_maquina,
                    s.codigo,
                    s.lectura_actual,
                    p.id_producto,
                    p.nombre as producto_nombre,
                    p.tipo as producto_tipo,
                    p.precio_actual
                FROM surtidores s
                INNER JOIN productos p ON s.id_producto = p.id_producto
                WHERE s.estado = 'ACTIVO' AND p.estado = 'ACTIVO'
                ORDER BY s.numero_maquina ASC, s.codigo ASC";
        $query = $c->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
        conexionBD::cerrar_conexion();
    }

    // OBTENER SURTIDORES POR MÁQUINA
    public function Obtener_Surtidores_Por_Maquina($numero_maquina) {
        $c = conexionBD::conexionPDO();
        $sql = "SELECT 
                    s.id_surtidor,
                    s.numero_maquina,
                    s.codigo,
                    s.lectura_actual,
                    s.estado,
                    p.id_producto,
                    p.nombre as producto_nombre,
                    p.tipo as producto_tipo,
                    p.precio_actual
                FROM surtidores s
                INNER JOIN productos p ON s.id_producto = p.id_producto
                WHERE s.numero_maquina = ?
                ORDER BY s.codigo ASC";
        $query = $c->prepare($sql);
        $query->execute(array($numero_maquina));
        return $query->fetchAll(PDO::FETCH_ASSOC);
        conexionBD::cerrar_conexion();
    }

    // VERIFICAR CÓDIGO DUPLICADO
    public function Verificar_Codigo_Duplicado($codigo, $numero_maquina, $id_excluir = 0) {
        $c = conexionBD::conexionPDO();
        if ($id_excluir > 0) {
            $sql = "SELECT COUNT(*) as total FROM surtidores 
                    WHERE codigo = ? AND numero_maquina = ? AND id_surtidor != ?";
            $query = $c->prepare($sql);
            $query->execute(array($codigo, $numero_maquina, $id_excluir));
        } else {
            $sql = "SELECT COUNT(*) as total FROM surtidores 
                    WHERE codigo = ? AND numero_maquina = ?";
            $query = $c->prepare($sql);
            $query->execute(array($codigo, $numero_maquina));
        }
        $resultado = $query->fetch(PDO::FETCH_ASSOC);
        return $resultado['total'];
        conexionBD::cerrar_conexion();
    }

    // OBTENER ÚLTIMA LECTURA DE UN SURTIDOR
    public function Obtener_Ultima_Lectura($id_surtidor) {
        $c = conexionBD::conexionPDO();
        $sql = "SELECT lectura_actual FROM surtidores WHERE id_surtidor = ?";
        $query = $c->prepare($sql);
        $query->execute(array($id_surtidor));
        $resultado = $query->fetch(PDO::FETCH_ASSOC);
        return $resultado['lectura_actual'];
        conexionBD::cerrar_conexion();
    }
}
?>
