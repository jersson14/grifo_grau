<?php
require_once 'model_conexion.php';
require_once 'model_precios.php';

class Modelo_Productos extends conexionBD {
    
    // LISTAR PRODUCTOS
    public function Listar_Productos() {
        $c = conexionBD::conexionPDO();
        $sql = "SELECT 
                    p.id_producto,
                    p.nombre,
                    p.tipo,
                    p.precio_actual,
                    p.estado,
                    p.created_at,
                    p.updated_at,
                    CONCAT(u.usu_nombre, ' ', u.usu_apellido) as usuario_registro
                FROM productos p
                LEFT JOIN usuario u ON p.id_usuario = u.id_usuario
                ORDER BY p.id_producto ASC";
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

    // REGISTRAR PRODUCTO
    /** Tipos de combustible. Diésel, regular y premium además llenan sus columnas propias en el turno. */
    public const TIPOS = ['DIESEL', 'REGULAR', 'PREMIUM', 'GLP', 'GNV', 'OTRO'];

    public function Registrar_Producto($nombre, $tipo, $precio, $id_usuario) {
        if (!in_array($tipo, self::TIPOS, true)) {
            return 0;
        }
        $c = conexionBD::conexionPDO();
        $sql = "INSERT INTO productos (nombre, tipo, precio_actual, estado, created_at, id_usuario) 
                VALUES (?, ?, ?, 'ACTIVO', NOW(), ?)";
        $query = $c->prepare($sql);
        $resultado = $query->execute(array($nombre, $tipo, $precio, $id_usuario));
        if ($resultado) {
            return 1;
        } else {
            return 0;
        }
        conexionBD::cerrar_conexion();
    }

    // MODIFICAR PRODUCTO
    public function Modificar_Producto($id, $nombre, $tipo, $precio, $id_usuario = null) {
        if (!in_array($tipo, self::TIPOS, true)) {
            return 0;
        }
        $c = conexionBD::conexionPDO();
        $c->beginTransaction();
        try {
            $actual = $c->prepare("SELECT precio_actual FROM productos WHERE id_producto = ? FOR UPDATE");
            $actual->execute(array($id));
            $anterior = $actual->fetchColumn();

            $query = $c->prepare("UPDATE productos SET nombre = ?, tipo = ?, precio_actual = ?, updated_at = NOW() WHERE id_producto = ?");
            $query->execute(array($nombre, $tipo, $precio, $id));

            if ($anterior !== false && abs((float) $anterior - (float) $precio) > 0.0005) {
                Modelo_Precios::Registrar_Cambio($c, (int) $id, (float) $anterior, (float) $precio, $id_usuario === null ? null : (int) $id_usuario);
            }
            $c->commit();
            return 1;
        } catch (Exception $e) {
            $c->rollBack();
            error_log("Error al modificar producto: " . $e->getMessage());
            return 0;
        }
    }

    // CAMBIAR ESTADO
    public function Cambiar_Estado_Producto($id, $estado) {
        $c = conexionBD::conexionPDO();
        $sql = "UPDATE productos SET estado = ?, updated_at = NOW() WHERE id_producto = ?";
        $query = $c->prepare($sql);
        $resultado = $query->execute(array($estado, $id));
        if ($resultado) {
            return 1;
        } else {
            return 0;
        }
        conexionBD::cerrar_conexion();
    }

    // OBTENER PRECIOS ACTUALES (para turnos)
    public function Obtener_Precios_Actuales() {
        $c = conexionBD::conexionPDO();
        $sql = "SELECT id_producto, nombre, tipo, precio_actual 
                FROM productos 
                WHERE estado = 'ACTIVO' 
                ORDER BY tipo ASC";
        $query = $c->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
        conexionBD::cerrar_conexion();
    }

    // HISTORIAL DE CAMBIOS DE PRECIOS
    public function Historial_Precios() {
        $c = conexionBD::conexionPDO();
        $sql = "SELECT
                    p.nombre,
                    p.tipo,
                    h.precio_anterior,
                    h.precio_nuevo as precio_actual,
                    h.creado_en as fecha_cambio,
                    CONCAT(COALESCE(CONCAT(u.usu_nombre, ' ', u.usu_apellido), ''), IF(h.origen = 'PROGRAMADO', ' (programado)', '')) as modificado_por
                FROM precios_historial h
                INNER JOIN productos p ON h.id_producto = p.id_producto
                LEFT JOIN usuario u ON h.id_usuario = u.id_usuario
                ORDER BY h.id_historial DESC
                LIMIT 100";
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
}
?>
