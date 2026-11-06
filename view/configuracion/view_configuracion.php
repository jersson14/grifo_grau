<?php require_once __DIR__ . '/../../controller/_bootstrap.php'; ?>
<script src="../js/console_configuracion.js?rev=<?php echo time(); ?>"></script>

<div class="content-header">
  <div class="container-fluid">
    <h1 class="m-0"><i class="fas fa-cogs"></i> <b>CONFIGURACIÓN DEL SISTEMA</b></h1>
  </div>
</div>

<div class="content">
  <div class="container-fluid">
    <div class="card card-primary card-outline card-tabs">
      <div class="card-header p-0 pt-1 border-bottom-0">
        <ul class="nav nav-tabs" role="tablist">
          <li class="nav-item"><a class="nav-link active" data-toggle="pill" href="#tab_modulos" role="tab">Módulos</a></li>
          <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#tab_empresa" role="tab">Empresa</a></li>
          <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#tab_operacion" role="tab">Operación e impuestos</a></li>
          <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#tab_inicio" role="tab">Página principal</a></li>
          <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#tab_ia" role="tab">Chatbot de IA</a></li>
          <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#tab_integraciones" role="tab">Integraciones</a></li>
        </ul>
      </div>

      <div class="card-body">
        <div class="tab-content">

          <div class="tab-pane fade show active" id="tab_modulos" role="tabpanel">
            <p class="text-muted">Un módulo desactivado desaparece del menú y sus funciones dejan de responder. El registro de turnos no depende de ninguno de ellos.</p>
            <div id="lista_modulos"></div>
          </div>

          <div class="tab-pane fade" id="tab_empresa" role="tabpanel">
            <form id="form_empresa" class="row">
              <input type="hidden" id="emp_id">
              <div class="col-md-6 form-group"><label for="emp_nombre">Razón social</label><input class="form-control" id="emp_nombre" maxlength="200"></div>
              <div class="col-md-6 form-group"><label for="emp_codigo">RUC</label><input class="form-control" id="emp_codigo" maxlength="30"></div>
              <div class="col-md-4 form-group"><label for="emp_email">Correo</label><input type="email" class="form-control" id="emp_email" maxlength="150"></div>
              <div class="col-md-4 form-group"><label for="emp_telefono">Teléfono</label><input class="form-control" id="emp_telefono" maxlength="30"></div>
              <div class="col-md-4 form-group"><label for="emp_direccion">Dirección</label><input class="form-control" id="emp_direccion" maxlength="255"></div>
              <div class="col-12"><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar empresa</button></div>
            </form>
            <hr>
            <div class="form-group">
              <label for="emp_logo">Logo (JPG, PNG o GIF, hasta 2 MB)</label>
              <div class="d-flex align-items-center">
                <img id="emp_logo_actual" alt="Logo actual" style="max-height:60px" class="mr-3">
                <input type="file" id="emp_logo" accept="image/png,image/jpeg,image/gif">
              </div>
            </div>
          </div>

          <div class="tab-pane fade" id="tab_operacion" role="tabpanel">
            <form class="form_config row">
              <div class="col-md-3 form-group"><label for="igv_porcentaje">IGV (%)</label><input type="number" step="0.01" min="0" max="100" class="form-control" id="igv_porcentaje" name="igv_porcentaje"></div>
              <div class="col-md-3 form-group"><label for="credito_dias">Días de crédito por defecto</label><input type="number" min="0" max="365" class="form-control" id="credito_dias" name="credito_dias"></div>
              <div class="col-md-3 form-group"><label for="turno_tolerancia_soles">Tolerancia de diferencia en caja (S/)</label><input type="number" step="0.01" min="0" class="form-control" id="turno_tolerancia_soles" name="turno_tolerancia_soles"></div>
              <div class="col-md-3 form-group"><label for="moneda_simbolo">Símbolo de moneda</label><input class="form-control" id="moneda_simbolo" name="moneda_simbolo" maxlength="5"></div>
              <div class="col-md-3 form-group"><label for="varillaje_tolerancia_pct">Tolerancia de varillaje (%)</label><input type="number" step="0.01" min="0" max="100" class="form-control" id="varillaje_tolerancia_pct" name="varillaje_tolerancia_pct"></div>
              <div class="col-12"><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar</button></div>
            </form>
          </div>

          <div class="tab-pane fade" id="tab_inicio" role="tabpanel">
            <p class="text-muted">Elige qué avisos ve el administrador en el Dashboard. Los de un módulo apagado no se muestran aunque estén marcados.</p>
            <form class="form_config">
              <div id="lista_alertas" class="mb-3"></div>
              <input type="hidden" id="inicio_alertas" name="inicio_alertas">
              <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar</button>
            </form>
          </div>

          <div class="tab-pane fade" id="tab_ia" role="tabpanel">
            <div class="alert alert-warning">Al activar el chatbot, los datos que responden a cada pregunta (ventas, turnos, clientes con deuda) se envían al proveedor de IA. La activación se hace en la pestaña Módulos.</div>
            <form class="form_config row">
              <div class="col-md-6 form-group">
                <label for="ia_api_key">Clave de API de Anthropic</label>
                <input type="password" class="form-control" id="ia_api_key" name="ia_api_key" autocomplete="off" placeholder="Escribe una clave para reemplazar la actual">
                <small id="ia_api_key_estado" class="form-text text-muted"></small>
              </div>
              <div class="col-md-3 form-group">
                <label for="ia_modelo">Modelo</label>
                <select class="form-control" id="ia_modelo" name="ia_modelo">
                  <option value="claude-opus-5-5">Claude Opus 5.5 (más capaz)</option>
                  <option value="claude-sonnet-5-5">Claude Sonnet 5.5 (equilibrado)</option>
                  <option value="claude-haiku-5-5">Claude Haiku 5.5 (más económico)</option>
                </select>
              </div>
              <div class="col-md-3 form-group">
                <label for="ia_esfuerzo">Esfuerzo</label>
                <select class="form-control" id="ia_esfuerzo" name="ia_esfuerzo">
                  <option value="low">Bajo (más rápido y barato)</option>
                  <option value="medium">Medio</option>
                  <option value="high">Alto</option>
                </select>
              </div>
              <div class="col-md-3 form-group"><label for="ia_limite_mensual_usd">Límite mensual (USD)</label><input type="number" step="1" min="0" class="form-control" id="ia_limite_mensual_usd" name="ia_limite_mensual_usd"></div>
              <div class="col-md-5 form-group">
                <label class="d-block">Roles con acceso</label>
                <div id="lista_ia_roles"></div>
                <input type="hidden" id="ia_roles" name="ia_roles">
              </div>
              <div class="col-12"><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar</button></div>
            </form>
            <hr>
            <h5>Consumo de este mes</h5>
            <div class="row" id="ia_resumen"></div>
            <div class="table-responsive">
              <table class="table table-sm table-striped">
                <thead><tr><th>Fecha</th><th>Usuario</th><th>Pregunta</th><th>Consultas usadas</th><th>Estado</th><th>Costo est. (USD)</th></tr></thead>
                <tbody id="ia_ultimas"></tbody>
              </table>
            </div>
          </div>

          <div class="tab-pane fade" id="tab_integraciones" role="tabpanel">
            <h5>Facturación electrónica (Nubefact)</h5>
            <p class="text-muted">La ruta y el token se obtienen en tu cuenta de Nubefact, opción API. Usa primero los de modo demo. El módulo se activa en la pestaña Módulos.</p>
            <form class="form_config row mb-4">
              <div class="col-md-8 form-group"><label for="factura_ruta">Ruta</label><input class="form-control" id="factura_ruta" name="factura_ruta" autocomplete="off" placeholder="https://api.nubefact.com/api/v1/..."></div>
              <div class="col-md-4 form-group">
                <label for="factura_token">Token</label>
                <input type="password" class="form-control" id="factura_token" name="factura_token" autocomplete="off" placeholder="Escribe un token para reemplazar el actual">
                <small id="factura_token_estado" class="form-text text-muted"></small>
              </div>
              <div class="col-md-2 form-group"><label for="factura_serie_factura">Serie de factura</label><input class="form-control text-uppercase" id="factura_serie_factura" name="factura_serie_factura" maxlength="4"></div>
              <div class="col-md-2 form-group"><label for="factura_serie_boleta">Serie de boleta</label><input class="form-control text-uppercase" id="factura_serie_boleta" name="factura_serie_boleta" maxlength="4"></div>
              <div class="col-md-3 form-group"><label for="factura_unidad_medida">Unidad de medida</label><input class="form-control text-uppercase" id="factura_unidad_medida" name="factura_unidad_medida" maxlength="3"></div>
              <div class="col-md-5 form-group"><label for="factura_boleta_tope_sin_documento">Boleta sin DNI: monto máximo (S/)</label><input type="number" step="0.01" min="0" class="form-control" id="factura_boleta_tope_sin_documento" name="factura_boleta_tope_sin_documento"></div>
              <div class="col-12"><button type="submit" class="btn btn-primary mr-2"><i class="fas fa-save"></i> Guardar</button><button type="button" class="btn btn-outline-primary" id="btn_probar_nubefact">Probar conexión</button></div>
            </form>
            <hr>
            <h5>Consulta DNI/RUC</h5>
            <p class="text-muted">Las credenciales se guardan cifradas y no vuelven a mostrarse.</p>
            <form class="form_config row">
              <div class="col-md-6 form-group">
                <label for="decolecta_token">Token de consulta DNI/RUC (Decolecta)</label>
                <input type="password" class="form-control" id="decolecta_token" name="decolecta_token" autocomplete="off" placeholder="Escribe un token para reemplazar el actual">
                <small id="decolecta_token_estado" class="form-text text-muted"></small>
              </div>
              <div class="col-12"><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar</button></div>
            </form>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>

<script>cargar_configuracion();</script>
