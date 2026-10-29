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

Todos los bloques de contenido (sección 6.1: `barra_promocion`, `texto`, `texto_con_imagen`, `banner_oferta`, `testimonios`, `tarjetas`, `equipo`, `servicios`, `cursos`, `productos`, `galeria`, `ubicacion`, `whatsapp`) no usan los tokens de color en su fondo/texto — cada uno tiene 2 campos de color libre (`colorFondo`/`colorTexto`, un `ColorPicker` en el Builder, reutilizando el mismo helper `camposColorLibre()`) para que el super admin arme la paleta exacta de cada sección. Esta es la **única** excepción permitida a la regla de "prohibido escribir colores sueltos" de arriba; el resto del sistema (botones, tarjetas, paneles, componentes base) sigue solo con tokens — incluidas las tarjetas blancas dentro de estos bloques (`equipo`, `servicios`, `testimonios`, `tarjetas`), que mantienen su texto `--tinta` fijo sin importar el color del fondo de la sección.

`encabezado`, `pie_de_pagina` y `portada` quedan fuera de este sistema (fondo fijo o capa de legibilidad, ver abajo) — no son "contenido de marca" sino chrome del sitio o una foto de fondo.

Sobre `colorFondo`/`colorTexto`, el contraste AA **es una advertencia, no un bloqueo**: el Builder calcula el contraste entre ambos (con `App\Services\CalculadoraContraste`, el mismo servicio de las paletas de marca) y muestra "⚠ Este texto puede ser difícil de leer con este fondo" si da menos de 4,5:1 — pero el super admin puede guardar igual. Esto es distinto de:
- La tabla de contraste de arriba (tokens del sistema): sigue siendo obligatoria, sin excepción.
- Las paletas de marca (sección 6.1, "Librería de paletas de marca"): su `color_primario` **sigue bloqueando** el guardado si no llega a 4,5:1 con texto blanco.

El texto manuscrito/decorativo superpuesto sobre fotos (`textoManuscrito`) **no usa una fuente nueva**: es Asap itálica (el mismo `--fuente-base` de todo el proyecto), solo con un tratamiento visual distinto (itálica, rotación leve, chip de fondo).

### Capa de legibilidad de `portada` (no es color libre)

`portada` usa una foto de fondo a pantalla completa con un **degradado** encima (no una capa pareja en todo el ancho) para que el texto se lea donde está, sin tapar la foto donde no hay texto — es legibilidad, no decoración, por eso es un `Select` de 2 opciones fijas (`App\Enums\CapaPortada`), no un `ColorPicker`:

| Opción | Degradado (izquierda → derecha) | Texto | Uso |
| --- | --- | --- | --- |
| `oscura` (por defecto) | Negro ~35 % de opacidad del 0 % al 40 % del ancho, se desvanece a transparente hacia el 75 % | Blanco | Fotos claras |
| `clara` | Blanco ~45 % de opacidad del 0 % al 40 % del ancho, se desvanece a transparente hacia el 75 % | `--tinta` | Fotos oscuras |

Las opacidades pico (35 %/45 %) son las mismas que ya se verificaron con fotos reales del proyecto (contraste de 5,6 a 6,3:1 con el texto de cada opción) — el degradado solo acota esa opacidad al 0-40 % del ancho, donde vive el texto (anclado abajo a la izquierda), en vez de cubrir el 100 % de la foto. No lleva el cálculo estricto de AA de las paletas de marca (una capa sobre una foto variable no se puede verificar en el momento de guardar), es "razonablemente legible a simple vista". Si una foto puntual queda difícil de leer, el super admin cambia a la otra opción.

## 2. Componentes base

Todas las pantallas se arman con estos componentes. Si falta uno, se crea aquí primero.

