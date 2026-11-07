# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Stack

PHP sin framework (MVC propio), jQuery y MySQL/MariaDB, desplegado en hosting compartido. La interfaz nueva usa Tailwind CSS compilado a un único CSS que se versiona en el repositorio: el hosting no ejecuta Node ni ningún paso de compilación. Elegido por el dueño el 2026-11-07 frente a reescribir en React + shadcn/ui; se adopta el lenguaje visual de shadcn/ui (su estilo convencional, sin temas propios), no su código React. Las pantallas antiguas (AdminLTE 3 + Bootstrap 4) se migran una a una sin romper su lógica.

## Users

- **Grifero:** atiende la isla en turnos de día o de noche, a menudo desde el celular, de pie, con prisa y a veces con poca luz. Registra lecturas de surtidores, pagos, créditos y ventas, y cierra su turno.
- **Administrador / dueño:** revisa y valida turnos, controla caja, inventario, créditos, precios y usuarios; configura el sistema y la página pública. Trabaja sobre todo en computadora.
- **Supervisor, contador, cajero:** roles con permisos editables; consultan reportes, cobran créditos o arquean caja.
- **Visitantes de la página pública:** conductores de la zona (precios del día, horario, cómo llegar, servicios, promociones) y empresas con flotas (pedir crédito o convenio).

## Product Purpose

Gestionar una estación de servicio (grifo) en Perú: turnos con lecturas de 12 surtidores en 2 máquinas, ventas de combustible (Diesel B5, Regular 84, Premium 95), créditos con vales, caja y arqueos, inventario de tanques, compras, precios, facturación electrónica opcional (Nubefact) y un chatbot de IA opcional. Éxito: que cada turno cuadre sin hojas de papel, que el dueño sepa en minutos cuánto vendió y cuánto le deben, y que la página pública atraiga clientes y flotas.

## Positioning

Hecho a la medida de un grifo peruano: turnos con lecturas reales de surtidores, vales de crédito por placa, medios de pago locales (YAPE, BCP, VISA), boletas y facturas SUNAT, y una página pública cuyos precios salen directamente del sistema, sin actualizarlos a mano.

## Operating Context

- Turnos DÍA y NOCHE con número DOC-XXXX; al cerrar se calculan galones vendidos y se actualizan lecturas.
- Seis medios de pago: YAPE, BCP y VISA con código de operación; EFECTIVO, DESCUENTO y OTROS_GASTOS sin código.
- Moneda soles (S/), zona horaria America/Lima, idioma español de Perú.
- Reportes en PDF (mPDF) y Excel.
- Todo lo configurable se administra desde el panel; los módulos opcionales se activan y desactivan sin bloquear los turnos.

## Capabilities and Constraints

- Existente: turnos, créditos y cobranza, clientes, surtidores, productos y precios (historial y programación), inventario de tanques, varillaje, compras y cuentas por pagar, ventas individuales y conciliación, caja con arqueo y depósitos, roles y permisos editables, menú editable, auditoría, copias de seguridad, facturación electrónica, chatbot de IA.
- Por construir, en orden pedido por el dueño: página pública administrable (precios en vivo, servicios, horario, ubicación, contacto, promociones, solicitud de crédito para flotas), gastos del grifo, tienda/market, flotas y vales, personal.
- Debe funcionar en hosting compartido sin cron ni procesos en segundo plano, con PHP 8.2+ y MySQL/MariaDB.
- La identidad del grifo (nombre, logo, colores, dirección, teléfono, WhatsApp, redes) se carga desde Configuración; hoy hay datos de ejemplo ("GRIFO DEMO").

## Brand Commitments

- **Estilo visual: convencional, tipo shadcn/ui.** Elegido por el dueño el 2026-11-07 (al ver el mundo «Carrocería y cinta reflectiva» pidió «algo normal como los sistemas normales»). Referencias de calidad: Vercel, Linear y Stripe. Fondo claro, bordes finos, letra Inter, un solo color de acento; aplica a la página pública, el acceso y el panel.
- Nombre, logo y datos de contacto los define el dueño desde Configuración; el código no fija ninguna marca. Voz en español claro y directo, tuteo o usted según la pantalla existente (el panel usa usted).

## Evidence on Hand

- Datos reales de operación en la base de producción, aún no exportados.
- No hay testimonios, fotos propias del grifo, cifras de clientes ni premios: la página pública no debe inventarlos.
- Logo de ejemplo en `img/grau.png`.

## Product Principles

1. El turno nunca se bloquea: ningún módulo opcional ni pantalla nueva puede impedir registrar o cerrar un turno.
2. Lo configurable vive en el panel, no en el código.
3. El grifero en el celular es el caso más exigente: si funciona ahí, funciona en todos lados.
4. La página pública solo muestra datos verdaderos que salen del sistema o que el dueño escribió.
5. Migrar sin romper: cada pantalla rediseñada conserva su lógica y sus pruebas.

## Accessibility & Inclusion

Contraste WCAG AA medido (`tests/contraste.cjs`), campos con etiqueta, letra de 16 px en campos táctiles y objetivos de 44 px en celular; ya exigido por las pruebas de la Fase 8.
