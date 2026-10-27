# Requerimientos – Sistema de Reservas para Barberías y Spas

Oct 27, 2026 · @Daniel

## 1. Visión y alcance

Un sistema SaaS multi-negocio de reservas, cursos y tienda para barberías, spas de uñas y salones en Colombia, a un precio de $30.000–$40.000 COP al mes por negocio.

**Problema.** Las opciones actuales fallan en algo clave: Hostinger Ecommerce no permite usuarios por profesional ni compartir solo la tienda; AgendaPro cuesta $29.900 (1 profesional) a $99.000 al mes y no da una página web propia con la marca del negocio. Los negocios pequeños necesitan ambas cosas a un precio local.

**Objetivos del producto**

- Que el cliente final reserve en menos de 1 minuto desde el celular, eligiendo servicio, profesional, fecha y hora.
- Que cada profesional maneje su propio horario y vea solo sus citas, con su propio usuario.
- Que el dueño vea todo el negocio: citas, ventas, cursos, clientes y reportes.
- Que cada negocio tenga su página pública con su marca, sin que el desarrollador tenga que dar soporte diario.
- Servir como proyecto de portafolio: arquitectura limpia, pruebas, documentación y despliegue real.

**Stack definido:** Laravel (backend y API), Filament PHP (paneles de administración), React (página pública y flujo de reserva), MySQL, colas y tareas programadas de Laravel.

**Alcance por fases**

| Fase | Incluye | No incluye aún |
| --- | --- | --- |
| MVP | Negocios, profesionales, servicios, horarios, bloqueos, reservas, página pública, correos, panel dueño y profesional | Pagos en línea, WhatsApp, tienda |
| Fase 2 | Cursos con cupos, Google Calendar por profesional, recordatorios, reportes | Tienda |
| Fase 3 | Tienda de productos con inventario, pagos en línea (Wompi/Mercado Pago), WhatsApp | — |
| Fase 4 | Suscripciones del SaaS, facturación a negocios, marketplace | — |

**Primeros clientes:** Japo Barber (barbería, varios barberos, venta de productos) y un spa de uñas (manicura, pedicura y cursos con cupos).

## 2. Referentes y lecciones aprendidas

El sistema toma el flujo de reserva de Hostinger (que funciona bien para el cliente final) y la gestión por profesional de AgendaPro, y corrige los problemas encontrados en el proyecto Japo.

| Aspecto | Hostinger Ecommerce (citas) | AgendaPro | Nuestro sistema |
| --- | --- | --- | --- |
| Precio | \~$26.900/mes año 1, $39.900 después; o incluido en Business | $29.900 (1 prof.) a $510.000/mes | $30.000–$40.000/mes por negocio |
| Usuarios por profesional | No: un solo usuario de toda la cuenta | Sí, con niveles de acceso y permisos | Sí: dueño, profesional, recepción |
| Horario | Global o personalizado por servicio | Por profesional | Por profesional, con excepciones por fecha |
| Confirmación de cita | Llega a Google Calendar solo si se marca como pagada | Automática | Automática, configurable |
| Página web propia | Sí, con el Creador (hosting aparte) | Sitio de reservas; URL y colores propios solo desde Premium | Página pública con marca en todos los planes |
| Clases y cursos | No: una persona por turno; se simula con inventario | Sí, clases ilimitadas | Cursos con cupos nativos |
| Recordatorios | Correo | Automáticos; WhatsApp 50 gratis y $10.000 por cada 50 más | Correo en MVP; WhatsApp en fase 3 |
| Tienda | Sí, con inventario | Inventario desde Básico | Fase 3 |
| Formato de precios COP | CO$20000.00, requiere código extra | Correcto | $20.000 nativo |

**Lo que se copia de Hostinger (flujo del cliente final)**

- Ventana de reserva: servicio a la izquierda (nombre, duración, ubicación), calendario y horas disponibles a la derecha.
- Franjas generadas por la duración del servicio, o flexibles cada 15/30 minutos.
- Tiempo de espera previo y posterior (margen entre citas).
- Tiempo mínimo de notificación (aviso mínimo) y ventana máxima de reserva (por ejemplo, 30 días).
- Horario semanal con varios bloques por día y fechas bloqueadas.
- Enlaces para que el cliente cancele o reprograme desde el correo de confirmación.

**Lo que se copia de AgendaPro (gestión del negocio)**

- Agenda por profesional y vista de ocupación.
- CRM: ficha de cada cliente con su historial de citas.
- Recordatorios automáticos, notificaciones y alertas diarias.
- Sistema de caja, comisiones por profesional e inventario (fases posteriores).

**Problemas del proyecto Japo que este sistema debe resolver**

1. No se podía dar acceso a un solo negocio sin exponer los demás sitios del plan.
2. Los barberos no podían entrar a ver ni cambiar su horario.
3. Las citas con pago en el local no llegaban al calendario hasta marcarlas como pagadas.
4. El buscador de la tienda devolvía 0 resultados.
5. Los precios se mostraban como CO$20000.00 y el formulario de pago exigía código postal.
6. La disponibilidad personalizada por servicio anulaba el horario general sin avisar.
7. El aviso mínimo de 4 horas escondía las horas del día sin explicación para el dueño.

