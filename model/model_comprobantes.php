<?php
require_once 'model_conexion.php';
require_once 'model_configuracion.php';
require_once __DIR__ . '/facturacion/proveedor_nubefact.php';

class Modelo_Comprobantes extends conexionBD
{
    private const TIPOS = ['FACTURA' => 1, 'BOLETA' => 2, 'NCREDITO' => 3, 'NDEBITO' => 4];
    public const NOMBRES = ['FACTURA' => 'Factura', 'BOLETA' => 'Boleta', 'NCREDITO' => 'Nota de crédito', 'NDEBITO' => 'Nota de débito'];
    /** Catálogo 09 de SUNAT: tipos de nota de crédito. */
    public const MOTIVOS_NC = [
        '01' => 'Anulación de la operación', '02' => 'Anulación por error en el RUC', '03' => 'Corrección por error en la descripción',
        '04' => 'Descuento global', '05' => 'Descuento por ítem', '06' => 'Devolución total', '07' => 'Devolución por ítem',
        '08' => 'Bonificación', '09' => 'Disminución en el valor', '10' => 'Otros conceptos',
    ];
    /** Catálogo 10 de SUNAT: tipos de nota de débito. */
    public const MOTIVOS_ND = ['01' => 'Intereses por mora', '02' => 'Aumento en el valor', '03' => 'Penalidades u otros conceptos'];
    /** Notas de crédito que cubren todo lo que queda del comprobante: el monto no se elige. */
    private const NC_TOTALES = ['01', '02', '06'];

    /** Tope de boleta sin documento del cliente, para validar antes de guardar la venta. */
    public static function Tope_Sin_Documento(): float
    {
        $config = new Modelo_Configuracion();
        return (float) $config->Obtener('factura_boleta_tope_sin_documento', '700');
    }

