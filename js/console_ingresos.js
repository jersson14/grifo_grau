var tbl_ingresos;
function listar_ingresos() {
//   Cargar_Select_Regiones();

  tbl_ingresos = $("#tabla_ingresos").DataTable({
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
      url: "../controller/ingresos/controlador_listar_ingresos.php",
      type: "POST"
    },
    dom: "Bfrtip",
    buttons: [
      {
        extend: "excelHtml5",
        text: '<i class="fas fa-file-excel"></i> Excel',
        titleAttr: "Exportar a Excel",
        filename: function() {
          return "LISTA DE INGRESOS";
        },
        title: function() {
            return "LISTA DE INGRESOS";
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
            return "LISTA DE INGRESOS";
        },
        title: function() {
            return "LISTA DE INGRESOS";
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
            return "LISTA DE INGRESOS";
        },
        className: "btn btn-print",
        exportOptions: {
            columns: [1, 2, 3, 4, 5,6] // Exportar solo hasta la columna 'estado'
        }
      }
    ],
    columns: [
        { defaultContent: "" },
        { "data": "INDICADOR" },
      
        {
          "data": "monto_pagado",
          render: function(data, type, row) {
            return '<strong>S/. ' + data + '</strong>';
          }
        },
      
        {
          "data": "igv",
          render: function(data, type, row) {
            return '<strong>S/. ' + data + '</strong>';
          }
        },
      
        {
            "data": "monto_total",
            render: function(data, type, row) {
              if (data == data) {
                return '<span class="badge bg-success" style="font-size: 16px;"><strong>S/. ' + data + '</strong></span>';
              }
            }
          },
          
      
        { "data": "observacion" },
        { "data": "fecha_formateada" },
      
        {
          "data": "estado",
          render: function(data, type, row) {
            if (data == 'VALIDO') {
              return '<span class="badge bg-success">VALIDO</span>';
            } else {
              return '<span class="badge bg-danger">ANULADO</span>';
            }
          }
        },
      
        {
          "data": "estado",
          render: function(data, type, row) {
            if (data == 'VALIDO') {
              return "<button class='imprimir btn btn-primary btn-sm' title='Imprimir boleta'><i class='fa fa-print'></i> Imprimir boleta</button>&nbsp;&nbsp; <button class='delete btn btn-danger btn-sm' title='Anular ingreso'><i class='fa fa-trash'></i> Anular</button>";
            } else {
              return "<button hidden class='delete btn btn-danger btn-sm' title='Anular ingreso'><i class='fa fa-trash'></i> Anular</button>&nbsp;&nbsp; <button class='view btn btn-warning btn-sm' title='Motivo de anulación'><i class='fa fa-eye'></i> Ver motivo de anulación</button>";
            }
          }
        },
      ],
      

    language: idioma_espanol,
    select: true
  });
  tbl_ingresos.on("draw.td", function() {
    var PageInfo = $("#tabla_ingresos").DataTable().page.info();
    tbl_ingresos.column(0, { page: "current" }).nodes().each(function(cell, i) {
      cell.innerHTML = i + 1 + PageInfo.start;
    });
  });
}


