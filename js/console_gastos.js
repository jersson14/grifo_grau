var tbl_gastos;
function listar_gastos() {
//   Cargar_Select_Regiones();

  tbl_gastos = $("#tabla_gastos").DataTable({
    ordering: false,
    bLengthChange: true,
    searching: { regex: false },
    lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
    pageLength: 10,
    destroy: true,
    pagingType: "full_numbers",
    scrollCollapse: true,
    responsive: true,
    async: false,
    processing: true,
    ajax: {
      url: "../controller/gastos/controlador_listar_gastos.php",
      type: "POST"
    },
    dom: "Bfrtip",
    buttons: [
      {
        extend: "excelHtml5",
        text: '<i class="fas fa-file-excel"></i> Excel',
        titleAttr: "Exportar a Excel",
        filename: function() {
          return "LISTA DE GASTOS";
        },
        title: function() {
            return "LISTA DE GASTOS";
        },
        className: "btn btn-excel",
        exportOptions: {
            columns: [1, 2, 3, 4, 5,6] // Exportar solo hasta la columna 'estado'
        }
      },
      {
        extend: "pdfHtml5",
        text: '<i class="fas fa-file-pdf"></i> PDF',
        titleAttr: "Exportar a PDF",
        filename: function() {
            return "LISTA DE GASTOS";
        },
        title: function() {
            return "LISTA DE GASTOS";
        },
        className: "btn btn-pdf",
        exportOptions: {
          columns: [1, 2, 3, 4, 5,6] // Exportar solo hasta la columna 'estado'
        }
      },
      {
        extend: "print",
        text: '<i class="fa fa-print"></i> Imprimir',
        titleAttr: "Imprimir",
        title: function() {
            return "LISTA DE GASTOS";
        },
        className: "btn btn-print",
        exportOptions: {
            columns: [1, 2, 3, 4, 5,6] // Exportar solo hasta la columna 'estado'
        }
      }
    ],
    columns: [
      { defaultContent: "" },
      { "data": "nombre" },
      {"data":"cantidad"},
      {"data":"monto",
        render: function(data,type,row){
            if(data==data){
            return '<span class="badge bg-success">S/. '+data+'</span>';
            }
    }   
    },      
    {"data":"observacion"},
    {"data":"fecha_formateada"},
    {"data":"fecha_formateada2"},
    {"data":"USUARIO"},

    {"data":"estado",
        render: function(data,type,row){
                if(data=='VALIDO'){
                return '<span class="badge bg-success">VALIDO</span>';
                }else{
                return '<span class="badge bg-danger">ANULADO</span>';
                }
        }   
    },
    {"data":"estado",
        render: function(data,type,row){
                if(data=='VALIDO'){
                return "<button class='editar btn btn-primary  btn-sm' title='Editar datos de especialidad'><i class='fa fa-edit'></i> Editar</button>&nbsp;&nbsp; <button class='delete btn btn-danger  btn-sm' title='Anular ingreso'><i class='fa fa-trash'></i> Anular</button>";
                }else{
                return "<button hidden class='editar btn btn-primary  btn-sm' title='Editar datos de especialidad'><i class='fa fa-edit'></i> Editar</button>&nbsp;&nbsp; <button hidden class='delete btn btn-danger  btn-sm' title='Anular ingreso'><i class='fa fa-trash'></i> Anular</button>&nbsp;&nbsp; <button class='view btn btn-warning  btn-sm' title='Motivo de anulación'><i class='fa fa-eye'></i> Ver motivo de anulación</button>";
                }
        }   
    },        
    ],

    language: idioma_espanol,
    select: true
  });
  tbl_gastos.on("draw.td", function() {
    var PageInfo = $("#tabla_gastos").DataTable().page.info();
    tbl_gastos.column(0, { page: "current" }).nodes().each(function(cell, i) {
      cell.innerHTML = i + 1 + PageInfo.start;
    });
  });
}


