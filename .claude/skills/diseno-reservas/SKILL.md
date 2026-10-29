---
name: diseno-reservas
description: Reglas de diseño visual e interfaz del sistema de reservas para barberías, spas y salones. Úsala SIEMPRE antes de crear o modificar cualquier pantalla, componente de React, vista de Filament o plantilla de correo del proyecto.
---

# Diseño del sistema de reservas

Esta skill define cómo se ve y cómo se comporta toda la interfaz. La **estructura y la experiencia** se toman de las pantallas de reserva y pago de Hostinger Ecommerce; los **colores, la fuente, las sombras y los bordes** salen de la guía propia de este proyecto. Nunca se copian el logo, los íconos propios ni el código de Hostinger.

Antes de escribir una pantalla:
1. Lee esta skill completa.
2. Identifica qué patrón de los de abajo aplica (ventana de reserva, datos y confirmación, bloque de página, panel).
3. Construye solo con los tokens y componentes base definidos aquí.
4. Define los estados de carga, vacío y error de la pantalla antes de darla por terminada.

## 1. Tokens de diseño (únicos permitidos)

```css
:root {
  /* Colores */
  --primario: #673DE6;
  --secundario: #7454E9;
  --acento: #587EAC;
  --celeste: #E1EEFF;
  --gris-claro: #EAEAEA;
  --texto: #898989;
  --texto-suave: #7A7A7A;
  --tinta: #3E4043;
  --blanco: #FFFFFF;
  --fondo-panel: #F6F8FC;

  /* Tipografía */
  --fuente-base: 'Asap', sans-serif;

  /* Sombras */
  --sombra-tarjeta: 0 4px 24px rgba(91, 107, 177, 0.12);

  /* Bordes */
  --radio-tarjeta: 16px;
  --radio-boton: 8px;
}
```

Reglas:
- Prohibido escribir colores hexadecimales, sombras o radios sueltos en los componentes. Todo sale de estos tokens (en Tailwind: `bg-primario`, `text-tinta`, `rounded-tarjeta`, `rounded-boton`, `shadow-tarjeta`).
- Fuente Asap desde Google Fonts, pesos 400, 500, 600 y 700.
- Espaciados en múltiplos de 4 px (4, 8, 12, 16, 24, 32, 48).

### Contraste (obligatorio, nivel AA)

| Color sobre blanco | Contraste | Uso permitido |
| --- | --- | --- |
| `--tinta` | 10,4:1 | Todo el texto de lectura y títulos |
| `--primario` | 5,0:1 | Enlaces, botones (texto blanco encima también cumple) |
| `--acento` | 4,2:1 | Íconos y bordes; texto solo en 18 px o más |
| `--texto-suave` | 4,3:1 | Texto secundario de 18 px o más, o información no indispensable |
| `--texto` | 3,5:1 | Solo días deshabilitados, marcadores de posición y ayudas grandes |
| Blanco sobre `--secundario` | 3,2:1 | Solo texto de 18 px o más en negrita; si no, texto `--tinta` |

### Color libre de bloque (única excepción a "nunca colores sueltos")

Los bloques de la plantilla de landing (sección 6.1: `barra_promocion`, `portada`, `texto`, `texto_con_imagen`, `banner_oferta`, `testimonios`) no usan los tokens de color — cada uno tiene 2 campos de color libre (`colorFondo`/`colorTexto`, un `ColorPicker` en el Builder) para que el super admin arme la paleta exacta de cada sección. Esta es la **única** excepción permitida a la regla de "prohibido escribir colores sueltos" de arriba; el resto del sistema (botones, tarjetas, paneles, componentes base) sigue solo con tokens.

Sobre esos 2 campos, el contraste AA **es una advertencia, no un bloqueo**: el Builder calcula el contraste entre `colorFondo` y `colorTexto` (con `App\Services\CalculadoraContraste`, el mismo servicio de las paletas de marca) y muestra "⚠ Este texto puede ser difícil de leer con este fondo" si da menos de 4,5:1 — pero el super admin puede guardar igual. Esto es distinto de:
- La tabla de contraste de arriba (tokens del sistema): sigue siendo obligatoria, sin excepción.
- Las paletas de marca (sección 6.1, "Librería de paletas de marca"): su `color_primario` **sigue bloqueando** el guardado si no llega a 4,5:1 con texto blanco.

