var tbl_indicadores;
function listar_indicadores(){
  tbl_indicadores = $("#tabla_indicadores").DataTable({
      "ordering":false,   
      "bLengthChange":true,
      "searching": { "regex": false },
      "lengthMenu": [ [10, 25, 50, 100, -1], [10, 25, 50, 100, "All"] ],
      "pageLength": 10,
      "destroy":true,
      pagingType: 'full_numbers',
      scrollCollapse: true,
      responsive: true,
      "async": false ,
      "processing": true,
      "ajax":{
          "url":"../controller/indicadores/controlador_listar_indicadores.php",
          type:'POST'
      },
      dom: 'Bfrtip',       
      buttons: [
        {
          extend: 'excelHtml5',
          text: '<i class="fas fa-file-excel"></i> Excel',
          titleAttr: 'Exportar a Excel',
          filename: function() {
            return "LISTA DE INDICADORES";
          },
          title: function() {
            return "LISTA DE INDICADORES";
          },
          className: 'btn btn-excel',
          exportOptions: {
            columns: [1, 2, 3, 4, 5, 6, 7] // Exportar solo hasta la columna 'estado'
          }
        },
        {
          extend: 'pdfHtml5',
          text: '<i class="fas fa-file-pdf"></i> PDF',
          titleAttr: 'Exportar a PDF',
          filename: function() {
            return "LISTA DE INDICADORES";
          },
          title: function() {
            return "LISTA DE INDICADORES";
          },
          className: 'btn btn-pdf',
          exportOptions: {
            columns: [1, 2, 3, 4, 5, 6, 7] // Exportar solo hasta la columna 'estado'
          }
        },
        {
          extend: 'print',
          text: '<i class="fa fa-print"></i> Imprimir',
          titleAttr: 'Imprimir',
          title: function() {
            return "LISTA DE INDICADORES";
          },
          className: 'btn btn-print',
          exportOptions: {
            columns: [1, 2, 3, 4, 5, 6, 7] // Exportar solo hasta la columna 'estado'
          }
        }
      ],
      "columns":[
        {"defaultContent":""},
        {"data":"tipo_indicador",
          render: function(data,type,row){
            if(data=='INGRESOS'){
            return '<span class="badge bg-success">INGRESOS</span>';
            }else{
            return '<span class="badge bg-danger">GASTO</span>';
            }
    }   
        },
        {"data":"nombre"},
        {"data":"descripcion"},
        { "data": "fecha_formateada" },
        { "data": "fecha_formateada2" },
        { "data": "USUARIO" },
        {"data":"estado",
            render: function(data,type,row){
                    if(data=='ACTIVO'){
                    return '<span class="badge bg-success">ACTIVO</span>';
                    }else{
                    return '<span class="badge bg-danger">INACTIVO</span>';
                    }
            }   
        },
        {
          "defaultContent": "<button class='editar btn btn-primary btn-sm' title='Editar datos de área'><i class='fa fa-edit'></i> Editar</button> <button class='eliminar btn btn-danger btn-sm' title='Eliminar datos de área'><i class='fa fa-trash'></i> Eliminar</button>"
        }        
    ],

    "language":idioma_espanol,
    select: true
});
tbl_indicadores.on('draw.td',function(){
  var PageInfo = $("#tabla_indicadores").DataTable().page.info();
  tbl_indicadores.column(0, {page: 'current'}).nodes().each(function(cell, i){
    cell.innerHTML = i + 1 + PageInfo.start;
  });
});
}
$('#tabla_indicadores').on('click','.editar',function(){
  var data = tbl_indicadores.row($(this).parents('tr')).data();

  if(tbl_indicadores.row(this).child.isShown()){
      var data = tbl_indicadores.row(this).data();
  }
  $("#modal_editar").modal('show');
  document.getElementById('txt_id_indi').value=data.id_indicador;
  document.getElementById('txt_tipo_indi_editar').value=data.tipo_indicador;
  document.getElementById('txt_indicador_editar').value=data.nombre;
  document.getElementById('txt_descripcion_editar').value=data.descripcion;
  document.getElementById('txt_status').value=data.estado;

})

