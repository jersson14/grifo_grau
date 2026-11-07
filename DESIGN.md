---
name: Grifo Grau
description: Carrocería pintada y cinta reflectiva para un grifo peruano; primera superficie, la página pública.
colors:
  cabina: "#0b2a4a"
  cabina-profunda: "#071d34"
  cabina-clara: "#123a63"
  cabina-linea: "#2b5582"
  hueso: "#f4f1ea"
  hueso-oscuro: "#e4ded1"
  reflectivo: "#d7261e"
  reflectivo-tinta: "#a8170f"
  seguridad: "#f6b800"
  seguridad-oscuro: "#c99400"
  cromo: "#c9ced4"
  cromo-oscuro: "#8b939c"
  cromo-claro: "#eef1f4"
  caucho: "#16181b"
  caucho-borde: "#2a2e33"
  tinta: "#0d1117"
  blanco: "#ffffff"
typography:
  display:
    fontFamily: "Big Shoulders Display, Barlow Semi Condensed, sans-serif"
    fontSize: "clamp(2.4rem, 7vw, 4.5rem)"
    fontWeight: 900
    lineHeight: 0.98
    letterSpacing: "0.01em"
  numeral:
    fontFamily: "Big Shoulders Display, Barlow Semi Condensed, sans-serif"
    fontSize: "clamp(2.2rem, 8.6vw, 6rem)"
    fontWeight: 900
    lineHeight: 1
    letterSpacing: "0.01em"
    fontFeature: "tnum, lnum"
  headline:
    fontFamily: "Big Shoulders Display, Barlow Semi Condensed, sans-serif"
    fontSize: "clamp(2.5rem, 6vw, 4.5rem)"
    fontWeight: 900
    lineHeight: 0.9
    letterSpacing: "0.01em"
  title:
    fontFamily: "Big Shoulders Display, Barlow Semi Condensed, sans-serif"
    fontSize: "clamp(2rem, 4.6vw, 3.5rem)"
    fontWeight: 900
    lineHeight: 0.9
    letterSpacing: "0.01em"
  body:
    fontFamily: "Barlow Semi Condensed, system-ui, sans-serif"
    fontSize: "1.125rem"
    fontWeight: 400
    lineHeight: 1.375
  label:
    fontFamily: "Barlow Semi Condensed, system-ui, sans-serif"
    fontSize: "1rem"
    fontWeight: 600
    lineHeight: 1.5
    letterSpacing: "0.025em"
  placa:
    fontFamily: "Big Shoulders Display, Barlow Semi Condensed, sans-serif"
    fontSize: "1.25rem"
    fontWeight: 800
    lineHeight: 1.75
    letterSpacing: "0.025em"
rounded:
  sello: "3px"
  campo: "4px"
  cara: "5px"
  ficha: "6px"
  plancha: "7px"
  luna: "26px"
spacing:
  plancha: "8px"
  gutter-sm: "16px"
  gutter: "24px"
  seccion-sm: "64px"
  seccion: "96px"
  contenedor: "72rem"
components:
  placa-hueso:
    backgroundColor: "{colors.hueso}"
    textColor: "{colors.tinta}"
    typography: "{typography.placa}"
    rounded: "{rounded.cara}"
    padding: "0 32px"
    height: "56px"
  placa-roja:
    backgroundColor: "{colors.reflectivo-tinta}"
    textColor: "{colors.hueso}"
    typography: "{typography.placa}"
    rounded: "{rounded.cara}"
    padding: "0 32px"
    height: "56px"
  boton-contorno:
    backgroundColor: "transparent"
    textColor: "{colors.hueso}"
    typography: "{typography.label}"
    rounded: "{rounded.campo}"
    padding: "0 16px"
    height: "44px"
  campo:
    backgroundColor: "{colors.blanco}"
    textColor: "{colors.tinta}"
    typography: "{typography.body}"
    rounded: "{rounded.campo}"
    padding: "12px 14px"
  sello-sube:
    backgroundColor: "{colors.reflectivo-tinta}"
    textColor: "{colors.hueso}"
    rounded: "{rounded.sello}"
    padding: "4px 12px"
  sello-baja:
    backgroundColor: "{colors.seguridad}"
    textColor: "{colors.tinta}"
    rounded: "{rounded.sello}"
    padding: "4px 12px"
  ficha:
    backgroundColor: "{colors.cabina}"
    textColor: "{colors.hueso}"
    rounded: "{rounded.ficha}"
    padding: "32px"
  luna:
    backgroundColor: "{colors.cabina-profunda}"
    textColor: "{colors.hueso}"
    rounded: "{rounded.luna}"
    padding: "28px 48px"