function listar_gastos_filto() {
    let indica = document.getElementById('select_indicadores_buscar').value;
    let fechainicio = document.getElementById('txtfechainicio').value;
    let fechafin = document.getElementById('txtfechafin').value;

      tbl_gastos = $("#tabla_gastos").DataTable({
        ordering: false,
        bLengthChange: true,
        searching: { regex: false },
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        pageLength: 10,
        destroy: true,
        pagingType: "full_numbers",
        scrollCollapse: true,
        responsive: true,
        async: false,
        processing: true,
        ajax: {
          url: "../controller/gastos/controlador_listar_gastos_fechas.php",
          type: "POST",
          data:{
            indica:indica,
            fechainicio:fechainicio,
            fechafin:fechafin,
          }
        },
        dom: "Bfrtip",
        buttons: [
          {
            extend: "excelHtml5",
            text: '<i class="fas fa-file-excel"></i> Excel',
            titleAttr: "Exportar a Excel",
            filename: function() {
              return "LISTA DE GASTOS";
            },
            title: function() {
                return "LISTA DE GASTOS";
            },
            className: "btn btn-excel",
            exportOptions: {
                columns: [1, 2, 3, 4, 5,6] // Exportar solo hasta la columna 'estado'
            }
          },
          {
            extend: "pdfHtml5",
            text: '<i class="fas fa-file-pdf"></i> PDF',
            titleAttr: "Exportar a PDF",
            filename: function() {
                return "LISTA DE GASTOS";
            },
            title: function() {
                return "LISTA DE GASTOS";
            },
            className: "btn btn-pdf",
            exportOptions: {
              columns: [1, 2, 3, 4, 5,6] // Exportar solo hasta la columna 'estado'
            }
          },
          {
            extend: "print",
            text: '<i class="fa fa-print"></i> Imprimir',
            titleAttr: "Imprimir",
            title: function() {
                return "LISTA DE GASTOS";
            },
            className: "btn btn-print",
            exportOptions: {
                columns: [1, 2, 3, 4, 5,6] // Exportar solo hasta la columna 'estado'
            }
          }
        ],
        columns: [
          { defaultContent: "" },
          { "data": "nombre" },
          {"data":"cantidad"},
          {"data":"monto",
            render: function(data,type,row){
                if(data==data){
                return '<span class="badge bg-success">S/. '+data+'</span>';
                }
        }   
        },      
        {"data":"observacion"},
        {"data":"fecha_formateada"},
        {"data":"fecha_formateada2"},
    {"data":"USUARIO"},
        {"data":"estado",
            render: function(data,type,row){
                    if(data=='VALIDO'){
                    return '<span class="badge bg-success">VALIDO</span>';
                    }else{
                    return '<span class="badge bg-danger">ANULADO</span>';
                    }
            }   
        },
        {"data":"estado",
            render: function(data,type,row){
                    if(data=='VALIDO'){
                    return "<button class='editar btn btn-primary  btn-sm' title='Editar datos de especialidad'><i class='fa fa-edit'></i> Editar</button>&nbsp;&nbsp; <button class='delete btn btn-danger  btn-sm' title='Anular ingreso'><i class='fa fa-trash'></i> Anular</button>";
                    }else{
                    return "<button hidden class='editar btn btn-primary  btn-sm' title='Editar datos de especialidad'><i class='fa fa-edit'></i> Editar</button>&nbsp;&nbsp; <button hidden class='delete btn btn-danger  btn-sm' title='Anular ingreso'><i class='fa fa-trash'></i> Anular</button>&nbsp;&nbsp; <button class='view btn btn-warning  btn-sm' title='Motivo de anulación'><i class='fa fa-eye'></i> Ver motivo de anulación</button>";
                    }
            }   
        },        
        ],
    
        language: idioma_espanol,
        select: true
      });
      tbl_gastos.on("draw.td", function() {
        var PageInfo = $("#tabla_gastos").DataTable().page.info();
        tbl_gastos.column(0, { page: "current" }).nodes().each(function(cell, i) {
          cell.innerHTML = i + 1 + PageInfo.start;
        });
      });
    }
