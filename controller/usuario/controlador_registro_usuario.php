<?php
    require '../../model/model_usuario.php';
    $MU = new Modelo_Usuario();
    //DATOS DE DOCENTE//
    $dni = strtoupper(htmlspecialchars($_POST['dni'],ENT_QUOTES,'UTF-8'));
    $nombre = strtoupper(htmlspecialchars($_POST['nombre'],ENT_QUOTES,'UTF-8'));
    $apelli = strtoupper(htmlspecialchars($_POST['apelli'],ENT_QUOTES,'UTF-8'));
    $correo = htmlspecialchars($_POST['correo'],ENT_QUOTES,'UTF-8');
    $tele = strtoupper(htmlspecialchars($_POST['tele'],ENT_QUOTES,'UTF-8'));
    $dire = strtoupper(htmlspecialchars($_POST['dire'],ENT_QUOTES,'UTF-8'));
    $nombrefoto = htmlspecialchars($_POST['nombrefoto'],ENT_QUOTES,'UTF-8');

    //DATOS DEL USUARIO //
    $usu = htmlspecialchars($_POST['usu'],ENT_QUOTES,'UTF-8');
    $contra = password_hash(htmlspecialchars($_POST['contra'],ENT_QUOTES,'UTF-8'),PASSWORD_DEFAULT,['cost'=>12]);
    $rol = htmlspecialchars($_POST['rol'],ENT_QUOTES,'UTF-8');


    $ruta='controller/usuario/fotos/'.$nombrefoto;
    $consulta = $MU->Registrar_Usuario($dni,$nombre,$apelli,$correo,$tele,$dire,$ruta,$usu,$contra,$rol);
    if ($consulta) {
        if($nombrefoto!=""){
            move_uploaded_file($_FILES['foto']['tmp_name'],"fotos/".$nombrefoto);
        }
        echo $consulta;
    }
?>