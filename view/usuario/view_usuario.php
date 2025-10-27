<script src="../js/console_usuario.js?rev=<?php echo time();?>"></script>

<!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0"><b>MANTENIMIENTO DE USUARIOS</b></h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="../index.php">MENU</a></li>
              <li class="breadcrumb-item active">USUARIO</li>
            </ol>
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <div class="content">
      <div class="container-fluid">
        <div class="row">
          <!-- /.col-md-6 -->
          <div class="col-lg-12">
            <div class="card">
              <div class="card-header">
              <h3 class="card-title"><i class="fas fa-user"></i>&nbsp;&nbsp;<b>Listado de Usuarios</b></h3>
                <button class="btn btn-success float-right" onclick="AbrirRegistro()"><i class="fas fa-plus"></i> Nuevo Registro</button>
              </div>
                <div class="table-responsive" style="text-align:center">
                  <div class="card-body">
                    <table id="tabla_usuario" class="table table-striped table-bordered" style="width:100%">
                        <thead style="background-color:#023D77;color:#FFFFFF; ">
                            <tr>
                                <th style="text-align:center">Nro.</th>
                                <th style="text-align:center">DNI</th>
                                <th style="text-align:center">Foto</th>
                                <th style="text-align:center">Nombre y Apellidos</th>
                                <th style="text-align:center">Usuario</th>
                                <th style="text-align:center">Email</th>
                                <th style="text-align:center">Teléfono</th>
                                <th style="text-align:center">Rol</th>
                                <th style="text-align:center">Fecha de registro</th>
                                <th style="text-align:center">Estado</th>
                                <th style="text-align:center">Acción</th>
                            </tr>
                        </thead>
                    </table>
                  </div>
                </div>           
           </div>
          </div>
          <!-- /.col-md-6 -->
        </div>
        <!-- /.row -->
      </div><!-- /.container-fluid -->
<div class="modal fade" id="modal_registro" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header" style="background-color:#1FA0E0;">
        <h5 class="modal-title" id="exampleModalLabel" style="color:white; text-align:center"><b>REGISTRO DE USUARIO</b></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="row">
        <div class="col-12 form-group" style="color:red">
            <h6><b>Campos Obligatorios (*)</b></h6>
            </div>
            <div class="col-4 form-group">
                <label for="">DNI<b style="color:red">(*)</b>:</label>
                <input type="text" class="form-control" id="txt_dni" placeholder="Ingrese el DNI del usuario" onkeypress="return soloNumeros(event)" maxlenght="8">
            </div>
            <div class="col-4 form-group">
                <label for="">Nombres<b style="color:red">(*)</b>:</label>
                <input type="text" class="form-control" id="txt_nomb" placeholder="Ingrese los nombres" onkeypress="return sololetras(event)">
            </div>
            <div class="col-4 form-group">
                <label for="">Apellidos<b style="color:red">(*)</b>:</label>
                <input type="text" class="form-control" id="txt_apelli" placeholder="Ingrese los apellidos" onkeypress="return sololetras(event)">
            </div>
            <div class="col-4 form-group">
                <label for="">Correo electronico<b style="color:red">(*)</b>:</label>
                <input type="email" class="form-control" id="txt_correo" placeholder="Ingrese el correo electronico">
            </div>
            <div class="col-4 form-group">
                <label for="">Teléfono o Celular:</label>
                <input type="text" class="form-control" id="txt_tele" placeholder="Ingrese el teléfono o celular" onkeypress="return soloNumeros(event)">
            </div>
            <div class="col-4 form-group">
                <label for="">Dirección<b style="color:red">(*)</b>:</label>
                <textarea class="form-control" id="txt_direc" rows="2" style="resize:none" placeholder="Ingrese la dirección"></textarea>
            </div>
            <div class="col-6">
                <label for="txt_foto">Subir Foto <b style="color:red">*</b>:</label>
                <div class="custom-file d-flex align-items-center" style="position: relative;">
                    <input type="file" class="custom-file-input" id="txt_foto" accept="image/*" onchange="previewImage(event)">
                    <label class="custom-file-label" for="txt_foto" id="label_txt_foto">Seleccione Foto...</label>
                    <button type="button" class="btn btn-danger btn-sm" 
                            id="btn_clear_foto" onclick="clearPhoto()" 
                            style="position: absolute; right: 80px; top: 50%; transform: translateY(-50%); z-index: 10;">
                        X
                    </button>
                </div>
            </div>

            <div class="col-6" align="center" style="border: 2px solid black; padding: 10px; display: inline-block;">
                <img id="preview" src="#" alt="Vista previa" style="max-width: 100%; max-height: 150px; display: none;">
            </div>


            <div class="col-12"><br>
                <li class="header text-center" style="color:#FFFFFF;background-color:Black;"><b>DATOS DE ACCESO PARA EL SISTEMA</b></li>  
            </div><br>
            <div class="col-4 form-group"><br>
                <label for="">Usuario<b style="color:red">(*)</b>:</label>
                <input type="text" class="form-control" id="txt_usu"  placeholder="Ingrese el usuario">
            </div>
            <div class="col-4 form-group"><br>
                <label for="">Contraseña<b style="color:red">(*)</b>:</label>
                <input type="text" class="form-control" id="txt_contra" placeholder="Ingrese la contraseña">
            </div>
            <div class="col-4 form-group">
              <br>
              <label for="">Rol<b style="color:red">(*)</b>:</label>
              <select class="form-control" id="select_rol_editar" style="width:100%">
                  <option value="">Seleccione</option>
                  <option value="SECRETARIA">SECRETARIA</option>
                  <option value="ADMINISTRADOR">ADMINISTRADOR</option>
              </select>
          </div>

        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fas fa-times ml-1"></i> Cerrar</button>
        <button type="button" class="btn btn-success" onclick="Registrar_Usuario()"><i class="fas fa-save"></i> Registrar</button>
      </div>
    </div>
  </div>
