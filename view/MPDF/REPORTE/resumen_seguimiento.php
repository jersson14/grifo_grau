<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once '../conexion.php';
$mysqli->set_charset("utf8");

// Parámetros de entrada
$nro_documento = $_GET['nro_documento'];
$nro_expediente = $_GET['nro_expediente'];

// Consulta 1: Datos del Cliente y Expediente
$queryExpediente = "SELECT
    expediente.id_expediente,
    expediente.nro_expediente,
    expediente.representación,
    expediente.doc_ruc,
    expediente.doc_empresa,
    expediente.id_servicio,
    expediente.id_cliente,
    expediente.folios,
    expediente.estado AS ESTADO_EXPE,
    expediente.created_at,
    DATE_FORMAT(expediente.created_at, '%d-%m-%Y - %H:%i:%s') AS fecha_formateada,
    expediente.updated_at,
    DATE_FORMAT(expediente.updated_at, '%d-%m-%Y - %H:%i:%s') AS fecha_formateada2,
    expediente.tiempo_transcurrido,
    expediente.id_usu,
    clientes.nro_documento,
    CONCAT_WS(' ', clientes.nombres, clientes.apellidos) AS CLIENTE,
    clientes.tipo_documento,
    clientes.nombres,
    clientes.apellidos,
    CONCAT_WS(' ', usuario.usu_nombre, usuario.usu_apellido) AS USUARIO,
    servicios.nombre AS servicio_nombre,
    servicios.costo,
    clientes.id_cliente,
    clientes.celular,
    clientes.telefono,
    clientes.direccion,
    clientes.email,
    clientes.observacion,
    clientes.created_at AS cliente_created_at,
    clientes.updated_at AS cliente_updated_at,
    clientes.id_distrito,
    pagos.motivo_anula,
    pagos.id_pago,
    pagos.id_expediente AS pago_id_expediente,
    pagos.monto_total,
    pagos.saldro_cobrado,
    pagos.saldo_pendiente,
    pagos.estado,
    pagos.motivo_anula,
    pagos.created_at AS pago_created_at,
    DATE_FORMAT(pagos.created_at, '%d-%m-%Y - %H:%i:%s') AS fecha_formateada_pagos,
    pagos.updated_at AS pago_updated_at,
    DATE_FORMAT(pagos.updated_at, '%d-%m-%Y - %H:%i:%s') AS fecha_formateada_pagos2,
    distritos.id_provincia,
    provincias.id_region,
    regiones.Nombre AS REGION,
    provincias.nombre AS PROVINCIA,
    distritos.nombre AS DISTRITO,
 empresa.logo,
    empresa.nombre AS nombre_empresa,
    empresa.email,
    empresa.telefono,
    empresa.direccion
FROM expediente 
INNER JOIN clientes ON expediente.id_cliente = clientes.id_cliente 
INNER JOIN usuario ON expediente.id_usu = usuario.id_usuario 
INNER JOIN servicios ON expediente.id_servicio = servicios.id_servicios 
INNER JOIN pagos ON expediente.id_expediente = pagos.id_expediente 
INNER JOIN distritos ON clientes.id_distrito = distritos.id_distritos 
INNER JOIN provincias ON distritos.id_provincia = provincias.id_provincia 
INNER JOIN regiones ON provincias.id_region = regiones.id_region
INNER JOIN empresa ON usuario.id_empresa = empresa.id_empresa
WHERE clientes.nro_documento = '$nro_documento' AND expediente.nro_expediente = '$nro_expediente'";

$resultExpediente = $mysqli->query($queryExpediente);
$expedienteData = $resultExpediente->fetch_assoc();
$id_expediente = $expedienteData['id_expediente'];
$id_pago = $expedienteData['id_pago'];
$logo = $expedienteData['logo'];

// Consulta 2: Documentos Adjuntos
$queryDocumentos = "SELECT 
    requisito_expediente.id_requisito_expe,
    requisito_expediente.id_requisito,
    requisito_expediente.archivo,
    requisito_expediente.estado,
    requisito_expediente.created_at,
    DATE_FORMAT(requisito_expediente.created_at, '%d-%m-%Y - %H:%i:%s') AS fecha_formateada,
    requisito_expediente.updated_at,
    DATE_FORMAT(requisito_expediente.updated_at, '%d-%m-%Y - %H:%i:%s') AS fecha_formateada2,
    requisito_expediente.id_expediente,
    requisito_expediente.id_usu,
    requisitos.requisito AS REQUISITO
FROM requisito_expediente 
INNER JOIN requisitos ON requisito_expediente.id_requisito = requisitos.id_requisitos
WHERE requisito_expediente.id_expediente = '$id_expediente'";

$resultDocumentos = $mysqli->query($queryDocumentos);

// Consulta 3: Pagos Realizados
$queryPagos = "SELECT 
    ingresos.id_ingresos,
    ingresos.id_pagos,
    ingresos.id_indicador,
    ingresos.monto_pagado,
    ingresos.igv,
    ingresos.monto_total,
    ingresos.observacion,
    ingresos.estado,
    ingresos.id_usu,
    ingresos.fecha_anulacion,
    DATE_FORMAT(ingresos.fecha_anulacion, '%d-%m-%Y - %H:%i:%s') AS ANULACION,
    ingresos.motivo_anu,
    ingresos.created_at,
    DATE_FORMAT(ingresos.created_at, '%d-%m-%Y - %H:%i:%s') AS fecha_formateada,
    ingresos.updated_at,
    DATE_FORMAT(ingresos.updated_at, '%d-%m-%Y - %H:%i:%s') AS fecha_formateada2,
    CONCAT_WS(' ', usuario.usu_nombre, usuario.usu_apellido) AS USUARIO
