<?php
require_once 'model_conexion.php';
require_once 'model_inventario.php';

class Modelo_Compras extends conexionBD
{
    public function Listar_Proveedores(bool $soloActivos = false): array
    {
        $c = conexionBD::conexionPDO();
        $sql = 'SELECT id_proveedor, ruc, razon_social, telefono, direccion, estado FROM proveedores';
        if ($soloActivos) {
            $sql .= " WHERE estado = 'ACTIVO'";
        }
        return $c->query($sql . ' ORDER BY razon_social')->fetchAll(PDO::FETCH_ASSOC);
    }

    public function Guardar_Proveedor(int $id, string $ruc, string $razon, string $telefono, string $direccion): void
    {
        if (!preg_match('/^\d{11}$/', $ruc) || $razon === '') {
            throw new DomainException('Indique la razón social y un RUC de 11 dígitos.');
        }
        $c = conexionBD::conexionPDO();
        $duplicado = $c->prepare('SELECT COUNT(*) FROM proveedores WHERE ruc = ? AND id_proveedor <> ?');
        $duplicado->execute([$ruc, $id]);
        if ($duplicado->fetchColumn()) {
            throw new DomainException('Ya existe un proveedor con ese RUC.');
        }
        if ($id > 0) {
            $query = $c->prepare('UPDATE proveedores SET ruc = ?, razon_social = ?, telefono = ?, direccion = ? WHERE id_proveedor = ?');
            $query->execute([$ruc, $razon, $telefono, $direccion, $id]);
        } else {
            $query = $c->prepare('INSERT INTO proveedores (ruc, razon_social, telefono, direccion) VALUES (?, ?, ?, ?)');
            $query->execute([$ruc, $razon, $telefono, $direccion]);
        }
    }

    public function Cambiar_Estado_Proveedor(int $id, string $estado): void
    {
        if (!in_array($estado, ['ACTIVO', 'INACTIVO'], true)) {
            throw new DomainException('Estado no válido.');
        }
        $c = conexionBD::conexionPDO();
        $c->prepare('UPDATE proveedores SET estado = ? WHERE id_proveedor = ?')->execute([$estado, $id]);
    }

