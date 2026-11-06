<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_conexion.php';

header('Content-Type: application/json; charset=utf-8');

$c = conexionBD::conexionPDO();
$resumen = $c->query("SELECT COUNT(*) AS consultas, COALESCE(SUM(tokens_entrada), 0) AS tokens_entrada,
                             COALESCE(SUM(tokens_salida), 0) AS tokens_salida, COALESCE(SUM(costo_usd), 0) AS costo_usd
                      FROM ia_consultas WHERE creado_en >= DATE_FORMAT(CURDATE(), '%Y-%m-01')")->fetch(PDO::FETCH_ASSOC);
$ultimas = $c->query("SELECT i.creado_en, u.usu_usuario, i.pregunta, i.herramientas, i.estado, i.costo_usd
                      FROM ia_consultas i LEFT JOIN usuario u ON i.id_usuario = u.id_usuario
                      ORDER BY i.id_consulta DESC LIMIT 20")->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['resumen' => $resumen, 'ultimas' => $ultimas]);
