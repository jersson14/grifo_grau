-- Comprobantes electrónicos emitidos mediante un proveedor (Nubefact).

CREATE TABLE IF NOT EXISTS series_comprobante (
  serie VARCHAR(4) PRIMARY KEY,
  ultimo_numero INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS comprobantes (
  id_comprobante BIGINT AUTO_INCREMENT PRIMARY KEY,
  id_venta BIGINT NOT NULL,
  tipo VARCHAR(10) NOT NULL,
  serie VARCHAR(4) NOT NULL,
  numero INT NOT NULL,
  cliente_tipo VARCHAR(2) NOT NULL,
  cliente_documento VARCHAR(20) NOT NULL,
  cliente_denominacion VARCHAR(200) NOT NULL,
  cliente_direccion VARCHAR(255) NULL,
  gravada DECIMAL(12,2) NOT NULL,
  igv DECIMAL(12,2) NOT NULL,
  total DECIMAL(12,2) NOT NULL,
  porcentaje_igv DECIMAL(5,2) NOT NULL,
  estado VARCHAR(25) NOT NULL DEFAULT 'PENDIENTE',
  intentos INT NOT NULL DEFAULT 0,
  ultimo_error VARCHAR(500) NULL,
  enlace VARCHAR(255) NULL,
  enlace_pdf VARCHAR(255) NULL,
  enlace_xml VARCHAR(255) NULL,
  enlace_cdr VARCHAR(255) NULL,
  codigo_hash VARCHAR(255) NULL,
  cadena_qr VARCHAR(500) NULL,
  sunat_descripcion VARCHAR(500) NULL,
  motivo_anulacion VARCHAR(100) NULL,
  id_usuario INT NOT NULL,
  creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en DATETIME NULL,
  UNIQUE KEY uq_comprobante_numero (serie, numero),
  UNIQUE KEY uq_comprobante_venta (id_venta),
  KEY ix_comprobante_estado (estado),
  KEY ix_comprobante_fecha (creado_en)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO configuracion (clave, valor) VALUES
  ('factura_serie_factura', 'F001'),
  ('factura_serie_boleta', 'B001'),
  ('factura_unidad_medida', 'GLL'),
  ('factura_boleta_tope_sin_documento', '700');

UPDATE configuracion SET valor = CONCAT(valor, ',comprobantes_problema')
WHERE clave = 'inicio_alertas' AND valor NOT LIKE '%comprobantes_problema%';

UPDATE modulos SET descripcion = 'Emisión de boletas y facturas electrónicas con Nubefact. Requiere el módulo de ventas individuales.' WHERE codigo = 'facturacion';

INSERT IGNORE INTO menu_items (id_item, id_padre, tipo, titulo, icono, vista, orden, roles, protegido) VALUES
  (117, 100, 'enlace', 'Comprobantes', 'fas fa-file-invoice-dollar', 'facturacion/view_comprobantes.php', 35, NULL, 0);

INSERT IGNORE INTO roles_capacidades (rol, capacidad)
SELECT codigo, 'comprobantes_gestionar' FROM roles WHERE codigo IN ('SUPERVISOR', 'CONTADOR');