</div>



<div class="modal fade" id="modal_editar" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header" style="background-color:#1FA0E0;">
        <h5 class="modal-title" id="exampleModalLabel" style="color:white; text-align:center"><b>MODIFICAR DE USUARIO</b></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="row">
        <div class="col-12 form-group" style="color:red">
            <h6><b>Campos Obligatorios (*)</b></h6>
            </div>
            <div class="col-4 form-group">
                <label for="">DNI<b style="color:red">(*)</b>:</label>
                <input type="text" id="id_usuario" hidden>
                <input type="text" class="form-control" id="txt_dni_editar" placeholder="Ingrese el DNI del usuario" onkeypress="return soloNumeros(event)" maxlenght="8">
            </div>
            <div class="col-4 form-group">
                <label for="">Nombres<b style="color:red">(*)</b>:</label>
                <input type="text" class="form-control" id="txt_nomb_editar" placeholder="Ingrese los nombres" onkeypress="return sololetras(event)">
            </div>
            <div class="col-4 form-group">
                <label for="">Apellidos<b style="color:red">(*)</b>:</label>
                <input type="text" class="form-control" id="txt_apelli_editar" placeholder="Ingrese los apellidos" onkeypress="return sololetras(event)">
            </div>
            <div class="col-4 form-group">
                <label for="">Correo electronico<b style="color:red">(*)</b>:</label>
                <input type="email" class="form-control" id="txt_correo_editar" placeholder="Ingrese el correo electronico">
            </div>
            <div class="col-4 form-group">
                <label for="">Teléfono o Celular:</label>
                <input type="text" class="form-control" id="txt_tele_editar" placeholder="Ingrese el teléfono o celular" onkeypress="return soloNumeros(event)">
            </div>
            <div class="col-4 form-group">
                <label for="">Dirección<b style="color:red">(*)</b>:</label>
                <textarea class="form-control" id="txt_direc_editar" rows="2" style="resize:none" placeholder="Ingrese la dirección"></textarea>
            </div>
            <div class="col-6">
              <input type="text" id="txt_foto_actual" hidden>

              <label for="txt_foto_editar">Subir Foto <b style="color:red">*</b>:</label>
              <div class="custom-file d-flex align-items-center" style="position: relative;">
                  <input type="file" class="custom-file-input" id="txt_foto_editar" accept="image/*" onchange="previewImage2(event)">
                  <label class="custom-file-label" for="txt_foto_editar" id="label_txt_foto_editar">Seleccione Foto...</label>
                  <button type="button" class="btn btn-danger btn-sm" 
                          id="btn_clear_foto_editar" onclick="clearPhoto2()" 
                          style="position: absolute; right: 80px; top: 50%; transform: translateY(-50%); z-index: 10;">
                      X
                  </button>
              </div>
            </div>

            <div class="col-6 text-center" style="border: 2px solid black; padding: 10px; display: flex; justify-content: center; align-items: center;">
              <img id="preview2" src="#" alt="Vista previa" style="max-width: 100%; max-height: 150px; display: none; object-fit: contain;">
            </div>



            <div class="col-12"><br>
                <li class="header text-center" style="color:#FFFFFF;background-color:Black;"><b>DATOS DE ACCESO PARA EL SISTEMA</b></li>  
            </div><br>
            <div class="col-6 form-group"><br>
                <label for="">Usuario<b style="color:red">(*)</b>:</label>
                <input type="text" class="form-control" id="txt_usu_editar"  placeholder="Ingrese el usuario">
            </div>
        
            <div class="col-6 form-group">
              <br>
              <label for="">Rol<b style="color:red">(*)</b>:</label>
              <select class="form-control" id="select_rol_editar2" style="width:100%">
                  <option value="">Seleccione</option>
                  <option value="SECRETARIA">SECRETARIA</option>
                  <option value="ADMINISTRADOR">ADMINISTRADOR</option>
              </select>
          </div>

        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fas fa-times ml-1"></i> Cerrar</button>
        <button type="button" class="btn btn-success" onclick="Modificar_Usuario()"><i class="fas fa-edit"></i> Módificar</button>
      </div>
    </div>
  </div>