//CARGAR REGIONES
function Cargar_Select_Indicadores() {
  $.ajax({
    url: "../controller/indicadores/controlador_cargar_select_indicadores.php",
    type: 'POST',
  }).done(function(resp) {
    let data = JSON.parse(resp);
    let cadena = "<option value=''>Seleccionar Indicador</option>";
    if (data.length > 0) {
      for (let i = 0; i < data.length; i++) {
        cadena += "<option value='" + data[i][0] + "'>INDICADOR: " + data[i][1] + "</option>";
      }
    } else {
      cadena += "<option value=''>No hay obras disponibles</option>";
    }
    $('#select_indicadores').html(cadena);
    $('#select_indicadores_buscar').html(cadena);
    $('#select_indicadores_editar').html(cadena);
    $('#select_indicadores_anular').html(cadena);

    // Inicializar Select2 después de cargar opciones
    $('#select_indicadores').select2({
      placeholder: "Seleccionar Indicador",
      allowClear: true,
      width: '100%' // Asegura que use todo el ancho
    });
  });
}
// Agregar estos event listeners
$('#modal_registro').on('shown.bs.modal', function() {
  $('#select_indicadores').select2({
    placeholder: "Seleccionar Indicador",
    allowClear: true,
      dropdownParent: $('#modal_registro')
  });
});

$('#select_indicadores_editar').on('shown.bs.modal', function() {
  $('#select_region_editar').select2({
      placeholder: "Seleccionar Indicador",
      allowClear: true,
      dropdownParent: $('#modal_editar')
  });
});

$('#select_indicadores_anular').on('shown.bs.modal', function() {
    $('#select_region_editar').select2({
        placeholder: "Seleccionar Indicador",
        allowClear: true,
        dropdownParent: $('#modal_anular')
    });
  });


$("#tabla_gastos").on("click", ".editar", function() {
  var data = tbl_gastos.row($(this).parents("tr")).data();

  if (tbl_gastos.row(this).child.isShown()) {
    var data = tbl_gastos.row(this).data();
  }
  $("#modal_editar").modal("show");
  document.getElementById("txt_id_gasto").value = data.id_gastos;
  $("#select_indicadores_editar").select2().val(data.id_indicador).trigger('change.select2');
  document.getElementById("txt_cantidad_editar").value = data.cantidad;
  document.getElementById("txt_monto_editar").value = data.monto;
  document.getElementById("txt_descripcion_editar").value = data.observacion;

});





function AbrirRegistro() {
  $("#modal_registro").modal({ backdrop: "static", keyboard: false });
  $("#modal_registro").modal("show");
}

function Registrar_Gasto() {
  let indi = document.getElementById("select_indicadores").value;
  let cantidad = document.getElementById("txt_cantidad").value;
  let monto = document.getElementById("txt_monto").value;
  let descri = document.getElementById("txt_descripcion").value;
  let idusu = document.getElementById("txtprincipalid").value;

  if (indi.length == 0 || cantidad.length == 0 || monto.length == 0|| idusu.length == 0) {
    return Swal.fire(
      "Mensaje de Advertencia",
      "Tiene campos vacios, revise los campos que faltan",
      "warning"
    );
  }
  $.ajax({
    url: "../controller/gastos/controlador_registro_gastos.php",
    type: "POST",
    data: {
        indi: indi,
        cantidad: cantidad,
        monto: monto,
        descri: descri,
        idusu: idusu
    }
  }).done(function(resp) {
    if (resp > 0) {
        Swal.fire(
          "Mensaje de Confirmación",
          "Nueva Gasto registrado con el monto de: <b> S/." + monto + "</b>",
          "success"
        ).then(value => {
          tbl_gastos.ajax.reload();
          Cargar_Select_Indicadores();
          document.getElementById("txt_cantidad").value = "";
          document.getElementById("txt_monto").value = "";
          document.getElementById("txt_descripcion").value = "";

          $("#modal_registro").modal("hide");
        });
      
    } else {
      return Swal.fire(
        "Mensaje de Error",
        "No se completo el registro",
        "error"
      );
    }
  });
}
function Modificar_Gasto() {
  let id = document.getElementById("txt_id_gasto").value;
  let indi = document.getElementById("select_indicadores_editar").value;
  let cantidad = document.getElementById("txt_cantidad_editar").value;
  let monto = document.getElementById("txt_monto_editar").value;
  let descri = document.getElementById("txt_descripcion_editar").value;
  let idusu = document.getElementById("txtprincipalid").value;

  if (indi.length == 0 || cantidad.length == 0 || monto.length == 0|| idusu.length == 0||id.length == 0) {
    return Swal.fire(
      "Mensaje de Advertencia",
      "Tiene campos vacios, revise los campos que faltan",
      "warning"
    );
  }
  $.ajax({
    url: "../controller/gastos/controlador_modificar_gasto.php",
    type: "POST",
    data: {
      id: id,
      indi: indi,
      cantidad: cantidad,
      monto: monto,
      descri: descri,
      idusu: idusu
    }
  }).done(function(resp) {
    if (resp > 0) {
        Swal.fire(
          "Mensaje de Confirmación",
          "Datos actualizados del gasto con el monto de: S/.<b>" +
          monto +
            "</b>",
          "success"
        ).then(value => {
          tbl_gastos.ajax.reload();
          $("#modal_editar").modal("hide");
        });
      
    } else {
      return Swal.fire(
        "Mensaje de Error",
        "No se completo la actualización",
        "error"
      );
    }
  });
}

