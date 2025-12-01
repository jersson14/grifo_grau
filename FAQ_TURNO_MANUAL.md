# ❓ PREGUNTAS FRECUENTES - REGISTRO DE TURNO MANUAL

## 📋 GENERAL

### ¿Qué es el Registro de Turno Manual?
Es una nueva vista del sistema que replica el formato Excel que el cliente ya conoce, permitiendo ingresar datos manualmente en campos de texto de forma más intuitiva.

### ¿Reemplaza la vista anterior?
No necesariamente. Puedes mantener ambas vistas o usar solo la nueva. Es una opción adicional para facilitar el trabajo de usuarios no técnicos.

### ¿Necesito conocimientos técnicos para usarla?
No. El sistema está diseñado para ser usado por cualquier persona que sepa usar Excel básico.

---

## 🚀 INSTALACIÓN

### ¿Cómo instalo el sistema?
Sigue la guía completa en `INSTALACION_TURNO_MANUAL.md`. Los pasos básicos son:
1. Copiar archivos a sus ubicaciones
2. Ejecutar script SQL de tipos de pago
3. Agregar opción al menú
4. Probar funcionalidad

### ¿Qué archivos necesito?
- `view/turnos/view_registrar_turno_manual.php`
- `js/console_turnos_manual.js`
- `controller/turnos/controlador_guardar_turno_manual.php`
- `controller/turnos/controlador_cerrar_turno_manual.php`
- `database/tipos_pago.sql`

### ¿Necesito modificar la base de datos?
Sí, debes ejecutar el script `database/tipos_pago.sql` para crear la tabla de tipos de pago.

---

## 💻 USO DEL SISTEMA

### ¿Cómo abro un turno?
Usa la vista existente "Abrir Turno". El nuevo sistema solo se usa para registrar y cerrar el turno.

### ¿Puedo tener varios turnos abiertos?
No. Solo puede haber un turno abierto a la vez en todo el sistema.

### ¿Las lecturas aparecen en blanco?
Sí, en cada turno nuevo las lecturas aparecen con el valor de la lectura anterior, pero puedes editarlas manualmente.

### ¿Cuántas filas hay para pagos y créditos?
Por defecto hay 15 filas para cada uno. Si necesitas más, se puede modificar fácilmente en el código.

### ¿Los totales se calculan automáticamente?
Sí, todos los totales se calculan en tiempo real mientras escribes.

### ¿Puedo guardar sin cerrar el turno?
Sí, usa el botón "GUARDAR CAMBIOS" para guardar sin cerrar. Puedes guardar múltiples veces.

### ¿Qué pasa al cerrar el turno?
Al cerrar el turno:
1. Se guardan todos los datos
2. Se actualiza el estado a CERRADO
3. Se actualizan las lecturas de los surtidores
4. Se calcula el faltante/sobrante
5. Ya no se puede editar

---

## 📊 LECTURAS

### ¿Cómo ingreso las lecturas?
Simplemente haz clic en el campo de "Lectura Actual" y escribe el valor. Los galones y totales se calculan automáticamente.

### ¿Puedo poner una lectura menor a la anterior?
No. El sistema validará que la lectura actual sea mayor o igual a la anterior.

### ¿Qué pasa si me equivoco en una lectura?
Puedes corregirla antes de cerrar el turno. Simplemente edita el campo y los cálculos se actualizarán.

### ¿Los decimales son importantes?
Sí, las lecturas usan 3 decimales (ejemplo: 12345.678). Los montos usan 2 decimales (ejemplo: 100.50).

---

## 💰 PAGOS

### ¿Cómo registro un pago?
Ingresa el monto en la columna correspondiente (YAPE, BCP, VISA, etc.) y el código de operación si aplica.

### ¿El código de operación es obligatorio?
Sí, para YAPE, BCP y VISA. Para EFECTIVO, DESCUENTOS y OTROS GASTOS no es necesario.

### ¿Puedo dejar filas vacías?
Sí, solo se guardarán las filas que tengan monto mayor a 0.

### ¿Qué pasa si necesito más de 15 pagos?
Puedes modificar el código para agregar más filas, o usar múltiples filas para el mismo tipo de pago.

### ¿Puedo poner varios pagos del mismo tipo?
Sí, puedes usar varias filas para el mismo tipo de pago (ejemplo: 3 pagos por YAPE en diferentes filas).

---

## 💳 CRÉDITOS

### ¿Cómo registro un crédito?
Ingresa el monto, el nombre del cliente y el número de vale.

### ¿El cliente debe existir en el sistema?
Sí, el cliente debe estar registrado previamente en el módulo de clientes.

### ¿Cómo busco un cliente?
Empieza a escribir el nombre y aparecerá un autocompletado con los clientes disponibles.

### ¿Qué pasa si el cliente no aparece?
Debes registrarlo primero en el módulo de clientes antes de poder asignarle un crédito.

### ¿El número de vale es obligatorio?
Sí, es obligatorio para identificar el crédito.

---

## 🧮 CUADRE DE CAJA

### ¿Cómo se calcula el cuadre?
```
Total Justificado = Pagos + Créditos + Otros Gastos + Efectivo
Total Neto Ventas = Total Ventas - Descuentos
Diferencia = Total Justificado - Total Neto Ventas
```

### ¿Qué significa FALTANTE?
Significa que falta dinero en caja. El total justificado es menor que las ventas.

### ¿Qué significa SOBRANTE?
Significa que sobra dinero en caja. El total justificado es mayor que las ventas.

### ¿Qué significa CUADRADO?
Significa que la caja está perfectamente cuadrada. No falta ni sobra dinero.

### ¿Puedo cerrar el turno con faltante?
Sí, el sistema permite cerrar con faltante o sobrante. Se registrará el monto de la diferencia.

