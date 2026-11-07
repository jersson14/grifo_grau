-- Copias de seguridad desde la interfaz.

INSERT IGNORE INTO configuracion (clave, valor) VALUES ('respaldo_conservar', '14');

UPDATE configuracion SET valor = CONCAT(valor, ',respaldo_antiguo')
WHERE clave = 'inicio_alertas' AND valor NOT LIKE '%respaldo_antiguo%';