| Componente | Reglas |
| --- | --- |
| `Boton` | Variantes `principal` (fondo `--primario`, texto blanco), `contorno` (borde `--gris-claro`, texto `--primario`), `texto`. Tamaño `normal` (alto 44 px, por defecto) o `grande` (alto 56 px, texto 18 px — para llamados a la acción destacados, ej. `banner_oferta`). Radio 8 px, peso 600. Estado deshabilitado con opacidad 0,45. Hover del principal en `--acento`. |
| `Tarjeta` | Fondo blanco, radio 16 px, `--sombra-tarjeta`, relleno 24 px (16 px en celular). |
| `CampoTexto` | Alto 48 px, borde `--gris-claro`, radio 8 px, etiqueta flotante o arriba, foco con borde `--primario`. Error en rojo accesible debajo del campo. |
| `CampoTelefono` | Selector de país con bandera y +57 por defecto, más el número. |
| `Insignia` | Estados: confirmada (`--secundario`), pendiente (`--acento`), cancelada (gris), no asistió (gris oscuro). |
| `SelectorDeHora` | Botón de ancho completo, alto 44 px, borde `--gris-claro`, radio 8 px, hora en negrita centrada. Seleccionado: fondo `--primario`, texto blanco. |
| `CalendarioMes` | Ver sección 3. |
| `LineaDetalle` | Ícono de Lucide de 18 px en `--texto-suave` + texto, para duración, fecha, empleado, dirección, servicio y precio. |

Íconos: solo **Lucide** (`lucide-react`), trazo 1,75, mismo tamaño en cada contexto. `clock` para duración/hora, `calendar` para fecha, `user` para empleado, `building-2` para dirección, `scissors` para el servicio, `banknote` para el precio, `x` para cerrar.

**Única excepción, íconos de marca**: Lucide no tiene logos de marca (ni WhatsApp, ni Facebook, ni Instagram), solo íconos genéricos de interfaz. El botón flotante de WhatsApp (`whatsapp`) y los enlaces de redes sociales del pie de página (`pie_de_pagina`) usan el glifo oficial de cada marca como SVG local (`IconoWhatsapp`, `IconoFacebook`, `IconoInstagram` en `resources/js/componentes`, mismo `path` que el paquete `simple-icons`, sin agregar esa dependencia completa por 3 íconos) — son canales de contacto/redes reconocibles, no íconos de interfaz. No es precedente para agregar otros íconos de marca sueltos sin necesidad.

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
| `encabezado` | Color libre. Logo a la izquierda, enlaces de navegación al centro, ícono `log-in` a la derecha (enlaza a `/ingresar`, sin verificar sesión). |
| `portada` | Foto de fondo a pantalla completa con capa de legibilidad (ver sección 1). Insignia con el tipo de negocio + título muy grande arriba a la izquierda; fila de ubicación/horario; descripción; 2 botones; fila de hasta 4 íconos con etiqueta; texto decorativo opcional. |
| `equipo` | Color libre. Cuadrícula de `Tarjeta` con foto cuadrada, nombre, especialidad y botón "Reservar con {nombre}". |
| `servicios` | Color libre. Tarjetas con ícono de tijeras, foto cuadrada, nombre, descripción, "$20.000 · 45 min" y botón "Agendar". |
| `cursos` | Color libre. Tarjeta por fecha: nombre, fecha y hora, "Quedan 3 cupos" en `--secundario`, precio, botón "Inscribirme"; agotado en gris. |
| `productos` | Color libre. Cuadrícula de tarjetas con foto, nombre, precio y botón "Agregar". |
| `texto` | Color libre. Título y párrafo de máximo 70 caracteres por línea. |
| `texto_con_imagen` | Color libre. Modo simple (párrafo con una frase resaltada) o modo "motivos" (etiqueta + título + grid 2×2 de ícono/título/descripción) si se agrega al menos un motivo — mismo bloque, dos formas de verse. Foto con texto decorativo opcional del otro lado. |
| `galeria` | Color libre. Cuadrícula de fotos cuadradas con vista ampliada al tocar. |
| `tarjetas` | Color libre. Tarjetas repetidas genéricas (imagen + título opcional + descripción), 1 a 3 columnas elegidas por el super admin — contenido manual, sin atarse a ningún modelo. |
| `banner_oferta` | Color libre. Etiqueta + título + descripción + botón de un lado; foto con texto decorativo opcional del otro. |
| `testimonios` | Color libre. Tarjetas con cita, estrellas (1-5), foto y nombre — contenido manual (ver sección 6.1). |
| `ubicacion` | Color libre. Mapa, dirección, horario de atención por día y botón "Cómo llegar". |
| `whatsapp` | Color libre (fondo/ícono del botón). Botón flotante redondo abajo a la derecha, visible en toda la página. |
| `pie_de_pagina` | Fondo fijo `--tinta`/texto blanco. 3 columnas (nombre+dirección, "Info" con dirección/teléfono, redes sociales) y año de copyright centrado debajo. |

