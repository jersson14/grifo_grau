<?php require_once __DIR__ . '/../../controller/_bootstrap.php'; ?>
<script src="../js/console_analisis.js?rev=<?php echo time(); ?>"></script>

<div class="content-header">
  <div class="container-fluid"><h1 class="m-0"><i class="fas fa-chart-pie"></i> <b>ANÁLISIS Y RENTABILIDAD</b></h1></div>
</div>

<div class="content">
  <div class="container-fluid">
    <form id="filtro_analisis" class="d-flex flex-wrap align-items-end mb-3" style="gap: .5rem;">
      <div><label for="ana_desde" class="d-block mb-1">Desde</label><input type="date" class="form-control form-control-sm" id="ana_desde" required></div>
      <div><label for="ana_hasta" class="d-block mb-1">Hasta</label><input type="date" class="form-control form-control-sm" id="ana_hasta" required></div>
      <div class="g-segmento" role="group" aria-label="Periodos rápidos">
        <label><input type="radio" name="ana_rapido" value="mes" checked><span>Este mes</span></label>
        <label><input type="radio" name="ana_rapido" value="anterior"><span>Mes anterior</span></label>
        <label><input type="radio" name="ana_rapido" value="30"><span>30 días</span></label>
      </div>
      <button class="btn btn-primary btn-sm" type="submit">Consultar</button>
      <div class="ml-auto d-flex flex-wrap" style="gap: .5rem;">
        <a class="btn btn-outline-secondary btn-sm" id="ana_excel" href="#"><i class="fas fa-file-excel" aria-hidden="true"></i> Exportar a Excel</a>
        <a class="btn btn-outline-secondary btn-sm" id="ana_pdf" href="#" target="_blank" rel="noopener"><i class="fas fa-file-pdf" aria-hidden="true"></i> Informe en PDF</a>
      </div>
    </form>

    <div class="card card-tabs">
      <div class="card-header p-0 pt-1 border-bottom-0">
        <ul class="nav nav-tabs flex-nowrap tw-overflow-x-auto" role="tablist" id="ana_pestanas">
          <li class="nav-item"><a class="nav-link active text-nowrap" data-toggle="pill" data-seccion="rentabilidad" href="#tab_ana_rentabilidad" role="tab">Rentabilidad</a></li>
          <li class="nav-item"><a class="nav-link text-nowrap" data-toggle="pill" data-seccion="griferos" href="#tab_ana_griferos" role="tab">Ranking de griferos</a></li>
          <li class="nav-item"><a class="nav-link text-nowrap" data-toggle="pill" data-seccion="morosidad" href="#tab_ana_morosidad" role="tab">Morosidad</a></li>
          <li class="nav-item"><a class="nav-link text-nowrap" data-toggle="pill" data-seccion="horario" href="#tab_ana_horario" role="tab">Ventas por horario</a></li>
        </ul>
      </div>
      <div class="card-body">
        <div class="tab-content">

          <div class="tab-pane fade show active" id="tab_ana_rentabilidad" role="tabpanel">
            <div class="row" id="ren_tarjetas"></div>
            <div class="table-responsive">
              <table class="table table-sm">
                <thead><tr><th>Producto</th><th class="text-right">Galones</th><th class="text-right">Ingresos</th><th class="text-right">Precio prom.</th><th class="text-right">Costo por galón</th><th class="text-right">Margen bruto</th><th style="width: 22%">Margen %</th></tr></thead>
                <tbody id="cuerpo_rentabilidad"></tbody>
              </table>
            </div>
            <p class="text-muted small mb-0" id="ren_nota"></p>
          </div>

          <div class="tab-pane fade" id="tab_ana_griferos" role="tabpanel">
            <p class="text-muted small">Turnos cerrados del periodo, ordenados por ventas. Faltante y sobrante salen del cuadre de cada turno.</p>
            <div class="table-responsive">
              <table class="table table-sm">
                <thead><tr><th>#</th><th>Grifero</th><th class="text-right">Turnos</th><th class="text-right">Galones</th><th style="width: 24%">Ventas</th><th class="text-right">Por turno</th><th class="text-right">Faltantes</th><th class="text-right">Sobrantes</th><th class="text-right">Cuadrados</th></tr></thead>
                <tbody id="cuerpo_griferos"></tbody>
              </table>
            </div>
          </div>

          <div class="tab-pane fade" id="tab_ana_morosidad" role="tabpanel">
            <div class="row" id="mor_tarjetas"></div>
            <p class="text-muted small" id="mor_nota"></p>
            <div class="table-responsive">
              <table class="table table-sm">
                <thead><tr><th>Cliente</th><th class="text-right">Vales</th><th class="text-right">Pendiente</th><th class="text-right">Por vencer</th><th class="text-right">1-30 días</th><th class="text-right">31-60 días</th><th class="text-right">61-90 días</th><th class="text-right">Más de 90</th><th class="text-right">Más atrasado</th></tr></thead>
                <tbody id="cuerpo_morosidad"></tbody>
              </table>
            </div>
          </div>

          <div class="tab-pane fade" id="tab_ana_horario" role="tabpanel">
            <h2 class="h6 font-weight-bold">Ventas por hora y día de la semana</h2>
            <p class="text-muted small" id="hor_nota"></p>
            <div class="table-responsive mb-4">
              <table class="table table-sm g-calor" id="tabla_calor">
                <caption class="sr-only">Total vendido por día de la semana y hora</caption>
                <thead id="cabecera_calor"></thead>
                <tbody id="cuerpo_calor"></tbody>
              </table>
            </div>
            <h2 class="h6 font-weight-bold">Venta promedio por turno</h2>
            <p class="text-muted small">Promedio de ventas de los turnos cerrados; entre paréntesis, cuántos turnos.</p>
            <div class="table-responsive">
              <table class="table table-sm" style="max-width: 36rem">
                <thead><tr><th>Día de la semana</th><th class="text-right">Turno día</th><th class="text-right">Turno noche</th></tr></thead>
                <tbody id="cuerpo_turnos_dia"></tbody>
              </table>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>

<script>iniciar_analisis();</script>
