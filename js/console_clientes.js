var tbl_clientes;
function listar_clientes() {
  tbl_clientes = $("#tabla_clientes").DataTable({
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
      "url": "../controller/clientes/controlador_listar_clientes.php",
      type: 'POST'
    },
    dom: 'Bfrtip',
    buttons: [
      {
        extend: 'excelHtml5',
        text: '<i class="fas fa-file-excel"></i> Excel',
        titleAttr: 'Exportar a Excel',
        filename: function() {
          return "LISTA DE CLIENTES";
        },
        title: function() {
            return "LISTA DE CLIENTES";
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
            return "LISTA DE CLIENTES";
        },
        title: function() {
            return "LISTA DE CLIENTES";
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
            return "LISTA DE CLIENTES";
        },
        className: 'btn btn-print',
        exportOptions: {
          columns: [1, 2, 3, 4, 5, 6, 7] // Exportar solo hasta la columna 'estado'
        }
      }
    ],

    "columns": [
      { "defaultContent": "" },
      { "data": "tipo_documento" },
      { "data": "nro_documento" },

      { 
        "data": "CLIENTE",
        "render": function(data, type, row) {
          return "<strong>" + data + "</strong>";
        }
      },
      { "data": "celular" },
      { "data": "direccion" },
      { "data": "email" },

      { "data": "fecha_formateada" },

      {
        "defaultContent": "<button class='ver_expe btn btn-warning btn-sm' title='Ver expedientes presentados'><i class='fa fa-file'></i> Ver expedientes</button> <button class='mostrar btn btn-success btn-sm' title='Ver datos'><i class='fa fa-eye'></i> Mostrar</button> <button class='editar btn btn-primary btn-sm' title='Editar datos de cliente'><i class='fa fa-edit'></i> Editar</button> <button class='eliminar btn btn-danger btn-sm' title='Eliminar datos de cliente'><i class='fa fa-trash'></i> Eliminar</button>"
      }
    ],

    "language": idioma_espanol,
    select: true
  });

  tbl_clientes.on('draw.td', function() {
    var PageInfo = $("#tabla_clientes").DataTable().page.info();
    tbl_clientes.column(0, { page: 'current' }).nodes().each(function(cell, i) {
      cell.innerHTML = i + 1 + PageInfo.start;
    });
  });
}

$('#tabla_clientes').on('click', '.editar', async function () {
  let data = tbl_clientes.row($(this).parents('tr')).data();
  if (tbl_clientes.row(this).child.isShown()) {
    data = tbl_clientes.row(this).data();
  }

  $("#modal_editar").modal('show');
  
  // Desactivar selects temporalmente para evitar interacción mientras carga
  $('#select_region_editar, #txt_provincia_editar, #select_distrito_editar').prop('disabled', true);

  // Setea campos normales
  $('#txt_idcliente').val(data.id_cliente);
  $('#select_tipo_doc').val(data.tipo_documento);
  $('#txt_nro_doc').val(data.nro_documento);
  $('#txt_nombre').val(data.nombres);
  $('#txt_apellido').val(data.apellidos);
  $('#txt_celular').val(data.celular);
  $('#txt_telefono').val(data.telefono);
  $('#txt_direccion').val(data.direccion);
  $('#txt_email').val(data.email);
  $('#txt_obser').val(data.observacion);

  try {
    await cargarRegionesYSeleccionarEditar(data.id_region);
    await cargarProvinciasYSeleccionarEditar(data.id_region, data.id_provincia);
    await cargarDistritosYSeleccionarEditar(data.id_provincia, data.id_distrito);
  } catch (e) {
    console.error("Error al cargar selects:", e);
  } finally {
    // Reactivar selects
    $('#select_region_editar, #txt_provincia_editar, #select_distrito_editar').prop('disabled', false);
  }
});


