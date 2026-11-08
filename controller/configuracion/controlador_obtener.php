<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_configuracion.php';
require_once '../../model/model_alertas.php';
require_once '../../model/model_roles.php';
require_once '../../model/model_inicio.php';

header('Content-Type: application/json; charset=utf-8');

$config = new Modelo_Configuracion();
echo json_encode(['valores' => $config->Listar_Para_Formulario(), 'modulos' => $config->Listar_Modulos(), 'alertas' => Modelo_Alertas::Opciones(), 'bloques' => Modelo_Inicio::Opciones(), 'roles' => Modelo_Roles::Codigos()]);
