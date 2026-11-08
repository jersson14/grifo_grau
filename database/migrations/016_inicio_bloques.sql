-- Bloques de la página principal (Dashboard), elegibles en Configuración → Página principal. Por defecto, todos.
INSERT IGNORE INTO configuracion (clave, valor, secreto) VALUES
  ('inicio_bloques', 'indicadores,ventas_semana,combustibles,turnos,resumen_mes,creditos,mi_resumen,accesos', 0);
