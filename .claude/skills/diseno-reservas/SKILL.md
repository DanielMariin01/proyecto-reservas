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

### Combinaciones de color de bloque (contraste AA verificado)

Cada bloque de página pública con texto libre (portada, texto, texto con imagen) usa una de estas 3 combinaciones — nunca un color suelto. El super admin la elige al armar el bloque; el dueño nunca la cambia.

| Combinación | Fondo | Texto principal | Contraste |
| --- | --- | --- | --- |
| `clara` | `--blanco` | `--tinta` | 10,4:1 |
| `oscura` | `--tinta` | `--blanco` | 10,4:1 |
| `marca` | `--primario` (o el de la paleta de marca del negocio) | `--blanco` | 5,0:1 (paletas de marca: mínimo 4,5:1, validado al guardarlas) |

Dentro de un bloque con combinación `oscura` o `marca`, el texto secundario (subtítulos, descripciones) nunca usa `--acento`, `--secundario` ni `--texto-suave` — ninguno cumple AA sobre fondo oscuro. Usa en su lugar:

| Uso | Color sobre fondo oscuro | Contraste |
| --- | --- | --- |
| Acento/subtítulo sobre `--tinta` o `--primario` | `--celeste` | 8,85:1 sobre `--tinta` |
| Texto secundario/descripción sobre `--tinta` | `--gris-claro` | 8,63:1 sobre `--tinta`, 5,15:1 sobre `--primario` |

`--acento` solo se usa como texto sobre fondo **claro** y en 18 px o más (ver tabla de arriba). Para resaltar una frase dentro de un párrafo de tamaño normal (< 18 px), usa `--primario` (5,0:1, cumple a cualquier tamaño), nunca `--acento`.

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

Cada bloque es un componente registrado por tipo. Reglas comunes: ancho máximo 1200 px centrado, relleno vertical 64 px (40 px en celular), título de sección en 28 px peso 700 `--tinta`.

| Bloque | Diseño |
| --- | --- |
| `portada` | Combinación de color (por defecto `oscura`). Dos columnas en escritorio (texto a la izquierda, imagen a la derecha dentro de una forma orgánica recortada con `clip-path`; una columna apilada en celular, imagen debajo). Título en 2 líneas, mayúsculas, muy grande (48-60 px) y negrita; subtítulo corto en `--celeste`; descripción corta (máx. 450 px de ancho) en `--gris-claro`; `Boton principal` "Reservar". |
| `equipo` | Cuadrícula de `Tarjeta` con foto cuadrada, nombre, especialidad y botón "Reservar con {nombre}". |
| `servicios` | Tarjetas por categoría: foto cuadrada, nombre, "$20.000 · 45 min" y botón "Agendar". Sin buscador si hay menos de 10 servicios. |
| `cursos` | Tarjeta por fecha: nombre, fecha y hora, "Quedan 3 cupos" en `--secundario`, precio, botón "Inscribirme"; agotado en gris. |
| `productos` | Cuadrícula de tarjetas con foto, nombre, precio y botón "Agregar". |
| `texto` | Combinación de color (por defecto `clara`). Título y párrafo de máximo 70 caracteres por línea. |
| `texto_con_imagen` | Combinación de color (por defecto `clara`). Título centrado en `--acento` (28-32 px); párrafo a la izquierda con una frase clave resaltada en `--primario`; imagen rectangular a la derecha con `--sombra-tarjeta` y `--radio-tarjeta`. En celular, una columna con la imagen arriba o abajo del texto. |
| `galeria` | Cuadrícula de fotos cuadradas con vista ampliada al tocar. |
| `ubicacion` | Mapa, dirección, horario de atención por día y botón "Cómo llegar". |
| `whatsapp` | Botón flotante redondo abajo a la derecha, visible en toda la página. |

Los colores de marca del negocio (su paleta de marca, sección "Librería de paletas de marca") sobrescriben `--primario` y `--secundario` solo dentro de su página pública (incluido el flujo de reserva); el resto de tokens se mantiene.

### Librería de paletas de marca

El negocio no define un color libre: el super admin le asigna una paleta con nombre de una lista reutilizable (tabla `paletas_marca`), pensada para crecer sin tocar código. Cada paleta tiene `color_primario` y `color_secundario`; `color_primario` debe cumplir AA con texto blanco (mínimo 4,5:1) para poder guardarse — lo valida `App\Services\CalculadoraContraste`. Paletas iniciales: "Color Barbería" (`#1B263B` / `#415A77`, 15,1:1) y "Color Uñas" (`#C2185B` / `#F06292`, 5,9:1).

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
