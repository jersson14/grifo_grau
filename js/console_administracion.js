var RUTA_ADM = '../controller/administracion/controlador_';

function iniciar_roles() {
  var catalogo = {};
  var vistas = [];

  function listar() {
    G.post(RUTA_ADM + 'listar_roles.php', {}, function (r) {
      catalogo = r.catalogo;
      vistas = r.vistas;
      G.opciones('#rol_inicio', vistas, 'vista', function (v) { return v.titulo; });
      var total = Object.keys(catalogo).length;
      G.tabla('#cuerpo_roles', r.roles, [
        function (x) { return x.nombre; },
        function (x) { return x.descripcion; },
        function (x) { return x.usuarios; },
        function (x) { return x.codigo === 'ADMINISTRADOR' ? 'Todos' : x.capacidades.length + ' de ' + total; },
        function (x) { var v = vistas.filter(function (w) { return w.vista === x.vista_inicio; })[0]; return v ? v.titulo : ''; },
        function (x) {
          return G.grupo(
            G.boton('Editar', 'btn-primary', function () { abrir(x); }),
            x.sistema == 1 ? null : G.boton('Eliminar', 'btn-outline-danger', function () {
              Swal.fire({ titleText: '¿Eliminar el rol ' + x.nombre + '?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Eliminar', cancelButtonText: 'Cancelar' }).then(function (c) {
                if (c.isConfirmed) { G.post(RUTA_ADM + 'eliminar_rol.php', { codigo: x.codigo }, function () { G.ok('Rol eliminado'); listar(); }); }
              });
            })
          );
        }
      ]);
    });
  }

  function abrir(rol) {
    var esAdmin = !!rol && rol.codigo === 'ADMINISTRADOR';
    $('#titulo_rol').text(rol ? 'Editar rol: ' + rol.nombre : 'Nuevo rol');
    $('#rol_codigo').val(rol ? rol.codigo : '');
    $('#rol_nombre').val(rol ? rol.nombre : '').prop('readonly', esAdmin);
    $('#rol_descripcion').val(rol ? rol.descripcion : '').prop('readonly', esAdmin);
    if (rol && rol.vista_inicio) { $('#rol_inicio').val(rol.vista_inicio); }
    $('#rol_aviso_admin').toggle(esAdmin);

    var caja = $('#rol_capacidades').empty();
    if (!esAdmin) {
      var grupos = {};
      Object.keys(catalogo).forEach(function (cap) { (grupos[catalogo[cap].grupo] = grupos[catalogo[cap].grupo] || []).push(cap); });
      Object.keys(grupos).forEach(function (grupo) {
        var columna = $('<div class="col-md-6 mb-3"></div>').append($('<b class="d-block mb-1"></b>').text(grupo));
        grupos[grupo].forEach(function (cap) {
          var id = 'cap_' + cap;
          columna.append($('<div class="form-check"></div>')
            .append($('<input type="checkbox" class="form-check-input rol_cap">').attr('id', id).val(cap).prop('checked', !!rol && rol.capacidades.indexOf(cap) !== -1))
            .append($('<label class="form-check-label"></label>').attr('for', id).text(catalogo[cap].nombre)));
        });
        caja.append(columna);
      });
    }
    $('#modal_rol').modal('show');
  }

  $('#btn_nuevo_rol').on('click', function () { abrir(null); });

  $('#form_rol').on('submit', function (e) {
    e.preventDefault();
    G.post(RUTA_ADM + 'guardar_rol.php', {
      codigo: $('#rol_codigo').val(), nombre: $('#rol_nombre').val(), descripcion: $('#rol_descripcion').val(), vista_inicio: $('#rol_inicio').val(),
      capacidades: JSON.stringify($('.rol_cap:checked').map(function () { return this.value; }).get())
    }, function () { $('#modal_rol').modal('hide'); G.ok('Rol guardado'); listar(); });
  });

  listar();
}

