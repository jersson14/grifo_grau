<?php
require_once __DIR__ . '/model_conexion.php';

/** Página principal (Dashboard): bloques que se pueden mostrar y el resumen propio de cada grifero. */
class Modelo_Inicio
{
    /**
     * Bloques con cifras de todo el grifo: solo los ve quien tiene «Reportes gerenciales».
     * «mi_resumen» lo ve quien no puede ver a todos (el grifero) y «accesos» cualquiera, con los enlaces que puede abrir.
     */
    public const BLOQUES = [
        'indicadores'   => 'Indicadores del día: turnos abiertos, ventas de hoy, créditos por cobrar y turnos por validar',
        'ventas_semana' => 'Ventas de los últimos 7 días',
        'combustibles'  => 'Ventas por combustible y métodos de pago del mes',
        'turnos'        => 'Comparativo por turno y desempeño de cada grifero',
        'resumen_mes'   => 'Resumen del mes por combustible',
        'creditos'      => 'Créditos del mes',
        'mi_resumen'    => 'Resumen propio del grifero (sus turnos, ventas y faltantes del mes)',
        'accesos'       => 'Accesos rápidos',
    ];

    public static function Opciones(): array
    {
        $r = [];
        foreach (self::BLOQUES as $clave => $nombre) {
            $r[] = ['clave' => $clave, 'nombre' => $nombre];
        }
        return $r;
    }

    /** Bloques elegidos en Configuración; sin configurar, todos. */
    public static function Elegidos(): array
    {
        $c = conexionBD::conexionPDO();
        $valor = $c->query("SELECT valor FROM configuracion WHERE clave = 'inicio_bloques'")->fetchColumn();
        if ($valor === false || $valor === null) {
            return array_keys(self::BLOQUES);
        }
        return array_values(array_intersect(explode(',', (string) $valor), array_keys(self::BLOQUES)));
    }

    /** Turnos, ventas y cuadre del mes del usuario, y su último turno. monto_faltante negativo es faltante. */
    public function Mi_Resumen(int $idUsuario): array
    {
        $c = conexionBD::conexionPDO();
        $q = $c->prepare("SELECT COUNT(*) AS turnos, COALESCE(SUM(total_ventas), 0) AS ventas, COALESCE(SUM(total_galones), 0) AS galones,
                                 COALESCE(SUM(CASE WHEN monto_faltante < 0 THEN -monto_faltante ELSE 0 END), 0) AS faltantes,
                                 COALESCE(SUM(ABS(COALESCE(monto_faltante, 0)) < 0.01), 0) AS cuadrados,
                                 COALESCE(SUM(monto_credito), 0) AS creditos
                          FROM reportes_turno
                          WHERE id_usuario = ? AND estado = 'CERRADO'
                            AND fecha_reporte >= DATE_FORMAT(CURDATE(), '%Y-%m-01') AND fecha_reporte <= CURDATE()");
        $q->execute([$idUsuario]);
        $mes = $q->fetch(PDO::FETCH_ASSOC);
        $q = $c->prepare("SELECT numero_documento, fecha_reporte, turno, estado, total_ventas, monto_faltante
                          FROM reportes_turno WHERE id_usuario = ? ORDER BY fecha_reporte DESC, id_reporte DESC LIMIT 1");
        $q->execute([$idUsuario]);
        $ultimo = $q->fetch(PDO::FETCH_ASSOC) ?: null;
        $q = $c->prepare("SELECT COUNT(*) FROM reportes_turno WHERE id_usuario = ? AND estado = 'ABIERTO'");
        $q->execute([$idUsuario]);
        return [
            'mes' => array_map(fn($v) => round((float) $v, 2), $mes),
            'ultimo' => $ultimo,
            'abiertos' => (int) $q->fetchColumn(),
        ];
    }
}
