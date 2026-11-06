<?php require_once __DIR__ . '/../_bootstrap.php'; ?>

<?php
    require '../../model/model_usuario.php';

    $MUSU= new Modelo_Usuario();//Instaciamos
    $consulta = $MUSU->listar_total_expedientes_observados();
    echo json_encode($consulta);

?>