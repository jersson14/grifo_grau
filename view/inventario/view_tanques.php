<?php require_once __DIR__ . '/../../controller/_bootstrap.php'; ?>
<script src="../js/console_inventario.js?rev=<?php echo time(); ?>"></script>

<div class="content-header">
  <div class="container-fluid"><h1 class="m-0"><i class="fas fa-oil-can"></i> <b>TANQUES E INVENTARIO</b></h1></div>
</div>

<div class="content">
  <div class="container-fluid">
    <div class="row" id="tarjetas_tanques"></div>

    <div class="card card-primary card-outline card-tabs">
      <div class="card-header p-0 pt-1 border-bottom-0">
        <ul class="nav nav-tabs" role="tablist">
          <li class="nav-item"><a class="nav-link active" data-toggle="pill" href="#tab_tanques" role="tab">Tanques</a></li>
          <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#tab_asignacion" role="tab">Surtidores por tanque</a></li>
          <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#tab_kardex" role="tab">Kardex</a></li>
        </ul>
      </div>
      <div class="card-body">
        <div class="tab-content">
          <div class="tab-pane fade show active" id="tab_tanques" role="tabpanel">
            <button class="btn btn-success btn-sm mb-2" id="btn_nuevo_tanque"><i class="fas fa-plus"></i> Nuevo tanque</button>
            <div class="table-responsive">
              <table class="table table-sm table-striped">
                <thead><tr><th>Tanque</th><th>Combustible</th><th>Capacidad (gal)</th><th>Mínimo (gal)</th><th>Stock teórico (gal)</th><th>Estado</th><th></th></tr></thead>
                <tbody id="cuerpo_tanques"></tbody>
              </table>
            </div>
          </div>

          <div class="tab-pane fade" id="tab_asignacion" role="tabpanel">
            <p class="text-muted">Indica de qué tanque sale cada surtidor. Sin asignar, descuenta del primer tanque activo de su combustible.</p>
            <div class="table-responsive">
              <table class="table table-sm table-striped">
                <thead><tr><th>Máquina</th><th>Surtidor</th><th>Combustible</th><th>Tanque</th></tr></thead>
                <tbody id="cuerpo_asignacion"></tbody>
              </table>
            </div>
          </div>

          <div class="tab-pane fade" id="tab_kardex" role="tabpanel">
            <form id="form_kardex" class="form-inline mb-2">
              <label class="mr-2" for="kardex_tanque">Tanque</label><select class="form-control form-control-sm mr-2" id="kardex_tanque"></select>
              <label class="mr-2" for="kardex_desde">Desde</label><input type="date" class="form-control form-control-sm mr-2" id="kardex_desde">
              <label class="mr-2" for="kardex_hasta">Hasta</label><input type="date" class="form-control form-control-sm mr-2" id="kardex_hasta">
              <button class="btn btn-primary btn-sm" type="submit">Consultar</button>
            </form>
            <div class="table-responsive">
              <table class="table table-sm table-striped">
                <thead><tr><th>Fecha</th><th>Movimiento</th><th>Galones</th><th>Saldo</th><th>Referencia</th><th>Usuario</th></tr></thead>
                <tbody id="cuerpo_kardex"></tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modal_tanque" tabindex="-1" role="dialog" aria-labelledby="titulo_tanque">
  <div class="modal-dialog" role="document">
    <form class="modal-content" id="form_tanque">
      <div class="modal-header"><h5 class="modal-title" id="titulo_tanque">Tanque</h5><button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button></div>
      <div class="modal-body">
        <input type="hidden" id="tanque_id">
        <div class="form-group"><label for="tanque_nombre">Nombre</label><input class="form-control" id="tanque_nombre" maxlength="60" required></div>
        <div class="form-group"><label for="tanque_producto">Combustible</label><select class="form-control" id="tanque_producto" required></select></div>
        <div class="row">
          <div class="col-6 form-group"><label for="tanque_capacidad">Capacidad (gal)</label><input type="number" step="0.001" min="0.001" class="form-control" id="tanque_capacidad" required></div>
          <div class="col-6 form-group"><label for="tanque_minimo">Stock mínimo (gal)</label><input type="number" step="0.001" min="0" class="form-control" id="tanque_minimo" value="0"></div>
        </div>
        <div class="form-group" id="grupo_stock_inicial"><label for="tanque_inicial">Stock inicial medido (gal)</label><input type="number" step="0.001" min="0" class="form-control" id="tanque_inicial" value="0"></div>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-primary">Guardar</button></div>
    </form>
  </div>
</div>

<script>iniciar_tanques();</script>
