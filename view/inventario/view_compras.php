<?php require_once __DIR__ . '/../../controller/_bootstrap.php'; ?>
<script src="../js/console_compras.js?rev=<?php echo time(); ?>"></script>

<div class="content-header">
  <div class="container-fluid"><h1 class="m-0"><i class="fas fa-truck-moving"></i> <b>COMPRAS Y PROVEEDORES</b></h1></div>
</div>

<div class="content">
  <div class="container-fluid">
    <div class="card card-primary card-outline card-tabs">
      <div class="card-header p-0 pt-1 border-bottom-0">
        <ul class="nav nav-tabs" role="tablist">
          <li class="nav-item"><a class="nav-link active" data-toggle="pill" href="#tab_compras" role="tab">Compras</a></li>
          <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#tab_proveedores" role="tab">Proveedores</a></li>
        </ul>
      </div>
      <div class="card-body">
        <div class="tab-content">
          <div class="tab-pane fade show active" id="tab_compras" role="tabpanel">
            <form id="filtro_compras" class="form-inline mb-2">
              <button type="button" class="btn btn-success btn-sm mr-3" id="btn_nueva_compra"><i class="fas fa-plus"></i> Registrar compra</button>
              <label class="mr-2" for="compra_desde">Desde</label><input type="date" class="form-control form-control-sm mr-2" id="compra_desde">
              <label class="mr-2" for="compra_hasta">Hasta</label><input type="date" class="form-control form-control-sm mr-2" id="compra_hasta">
              <select class="form-control form-control-sm mr-2" id="compra_filtro" aria-label="Filtro de estado"><option value="">Todas</option><option value="POR_PAGAR">Por pagar</option></select>
              <button class="btn btn-primary btn-sm" type="submit">Consultar</button>
            </form>
            <div class="table-responsive">
              <table class="table table-sm table-striped">
                <thead><tr><th>Fecha</th><th>Proveedor</th><th>Documento</th><th>Total</th><th>Saldo</th><th>Vence</th><th>Estado</th><th></th></tr></thead>
                <tbody id="cuerpo_compras"></tbody>
              </table>
            </div>
          </div>

          <div class="tab-pane fade" id="tab_proveedores" role="tabpanel">
            <button class="btn btn-success btn-sm mb-2" id="btn_nuevo_proveedor"><i class="fas fa-plus"></i> Nuevo proveedor</button>
            <div class="table-responsive">
              <table class="table table-sm table-striped">
                <thead><tr><th>RUC</th><th>Razón social</th><th>Teléfono</th><th>Dirección</th><th>Estado</th><th></th></tr></thead>
                <tbody id="cuerpo_proveedores"></tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modal_proveedor" tabindex="-1" role="dialog" aria-labelledby="titulo_proveedor">
  <div class="modal-dialog" role="document">
    <form class="modal-content" id="form_proveedor">
      <div class="modal-header"><h5 class="modal-title" id="titulo_proveedor">Proveedor</h5><button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button></div>
      <div class="modal-body">
        <input type="hidden" id="prov_id">
        <div class="form-group"><label for="prov_ruc">RUC</label><input class="form-control" id="prov_ruc" maxlength="11" pattern="\d{11}" required></div>
        <div class="form-group"><label for="prov_razon">Razón social</label><input class="form-control" id="prov_razon" maxlength="200" required></div>
        <div class="form-group"><label for="prov_telefono">Teléfono</label><input class="form-control" id="prov_telefono" maxlength="30"></div>
        <div class="form-group"><label for="prov_direccion">Dirección</label><input class="form-control" id="prov_direccion" maxlength="255"></div>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-primary">Guardar</button></div>
    </form>
  </div>
</div>

<div class="modal fade" id="modal_compra" tabindex="-1" role="dialog" aria-labelledby="titulo_compra">
  <div class="modal-dialog modal-lg" role="document">
    <form class="modal-content" id="form_compra">
      <div class="modal-header"><h5 class="modal-title" id="titulo_compra">Registrar compra de combustible</h5><button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button></div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-6 form-group"><label for="compra_proveedor">Proveedor</label><select class="form-control" id="compra_proveedor" required></select></div>
          <div class="col-md-3 form-group"><label for="compra_tipo">Documento</label><select class="form-control" id="compra_tipo"><option value="FACTURA">Factura</option><option value="GUIA">Guía</option><option value="OTRO">Otro</option></select></div>
          <div class="col-md-3 form-group"><label for="compra_numero">Número</label><input class="form-control" id="compra_numero" maxlength="30" required></div>
          <div class="col-md-3 form-group"><label for="compra_fecha">Fecha</label><input type="date" class="form-control" id="compra_fecha" required></div>
          <div class="col-md-3 form-group"><label for="compra_vence">Vencimiento</label><input type="date" class="form-control" id="compra_vence"></div>
          <div class="col-md-3 form-group"><label for="compra_percepcion">Percepción (S/)</label><input type="number" step="0.01" min="0" class="form-control" id="compra_percepcion" value="0"></div>
          <div class="col-md-3 form-group"><label for="compra_obs">Observaciones</label><input class="form-control" id="compra_obs" maxlength="255"></div>
        </div>
        <table class="table table-sm">
          <thead><tr><th>Tanque de descarga</th><th>Galones</th><th>Costo por galón (S/)</th><th>Importe</th><th></th></tr></thead>
          <tbody id="lineas_compra"></tbody>
        </table>
        <button type="button" class="btn btn-outline-primary btn-sm" id="btn_agregar_linea"><i class="fas fa-plus"></i> Agregar combustible</button>
        <h5 class="text-right mt-2">Total: <span id="compra_total">S/ 0.00</span></h5>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-primary">Registrar y sumar al stock</button></div>
    </form>
  </div>
</div>

<div class="modal fade" id="modal_pago" tabindex="-1" role="dialog" aria-labelledby="titulo_pago">
  <div class="modal-dialog" role="document">
    <form class="modal-content" id="form_pago">
      <div class="modal-header"><h5 class="modal-title" id="titulo_pago">Registrar pago</h5><button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button></div>
      <div class="modal-body">
        <input type="hidden" id="pago_compra">
        <div class="form-group"><label for="pago_monto">Monto (S/)</label><input type="number" step="0.01" min="0.01" class="form-control" id="pago_monto" required></div>
        <div class="form-group"><label for="pago_fecha">Fecha</label><input type="date" class="form-control" id="pago_fecha" required></div>
        <div class="form-group"><label for="pago_medio">Medio</label><input class="form-control" id="pago_medio" maxlength="40" placeholder="Transferencia, cheque…"></div>
        <div class="form-group"><label for="pago_referencia">Referencia</label><input class="form-control" id="pago_referencia" maxlength="60"></div>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-primary">Registrar pago</button></div>
    </form>
  </div>
</div>

<script>iniciar_compras();</script>
