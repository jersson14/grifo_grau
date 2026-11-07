<?php require_once __DIR__ . '/../../controller/_bootstrap.php'; ?>
<script src="../js/console_flotas.js?rev=<?php echo time(); ?>"></script>

<div class="content-header">
  <div class="container-fluid d-flex flex-wrap align-items-center" style="gap: .5rem;">
    <h1 class="m-0 mr-auto"><i class="fas fa-truck"></i> <b>FLOTAS</b></h1>
    <button type="button" class="btn btn-primary" id="flo_nueva"><i class="fas fa-plus" aria-hidden="true"></i> Nueva flota</button>
  </div>
</div>

<div class="content">
  <div class="container-fluid">

    <div id="flo_lista">
      <div class="row" id="flo_tarjetas"></div>
      <div class="card">
        <div class="card-body table-responsive">
          <table class="table table-sm table-hover mb-0">
            <thead><tr><th>Empresa</th><th>Deuda / límite</th><th class="text-right">Consumo del mes</th><th class="text-right">Vencido</th><th>Avisos</th><th></th></tr></thead>
            <tbody id="cuerpo_flotas"></tbody>
          </table>
        </div>
      </div>
    </div>

    <div id="flo_detalle" class="d-none">
      <div class="d-flex flex-wrap align-items-center mb-3" style="gap: .5rem;">
        <button type="button" class="btn btn-outline-secondary btn-sm" id="flo_volver"><i class="fas fa-arrow-left" aria-hidden="true"></i> Volver a las flotas</button>
        <h2 class="h5 m-0 ml-2" id="flo_titulo"></h2>
        <span id="flo_estado_badge"></span>
      </div>
      <div class="card card-tabs">
        <div class="card-header p-0 pt-1 border-bottom-0">
          <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item"><a class="nav-link active" data-toggle="pill" href="#tab_flo_cuenta" role="tab">Estado de cuenta</a></li>
            <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#tab_flo_placas" role="tab">Placas autorizadas</a></li>
            <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#tab_flo_datos" role="tab">Datos de crédito</a></li>
          </ul>
        </div>
        <div class="card-body">
          <div class="tab-content">

            <div class="tab-pane fade show active" id="tab_flo_cuenta" role="tabpanel">
              <form id="filtro_flota_mes" class="form-inline flex-wrap mb-3" style="gap: .5rem;">
                <label for="flo_mes">Mes</label><input type="month" class="form-control form-control-sm" id="flo_mes" required>
                <button type="submit" class="btn btn-primary btn-sm">Ver</button>
                <a class="btn btn-outline-secondary btn-sm" id="flo_pdf" href="#" target="_blank" rel="noopener"><i class="fas fa-file-pdf" aria-hidden="true"></i> Estado de cuenta en PDF</a>
              </form>
              <div class="row" id="flo_totales"></div>
              <h3 class="h6 mt-2">Vales del mes</h3>
              <div class="table-responsive">
                <table class="table table-sm table-striped">
                  <thead><tr><th>Fecha</th><th>Vale</th><th>Placa</th><th>Turno</th><th class="text-right">Monto</th><th class="text-right">Saldo</th><th>Estado</th></tr></thead>
                  <tbody id="cuerpo_flota_vales"></tbody>
                </table>
              </div>
              <div class="row">
                <div class="col-lg-6">
                  <h3 class="h6 mt-3">Consumo por placa</h3>
                  <table class="table table-sm"><thead><tr><th>Placa</th><th>Vales</th><th class="text-right">Total</th></tr></thead><tbody id="cuerpo_flota_placas_consumo"></tbody></table>
                </div>
                <div class="col-lg-6">
                  <h3 class="h6 mt-3">Pagos recibidos en el mes</h3>
                  <table class="table table-sm"><thead><tr><th>Fecha</th><th>Medio</th><th>Vale</th><th class="text-right">Monto</th></tr></thead><tbody id="cuerpo_flota_pagos"></tbody></table>
                </div>
              </div>
            </div>

            <div class="tab-pane fade" id="tab_flo_placas" role="tabpanel">
              <form id="form_placa" class="row align-items-end">
                <input type="hidden" id="pla_id">
                <div class="col-sm-6 col-lg-2 form-group"><label for="pla_placa">Placa</label><input class="form-control text-uppercase" id="pla_placa" maxlength="10" required placeholder="ABC-123"></div>
                <div class="col-sm-6 col-lg-3 form-group"><label for="pla_vehiculo">Vehículo <span class="text-muted font-weight-normal">(opcional)</span></label><input class="form-control" id="pla_vehiculo" maxlength="80" placeholder="Volvo FH, tracto"></div>
                <div class="col-sm-6 col-lg-3 form-group"><label for="pla_conductor">Conductor <span class="text-muted font-weight-normal">(opcional)</span></label><input class="form-control" id="pla_conductor" maxlength="120"></div>
                <div class="col-sm-6 col-lg-1 form-group"><label for="pla_activo">Activa</label><select class="form-control" id="pla_activo"><option value="1">Sí</option><option value="0">No</option></select></div>
                <div class="col-lg-3 form-group"><button type="submit" class="btn btn-primary" id="pla_guardar">Agregar placa</button> <button type="button" class="btn btn-link d-none" id="pla_cancelar">Cancelar</button></div>
              </form>
              <div class="table-responsive">
                <table class="table table-sm table-striped">
                  <thead><tr><th>Placa</th><th>Vehículo</th><th>Conductor</th><th>Estado</th><th></th></tr></thead>
                  <tbody id="cuerpo_flota_placas"></tbody>
                </table>
              </div>
            </div>

            <div class="tab-pane fade" id="tab_flo_datos" role="tabpanel">
              <form id="form_flota_datos" class="row">
                <div class="col-sm-6 col-lg-3 form-group"><label for="dat_limite">Límite de crédito (S/)</label><input type="number" step="0.01" min="0" class="form-control" id="dat_limite" placeholder="Sin límite"></div>
                <div class="col-sm-6 col-lg-2 form-group"><label for="dat_dias">Días de crédito</label><input type="number" min="0" max="180" class="form-control" id="dat_dias" required></div>
                <div class="col-sm-6 col-lg-3 form-group"><label for="dat_estado">Estado</label><select class="form-control" id="dat_estado"><option value="ACTIVA">Activa</option><option value="SUSPENDIDA">Suspendida</option></select></div>
                <div class="col-sm-6 col-lg-4 form-group"><label for="dat_contacto">Contacto</label><input class="form-control" id="dat_contacto" maxlength="120"></div>
                <div class="col-sm-6 col-lg-3 form-group"><label for="dat_telefono">Teléfono</label><input class="form-control" id="dat_telefono" inputmode="tel"></div>
                <div class="col-sm-6 col-lg-4 form-group"><label for="dat_correo">Correo</label><input type="email" class="form-control" id="dat_correo"></div>
                <div class="col-lg-5 form-group"><label for="dat_observaciones">Observaciones</label><input class="form-control" id="dat_observaciones" maxlength="400"></div>
                <div class="col-12 form-group">
                  <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="dat_solo_placas">
                    <label class="custom-control-label" for="dat_solo_placas">Avisar cuando un vale use una placa no autorizada</label>
                  </div>
                </div>
                <div class="col-12"><button type="submit" class="btn btn-primary"><i class="fas fa-save" aria-hidden="true"></i> Guardar datos</button></div>
              </form>
            </div>

          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modal_flota" tabindex="-1" role="dialog" aria-labelledby="modal_flota_titulo" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <form class="modal-content" id="form_flota_nueva">
      <div class="modal-header"><h5 class="modal-title" id="modal_flota_titulo">Nueva flota</h5><button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button></div>
      <div class="modal-body row">
        <div class="col-12 form-group">
          <div class="custom-control custom-radio custom-control-inline"><input type="radio" class="custom-control-input" id="nf_tipo_cliente" name="nf_tipo" value="cliente" checked><label class="custom-control-label" for="nf_tipo_cliente">Un cliente que ya existe</label></div>
          <div class="custom-control custom-radio custom-control-inline"><input type="radio" class="custom-control-input" id="nf_tipo_nueva" name="nf_tipo" value="nueva"><label class="custom-control-label" for="nf_tipo_nueva">Una empresa nueva</label></div>
        </div>
        <div class="col-12 form-group nf-cliente"><label for="nf_cliente">Cliente</label><select class="form-control" id="nf_cliente"></select></div>
        <div class="col-md-8 form-group nf-nueva d-none"><label for="nf_nombre">Razón social</label><input class="form-control" id="nf_nombre" maxlength="200"></div>
        <div class="col-md-4 form-group nf-nueva d-none"><label for="nf_ruc">RUC</label><input class="form-control" id="nf_ruc" maxlength="11" inputmode="numeric"></div>
        <div class="col-md-4 form-group"><label for="nf_limite">Límite de crédito (S/)</label><input type="number" step="0.01" min="0" class="form-control" id="nf_limite" placeholder="Sin límite"></div>
        <div class="col-md-4 form-group"><label for="nf_dias">Días de crédito</label><input type="number" min="0" max="180" class="form-control" id="nf_dias" value="30" required></div>
        <div class="col-md-4 form-group"><label for="nf_contacto">Contacto</label><input class="form-control" id="nf_contacto" maxlength="120"></div>
        <div class="col-md-6 form-group"><label for="nf_telefono">Teléfono</label><input class="form-control" id="nf_telefono" inputmode="tel"></div>
        <div class="col-md-6 form-group"><label for="nf_correo">Correo</label><input type="email" class="form-control" id="nf_correo"></div>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-primary">Crear flota</button></div>
    </form>
  </div>
</div>

<script>iniciar_flotas();</script>
