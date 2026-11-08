-- Historial del chatbot por usuario: «Nueva conversación» oculta lo anterior sin borrarlo de la bitácora de consumo.
ALTER TABLE ia_consultas ADD COLUMN oculta TINYINT(1) NOT NULL DEFAULT 0 AFTER estado;
ALTER TABLE ia_consultas ADD KEY ix_ia_usuario_visible (id_usuario, oculta, id_consulta);
