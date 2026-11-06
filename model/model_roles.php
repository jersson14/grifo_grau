<?php
require_once 'model_conexion.php';

class Modelo_Roles extends conexionBD
{
    public const ADMIN = 'ADMINISTRADOR';

    public static function Catalogo(): array
    {
        return require dirname(__DIR__) . '/config/capacidades.php';
    }

    /** Capacidades de un rol. No incluye al administrador, que las tiene todas por definición. */
    public static function Capacidades_De(string $rol): array
    {
        $c = conexionBD::conexionPDO();
        $query = $c->prepare('SELECT capacidad FROM roles_capacidades WHERE rol = ?');
        $query->execute([$rol]);
        return $query->fetchAll(PDO::FETCH_COLUMN);
    }

    public static function Codigos(): array
    {
        $c = conexionBD::conexionPDO();
        return $c->query('SELECT codigo FROM roles ORDER BY sistema DESC, nombre')->fetchAll(PDO::FETCH_COLUMN);
    }

    public function Listar(): array
    {
        $c = conexionBD::conexionPDO();
        $roles = $c->query("SELECT r.codigo, r.nombre, r.descripcion, r.vista_inicio, r.sistema, COUNT(u.id_usuario) AS usuarios
                            FROM roles r LEFT JOIN usuario u ON u.usu_rol = r.codigo
                            GROUP BY r.codigo, r.nombre, r.descripcion, r.vista_inicio, r.sistema
                            ORDER BY r.sistema DESC, r.nombre")->fetchAll(PDO::FETCH_ASSOC);
        $capacidades = $c->query('SELECT rol, capacidad FROM roles_capacidades')->fetchAll(PDO::FETCH_ASSOC);
        foreach ($roles as &$rol) {
            $rol['capacidades'] = array_values(array_column(array_filter($capacidades, fn($x) => $x['rol'] === $rol['codigo']), 'capacidad'));
        }
        return $roles;
    }

    /** Crea o actualiza un rol. Del administrador solo se puede cambiar la pantalla de inicio. */
    public function Guardar(string $codigo, string $nombre, string $descripcion, array $capacidades, string $vistaInicio, int $idUsuario): void
    {
        $nombre = trim($nombre);
        if ($nombre === '') {
            throw new DomainException('Indique el nombre del rol.');
        }
        $c = conexionBD::conexionPDO();

        $vista = $c->prepare("SELECT COUNT(*) FROM menu_items WHERE vista = ?");
        $vista->execute([$vistaInicio]);
        if (!$vista->fetchColumn()) {
            throw new DomainException('Seleccione una pantalla de inicio válida.');
        }

        $validas = array_keys(self::Catalogo());
        $capacidades = array_values(array_unique(array_intersect($capacidades, $validas)));

        $c->beginTransaction();
        try {
            if ($codigo === '') {
                $codigo = self::Generar_Codigo($nombre);
                $existe = $c->prepare('SELECT COUNT(*) FROM roles WHERE codigo = ?');
                $existe->execute([$codigo]);
                if ($existe->fetchColumn()) {
                    throw new DomainException('Ya existe un rol con ese nombre.');
                }
                $c->prepare('INSERT INTO roles (codigo, nombre, descripcion, vista_inicio) VALUES (?, ?, ?, ?)')
                  ->execute([$codigo, mb_substr($nombre, 0, 60), mb_substr($descripcion, 0, 255), $vistaInicio]);
            } else {
                $existe = $c->prepare('SELECT COUNT(*) FROM roles WHERE codigo = ?');
                $existe->execute([$codigo]);
                if (!$existe->fetchColumn()) {
                    throw new DomainException('El rol no existe.');
                }
                if ($codigo === self::ADMIN) {
                    $c->prepare('UPDATE roles SET vista_inicio = ? WHERE codigo = ?')->execute([$vistaInicio, $codigo]);
                } else {
                    $c->prepare('UPDATE roles SET nombre = ?, descripcion = ?, vista_inicio = ? WHERE codigo = ?')
                      ->execute([mb_substr($nombre, 0, 60), mb_substr($descripcion, 0, 255), $vistaInicio, $codigo]);
                }
            }

            if ($codigo !== self::ADMIN) {
                $c->prepare('DELETE FROM roles_capacidades WHERE rol = ?')->execute([$codigo]);
                $insertar = $c->prepare('INSERT INTO roles_capacidades (rol, capacidad) VALUES (?, ?)');
                foreach ($capacidades as $capacidad) {
                    $insertar->execute([$codigo, $capacidad]);
                }
            }
            $c->prepare('INSERT INTO auditoria (id_usuario, usuario, accion, detalle, ip) VALUES (?, ?, ?, ?, ?)')
              ->execute([$idUsuario, $_SESSION['S_USU'] ?? null, 'ROL_GUARDADO', $codigo . ': ' . implode(', ', $capacidades), $_SERVER['REMOTE_ADDR'] ?? null]);
            $c->commit();
        } catch (Throwable $e) {
            $c->rollBack();
            throw $e;
        }
    }

    public function Eliminar(string $codigo): void
    {
        $c = conexionBD::conexionPDO();
        $query = $c->prepare('SELECT sistema FROM roles WHERE codigo = ?');
        $query->execute([$codigo]);
        $sistema = $query->fetchColumn();
        if ($sistema === false) {
            throw new DomainException('El rol no existe.');
        }
        if ($sistema) {
            throw new DomainException('Los roles Administrador y Grifero no se pueden eliminar.');
        }
        $usuarios = $c->prepare('SELECT COUNT(*) FROM usuario WHERE usu_rol = ?');
        $usuarios->execute([$codigo]);
        if ($usuarios->fetchColumn()) {
            throw new DomainException('Hay usuarios con este rol. Asígnales otro antes de eliminarlo.');
        }
        $c->prepare('DELETE FROM roles WHERE codigo = ?')->execute([$codigo]);
    }

    private static function Generar_Codigo(string $nombre): string
    {
        $texto = strtr(mb_strtoupper($nombre, 'UTF-8'), ['Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U', 'Ñ' => 'N', 'Ü' => 'U']);
        $codigo = trim(preg_replace('/[^A-Z0-9]+/', '_', $texto), '_');
        if ($codigo === '') {
            throw new DomainException('El nombre del rol debe contener letras o números.');
        }
        return substr($codigo, 0, 30);
    }
}
