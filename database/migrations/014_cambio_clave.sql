-- Cambio obligatorio de contraseña: claves de ejemplo o débiles, y las que asigna un administrador.
ALTER TABLE usuario ADD COLUMN debe_cambiar_clave TINYINT(1) NOT NULL DEFAULT 0;

-- Encendido por defecto. La instalación de demostración lo apaga: sus claves son públicas a propósito.
INSERT IGNORE INTO configuracion (clave, valor, secreto) VALUES ('clave_forzar_cambio', '1', 0);
