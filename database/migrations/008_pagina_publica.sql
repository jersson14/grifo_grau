-- Página pública del grifo, administrable desde el panel: datos, servicios, promociones y solicitudes de flota.

CREATE TABLE IF NOT EXISTS pagina_publica (
  clave VARCHAR(60) PRIMARY KEY,
  valor TEXT NULL,
  actualizado_en DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pagina_servicios (
  id_servicio INT AUTO_INCREMENT PRIMARY KEY,
  titulo VARCHAR(80) NOT NULL,
  descripcion VARCHAR(255) NULL,
  icono VARCHAR(40) NOT NULL DEFAULT 'gas-pump',
  orden INT NOT NULL DEFAULT 0,
  activo TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS promociones (
  id_promocion INT AUTO_INCREMENT PRIMARY KEY,
  titulo VARCHAR(100) NOT NULL,
  detalle VARCHAR(400) NULL,
  desde DATE NOT NULL,
  hasta DATE NULL,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  id_usuario INT NULL,
  creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY ix_promociones_vigencia (activo, desde, hasta)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS solicitudes_flota (
  id_solicitud INT AUTO_INCREMENT PRIMARY KEY,
  empresa VARCHAR(150) NOT NULL,
  ruc VARCHAR(11) NULL,
  contacto VARCHAR(120) NOT NULL,
  telefono VARCHAR(20) NOT NULL,
  correo VARCHAR(150) NULL,
  unidades INT NULL,
  galones_mes INT NULL,
  mensaje VARCHAR(600) NULL,
  estado VARCHAR(15) NOT NULL DEFAULT 'NUEVA',
  nota VARCHAR(400) NULL,
  ip VARCHAR(45) NULL,
  atendido_por INT NULL,
  atendido_en DATETIME NULL,
  creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY ix_solicitudes_estado (estado, creado_en),
  KEY ix_solicitudes_ip (ip, creado_en)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO modulos (codigo, nombre, descripcion, activo) VALUES
  ('pagina_publica', 'Página pública', 'Página de inicio para clientes con precios en vivo, servicios, promociones y solicitudes de crédito para flotas. Si está apagada, la dirección del sistema muestra el acceso.', 0);

INSERT IGNORE INTO pagina_publica (clave, valor) VALUES
  ('frase', 'Tanque lleno, ruta segura'),
  ('descripcion', 'Combustible de calidad, atención las 24 horas y crédito para empresas con flota.'),
  ('horario', 'Abierto las 24 horas, todos los días'),
  ('direccion', ''),
  ('referencia', ''),
  ('mapa_url', ''),
  ('whatsapp', ''),
  ('telefono', ''),
  ('facebook', ''),
  ('instagram', ''),
  ('tiktok', ''),
  ('mostrar_precios', '1'),
  ('flota_titulo', 'Crédito para tu flota'),
  ('flota_texto', 'Consumo por placa, vales controlados y un estado de cuenta claro cada mes.');

INSERT INTO pagina_servicios (titulo, descripcion, icono, orden)
SELECT * FROM (
  SELECT 'Combustible' AS titulo, 'Diesel B5, gasohol regular y premium.' AS descripcion, 'gas-pump' AS icono, 10 AS orden UNION ALL
  SELECT 'Crédito con vales', 'Para empresas y transportistas, con control por placa.', 'file-invoice', 20 UNION ALL
  SELECT 'Pagos digitales', 'Yape, tarjetas y transferencia.', 'mobile-alt', 30 UNION ALL
  SELECT 'Boleta y factura electrónica', 'Tu comprobante al instante.', 'receipt', 40
) AS semilla
WHERE NOT EXISTS (SELECT 1 FROM pagina_servicios);

INSERT IGNORE INTO menu_items (id_item, id_padre, tipo, titulo, icono, vista, orden, roles, protegido) VALUES
  (209, 200, 'enlace', 'Página pública', 'fas fa-globe-americas', 'publico/view_pagina_publica.php', 55, NULL, 0);
