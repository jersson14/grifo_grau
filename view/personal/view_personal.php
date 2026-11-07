<?php require_once __DIR__ . '/../../controller/_bootstrap.php'; ?>
<script src="../js/console_personal.js?rev=<?php echo time(); ?>"></script>

<div class="content-header">
  <div class="container-fluid"><h1 class="m-0"><i class="fas fa-id-badge"></i> <b>PERSONAL</b></h1></div>
</div>

<div class="content">
  <div class="container-fluid">
    <div class="card card-tabs">
      <div class="card-header p-0 pt-1 border-bottom-0">
        <ul class="nav nav-tabs flex-nowrap tw-overflow-x-auto" role="tablist">
          <li class="nav-item"><a class="nav-link active text-nowrap" data-toggle="pill" href="#tab_per_fichas" role="tab">Personal</a></li>
          <li class="nav-item"><a class="nav-link text-nowrap" data-toggle="pill" href="#tab_per_horario" role="tab">Horario semanal</a></li>
          <li class="nav-item"><a class="nav-link text-nowrap" data-toggle="pill" href="#tab_per_asistencia" role="tab">Asistencia</a></li>
          <li class="nav-item"><a class="nav-link text-nowrap" data-toggle="pill" href="#tab_per_movimientos" role="tab">Adelantos y descuentos <span class="badge badge-warning d-none" id="per_faltantes_cuenta"></span></a></li>
          <li class="nav-item"><a class="nav-link text-nowrap" data-toggle="pill" href="#tab_per_resumen" role="tab">Resumen del mes</a></li>
        </ul>
      </div>
      <div class="card-body">
        <div class="tab-content">

          <div class="tab-pane fade show active" id="tab_per_fichas" role="tabpanel">
            <form id="form_personal" class="row align-items-end">
              <input type="hidden" id="per_id">
              <div class="col-lg-4 form-group"><label for="per_nombres">Nombres y apellidos</label><input class="form-control" id="per_nombres" maxlength="150" required autocomplete="off"></div>
              <div class="col-sm-6 col-lg-2 form-group"><label for="per_dni">DNI <span class="text-muted font-weight-normal">(opcional)</span></label><input class="form-control" id="per_dni" maxlength="12" inputmode="numeric" autocomplete="off"></div>
              <div class="col-sm-6 col-lg-3 form-group"><label for="per_cargo">Cargo</label><select class="form-control" id="per_cargo" required></select></div>
              <div class="col-sm-6 col-lg-3 form-group"><label for="per_telefono">Teléfono <span class="text-muted font-weight-normal">(opcional)</span></label><input class="form-control" id="per_telefono" maxlength="20" inputmode="tel"></div>
              <div class="col-sm-6 col-lg-2 form-group"><label for="per_ingreso">Fecha de ingreso</label><input type="date" class="form-control" id="per_ingreso"></div>
              <div class="col-sm-6 col-lg-2 form-group"><label for="per_sueldo">Sueldo mensual (S/)</label><input type="number" step="0.01" min="0" class="form-control" id="per_sueldo" inputmode="decimal"></div>
              <div class="col-sm-6 col-lg-3 form-group">
                <label for="per_usuario">Usuario del sistema</label>
                <select class="form-control" id="per_usuario" aria-describedby="per_usuario_ayuda"></select>
                <small id="per_usuario_ayuda" class="form-text text-muted">Vincúlelo para ver sus turnos y descontarle los faltantes de caja.</small>
              </div>
              <div class="col-sm-6 col-lg-2 form-group"><label for="per_activo">Estado</label><select class="form-control" id="per_activo"><option value="1">Activo</option><option value="0">Cesado</option></select></div>
              <div class="col-lg-3 form-group"><label for="per_obs">Observaciones</label><input class="form-control" id="per_obs" maxlength="300"></div>
              <div class="col-12 form-group">
                <button type="submit" class="btn btn-primary" id="per_guardar"><i class="fas fa-plus" aria-hidden="true"></i> Agregar persona</button>
                <button type="button" class="btn btn-link d-none" id="per_cancelar">Cancelar</button>
              </div>
            </form>
            <div class="table-responsive">
              <table class="table table-sm table-striped">
                <thead><tr><th>Nombre</th><th>Cargo</th><th>DNI</th><th>Teléfono</th><th>Usuario</th><th class="text-right">Sueldo</th><th>Estado</th><th></th></tr></thead>
                <tbody id="cuerpo_personal"></tbody>
              </table>
            </div>
          </div>

          <div class="tab-pane fade" id="tab_per_horario" role="tabpanel">
            <div class="d-flex flex-wrap align-items-center mb-3" style="gap: .5rem;">
              <button type="button" class="btn btn-outline-secondary btn-sm" id="hor_anterior" aria-label="Semana anterior"><i class="fas fa-chevron-left" aria-hidden="true"></i></button>
              <strong id="hor_titulo" aria-live="polite"></strong>
              <button type="button" class="btn btn-outline-secondary btn-sm" id="hor_siguiente" aria-label="Semana siguiente"><i class="fas fa-chevron-right" aria-hidden="true"></i></button>
              <button type="button" class="btn btn-outline-secondary btn-sm" id="hor_hoy">Esta semana</button>
              <button type="button" class="btn btn-outline-primary btn-sm ml-auto" id="hor_copiar"><i class="fas fa-copy" aria-hidden="true"></i> Copiar la semana anterior</button>
            </div>
            <p class="text-muted small">Cada cambio se guarda al elegirlo. Copiar la semana anterior solo llena los días que siguen vacíos.</p>
            <div class="table-responsive">
              <table class="table table-sm table-bordered" id="tabla_horario">
                <thead><tr id="cabecera_horario"></tr></thead>
                <tbody id="cuerpo_horario"></tbody>
              </table>
            </div>
          </div>

          <div class="tab-pane fade" id="tab_per_asistencia" role="tabpanel">
            <div class="form-inline mb-3" style="gap: .5rem;">
              <label for="asi_fecha">Día</label><input type="date" class="form-control form-control-sm" id="asi_fecha">
            </div>
            <p class="text-muted small">«En el sistema» indica si el usuario vinculado abrió un turno ese día.</p>
            <div class="table-responsive">
              <table class="table table-sm">
                <thead><tr><th>Persona</th><th>Programado</th><th>En el sistema</th><th>Asistencia</th><th>Entrada</th><th>Salida</th><th>Observación</th><th></th></tr></thead>
                <tbody id="cuerpo_asistencia"></tbody>
              </table>
            </div>
          </div>

          <div class="tab-pane fade" id="tab_per_movimientos" role="tabpanel">
            <div class="callout callout-warning d-none" id="per_faltantes">
              <h5 class="h6 font-weight-bold">Faltantes de caja por descontar</h5>
              <p class="small text-muted mb-2">Arqueos resueltos como «descontado» en Caja que aún no se registraron como descuento.</p>
              <div class="table-responsive">
                <table class="table table-sm mb-0">
                  <thead><tr><th>Turno</th><th>Fecha</th><th>Grifero</th><th class="text-right">Faltante</th><th></th></tr></thead>
                  <tbody id="cuerpo_faltantes"></tbody>
                </table>
              </div>
            </div>
            <form id="form_movimiento" class="row align-items-end">
              <div class="col-sm-6 col-lg-3 form-group"><label for="mov_persona">Persona</label><select class="form-control" id="mov_persona" required></select></div>
              <div class="col-sm-6 col-lg-2 form-group"><label for="mov_tipo">Tipo</label><select class="form-control" id="mov_tipo" required></select></div>
              <div class="col-sm-6 col-lg-2 form-group"><label for="mov_fecha">Fecha</label><input type="date" class="form-control" id="mov_fecha" required></div>
              <div class="col-sm-6 col-lg-2 form-group"><label for="mov_monto">Monto (S/)</label><input type="number" step="0.01" min="0.01" class="form-control" id="mov_monto" required inputmode="decimal"></div>
              <div class="col-lg-3 form-group"><label for="mov_motivo">Motivo</label><input class="form-control" id="mov_motivo" maxlength="200" required placeholder="Adelanto de quincena"></div>
              <div class="col-12 form-group"><button type="submit" class="btn btn-primary"><i class="fas fa-plus" aria-hidden="true"></i> Registrar</button></div>
            </form>
            <div class="form-inline mb-2" style="gap: .5rem;">
              <label for="mov_mes">Mes</label><input type="month" class="form-control form-control-sm" id="mov_mes">
            </div>
            <div class="table-responsive">
              <table class="table table-sm table-striped">
                <thead><tr><th>Fecha</th><th>Persona</th><th>Tipo</th><th>Motivo</th><th class="text-right">Monto</th><th>Estado</th><th></th></tr></thead>
                <tbody id="cuerpo_movimientos"></tbody>
              </table>
            </div>
          </div>

          <div class="tab-pane fade" id="tab_per_resumen" role="tabpanel">
            <div class="form-inline mb-2" style="gap: .5rem;">
              <label for="res_mes">Mes</label><input type="month" class="form-control form-control-sm" id="res_mes">
            </div>
            <p class="text-muted small">Referencia para el pago: sueldo − adelantos − descuentos + bonos. No reemplaza la planilla ni calcula aportes.</p>
            <div class="table-responsive">
              <table class="table table-sm">
                <thead><tr><th>Persona</th><th class="text-right">Programados</th><th class="text-right">Asistió</th><th class="text-right">Tardanzas</th><th class="text-right">Faltas</th><th class="text-right">Permisos</th><th class="text-right">Turnos en sistema</th><th class="text-right">Sueldo</th><th class="text-right">Adelantos</th><th class="text-right">Descuentos</th><th class="text-right">Bonos</th><th class="text-right">Neto estimado</th></tr></thead>
                <tbody id="cuerpo_resumen_personal"></tbody>
                <tfoot id="pie_resumen_personal"></tfoot>
              </table>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>

<script>iniciar_personal();</script>
