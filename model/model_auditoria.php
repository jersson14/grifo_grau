<?php
require_once 'model_conexion.php';

class Modelo_Auditoria extends conexionBD
{
    public function Listar(string $desde, string $hasta, string $usuario, string $texto): array
    {
        $c = conexionBD::conexionPDO();
        $sql = 'SELECT creado_en, usuario, accion, detalle, ip FROM auditoria WHERE creado_en >= ? AND creado_en < DATE_ADD(?, INTERVAL 1 DAY)';
        $params = [$desde, $hasta];
        if ($usuario !== '') {
            $sql .= ' AND usuario = ?';
            $params[] = $usuario;
        }
        if ($texto !== '') {
            $sql .= ' AND (accion LIKE ? OR detalle LIKE ?)';
            $params[] = "%$texto%";
            $params[] = "%$texto%";
        }
        $query = $c->prepare($sql . ' ORDER BY id_auditoria DESC LIMIT 500');
        $query->execute($params);
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function Usuarios(): array
    {
        $c = conexionBD::conexionPDO();
        return $c->query('SELECT DISTINCT usuario FROM auditoria WHERE usuario IS NOT NULL ORDER BY usuario')->fetchAll(PDO::FETCH_COLUMN);
    }
}
