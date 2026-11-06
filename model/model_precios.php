<?php
require_once 'model_conexion.php';

class Modelo_Precios extends conexionBD
{
    /** Registra un cambio de precio en el historial. Se ejecuta dentro de la transacción de quien cambia el precio. */
    public static function Registrar_Cambio(PDO $c, int $idProducto, ?float $anterior, float $nuevo, ?int $idUsuario, string $origen = 'MANUAL'): void
    {
        $c->prepare('INSERT INTO precios_historial (id_producto, precio_anterior, precio_nuevo, origen, id_usuario) VALUES (?, ?, ?, ?, ?)')
          ->execute([$idProducto, $anterior, $nuevo, $origen, $idUsuario]);
    }

    /**
     * Aplica los precios programados que ya entraron en vigencia. El hosting compartido puede no tener
     * tareas programadas, así que se invoca antes de usar los precios. Si falla, se siguen usando los actuales.
     */
    public static function Aplicar_Vencidos(?PDO $c = null): void
    {
        $c = $c ?? conexionBD::conexionPDO();
        if ($c === null) {
            return;
        }
        try {
            $pendientes = $c->query("SELECT COUNT(*) FROM precios_programados WHERE estado = 'PENDIENTE' AND vigente_desde <= NOW()")->fetchColumn();
            if (!$pendientes) {
                return;
            }
            $propia = !$c->inTransaction();
            if ($propia) {
                $c->beginTransaction();
            }
            $filas = $c->query("SELECT id_programado, id_producto, precio, id_usuario FROM precios_programados
                                WHERE estado = 'PENDIENTE' AND vigente_desde <= NOW() ORDER BY vigente_desde, id_programado FOR UPDATE")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($filas as $f) {
                $actual = $c->prepare('SELECT precio_actual FROM productos WHERE id_producto = ? FOR UPDATE');
                $actual->execute([$f['id_producto']]);
                $anterior = $actual->fetchColumn();
                if ($anterior !== false) {
                    $c->prepare('UPDATE productos SET precio_actual = ?, updated_at = NOW() WHERE id_producto = ?')->execute([$f['precio'], $f['id_producto']]);
                    self::Registrar_Cambio($c, (int) $f['id_producto'], (float) $anterior, (float) $f['precio'], (int) $f['id_usuario'], 'PROGRAMADO');
                }
                $c->prepare("UPDATE precios_programados SET estado = 'APLICADO' WHERE id_programado = ?")->execute([$f['id_programado']]);
            }
            if ($propia) {
                $c->commit();
            }
        } catch (Throwable $e) {
            if (!empty($propia) && $c->inTransaction()) {
                $c->rollBack();
            }
            error_log('No se pudieron aplicar los precios programados: ' . $e->getMessage());
        }
    }

    public function Programar(int $idProducto, float $precio, string $vigencia, int $idUsuario): void
    {
        $vigencia = str_replace('T', ' ', $vigencia);
        $fecha = DateTime::createFromFormat('Y-m-d H:i', $vigencia);
        if ($precio <= 0 || !$fecha || $fecha->format('Y-m-d H:i') !== $vigencia || $fecha <= new DateTime()) {
            throw new DomainException('Indique un precio mayor que cero y una fecha y hora futuras.');
        }
        $c = conexionBD::conexionPDO();
        $producto = $c->prepare("SELECT COUNT(*) FROM productos WHERE id_producto = ? AND estado = 'ACTIVO'");
        $producto->execute([$idProducto]);
        if (!$producto->fetchColumn()) {
            throw new DomainException('Seleccione un combustible activo.');
        }
        $c->prepare('INSERT INTO precios_programados (id_producto, precio, vigente_desde, id_usuario) VALUES (?, ?, ?, ?)')
          ->execute([$idProducto, round($precio, 3), $vigencia . ':00', $idUsuario]);
    }

    public function Listar_Programados(): array
    {
        self::Aplicar_Vencidos();
        $c = conexionBD::conexionPDO();
        return $c->query("SELECT pp.id_programado, pp.precio, pp.vigente_desde, pp.estado, p.nombre AS producto, p.precio_actual, u.usu_usuario AS usuario
                          FROM precios_programados pp
                          INNER JOIN productos p ON pp.id_producto = p.id_producto
                          LEFT JOIN usuario u ON pp.id_usuario = u.id_usuario
                          ORDER BY pp.estado = 'PENDIENTE' DESC, pp.vigente_desde DESC LIMIT 50")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function Cancelar(int $id): void
    {
        $c = conexionBD::conexionPDO();
        $query = $c->prepare("UPDATE precios_programados SET estado = 'CANCELADO' WHERE id_programado = ? AND estado = 'PENDIENTE'");
        $query->execute([$id]);
        if ($query->rowCount() === 0) {
            throw new DomainException('Este cambio ya se aplicó o fue cancelado.');
        }
    }
}
