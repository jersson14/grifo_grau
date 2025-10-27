<?php
    require '../../model/model_clientes.php';
    $MCL = new Modelo_Clientes();//Instaciamos
    $id = strtoupper(htmlspecialchars($_POST['id'],ENT_QUOTES,'UTF-8'));
    $tipo = strtoupper(htmlspecialchars($_POST['tipo'],ENT_QUOTES,'UTF-8'));
    $nro = strtoupper(htmlspecialchars($_POST['nro'],ENT_QUOTES,'UTF-8'));
    $nombre = strtoupper(htmlspecialchars($_POST['nombre'],ENT_QUOTES,'UTF-8'));
    $apellido = strtoupper(htmlspecialchars($_POST['apellido'],ENT_QUOTES,'UTF-8'));
    $celular = strtoupper(htmlspecialchars($_POST['celular'],ENT_QUOTES,'UTF-8'));
    $telefono = strtoupper(htmlspecialchars($_POST['telefono'],ENT_QUOTES,'UTF-8'));
    $direccion = strtoupper(htmlspecialchars($_POST['direccion'],ENT_QUOTES,'UTF-8'));
    $email = strtoupper(htmlspecialchars($_POST['email'],ENT_QUOTES,'UTF-8'));
    $iddistri = strtoupper(htmlspecialchars($_POST['iddistri'],ENT_QUOTES,'UTF-8'));
    $ober = strtoupper(htmlspecialchars($_POST['ober'],ENT_QUOTES,'UTF-8'));

    $consulta = $MCL->Modificar_Clientes($id,$tipo,$nro,$nombre,$apellido,$celular,$telefono,$direccion,$email,$iddistri,$ober);
    echo $consulta;



?>