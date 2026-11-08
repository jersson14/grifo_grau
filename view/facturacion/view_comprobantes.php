<?php require_once __DIR__ . '/../../controller/_bootstrap.php'; ?>
<script src="../js/console_comprobantes.js?rev=<?php echo time(); ?>"></script>

<div class="content-header">
  <div class="container-fluid"><h1 class="m-0"><i class="fas fa-file-invoice-dollar"></i> <b>COMPROBANTES ELECTRÓNICOS</b></h1></div>
</div>

<div class="content">
  <div class="container-fluid">
    <div class="d-flex flex-wrap align-items-end mb-3" style="gap: .5rem;">
      <form id="filtro_comprobantes" class="d-flex flex-wrap align-items-end" style="gap: .5rem;">
        <div><label for="cpe_desde" class="d-block mb-1">Desde</label><input type="date" class="form-control form-control-sm" id="cpe_desde"></div>
        <div><label for="cpe_hasta" class="d-block mb-1">Hasta</label><input type="date" class="form-control form-control-sm" id="cpe_hasta"></div>
        <div>
          <label for="cpe_estado" class="d-block mb-1">Estado</label>
          <select class="form-control form-control-sm" id="cpe_estado">
            <option value="">Todos</option>
            <option value="PENDIENTE">Pendiente de envío</option>
            <option value="ENVIADO">Por confirmar en SUNAT</option>
            <option value="ACEPTADO">Aceptado</option>
            <option value="RECHAZADO">Rechazado</option>
            <option value="ANULACION_PENDIENTE">Anulación por confirmar</option>
            <option value="ANULADO">Anulado</option>
          </select>
        </div>
        <button class="btn btn-outline-secondary btn-sm" type="submit">Consultar</button>
      </form>
      <div class="ml-auto d-flex flex-wrap" style="gap: .5rem;">
        <button class="btn btn-outline-secondary btn-sm" type="button" id="btn_procesar_cola"><i class="fas fa-sync" aria-hidden="true"></i> Enviar pendientes y actualizar</button>
        <button class="btn btn-primary btn-sm" type="button" id="btn_facturar_vales"><i class="fas fa-receipt" aria-hidden="true"></i> Facturar vales de un cliente</button>
      </div>
    </div>

    <div class="card">
      <div class="card-body table-responsive">
        <table class="table table-sm table-hover">
          <thead><tr><th>Fecha</th><th>Comprobante</th><th>Cliente</th><th class="text-right">Total</th><th>Estado</th><th>Detalle</th><th>Archivos</th><th><span class="sr-only">Acciones</span></th></tr></thead>
          <tbody id="cuerpo_comprobantes"></tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- FACTURAR VALES -->
<div class="modal fade" id="modal_vales" tabindex="-1" role="dialog" aria-labelledby="modal_vales_titulo">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h2 class="modal-title h5" id="modal_vales_titulo">Facturar vales de un cliente</h2>
        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
      </div>
      <div class="modal-body">
        <p class="text-muted small">Un solo comprobante con una línea por vale. Solo aparecen los vales que aún no están en otro comprobante; si el comprobante se anula, sus vales vuelven a estar disponibles.</p>
        <div class="form-row">
          <div class="col-md-6 form-group"><label for="val_cliente">Cliente</label><select class="form-control" id="val_cliente"></select></div>
          <div class="col-6 col-md-3 form-group"><label for="val_desde">Desde</label><input type="date" class="form-control" id="val_desde"></div>
          <div class="col-6 col-md-3 form-group"><label for="val_hasta">Hasta</label><input type="date" class="form-control" id="val_hasta"></div>
        </div>
        <div class="table-responsive" style="max-height: 320px;">
          <table class="table table-sm">
            <thead><tr><th style="width: 2rem;"><input type="checkbox" id="val_todos" aria-label="Marcar todos los vales"></th><th>Fecha</th><th>Vale</th><th>Placa</th><th>Turno</th><th class="text-right">Monto</th></tr></thead>
            <tbody id="cuerpo_vales"></tbody>
          </table>
        </div>
        <div class="d-flex flex-wrap align-items-center mt-2" style="gap: 1rem;">
          <div>
            <span class="d-block font-weight-bold mb-1" id="val_tipo_titulo">Comprobante</span>
            <div class="g-segmento" role="radiogroup" aria-labelledby="val_tipo_titulo">
              <label><input type="radio" name="val_tipo" value="FACTURA" checked><span>Factura</span></label>
              <label><input type="radio" name="val_tipo" value="BOLETA"><span>Boleta</span></label>
            </div>
          </div>
          <div class="ml-auto text-right"><div class="small text-muted">Total a facturar</div><div class="g-cifra" id="val_total" aria-live="polite">S/ 0.00</div></div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-link" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" id="val_emitir" disabled>Emitir comprobante</button>
      </div>
    </div>
  </div>
</div>

<!-- NOTA DE CRÉDITO O DÉBITO -->
<div class="modal fade" id="modal_nota" tabindex="-1" role="dialog" aria-labelledby="modal_nota_titulo">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h2 class="modal-title h5" id="modal_nota_titulo">Nota sobre el comprobante</h2>
        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
      </div>
      <div class="modal-body">
        <p class="mb-3" id="nota_info"></p>
        <div class="form-group">
          <div class="g-segmento g-segmento-ancho" role="radiogroup" aria-label="Tipo de nota">
            <label><input type="radio" name="nota_clase" value="NCREDITO" checked><span>Nota de crédito</span></label>
            <label><input type="radio" name="nota_clase" value="NDEBITO"><span>Nota de débito</span></label>
          </div>
          <small class="form-text text-muted" id="nota_ayuda_clase">Resta del comprobante: anulación, devolución, descuento o corrección.</small>
        </div>
        <div class="form-group"><label for="nota_motivo">Motivo (catálogo de SUNAT)</label><select class="form-control" id="nota_motivo"></select></div>
        <div class="form-group"><label for="nota_sustento">Sustento</label><input class="form-control" id="nota_sustento" maxlength="250" placeholder="Cliente devolvió el pedido"><small class="form-text text-muted">Aparece en el documento que recibe el cliente.</small></div>
        <div class="form-group" id="nota_grupo_monto">
          <label for="nota_monto">Monto (S/, con IGV)</label>
          <input type="number" step="0.01" min="0.01" class="form-control" id="nota_monto" inputmode="decimal">
        </div>
        <p class="font-weight-bold mb-0 d-none" id="nota_monto_total"></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-link" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" id="nota_emitir">Emitir nota</button>
      </div>
    </div>
  </div>
</div>

<script>iniciar_comprobantes();</script>