---

## 🐛 PROBLEMAS COMUNES

### No se cargan las lecturas
**Solución**: Verifica que hay un turno abierto. Si no hay, abre uno primero.

### Los totales no se calculan
**Solución**: 
1. Limpia el caché del navegador (Ctrl + F5)
2. Verifica que el archivo `console_turnos_manual.js` está cargado
3. Abre la consola del navegador (F12) y busca errores

### Error al guardar
**Solución**:
1. Verifica la conexión a la base de datos
2. Verifica que la tabla `tipos_pago` existe
3. Revisa los logs de error de PHP

### No aparece la opción en el menú
**Solución**: Verifica que agregaste correctamente la opción al archivo de menú.

### Los clientes no aparecen en el autocompletado
**Solución**: Verifica que hay clientes registrados en el sistema con estado ACTIVO.

---

## 🔧 PERSONALIZACIÓN

### ¿Puedo cambiar el número de filas?
Sí, edita el archivo `js/console_turnos_manual.js` y cambia el número 15 en las funciones:
- `Generar_Filas_Pagos()`
- `Generar_Filas_Creditos()`

### ¿Puedo agregar más tipos de pago?
Sí, debes:
1. Agregar el tipo en la tabla `tipos_pago`
2. Modificar el controlador `controlador_guardar_turno_manual.php`
3. Agregar la columna en la vista `view_registrar_turno_manual.php`

### ¿Puedo cambiar los colores?
Sí, edita los estilos CSS en la vista `view_registrar_turno_manual.php`.

### ¿Puedo cambiar el formato de la tabla?
Sí, pero ten cuidado de no romper la funcionalidad. Modifica solo los estilos CSS.

---

## 📱 COMPATIBILIDAD

### ¿Funciona en móviles?
Sí, pero se recomienda usar en tablet o computadora para mejor experiencia.

### ¿Funciona en todos los navegadores?
Sí, funciona en Chrome, Firefox, Edge y Safari modernos.

### ¿Necesito internet?
Solo si el sistema está en un servidor remoto. Si está en red local, no necesitas internet.

---

## 🔐 SEGURIDAD

### ¿Quién puede ver los turnos?
Depende de los permisos configurados en el sistema. Generalmente ADMINISTRADOR y GRIFERO.

### ¿Se pueden editar turnos cerrados?
No, una vez cerrado el turno no se puede editar.

### ¿Se guardan los cambios automáticamente?
No, debes hacer clic en "GUARDAR CAMBIOS" para guardar.

### ¿Qué pasa si cierro el navegador sin guardar?
Perderás los cambios no guardados. Guarda frecuentemente.

---

## 📊 REPORTES

### ¿Puedo imprimir el turno?
Sí, desde el historial de turnos puedes imprimir el reporte en PDF.

### ¿Puedo exportar a Excel?
El sistema genera PDF. Si necesitas Excel, puedes copiar y pegar los datos.

### ¿Dónde veo el historial?
En la opción "Historial de Turnos" del menú.

---

## 🎓 CAPACITACIÓN

### ¿Hay un manual de usuario?
Sí, consulta `GUIA_REGISTRO_TURNO_MANUAL.md`.

### ¿Hay videos tutoriales?
Puedes grabar tus propios videos siguiendo la guía de uso.

### ¿Cuánto tiempo toma aprender?
Si ya conoces Excel, aprenderás en 10-15 minutos. Es muy intuitivo.

---

## 🔄 MIGRACIÓN

### ¿Puedo migrar datos del Excel al sistema?
No automáticamente, pero puedes copiar y pegar los datos manualmente.

### ¿Puedo usar ambas vistas (antigua y nueva)?
Sí, puedes mantener ambas opciones en el menú.

### ¿Qué vista es mejor?
Depende del usuario:
- **Vista nueva**: Para usuarios acostumbrados a Excel
- **Vista antigua**: Para usuarios técnicos que prefieren modales

---

## 💡 TIPS Y TRUCOS

### Usa TAB para navegar
Presiona TAB para moverte rápidamente entre campos.

### Guarda frecuentemente
No esperes a terminar todo. Guarda cada cierto tiempo.

### Verifica el cuadre antes de cerrar
Revisa que los números cuadren antes de cerrar el turno.

### Usa el autocompletado
Para clientes, empieza a escribir y selecciona de la lista.

### Copia desde Excel
Si tienes datos en Excel, puedes copiar y pegar.

---

## 📞 SOPORTE

### ¿Dónde encuentro más ayuda?
Consulta los siguientes documentos:
- `GUIA_REGISTRO_TURNO_MANUAL.md` - Guía de uso
- `INSTALACION_TURNO_MANUAL.md` - Guía de instalación
- `INTEGRACION_MENU_TURNO_MANUAL.md` - Integración al menú
- `RESUMEN_REGISTRO_TURNO_MANUAL.md` - Resumen completo
- `EJEMPLO_VISUAL_TURNO_MANUAL.md` - Ejemplos visuales

### ¿Cómo reporto un error?
Anota el mensaje de error, los pasos para reproducirlo y consulta con el administrador del sistema.

### ¿Puedo sugerir mejoras?
Sí, todas las sugerencias son bienvenidas para mejorar el sistema.

---

## 🎯 MEJORES PRÁCTICAS

1. **Abre el turno al inicio del día/turno**
2. **Registra las ventas durante el turno**
3. **Guarda cambios cada hora**
4. **Verifica el cuadre al final**
5. **Cierra el turno al terminar**
6. **Revisa el reporte impreso**
7. **Archiva los documentos físicos**

---

**¿Tienes más preguntas?** Consulta la documentación completa o contacta al administrador del sistema.
