function Iniciar_Sesion(){
    recuerdame();
    let usu = document.getElementById("txt_usuario").value;
    let con = document.getElementById("txt_contra").value;
    if(usu.length==0 || con.length==0){
       return  Swal.fire({
        icon: 'warning',
        title: 'Mensaje de Advertencia',
        text: 'Llene todo los campos de la sesión',
        heightAuto: false
      });
    }
    $.ajax({
        url:'controller/usuario/controlador_iniciar_sesion.php',
        type: 'POST',
        data:{
            u:usu,
            c:con
        }
    }).done(function(resp){
       let data = JSON.parse(resp)
       if(data.length>0){
            if(data[0][7]=="INACTIVO"){
                return  Swal.fire({
                    icon: 'warning',
                    title: 'Mensaje de Advertencia',
                    text: 'El usuario: '+usu+' se encuentra inactivo',
                    heightAuto: false
                  });  
            }$.ajax({
                url:'controller/usuario/controlador_crear_sesion.php',
                type: 'POST',
                data:{
                    idusuario:data[0][0],
                    nombres:data[0][2], //solo nombre
                    solonombres:data[0][4], // nombres completos
                    usuario:data[0][7],
                    rol:data[0][9],   
                    foto:data[0][15], // foto de usuario
                    foto_empresa:data[0][17],                  // foto de empresa
                    razon:data[0][18]       // razón de la empresa

                }
            }).done(function(resp){
                let timerInterval
                Swal.fire({
                  title: 'Bienvenido al Sistema',
                  html: 'Seras redireccionado en <b></b> milliseconds.',
                  icon: 'success',
                  timer: 1200,
                  timerProgressBar: true,
                  heightAuto: false,
                  didOpen: () => {
                    Swal.showLoading()
                    const b = Swal.getHtmlContainer().querySelector('b')
                    timerInterval = setInterval(() => {
                      b.textContent = Swal.getTimerLeft()
                    }, 100)
                  },
                  willClose: () => {
                    clearInterval(timerInterval)
                  }
                }).then((result) => {
                  /* Read more about handling dismissals below */
                  if (result.dismiss === Swal.DismissReason.timer) {
                    location.reload();
                  }
                })            
            })
       }else{
        Swal.fire({
            icon: 'error',
            title: 'Mensaje de Error',
            text: 'Usuario o Contraseña Incorrectos',
            heightAuto: false
          });

       }
    })
}

function recuerdame(){
    if(rmcheck.checked && usuarioInput.value !="" && passInput.value !=""){
        localStorage.usuario     = usuarioInput.value;
        localStorage.pass        = passInput.value;
        localStorage.checkbox    = rmcheck.value;
    }else{
        localStorage.usuario     = "";
        localStorage.pass        = "";
        localStorage.checkbox    = "";
    }
}

