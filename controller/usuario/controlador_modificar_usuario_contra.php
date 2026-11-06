<?php
require_once __DIR__ . '/../_bootstrap.php';
require '../../model/model_usuario.php';

$MU  = new Modelo_Usuario();
$id  = (int) $_POST['id'];
$con = password_hash($_POST['con'], PASSWORD_DEFAULT, ['cost' => 12]);

echo $MU->Modificar_Usuario_Contra($id, $con);
