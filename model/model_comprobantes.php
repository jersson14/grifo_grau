<?php
require_once 'model_conexion.php';
require_once 'model_configuracion.php';
require_once __DIR__ . '/facturacion/proveedor_nubefact.php';

class Modelo_Comprobantes extends conexionBD
{
    private const TIPOS = ['FACTURA' => 1, 'BOLETA' => 2];

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
        $query = $c->prepare('SELECT co.*, v.galones, v.placa, p.nombre AS producto, p.id_producto
                              FROM comprobantes co
                              INNER JOIN ventas v ON co.id_venta = v.id_venta
                              LEFT JOIN productos p ON v.id_producto = p.id_producto
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
        $sql = 'SELECT co.id_comprobante, co.id_venta, co.tipo, co.serie, co.numero, co.cliente_documento, co.cliente_denominacion, co.gravada, co.igv, co.total,
                       co.estado, co.intentos, co.ultimo_error, co.enlace, co.enlace_pdf, co.enlace_xml, co.enlace_cdr, co.sunat_descripcion, co.motivo_anulacion, co.creado_en,
                       u.usu_usuario AS usuario
                FROM comprobantes co LEFT JOIN usuario u ON co.id_usuario = u.id_usuario
                WHERE co.creado_en >= ? AND co.creado_en < DATE_ADD(?, INTERVAL 1 DAY)';
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