---

# Design System: Grifo Grau

## Overview

**Creative North Star: "Carrocería y cinta reflectiva"**

El sistema habla el idioma de quien maneja: cada superficie es la carrocería pintada de un camión o una combi peruana de ruta, no la web corporativa de una cadena de estaciones. El fondo es esmalte azul de cabina; la letra es rótulo condensado pintado a mano con contorno oscuro y sombreado rojo; los divisores son cinta reflectiva roja y blanca en diagonal; los botones son placas de esmalte atornilladas sobre una plancha de acero; los precios van en loderas de caucho. Todo es plano y pintado: la profundidad sale de planchas superpuestas, nunca de sombras difusas.

La densidad es baja y la escala alta en la página pública (modo Persuade): cifras gigantes, titulares que ocupan el ancho, una acción por bloque. El estado se dice con forma antes que con color: cinta continua es vigente, cinta cortada es pendiente o por terminar, cinta tachada es inactiva.

El mundo se extenderá al panel de operación (registro de turnos y ventas en celular, pantallas de administración), hoy en AdminLTE 3 + Bootstrap 4, que se migrará pantalla por pantalla con Tailwind prefijado conviviendo con Bootstrap. El panel hereda la paleta, las dos familias tipográficas y el lenguaje de materiales (esmalte, placa, cinta, plancha superpuesta) con la contención del modo Operate: rótulo pintado y cifras gigantes solo donde un dato manda, texto de trabajo para todo lo demás. Sus componentes construidos están en «Panel de operación»; los que falten se documentan al construirlos.

**Key Characteristics:**
- Esmalte azul de cabina como fondo dominante; hueso como letra y como cara clara.
- Rótulo condensado pesado, siempre en mayúsculas, pintado con contorno y sombreado en los momentos de mayor jerarquía.
- Cinta reflectiva roja y blanca como divisor y como señal de estado por forma.
- Placas atornilladas como botones: cara de esmalte con pernos sobre una plancha de acero fija.
- Plano por completo: profundidad por superposición de planchas, sin desenfoque.
- Cifras tabulares que ruedan como el contador de un surtidor.

## Colors

Esmaltes de carrocería saturados sobre un azul de cabina oscuro, con acero y caucho como neutros de material y un único amarillo reservado.

### Primary
- **Azul Cabina** (cabina): fondo dominante de la página y cara de la ficha técnica pintada.
- **Cabina Profunda** (cabina-profunda): cabecera, pie, banda de servicios y vidrio de la luna trasera; también `theme-color` del navegador.
- **Cabina Clara** (cabina-clara): exclusivamente el contorno de foco de los campos de formulario.
- **Filete de Cabina** (cabina-linea): filetes pintados, bordes punteados entre filas, borde del botón de contorno y tercera franja del filete de titulares.

### Secondary
- **Rojo Reflectivo** (reflectivo): las franjas de la cinta, el sombreado del rótulo pintado, el aro del emblema y el anillo exterior del foco global.
- **Rojo Esmalte** (reflectivo-tinta): cara de la placa roja (acción primaria), sello SUBIÓ, fondo de la banda de promociones, texto y borde de error.

### Tertiary
- **Amarillo de Seguridad** (seguridad): solo el sello BAJÓ. El tono oscuro (seguridad-oscuro) está declarado sin uso en la página.

### Neutral
- **Blanco Hueso** (hueso): texto sobre cabina, cara de la placa clara, fondo de la sección de flotas, filete de la ficha y contorno interior del foco global.
- **Hueso Oscuro** (hueso-oscuro): declarado; sin uso en la página.
- **Acero** (cromo): rótulos de etiqueta («POR GALÓN», «DIRECCIÓN») y texto del pie sobre cabina.
- **Acero Oscuro** (cromo-oscuro): plancha de montaje de las placas, borde superior de la defensa, soporte de la lodera, cinta tachada, barra de desplazamiento.
- **Acero Claro** (cromo-claro): texto informativo sobre la defensa de caucho.
- **Caucho** (caucho): defensa, jebe de la luna, cuerpo de la lodera, anillo interior de los sellos.
- **Tinta** (tinta): contornos de 2-3px de placas y campos, contorno del rótulo pintado, texto sobre hueso y blanco.
- **Blanco** (blanco): franjas blancas de la cinta y fondo del formulario y sus campos.

