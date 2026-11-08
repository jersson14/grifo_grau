-- Facturación: notas de crédito y débito, y un comprobante para varios vales de crédito de un cliente.
-- Un comprobante ya no siempre sale de una venta individual: puede salir de vales o modificar otro comprobante.

ALTER TABLE comprobantes MODIFY id_venta BIGINT NULL;
ALTER TABLE comprobantes ADD COLUMN origen VARCHAR(10) NOT NULL DEFAULT 'VENTA' AFTER id_venta;
ALTER TABLE comprobantes ADD COLUMN id_cliente INT NULL AFTER origen;
-- Nota de crédito o débito: el comprobante que modifica, el código del motivo (catálogos 09 y 10 de SUNAT) y el sustento.
ALTER TABLE comprobantes ADD COLUMN id_referencia BIGINT NULL AFTER id_cliente;
ALTER TABLE comprobantes ADD COLUMN tipo_nota VARCHAR(2) NULL AFTER id_referencia;
ALTER TABLE comprobantes ADD COLUMN motivo_nota VARCHAR(250) NULL AFTER tipo_nota;
ALTER TABLE comprobantes ADD KEY ix_comprobante_referencia (id_referencia);

-- Líneas de los comprobantes que no salen de una venta individual. El precio incluye el IGV.
CREATE TABLE IF NOT EXISTS comprobante_items (
  id_item BIGINT AUTO_INCREMENT PRIMARY KEY,
  id_comprobante BIGINT NOT NULL,
  codigo VARCHAR(30) NOT NULL DEFAULT '',
  descripcion VARCHAR(250) NOT NULL,
  unidad VARCHAR(3) NOT NULL DEFAULT 'NIU',
  cantidad DECIMAL(12,3) NOT NULL,
  precio_unitario DECIMAL(14,6) NOT NULL,
  total DECIMAL(12,2) NOT NULL,
  KEY ix_item_comprobante (id_comprobante),
  CONSTRAINT fk_item_comprobante FOREIGN KEY (id_comprobante) REFERENCES comprobantes (id_comprobante)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Vales incluidos en un comprobante. Un vale va en un solo comprobante vigente; si este se anula, se libera.
CREATE TABLE IF NOT EXISTS comprobante_vales (
  id_credito INT NOT NULL PRIMARY KEY,
  id_comprobante BIGINT NOT NULL,
  KEY ix_vale_comprobante (id_comprobante),
  CONSTRAINT fk_vale_comprobante FOREIGN KEY (id_comprobante) REFERENCES comprobantes (id_comprobante)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Series de las notas: las que modifican facturas empiezan con F y las que modifican boletas con B (regla de SUNAT).
INSERT IGNORE INTO configuracion (clave, valor, secreto) VALUES
  ('factura_serie_nc_factura', 'FC01', 0),
  ('factura_serie_nc_boleta', 'BC01', 0),
  ('factura_serie_nd_factura', 'FD01', 0),
  ('factura_serie_nd_boleta', 'BD01', 0);
