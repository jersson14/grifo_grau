-- Límite de intentos de inicio de sesión y bitácora de auditoría.

CREATE TABLE IF NOT EXISTS login_intentos (
  clave CHAR(64) PRIMARY KEY,
  intentos INT NOT NULL DEFAULT 0,
  bloqueado_hasta DATETIME NULL,
  actualizado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS auditoria (
  id_auditoria BIGINT AUTO_INCREMENT PRIMARY KEY,
  id_usuario INT NULL,
  usuario VARCHAR(60) NULL,
  accion VARCHAR(80) NOT NULL,
  detalle TEXT NULL,
  ip VARCHAR(45) NULL,
  creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY ix_auditoria_fecha (creado_en),
  KEY ix_auditoria_usuario (id_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
