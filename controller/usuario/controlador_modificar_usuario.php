<?php
    require '../../model/model_usuario.php';
    $MU = new Modelo_Usuario();
    $id = strtoupper(htmlspecialchars($_POST['id'],ENT_QUOTES,'UTF-8'));
    $dni = strtoupper(htmlspecialchars($_POST['dni'],ENT_QUOTES,'UTF-8'));
    $nombre = strtoupper(htmlspecialchars($_POST['nombre'],ENT_QUOTES,'UTF-8'));
    $apelli = strtoupper(htmlspecialchars($_POST['apelli'],ENT_QUOTES,'UTF-8'));
    $correo = htmlspecialchars($_POST['correo'],ENT_QUOTES,'UTF-8');
    $tele = strtoupper(htmlspecialchars($_POST['tele'],ENT_QUOTES,'UTF-8'));
    $dire = strtoupper(htmlspecialchars($_POST['dire'],ENT_QUOTES,'UTF-8'));
    $fotoactual = htmlspecialchars($_POST['fotoactual'],ENT_QUOTES,'UTF-8');
    $nombrefoto = htmlspecialchars($_POST['nombrefoto'],ENT_QUOTES,'UTF-8');

    $usu = htmlspecialchars($_POST['usu'],ENT_QUOTES,'UTF-8');
    $rol = strtoupper(htmlspecialchars($_POST['rol'],ENT_QUOTES,'UTF-8'));
    if (empty($nombrefoto)) {
        $ruta = $fotoactual;
    } else {
        if ($nombrefoto == 'controller/usuario/fotos/') {
            $ruta = $nombrefoto; // Simplemente usa el nombre sin modificarlo
        } else {
            $ruta = 'controller/usuario/fotos/' . $nombrefoto; // Construye la ruta completa para la nueva foto
        }
    }
    
    if (!empty($nombrefoto)) {
        if ($nombrefoto != 'controller/usuario/fotos/' && move_uploaded_file($_FILES['foto']['tmp_name'], "fotos/" . $nombrefoto)) {
            $ruta = 'controller/usuario/fotos/' . $nombrefoto;
        } else {
            $ruta = $fotoactual;
        }
    }
    
    $consulta = $MU->Modificar_Usuario($id,$dni, $nombre, $apelli, $correo, $tele, $dire, $ruta,$usu,$rol);
    echo $consulta;
    
    if ($consulta == 1) {
        if (!empty($nombrefoto) && $nombrefoto != 'controller/usuario/fotos/') {
            if (move_uploaded_file($_FILES['foto']['tmp_name'], "fotos/" . $nombrefoto)) {
                unlink('../../' . $fotoactual);
            }
        }
    }
?>