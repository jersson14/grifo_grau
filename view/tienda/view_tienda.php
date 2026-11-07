<?php require_once __DIR__ . '/../../controller/_bootstrap.php'; ?>
<script src="../js/console_tienda.js?rev=<?php echo time(); ?>"></script>

<div class="content-header">
  <div class="container-fluid"><h1 class="m-0"><i class="fas fa-store"></i> <b>TIENDA</b></h1></div>
</div>

<div class="content">
  <div class="container-fluid">
    <div class="card card-tabs">
      <div class="card-header p-0 pt-1 border-bottom-0">
        <ul class="nav nav-tabs" role="tablist">
          <li class="nav-item"><a class="nav-link active" data-toggle="pill" href="#tab_tie_vender" role="tab">Vender</a></li>
          <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#tab_tie_ventas" role="tab">Ventas</a></li>
          <li class="nav-item d-none" id="tie_tab_productos_item"><a class="nav-link" data-toggle="pill" href="#tab_tie_productos" role="tab">Productos y stock <span class="badge badge-warning" id="tie_stock_bajo"></span></a></li>
        </ul>
      </div>
      <div class="card-body">
        <div class="tab-content">

          <div class="tab-pane fade show active" id="tab_tie_vender" role="tabpanel">
            <div class="row">
              <div class="col-lg-7">
                <label for="tie_buscar" class="sr-only">Buscar producto</label>
                <input type="search" class="form-control mb-3" id="tie_buscar" placeholder="Buscar por nombre o código…" autocomplete="off">
                <div id="tie_catalogo" class="tw-grid tw-grid-cols-2 tw-gap-2 sm:tw-grid-cols-3" role="list" aria-label="Productos"></div>
              </div>
              <div class="col-lg-5 mt-3 mt-lg-0">
                <div class="card mb-0">
                  <div class="card-header"><h3 class="card-title"><i class="fas fa-shopping-cart"></i> Venta actual</h3></div>
                  <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                      <thead><tr><th>Producto</th><th style="width: 7.5rem">Cantidad</th><th class="text-right">Importe</th><th></th></tr></thead>
                      <tbody id="tie_carrito"></tbody>
                    </table>
                  </div>
                  <div class="card-footer bg-white">
                    <div class="d-flex justify-content-between align-items-baseline mb-3">
                      <span class="text-muted">Total</span>
                      <span class="g-cifra" id="tie_total">S/ 0.00</span>
                    </div>
                    <form id="form_cobrar" class="row">
                      <div class="col-sm-6 form-group"><label for="tie_medio">Medio de pago</label><select class="form-control" id="tie_medio"></select></div>
                      <div class="col-sm-6 form-group" id="tie_operacion_grupo"><label for="tie_operacion">Código de operación</label><input class="form-control" id="tie_operacion" maxlength="60"></div>
                      <div class="col-12"><button type="submit" class="btn btn-primary btn-lg btn-block" id="tie_cobrar" disabled><i class="fas fa-cash-register" aria-hidden="true"></i> Cobrar</button></div>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="tab-pane fade" id="tab_tie_ventas" role="tabpanel">
            <form id="filtro_tienda" class="form-inline flex-wrap mb-3" style="gap: .5rem;">
              <label for="tie_desde">Desde</label><input type="date" class="form-control form-control-sm" id="tie_desde">
              <label for="tie_hasta">Hasta</label><input type="date" class="form-control form-control-sm" id="tie_hasta">
              <button class="btn btn-primary btn-sm" type="submit">Consultar</button>
            </form>
            <div class="row" id="tie_tarjetas"></div>
            <div class="row">
              <div class="col-lg-5">
                <h3 class="h6">Por medio de pago</h3>
                <table class="table table-sm"><thead><tr><th>Medio</th><th>Ventas</th><th class="text-right">Total</th></tr></thead><tbody id="tie_por_medio"></tbody></table>
              </div>
              <div class="col-lg-7">
                <h3 class="h6">Productos más vendidos</h3>
                <table class="table table-sm"><thead><tr><th>Producto</th><th class="text-right">Cantidad</th><th class="text-right">Total</th><th class="text-right">Margen</th></tr></thead><tbody id="tie_por_producto"></tbody></table>
              </div>
            </div>
            <h3 class="h6 mt-2">Ventas</h3>
            <div class="table-responsive">
              <table class="table table-sm table-striped">
                <thead><tr><th>N.º</th><th>Fecha</th><th>Productos</th><th>Medio</th><th class="text-right">Total</th><th>Vendedor</th><th>Estado</th><th></th></tr></thead>
                <tbody id="tie_ventas"></tbody>
              </table>
            </div>
          </div>

          <div class="tab-pane fade" id="tab_tie_productos" role="tabpanel">
            <form id="form_producto_tienda" class="row align-items-end">
              <input type="hidden" id="tp_id">
              <div class="col-sm-6 col-lg-4 form-group"><label for="tp_nombre">Producto</label><input class="form-control" id="tp_nombre" maxlength="120" required placeholder="Aceite 20W-50 1 galón"></div>
              <div class="col-sm-6 col-lg-2 form-group"><label for="tp_codigo">Código <span class="text-muted font-weight-normal">(opcional)</span></label><input class="form-control" id="tp_codigo" maxlength="40"></div>
              <div class="col-sm-6 col-lg-3 form-group"><label for="tp_categoria">Categoría</label><select class="form-control" id="tp_categoria"></select></div>
              <div class="col-sm-6 col-lg-3 form-group"><label for="tp_unidad">Unidad</label><select class="form-control" id="tp_unidad"></select></div>
              <div class="col-sm-4 col-lg-2 form-group"><label for="tp_precio">Precio (S/)</label><input type="number" step="0.01" min="0.01" class="form-control" id="tp_precio" required></div>
              <div class="col-sm-4 col-lg-2 form-group"><label for="tp_costo">Costo (S/) <span class="text-muted font-weight-normal">(opc.)</span></label><input type="number" step="0.01" min="0" class="form-control" id="tp_costo"></div>
              <div class="col-sm-4 col-lg-2 form-group"><label for="tp_minimo">Stock mínimo</label><input type="number" step="0.01" min="0" class="form-control" id="tp_minimo" value="0"></div>
              <div class="col-sm-4 col-lg-2 form-group" id="tp_inicial_grupo"><label for="tp_inicial">Stock inicial</label><input type="number" step="0.01" min="0" class="form-control" id="tp_inicial"></div>
              <div class="col-sm-4 col-lg-1 form-group"><label for="tp_activo">Activo</label><select class="form-control" id="tp_activo"><option value="1">Sí</option><option value="0">No</option></select></div>
              <div class="col-sm-4 col-lg-3 form-group"><button type="submit" class="btn btn-primary" id="tp_guardar">Agregar producto</button> <button type="button" class="btn btn-link d-none" id="tp_cancelar">Cancelar</button></div>
            </form>
            <div class="table-responsive">
              <table class="table table-sm table-striped">
                <thead><tr><th>Producto</th><th>Categoría</th><th class="text-right">Precio</th><th class="text-right">Stock</th><th>Estado</th><th></th></tr></thead>
                <tbody id="tie_productos"></tbody>
              </table>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modal_kardex_tienda" tabindex="-1" role="dialog" aria-labelledby="kardex_titulo" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title" id="kardex_titulo">Kardex</h5><button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button></div>
      <div class="modal-body table-responsive">
        <table class="table table-sm table-striped mb-0">
          <thead><tr><th>Fecha</th><th>Movimiento</th><th class="text-right">Cantidad</th><th class="text-right">Stock</th><th>Detalle</th><th>Usuario</th></tr></thead>
          <tbody id="cuerpo_kardex_tienda"></tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script>iniciar_tienda();</script>
