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
expediente.representación,
expediente.doc_ruc,
expediente.doc_empresa,
expediente.id_servicio,
expediente.id_cliente,
expediente.folios,
expediente.estado AS ESTADO_EXPE,
expediente.created_at AS expediente_creado,
expediente.updated_at AS expediente_actualizado,
clientes.nro_documento,
CONCAT_WS(' ', clientes.nombres, clientes.apellidos) AS CLIENTE,
clientes.tipo_documento,
clientes.nombres,
clientes.apellidos,
CONCAT_WS(' ', usuario.usu_nombre, usuario.usu_apellido) AS USUARIO,
servicios.nombre AS Servicio,
clientes.celular,
clientes.telefono,
clientes.direccion,
clientes.email,
distritos.id_provincia,
provincias.id_region,

 empresa.logo,
    empresa.nombre AS nombre_empresa,
    empresa.email,
    empresa.telefono,
    empresa.direccion
FROM expediente inner join clientes
on expediente.id_cliente=clientes.id_cliente inner join usuario
on expediente.id_usu = usuario.id_usuario inner join servicios
on expediente.id_servicio = servicios.id_servicios inner join pagos
on expediente.id_expediente=pagos.id_expediente inner join distritos
on clientes.id_distrito = distritos.id_distritos inner join provincias
on distritos.id_provincia=provincias.id_provincia inner join regiones
on provincias.id_region=regiones.id_region inner join empresa
on usuario.id_empresa = empresa.id_empresa
WHERE expediente.id_expediente = '$id'";

$resultado = $mysqli->query($query);

$html = '';

if ($resultado->num_rows > 0) {
    $first_row = $resultado->fetch_assoc();

    // Datos
    $logo = $first_row['logo'];
    $nombre_empresa = $first_row['nombre_empresa'];
    $telefono = $first_row['telefono'];
    $email = $first_row['email'];
    $direccion = $first_row['direccion'];    $fecha_expediente = mb_strtolower(strftime('%d de %B del %Y', strtotime($first_row['expediente_creado'])), 'UTF-8');

    $html = '
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            color: #333;
            margin: 20px;
        }
        .ticket {
            max-width: 650px;
            margin: auto;
            border: 1px solid #0154A0;
            padding: 20px;
            border-radius: 8px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            color: #0154A0;
            font-size: 24px;
            margin-bottom: 5px;
        }
        .header p {
            font-size: 14px;
            color: #555;
        }
        hr {
            border: 1px solid #0154A0;
        }
        .section {
            margin-top: 20px;
        }
        .section h2 {
            color: #0154A0;
            font-size: 18px;
            margin-bottom: 10px;
            border-bottom: 1px solid #0154A0;
            padding-bottom: 5px;
        }
        .info p {
            margin: 5px 0;
            font-size: 15px;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 13px;
            color: #777;
        }
    </style>

    <div class="ticket">
        <div class="header">
                    <img src="../../../'.$logo.'" alt="Logo Empresa">

            <h1>TICKET DE SEGUIMIENTO</h1>
        </div>


        <div class="section">
            <h2>Datos del Expediente</h2>
            <div class="info">
    <p style="text-align: center; color: #003366; font-weight: bold; font-size: 18px;">
        NRO. EXPEDIENTE: '.mb_strtoupper($first_row['nro_expediente'], 'UTF-8').'
    </p>                <p><strong>Fecha de Creación:</strong> '.$fecha_expediente.'</p>
                <p><strong>Estado del Expediente:</strong> '.mb_strtoupper($first_row['ESTADO_EXPE'], 'UTF-8').'</p>
            </div>
        </div>

        <div class="section">
            <h2>Datos del Cliente</h2>
            <div class="info">
                <p><strong>Nombre:</strong> '.mb_strtoupper($first_row['CLIENTE'], 'UTF-8').'</p>
                <p><strong>Tipo Documento:</strong> '.mb_strtoupper($first_row['tipo_documento'], 'UTF-8').'</p>
                <p><strong>Nro Documento:</strong> '.mb_strtoupper($first_row['nro_documento'], 'UTF-8').'</p>
                <p><strong>Celular:</strong> '.$first_row['celular'].'</p>
                <p><strong>Teléfono:</strong> '.$first_row['telefono'].'</p>
                <p><strong>Dirección:</strong> '.mb_strtoupper($first_row['direccion'], 'UTF-8').'</p>
                <p><strong>Email:</strong> '.$first_row['email'].'</p>
            </div>
        </div>

        <div class="section">
            <h2>Datos del Servicio</h2>
            <div class="info">
                <p><strong>Servicio Contratado:</strong> '.mb_strtoupper($first_row['Servicio'], 'UTF-8').'</p>
                <p><strong>Responsable:</strong> '.mb_strtoupper($first_row['USUARIO'], 'UTF-8').'</p>
            </div>
        </div>

         <div class="footer">
            <p><strong>'.$nombre_empresa.'</strong></p>
            <p>Teléfono: '.$telefono.'</p>
            <p>Email: '.$email.'</p>
            <p>Dirección: '.$direccion.'</p>
            <p>Abancay - Apurímac - Perú</p>
            <p style="margin-top: 10px; font-style: italic;">
                (Documento generado automáticamente para fines de seguimiento.)
            </p>
        </div>


    </div>';
}

$mpdf = new \Mpdf\Mpdf(['mode' => 'UTF-8','format' => [140, 297]]); // tamaño ticket
$mpdf->WriteHTML($html);
$mpdf->Output('ticket_seguimiento.pdf', 'I');
?>