function iniciar_menu() {
  var codigosRoles = [];

  function listar() {
    G.post(RUTA_ADM + 'listar_menu.php', {}, function (r) {
      codigosRoles = r.roles;
      var porId = {};
      r.data.forEach(function (i) { porId[i.id_item] = i; });
      var profundidad = function (i) { return i.id_padre === null ? 0 : profundidad(porId[i.id_padre]) + 1; };

      // Orden del árbol: cada elemento seguido de sus hijos
      var filas = [];
      (function recorrer(padre) {
        r.data.filter(function (i) { return i.id_padre === padre; }).forEach(function (i) { filas.push(i); recorrer(i.id_item); });
      })(null);

      G.tabla('#cuerpo_menu', filas, [
        function (i) {
          var texto = $('<span></span>').text(i.titulo).css('padding-left', profundidad(i) * 22 + 'px');
          return i.tipo === 'enlace' ? texto : texto.addClass('font-weight-bold');
        },
        function (i) { return i.icono ? $('<i></i>').addClass(i.icono) : ''; },
        function (i) { return i.roles ? i.roles.split(',').join(', ') : 'Todos con permiso'; },
        function (i) { return i.visible == 1 ? G.etiqueta('Visible', 'badge-success') : G.etiqueta('Oculto', 'badge-secondary'); },
        function (i) {
          return G.grupo(
            G.boton('↑', 'btn-outline-secondary', function () { mover(i, 'ARRIBA'); }).attr('aria-label', 'Subir ' + i.titulo),
            G.boton('↓', 'btn-outline-secondary', function () { mover(i, 'ABAJO'); }).attr('aria-label', 'Bajar ' + i.titulo),
            G.boton('Editar', 'btn-primary', function () { abrir(i); })
          );
        }
      ]);
    });
  }

  function mover(i, direccion) {
    G.post(RUTA_ADM + 'mover_menu.php', { id_item: i.id_item, direccion: direccion }, listar);
  }

  function abrir(i) {
    $('#menu_id').val(i.id_item);
    $('#menu_titulo').val(i.titulo);
    $('#menu_icono').val(i.icono || '');
    $('#grupo_menu_icono').toggle(i.tipo !== 'seccion');
    $('#menu_visible').prop('checked', i.visible == 1);
    var elegidos = i.roles ? i.roles.split(',') : [];
    var caja = $('#menu_roles').empty();
    codigosRoles.forEach(function (rol) {
      var id = 'menu_rol_' + rol;
      caja.append($('<div class="form-check form-check-inline"></div>')
        .append($('<input type="checkbox" class="form-check-input menu_rol">').attr('id', id).val(rol).prop('checked', elegidos.indexOf(rol) !== -1))
        .append($('<label class="form-check-label"></label>').attr('for', id).text(rol)));
    });
    $('#modal_menu').modal('show');
  }

  $('#form_menu').on('submit', function (e) {
    e.preventDefault();
    G.post(RUTA_ADM + 'guardar_menu.php', {
      id_item: $('#menu_id').val(), titulo: $('#menu_titulo').val(), icono: $('#menu_icono').val(), visible: $('#menu_visible').is(':checked') ? 1 : 0,
      roles: JSON.stringify($('.menu_rol:checked').map(function () { return this.value; }).get())
    }, function () { $('#modal_menu').modal('hide'); G.ok('Guardado'); listar(); });
  });

  listar();
}

function iniciar_auditoria() {
  $('#aud_desde').val(G.hoy());
  $('#aud_hasta').val(G.hoy());

  function listar() {
    G.post(RUTA_ADM + 'listar_auditoria.php', { desde: $('#aud_desde').val(), hasta: $('#aud_hasta').val(), usuario: $('#aud_usuario').val() || '', texto: $('#aud_texto').val() }, function (r) {
      if ($('#aud_usuario option').length === 0) {
        G.opciones('#aud_usuario', r.usuarios.map(function (u) { return { u: u }; }), 'u', function (x) { return x.u; }, 'Todos');
      }
      G.tabla('#cuerpo_auditoria', r.data, [
        function (a) { return a.creado_en; },
        function (a) { return a.usuario; },
        function (a) { return a.accion; },
        function (a) { return $('<span style="overflow-wrap:anywhere"></span>').text(a.detalle || ''); },
        function (a) { return a.ip; }
      ], 'No hay acciones en este periodo.');
    });
  }

  $('#filtro_auditoria').on('submit', function (e) { e.preventDefault(); listar(); });
  listar();
}