var tbl_usuario;
function listar_usuario(){
  tbl_usuario = $("#tabla_usuario").DataTable({
    pagingType: 'full_numbers',
    scrollCollapse: true,
    responsive: true,
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
          "url":"../controller/usuario/controlador_listar_usuario.php",
          type:'POST'
      },
      dom: 'Bfrtip',       
    
      buttons: [ 
        {
          extend: 'excelHtml5',
          text: '<i class="fas fa-file-excel"></i> Excel',
          titleAttr: 'Exportar a Excel',
          filename: "LISTA DE USUARIOS",
          title: "LISTA DE USUARIOS",
          className: 'btn btn-excel',
          exportOptions: {
            columns: [ 1, 3, 4, 5, 6, 7, 8,9] // Exportar solo hasta la columna 'estado'
          }
        },
        {
          extend: 'pdfHtml5',
          text: '<i class="fas fa-file-pdf"></i> PDF',
          titleAttr: 'Exportar a PDF',
          filename: "LISTA DE USUARIOS",
          title: "LISTA DE USUARIOS",
          className: 'btn btn-pdf',
          orientation: 'landscape', // <-- Establece la orientación en horizontal
          pageSize: 'A4', // <-- Especifica el tamaño de la página
          exportOptions: {
            columns: [ 1, 3, 4, 5, 6, 7, 8,9] // Exportar solo hasta la columna 'estado'
          }
        },
        {
          extend: 'print',
          text: '<i class="fa fa-print"></i> Imprimir',
          titleAttr: 'Imprimir',
          title: "LISTA DE USUARIOS",
          className: 'btn btn-print',
          exportOptions: {
            columns: [ 1, 3, 4, 5, 6, 7, 8,9] // Exportar solo hasta la columna 'estado'
          }
        }
      ],
      "columns":[
        {"defaultContent":""},
        {"data":"dni_usuario"},
        {"data":"usu_foto",
          render: function(data,type,row){
                  if(data=='controller/usuario/fotos/'){
                    return '<img src="../img/vacio.png" class="img img-responsive" style="width:40px">';
                  }else{
                    return '<img src="../'+data+'" class="img img-responsive" style="width:40px">';
                  }
              }   
        }, 
        {"data":"USUARIO"},
        {"data":"usu_usuario"},
        {"data":"usu_email"},
        {"data":"usu_telefono"},
        {"data":"usu_rol"},
        {"data":"fecha_formateada"},
        {"data":"usu_estatus",
            render: function(data,type,row){
                    if(data=='ACTIVO'){
                    return '<span class="badge bg-success">ACTIVO</span>';
                    }else{
                    return '<span class="badge bg-danger">INACTIVO</span>';
                    }
            }   
        },
        {"data":"usu_estatus",
            render: function(data,type,row){
                    if(data=='ACTIVO'){
                    return "<button class='editar btn btn-primary btn-sm' title='Editar datos de usuario'><i class='fa fa-edit'></i></button>&nbsp;<button class='contra btn btn-warning btn-sm' title='Cambiar contraseña de usuario'><i class='fas fa-key'></i></button>&nbsp;<button class='btn btn-success btn-sm' disabled title='Activar usuario'><i class='fa fa-check-circle'></i></button>&nbsp;<button class='desactivar btn btn-danger btn-sm' title='Desactivar usuario'><i class='fa fa-times-circle'></i></button>";
                    }else{
                    return "<button class='editar btn btn-primary btn-sm' title='Editar datos de usuario'><i class='fa fa-edit'></i></button>&nbsp;<button class='contra btn btn-warning btn-sm' title='Cambiar contraseña de usuario'><i class='fas fa-key'></i></button>&nbsp;<button class='activar btn btn-success btn-sm' title='Activar usuario'><i class='fa fa-check-circle'></i></button>&nbsp;<button class='btn btn-danger btn-sm' disabled title='Desactivar usuario'><i class='fa fa-times-circle'></i></button>";
                    }
            }   
        }
    ],

    "language":idioma_espanol,
    select: true
});
tbl_usuario.on('draw.td',function(){
  var PageInfo = $("#tabla_usuario").DataTable().page.info();
  tbl_usuario.column(0, {page: 'current'}).nodes().each(function(cell, i){
    cell.innerHTML = i + 1 + PageInfo.start;
  });
});
}
function AbrirRegistro(){
  $("#modal_registro").modal({backdrop:'static',keyboard:false})
  $("#modal_registro").modal('show');
}

//ABRIR MODAL EDITAR
$('#tabla_usuario').on('click','.editar',function(){
  var data = tbl_usuario.row($(this).parents('tr')).data();

  if(tbl_usuario.row(this).child.isShown()){
      var data = tbl_usuario.row(this).data();
  }
  $("#modal_editar").modal('show');
  document.getElementById('id_usuario').value=data.id_usuario;
  document.getElementById('txt_dni_editar').value=data.dni_usuario;
  document.getElementById('txt_nomb_editar').value=data.usu_nombre;
  document.getElementById('txt_apelli_editar').value=data.usu_apellido;

  document.getElementById('txt_correo_editar').value=data.usu_email;
  document.getElementById('txt_tele_editar').value=data.usu_telefono;
  document.getElementById('txt_direc_editar').value=data.usu_direccion;
  document.getElementById('txt_foto_actual').value=data.usu_foto;

  var imgElement = document.getElementById('preview2');
  if (imgElement) {
      console.log('Data:', data); // Depuración
      console.log('Image URL:', data.usu_foto); // Verificar URL
  
      if (data.usu_foto && data.usu_foto.trim() !== '') {
          imgElement.src = "../" + data.usu_foto; // Ruta relativa
      } else {
          imgElement.src = '../img/vacio.png'; // Ruta por defecto
      }
  
      imgElement.style.display = 'block'; // Mostrar siempre la imagen
  
      // Manejar errores de carga
      imgElement.onerror = function () {
          console.error("Error al cargar la imagen desde la ruta: " + imgElement.src);
          imgElement.src = '../img/vacio.png'; // Ruta por defecto
      };
  } else {
      console.error('Elemento img con id preview2 no encontrado');
  }
  

document.getElementById('txt_usu_editar').value=data.usu_usuario;
document.getElementById('select_rol_editar2').value=data.usu_rol;

})