El texto manuscrito/decorativo superpuesto sobre fotos (`textoManuscrito`) **no usa una fuente nueva**: es Asap itálica (el mismo `--fuente-base` de todo el proyecto), solo con un tratamiento visual distinto (itálica, rotación leve, chip de fondo).

## 2. Componentes base

Todas las pantallas se arman con estos componentes. Si falta uno, se crea aquí primero.

| Componente | Reglas |
| --- | --- |
| `Boton` | Variantes `principal` (fondo `--primario`, texto blanco), `contorno` (borde `--gris-claro`, texto `--primario`), `texto`. Radio 8 px, alto 44 px, peso 600. Estado deshabilitado con opacidad 0,45. Hover del principal en `--acento`. |
| `Tarjeta` | Fondo blanco, radio 16 px, `--sombra-tarjeta`, relleno 24 px (16 px en celular). |
| `CampoTexto` | Alto 48 px, borde `--gris-claro`, radio 8 px, etiqueta flotante o arriba, foco con borde `--primario`. Error en rojo accesible debajo del campo. |
| `CampoTelefono` | Selector de país con bandera y +57 por defecto, más el número. |
| `Insignia` | Estados: confirmada (`--secundario`), pendiente (`--acento`), cancelada (gris), no asistió (gris oscuro). |
| `SelectorDeHora` | Botón de ancho completo, alto 44 px, borde `--gris-claro`, radio 8 px, hora en negrita centrada. Seleccionado: fondo `--primario`, texto blanco. |
| `CalendarioMes` | Ver sección 3. |
| `LineaDetalle` | Ícono de Lucide de 18 px en `--texto-suave` + texto, para duración, fecha, empleado, dirección, servicio y precio. |

Íconos: solo **Lucide** (`lucide-react`), trazo 1,75, mismo tamaño en cada contexto. `clock` para duración/hora, `calendar` para fecha, `user` para empleado, `building-2` para dirección, `scissors` para el servicio, `banknote` para el precio, `x` para cerrar.

## 3. Patrón: ventana de reserva

Basado en la ventana de citas de Hostinger Ecommerce.

```
┌──────────────────────────┬──────────────────────────────────────────────┐
│ Corte + Barba            │ Selecciona fecha y hora                 (x)  │
│ ⏱ 60 min                 │ ┌──────────────────────────┐  Elige un horario│
│ 👤 Japo                  │ │  ‹     oct 2026      ›   │  disponible      │
│ 🏢 Calle 8 #5-18,        │ │ lun mar mié jue vie sáb  │  (GMT-5) Bogotá  │
│    El Porvenir, Alcalá   │ │  ̶2̶6̶  (27)  28  29  30 31  │  [  10:00 a. m. ]│
│ $24.000                  │ └──────────────────────────┘  [  11:00 a. m. ]│
│                          │                               [  2:00 p. m.  ]│
│                          │                  [ Cancelar ] [ Continuar ]  │
└──────────────────────────┴──────────────────────────────────────────────┘
```

- **Panel izquierdo** (fondo blanco, 35 % del ancho): nombre del servicio en 20 px peso 600 `--tinta`; debajo, `LineaDetalle` de duración, empleado, dirección y precio.
- **Panel derecho** (fondo `--fondo-panel`): título "Selecciona fecha y hora" en 22 px peso 600; calendario y columna de horas lado a lado.
- **Calendario** dentro de una `Tarjeta`: mes y año centrados con flechas a los lados; días de la semana abreviados en minúscula (lun., mar., mié.…); días pasados o sin horas en `--texto` y tachados, sin clic; día elegido en círculo de 40 px con borde 2 px `--primario` y fondo `--celeste`; punto de 4 px bajo el día de hoy. Al cambiar de mes se consulta `/disponibilidad?mes=`.
- **Columna de horas**: título "Elige un horario disponible" en 15 px peso 600, zona horaria debajo en 12 px `--texto-suave`, lista de `SelectorDeHora` con horas en formato 12 h con a. m. / p. m. Si el día no tiene horas: mensaje "No hay horas este día" y botón "Ver próximo día disponible".
- **Pie** alineado a la derecha: `Boton contorno` "Cancelar" y `Boton principal` "Continuar", deshabilitado hasta elegir hora.
- **Celular (< 768 px)**: pantalla completa; resumen del servicio arriba en una franja; calendario debajo; horas en cuadrícula de 3 columnas; botón "Continuar" fijo abajo.
- **Cerrar**: botón circular oscuro con ícono `x` arriba a la derecha, y tecla Esc.

