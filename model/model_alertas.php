<?php
require_once 'model_conexion.php';
require_once 'model_configuracion.php';
require_once 'model_precios.php';
require_once 'model_respaldos.php';

/** Avisos de la página principal. Solo se calculan los elegidos en Configuración y los de módulos activos. */
class Modelo_Alertas extends conexionBD
{
    /** Definición de cada aviso: módulo requerido, nombre en Configuración, texto, nivel y pantalla a la que lleva. */
    public const ALERTAS = [
        'creditos_vencidos'    => [null, 'Créditos vencidos', 'crédito(s) vencido(s) por cobrar', 'danger', 'creditos/view_creditos_pendientes.php',
                                   "SELECT COUNT(*) FROM ventas_credito WHERE estado = 'PENDIENTE' AND saldo_pendiente > 0 AND fecha_vencimiento < CURDATE()"],
        'turnos_sin_validar'   => [null, 'Turnos sin validar', 'turno(s) cerrado(s) sin validar', 'warning', 'reportes/view_validar_reportes.php',
                                   "SELECT COUNT(*) FROM reportes_turno WHERE estado = 'CERRADO' AND id_administrador IS NULL"],
        'stock_bajo'           => ['inventario', 'Stock bajo', 'tanque(s) con stock bajo', 'danger', 'inventario/view_tanques.php',
                                   "SELECT COUNT(*) FROM tanques WHERE estado = 'ACTIVO' AND stock_actual <= stock_minimo"],
        'compras_por_vencer'   => ['inventario', 'Compras por vencer', 'compra(s) por pagar vencida(s) o que vencen en 7 días', 'warning', 'inventario/view_compras.php',
                                   "SELECT COUNT(*) FROM compras WHERE estado = 'REGISTRADA' AND total > monto_pagado AND fecha_vencimiento <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)"],
        'varillaje_diferencia' => ['inventario', 'Diferencias de varillaje', 'medición(es) de varillaje con diferencia sin ajustar en los últimos 7 días', 'info', 'inventario/view_varillaje.php',
                                   "SELECT COUNT(*) FROM varillajes WHERE ajuste_aplicado = 0 AND diferencia <> 0 AND fecha >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)"],
        'arqueos_pendientes'   => ['caja', 'Turnos sin arqueo', 'turno(s) de los últimos 7 días sin arqueo de caja', 'warning', 'caja/view_caja.php',
                                   "SELECT COUNT(*) FROM reportes_turno rt LEFT JOIN arqueos a ON a.id_reporte = rt.id_reporte WHERE rt.estado = 'CERRADO' AND a.id_arqueo IS NULL AND rt.fecha_reporte >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)"],
        'diferencias_caja'     => ['caja', 'Diferencias de caja', 'diferencia(s) de caja por resolver', 'danger', 'caja/view_caja.php',
                                   "SELECT COUNT(*) FROM arqueos WHERE diferencia <> 0 AND resolucion = 'PENDIENTE'"],
        'comprobantes_problema' => ['facturacion', 'Comprobantes con problemas', 'comprobante(s) electrónico(s) pendiente(s) o rechazado(s)', 'danger', 'facturacion/view_comprobantes.php',
                                   "SELECT COUNT(*) FROM comprobantes WHERE estado IN ('PENDIENTE', 'RECHAZADO')"],
        'respaldo_antiguo'     => [null, 'Copia de seguridad atrasada', 'semana(s) sin una copia de seguridad', 'warning', 'configuracion/view_configuracion.php', 'respaldo'],
        'precios_programados'  => [null, 'Precios programados', 'cambio(s) de precio programado(s)', 'info', 'productos/view_productos.php',
                                   "SELECT COUNT(*) FROM precios_programados WHERE estado = 'PENDIENTE'"],
        'tienda_stock_bajo'    => ['tienda', 'Tienda: stock bajo', 'producto(s) de la tienda con stock bajo', 'warning', 'tienda/view_tienda.php',
                                   "SELECT COUNT(*) FROM tienda_productos WHERE activo = 1 AND stock_minimo > 0 AND stock <= stock_minimo"],
        'flotas_sobre_limite'  => ['flotas', 'Flotas sobre su límite', 'flota(s) con deuda sobre su límite de crédito', 'danger', 'flotas/view_flotas.php',
                                   "SELECT COUNT(*) FROM flotas f WHERE f.limite_credito IS NOT NULL AND (SELECT COALESCE(SUM(v.saldo_pendiente), 0) FROM ventas_credito v WHERE v.id_cliente = f.id_cliente AND v.estado = 'PENDIENTE') > f.limite_credito"],
    ];

    public function Listar(): array
    {
        Modelo_Precios::Aplicar_Vencidos();
        $config = new Modelo_Configuracion();
        $elegidas = explode(',', (string) $config->Obtener('inicio_alertas', ''));
        $c = conexionBD::conexionPDO();
        $avisos = [];

        foreach (self::ALERTAS as $clave => [$modulo, , $texto, $nivel, $vista, $sql]) {
            if (!in_array($clave, $elegidas, true) || ($modulo !== null && !Modelo_Configuracion::Modulo_Activo($modulo))) {
                continue;
            }
            try {
                if ($sql === 'respaldo') {
                    $dias = Modelo_Respaldos::Dias_Sin_Respaldo();
                    $cantidad = $dias === null ? 1 : intdiv($dias, 7);
                } else {
                    $cantidad = (int) $c->query($sql)->fetchColumn();
                }
            } catch (PDOException $e) {
                error_log("Alerta $clave: " . $e->getMessage());
                continue;
            }
            if ($cantidad > 0) {
                $avisos[] = ['clave' => $clave, 'cantidad' => $cantidad, 'texto' => $texto, 'nivel' => $nivel, 'vista' => $vista];
            }
        }
        return $avisos;
    }

    /** Solo los avisos que llevan a una pantalla que el usuario en sesión puede abrir (permiso y módulo activo). */
    public static function Visibles(array $avisos): array
    {
        return array_values(array_filter($avisos, fn($a) => ruta_accesible('view/' . $a['vista'])));
    }

    public static function Opciones(): array
    {
        $opciones = [];
        foreach (self::ALERTAS as $clave => $a) {
            $opciones[] = ['clave' => $clave, 'nombre' => $a[1]];
        }
        return $opciones;
    }
}
