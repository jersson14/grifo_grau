//LISTADO DE EXPEDIENTES
    var tbl_pagos;
  function listar_pagos() {
    tbl_pagos = $("#tabla_pagos").DataTable({
      "ordering": true,
      "bLengthChange": true,
      "searching": { "regex": false },
      "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
      "pageLength": 10,
      "destroy": true,
      pagingType: 'full_numbers',
      scrollCollapse: true,
      responsive: true,
      "async": false,
      "processing": true,
      "ajax": {
        "url": "../controller/pagos/controlador_listar_pagos.php",
        type: 'POST'
      },
      dom: 'Bfrtip',
      buttons: [
        {
          extend: 'excelHtml5',
          text: '<i class="fas fa-file-excel"></i> Excel',
          titleAttr: 'Exportar a Excel',
          filename: function() {
            return "LISTA DE PAGOS";
          },
          title: function() {
            return "LISTA DE PAGOS";
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
            return "LISTA DE PAGOS";
          },
          title: function() {
            return "LISTA DE PAGOS";
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
            return "LISTA DE PAGOS";
          },
          className: 'btn btn-print',
          exportOptions: {
            columns: [1, 2, 3, 4, 5, 6, 7] // Exportar solo hasta la columna 'estado'
          }
        }
      ],
  
      "columns": [
        { "data": "nro_expediente" },
        {
          "data": null,
          "render": function(data, type, row) {
            return `
              <div style="font-weight: bold; font-size: 12px;">${row.tipo_documento}</div>
              <div>${row.nro_documento}</div>
            `;
          }
        },
        
        { 
          "data": "CLIENTE",
          "render": function(data, type, row) {
            return "<strong>" + data + "</strong>";
          }
        },
        { "data": "nombre" },
  
        { 
            "data": "monto_total",
            "render": function(data, type, row) {
              return "<strong>S/. " + data + "</strong>";
            }
          },
          { 
            "data": "saldro_cobrado",
            "render": function(data, type, row) {
              return "S/. " + data;
            }
          },
        {
            "data": "saldo_pendiente",
            render: function(data, type, row) {
              if (data == 0) {
                return '<span class="badge bg-success fs-5">S/. ' + data + '</span>';
              } else {
                return '<span class="badge bg-danger fs-5">S/. ' + data + '</span>';
              }
            }
          },        
        { "data": "fecha_formateada_pagos" },
        {
            "data": "estado",
            "render": function(data, type, row) {
              let color = '';
          
              switch (data.toUpperCase()) {
                case 'PENDIENTE':
                  color = 'warning'; break;
                case 'PAGADO':
                  color = 'success'; break;
                case 'ANULADA':
                    color = 'danger'; break;
                default:
                  color = 'secondary';
              }
          
              return `<span class="badge bg-${color}">${data}</span>`;
            }
          },
          {
            "data": "estado",
            render: function(data, type, row) {
                if (data == 'PENDIENTE') {
                    return  `

                   <button class='pagar btn btn-success btn-sm' title='Pagar saldo'><i class='fa fas fa-hand-holding-usd'></i> Pagar</button> 
                   <button class='historial_pagos btn btn-dark btn-sm' title='Ver historial de estado'><i class='fas fa-history'></i> Historial pagos</button>
                    <button type="button" class="delete btn btn-danger btn-sm" title="Anular todo">
                    <i class="fas fa-ban"></i> Anular todo
                    </button>

                      `;
              
              } else if (data == 'ANULADA') {
                return  `

                <button class='historial_pagos btn btn-dark btn-sm' title='Ver historial de estado'><i class='fas fa-history'></i> Historial pagos</button>
                 <button type="button" class="ver_anulacion btn btn-secondary btn-sm" title="Ver anulación">
                 <i class="fas fa-eye"></i> Ver anulación
                 </button>


                   `;

              }
              
              else {
                    return ` 
                    <button class='boleta btn btn-warning btn-sm' title='Imprimir boleta'><i class='fa fas fa-print'></i> Imprimir boleta</button>
                    <button hidden class='pagar btn btn-success btn-sm' title='Pagar saldo'><i class='fa fas fa-hand-holding-usd'></i> Pagar</button> 
                   <button class='historial_pagos btn btn-dark btn-sm' title='Ver historial de estado'><i class='fas fa-history'></i> Historial pagos</button>
                    <button hidden type="button" class="delete btn btn-danger btn-sm" title="Anular todo">
                    <i class="fas fa-ban"></i> Anular todo
                    </button>
             `;
              }
            }
        },        
      ],
  
      "language": idioma_espanol,
      select: true
    });
  
    tbl_pagos.on('draw.td', function() {
      var PageInfo = $("#tabla_pagos").DataTable().page.info();
      tbl_pagos.column(0, { page: 'current' }).nodes().each(function(cell, i) {
  
      });
    });
  }
  
  
  function listar_pagos_filtro() {
      let fechaini = document.getElementById('txt_fecha_desde').value;
      let fechafin = document.getElementById('txt_fecha_hasta').value;
      let servicio = document.getElementById('select_servicios_buscar').value;
  
      tbl_pagos = $("#tabla_pagos").DataTable({
        "ordering": true,
        "bLengthChange": true,
        "searching": { "regex": false },
        "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        "pageLength": 10,
        "destroy": true,
        pagingType: 'full_numbers',
        scrollCollapse: true,
        responsive: true,
        "async": false,
        "processing": true,
        "ajax": {
          "url": "../controller/pagos/controlador_listar_pagos_filtro.php",
          type: 'POST',
          data:{
            fechaini:fechaini,
            fechafin:fechafin,
            servicio:servicio,
          }
        },
        dom: 'Bfrtip',
        buttons: [
            {
              extend: 'excelHtml5',
              text: '<i class="fas fa-file-excel"></i> Excel',
              titleAttr: 'Exportar a Excel',
              filename: function() {
                return "LISTA DE PAGOS";
              },
              title: function() {
                return "LISTA DE PAGOS";
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
                return "LISTA DE PAGOS";
              },
              title: function() {
                return "LISTA DE PAGOS";
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
                return "LISTA DE PAGOS";
              },
              className: 'btn btn-print',
              exportOptions: {
                columns: [1, 2, 3, 4, 5, 6, 7] // Exportar solo hasta la columna 'estado'
              }
            }
          ],
      
          "columns": [
            { "data": "nro_expediente" },
            {
              "data": null,
              "render": function(data, type, row) {
                return `
                  <div style="font-weight: bold; font-size: 12px;">${row.tipo_documento}</div>
                  <div>${row.nro_documento}</div>
                `;
              }
            },
            
            { 
              "data": "CLIENTE",
              "render": function(data, type, row) {
                return "<strong>" + data + "</strong>";
              }
            },
            { "data": "nombre" },
      
            { 
                "data": "monto_total",
                "render": function(data, type, row) {
                  return "<strong>S/. " + data + "</strong>";
                }
              },
              { 
                "data": "saldro_cobrado",
                "render": function(data, type, row) {
                  return "S/. " + data;
                }
              },
            {
                "data": "saldo_pendiente",
                render: function(data, type, row) {
                  if (data == 0) {
                    return '<span class="badge bg-success fs-5">S/. ' + data + '</span>';
                  } else {
                    return '<span class="badge bg-danger fs-5">S/. ' + data + '</span>';
                  }
                }
              },        
            { "data": "fecha_formateada_pagos" },
            {
                "data": "estado",
                "render": function(data, type, row) {
                  let color = '';
              
                  switch (data.toUpperCase()) {
                    case 'PENDIENTE':
                      color = 'warning'; break;
                    case 'PAGADO':
                      color = 'success'; break;
                    case 'ANULADA':
                        color = 'danger'; break;
                    default:
                      color = 'secondary';
                  }
              
                  return `<span class="badge bg-${color}">${data}</span>`;
                }
              },
              {
                "data": "estado",
                render: function(data, type, row) {
                    if (data == 'PENDIENTE') {
                        return  `
    
                       <button class='pagar btn btn-success btn-sm' title='Pagar saldo'><i class='fa fas fa-hand-holding-usd'></i> Pagar</button> 
                       <button class='historial_pagos btn btn-dark btn-sm' title='Ver historial de estado'><i class='fas fa-history'></i> Historial pagos</button>
                        <button type="button" class="delete btn btn-danger btn-sm" title="Anular todo">
                        <i class="fas fa-ban"></i> Anular todo
                        </button>
    
                          `;
                  
                  } else if (data == 'ANULADA') {
                    return  `
    
                    <button class='historial_pagos btn btn-dark btn-sm' title='Ver historial de estado'><i class='fas fa-history'></i> Historial pagos</button>
                     <button type="button" class="ver_anulacion btn btn-secondary btn-sm" title="Ver anulación">
                     <i class="fas fa-eye"></i> Ver anulación
                     </button>
    
    
                       `;
    
                  }
                  
                  else {
                        return ` 
                        <button class='boleta btn btn-warning btn-sm' title='Imprimir boleta'><i class='fa fas fa-print'></i> Imprimir boleta</button>
                        <button hidden class='pagar btn btn-success btn-sm' title='Pagar saldo'><i class='fa fas fa-hand-holding-usd'></i> Pagar</button> 
                       <button class='historial_pagos btn btn-dark btn-sm' title='Ver historial de estado'><i class='fas fa-history'></i> Historial pagos</button>
                        <button hidden type="button" class="delete btn btn-danger btn-sm" title="Anular todo">
                        <i class="fas fa-ban"></i> Anular todo
                        </button>
                 `;
                  }
                }
            },        
          ],
      
    
        "language": idioma_espanol,
        select: true
      });
    
      tbl_pagos.on('draw.td', function() {
        var PageInfo = $("#tabla_pagos").DataTable().page.info();
        tbl_pagos.column(0, { page: 'current' }).nodes().each(function(cell, i) {
    
        });
      });
    }
  
  
    //CARGAR_SELECT_SERVICIOS
    function Cargar_Select_Servi() {
        $.ajax({
            url: "../controller/servicios/controlador_cargar_servicios.php",
          type: 'POST',
        }).done(function(resp) {
          let data = JSON.parse(resp);
          let cadena = "<option value=''>Seleccionar Servicio</option>";
          if (data.length > 0) {
            for (let i = 0; i < data.length; i++) {
              cadena += "<option value='" + data[i][0] + "'>" + data[i][1] + "</option>";
            }
          } else {
            cadena += "<option value=''>No hay obras disponibles</option>";
          }
          $('#select_servicios_buscar').html(cadena);
      
          // Inicializar Select2 después de cargar opciones
          $('#select_servicios_buscar').select2({
            placeholder: "Seleccionar servicio",
            allowClear: true,
            width: '100%' // Asegura que use todo el ancho
          });
        });
      }
  

  
  