## 4. Patrón: datos y confirmación

Basado en el checkout de Hostinger Ecommerce, corregido.

- **Dos columnas**: formulario a la izquierda (55 %), resumen a la derecha (45 %, fondo `--fondo-panel`, borde izquierdo `--gris-claro`). Logo del negocio centrado arriba. Enlace "← Volver" arriba del formulario.
- **Formulario** (en este orden):
  1. "Contacto": correo.
  2. "Tus datos": nombre completo y `CampoTelefono`. Si el celular ya existe, precargar el nombre.
  3. "Nota para el profesional" (opcional).
  4. "Pago": `Tarjeta` con el método del negocio, por ejemplo "Pagas en el local – efectivo, Nequi o transferencia", y sus instrucciones en `--texto-suave`.
  5. Casilla "Autorizo el tratamiento de mis datos personales" con enlace a la política (obligatoria, Ley 1581 de 2012).
  6. `Boton principal` de ancho completo "Confirmar reserva".
- **Resumen**: foto del servicio de 64 px con radio 8 px y burbuja de cantidad; nombre y precio; `LineaDetalle` de fecha y hora ("mar 27 oct, 10:00 a. m. – 11:00 a. m."), empleado y dirección; separador; subtotal; total en 18 px peso 700.
- **Nunca** pedir país, código postal ni usar la palabra "Facturación".
- **Precios** siempre como `$24.000` (pesos enteros, punto de miles, sin decimales ni "CO$").
- **Celular**: resumen arriba, plegable ("Ver resumen · $24.000").

## 5. Pantalla de éxito

- Ícono de verificación en círculo `--secundario`, título "¡Tu cita quedó reservada!".
- `Tarjeta` con el resumen completo.
- Botones: "Agregar a mi calendario" (descarga .ics), "Escribir por WhatsApp" (enlace wa.me con mensaje prellenado), "Cómo llegar" (Google Maps).
- Texto: "Te enviamos la confirmación a tu correo. Desde ahí puedes cancelar o cambiar la hora."

## 6. Bloques de la página pública

Cada bloque es un componente registrado por tipo. El super admin arma la página agregando, quitando y reordenando bloques de este catálogo en el Builder — **ningún bloque es obligatorio para todos los negocios**: si un negocio no vende productos, no agrega ese bloque; si otro sí, lo agrega. Reglas comunes: ancho máximo 1200 px centrado, relleno vertical 64 px (40 px en celular), título de sección en 28 px peso 700 (o el `colorTexto` libre del bloque, ver más abajo).