$("#tabla_gastos").on("click", ".delete", function() {
    var data = tbl_gastos.row($(this).parents("tr")).data();
  
    if (tbl_gastos.row(this).child.isShown()) {
      var data = tbl_gastos.row(this).data();
    }
    $("#modal_anular").modal("show");
    document.getElementById("txt_id_gasto_anular").value = data.id_gastos;
    $("#select_indicadores_anular").select2().val(data.id_indicador).trigger('change.select2');
    document.getElementById("txt_cantidad_anular").value = data.cantidad;
    document.getElementById("txt_monto_anular").value = data.monto;
  
  });

  function Anular_Gasto() {
    let id = document.getElementById("txt_id_gasto_anular").value;
    let descri = document.getElementById("txt_descripcion_anular").value;
    let monto = document.getElementById("txt_monto_anular").value;
    let idusu = document.getElementById("txtprincipalid").value;
  
    if (id.length == 0 || descri.length == 0 || idusu.length == 0) {
      return Swal.fire(
        "Mensaje de Advertencia",
        "Tiene campos vacios, revise los campos que faltan",
        "warning"
      );
    }
    $.ajax({
      url: "../controller/gastos/controlador_anular_gasto.php",
      type: "POST",
      data: {
        id: id,
        descri: descri,
        idusu: idusu
      }
    }).done(function(resp) {
      if (resp > 0) {
          Swal.fire(
            "Mensaje de Confirmación",
            "Se anulo el satisfactoriamente con el monto de: S/.<b>" +
            monto +
              "</b>",
            "success"
          ).then(value => {
            tbl_gastos.ajax.reload();
            $("#modal_anular").modal("hide");
          });
        
      } else {
        return Swal.fire(
          "Mensaje de Error",
          "No se completo la anulación",
          "error"
        );
      }
    });
  }


  $("#tabla_gastos").on("click", ".view", function() {
    var data = tbl_gastos.row($(this).parents("tr")).data();
  
    if (tbl_gastos.row(this).child.isShown()) {
      var data = tbl_gastos.row(this).data();
    }
    $("#modal_motivo").modal("show");

    document.getElementById("fecha_anulacion").value = data.fecha_anulacion;
    document.getElementById("txt_observación_motivo").value = data.motivo_anulacion;
  
  });

  var tbl_diferencia;