### Named Rules
**The Sello Amarillo Rule.** El amarillo de seguridad pertenece solo al sello BAJÓ (con flecha abajo). SUBIÓ es rojo esmalte con flecha arriba. Ningún otro elemento usa amarillo.

**The Cinta Habla Rule.** La cinta reflectiva no es decoración libre: continua es vigente, cortada es pendiente o termina pronto, tachada (acero con raya de tinta) es inactiva. No se usa para otra cosa que dividir y señalar estado.

## Typography

**Display Font:** Big Shoulders Display, variable 100-900, autoalojada (con Barlow Semi Condensed, sans-serif)
**Body Font:** Barlow Semi Condensed 400/500/600/700, autoalojada (con system-ui, sans-serif)

**Character:** Rótulo de carrocería pesado y estrecho para nombres, titulares, cifras y placas; letra de trabajo semicondensada, clara y amplia (base 17px) para leer de pie y con prisa.

### Hierarchy
- **Display** (900, clamp(2.4rem, 7vw, 4.5rem), 0.98): la frase pintada en la luna trasera; el nombre del grifo va justo encima en clamp(2.25rem, 5vw, 4rem).
- **Numeral** (900, clamp(2.2rem, 8.6vw, 6rem), 1, cifras tabulares): precio por galón en la lodera, con «S/» a un tercio del tamaño.
- **Headline** (900, clamp(2.5rem, 6vw, 4.5rem), 0.9): títulos de sección.
- **Title** (900, clamp(2rem, 4.6vw, 3.5rem), 0.9): nombres de servicio y de promoción; paso de la ficha a 1.75rem.
- **Body** (400, 1.125-1.25rem, 1.375): descripciones; medida 48-60ch.
- **Label** (600, 1rem, 0.025em, mayúsculas): etiquetas de dato sobre cabina; en formularios, 600 sin mayúsculas.

### Named Rules
**The Rótulo Rule.** Toda letra de Big Shoulders va en mayúsculas, peso 800-900, interlínea 0.9 y tracking 0.01em. Nunca para párrafos.

**The Letra Pintada Rule.** El contorno de tinta con sombreado rojo (sombra sin desenfoque, desplazada 0.07-0.11em) se reserva para la letra de mayor jerarquía sobre esmalte: nombre del grifo, frase de la luna, precios, servicios, título de promociones, números de la ficha. Dentro del contador recortado solo lleva el sombreado rojo. Sobre fondo hueso los titulares van en azul cabina sin pintar.

**The Cifra Tabular Rule.** Todo número operativo (precios, teléfono, RUC, cantidades) usa cifras tabulares y alineadas.

## Layout

Columna centrada de 72rem con márgenes de 16px en celular y 24px desde 640px. Las secciones se apilan como bandas de color de esmalte a ancho completo (cabina, cabina profunda, rojo esmalte, hueso) con 64px de relleno vertical, 96px desde 640px. Las loderas de precios rompen la columna y ocupan casi todo el ancho, hasta cuatro en una fila sin importar el ancho de pantalla, con huecos de 8/20/32px. Servicios es una lista de filas separadas por raya punteada de 3px en dos columnas desde 768px; flotas es ficha más formulario en 5:7 desde 1024px. Puntos de quiebre: 640, 768, 1024px. Los objetivos táctiles miden al menos 44px; las placas, 56px.

## Elevation & Depth

Sistema plano. No hay ninguna sombra con desenfoque. La profundidad se construye superponiendo planchas planas: la cara de la placa sobre su plancha de acero más grande, la luna dentro de su jebe de caucho, la lodera colgada de su soporte, la defensa con su borde de acero. Los únicos usos de `box-shadow` y `text-shadow` son anillos de expansión sin desenfoque (sellos, foco) y el contorno y sombreado duros del rótulo pintado, que es un recurso nativo de la letra de carrocería.

### Shadow Vocabulary
- **Foco global** (`outline: 3px solid hueso; outline-offset: 3px; box-shadow: 0 0 0 6px reflectivo`): todo elemento enfocable fuera de los campos.
- **Anillo de sello** (`box-shadow: 0 0 0 2px caucho, 0 0 0 4px hueso|seguridad`): borde doble pintado del sello.
- **Letra pintada** (contorno de tinta de 0.03-0.035em en ocho direcciones, sombreado `0.07em 0.075em 0` rojo reflectivo y `0.1em 0.11em 0` tinta): rótulos de mayor jerarquía.