| Bloque | Diseño |
| --- | --- |
| `barra_promocion` | Franja angosta, color libre. Texto corto + botón de texto opcional + botón de cerrar (x). |
| `encabezado` | Logo a la izquierda, enlaces de navegación al centro, ícono de búsqueda (decorativo) + botón "Reservar" a la derecha. Fondo fijo blanco. |
| `portada` | Color libre (ver sección 6.1). Dos columnas: texto a la izquierda, foto rectangular a la derecha con texto decorativo opcional. Título en 3 líneas, mayúsculas, muy grande y negrita; descripción corta; 2 botones; fila de hasta 4 íconos con etiqueta. |
| `equipo` | Cuadrícula de `Tarjeta` con foto cuadrada, nombre, especialidad y botón "Reservar con {nombre}". |
| `servicios` | Tarjetas con ícono de tijeras, foto cuadrada, nombre, descripción, "$20.000 · 45 min" y botón "Agendar". |
| `cursos` | Tarjeta por fecha: nombre, fecha y hora, "Quedan 3 cupos" en `--secundario`, precio, botón "Inscribirme"; agotado en gris. |
| `productos` | Cuadrícula de tarjetas con foto, nombre, precio y botón "Agregar". |
| `texto` | Color libre. Título y párrafo de máximo 70 caracteres por línea. |
| `texto_con_imagen` | Color libre. Modo simple (párrafo con una frase resaltada) o modo "motivos" (etiqueta + título + grid 2×2 de ícono/título/descripción) si se agrega al menos un motivo — mismo bloque, dos formas de verse. Foto con texto decorativo opcional del otro lado. |
| `galeria` | Cuadrícula de fotos cuadradas con vista ampliada al tocar. |
| `banner_oferta` | Color libre. Etiqueta + título + descripción + botón de un lado; foto con texto decorativo opcional del otro. |
| `testimonios` | Color libre. Tarjetas con cita, estrellas (1-5), foto y nombre — contenido manual (ver sección 6.1). |
| `ubicacion` | Mapa, dirección, horario de atención por día y botón "Cómo llegar". |
| `whatsapp` | Botón flotante redondo abajo a la derecha, visible en toda la página. |
| `pie_de_pagina` | Fondo fijo `--tinta`/texto blanco. Nombre del negocio, descripción corta, dirección/WhatsApp, enlaces y año de copyright. |

Los colores de marca del negocio (su paleta de marca, ver más abajo) sobrescriben `--primario` y `--secundario` solo dentro de su página pública (incluido el flujo de reserva); el resto de tokens se mantiene.

### 6.1 Plantilla de landing — detalle por bloque

Igual de detallado que la ventana de reserva (sección 3), porque esta plantilla reemplaza la portada orgánica de una iteración anterior y suma varios bloques de una sola vez.

**`barra_promocion`**
- Franja de ancho completo, alto mínimo (padding vertical 8 px), texto centrado 14 px.
- **Celular**: el texto, el botón y la "x" se envuelven (`flex-wrap`) en vez de recortarse.

**`encabezado`**
- Logo (o nombre del negocio si no hay logo) a la izquierda; enlaces de navegación al centro; ícono de búsqueda (decorativo, sin buscador funcional en este alcance) + `Boton principal` "Reservar" a la derecha.
- No es `sticky`: el super admin lo puede reordenar como cualquier bloque, así que fijarlo rompería si no queda de primero.
- **Celular**: los enlaces de navegación se **ocultan por completo** (sin menú hamburguesa en este alcance) — quedan logo, ícono de búsqueda y botón "Reservar".

**`portada`**
- Escritorio: 2 columnas. Izquierda: etiqueta pequeña en mayúsculas espaciadas (opacidad 80 %), título en 3 líneas mayúsculas muy grande (48-60 px) y negrita, descripción (máx. 450 px de ancho, opacidad 80 %), 2 botones (`Boton principal` "Reservar cita" + `Boton contorno` con ícono `play` "Ver video", opcional), fila de hasta 4 íconos con etiqueta corta debajo. Derecha: foto rectangular `--radio-tarjeta`, con texto decorativo opcional superpuesto en una esquina (Asap itálica, ver sección 1).
- **Celular**: una sola columna; el texto va primero, la foto después (más pequeña, relación de aspecto 4:3 igual que en escritorio); la fila de íconos pasa de 4 a 2 columnas; los 2 botones se envuelven en vez de desbordar.

**`servicios`** (ajuste visual sobre el bloque ya existente)
- Ícono fijo de tijeras + foto + nombre + descripción + precio/duración + botón "Agendar", en `Tarjeta`.
- **Celular**: cuadrícula de 1 columna (ya lo hacía).

**`texto_con_imagen`**
- Modo simple: párrafo con una frase resaltada en `--primario`.
- Modo "motivos" (si el bloque tiene al menos un motivo): etiqueta + título centrado arriba del texto, grid 2×2 de motivos (ícono 24 px + título corto + descripción corta) en vez del párrafo.
- Foto con texto decorativo opcional, del lado contrario al texto.
- **Celular**: una columna, foto primero; el grid de motivos pasa de 2 columnas a 1.

**`galeria`** (sin cambios de diseño en esta plantilla)
- Cuadrícula de fotos cuadradas, 2 columnas en celular, 4 en escritorio, con vista ampliada al tocar.

