-- Personal: fichas, horario semanal, asistencia diaria y adelantos, descuentos y bonos.
-- No es una planilla: el resumen del mes es una referencia para el pago.

CREATE TABLE IF NOT EXISTS personal (
  id_personal INT AUTO_INCREMENT PRIMARY KEY,
  nombres VARCHAR(150) NOT NULL,
  dni VARCHAR(12) NULL,
  cargo VARCHAR(30) NOT NULL DEFAULT 'Grifero',
  telefono VARCHAR(20) NULL,
  fecha_ingreso DATE NULL,
  sueldo DECIMAL(10,2) NULL,
  id_usuario INT NULL,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  observaciones VARCHAR(300) NULL,
  creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_personal_dni (dni),
  UNIQUE KEY uq_personal_usuario (id_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS personal_horario (
  id_horario INT AUTO_INCREMENT PRIMARY KEY,
  id_personal INT NOT NULL,
  fecha DATE NOT NULL,
  turno VARCHAR(10) NOT NULL,
  UNIQUE KEY uq_horario_dia (id_personal, fecha),
  CONSTRAINT fk_horario_personal FOREIGN KEY (id_personal) REFERENCES personal (id_personal)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS personal_asistencia (
  id_asistencia INT AUTO_INCREMENT PRIMARY KEY,
  id_personal INT NOT NULL,
  fecha DATE NOT NULL,
  estado VARCHAR(10) NOT NULL,
  hora_entrada TIME NULL,
  hora_salida TIME NULL,
  observacion VARCHAR(200) NULL,
  id_usuario INT NOT NULL,
  actualizado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_asistencia_dia (id_personal, fecha),
  CONSTRAINT fk_asistencia_personal FOREIGN KEY (id_personal) REFERENCES personal (id_personal)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS personal_movimientos (
  id_movimiento INT AUTO_INCREMENT PRIMARY KEY,
  id_personal INT NOT NULL,
  fecha DATE NOT NULL,
  tipo VARCHAR(10) NOT NULL,
  monto DECIMAL(10,2) NOT NULL,
  motivo VARCHAR(200) NOT NULL,
  id_arqueo INT NULL,
  estado VARCHAR(10) NOT NULL DEFAULT 'ACTIVO',
  motivo_anulacion VARCHAR(200) NULL,
  id_usuario INT NOT NULL,
  creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_movimiento_arqueo (id_arqueo),
  KEY ix_movimiento_mes (fecha, estado),
  CONSTRAINT fk_movimiento_personal FOREIGN KEY (id_personal) REFERENCES personal (id_personal)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO modulos (codigo, nombre, descripcion, activo) VALUES
  ('personal', 'Personal', 'Fichas del personal, horario semanal, asistencia, adelantos, descuentos (incluidos los faltantes de caja) y resumen del mes.', 0);

INSERT IGNORE INTO menu_items (id_item, id_padre, tipo, titulo, icono, vista, orden, roles, protegido) VALUES
  (121, 100, 'enlace', 'Personal', 'fas fa-id-badge', 'personal/view_personal.php', 75, NULL, 0);

INSERT IGNORE INTO roles_capacidades (rol, capacidad)
SELECT codigo, 'personal_gestionar' FROM roles WHERE codigo IN ('SUPERVISOR');
