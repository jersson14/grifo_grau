<?php
setlocale(LC_TIME, 'es_ES.UTF-8');
date_default_timezone_set('America/Lima');

require_once __DIR__ . '/../vendor/autoload.php';
require_once '../conexion.php';
$mysqli->set_charset("utf8");

$id = $mysqli->real_escape_string($_GET['id']);

// Consulta SQL
$query = "SELECT
    expediente.id_expediente,
    expediente.nro_expediente,
    clientes.nro_documento,
    CONCAT_WS(' ', clientes.nombres, clientes.apellidos) AS CLIENTE,
    clientes.tipo_documento,
    servicios.nombre AS Servicio,
    pagos.monto_total AS monto_total_pago,
    pagos.saldo_pendiente,
    ingresos.monto_pagado,
    ingresos.igv,
    ingresos.monto_total,
    ingresos.observacion,
    ingresos.created_at,
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
INNER JOIN ingresos ON pagos.id_pago = ingresos.id_pagos
INNER JOIN empresa ON usuario.id_empresa = empresa.id_empresa
WHERE ingresos.id_ingresos = '$id'";

$resultado = $mysqli->query($query);

$html = '';

if ($resultado->num_rows > 0) {
    $first_row = $resultado->fetch_assoc();

    // Empresa
    $logo = $first_row['logo'];
    $nombre_empresa = $first_row['nombre_empresa'];
    $telefono = $first_row['telefono'];
    $email = $first_row['email'];
    $direccion = $first_row['direccion'];

    // Datos de pago
    $saldo_pendiente = $first_row['saldo_pendiente'];

    $html = '
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            color: #333;
            margin: 20px;
        }
        .boleta {
            max-width: 750px;
            margin: auto;
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 8px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header img {
            width: 140px;
        }
        .titulo {
            font-size: 22px;
            margin-top: 10px;
            text-decoration: underline;
            color: #0154A0;
        }
        hr {
            border: 1px solid #ccc;
        }
        .info p {
            margin: 4px 0;
            font-size: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 14px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: center;
        }
        th {
            background-color: #0154A0;
            color: white;
        }
        .totales {
            margin-top: 20px;
            text-align: right;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 13px;
            color: #777;
        }
        .footer p {
            margin: 3px;
        }
    </style>

    <div class="boleta">
        <div class="header">
            <img src="../../../'.$logo.'" alt="Logo Empresa">
            <div class="titulo"><b>RECIBO DE PAGO</b></div>
        </div>

        <hr>
<div class="info" style="text-align: left;">
    <p style="text-align: center; color: #003366; font-weight: bold; font-size: 18px;">
        NRO. EXPEDIENTE: '.mb_strtoupper($first_row['nro_expediente'], 'UTF-8').'
    </p>

    <p><strong>Tipo Documento:</strong> '.mb_strtoupper($first_row['tipo_documento'], 'UTF-8').'</p>
    <p><strong>Nro Documento:</strong> '.mb_strtoupper($first_row['nro_documento'], 'UTF-8').'</p>
    <p><strong>Cliente:</strong> '.mb_strtoupper($first_row['CLIENTE'], 'UTF-8').'</p>
    <p><strong>Servicio:</strong> '.mb_strtoupper($first_row['Servicio'], 'UTF-8').'</p>
</div>


        <table>
            <thead>
                <tr>
                    <th>Fecha de pago</th>
                    <th>Subtotal</th>
                    <th>IGV</th>
                    <th>Total</th>
                    <th>Observación</th>
                </tr>
            </thead>
            <tbody>';

    $resultado->data_seek(0);
    $total_pagado = 0;
    while ($row = $resultado->fetch_assoc()) {
        $fecha_pago = mb_strtolower(strftime('%d de %B del %Y', strtotime($row['created_at'])), 'UTF-8');
        $html .= '
            <tr>
                <td>'.$fecha_pago.'</td>
                <td>S/. '.number_format($row['monto_pagado'], 2).'</td>
                <td>S/. '.number_format($row['igv'], 2).'</td>
                <td>S/. '.number_format($row['monto_total'], 2).'</td>
                <td>'.$row['observacion'].'</td>
            </tr>';
        $total_pagado += $row['monto_total'];
    }

    $html .= '
            </tbody>
        </table>

        <div class="totales">
            <p><strong>Monto Total Pagado: </strong> S/. '.number_format($total_pagado, 2).'</p>
            <p><strong>Saldo Pendiente: </strong> S/. '.number_format($saldo_pendiente, 2).'</p>
        </div>

        <div class="footer">
            <p><strong>'.$nombre_empresa.'</strong></p>
            <p>Teléfono: '.$telefono.'</p>
            <p>Email: '.$email.'</p>
            <p>Dirección: '.$direccion.'</p>
            <p>Abancay - Apurímac - Perú</p>
            <p style="margin-top: 10px; font-style: italic;">
                (Esta boleta de pago puede ser reemplazada por una original en la oficina de la empresa.)
            </p>
        </div>
    </div>';

}

$mpdf = new \Mpdf\Mpdf(['mode' => 'UTF-8','format' => [130,300]]);
$mpdf->WriteHTML($html);
$mpdf->Output('boleta_de_pago.pdf', 'I');
?>
