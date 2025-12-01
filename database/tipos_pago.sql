-- Tabla de tipos de pago
CREATE TABLE IF NOT EXISTS `tipos_pago` (
  `id_tipo_pago` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `requiere_codigo` enum('SI','NO') DEFAULT 'NO',
  `estado` enum('ACTIVO','INACTIVO') DEFAULT 'ACTIVO',
  PRIMARY KEY (`id_tipo_pago`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insertar tipos de pago si no existen
INSERT INTO `tipos_pago` (`id_tipo_pago`, `nombre`, `requiere_codigo`, `estado`) VALUES
(1, 'YAPE', 'SI', 'ACTIVO'),
(2, 'BCP', 'SI', 'ACTIVO'),
(3, 'VISA', 'SI', 'ACTIVO'),
(4, 'EFECTIVO', 'NO', 'ACTIVO'),
(5, 'DESCUENTO', 'NO', 'ACTIVO'),
(6, 'OTROS_GASTOS', 'NO', 'ACTIVO')
ON DUPLICATE KEY UPDATE nombre=nombre;
