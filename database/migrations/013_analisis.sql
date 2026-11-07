-- Análisis gerencial: rentabilidad, ranking de griferos, morosidad y ventas por horario (solo lectura).
INSERT IGNORE INTO menu_items (id_item, id_padre, tipo, titulo, icono, vista, orden, roles, protegido) VALUES
  (122, 112, 'enlace', 'Análisis y rentabilidad', 'fas fa-chart-pie', 'reportes/view_analisis.php', 5, NULL, 0);

INSERT IGNORE INTO roles_capacidades (rol, capacidad)
SELECT codigo, 'reportes_analisis' FROM roles WHERE codigo IN ('SUPERVISOR', 'CONTADOR');