</div>



<div class="modal fade" id="modal_contra" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header" style="background-color:#005CA5;">
        <h5 class="modal-title" id="exampleModalLabel" style="color:white; text-align:center"><b>CAMBIAR CONTRASEÑA</b></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-12 form-group" style="color:red">
              <h6><b>Campos Obligatorios (*)</b></h6>
          </div>
          <div class="col-12">
            <input type="text" id="txt_idusuario_contra" hidden>
            <label for="">Contraseña Nueva(*):</label>
            <div class="input-group">
              <input type="password" class="form-control" id="txt_contra_nueva">
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
        <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fas fa-times ml-1"></i> Cerrar</button>
        <button type="button" class="btn btn-success" onclick="Modificar_Contra()"><i class="fas fa-check"></i> Modificar</button>
      </div>
    </div>
  </div>
</div>

    <!-- /.content -->
    <script>
    $(document).ready(function () {
      listar_usuario();
      $('.js-example-basic-single').select2();
      Cargar_Select_Empleado();
      Cargar_Select_Area_Solo();
      
    });
    $('#modal_registro').on('shown.bs.modal', function () {
      $('#txt_usu').trigger('focus')
    })
    $('#modal_contra').on('shown.bs.modal', function () {
      $('#txt_contra_nueva').trigger('focus')
    })
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
  var input=  document.getElementById('txt_dni');
input.addEventListener('input',function(){
  if (this.value.length > 8) 
  this.value = this.value.slice(0,8); 
})

var input=  document.getElementById('txt_dni_editar');
input.addEventListener('input',function(){
  if (this.value.length > 8) 
  this.value = this.value.slice(0,8); 
})
    </script>
<script>
function previewImage(event) {
    var input = event.target;
    var preview = document.getElementById('preview');
    var label = input.nextElementSibling;

    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function () {
            preview.src = reader.result;
            preview.style.display = 'block'; // Mostrar la vista previa
        };
        reader.readAsDataURL(input.files[0]);

        // Actualizar el label con el nombre del archivo seleccionado
        var fileName = input.files[0].name;
        label.innerHTML = "Subir Foto (" + fileName + ")";
    }
}

function clearPhoto() {
    var fileInput = document.getElementById('txt_foto');
    var fileLabel = document.getElementById('label_txt_foto');
    var preview = document.getElementById('preview');

    // Limpiar el input de archivo
    fileInput.value = ''; // Elimina el archivo seleccionado

    // Restablecer el texto del label
    fileLabel.innerHTML = "Seleccione Foto...";

    // Ocultar la vista previa
    preview.src = '#';
    preview.style.display = 'none';
}

</script>

<script>
function previewImage2(event) {
    const input = event.target;
    const preview2 = document.getElementById('preview2');
    const label = document.getElementById('label_txt_foto_editar');

    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function (e) {
            preview2.src = e.target.result; // Cargar la imagen en el src del preview
            preview2.style.display = 'block'; // Mostrar la vista previa
        };
        reader.readAsDataURL(input.files[0]);

        // Actualizar el label con el nombre del archivo seleccionado
        label.textContent = `Subir Foto (${input.files[0].name})`;
    } else {
        clearPhoto2(); // Si no hay archivo seleccionado, limpiar la vista previa
    }
}

function clearPhoto2() {
    const fileInput = document.getElementById('txt_foto_editar');
    const fileLabel = document.getElementById('label_txt_foto_editar');
    const preview2 = document.getElementById('preview2');

    // Limpiar el input de archivo
    fileInput.value = ''; // Elimina el archivo seleccionado

    // Restablecer el texto del label
    fileLabel.textContent = "Seleccione Foto...";

    // Ocultar la vista previa
    preview2.src = '#';
    preview2.style.display = 'none';
}

</script>