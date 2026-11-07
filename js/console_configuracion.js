function cargar_configuracion() {
  $.post('../controller/configuracion/controlador_obtener.php', function (datos) {
    pintar_modulos(datos.modulos);

    Object.keys(datos.valores).forEach(function (clave) {
      var campo = document.getElementById(clave);
      if (campo && campo.type !== 'password') {
        campo.value = datos.valores[clave];
      }
    });

    estado_secreto('ia_api_key', datos.valores.ia_api_key_configurado);
    estado_secreto('decolecta_token', datos.valores.decolecta_token_configurado);
    estado_secreto('factura_token', datos.valores.factura_token_configurado);

    var roles = (datos.valores.ia_roles || '').split(',');
    var cajaRoles = $('#lista_ia_roles').empty();
    datos.roles.forEach(function (rol) {
      var idRol = 'ia_rol_' + rol;
      cajaRoles.append($('<div class="form-check form-check-inline"></div>')
        .append($('<input type="checkbox" class="form-check-input ia_rol">').attr('id', idRol).val(rol).prop('checked', roles.indexOf(rol) !== -1))
        .append($('<label class="form-check-label"></label>').attr('for', idRol).text(rol)));
    });

    var elegidas = (datos.valores.inicio_alertas || '').split(',');
    var lista = $('#lista_alertas').empty();
    datos.alertas.forEach(function (a) {
      var id = 'alerta_' + a.clave;
      lista.append($('<div class="form-check"></div>')
        .append($('<input type="checkbox" class="form-check-input alerta_opcion">').attr('id', id).val(a.clave).prop('checked', elegidas.indexOf(a.clave) !== -1))
        .append($('<label class="form-check-label"></label>').attr('for', id).text(a.nombre)));
    });
  }, 'json');

  cargar_empresa();
  cargar_consumo_ia();
  cargar_respaldos();
}

function estado_secreto(id, configurado) {
  document.getElementById(id + '_estado').textContent = configurado ? 'Configurada. Déjalo en blanco para conservarla.' : 'Sin configurar.';
}

function pintar_modulos(modulos) {
  var lista = $('#lista_modulos').empty();
  modulos.forEach(function (m) {
    var id = 'modulo_' + m.codigo;
    var fila = $('<div class="d-flex justify-content-between align-items-center border-bottom py-2"></div>');
    var texto = $('<div></div>').append($('<b></b>').text(m.nombre)).append($('<div class="text-muted small"></div>').text(m.descripcion || ''));
    var interruptor = $('<div class="custom-control custom-switch"></div>');
    var entrada = $('<input type="checkbox" class="custom-control-input">').attr('id', id).prop('checked', m.activo == 1);
    entrada.on('change', function () { cambiar_modulo(m.codigo, this); });
    interruptor.append(entrada).append($('<label class="custom-control-label"></label>').attr('for', id).text(' '));
    lista.append(fila.append(texto).append(interruptor));
  });
}

function cambiar_modulo(codigo, entrada) {
  $.post('../controller/configuracion/controlador_cambiar_modulo.php', { codigo: codigo, activo: entrada.checked ? '1' : '0' }, function (r) {
    if (r.success) {
      Swal.fire({ icon: 'success', title: 'Módulo actualizado', text: 'La página se recargará para aplicar el cambio.', timer: 1500, showConfirmButton: false })
        .then(function () { location.reload(); });
    } else {
      entrada.checked = !entrada.checked;
      Swal.fire('Error', 'No se pudo cambiar el módulo.', 'error');
    }
  }, 'json');
}

$(document).off('submit.config').on('submit.config', '.form_config', function (e) {
  e.preventDefault();
  var marcados = $('.ia_rol:checked').map(function () { return this.value; }).get().join(',');
  $('#ia_roles').val(marcados);
  $('#inicio_alertas').val($('.alerta_opcion:checked').map(function () { return this.value; }).get().join(','));

  $.post('../controller/configuracion/controlador_guardar.php', $(this).serialize(), function (r) {
    if (r.success) {
      Swal.fire({ icon: 'success', title: 'Guardado', timer: 1200, showConfirmButton: false });
      $('.form_config input[type=password]').val('');
      cargar_configuracion();
    } else {
      Swal.fire('Revise los datos', 'Valores no válidos en: ' + r.errores.join(', '), 'warning');
    }
  }, 'json');
});

$(document).off('click.nubefact').on('click.nubefact', '#btn_probar_nubefact', function () {
  $.post('../controller/facturacion/controlador_probar_conexion.php', function (r) {
    Swal.fire(r.success ? 'Respuesta de Nubefact' : 'No se pudo conectar', r.success ? r.respuesta : r.message, r.success ? 'info' : 'warning');
  }, 'json');
});