$('#tabla_usuario').on('click','.contra',function(){
  var data = tbl_usuario.row($(this).parents('tr')).data();

  if(tbl_usuario.row(this).child.isShown()){
      var data = tbl_usuario.row(this).data();
  }
  $("#modal_contra").modal('show');
  document.getElementById('txt_idusuario_contra').value=data.id_usuario;

})
$('#tabla_usuario').on('click','.desactivar',function(){
  var data = tbl_usuario.row($(this).parents('tr')).data();

  if(tbl_usuario.row(this).child.isShown()){
      var data = tbl_usuario.row(this).data();
  }
    Swal.fire({
      title: 'Desea desactivar al usuario '+data.USUARIO+'?',
      text: "Una vez desactivado el usuario no tendra acceso al sistema",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#005CA5',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Si, Desactivar'
    }).then((result) => {
      if (result.isConfirmed) {
        Modificar_Estatus_Usuario(parseInt(data.id_usuario),'INACTIVO',data.USUARIO);
      }
    })

})


$('#tabla_usuario').on('click','.activar',function(){
  var data = tbl_usuario.row($(this).parents('tr')).data();

  if(tbl_usuario.row(this).child.isShown()){
      var data = tbl_usuario.row(this).data();
  }
    Swal.fire({
      title: 'Desea activar al usuario '+data.USUARIO+'?',
      text: "Una vez activado el usuario tendra acceso al sistema",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#005CA5',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Si, Activar'
    }).then((result) => {
      if (result.isConfirmed) {
        Modificar_Estatus_Usuario(parseInt(data.id_usuario),'ACTIVO',data.USUARIO);
      }
    })

})


function Modificar_Estatus_Usuario(id,estatus,user){
  let esta=estatus;
  if(esta==="INACTIVO"){
    esta="Desactivo";
  }
  $.ajax({
    "url":"../controller/usuario/controlador_modificar_usuario_estatus.php",
    type:'POST',
    data:{
      id:id,
      estatus:estatus
    }
  }).done(function(resp){
    if(resp>0){
        Swal.fire("Mensaje de Confirmación","Se "+esta+" con exito El Usuario "+user,"success").then((value)=>{
          tbl_usuario.ajax.reload();
        });
    }else{
      return Swal.fire("Mensaje de Error","No se completo la actualización","error");

    }
  })
}