FROM ingresos 
INNER JOIN usuario ON ingresos.id_usu = usuario.id_usuario
WHERE ingresos.id_pagos = '$id_pago'";

$resultPagos = $mysqli->query($queryPagos);

// Generar HTML para el PDF
$html = '
<style>
    body {
        font-family: "Arial", sans-serif;
        font-size: 11px;
        color: #333;
        margin: 0;
        padding: 0;
    }
    .logo {
        text-align: center;
        margin-top: 8px;
    }
    .logo img {
        width: 40px;
            background-color: red;

    }
    .header {
        text-align: center;
        background-color: #0154A0;
        color: white;
        padding: 12px 0;
        border-radius: 5px;
        margin-top: 10px;
    }
    .header h1 {
        margin: 0;
        font-size: 20px;
        letter-spacing: 1px;
    }
    .section {
        margin-top: 25px;
    }
    h2 {
        background-color: #E8F0FE;
        color: #0154A0;
        padding: 8px;
        border-left: 6px solid #0154A0;
        font-size: 15px;
        margin-bottom: 10px;
    }
    p {
        margin: 4px 0;
        font-size: 11px;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }
    th {
        background-color: #0154A0;
        color: white;
        padding: 6px;
        border: 1px solid #ccc;
        font-size: 12px;
        text-align: left;
    }
    td {
        border: 1px solid #ccc;
        padding: 6px;
        font-size: 11px;
    }
    .footer {
        text-align: center;
        font-size: 10px;
        margin-top: 40px;
        color: #999;
    }
</style>

<div class="logo">
            <img src="../../../'.$logo.'" alt="Logo Empresa" style="width:450px;">
</div>

<div class="header">
    <h1>REPORTE RESUMEN DE SEGUIMIENTO</h1>
</div>

<div class="section">
    <h2>Datos del Cliente y Expediente</h2>
    <p><strong>Cliente:</strong> ' . $expedienteData['CLIENTE'] . '</p>
    <p><strong>Documento:</strong> ' . $expedienteData['tipo_documento'] . ' - ' . $expedienteData['nro_documento'] . '</p>
    <p><strong>Expediente:</strong> ' . $expedienteData['nro_expediente'] . '</p>
    <p><strong>Servicio:</strong> ' . $expedienteData['servicio_nombre'] . '</p>
    <p><strong>Estado:</strong> ' . $expedienteData['ESTADO_EXPE'] . '</p>
    <p><strong>Fecha de Creación:</strong> ' . $expedienteData['fecha_formateada'] . '</p>
    <p><strong>Región:</strong> ' . $expedienteData['REGION'] . '</p>
    <p><strong>Provincia:</strong> ' . $expedienteData['PROVINCIA'] . '</p>
    <p><strong>Distrito:</strong> ' . $expedienteData['DISTRITO'] . '</p>
</div>

<div class="section">
    <h2>Documentos Adjuntos</h2>
    <table>
        <thead>
            <tr>
                <th>Documento</th>
                <th>Estado</th>
                <th>Fecha de Presentación</th>
            </tr>
        </thead>
        <tbody>';
while ($doc = $resultDocumentos->fetch_assoc()) {
    $html .= '
            <tr>
                <td>' . $doc['REQUISITO'] . '</td>
                <td>' . $doc['estado'] . '</td>
                <td>' . $doc['fecha_formateada'] . '</td>
            </tr>';
}

$html .= '
        </tbody>
    </table>
</div>

<div class="section">
    <h2>Pagos Realizados</h2>
    <table>
        <thead>
            <tr>
                <th>Fecha de Pago</th>
                <th>Subtotal</th>
                <th>IGV</th>
                <th>Total</th>
                <th>Observación</th>
            </tr>
        </thead>
        <tbody>';

while ($pago = $resultPagos->fetch_assoc()) {
    $html .= '
            <tr>
                <td>' . $pago['fecha_formateada'] . '</td>
                <td>S/ ' . number_format($pago['monto_pagado'], 2) . '</td>
                <td>S/ ' . number_format($pago['igv'], 2) . '</td>
                <td>S/ ' . number_format($pago['monto_total'], 2) . '</td>
                <td>' . $pago['observacion'] . '</td>
            </tr>';
}
$html .= '
</tbody>
</table>
</div>

<!-- Tabla de resumen -->
<div style="margin-top:20px; width:300px;">
    <h2>Totales</h2>
    <table border="1" cellpadding="6" cellspacing="0" width="100%">
        <tr>
            <th>Saldo Cobrado</th>
            <td>S/ ' . number_format($expedienteData['saldro_cobrado'], 2) . '</td>
        </tr>
        <tr>
            <th>Saldo Pendiente</th>
            <td>S/ ' . number_format($expedienteData['saldo_pendiente'], 2) . '</td>
        </tr>
        <tr>
            <th>Monto Total</th>
            <td>S/ ' . number_format($expedienteData['monto_total'], 2) . '</td>
        </tr>
    </table>
</div>

<div class="footer">
    <p>Reporte generado automáticamente por el sistema.</p>
</div>';


// Generar PDF con mPDF
$mpdf = new \Mpdf\Mpdf(['mode' => 'UTF-8', 'format' => 'A4']);
$mpdf->WriteHTML($html);
$mpdf->Output('ResumenSeguimiento.pdf', 'I');
?>
