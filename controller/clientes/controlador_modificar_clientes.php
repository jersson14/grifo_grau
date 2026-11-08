<?php
require_once __DIR__ . '/../_bootstrap.php';
    require '../../model/model_clientes.php';
    $MCL = new Modelo_Clientes();//Instaciamos
    $id = strtoupper(trim((string) ($_POST['id'])));
    $tipo = strtoupper(trim((string) ($_POST['tipo'])));
    $nro = strtoupper(trim((string) ($_POST['nro'])));
    $nombre = strtoupper(trim((string) ($_POST['nombre'])));
    $apellido = strtoupper(trim((string) ($_POST['apellido'])));
    $celular = strtoupper(trim((string) ($_POST['celular'])));
    $telefono = strtoupper(trim((string) ($_POST['telefono'])));
    $direccion = strtoupper(trim((string) ($_POST['direccion'])));
    $email = strtoupper(trim((string) ($_POST['email'])));
    $iddistri = strtoupper(trim((string) ($_POST['iddistri'])));
    $ober = strtoupper(trim((string) ($_POST['ober'])));

    $consulta = $MCL->Modificar_Clientes($id,$tipo,$nro,$nombre,$apellido,$celular,$telefono,$direccion,$email,$iddistri,$ober);
    echo $consulta;



?>