//REGISTROS DE USUARIOS
function Registrar_Usuario(){

  //DATOS DEL DOCENTE
  let dni = document.getElementById('txt_dni').value;
  let nombre = document.getElementById('txt_nomb').value;
  let apelli = document.getElementById('txt_apelli').value;
  let correo = document.getElementById('txt_correo').value;
  let tele = document.getElementById('txt_tele').value;
  let dire = document.getElementById('txt_direc').value;
  let foto = document.getElementById('txt_foto').value;


  //DATOS DEL USUARIO
  let usu = document.getElementById('txt_usu').value;
  let contra = document.getElementById('txt_contra').value;
  let rol = document.getElementById('select_rol_editar').value;


  
  if(dni.length==0|| apelli.length==0||correo.length==0||dire.length==0||nombre.length==0){
    return Swal.fire("Mensaje de Advertencia","Tiene campos en el registro del docente","warning");
  }
  if(usu.length==0||contra.length==0||rol.length==0){
    return Swal.fire("Mensaje de Advertencia","Los datos del usuario son oblgatorios","warning");
  }

    let extension = foto.split('.').pop();
    let nombrefoto="";
    let f = new Date();
    if(foto.length>0){
      nombrefoto="IMG"+f.getDate()+"-"+(f.getMonth()+1)+"-"+f.getFullYear()+"-"+f.getHours()+"-"+f.getMilliseconds()+"."+extension;
    }
    //CONDICIONANDO LOS CAMPOS VACIOS


    let formData = new FormData();
    let fotoobj = $("#txt_foto")[0].files[0];

    formData.append("dni",dni);
    formData.append("nombre",nombre);
    formData.append("apelli",apelli);
    formData.append("correo",correo);
    formData.append("tele",tele);
    formData.append("dire",dire);
    formData.append("nombrefoto",nombrefoto);
    formData.append("foto",fotoobj);

    formData.append("usu",usu);
    formData.append("contra",contra);
    formData.append("rol",rol);
    $.ajax({
      url:"../controller/usuario/controlador_registro_usuario.php",
      type:'POST',
      data:formData,
      contentType:false,
      processData:false,
      success:function(resp){
        if(resp.length>0){
        if(resp==1){
          Swal.fire("Mensaje de Confirmación","Se registro correctamente al usuario con el DNI N° <b>"+dni+"</b>","success").then((value)=>{
            // Limpiar todos los campos
            document.getElementById('txt_dni').value = "";
            document.getElementById('txt_nomb').value = "";
            document.getElementById('txt_apelli').value = "";
            document.getElementById('txt_correo').value = "";
            document.getElementById('txt_tele').value = "";
            document.getElementById('txt_direc').value = "";


            // Limpiar la vista previa de la imagen
            document.getElementById('preview').src = '#';
            document.getElementById('preview').alt = 'Vista previa';

            document.getElementById('txt_usu').value = "";
            document.getElementById('txt_contra').value = "";
            document.getElementById('select_rol_editar').option = "Seleccione";

            // Cerrar el modal
            $("#modal_registro").modal('hide');
            tbl_usuario.ajax.reload();

          });
            }else{
            Swal.fire("Mensaje de Advertencia","El DNI o el USUARIO que intentas registrar ya se encuentra en la base de datos, revise por favor","warning");
            }
        }else{
          Swal.fire("Mensaje de Advertencia","No se pudo registrar al usuario","warning");
        }
      }
    });
}



function Modificar_Usuario(){

  //DATOS DEL DOCENTE
  let id = document.getElementById('id_usuario').value;
  let dni = document.getElementById('txt_dni_editar').value;
  let nombre = document.getElementById('txt_nomb_editar').value;
  let apelli = document.getElementById('txt_apelli_editar').value;
  let correo = document.getElementById('txt_correo_editar').value;
  let tele = document.getElementById('txt_tele_editar').value;
  let dire = document.getElementById('txt_direc_editar').value;
  let fotoactual = document.getElementById('txt_foto_actual').value;
  let foto = document.getElementById('txt_foto_editar').value;


  //DATOS DEL USUARIO
  let usu = document.getElementById('txt_usu_editar').value;
  let rol = document.getElementById('select_rol_editar2').value;


  
  if(id.length==0||dni.length==0|| apelli.length==0||correo.length==0||dire.length==0||nombre.length==0){
    return Swal.fire("Mensaje de Advertencia","Tiene campos en el registro del docente","warning");
  }
  if(usu.length==0||rol.length==0){
    return Swal.fire("Mensaje de Advertencia","Los datos del usuario son oblgatorios","warning");
  }

    let extension = foto.split('.').pop();
    let nombrefoto="";
    let f = new Date();
    if(foto.length>0){
      nombrefoto="IMG"+f.getDate()+"-"+(f.getMonth()+1)+"-"+f.getFullYear()+"-"+f.getHours()+"-"+f.getMilliseconds()+"."+extension;
    }
    //CONDICIONANDO LOS CAMPOS VACIOS


    let formData = new FormData();
    let fotoobj = $("#txt_foto_editar")[0].files[0];

    formData.append("id",id);
    formData.append("dni",dni);
    formData.append("nombre",nombre);
    formData.append("apelli",apelli);
    formData.append("correo",correo);
    formData.append("tele",tele);
    formData.append("dire",dire);
    formData.append("fotoactual",fotoactual);
    formData.append("nombrefoto",nombrefoto);
    formData.append("foto",fotoobj);

    formData.append("usu",usu);
    formData.append("rol",rol);
    $.ajax({
      url:"../controller/usuario/controlador_modificar_usuario.php",
      type:'POST',
      data:formData,
      contentType:false,
      processData:false,
      success:function(resp){
        if(resp.length>0){
        if(resp==1){
          Swal.fire("Mensaje de Confirmación","Se actualizo correctamente al usuario con el DNI N° <b>"+dni+"</b>","success").then((value)=>{
            // Cerrar el modal
            $("#modal_editar").modal('hide');
            tbl_usuario.ajax.reload();
            document.getElementById('txt_foto_editar').value="";

          });
            }else{
            Swal.fire("Mensaje de Advertencia","El DNI o USUARIO que intentas actualizar ya se encuentra en la base de datos, revise por favor","warning");
            }
        }else{
          Swal.fire("Mensaje de Advertencia","No se pudo actualizar al usuario","warning");
        }
      }
    });
}



