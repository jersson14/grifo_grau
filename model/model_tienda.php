<?php
require_once __DIR__ . '/model_conexion.php';

/** Tienda: productos no combustibles, kardex, venta rápida con control de stock y resumen. */
class Modelo_Tienda
{
    public const CATEGORIAS = ['Lubricantes', 'Aditivos', 'Bebidas', 'Snacks', 'Accesorios', 'Limpieza', 'Otros'];
    public const UNIDADES = ['UND' => 'Unidad', 'LT' => 'Litro', 'GLN' => 'Galón', 'KG' => 'Kilo', 'PAQ' => 'Paquete'];
    public const MEDIOS = ['EFECTIVO' => 'Efectivo', 'YAPE' => 'Yape / Plin', 'TARJETA' => 'Tarjeta', 'TRANSFERENCIA' => 'Transferencia'];

    private function c(): PDO
    {
        $c = conexionBD::conexionPDO();
        if ($c === null) {
            throw new RuntimeException('Sin conexión a la base de datos');
        }
        return $c;
    }

    // ---------- Productos ----------

    public function Productos(bool $soloActivos): array
    {
        $sql = 'SELECT id_producto, codigo, nombre, categoria, unidad, precio_venta, costo, stock, stock_minimo, activo,
                       (activo = 1 AND stock_minimo > 0 AND stock <= stock_minimo) AS stock_bajo
                FROM tienda_productos' . ($soloActivos ? ' WHERE activo = 1' : '') . ' ORDER BY nombre';
        return $this->c()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function Guardar_Producto(?int $id, array $d, int $idUsuario): int
    {
        $nombre = trim((string) ($d['nombre'] ?? ''));
        $codigo = trim((string) ($d['codigo'] ?? ''));
        $categoria = (string) ($d['categoria'] ?? 'Otros');
        $unidad = (string) ($d['unidad'] ?? 'UND');
        $precio = (string) ($d['precio_venta'] ?? '');
        $costo = trim((string) ($d['costo'] ?? ''));
        $minimo = trim((string) ($d['stock_minimo'] ?? '0'));
        $activo = ($d['activo'] ?? '1') === '1';
        if ($nombre === '' || mb_strlen($nombre) > 120) {
            throw new DomainException('El nombre del producto es obligatorio (máximo 120 caracteres).');
        }
        if (mb_strlen($codigo) > 40) {
            throw new DomainException('El código tiene como máximo 40 caracteres.');
        }
        if (!in_array($categoria, self::CATEGORIAS, true) || !isset(self::UNIDADES[$unidad])) {
            throw new DomainException('Elija la categoría y la unidad.');
        }
        if (!is_numeric($precio) || (float) $precio <= 0 || (float) $precio > 100000) {
            throw new DomainException('El precio de venta debe ser mayor que cero.');
        }
        if ($costo !== '' && (!is_numeric($costo) || (float) $costo < 0)) {
            throw new DomainException('El costo debe ser un monto válido.');
        }
        if (!is_numeric($minimo) || (float) $minimo < 0) {
            throw new DomainException('El stock mínimo debe ser cero o más.');
        }
        $c = $this->c();
        if ($codigo !== '') {
            $q = $c->prepare('SELECT id_producto FROM tienda_productos WHERE codigo = ? AND id_producto <> ?');
            $q->execute([$codigo, (int) $id]);
            if ($q->fetchColumn()) {
                throw new DomainException('Ya existe un producto con ese código.');
            }
        }
        $valores = [$codigo ?: null, $nombre, $categoria, $unidad, round((float) $precio, 2), $costo === '' ? null : round((float) $costo, 2), round((float) $minimo, 2), (int) $activo];
        if ($id) {
            $q = $c->prepare('UPDATE tienda_productos SET codigo = ?, nombre = ?, categoria = ?, unidad = ?, precio_venta = ?, costo = ?, stock_minimo = ?, activo = ?, actualizado_en = NOW() WHERE id_producto = ?');
            $q->execute(array_merge($valores, [$id]));
            return $id;
        }
        $c->prepare('INSERT INTO tienda_productos (codigo, nombre, categoria, unidad, precio_venta, costo, stock_minimo, activo) VALUES (?, ?, ?, ?, ?, ?, ?, ?)')->execute($valores);
        $id = (int) $c->lastInsertId();
        $inicial = trim((string) ($d['stock_inicial'] ?? ''));
        if ($inicial !== '' && (float) $inicial > 0) {
            $this->Entrada($id, (float) $inicial, $costo === '' ? null : (float) $costo, 'Stock inicial', $idUsuario);
        }
        return $id;
    }

    // ---------- Stock ----------

    /** Aplica un movimiento con el producto bloqueado. Devuelve el stock resultante. */
    private function Mover(PDO $c, int $idProducto, string $tipo, float $cantidad, ?float $costo, ?int $idVenta, string $detalle, int $idUsuario): float
    {
        $q = $c->prepare('SELECT stock, nombre FROM tienda_productos WHERE id_producto = ? FOR UPDATE');
        $q->execute([$idProducto]);
        $p = $q->fetch(PDO::FETCH_ASSOC);
        if (!$p) {
            throw new DomainException('El producto no existe.');
        }
        $nuevo = round((float) $p['stock'] + $cantidad, 2);
        if ($nuevo < 0) {
            throw new DomainException('Stock insuficiente de ' . html_entity_decode($p['nombre'], ENT_QUOTES, 'UTF-8') . ': quedan ' . rtrim(rtrim(number_format((float) $p['stock'], 2, '.', ''), '0'), '.') . '.');
        }
        $c->prepare('UPDATE tienda_productos SET stock = ? WHERE id_producto = ?')->execute([$nuevo, $idProducto]);
        $c->prepare('INSERT INTO tienda_movimientos (id_producto, tipo, cantidad, stock_resultante, costo_unitario, id_venta, detalle, id_usuario) VALUES (?, ?, ?, ?, ?, ?, ?, ?)')
            ->execute([$idProducto, $tipo, $cantidad, $nuevo, $costo, $idVenta, $detalle ?: null, $idUsuario]);
        return $nuevo;
    }

    /** Ingreso de mercadería. Con costo, actualiza el costo del producto. */
    public function Entrada(int $idProducto, float $cantidad, ?float $costo, string $detalle, int $idUsuario): float
    {
        if (!($cantidad > 0) || $cantidad > 1000000) {
            throw new DomainException('La cantidad que ingresa debe ser mayor que cero.');
        }
        if ($costo !== null && $costo < 0) {
            throw new DomainException('El costo debe ser un monto válido.');
        }
        if (mb_strlen(trim($detalle)) > 200) {
            throw new DomainException('El detalle tiene como máximo 200 caracteres.');
        }
        $c = $this->c();
        $c->beginTransaction();
        try {
            $stock = $this->Mover($c, $idProducto, 'ENTRADA', round($cantidad, 2), $costo, null, trim($detalle), $idUsuario);
            if ($costo !== null) {
                $c->prepare('UPDATE tienda_productos SET costo = ? WHERE id_producto = ?')->execute([round($costo, 2), $idProducto]);
            }
            $c->commit();
            return $stock;
        } catch (Throwable $e) {
            $c->rollBack();
            throw $e;
        }
    }

    /** Ajuste por conteo físico: deja el stock en la cantidad contada. */
    public function Ajustar(int $idProducto, float $contado, string $motivo, int $idUsuario): float
    {
        $motivo = trim($motivo);
        if ($contado < 0 || $contado > 1000000) {
            throw new DomainException('La cantidad contada debe ser cero o más.');
        }
        if ($motivo === '' || mb_strlen($motivo) > 200) {
            throw new DomainException('Indique el motivo del ajuste.');
        }
        $c = $this->c();
        $c->beginTransaction();
        try {
            $q = $c->prepare('SELECT stock FROM tienda_productos WHERE id_producto = ? FOR UPDATE');
            $q->execute([$idProducto]);
            $actual = $q->fetchColumn();
            if ($actual === false) {
                throw new DomainException('El producto no existe.');
            }
            $diferencia = round($contado - (float) $actual, 2);
            if ($diferencia == 0) {
                throw new DomainException('El conteo coincide con el stock: no hay nada que ajustar.');
            }
            $stock = $this->Mover($c, $idProducto, 'AJUSTE', $diferencia, null, null, $motivo, $idUsuario);
            $c->commit();
            return $stock;
        } catch (Throwable $e) {
            $c->rollBack();
            throw $e;
        }
    }

    public function Kardex(int $idProducto): array
    {
        $q = $this->c()->prepare('SELECT m.creado_en, m.tipo, m.cantidad, m.stock_resultante, m.costo_unitario, m.detalle, v.numero AS venta,
                                         CONCAT(u.usu_nombre, " ", u.usu_apellido) AS usuario
                                  FROM tienda_movimientos m
                                  LEFT JOIN tienda_ventas v ON v.id_venta = m.id_venta
                                  LEFT JOIN usuario u ON u.id_usuario = m.id_usuario
                                  WHERE m.id_producto = ? ORDER BY m.id_movimiento DESC LIMIT 300');
        $q->execute([$idProducto]);
        return $q->fetchAll(PDO::FETCH_ASSOC);
    }

    // ---------- Ventas ----------

    /**
     * Registra una venta. $items: lista de [id_producto, cantidad]. El precio sale del producto, no del navegador.
     * Todo o nada: si un producto no tiene stock suficiente, no se guarda nada.
     */
    public function Vender(array $items, string $medio, string $codigoOperacion, int $idUsuario): array
    {
        if (!isset(self::MEDIOS[$medio])) {
            throw new DomainException('Elija el medio de pago.');
        }
        $codigoOperacion = trim($codigoOperacion);
        if (in_array($medio, ['YAPE', 'TARJETA', 'TRANSFERENCIA'], true) && $codigoOperacion === '') {
            throw new DomainException('Indique el código de operación del pago.');
        }
        $agrupados = [];
        foreach ($items as $item) {
            $id = (int) ($item['id_producto'] ?? 0);
            $cantidad = round((float) ($item['cantidad'] ?? 0), 2);
            if ($id <= 0 || !($cantidad > 0) || $cantidad > 10000) {
                throw new DomainException('Revise las cantidades de la venta.');
            }
            $agrupados[$id] = ($agrupados[$id] ?? 0) + $cantidad;
        }
        if (!$agrupados) {
            throw new DomainException('Agregue al menos un producto.');
        }
        $c = $this->c();
        $c->beginTransaction();
        try {
            $q = $c->prepare('SELECT id_producto, nombre, precio_venta, costo, activo FROM tienda_productos WHERE id_producto = ? FOR UPDATE');
            $lineas = [];
            $total = 0.0;
            foreach ($agrupados as $id => $cantidad) {
                $q->execute([$id]);
                $p = $q->fetch(PDO::FETCH_ASSOC);
                if (!$p || !(int) $p['activo']) {
                    throw new DomainException('Un producto de la venta no existe o está inactivo.');
                }
                $subtotal = round($cantidad * (float) $p['precio_venta'], 2);
                $lineas[] = [$id, $cantidad, (float) $p['precio_venta'], $p['costo'] !== null ? (float) $p['costo'] : null, $subtotal];
                $total += $subtotal;
            }
            $siguiente = (int) $c->query('SELECT COALESCE(MAX(id_venta), 0) + 1 FROM tienda_ventas FOR UPDATE')->fetchColumn();
            $numero = 'T-' . str_pad((string) $siguiente, 6, '0', STR_PAD_LEFT);
            $c->prepare('INSERT INTO tienda_ventas (numero, total, medio_pago, codigo_operacion, id_usuario) VALUES (?, ?, ?, ?, ?)')
                ->execute([$numero, round($total, 2), $medio, $codigoOperacion ?: null, $idUsuario]);
            $idVenta = (int) $c->lastInsertId();
            $det = $c->prepare('INSERT INTO tienda_ventas_detalle (id_venta, id_producto, cantidad, precio_unitario, costo_unitario, subtotal) VALUES (?, ?, ?, ?, ?, ?)');
            foreach ($lineas as [$id, $cantidad, $precio, $costo, $subtotal]) {
                $det->execute([$idVenta, $id, $cantidad, $precio, $costo, $subtotal]);
                $this->Mover($c, $id, 'VENTA', -$cantidad, $costo, $idVenta, $numero, $idUsuario);
            }
            $c->commit();
            return ['id_venta' => $idVenta, 'numero' => $numero, 'total' => round($total, 2)];
        } catch (Throwable $e) {
            $c->rollBack();
            throw $e;
        }
    }

    /** Anula una venta y devuelve sus productos al stock. */
    public function Anular_Venta(int $idVenta, string $motivo, int $idUsuario): void
    {
        $motivo = trim($motivo);
        if ($motivo === '' || mb_strlen($motivo) > 200) {
            throw new DomainException('Indique el motivo de la anulación.');
        }
        $c = $this->c();
        $c->beginTransaction();
        try {
            $q = $c->prepare("SELECT numero, estado FROM tienda_ventas WHERE id_venta = ? FOR UPDATE");
            $q->execute([$idVenta]);
            $v = $q->fetch(PDO::FETCH_ASSOC);
            if (!$v || $v['estado'] !== 'ACTIVA') {
                throw new DomainException('La venta no existe o ya estaba anulada.');
            }
            $q = $c->prepare('SELECT id_producto, cantidad, costo_unitario FROM tienda_ventas_detalle WHERE id_venta = ?');
            $q->execute([$idVenta]);
            foreach ($q->fetchAll(PDO::FETCH_ASSOC) as $d) {
                $this->Mover($c, (int) $d['id_producto'], 'ANULACION', (float) $d['cantidad'], $d['costo_unitario'] !== null ? (float) $d['costo_unitario'] : null, $idVenta, 'Anulación ' . $v['numero'], $idUsuario);
            }
            $c->prepare("UPDATE tienda_ventas SET estado = 'ANULADA', motivo_anulacion = ?, anulado_por = ?, anulado_en = NOW() WHERE id_venta = ?")
                ->execute([$motivo, $idUsuario, $idVenta]);
            $c->commit();
        } catch (Throwable $e) {
            $c->rollBack();
            throw $e;
        }
    }

    /** Ventas del periodo. Sin la capacidad de ver todo, solo las propias. */
    public function Ventas(string $desde, string $hasta, ?int $soloUsuario): array
    {
        $sql = 'SELECT v.id_venta, v.numero, v.fecha, v.total, v.medio_pago, v.codigo_operacion, v.estado, v.motivo_anulacion,
                       CONCAT(u.usu_nombre, " ", u.usu_apellido) AS vendedor,
                       (SELECT GROUP_CONCAT(CONCAT(TRIM(TRAILING ".00" FROM d.cantidad), " × ", p.nombre) SEPARATOR ", ")
                          FROM tienda_ventas_detalle d INNER JOIN tienda_productos p ON p.id_producto = d.id_producto WHERE d.id_venta = v.id_venta) AS productos
                FROM tienda_ventas v LEFT JOIN usuario u ON u.id_usuario = v.id_usuario
                WHERE DATE(v.fecha) BETWEEN ? AND ?';
        $params = [$desde, $hasta];
        if ($soloUsuario) {
            $sql .= ' AND v.id_usuario = ?';
            $params[] = $soloUsuario;
        }
        $q = $this->c()->prepare($sql . ' ORDER BY v.id_venta DESC LIMIT 1000');
        $q->execute($params);
        return $q->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Totales del periodo: por medio de pago, por producto y margen cuando hay costo. */
    public function Resumen(string $desde, string $hasta, ?int $soloUsuario): array
    {
        $c = $this->c();
        $filtro = $soloUsuario ? ' AND v.id_usuario = ' . (int) $soloUsuario : '';
        $q = $c->prepare("SELECT v.medio_pago, COUNT(*) AS ventas, SUM(v.total) AS total FROM tienda_ventas v
                          WHERE DATE(v.fecha) BETWEEN ? AND ? AND v.estado = 'ACTIVA'$filtro GROUP BY v.medio_pago ORDER BY total DESC");
        $q->execute([$desde, $hasta]);
        $porMedio = array_map(fn($f) => ['medio' => $f['medio_pago'], 'ventas' => (int) $f['ventas'], 'total' => round((float) $f['total'], 2)], $q->fetchAll(PDO::FETCH_ASSOC));

        $q = $c->prepare("SELECT p.nombre, SUM(d.cantidad) AS cantidad, SUM(d.subtotal) AS total,
                                 SUM(CASE WHEN d.costo_unitario IS NULL THEN NULL ELSE d.subtotal - d.cantidad * d.costo_unitario END) AS margen
                          FROM tienda_ventas_detalle d
                          INNER JOIN tienda_ventas v ON v.id_venta = d.id_venta
                          INNER JOIN tienda_productos p ON p.id_producto = d.id_producto
                          WHERE DATE(v.fecha) BETWEEN ? AND ? AND v.estado = 'ACTIVA'$filtro
                          GROUP BY p.id_producto, p.nombre ORDER BY total DESC LIMIT 50");
        $q->execute([$desde, $hasta]);
        $porProducto = array_map(fn($f) => ['nombre' => $f['nombre'], 'cantidad' => round((float) $f['cantidad'], 2), 'total' => round((float) $f['total'], 2),
            'margen' => $f['margen'] === null ? null : round((float) $f['margen'], 2)], $q->fetchAll(PDO::FETCH_ASSOC));

        $total = round(array_sum(array_column($porMedio, 'total')), 2);
        return [
            'total'        => $total,
            'ventas'       => array_sum(array_column($porMedio, 'ventas')),
            'por_medio'    => $porMedio,
            'por_producto' => $porProducto,
            'margen'       => round(array_sum(array_map(fn($p) => (float) $p['margen'], $porProducto)), 2),
            'stock_bajo'   => (int) $c->query('SELECT COUNT(*) FROM tienda_productos WHERE activo = 1 AND stock_minimo > 0 AND stock <= stock_minimo')->fetchColumn(),
        ];
    }
}
