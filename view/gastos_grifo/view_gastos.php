<?php require_once __DIR__ . '/../../controller/_bootstrap.php'; ?>
<script src="../js/console_gastos_grifo.js?rev=<?php echo time(); ?>"></script>

<div class="content-header">
  <div class="container-fluid"><h1 class="m-0"><i class="fas fa-receipt"></i> <b>GASTOS DEL GRIFO</b></h1></div>
</div>

<div class="content">
  <div class="container-fluid">
    <form id="filtro_gastos" class="form-inline flex-wrap mb-3" style="gap: .5rem;">
      <label for="gas_desde">Desde</label><input type="date" class="form-control form-control-sm" id="gas_desde">
      <label for="gas_hasta">Hasta</label><input type="date" class="form-control form-control-sm" id="gas_hasta">
      <label for="gas_filtro_categoria">Categoría</label>
      <select class="form-control form-control-sm" id="gas_filtro_categoria"></select>
      <div class="custom-control custom-checkbox mx-2">
        <input type="checkbox" class="custom-control-input" id="gas_anulados">
        <label class="custom-control-label" for="gas_anulados">Incluir anulados</label>
      </div>
      <button class="btn btn-primary btn-sm" type="submit">Consultar</button>
      <a class="btn btn-outline-secondary btn-sm" id="gas_exportar" href="#"><i class="fas fa-file-excel" aria-hidden="true"></i> Exportar a Excel</a>
    </form>

    <div class="row" id="gas_tarjetas"></div>

    <div class="card card-tabs">
      <div class="card-header p-0 pt-1 border-bottom-0">
        <ul class="nav nav-tabs" role="tablist">
          <li class="nav-item"><a class="nav-link active" data-toggle="pill" href="#tab_gas_lista" role="tab">Gastos</a></li>
          <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#tab_gas_categorias_resumen" role="tab">Por categoría</a></li>
          <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#tab_gas_categorias" role="tab">Categorías</a></li>
        </ul>
      </div>
      <div class="card-body">
        <div class="tab-content">

          <div class="tab-pane fade show active" id="tab_gas_lista" role="tabpanel">
            <form id="form_gasto" class="row align-items-end">
              <div class="col-sm-6 col-lg-2 form-group"><label for="gas_fecha">Fecha</label><input type="date" class="form-control" id="gas_fecha" required></div>
              <div class="col-sm-6 col-lg-3 form-group"><label for="gas_categoria">Categoría</label><select class="form-control" id="gas_categoria" required></select></div>
              <div class="col-lg-7 form-group"><label for="gas_descripcion">Descripción</label><input class="form-control" id="gas_descripcion" maxlength="200" required placeholder="Recibo de luz de octubre"></div>
              <div class="col-sm-6 col-lg-2 form-group"><label for="gas_monto">Monto (S/)</label><input type="number" step="0.01" min="0.01" class="form-control" id="gas_monto" required inputmode="decimal"></div>
              <div class="col-sm-6 col-lg-3 form-group"><label for="gas_medio">Medio de pago</label><select class="form-control" id="gas_medio" required></select></div>
              <div class="col-sm-6 col-lg-3 form-group"><label for="gas_comprobante">N.º de comprobante <span class="text-muted font-weight-normal">(opcional)</span></label><input class="form-control" id="gas_comprobante" maxlength="40"></div>
              <div class="col-sm-6 col-lg-4 form-group"><button type="submit" class="btn btn-primary btn-block"><i class="fas fa-plus" aria-hidden="true"></i> Registrar gasto</button></div>
            </form>
            <div class="table-responsive">
              <table class="table table-sm table-striped">
                <thead><tr><th>Fecha</th><th>Categoría</th><th>Descripción</th><th>Medio</th><th class="text-right">Monto</th><th>Estado</th><th></th></tr></thead>
                <tbody id="cuerpo_gastos"></tbody>
              </table>
            </div>
          </div>

          <div class="tab-pane fade" id="tab_gas_categorias_resumen" role="tabpanel">
            <p class="text-muted">Gastos registrados del periodo por categoría. Los «otros gastos» declarados al cerrar cada turno se suman aparte en el resumen.</p>
            <div class="table-responsive">
              <table class="table table-sm">
                <thead><tr><th>Categoría</th><th>Gastos</th><th class="text-right">Total</th><th style="width: 40%">Participación</th></tr></thead>
                <tbody id="cuerpo_gastos_categoria"></tbody>
              </table>
            </div>
          </div>

          <div class="tab-pane fade" id="tab_gas_categorias" role="tabpanel">
            <form id="form_categoria_gasto" class="row align-items-end">
              <input type="hidden" id="cat_id">
              <div class="col-md-5 form-group"><label for="cat_nombre">Nombre de la categoría</label><input class="form-control" id="cat_nombre" maxlength="60" required></div>
              <div class="col-md-2 form-group"><label for="cat_activo">Activa</label><select class="form-control" id="cat_activo"><option value="1">Sí</option><option value="0">No</option></select></div>
              <div class="col-md-3 form-group"><button type="submit" class="btn btn-primary" id="cat_guardar">Agregar categoría</button> <button type="button" class="btn btn-link d-none" id="cat_cancelar">Cancelar</button></div>
            </form>
            <div class="table-responsive">
              <table class="table table-sm table-striped">
                <thead><tr><th>Categoría</th><th>Gastos registrados</th><th>Estado</th><th></th></tr></thead>
                <tbody id="cuerpo_categorias_gasto"></tbody>
              </table>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>

<script>iniciar_gastos();</script>
