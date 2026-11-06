<?php require_once __DIR__ . '/../../controller/_bootstrap.php'; ?>
<script src="../js/console_inventario.js?rev=<?php echo time(); ?>"></script>

<div class="content-header">
  <div class="container-fluid"><h1 class="m-0"><i class="fas fa-ruler-vertical"></i> <b>VARILLAJE</b></h1></div>
</div>

<div class="content">
  <div class="container-fluid">
    <div class="card card-primary card-outline">
      <div class="card-header"><h3 class="card-title">Registrar medición</h3></div>
      <div class="card-body">
        <form id="form_varillaje" class="row">
          <div class="col-md-3 form-group"><label for="var_tanque">Tanque</label><select class="form-control" id="var_tanque" required></select></div>
          <div class="col-md-2 form-group"><label for="var_fecha">Fecha</label><input type="date" class="form-control" id="var_fecha" required></div>
          <div class="col-md-2 form-group"><label for="var_medida">Medida (cm)</label><input type="number" step="0.01" min="0" class="form-control" id="var_medida"></div>
          <div class="col-md-2 form-group"><label for="var_galones">Galones medidos</label><input type="number" step="0.001" min="0" class="form-control" id="var_galones" required></div>
          <div class="col-md-3 form-group"><label for="var_obs">Observaciones</label><input class="form-control" id="var_obs" maxlength="255"></div>
          <div class="col-12"><button class="btn btn-primary" type="submit"><i class="fas fa-save"></i> Registrar</button></div>
        </form>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        <form id="filtro_varillaje" class="form-inline">
          <label class="mr-2" for="var_desde">Desde</label><input type="date" class="form-control form-control-sm mr-2" id="var_desde">
          <label class="mr-2" for="var_hasta">Hasta</label><input type="date" class="form-control form-control-sm mr-2" id="var_hasta">
          <button class="btn btn-primary btn-sm" type="submit">Consultar</button>
        </form>
      </div>
      <div class="card-body table-responsive">
        <table class="table table-sm table-striped">
          <thead><tr><th>Fecha</th><th>Tanque</th><th>Medido (gal)</th><th>Teórico (gal)</th><th>Diferencia (gal)</th><th>Usuario</th><th>Observaciones</th><th></th></tr></thead>
          <tbody id="cuerpo_varillajes"></tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script>iniciar_varillaje(<?php echo json_encode(puede('inventario_gestionar')); ?>);</script>