Fuentes: [AgendaPro Colombia – Planes](https://agendapro.com/co/planes) · [Hostinger – How to accept appointments](https://www.hostinger.com/support/6538407-hostinger-ecommerce-how-to-accept-appointments/)

## 3. Actores, roles y permisos

Cada usuario pertenece a uno o varios negocios y solo ve los datos del negocio que tiene seleccionado; nadie ve datos de otro negocio.

| Rol | Quién es | Entra por | Puede | No puede |
| --- | --- | --- | --- | --- |
| Super administrador | Tú, dueño del SaaS | Panel Filament `/admin` | Crear y suspender negocios, ver uso, gestionar planes del SaaS, entrar como soporte con registro de auditoría | Ver contraseñas; cambiar datos sin dejar registro |
| Dueño del negocio | Japo, la dueña del spa | Panel Filament `/app/{negocio}` | Todo su negocio: profesionales, servicios, horarios, citas, cursos, productos, clientes, reportes, configuración y página pública | Ver otros negocios |
| Recepción / administrador | Empleado de confianza | Panel `/app/{negocio}` | Crear, mover y cancelar citas de cualquier profesional; registrar pagos; gestionar clientes e inscripciones | Cambiar precios, servicios, profesionales y configuración |
| Profesional | Barbero, manicurista, instructora | Panel `/app/{negocio}` (vista reducida, ideal en celular) | Ver y gestionar solo sus citas; editar su horario y bloquear sus días; marcar asistencia y completado | Ver citas o ingresos de otros profesionales; cambiar precios |
| Cliente final | Quien reserva un servicio | Página pública (React) | Reservar sin crear cuenta; cancelar o reprogramar desde el enlace del correo; ver sus próximas citas | Reservar fuera de las reglas del negocio |
| Alumna | Quien se inscribe a un curso | Página pública | Inscribirse a una fecha de curso con cupo; recibir confirmación e instrucciones de pago | Inscribirse a un curso lleno |

**Reglas de permisos**

- Un profesional puede tener también rol de dueño (caso de Japo, que es dueño y barbero).
- Los permisos se definen por rol y por negocio (spatie/laravel-permission con equipos = negocio).
- Toda acción sensible (cancelar, cambiar precio, borrar, entrar como soporte) queda en el registro de auditoría con usuario, fecha y valores antes/después.
- El dueño puede invitar usuarios por correo; el invitado crea su contraseña y queda ligado solo a ese negocio.

## 4. Requerimientos funcionales

Cada requerimiento tiene un código (RF-módulo-número) y una fase, para que los agentes los tomen como tareas independientes.

### 4.1 Negocios (multi-tenant)

- **RF-NEG-01 (MVP)** Crear un negocio con nombre, slug único (`barberjapo`), tipo (barbería, spa, salón), logo, colores, dirección, ciudad, teléfono/WhatsApp, redes sociales y zona horaria (por defecto America/Bogota).
- **RF-NEG-02 (MVP)** Configurar moneda COP sin decimales y separador de miles con punto ($20.000).
- **RF-NEG-03 (MVP)** Definir el horario de atención del local (días y bloques) que sirve de base para los profesionales.
- **RF-NEG-04 (MVP)** Configurar reglas de reserva del negocio: aviso mínimo, ventana máxima, margen entre citas, intervalo de franjas, política de cancelación.
- **RF-NEG-05 (Fase 4)** Dominio propio por negocio (spadeunas.com) apuntando a su página pública.

### 4.2 Profesionales

- **RF-PRO-01 (MVP)** Crear profesionales con foto, nombre, especialidad, biografía corta, certificaciones y color en la agenda.
- **RF-PRO-02 (MVP)** Asignar qué servicios presta cada profesional, con duración o precio propio opcional (un barbero senior puede cobrar más).
- **RF-PRO-03 (MVP)** Vincular un profesional a un usuario para que entre con su propio correo.
- **RF-PRO-04 (MVP)** Activar o desactivar un profesional sin borrar su historial.

### 4.3 Servicios

- **RF-SER-01 (MVP)** Crear servicios con nombre, descripción, foto, categoría, duración (mínimo 5 minutos), precio y estado visible/oculto.
- **RF-SER-02 (MVP)** Ordenar servicios manualmente (arrastrar) y agruparlos por categoría (Cortes, Barba, Manicura, Pedicura, Adicionales).
- **RF-SER-03 (MVP)** Margen propio por servicio (antes y después) que sobrescribe el del negocio.
- **RF-SER-04 (Fase 2)** Servicios combinados (Corte + Barba) cuya duración es la suma o un valor propio.

### 4.4 Disponibilidad

- **RF-DIS-01 (MVP)** Horario semanal por profesional con varios bloques por día (10:00–12:00 y 14:00–18:00).
- **RF-DIS-02 (MVP)** Excepciones por fecha: día bloqueado, horas bloqueadas o horario especial (abrir un domingo).
- **RF-DIS-03 (MVP)** Bloqueos rápidos desde el celular del profesional (“no atiendo hoy de 3 a 5”).
- **RF-DIS-04 (MVP)** Festivos de Colombia precargados, que el dueño decide si trabaja o no.
- **RF-DIS-05 (MVP)** Vista previa: al guardar un horario, mostrar las franjas que verá el cliente para un servicio de ejemplo, para evitar la confusión vivida con Hostinger.

### 4.5 Reservas (citas)

- **RF-RES-01 (MVP)** Reserva pública: servicio → profesional (o “cualquiera disponible”) → fecha → hora → datos (nombre, celular, correo, nota) → confirmación.
- **RF-RES-02 (MVP)** Reserva sin cuenta; el cliente se identifica por celular y correo.
- **RF-RES-03 (MVP)** Estados: pendiente, confirmada, completada, cancelada, no asistió, reprogramada.
- **RF-RES-04 (MVP)** Confirmación automática por defecto; opción del negocio para exigir confirmación manual.
- **RF-RES-05 (MVP)** Cancelar y reprogramar desde un enlace firmado del correo, respetando la política de cancelación.
- **RF-RES-06 (MVP)** Crear citas desde el panel (cliente que llama o llega sin cita), incluso fuera del horario público si el usuario tiene permiso.
- **RF-RES-07 (MVP)** Agenda en el panel por día, semana y profesional, con arrastrar para mover citas.
- **RF-RES-08 (Fase 2)** Lista de espera cuando no hay horas en el día elegido.
- **RF-RES-09 (Fase 3)** Abono o pago completo en línea al reservar.

### 4.6 Cursos y clases

- **RF-CUR-01 (Fase 2)** Crear cursos (Acrílicas básico) con descripción, qué incluye, qué traer, precio e instructora.
- **RF-CUR-02 (Fase 2)** Crear sesiones del curso con fecha, hora de inicio y fin, y cupo máximo; un curso puede tener varias sesiones o varias fechas.
- **RF-CUR-03 (Fase 2)** Inscripción pública que descuenta cupo; al llenarse, la fecha aparece agotada.
- **RF-CUR-04 (Fase 2)** Lista de inscritas por fecha con estado de pago y asistencia, exportable a CSV.
- **RF-CUR-05 (Fase 2)** La sesión de curso bloquea la agenda de la instructora en ese horario.

### 4.7 Tienda

- **RF-TIE-01 (Fase 3)** Productos con fotos, precio, variantes (50 g / 100 g) e inventario con alerta de stock bajo.
- **RF-TIE-02 (Fase 3)** Carrito y pedido con “recoger en el local” sin pedir dirección ni código postal; domicilio opcional por barrio.
- **RF-TIE-03 (Fase 3)** Buscador local que filtra por nombre y categoría, sin tildes ni mayúsculas.

### 4.8 Pagos y caja

- **RF-PAG-01 (MVP)** Registrar pago manual de una cita o pedido: efectivo, Nequi, Daviplata, transferencia o tarjeta en datáfono.
- **RF-PAG-02 (Fase 2)** Caja diaria por negocio y por profesional, con cierre de caja.
- **RF-PAG-03 (Fase 2)** Comisiones por profesional (porcentaje por servicio).
- **RF-PAG-04 (Fase 3)** Pasarela en línea (Wompi o Mercado Pago) para abonos, cursos y tienda.

### 4.9 Notificaciones

- **RF-NOT-01 (MVP)** Correo al cliente: confirmación, cambio, cancelación y recordatorio 24 h antes.
- **RF-NOT-02 (MVP)** Correo o notificación al profesional y al dueño por cada cita nueva, cambiada o cancelada.
- **RF-NOT-03 (MVP)** Resumen diario por correo al profesional con sus citas del día.
- **RF-NOT-04 (MVP)** Botón “escribir por WhatsApp” con mensaje prellenado (enlace wa.me, sin costo).
- **RF-NOT-05 (Fase 3)** Recordatorios automáticos por WhatsApp Business API.

### 4.10 Integraciones

- **RF-INT-01 (Fase 2)** Google Calendar por profesional: cada cita confirmada crea, actualiza o borra el evento en el calendario de ese profesional.
- **RF-INT-02 (Fase 2)** Los eventos ocupados del Google Calendar del profesional bloquean su disponibilidad (sincronización en dos vías, opcional).
- **RF-INT-03 (Fase 2)** Archivo .ics adjunto al correo del cliente para agregar la cita a su calendario.

### 4.11 Página pública

- **RF-WEB-01 (MVP)** Página por negocio en `/{slug}` con secciones: inicio, equipo, servicios, cursos, galería, tienda, ubicación con mapa, horario y redes.
- **RF-WEB-02 (MVP)** El dueño activa, ordena y edita los textos de cada sección desde el panel, sin código.
- **RF-WEB-03 (MVP)** Botón “Reservar con {profesional}” que abre el flujo con ese profesional ya elegido.
- **RF-WEB-04 (MVP)** SEO básico: título, descripción, imagen para compartir y datos estructurados de negocio local.
- **RF-WEB-05 (Fase 2)** Galería de trabajos subida por los profesionales.

### 4.12 Clientes (CRM)

- **RF-CLI-01 (MVP)** Ficha del cliente con datos, historial de citas, gasto total, inasistencias y notas.
- **RF-CLI-02 (MVP)** Detección de cliente existente por celular al reservar.
- **RF-CLI-03 (Fase 2)** Etiquetas y lista de clientes inactivos para recontactar.

### 4.13 Reportes

- **RF-REP-01 (MVP)** Panel del día: citas de hoy, próximas, ingresos registrados y huecos libres.
- **RF-REP-02 (Fase 2)** Ingresos por periodo, por profesional y por servicio; ocupación; tasa de cancelación y de inasistencia.
- **RF-REP-03 (Fase 2)** Exportar citas, inscripciones y clientes a CSV/Excel.

## 5. Reglas del motor de reservas

Una hora se ofrece al cliente solo si el servicio completo, con sus márgenes, cabe dentro de un bloque libre del profesional y respeta el aviso mínimo y la ventana máxima.

**Parámetros y valores por defecto**

| Parámetro | Nivel | Por defecto | Ejemplo |
| --- | --- | --- | --- |
| Zona horaria | Negocio | America/Bogota | Todas las horas se guardan en UTC y se muestran en la del negocio |
| Intervalo de franjas | Negocio o servicio | Igual a la duración del servicio | Flexible cada 15 min: 10:00, 10:15, 10:30… |
| Aviso mínimo | Negocio o servicio | 1 hora | A las 10:00 se puede reservar desde las 11:00 |
| Ventana máxima | Negocio | 30 días | No se puede reservar a 45 días |
| Margen antes / después | Servicio | 0 / 0 min | 5 min después para limpiar |
| Cancelación por el cliente | Negocio | Hasta 2 horas antes | Después, solo por WhatsApp |
| Reprogramación por el cliente | Negocio | Hasta 2 horas antes, máximo 2 veces | — |
| Citas simultáneas por profesional | Profesional | 1 | — |

**Algoritmo de disponibilidad (por profesional y día)**

1. Tomar los bloques del horario semanal del profesional para ese día de la semana.
2. Aplicar excepciones por fecha: día bloqueado, horas bloqueadas u horario especial; luego festivos si el negocio no trabaja.
3. Restar las citas existentes (pendientes, confirmadas y reprogramadas), cada una ampliada con sus márgenes, y las sesiones de curso que dicta.
4. Restar eventos ocupados de Google Calendar si la sincronización en dos vías está activa (fase 2).
5. Generar franjas desde el inicio de cada bloque libre, avanzando por el intervalo configurado.
6. Descartar la franja si inicio + margen previo + duración + margen posterior supera el fin del bloque.
7. Descartar franjas anteriores a ahora + aviso mínimo, y días posteriores a hoy + ventana máxima.
8. Con “cualquier profesional”, unir las franjas de todos los profesionales que prestan el servicio y asignar al confirmar el que tenga menos citas ese día.

**Ejemplo verificado con el caso Japo:** bloques 10:00–12:00 y 14:00–18:00, Corte de 45 min, intervalo = duración: franjas 10:00, 10:45, 14:00, 14:45, 15:30, 16:15 y 17:00. A las 11:00 no aparece porque no cae en el paso de 45 min; con intervalo flexible de 15 min sí aparece.

**Reglas de integridad**

- Bloqueo optimista al confirmar: si dos clientes eligen la misma franja, solo la primera transacción se guarda y la segunda ve “esta hora ya fue tomada” con alternativas cercanas.
- Restricción en base de datos que impide dos citas activas superpuestas del mismo profesional.
- Cambiar el horario de un profesional no borra citas ya agendadas; el panel avisa cuáles quedaron fuera del nuevo horario.
- Una cita cancelada o archivada libera de inmediato la franja; una inscripción cancelada devuelve el cupo.
- Precios en pesos enteros (sin centavos), mostrados como $20.000.
- El panel explica por qué una hora no aparece (“fuera del aviso mínimo de 1 h”, “no cabe antes de las 12:00”), para que el dueño no adivine.

## 6. Flujos de trabajo

Cinco flujos cubren el día a día: alta del negocio, reserva del cliente, día del profesional, inscripción a curso y cierre del día.

&#91;embedded content: estados de una cita · 7 estados\]

Una cita reprogramada vuelve a quedar confirmada; solo completada, cancelada y no asistió son estados finales.

**F1. Alta de un negocio nuevo (super admin + dueño)**

1. El super admin crea el negocio con nombre, slug y correo del dueño.
2. El dueño recibe la invitación, crea su contraseña y entra al asistente de configuración.
3. Asistente en 5 pasos: datos y marca → horario del local → servicios → profesionales (con invitación por correo) → reglas de reserva.
4. Vista previa de la página pública y botón “Publicar”.
5. El sistema muestra el enlace público y un código QR para el mostrador e Instagram.

**F2. Reserva del cliente final (celular)**

1. Entra a la página desde Instagram, WhatsApp o Google Maps.
2. Toca “Reservar” o “Reservar con Japo”.
3. Elige servicio (ve precio y duración) y profesional, o “cualquiera”.
4. Elige día en un calendario donde los días sin horas aparecen deshabilitados, y luego la hora.
5. Escribe nombre, celular y correo; si el celular ya existe, se precargan sus datos.
6. Revisa el resumen (servicio, profesional, fecha, hora, precio, dirección, “pagas en el local”) y confirma.
7. Ve la pantalla de éxito con “Agregar a mi calendario” y “Escribir por WhatsApp”; recibe el correo con enlaces para cancelar o reprogramar.

**F3. El día del profesional**

1. A las 7:00 a. m. recibe el resumen de sus citas del día.
2. Abre su agenda en el celular y ve solo sus citas, con nombre, servicio y notas del cliente.
3. Cuando llega el cliente, marca “en atención”; al terminar, “completada” y registra el pago (método y valor).
4. Si el cliente no llega 15 minutos después, marca “no asistió”, lo que suma al historial del cliente.
5. Si necesita salir, bloquea un rango de horas y el sistema le avisa si hay citas afectadas para reprogramarlas.

**F4. Inscripción a un curso**

1. La alumna ve los cursos con fechas, cupos disponibles y precio.
2. Elige una fecha con cupo y deja nombre, celular y correo.
3. Recibe la confirmación con las instrucciones de pago (Nequi o transferencia) y lo que debe traer.
4. La dueña ve la inscripción como “pendiente de pago”; al recibir el comprobante la marca “pagada”.
5. Si no paga en el plazo configurado (por ejemplo, 48 horas), el sistema libera el cupo y le avisa.
6. El día del curso, la instructora usa la lista de inscritas para marcar asistencia.

**F5. Cierre del día (dueño)**

1. Revisa el panel: citas completadas, inasistencias, ingresos por método de pago y por profesional.
2. Corrige pagos mal registrados y cierra la caja del día.
3. Revisa la agenda de mañana y los huecos libres para promocionar en estados de WhatsApp.

## 7. Casos de uso

Los casos marcados MVP son los primeros que deben pasar sus criterios de aceptación; cada criterio se convierte en una prueba automatizada (Pest).

| Código | Caso de uso | Actor | Fase |
| --- | --- | --- | --- |
| CU-01 | Reservar una cita | Cliente final | MVP |
| CU-02 | Cancelar o reprogramar desde el correo | Cliente final | MVP |
| CU-03 | Configurar mi horario y bloquear horas | Profesional | MVP |
| CU-04 | Ver y gestionar mis citas del día | Profesional | MVP |
| CU-05 | Crear una cita desde el panel | Recepción / dueño | MVP |
| CU-06 | Gestionar servicios y precios | Dueño | MVP |
| CU-07 | Invitar y administrar profesionales | Dueño | MVP |
| CU-08 | Registrar pago de una cita | Profesional / recepción | MVP |
| CU-09 | Personalizar y publicar la página | Dueño | MVP |
| CU-10 | Crear un negocio nuevo | Super admin | MVP |
| CU-11 | Inscribirse a un curso | Alumna | Fase 2 |
| CU-12 | Sincronizar con Google Calendar | Profesional | Fase 2 |
| CU-13 | Ver reportes de ingresos | Dueño | Fase 2 |
| CU-14 | Comprar un producto | Cliente final | Fase 3 |

### CU-01 Reservar una cita

- **Precondiciones:** el negocio está publicado y tiene al menos un servicio visible y un profesional activo.
- **Flujo principal:** F2 (sección 6).
- **Alternos:** sin horas en el día → sugerir el próximo día con disponibilidad; la franja se ocupa mientras confirma → mensaje y tres horas cercanas; cliente con 3 inasistencias → se acepta pero se marca para el dueño (configurable).
- **Criterios de aceptación:**
  - Nunca se ofrece una hora que no cumpla las reglas de la sección 5.
  - Dos reservas simultáneas a la misma franja dejan exactamente una cita.
  - El cliente recibe el correo de confirmación en menos de 1 minuto.
  - Todo el flujo funciona en una pantalla de 360 px de ancho.

### CU-02 Cancelar o reprogramar desde el correo

- **Precondiciones:** la cita está pendiente o confirmada y el enlace firmado no ha vencido.
- **Flujo principal:** abre el enlace → ve el resumen → elige cancelar (con motivo opcional) o una nueva fecha y hora → confirma.
- **Alternos:** fuera del plazo de la política → se muestra el botón de WhatsApp del negocio en lugar de cancelar.
- **Criterios:** la franja anterior queda libre de inmediato; el profesional y el dueño reciben el aviso; el enlace no sirve para otra cita.

### CU-03 Configurar mi horario y bloquear horas

- **Precondiciones:** el profesional tiene usuario activo en el negocio.
- **Flujo principal:** abre “Mi horario” → marca días y bloques → guarda → ve la vista previa de franjas → para un día puntual usa “Bloquear” con rango y motivo.
- **Alternos:** el bloqueo choca con citas → lista de citas afectadas con opción de reprogramar o mantener.
- **Criterios:** solo puede editar su propio horario; el cambio se refleja en la página pública sin recargar caché manualmente.

### CU-04 Ver y gestionar mis citas del día

- **Flujo principal:** abre “Hoy” → ve sus citas ordenadas → cambia estado (en atención, completada, no asistió).
- **Criterios:** no ve citas de otros profesionales; los cambios de estado quedan en el historial del cliente.

### CU-05 Crear una cita desde el panel

- **Flujo principal:** elige profesional, servicio, fecha y hora en la agenda → busca o crea el cliente por celular → guarda.
- **Alternos:** hora fuera del horario o ocupada → advertencia; con permiso “forzar”, se guarda igual y queda registrado.
- **Criterios:** el cliente recibe el correo solo si tiene correo registrado.

### CU-08 Registrar pago de una cita

- **Flujo principal:** en la cita completada → “Registrar pago” → método, valor (precargado con el precio) y propina opcional → guardar.
- **Criterios:** el pago aparece en la caja del día y en el reporte del profesional; un pago se puede anular con motivo, nunca borrar.

### CU-11 Inscribirse a un curso

- **Precondiciones:** la sesión del curso tiene cupos y no ha empezado.
- **Flujo principal:** F4 (sección 6).
- **Alternos:** cupo lleno mientras llena el formulario → mensaje y otras fechas del mismo curso; inscripción sin pagar al vencer el plazo → se libera el cupo.
- **Criterios:** el cupo nunca queda negativo; la lista de inscritas se exporta a CSV con nombre, celular y estado de pago.

Los casos CU-06, 07, 09, 10, 12, 13 y 14 siguen el mismo formato y se detallan al iniciar su fase.

## 8. Usabilidad y experiencia

El cliente final reserva con el pulgar en un celular de gama media; el profesional gestiona su día desde el celular; solo el dueño usa el computador con frecuencia.

**Principios**

- **Móvil primero:** diseñar a 360 px y ampliar; botones de al menos 44 × 44 px; acción principal fija abajo en el flujo de reserva.
- **Pocos pasos:** reservar en 4 pantallas o menos y en menos de 60 segundos; sin crear cuenta.
- **Sin sorpresas:** precio, duración, dirección y forma de pago visibles antes de confirmar.
- **Explicar lo que falta:** días sin horas deshabilitados con el motivo al tocarlos; nunca un calendario vacío sin explicación.
- **Lenguaje local:** español de Colombia, tuteo, formato $20.000, fechas “mar 27 oct”, hora en formato 12 h con a. m. / p. m.
- **Marca del negocio:** logo, colores y fotos del negocio en la página pública y en los correos.
- **Rendimiento:** página pública con carga menor a 2,5 s en 4G (LCP) e imágenes optimizadas.
- **Accesibilidad:** contraste AA, navegación con teclado, etiquetas en formularios, mensajes de error junto al campo.

**Pantallas clave**

| Pantalla | Usuario | Debe mostrar |
| --- | --- | --- |
| Página pública | Cliente | Hero con botón Reservar, equipo, servicios con precio y duración, cursos, galería, ubicación, WhatsApp flotante |
| Elegir servicio | Cliente | Lista por categorías, precio, duración, foto opcional |
| Elegir profesional | Cliente | Foto, nombre, especialidad, opción “cualquiera” |
| Fecha y hora | Cliente | Calendario mensual + horas como botones; resumen del servicio a la izquierda en escritorio, arriba en celular |
| Datos y confirmación | Cliente | Nombre, celular, correo, nota, aceptación de tratamiento de datos, resumen y botón Confirmar |
| Éxito | Cliente | Resumen, agregar al calendario, WhatsApp, cómo llegar |
| Hoy | Profesional | Citas del día en tarjetas, estado, botón para cambiar estado y registrar pago |
| Mi horario | Profesional | Semana con bloques editables, bloqueos, vista previa de franjas |
| Agenda | Dueño / recepción | Vista día/semana por columnas de profesional, arrastrar para mover, crear cita con un clic |
| Tablero | Dueño | Citas de hoy, ingresos del día y del mes, ocupación, próximas citas |
| Configuración | Dueño | Asistente inicial y reglas de reserva con ayuda en lenguaje simple |

**Mensajes de ayuda obligatorios (aprendidos con Hostinger)**

- Junto a “Aviso mínimo”: “Con 1 hora, si el cliente entra a las 10:00 a. m., la primera hora que ve es 11:00 a. m.”
- Junto a “Intervalo de franjas”: “Con el intervalo igual a la duración, un corte de 45 min muestra 10:00 y 10:45; con 15 min muestra 10:00, 10:15, 10:30…”
- Al activar un horario propio en un servicio: “Este servicio dejará de usar el horario general.”

## 9. Requerimientos no funcionales

La regla más importante es el aislamiento: ningún usuario, consulta o archivo de un negocio puede llegar a otro.

| Código | Tema | Requerimiento |
| --- | --- | --- |
| RNF-01 | Multi-tenant | Una base de datos con columna `business_id` en cada tabla del negocio y scope global obligatorio; pruebas que verifiquen que un usuario no puede leer ni modificar datos de otro negocio |
| RNF-02 | Autenticación | Correo y contraseña con verificación de correo; recuperación de contraseña; 2FA opcional para dueño y super admin |
| RNF-03 | Autorización | Policies de Laravel por modelo y permisos por rol y negocio |
| RNF-04 | Seguridad web | HTTPS, protección CSRF, límite de intentos en login y en el endpoint de reserva, enlaces firmados con vencimiento para cancelar o reprogramar, captcha invisible en reservas públicas |
| RNF-05 | Datos personales | Cumplir la Ley 1581 de 2012 (Habeas Data): aviso de privacidad, casilla de autorización de tratamiento en cada formulario público, opción de exportar y eliminar datos de un cliente |
| RNF-06 | Auditoría | Registro de cambios en citas, pagos, precios y permisos (quién, cuándo, antes y después) |
| RNF-07 | Rendimiento | Cálculo de disponibilidad de un día en menos de 300 ms para 10 profesionales; caché por profesional y día que se invalida al cambiar horario o citas |
| RNF-08 | Fechas | Guardar en UTC, mostrar en la zona del negocio; pruebas con cambio de mes y año |
| RNF-09 | Tareas en segundo plano | Correos, recordatorios y sincronización con Google por colas; tareas programadas para recordatorios y liberación de cupos vencidos |
| RNF-10 | Disponibilidad | Copias de seguridad diarias de base de datos y archivos, con prueba de restauración mensual |
| RNF-11 | Calidad | Pruebas con Pest: unitarias del motor de disponibilidad y de integración para cada caso de uso MVP; análisis estático (Larastan) y formato (Pint) en CI |
| RNF-12 | Despliegue | Entorno local con Docker o Laravel Herd; producción inicial en hosting compartido con PHP 8.3+, cron y SSH, o VPS pequeño; despliegue automático desde GitHub |
| RNF-13 | Observabilidad | Registro de errores (Sentry o Laravel Pulse) y alerta por correo ante fallos de colas |
| RNF-14 | Archivos | Imágenes redimensionadas y convertidas a WebP al subirlas; límite de 5 MB |
| RNF-15 | Documentación | README con instalación, diagrama de arquitectura, decisiones técnicas (ADR) y guía del usuario corta, pensando en el portafolio |

**Arquitectura propuesta**

- **Backend:** Laravel con API REST bajo `/api/public/{slug}` para la página y el flujo de reserva, y servicios de dominio separados (`AvailabilityService`, `BookingService`, `EnrollmentService`).
- **Paneles:** Filament con dos paneles: `admin` (super admin) y `app` (negocio, con tenancy por `Business`).
- **Frontend público:** React con Vite (o Inertia), TypeScript, Tailwind y React Query; renderizado del lado del servidor opcional para SEO de la página pública.
- **Base de datos:** MySQL 8 o PostgreSQL; restricciones de integridad para evitar citas superpuestas.

Las versiones exactas de Laravel, Filament y React se confirman al iniciar el proyecto, usando las estables vigentes.

## 10. Modelo de datos

Veinte tablas cubren todas las fases; las trece marcadas MVP bastan para la primera versión con reservas.

| Entidad | Campos principales | Relaciones | Fase |
| --- | --- | --- | --- |
| `businesses` | name, slug, type, logo, colors, address, city, phone, whatsapp, timezone, settings (JSON: aviso mínimo, ventana, intervalo, política), status | tiene muchos usuarios, profesionales, servicios | MVP |
| `users` | name, email, password, phone, 2fa | pertenece a muchos negocios vía `business_user` (rol) | MVP |
| `professionals` | business\_id, user\_id (nullable), name, photo, specialty, bio, color, active, google\_calendar\_id | tiene muchos horarios, bloqueos, citas | MVP |
| `service_categories` | business\_id, name, sort | tiene muchos servicios | MVP |
| `services` | business\_id, category\_id, name, description, photo, duration\_min, price, buffer\_before, buffer\_after, slot\_interval, visible, sort | muchos a muchos con profesionales (`professional_service`: price y duration opcionales) | MVP |
| `working_hours` | professional\_id, weekday (0–6), start\_time, end\_time | pertenece a profesional | MVP |
| `time_offs` | professional\_id, starts\_at, ends\_at, reason | pertenece a profesional | MVP |
| `customers` | business\_id, name, phone (único por negocio), email, notes, no\_show\_count, data\_consent\_at | tiene muchas citas, inscripciones, pedidos | MVP |
| `appointments` | business\_id, professional\_id, service\_id, customer\_id, starts\_at, ends\_at, status, price, source (web/panel), notes, cancel\_token, google\_event\_id | pertenece a profesional, servicio y cliente; tiene muchos pagos | MVP |
| `payments` | business\_id, payable (cita, inscripción o pedido), method, amount, tip, status, received\_by | polimórfica | MVP |
| `holidays` | date, name, country | global (festivos de Colombia) | MVP |
| `audit_logs` | business\_id, user\_id, action, model, before, after | — | MVP |
| `page_sections` | business\_id, type, title, content (JSON), sort, visible | página pública | MVP |
| `courses` | business\_id, name, description, includes, bring, price, instructor\_id | tiene muchas sesiones | Fase 2 |
| `course_sessions` | course\_id, starts\_at, ends\_at, capacity, enrolled\_count | tiene muchas inscripciones | Fase 2 |
| `enrollments` | session\_id, customer\_id, status, pay\_until, attended | pertenece a sesión y cliente | Fase 2 |
| `products` | business\_id, name, description, photos, price, stock, low\_stock\_threshold | tiene variantes | Fase 3 |
| `product_variants` | product\_id, name, price, stock | — | Fase 3 |
| `orders` / `order_items` | business\_id, customer\_id, status, total, delivery (recoger/domicilio) | tiene ítems y pagos | Fase 3 |
| `cash_closings` | business\_id, date, totals por método, closed\_by | — | Fase 2 |

**Restricciones clave**

- Índice único `(business_id, phone)` en `customers`.
- Sin dos `appointments` activas del mismo profesional que se superpongan (validación en transacción con bloqueo de fila del profesional; restricción de exclusión si se usa PostgreSQL).
- `enrolled_count` nunca mayor que `capacity` (actualización atómica).
- Precios guardados como enteros en pesos.

## 11. Plan de construcción con agentes

El MVP se construye en 8 tareas en orden, cada una pequeña, probada y revisada antes de pasar a la siguiente; ningún agente toca más de una tarea a la vez.

**Tareas del MVP (en orden)**

- [ ] T1. Proyecto base: Laravel, Filament, React con Vite, Pest, Pint, Larastan, CI en GitHub Actions, README.
- [ ] T2. Multi-tenant y roles: `businesses`, `business_user`, roles y permisos, paneles `admin` y `app`, pruebas de aislamiento (RNF-01).
- [ ] T3. Catálogo: categorías, servicios, profesionales y su relación, con recursos de Filament (RF-SER, RF-PRO).
- [ ] T4. Disponibilidad: horarios, bloqueos, festivos y `AvailabilityService` con pruebas de la sección 5, incluido el ejemplo de Japo.
- [ ] T5. Reservas: `BookingService`, estados, concurrencia, agenda en Filament, creación desde panel (CU-01, 04, 05).
- [ ] T6. API pública y flujo de reserva en React (F2), con enlaces firmados para cancelar y reprogramar (CU-02).
- [ ] T7. Notificaciones por correo y colas: confirmación, cambios, recordatorio 24 h, resumen diario (RF-NOT-01 a 03).
- [ ] T8. Página pública editable, pagos manuales, tablero del dueño y despliegue (RF-WEB, CU-08, RF-REP-01).

Después del MVP: Fase 2 (cursos, Google Calendar, reportes, caja), Fase 3 (tienda, pasarela, WhatsApp), Fase 4 (cobro del SaaS, dominios propios).

**Reglas de trabajo con agentes**

- Un archivo `CLAUDE.md` (o `AGENTS.md`) en la raíz con el resumen de este documento, convenciones y comandos.
- Cada tarea empieza con un plan corto que tú apruebas, y termina con pruebas pasando, Pint y Larastan sin errores.
- Un commit por tarea con mensaje descriptivo; una rama y un pull request por tarea, útiles para mostrar en tu hoja de vida.
- Un segundo agente revisa cada pull request contra los criterios de aceptación antes de unirlo.

**Prompt base para los agentes**

```markdown
Eres un desarrollador senior de Laravel, Filament y React. Vas a construir un SaaS multi-negocio de reservas para barberías, spas de uñas y salones en Colombia.

Contexto obligatorio: lee docs/requerimientos.md (este documento) antes de escribir código.

Stack: Laravel (estable vigente), Filament (estable vigente) con dos paneles (admin y app con tenancy por Business), React + TypeScript + Vite + Tailwind para la página pública, MySQL, colas y scheduler de Laravel, Pest para pruebas.

Reglas:
- Todo dato del negocio lleva business_id y scope global; nunca filtres a mano. Escribe pruebas de aislamiento.
- Guarda fechas en UTC y muéstralas en la zona del negocio (America/Bogota por defecto).
- Precios en pesos enteros; formato de salida $20.000.
- La lógica de disponibilidad y reserva vive en servicios de dominio (AvailabilityService, BookingService), no en controladores ni recursos de Filament.
- Sigue exactamente las reglas de la sección 5 del documento; si una regla no está clara, pregunta antes de inventarla.
- Textos de la interfaz en español de Colombia.
- Cada tarea: 1) plan corto, 2) migraciones y modelos, 3) servicios, 4) pruebas Pest con los criterios de aceptación, 5) interfaz, 6) Pint + Larastan + pruebas en verde, 7) resumen de cambios.

Tarea actual: [pega aquí la tarea, por ejemplo T4 con sus RF y criterios].
```

Para cada tarea, reemplaza la última línea con el código de la tarea y copia debajo los requerimientos y criterios que le corresponden.

## 12. Estándares de código

Todo el código se escribe en español, aplica SOLID y patrones de diseño de forma explícita, y debe poder leerlo otro programador sin explicaciones adicionales.

### 12.1 Principios SOLID aplicados

| Principio | Cómo se aplica en este proyecto |
| --- | --- |
| Responsabilidad única (S) | Controladores y recursos de Filament solo reciben y responden; cada caso de uso vive en su propia clase de acción (`CrearReserva`, `CancelarCita`) y cada regla de disponibilidad en su propia clase |
| Abierto/cerrado (O) | Nuevos canales de notificación, métodos de pago o reglas de reserva se agregan creando clases nuevas, sin modificar las existentes |
| Sustitución de Liskov (L) | Toda implementación de una interfaz (`NotificadorInterface`, `PasarelaPagoInterface`) se puede intercambiar sin romper a quien la usa |
| Segregación de interfaces (I) | Interfaces pequeñas y específicas (`EnviaRecordatorios`, `SincronizaCalendario`) en vez de una interfaz grande |
| Inversión de dependencias (D) | Las clases dependen de interfaces registradas en un `ServiceProvider`, nunca de clases concretas como Google o Wompi |

### 12.2 Patrones de diseño obligatorios

| Patrón | Dónde se usa | Ejemplo de nombre |
| --- | --- | --- |
| Capa de servicios | Lógica de dominio de disponibilidad, reservas e inscripciones | `ServicioDisponibilidad`, `ServicioReservas` |
| Acción / Comando | Un caso de uso por clase, con un solo método público `ejecutar()` | `CrearReserva`, `ReprogramarCita`, `InscribirAlumna` |
| DTO (objeto de transferencia) | Datos que entran a una acción, en vez de arreglos sueltos | `DatosReserva`, `DatosInscripcion` |
| Estado | Transiciones de la cita (diagrama de la sección 6); cada estado sabe a cuáles puede pasar | `EstadoPendiente`, `EstadoConfirmada`, `EstadoCompletada` |
| Estrategia | Generación de franjas (por duración o flexible), canales de notificación, métodos de pago | `EstrategiaFranjaPorDuracion`, `EstrategiaFranjaFlexible` |
| Especificación / Cadena de reglas | Reglas que descartan una franja (aviso mínimo, ventana máxima, cabe en el bloque, choque con citas) | `ReglaAvisoMinimo`, `ReglaVentanaMaxima`, `ReglaCabeEnBloque` |
| Observador (eventos) | Efectos secundarios desacoplados: correos, Google Calendar, auditoría | Evento `CitaCreada` → oyentes `EnviarConfirmacion`, `SincronizarConGoogle` |
| Adaptador | Servicios externos detrás de una interfaz propia | `AdaptadorGoogleCalendar`, `AdaptadorWompi` |
| Fábrica | Crear la estrategia o el notificador correcto según la configuración del negocio | `FabricaNotificadores` |
| Repositorio (solo donde aporte) | Consultas complejas reutilizadas, como disponibilidad por rango | `RepositorioCitas` |
| Política | Autorización por modelo y rol | `PoliticaCita`, `PoliticaServicio` |

Un patrón se usa cuando resuelve un problema real del código; si un agente propone uno, debe explicar en el pull request qué problema resuelve.

### 12.3 Nombres en español

- Clases, métodos, funciones, variables, propiedades, constantes, enums, eventos, componentes de React y pruebas se nombran en español.
- Sin tildes ni ñ en los identificadores, para evitar problemas de codificación: `anio`, `numeroTelefono`, `duracionMinutos`.
- Convenciones: clases y componentes en `PascalCase` (`ServicioReservas`, `SelectorDeHora`); métodos y variables en `camelCase` (`obtenerFranjasDisponibles`, `fechaInicio`); columnas y tablas en `snake_case`; constantes en `MAYUSCULAS_CON_GUION`.
- Las tablas del modelo de la sección 10 se crean con su nombre en español: `negocios`, `usuarios`, `profesionales`, `categorias_servicio`, `servicios`, `horarios_trabajo`, `bloqueos`, `clientes`, `citas`, `pagos`, `festivos`, `registros_auditoria`, `secciones_pagina`, `cursos`, `sesiones_curso`, `inscripciones`, `productos`, `variantes_producto`, `pedidos`, `cierres_caja`.
- Se mantienen en inglés solo los nombres que exige el framework: métodos heredados (`handle`, `boot`, `rules`), claves de configuración y palabras reservadas.
- Booleanos con prefijo de pregunta: `estaActivo`, `tieneCupo`, `puedeCancelar`.
- Funciones con verbo que dice lo que hacen: `calcularPrecioTotal`, no `procesar` ni `hacerCosas`.

### 12.4 Reglas de código limpio

- Funciones cortas (idealmente menos de 20 líneas) que hacen una sola cosa.
- Máximo 3 parámetros; si se necesitan más, se usa un DTO.
- Sin números ni textos mágicos: constantes o enums (`EstadoCita::Confirmada`, `MINUTOS_TOLERANCIA_INASISTENCIA = 15`).
- Retorno temprano en vez de `if` anidados.
- `declare(strict_types=1);` y tipos en todos los parámetros y retornos (PHP y TypeScript estricto).
- Sin código duplicado: si algo se repite dos veces, se extrae.
- Comentarios solo para explicar el porqué de una decisión, nunca el qué; el nombre debe explicar el qué.
- Pruebas con nombres que describen el comportamiento: `test('no ofrece una franja que no cabe antes del cierre del bloque')`.
- Cada pull request pasa Pint, Larastan (nivel alto), ESLint y todas las pruebas.

**Ejemplo de estilo esperado**

```php
<?php

declare(strict_types=1);

final class ServicioDisponibilidad
{
    public function __construct(
        private readonly EstrategiaGeneracionFranjas $estrategiaFranjas,
        private readonly CadenaReglasFranja $reglasFranja,
    ) {}

    public function obtenerFranjasDisponibles(
        Profesional $profesional,
        Servicio $servicio,
        CarbonImmutable $fecha,
    ): Collection {
        $bloquesLibres = $profesional->obtenerBloquesLibres($fecha);

        if ($bloquesLibres->isEmpty()) {
            return collect();
        }

        return $this->estrategiaFranjas
            ->generar($bloquesLibres, $servicio)
            ->filter(fn (Franja $franja): bool => $this->reglasFranja->permite($franja, $servicio));
    }
}
```

**Complemento para el prompt base de la sección 11** (se pega al final del prompt):

```markdown
Estándares obligatorios (secciones 12 y 13 del documento):
- Aplica SOLID en todo el código y los patrones de la tabla 12.2; justifica cada patrón en el pull request.
- Todos los nombres (clases, métodos, variables, tablas, componentes, pruebas) en español, sin tildes ni ñ, con las convenciones de 12.3.
- Sigue las reglas de código limpio de 12.4.
- Usa únicamente los tokens de diseño de la sección 13 para colores, tipografía, sombras y bordes; nunca colores sueltos en el código.
```

## 13. Guía de estilo visual

Todo el sistema (página pública, flujo de reserva, paneles de Filament y correos) usa los mismos tokens de diseño, definidos una sola vez y reutilizados en todas partes.

**Tokens de color**

| Token | Valor | Uso recomendado |
| --- | --- | --- |
| `--primario` | #5B6BB1 | Botones principales, enlaces, elementos activos, color primario de Filament |
| `--secundario` | #00A09B | Estados positivos (confirmada, pagada), insignias, detalles |
| `--acento` | #587EAC | Hover de botones, íconos, gráficos secundarios |
| `--celeste` | #E1EEFF | Fondos de secciones, tarjetas destacadas, franja horaria seleccionada |
| `--gris-claro` | #EAEAEA | Bordes, separadores, fondos de campos deshabilitados |
| `--texto` | #898989 | Textos de ayuda y etiquetas secundarias en tamaño grande |
| `--texto-suave` | #7A7A7A | Textos secundarios, fechas, metadatos |
| `--tinta` | #3E4043 | Títulos y texto principal de lectura |

**Tipografía, sombras y bordes**

| Token | Valor |
| --- | --- |
| Fuente | `'Asap', sans-serif` (Google Fonts, pesos 400, 500, 600 y 700) |
| Sombra de tarjetas | `0 4px 24px rgba(91,107,177,0.12)` |
| Radio de tarjetas | 16px |
| Radio de botones | 8px |

**Contraste (para cumplir la accesibilidad AA de la sección 8)**

Según el cálculo de contraste sobre fondo blanco, `--tinta` y `--primario` pasan el mínimo de 4,5:1 para texto normal, pero `--texto` (≈ 3,5:1), `--texto-suave` (≈ 4,3:1) y texto blanco sobre `--secundario` (≈ 3,2:1) no lo alcanzan.

- El texto de lectura usa `--tinta`.
- `--texto` y `--texto-suave` se usan solo en textos de 18 px o más, o en información que no es indispensable.
- Sobre `--secundario`, el texto va en `--tinta` o en blanco con 18 px o más en negrita.

**Definición única de los tokens (CSS)**

```css
:root {
  /* Colores */
  --primario: #5B6BB1;
  --secundario: #00A09B;
  --acento: #587EAC;
  --celeste: #E1EEFF;
  --gris-claro: #EAEAEA;
  --texto: #898989;
  --texto-suave: #7A7A7A;
  --tinta: #3E4043;

  /* Tipografía */
  --fuente-base: 'Asap', sans-serif;

  /* Sombras */
  --sombra-tarjeta: 0 4px 24px rgba(91, 107, 177, 0.12);

  /* Bordes */
  --radio-tarjeta: 16px;
  --radio-boton: 8px;
}

body {
  font-family: var(--fuente-base);
  color: var(--tinta);
}
```

**Cómo se aplica en cada parte**

- **React y Tailwind:** el archivo de tokens se importa una vez y Tailwind los expone como clases (`bg-primario`, `text-tinta`, `rounded-tarjeta`, `shadow-tarjeta`); prohibido escribir colores hexadecimales en los componentes.
- **Filament:** tema personalizado con `--primario` como color primario, `--secundario` como color de éxito, fuente Asap y los mismos radios y sombras.
- **Correos:** plantilla base con los mismos colores y fuente (con respaldo `Arial` para clientes de correo que no cargan fuentes web).
- **Componentes base reutilizables:** `Boton`, `Tarjeta`, `Insignia`, `CampoTexto`, `SelectorDeHora`, todos construidos con estos tokens.
- **Logo y colores del negocio:** la página pública de cada negocio puede sobrescribir `--primario` y `--secundario` con sus colores de marca (RF-NEG-01), manteniendo el resto del sistema.

## 14. Ajustes de alcance (27 oct 2026)

Estos requerimientos se suman a los anteriores y tienen prioridad sobre ellos donde haya diferencia: todo el sistema funciona en un solo dominio, el super admin controla todo y cada negocio queda activo solo mientras pague su mensualidad.

### 14.1 Un solo dominio para todos los negocios

- **RF-DOM-01 (MVP)** Todo el sistema vive en un solo dominio. Cada negocio tiene su página pública en `sudominio.com/{slug}` (por ejemplo `sudominio.com/barberjapo`) y su panel en `sudominio.com/app/{slug}`.
- **RF-DOM-02 (MVP)** Todos los usuarios inician sesión en la misma pantalla de acceso; al entrar, el sistema los lleva al panel de su negocio con sus propios servicios, empleados y citas. Si pertenecen a más de un negocio, eligen cuál abrir.
- **RF-DOM-03** El dominio propio por negocio (RF-NEG-05) queda fuera del alcance.

### 14.2 Super administrador con control total

- **RF-SUP-01 (MVP)** El super admin puede ver y modificar cualquier dato de cualquier negocio: datos del negocio, horarios, servicios, precios, duraciones, empleados, usuarios, citas, cursos, productos, configuración y página web.
- **RF-SUP-02 (MVP)** Botón “Entrar como el negocio” (suplantación): abre el panel del negocio tal como lo ve su dueño, con una barra visible “Estás viendo el negocio X como super admin – Salir”.
- **RF-SUP-03 (MVP)** Todo lo que el super admin hace dentro de un negocio queda en la auditoría marcado como “hecho por soporte”.
- **RF-SUP-04 (MVP)** Ficha de cada negocio en el panel de super admin: estado de la suscripción, pagos, usuarios, uso (citas del mes), últimos errores y últimos cambios, para atender un reporte en un solo lugar.
- **RF-SUP-05 (MVP)** Restablecer la contraseña de un usuario de cualquier negocio, sin ver la contraseña actual.

### 14.3 Página web personalizable por negocio

- **RF-PAG-01 (MVP)** El super admin crea y personaliza la página de cada negocio desde su panel, como se hizo con BarberJapo; el dueño puede editar textos, fotos y servicios si el super admin se lo permite.
- **RF-PAG-02 (MVP)** Plantillas de diseño por tipo de negocio (barbería, spa de uñas, salón) que se eligen y ajustan por negocio.
- **RF-PAG-03 (MVP)** La página se arma con bloques que se agregan, quitan y reordenan: portada, nuestro equipo, servicios, cursos, productos, galería, testimonios, ubicación con mapa, horario, redes y botón flotante de WhatsApp.
- **RF-PAG-04 (MVP)** Cada negocio tiene su logo, fotos, colores de marca y textos; la guía de estilo de la sección 13 es la base y los colores del negocio la sobrescriben solo en su página.
- **RF-PAG-05 (MVP)** Desde cualquier bloque de la página se puede reservar: “Reservar” en la portada, “Reservar con {empleado}” en el equipo y “Agendar” en cada servicio, todos abren el flujo F2 con lo elegido ya marcado.
- **RF-PAG-06 (MVP)** Vista previa antes de publicar los cambios de la página.

### 14.4 Empleados con horario propio y Google Calendar

- **RF-EMP-01 (MVP)** Cada empleado tiene su propio usuario, su horario semanal y sus bloqueos, y solo ve sus citas.
- **RF-EMP-02 (Fase 2)** Cada empleado conecta su propia cuenta de Google Calendar desde su perfil; sus citas se crean, actualizan y borran en ese calendario, y si se desconecta el sistema avisa al empleado y al dueño.

### 14.5 Suscripción del negocio: suspensión y reactivación

Un negocio que no paga o se retira deja de funcionar, pero sus datos se conservan para poder reactivarlo si vuelve.

| Estado | Cuándo | Página pública | Usuarios del negocio |
| --- | --- | --- | --- |
| Activo | La mensualidad está al día | Funciona y recibe reservas | Entran normal |
| Vencido (gracia) | Pasó la fecha de pago; dura 5 días (configurable) | Funciona normal | Entran y ven un aviso de pago pendiente |
| Suspendido | Terminó la gracia sin pago, o el super admin lo suspende | Muestra “Esta página no está disponible por el momento”; no recibe reservas | No pueden entrar; ven “Tu cuenta está suspendida, comunícate con soporte” |
| Cancelado | El negocio se retira o lleva 3 meses suspendido (configurable) | Igual que suspendido | No pueden entrar |
| Reactivado | El super admin registra un pago o lo reactiva | Vuelve a funcionar con todo lo que tenía | Vuelven a entrar con sus mismos usuarios |

- **RF-SUS-01 (MVP)** El super admin registra los pagos de la mensualidad de cada negocio (fecha, valor, método, periodo cubierto) y el sistema calcula la próxima fecha de pago.
- **RF-SUS-02 (MVP)** Una tarea programada diaria cambia los estados según las fechas: activo → vencido → suspendido → cancelado.
- **RF-SUS-03 (MVP)** Avisos automáticos por correo al dueño: 5 días antes del vencimiento, el día del vencimiento, al iniciar la gracia y al suspender; y al super admin, la lista diaria de negocios vencidos.
- **RF-SUS-04 (MVP)** Al suspender o cancelar, se cierran las sesiones abiertas de todos los usuarios del negocio y se bloquean las reservas nuevas, incluidas las hechas por API.
- **RF-SUS-05 (MVP)** Las citas futuras ya agendadas no se borran; el panel del super admin las lista para que el negocio avise a sus clientes.
- **RF-SUS-06 (MVP)** Reactivar un negocio con un clic devuelve todo como estaba: usuarios, servicios, empleados, horarios, historial, página y su mismo enlace.
- **RF-SUS-07 (MVP)** El super admin puede suspender, cancelar o reactivar manualmente cualquier negocio, con un motivo obligatorio que queda en la auditoría.
- **RF-SUS-08 (MVP)** Los datos de un negocio cancelado se conservan 12 meses (configurable); después, el super admin decide si los elimina, cumpliendo la Ley 1581 de 2012.
- **RF-SUS-09 (Fase 3)** Cobro automático de la mensualidad con pasarela (Wompi o Mercado Pago), que reactiva el negocio al confirmarse el pago.

**Criterios de aceptación**

- Un usuario de un negocio suspendido o cancelado no puede entrar al panel ni usar una sesión que ya tenía abierta.
- La página pública de un negocio suspendido no ofrece horas ni acepta reservas.
- Al reactivar, el dueño entra con su misma contraseña y encuentra todos sus datos intactos.
- Cada cambio de estado queda registrado con fecha, motivo y quién lo hizo (sistema o super admin).

### 14.6 Observabilidad

Cuando un negocio reporta un problema, el super admin debe encontrar la causa en minutos, filtrando por negocio.

- **RNF-OBS-01 (MVP)** Registro de errores con Sentry (o equivalente) que incluya en cada error el negocio, el usuario, la ruta y un identificador de la petición.
- **RNF-OBS-02 (MVP)** Logs estructurados en JSON con `negocio_id`, `usuario_id` e `id_peticion` en cada línea, para buscar todo lo que le pasó a un negocio.
- **RNF-OBS-03 (MVP)** Panel de salud para el super admin: errores de las últimas 24 horas por negocio, trabajos fallidos en las colas, correos no enviados y sincronizaciones con Google Calendar fallidas, con opción de reintentar.
- **RNF-OBS-04 (MVP)** Alertas por correo (o Telegram) cuando un mismo error se repite, cuando una cola se detiene o cuando falla la tarea diaria de suscripciones.
- **RNF-OBS-05 (MVP)** Ruta de salud (`/estado`) que verifica base de datos, colas, almacenamiento y envío de correos, vigilada por un monitor externo gratuito que avisa si el sitio se cae.
- **RNF-OBS-06 (Fase 2)** Métricas de rendimiento (tiempo de respuesta de la página pública y del cálculo de disponibilidad) con Laravel Pulse.
- **RNF-OBS-07 (MVP)** Mensajes de error para el usuario en lenguaje simple con un código de referencia (“Error REF-8F3K”) que el negocio puede enviar a soporte para encontrar el error exacto.

### 14.7 Cambios al plan de construcción

- La tarea T2 incluye además la suscripción del negocio (14.5) y la suplantación del super admin (14.2).
- La tarea T8 incluye las plantillas y bloques de la página (14.3).
- Nueva tarea **T9. Observabilidad** (14.6), antes del primer despliegue con clientes reales.

## 15. Página personalizable y diseño de pantallas

La página de cada negocio es una lista de bloques guardada en la base de datos que React dibuja y que se comunica con las reservas solo por la API pública; las pantallas de reserva y pago siguen la estructura de Hostinger Ecommerce con los colores y la fuente de la sección 13.

### 15.1 Cómo funciona la página personalizable

- **RF-BLQ-01 (MVP)** Cada negocio tiene una página con plantilla, colores, logo y una lista ordenada de bloques guardada como JSON (tabla `paginas`).
- **RF-BLQ-02 (MVP)** Los bloques de equipo, servicios, cursos y productos no guardan datos: los consultan al sistema al mostrarse, así un cambio de precio o de empleado se ve en la página sin editarla.
- **RF-BLQ-03 (MVP)** El super admin arma la página en Filament con el componente *Builder*: un formulario por tipo de bloque, arrastrar para ordenar, vista previa y publicar.
- **RF-BLQ-04 (MVP)** React dibuja cada bloque con un registro de componentes por tipo (patrón Registro/Fábrica); agregar un tipo de bloque nuevo no modifica los existentes.
- **RF-BLQ-05 (MVP)** Un solo componente `FlujoReserva` abre la reserva desde cualquier bloque, recibiendo lo ya elegido (servicio o empleado).

**API pública (todas las rutas bajo `/api/publico/{slug}`)**

| Método | Ruta | Devuelve o hace |
| --- | --- | --- |
| GET | `/pagina` | Plantilla, colores, logo y bloques publicados; o “no disponible” si el negocio está suspendido |
| GET | `/servicios` | Servicios visibles con categoría, precio, duración y foto |
| GET | `/empleados` | Empleados activos con foto, especialidad y servicios que presta |
| GET | `/disponibilidad?servicio=&empleado=&mes=` | Días del mes que tienen al menos una hora libre |
| GET | `/disponibilidad?servicio=&empleado=&fecha=` | Horas libres de ese día, calculadas por el motor de la sección 5 |
| POST | `/reservas` | Verifica de nuevo la hora, crea la cita y dispara el evento `CitaCreada` |
| GET / POST | `/reservas/{token}` | Ver, cancelar o reprogramar una cita desde el enlace firmado del correo |
| GET | `/cursos` y `/productos` | Fechas con cupos y productos con existencias (fases 2 y 3) |

La página nunca calcula horas ni guarda citas: solo muestra y pregunta.

### 15.2 Ventana de reserva (estructura tomada de Hostinger)

- Ventana con dos paneles. Izquierda, fondo blanco: nombre del servicio, duración con ícono de reloj, empleado elegido y dirección con ícono de edificio. Derecha, fondo `--celeste` muy suave o gris claro: título “Selecciona fecha y hora”.
- Calendario mensual en tarjeta blanca (radio 16 px, sombra de la guía) con flechas de mes; días pasados o sin horas tachados en `--texto`; día elegido en círculo con borde `--primario` y fondo `--celeste`; punto bajo el día de hoy.
- Columna de horas: “Elige un horario disponible”, la zona horaria debajo en texto pequeño, y cada hora como botón de ancho completo con borde `--gris-claro`, texto en negrita centrado y estado seleccionado en `--primario`.
- Pie: “Cancelar” (botón con borde) y “Continuar” (botón lleno `--primario`, deshabilitado hasta elegir hora).
- En celular los paneles se apilan: resumen arriba, calendario y horas debajo, botón fijo abajo.

### 15.3 Datos y confirmación (estructura tomada del checkout de Hostinger)

- Dos columnas. Izquierda: Contacto (correo), Tus datos (nombre completo, celular con bandera y +57), Pago (tarjeta con el método, por ejemplo “Pagas en el local”, y sus instrucciones), casilla de tratamiento de datos y botón “Confirmar reserva” de ancho completo.
- Derecha, fondo gris claro: foto del servicio con burbuja de cantidad, nombre y precio, fecha y hora con ícono, empleado, dirección, código de descuento (fase 2), subtotal y total.
- Corregido frente a Hostinger: sin país obligatorio, sin código postal, sin la palabra “Facturación”, precios como $24.000 y fechas como “mar 27 oct, 10:00 a. m. – 11:00 a. m.”.
- En celular el resumen va arriba, plegable.

### 15.4 Skill de diseño para los agentes

- Todas las pantallas se construyen siguiendo la skill del proyecto `diseno-reservas`, guardada en `.claude/skills/diseno-reservas/SKILL.md`, que contiene estas reglas, los tokens de la sección 13 y los estados de carga, vacío y error.
- Se copian la estructura y la experiencia de Hostinger, no su logo, sus íconos propios ni su código; los íconos salen de Lucide.

## 16. Decisiones técnicas confirmadas

El proyecto arranca con Laravel 12 sobre PHP 8.2/8.3 y MariaDB, para trabajar con el entorno actual sin actualizar PHP en el computador.

| Tema | Decisión | Motivo |
| --- | --- | --- |
| Framework | Laravel 12 (`^12.0`) | Funciona con PHP 8.2 local y 8.3 del hosting; correcciones de seguridad hasta el 24 feb 2027 |
| Paneles | Filament 5 | Compatible con PHP 8.2+ y Laravel 11.28+ |
| PHP | 8.2 en local (XAMPP) y 8.3 en el hosting | No usar funciones exclusivas de PHP 8.3 o superior |
| Base de datos | MariaDB (11.8 en el hosting) | Citas superpuestas se evitan con transacción y bloqueo de fila, no con restricción de exclusión |
| Hosting | Hostinger Business, servidor us-phx, Composer 2.9 y Git 2.43 disponibles por SSH | Sin Node en el servidor: React se compila en local o en GitHub Actions |
| Colas | Driver `database` con cron cada minuto | Hosting compartido sin procesos permanentes |
| Actualización | Pasar a Laravel 13 antes de febrero de 2027, con PHP 8.3+ en local | Laravel 12 deja de recibir parches de seguridad en esa fecha |
