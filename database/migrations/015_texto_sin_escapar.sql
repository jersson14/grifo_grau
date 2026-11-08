-- Los datos de texto se guardaban pasados por htmlspecialchars (&amp;, &quot;...).
-- Desde ahora se guardan tal cual y se escapan al mostrarse: aquí se devuelven los guardados a su texto normal.
-- Solo toca valores que contienen '&'. Las salidas (pantallas, PDF y Excel) escapan con Texto_Seguro y esc().

UPDATE clientes SET
    nombre_completo = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(nombre_completo, '&lt;', '<'), '&gt;', '>'), '&quot;', '"'), '&#039;', ''''), '&#39;', ''''), '&amp;', '&'),
    dni_ruc = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(dni_ruc, '&lt;', '<'), '&gt;', '>'), '&quot;', '"'), '&#039;', ''''), '&#39;', ''''), '&amp;', '&'),
    telefono = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(telefono, '&lt;', '<'), '&gt;', '>'), '&quot;', '"'), '&#039;', ''''), '&#39;', ''''), '&amp;', '&'),
    direccion = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(direccion, '&lt;', '<'), '&gt;', '>'), '&quot;', '"'), '&#039;', ''''), '&#39;', ''''), '&amp;', '&')
WHERE nombre_completo LIKE '%&%' OR dni_ruc LIKE '%&%' OR telefono LIKE '%&%' OR direccion LIKE '%&%';

UPDATE usuario SET
    usu_usuario = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(usu_usuario, '&lt;', '<'), '&gt;', '>'), '&quot;', '"'), '&#039;', ''''), '&#39;', ''''), '&amp;', '&'),
    usu_nombre = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(usu_nombre, '&lt;', '<'), '&gt;', '>'), '&quot;', '"'), '&#039;', ''''), '&#39;', ''''), '&amp;', '&'),
    usu_apellido = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(usu_apellido, '&lt;', '<'), '&gt;', '>'), '&quot;', '"'), '&#039;', ''''), '&#39;', ''''), '&amp;', '&'),
    usu_email = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(usu_email, '&lt;', '<'), '&gt;', '>'), '&quot;', '"'), '&#039;', ''''), '&#39;', ''''), '&amp;', '&'),
    usu_telefono = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(usu_telefono, '&lt;', '<'), '&gt;', '>'), '&quot;', '"'), '&#039;', ''''), '&#39;', ''''), '&amp;', '&'),
    usu_direccion = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(usu_direccion, '&lt;', '<'), '&gt;', '>'), '&quot;', '"'), '&#039;', ''''), '&#39;', ''''), '&amp;', '&'),
    dni_usuario = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(dni_usuario, '&lt;', '<'), '&gt;', '>'), '&quot;', '"'), '&#039;', ''''), '&#39;', ''''), '&amp;', '&')
WHERE usu_usuario LIKE '%&%' OR usu_nombre LIKE '%&%' OR usu_apellido LIKE '%&%' OR usu_email LIKE '%&%' OR usu_telefono LIKE '%&%' OR usu_direccion LIKE '%&%' OR dni_usuario LIKE '%&%';

UPDATE ventas_credito SET
    numero_vale = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(numero_vale, '&lt;', '<'), '&gt;', '>'), '&quot;', '"'), '&#039;', ''''), '&#39;', ''''), '&amp;', '&'),
    placa = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(placa, '&lt;', '<'), '&gt;', '>'), '&quot;', '"'), '&#039;', ''''), '&#39;', ''''), '&amp;', '&'),
    observaciones = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(observaciones, '&lt;', '<'), '&gt;', '>'), '&quot;', '"'), '&#039;', ''''), '&#39;', ''''), '&amp;', '&')
WHERE numero_vale LIKE '%&%' OR placa LIKE '%&%' OR observaciones LIKE '%&%';

UPDATE historial_pagos_credito SET
    codigo_operacion = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(codigo_operacion, '&lt;', '<'), '&gt;', '>'), '&quot;', '"'), '&#039;', ''''), '&#39;', ''''), '&amp;', '&'),
    observaciones = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(observaciones, '&lt;', '<'), '&gt;', '>'), '&quot;', '"'), '&#039;', ''''), '&#39;', ''''), '&amp;', '&')
WHERE codigo_operacion LIKE '%&%' OR observaciones LIKE '%&%';

UPDATE pagos_reporte SET
    codigo_operacion = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(codigo_operacion, '&lt;', '<'), '&gt;', '>'), '&quot;', '"'), '&#039;', ''''), '&#39;', ''''), '&amp;', '&'),
    observaciones = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(observaciones, '&lt;', '<'), '&gt;', '>'), '&quot;', '"'), '&#039;', ''''), '&#39;', ''''), '&amp;', '&')
WHERE codigo_operacion LIKE '%&%' OR observaciones LIKE '%&%';

UPDATE reportes_turno SET
    observaciones = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(observaciones, '&lt;', '<'), '&gt;', '>'), '&quot;', '"'), '&#039;', ''''), '&#39;', ''''), '&amp;', '&')
WHERE observaciones LIKE '%&%';

UPDATE empresa SET
    nombre = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(nombre, '&lt;', '<'), '&gt;', '>'), '&quot;', '"'), '&#039;', ''''), '&#39;', ''''), '&amp;', '&'),
    direccion = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(direccion, '&lt;', '<'), '&gt;', '>'), '&quot;', '"'), '&#039;', ''''), '&#39;', ''''), '&amp;', '&'),
    telefono = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(telefono, '&lt;', '<'), '&gt;', '>'), '&quot;', '"'), '&#039;', ''''), '&#39;', ''''), '&amp;', '&'),
    email = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(email, '&lt;', '<'), '&gt;', '>'), '&quot;', '"'), '&#039;', ''''), '&#39;', ''''), '&amp;', '&')
WHERE nombre LIKE '%&%' OR direccion LIKE '%&%' OR telefono LIKE '%&%' OR email LIKE '%&%';

UPDATE productos SET
    nombre = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(nombre, '&lt;', '<'), '&gt;', '>'), '&quot;', '"'), '&#039;', ''''), '&#39;', ''''), '&amp;', '&')
WHERE nombre LIKE '%&%';

UPDATE surtidores SET
    codigo = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(codigo, '&lt;', '<'), '&gt;', '>'), '&quot;', '"'), '&#039;', ''''), '&#39;', ''''), '&amp;', '&')
WHERE codigo LIKE '%&%';