Los colores de marca del negocio (su paleta de marca, ver más abajo) sobrescriben `--primario` y `--secundario` solo dentro de su página pública (incluido el flujo de reserva); el resto de tokens se mantiene.

### 6.1 Plantilla de landing — detalle por bloque

Igual de detallado que la ventana de reserva (sección 3), porque esta plantilla reemplaza la portada orgánica de una iteración anterior y suma varios bloques de una sola vez.

**`barra_promocion`**
- Franja de ancho completo, alto mínimo (padding vertical 8 px), texto centrado 14 px.
- **Celular**: el texto, el botón y la "x" se envuelven (`flex-wrap`) en vez de recortarse.

**`encabezado`**
- Color libre. Logo (o nombre del negocio si no hay logo) a la izquierda; enlaces de navegación al centro; ícono `user-circle` a la derecha, enlaza directo a `/ingresar` (sin verificar si ya hay sesión activa — simple a propósito, no es el flujo de autenticación completo). Sin botón "Reservar" ni ícono de búsqueda.
- No es `sticky`: el super admin lo puede reordenar como cualquier bloque, así que fijarlo rompería si no queda de primero.
- **Celular**: los enlaces de navegación se **ocultan por completo** (sin menú hamburguesa en este alcance) — quedan logo e ícono de iniciar sesión.

**`portada`**
- Foto de fondo con `object-fit: cover` (nunca se deforma) dentro de un contenedor de **`aspect-ratio` fijo** por tamaño de pantalla — `4/5` en celular, `16/9` desde tablet, `21/9` desde escritorio grande —, con un piso de altura mínima (520/420/380 px) para que nunca quede demasiado bajo. Así el recorte de la foto es consistente entre pantallas, no depende del alto variable del viewport como antes. Degradado de legibilidad de la sección 1 encima. Contenido anclado **abajo** a la izquierda (no arriba), en una sola columna sobre la foto (no 2 columnas):
  1. Insignia en píldora semitransparente con el tipo de negocio (`Negocio.tipo`, nunca un campo escrito a mano — así siempre coincide con el negocio real), 14 px.
  2. Título mayúsculas muy grande (56-72 px) y negrita — el tamaño más grande del sistema.
  3. Fila de ubicación (ícono `map-pin` 20 px + `direccion`, ya existente) y horario (ícono `clock` 20 px + `horarioHoy`, texto libre que escribe el super admin — **no se calcula solo todavía**, no hay un endpoint de horario de atención del negocio hoy; construirlo es una tarea aparte), 16-18 px.
  4. Descripción (máx. 500 px de ancho, opacidad 90 %, 16-18 px).
  5. 2 botones (`Boton principal` "Reservar cita" + `Boton contorno` con ícono `play` "Ver video", opcional).
  6. Fila de hasta 4 íconos de 36 px con etiqueta de 14 px debajo.
- Texto decorativo opcional (`textoManuscrito`) como insignia flotante en la esquina superior derecha de la foto, no en el flujo del contenido (que ahora está abajo).
- **Sin calificación de estrellas/reseñas**: el sistema real de reseñas no existe todavía (tarea aparte) — no se muestra nada ni se inventa un campo manual para esto aquí; cuando ese sistema exista, se agrega.
- **Celular**: los tamaños de título y relleno se reducen para que el texto nunca quede recortado contra la foto; la fila de íconos pasa de 4 a 2 columnas; los 2 botones y la fila de ubicación/horario se envuelven en vez de desbordar.

**`servicios`** (ajuste visual sobre el bloque ya existente, más color libre)
- Ícono fijo de tijeras + foto + nombre + descripción + precio/duración + botón "Agendar", en `Tarjeta`.
- **Celular**: cuadrícula de 1 columna (ya lo hacía).