### Named Rules
**The Plancha Superpuesta Rule.** Si algo necesita sentirse elevado, se le pone una plancha detrás o un borde de material alrededor; nunca una sombra difusa.

## Shapes

Esquinas casi rectas de chapa cortada: 3px en sellos, 4px en campos y botón de contorno, 5px en la cara de la placa, 7px en su plancha, 6px en la ficha y la tarjeta del formulario. La única curva grande es la luna trasera (26px) con jebe de 10px. La lodera es una silueta SVG con esquinas inferiores cortadas a 45°, estrías horizontales y un emblema circular (gota en aro rojo). Bordes de material: 2-3px de tinta en placas y campos, 6px de acero sobre la defensa, filetes interiores de 2px desplazados 10-12px hacia adentro en la luna y la ficha. Los sellos se giran -3°. Los iconos de la red social y el logo son círculos.

## Components

### Buttons
Placas de esmalte atornilladas: táctiles, macizas, de camión.
- **Shape:** cara de 5px de radio con borde de tinta de 3px y cuatro cabezas de perno hexagonales de 12px a 7px de cada esquina, sobre una plancha de montaje de acero oscuro con borde de tinta de 2px, radio 7px, que sobresale 8px por cada lado (la placa lleva 8px de margen para contenerla).
- **Primary (placa roja):** cara rojo esmalte, letra hueso; rótulo 800 en mayúsculas, 1.125rem en celular y 1.25rem desde 640px, alto mínimo 56px, relleno lateral 24/32px, icono a la izquierda.
- **Secondary (placa hueso):** cara hueso, letra tinta.
- **Hover / Active:** solo la cara y su contenido se mueven (-2px al pasar, +1px al presionar, 160ms cubic-bezier(0.16, 1, 0.3, 1)); la plancha queda fija. Sin movimiento con reducción de movimiento. Deshabilitada: 70% de opacidad, cursor de espera, sin movimiento.
- **Contorno (Ingresar):** borde de 2px filete de cabina, radio 4px, letra hueso al 90%, 44px de alto; al pasar, borde y letra hueso.

### Chips
- **Sello de cambio de precio:** SUBIÓ en rojo esmalte con flecha arriba y letra hueso; BAJÓ en amarillo de seguridad con flecha abajo y letra tinta. Rótulo 800, radio 3px, girado -3°, anillo doble caucho más hueso o amarillo.

### Cards / Containers
- **Lodera:** precio de combustible; silueta de caucho con soporte de acero, estrías y emblema pintado al pie; nombre en rótulo hueso, cifra pintada, «POR GALÓN» en acero.
- **Luna trasera:** vidrio cabina profunda, radio 26px, jebe de caucho de 10px y filete interior de cabina; lleva la frase pintada.
- **Ficha técnica pintada:** panel azul cabina, radio 6px, filete hueso de 2px desplazado 10px hacia adentro; pasos numerados con número pintado y filas separadas por raya punteada.
- **Defensa:** banda de caucho con borde superior de acero de 6px e inferior de tinta de 3px; aquí se atornillan las placas.
- **Tarjeta de formulario:** blanco, borde de tinta de 3px, radio 6px, relleno 20/32px.

### Inputs / Fields
- **Style:** fondo blanco, borde de tinta de 2px, radio 4px, relleno 12px 14px, texto 1.125rem; placeholder #5b6470. Etiqueta encima en 600, 1rem, tinta.
- **Focus:** contorno de 3px cabina clara a 2px de distancia y borde azul cabina.
- **Error:** borde de 3px rojo esmalte y mensaje 600 rojo esmalte debajo, enlazado por `aria-describedby`. Foco y error son dos estados distintos y no comparten color.

### Navigation
Cabecera cabina profunda con logo circular sobre hueso, enlaces de sección en Barlow 600 de 1.125rem hueso al 90% (al pasar, hueso pleno y subrayado), oculta por debajo de 768px; a la derecha, el botón de contorno Ingresar. Bajo la cabecera y sobre el pie corre una cinta reflectiva de 12px.

### Cinta reflectiva
Franjas diagonales a -45° rojo reflectivo y blanco de 22px (12px en promociones). Cortada: la misma cinta enmascarada en tramos de 64px con huecos de 20px. Tachada: acero oscuro con raya de tinta de 3px al centro (definida, aún sin uso en la página). Al cargar, un destello de faros cruza la cinta una sola vez (1.1s); se omite con reducción de movimiento.

