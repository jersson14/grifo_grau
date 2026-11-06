<?php
require_once 'model_conexion.php';

class Modelo_Menu extends conexionBD
{
    /**
     * Menú del usuario en sesión. Un enlace aparece si está visible, su rol lo admite, tiene permiso
     * y su módulo está activo. Un grupo con un solo enlace se muestra como ese enlace.
     */
    public function Para_Sesion(): array
    {
        $c = conexionBD::conexionPDO();
        $filas = $c->query('SELECT * FROM menu_items WHERE visible = 1 ORDER BY orden, id_item')->fetchAll(PDO::FETCH_ASSOC);
        $rol = (string) $_SESSION['S_ROL'];

        $admite = function (array $f) use ($rol) {
            return $f['roles'] === null || $f['roles'] === '' || in_array($rol, explode(',', $f['roles']), true);
        };
        $hijos = [];
        foreach ($filas as $f) {
            if ($admite($f)) {
                $hijos[(int) $f['id_padre']][] = $f;
            }
        }

        $menu = [];
        foreach ($hijos[0] ?? [] as $seccion) {
            $items = [];
            foreach ($hijos[(int) $seccion['id_item']] ?? [] as $item) {
                if ($item['tipo'] === 'enlace') {
                    if (ruta_accesible('view/' . $item['vista'])) {
                        $items[] = self::Enlace($item);
                    }
                    continue;
                }
                $enlaces = [];
                foreach ($hijos[(int) $item['id_item']] ?? [] as $hijo) {
                    if (ruta_accesible('view/' . $hijo['vista'])) {
                        $enlaces[] = self::Enlace($hijo);
                    }
                }
                if (count($enlaces) === 1) {
                    $items[] = $enlaces[0];
                } elseif ($enlaces) {
                    $items[] = ['titulo' => $item['titulo'], 'icono' => $item['icono'], 'vista' => null, 'hijos' => $enlaces];
                }
            }
            if ($items) {
                $menu[] = ['titulo' => $seccion['titulo'], 'items' => $items];
            }
        }
        return $menu;
    }

    private static function Enlace(array $f): array
    {
        return ['titulo' => $f['titulo'], 'icono' => $f['icono'], 'vista' => $f['vista'], 'hijos' => []];
    }

    /** Pantalla inicial del usuario: la de su rol si puede abrirla; si no, la primera de su menú. */
    public function Vista_Inicio(array $menu): ?string
    {
        $c = conexionBD::conexionPDO();
        $query = $c->prepare('SELECT vista_inicio FROM roles WHERE codigo = ?');
        $query->execute([$_SESSION['S_ROL']]);
        $vista = $query->fetchColumn();
        if ($vista && ruta_accesible('view/' . $vista)) {
            return $vista;
        }
        foreach ($menu as $seccion) {
            foreach ($seccion['items'] as $item) {
                if ($item['vista']) {
                    return $item['vista'];
                }
                if ($item['hijos']) {
                    return $item['hijos'][0]['vista'];
                }
            }
        }
        return null;
    }

    public function Listar_Para_Editor(): array
    {
        $c = conexionBD::conexionPDO();
        return $c->query('SELECT * FROM menu_items ORDER BY orden, id_item')->fetchAll(PDO::FETCH_ASSOC);
    }

    public function Vistas(): array
    {
        $c = conexionBD::conexionPDO();
        return $c->query("SELECT titulo, vista FROM menu_items WHERE vista IS NOT NULL ORDER BY titulo")->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Lo editable: título, ícono, visibilidad y roles. La pantalla que abre un enlace no se puede cambiar. */
    public function Guardar(int $id, string $titulo, string $icono, bool $visible, array $roles): void
    {
        $titulo = trim($titulo);
        if ($titulo === '') {
            throw new DomainException('Indique un título.');
        }
        if ($icono !== '' && !preg_match('/^(fas|far|fab) fa-[a-z0-9-]+$/', $icono)) {
            throw new DomainException('El ícono debe tener un formato como «fas fa-gas-pump».');
        }
        $c = conexionBD::conexionPDO();
        $query = $c->prepare('SELECT protegido FROM menu_items WHERE id_item = ?');
        $query->execute([$id]);
        $protegido = $query->fetchColumn();
        if ($protegido === false) {
            throw new DomainException('El elemento no existe.');
        }
        if ($protegido && (!$visible || $roles)) {
            throw new DomainException('Este elemento no se puede ocultar ni limitar: es necesario para administrar el sistema.');
        }

        require_once __DIR__ . '/model_roles.php';
        $roles = array_values(array_intersect($roles, Modelo_Roles::Codigos()));
        $c->prepare('UPDATE menu_items SET titulo = ?, icono = ?, visible = ?, roles = ? WHERE id_item = ?')
          ->execute([mb_substr($titulo, 0, 60), $icono === '' ? null : $icono, $visible ? 1 : 0, $roles ? implode(',', $roles) : null, $id]);
    }

    /** Intercambia la posición con el elemento vecino del mismo nivel. */
    public function Mover(int $id, string $direccion): void
    {
        if (!in_array($direccion, ['ARRIBA', 'ABAJO'], true)) {
            throw new DomainException('Dirección no válida.');
        }
        $c = conexionBD::conexionPDO();
        $c->beginTransaction();
        try {
            $query = $c->prepare('SELECT id_padre FROM menu_items WHERE id_item = ?');
            $query->execute([$id]);
            $padre = $query->fetch(PDO::FETCH_ASSOC);
            if (!$padre) {
                throw new DomainException('El elemento no existe.');
            }
            $hermanos = $c->prepare('SELECT id_item FROM menu_items WHERE id_padre <=> ? ORDER BY orden, id_item FOR UPDATE');
            $hermanos->execute([$padre['id_padre']]);
            $lista = array_map('intval', $hermanos->fetchAll(PDO::FETCH_COLUMN));

            $posicion = array_search($id, $lista, true);
            $destino = $posicion + ($direccion === 'ARRIBA' ? -1 : 1);
            if (isset($lista[$destino])) {
                [$lista[$posicion], $lista[$destino]] = [$lista[$destino], $lista[$posicion]];
                $orden = $c->prepare('UPDATE menu_items SET orden = ? WHERE id_item = ?');
                foreach ($lista as $i => $item) {
                    $orden->execute([($i + 1) * 10, $item]);
                }
            }
            $c->commit();
        } catch (Throwable $e) {
            $c->rollBack();
            throw $e;
        }
    }
}