//INGRESOS DEL DIA
function listar_ingresos_hoy() {
    //   Cargar_Select_Regiones();
    
      tbl_ingresos = $("#tabla_ingresos").DataTable({
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
          url: "../controller/ingresos/controlador_listar_ingresos_hoy.php",
          type: "POST"
        },
        dom: "Bfrtip",
        buttons: [
          {
            extend: "excelHtml5",
            text: '<i class="fas fa-file-excel"></i> Excel',
            titleAttr: "Exportar a Excel",
            filename: function() {
              return "LISTA DE INGRESOS";
            },
            title: function() {
                return "LISTA DE INGRESOS";
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
                return "LISTA DE INGRESOS";
            },
            title: function() {
                return "LISTA DE INGRESOS";
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
                return "LISTA DE INGRESOS";
            },
            className: "btn btn-print",
            exportOptions: {
                columns: [1, 2, 3, 4, 5,6] // Exportar solo hasta la columna 'estado'
            }
          }
        ],
        columns: [
            { defaultContent: "" },
            { "data": "INDICADOR" },
          
            {
              "data": "monto_pagado",
              render: function(data, type, row) {
                return '<strong>S/. ' + data + '</strong>';
              }
            },
          
            {
              "data": "igv",
              render: function(data, type, row) {
                return '<strong>S/. ' + data + '</strong>';
              }
            },
          
            {
                "data": "monto_total",
                render: function(data, type, row) {
                  if (data == data) {
                    return '<span class="badge bg-success" style="font-size: 16px;"><strong>S/. ' + data + '</strong></span>';
                  }
                }
              },
              
          
            { "data": "observacion" },
            { "data": "fecha_formateada" },
          
            {
              "data": "estado",
              render: function(data, type, row) {
                if (data == 'VALIDO') {
                  return '<span class="badge bg-success">VALIDO</span>';
                } else {
                  return '<span class="badge bg-danger">ANULADO</span>';
                }
              }
            },
          
            {
              "data": "estado",
              render: function(data, type, row) {
                if (data == 'VALIDO') {
                  return "<button class='imprimir btn btn-primary btn-sm' title='Imprimir boleta'><i class='fa fa-print'></i> Imprimir boleta</button>&nbsp;&nbsp; <button class='delete btn btn-danger btn-sm' title='Anular ingreso'><i class='fa fa-trash'></i> Anular</button>";
                } else {
                  return "<button hidden class='delete btn btn-danger btn-sm' title='Anular ingreso'><i class='fa fa-trash'></i> Anular</button>&nbsp;&nbsp; <button class='view btn btn-warning btn-sm' title='Motivo de anulación'><i class='fa fa-eye'></i> Ver motivo de anulación</button>";
                }
              }
            },
          ],
          
    
        language: idioma_espanol,
        select: true
      });
      tbl_ingresos.on("draw.td", function() {
        var PageInfo = $("#tabla_ingresos").DataTable().page.info();
        tbl_ingresos.column(0, { page: "current" }).nodes().each(function(cell, i) {
          cell.innerHTML = i + 1 + PageInfo.start;
        });
      });
    }
    


function listar_ingresos_filto() {
    let fechainicio = document.getElementById('txtfechainicio').value;
    let fechafin = document.getElementById('txtfechafin').value;

      tbl_ingresos = $("#tabla_ingresos").DataTable({
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
          url: "../controller/ingresos/controlador_listar_ingresos_fechas.php",
          type: "POST",
          data:{
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
                return "LISTA DE INGRESOS";
              },
              title: function() {
                  return "LISTA DE INGRESOS";
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
                  return "LISTA DE INGRESOS";
              },
              title: function() {
                  return "LISTA DE INGRESOS";
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
                  return "LISTA DE INGRESOS";
              },
              className: "btn btn-print",
              exportOptions: {
                  columns: [1, 2, 3, 4, 5,6] // Exportar solo hasta la columna 'estado'
              }
            }
          ],
          columns: [
              { defaultContent: "" },
              { "data": "INDICADOR" },
            
              {
                "data": "monto_pagado",
                render: function(data, type, row) {
                  return '<strong>S/. ' + data + '</strong>';
                }
              },
            
              {
                "data": "igv",
                render: function(data, type, row) {
                  return '<strong>S/. ' + data + '</strong>';
                }
              },
            
              {
                  "data": "monto_total",
                  render: function(data, type, row) {
                    if (data == data) {
                      return '<span class="badge bg-success" style="font-size: 16px;"><strong>S/. ' + data + '</strong></span>';
                    }
                  }
                },
                
            
              { "data": "observacion" },
              { "data": "fecha_formateada" },
            
              {
                "data": "estado",
                render: function(data, type, row) {
                  if (data == 'VALIDO') {
                    return '<span class="badge bg-success">VALIDO</span>';
                  } else {
                    return '<span class="badge bg-danger">ANULADO</span>';
                  }
                }
              },
            
              {
                "data": "estado",
                render: function(data, type, row) {
                  if (data == 'VALIDO') {
                    return "<button class='imprimir btn btn-primary btn-sm' title='Imprimir boleta'><i class='fa fa-print'></i> Imprimir boleta</button>&nbsp;&nbsp; <button class='delete btn btn-danger btn-sm' title='Anular ingreso'><i class='fa fa-trash'></i> Anular</button>";
                  } else {
                    return "<button hidden class='delete btn btn-danger btn-sm' title='Anular ingreso'><i class='fa fa-trash'></i> Anular</button>&nbsp;&nbsp; <button class='view btn btn-warning btn-sm' title='Motivo de anulación'><i class='fa fa-eye'></i> Ver motivo de anulación</button>";
                  }
                }
              },
            ],
            
        language: idioma_espanol,
        select: true
      });
      tbl_ingresos.on("draw.td", function() {
        var PageInfo = $("#tabla_ingresos").DataTable().page.info();
        tbl_ingresos.column(0, { page: "current" }).nodes().each(function(cell, i) {
          cell.innerHTML = i + 1 + PageInfo.start;
        });
      });
    }