### Contador de surtidor
Cada cifra del precio es una tira vertical que rueda hasta su valor (1.25s cubic-bezier(0.16, 1, 0.3, 1)); con reducción de movimiento queda quieta en su valor.

### Franjas de titular
Filete pintado de 18px bajo los títulos de sección sobre cabina: hueso, rojo y filete de cabina de 4px cada uno, hasta 20rem de ancho.

### Panel de operación

Construido en `tailwind/panel.css` (compilado a `css/panel.css`, prefijo `tw-`, sin reinicio de estilos, por encima de AdminLTE y `css/grifo.css`). Contención Operate: el contenido va sobre blanco hueso y el rótulo pintado se reserva a títulos de pantalla y cifras que mandan.

- **Cabecera:** esmalte cabina profunda con un borde inferior de cinta reflectiva de 6px; usuario con foto y nombre en hueso.
- **Menú lateral:** esmalte cabina; secciones como etiquetas cromo en mayúsculas espaciadas; la pantalla abierta se marca como placa hueso con texto tinta e icono rojo (`.g-actual`, con `aria-current="page"`).
- **Título de pantalla:** Big Shoulders 800 en mayúsculas, 2.25rem (1.85rem en celular), icono en cabina.
- **Tarjetas:** blancas, borde 1px `#d8d1c2`, radio 6px, sin sombra; las tarjetas con color heredado llevan una franja superior de 4px en cabina.
- **Indicadores (`info-box`, `small-box`):** cara blanca, cifra en Big Shoulders 800 a 2.25rem, etiqueta en mayúsculas gris; el color de estado queda solo en el cuadro del icono.
- **Número de paso (`.g-paso`):** placa cabina de 2.25rem con el número pintado en hueso; título del paso en letra de trabajo.
- **Barra de cuadre (`.g-barra-cuadre`):** fija al pie en formularios largos; esmalte cabina profunda con borde superior de cinta. Total en cifra pintada; el estado tiene forma propia: *cuadra* placa hueso con visto, *sobra* verde `#1f6b3a` con signo más, *falta* rojo reflectivo tinta con flecha abajo, *vacío* texto cromo. La acción principal de la barra es una placa hueso.
- **Tarjeta de surtidor en celular (`.tabla-surtidores`):** cuadrícula de seis columnas: código pintado y producto arriba, lecturas inicial y final lado a lado, galones, precio y total en una fila.
- **Botones:** primario y guardar en cabina; peligro en reflectivo tinta; aviso en amarillo de seguridad con texto tinta; radio 5px.
- **Gráficos:** un color fijo por combustible (Diesel cabina, Regular reflectivo, Premium ámbar `#c99400`) y la letra de trabajo.
- **Verde de estado (`correcto`, `#1f6b3a`):** existe solo en el panel, para estados correctos (sobrante, activo); la página pública no lo usa.
## Do's and Don'ts

### Do:
- **Do** dar profundidad con planchas superpuestas y bordes de material (acero, caucho, tinta de 2-3px).
- **Do** usar la placa roja para la acción principal de cada bloque y la placa hueso para la secundaria, siempre sobre su plancha de acero fija.
- **Do** señalar estado con la forma de la cinta: continua vigente, cortada pendiente o por terminar, tachada inactiva.
- **Do** escribir titulares, cifras y placas en Big Shoulders en mayúsculas, y todo texto de lectura en Barlow Semi Condensed de al menos 1.0625rem.
- **Do** usar cifras tabulares en todo número operativo.
- **Do** respetar la reducción de movimiento: destello, contador y placas quedan quietos.
- **Do** llevar al panel de operación la paleta, las familias y los materiales con contención Operate, sin trasladar la escala de la página pública.

### Don't:
- **Don't** usar sombras con desenfoque ni brillos difusos en ningún elemento.
- **Don't** usar el amarillo de seguridad fuera del sello BAJÓ.
- **Don't** usar la cinta reflectiva como adorno sin significado de división o estado.
- **Don't** pintar placas en cromo brillante ni mover la plancha de montaje al pasar: solo se mueve la cara.
- **Don't** confundir foco y error en un campo: foco es contorno cabina clara, error es borde rojo esmalte de 3px.
- **Don't** colgar pestañas laterales para pasos numerados; los pasos van en la ficha técnica pintada.
- **Don't** documentar componentes del panel antes de que existan en el código.