$('#tabla_pagos').on('click', '.pagar', function () {
var data = tbl_pagos.row($(this).parents('tr')).data();

if (tbl_pagos.row(this).child.isShown()) {
    data = tbl_pagos.row(this).data();
}

// Mostrar el modal
$("#modal_pagar").modal('show');

// Esperar a que el modal termine de abrirse para enfocar el campo
$("#modal_pagar").on('shown.bs.modal', function () {
    $('#txt_pagar').trigger('focus');
});

// Actualizar los valores en el modal
document.getElementById('lb_tituloesta_pagar').innerHTML = "<b>CLIENTE:</b> " + data.CLIENTE;
document.getElementById('lb_titulo2esta_pagar').innerHTML = "<b>SERVICIO:</b> " + data.nombre;
document.getElementById('id_pago').value = data.id_pago;
document.getElementById('txt_total').value = data.saldo_pendiente;
});

  //VER HISTORIAL DE ESTADO
  $('#tabla_pagos').on('click','.mostrar',function(){
    var data = tbl_pagos.row($(this).parents('tr')).data();
  
    if(tbl_pagos.row(this).child.isShown()){
        var data = tbl_pagos.row(this).data();
    }
  $("#modal_ver_requisitos").modal('show');
  
    document.getElementById('lb_titulo_historial_requi').innerHTML="<b>CLIENTE:</b> "+data.CLIENTE+"";
    document.getElementById('lb_titulo_historial2_requi').innerHTML="<b>SERVICIO:</b> "+data.nombre+"";
  
    listar_requi(data.id_expediente);
  
  })
  // VISTA DE REQUISITOS DE EXPEDIENTE
  var tbl_requisitos;
  function listar_requi(id) {
    tbl_requisitos = $("#tabla_ver_requi").DataTable({
        "ordering": false,
        "bLengthChange": true,
        "searching": false,  // Deshabilita la barra de búsqueda
        "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Todos"]],
        "pageLength": 5,
        "destroy": true,
        "pagingType": 'full_numbers',
        "scrollCollapse": true,
        "responsive": true,
        "async": false,
        "processing": true,
        "ajax": {
            "url": "../controller/expedientes/controlador_listar_requisitos_expedientes.php",
            "type": 'POST',
            "data": { id: id },
            "dataSrc": function(json) {
                console.log("Respuesta JSON:", json);
                return json.data;
            }
        },
        "dom": 'Bfrtip', 
        "buttons": [
          {
            extend: 'excelHtml5',
            text: '<i class="fas fa-file-excel"></i> Excel',
            titleAttr: 'Exportar a Excel',
            filename: "LISTA_DE_REQUISITOS",
            title: "LISTA DE REQUISITOS",
            className: 'btn btn-success' 
          },
          {
            extend: 'pdfHtml5',
            text: '<i class="fas fa-file-pdf"></i> PDF',
            titleAttr: 'Exportar a PDF',
            filename: "LISTA_DE_REQUISITOS",
            title: "LISTA DE REQUISITOS",
            className: 'btn btn-danger'
          },
          {
            extend: 'print',
            text: '<i class="fa fa-print"></i> Imprimir',
            titleAttr: 'Imprimir',
            title: "LISTA DE REQUISITOS",
            className: 'btn btn-primary' 
          }
        ],
       "columns": [
    { 
      "data": null, 
      "render": function(data, type, row, meta) { 
        return meta.row + 1; 
      } 
    }, 
    {
      "data": "REQUISITO",
      "render": function (data, type, row) {
        return `<strong>${data}</strong>`;
      }
    },
    {
      "data": "archivo",
      "render": function(data, type, row) {
        if (!data) {
          return "<button class='btn btn-danger btn-sm' disabled title='Sin archivo'><i class='fa fa-file-pdf'></i></button>";
        } else {
          return `<a class='btn btn-success btn-sm' href='incocat_abancay/${data}' target='_blank' title='Ver archivo adjunto'><i class='fas fa-eye'></i> Ver archivo</a>`;
        }
      }   
    },
    {
      "data": "estado",
      "render": function(data, type, row) {
        if (data == 'SI') {
          return '<span class="badge bg-success">COMPLETO</span>';
        } else if (data == 'NO' || data == '') {
          return '<span class="badge bg-danger">INCOMPLETO</span>';
        }
      }
    },
    {
      "data": "fecha_formateada",
      "render": function(data, type, row) {
        if (!data || data === '00-00-0000 - 00:00:00') {
          return 'SIN FECHA';
        }
        return data;
      }
    },
    {
      "data": "fecha_formateada2",
      "render": function(data, type, row) {
        if (!data || data === '00-00-0000 - 00:00:00') {
          return 'SIN FECHA';
        }
        return data;
      }
    }
  ],
  
        "language": {
            "emptyTable": "No se encontraron datos", // ✅ Mensaje cuando la tabla está vacía
            "zeroRecords": "No se encontraron resultados", // ✅ Mensaje para búsquedas sin coincidencias
            "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
            "infoEmpty": "Mostrando 0 a 0 de 0 registros",
            "infoFiltered": "(filtrado de _MAX_ registros en total)",
            "lengthMenu": "Mostrar _MENU_ registros",
            "loadingRecords": "Cargando...",
            "processing": "Procesando...",
            "search": "Buscar:",
            "paginate": {
                "first": "Primero",
                "last": "Último",
                "next": "Siguiente",
                "previous": "Anterior"
            }
        },
        "select": true
    });
  }
  
  //PAGAR
  function Realizar_pago() {
    let id = document.getElementById('id_pago').value;
    let total = parseFloat(document.getElementById('txt_total').value) || 0;
    let pagar = parseFloat(document.getElementById('txt_pagar').value) || 0;
    let igv = parseFloat(document.getElementById('txt_igv').value) || 0;
    let subtotal = parseFloat(document.getElementById('txt_subtotal_a_cancelar').value) || 0;
    let saldo = parseFloat(document.getElementById('txt_saldo').value) || 0; 
    let descrip = document.getElementById('txt_obser').value;       
    let idusu = document.getElementById('txtprincipalid').value;
  
    if (isNaN(pagar) || pagar <= 0) {
        return Swal.fire("Mensaje de Advertencia", "Ingrese un monto válido a pagar", "warning");
    }
  
    if (pagar > total) {
        return Swal.fire("Mensaje de Advertencia", "El monto a pagar no puede ser mayor al total", "warning");
    }
  
    $.ajax({
        url: "../controller/pagos/controlador_realizar_pago.php",
        type: 'POST',
        data: {
            id: id,
            pagar: pagar,
            igv: igv,
            subtotal: subtotal,
            saldo: saldo,
            descrip:descrip,
            idusu: idusu
        }
    }).done(function(resp) {
        if (resp > 0) {

          Swal.fire({
            title: 'Se registro el pago correctamente<b>',
            text: "Datos de Confirmación",
            icon: 'success',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Imprimir Boleta!'
          }).then((result) => {
            if (result.isConfirmed) {
              var url = "../view/MPDF/REPORTE/boleta_pago.php?id="+ encodeURIComponent(resp) + "#zoom=100%";;
    
              // Abrir una nueva ventana con la URL construida
              var newWindow = window.open(url, "PAGO", "scrollbars=NO");
              
              // Asegurarse de que la ventana se abre en tamaño máximo
              if (newWindow) {
                  newWindow.moveTo(0, 0);
                  newWindow.resizeTo(screen.width, screen.height);
              }           
               $("#modal_pagar").modal('hide');
    
               tbl_pagos.ajax.reload();

            }else{
              $("#modal_pagar").modal('hide');
              tbl_pagos.ajax.reload();

            }
          });
       
        } else {
            return Swal.fire("Mensaje de Error", "No se completó el pago", "error");
        }
    });
  }
  

  //ANULAR PAGO