**`equipo`, `cursos`, `productos`, `ubicacion`, `whatsapp`** (sin cambios de diseño en esta plantilla, solo color libre)
- Mismo diseño que ya tenían (ver tabla de arriba); el color libre no cambia su comportamiento responsive.

**`texto`**
- Color libre. Título y párrafo **centrados**, mismo contenedor (`max-w-3xl` centrado), espaciado consistente (margen superior en el párrafo, no margen inferior solo en el título) para que ambos compartan la misma alineación en cualquier tamaño de pantalla.

**`texto_con_imagen`**
- Modo simple: párrafo con una frase resaltada en `--primario`.
- Modo "motivos" (si el bloque tiene al menos un motivo): etiqueta + título centrado arriba del texto, grid 2×2 de motivos (ícono 24 px + título corto + descripción corta) en vez del párrafo.
- Foto con texto decorativo opcional, del lado contrario al texto.
- **Celular**: una columna, foto primero; el grid de motivos pasa de 2 columnas a 1.

**`galeria`** (sin cambios de diseño en esta plantilla, solo color libre)
- Cuadrícula de fotos cuadradas, 2 columnas en celular, 4 en escritorio, con vista ampliada al tocar.

**`tarjetas`**
- Genérico y manual (servicios destacados, pasos de un proceso, lo que el negocio necesite) — no se ata a ningún modelo de datos. Cada tarjeta: imagen arriba (relación 4:3, solo `--radio-tarjeta` en la imagen) + título opcional + descripción corta, **centrados** debajo de la imagen — **sin** el contenedor `Tarjeta` (sin fondo blanco, sombra ni radio en un recuadro propio): la imagen y el texto quedan directo sobre el fondo de la sección.
- El super admin elige 1, 2 o 3 columnas para escritorio y agrega/quita tarjetas libremente (máximo 9).
- **Celular**: siempre 1 columna, sin importar cuántas se eligieron para escritorio; el centrado se mantiene igual.

**`banner_oferta`**
- Estilo "tienda online": etiqueta con ícono de descuento (`badge-percent`, 22 px) en píldora, título, descripción y un `Boton` **tamaño `grande`** con ícono `arrow-right`, de un lado; foto más pequeña (un tercio del ancho en escritorio) con texto decorativo del otro.
- Elementos decorativos: 2 círculos translúcidos superpuestos (`bg-blanco/10`) y 2 destellos (`sparkles` de Lucide) con opacidad baja — todo con los tokens de opacidad ya permitidos, nunca un color nuevo. Es la composición (píldoras/destellos superpuestos), no los colores exactos de ninguna referencia visual.
- Fondo en degradado calculado a partir del mismo `colorFondo` que ya elige el super admin (oscurecido ~15%, ver sección 1) — nunca un color nuevo.
- Sección muy delgada a propósito (relleno vertical 16-24 px, bastante menos que el resto de bloques de la plantilla) para que se sienta como una franja promocional, no como otra sección completa.
- **Celular**: una columna, texto primero, foto después.

**`pie_de_pagina`**
- Fondo fijo `--tinta`/texto blanco (no es color libre, sigue siendo "chrome" del sitio). 3 columnas centradas:
  1. Nombre del negocio + dirección.
  2. "Info": dirección (con ícono `map-pin`) + teléfono (con ícono `phone`, usa el WhatsApp del negocio, que ya es su número de contacto).
  3. "Síguenos": ícono de Facebook y/o Instagram (excepción de marca, ver sección 2), cada uno enlazando al link que configuró el super admin — si no configuró el link de una red, no se muestra su ícono; si no configuró ninguna, la columna completa no aparece.
- Línea de copyright centrada debajo de las 3 columnas, con el año actual.
- **Celular**: las 3 columnas se apilan verticalmente, todo centrado igual que en escritorio.

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
- **Builder de bloques de página** (`NegocioForm`): usa el soporte nativo de Filament 5 para no obligar a hacer scroll por una lista larga — `->collapsible()->collapsed()` (cada bloque empieza contraído, con botón individual y "colapsar/expandir todo"), `->blockNumbers(false)` (sin número, solo label) y `Block::make(...)->icon(...)` con un ícono Heroicon por tipo de bloque, para reconocerlo sin expandirlo. Nunca construir esto a mano si Filament ya lo trae.

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
