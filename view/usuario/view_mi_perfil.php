<script src="../js/console_usuario.js?rev=<?php echo time(); ?>"></script>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-user-circle"></i> <b>MI PERFIL</b></h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="../view/index.php">Inicio</a></li>
                    <li class="breadcrumb-item active">Mi Perfil</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-4">
                <!-- Tarjeta de Perfil -->
                <div class="card card-primary card-outline">
                    <div class="card-body box-profile">
                        <div class="text-center">
                            <img class="profile-user-img img-fluid img-circle" id="perfil_foto_preview" src="../img/avatar.png" alt="Foto de perfil" style="width: 150px; height: 150px; object-fit: cover;">
                        </div>
                        <h3 class="profile-username text-center" id="perfil_nombre_completo">Cargando...</h3>
                        <p class="text-muted text-center" id="perfil_rol">-</p>
                        
                        <button class="btn btn-primary btn-block" onclick="Abrir_Modal_Cambiar_Foto()">
                            <i class="fas fa-camera"></i> Cambiar Foto
                        </button>
                        <button class="btn btn-warning btn-block" onclick="Abrir_Modal_Cambiar_Contrasena()">
                            <i class="fas fa-key"></i> Cambiar Contraseña
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <!-- Información Personal -->
                <div class="card card-primary">
                    <div class="card-header" style="background: linear-gradient(135deg, #023D77, #0266C8); color:white">
                        <h3 class="card-title"><i class="fas fa-user"></i> Información Personal</h3>
                    </div>
                    <div class="card-body">
                        <form id="form_actualizar_perfil">
                            <input type="hidden" id="txt_id_usuario">
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><i class="fas fa-id-card"></i> DNI</label>
                                        <input type="text" class="form-control" id="txt_dni" maxlength="8" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><i class="fas fa-user"></i> Usuario</label>
                                        <input type="text" class="form-control" id="txt_usuario" readonly>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><i class="fas fa-user"></i> Nombres *</label>
                                        <input type="text" class="form-control" id="txt_nombre" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><i class="fas fa-user"></i> Apellidos *</label>
                                        <input type="text" class="form-control" id="txt_apellido" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><i class="fas fa-envelope"></i> Email</label>
                                        <input type="email" class="form-control" id="txt_email">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><i class="fas fa-phone"></i> Teléfono</label>
                                        <input type="text" class="form-control" id="txt_telefono" maxlength="11">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label><i class="fas fa-map-marker-alt"></i> Dirección</label>
                                <textarea class="form-control" id="txt_direccion" rows="2"></textarea>
                            </div>

                            <div class="text-right">
                                <button type="button" class="btn btn-success" onclick="Actualizar_Mi_Perfil()">
                                    <i class="fas fa-save"></i> Guardar Cambios
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL CAMBIAR FOTO -->
<div class="modal fade" id="modal_cambiar_foto">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #023D77, #0266C8); color:white">
                <h5 class="modal-title"><i class="fas fa-camera"></i> Cambiar Foto de Perfil</h5>
                <button type="button" class="close" data-dismiss="modal" style="color:white">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group text-center">
                    <img id="preview_nueva_foto" src="../img/avatar.png" class="img-fluid img-circle" style="width: 200px; height: 200px; object-fit: cover; margin-bottom: 15px;">
                </div>
                <div class="form-group">
                    <label>Seleccionar Foto</label>
                    <input type="file" class="form-control-file" id="txt_foto" accept="image/*" onchange="Preview_Foto(event)">
                    <small class="form-text text-muted">Formatos permitidos: JPG, PNG. Tamaño máximo: 2MB</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="Cambiar_Foto_Perfil()"><i class="fas fa-save"></i> Guardar Foto</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL CAMBIAR CONTRASEÑA -->
<div class="modal fade" id="modal_cambiar_contrasena">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #ffc107, #ff9800); color:white">
                <h5 class="modal-title"><i class="fas fa-key"></i> Cambiar Contraseña</h5>
                <button type="button" class="close" data-dismiss="modal" style="color:white">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Contraseña Actual *</label>
                    <input type="password" class="form-control" id="txt_contrasena_actual" required>
                </div>
                <div class="form-group">
                    <label>Nueva Contraseña *</label>
                    <input type="password" class="form-control" id="txt_contrasena_nueva" required>
                    <small class="form-text text-muted">Mínimo 6 caracteres</small>
                </div>
                <div class="form-group">
                    <label>Confirmar Nueva Contraseña *</label>
                    <input type="password" class="form-control" id="txt_contrasena_confirmar" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> Cancelar</button>
                <button type="button" class="btn btn-warning" onclick="Cambiar_Contrasena()"><i class="fas fa-key"></i> Cambiar Contraseña</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    Cargar_Datos_Perfil();
});
</script>
