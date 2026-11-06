-- Tanques, kardex, varillaje, proveedores, compras y ventas individuales.
-- Sin claves foráneas hacia tablas heredadas, porque aún no se contrastan con producción.

CREATE TABLE IF NOT EXISTS tanques (
  id_tanque INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(60) NOT NULL,
  id_producto INT NOT NULL,
  capacidad_galones DECIMAL(12,3) NOT NULL,
  stock_minimo DECIMAL(12,3) NOT NULL DEFAULT 0,
  stock_actual DECIMAL(12,3) NOT NULL DEFAULT 0,
  estado VARCHAR(15) NOT NULL DEFAULT 'ACTIVO',
  creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_tanque_nombre (nombre),
  KEY ix_tanque_producto (id_producto)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS surtidor_tanque (
  id_surtidor INT PRIMARY KEY,
  id_tanque INT NOT NULL,
  CONSTRAINT fk_st_tanque FOREIGN KEY (id_tanque) REFERENCES tanques (id_tanque)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS movimientos_tanque (
  id_movimiento BIGINT AUTO_INCREMENT PRIMARY KEY,
  id_tanque INT NOT NULL,
  tipo VARCHAR(30) NOT NULL,
  galones DECIMAL(12,3) NOT NULL,
  saldo DECIMAL(12,3) NOT NULL,
  id_reporte INT NULL,
  id_compra INT NULL,
  id_varillaje INT NULL,
  id_usuario INT NULL,
  observaciones VARCHAR(255) NULL,
  creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY ix_mov_tanque (id_tanque, id_movimiento),
  KEY ix_mov_reporte (id_reporte),
  CONSTRAINT fk_mov_tanque FOREIGN KEY (id_tanque) REFERENCES tanques (id_tanque)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS varillajes (
  id_varillaje INT AUTO_INCREMENT PRIMARY KEY,
  id_tanque INT NOT NULL,
  fecha DATE NOT NULL,
  medida_cm DECIMAL(8,2) NULL,
  galones_fisicos DECIMAL(12,3) NOT NULL,
  galones_teoricos DECIMAL(12,3) NOT NULL,
  diferencia DECIMAL(12,3) NOT NULL,
  ajuste_aplicado TINYINT(1) NOT NULL DEFAULT 0,
  id_usuario INT NOT NULL,
  observaciones VARCHAR(255) NULL,
  creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY ix_var_fecha (fecha),
  CONSTRAINT fk_var_tanque FOREIGN KEY (id_tanque) REFERENCES tanques (id_tanque)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS proveedores (
  id_proveedor INT AUTO_INCREMENT PRIMARY KEY,
  ruc VARCHAR(11) NOT NULL,
  razon_social VARCHAR(200) NOT NULL,
  telefono VARCHAR(30) NULL,
  direccion VARCHAR(255) NULL,
  estado VARCHAR(15) NOT NULL DEFAULT 'ACTIVO',
  creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_proveedor_ruc (ruc)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS compras (
  id_compra INT AUTO_INCREMENT PRIMARY KEY,
  id_proveedor INT NOT NULL,
  tipo_documento VARCHAR(20) NOT NULL DEFAULT 'FACTURA',
  numero_documento VARCHAR(30) NOT NULL,
  fecha_compra DATE NOT NULL,
  fecha_vencimiento DATE NULL,
  subtotal DECIMAL(12,2) NOT NULL,
  percepcion DECIMAL(12,2) NOT NULL DEFAULT 0,
  total DECIMAL(12,2) NOT NULL,
  monto_pagado DECIMAL(12,2) NOT NULL DEFAULT 0,
  estado VARCHAR(15) NOT NULL DEFAULT 'REGISTRADA',
  id_usuario INT NOT NULL,
  observaciones VARCHAR(255) NULL,
  creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY ix_compra_fecha (fecha_compra),
  KEY ix_compra_doc (id_proveedor, numero_documento),
  CONSTRAINT fk_compra_proveedor FOREIGN KEY (id_proveedor) REFERENCES proveedores (id_proveedor)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS compras_detalle (
  id_detalle INT AUTO_INCREMENT PRIMARY KEY,
  id_compra INT NOT NULL,
  id_tanque INT NOT NULL,
  galones DECIMAL(12,3) NOT NULL,
  costo_galon DECIMAL(10,4) NOT NULL,
  importe DECIMAL(12,2) NOT NULL,
  CONSTRAINT fk_det_compra FOREIGN KEY (id_compra) REFERENCES compras (id_compra),
  CONSTRAINT fk_det_tanque FOREIGN KEY (id_tanque) REFERENCES tanques (id_tanque)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pagos_compra (
  id_pago INT AUTO_INCREMENT PRIMARY KEY,
  id_compra INT NOT NULL,
  monto DECIMAL(12,2) NOT NULL,
  fecha_pago DATE NOT NULL,
  medio VARCHAR(40) NULL,
  referencia VARCHAR(60) NULL,
  id_usuario INT NOT NULL,
  creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_pago_compra FOREIGN KEY (id_compra) REFERENCES compras (id_compra)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ventas (
  id_venta BIGINT AUTO_INCREMENT PRIMARY KEY,
  id_reporte INT NULL,
  id_usuario INT NOT NULL,
  id_surtidor INT NOT NULL,
  id_producto INT NOT NULL,
  galones DECIMAL(12,3) NOT NULL,
  precio_galon DECIMAL(10,3) NOT NULL,
  total DECIMAL(12,2) NOT NULL,
  id_cliente INT NULL,
  placa VARCHAR(20) NULL,
  id_tipo_pago INT NOT NULL,
  codigo_operacion VARCHAR(60) NULL,
  tipo_comprobante VARCHAR(15) NOT NULL DEFAULT 'NINGUNO',
  estado VARCHAR(15) NOT NULL DEFAULT 'REGISTRADA',
  motivo_anulacion VARCHAR(255) NULL,
  creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY ix_venta_fecha (creado_en),
  KEY ix_venta_reporte (id_reporte),
  KEY ix_venta_usuario (id_usuario, id_reporte)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO modulos (codigo, nombre, descripcion, activo) VALUES
  ('inventario', 'Inventario y compras', 'Tanques, kardex de combustible, varillaje, proveedores y compras. Descuenta stock al cerrar cada turno.', 0),
  ('ventas', 'Ventas individuales', 'Registro de cada despacho con cliente y placa, conciliado con las lecturas del turno. Requisito de la facturación.', 0);

INSERT IGNORE INTO configuracion (clave, valor) VALUES
  ('varillaje_tolerancia_pct', '0.5');
