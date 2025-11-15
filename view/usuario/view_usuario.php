<script src="../js/console_usuario.js?rev=<?php echo time();?>"></script>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-users"></i> <b>GESTIÓN DE USUARIOS</b></h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="../view/index.php">Inicio</a></li>
                    <li class="breadcrumb-item active">Usuarios</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header" style="background: linear-gradient(135deg, #023D77, #0266C8)">
                        <h3 class="card-title" style="color:white"><i class="fas fa-list"></i> Listado de Usuarios</h3>
                        <div class="card-tools">
                            <button class="btn btn-success btn-sm" onclick="AbrirRegistro()">
                                <i class="fas fa-plus"></i> Nuevo Usuario
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <table id="tabla_usuario" class="table table-striped table-bordered table-hover" style="width:100%">
                            <thead style="background-color:#023D77; color:white">
                                <tr>
                                    <th>Nro.</th>
                                    <th>DNI</th>
                                    <th>Foto</th>
                                    <th>Nombre y Apellidos</th>
                                    <th>Usuario</th>
                                    <th>Email</th>
                                    <th>Teléfono</th>
                                    <th>Rol</th>
                                    <th>Fecha Registro</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- MODAL REGISTRAR -->
<div class="modal fade" id="modal_registro" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #023D77, #0266C8); color:white">
                <h5 class="modal-title"><i class="fas fa-plus-circle"></i> Registrar Usuario</h5>
                <button type="button" class="close" data-dismiss="modal" style="color:white">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12 form-group">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Los campos marcados con <span class="text-danger">*</span> son obligatorios
                        </div>
                    </div>
                    
                    <div class="col-md-4 form-group">
                        <label>DNI <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="txt_dni" placeholder="DNI del usuario" maxlength="8">
                            <div class="input-group-append">
                                <button class="btn btn-info" type="button" onclick="Buscar_DNI_Usuario()" title="Buscar en RENIEC">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 form-group">
                        <label>Nombres <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="txt_nomb" placeholder="Nombres">
                    </div>
                    <div class="col-md-4 form-group">
                        <label>Apellidos <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="txt_apelli" placeholder="Apellidos">
                    </div>
                    <div class="col-md-4 form-group">
                        <label>Correo Electrónico <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="txt_correo" placeholder="correo@ejemplo.com">
                    </div>
                    <div class="col-md-4 form-group">
                        <label>Teléfono</label>
                        <input type="text" class="form-control" id="txt_tele" placeholder="Teléfono o celular" maxlength="15">
                    </div>
                    <div class="col-md-4 form-group">
                        <label>Dirección <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="txt_direc" placeholder="Dirección">
                    </div>
                    
                    <div class="col-md-6 form-group">
                        <label>Foto del Usuario</label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="txt_foto" accept="image/*" onchange="previewImage(event)">
                            <label class="custom-file-label" for="txt_foto" id="label_txt_foto">Seleccione foto...</label>
                        </div>
                        <button type="button" class="btn btn-danger btn-sm mt-2" onclick="clearPhoto()">
                            <i class="fas fa-times"></i> Limpiar Foto
                        </button>
                    </div>
                    <div class="col-md-6 form-group text-center">
                        <label>Vista Previa</label>
                        <div style="border: 2px dashed #ccc; padding: 10px; min-height: 150px; display: flex; align-items: center; justify-content: center;">
                            <img id="preview" src="../img/vacio.png" alt="Vista previa" style="max-width: 100%; max-height: 150px;">
                        </div>
                    </div>

                    <div class="col-12">
                        <hr>
                        <h5 class="text-center" style="background-color:#023D77; color:white; padding:10px; border-radius:5px;">
                            <i class="fas fa-key"></i> DATOS DE ACCESO AL SISTEMA
                        </h5>
                    </div>
                    
                    <div class="col-md-4 form-group">
                        <label>Usuario <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="txt_usu" placeholder="Nombre de usuario">
                    </div>
                    <div class="col-md-4 form-group">
                        <label>Contraseña <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="txt_contra" placeholder="Contraseña">
                    </div>
                    <div class="col-md-4 form-group">
                        <label>Rol <span class="text-danger">*</span></label>
                        <select class="form-control" id="select_rol_editar">
                            <option value="">-- Seleccione --</option>
                            <option value="ADMINISTRADOR">ADMINISTRADOR</option>
                            <option value="GRIFERO">GRIFERO</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> Cerrar</button>
                <button type="button" class="btn btn-primary" onclick="Registrar_Usuario()"><i class="fas fa-save"></i> Registrar</button>
            </div>
    </div>
  </div>
</div>



<!-- MODAL EDITAR -->
<div class="modal fade" id="modal_editar" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #FFA500, #FF8C00); color:white">
                <h5 class="modal-title"><i class="fas fa-edit"></i> Modificar Usuario</h5>
                <button type="button" class="close" data-dismiss="modal" style="color:white">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12 form-group" style="display:none">
                        <input type="text" id="id_usuario">
                        <input type="text" id="txt_foto_actual">
                    </div>
                    
                    <div class="col-md-4 form-group">
                        <label>DNI <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="txt_dni_editar" maxlength="8">
                            <div class="input-group-append">
                                <button class="btn btn-info" type="button" onclick="Buscar_DNI_Usuario_Editar()" title="Buscar en RENIEC">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 form-group">
                        <label>Nombres <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="txt_nomb_editar">
                    </div>
                    <div class="col-md-4 form-group">
                        <label>Apellidos <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="txt_apelli_editar">
                    </div>
                    <div class="col-md-4 form-group">
                        <label>Correo Electrónico <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="txt_correo_editar">
                    </div>
                    <div class="col-md-4 form-group">
                        <label>Teléfono</label>
                        <input type="text" class="form-control" id="txt_tele_editar" maxlength="15">
                    </div>
                    <div class="col-md-4 form-group">
                        <label>Dirección <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="txt_direc_editar">
                    </div>
                    
                    <div class="col-md-6 form-group">
                        <label>Foto del Usuario</label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="txt_foto_editar" accept="image/*" onchange="previewImage2(event)">
                            <label class="custom-file-label" for="txt_foto_editar" id="label_txt_foto_editar">Seleccione foto...</label>
                        </div>
                        <button type="button" class="btn btn-danger btn-sm mt-2" onclick="clearPhoto2()">
                            <i class="fas fa-times"></i> Limpiar Foto
                        </button>
                    </div>
                    <div class="col-md-6 form-group text-center">
                        <label>Vista Previa</label>
                        <div style="border: 2px dashed #ccc; padding: 10px; min-height: 150px; display: flex; align-items: center; justify-content: center;">
                            <img id="preview2" src="../img/vacio.png" alt="Vista previa" style="max-width: 100%; max-height: 150px;">
                        </div>
                    </div>

                    <div class="col-12">
                        <hr>
                        <h5 class="text-center" style="background-color:#FFA500; color:white; padding:10px; border-radius:5px;">
                            <i class="fas fa-key"></i> DATOS DE ACCESO AL SISTEMA
                        </h5>
                    </div>
                    
                    <div class="col-md-6 form-group">
                        <label>Usuario <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="txt_usu_editar">
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Rol <span class="text-danger">*</span></label>
                        <select class="form-control" id="select_rol_editar2">
                            <option value="">-- Seleccione --</option>
                            <option value="ADMINISTRADOR">ADMINISTRADOR</option>
                            <option value="GRIFERO">GRIFERO</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> Cerrar</button>
                <button type="button" class="btn btn-warning" onclick="Modificar_Usuario()"><i class="fas fa-save"></i> Actualizar</button>
            </div>
        </div>
    </div>
</div>



<!-- MODAL CAMBIAR CONTRASEÑA -->
<div class="modal fade" id="modal_contra" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #17a2b8, #138496); color:white">
                <h5 class="modal-title"><i class="fas fa-key"></i> Cambiar Contraseña</h5>
                <button type="button" class="close" data-dismiss="modal" style="color:white">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12 form-group" style="display:none">
                        <input type="text" id="txt_idusuario_contra">
                    </div>
                    <div class="col-12 form-group">
                        <label>Nueva Contraseña <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="txt_contra_nueva" placeholder="Ingrese la nueva contraseña">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('txt_contra_nueva', this)">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> Cerrar</button>
                <button type="button" class="btn btn-info" onclick="Modificar_Contra()"><i class="fas fa-save"></i> Actualizar</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    listar_usuario();
});

