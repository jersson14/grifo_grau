var tbl_expedientes;
function listar_expedientes() {
  tbl_expedientes = $("#tabla_expedientes").DataTable({
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
      "url": "../controller/expedientes/controlador_listar_expedientes.php",
      type: 'POST'
    },
    dom: 'Bfrtip',
    buttons: [
      {
        extend: 'excelHtml5',
        text: '<i class="fas fa-file-excel"></i> Excel',
        titleAttr: 'Exportar a Excel',
        filename: function() {
          return "LISTA DE EXPEDIENTES";
        },
        title: function() {
            return "LISTA DE EXPEDIENTES";
        },
        className: 'btn btn-excel',
        exportOptions: {
            columns: [1, 2, 3, 4, 5, 6, 7,8,9] // Exportar solo hasta la columna 'estado'
        }
      },
      {
        extend: 'pdfHtml5',
        text: '<i class="fas fa-file-pdf"></i> PDF',
        titleAttr: 'Exportar a PDF',
        filename: function() {
          return "LISTA DE EXPEDIENTES";
        },
        title: function() {
          return "LISTA DE EXPEDIENTES";
        },
        orientation: 'landscape', // 👉 Esto hace que la hoja sea horizontal
        pageSize: 'A4',            // (Opcional) tamaño de hoja
        className: 'btn btn-pdf',
        exportOptions: {
          columns: [1, 2, 3, 4, 5, 6, 7, 8, 9]
        }
      },
      
      {
        extend: 'print',
        text: '<i class="fa fa-print"></i> Imprimir',
        titleAttr: 'Imprimir',
        title: function() {
            return "LISTA DE EXPEDIENTES";
        },
        className: 'btn btn-print',
        exportOptions: {
            columns: [1, 2, 3, 4, 5, 6, 7,8,9] // Exportar solo hasta la columna 'estado'
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
        { "data": "REGION" },
        { 
            "data": "PROVINCIA",
            "render": function(data, type, row) {
              return "<strong>" + data + "</strong>"; // Aquí agregamos el strong
            }
          },

        {
          "data": null,
          "render": function(data, type, row) {
            return `
              <div>${row.nombre}</div>
              <div style="font-weight: bold; font-size: 20px;">PRECIO: S/. ${row.monto_total}</div>
  
            `;
          }
        },
        { "data": "folios" },
        {
          "data": "ESTADO_EXPE",
          "render": function(data, type, row) {
            let color = '';
        
            switch (data.toUpperCase()) {
              case 'REINGRESADO':
                color = 'warning'; break;
              case 'OBSERVADO':
                color = 'danger'; break;
              case 'EN PROCESO':
                color = 'info'; break;
              case 'EN TRAMITE':
                color = 'primary'; break;
              default:
                color = 'success';
            }
        
            return `<span class="badge bg-${color}">${data}</span>`;
          }
        },

        { "data": "fecha_formateada" },
        {
          "data": "tiempo_transcurrido",
          "render": function(data, type, row) {
            let dias = parseInt(data); // Asegúrate que el valor sea numérico
        
            if (isNaN(dias)) {
              return `<span class="badge bg-secondary">${data}</span>`;
            }
        
            let colorClass = "bg-dark"; // default: 1 día o menos
            if (dias >= 30) {
              colorClass = "bg-danger";
            } else if (dias >= 20) {
              colorClass = "bg-warning text-dark";
            } else if (dias >= 10) {
              colorClass = "bg-success";
            }
        
            return `<span class="badge ${colorClass}">${dias} días</span>`;
          }
        },      

    ],

    "language": idioma_espanol,
    select: true
  });

  tbl_expedientes.on('draw.td', function() {
    var PageInfo = $("#tabla_expedientes").DataTable().page.info();
    tbl_expedientes.column(0, { page: 'current' }).nodes().each(function(cell, i) {

    });
  });
}
function listar_expedientes_filtro() {
    let fechaini = document.getElementById('txt_fecha_desde').value;
    let fechafin = document.getElementById('txt_fecha_hasta').value;
    let provincia = document.getElementById('select_provincia_buscar').value;

    tbl_expedientes = $("#tabla_expedientes").DataTable({
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
        "url": "../controller/expedientes/controlador_listar_expedientes_fecha_provincia.php",
        type: 'POST',
        data:{
          fechaini:fechaini,
          fechafin:fechafin,
          provincia:provincia,
        }
      },
      dom: 'Bfrtip',
      buttons: [
        {
          extend: 'excelHtml5',
          text: '<i class="fas fa-file-excel"></i> Excel',
          titleAttr: 'Exportar a Excel',
          filename: function() {
            return "LISTA DE EXPEDIENTES";
          },
          title: function() {
              return "LISTA DE EXPEDIENTES";
          },
          className: 'btn btn-excel',
          exportOptions: {
            columns: [1, 2, 3, 4, 5, 6, 7,8,9] // Exportar solo hasta la columna 'estado'
          }
        },
        {
            extend: 'pdfHtml5',
            text: '<i class="fas fa-file-pdf"></i> PDF',
            titleAttr: 'Exportar a PDF',
            filename: function() {
              return "LISTA DE EXPEDIENTES";
            },
            title: function() {
              return "LISTA DE EXPEDIENTES";
            },
            orientation: 'landscape', // 👉 Esto hace que la hoja sea horizontal
            pageSize: 'A4',            // (Opcional) tamaño de hoja
            className: 'btn btn-pdf',
            exportOptions: {
              columns: [1, 2, 3, 4, 5, 6, 7, 8, 9]
            }
          },
          
        {
          extend: 'print',
          text: '<i class="fa fa-print"></i> Imprimir',
          titleAttr: 'Imprimir',
          title: function() {
              return "LISTA DE EXPEDIENTES";
          },
          className: 'btn btn-print',
          exportOptions: {
            columns: [1, 2, 3, 4, 5, 6, 7,8,9] // Exportar solo hasta la columna 'estado'
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
        { "data": "REGION" },
        { 
            "data": "PROVINCIA",
            "render": function(data, type, row) {
              return "<strong>" + data + "</strong>"; // Aquí agregamos el strong
            }
          },

        {
          "data": null,
          "render": function(data, type, row) {
            return `
              <div>${row.nombre}</div>
              <div style="font-weight: bold; font-size: 20px;">PRECIO: S/. ${row.monto_total}</div>
  
            `;
          }
        },
        { "data": "folios" },
        {
          "data": "ESTADO_EXPE",
          "render": function(data, type, row) {
            let color = '';
        
            switch (data.toUpperCase()) {
              case 'REINGRESADO':
                color = 'warning'; break;
              case 'OBSERVADO':
                color = 'danger'; break;
              case 'EN PROCESO':
                color = 'info'; break;
              case 'EN TRAMITE':
                color = 'primary'; break;
              default:
                color = 'success';
            }
        
            return `<span class="badge bg-${color}">${data}</span>`;
          }
        },

        { "data": "fecha_formateada" },
        {
          "data": "tiempo_transcurrido",
          "render": function(data, type, row) {
            let dias = parseInt(data); // Asegúrate que el valor sea numérico
        
            if (isNaN(dias)) {
              return `<span class="badge bg-secondary">${data}</span>`;
            }
        
            let colorClass = "bg-dark"; // default: 1 día o menos
            if (dias >= 30) {
              colorClass = "bg-danger";
            } else if (dias >= 20) {
              colorClass = "bg-warning text-dark";
            } else if (dias >= 10) {
              colorClass = "bg-success";
            }
        
            return `<span class="badge ${colorClass}">${dias} días</span>`;
          }
        },      

    ],
  
      "language": idioma_espanol,
      select: true
    });
  
    tbl_expedientes.on('draw.td', function() {
      var PageInfo = $("#tabla_expedientes").DataTable().page.info();
      tbl_expedientes.column(0, { page: 'current' }).nodes().each(function(cell, i) {
  
      });
    });
  }


  function Cargar_Select_Regiones() {
    $.ajax({
        url: "../controller/regiones/controlador_cargar_select_regiones.php",
        type: "POST",
        dataType: "json",
        beforeSend: function() {
            console.log("Cargando regiones...");
        }
    })
    .done(function(data) {
        let opciones = "<option value=''>Seleccionar Región</option>";

        if (data.length > 0) {
            data.forEach(region => {
                opciones += `<option value="${region[0]}">${region[1]}</option>`;
            });
        }

        $("#select_depar_buscar").html(opciones);

        // Inicializar Select2
        $("#select_depar_buscar").select2({
            placeholder: "Seleccionar Región",
            allowClear: true,
            width: "100%"
        });

        // Cargar provincias cuando se seleccione una región
        $("#select_depar_buscar").on("change", function() {
            let id_region = $(this).val();
            let target = $(this).attr("id");
            Cargar_Select_Provincia(id_region, target);
        });
    })
    .fail(function(jqXHR, textStatus, errorThrown) {
        console.error("Error al cargar regiones:", textStatus, errorThrown);
    });
}

// Función para cargar las provincias según la región seleccionada
function Cargar_Select_Provincia(id_region, target) {
    if (!id_region) {
        $("#select_provincia_buscar").html("<option value=''>Seleccionar Provincia</option>");
        return;
    }

    $.ajax({
        url: "../controller/provincias/controlador_cargar_select_provincias.php",
        type: "POST",
        data: { id_region: id_region },
        dataType: "json",
        beforeSend: function() {
            console.log("Cargando provincias para la región ID:", id_region);
        }
    })
    .done(function(data) {
        let opciones = "<option value=''>Seleccionar Provincia</option>";

        if (data.length > 0) {
            data.forEach(provincia => {
                opciones += `<option value="${provincia[0]}">${provincia[1]}</option>`;
            });
        } else {
            opciones = "<option value=''>No hay provincias disponibles</option>";
        }

        if (target === "select_depar_buscar") {
            $("#select_provincia_buscar").html(opciones);
        } 
        // Inicializar Select2
        $("#select_provincia_buscar").select2({
            placeholder: "Seleccionar Provincia",
            allowClear: true,
            width: "100%"
        });
    })
    .fail(function(jqXHR, textStatus, errorThrown) {
        console.error("Error al cargar provincias:", textStatus, errorThrown);
    });
}


// Función para cargar los estados
function listar_expedientes_estados() {
  tbl_expedientes = $("#tabla_expedientes").DataTable({
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
      "url": "../controller/expedientes/controlador_listar_expedientes.php",
      type: 'POST'
    },
    dom: 'Bfrtip',
    buttons: [
      {
        extend: 'excelHtml5',
        text: '<i class="fas fa-file-excel"></i> Excel',
        titleAttr: 'Exportar a Excel',
        filename: function() {
          return "LISTA DE EXPEDIENTES";
        },
        title: function() {
            return "LISTA DE EXPEDIENTES";
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
          return "LISTA DE EXPEDIENTES";
        },
        title: function() {
          return "LISTA DE EXPEDIENTES";
        },
        orientation: 'landscape', // 👉 Esto pone el PDF horizontal
        pageSize: 'A4',            // (opcional, recomendado)
        className: 'btn btn-pdf',
        exportOptions: {
          columns: [1, 2, 3, 4, 5, 6, 7] // Tus columnas a exportar
        }
      },
      
      {
        extend: 'print',
        text: '<i class="fa fa-print"></i> Imprimir',
        titleAttr: 'Imprimir',
        title: function() {
            return "LISTA DE EXPEDIENTES";
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

      {
        "data": null,
        "render": function(data, type, row) {
          return `
            <div>${row.nombre}</div>
            <div style="font-weight: bold; font-size: 20px;">PRECIO: S/. ${row.monto_total}</div>

          `;
        }
      },
      { "data": "folios" },
      {
        "data": "ESTADO_EXPE",
        "render": function(data, type, row) {
          let color = '';
      
          switch (data.toUpperCase()) {
            case 'REINGRESADO':
              color = 'warning'; break;
            case 'OBSERVADO':
              color = 'danger'; break;
            case 'EN PROCESO':
              color = 'info'; break;
            case 'EN TRAMITE':
              color = 'primary'; break;
            default:
              color = 'success';
          }
      
          return `<span class="badge bg-${color}">${data}</span>`;
        }
      },

      { "data": "fecha_formateada" },
      {
        "data": "tiempo_transcurrido",
        "render": function(data, type, row) {
          let dias = parseInt(data); // Asegúrate que el valor sea numérico
      
          if (isNaN(dias)) {
            return `<span class="badge bg-secondary">${data}</span>`;
          }
      
          let colorClass = "bg-dark"; // default: 1 día o menos
          if (dias >= 30) {
            colorClass = "bg-danger";
          } else if (dias >= 20) {
            colorClass = "bg-warning text-dark";
          } else if (dias >= 10) {
            colorClass = "bg-success";
          }
      
          return `<span class="badge ${colorClass}">${dias} días</span>`;
        }
      },      
      
    ],

    "language": idioma_espanol,
    select: true
  });

  tbl_expedientes.on('draw.td', function() {
    var PageInfo = $("#tabla_expedientes").DataTable().page.info();
    tbl_expedientes.column(0, { page: 'current' }).nodes().each(function(cell, i) {

    });
  });
}


function listar_expedientes_filtro_estados() {
    let fechaini = document.getElementById('txt_fecha_desde').value;
    let fechafin = document.getElementById('txt_fecha_hasta').value;
    let estado = document.getElementById('select_estado_buscar').value;

    tbl_expedientes = $("#tabla_expedientes").DataTable({
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
        "url": "../controller/expedientes/controlador_listar_expedientes_fecha_estado.php",
        type: 'POST',
        data:{
          fechaini:fechaini,
          fechafin:fechafin,
          estado:estado,
        }
      },
      dom: 'Bfrtip',
      buttons: [
        {
          extend: 'excelHtml5',
          text: '<i class="fas fa-file-excel"></i> Excel',
          titleAttr: 'Exportar a Excel',
          filename: function() {
            return "LISTA DE EXPEDIENTES";
          },
          title: function() {
              return "LISTA DE EXPEDIENTES";
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
              return "LISTA DE EXPEDIENTES";
            },
            title: function() {
              return "LISTA DE EXPEDIENTES";
            },
            orientation: 'landscape', // 👉 Esto pone el PDF horizontal
            pageSize: 'A4',            // (opcional, recomendado)
            className: 'btn btn-pdf',
            exportOptions: {
              columns: [1, 2, 3, 4, 5, 6, 7] // Tus columnas a exportar
            }
          },
          
        {
          extend: 'print',
          text: '<i class="fa fa-print"></i> Imprimir',
          titleAttr: 'Imprimir',
          title: function() {
              return "LISTA DE EXPEDIENTES";
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
  
        {
          "data": null,
          "render": function(data, type, row) {
            return `
              <div>${row.nombre}</div>
              <div style="font-weight: bold; font-size: 20px;">PRECIO: S/. ${row.monto_total}</div>
  
            `;
          }
        },
        { "data": "folios" },
        {
          "data": "ESTADO_EXPE",
          "render": function(data, type, row) {
            let color = '';
        
            switch (data.toUpperCase()) {
              case 'REINGRESADO':
                color = 'warning'; break;
              case 'OBSERVADO':
                color = 'danger'; break;
              case 'EN PROCESO':
                color = 'info'; break;
              case 'EN TRAMITE':
                color = 'primary'; break;
              default:
                color = 'success';
            }
        
            return `<span class="badge bg-${color}">${data}</span>`;
          }
        },
  
        { "data": "fecha_formateada" },
        {
          "data": "tiempo_transcurrido",
          "render": function(data, type, row) {
            let dias = parseInt(data); // Asegúrate que el valor sea numérico
        
            if (isNaN(dias)) {
              return `<span class="badge bg-secondary">${data}</span>`;
            }
        
            let colorClass = "bg-dark"; // default: 1 día o menos
            if (dias >= 30) {
              colorClass = "bg-danger";
            } else if (dias >= 20) {
              colorClass = "bg-warning text-dark";
            } else if (dias >= 10) {
              colorClass = "bg-success";
            }
        
            return `<span class="badge ${colorClass}">${dias} días</span>`;
          }
        },      
        
      ],
  
      "language": idioma_espanol,
      select: true
    });
  
    tbl_expedientes.on('draw.td', function() {
      var PageInfo = $("#tabla_expedientes").DataTable().page.info();
      tbl_expedientes.column(0, { page: 'current' }).nodes().each(function(cell, i) {
  
      });
    });
  }


  function listar_expedientes2() {
    tbl_expedientes = $("#tabla_expedientes").DataTable({
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
        "url": "../controller/expedientes/controlador_listar_expedientes.php",
        type: 'POST'
      },
      dom: 'Bfrtip',
      buttons: [
        {
          extend: 'excelHtml5',
          text: '<i class="fas fa-file-excel"></i> Excel',
          titleAttr: 'Exportar a Excel',
          filename: function() {
            return "LISTA DE EXPEDIENTES";
          },
          title: function() {
              return "LISTA DE EXPEDIENTES";
          },
          className: 'btn btn-excel',
          exportOptions: {
            columns: [1, 2, 3, 4, 5, 6, 7,8,9,10] // Exportar solo hasta la columna 'estado'
          }
        },
        {
            extend: 'pdfHtml5',
            text: '<i class="fas fa-file-pdf"></i> PDF',
            titleAttr: 'Exportar a PDF',
            filename: function() {
              return "LISTA DE EXPEDIENTES";
            },
            title: function() {
              return "LISTA DE EXPEDIENTES";
            },
            orientation: 'landscape', // 👉 PDF horizontal
            pageSize: 'A4',            // (opcional)
            className: 'btn btn-pdf',
            exportOptions: {
              columns: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
            }
          },
          
        {
          extend: 'print',
          text: '<i class="fa fa-print"></i> Imprimir',
          titleAttr: 'Imprimir',
          title: function() {
              return "LISTA DE EXPEDIENTES";
          },
          className: 'btn btn-print',
          exportOptions: {
            columns: [1, 2, 3, 4, 5, 6, 7,8,9,10] // Exportar solo hasta la columna 'estado'
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
          { "data": "REGION" },
          { "data": "PROVINCIA" },
          { 
              "data": "DISTRITO",
              "render": function(data, type, row) {
                return "<strong>" + data + "</strong>"; // Aquí agregamos el strong
              }
            },
  
          {
            "data": null,
            "render": function(data, type, row) {
              return `
                <div>${row.nombre}</div>
                <div style="font-weight: bold; font-size: 20px;">PRECIO: S/. ${row.monto_total}</div>
    
              `;
            }
          },
          { "data": "folios" },
          {
            "data": "ESTADO_EXPE",
            "render": function(data, type, row) {
              let color = '';
          
              switch (data.toUpperCase()) {
                case 'REINGRESADO':
                  color = 'warning'; break;
                case 'OBSERVADO':
                  color = 'danger'; break;
                case 'EN PROCESO':
                  color = 'info'; break;
                case 'EN TRAMITE':
                  color = 'primary'; break;
                default:
                  color = 'success';
              }
          
              return `<span class="badge bg-${color}">${data}</span>`;
            }
          },
  
          { "data": "fecha_formateada" },
          {
            "data": "tiempo_transcurrido",
            "render": function(data, type, row) {
              let dias = parseInt(data); // Asegúrate que el valor sea numérico
          
              if (isNaN(dias)) {
                return `<span class="badge bg-secondary">${data}</span>`;
              }
          
              let colorClass = "bg-dark"; // default: 1 día o menos
              if (dias >= 30) {
                colorClass = "bg-danger";
              } else if (dias >= 20) {
                colorClass = "bg-warning text-dark";
              } else if (dias >= 10) {
                colorClass = "bg-success";
              }
          
              return `<span class="badge ${colorClass}">${dias} días</span>`;
            }
          },      
  
      ],
  
      "language": idioma_espanol,
      select: true
    });
  
    tbl_expedientes.on('draw.td', function() {
      var PageInfo = $("#tabla_expedientes").DataTable().page.info();
      tbl_expedientes.column(0, { page: 'current' }).nodes().each(function(cell, i) {
  
      });
    });
  }

  //FILTROS DE DISTRITO


  function Cargar_Select_Regiones2() {
    $.ajax({
        url: "../controller/regiones/controlador_cargar_select_regiones.php",
        type: "POST",
        dataType: "json",
        beforeSend: function () {
            console.log("Cargando regiones...");
        }
    })
    .done(function (data) {
        let opciones = "<option value=''>Seleccionar Región</option>";
  
        if (data.length > 0) {
            data.forEach(region => {
                // Verificamos si el valor es 1 para ponerlo como seleccionado
                const selected = region[0] === "1" ? " selected" : "";
                opciones += `<option value="${region[0]}"${selected}>${region[1]}</option>`;
            });
        }
  
        // Seleccionar los elementos por ID
        let $regiones = $("#select_depar_buscar3");
        
        // Actualizar HTML
        $regiones.html(opciones);
        
        // Si usa Select2, actualizar correctamente
        $regiones.each(function() {
            let $this = $(this);
            if ($this.hasClass("select2-hidden-accessible")) {
                $this.select2("destroy");
                $this.html(opciones);
                $this.select2({
                    placeholder: "Seleccionar Región",
                    allowClear: true,
                    width: "100%"
                });
                
                // Forzar la actualización del valor seleccionado para Select2
                $this.val("1").trigger("change");
            } else {
                // Para selects normales, establece el valor directamente
                $this.val("1");
            }
            
            // Asegurarse de que tiene la clase form-control
            if (!$this.hasClass("form-control")) {
                $this.addClass("form-control");
            }
        });
  
        // Cargar provincias cuando se seleccione una región
        $regiones.off("change").on("change", function () {
            let id_region = $(this).val();
            let target = $(this).attr("id");
            Cargar_Select_Provincia2(id_region, target);
        });
        
        // Cargar automáticamente las provincias para la región 1 al inicializar
        Cargar_Select_Provincia2("1", "select_depar_buscar3");

    })
    .fail(function (jqXHR, textStatus, errorThrown) {
        console.error("Error al cargar regiones:", textStatus, errorThrown);
        $("#select_depar_buscar3").html("<option value=''>Error al cargar regiones</option>");
    });
  }
  
  // Función para cargar las provincias según la región seleccionada
  // Función para cargar las provincias según la región seleccionada
  function Cargar_Select_Provincia2(id_region, target) {
    // Determinar qué select corresponde al target
    let $select;
    
    if (target === "select_depar_buscar3") {
        $select = $("#select_provincia_buscar3");
    } 
    
    // Limpiar el select si no hay región seleccionada
    if (!id_region) {
        $select.html("<option value=''>Seleccionar Provincia</option>");
        
        // Si usa Select2, actualizar correctamente
        if ($select.hasClass("select2-hidden-accessible")) {
            $select.val("").trigger("change");
        }
        
        // También resetear el select de distritos
        $("#select_provincia_buscar3").html("<option value=''>Seleccionar Distrito</option>");
        if ($("#select_provincia_buscar3").hasClass("select2-hidden-accessible")) {
            $("#select_provincia_buscar3").val("").trigger("change");
        }
        
        return;
    }
  
    // Cargar provincias
    $.ajax({
        url: "../controller/provincias/controlador_cargar_select_provincias.php",
        type: "POST",
        data: { id_region: id_region },
        dataType: "json",
        beforeSend: function () {
            console.log("Cargando provincias para la región ID:", id_region);
        }
    })
    .done(function (data) {
        // Preparar opciones
        let opciones = "<option value=''>Seleccionar Provincia</option>";
        
        if (data && data.length > 0) {
            data.forEach(provincia => {
                opciones += `<option value="${provincia[0]}">${provincia[1]}</option>`;
            });
        } else {
            console.log("No se encontraron provincias para esta región");
        }
        
        // Actualizar HTML del select
        $select.html(opciones);
        
        // Si usa Select2, actualizar correctamente
        if ($select.hasClass("select2-hidden-accessible")) {
            $select.select2("destroy");
            $select.html(opciones);
            $select.select2({
                placeholder: "Seleccionar Provincia",
                allowClear: true,
                width: "100%"
            });
        }
        
        // Asegurarse de que tiene la clase form-control
        if (!$select.hasClass("form-control")) {
            $select.addClass("form-control");
        }
        
        // Configurar evento para cargar distritos
        $select.off("change").on("change", function() {
            let id_provincia = $(this).val();
            console.log("Provincia seleccionada:", id_provincia);
            
            // Solo cargar distritos si hay un ID de provincia
            if (id_provincia) {
                Cargar_Select_Distrito2(id_provincia);
            } else {
                // Limpiar el select de distritos si no hay provincia seleccionada
                $("#select_provincia_buscar3").html("<option value=''>Seleccionar Distrito</option>");
                if ($("#select_provincia_buscar3").hasClass("select2-hidden-accessible")) {
                    $("#select_provincia_buscar3").val("").trigger("change");
                }
            }
        });
    })
    .fail(function (jqXHR, textStatus, errorThrown) {
        console.error("Error al cargar provincias:", textStatus, errorThrown);
        $select.html("<option value=''>Error al cargar provincias</option>");
        
        // Si usa Select2, actualizar correctamente
        if ($select.hasClass("select2-hidden-accessible")) {
            $select.val("").trigger("change");
        }
    });
  }
  
  
  
  // Función para cargar los distritos según la provincia seleccionada
  function Cargar_Select_Distrito2(id_provincia) {
    let $select = $("#select_distrito_buscar3");
    
    if (!id_provincia) {
        if ($select.hasClass("select2-hidden-accessible")) {
            $select.html("<option value=''>Seleccionar Distrito</option>");
            $select.val("").trigger("change");
        } else {
            $select.html("<option value=''>Seleccionar Distrito</option>");
        }
        return;
    }
  
    $.ajax({
        url: "../controller/distritos/controlador_cargar_select_distritos.php",
        type: "POST",
        data: { id_provincia: id_provincia },
        dataType: "json",
        beforeSend: function () {
            console.log("Cargando distritos para la provincia ID:", id_provincia);
        }
    })
    .done(function (response) {
        let opciones = "<option value=''>Seleccionar Distrito</option>";
        const data = response.data || [];
  
        if (data.length > 0) {
            data.forEach(distrito => {
                opciones += `<option value="${distrito.id_distritos}">${distrito.nombre}</option>`;
            });
        } else {
            console.log("No se encontraron distritos para esta provincia");
        }
  
        // Verificar si el select tiene Select2 inicializado
        let isInitialized = $select.hasClass("select2-hidden-accessible");
        
        // Si está inicializado, destruirlo primero
        if (isInitialized) {
            $select.select2("destroy");
        }
        
        // Actualizar HTML
        $select.html(opciones);
        
        // Reinicializar Select2
        $select.select2({
            placeholder: "Seleccionar Distrito",
            allowClear: true,
            width: "100%"
        });
    })
    .fail(function (jqXHR, textStatus, errorThrown) {
        console.error("Error al cargar distritos:", textStatus, errorThrown);
        
        if ($select.hasClass("select2-hidden-accessible")) {
            $select.html("<option value=''>Error al cargar distritos</option>");
            $select.val("").trigger("change");
        } else {
            $select.html("<option value=''>Error al cargar distritos</option>");
        }
    });
  }


  function listar_expedientes_filtro_distrito() {
    let fechaini = document.getElementById('txt_fecha_desde').value;
    let fechafin = document.getElementById('txt_fecha_hasta').value;
    let distrito = document.getElementById('select_distrito_buscar3').value;

    tbl_expedientes = $("#tabla_expedientes").DataTable({
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
        "url": "../controller/expedientes/controlador_listar_expedientes_fecha_distrito.php",
        type: 'POST',
        data:{
          fechaini:fechaini,
          fechafin:fechafin,
          distrito:distrito,
        }
      },
      dom: 'Bfrtip',
      buttons: [
        {
          extend: 'excelHtml5',
          text: '<i class="fas fa-file-excel"></i> Excel',
          titleAttr: 'Exportar a Excel',
          filename: function() {
            return "LISTA DE EXPEDIENTES";
          },
          title: function() {
              return "LISTA DE EXPEDIENTES";
          },
          className: 'btn btn-excel',
          exportOptions: {
            columns: [1, 2, 3, 4, 5, 6, 7,8,9,10] // Exportar solo hasta la columna 'estado'
          }
        },
        {
            extend: 'pdfHtml5',
            text: '<i class="fas fa-file-pdf"></i> PDF',
            titleAttr: 'Exportar a PDF',
            filename: function() {
              return "LISTA DE EXPEDIENTES";
            },
            title: function() {
              return "LISTA DE EXPEDIENTES";
            },
            orientation: 'landscape', // 👉 Esto pone el PDF horizontal
            pageSize: 'A4',            // (opcional, recomendado)
            className: 'btn btn-pdf',
            exportOptions: {
                columns: [1, 2, 3, 4, 5, 6, 7,8,9,10] // Exportar solo hasta la columna 'estado'
            }
          },
          
        {
          extend: 'print',
          text: '<i class="fa fa-print"></i> Imprimir',
          titleAttr: 'Imprimir',
          title: function() {
              return "LISTA DE EXPEDIENTES";
          },
          className: 'btn btn-print',
          exportOptions: {
            columns: [1, 2, 3, 4, 5, 6, 7,8,9,10] // Exportar solo hasta la columna 'estado'
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
        { "data": "REGION" },
        { "data": "PROVINCIA" },
        { 
            "data": "DISTRITO",
            "render": function(data, type, row) {
              return "<strong>" + data + "</strong>"; // Aquí agregamos el strong
            }
          },

        {
          "data": null,
          "render": function(data, type, row) {
            return `
              <div>${row.nombre}</div>
              <div style="font-weight: bold; font-size: 20px;">PRECIO: S/. ${row.monto_total}</div>
  
            `;
          }
        },
        { "data": "folios" },
        {
          "data": "ESTADO_EXPE",
          "render": function(data, type, row) {
            let color = '';
        
            switch (data.toUpperCase()) {
              case 'REINGRESADO':
                color = 'warning'; break;
              case 'OBSERVADO':
                color = 'danger'; break;
              case 'EN PROCESO':
                color = 'info'; break;
              case 'EN TRAMITE':
                color = 'primary'; break;
              default:
                color = 'success';
            }
        
            return `<span class="badge bg-${color}">${data}</span>`;
          }
        },

        { "data": "fecha_formateada" },
        {
          "data": "tiempo_transcurrido",
          "render": function(data, type, row) {
            let dias = parseInt(data); // Asegúrate que el valor sea numérico
        
            if (isNaN(dias)) {
              return `<span class="badge bg-secondary">${data}</span>`;
            }
        
            let colorClass = "bg-dark"; // default: 1 día o menos
            if (dias >= 30) {
              colorClass = "bg-danger";
            } else if (dias >= 20) {
              colorClass = "bg-warning text-dark";
            } else if (dias >= 10) {
              colorClass = "bg-success";
            }
        
            return `<span class="badge ${colorClass}">${dias} días</span>`;
          }
        },      

    ],

  
      "language": idioma_espanol,
      select: true
    });
  
    tbl_expedientes.on('draw.td', function() {
      var PageInfo = $("#tabla_expedientes").DataTable().page.info();
      tbl_expedientes.column(0, { page: 'current' }).nodes().each(function(cell, i) {
  
      });
    });
  }
