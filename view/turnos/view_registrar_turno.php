<?php require_once __DIR__ . '/../../controller/_bootstrap.php'; ?>
<script src="../js/console_turnos.js?rev=<?php echo time(); ?>"></script>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-clipboard-check"></i> <b>REGISTRAR TURNO</b></h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="../view/index.php">Inicio</a></li>
                    <li class="breadcrumb-item active">Registrar Turno</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        
        <!-- FORMULARIO REGISTRAR TURNO -->
        <div id="formulario_registrar_turno">
            <input type="hidden" id="txt_id_reporte">
            
            <!-- PASO 1: INFORMACIÓN DEL TURNO -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title g-titulo-paso"><span class="g-paso" aria-hidden="true">1</span> <span class="sr-only">Paso 1: </span>Información del turno</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 form-group">
                                    <label>Número de Documento</label>
                                    <input type="text" class="form-control" id="txt_numero_documento" readonly style="background-color:#f0f0f0; font-weight:bold">
                                </div>
                                <div class="col-md-3 form-group">
                                    <label>Fecha del Turno <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="txt_fecha_turno">
                                </div>
                                <div class="col-md-3 form-group">
                                    <label>Tipo de Turno <span class="text-danger">*</span></label>
                                    <select class="form-control" id="txt_tipo_turno">
                                        <option value="">-- Seleccione --</option>
                                        <option value="DIA">DÍA (07:00 - 19:00)</option>
                                        <option value="NOCHE">NOCHE (19:00 - 07:00)</option>
                                    </select>
                                </div>
                                <div class="col-md-3 form-group">
                                    <label>Grifero <span class="text-danger">*</span></label>
                                    <select class="form-control" id="txt_grifero">
                                        <option value="">-- Seleccione Grifero --</option>
                                    </select>
                                </div>
                                <div class="col-md-3 form-group">
                                    <label>Hora Inicio <span class="text-danger">*</span></label>
                                    <input type="time" class="form-control" id="txt_hora_inicio">
                                </div>
                                <div class="col-md-3 form-group">
                                    <label>Hora Fin <span class="text-danger">*</span></label>
                                    <input type="time" class="form-control" id="txt_hora_fin">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PASO 2: LECTURAS DE SURTIDORES -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-success">
                        <div class="card-header" data-card-widget="collapse">
                            <h3 class="card-title g-titulo-paso"><span class="g-paso" aria-hidden="true">2</span> <span class="sr-only">Paso 2: </span>Lecturas de surtidores</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            
                            <!-- Una sección por máquina, generada desde los surtidores activos -->
                            <div id="maquinas_lecturas"></div>

                            <!-- RESUMEN POR COMBUSTIBLE -->
                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <h5 style="background-color:#6c757d; color:white; padding:10px; border-radius:5px;">
                                        <i class="fas fa-chart-bar"></i> RESUMEN POR COMBUSTIBLE
                                    </h5>
                                </div>
                                <div class="col-md-4">
                                    <div class="info-box bg-dark">
                                        <span class="info-box-icon"><i class="fas fa-gas-pump"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">DIESEL</span>
                                            <span class="info-box-number" id="total_diesel">S/. 0.00</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="info-box bg-info">
                                        <span class="info-box-icon"><i class="fas fa-gas-pump"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">REGULAR</span>
                                            <span class="info-box-number" id="total_regular">S/. 0.00</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="info-box bg-warning">
                                        <span class="info-box-icon"><i class="fas fa-gas-pump"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">PREMIUM</span>
                                            <span class="info-box-number" id="total_premium">S/. 0.00</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="info-box bg-success">
                                        <span class="info-box-icon"><i class="fas fa-dollar-sign"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">TOTAL VENTAS</span>
                                            <span class="info-box-number" id="total_ventas">S/. 0.00</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PASO 3: MÉTODOS DE PAGO -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-warning">
                        <div class="card-header" data-card-widget="collapse">
                            <h3 class="card-title g-titulo-paso"><span class="g-paso" aria-hidden="true">3</span> <span class="sr-only">Paso 3: </span>Métodos de pago <small>(opcional)</small></h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <button class="btn btn-success btn-sm mb-2" onclick="Agregar_Fila_Pago_Registro()">
                                <i class="fas fa-plus"></i> Agregar Fila
                            </button>
                            <div class="table-responsive">
                            <table id="tabla_pagos_registro" class="table table-sm table-bordered table-hover">
                                <thead style="background-color:#ffc107; color:black">
                                    <tr>
                                        <th width="30%">Tipo de Pago</th>
                                        <th width="25%">Código Operación</th>
                                        <th width="20%">Monto (S/.)</th>
                                        <th width="25%">Observaciones</th>
                                        <th width="10%">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody_pagos_registro">
                                    <!-- Filas se agregarán dinámicamente -->
                                </tbody>
                            </table>
                            </div>
                            <div class="alert alert-info mt-2">
                                <i class="fas fa-info-circle"></i> <strong>Tip:</strong> Escribe directamente en las celdas. Los datos se guardarán al registrar el turno.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PASO 4: VENTAS A CRÉDITO -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-danger">
                        <div class="card-header" data-card-widget="collapse">
                            <h3 class="card-title g-titulo-paso"><span class="g-paso" aria-hidden="true">4</span> <span class="sr-only">Paso 4: </span>Ventas a crédito <small>(opcional)</small></h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <button class="btn btn-success btn-sm mb-2" onclick="Agregar_Fila_Credito_Registro()">
                                <i class="fas fa-plus"></i> Agregar Fila
                            </button>
                            <div class="table-responsive">
                            <table id="tabla_creditos_registro" class="table table-sm table-bordered table-hover">
                                <thead style="background-color:#dc3545; color:white">
                                    <tr>
                                        <th width="28%">Cliente</th>
                                        <th width="18%">N° Vale</th>
                                        <th width="12%">Placa</th>
                                        <th width="17%">Monto (S/.)</th>
                                        <th width="15%">Fecha Vencimiento</th>
                                        <th width="10%">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody_creditos_registro">
                                    <!-- Filas se agregarán dinámicamente -->
                                </tbody>
                            </table>
                            </div>
                            <div class="alert alert-info mt-2">
                                <i class="fas fa-info-circle"></i> <strong>Tip:</strong> Escribe directamente en las celdas. Los datos se guardarán al registrar el turno.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PASO 5: OTROS CONCEPTOS Y CUADRE -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-secondary">
                        <div class="card-header" data-card-widget="collapse">
                            <h3 class="card-title g-titulo-paso"><span class="g-paso" aria-hidden="true">5</span> <span class="sr-only">Paso 5: </span>Otros conceptos y cuadre de caja</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header" style="background-color:#6c757d; color:white">
                            <h3 class="card-title"><i class="fas fa-minus-circle"></i> Otros Conceptos</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Descuentos (S/.)</label>
                                <input type="number" step="0.01" class="form-control" id="txt_descuentos" value="0" placeholder="0.00">
                            </div>
                            <div class="form-group">
                                <label>Otros Gastos (S/.)</label>
                                <input type="number" step="0.01" class="form-control" id="txt_otros_gastos" value="0" placeholder="0.00">
                            </div>
                            <div class="form-group">
                                <label>Efectivo (S/.)</label>
                                <input type="number" step="0.01" class="form-control" id="txt_monto_efectivo" value="0" placeholder="0.00">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CUADRE DE CAJA -->
                <div class="col-md-6">
                    <div class="card card-success">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-calculator"></i> Cuadre de Caja</h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>Total Ventas:</strong></td>
                                    <td class="text-right"><span id="cuadre_total_ventas">S/. 0.00</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Total Pagos:</strong></td>
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
                                    <td class="text-right"><span id="cuadre_monto_efectivo">S/. 0.00</span></td>
                                </tr>
                                <tr style="background-color:#28a745; color:white; font-size:18px">
                                    <td><strong>FALTANTE/SOBRANTE:</strong></td>
                                    <td class="text-right"><strong><span id="cuadre_faltante">S/. 0.00</span></strong></td>
                                </tr>
                            </table>
                            </div>
                        </div>
                        </div>
                    </div>
                </div>
            </div>
                        </div>
                    </div>
                </div>
            </div>

            <p class="text-muted text-center small mb-3">Al registrar el turno se guardan todos los datos y se actualizan las lecturas de los surtidores.</p>

            <!-- Barra de cuadre: siempre a la vista mientras se llena el turno -->
            <div class="g-barra-cuadre" role="region" aria-label="Cuadre del turno">
                <div class="g-barra-dato">
                    <span class="g-barra-etiqueta">Total ventas</span>
                    <span class="g-cifra" id="barra_total">S/ 0.00</span>
                </div>
                <div class="g-barra-dato">
                    <span class="g-barra-etiqueta">Cuadre</span>
                    <span class="g-estado-cuadre" id="barra_estado" data-estado="vacio" aria-live="polite">Faltan lecturas</span>
                </div>
                <button type="button" class="btn btn-primary btn-lg g-barra-boton" onclick="Registrar_Turno_Completo()"><i class="fas fa-save" aria-hidden="true"></i> Registrar turno</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    Cargar_Numero_Documento();
    Cargar_Lecturas_Para_Registro();
    Cargar_Griferos();
    
    // Fecha local de hoy: toISOString() usa UTC y desde las 19:00 en Lima daría la fecha de mañana.
    $('#txt_fecha_turno').val(G.hoy());

    // Las horas por defecto según el tipo de turno se ponen al final de esta vista.

    // Agregar 3 filas iniciales de pagos y créditos
    // Una fila vacía de cada una: se agregan más con su botón.
    Agregar_Fila_Pago_Registro();
    Agregar_Fila_Credito_Registro();
});
</script>
<script>
// La barra repite el total y el cuadre que calcula la pantalla, con una forma distinta para cada estado.
(function () {
  var total = document.getElementById('cuadre_total_ventas');
  var faltante = document.getElementById('cuadre_faltante');
  var barraTotal = document.getElementById('barra_total');
  var barraEstado = document.getElementById('barra_estado');
  if (!total || !faltante || !barraTotal || !barraEstado) { return; }
  function icono(nombre) { var i = document.createElement('i'); i.className = 'fas fa-' + nombre; i.setAttribute('aria-hidden', 'true'); return i; }
  function reflejar() {
    var monto = total.textContent.replace('S/.', 'S/').trim();
    barraTotal.textContent = monto;
    var texto = faltante.textContent;
    var cifra = (texto.match(/\d[\d.,]*/) || ['0'])[0];
    barraEstado.textContent = '';
    if (!(parseFloat(monto.replace(/[^\d.]/g, '')) > 0)) {
      barraEstado.dataset.estado = 'vacio';
      barraEstado.textContent = 'Faltan lecturas';
    } else if (texto.indexOf('FALTANTE') !== -1) {
      barraEstado.dataset.estado = 'falta';
      barraEstado.append(icono('arrow-down'), ' Falta S/ ' + cifra);
    } else if (texto.indexOf('SOBRANTE') !== -1) {
      barraEstado.dataset.estado = 'sobra';
      barraEstado.append(icono('plus'), ' Sobra S/ ' + cifra);
    } else {
      barraEstado.dataset.estado = 'cuadra';
      barraEstado.append(icono('check'), ' Cuadra');
    }
  }
  var observador = new MutationObserver(reflejar);
  [total, faltante].forEach(function (nodo) { observador.observe(nodo, { childList: true, characterData: true, subtree: true }); });
  reflejar();
})();
</script>
<script>
// Valores por defecto: el tipo de turno según la hora actual y sus horas al elegirlo.
// Las horas se rellenan solo si están vacías o las puso el sistema, nunca sobre lo escrito a mano.
(function () {
  var HORAS = { DIA: ['07:00', '19:00'], NOCHE: ['19:00', '07:00'] };
  var tipo = document.getElementById('txt_tipo_turno');
  var inicio = document.getElementById('txt_hora_inicio');
  var fin = document.getElementById('txt_hora_fin');
  if (!tipo || !inicio || !fin) { return; }

  function poner_horas() {
    var horas = HORAS[tipo.value];
    if (!horas) { return; }
    [[inicio, horas[0]], [fin, horas[1]]].forEach(function (par) {
      if (!par[0].value || par[0].dataset.automatico === '1') {
        par[0].value = par[1];
        par[0].dataset.automatico = '1';
      }
    });
  }
  [inicio, fin].forEach(function (campo) {
    campo.addEventListener('input', function () { campo.dataset.automatico = '0'; });
  });
  tipo.addEventListener('change', poner_horas);

  if (!tipo.value) {
    var hora = new Date().getHours();
    tipo.value = (hora >= 7 && hora < 19) ? 'DIA' : 'NOCHE';
  }
  poner_horas();
})();
</script>
