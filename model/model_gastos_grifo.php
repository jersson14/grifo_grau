<?php
require_once __DIR__ . '/model_conexion.php';

/** Gastos del grifo: categorías, registro, anulación con motivo y resumen contra ventas y compras. */
class Modelo_Gastos_Grifo
{
    public const MEDIOS = ['EFECTIVO' => 'Efectivo', 'YAPE' => 'Yape / Plin', 'TRANSFERENCIA' => 'Transferencia', 'TARJETA' => 'Tarjeta', 'OTRO' => 'Otro'];

    private function c(): PDO
    {
        $c = conexionBD::conexionPDO();
        if ($c === null) {
            throw new RuntimeException('Sin conexión a la base de datos');
        }
        return $c;
    }

    private static function fecha_valida(string $f): bool
    {
        return (bool) preg_match('/^\d{4}-\d{2}-\d{2}$/', $f) && checkdate((int) substr($f, 5, 2), (int) substr($f, 8, 2), (int) substr($f, 0, 4));
    }

    // ---------- Categorías ----------

    public function Categorias(bool $soloActivas): array
    {
        $sql = 'SELECT c.id_categoria, c.nombre, c.activo,
                       (SELECT COUNT(*) FROM gastos_grifo g WHERE g.id_categoria = c.id_categoria) AS usos
                FROM categorias_gasto c' . ($soloActivas ? ' WHERE c.activo = 1' : '') . ' ORDER BY c.nombre';
        return $this->c()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function Guardar_Categoria(?int $id, string $nombre, bool $activo): int
    {
        $nombre = trim(preg_replace('/\s+/', ' ', $nombre));
        if ($nombre === '' || mb_strlen($nombre) > 60) {
            throw new DomainException('El nombre de la categoría es obligatorio y tiene como máximo 60 caracteres.');
        }
        $c = $this->c();
        $q = $c->prepare('SELECT id_categoria FROM categorias_gasto WHERE nombre = ? AND id_categoria <> ?');
        $q->execute([$nombre, (int) $id]);
        if ($q->fetchColumn()) {
            throw new DomainException('Ya existe una categoría con ese nombre.');
        }
        if ($id) {
            $c->prepare('UPDATE categorias_gasto SET nombre = ?, activo = ? WHERE id_categoria = ?')->execute([$nombre, (int) $activo, $id]);
            return $id;
        }
        $c->prepare('INSERT INTO categorias_gasto (nombre, activo) VALUES (?, ?)')->execute([$nombre, (int) $activo]);
        return (int) $c->lastInsertId();
    }

    // ---------- Gastos ----------

    public function Registrar(string $fecha, int $idCategoria, string $descripcion, float $monto, string $medio, string $comprobante, int $idUsuario): int
    {
        $descripcion = trim($descripcion);
        $comprobante = trim($comprobante);
        if (!self::fecha_valida($fecha)) {
            throw new DomainException('Indique la fecha del gasto.');
        }
        if ($fecha > date('Y-m-d')) {
            throw new DomainException('La fecha del gasto no puede ser futura.');
        }
        if ($descripcion === '' || mb_strlen($descripcion) > 200) {
            throw new DomainException('Describa el gasto (máximo 200 caracteres).');
        }
        if (!($monto > 0) || $monto > 10000000) {
            throw new DomainException('El monto debe ser mayor que cero.');
        }
        if (!isset(self::MEDIOS[$medio])) {
            throw new DomainException('Elija el medio de pago.');
        }
        if (mb_strlen($comprobante) > 40) {
            throw new DomainException('El número de comprobante tiene como máximo 40 caracteres.');
        }
        $c = $this->c();
        $q = $c->prepare('SELECT activo FROM categorias_gasto WHERE id_categoria = ?');
        $q->execute([$idCategoria]);
        if ((string) $q->fetchColumn() !== '1') {
            throw new DomainException('Elija una categoría activa.');
        }
        $c->prepare('INSERT INTO gastos_grifo (fecha, id_categoria, descripcion, monto, medio_pago, comprobante, id_usuario) VALUES (?, ?, ?, ?, ?, ?, ?)')
            ->execute([$fecha, $idCategoria, $descripcion, round($monto, 2), $medio, $comprobante ?: null, $idUsuario]);
        return (int) $c->lastInsertId();
    }

    public function Anular(int $id, string $motivo, int $idUsuario): void
    {
        $motivo = trim($motivo);
        if ($motivo === '' || mb_strlen($motivo) > 200) {
            throw new DomainException('Indique el motivo de la anulación (máximo 200 caracteres).');
        }
        $q = $this->c()->prepare("UPDATE gastos_grifo SET estado = 'ANULADO', motivo_anulacion = ?, anulado_por = ?, anulado_en = NOW()
                                  WHERE id_gasto = ? AND estado = 'ACTIVO'");
        $q->execute([$motivo, $idUsuario, $id]);
        if ($q->rowCount() === 0) {
            throw new DomainException('El gasto no existe o ya estaba anulado.');
        }
    }

    public function Listar(string $desde, string $hasta, ?int $idCategoria, bool $conAnulados): array
    {
        $sql = 'SELECT g.id_gasto, g.fecha, g.descripcion, g.monto, g.medio_pago, g.comprobante, g.estado, g.motivo_anulacion,
                       c.nombre AS categoria, CONCAT(u.usu_nombre, " ", u.usu_apellido) AS registrado_por
                FROM gastos_grifo g
                INNER JOIN categorias_gasto c ON c.id_categoria = g.id_categoria
                LEFT JOIN usuario u ON u.id_usuario = g.id_usuario
                WHERE g.fecha BETWEEN ? AND ?';
        $params = [$desde, $hasta];
        if ($idCategoria) {
            $sql .= ' AND g.id_categoria = ?';
            $params[] = $idCategoria;
        }
        if (!$conAnulados) {
            $sql .= " AND g.estado = 'ACTIVO'";
        }
        $q = $this->c()->prepare($sql . ' ORDER BY g.fecha DESC, g.id_gasto DESC LIMIT 2000');
        $q->execute($params);
        return $q->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Totales del periodo: gastos por categoría, otros gastos declarados en turnos, ventas y compras. */
    public function Resumen(string $desde, string $hasta): array
    {
        $c = $this->c();
        $q = $c->prepare("SELECT c.nombre AS categoria, SUM(g.monto) AS total, COUNT(*) AS cantidad
                          FROM gastos_grifo g INNER JOIN categorias_gasto c ON c.id_categoria = g.id_categoria
                          WHERE g.fecha BETWEEN ? AND ? AND g.estado = 'ACTIVO'
                          GROUP BY c.id_categoria, c.nombre ORDER BY total DESC");
        $q->execute([$desde, $hasta]);
        $porCategoria = array_map(fn($f) => ['categoria' => $f['categoria'], 'total' => round((float) $f['total'], 2), 'cantidad' => (int) $f['cantidad']], $q->fetchAll(PDO::FETCH_ASSOC));

        $q = $c->prepare('SELECT COALESCE(SUM(total_ventas), 0), COALESCE(SUM(monto_otros_gastos), 0) FROM reportes_turno WHERE fecha_reporte BETWEEN ? AND ?');
        $q->execute([$desde, $hasta]);
        [$ventas, $otrosTurnos] = array_map('floatval', $q->fetch(PDO::FETCH_NUM));

        $q = $c->prepare("SELECT COALESCE(SUM(total), 0) FROM compras WHERE fecha_compra BETWEEN ? AND ? AND estado <> 'ANULADA'");
        $q->execute([$desde, $hasta]);
        $compras = (float) $q->fetchColumn();

        $gastos = round(array_sum(array_column($porCategoria, 'total')), 2);
        return [
            'por_categoria'      => $porCategoria,
            'gastos'             => $gastos,
            'otros_gastos_turno' => round($otrosTurnos, 2),
            'ventas'             => round($ventas, 2),
            'compras'            => round($compras, 2),
            'diferencia'         => round($ventas - $compras - $gastos - $otrosTurnos, 2),
        ];
    }
}