    /**
     * Reserva el número y guarda el comprobante como pendiente. Reservar en una transacción con la fila
     * de la serie bloqueada evita números repetidos; reenviar siempre usa el mismo número.
     */
    public function Crear_Para_Venta(int $idVenta, string $tipo, int $idUsuario): int
    {
        $c = conexionBD::conexionPDO();
        $config = new Modelo_Configuracion();
        $query = $c->prepare('SELECT v.total, v.galones, cl.nombre_completo, cl.dni_ruc, cl.direccion
                              FROM ventas v LEFT JOIN clientes cl ON v.id_cliente = cl.id_cliente WHERE v.id_venta = ?');
        $query->execute([$idVenta]);
        $venta = $query->fetch(PDO::FETCH_ASSOC);
        if (!$venta || !isset(self::TIPOS[$tipo])) {
            throw new DomainException('No se pudo preparar el comprobante.');
        }

        $documento = trim((string) $venta['dni_ruc']);
        $clienteTipo = preg_match('/^\d{11}$/', $documento) ? '6' : (preg_match('/^\d{8}$/', $documento) ? '1' : '-');
        $denominacion = $clienteTipo === '-' ? 'VARIOS' : (string) $venta['nombre_completo'];
        if ($clienteTipo === '-') {
            $documento = '-';
        }

        $total = round((float) $venta['total'], 2);
        $porcentaje = (float) $config->Obtener('igv_porcentaje', '18');
        $gravada = round($total / (1 + $porcentaje / 100), 2);
        $igv = round($total - $gravada, 2);

        $serie = (string) $config->Obtener($tipo === 'FACTURA' ? 'factura_serie_factura' : 'factura_serie_boleta');

        $c->beginTransaction();
        try {
            $c->prepare('INSERT IGNORE INTO series_comprobante (serie, ultimo_numero) VALUES (?, 0)')->execute([$serie]);
            $bloqueo = $c->prepare('SELECT ultimo_numero FROM series_comprobante WHERE serie = ? FOR UPDATE');
            $bloqueo->execute([$serie]);
            $numero = (int) $bloqueo->fetchColumn() + 1;
            $c->prepare('UPDATE series_comprobante SET ultimo_numero = ? WHERE serie = ?')->execute([$numero, $serie]);

            $c->prepare('INSERT INTO comprobantes (id_venta, tipo, serie, numero, cliente_tipo, cliente_documento, cliente_denominacion, cliente_direccion, gravada, igv, total, porcentaje_igv, id_usuario)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)')
              ->execute([$idVenta, $tipo, $serie, $numero, $clienteTipo, $documento, mb_substr($denominacion, 0, 200), $venta['direccion'], $gravada, $igv, $total, $porcentaje, $idUsuario]);
            $id = (int) $c->lastInsertId();
            $c->commit();
            return $id;
        } catch (Throwable $e) {
            $c->rollBack();
            throw $e;
        }
    }

    /** Tipo y número de documento del cliente para SUNAT: RUC (6), DNI (1) o sin documento («VARIOS»). */
    private static function Datos_Cliente(?string $nombre, ?string $documento): array
    {
        $documento = trim((string) $documento);
        $tipo = preg_match('/^\d{11}$/', $documento) ? '6' : (preg_match('/^\d{8}$/', $documento) ? '1' : '-');
        return $tipo === '-' ? ['-', '-', 'VARIOS'] : [$tipo, $documento, mb_substr(html_entity_decode((string) $nombre, ENT_QUOTES, 'UTF-8'), 0, 200)];
    }

    /** Separa el IGV de un total que lo incluye. */
    private static function Importes(float $total, float $porcentaje): array
    {
        $total = round($total, 2);
        $gravada = round($total / (1 + $porcentaje / 100), 2);
        return [$gravada, round($total - $gravada, 2), $total];
    }

    /**
     * Reserva el número de la serie (fila bloqueada: no hay números repetidos) y guarda el comprobante con sus líneas.
     * Debe llamarse dentro de una transacción.
     */
    private function Guardar_Con_Numero(PDO $c, string $serie, array $cab, array $items): int
    {
        $c->prepare('INSERT IGNORE INTO series_comprobante (serie, ultimo_numero) VALUES (?, 0)')->execute([$serie]);
        $bloqueo = $c->prepare('SELECT ultimo_numero FROM series_comprobante WHERE serie = ? FOR UPDATE');
        $bloqueo->execute([$serie]);
        $numero = (int) $bloqueo->fetchColumn() + 1;
        $c->prepare('UPDATE series_comprobante SET ultimo_numero = ? WHERE serie = ?')->execute([$numero, $serie]);
        $cab['serie'] = $serie;
        $cab['numero'] = $numero;
        $campos = array_keys($cab);
        $c->prepare('INSERT INTO comprobantes (' . implode(', ', $campos) . ') VALUES (' . implode(', ', array_fill(0, count($campos), '?')) . ')')
          ->execute(array_values($cab));
        $id = (int) $c->lastInsertId();
        $linea = $c->prepare('INSERT INTO comprobante_items (id_comprobante, codigo, descripcion, unidad, cantidad, precio_unitario, total) VALUES (?, ?, ?, ?, ?, ?, ?)');
        foreach ($items as $it) {
            $linea->execute([$id, $it['codigo'] ?? '', mb_substr($it['descripcion'], 0, 250), $it['unidad'] ?? 'NIU', $it['cantidad'], $it['precio_unitario'], $it['total']]);
        }
        return $id;
    }

    /** Vales de un cliente que aún no están en un comprobante vigente, en un periodo. */
    public function Vales_Facturables(int $idCliente, string $desde, string $hasta): array
    {
        $c = conexionBD::conexionPDO();
        $q = $c->prepare("SELECT vc.id_credito, vc.numero_vale, vc.placa, vc.monto, vc.estado, vc.created_at, rt.numero_documento AS turno
                          FROM ventas_credito vc
                          LEFT JOIN reportes_turno rt ON rt.id_reporte = vc.id_reporte
                          WHERE vc.id_cliente = ? AND vc.estado <> 'ANULADO' AND vc.monto > 0
                            AND vc.created_at >= ? AND vc.created_at < DATE_ADD(?, INTERVAL 1 DAY)
                            AND NOT EXISTS (SELECT 1 FROM comprobante_vales cv WHERE cv.id_credito = vc.id_credito)
                          ORDER BY vc.created_at, vc.id_credito");
        $q->execute([$idCliente, $desde, $hasta]);
        return $q->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Clientes con vales por facturar, para elegir en la pantalla. */
    public function Clientes_Con_Vales(): array
    {
        return conexionBD::conexionPDO()->query("SELECT cl.id_cliente, cl.nombre_completo, cl.dni_ruc, COUNT(*) AS vales, SUM(vc.monto) AS total
            FROM ventas_credito vc INNER JOIN clientes cl ON cl.id_cliente = vc.id_cliente
            WHERE vc.estado <> 'ANULADO' AND vc.monto > 0 AND NOT EXISTS (SELECT 1 FROM comprobante_vales cv WHERE cv.id_credito = vc.id_credito)
            GROUP BY cl.id_cliente, cl.nombre_completo, cl.dni_ruc ORDER BY cl.nombre_completo")->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Un comprobante para varios vales de un cliente, con una línea por vale. La factura exige RUC.
     * Cada vale queda ligado al comprobante (clave única): no se puede facturar dos veces.
     */
    public function Facturar_Vales(int $idCliente, array $idsVales, string $tipo, int $idUsuario): int
    {
        if (!in_array($tipo, ['FACTURA', 'BOLETA'], true)) {
            throw new DomainException('Elija factura o boleta.');
        }
        $ids = array_values(array_unique(array_filter(array_map('intval', $idsVales))));
        if (!$ids) {
            throw new DomainException('Marque al menos un vale.');
        }
        $c = conexionBD::conexionPDO();
        $cliente = $c->prepare('SELECT nombre_completo, dni_ruc, direccion FROM clientes WHERE id_cliente = ?');
        $cliente->execute([$idCliente]);
        $cl = $cliente->fetch(PDO::FETCH_ASSOC);
        if (!$cl) {
            throw new DomainException('El cliente no existe.');
        }
        [$clienteTipo, $documento, $denominacion] = self::Datos_Cliente($cl['nombre_completo'], $cl['dni_ruc']);
        if ($tipo === 'FACTURA' && $clienteTipo !== '6') {
            throw new DomainException('Para emitir factura el cliente debe tener RUC (11 dígitos). Corríjalo en Clientes o emita boleta.');
        }
        $config = new Modelo_Configuracion();
        $porcentaje = (float) $config->Obtener('igv_porcentaje', '18');
        if ($tipo === 'BOLETA' && $clienteTipo === '-') {
            throw new DomainException('El cliente no tiene DNI ni RUC: regístrelo en Clientes para emitirle la boleta.');
        }
        $serie = (string) $config->Obtener($tipo === 'FACTURA' ? 'factura_serie_factura' : 'factura_serie_boleta');

        $c->beginTransaction();
        try {
            $marcas = implode(',', array_fill(0, count($ids), '?'));
            $q = $c->prepare("SELECT vc.id_credito, vc.numero_vale, vc.placa, vc.monto, vc.created_at FROM ventas_credito vc
                              WHERE vc.id_credito IN ($marcas) AND vc.id_cliente = ? AND vc.estado <> 'ANULADO' AND vc.monto > 0
                                AND NOT EXISTS (SELECT 1 FROM comprobante_vales cv WHERE cv.id_credito = vc.id_credito)
                              ORDER BY vc.created_at, vc.id_credito FOR UPDATE");
            $q->execute(array_merge($ids, [$idCliente]));
            $vales = $q->fetchAll(PDO::FETCH_ASSOC);
            if (count($vales) !== count($ids)) {
                throw new DomainException('Algún vale ya está en otro comprobante, está anulado o no es de este cliente. Actualice la lista.');
            }
            $items = [];
            $suma = 0.0;
            foreach ($vales as $v) {
                $monto = round((float) $v['monto'], 2);
                $suma += $monto;
                $placa = trim((string) $v['placa']);
                $items[] = ['codigo' => 'VALE', 'unidad' => 'NIU', 'cantidad' => 1, 'precio_unitario' => $monto, 'total' => $monto,
                    'descripcion' => 'Consumo de combustible, vale ' . html_entity_decode((string) $v['numero_vale'], ENT_QUOTES, 'UTF-8')
                        . ($placa !== '' ? ', placa ' . $placa : '') . ', ' . date('d/m/Y', strtotime($v['created_at']))];
            }
            [$gravada, $igv, $total] = self::Importes($suma, $porcentaje);
            $id = $this->Guardar_Con_Numero($c, $serie, [
                'origen' => 'VALES', 'id_cliente' => $idCliente, 'tipo' => $tipo, 'cliente_tipo' => $clienteTipo, 'cliente_documento' => $documento,
                'cliente_denominacion' => $denominacion, 'cliente_direccion' => $cl['direccion'], 'gravada' => $gravada, 'igv' => $igv, 'total' => $total,
                'porcentaje_igv' => $porcentaje, 'id_usuario' => $idUsuario,
            ], $items);
            $liga = $c->prepare('INSERT INTO comprobante_vales (id_credito, id_comprobante) VALUES (?, ?)');
            foreach ($vales as $v) {
                $liga->execute([(int) $v['id_credito'], $id]);
            }
            $c->commit();
        } catch (Throwable $e) {
            $c->rollBack();
            throw $e;
        }
        return $id;
    }

    /** Lo que queda de un comprobante: su total menos las notas de crédito vigentes que lo modifican. */
    private function Restante(int $idComprobante, float $total): float
    {
        $q = conexionBD::conexionPDO()->prepare("SELECT COALESCE(SUM(total), 0) FROM comprobantes
                                                WHERE id_referencia = ? AND tipo = 'NCREDITO' AND estado NOT IN ('ANULADO', 'CANCELADO')");
        $q->execute([$idComprobante]);
        return round($total - (float) $q->fetchColumn(), 2);
    }

    /**
     * Nota de crédito o de débito sobre una factura o boleta ya enviada. La de crédito no puede superar lo que
     * queda del comprobante; la de anulación o devolución total va siempre por todo lo que queda.
     */
    public function Crear_Nota(int $idReferencia, string $clase, string $motivo, string $sustento, float $monto, int $idUsuario): int
    {
        $sustento = mb_substr(trim($sustento), 0, 250);
        $motivos = $clase === 'NCREDITO' ? self::MOTIVOS_NC : ($clase === 'NDEBITO' ? self::MOTIVOS_ND : null);
        if ($motivos === null) {
            throw new DomainException('Elija nota de crédito o de débito.');
        }
        if (!isset($motivos[$motivo])) {
            throw new DomainException('Elija el motivo de la nota.');
        }
        if ($sustento === '') {
            throw new DomainException('Explique el sustento de la nota: queda en el documento que recibe el cliente.');
        }
        $c = conexionBD::conexionPDO();
        $q = $c->prepare('SELECT * FROM comprobantes WHERE id_comprobante = ?');
        $q->execute([$idReferencia]);
        $ref = $q->fetch(PDO::FETCH_ASSOC);
        if (!$ref || !in_array($ref['tipo'], ['FACTURA', 'BOLETA'], true)) {
            throw new DomainException('Las notas se emiten sobre una factura o una boleta.');
        }
        if (!in_array($ref['estado'], ['ACEPTADO', 'ENVIADO'], true)) {
            throw new DomainException('Solo se emite una nota sobre un comprobante ya enviado a SUNAT.');
        }
        $restante = $this->Restante($idReferencia, (float) $ref['total']);
        if ($clase === 'NCREDITO') {
            if ($restante <= 0) {
                throw new DomainException('Este comprobante ya tiene notas de crédito por todo su importe.');
            }
            if (in_array($motivo, self::NC_TOTALES, true)) {
                $monto = $restante;
            } elseif (!($monto > 0) || round($monto, 2) > $restante) {
                throw new DomainException('El monto de la nota debe ser mayor que cero y no pasar de S/ ' . number_format($restante, 2) . ', lo que queda del comprobante.');
            }
        } elseif (!($monto > 0) || $monto > 100000) {
            throw new DomainException('El monto de la nota de débito debe ser mayor que cero.');
        }
        $config = new Modelo_Configuracion();
        $clave = 'factura_serie_' . ($clase === 'NCREDITO' ? 'nc' : 'nd') . '_' . ($ref['tipo'] === 'FACTURA' ? 'factura' : 'boleta');
        $serie = (string) $config->Obtener($clave);
        [$gravada, $igv, $total] = self::Importes($monto, (float) $ref['porcentaje_igv']);
        $descripcion = $motivos[$motivo] . ' de ' . self::NOMBRES[$ref['tipo']] . ' ' . $ref['serie'] . '-' . $ref['numero'] . ': ' . $sustento;

        $c->beginTransaction();
        try {
            $id = $this->Guardar_Con_Numero($c, $serie, [
                'origen' => 'NOTA', 'id_cliente' => $ref['id_cliente'], 'id_referencia' => $idReferencia, 'tipo_nota' => $motivo, 'motivo_nota' => $sustento,
                'tipo' => $clase, 'cliente_tipo' => $ref['cliente_tipo'], 'cliente_documento' => $ref['cliente_documento'],
                'cliente_denominacion' => $ref['cliente_denominacion'], 'cliente_direccion' => $ref['cliente_direccion'],
                'gravada' => $gravada, 'igv' => $igv, 'total' => $total, 'porcentaje_igv' => $ref['porcentaje_igv'], 'id_usuario' => $idUsuario,
            ], [['codigo' => 'NOTA', 'unidad' => 'ZZ', 'cantidad' => 1, 'precio_unitario' => $total, 'total' => $total, 'descripcion' => $descripcion]]);
            $c->prepare('INSERT INTO auditoria (id_usuario, usuario, accion, detalle, ip) VALUES (?, ?, ?, ?, ?)')
              ->execute([$idUsuario, $_SESSION['S_USU'] ?? null, $clase === 'NCREDITO' ? 'NOTA_CREDITO' : 'NOTA_DEBITO',
                  $serie . ' sobre ' . $ref['serie'] . '-' . $ref['numero'] . ' por S/ ' . number_format($total, 2) . ': ' . $sustento, $_SERVER['REMOTE_ADDR'] ?? null]);
            $c->commit();
        } catch (Throwable $e) {
            $c->rollBack();
            throw $e;
        }
        return $id;
    }

    /** Un comprobante de vales anulado o cancelado libera sus vales para facturarlos de nuevo. */
    private function Liberar_Vales(int $id): void
    {
        conexionBD::conexionPDO()->prepare('DELETE FROM comprobante_vales WHERE id_comprobante = ?')->execute([$id]);
    }

    private function Proveedor(): ?Proveedor_Nubefact
    {
        $config = new Modelo_Configuracion();
        $ruta = env('NUBEFACT_RUTA') ?: $config->Obtener('factura_ruta');
        $token = $config->Obtener('factura_token');
        return ($ruta && $token) ? new Proveedor_Nubefact($ruta, $token) : null;
    }

    private function Obtener(int $id): array
    {
        $c = conexionBD::conexionPDO();
        $query = $c->prepare('SELECT co.*, v.galones, v.placa, p.nombre AS producto, p.id_producto,
                                     r.tipo AS ref_tipo, r.serie AS ref_serie, r.numero AS ref_numero
                              FROM comprobantes co
                              LEFT JOIN ventas v ON co.id_venta = v.id_venta
                              LEFT JOIN productos p ON v.id_producto = p.id_producto
                              LEFT JOIN comprobantes r ON r.id_comprobante = co.id_referencia
                              WHERE co.id_comprobante = ?');
        $query->execute([$id]);
        $fila = $query->fetch(PDO::FETCH_ASSOC);
        if (!$fila) {
            throw new DomainException('El comprobante no existe.');
        }
        return $fila;
    }

    /** Documento según el formato de Nubefact. El precio de venta ya incluye el IGV. */
    private function Armar_Documento(array $f): array
    {
        $config = new Modelo_Configuracion();
        if ($f['id_venta'] === null) {
            return $this->Armar_Documento_Con_Items($f);
        }
        $galones = (float) $f['galones'];
        return [
            'tipo_de_comprobante' => (string) self::TIPOS[$f['tipo']],
            'serie' => $f['serie'],
            'numero' => (string) $f['numero'],
            'sunat_transaction' => '1',
            'cliente_tipo_de_documento' => $f['cliente_tipo'],
            'cliente_numero_de_documento' => $f['cliente_documento'],
            'cliente_denominacion' => $f['cliente_denominacion'],
            'cliente_direccion' => (string) $f['cliente_direccion'],
            'cliente_email' => '',
            'fecha_de_emision' => date('d-m-Y', strtotime($f['creado_en'])),
            'fecha_de_vencimiento' => '',
            'moneda' => '1',
            'tipo_de_cambio' => '',
            'porcentaje_de_igv' => number_format((float) $f['porcentaje_igv'], 2, '.', ''),
            'total_gravada' => number_format((float) $f['gravada'], 2, '.', ''),
            'total_igv' => number_format((float) $f['igv'], 2, '.', ''),
            'total' => number_format((float) $f['total'], 2, '.', ''),
            'detraccion' => 'false',
            'observaciones' => '',
            'enviar_automaticamente_a_la_sunat' => 'true',
            'enviar_automaticamente_al_cliente' => 'false',
            'placa_vehiculo' => (string) $f['placa'],
            'items' => [[
                'unidad_de_medida' => (string) $config->Obtener('factura_unidad_medida', 'GLL'),
                'codigo' => (string) $f['id_producto'],
                'descripcion' => (string) $f['producto'],
                'cantidad' => number_format($galones, 3, '.', ''),
                // Se derivan de los importes para que cantidad por valor coincida con el subtotal, sin diferencias de redondeo.
                'valor_unitario' => number_format((float) $f['gravada'] / $galones, 10, '.', ''),
                'precio_unitario' => number_format((float) $f['total'] / $galones, 10, '.', ''),
                'descuento' => '',
                'subtotal' => number_format((float) $f['gravada'], 2, '.', ''),
                'tipo_de_igv' => '1',
                'igv' => number_format((float) $f['igv'], 2, '.', ''),
                'total' => number_format((float) $f['total'], 2, '.', ''),
                'anticipo_regularizacion' => 'false',
                'anticipo_documento_serie' => '',
                'anticipo_documento_numero' => '',
            ]],
        ];
    }

    /** Comprobante de vales o nota: las líneas están en comprobante_items, con el precio con IGV. */
    private function Armar_Documento_Con_Items(array $f): array
    {
        $c = conexionBD::conexionPDO();
        $q = $c->prepare('SELECT * FROM comprobante_items WHERE id_comprobante = ? ORDER BY id_item');
        $q->execute([(int) $f['id_comprobante']]);
        $factor = 1 + (float) $f['porcentaje_igv'] / 100;
        $items = [];
        $lineas = $q->fetchAll(PDO::FETCH_ASSOC);
        $restoGravada = (float) $f['gravada'];
        $restoIgv = (float) $f['igv'];
        foreach ($lineas as $i => $l) {
            $total = round((float) $l['total'], 2);
            // La última línea toma lo que falta, para que la suma de las líneas coincida con los totales del comprobante.
            $gravada = $i === count($lineas) - 1 ? round($restoGravada, 2) : round($total / $factor, 2);
            $igv = $i === count($lineas) - 1 ? round($restoIgv, 2) : round($total - $gravada, 2);
            $restoGravada -= $gravada;
            $restoIgv -= $igv;
            $cantidad = (float) $l['cantidad'];
            $items[] = [
                'unidad_de_medida' => $l['unidad'], 'codigo' => $l['codigo'], 'descripcion' => $l['descripcion'],
                'cantidad' => number_format($cantidad, 3, '.', ''),
                'valor_unitario' => number_format($gravada / $cantidad, 10, '.', ''),
                'precio_unitario' => number_format($total / $cantidad, 10, '.', ''),
                'descuento' => '', 'subtotal' => number_format($gravada, 2, '.', ''), 'tipo_de_igv' => '1',
                'igv' => number_format($igv, 2, '.', ''), 'total' => number_format($total, 2, '.', ''),
                'anticipo_regularizacion' => 'false', 'anticipo_documento_serie' => '', 'anticipo_documento_numero' => '',
            ];
        }
        $doc = [
            'tipo_de_comprobante' => (string) self::TIPOS[$f['tipo']], 'serie' => $f['serie'], 'numero' => (string) $f['numero'],
            'sunat_transaction' => '1', 'cliente_tipo_de_documento' => $f['cliente_tipo'], 'cliente_numero_de_documento' => $f['cliente_documento'],
            'cliente_denominacion' => $f['cliente_denominacion'], 'cliente_direccion' => (string) $f['cliente_direccion'], 'cliente_email' => '',
            'fecha_de_emision' => date('d-m-Y', strtotime($f['creado_en'])), 'fecha_de_vencimiento' => '', 'moneda' => '1', 'tipo_de_cambio' => '',
            'porcentaje_de_igv' => number_format((float) $f['porcentaje_igv'], 2, '.', ''),
            'total_gravada' => number_format((float) $f['gravada'], 2, '.', ''), 'total_igv' => number_format((float) $f['igv'], 2, '.', ''),
            'total' => number_format((float) $f['total'], 2, '.', ''), 'detraccion' => 'false', 'observaciones' => (string) $f['motivo_nota'],
            'enviar_automaticamente_a_la_sunat' => 'true', 'enviar_automaticamente_al_cliente' => 'false',
            'items' => $items,
        ];
        if ($f['origen'] === 'NOTA') {
            $doc['documento_que_se_modifica_tipo'] = (string) self::TIPOS[$f['ref_tipo']];
            $doc['documento_que_se_modifica_serie'] = $f['ref_serie'];
            $doc['documento_que_se_modifica_numero'] = (string) $f['ref_numero'];
            $doc[$f['tipo'] === 'NCREDITO' ? 'tipo_de_nota_de_credito' : 'tipo_de_nota_de_debito'] = (string) (int) $f['tipo_nota'];
        }
        return $doc;
    }

    /**
     * Envía un comprobante pendiente. Nunca lanza errores del proveedor: el resultado queda en su estado.
     * Tras un intento sin respuesta se consulta antes de reenviar, para no intentar emitirlo dos veces.
     */
    public function Procesar(int $id): array
    {
        $f = $this->Obtener($id);
        if (!in_array($f['estado'], ['PENDIENTE', 'RECHAZADO'], true)) {
            return $f;
        }
        $proveedor = $this->Proveedor();
        if ($proveedor === null) {
            $this->Actualizar($id, ['ultimo_error' => 'Falta configurar la ruta y el token de Nubefact.']);
            return $this->Obtener($id);
        }

        $this->Actualizar($id, ['intentos' => (int) $f['intentos'] + 1]);
        try {
            if ($f['estado'] === 'PENDIENTE' && (int) $f['intentos'] > 0) {
                $previa = $proveedor->Consultar(self::TIPOS[$f['tipo']], $f['serie'], (int) $f['numero']);
                if (!isset($previa['errors'])) {
                    $this->Aplicar_Respuesta($id, $previa);
                    return $this->Obtener($id);
                }
            }
            $this->Aplicar_Respuesta($id, $proveedor->Generar($this->Armar_Documento($f)));
        } catch (Error_Red_Facturacion $e) {
            error_log("Comprobante $id: " . $e->getMessage());
            $this->Actualizar($id, ['estado' => 'PENDIENTE', 'ultimo_error' => mb_substr($e->getMessage(), 0, 500)]);
        }
        return $this->Obtener($id);
    }

    private function Aplicar_Respuesta(int $id, array $r): void
    {
        if (isset($r['errors'])) {
            $this->Actualizar($id, ['estado' => 'RECHAZADO', 'ultimo_error' => mb_substr(is_string($r['errors']) ? $r['errors'] : json_encode($r['errors'], JSON_UNESCAPED_UNICODE), 0, 500)]);
            return;
        }
        $aceptada = filter_var($r['aceptada_por_sunat'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $this->Actualizar($id, [
            'estado' => $aceptada ? 'ACEPTADO' : 'ENVIADO',
            'ultimo_error' => null,
            'enlace' => $r['enlace'] ?? null,
            'enlace_pdf' => $r['enlace_del_pdf'] ?? null,
            'enlace_xml' => $r['enlace_del_xml'] ?? null,
            'enlace_cdr' => $r['enlace_del_cdr'] ?? null,
            'codigo_hash' => $r['codigo_hash'] ?? null,
            'cadena_qr' => $r['cadena_para_codigo_qr'] ?? null,
            'sunat_descripcion' => mb_substr(trim(($r['sunat_description'] ?? '') . ' ' . ($r['sunat_note'] ?? '')), 0, 500),
        ]);
    }

    /** Consulta de nuevo lo que SUNAT aún no confirma y reintenta lo que no se pudo enviar. */
    public function Procesar_Cola(int $limite = 20): array
    {
        $c = conexionBD::conexionPDO();
        $proveedor = $this->Proveedor();
        $resultado = ['enviados' => 0, 'actualizados' => 0, 'sin_cambio' => 0];
        if ($proveedor === null) {
            return $resultado;
        }
        $limite = max(1, min(100, $limite));

        foreach ($c->query("SELECT id_comprobante FROM comprobantes WHERE estado = 'PENDIENTE' ORDER BY id_comprobante LIMIT $limite")->fetchAll(PDO::FETCH_COLUMN) as $id) {
            $f = $this->Procesar((int) $id);
            $f['estado'] === 'PENDIENTE' ? $resultado['sin_cambio']++ : $resultado['enviados']++;
        }

        foreach ($c->query("SELECT id_comprobante, tipo, serie, numero, estado FROM comprobantes WHERE estado IN ('ENVIADO', 'ANULACION_PENDIENTE') ORDER BY id_comprobante LIMIT $limite")->fetchAll(PDO::FETCH_ASSOC) as $f) {
            try {
                if ($f['estado'] === 'ENVIADO') {
                    $r = $proveedor->Consultar(self::TIPOS[$f['tipo']], $f['serie'], (int) $f['numero']);
                    if (!isset($r['errors'])) {
                        $this->Aplicar_Respuesta((int) $f['id_comprobante'], $r);
                    }
                } else {
                    $r = $proveedor->Consultar_Anulacion(self::TIPOS[$f['tipo']], $f['serie'], (int) $f['numero']);
                    if (!isset($r['errors']) && filter_var($r['aceptada_por_sunat'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                        $this->Actualizar((int) $f['id_comprobante'], ['estado' => 'ANULADO', 'ultimo_error' => null]);
                        $this->Liberar_Vales((int) $f['id_comprobante']);
                    }
                }
                $resultado['actualizados']++;
            } catch (Error_Red_Facturacion $e) {
                $resultado['sin_cambio']++;
            }
        }
        return $resultado;
    }

    public function Anular(int $id, string $motivo, int $idUsuario): array
    {
        $motivo = mb_substr(trim($motivo), 0, 100);
        if ($motivo === '') {
            throw new DomainException('Indique el motivo de la anulación.');
        }
        $f = $this->Obtener($id);
        if (!in_array($f['estado'], ['ACEPTADO', 'ENVIADO'], true)) {
            throw new DomainException('Solo se puede anular un comprobante ya enviado.');
        }
        $proveedor = $this->Proveedor();
        if ($proveedor === null) {
            throw new DomainException('Falta configurar la ruta y el token de Nubefact.');
        }
        try {
            $r = $proveedor->Anular(self::TIPOS[$f['tipo']], $f['serie'], (int) $f['numero'], $motivo);
        } catch (Error_Red_Facturacion $e) {
            throw new DomainException('No hubo respuesta de Nubefact. Inténtelo de nuevo más tarde.');
        }
        if (isset($r['errors'])) {
            throw new DomainException('Nubefact rechazó la anulación: ' . (is_string($r['errors']) ? $r['errors'] : json_encode($r['errors'], JSON_UNESCAPED_UNICODE)));
        }
        $aceptada = filter_var($r['aceptada_por_sunat'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $this->Actualizar($id, ['estado' => $aceptada ? 'ANULADO' : 'ANULACION_PENDIENTE', 'motivo_anulacion' => $motivo, 'ultimo_error' => null]);
        if ($aceptada) {
            $this->Liberar_Vales($id);
        }
        $c = conexionBD::conexionPDO();
        $c->prepare('INSERT INTO auditoria (id_usuario, usuario, accion, detalle, ip) VALUES (?, ?, ?, ?, ?)')
          ->execute([$idUsuario, $_SESSION['S_USU'] ?? null, 'COMPROBANTE_ANULADO', $f['serie'] . '-' . $f['numero'] . ': ' . $motivo, $_SERVER['REMOTE_ADDR'] ?? null]);
        return $this->Obtener($id);
    }

    /**
     * Decide si una venta se puede anular según su comprobante. Uno que SUNAT conoce debe anularse primero;
     * uno que nunca llegó se cancela. Con un intento sin respuesta no se sabe, y hay que esperar la confirmación.
     */
    public function Preparar_Anulacion_Venta(int $idVenta): void
    {
        $c = conexionBD::conexionPDO();
        $query = $c->prepare('SELECT id_comprobante, estado, intentos FROM comprobantes WHERE id_venta = ?');
        $query->execute([$idVenta]);
        $f = $query->fetch(PDO::FETCH_ASSOC);
        if (!$f || in_array($f['estado'], ['ANULADO', 'CANCELADO'], true)) {
            return;
        }
        if ($f['estado'] === 'RECHAZADO' || ($f['estado'] === 'PENDIENTE' && (int) $f['intentos'] === 0)) {
            $this->Actualizar((int) $f['id_comprobante'], ['estado' => 'CANCELADO']);
            return;
        }
        throw new DomainException('Esta venta tiene un comprobante en SUNAT o por confirmar. Anúlelo primero en Comprobantes.');
    }

    public function Listar(string $desde, string $hasta, string $estado): array
    {
        $c = conexionBD::conexionPDO();
        $sql = "SELECT co.id_comprobante, co.id_venta, co.origen, co.tipo, co.serie, co.numero, co.cliente_documento, co.cliente_denominacion, co.gravada, co.igv, co.total,
                       co.estado, co.intentos, co.ultimo_error, co.enlace, co.enlace_pdf, co.enlace_xml, co.enlace_cdr, co.sunat_descripcion, co.motivo_anulacion, co.creado_en,
                       co.tipo_nota, co.motivo_nota, r.serie AS ref_serie, r.numero AS ref_numero,
                       ROUND(co.total - (SELECT COALESCE(SUM(n.total), 0) FROM comprobantes n WHERE n.id_referencia = co.id_comprobante AND n.tipo = 'NCREDITO'
                                         AND n.estado NOT IN ('ANULADO', 'CANCELADO')), 2) AS restante,
                       (SELECT COUNT(*) FROM comprobante_vales cv WHERE cv.id_comprobante = co.id_comprobante) AS vales,
                       u.usu_usuario AS usuario
                FROM comprobantes co LEFT JOIN usuario u ON co.id_usuario = u.id_usuario
                LEFT JOIN comprobantes r ON r.id_comprobante = co.id_referencia
                WHERE co.creado_en >= ? AND co.creado_en < DATE_ADD(?, INTERVAL 1 DAY)";
        $params = [$desde, $hasta];
        if ($estado !== '') {
            $sql .= ' AND co.estado = ?';
            $params[] = $estado;
        }
        $query = $c->prepare($sql . ' ORDER BY co.id_comprobante DESC LIMIT 500');
        $query->execute($params);
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Comprueba la ruta y el token consultando un documento que no debería existir. */
    public function Probar_Conexion(): array
    {
        $proveedor = $this->Proveedor();
        if ($proveedor === null) {
            throw new DomainException('Primero guarda la ruta y el token de Nubefact.');
        }
        try {
            $r = $proveedor->Consultar(2, 'B999', 99999999);
        } catch (Error_Red_Facturacion $e) {
            throw new DomainException($e->getMessage());
        }
        $mensaje = isset($r['errors']) ? (is_string($r['errors']) ? $r['errors'] : json_encode($r['errors'], JSON_UNESCAPED_UNICODE)) : 'Consulta aceptada.';
        return ['respuesta' => mb_substr($mensaje, 0, 300)];
    }

    private function Actualizar(int $id, array $campos): void
    {
        $c = conexionBD::conexionPDO();
        $partes = [];
        foreach (array_keys($campos) as $campo) {
            $partes[] = "$campo = ?";
        }
        $valores = array_values($campos);
        $valores[] = $id;
        $c->prepare('UPDATE comprobantes SET ' . implode(', ', $partes) . ', actualizado_en = NOW() WHERE id_comprobante = ?')->execute($valores);
    }
}
