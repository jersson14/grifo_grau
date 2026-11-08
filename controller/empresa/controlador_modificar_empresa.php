<?php
require_once __DIR__ . '/../_bootstrap.php';
    require '../../model/model_empresa.php';
    $ME = new Modelo_Empresa();//Instaciamos
    $id = strtoupper(trim((string) ($_POST['id'])));
    $nom = strtoupper(trim((string) ($_POST['nom'])));
    $email = strtoupper(trim((string) ($_POST['email'])));
    $cod = strtoupper(trim((string) ($_POST['cod'])));
    $tel = strtoupper(trim((string) ($_POST['tel'])));
    $dir = strtoupper(trim((string) ($_POST['dir'])));
    

    $consulta = $ME->Modificar_Empresa($id,$nom,$email,$cod,$tel,$dir);
    echo $consulta;



?>