<?php require_once __DIR__ . '/../../controller/_bootstrap.php'; ?>
<script src="../js/console_administracion.js?rev=<?php echo time(); ?>"></script>

<div class="content-header">
  <div class="container-fluid"><h1 class="m-0"><i class="fas fa-user-shield"></i> <b>ROLES Y PERMISOS</b></h1></div>
</div>

<div class="content">
  <div class="container-fluid">
    <div class="card card-primary card-outline">
      <div class="card-body">
        <button class="btn btn-success btn-sm mb-2" id="btn_nuevo_rol"><i class="fas fa-plus"></i> Nuevo rol</button>
        <div class="table-responsive">
          <table class="table table-sm table-striped">
            <thead><tr><th>Rol</th><th>Descripción</th><th>Usuarios</th><th>Permisos</th><th>Pantalla de inicio</th><th></th></tr></thead>
            <tbody id="cuerpo_roles"></tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modal_rol" tabindex="-1" role="dialog" aria-labelledby="titulo_rol">
  <div class="modal-dialog modal-lg" role="document">
    <form class="modal-content" id="form_rol">
      <div class="modal-header"><h5 class="modal-title" id="titulo_rol">Rol</h5><button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button></div>
      <div class="modal-body">
        <input type="hidden" id="rol_codigo">
        <div class="row">
          <div class="col-md-6 form-group"><label for="rol_nombre">Nombre</label><input class="form-control" id="rol_nombre" maxlength="60" required></div>
          <div class="col-md-6 form-group"><label for="rol_inicio">Pantalla de inicio</label><select class="form-control" id="rol_inicio" required></select></div>
          <div class="col-12 form-group"><label for="rol_descripcion">Descripción</label><input class="form-control" id="rol_descripcion" maxlength="255"></div>
        </div>
        <div id="rol_aviso_admin" class="alert alert-info">El administrador tiene todos los permisos y no se pueden quitar. Solo puedes cambiar su pantalla de inicio.</div>
        <div id="rol_capacidades" class="row"></div>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-primary">Guardar</button></div>
    </form>
  </div>
</div>

<script>iniciar_roles();</script>
