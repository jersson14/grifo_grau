-- Gastos del grifo: categorías, registro con medio de pago y anulación con motivo.
-- Tablas con nombre propio: el módulo heredado de gastos usa otras (y procedimientos que el esquema provisional no tiene).

CREATE TABLE IF NOT EXISTS categorias_gasto (
  id_categoria INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(60) NOT NULL,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  UNIQUE KEY uq_categoria_gasto (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS gastos_grifo (
  id_gasto INT AUTO_INCREMENT PRIMARY KEY,
  fecha DATE NOT NULL,
  id_categoria INT NOT NULL,
  descripcion VARCHAR(200) NOT NULL,
  monto DECIMAL(12,2) NOT NULL,
  medio_pago VARCHAR(15) NOT NULL DEFAULT 'EFECTIVO',
  comprobante VARCHAR(40) NULL,
  estado VARCHAR(10) NOT NULL DEFAULT 'ACTIVO',
  motivo_anulacion VARCHAR(200) NULL,
  id_usuario INT NOT NULL,
  anulado_por INT NULL,
  anulado_en DATETIME NULL,
  creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY ix_gasto_fecha (fecha, estado),
  KEY ix_gasto_categoria (id_categoria),
  CONSTRAINT fk_gasto_categoria FOREIGN KEY (id_categoria) REFERENCES categorias_gasto (id_categoria)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO categorias_gasto (nombre) VALUES
  ('Luz'), ('Agua'), ('Internet y teléfono'), ('Sueldos y pagos al personal'), ('Mantenimiento'),
  ('Alquiler'), ('Útiles y limpieza'), ('Transporte'), ('Impuestos y trámites'), ('Otros');

INSERT IGNORE INTO modulos (codigo, nombre, descripcion, activo) VALUES
  ('gastos', 'Gastos del grifo', 'Registro de gastos por categoría y medio de pago, con su comparación contra las ventas y compras del periodo.', 0);

INSERT IGNORE INTO menu_items (id_item, id_padre, tipo, titulo, icono, vista, orden, roles, protegido) VALUES
  (118, 100, 'enlace', 'Gastos', 'fas fa-receipt', 'gastos_grifo/view_gastos.php', 55, NULL, 0);

INSERT IGNORE INTO roles_capacidades (rol, capacidad)
SELECT codigo, 'gastos_gestionar' FROM roles WHERE codigo IN ('SUPERVISOR', 'CONTADOR');