$('#tabla_clientes').on('click','.mostrar',function(){
  var data = tbl_clientes.row($(this).parents('tr')).data();

  if(tbl_clientes.row(this).child.isShown()){
      var data = tbl_clientes.row(this).data();
  }
  $("#modal_mostrar").modal('show');
  document.getElementById('txt_idcliente_mostrar').value=data.id_cliente;
  document.getElementById('select_tipo_doc_mostrar').value=data.tipo_documento;
  document.getElementById('txt_nro_doc_mostrar').value=data.nro_documento;
  document.getElementById('txt_nombre_mostrar').value=data.nombres;
  document.getElementById('txt_apellido_mostrar').value=data.apellidos;
  document.getElementById('txt_celular_mostrar').value=data.apellidos;
  document.getElementById('txt_telefono_mostrar').value=data.telefono;
  document.getElementById('txt_direccion_mostrar').value=data.direccion;
  document.getElementById('txt_email_mostrar').value=data.email;
  document.getElementById('txt_region_mostrar').value=data.REGION;
  document.getElementById('txt_provincia_mostrar').value=data.PROVINCIA;
  document.getElementById('txt_distrito_mostrar').value=data.DISTRITO;

  document.getElementById('txt_obser_mostrar').value=data.observacion;
})


function AbrirRegistro(){
  $("#modal_registro").modal({backdrop:'static',keyboard:false})
  $("#modal_registro").modal('show');
}

//EDITAR CLIENTE
function Modificar_Cliente(){
  let id = document.getElementById('txt_idcliente').value;
  let tipo = document.getElementById('select_tipo_doc').value;
  let nro = document.getElementById('txt_nro_doc').value;
  let nombre = document.getElementById('txt_nombre').value;
  let apellido = document.getElementById('txt_apellido').value;
  let celular = document.getElementById('txt_celular').value;
  let telefono = document.getElementById('txt_telefono').value;
  let direccion = document.getElementById('txt_direccion').value;
  let email = document.getElementById('txt_email').value;
  let iddistri = document.getElementById('select_distrito_editar').value;
  let ober = document.getElementById('txt_obser').value;


  if(tipo.length==0||nro.length==0||nombre.length==0||apellido.length==0||celular.length==0||id.length==0){
    return Swal.fire("Mensaje de Advertencia","Tiene campos vacios, revise los campos obligatorios","warning");
  }
  $.ajax({
    "url":"../controller/clientes/controlador_modificar_clientes.php",
    type:'POST',
    data:{
      id:id,
      tipo:tipo,
      nro:nro,
      nombre:nombre,
      apellido:apellido,
      celular:celular,
      telefono:telefono,
      direccion:direccion,
      email:email,
      iddistri:iddistri,
      ober:ober,
    }
  }).done(function(resp){
    if(resp>0){
      if(resp==1){
        Swal.fire("Mensaje de Confirmación","Datos actualizados del cliente: <b>"+nombre+" "+apellido+"</b>","success").then((value)=>{
          tbl_clientes.ajax.reload();
        $("#modal_editar").modal('hide');
        });
      }else{
        Swal.fire("Mensaje de Advertencia","El DNI del cliente ingresado ya se encuentra en la base de datos, revise por favor, no se puede insertar un DNI repetido","warning");
      }
    }else{
      return Swal.fire("Mensaje de Error","No se completo la actualización","error");

    }
  })
}

//ELIMINAR AREAS
function Eliminar_cliente(id){
  $.ajax({
    "url":"../controller/clientes/controlador_eliminar_clientes.php",
    type:'POST',
    data:{
      id:id
    }
  }).done(function(resp){
    if(resp>0){
        Swal.fire("Mensaje de Confirmación","Se elimino el cliente con exito","success").then((value)=>{
          tbl_clientes.ajax.reload();

        });
    }else{
      return Swal.fire("Mensaje de Advetencia","No se puede eliminar este cliente por que tiene expedientes registrados, verifique por favor","warning");

    }
  })
}