//CARGAR REGIONES


function Anular_pago_historial(id, motivo, monto_anulado) {
    let idusu = document.getElementById('txtprincipalid').value;
  
  
  
    $.ajax({
        url: "../controller/pagos/controlador_anular_pago_historial.php",
        type: 'POST',
        data: {
            id: id,
            idusu: idusu,
            motivo_anulacion: motivo,  // Enviamos el motivo de anulación
            monto_anulado: monto_anulado       // Enviamos el monto anulado
        }
    }).done(function(resp) {
        if (resp > 0) {
            Swal.fire("Mensaje de Confirmación", "Se anuló el pago con éxito", "success").then(() => {
                tbl_ingresos.ajax.reload();
            });
        } else {
            return Swal.fire("Mensaje de Advertencia", "No se puede anular el pago, verifique por favor", "warning");
        }
    });
  }
  

$('#tabla_ingresos').on('click', '.delete', function() {
    var data = tbl_ingresos.row($(this).parents('tr')).data();
  
    if (tbl_ingresos.row(this).child.isShown()) {
        var data = tbl_ingresos.row(this).data();
    }
  
    Swal.fire({
        title: '¿Desea anular el pago de <b style="color:blue">AR$ ' + data.monto_total + '</b>?',
        html: "<p>Por favor, ingrese el motivo de la anulación antes de confirmar.</p>" +
              "<p style='color:red; font-weight: bold;'>⚠️ Al anular este pago:</p>" +
              "<ul style='text-align: left; color: red; font-weight: bold;'>" +
              "<li>El monto será sumado al <b>Saldo pendiente</b>.</li>" +
              "<li>El monto será restado del <b>Saldo cobrado</b> y pasara a un estado de PENDIENTE.</li>" +
              "</ul>",
        icon: 'warning',
        input: 'text',
        inputPlaceholder: 'Escriba el motivo de la anulación...',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, Anular',
        preConfirm: (motivo) => {
            if (!motivo) {
                Swal.showValidationMessage("El motivo de anulación es obligatorio");
            }
            return motivo;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            let motivoAnulacion = result.value;
            Anular_pago_historial(data.id_ingresos, motivoAnulacion, data.monto_total);
        }
    });
  });
  
  
  $('#tabla_ingresos').on('click','.view',function(){
    var data = tbl_ingresos.row($(this).parents('tr')).data();
  
    if(tbl_ingresos.row(this).child.isShown()){
        var data = tbl_ingresos.row(this).data();
    }
  $("#modal_motivo").modal('show');
  
  document.getElementById('fecha_anulacion').value = data.ANULACION;
  document.getElementById('txt_observación_motivo').value = data.motivo_anu;
  
  })


  //DIFERENCIAS
  
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

//IMPRIMIR BOLETA

$('#tabla_ingresos').on('click','.imprimir',function(){
  var data = tbl_ingresos.row($(this).parents('tr')).data();

  if(tbl_ingresos.row(this).child.isShown()){
      var data = tbl_ingresos.row(this).data();
  }
  var url = "../view/MPDF/REPORTE/boleta_pago.php?id=" + encodeURIComponent(data.id_ingresos) + "#zoom=100%";

  // Abrir una nueva ventana con la URL construida
  var newWindow = window.open(url, "BOLETA DE PAGO", "scrollbars=NO");
  
  // Asegurarse de que la ventana se abre en tamaño máximo
  if (newWindow) {
      newWindow.moveTo(0, 0);
      newWindow.resizeTo(screen.width, screen.height);
  }

})
