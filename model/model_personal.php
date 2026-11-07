<?php
require_once __DIR__ . '/model_conexion.php';

/** Personal: fichas, horario semanal, asistencia, adelantos/descuentos/bonos y resumen del mes. */
class Modelo_Personal
{
    public const CARGOS = ['Grifero', 'Cajero', 'Supervisor', 'Administración', 'Limpieza', 'Vigilancia', 'Otro'];
    public const TURNOS = ['DIA' => 'Día', 'NOCHE' => 'Noche', 'DESCANSO' => 'Descanso'];
    public const ESTADOS = ['PRESENTE' => 'Presente', 'TARDANZA' => 'Tardanza', 'FALTA' => 'Falta', 'PERMISO' => 'Permiso'];
    public const TIPOS = ['ADELANTO' => 'Adelanto', 'DESCUENTO' => 'Descuento', 'BONO' => 'Bono'];

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

    private static function mes_valido(string $mes): string
    {
        return preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $mes) ? $mes : date('Y-m');
    }

    private function existe(int $id): void
    {
        $q = $this->c()->prepare('SELECT 1 FROM personal WHERE id_personal = ?');
        $q->execute([$id]);
        if (!$q->fetchColumn()) {
            throw new DomainException('La persona no existe.');
        }
    }

    // ---------- Fichas ----------

    public function Listar(): array
    {
        return $this->c()->query('SELECT p.*, u.usu_usuario AS usuario FROM personal p LEFT JOIN usuario u ON u.id_usuario = p.id_usuario
                                  ORDER BY p.activo DESC, p.nombres')->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Usuarios activos del sistema que aún no están vinculados a una ficha. */
    public function Usuarios_Libres(?int $idPersonal): array
    {
        $q = $this->c()->prepare("SELECT u.id_usuario, u.usu_usuario, CONCAT(u.usu_nombre, ' ', u.usu_apellido) AS nombre, u.usu_rol
                                  FROM usuario u
                                  WHERE u.usu_estatus = 'ACTIVO' AND NOT EXISTS (SELECT 1 FROM personal p WHERE p.id_usuario = u.id_usuario AND p.id_personal <> ?)
                                  ORDER BY u.usu_nombre");
        $q->execute([(int) $idPersonal]);
        return $q->fetchAll(PDO::FETCH_ASSOC);
    }

    public function Guardar(?int $id, array $d): int
    {
        $nombres = trim((string) ($d['nombres'] ?? ''));
        $dni = preg_replace('/\D/', '', (string) ($d['dni'] ?? ''));
        $cargo = (string) ($d['cargo'] ?? '');
        $telefono = preg_replace('/[\s-]/', '', (string) ($d['telefono'] ?? ''));
        $ingreso = trim((string) ($d['fecha_ingreso'] ?? ''));
        $sueldo = trim((string) ($d['sueldo'] ?? ''));
        $idUsuario = (int) ($d['id_usuario'] ?? 0);
        $obs = trim((string) ($d['observaciones'] ?? ''));
        if ($nombres === '' || mb_strlen($nombres) > 150) {
            throw new DomainException('Escriba los nombres y apellidos (máximo 150 caracteres).');
        }
        if ($dni !== '' && !preg_match('/^\d{8}$|^\d{9,12}$/', $dni)) {
            throw new DomainException('El DNI tiene 8 dígitos (o el carné de extranjería, de 9 a 12).');
        }
        if (!in_array($cargo, self::CARGOS, true)) {
            throw new DomainException('Elija el cargo.');
        }
        if ($telefono !== '' && !preg_match('/^\+?\d{6,15}$/', $telefono)) {
            throw new DomainException('El teléfono solo lleva números.');
        }
        if ($ingreso !== '' && !self::fecha_valida($ingreso)) {
            throw new DomainException('Revise la fecha de ingreso.');
        }
        if ($sueldo !== '' && (!is_numeric($sueldo) || (float) $sueldo < 0 || (float) $sueldo > 100000)) {
            throw new DomainException('El sueldo debe ser un monto válido.');
        }
        if (mb_strlen($obs) > 300) {
            throw new DomainException('Las observaciones tienen como máximo 300 caracteres.');
        }
        $c = $this->c();
        if ($dni !== '') {
            $q = $c->prepare('SELECT id_personal FROM personal WHERE dni = ? AND id_personal <> ?');
            $q->execute([$dni, (int) $id]);
            if ($q->fetchColumn()) {
                throw new DomainException('Ya hay una ficha con ese DNI.');
            }
        }
        if ($idUsuario) {
            $q = $c->prepare('SELECT id_personal FROM personal WHERE id_usuario = ? AND id_personal <> ?');
            $q->execute([$idUsuario, (int) $id]);
            if ($q->fetchColumn()) {
                throw new DomainException('Ese usuario ya está vinculado a otra ficha.');
            }
        }
        $valores = [$nombres, $dni ?: null, $cargo, $telefono ?: null, $ingreso ?: null, $sueldo === '' ? null : round((float) $sueldo, 2),
            $idUsuario ?: null, ($d['activo'] ?? '1') === '1' ? 1 : 0, $obs ?: null];
        if ($id) {
            $this->existe($id);
            $c->prepare('UPDATE personal SET nombres = ?, dni = ?, cargo = ?, telefono = ?, fecha_ingreso = ?, sueldo = ?, id_usuario = ?, activo = ?, observaciones = ? WHERE id_personal = ?')
                ->execute(array_merge($valores, [$id]));
            return $id;
        }
        $c->prepare('INSERT INTO personal (nombres, dni, cargo, telefono, fecha_ingreso, sueldo, id_usuario, activo, observaciones) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)')->execute($valores);
        return (int) $c->lastInsertId();
    }

    // ---------- Horario semanal ----------

    /** Semana que empieza el lunes de la fecha dada: personal activo y su turno de cada día. */
    public function Horario_Semana(string $fecha): array
    {
        if (!self::fecha_valida($fecha)) {
            $fecha = date('Y-m-d');
        }
        $lunes = date('Y-m-d', strtotime($fecha . ' -' . ((int) date('N', strtotime($fecha)) - 1) . ' days'));
        $dias = [];
        for ($i = 0; $i < 7; $i++) {
            $dias[] = date('Y-m-d', strtotime($lunes . " +$i days"));
        }
        $c = $this->c();
        $personas = $c->query('SELECT id_personal, nombres, cargo FROM personal WHERE activo = 1 ORDER BY nombres')->fetchAll(PDO::FETCH_ASSOC);
        $q = $c->prepare('SELECT id_personal, fecha, turno FROM personal_horario WHERE fecha BETWEEN ? AND ?');
        $q->execute([$dias[0], $dias[6]]);
        $mapa = [];
        foreach ($q->fetchAll(PDO::FETCH_ASSOC) as $h) {
            $mapa[$h['id_personal']][$h['fecha']] = $h['turno'];
        }
        foreach ($personas as &$p) {
            $p['turnos'] = array_map(fn($d) => $mapa[$p['id_personal']][$d] ?? '', $dias);
        }
        return ['dias' => $dias, 'personas' => $personas];
    }

    /** Asigna (o quita, con turno vacío) el turno de una persona en un día. */
    public function Guardar_Horario(int $idPersonal, string $fecha, string $turno): void
    {
        if (!self::fecha_valida($fecha)) {
            throw new DomainException('Fecha no válida.');
        }
        if ($turno !== '' && !isset(self::TURNOS[$turno])) {
            throw new DomainException('Elija día, noche o descanso.');
        }
        $this->existe($idPersonal);
        $c = $this->c();
        if ($turno === '') {
            $c->prepare('DELETE FROM personal_horario WHERE id_personal = ? AND fecha = ?')->execute([$idPersonal, $fecha]);
            return;
        }
        $c->prepare('INSERT INTO personal_horario (id_personal, fecha, turno) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE turno = VALUES(turno)')
            ->execute([$idPersonal, $fecha, $turno]);
    }

    /** Copia el horario de la semana anterior a la semana del lunes dado, sin pisar lo ya asignado. */
    public function Copiar_Semana_Anterior(string $lunes): int
    {
        if (!self::fecha_valida($lunes) || (int) date('N', strtotime($lunes)) !== 1) {
            throw new DomainException('Indique el lunes de la semana.');
        }
        $q = $this->c()->prepare('INSERT IGNORE INTO personal_horario (id_personal, fecha, turno)
                                  SELECT h.id_personal, DATE_ADD(h.fecha, INTERVAL 7 DAY), h.turno
                                  FROM personal_horario h INNER JOIN personal p ON p.id_personal = h.id_personal AND p.activo = 1
                                  WHERE h.fecha BETWEEN DATE_SUB(?, INTERVAL 7 DAY) AND DATE_SUB(?, INTERVAL 1 DAY)');
        $q->execute([$lunes, $lunes]);
        return $q->rowCount();
    }

    // ---------- Asistencia ----------

    /** Personal activo con su turno programado, su asistencia del día y si registró un turno en el sistema. */
    public function Asistencia_Dia(string $fecha): array
    {
        if (!self::fecha_valida($fecha)) {
            $fecha = date('Y-m-d');
        }
        $q = $this->c()->prepare('SELECT p.id_personal, p.nombres, p.cargo, h.turno AS programado,
                                         a.estado, a.hora_entrada, a.hora_salida, a.observacion,
                                         (SELECT COUNT(*) FROM reportes_turno rt WHERE p.id_usuario IS NOT NULL AND rt.id_usuario = p.id_usuario AND rt.fecha_reporte = ?) AS turnos_sistema
                                  FROM personal p
                                  LEFT JOIN personal_horario h ON h.id_personal = p.id_personal AND h.fecha = ?
                                  LEFT JOIN personal_asistencia a ON a.id_personal = p.id_personal AND a.fecha = ?
                                  WHERE p.activo = 1 ORDER BY (h.turno IS NULL), h.turno, p.nombres');
        $q->execute([$fecha, $fecha, $fecha]);
        return ['fecha' => $fecha, 'personas' => $q->fetchAll(PDO::FETCH_ASSOC)];
    }

    public function Marcar_Asistencia(int $idPersonal, string $fecha, string $estado, string $entrada, string $salida, string $obs, int $idUsuario): void
    {
        if (!self::fecha_valida($fecha) || $fecha > date('Y-m-d')) {
            throw new DomainException('La asistencia se marca para hoy o días pasados.');
        }
        if (!isset(self::ESTADOS[$estado])) {
            throw new DomainException('Elija presente, tardanza, falta o permiso.');
        }
        $hora = fn($h) => $h === '' || preg_match('/^([01]\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/', $h);
        if (!$hora($entrada) || !$hora($salida)) {
            throw new DomainException('Revise las horas de entrada y salida.');
        }
        if (in_array($estado, ['FALTA', 'PERMISO'], true)) {
            $entrada = $salida = '';
        }
        if (mb_strlen(trim($obs)) > 200) {
            throw new DomainException('La observación tiene como máximo 200 caracteres.');
        }
        $this->existe($idPersonal);
        $this->c()->prepare('INSERT INTO personal_asistencia (id_personal, fecha, estado, hora_entrada, hora_salida, observacion, id_usuario) VALUES (?, ?, ?, ?, ?, ?, ?)
                             ON DUPLICATE KEY UPDATE estado = VALUES(estado), hora_entrada = VALUES(hora_entrada), hora_salida = VALUES(hora_salida),
                                                     observacion = VALUES(observacion), id_usuario = VALUES(id_usuario)')
            ->execute([$idPersonal, $fecha, $estado, $entrada ?: null, $salida ?: null, trim($obs) ?: null, $idUsuario]);
    }

    // ---------- Adelantos, descuentos y bonos ----------

    public function Movimientos(string $mes): array
    {
        $mes = self::mes_valido($mes);
        $q = $this->c()->prepare('SELECT m.*, p.nombres, CONCAT(u.usu_nombre, " ", u.usu_apellido) AS registrado_por
                                  FROM personal_movimientos m
                                  INNER JOIN personal p ON p.id_personal = m.id_personal
                                  LEFT JOIN usuario u ON u.id_usuario = m.id_usuario
                                  WHERE m.fecha >= ? AND m.fecha < DATE_ADD(?, INTERVAL 1 MONTH)
                                  ORDER BY m.fecha DESC, m.id_movimiento DESC');
        $q->execute([$mes . '-01', $mes . '-01']);
        return $q->fetchAll(PDO::FETCH_ASSOC);
    }

    public function Registrar_Movimiento(int $idPersonal, string $fecha, string $tipo, float $monto, string $motivo, int $idUsuario, ?int $idArqueo = null): int
    {
        $motivo = trim($motivo);
        if (!self::fecha_valida($fecha)) {
            throw new DomainException('Indique la fecha.');
        }
        if (!isset(self::TIPOS[$tipo])) {
            throw new DomainException('Elija adelanto, descuento o bono.');
        }
        if (!($monto > 0) || $monto > 100000) {
            throw new DomainException('El monto debe ser mayor que cero.');
        }
        if ($motivo === '' || mb_strlen($motivo) > 200) {
            throw new DomainException('Indique el motivo (máximo 200 caracteres).');
        }
        $this->existe($idPersonal);
        $c = $this->c();
        $c->prepare('INSERT INTO personal_movimientos (id_personal, fecha, tipo, monto, motivo, id_arqueo, id_usuario) VALUES (?, ?, ?, ?, ?, ?, ?)')
            ->execute([$idPersonal, $fecha, $tipo, round($monto, 2), $motivo, $idArqueo, $idUsuario]);
        return (int) $c->lastInsertId();
    }

    public function Anular_Movimiento(int $id, string $motivo, int $idUsuario): void
    {
        $motivo = trim($motivo);
        if ($motivo === '' || mb_strlen($motivo) > 200) {
            throw new DomainException('Indique el motivo de la anulación.');
        }
        // Al anular se libera el arqueo: el faltante vuelve a la lista de pendientes.
        $q = $this->c()->prepare("UPDATE personal_movimientos SET estado = 'ANULADO', motivo_anulacion = ?, id_arqueo = NULL WHERE id_movimiento = ? AND estado = 'ACTIVO'");
        $q->execute([$motivo, $id]);
        if ($q->rowCount() === 0) {
            throw new DomainException('El movimiento no existe o ya estaba anulado.');
        }
    }

    /** Faltantes de caja resueltos como «descontado» que aún no se registraron como descuento del grifero. */
    public function Faltantes_Pendientes(): array
    {
        return $this->c()->query("SELECT a.id_arqueo, ABS(a.diferencia) AS monto, a.nota_resolucion, rt.numero_documento, rt.fecha_reporte, rt.turno,
                                         p.id_personal, p.nombres, CONCAT(u.usu_nombre, ' ', u.usu_apellido) AS grifero
                                  FROM arqueos a
                                  INNER JOIN reportes_turno rt ON rt.id_reporte = a.id_reporte
                                  LEFT JOIN usuario u ON u.id_usuario = rt.id_usuario
                                  LEFT JOIN personal p ON p.id_usuario = rt.id_usuario
                                  WHERE a.resolucion = 'DESCONTADO' AND a.diferencia < 0
                                    AND NOT EXISTS (SELECT 1 FROM personal_movimientos m WHERE m.id_arqueo = a.id_arqueo)
                                  ORDER BY rt.fecha_reporte DESC")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function Descontar_Faltante(int $idArqueo, int $idUsuario): int
    {
        foreach ($this->Faltantes_Pendientes() as $f) {
            if ((int) $f['id_arqueo'] === $idArqueo) {
                if (!$f['id_personal']) {
                    throw new DomainException('El grifero de ese turno no tiene ficha de personal. Créela y vincúlela a su usuario.');
                }
                return $this->Registrar_Movimiento((int) $f['id_personal'], date('Y-m-d'), 'DESCUENTO', (float) $f['monto'],
                    'Faltante de caja del turno ' . $f['numero_documento'] . ' (' . date('d/m/Y', strtotime($f['fecha_reporte'])) . ')', $idUsuario, $idArqueo);
            }
        }
        throw new DomainException('Ese faltante no está pendiente de descontar.');
    }

    // ---------- Resumen del mes ----------

    /** Por persona: días programados, asistencia, turnos en el sistema, movimientos y neto estimado (referencial). */
    public function Resumen_Mes(string $mes): array
    {
        $mes = self::mes_valido($mes);
        $desde = $mes . '-01';
        $q = $this->c()->prepare("SELECT p.id_personal, p.nombres, p.cargo, p.sueldo,
                (SELECT COUNT(*) FROM personal_horario h WHERE h.id_personal = p.id_personal AND h.turno <> 'DESCANSO' AND h.fecha >= ? AND h.fecha < DATE_ADD(?, INTERVAL 1 MONTH)) AS programados,
                (SELECT COUNT(*) FROM personal_asistencia a WHERE a.id_personal = p.id_personal AND a.estado IN ('PRESENTE', 'TARDANZA') AND a.fecha >= ? AND a.fecha < DATE_ADD(?, INTERVAL 1 MONTH)) AS asistidos,
                (SELECT COUNT(*) FROM personal_asistencia a WHERE a.id_personal = p.id_personal AND a.estado = 'TARDANZA' AND a.fecha >= ? AND a.fecha < DATE_ADD(?, INTERVAL 1 MONTH)) AS tardanzas,
                (SELECT COUNT(*) FROM personal_asistencia a WHERE a.id_personal = p.id_personal AND a.estado = 'FALTA' AND a.fecha >= ? AND a.fecha < DATE_ADD(?, INTERVAL 1 MONTH)) AS faltas,
                (SELECT COUNT(*) FROM personal_asistencia a WHERE a.id_personal = p.id_personal AND a.estado = 'PERMISO' AND a.fecha >= ? AND a.fecha < DATE_ADD(?, INTERVAL 1 MONTH)) AS permisos,
                (SELECT COUNT(*) FROM reportes_turno rt WHERE p.id_usuario IS NOT NULL AND rt.id_usuario = p.id_usuario AND rt.fecha_reporte >= ? AND rt.fecha_reporte < DATE_ADD(?, INTERVAL 1 MONTH)) AS turnos_sistema,
                (SELECT COALESCE(SUM(m.monto), 0) FROM personal_movimientos m WHERE m.id_personal = p.id_personal AND m.estado = 'ACTIVO' AND m.tipo = 'ADELANTO' AND m.fecha >= ? AND m.fecha < DATE_ADD(?, INTERVAL 1 MONTH)) AS adelantos,
                (SELECT COALESCE(SUM(m.monto), 0) FROM personal_movimientos m WHERE m.id_personal = p.id_personal AND m.estado = 'ACTIVO' AND m.tipo = 'DESCUENTO' AND m.fecha >= ? AND m.fecha < DATE_ADD(?, INTERVAL 1 MONTH)) AS descuentos,
                (SELECT COALESCE(SUM(m.monto), 0) FROM personal_movimientos m WHERE m.id_personal = p.id_personal AND m.estado = 'ACTIVO' AND m.tipo = 'BONO' AND m.fecha >= ? AND m.fecha < DATE_ADD(?, INTERVAL 1 MONTH)) AS bonos
            FROM personal p WHERE p.activo = 1 ORDER BY p.nombres");
        $q->execute(array_fill(0, 18, $desde));
        $filas = $q->fetchAll(PDO::FETCH_ASSOC);
        foreach ($filas as &$f) {
            foreach (['adelantos', 'descuentos', 'bonos'] as $k) {
                $f[$k] = round((float) $f[$k], 2);
            }
            $f['neto'] = $f['sueldo'] !== null ? round((float) $f['sueldo'] - $f['adelantos'] - $f['descuentos'] + $f['bonos'], 2) : null;
        }
        return ['mes' => $mes, 'personas' => $filas];
    }
}
