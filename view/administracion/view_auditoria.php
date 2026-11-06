<?php require_once __DIR__ . '/../../controller/_bootstrap.php'; ?>
<script src="../js/console_administracion.js?rev=<?php echo time(); ?>"></script>

<div class="content-header">
  <div class="container-fluid"><h1 class="m-0"><i class="fas fa-clipboard-list"></i> <b>AUDITORÍA</b></h1></div>
</div>

<div class="content">
  <div class="container-fluid">
    <div class="card card-primary card-outline">
      <div class="card-header">
        <form id="filtro_auditoria" class="form-inline">
          <label class="mr-2" for="aud_desde">Desde</label><input type="date" class="form-control form-control-sm mr-2" id="aud_desde">
          <label class="mr-2" for="aud_hasta">Hasta</label><input type="date" class="form-control form-control-sm mr-2" id="aud_hasta">
          <label class="mr-2" for="aud_usuario">Usuario</label><select class="form-control form-control-sm mr-2" id="aud_usuario"></select>
          <label class="mr-2" for="aud_texto">Buscar</label><input class="form-control form-control-sm mr-2" id="aud_texto" maxlength="60">
          <button class="btn btn-primary btn-sm" type="submit">Consultar</button>
        </form>
      </div>
      <div class="card-body table-responsive">
        <p class="text-muted">Muestra las 500 acciones más recientes del filtro. Registra quién hizo qué y con qué datos; las contraseñas y claves nunca se guardan.</p>
        <table class="table table-sm table-striped">
          <thead><tr><th>Fecha</th><th>Usuario</th><th>Acción</th><th>Detalle</th><th>IP</th></tr></thead>
          <tbody id="cuerpo_auditoria"></tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script>iniciar_auditoria();</script>
