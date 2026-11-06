<?php
require_once 'model_conexion.php';

class Modelo_Inventario extends conexionBD
{
    public function Listar_Tanques(bool $soloActivos = false): array
    {
        $c = conexionBD::conexionPDO();
        $sql = "SELECT t.*, p.nombre AS producto,
                       ROUND(t.stock_actual / NULLIF(t.capacidad_galones, 0) * 100, 1) AS porcentaje,
                       IF(t.stock_actual <= t.stock_minimo, 1, 0) AS stock_bajo
                FROM tanques t LEFT JOIN productos p ON t.id_producto = p.id_producto";
        if ($soloActivos) {
            $sql .= " WHERE t.estado = 'ACTIVO'";
        }
        return $c->query($sql . ' ORDER BY t.nombre')->fetchAll(PDO::FETCH_ASSOC);
    }

    public function Guardar_Tanque(int $id, string $nombre, int $idProducto, float $capacidad, float $minimo, float $stockInicial, int $idUsuario): void
    {
        if ($nombre === '' || $capacidad <= 0 || $minimo < 0 || $minimo > $capacidad || $stockInicial < 0) {
            throw new DomainException('Revise el nombre, la capacidad y el stock mínimo del tanque.');
        }
        $c = conexionBD::conexionPDO();
        $producto = $c->prepare('SELECT COUNT(*) FROM productos WHERE id_producto = ?');
        $producto->execute([$idProducto]);
        if (!$producto->fetchColumn()) {
            throw new DomainException('Seleccione un combustible válido.');
        }
        $duplicado = $c->prepare('SELECT COUNT(*) FROM tanques WHERE nombre = ? AND id_tanque <> ?');
        $duplicado->execute([$nombre, $id]);
        if ($duplicado->fetchColumn()) {
            throw new DomainException('Ya existe un tanque con ese nombre.');
        }

        if ($id > 0) {
            // El combustible no cambia si ya hay movimientos: alteraría el sentido del kardex.
            $query = $c->prepare('UPDATE tanques SET nombre = ?, capacidad_galones = ?, stock_minimo = ? WHERE id_tanque = ?');
            $query->execute([$nombre, $capacidad, $minimo, $id]);
            return;
        }

        $c->beginTransaction();
        try {
            $query = $c->prepare('INSERT INTO tanques (nombre, id_producto, capacidad_galones, stock_minimo) VALUES (?, ?, ?, ?)');
            $query->execute([$nombre, $idProducto, $capacidad, $minimo]);
            $nuevo = (int) $c->lastInsertId();
            if ($stockInicial > 0) {
                self::Movimiento($c, $nuevo, 'STOCK_INICIAL', $stockInicial, ['id_usuario' => $idUsuario]);
            }
            $c->commit();
        } catch (Throwable $e) {
            $c->rollBack();
            throw $e;
        }
    }

    public function Cambiar_Estado_Tanque(int $id, string $estado): void
    {
        if (!in_array($estado, ['ACTIVO', 'INACTIVO'], true)) {
            throw new DomainException('Estado no válido.');
        }
        $c = conexionBD::conexionPDO();
        $query = $c->prepare('UPDATE tanques SET estado = ? WHERE id_tanque = ?');
        $query->execute([$estado, $id]);
    }

    /**
     * Registra un movimiento y actualiza el stock. Debe llamarse dentro de una transacción:
     * bloquea la fila del tanque para que el saldo sea correcto con operaciones simultáneas.
     */
    public static function Movimiento(PDO $c, int $idTanque, string $tipo, float $galones, array $extra = []): void
    {
        $bloqueo = $c->prepare('SELECT stock_actual FROM tanques WHERE id_tanque = ? FOR UPDATE');
        $bloqueo->execute([$idTanque]);
        $stock = $bloqueo->fetchColumn();
        if ($stock === false) {
            throw new DomainException('El tanque no existe.');
        }
        $saldo = round((float) $stock + $galones, 3);

        $c->prepare('UPDATE tanques SET stock_actual = ? WHERE id_tanque = ?')->execute([$saldo, $idTanque]);
        $c->prepare('INSERT INTO movimientos_tanque (id_tanque, tipo, galones, saldo, id_reporte, id_compra, id_varillaje, id_usuario, observaciones)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)')
          ->execute([$idTanque, $tipo, round($galones, 3), $saldo, $extra['id_reporte'] ?? null, $extra['id_compra'] ?? null,
                     $extra['id_varillaje'] ?? null, $extra['id_usuario'] ?? null, $extra['observaciones'] ?? null]);
    }

