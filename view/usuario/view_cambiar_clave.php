<?php
require_once __DIR__ . '/../../controller/_bootstrap.php';
require_once __DIR__ . '/../../model/model_usuario.php';
$obligatorio = !empty($_SESSION['S_CAMBIAR_CLAVE']);
?>
<div class="content-header">
  <div class="container-fluid"><h1 class="m-0"><i class="fas fa-key"></i> <b>CAMBIAR CONTRASEÑA</b></h1></div>
</div>

<div class="content">
  <div class="container-fluid">
    <div class="card mx-auto" style="max-width: 32rem;">
      <div class="card-body">
        <?php if ($obligatorio) { ?>
        <div class="callout callout-warning mb-4">
          <p class="mb-0"><strong>Antes de seguir, cambie su contraseña.</strong> La que usó es de ejemplo, muy común o se la asignó un administrador.</p>
        </div>
        <?php } ?>
        <form id="form_cambiar_clave">
          <div class="form-group">
            <label for="clave_actual">Contraseña actual</label>
            <input type="password" class="form-control" id="clave_actual" autocomplete="current-password" required>
          </div>
          <div class="form-group">
            <label for="clave_nueva">Contraseña nueva</label>
            <input type="password" class="form-control" id="clave_nueva" autocomplete="new-password" minlength="<?php echo Modelo_Usuario::CLAVE_MINIMO; ?>" maxlength="72" required aria-describedby="clave_nueva_ayuda">
            <small id="clave_nueva_ayuda" class="form-text text-muted">Al menos <?php echo Modelo_Usuario::CLAVE_MINIMO; ?> caracteres. Evite su nombre de usuario y claves como 123456.</small>
          </div>
          <div class="form-group">
            <label for="clave_repetir">Repita la contraseña nueva</label>
            <input type="password" class="form-control" id="clave_repetir" autocomplete="new-password" required>
          </div>
          <div class="d-flex flex-wrap align-items-center" style="gap: .5rem;">
            <button type="submit" class="btn btn-primary" id="clave_guardar">Cambiar contraseña</button>
            <?php if ($obligatorio) { ?>
            <a href="../controller/usuario/controlador_cerrar_sesion.php" class="btn btn-link">Salir</a>
            <?php } ?>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
(function () {
  var obligatorio = <?php echo json_encode($obligatorio); ?>;
  $('#clave_repetir, #clave_nueva').on('input', function () {
    var repetir = document.getElementById('clave_repetir');
    repetir.setCustomValidity(repetir.value && repetir.value !== $('#clave_nueva').val() ? 'Las contraseñas no coinciden.' : '');
  });
  $('#form_cambiar_clave').on('submit', function (e) {
    e.preventDefault();
    var boton = $('#clave_guardar').prop('disabled', true);
    G.post('../controller/usuario/controlador_cambiar_contrasena.php', {
      contrasena_actual: $('#clave_actual').val(), contrasena_nueva: $('#clave_nueva').val()
    }, function () {
      $('#form_cambiar_clave')[0].reset();
      // Con el cambio obligatorio hecho, se recarga el panel para mostrar el menú y la pantalla de inicio.
      G.ok('Contraseña actualizada').then(function () { if (obligatorio) { location.reload(); } });
    }).always(function () { boton.prop('disabled', false); });
  });
})();
</script>
