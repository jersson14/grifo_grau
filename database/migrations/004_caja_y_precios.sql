-- Arqueo de caja por turno, depósitos bancarios e historial y programación de precios.

CREATE TABLE IF NOT EXISTS arqueos (
  id_arqueo INT AUTO_INCREMENT PRIMARY KEY,
  id_reporte INT NOT NULL,
  denominaciones TEXT NOT NULL,
  efectivo_contado DECIMAL(12,2) NOT NULL,
  efectivo_esperado DECIMAL(12,2) NOT NULL,
  diferencia DECIMAL(12,2) NOT NULL,
  resolucion VARCHAR(15) NOT NULL DEFAULT 'PENDIENTE',
  nota_resolucion VARCHAR(255) NULL,
  id_usuario INT NOT NULL,
  observaciones VARCHAR(255) NULL,
  creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en DATETIME NULL,
  UNIQUE KEY uq_arqueo_reporte (id_reporte)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS depositos (
  id_deposito INT AUTO_INCREMENT PRIMARY KEY,
  fecha DATE NOT NULL,
  banco VARCHAR(60) NOT NULL,
  numero_operacion VARCHAR(60) NOT NULL,
  monto DECIMAL(12,2) NOT NULL,
  estado VARCHAR(15) NOT NULL DEFAULT 'REGISTRADO',
  motivo_anulacion VARCHAR(255) NULL,
  id_usuario INT NOT NULL,
  observaciones VARCHAR(255) NULL,
  creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY ix_deposito_fecha (fecha)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS precios_historial (
  id_historial INT AUTO_INCREMENT PRIMARY KEY,
  id_producto INT NOT NULL,
  precio_anterior DECIMAL(10,3) NULL,
  precio_nuevo DECIMAL(10,3) NOT NULL,
  origen VARCHAR(15) NOT NULL DEFAULT 'MANUAL',
  id_usuario INT NULL,
  creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY ix_historial_producto (id_producto, id_historial)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS precios_programados (
  id_programado INT AUTO_INCREMENT PRIMARY KEY,
  id_producto INT NOT NULL,
  precio DECIMAL(10,3) NOT NULL,
  vigente_desde DATETIME NOT NULL,
  estado VARCHAR(15) NOT NULL DEFAULT 'PENDIENTE',
  id_usuario INT NOT NULL,
  creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY ix_programado_estado (estado, vigente_desde)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO modulos (codigo, nombre, descripcion, activo) VALUES
  ('caja', 'Caja y arqueo', 'Conteo de efectivo por turno, diferencias por grifero y depósitos bancarios.', 0);

INSERT IGNORE INTO configuracion (clave, valor) VALUES
  ('inicio_alertas', 'creditos_vencidos,turnos_sin_validar,stock_bajo,compras_por_vencer,varillaje_diferencia,arqueos_pendientes,diferencias_caja,precios_programados');
