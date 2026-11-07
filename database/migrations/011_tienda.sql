-- Tienda: productos no combustibles con stock, kardex y venta rápida.
-- Las ventas de la tienda no alteran el cuadre del turno: tienen su propio resumen por medio de pago.

CREATE TABLE IF NOT EXISTS tienda_productos (
  id_producto INT AUTO_INCREMENT PRIMARY KEY,
  codigo VARCHAR(40) NULL,
  nombre VARCHAR(120) NOT NULL,
  categoria VARCHAR(40) NOT NULL DEFAULT 'Otros',
  unidad VARCHAR(10) NOT NULL DEFAULT 'UND',
  precio_venta DECIMAL(10,2) NOT NULL,
  costo DECIMAL(10,2) NULL,
  stock DECIMAL(12,2) NOT NULL DEFAULT 0,
  stock_minimo DECIMAL(12,2) NOT NULL DEFAULT 0,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en DATETIME NULL,
  UNIQUE KEY uq_tienda_codigo (codigo),
  KEY ix_tienda_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS tienda_ventas (
  id_venta INT AUTO_INCREMENT PRIMARY KEY,
  numero VARCHAR(12) NOT NULL,
  fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  total DECIMAL(12,2) NOT NULL,
  medio_pago VARCHAR(15) NOT NULL DEFAULT 'EFECTIVO',
  codigo_operacion VARCHAR(60) NULL,
  estado VARCHAR(10) NOT NULL DEFAULT 'ACTIVA',
  motivo_anulacion VARCHAR(200) NULL,
  id_usuario INT NOT NULL,
  anulado_por INT NULL,
  anulado_en DATETIME NULL,
  UNIQUE KEY uq_tienda_venta_numero (numero),
  KEY ix_tienda_venta_fecha (fecha, estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS tienda_ventas_detalle (
  id_detalle INT AUTO_INCREMENT PRIMARY KEY,
  id_venta INT NOT NULL,
  id_producto INT NOT NULL,
  cantidad DECIMAL(12,2) NOT NULL,
  precio_unitario DECIMAL(10,2) NOT NULL,
  costo_unitario DECIMAL(10,2) NULL,
  subtotal DECIMAL(12,2) NOT NULL,
  KEY ix_detalle_venta (id_venta),
  KEY ix_detalle_producto (id_producto),
  CONSTRAINT fk_tienda_detalle_venta FOREIGN KEY (id_venta) REFERENCES tienda_ventas (id_venta),
  CONSTRAINT fk_tienda_detalle_producto FOREIGN KEY (id_producto) REFERENCES tienda_productos (id_producto)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS tienda_movimientos (
  id_movimiento INT AUTO_INCREMENT PRIMARY KEY,
  id_producto INT NOT NULL,
  tipo VARCHAR(12) NOT NULL,
  cantidad DECIMAL(12,2) NOT NULL,
  stock_resultante DECIMAL(12,2) NOT NULL,
  costo_unitario DECIMAL(10,2) NULL,
  id_venta INT NULL,
  detalle VARCHAR(200) NULL,
  id_usuario INT NOT NULL,
  creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY ix_tienda_mov_producto (id_producto, id_movimiento),
  CONSTRAINT fk_tienda_mov_producto FOREIGN KEY (id_producto) REFERENCES tienda_productos (id_producto)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO modulos (codigo, nombre, descripcion, activo) VALUES
  ('tienda', 'Tienda', 'Lubricantes, aditivos, bebidas y otros productos: stock, kardex, venta rápida y resumen por medio de pago. No altera el cuadre del turno.', 0);

INSERT IGNORE INTO menu_items (id_item, id_padre, tipo, titulo, icono, vista, orden, roles, protegido) VALUES
  (120, 100, 'enlace', 'Tienda', 'fas fa-store', 'tienda/view_tienda.php', 45, NULL, 0);

INSERT IGNORE INTO roles_capacidades (rol, capacidad)
SELECT codigo, 'tienda_vender' FROM roles WHERE codigo IN ('GRIFERO', 'SUPERVISOR');
INSERT IGNORE INTO roles_capacidades (rol, capacidad)
SELECT codigo, 'tienda_gestionar' FROM roles WHERE codigo IN ('SUPERVISOR');

UPDATE configuracion SET valor = CONCAT(valor, ',tienda_stock_bajo,flotas_sobre_limite')
WHERE clave = 'inicio_alertas' AND valor NOT LIKE '%tienda_stock_bajo%';
