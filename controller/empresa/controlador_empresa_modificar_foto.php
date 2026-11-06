<?php
require_once __DIR__ . '/../_bootstrap.php';
require '../../model/model_empresa.php';

$ME = new Modelo_Empresa();
$id = (int) $_POST['id'];

$nombreFoto = guardar_imagen_subida($_FILES['foto'] ?? [], __DIR__ . '/FOTOS');
if ($nombreFoto === null) {
    echo 0;
    exit;
}

echo $ME->Modificar_foto_empresa($id, 'controller/empresa/FOTOS/' . $nombreFoto);
