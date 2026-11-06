<?php require_once __DIR__ . '/../../controller/_bootstrap.php'; ?>
<script src="../js/console_comprobantes.js?rev=<?php echo time(); ?>"></script>

<div class="content-header">
  <div class="container-fluid"><h1 class="m-0"><i class="fas fa-file-invoice-dollar"></i> <b>COMPROBANTES ELECTRÓNICOS</b></h1></div>
</div>

<div class="content">
  <div class="container-fluid">
    <div class="card card-primary card-outline">
      <div class="card-header">
        <form id="filtro_comprobantes" class="form-inline">
          <label class="mr-2" for="cpe_desde">Desde</label><input type="date" class="form-control form-control-sm mr-2" id="cpe_desde">
          <label class="mr-2" for="cpe_hasta">Hasta</label><input type="date" class="form-control form-control-sm mr-2" id="cpe_hasta">
          <label class="mr-2" for="cpe_estado">Estado</label>
          <select class="form-control form-control-sm mr-2" id="cpe_estado">
            <option value="">Todos</option>
            <option value="PENDIENTE">Pendiente de envío</option>
            <option value="ENVIADO">Por confirmar en SUNAT</option>
            <option value="ACEPTADO">Aceptado</option>
            <option value="RECHAZADO">Rechazado</option>
            <option value="ANULACION_PENDIENTE">Anulación por confirmar</option>
            <option value="ANULADO">Anulado</option>
          </select>
          <button class="btn btn-primary btn-sm mr-2" type="submit">Consultar</button>
          <button class="btn btn-success btn-sm" type="button" id="btn_procesar_cola"><i class="fas fa-sync"></i> Enviar pendientes y actualizar</button>
        </form>
      </div>
      <div class="card-body table-responsive">
        <table class="table table-sm table-striped">
          <thead><tr><th>Fecha</th><th>Comprobante</th><th>Cliente</th><th>Total</th><th>Estado</th><th>Detalle</th><th>Archivos</th><th></th></tr></thead>
          <tbody id="cuerpo_comprobantes"></tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script>iniciar_comprobantes();</script>