function AbrirRegistro(){
  $("#modal_registro").modal({backdrop:'static',keyboard:false})
  $("#modal_registro").modal('show');
}

function Registrar_Indicador(){
  let tipo = document.getElementById('txt_tipo_indi').value;
  let indi = document.getElementById('txt_indicador').value;
  let descrip = document.getElementById('txt_descripcion').value;
  let idusu = document.getElementById('txtprincipalid').value;

  if(tipo.length==0||indi.length==0||idusu.length==0){
      return Swal.fire("Mensaje de Advertencia","Tiene campos vacios, revise los campos que faltan","warning");
  }
  $.ajax({
    "url":"../controller/indicadores/controlador_registro_indicador.php",
    type:'POST',
    data:{
      tipo:tipo,
      indi:indi,
      descrip:descrip,
      idusu:idusu
    }
  }).done(function(resp){
    if(resp>0){
      if(resp==1){
        Swal.fire("Mensaje de Confirmación","Nueva Indicador registrado con el nombre: <b>"+indi+"</b>","success").then((value)=>{
          tbl_indicadores.ajax.reload();
          document.getElementById('txt_tipo_indi').value="";
          document.getElementById('txt_indicador').value="";
          document.getElementById('txt_descripcion').value="";

        $("#modal_registro").modal('hide');
        });
      }else{
        Swal.fire("Mensaje de Advertencia","El indicador ingresado ya se encuentra en la base de datos, revise por favor","warning");
      }
    }else{
      return Swal.fire("Mensaje de Error","No se completo el registro","error");

    }
  })
}
function Modificar_Indicador(){
  let id = document.getElementById('txt_id_indi').value;
  let tipo = document.getElementById('txt_tipo_indi_editar').value;
  let indi = document.getElementById('txt_indicador_editar').value;
  let descrip = document.getElementById('txt_descripcion_editar').value;
  let esta = document.getElementById('txt_status').value;
  let idusu = document.getElementById('txtprincipalid').value;


  if(tipo.length==0||indi.length==0||idusu.length==0||id.length==0||esta.length==0){
    return Swal.fire("Mensaje de Advertencia","Tiene campos vacios, revise los campos que faltan","warning");
  }
  $.ajax({
    "url":"../controller/indicadores/controlador_modificar_indicador.php",
    type:'POST',
    data:{
      id:id,
      tipo:tipo,
      indi:indi,
      descrip:descrip,
      esta:esta,
      idusu:idusu
    }
  }).done(function(resp){
    if(resp>0){
      if(resp==1){
        Swal.fire("Mensaje de Confirmación","Datos actualizados del indicador con el nombre: <b>"+indi+"</b>","success").then((value)=>{
          tbl_indicadores.ajax.reload();
        $("#modal_editar").modal('hide');
        });
      }else{
        Swal.fire("Mensaje de Advertencia","El indicador ingresado ya se encuentra en la base de datos, revise por favor","warning");
      }
    }else{
      return Swal.fire("Mensaje de Error","No se completo la actualización","error");

    }
  })
}

function Eliminar_indicador(id){
  $.ajax({
    "url":"../controller/indicadores/controlador_eliminar_indicador.php",
    type:'POST',
    data:{
      id:id
    }
  }).done(function(resp){
    if(resp>0){
        Swal.fire("Mensaje de Confirmación","Se elimino el indicador con éxito","success").then((value)=>{
          tbl_indicadores.ajax.reload();

        });
    }else{
      return Swal.fire("Mensaje de Advetencia","No se puede eliminar este indicador por que esta siendo utilizado en el módulo de gastos e ingresos, verifique por favor","warning");

    }
  })
}

//ENVIANDO AL BOTON DELETE
$('#tabla_indicadores').on('click','.eliminar',function(){
  var data = tbl_indicadores.row($(this).parents('tr')).data();

  if(tbl_indicadores.row(this).child.isShown()){
      var data = tbl_indicadores.row(this).data();
  }
  Swal.fire({
    title: 'Desea eliminar el indicador con el nombre: '+data.nombre+'?',
    text: "Una vez aceptado el indicador sera eliminado!!!",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Si, Eliminar'
  }).then((result) => {
    if (result.isConfirmed) {
      Eliminar_indicador(data.id_indicador);
    }
  })
})