-- Configuración editable desde la interfaz, módulos activables y bitácora del chatbot.

CREATE TABLE IF NOT EXISTS configuracion (
  clave VARCHAR(80) PRIMARY KEY,
  valor TEXT NULL,
  secreto TINYINT(1) NOT NULL DEFAULT 0,
  actualizado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_por INT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS modulos (
  codigo VARCHAR(40) PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  descripcion VARCHAR(255) NULL,
  activo TINYINT(1) NOT NULL DEFAULT 0,
  actualizado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ia_consultas (
  id_consulta BIGINT AUTO_INCREMENT PRIMARY KEY,
  id_usuario INT NOT NULL,
  pregunta TEXT NOT NULL,
  respuesta MEDIUMTEXT NULL,
  herramientas TEXT NULL,
  modelo VARCHAR(40) NULL,
  tokens_entrada INT NOT NULL DEFAULT 0,
  tokens_salida INT NOT NULL DEFAULT 0,
  costo_usd DECIMAL(10,5) NOT NULL DEFAULT 0,
  estado VARCHAR(15) NOT NULL DEFAULT 'OK',
  creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY ix_ia_fecha (creado_en),
  KEY ix_ia_usuario (id_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO modulos (codigo, nombre, descripcion, activo) VALUES
  ('chatbot_ia', 'Chatbot de IA', 'Preguntas en lenguaje natural sobre ventas, turnos y créditos. Envía los datos consultados al proveedor de IA.', 0),
  ('facturacion', 'Facturación electrónica', 'Emisión de boletas y facturas electrónicas mediante un proveedor autorizado.', 0);

INSERT IGNORE INTO configuracion (clave, valor) VALUES
  ('igv_porcentaje', '18'),
  ('credito_dias', '30'),
  ('moneda_simbolo', 'S/'),
  ('turno_tolerancia_soles', '1.00'),
  ('ia_modelo', 'claude-opus-5-5'),
  ('ia_esfuerzo', 'low'),
  ('ia_limite_mensual_usd', '20'),
  ('ia_roles', 'ADMINISTRADOR');
