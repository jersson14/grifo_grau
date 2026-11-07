---
version: 1
slug: "index-php"
primary_target: "index.php"
related_targets: []
---

# Página pública del grifo

Alcance: `index.php` cuando el módulo «Página pública» está activo (si no, muestra el acceso). Modo: Persuade. El contenido se administra en Configuración → Página pública; los precios salen de Productos.

Audiencia: conductores de la zona (precio del día, horario, cómo llegar, servicios), empresas con flotas (solicitar crédito) y promociones vigentes. Acción principal: Cómo llegar / WhatsApp; secundaria: solicitud de flota.

Restricciones: sin testimonios, cifras ni fotos inventadas; sin paso de compilación en el hosting; contraste AA; celular primero.

## Direction contract

THESIS: Estándar de la categoría ejecutado con cuidado, elegido por el dueño («algo normal como los sistemas normales»): una página clara y confiable al estilo shadcn/ui, sin metáforas visuales; reemplaza al mundo «Carrocería y cinta reflectiva».

OWN-WORLD: Fondo blanco y gris zinc muy claro por secciones, bordes finos zinc-200, tarjetas con radio 12px y sombra mínima, letra Inter, botón primario zinc-900, un solo acento azul (blue-600) para iconos y enlaces, insignias suaves rojo/verde/ámbar para estados.

STORY: El visitante entiende en segundos dónde está, cuánto cuesta el galón hoy y si subió o bajó, y toca «Cómo llegar» o «WhatsApp»; la empresa con flota baja al formulario.

FIRST VIEWPORT: Cabecera blanca con logo, nombre y secciones; titular con la frase del día y su descripción; botones «Cómo llegar» (primario) y «WhatsApp» (contorno); debajo, «Precios de hoy» con una tarjeta por combustible, su precio grande y la insignia de cambio del día.

FORM: Canon de la categoría (salida estándar elegida por el dueño), sin forma sorteada; referencias de calidad: Vercel, Linear, Stripe. seed: none (unavailable)

FINISH: unreviewed and undocumented is unfinished; this build ends with the finish review, the verdict, DESIGN.md, and every shipping raster carrying its provenance