    public function Kardex(int $idTanque, string $desde, string $hasta): array
    {
        $c = conexionBD::conexionPDO();
        $query = $c->prepare("SELECT m.creado_en, m.tipo, m.galones, m.saldo, m.observaciones, rt.numero_documento AS turno, co.numero_documento AS compra,
                                     u.usu_usuario AS usuario
                              FROM movimientos_tanque m
                              LEFT JOIN reportes_turno rt ON m.id_reporte = rt.id_reporte
                              LEFT JOIN compras co ON m.id_compra = co.id_compra
                              LEFT JOIN usuario u ON m.id_usuario = u.id_usuario
                              WHERE m.id_tanque = ? AND m.creado_en >= ? AND m.creado_en < DATE_ADD(?, INTERVAL 1 DAY)
                              ORDER BY m.id_movimiento DESC LIMIT 1000");
        $query->execute([$idTanque, $desde, $hasta]);
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function Listar_Asignaciones(): array
    {
        $c = conexionBD::conexionPDO();
        return $c->query("SELECT s.id_surtidor, s.numero_maquina, s.codigo, s.id_producto, p.nombre AS producto, st.id_tanque
                          FROM surtidores s
                          INNER JOIN productos p ON s.id_producto = p.id_producto
                          LEFT JOIN surtidor_tanque st ON s.id_surtidor = st.id_surtidor
                          WHERE s.estado = 'ACTIVO'
                          ORDER BY s.numero_maquina, s.codigo")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function Asignar_Surtidor(int $idSurtidor, int $idTanque): void
    {
        $c = conexionBD::conexionPDO();
        if ($idTanque === 0) {
            $c->prepare('DELETE FROM surtidor_tanque WHERE id_surtidor = ?')->execute([$idSurtidor]);
            return;
        }
        $coincide = $c->prepare('SELECT COUNT(*) FROM surtidores s INNER JOIN tanques t ON s.id_producto = t.id_producto
                                 WHERE s.id_surtidor = ? AND t.id_tanque = ?');
        $coincide->execute([$idSurtidor, $idTanque]);
        if (!$coincide->fetchColumn()) {
            throw new DomainException('El tanque debe contener el mismo combustible que el surtidor.');
        }
        $c->prepare('REPLACE INTO surtidor_tanque (id_surtidor, id_tanque) VALUES (?, ?)')->execute([$idSurtidor, $idTanque]);
    }

    public function Registrar_Varillaje(int $idTanque, string $fecha, ?float $medida, float $fisicos, int $idUsuario, string $observaciones): array
    {
        if ($fisicos < 0 || !self::Fecha_Valida($fecha) || $fecha > date('Y-m-d')) {
            throw new DomainException('Revise la fecha y los galones medidos.');
        }
        $c = conexionBD::conexionPDO();
        $query = $c->prepare("SELECT stock_actual, capacidad_galones FROM tanques WHERE id_tanque = ? AND estado = 'ACTIVO'");
        $query->execute([$idTanque]);
        $tanque = $query->fetch(PDO::FETCH_ASSOC);
        if (!$tanque) {
            throw new DomainException('Seleccione un tanque activo.');
        }
        if ($fisicos > (float) $tanque['capacidad_galones']) {
            throw new DomainException('La medida supera la capacidad del tanque.');
        }
        $teoricos = (float) $tanque['stock_actual'];
        $diferencia = round($fisicos - $teoricos, 3);

        $insertar = $c->prepare('INSERT INTO varillajes (id_tanque, fecha, medida_cm, galones_fisicos, galones_teoricos, diferencia, id_usuario, observaciones)
                                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        $insertar->execute([$idTanque, $fecha, $medida, $fisicos, $teoricos, $diferencia, $idUsuario, mb_substr($observaciones, 0, 255)]);

        $config = new Modelo_Configuracion();
        $tolerancia = (float) $config->Obtener('varillaje_tolerancia_pct', '0.5');
        $porcentaje = $teoricos > 0 ? abs($diferencia) / $teoricos * 100 : ($diferencia == 0.0 ? 0 : 100);
        return ['teoricos' => $teoricos, 'diferencia' => $diferencia, 'fuera_de_tolerancia' => $porcentaje > $tolerancia];
    }

    public function Listar_Varillajes(string $desde, string $hasta): array
    {
        $c = conexionBD::conexionPDO();
        $query = $c->prepare("SELECT v.*, t.nombre AS tanque, u.usu_usuario AS usuario
                              FROM varillajes v
                              INNER JOIN tanques t ON v.id_tanque = t.id_tanque
                              LEFT JOIN usuario u ON v.id_usuario = u.id_usuario
                              WHERE v.fecha BETWEEN ? AND ?
                              ORDER BY v.fecha DESC, v.id_varillaje DESC LIMIT 500");
        $query->execute([$desde, $hasta]);
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Lleva el stock teórico a lo medido, usando la diferencia registrada en la medición. */
    public function Aplicar_Ajuste(int $idVarillaje, int $idUsuario): void
    {
        $c = conexionBD::conexionPDO();
        $c->beginTransaction();
        try {
            $query = $c->prepare('SELECT id_tanque, diferencia, ajuste_aplicado FROM varillajes WHERE id_varillaje = ? FOR UPDATE');
            $query->execute([$idVarillaje]);
            $varillaje = $query->fetch(PDO::FETCH_ASSOC);
            if (!$varillaje) {
                throw new DomainException('La medición no existe.');
            }
            if ($varillaje['ajuste_aplicado']) {
                throw new DomainException('El ajuste de esta medición ya se aplicó.');
            }
            self::Movimiento($c, (int) $varillaje['id_tanque'], 'AJUSTE_VARILLAJE', (float) $varillaje['diferencia'],
                             ['id_varillaje' => $idVarillaje, 'id_usuario' => $idUsuario]);
            $c->prepare('UPDATE varillajes SET ajuste_aplicado = 1 WHERE id_varillaje = ?')->execute([$idVarillaje]);
            $c->commit();
        } catch (Throwable $e) {
            $c->rollBack();
            throw $e;
        }
    }

    public static function Fecha_Valida(string $fecha): bool
    {
        $d = DateTime::createFromFormat('!Y-m-d', $fecha);
        return $d && $d->format('Y-m-d') === $fecha;
    }
}
