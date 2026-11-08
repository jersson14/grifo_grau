<?php
require_once __DIR__ . '/../_bootstrap.php';
require '../../model/model_usuario.php';

$MU  = new Modelo_Usuario();
$id  = (int) $_POST['id'];
$MU->Validar_Cambio_Rol($id, null);
$con = password_hash($_POST['con'], PASSWORD_DEFAULT, ['cost' => 12]);

$resultado = $MU->Modificar_Usuario_Contra($id, $con);
// Una clave asignada por otra persona se cambia al entrar. Quien cambia su propia clave aquí no queda marcado.
if ((int) $resultado === 1 && $id !== (int) $_SESSION['S_ID']) {
    $c = conexionBD::conexionPDO();
    $usuario = $c->prepare('SELECT usu_usuario FROM usuario WHERE id_usuario = ?');
    $usuario->execute([$id]);
    Modelo_Usuario::Marcar_Cambio_Clave((string) $usuario->fetchColumn());
}
echo $resultado;
