-- Flotas: clientes con línea de crédito, días de pago y placas autorizadas.
-- No altera las tablas heredadas: la flota extiende a un cliente existente y sus vales siguen en ventas_credito.

CREATE TABLE IF NOT EXISTS flotas (
  id_cliente INT PRIMARY KEY,
  limite_credito DECIMAL(12,2) NULL,
  dias_credito INT NOT NULL DEFAULT 30,
  solo_placas_autorizadas TINYINT(1) NOT NULL DEFAULT 1,
  contacto VARCHAR(120) NULL,
  telefono VARCHAR(20) NULL,
  correo VARCHAR(150) NULL,
  observaciones VARCHAR(400) NULL,
  estado VARCHAR(12) NOT NULL DEFAULT 'ACTIVA',
  id_solicitud INT NULL,
  id_usuario INT NULL,
  creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en DATETIME NULL,
  CONSTRAINT fk_flota_cliente FOREIGN KEY (id_cliente) REFERENCES clientes (id_cliente)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS flota_placas (
  id_placa INT AUTO_INCREMENT PRIMARY KEY,
  id_cliente INT NOT NULL,
  placa VARCHAR(10) NOT NULL,
  vehiculo VARCHAR(80) NULL,
  conductor VARCHAR(120) NULL,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_flota_placa (id_cliente, placa),
  CONSTRAINT fk_placa_flota FOREIGN KEY (id_cliente) REFERENCES flotas (id_cliente)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO modulos (codigo, nombre, descripcion, activo) VALUES
  ('flotas', 'Flotas y vales', 'Empresas con línea de crédito, placas autorizadas, consumo por placa, alertas y estado de cuenta mensual en PDF. Recibe las solicitudes de la página pública.', 0);

INSERT IGNORE INTO menu_items (id_item, id_padre, tipo, titulo, icono, vista, orden, roles, protegido) VALUES
  (119, 100, 'enlace', 'Flotas', 'fas fa-truck', 'flotas/view_flotas.php', 65, NULL, 0);

INSERT IGNORE INTO roles_capacidades (rol, capacidad)
SELECT codigo, 'flotas_gestionar' FROM roles WHERE codigo IN ('SUPERVISOR', 'CONTADOR');
