# Sistema de Reservas para Barberías, Spas y Salones

SaaS multi-negocio de reservas, cursos y tienda para negocios de belleza en Colombia. Un solo dominio: cada negocio tiene su página pública en `/{slug}` y su panel en `/app/{slug}`. El super admin controla todos los negocios desde `/admin`.

**Antes de escribir código, lee siempre:**
1. `docs/requerimientos.md` — requerimientos completos (secciones 1 a 16). Las secciones 14, 15 y 16 tienen prioridad sobre las anteriores si hay diferencias.
2. `.claude/skills/diseno-reservas/SKILL.md` — antes de crear o modificar cualquier pantalla, componente o vista.

Si una regla no está clara en el documento, **pregunta antes de inventarla**.

## Stack

- **Laravel 12** (`^12.0`), PHP **8.2** en local y **8.3** en producción: no uses funciones exclusivas de PHP 8.3+.
- **Filament 5** con dos paneles: `admin` (super admin) y `app` (negocio, con tenancy por `Negocio`).
- **React + TypeScript + Vite + Tailwind** para la página pública y el flujo de reserva.
- **MariaDB** (11.8 en producción).
- Colas con driver `database` y scheduler por cron (hosting compartido, sin procesos permanentes).
- Pruebas con **Pest**; formato con **Pint**; análisis estático con **Larastan**.
- Íconos: **Lucide**.

## Reglas obligatorias

### Arquitectura
- Aplica **SOLID** en todo el código y los patrones de la sección 12.2 (Servicios, Acciones, DTO, Estado, Estrategia, Cadena de reglas, Observador con eventos, Adaptador, Fábrica, Políticas). Justifica cada patrón en el pull request.
- Controladores y recursos de Filament solo reciben y responden. La lógica vive en servicios de dominio (`ServicioDisponibilidad`, `ServicioReservas`) y acciones (`CrearReserva`, `CancelarCita`).
- Servicios externos (Google Calendar, pasarelas, correo) siempre detrás de una interfaz propia, registrada en un `ServiceProvider`.
- La página pública **nunca** calcula horas ni guarda citas: solo consume la API pública `/api/publico/{slug}/...` (sección 15.1).

### Multi-negocio y seguridad
- Toda tabla del negocio lleva `negocio_id` con scope global obligatorio. Nunca filtres a mano. Escribe pruebas que verifiquen que un negocio no puede ver ni modificar datos de otro.
- Un negocio **suspendido o cancelado** no permite entrar a sus usuarios ni recibe reservas (sección 14.5).
- Todo cambio sensible queda en la auditoría.

### Reglas de reserva
- Sigue exactamente el algoritmo de la sección 5.
- Fechas guardadas en UTC y mostradas en la zona del negocio (America/Bogota por defecto).
- Citas superpuestas se evitan con transacción y bloqueo de fila del empleado (MariaDB no tiene restricción de exclusión).

### Nombres y código limpio
- Todo en **español**: clases, métodos, variables, propiedades, tablas, columnas, componentes, props y pruebas. Sin tildes ni ñ en identificadores (`anio`, `duracionMinutos`).
- Clases y componentes en `PascalCase`, métodos y variables en `camelCase`, tablas y columnas en `snake_case`, constantes en `MAYUSCULAS_CON_GUION`.
- En inglés solo lo que exige el framework (`handle`, `boot`, `rules`, etc.).
- `declare(strict_types=1);` y tipos en todo. Funciones cortas, una sola responsabilidad, máximo 3 parámetros (si no, DTO), sin números mágicos (usa enums y constantes), retorno temprano, sin código duplicado. Comentarios solo para explicar el porqué.

### Diseño
- Solo los tokens de la sección 13 y de la skill `diseno-reservas`. Prohibido escribir colores, sombras o radios sueltos.
- Fuente Asap, tarjetas con radio 16 px, botones con radio 8 px.
- Precios como `$24.000`, fechas como "mar 27 oct", horas como "10:00 a. m.".

### Observabilidad
- Cada error y log incluye `negocio_id`, `usuario_id` e `id_peticion` (sección 14.6).
- Errores para el usuario en lenguaje simple con código de referencia.

## Forma de trabajar cada tarea

1. Lee la tarea (T1, T2…) y sus requerimientos en `docs/requerimientos.md`.
2. Muestra un **plan corto** y espera aprobación.
3. Migraciones y modelos → servicios y acciones → pruebas Pest con los criterios de aceptación → interfaz.
4. Ejecuta y deja en verde:
   ```bash
   php artisan test
   ./vendor/bin/pint
   ./vendor/bin/phpstan analyse
   npm run lint
   npm run typecheck
   npm run build
   ```
5. Una rama y un pull request por tarea, con resumen de cambios y patrones usados.

## Comandos útiles

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve          # backend
npm run dev                # frontend con Vite
php artisan queue:work     # colas en local
php artisan schedule:work  # scheduler en local
```

## Orden de tareas del MVP

- [ ] T1. Proyecto base: Filament 5, React con Vite, Pest, Pint, Larastan, GitHub Actions, README.
- [ ] T2. Multi-negocio, roles, suscripción del negocio y suplantación del super admin.
- [ ] T3. Catálogo: categorías, servicios, empleados.
- [ ] T4. Disponibilidad: horarios, bloqueos, festivos y `ServicioDisponibilidad`.
- [ ] T5. Reservas: `ServicioReservas`, estados, concurrencia, agenda en Filament.
- [ ] T6. API pública y flujo de reserva en React.
- [ ] T7. Notificaciones por correo y colas.
- [ ] T8. Página pública por bloques, pagos manuales, tablero del dueño y despliegue.
- [ ] T9. Observabilidad.