**`banner_oferta`**
- Mismo patrón que `portada` sin la fila de íconos ni el botón de video: etiqueta + título + descripción + botón de un lado, foto con texto decorativo del otro.
- **Celular**: una columna, texto primero, foto después.

**`testimonios`**
- Cuadrícula de `Tarjeta` (1 columna en celular, 2 en tablet, 3 en escritorio): estrellas (las de `estrellas` en `--secundario`, el resto en `--gris-claro`), cita entre comillas, foto circular + nombre.
- **Alcance**: contenido 100 % manual (cita, nombre, foto y estrellas elegidos a mano por el super admin) — no es un sistema de reseñas ni se relaciona con citas reales. El dato `fuente` (hoy siempre `'manual'`, `App\Enums\FuenteTestimonios`) existe para que, cuando se construya el sistema real de reseñas (tarea aparte), se pueda sumar una fuente que resuelva este mismo arreglo de testimonios desde otra parte, sin cambiar la forma visual del bloque ni el componente de React.

**`pie_de_pagina`**
- Nombre del negocio + descripción corta + dirección/WhatsApp de un lado, enlaces del otro, línea de copyright abajo.
- **Celular**: las 2 columnas se apilan (nombre/dirección primero, enlaces después).

### Lista fija de íconos (`IconoBloque`)

Los campos "ícono" de `portada` y `texto_con_imagen` (modo motivos) eligen de una lista fija, nunca texto libre: escudo, reloj, estrella, premio, corazón, usuarios, destellos, ubicación, billete, pulgar arriba, teléfono, tijeras. El Select del Builder (`App\Enums\IconoBloque`) y el mapa a Lucide en React (`iconoBloque.tsx`) comparten los mismos valores.

### Librería de paletas de marca

El negocio no define un color libre para su identidad de marca: el super admin le asigna una paleta con nombre de una lista reutilizable (tabla `paletas_marca`), pensada para crecer sin tocar código. Cada paleta tiene `color_primario` y `color_secundario`; `color_primario` debe cumplir AA con texto blanco (mínimo 4,5:1) para poder guardarse — lo valida `App\Services\CalculadoraContraste`, y a diferencia del color libre de bloque (sección 1), aquí **sí bloquea**. Paletas iniciales: "Color Barbería" (`#1B263B` / `#415A77`, 15,1:1) y "Color Uñas" (`#C2185B` / `#F06292`, 5,9:1).

## 7. Paneles (Filament)

- Tema personalizado: `--primario` como color primario, `--secundario` como éxito, fuente Asap, radios y sombras de la guía.
- Agenda por columnas de empleado con el color de cada empleado en el borde izquierdo de la cita.
- Vista "Hoy" del empleado pensada para celular: tarjetas grandes con hora, cliente, servicio, `Insignia` de estado y acciones visibles sin menús ocultos.

## 8. Estados obligatorios en cada pantalla

- **Cargando**: esqueletos con la forma del contenido (no solo un spinner).
- **Vacío**: mensaje que explica por qué y qué hacer ("Aún no tienes citas hoy").
- **Error**: mensaje en lenguaje simple con código de referencia ("Algo falló. Código REF-8F3K") y botón para reintentar.
- **Negocio suspendido**: pantalla "Esta página no está disponible por el momento", sin horas ni botones de reserva.

## 9. Lenguaje

- Español de Colombia, tuteo.
- Fechas "mar 27 oct", horas "10:00 a. m.", dinero "$24.000".
- Botones con verbos claros: "Reservar", "Continuar", "Confirmar reserva", "Inscribirme".
- Nombres de componentes, props y variables en español (ver sección 12 del documento de requerimientos).

## 10. Lista de revisión antes de entregar una pantalla

- [ ] Solo usa tokens y componentes base.
- [ ] Cumple contraste AA según la tabla.
- [ ] Funciona a 360 px de ancho, con botones de al menos 44 × 44 px.
- [ ] Tiene estados de carga, vacío y error.
- [ ] Precios, fechas y horas en formato colombiano.
- [ ] Navegable con teclado y con etiquetas en todos los campos.
- [ ] No pide datos innecesarios (país, código postal).
