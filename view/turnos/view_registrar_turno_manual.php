<script src="../js/console_turnos_manual.js?rev=<?php echo time(); ?>"></script>

<style>
.tabla-excel {
    font-size: 11px;
    border-collapse: collapse;
}
.tabla-excel th, .tabla-excel td {
    border: 1px solid #000;
    padding: 4px;
    text-align: center;
}
.tabla-excel input {
    width: 100%;
    border: none;
    text-align: center;
    padding: 2px;
    font-size: 11px;
    background-color: #fff3cd;
    font-weight: bold;
}
.tabla-excel input:focus {
    background-color: #ffffcc;
    outline: 2px solid #007bff;
}
.header-naranja {
    background-color: #ff9800;
    color: white;
    font-weight: bold;
}
.header-verde {
    background-color: #4caf50;
    color: white;
}
.header-gris {
    background-color: #6c757d;
    color: white;
}
.total-row {
    background-color: #ffc107;
    font-weight: bold;
}
.input-readonly {
    background-color: #f0f0f0;
    font-weight: bold;
}
</style>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-file-alt"></i> <b>REGISTRO DE TURNO</b></h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="../view/index.php">Inicio</a></li>
                    <li class="breadcrumb-item active">Registro de Turno</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        
        <!-- SIN TURNO ABIERTO -->
        <div id="sin_turno_abierto" style="display:none">
            <div class="alert alert-info">
                <h5><i class="icon fas fa-info-circle"></i> No hay un turno abierto en el sistema</h5>
                Debes abrir un turno para comenzar a registrar las ventas.
                <br><br>
                <button class="btn btn-primary" onclick="cargar_contenido('contenido_principal','turnos/view_abrir_turno.php')">
                    <i class="fas fa-plus-circle"></i> Abrir Turno
                </button>
            </div>
        </div>

        <!-- FORMULARIO REGISTRO TURNO -->
        <div id="formulario_registro_turno" style="display:none">
            <input type="hidden" id="txt_id_reporte">
            
            <!-- ENCABEZADO DEL REPORTE -->
            <div class="card">
                <div class="card-header" style="background: linear-gradient(135deg, #023D77, #0266C8); color:white">
                    <h3 class="card-title"><b>REPORTE DE VENTAS DIARIAS</b></h3>
                    <div class="card-tools">
                        <span class="badge badge-light" style="font-size:14px">DOC: <span id="info_numero_documento"></span></span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <p><strong>GRIFERO:</strong> <span id="info_grifero"></span></p>
                        </div>
                        <div class="col-md-3">
                            <p><strong>TURNO:</strong> <span id="info_turno" class="badge badge-info"></span></p>
                        </div>
                        <div class="col-md-3">
                            <p><strong>FECHA:</strong> <span id="info_fecha"></span></p>
                        </div>
                        <div class="col-md-3">
                            <p><strong>HORARIO:</strong> <span id="info_hora_inicio"></span> - <span id="info_hora_fin"></span></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TABLA ESTILO EXCEL -->
            <div class="card">
                <div class="card-body" style="overflow-x: auto;">
                    
                    <!-- INSTRUCCIÓN PARA EL USUARIO -->
                    <div class="alert alert-info" style="text-align:center; font-size:14px">
                        <i class="fas fa-info-circle"></i> <strong>INSTRUCCIÓN:</strong> Los campos con <span style="background-color:#fff3cd; padding:2px 8px; border:1px solid #856404; font-weight:bold">fondo amarillo</span> son los que debe llenar. Los totales se calculan automáticamente.
                    </div>
                    
                    <!-- SECCIÓN ISLA / MÁQUINA 1 -->
                    <h5 class="header-naranja" style="padding:8px; margin-bottom:10px">
                        <i class="fas fa-gas-pump"></i> ISLA - MÁQUINA 1
                    </h5>
                    <table class="tabla-excel table-sm" style="width:100%; margin-bottom:20px">
                        <thead>
                            <tr class="header-verde">
                                <th>FECHA</th>
                                <th>PRODUCTO</th>
                                <th>LECTURA ANTERIOR</th>
                                <th>LECTURA ACTUAL</th>
                                <th>GALONES VENDIDOS</th>
                                <th>PRECIO S/.</th>
                                <th>TOTAL EN SOLES</th>
                            </tr>
                        </thead>
                        <tbody id="tabla_maquina_1">
                        </tbody>
                        <tfoot>
                            <tr class="total-row">
                                <td colspan="6" style="text-align:right"><strong>TOTAL 1</strong></td>
                                <td><strong id="total_maquina_1">0.00</strong></td>
                            </tr>
                        </tfoot>
                    </table>

                    <!-- SECCIÓN ISLA / MÁQUINA 2 -->
                    <h5 class="header-naranja" style="padding:8px; margin-bottom:10px">
                        <i class="fas fa-gas-pump"></i> ISLA - MÁQUINA 2
                    </h5>
                    <table class="tabla-excel table-sm" style="width:100%; margin-bottom:20px">
                        <thead>
                            <tr class="header-verde">
                                <th>FECHA</th>
                                <th>PRODUCTO</th>
                                <th>LECTURA ANTERIOR</th>
                                <th>LECTURA ACTUAL</th>
                                <th>GALONES VENDIDOS</th>
                                <th>PRECIO S/.</th>
                                <th>TOTAL EN SOLES</th>
                            </tr>
                        </thead>
                        <tbody id="tabla_maquina_2">
                        </tbody>
                        <tfoot>
                            <tr class="total-row">
                                <td colspan="6" style="text-align:right"><strong>TOTAL 2</strong></td>
                                <td><strong id="total_maquina_2">0.00</strong></td>
                            </tr>
                        </tfoot>
                    </table>

                    <!-- TOTALES GENERALES (MÁQUINA 1 + MÁQUINA 2) -->
                    <div class="alert alert-warning" style="font-size:18px; font-weight:bold; text-align:center">
                        TOTALES (MÁQUINA 1 + MÁQUINA 2): <span id="total_general">S/. 0.00</span>
                    </div>

                    <hr>

                    <!-- SECCIÓN DE PAGOS -->
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px">
                        <h5 class="header-gris" style="padding:8px; margin:0; flex:1">
                            <i class="fas fa-money-bill-wave"></i> MÉTODOS DE PAGO
                        </h5>
                        <button class="btn btn-success btn-sm" onclick="Agregar_Fila_Pago()" style="margin-left:10px">
                            <i class="fas fa-plus"></i> Agregar Fila
                        </button>
                    </div>
                    <table class="tabla-excel table-sm" style="width:100%; margin-bottom:20px">
                        <thead>
                            <tr class="header-gris">
                                <th style="width:9%">YAPE</th>
                                <th style="width:13%">COD. OPERACIÓN</th>
                                <th style="width:9%">BCP</th>
                                <th style="width:13%">COD. OPERACIÓN</th>
                                <th style="width:9%">VISA</th>
                                <th style="width:13%">COD. OPERACIÓN</th>
                                <th style="width:9%">DESCUENTOS</th>
                                <th style="width:9%">EFECTIVO</th>
                                <th style="width:11%">OTROS GASTOS</th>
                                <th style="width:5%">ACCIÓN</th>
                            </tr>
                        </thead>
                        <tbody id="tabla_pagos">
                        </tbody>
                        <tfoot>
                            <tr class="total-row">
                                <td><strong id="total_yape">0.00</strong></td>
                                <td></td>
                                <td><strong id="total_bcp">0.00</strong></td>
                                <td></td>
                                <td><strong id="total_visa">0.00</strong></td>
                                <td></td>
                                <td><strong id="total_descuentos">0.00</strong></td>
                                <td><strong id="total_efectivo">0.00</strong></td>
                                <td><strong id="total_otros_gastos">0.00</strong></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>

                    <hr>

                    <!-- SECCIÓN DE CRÉDITOS -->
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px">
                        <h5 class="header-gris" style="padding:8px; margin:0; flex:1">
                            <i class="fas fa-credit-card"></i> VENTAS A CRÉDITO
                        </h5>
                        <button class="btn btn-success btn-sm" onclick="Agregar_Fila_Credito()" style="margin-left:10px">
                            <i class="fas fa-plus"></i> Agregar Fila
                        </button>
                    </div>
                    <table class="tabla-excel table-sm" style="width:100%; margin-bottom:20px">
                        <thead>
                            <tr class="header-gris">
                                <th style="width:20%">MONTO DE CRÉDITO</th>
                                <th style="width:40%">NOMBRE DEL CLIENTE</th>
                                <th style="width:25%">N° DE VALE</th>
                                <th style="width:15%">ACCIÓN</th>
                            </tr>
                        </thead>
                        <tbody id="tabla_creditos">
                        </tbody>
                        <tfoot>
                            <tr class="total-row">
                                <td><strong id="total_creditos">0.00</strong></td>
                                <td colspan="3"></td>
                            </tr>
                        </tfoot>
                    </table>

                    <hr>

                    <!-- RESUMEN FINAL -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card card-primary">
                                <div class="card-header">
                                    <h3 class="card-title"><i class="fas fa-chart-bar"></i> RESUMEN POR COMBUSTIBLE</h3>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm">
                                        <tr>
                                            <td><strong>DIESEL:</strong></td>
                                            <td class="text-right"><span id="resumen_diesel">S/. 0.00</span></td>
                                        </tr>
                                        <tr>
                                            <td><strong>GASOLINA REGULAR:</strong></td>
                                            <td class="text-right"><span id="resumen_regular">S/. 0.00</span></td>
                                        </tr>
                                        <tr>
                                            <td><strong>GASOLINA PREMIUM:</strong></td>
                                            <td class="text-right"><span id="resumen_premium">S/. 0.00</span></td>
                                        </tr>
                                        <tr style="background-color:#007bff; color:white; font-weight:bold">
                                            <td><strong>TOTAL EN SOLES:</strong></td>
                                            <td class="text-right"><span id="resumen_total_soles">S/. 0.00</span></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card card-success">
                                <div class="card-header">
                                    <h3 class="card-title"><i class="fas fa-calculator"></i> CUADRE DE CAJA</h3>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm">
                                        <tr>
                                            <td><strong>Total Ventas:</strong></td>
                                            <td class="text-right"><span id="cuadre_total_ventas">S/. 0.00</span></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Total Pagos (Yape+BCP+Visa):</strong></td>
                                            <td class="text-right"><span id="cuadre_total_pagos">S/. 0.00</span></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Total Créditos:</strong></td>
                                            <td class="text-right"><span id="cuadre_total_creditos">S/. 0.00</span></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Descuentos:</strong></td>
                                            <td class="text-right"><span id="cuadre_descuentos">S/. 0.00</span></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Otros Gastos:</strong></td>
                                            <td class="text-right"><span id="cuadre_otros_gastos">S/. 0.00</span></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Efectivo:</strong></td>
                                            <td class="text-right"><span id="cuadre_efectivo">S/. 0.00</span></td>
                                        </tr>
                                        <tr style="background-color:#28a745; color:white; font-size:16px">
                                            <td><strong>FALTANTE/SOBRANTE:</strong></td>
                                            <td class="text-right"><strong><span id="cuadre_diferencia">S/. 0.00</span></strong></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- BOTÓN CERRAR TURNO -->
                    <div class="row">
                        <div class="col-md-12 text-center">
                            <button class="btn btn-danger btn-lg" style="font-size:20px; padding:15px 40px" onclick="Cerrar_Turno_Manual()">
                                <i class="fas fa-check-circle"></i> CERRAR TURNO Y GUARDAR TODO
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    Cargar_Turno_Manual();
});
</script>