$('#modal_registro').on('shown.bs.modal', function() {
    $('#txt_dni').trigger('focus');
});

$('#modal_contra').on('shown.bs.modal', function() {
    $('#txt_contra_nueva').trigger('focus');
});

function togglePasswordVisibility(inputId, button) {
    const input = document.getElementById(inputId);
    const icon = button.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

function previewImage(event) {
    var input = event.target;
    var preview = document.getElementById('preview');
    var label = document.getElementById('label_txt_foto');

    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function() {
            preview.src = reader.result;
        };
        reader.readAsDataURL(input.files[0]);
        label.textContent = input.files[0].name;
    }
}

function clearPhoto() {
    document.getElementById('txt_foto').value = '';
    document.getElementById('label_txt_foto').textContent = 'Seleccione foto...';
    document.getElementById('preview').src = '../img/vacio.png';
}

function previewImage2(event) {
    var input = event.target;
    var preview2 = document.getElementById('preview2');
    var label = document.getElementById('label_txt_foto_editar');

    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function() {
            preview2.src = reader.result;
        };
        reader.readAsDataURL(input.files[0]);
        label.textContent = input.files[0].name;
    }
}

function clearPhoto2() {
    document.getElementById('txt_foto_editar').value = '';
    document.getElementById('label_txt_foto_editar').textContent = 'Seleccione foto...';
    document.getElementById('preview2').src = '../img/vacio.png';
}
</script>