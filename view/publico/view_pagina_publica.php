<?php require_once __DIR__ . '/../../controller/_bootstrap.php'; ?>
<script src="../js/console_pagina_publica.js?rev=<?php echo time(); ?>"></script>

<div class="content-header">
  <div class="container-fluid d-flex flex-wrap align-items-center">
    <h1 class="m-0 mr-auto"><i class="fas fa-globe-americas"></i> <b>PÁGINA PÚBLICA</b></h1>
    <a class="btn btn-outline-primary btn-sm" href="../index.php?vista_previa=1" target="_blank" rel="noopener"><i class="fas fa-external-link-alt"></i> Ver la página</a>
  </div>
</div>

<div class="content">
  <div class="container-fluid">
    <div id="pub_estado" class="alert d-none" role="status"></div>

    <div class="card card-primary card-outline card-tabs">
      <div class="card-header p-0 pt-1 border-bottom-0">
        <ul class="nav nav-tabs" role="tablist">
          <li class="nav-item"><a class="nav-link active" data-toggle="pill" href="#tab_pub_datos" role="tab">Datos de la página</a></li>
          <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#tab_pub_servicios" role="tab">Servicios</a></li>
          <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#tab_pub_promos" role="tab">Promociones</a></li>
          <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#tab_pub_solicitudes" role="tab">Solicitudes de flota <span class="badge badge-warning" id="pub_nuevas"></span></a></li>
        </ul>
      </div>
      <div class="card-body">
        <div class="tab-content">

          <div class="tab-pane fade show active" id="tab_pub_datos" role="tabpanel">
            <form id="form_pub_datos" class="row">
              <div class="col-md-6 form-group"><label for="pub_frase">Frase principal</label><input class="form-control" id="pub_frase" name="frase" maxlength="60" required><small class="form-text text-muted">Se pinta en grande en la parte de arriba. Máximo 60 caracteres.</small></div>
              <div class="col-md-6 form-group"><label for="pub_horario">Horario</label><input class="form-control" id="pub_horario" name="horario" maxlength="80"></div>
              <div class="col-12 form-group"><label for="pub_descripcion">Descripción</label><input class="form-control" id="pub_descripcion" name="descripcion" maxlength="220"></div>
              <div class="col-md-6 form-group"><label for="pub_direccion">Dirección</label><input class="form-control" id="pub_direccion" name="direccion" maxlength="160"><small class="form-text text-muted">Si la deja vacía se usa la de Configuración → Empresa.</small></div>
              <div class="col-md-6 form-group"><label for="pub_referencia">Referencia</label><input class="form-control" id="pub_referencia" name="referencia" maxlength="160" placeholder="Frente al paradero, a media cuadra del puente…"></div>
              <div class="col-md-6 form-group"><label for="pub_mapa_url">Enlace del mapa</label><input class="form-control" id="pub_mapa_url" name="mapa_url" type="url" placeholder="https://maps.app.goo.gl/…"><small class="form-text text-muted">Copie el enlace «Compartir» de Google Maps. Sin enlace se busca la dirección.</small></div>
              <div class="col-md-3 form-group"><label for="pub_whatsapp">WhatsApp</label><input class="form-control" id="pub_whatsapp" name="whatsapp" inputmode="tel" placeholder="987654321"></div>
              <div class="col-md-3 form-group"><label for="pub_telefono">Teléfono</label><input class="form-control" id="pub_telefono" name="telefono" inputmode="tel"></div>
              <div class="col-md-4 form-group"><label for="pub_facebook">Facebook</label><input class="form-control" id="pub_facebook" name="facebook" type="url" placeholder="https://facebook.com/…"></div>
              <div class="col-md-4 form-group"><label for="pub_instagram">Instagram</label><input class="form-control" id="pub_instagram" name="instagram" type="url" placeholder="https://instagram.com/…"></div>
              <div class="col-md-4 form-group"><label for="pub_tiktok">TikTok</label><input class="form-control" id="pub_tiktok" name="tiktok" type="url" placeholder="https://tiktok.com/@…"></div>
              <div class="col-md-6 form-group"><label for="pub_flota_titulo">Título de la sección de flotas</label><input class="form-control" id="pub_flota_titulo" name="flota_titulo" maxlength="60"></div>
              <div class="col-md-6 form-group"><label for="pub_flota_texto">Texto de la sección de flotas</label><input class="form-control" id="pub_flota_texto" name="flota_texto" maxlength="220"></div>
              <div class="col-12 form-group">
                <div class="custom-control custom-switch">
                  <input type="checkbox" class="custom-control-input" id="pub_mostrar_precios" name="mostrar_precios" value="1">
                  <label class="custom-control-label" for="pub_mostrar_precios">Mostrar los precios de combustible (salen de Productos, con su cambio del día)</label>
                </div>
              </div>
              <div class="col-12"><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar datos</button></div>
            </form>
          </div>

          <div class="tab-pane fade" id="tab_pub_servicios" role="tabpanel">
            <form id="form_pub_servicio" class="row align-items-end">
              <input type="hidden" id="srv_id">
              <div class="col-md-3 form-group"><label for="srv_titulo">Servicio</label><input class="form-control" id="srv_titulo" maxlength="80" required></div>
              <div class="col-md-4 form-group"><label for="srv_descripcion">Descripción</label><input class="form-control" id="srv_descripcion" maxlength="255"></div>
              <div class="col-md-2 form-group"><label for="srv_icono">Icono</label><select class="form-control" id="srv_icono" required></select></div>
              <div class="col-md-1 form-group"><label for="srv_activo">Visible</label><select class="form-control" id="srv_activo"><option value="1">Sí</option><option value="0">No</option></select></div>
              <div class="col-md-2 form-group"><button type="submit" class="btn btn-primary btn-block" id="srv_guardar">Agregar</button><button type="button" class="btn btn-link btn-block d-none" id="srv_cancelar">Cancelar edición</button></div>
            </form>
            <div class="table-responsive">
              <table class="table table-sm table-striped">
                <thead><tr><th>Icono</th><th>Servicio</th><th>Descripción</th><th>Visible</th><th></th></tr></thead>
                <tbody id="cuerpo_pub_servicios"></tbody>
              </table>
            </div>
          </div>

          <div class="tab-pane fade" id="tab_pub_promos" role="tabpanel">
            <form id="form_pub_promo" class="row align-items-end">
              <input type="hidden" id="promo_id">
              <div class="col-md-3 form-group"><label for="promo_titulo">Título</label><input class="form-control" id="promo_titulo" maxlength="100" required></div>
              <div class="col-md-4 form-group"><label for="promo_detalle">Detalle</label><input class="form-control" id="promo_detalle" maxlength="400"></div>
              <div class="col-md-2 form-group"><label for="promo_desde">Desde</label><input type="date" class="form-control" id="promo_desde" required></div>
              <div class="col-md-2 form-group"><label for="promo_hasta">Hasta <small class="text-muted">(opcional)</small></label><input type="date" class="form-control" id="promo_hasta"></div>
              <div class="col-md-1 form-group"><label for="promo_activo">Activa</label><select class="form-control" id="promo_activo"><option value="1">Sí</option><option value="0">No</option></select></div>
              <div class="col-12 form-group"><button type="submit" class="btn btn-primary" id="promo_guardar">Agregar promoción</button> <button type="button" class="btn btn-link d-none" id="promo_cancelar">Cancelar edición</button></div>
            </form>
            <div class="table-responsive">
              <table class="table table-sm table-striped">
                <thead><tr><th>Título</th><th>Vigencia</th><th>Estado</th><th></th></tr></thead>
                <tbody id="cuerpo_pub_promos"></tbody>
              </table>
            </div>
          </div>

          <div class="tab-pane fade" id="tab_pub_solicitudes" role="tabpanel">
            <form id="filtro_solicitudes" class="form-inline mb-3">
              <label class="mr-2" for="sol_estado">Estado</label>
              <select class="form-control form-control-sm mr-2" id="sol_estado">
                <option value="NUEVA">Nuevas</option><option value="EN_PROCESO">En proceso</option><option value="ATENDIDA">Atendidas</option><option value="DESCARTADA">Descartadas</option><option value="">Todas</option>
              </select>
              <button class="btn btn-primary btn-sm" type="submit">Consultar</button>
            </form>
            <div class="table-responsive">
              <table class="table table-sm table-striped">
                <thead><tr><th>Fecha</th><th>Empresa</th><th>Contacto</th><th>Flota</th><th>Mensaje</th><th>Estado</th><th></th></tr></thead>
                <tbody id="cuerpo_pub_solicitudes"></tbody>
              </table>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>

<script>iniciar_pagina_publica();</script>
