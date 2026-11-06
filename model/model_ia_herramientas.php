<?php
require_once 'model_conexion.php';
require_once 'model_configuracion.php';

/**
 * Consultas de solo lectura que el chatbot puede ejecutar. El modelo elige la herramienta y
 * sus parámetros, pero nunca escribe SQL. El rol y el usuario salen de la sesión, no del chat:
 * un grifero solo obtiene sus propios turnos y no accede al ranking de griferos.
 */
class Modelo_IA_Herramientas extends conexionBD
{
    private const MAX_FILAS = 100;

    public function __construct(private int $idUsuario, private string $rol)
    {
    }

    private function verTodos(): bool
    {
        return puede('ver_todos');
    }

    public function Definiciones(): array
    {
        $rango = [
            'desde' => ['type' => 'string', 'description' => 'Fecha inicial AAAA-MM-DD'],
            'hasta' => ['type' => 'string', 'description' => 'Fecha final AAAA-MM-DD, inclusive'],
        ];

        $definiciones = [
            [
                'name' => 'ventas_por_periodo',
                'description' => 'Ventas de turnos cerrados en un rango de fechas: soles y galones por combustible (diesel, regular, premium) y pagos por método. Úsala para preguntas de ventas, galones o ingresos.',
                'inputSchema' => [
                    'type' => 'object', 'additionalProperties' => false,
                    'properties' => $rango + ['agrupar_por_dia' => ['type' => 'boolean', 'description' => 'true para una fila por día; false para un total del periodo']],
                    'required' => ['desde', 'hasta', 'agrupar_por_dia'],
                ],
                'strict' => true,
            ],
            [
                'name' => 'turnos_por_periodo',
                'description' => 'Lista de turnos en un rango de fechas con grifero, estado, total vendido, faltante o sobrante y validación.',
                'inputSchema' => [
                    'type' => 'object', 'additionalProperties' => false,
                    'properties' => $rango,
                    'required' => ['desde', 'hasta'],
                ],
                'strict' => true,
            ],
            [
                'name' => 'creditos_pendientes',
                'description' => 'Deudas pendientes por cliente: saldo, cantidad de vales, vencimiento más antiguo y días de atraso. Puede filtrar por nombre, DNI/RUC, o solo vencidos.',
                'inputSchema' => [
                    'type' => 'object', 'additionalProperties' => false,
                    'properties' => [
                        'cliente' => ['type' => 'string', 'description' => 'Parte del nombre o DNI/RUC. Cadena vacía para todos'],
                        'solo_vencidos' => ['type' => 'boolean', 'description' => 'true para incluir solo deudas vencidas'],
                    ],
                    'required' => ['cliente', 'solo_vencidos'],
                ],
                'strict' => true,
            ],
            [
                'name' => 'precios_y_surtidores',
                'description' => 'Precio actual de cada combustible y lectura actual de cada surtidor.',
                'inputSchema' => ['type' => 'object', 'additionalProperties' => false, 'properties' => (object) [], 'required' => []],
                'strict' => true,
            ],
        ];

        if (Modelo_Configuracion::Modulo_Activo('inventario')) {
            $definiciones[] = [
                'name' => 'stock_tanques',
                'description' => 'Stock teórico actual de cada tanque activo, con su capacidad, stock mínimo y si está bajo.',
                'inputSchema' => ['type' => 'object', 'additionalProperties' => false, 'properties' => (object) [], 'required' => []],
                'strict' => true,
            ];
        }

        if ($this->verTodos()) {
            $definiciones[] = [
                'name' => 'ranking_griferos',
                'description' => 'Griferos ordenados por ventas en un rango de fechas, con turnos trabajados y faltantes acumulados.',
                'inputSchema' => [
                    'type' => 'object', 'additionalProperties' => false,
                    'properties' => $rango,
                    'required' => ['desde', 'hasta'],
                ],
                'strict' => true,
            ];
        }
        return $definiciones;
    }