//ENVIANDO AL BOTON DELETE
$('#tabla_clientes').on('click','.eliminar',function(){
  var data = tbl_clientes.row($(this).parents('tr')).data();

  if(tbl_clientes.row(this).child.isShown()){
      var data = tbl_clientes.row(this).data();
  }
  Swal.fire({
    title: 'Desea eliminar al cliente con el nombre: '+data.nombres+' '+data.apellidos+'?',
    text: "Una vez aceptado el cliente sera eliminado!!!",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Si, Eliminar'
  }).then((result) => {
    if (result.isConfirmed) {
        Eliminar_cliente(data.id_cliente);
    }
  })
})

//VER EXPEDIENTES POR CLIENTE

$('#tabla_clientes').on('click','.ver_expe',function(){
  var data = tbl_clientes.row($(this).parents('tr')).data();

  if(tbl_clientes.row(this).child.isShown()){
      var data = tbl_clientes.row(this).data();
  }
$("#modal_ver_expedientes").modal('show');

  document.getElementById('lb_titulo_historial_requi').innerHTML="<b>TIPO DOCUMENTO - NRO DOCUMENTO:</b> "+data.tipo_documento+" - "+data.nro_documento;
  document.getElementById('lb_titulo_historial2_requi').innerHTML="<b>CLIENTE:</b> "+data.CLIENTE+"";

  listar_expe(data.id_cliente);

})
// VISTA DE REQUISITOS DE EXPEDIENTE
var tbl_expedientes;
function listar_expe(id) {
  tbl_expedientes = $("#tabla_ver_requi").DataTable({
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
          "url": "../controller/expedientes/controlador_listar_expedientes_cliente.php",
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
          filename: "LISTA_DE_EXPEDIENTES",
          title: "LISTA DE EXPEDIENTES",
          className: 'btn btn-success' 
        },
        {
          extend: 'pdfHtml5',
          text: '<i class="fas fa-file-pdf"></i> PDF',
          titleAttr: 'Exportar a PDF',
          filename: "LISTA_DE_EXPEDIENTES",
          title: "LISTA DE EXPEDIENTES",
          className: 'btn btn-danger'
        },
        {
          extend: 'print',
          text: '<i class="fa fa-print"></i> Imprimir',
          titleAttr: 'Imprimir',
          title: "LISTA DE EXPEDIENTES",
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
  { "data": "nro_expediente" },
  { "data": "nombre" },
 {
        "data": null,
        "render": function(data, type, row) {
          return `
            <div style="font-weight: bold; font-size: 20px;">S/. ${row.monto_total}</div>

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


function cargarRegionesYSeleccionarEditar(id_region) {
  return $.ajax({
    url: "../controller/regiones/controlador_cargar_select_regiones.php",
    type: "POST",
    dataType: "json"
  }).then(data => {
    let opciones = "<option value=''>Seleccionar Región</option>";
    data.forEach(r => {
      opciones += `<option value="${r[0]}" ${r[0] == id_region ? 'selected' : ''}>${r[1]}</option>`;
    });
    $('#select_region_editar').html(opciones);
  });
}

function cargarProvinciasYSeleccionarEditar(id_region, id_provincia) {
  return $.ajax({
    url: "../controller/provincias/controlador_cargar_select_provincias.php",
    type: "POST",
    data: { id_region },
    dataType: "json"
  }).then(data => {
    let opciones = "<option value=''>Seleccionar Provincia</option>";
    data.forEach(p => {
      opciones += `<option value="${p[0]}" ${p[0] == id_provincia ? 'selected' : ''}>${p[1]}</option>`;
    });
    $('#txt_provincia_editar').html(opciones);
  });
}

function cargarDistritosYSeleccionarEditar(id_provincia, id_distrito) {
  return $.ajax({
    url: "../controller/distritos/controlador_cargar_select_distritos.php",
    type: "POST",
    data: { id_provincia },
    dataType: "json"
  }).then(response => {
    let opciones = "<option value=''>Seleccionar Distrito</option>";
    const data = response.data || [];
    data.forEach(d => {
      opciones += `<option value="${d.id_distritos}" ${d.id_distritos == id_distrito ? 'selected' : ''}>${d.nombre}</option>`;
    });
    $('#select_distrito_editar').html(opciones);
  });
}
