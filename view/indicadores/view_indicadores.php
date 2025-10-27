<script src="../js/console_indicadores.js?rev=<?php echo time(); ?>"></script>

<!-- Content Header (Page header) -->
<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0"><b>MANTENIMIENTO DE INDICADORES</b></h1>
      </div><!-- /.col -->
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="../index.php">MENU</a></li>
          <li class="breadcrumb-item active">INDICADORES</li>
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
            <h3 class="card-title"><i class="fas fa-file"></i>&nbsp;&nbsp;<b>Listado de Indicadores</b>&nbsp;&nbsp;</h3>
            <button class="btn btn-success float-right" onclick="AbrirRegistro()"><i class="fas fa-plus"></i> Nuevo Registro</button>
          </div>
          <div class="table-responsive" style="text-align:center">
            <div class="card-body">
              <table id="tabla_indicadores" class="table table-striped table-bordered" style="width:100%">
                <thead style="background-color:#0A5D86;color:#FFFFFF;">
                  <tr>
                    <th style="text-align:center">Nro.</th>
                    <th style="text-align:center">Tipo de Indicador</th>
                    <th style="text-align:center">Indicador</th>
                    <th style="text-align:center">Descripción</th>
                    <th style="text-align:center">Fecha de Registro</th>
                    <th style="text-align:center">Fecha de Actualización</th>
                    <th style="text-align:center">Ultimo Usuario en Actualizar</th>
                    <th style="text-align:center">Estado</th>
                    <th style="text-align:center">Acción</th>
                  </tr>
                </thead>
              </table>
            </div>
          </div>
        </div>
        <!-- /.col-md-6 -->
      </div>
      <!-- /.row -->
    </div><!-- /.container-fluid -->
  </div>
  <!-- /.content -->
  <!-- Modal -->
  <div class="modal fade" id="modal_registro" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header" style="background-color:#1FA0E0;">
          <h5 class="modal-title" id="exampleModalLabel" style="color:white; text-align:center"><b>REGISTRO DE INDICADOR</b></h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-12 form-group" style="color:red">
              <h6><b>Campos Obligatorios (*)</b></h6>
            </div>
            <div class="col-12 form-group">
              <label for="">Tipo de Indicador<b style="color:red">(*)</b>:</label>
              <select name="" id="txt_tipo_indi" class="form-control">
                <option value="" disabled selected>Seleccione</option>
                <option value="INGRESOS">INGRESOS</option>
                <option value="GASTO">GASTOS</option>
              </select>
            </div>
            <div class="col-12 form-group">
              <label for="">Indicador<b style="color:red">(*)</b>:</label>
              <input type="text" class="form-control" id="txt_indicador" placeholder="Ingrese el indicador" onkeypress="return sololetras(event)">
            </div>
            <div class="col-12 form-group">
              <label for="">Descripción(Opcional):</label>
              <textarea name="" id="txt_descripcion" placeholder="Ingrese la descripción" rows="3" class="form-control" style="resize:none;"></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fas fa-times ml-1"></i> Cerrar</button>
          <button type="button" class="btn btn-success" onclick="Registrar_Indicador()"><i class="fas fa-save"></i> Registrar</button>
        </div>
      </div>
    </div>
  </div>
  <div class="modal fade" id="modal_editar" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header" style="background-color:#1FA0E0;">
          <h5 class="modal-title" id="exampleModalLabel" style="color:white; text-align:center"><b>EDITAR DATOS DE INDICADOR   </b></h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-12 form-group" style="color:red">
              <h6><b>Campos Obligatorios (*)</b></h6>
            </div>
            <div class="col-12 form-group">
              <label for="">Tipo de Indicador<b style="color:red">(*)</b>:</label>
              <input type="text" id="txt_id_indi" hidden>
              <select name="" id="txt_tipo_indi_editar" class="form-control">
                <option value="" disabled selected>Seleccione</option>
                <option value="INGRESOS">INGRESOS</option>
                <option value="GASTO">GASTOS</option>
              </select>
            </div>
            <div class="col-12 form-group">
              <label for="">Indicador<b style="color:red">(*)</b>:</label>
              <input type="text" class="form-control" id="txt_indicador_editar" onkeypress="return sololetras(event)">
            </div>
            <div class="col-12 form-group">
              <label for="">Descripción(Opcional):</label>
              <textarea name="" id="txt_descripcion_editar" rows="3" class="form-control" style="resize:none;"></textarea>
            </div>
            <div class="col-12 form-group">
              <label for="">Estado<b style="color:red">(*)</b>:</label>
              <select name="" id="txt_status" class="form-control">
                <option value="" disabled selected>Seleccione</option>
                <option value="ACTIVO">ACTIVO</option>
                <option value="INACTIVO">INACTIVO</option>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fas fa-times ml-1"></i> Cerrar</button>
          <button type="button" class="btn btn-success" onclick="Modificar_Indicador()"><i class="fas fa-edit"></i> Modificar</button>
        </div>
      </div>
    </div>
  </div>
  <script>
    $(document).ready(function() {
      listar_indicadores();
    });
    $('#modal_registro').on('shown.bs.modal', function() {
      $('#txt_tipo').trigger('focus')
    })
  </script>