function Anular_pago(id, motivo) {
    let idusu = document.getElementById('txtprincipalid').value;
  
  
  
    $.ajax({
        url: "../controller/pagos/controlador_anular_pago.php",
        type: 'POST',
        data: {
            id: id,
            idusu: idusu,
            motivo_anulacion: motivo,  // Enviamos el motivo de anulación
        }
    }).done(function(resp) {
        if (resp > 0) {
            Swal.fire("Mensaje de Confirmación", "Se anuló el pago con éxito", "success").then(() => {
                tbl_pagos.ajax.reload();
            });
        } else {
            return Swal.fire("Mensaje de Advertencia", "No se puede anular el pago, verifique por favor", "warning");
        }
    });
  }
  
  // ENVIANDO AL BOTÓN DELETE
  $('#tabla_pagos').on('click', '.delete', function() {
    var data = tbl_pagos.row($(this).parents('tr')).data();
  
    if (tbl_pagos.row(this).child.isShown()) {
        var data = tbl_pagos.row(this).data();
    }
  
    Swal.fire({
        title: '¿Desea anular el pago total de <b style="color:blue">AR$ ' + data.monto_total + '</b>?',
        html: "<p>Por favor, ingrese el motivo de la anulación antes de confirmar.</p>" +
              "<p style='color:red; font-weight: bold;'>⚠️ Al anular este pago:</p>" +
              "<ul style='text-align: left; color: red; font-weight: bold;'>" +
              "<li>Una vez anule este pago el historial de pagos también pasara a estar como anulado</b>.</li>" +
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
            Anular_pago(data.id_ingresos, motivoAnulacion);
        }
    });
  });
  
  $('#tabla_pagos').on('click','.ver_anulacion',function(){
    var data = tbl_pagos.row($(this).parents('tr')).data();
  
    if(tbl_pagos.row(this).child.isShown()){
        var data = tbl_pagos.row(this).data();
    }
  $("#modal_ver_anulado").modal('show');
  
  document.getElementById('txt_motivo2').value = data.motivo_anula;
  document.getElementById('txt_fecha_anulado2').value = data.fecha_formateada_pagos2;
  
  })

  //VER HISTORIAL DE PAGOS
  $('#tabla_pagos').on('click','.historial_pagos',function(){
    var data = tbl_pagos.row($(this).parents('tr')).data();
  
    if(tbl_pagos.row(this).child.isShown()){
        var data = tbl_pagos.row(this).data();
    }
  $("#modal_ver_pagos").modal('show');
  
  document.getElementById('lb_titulo_historialpagos').innerHTML = "<b>CLIENTE:</b> " + data.CLIENTE;
  document.getElementById('lb_titulo_historial2pagos').innerHTML = "<b>SERVICIO:</b> " + data.nombre;
  listar_pagos_historial(data.id_pago);
  
  })
  // VISTA DE HISTORIAL
  var tbl_historial_pagos;
  function listar_pagos_historial(id) {
    tbl_historial_pagos = $("#tabla_ver_pagos").DataTable({
        "ordering": false,
        "bLengthChange": true,
        "searching": false,  // Deshabilita la barra de búsqueda
        "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Todos"]],
        "pageLength": 5,
        "destroy": true,
        "pagingType": 'full_numbers',
        "scrollCollapse": true,
        "responsive": true,
        "async": false,
        "processing": true,
        "ajax": {
            "url": "../controller/pagos/controlador_listar_historial_pagos.php",
            "type": 'POST',
            "data": { id: id },
            "dataSrc": function(json) {
                console.log("Respuesta JSON:", json);
                return json.data;
            }
        },
        "dom": 'Bfrtip', 
        "buttons": [
          {
            extend: 'excelHtml5',
            text: '<i class="fas fa-file-excel"></i> Excel',
            titleAttr: 'Exportar a Excel',
            filename: "LISTA_DE_HISTORIAL_DE_PAGOS",
            title: "LISTA DE HISTORIAL DE PAGOS",
            className: 'btn btn-success' 
          },
          {
            extend: 'pdfHtml5',
            text: '<i class="fas fa-file-pdf"></i> PDF',
            titleAttr: 'Exportar a PDF',
            filename: "LISTA_DE_HISTORIAL_DE_PAGOS",
            title: "LISTA DE HISTORIAL DE PAGOS",
            className: 'btn btn-danger'
          },
          {
            extend: 'print',
            text: '<i class="fa fa-print"></i> Imprimir',
            titleAttr: 'Imprimir',
            title: "LISTA DE HISTORIAL DE PAGOS",
            className: 'btn btn-primary' 
          }
        ],
        "columns": [
            { "data": null, "render": function(data, type, row, meta) { return meta.row + 1; } }, 
            { "data": "USUARIO" },
            {
              "data": "monto_pagado",
              "render": function (data, type, row) {
                return `<strong>S/. ${data}</strong>`;
              }
            },
            {
              "data": "igv",
              "render": function (data, type, row) {
                return `<strong>S/. ${data}</strong>`;
              }
            },
            {
              "data": "monto_total",
              "render": function (data, type, row) {
                return `<strong>S/. ${data}</strong>`;
              }
            },
            { "data": "fecha_formateada" },
            {"data":"estado",
              render: function(data,type,row){
                      if(data=='VALIDO'){
                      return '<span class="badge bg-success">VALIDO</span>';
                      }else{
                      return '<span class="badge bg-danger">ANULADO</span>';
                      }
              }   
          },
            {
              "data": "estado",
              render: function(data, type, row) {
                  if (data == 'VALIDO') {
                      return  `
                      <button class='imprimir btn btn-primary btn-sm' title='Imprimir boleta'><i class='fa fa-print'></i> Imprimir</button>
                     <button class='anular_histo btn btn-danger btn-sm' title='Anular el pago'><i class='fa fa-ban'></i> Anular</button>
                      <button class='ver_anulado_histo btn btn-warning btn-sm' hidden title='Ver motivo'><i class='fa fa-eye'></i> Ver Anulado</button>
                        `;
                } else {
                  return  `
                  <button class='anular_histo btn btn-danger btn-sm' hidden title='Anular el pago'><i class='fa fa-ban'></i> Anular</button>
                      <button class='ver_anulado_histo btn btn-warning btn-sm' title='Ver motivo'><i class='fa fa-eye'></i> Ver Anulado</button>
                     `;
                  }
              }
          },        
        ],
        "language": {
            "emptyTable": "No se encontraron datos", // ✅ Mensaje cuando la tabla está vacía
            "zeroRecords": "No se encontraron resultados", // ✅ Mensaje para búsquedas sin coincidencias
            "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
            "infoEmpty": "Mostrando 0 a 0 de 0 registros",
            "infoFiltered": "(filtrado de _MAX_ registros en total)",
            "lengthMenu": "Mostrar _MENU_ registros",
            "loadingRecords": "Cargando...",
            "processing": "Procesando...",
            "search": "Buscar:",
            "paginate": {
                "first": "Primero",
                "last": "Último",
                "next": "Siguiente",
                "previous": "Anterior"
            }
        },
        "select": true
    });
  }
  
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
                tbl_historial_pagos.ajax.reload();
                tbl_pagos.ajax.reload();
            });
        } else {
            return Swal.fire("Mensaje de Advertencia", "No se puede anular el pago, verifique por favor", "warning");
        }
    });
  }
  
  // ENVIANDO AL BOTÓN DELETE
  $('#tabla_ver_pagos').on('click', '.anular_histo', function() {
    var data = tbl_historial_pagos.row($(this).parents('tr')).data();
  
    if (tbl_historial_pagos.row(this).child.isShown()) {
        var data = tbl_historial_pagos.row(this).data();
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
  
  
  $('#tabla_ver_pagos').on('click','.ver_anulado_histo',function(){
    var data = tbl_historial_pagos.row($(this).parents('tr')).data();
  
    if(tbl_historial_pagos.row(this).child.isShown()){
        var data = tbl_historial_pagos.row(this).data();
    }
  $("#modal_ver_anulado_historial").modal('show');
  
  document.getElementById('txt_fecha_anulado3').value = data.ANULACION;
  document.getElementById('txt_motivo3').value = data.motivo_anu;
  
  })

  //IMPRIMIR BOLETA
  $('#tabla_ver_pagos').on('click','.imprimir',function(){
    var data = tbl_historial_pagos.row($(this).parents('tr')).data();
  
    if(tbl_historial_pagos.row(this).child.isShown()){
        var data = tbl_historial_pagos.row(this).data();
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
  
  $('#tabla_pagos').on('click','.boleta',function(){
    var data = tbl_pagos.row($(this).parents('tr')).data();
  
    if(tbl_pagos.row(this).child.isShown()){
        var data = tbl_pagos.row(this).data();
    }
    var url = "../view/MPDF/REPORTE/kardex_pago.php?id=" + encodeURIComponent(data.id_pago) + "#zoom=100%";
  
    // Abrir una nueva ventana con la URL construida
    var newWindow = window.open(url, "KARDEX DE PAGO", "scrollbars=NO");
    
    // Asegurarse de que la ventana se abre en tamaño máximo
    if (newWindow) {
        newWindow.moveTo(0, 0);
        newWindow.resizeTo(screen.width, screen.height);
    }
  
  })
  