function cargar_empresa() {
  $.post('../controller/empresa/controlador_listar_empresa.php', function (r) {
    var e = (r.data || [])[0];
    if (!e) { return; }
    $('#emp_id').val(e.id_empresa);
    $('#emp_nombre').val(e.nombre);
    $('#emp_codigo').val(e.codigo);
    $('#emp_email').val(e.email);
    $('#emp_telefono').val(e.telefono);
    $('#emp_direccion').val(e.direccion);
    $('#emp_logo_actual').attr('src', '../' + e.logo);
  }, 'json');
}

$(document).off('submit.empresa').on('submit.empresa', '#form_empresa', function (e) {
  e.preventDefault();
  $.post('../controller/empresa/controlador_modificar_empresa.php', {
    id: $('#emp_id').val(), nom: $('#emp_nombre').val(), email: $('#emp_email').val(),
    cod: $('#emp_codigo').val(), tel: $('#emp_telefono').val(), dir: $('#emp_direccion').val()
  }, function (r) {
    Swal.fire(r > 0 ? 'Guardado' : 'Error', r > 0 ? 'Datos de la empresa actualizados.' : 'No se pudo guardar.', r > 0 ? 'success' : 'error');
  });
});

$(document).off('change.logo').on('change.logo', '#emp_logo', function () {
  if (!this.files.length) { return; }
  var datos = new FormData();
  datos.append('id', $('#emp_id').val());
  datos.append('foto', this.files[0]);
  $.ajax({ url: '../controller/empresa/controlador_empresa_modificar_foto.php', type: 'POST', data: datos, contentType: false, processData: false })
    .done(function (r) {
      if (r > 0) { cargar_empresa(); Swal.fire('Guardado', 'Logo actualizado.', 'success'); }
      else { Swal.fire('Imagen no válida', 'Use un JPG, PNG o GIF de hasta 2 MB.', 'warning'); }
    });
});

function cargar_consumo_ia() {
  $.post('../controller/ia/controlador_consumo.php', function (r) {
    var res = r.resumen;
    var tarjeta = function (titulo, valor) {
      return $('<div class="col-md-3"></div>').append($('<div class="info-box p-2"></div>')
        .append($('<div class="info-box-content"></div>')
          .append($('<span class="info-box-text"></span>').text(titulo))
          .append($('<span class="info-box-number"></span>').text(valor))));
    };
    $('#ia_resumen').empty()
      .append(tarjeta('Consultas', res.consultas))
      .append(tarjeta('Tokens de entrada', res.tokens_entrada))
      .append(tarjeta('Tokens de salida', res.tokens_salida))
      .append(tarjeta('Costo estimado', 'USD ' + Number(res.costo_usd).toFixed(4)));

    var cuerpo = $('#ia_ultimas').empty();
    r.ultimas.forEach(function (u) {
      var fila = $('<tr></tr>');
      [u.creado_en, u.usu_usuario, u.pregunta, u.herramientas, u.estado, Number(u.costo_usd).toFixed(4)].forEach(function (v) {
        fila.append($('<td></td>').text(v === null ? '' : v));
      });
      cuerpo.append(fila);
    });
  }, 'json');
}

function cargar_respaldos() {
  $.post('../controller/respaldos/controlador_listar.php', function (r) {
    G.tabla('#cuerpo_respaldos', r.data || [], [
      function (x) { return x.fecha; },
      function (x) { return x.archivo; },
      function (x) { return G.num(x.bytes / 1024, 1) + ' KB'; },
      function (x) {
        var descargar = $('<a class="btn btn-sm btn-primary mr-1"></a>').attr('href', '../controller/respaldos/controlador_descargar.php?archivo=' + encodeURIComponent(x.archivo)).text('Descargar');
        return G.grupo(descargar, G.boton('Eliminar', 'btn-outline-danger', function () {
          Swal.fire({ title: '¿Eliminar esta copia?', text: x.archivo, icon: 'warning', showCancelButton: true, confirmButtonText: 'Eliminar', cancelButtonText: 'Cancelar' }).then(function (c) {
            if (c.isConfirmed) { G.post('../controller/respaldos/controlador_eliminar.php', { archivo: x.archivo }, cargar_respaldos); }
          });
        }));
      }
    ], 'Aún no hay copias. Genera la primera.');
  }, 'json');
}

$(document).off('click.respaldo').on('click.respaldo', '#btn_generar_respaldo', function () {
  var boton = $(this).prop('disabled', true);
  G.post('../controller/respaldos/controlador_generar.php', {}, function (r) {
    G.ok('Copia generada: ' + G.num(r.bytes / 1024, 1) + ' KB');
    cargar_respaldos();
  }).always(function () { boton.prop('disabled', false); });
});