function Modificar_Contra(){
  let id = document.getElementById('txt_idusuario_contra').value;
  let con = document.getElementById('txt_contra_nueva').value;

  if(id.length==0 || con.length==0){
      return Swal.fire("Mensaje de Advertencia","Tiene campos vacios","warning");
  }
  $.ajax({
    "url":"../controller/usuario/controlador_modificar_usuario_contra.php",
    type:'POST',
    data:{
      id:id,
      con:con
    }
  }).done(function(resp){
    if(resp>0){
        Swal.fire("Mensaje de Confirmación","Contraseña del Usuario Actualizada","success").then((value)=>{
          tbl_usuario.ajax.reload();
        $("#modal_contra").modal('hide');
        });
    }else{
      return Swal.fire("Mensaje de Error","No se completo la actualización","error");

    }
  })
}

function Cargar_Select_Empleado(){
  $.ajax({
    "url":"../controller/usuario/controlador_cargar_select_empleado.php",
    type:'POST',
  }).done(function(resp){
    let data=JSON.parse(resp);
    if(data.length>0){
      let cadena ="";
      for (let i = 0; i < data.length; i++) {
        cadena+="<option value='"+data[i][0]+"'>"+data[i][4]+"</option>";    
      }
        document.getElementById('select_empleado').innerHTML=cadena;
        document.getElementById('select_empleado_editar').innerHTML=cadena;

    }else{
      cadena+="<option value=''>No hay empleado en la base de datos</option>";
      document.getElementById('select_empleado').innerHTML=cadena;
      document.getElementById('select_empleado_editar').innerHTML=cadena;

    }
  })
}

function Cargar_Select_Area_Solo(){
  $.ajax({
    "url":"../controller/usuario/controlador_cargar_select_area_solo.php",
    type:'POST',
  }).done(function(resp){
    let data=JSON.parse(resp);
    if(data.length>0){
      let cadena ="";
      for (let i = 0; i < data.length; i++) {
        cadena+="<option value='"+data[i][0]+"'>"+data[i][1]+"</option>";    
      }
        document.getElementById('select_area').innerHTML=cadena;
        document.getElementById('select_area_editar').innerHTML=cadena;
    }else{
      cadena+="<option value=''>No hay empleado en la base de datos</option>";
      document.getElementById('select_area').innerHTML=cadena;
      document.getElementById('select_area_editar').innerHTML=cadena;
    }
  })
}

//SEGUIMIENTO TRAMITE //




// TOTALES
function Total_reuniones_hoy(){
  $.ajax({
      "url":"../controller/usuario/controlador_total_reuniones_hoy.php",
      type:'POST'
      }).done(function(resp){
      var data = JSON.parse(resp);
      var cadena="";
      if (data.length > 0) {
        $("#total_reuniones_hoy").html(data[0][0]);
    } else {
      return Swal.fire("Mensaje de Error","No se pudo traer los resultados","error");
    }
    
  })
}