function listar_diferencia(){
    tbl_diferencia = $("#tabla_diferencia").DataTable({
      "ordering":false,   
      "bLengthChange":true,
      "searching": { "regex": false },
      "lengthMenu": [ [10, 25, 50, 100, -1], [10, 25, 50, 100, "All"] ],
      "pageLength": 5,
      "destroy":true,
      pagingType: 'full_numbers',
      scrollCollapse: true,
      responsive: true,
      "async": false ,
      "processing": true,
      "ajax":{
          "url":"../controller/ingresos/controlador_listar_diferencia.php",
          type:'POST'
      },
      dom: 'Bfrtip', 
     
      buttons:[ 
        
    {
      extend:    'excelHtml5',
      text:      '<i class="fas fa-file-excel"></i> ',
      titleAttr: 'Exportar a Excel',
      
      filename: function() {
        return  "LISTA DE DIFERENCIA"
      },
        title: function() {
          return  "LISTA DE DIFERENCIA" }
  
    },
    {
      extend:    'pdfHtml5',
      text:      '<i class="fas fa-file-pdf"></i> ',
      titleAttr: 'Exportar a PDF',
      filename: function() {
        return  "LISTA DE DIFERENCIA"
      },
    title: function() {
      return  "LISTA DE DIFERENCIA"
    }
  },
    {
      extend:    'print',
      text:      '<i class="fa fa-print"></i> ',
      titleAttr: 'Imprimir',
      
    title: function() {
      return  "LISTA DE DIFERENCIA"
  
    }
    }],
      "columns":[
        {"data":"FechaInicial"},
        {"data":"FechaFinal"},
        {"data":"TotalIngresos",
          render: function(data,type,row){
              if(data==data){
              return '<span class="badge bg-success">'+data+'</span>';
              }
      }   
      },        
      {"data":"TotalGastos",
        render: function(data,type,row){
            if(data==data){
            return '<span class="badge bg-danger">'+data+'</span>';
            }
    }   
    },    
    {"data":"Diferencia",
        "render": function(data, type, row) {
            if(data.toString().indexOf('-') !== -1) {
                return '<span class="badge bg-danger">' + data + '</span>';
            } else {
                return '<span class="badge bg-success">' + data + '</span>';
            }
        }
    },  
    ],

    "language":idioma_espanol,
    select: true
});
tbl_diferencia.on('draw.td',function(){
  var PageInfo = $("#tabla_diferencia").DataTable().page.info();
  tbl_diferencia.column(0, {page: 'current'}).nodes().each(function(cell, i){
  });
});
}
function listar_diferencia_filtro(){
    let fechaini = document.getElementById('txtfechainicio3').value;
    let fechafin = document.getElementById('txtfechafin3').value;
  
    tbl_diferencia = $("#tabla_diferencia").DataTable({
      "ordering":false,   
      "bLengthChange":true,
      "searching": { "regex": false },
      "lengthMenu": [ [10, 25, 50, 100, -1], [10, 25, 50, 100, "All"] ],
      "pageLength": 5,
      "destroy":true,
      pagingType: 'full_numbers',
      scrollCollapse: true,
      responsive: true,
      "async": false ,
      "processing": true,
      "ajax":{
          "url":"../controller/ingresos/controlador_listar_diferencia_filtro.php",
          type:'POST',
          data:{
            fechaini:fechaini,
            fechafin:fechafin
          }
      },
      dom: 'Bfrtip', 
     
      buttons:[ 
        
    {
      extend:    'excelHtml5',
      text:      '<i class="fas fa-file-excel"></i> ',
      titleAttr: 'Exportar a Excel',
      
      filename: function() {
        return  "LISTA DE DIFERENCIA"
      },
        title: function() {
          return  "LISTA DE DIFERENCIA" }
  
    },
    {
      extend:    'pdfHtml5',
      text:      '<i class="fas fa-file-pdf"></i> ',
      titleAttr: 'Exportar a PDF',
      filename: function() {
        return  "LISTA DE DIFERENCIA"
      },
    title: function() {
      return  "LISTA DE DIFERENCIA"
    }
  },
    {
      extend:    'print',
      text:      '<i class="fa fa-print"></i> ',
      titleAttr: 'Imprimir',
      
    title: function() {
      return  "LISTA DE DIFERENCIA"
  
    }
    }],
    "columns":[
        {"data":"FechaInicial"},
        {"data":"FechaFinal"},
        {"data":"TotalIngresos",
          render: function(data,type,row){
              if(data==data){
              return '<span class="badge bg-success">'+data+'</span>';
              }
      }   
      },        
      {"data":"TotalGastos",
        render: function(data,type,row){
            if(data==data){
            return '<span class="badge bg-danger">'+data+'</span>';
            }
    }   
    },    
    {"data":"Diferencia",
        "render": function(data, type, row) {
            if(data.toString().indexOf('-') !== -1) {
                return '<span class="badge bg-danger">' + data + '</span>';
            } else {
                return '<span class="badge bg-success">' + data + '</span>';
            }
        }
    },   
    ],

    "language":idioma_espanol,
    select: true
});
tbl_diferencia.on('draw.td',function(){
  var PageInfo = $("#tabla_diferencia").DataTable().page.info();
  tbl_diferencia.column(0, {page: 'current'}).nodes().each(function(cell, i){
  });
});
}