<?php
require_once 'model_configuracion.php';
require_once 'model_inventario.php';

/**
 * Efectos de los módulos opcionales sobre el ciclo de vida de un turno. Se ejecutan dentro de la
 * transacción del turno, en un punto de guardado: si algo falla se deshace solo este efecto, se
 * registra el error y el turno continúa. Un módulo opcional nunca impide cerrar un turno.
 */
class Eventos_Turno
{
    public static function Al_Cerrar(PDO $c, int $idReporte): void
    {
        self::Aislado($c, 'descuento de stock', function () use ($c, $idReporte) {
            if (self::Activo($c, 'inventario')) {
                self::Descontar_Stock($c, $idReporte);
            }
        });
        self::Aislado($c, 'vinculación de ventas', function () use ($c, $idReporte) {
            if (self::Activo($c, 'ventas')) {
                self::Vincular_Ventas($c, $idReporte);
            }
        });
    }

    public static function Al_Eliminar(PDO $c, int $idReporte): void
    {
        self::Aislado($c, 'reversión de stock', function () use ($c, $idReporte) {
            self::Revertir_Stock($c, $idReporte);
        });
        self::Aislado($c, 'desvinculación de ventas', function () use ($c, $idReporte) {
            $c->prepare('UPDATE ventas SET id_reporte = NULL WHERE id_reporte = ?')->execute([$idReporte]);
        });
        self::Aislado($c, 'eliminación del arqueo', function () use ($c, $idReporte) {
            $c->prepare('DELETE FROM arqueos WHERE id_reporte = ?')->execute([$idReporte]);
        });
    }

    private static function Activo(PDO $c, string $codigo): bool
    {
        $query = $c->prepare('SELECT activo FROM modulos WHERE codigo = ?');
        $query->execute([$codigo]);
        return (bool) $query->fetchColumn();
    }

    private static function Aislado(PDO $c, string $efecto, callable $accion): void
    {
        $c->exec('SAVEPOINT efecto_turno');
        try {
            $accion();
            $c->exec('RELEASE SAVEPOINT efecto_turno');
        } catch (Throwable $e) {
            $c->exec('ROLLBACK TO SAVEPOINT efecto_turno');
            error_log("Turno: falló el efecto '$efecto': " . $e->getMessage());
        }
    }

    /** Cada surtidor descuenta de su tanque asignado o, si no tiene, del primer tanque activo de su combustible. */
    private static function Descontar_Stock(PDO $c, int $idReporte): void
    {
        $repetido = $c->prepare("SELECT COUNT(*) FROM movimientos_tanque WHERE id_reporte = ? AND tipo = 'VENTA_TURNO'");
        $repetido->execute([$idReporte]);
        if ($repetido->fetchColumn()) {
            return;
        }

        $query = $c->prepare("SELECT tanque, SUM(galones) AS galones FROM (
                                  SELECT COALESCE(st.id_tanque, (SELECT t.id_tanque FROM tanques t
                                                                 WHERE t.id_producto = s.id_producto AND t.estado = 'ACTIVO'
                                                                 ORDER BY t.id_tanque LIMIT 1)) AS tanque,
                                         lt.galones_vendidos AS galones
                                  FROM lecturas_turno lt
                                  INNER JOIN surtidores s ON lt.id_surtidor = s.id_surtidor
                                  LEFT JOIN surtidor_tanque st ON s.id_surtidor = st.id_surtidor
                                  WHERE lt.id_reporte = ?
                              ) x WHERE tanque IS NOT NULL GROUP BY tanque");
        $query->execute([$idReporte]);
        $usuario = $c->prepare('SELECT id_usuario FROM reportes_turno WHERE id_reporte = ?');
        $usuario->execute([$idReporte]);
        $idUsuario = (int) $usuario->fetchColumn();

        foreach ($query->fetchAll(PDO::FETCH_ASSOC) as $fila) {
            if ((float) $fila['galones'] != 0.0) {
                Modelo_Inventario::Movimiento($c, (int) $fila['tanque'], 'VENTA_TURNO', -(float) $fila['galones'],
                                              ['id_reporte' => $idReporte, 'id_usuario' => $idUsuario]);
            }
        }
    }

    private static function Revertir_Stock(PDO $c, int $idReporte): void
    {
        $query = $c->prepare("SELECT id_tanque, SUM(galones) AS galones, MAX(id_usuario) AS id_usuario FROM movimientos_tanque
                              WHERE id_reporte = ? AND tipo IN ('VENTA_TURNO', 'REVERSION_TURNO') GROUP BY id_tanque");
        $query->execute([$idReporte]);
        foreach ($query->fetchAll(PDO::FETCH_ASSOC) as $fila) {
            if ((float) $fila['galones'] != 0.0) {
                Modelo_Inventario::Movimiento($c, (int) $fila['id_tanque'], 'REVERSION_TURNO', -(float) $fila['galones'],
                                              ['id_usuario' => $fila['id_usuario'], 'observaciones' => 'Turno eliminado (ID ' . $idReporte . ')']);
            }
        }
    }

    /** El flujo habitual crea el turno al final, así que se asocian las ventas pendientes del grifero. */
    private static function Vincular_Ventas(PDO $c, int $idReporte): void
    {
        $c->prepare("UPDATE ventas v INNER JOIN reportes_turno rt ON rt.id_reporte = ?
                     SET v.id_reporte = rt.id_reporte
                     WHERE v.id_usuario = rt.id_usuario AND v.id_reporte IS NULL AND v.estado = 'REGISTRADA'
                       AND v.creado_en < DATE_ADD(rt.fecha_reporte, INTERVAL 2 DAY)")
          ->execute([$idReporte]);
    }
}
