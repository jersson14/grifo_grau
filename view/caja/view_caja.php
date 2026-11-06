<?php require_once __DIR__ . '/../../controller/_bootstrap.php'; ?>
<script src="../js/console_caja.js?rev=<?php echo time(); ?>"></script>

<div class="content-header">
  <div class="container-fluid"><h1 class="m-0"><i class="fas fa-coins"></i> <b>CAJA Y ARQUEO</b></h1></div>
</div>

<div class="content">
  <div class="container-fluid">
    <form id="filtro_caja" class="form-inline mb-3">
      <label class="mr-2" for="caja_desde">Desde</label><input type="date" class="form-control form-control-sm mr-2" id="caja_desde">
      <label class="mr-2" for="caja_hasta">Hasta</label><input type="date" class="form-control form-control-sm mr-2" id="caja_hasta">
      <button class="btn btn-primary btn-sm" type="submit">Consultar</button>
    </form>

    <div class="row" id="caja_tarjetas"></div>

    <div class="card card-primary card-outline card-tabs">
      <div class="card-header p-0 pt-1 border-bottom-0">
        <ul class="nav nav-tabs" role="tablist">
          <li class="nav-item"><a class="nav-link active" data-toggle="pill" href="#tab_arqueos" role="tab">Arqueos por turno</a></li>
          <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#tab_depositos" role="tab">Depósitos</a></li>
          <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#tab_griferos" role="tab">Diferencias por grifero</a></li>
        </ul>
      </div>
      <div class="card-body">
        <div class="tab-content">
          <div class="tab-pane fade show active table-responsive" id="tab_arqueos" role="tabpanel">
            <table class="table table-sm table-striped">
              <thead><tr><th>Fecha</th><th>Turno</th><th>Grifero</th><th>Esperado</th><th>Declarado</th><th>Contado</th><th>Diferencia</th><th>Estado</th><th></th></tr></thead>
              <tbody id="cuerpo_arqueos"></tbody>
            </table>
          </div>

          <div class="tab-pane fade" id="tab_depositos" role="tabpanel">
            <form id="form_deposito" class="row">
              <div class="col-md-2 form-group"><label for="dep_fecha">Fecha</label><input type="date" class="form-control" id="dep_fecha" required></div>
              <div class="col-md-2 form-group"><label for="dep_banco">Banco</label><input class="form-control" id="dep_banco" maxlength="60" required></div>
              <div class="col-md-2 form-group"><label for="dep_operacion">N.º de operación</label><input class="form-control" id="dep_operacion" maxlength="60" required></div>
              <div class="col-md-2 form-group"><label for="dep_monto">Monto (S/)</label><input type="number" step="0.01" min="0.01" class="form-control" id="dep_monto" required></div>
              <div class="col-md-2 form-group"><label for="dep_obs">Observaciones</label><input class="form-control" id="dep_obs" maxlength="255"></div>
              <div class="col-md-2 form-group d-flex align-items-end"><button class="btn btn-primary btn-block" type="submit">Registrar</button></div>
            </form>
            <div class="table-responsive">
              <table class="table table-sm table-striped">
                <thead><tr><th>Fecha</th><th>Banco</th><th>Operación</th><th>Monto</th><th>Usuario</th><th>Estado</th><th></th></tr></thead>
                <tbody id="cuerpo_depositos"></tbody>
              </table>
            </div>
          </div>

          <div class="tab-pane fade table-responsive" id="tab_griferos" role="tabpanel">
            <table class="table table-sm table-striped">
              <thead><tr><th>Grifero</th><th>Arqueos</th><th>Faltantes</th><th>Sobrantes</th><th>Pendiente de resolver</th></tr></thead>
              <tbody id="cuerpo_griferos"></tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modal_arqueo" tabindex="-1" role="dialog" aria-labelledby="titulo_arqueo">
  <div class="modal-dialog modal-lg" role="document">
    <form class="modal-content" id="form_arqueo">
      <div class="modal-header"><h5 class="modal-title" id="titulo_arqueo">Arqueo</h5><button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button></div>
      <div class="modal-body">
        <input type="hidden" id="arqueo_reporte">
        <div class="row" id="arqueo_denominaciones"></div>
        <div class="form-group"><label for="arqueo_obs">Observaciones</label><input class="form-control" id="arqueo_obs" maxlength="255"></div>
        <table class="table table-sm mb-0">
          <tr><th>Efectivo esperado</th><td class="text-right" id="arqueo_esperado"></td></tr>
          <tr><th>Efectivo contado</th><td class="text-right" id="arqueo_contado">S/ 0.00</td></tr>
          <tr><th>Diferencia</th><td class="text-right font-weight-bold" id="arqueo_diferencia" aria-live="polite">S/ 0.00</td></tr>
        </table>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-primary">Guardar arqueo</button></div>
    </form>
  </div>
</div>

<script>iniciar_caja();</script>
