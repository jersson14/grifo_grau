<?php
require_once __DIR__ . '/model_conexion.php';
require_once __DIR__ . '/model_usuario.php';
require_once __DIR__ . '/model_productos.php';

/**
 * Primera puesta en marcha de un grifo nuevo: datos de la empresa, el administrador, los combustibles
 * y los surtidores. Solo funciona mientras no existe ningún usuario; después queda cerrada.
 */
class Modelo_Instalacion
{
    /** Sugerencia para el formulario: los combustibles más comunes en un grifo del Perú. */
    public const COMBUSTIBLES_SUGERIDOS = [
        ['nombre' => 'DIESEL B5', 'tipo' => 'DIESEL', 'precio' => ''],
        ['nombre' => 'REGULAR 90', 'tipo' => 'REGULAR', 'precio' => ''],
        ['nombre' => 'PREMIUM 95', 'tipo' => 'PREMIUM', 'precio' => ''],
    ];

    public static function Pendiente(): bool
    {
        $c = conexionBD::conexionPDO();
        if ($c === null) {
            return false;
        }
        return (int) $c->query('SELECT COUNT(*) FROM usuario')->fetchColumn() === 0;
    }

    /** Valida todo y lo guarda en una sola transacción. Lanza DomainException con el primer error. */
    public function Instalar(array $d): void
    {
        $empresa = $this->Validar_Empresa($d['empresa'] ?? []);
        $admin = $this->Validar_Administrador($d['administrador'] ?? []);
        $combustibles = $this->Validar_Combustibles($d['combustibles'] ?? []);
        $surtidores = $this->Validar_Surtidores($d['surtidores'] ?? [], count($combustibles));

        $c = conexionBD::conexionPDO();
        // Un solo instalador a la vez: dos pestañas enviando a la vez no crean dos administradores.
        if ((int) $c->query("SELECT GET_LOCK('grifo_instalacion', 10)")->fetchColumn() !== 1) {
            throw new DomainException('La instalación está ocupada. Inténtelo de nuevo en unos segundos.');
        }
        try {
            if ((int) $c->query('SELECT COUNT(*) FROM usuario')->fetchColumn() > 0) {
                throw new DomainException('El sistema ya está instalado. Inicie sesión.');
            }
            $c->beginTransaction();
            $c->exec('DELETE FROM empresa');
            $c->prepare("INSERT INTO empresa (nombre, codigo, telefono, direccion, email, logo) VALUES (?, ?, ?, ?, ?, 'img/grau.png')")
              ->execute([$empresa['nombre'], $empresa['ruc'] ?: null, $empresa['telefono'] ?: null, $empresa['direccion'] ?: null, $empresa['email'] ?: null]);

            $c->prepare("INSERT INTO usuario (usu_nombre, usu_apellido, usu_usuario, usu_contrasenia, usu_rol, usu_foto, usu_estatus, debe_cambiar_clave)
                         VALUES (?, ?, ?, ?, 'ADMINISTRADOR', 'img/avatar.png', 'ACTIVO', 0)")
              ->execute([$admin['nombres'], $admin['apellidos'], $admin['usuario'], password_hash($admin['clave'], PASSWORD_DEFAULT)]);

            $ids = [];
            $producto = $c->prepare("INSERT INTO productos (nombre, tipo, precio_actual, estado, created_at) VALUES (?, ?, ?, 'ACTIVO', NOW())");
            foreach ($combustibles as $p) {
                $producto->execute([$p['nombre'], $p['tipo'], $p['precio']]);
                $ids[] = (int) $c->lastInsertId();
            }
            $surtidor = $c->prepare("INSERT INTO surtidores (numero_maquina, codigo, id_producto, lectura_actual, estado, created_at) VALUES (?, ?, ?, ?, 'ACTIVO', NOW())");
            foreach ($surtidores as $s) {
                $surtidor->execute([$s['maquina'], $s['codigo'], $ids[$s['combustible']], $s['lectura']]);
            }
            $c->prepare("INSERT INTO auditoria (id_usuario, usuario, accion, detalle, ip) VALUES (NULL, ?, 'INSTALACION', ?, ?)")
              ->execute([$admin['usuario'], count($combustibles) . ' combustible(s), ' . count($surtidores) . ' surtidor(es)', $_SERVER['REMOTE_ADDR'] ?? null]);
            $c->commit();
        } catch (Throwable $e) {
            if ($c->inTransaction()) {
                $c->rollBack();
            }
            throw $e;
        } finally {
            $c->query("SELECT RELEASE_LOCK('grifo_instalacion')");
        }
    }

    private static function texto($v, int $max): string
    {
        return mb_substr(trim((string) $v), 0, $max);
    }

    private function Validar_Empresa(array $e): array
    {
        $r = ['nombre' => self::texto($e['nombre'] ?? '', 200), 'ruc' => preg_replace('/\D/', '', (string) ($e['ruc'] ?? '')),
              'telefono' => self::texto($e['telefono'] ?? '', 30), 'direccion' => self::texto($e['direccion'] ?? '', 255), 'email' => self::texto($e['email'] ?? '', 150)];
        if ($r['nombre'] === '') {
            throw new DomainException('Escriba el nombre del grifo.');
        }
        if ($r['ruc'] !== '' && !preg_match('/^(10|15|17|20)\d{9}$/', $r['ruc'])) {
            throw new DomainException('El RUC tiene 11 dígitos y empieza con 10, 15, 17 o 20.');
        }
        if ($r['email'] !== '' && !filter_var($r['email'], FILTER_VALIDATE_EMAIL)) {
            throw new DomainException('Revise el correo del grifo.');
        }
        return $r;
    }

    private function Validar_Administrador(array $a): array
    {
        $r = ['nombres' => mb_strtoupper(self::texto($a['nombres'] ?? '', 80)), 'apellidos' => mb_strtoupper(self::texto($a['apellidos'] ?? '', 80)),
              'usuario' => mb_strtolower(trim((string) ($a['usuario'] ?? ''))), 'clave' => (string) ($a['clave'] ?? '')];
        if ($r['nombres'] === '') {
            throw new DomainException('Escriba el nombre del administrador.');
        }
        if (!preg_match('/^[a-z0-9._-]{3,30}$/', $r['usuario'])) {
            throw new DomainException('El usuario lleva de 3 a 30 letras, números, punto, guion o guion bajo, sin espacios.');
        }
        $error = Modelo_Usuario::Validar_Clave_Nueva($r['clave'], $r['usuario']);
        if ($error !== null) {
            throw new DomainException(str_replace('La contraseña nueva', 'La contraseña', $error));
        }
        if (!hash_equals($r['clave'], (string) ($a['repetir'] ?? ''))) {
            throw new DomainException('Las contraseñas no coinciden.');
        }
        return $r;
    }

    private function Validar_Combustibles(array $lista): array
    {
        $r = [];
        $nombres = [];
        foreach (array_values($lista) as $i => $p) {
            $nombre = mb_strtoupper(self::texto($p['nombre'] ?? '', 100));
            $tipo = (string) ($p['tipo'] ?? '');
            $precio = (string) ($p['precio'] ?? '');
            $n = $i + 1;
            if ($nombre === '') {
                throw new DomainException("Combustible $n: escriba el nombre.");
            }
            if (isset($nombres[$nombre])) {
                throw new DomainException("El combustible $nombre está repetido.");
            }
            if (!in_array($tipo, Modelo_Productos::TIPOS, true)) {
                throw new DomainException("Combustible $n: elija el tipo.");
            }
            if (!is_numeric($precio) || (float) $precio <= 0 || (float) $precio > 100) {
                throw new DomainException("Combustible $nombre: el precio por galón debe ser mayor que cero.");
            }
            $nombres[$nombre] = true;
            $r[] = ['nombre' => $nombre, 'tipo' => $tipo, 'precio' => round((float) $precio, 3)];
        }
        if (!$r) {
            throw new DomainException('Agregue al menos un combustible.');
        }
        return $r;
    }

    private function Validar_Surtidores(array $lista, int $combustibles): array
    {
        $r = [];
        $vistos = [];
        foreach (array_values($lista) as $i => $s) {
            $n = $i + 1;
            $maquina = filter_var($s['maquina'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 50]]);
            $codigo = mb_strtoupper(trim((string) ($s['codigo'] ?? '')));
            $comb = filter_var($s['combustible'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => $combustibles - 1]]);
            $lectura = (string) ($s['lectura'] ?? '');
            if ($maquina === false) {
                throw new DomainException("Surtidor $n: la máquina es un número del 1 al 50.");
            }
            if (!preg_match('/^[A-Z0-9-]{1,10}$/', $codigo)) {
                throw new DomainException("Surtidor $n: el código lleva hasta 10 letras o números, como BS1 o R2.");
            }
            if (isset($vistos[$maquina . '|' . $codigo])) {
                throw new DomainException("El surtidor $codigo está repetido en la máquina $maquina.");
            }
            if ($comb === false) {
                throw new DomainException("Surtidor $codigo: elija su combustible.");
            }
            if (!is_numeric($lectura) || (float) $lectura < 0) {
                throw new DomainException("Surtidor $codigo: escriba la lectura actual del contómetro.");
            }
            $vistos[$maquina . '|' . $codigo] = true;
            $r[] = ['maquina' => $maquina, 'codigo' => $codigo, 'combustible' => $comb, 'lectura' => round((float) $lectura, 3)];
        }
        if (!$r) {
            throw new DomainException('Agregue al menos un surtidor.');
        }
        return $r;
    }
}
