<?php require_once __DIR__ . '/../../controller/_bootstrap.php'; ?>
<script src="../js/console_administracion.js?rev=<?php echo time(); ?>"></script>

<div class="content-header">
  <div class="container-fluid"><h1 class="m-0"><i class="fas fa-bars"></i> <b>EDITAR MENÚ</b></h1></div>
</div>

<div class="content">
  <div class="container-fluid">
    <div class="card card-primary card-outline">
      <div class="card-body">
        <p class="text-muted">Cambia el orden, los nombres y qué ve cada rol. Una persona nunca verá una opción para la que no tiene permiso, aunque aquí esté visible. Los cambios se aplican al recargar la página.</p>
        <div class="table-responsive">
          <table class="table table-sm table-striped">
            <thead><tr><th>Elemento</th><th>Ícono</th><th>Visible para</th><th>Estado</th><th></th></tr></thead>
            <tbody id="cuerpo_menu"></tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modal_menu" tabindex="-1" role="dialog" aria-labelledby="titulo_menu">
  <div class="modal-dialog" role="document">
    <form class="modal-content" id="form_menu">
      <div class="modal-header"><h5 class="modal-title" id="titulo_menu">Editar elemento</h5><button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button></div>
      <div class="modal-body">
        <input type="hidden" id="menu_id">
        <div class="form-group"><label for="menu_titulo">Título</label><input class="form-control" id="menu_titulo" maxlength="60" required></div>
        <div class="form-group" id="grupo_menu_icono"><label for="menu_icono">Ícono (Font Awesome)</label><input class="form-control" id="menu_icono" maxlength="40" placeholder="fas fa-gas-pump"></div>
        <div class="custom-control custom-switch mb-3"><input type="checkbox" class="custom-control-input" id="menu_visible"><label class="custom-control-label" for="menu_visible">Visible</label></div>
        <label class="d-block">Limitar a estos roles (sin marcar: todos los que tengan permiso)</label>
        <div id="menu_roles"></div>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-primary">Guardar</button></div>
    </form>
  </div>
</div>

<script>iniciar_menu();</script>