    /** Ejecuta una herramienta y devuelve el resultado en JSON para el modelo. */
    public function Ejecutar(string $nombre, array $entrada): string
    {
        switch ($nombre) {
            case 'ventas_por_periodo':
                $filas = $this->Ventas($entrada);
                break;
            case 'turnos_por_periodo':
                $filas = $this->Turnos($entrada);
                break;
            case 'creditos_pendientes':
                $filas = $this->Creditos($entrada);
                break;
            case 'precios_y_surtidores':
                $filas = $this->Precios();
                break;
            case 'stock_tanques':
                if (!Modelo_Configuracion::Modulo_Activo('inventario')) {
                    throw new InvalidArgumentException('El módulo de inventario está desactivado.');
                }
                $filas = $this->Consultar("SELECT t.nombre AS tanque, p.nombre AS combustible, t.stock_actual, t.capacidad_galones, t.stock_minimo,
                                                  IF(t.stock_actual <= t.stock_minimo, 'SI', 'NO') AS stock_bajo
                                           FROM tanques t LEFT JOIN productos p ON t.id_producto = p.id_producto
                                           WHERE t.estado = 'ACTIVO' ORDER BY t.nombre", []);
                break;
            case 'ranking_griferos':
                if (!$this->verTodos()) {
                    throw new InvalidArgumentException('Herramienta no permitida para este usuario.');
                }
                $filas = $this->Ranking($entrada);
                break;
            default:
                throw new InvalidArgumentException('Herramienta desconocida: ' . $nombre);
        }

        return json_encode([
            'filas' => array_slice($filas, 0, self::MAX_FILAS),
            'truncado' => count($filas) > self::MAX_FILAS,
        ], JSON_UNESCAPED_UNICODE);
    }

    private function Rango(array $entrada): array
    {
        foreach (['desde', 'hasta'] as $campo) {
            $fecha = DateTime::createFromFormat('!Y-m-d', (string) ($entrada[$campo] ?? ''));
            if (!$fecha || $fecha->format('Y-m-d') !== $entrada[$campo]) {
                throw new InvalidArgumentException("Fecha inválida en '$campo'. Use AAAA-MM-DD.");
            }
        }
        return [$entrada['desde'], $entrada['hasta']];
    }

    private function Ventas(array $e): array
    {
        [$desde, $hasta] = $this->Rango($e);
        $porDia = !empty($e['agrupar_por_dia']);
        $sql = 'SELECT ' . ($porDia ? 'fecha_reporte AS fecha, ' : '') . 'COUNT(*) AS turnos, SUM(total_ventas) AS total_soles,
                       SUM(total_diesel) AS soles_diesel, SUM(total_regular) AS soles_regular, SUM(total_premium) AS soles_premium,
                       SUM(total_galones) AS total_galones, SUM(galones_diesel) AS galones_diesel,
                       SUM(galones_regular) AS galones_regular, SUM(galones_premium) AS galones_premium,
                       SUM(monto_efectivo) AS efectivo, SUM(monto_yape) AS yape, SUM(monto_bcp) AS bcp,
                       SUM(monto_visa) AS visa, SUM(monto_credito) AS credito
                FROM reportes_turno
                WHERE estado = \'CERRADO\' AND fecha_reporte BETWEEN ? AND ?';
        $params = [$desde, $hasta];
        if (!$this->verTodos()) {
            $sql .= ' AND id_usuario = ?';
            $params[] = $this->idUsuario;
        }
        if ($porDia) {
            $sql .= ' GROUP BY fecha_reporte ORDER BY fecha_reporte';
        }
        return $this->Consultar($sql, $params);
    }

    private function Turnos(array $e): array
    {
        [$desde, $hasta] = $this->Rango($e);
        $sql = "SELECT rt.numero_documento, rt.fecha_reporte, rt.turno, rt.estado, CONCAT(u.usu_nombre, ' ', u.usu_apellido) AS grifero,
                       rt.total_ventas, rt.total_galones, rt.monto_faltante,
                       IF(rt.fecha_aprobacion IS NULL, 'NO', 'SI') AS validado
                FROM reportes_turno rt
                INNER JOIN usuario u ON rt.id_usuario = u.id_usuario
                WHERE rt.fecha_reporte BETWEEN ? AND ?";
        $params = [$desde, $hasta];
        if (!$this->verTodos()) {
            $sql .= ' AND rt.id_usuario = ?';
            $params[] = $this->idUsuario;
        }
        return $this->Consultar($sql . ' ORDER BY rt.fecha_reporte DESC, rt.id_reporte DESC LIMIT ' . (self::MAX_FILAS + 1), $params);
    }

    private function Creditos(array $e): array
    {
        $sql = "SELECT c.nombre_completo AS cliente, c.dni_ruc, COUNT(*) AS vales, SUM(vc.saldo_pendiente) AS saldo,
                       MIN(vc.fecha_vencimiento) AS vencimiento_mas_antiguo,
                       GREATEST(MAX(DATEDIFF(CURDATE(), vc.fecha_vencimiento)), 0) AS dias_atraso
                FROM ventas_credito vc
                INNER JOIN clientes c ON vc.id_cliente = c.id_cliente
                WHERE vc.estado = 'PENDIENTE' AND vc.saldo_pendiente > 0";
        $params = [];
        $cliente = trim((string) ($e['cliente'] ?? ''));
        if ($cliente !== '') {
            $sql .= ' AND (c.nombre_completo LIKE ? OR c.dni_ruc LIKE ?)';
            $params[] = "%$cliente%";
            $params[] = "%$cliente%";
        }
        if (!empty($e['solo_vencidos'])) {
            $sql .= ' AND vc.fecha_vencimiento < CURDATE()';
        }
        return $this->Consultar($sql . ' GROUP BY c.id_cliente, c.nombre_completo, c.dni_ruc ORDER BY saldo DESC LIMIT ' . (self::MAX_FILAS + 1), $params);
    }

    private function Precios(): array
    {
        return $this->Consultar("SELECT s.numero_maquina, s.codigo, p.nombre AS combustible, p.precio_actual, s.lectura_actual, s.estado
                                 FROM surtidores s INNER JOIN productos p ON s.id_producto = p.id_producto
                                 ORDER BY s.numero_maquina, s.codigo", []);
    }

    private function Ranking(array $e): array
    {
        [$desde, $hasta] = $this->Rango($e);
        return $this->Consultar("SELECT CONCAT(u.usu_nombre, ' ', u.usu_apellido) AS grifero, COUNT(*) AS turnos,
                                        SUM(rt.total_ventas) AS total_ventas, SUM(rt.total_galones) AS total_galones,
                                        SUM(rt.monto_faltante) AS faltante_acumulado
                                 FROM reportes_turno rt INNER JOIN usuario u ON rt.id_usuario = u.id_usuario
                                 WHERE rt.estado = 'CERRADO' AND rt.fecha_reporte BETWEEN ? AND ?
                                 GROUP BY u.id_usuario, u.usu_nombre, u.usu_apellido ORDER BY total_ventas DESC", [$desde, $hasta]);
    }

    private function Consultar(string $sql, array $params): array
    {
        $c = conexionBD::conexionPDO();
        $query = $c->prepare($sql);
        $query->execute($params);
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }
}
