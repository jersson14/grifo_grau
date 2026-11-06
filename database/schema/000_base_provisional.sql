-- ESQUEMA PROVISIONAL reconstruido a partir del código (2026-11-06).
-- No es copia de producción: sustituir o comparar con el volcado real (plan, tarea 0.1).
-- Solo cubre los módulos activos del menú. Los módulos heredados (gastos, ingresos,
-- indicadores, pagos de expedientes) dependen de procedimientos que aquí no existen.

CREATE TABLE usuario (
  id_usuario INT AUTO_INCREMENT PRIMARY KEY,
  dni_usuario VARCHAR(20) NULL,
  usu_nombre VARCHAR(100) NOT NULL,
  usu_apellido VARCHAR(100) NOT NULL,
  usu_email VARCHAR(150) NULL,
  usu_telefono VARCHAR(30) NULL,
  usu_direccion VARCHAR(255) NULL,
  usu_foto VARCHAR(255) NULL,
  usu_usuario VARCHAR(60) NOT NULL,
  usu_contrasenia VARCHAR(255) NOT NULL,
  usu_rol VARCHAR(30) NOT NULL DEFAULT 'GRIFERO',
  usu_estatus VARCHAR(15) NOT NULL DEFAULT 'ACTIVO',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL,
  UNIQUE KEY uq_usuario (usu_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE empresa (
  id_empresa INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(200) NOT NULL,
  email VARCHAR(150) NULL,
  codigo VARCHAR(30) NULL,
  telefono VARCHAR(30) NULL,
  direccion VARCHAR(255) NULL,
  logo VARCHAR(255) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE productos (
  id_producto INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  tipo VARCHAR(30) NOT NULL,
  precio_actual DECIMAL(10,3) NOT NULL DEFAULT 0,
  estado VARCHAR(15) NOT NULL DEFAULT 'ACTIVO',
  id_usuario INT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE surtidores (
  id_surtidor INT AUTO_INCREMENT PRIMARY KEY,
  numero_maquina INT NOT NULL,
  codigo VARCHAR(20) NOT NULL,
  id_producto INT NOT NULL,
  lectura_actual DECIMAL(14,3) NOT NULL DEFAULT 0,
  estado VARCHAR(15) NOT NULL DEFAULT 'ACTIVO',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL,
  UNIQUE KEY uq_surtidor (numero_maquina, codigo),
  CONSTRAINT fk_surtidor_producto FOREIGN KEY (id_producto) REFERENCES productos (id_producto)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE tipos_pago (
  id_tipo_pago INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(50) NOT NULL,
  codigo VARCHAR(30) NOT NULL,
  requiere_codigo ENUM('SI','NO') DEFAULT 'NO',
  estado ENUM('ACTIVO','INACTIVO') DEFAULT 'ACTIVO'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE clientes (
  id_cliente INT AUTO_INCREMENT PRIMARY KEY,
  nombre_completo VARCHAR(200) NOT NULL,
  dni_ruc VARCHAR(20) NULL,
  telefono VARCHAR(30) NULL,
  direccion VARCHAR(255) NULL,
  estado VARCHAR(15) NOT NULL DEFAULT 'ACTIVO',
  id_usuario INT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE reportes_turno (
  id_reporte INT AUTO_INCREMENT PRIMARY KEY,
  numero_documento VARCHAR(20) NOT NULL,
  id_usuario INT NOT NULL,
  turno VARCHAR(10) NOT NULL,
  fecha_reporte DATE NOT NULL,
  hora_inicio TIME NULL,
  hora_fin TIME NULL,
  estado VARCHAR(15) NOT NULL DEFAULT 'ABIERTO',
  total_diesel DECIMAL(12,2) NOT NULL DEFAULT 0,
  total_regular DECIMAL(12,2) NOT NULL DEFAULT 0,
  total_premium DECIMAL(12,2) NOT NULL DEFAULT 0,
  total_ventas DECIMAL(12,2) NOT NULL DEFAULT 0,
  galones_diesel DECIMAL(12,3) NOT NULL DEFAULT 0,
  galones_regular DECIMAL(12,3) NOT NULL DEFAULT 0,
  galones_premium DECIMAL(12,3) NOT NULL DEFAULT 0,
  total_galones DECIMAL(12,3) NOT NULL DEFAULT 0,
  monto_descuentos DECIMAL(12,2) NOT NULL DEFAULT 0,
  monto_otros_gastos DECIMAL(12,2) NOT NULL DEFAULT 0,
  monto_yape DECIMAL(12,2) NOT NULL DEFAULT 0,
  monto_bcp DECIMAL(12,2) NOT NULL DEFAULT 0,
  monto_visa DECIMAL(12,2) NOT NULL DEFAULT 0,
  monto_efectivo DECIMAL(12,2) NOT NULL DEFAULT 0,
  monto_credito DECIMAL(12,2) NOT NULL DEFAULT 0,
  total_pagos DECIMAL(12,2) NOT NULL DEFAULT 0,
  monto_faltante DECIMAL(12,2) NOT NULL DEFAULT 0,
  id_administrador INT NULL,
  fecha_aprobacion DATETIME NULL,
  observaciones TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL,
  UNIQUE KEY uq_documento (numero_documento),
  KEY ix_fecha (fecha_reporte),
  KEY ix_estado (estado),
  CONSTRAINT fk_reporte_usuario FOREIGN KEY (id_usuario) REFERENCES usuario (id_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE lecturas_turno (
  id_lectura INT AUTO_INCREMENT PRIMARY KEY,
  id_reporte INT NOT NULL,
  id_surtidor INT NOT NULL,
  lectura_anterior DECIMAL(14,3) NOT NULL DEFAULT 0,
  lectura_actual DECIMAL(14,3) NOT NULL DEFAULT 0,
  galones_vendidos DECIMAL(12,3) NOT NULL DEFAULT 0,
  precio_galon DECIMAL(10,3) NOT NULL DEFAULT 0,
  total_soles DECIMAL(12,2) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_lectura_reporte FOREIGN KEY (id_reporte) REFERENCES reportes_turno (id_reporte),
  CONSTRAINT fk_lectura_surtidor FOREIGN KEY (id_surtidor) REFERENCES surtidores (id_surtidor)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE pagos_reporte (
  id_pago_reporte INT AUTO_INCREMENT PRIMARY KEY,
  id_reporte INT NOT NULL,
  id_tipo_pago INT NOT NULL,
  codigo_operacion VARCHAR(60) NULL,
  monto DECIMAL(12,2) NOT NULL DEFAULT 0,
  observaciones VARCHAR(255) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_pago_reporte FOREIGN KEY (id_reporte) REFERENCES reportes_turno (id_reporte),
  CONSTRAINT fk_pago_tipo FOREIGN KEY (id_tipo_pago) REFERENCES tipos_pago (id_tipo_pago)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE ventas_credito (
  id_credito INT AUTO_INCREMENT PRIMARY KEY,
  id_reporte INT NULL,
  id_cliente INT NOT NULL,
  id_usuario_grifero INT NULL,
  numero_vale VARCHAR(40) NULL,
  placa VARCHAR(20) NULL,
  monto DECIMAL(12,2) NOT NULL DEFAULT 0,
  saldo_pendiente DECIMAL(12,2) NOT NULL DEFAULT 0,
  estado VARCHAR(15) NOT NULL DEFAULT 'PENDIENTE',
  fecha_vencimiento DATE NULL,
  observaciones TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL,
  KEY ix_estado (estado),
  CONSTRAINT ventas_credito_ibfk_1 FOREIGN KEY (id_reporte) REFERENCES reportes_turno (id_reporte) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_credito_cliente FOREIGN KEY (id_cliente) REFERENCES clientes (id_cliente)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE historial_pagos_credito (
  id_pago_credito INT AUTO_INCREMENT PRIMARY KEY,
  id_credito INT NOT NULL,
  id_tipo_pago INT NOT NULL,
  codigo_operacion VARCHAR(60) NULL,
  monto_pagado DECIMAL(12,2) NOT NULL DEFAULT 0,
  descuento DECIMAL(12,2) NOT NULL DEFAULT 0,
  saldo_anterior DECIMAL(12,2) NOT NULL DEFAULT 0,
  saldo_nuevo DECIMAL(12,2) NOT NULL DEFAULT 0,
  fecha_pago DATE NOT NULL,
  id_usuario_registro INT NOT NULL,
  observaciones VARCHAR(255) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_hist_credito FOREIGN KEY (id_credito) REFERENCES ventas_credito (id_credito),
  CONSTRAINT fk_hist_tipo FOREIGN KEY (id_tipo_pago) REFERENCES tipos_pago (id_tipo_pago)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO tipos_pago (id_tipo_pago, nombre, codigo, requiere_codigo, estado) VALUES
  (1, 'YAPE', 'YAPE', 'SI', 'ACTIVO'),
  (2, 'BCP', 'BCP', 'SI', 'ACTIVO'),
  (3, 'VISA', 'VISA', 'SI', 'ACTIVO'),
  (4, 'EFECTIVO', 'EFECTIVO', 'NO', 'ACTIVO'),
  (5, 'DESCUENTO', 'DESCUENTO', 'NO', 'ACTIVO'),
  (6, 'OTROS_GASTOS', 'OTROS_GASTOS', 'NO', 'ACTIVO');
