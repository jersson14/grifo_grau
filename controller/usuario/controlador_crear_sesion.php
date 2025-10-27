<?php
    session_start();
    $idusuario = htmlspecialchars($_POST['idusuario'],ENT_QUOTES,'UTF-8');
    $usuario = htmlspecialchars($_POST['usuario'],ENT_QUOTES,'UTF-8');
    $rol = htmlspecialchars($_POST['rol'],ENT_QUOTES,'UTF-8');
    $solonombres = htmlspecialchars($_POST['solonombres'],ENT_QUOTES,'UTF-8');
    $nombres = htmlspecialchars($_POST['nombres'],ENT_QUOTES,'UTF-8');
    $foto = htmlspecialchars($_POST['foto'],ENT_QUOTES,'UTF-8');
    $foto_empresa = htmlspecialchars($_POST['foto_empresa'],ENT_QUOTES,'UTF-8');
    $razon = htmlspecialchars($_POST['razon'],ENT_QUOTES,'UTF-8');

    $_SESSION['S_ID']=$idusuario;
    $_SESSION['S_USU']=$usuario;
    $_SESSION['S_ROL']=$rol;
    $_SESSION['S_COMPLETOS']=$solonombres;
    $_SESSION['S_NOMBRE']=$nombres;
    $_SESSION['S_FOTO']=$foto;
    $_SESSION['S_FOTO_EMPRESA']=$foto_empresa;
    $_SESSION['S_RAZON']=$razon;



?>