    /** Registra la compra y suma los galones recibidos a cada tanque, en una sola transacción. */
    public function Registrar_Compra(array $d, array $lineas, int $idUsuario): array
    {
        $numero = trim((string) ($d['numero_documento'] ?? ''));
        $fecha = (string) ($d['fecha_compra'] ?? '');
        $vencimiento = (string) ($d['fecha_vencimiento'] ?? '');
        $percepcion = round((float) ($d['percepcion'] ?? 0), 2);
        $idProveedor = (int) ($d['id_proveedor'] ?? 0);

        if ($numero === '' || !Modelo_Inventario::Fecha_Valida($fecha) || $fecha > date('Y-m-d') || $percepcion < 0) {
            throw new DomainException('Revise el número de documento, la fecha y la percepción.');
        }
        if ($vencimiento !== '' && (!Modelo_Inventario::Fecha_Valida($vencimiento) || $vencimiento < $fecha)) {
            throw new DomainException('El vencimiento no puede ser anterior a la fecha de compra.');
        }
        if (!$lineas) {
            throw new DomainException('Agregue al menos un combustible recibido.');
        }

        $subtotal = 0.0;
        $detalle = [];
        foreach ($lineas as $l) {
            $galones = round((float) ($l['galones'] ?? 0), 3);
            $costo = round((float) ($l['costo_galon'] ?? 0), 4);
            $tanque = (int) ($l['id_tanque'] ?? 0);
            if ($galones <= 0 || $costo <= 0 || $tanque <= 0) {
                throw new DomainException('Cada línea necesita tanque, galones y costo mayores que cero.');
            }
            $importe = round($galones * $costo, 2);
            $subtotal += $importe;
            $detalle[] = [$tanque, $galones, $costo, $importe];
        }
        $subtotal = round($subtotal, 2);

        $c = conexionBD::conexionPDO();
        $c->beginTransaction();
        try {
            $proveedor = $c->prepare("SELECT COUNT(*) FROM proveedores WHERE id_proveedor = ? AND estado = 'ACTIVO'");
            $proveedor->execute([$idProveedor]);
            if (!$proveedor->fetchColumn()) {
                throw new DomainException('Seleccione un proveedor activo.');
            }
            $duplicada = $c->prepare("SELECT COUNT(*) FROM compras WHERE id_proveedor = ? AND numero_documento = ? AND estado <> 'ANULADA'");
            $duplicada->execute([$idProveedor, $numero]);
            if ($duplicada->fetchColumn()) {
                throw new DomainException('Este documento ya está registrado para el proveedor.');
            }

            $tipo = in_array($d['tipo_documento'] ?? '', ['FACTURA', 'GUIA', 'OTRO'], true) ? $d['tipo_documento'] : 'FACTURA';
            $query = $c->prepare('INSERT INTO compras (id_proveedor, tipo_documento, numero_documento, fecha_compra, fecha_vencimiento, subtotal, percepcion, total, id_usuario, observaciones)
                                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $query->execute([$idProveedor, $tipo, $numero, $fecha, $vencimiento ?: null, $subtotal, $percepcion, round($subtotal + $percepcion, 2),
                             $idUsuario, mb_substr((string) ($d['observaciones'] ?? ''), 0, 255)]);
            $idCompra = (int) $c->lastInsertId();

            $avisos = [];
            $insertar = $c->prepare('INSERT INTO compras_detalle (id_compra, id_tanque, galones, costo_galon, importe) VALUES (?, ?, ?, ?, ?)');
            $datosTanque = $c->prepare("SELECT nombre, capacidad_galones, stock_actual FROM tanques WHERE id_tanque = ? AND estado = 'ACTIVO'");
            foreach ($detalle as [$tanque, $galones, $costo, $importe]) {
                $datosTanque->execute([$tanque]);
                $t = $datosTanque->fetch(PDO::FETCH_ASSOC);
                if (!$t) {
                    throw new DomainException('Uno de los tanques no está activo.');
                }
                // El stock teórico puede estar desfasado, así que exceder la capacidad avisa pero no bloquea.
                if ((float) $t['stock_actual'] + $galones > (float) $t['capacidad_galones']) {
                    $avisos[] = 'El tanque ' . $t['nombre'] . ' queda por encima de su capacidad. Conviene medirlo con varillaje.';
                }
                $insertar->execute([$idCompra, $tanque, $galones, $costo, $importe]);
                Modelo_Inventario::Movimiento($c, $tanque, 'COMPRA', $galones, ['id_compra' => $idCompra, 'id_usuario' => $idUsuario]);
            }
            $c->commit();
            return ['id_compra' => $idCompra, 'avisos' => $avisos];
        } catch (Throwable $e) {
            $c->rollBack();
            throw $e;
        }
    }

    public function Listar_Compras(string $desde, string $hasta, string $filtro = ''): array
    {
        $c = conexionBD::conexionPDO();
        $sql = "SELECT co.*, p.razon_social AS proveedor, p.ruc, (co.total - co.monto_pagado) AS saldo,
                       IF(co.estado = 'REGISTRADA' AND co.total > co.monto_pagado AND co.fecha_vencimiento < CURDATE(), 1, 0) AS vencida
                FROM compras co INNER JOIN proveedores p ON co.id_proveedor = p.id_proveedor
                WHERE co.fecha_compra BETWEEN ? AND ?";
        if ($filtro === 'POR_PAGAR') {
            $sql .= " AND co.estado = 'REGISTRADA' AND co.total > co.monto_pagado";
        }
        $query = $c->prepare($sql . ' ORDER BY co.fecha_compra DESC, co.id_compra DESC LIMIT 500');
        $query->execute([$desde, $hasta]);
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function Detalle_Compra(int $id): array
    {
        $c = conexionBD::conexionPDO();
        $lineas = $c->prepare('SELECT d.galones, d.costo_galon, d.importe, t.nombre AS tanque, pr.nombre AS producto
                               FROM compras_detalle d INNER JOIN tanques t ON d.id_tanque = t.id_tanque
                               LEFT JOIN productos pr ON t.id_producto = pr.id_producto WHERE d.id_compra = ?');
        $lineas->execute([$id]);
        $pagos = $c->prepare('SELECT monto, fecha_pago, medio, referencia FROM pagos_compra WHERE id_compra = ? ORDER BY id_pago');
        $pagos->execute([$id]);
        return ['lineas' => $lineas->fetchAll(PDO::FETCH_ASSOC), 'pagos' => $pagos->fetchAll(PDO::FETCH_ASSOC)];
    }

    /** Anula la compra y retira del stock lo recibido. Con pagos registrados no se permite. */
    public function Anular_Compra(int $id, string $motivo, int $idUsuario): void
    {
        if ($motivo === '') {
            throw new DomainException('Indique el motivo de la anulación.');
        }
        $c = conexionBD::conexionPDO();
        $c->beginTransaction();
        try {
            $query = $c->prepare('SELECT estado, monto_pagado FROM compras WHERE id_compra = ? FOR UPDATE');
            $query->execute([$id]);
            $compra = $query->fetch(PDO::FETCH_ASSOC);
            if (!$compra || $compra['estado'] === 'ANULADA') {
                throw new DomainException('La compra no existe o ya está anulada.');
            }
            if ((float) $compra['monto_pagado'] > 0) {
                throw new DomainException('La compra tiene pagos registrados y no se puede anular.');
            }
            $lineas = $c->prepare('SELECT id_tanque, galones FROM compras_detalle WHERE id_compra = ?');
            $lineas->execute([$id]);
            foreach ($lineas->fetchAll(PDO::FETCH_ASSOC) as $l) {
                Modelo_Inventario::Movimiento($c, (int) $l['id_tanque'], 'ANULACION_COMPRA', -(float) $l['galones'],
                                              ['id_compra' => $id, 'id_usuario' => $idUsuario, 'observaciones' => mb_substr($motivo, 0, 255)]);
            }
            $c->prepare("UPDATE compras SET estado = 'ANULADA' WHERE id_compra = ?")->execute([$id]);
            $c->commit();
        } catch (Throwable $e) {
            $c->rollBack();
            throw $e;
        }
    }

    public function Registrar_Pago(int $id, float $monto, string $fecha, string $medio, string $referencia, int $idUsuario): void
    {
        $monto = round($monto, 2);
        if ($monto <= 0 || !Modelo_Inventario::Fecha_Valida($fecha) || $fecha > date('Y-m-d')) {
            throw new DomainException('Revise el monto y la fecha del pago.');
        }
        $c = conexionBD::conexionPDO();
        $c->beginTransaction();
        try {
            $query = $c->prepare('SELECT estado, total, monto_pagado FROM compras WHERE id_compra = ? FOR UPDATE');
            $query->execute([$id]);
            $compra = $query->fetch(PDO::FETCH_ASSOC);
            if (!$compra || $compra['estado'] === 'ANULADA') {
                throw new DomainException('La compra no existe o está anulada.');
            }
            if ($monto > round((float) $compra['total'] - (float) $compra['monto_pagado'], 2)) {
                throw new DomainException('El pago supera el saldo de la compra.');
            }
            $c->prepare('INSERT INTO pagos_compra (id_compra, monto, fecha_pago, medio, referencia, id_usuario) VALUES (?, ?, ?, ?, ?, ?)')
              ->execute([$id, $monto, $fecha, mb_substr($medio, 0, 40), mb_substr($referencia, 0, 60), $idUsuario]);
            $c->prepare('UPDATE compras SET monto_pagado = monto_pagado + ? WHERE id_compra = ?')->execute([$monto, $id]);
            $c->commit();
        } catch (Throwable $e) {
            $c->rollBack();
            throw $e;
        }
    }
}
