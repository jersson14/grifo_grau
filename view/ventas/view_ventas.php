<?php
require_once __DIR__ . '/../../controller/_bootstrap.php';
$esAdmin = puede('ventas_gestionar');
$conComprobante = modulo_activo('facturacion');
?>
<script src="../js/console_ventas.js?rev=<?php echo time(); ?>"></script>

<div class="content-header">
  <div class="container-fluid"><h1 class="m-0"><i class="fas fa-cash-register"></i> <b>VENTAS</b></h1></div>
</div>

<div class="content">
  <div class="container-fluid">
    <div class="card">
      <div class="card-header"><h2 class="card-title">Registrar despacho</h2></div>
      <div class="card-body">
        <form id="form_venta">
          <fieldset class="mb-4">
            <legend class="g-leyenda">Surtidor</legend>
            <div id="venta_surtidores" role="radiogroup" aria-label="Surtidor"><p class="text-muted mb-0">Cargando surtidores…</p></div>
          </fieldset>

          <div class="row">
            <div class="col-lg-6 mb-4">
              <div class="d-flex flex-wrap align-items-center justify-content-between mb-2" style="gap: .5rem;">
                <label for="venta_valor" id="venta_valor_etiqueta" class="g-leyenda mb-0">Monto (S/)</label>
                <div class="g-segmento" role="radiogroup" aria-label="Vender por">
                  <label><input type="radio" name="venta_modo" value="SOLES" checked><span>Soles</span></label>
                  <label><input type="radio" name="venta_modo" value="GALONES"><span>Galones</span></label>
                </div>
              </div>
              <input type="number" step="0.001" min="0.001" class="form-control g-monto" id="venta_valor" required inputmode="decimal" autocomplete="off" placeholder="0.00">
              <div class="g-chips mt-2" id="venta_rapidos" aria-label="Montos rápidos"></div>
              <p class="g-equivale mt-2 mb-0">Equivale a <strong id="venta_calculo" aria-live="polite">—</strong></p>
            </div>

            <div class="col-lg-6 mb-4">
              <fieldset>
                <legend class="g-leyenda">Medio de pago</legend>
                <div class="g-segmento g-segmento-ancho" id="venta_pagos" role="radiogroup" aria-label="Medio de pago"></div>
              </fieldset>
              <div class="form-group mt-3 mb-0" id="grupo_codigo">
                <label for="venta_codigo">Código de operación</label>
                <input class="form-control" id="venta_codigo" maxlength="60" autocomplete="off" inputmode="numeric">
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 col-lg-5 form-group"><label for="venta_cliente">Cliente <span class="text-muted font-weight-normal">(opcional)</span></label><select class="form-control" id="venta_cliente"></select></div>
            <div class="col-md-6 col-lg-3 form-group"><label for="venta_placa">Placa <span class="text-muted font-weight-normal">(opcional)</span></label><input class="form-control text-uppercase" id="venta_placa" maxlength="20" autocomplete="off"></div>
            <?php if ($conComprobante) { ?>
            <div class="col-lg-4 form-group">
              <span class="d-block font-weight-bold mb-2" id="venta_comprobante_titulo">Comprobante</span>
              <div class="g-segmento" role="radiogroup" aria-labelledby="venta_comprobante_titulo">
                <label><input type="radio" name="venta_comprobante" value="NINGUNO" checked><span>Ninguno</span></label>
                <label><input type="radio" name="venta_comprobante" value="BOLETA"><span>Boleta</span></label>
                <label><input type="radio" name="venta_comprobante" value="FACTURA"><span>Factura</span></label>
              </div>
            </div>
            <?php } ?>
          </div>

          <button class="btn btn-primary btn-lg g-boton-venta" type="submit" id="venta_registrar"><i class="fas fa-check" aria-hidden="true"></i> <span>Registrar venta</span></button>
        </form>
      </div>
    </div>

    <div class="card card-tabs">
      <div class="card-header p-0 pt-1 border-bottom-0">
        <ul class="nav nav-tabs" role="tablist">
          <li class="nav-item"><a class="nav-link active" data-toggle="pill" href="#tab_ventas" role="tab"><?php echo $esAdmin ? 'Ventas' : 'Mis ventas'; ?></a></li>
          <?php if ($esAdmin) { ?><li class="nav-item"><a class="nav-link" data-toggle="pill" href="#tab_conciliacion" role="tab">Conciliación con lecturas</a></li><?php } ?>
        </ul>
      </div>
      <div class="card-body">
        <form id="filtro_ventas" class="form-inline flex-wrap mb-3" style="gap: .5rem;">
          <label for="venta_desde">Desde</label><input type="date" class="form-control form-control-sm" id="venta_desde">
          <label for="venta_hasta">Hasta</label><input type="date" class="form-control form-control-sm" id="venta_hasta">
          <button class="btn btn-outline-secondary btn-sm" type="submit">Consultar</button>
        </form>
        <div class="tab-content">
          <div class="tab-pane fade show active" id="tab_ventas" role="tabpanel">
            <p class="g-resumen-ventas mb-2" id="venta_resumen" aria-live="polite"></p>
            <div class="table-responsive">
              <table class="table table-sm table-striped tabla-ventas">
                <thead><tr><th>Fecha</th><th>Surtidor</th><th>Combustible</th><th class="text-right">Galones</th><th class="text-right">Total</th><th>Pago</th><th>Cliente</th><th>Placa</th><th>Comprobante</th><th>Turno</th><th>Usuario</th><th>Estado</th><?php if ($esAdmin) { ?><th></th><?php } ?></tr></thead>
                <tbody id="cuerpo_ventas"></tbody>
              </table>
            </div>
          </div>
          <?php if ($esAdmin) { ?>
          <div class="tab-pane fade" id="tab_conciliacion" role="tabpanel">
            <p class="text-muted">Una diferencia positiva indica galones despachados según las lecturas que no se registraron como venta individual.</p>
            <div class="table-responsive">
              <table class="table table-sm table-striped">
                <thead><tr><th>Fecha</th><th>Turno</th><th>Grifero</th><th>Combustible</th><th class="text-right">Según lecturas (gal)</th><th class="text-right">Ventas registradas (gal)</th><th class="text-right">Diferencia (gal)</th></tr></thead>
                <tbody id="cuerpo_conciliacion"></tbody>
              </table>
            </div>
          </div>
          <?php } ?>
        </div>
      </div>
    </div>
  </div>
</div>

<script>iniciar_ventas(<?php echo json_encode($esAdmin); ?>);</script>