function Total_reuniones(){
  $.ajax({
      "url":"../controller/usuario/controlador_total_reuniones.php",
      type:'POST'
      }).done(function(resp){
      var data = JSON.parse(resp);
      var cadena="";
      if (data.length > 0) {
        $("#total_reuniones").html(data[0][0]);
    } else {
      return Swal.fire("Mensaje de Error","No se pudo traer los resultados","error");
    }
    
  })
}
function Total_expedientes(){
  $.ajax({
      "url":"../controller/usuario/controlador_total_expedientes.php",
      type:'POST'
      }).done(function(resp){
      var data = JSON.parse(resp);
      var cadena="";
      if (data.length > 0) {
        $("#total_expedientes").html(data[0][0]);
    } else {
      return Swal.fire("Mensaje de Error","No se pudo traer los resultados","error");
    }
    
  })
}
function Total_expedientes_proceso(){
  $.ajax({
      "url":"../controller/usuario/controlador_total_expedientes_proceso.php",
      type:'POST'
      }).done(function(resp){
      var data = JSON.parse(resp);
      var cadena="";
      if (data.length > 0) {
        $("#total_expedientes_proceso").html(data[0][0]);
    } else {
      return Swal.fire("Mensaje de Error","No se pudo traer los resultados","error");
    }
    
  })
}
function Total_expedientes_tramite(){
  $.ajax({
      "url":"../controller/usuario/controlador_total_expedientes_tramite.php",
      type:'POST'
      }).done(function(resp){
      var data = JSON.parse(resp);
      var cadena="";
      if (data.length > 0) {
        $("#total_expedientes_tramite").html(data[0][0]);
    } else {
      return Swal.fire("Mensaje de Error","No se pudo traer los resultados","error");
    }
    
  })
}
function Total_expedientes_observados(){
  $.ajax({
      "url":"../controller/usuario/controlador_total_expedientes_observados.php",
      type:'POST'
      }).done(function(resp){
      var data = JSON.parse(resp);
      var cadena="";
      if (data.length > 0) {
        $("#total_expedientes_observados").html(data[0][0]);
    } else {
      return Swal.fire("Mensaje de Error","No se pudo traer los resultados","error");
    }
    
  })
}

function Total_expedientes_finalizados(){
  $.ajax({
      "url":"../controller/usuario/controlador_total_expedientes_finalizados.php",
      type:'POST'
      }).done(function(resp){
      var data = JSON.parse(resp);
      var cadena="";
      if (data.length > 0) {
        $("#total_expedientes_finalizados").html(data[0][0]);
    } else {
      return Swal.fire("Mensaje de Error","No se pudo traer los resultados","error");
    }
    
  })
}


function Total_clientes(){
  $.ajax({
      "url":"../controller/usuario/controlador_total_clientes.php",
      type:'POST'
      }).done(function(resp){
      var data = JSON.parse(resp);
      var cadena="";
      if (data.length > 0) {
        $("#total_clientes").html(data[0][0]);
    } else {
      return Swal.fire("Mensaje de Error","No se pudo traer los resultados","error");
    }
    
  })
}

function Total_ingresos_hoy(){
  $.ajax({
      "url":"../controller/usuario/controlador_total_ingresos_hoy.php",
      type:'POST'
      }).done(function(resp){
      var data = JSON.parse(resp);
      var cadena="";
      if (data.length > 0) {
        $("#total_ingresos_hoy").html('S/. '+data[0][0]);
    } else {
      return Swal.fire("Mensaje de Error","No se pudo traer los resultados","error");
    }
    
  })
  
}

function Total_gastos_hoy(){
  $.ajax({
      "url":"../controller/usuario/controlador_total_gastos_hoy.php",
      type:'POST'
      }).done(function(resp){
      var data = JSON.parse(resp);
      var cadena="";
      if (data.length > 0) {
        $("#total_gastos_hoy").html('S/. '+data[0][0]);
    } else {
      return Swal.fire("Mensaje de Error","No se pudo traer los resultados","error");
    }
    
  })
  
}

function Total_ingresos_mensual(){
  $.ajax({
      "url":"../controller/usuario/controlador_total_ingresos_mensual.php",
      type:'POST'
      }).done(function(resp){
      var data = JSON.parse(resp);
      var cadena="";
      if (data.length > 0) {
        $("#total_ingresos_mes_actual").html('S/. '+data[0][0]);
    } else {
      return Swal.fire("Mensaje de Error","No se pudo traer los resultados","error");
    }
    
  })
  
}

function Total_gastos_mensual(){
  $.ajax({
      "url":"../controller/usuario/controlador_total_gastos_mensual.php",
      type:'POST'
      }).done(function(resp){
      var data = JSON.parse(resp);
      var cadena="";
      if (data.length > 0) {
        $("#total_gastos_mes_actual").html('S/. '+data[0][0]);
    } else {
      return Swal.fire("Mensaje de Error","No se pudo traer los resultados","error");
    }
    
  })
  
}