<?php
require_once __DIR__ . '/../../controller/_bootstrap.php';
$esAdmin = $_SESSION['S_ROL'] === 'ADMINISTRADOR';
?>
<script src="../js/console_ventas.js?rev=<?php echo time(); ?>"></script>

<div class="content-header">
  <div class="container-fluid"><h1 class="m-0"><i class="fas fa-cash-register"></i> <b>VENTAS</b></h1></div>
</div>

<div class="content">
  <div class="container-fluid">
    <div class="card card-primary card-outline">
      <div class="card-header"><h3 class="card-title">Registrar despacho</h3></div>
      <div class="card-body">
        <form id="form_venta" class="row">
          <div class="col-md-3 form-group"><label for="venta_surtidor">Surtidor</label><select class="form-control" id="venta_surtidor" required></select></div>
          <div class="col-md-2 form-group"><label for="venta_modo">Vender por</label><select class="form-control" id="venta_modo"><option value="SOLES">Soles</option><option value="GALONES">Galones</option></select></div>
          <div class="col-md-2 form-group"><label for="venta_valor" id="venta_valor_etiqueta">Monto (S/)</label><input type="number" step="0.001" min="0.001" class="form-control" id="venta_valor" required></div>
          <div class="col-md-2 form-group"><label>Equivale a</label><div class="form-control-plaintext font-weight-bold" id="venta_calculo" aria-live="polite">—</div></div>
          <div class="col-md-3 form-group"><label for="venta_pago">Medio de pago</label><select class="form-control" id="venta_pago" required></select></div>
          <div class="col-md-2 form-group" id="grupo_codigo"><label for="venta_codigo">Código de operación</label><input class="form-control" id="venta_codigo" maxlength="60"></div>
          <div class="col-md-4 form-group"><label for="venta_cliente">Cliente (opcional)</label><select class="form-control" id="venta_cliente"></select></div>
          <div class="col-md-2 form-group"><label for="venta_placa">Placa</label><input class="form-control text-uppercase" id="venta_placa" maxlength="20"></div>
          <?php if (modulo_activo('facturacion')) { ?>
          <div class="col-md-2 form-group"><label for="venta_comprobante">Comprobante</label><select class="form-control" id="venta_comprobante"><option value="NINGUNO">Ninguno</option><option value="BOLETA">Boleta</option><option value="FACTURA">Factura</option></select></div>
          <?php } else { ?>
          <input type="hidden" id="venta_comprobante" value="NINGUNO">
          <?php } ?>
          <div class="col-12"><button class="btn btn-primary" type="submit"><i class="fas fa-save"></i> Registrar venta</button></div>
        </form>
      </div>
    </div>

    <div class="card card-primary card-outline card-tabs">
      <div class="card-header p-0 pt-1 border-bottom-0">
        <ul class="nav nav-tabs" role="tablist">
          <li class="nav-item"><a class="nav-link active" data-toggle="pill" href="#tab_ventas" role="tab"><?php echo $esAdmin ? 'Ventas' : 'Mis ventas'; ?></a></li>
          <?php if ($esAdmin) { ?><li class="nav-item"><a class="nav-link" data-toggle="pill" href="#tab_conciliacion" role="tab">Conciliación con lecturas</a></li><?php } ?>
        </ul>
      </div>
      <div class="card-body">
        <form id="filtro_ventas" class="form-inline mb-2">
          <label class="mr-2" for="venta_desde">Desde</label><input type="date" class="form-control form-control-sm mr-2" id="venta_desde">
          <label class="mr-2" for="venta_hasta">Hasta</label><input type="date" class="form-control form-control-sm mr-2" id="venta_hasta">
          <button class="btn btn-primary btn-sm" type="submit">Consultar</button>
        </form>
        <div class="tab-content">
          <div class="tab-pane fade show active table-responsive" id="tab_ventas" role="tabpanel">
            <table class="table table-sm table-striped">
              <thead><tr><th>Fecha</th><th>Surtidor</th><th>Combustible</th><th>Galones</th><th>Total</th><th>Pago</th><th>Cliente</th><th>Placa</th><th>Comprobante</th><th>Turno</th><th>Usuario</th><th>Estado</th><?php if ($esAdmin) { ?><th></th><?php } ?></tr></thead>
              <tbody id="cuerpo_ventas"></tbody>
            </table>
          </div>
          <?php if ($esAdmin) { ?>
          <div class="tab-pane fade table-responsive" id="tab_conciliacion" role="tabpanel">
            <p class="text-muted">Una diferencia positiva indica galones despachados según las lecturas que no se registraron como venta individual.</p>
            <table class="table table-sm table-striped">
              <thead><tr><th>Fecha</th><th>Turno</th><th>Grifero</th><th>Combustible</th><th>Según lecturas (gal)</th><th>Ventas registradas (gal)</th><th>Diferencia (gal)</th></tr></thead>
              <tbody id="cuerpo_conciliacion"></tbody>
            </table>
          </div>
          <?php } ?>
        </div>
      </div>
    </div>
  </div>
</div>

<script>iniciar_ventas(<?php echo json_encode($esAdmin); ?>);</script>
