-- Script para permitir NULL en id_reporte de ventas_credito
-- Esto permite agregar créditos manuales sin turno

-- Paso 1: Eliminar la foreign key existente
ALTER TABLE `ventas_credito` DROP FOREIGN KEY `ventas_credito_ibfk_1`;

-- Paso 2: Actualizar registros con id_reporte inválidos a NULL
UPDATE `ventas_credito` SET `id_reporte` = NULL 
WHERE `id_reporte` NOT IN (SELECT `id_reporte` FROM `reportes_turno`);

-- Paso 3: Modificar la columna para permitir NULL
ALTER TABLE `ventas_credito` MODIFY `id_reporte` INT(11) NULL;

-- Paso 4: Volver a crear la foreign key permitiendo NULL
ALTER TABLE `ventas_credito` 
ADD CONSTRAINT `ventas_credito_ibfk_1` 
FOREIGN KEY (`id_reporte`) 
REFERENCES `reportes_turno` (`id_reporte`) 
ON DELETE SET NULL 
ON UPDATE CASCADE;
