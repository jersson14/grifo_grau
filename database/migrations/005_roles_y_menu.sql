-- Roles editables con capacidades, y menú administrable.

ALTER TABLE usuario MODIFY usu_rol VARCHAR(30) NOT NULL;

CREATE TABLE IF NOT EXISTS roles (
  codigo VARCHAR(30) PRIMARY KEY,
  nombre VARCHAR(60) NOT NULL,
  descripcion VARCHAR(255) NULL,
  vista_inicio VARCHAR(120) NULL,
  sistema TINYINT(1) NOT NULL DEFAULT 0,
  creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS roles_capacidades (
  rol VARCHAR(30) NOT NULL,
  capacidad VARCHAR(40) NOT NULL,
  PRIMARY KEY (rol, capacidad),
  CONSTRAINT fk_rc_rol FOREIGN KEY (rol) REFERENCES roles (codigo) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO roles (codigo, nombre, descripcion, vista_inicio, sistema) VALUES
  ('ADMINISTRADOR', 'Administrador', 'Acceso total al sistema.', 'reportes/view_dashboard.php', 1),
  ('GRIFERO', 'Grifero', 'Registra turnos, ventas y cobros. Solo ve sus propios datos.', 'turnos/view_registrar_turno.php', 1),
  ('SUPERVISOR', 'Supervisor', 'Controla los turnos y la caja de todos los griferos.', 'reportes/view_dashboard.php', 0),
  ('CONTADOR', 'Contador', 'Consulta ventas, compras, créditos y caja.', 'reportes/view_dashboard.php', 0);

INSERT IGNORE INTO roles_capacidades (rol, capacidad) VALUES
  ('GRIFERO', 'turnos_registrar'), ('GRIFERO', 'dashboard_ver'), ('GRIFERO', 'clientes_gestionar'),
  ('GRIFERO', 'creditos_cobrar'), ('GRIFERO', 'ventas_registrar'), ('GRIFERO', 'varillaje_registrar'),
  ('SUPERVISOR', 'turnos_registrar'), ('SUPERVISOR', 'turnos_historial'), ('SUPERVISOR', 'turnos_validar'), ('SUPERVISOR', 'ver_todos'),
  ('SUPERVISOR', 'dashboard_ver'), ('SUPERVISOR', 'avisos'), ('SUPERVISOR', 'reportes_gerenciales'), ('SUPERVISOR', 'clientes_gestionar'),
  ('SUPERVISOR', 'creditos_cobrar'), ('SUPERVISOR', 'ventas_registrar'), ('SUPERVISOR', 'ventas_gestionar'),
  ('SUPERVISOR', 'varillaje_registrar'), ('SUPERVISOR', 'inventario_gestionar'), ('SUPERVISOR', 'caja_gestionar'),
  ('CONTADOR', 'turnos_historial'), ('CONTADOR', 'ver_todos'), ('CONTADOR', 'dashboard_ver'), ('CONTADOR', 'reportes_gerenciales'),
  ('CONTADOR', 'creditos_cobrar'), ('CONTADOR', 'compras_gestionar'), ('CONTADOR', 'caja_gestionar'), ('CONTADOR', 'auditoria_ver');

CREATE TABLE IF NOT EXISTS menu_items (
  id_item INT PRIMARY KEY,
  id_padre INT NULL,
  tipo VARCHAR(10) NOT NULL,
  titulo VARCHAR(60) NOT NULL,
  icono VARCHAR(40) NULL,
  vista VARCHAR(120) NULL,
  orden INT NOT NULL DEFAULT 0,
  visible TINYINT(1) NOT NULL DEFAULT 1,
  roles VARCHAR(255) NULL,
  protegido TINYINT(1) NOT NULL DEFAULT 0,
  KEY ix_menu_padre (id_padre, orden)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO menu_items (id_item, id_padre, tipo, titulo, icono, vista, orden, roles, protegido) VALUES
  (100, NULL, 'seccion', 'MENÚ PRINCIPAL', NULL, NULL, 10, NULL, 0),
  (101, 100, 'enlace', 'Dashboard', 'fas fa-tachometer-alt', 'reportes/view_dashboard.php', 10, NULL, 0),
  (102, 100, 'grupo', 'Gestión de Turnos', 'fas fa-clock', NULL, 20, NULL, 0),
  (103, 102, 'enlace', 'Registrar Turno', 'fas fa-clipboard-check', 'turnos/view_registrar_turno.php', 10, NULL, 0),
  (104, 102, 'enlace', 'Historial de Turnos', 'fas fa-history', 'turnos/view_historial.php', 20, NULL, 0),
  (105, 100, 'enlace', 'Ventas', 'fas fa-cash-register', 'ventas/view_ventas.php', 30, NULL, 0),
  (106, 100, 'grupo', 'Inventario', 'fas fa-oil-can', NULL, 40, NULL, 0),
  (107, 106, 'enlace', 'Tanques y kardex', 'fas fa-oil-can', 'inventario/view_tanques.php', 10, NULL, 0),
  (108, 106, 'enlace', 'Varillaje', 'fas fa-ruler-vertical', 'inventario/view_varillaje.php', 20, NULL, 0),
  (109, 106, 'enlace', 'Compras y proveedores', 'fas fa-truck-moving', 'inventario/view_compras.php', 30, NULL, 0),
  (110, 100, 'enlace', 'Caja y arqueo', 'fas fa-coins', 'caja/view_caja.php', 50, NULL, 0),
  (111, 100, 'enlace', 'Créditos/Vales', 'fas fa-credit-card', 'creditos/view_creditos_pendientes.php', 60, NULL, 0),
  (112, 100, 'grupo', 'Reportes', 'fas fa-file-alt', NULL, 70, NULL, 0),
  (113, 112, 'enlace', 'Ventas por Fecha', 'far fa-circle', 'reportes/view_ventas_por_fecha.php', 10, NULL, 0),
  (114, 112, 'enlace', 'Todos los Reportes', 'far fa-circle', 'reportes/view_listado_reportes.php', 20, NULL, 0),
  (115, 112, 'enlace', 'Validar Reportes', 'far fa-circle', 'reportes/view_validar_reportes.php', 30, NULL, 0),
  (116, 112, 'enlace', 'Mis Reportes', 'fas fa-file-alt', 'reportes/view_mis_reportes.php', 40, 'GRIFERO', 0),
  (200, NULL, 'seccion', 'CONFIGURACIÓN', NULL, NULL, 20, NULL, 0),
  (201, 200, 'enlace', 'Clientes', 'fas fa-user-friends', 'clientes/view_clientes.php', 10, NULL, 0),
  (202, 200, 'enlace', 'Productos (Combustibles)', 'fas fa-gas-pump', 'productos/view_productos.php', 20, NULL, 0),
  (203, 200, 'enlace', 'Surtidores', 'fas fa-tint', 'surtidores/view_surtidores.php', 30, NULL, 0),
  (204, 200, 'enlace', 'Usuarios', 'fas fa-users', 'usuario/view_usuario.php', 40, NULL, 0),
  (205, 200, 'enlace', 'Configuración', 'fas fa-cogs', 'configuracion/view_configuracion.php', 50, NULL, 1),
  (206, 200, 'enlace', 'Roles y permisos', 'fas fa-user-shield', 'administracion/view_roles.php', 60, NULL, 1),
  (207, 200, 'enlace', 'Editar menú', 'fas fa-bars', 'administracion/view_menu.php', 70, NULL, 1),
  (208, 200, 'enlace', 'Auditoría', 'fas fa-clipboard-list', 'administracion/view_auditoria.php', 80, NULL, 0),
  (300, NULL, 'seccion', 'MI CUENTA', NULL, NULL, 30, NULL, 0),
  (301, 300, 'enlace', 'Mi Perfil', 'fas fa-user-circle', 'usuario/view_mi_perfil.php', 10, NULL, 1);
