# Sistema de Reservas para Barberías, Spas y Salones

SaaS multi-negocio de reservas, cursos y tienda para negocios de belleza en Colombia. Un solo dominio: cada negocio tiene su página pública en `/{slug}` y su panel en `/app/{slug}`. El super admin controla todos los negocios desde `/admin`.

Los requerimientos completos están en [`docs/requerimientos.md`](docs/requerimientos.md) y las reglas de diseño visual en [`.claude/skills/diseno-reservas/SKILL.md`](.claude/skills/diseno-reservas/SKILL.md).

## Stack

- **Laravel 12**, PHP **8.2** en local (XAMPP) y **8.3** en producción.
- **Filament 5** con dos paneles: `admin` (super admin, en `/admin`) y `app` (negocio, en `/app`).
- **React + TypeScript + Vite + Tailwind** para la página pública y el flujo de reserva.
- **MariaDB** (11.8 en producción).
- Colas con driver `database` y scheduler por cron (hosting compartido, sin procesos permanentes).
- Pruebas con **Pest**, formato con **Pint**, análisis estático con **Larastan** (nivel 8), lint de frontend con **ESLint**.

## Instalación

Requisitos: PHP 8.2 con las extensiones `intl`, `mbstring`, `pdo_mysql`; Composer 2; Node 20+; MariaDB o MySQL corriendo localmente (por ejemplo, con XAMPP).

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
```

Crea en tu servidor MariaDB una base de datos `reservas` (y opcionalmente `reservas_testing` para las pruebas, que es la que usa `phpunit.xml`) y ajusta `DB_DATABASE`, `DB_USERNAME` y `DB_PASSWORD` en `.env` si no usas los valores por defecto de XAMPP.

```bash
php artisan migrate --seed
php artisan storage:link
```

`php artisan migrate --seed` crea, además del super admin, dos negocios de prueba (Barber Japo y Spa Luna) con su propio dueño; ver [`database/seeders/NegociosDemoSeeder.php`](database/seeders/NegociosDemoSeeder.php) para las credenciales (contraseña fija `password`, solo para desarrollo local). `storage:link` crea el symlink público para las fotos de servicios y profesionales (ver "Archivos" en Decisiones técnicas).

## Comandos de desarrollo

```bash
php artisan serve          # backend
npm run dev                # frontend con Vite (recarga en caliente)
php artisan queue:work     # colas en local
php artisan schedule:work  # scheduler en local
```

## Calidad de código

Antes de abrir un pull request, estos comandos deben pasar en verde:

```bash
php artisan test            # pruebas Pest (usa la base reservas_testing, ver phpunit.xml)
./vendor/bin/pint           # formato de PHP
./vendor/bin/phpstan analyse  # análisis estático, nivel 8
npm run lint                 # ESLint sobre resources/js
npm run build                # compilación de producción de Vite
```

Un GitHub Action (`.github/workflows/ci.yml`) corre estos mismos pasos en cada push y pull request, levantando un servicio de MariaDB para las pruebas.

## Arquitectura

- **Paneles Filament**: `admin` (`App\Providers\Filament\AdminPanelProvider`) es el panel del super administrador, con acceso a todos los negocios. `app` (`App\Providers\Filament\AppPanelProvider`) es el panel de cada negocio, con tenancy por `Negocio` (se agrega en la tarea T2).
- **Página pública y flujo de reserva**: construidos en React + TypeScript (`resources/js`), compilados con Vite y montados sobre una vista Blade mínima (`resources/views/app.blade.php`). La página nunca calcula horas ni guarda citas: solo consume la API pública en `/api/publico/{slug}/...` (se agrega en la tarea T6).
- **Lógica de dominio**: vive en servicios (`ServicioDisponibilidad`, `ServicioReservas`) y acciones (`CrearReserva`, `CancelarCita`), nunca en controladores ni recursos de Filament.
- **Multi-negocio**: toda tabla de negocio lleva `negocio_id` con scope global obligatorio (trait `PerteneceANegocio` + `Scopes\ScopeNegocio`, aplicado sobre el tenant activo de Filament); un negocio suspendido o cancelado no permite el acceso de sus usuarios ni recibe reservas nuevas.
- **Archivos**: las fotos de servicios y profesionales se guardan en el disco `public` (`storage/app/public`, enlazado con `php artisan storage:link`), porque son imágenes que la página pública debe poder cargar directamente. El disco por defecto (`local`, privado) queda libre para archivos que no deban ser accesibles por URL.
- **Diseño**: un único juego de tokens de color, tipografía (Asap), sombras y radios, definido en `.claude/skills/diseno-reservas/SKILL.md` y expuesto como utilidades de Tailwind (`bg-primario`, `text-tinta`, `rounded-tarjeta`, `rounded-boton`, `shadow-tarjeta`) y como colores del tema de Filament.

## Decisiones técnicas (ADR resumido)

| Decisión | Motivo |
| --- | --- |
| Laravel 12 sobre PHP 8.2 (local) / 8.3 (hosting) | Compatible con el hosting compartido actual; recibe parches de seguridad hasta feb 2027. Se migra a Laravel 13 antes de esa fecha. |
| Filament 5 | Requiere la extensión `intl` de PHP (se habilitó en `php.ini` de XAMPP); compatible con Laravel 12 y PHP 8.2+. |
| MariaDB en vez de PostgreSQL | Es lo disponible en el hosting compartido. Las citas superpuestas se evitan con transacción y bloqueo de fila del empleado, no con una restricción de exclusión (MariaDB no la soporta). |
| Pruebas Pest contra MariaDB, no SQLite | El motor de reservas depende del bloqueo de fila de MariaDB; una base en memoria con SQLite no reproduce ese comportamiento de forma confiable. |
| React + TypeScript + Vite (sin SSR en el MVP) | La página pública consume solo la API pública; el SEO básico se resuelve con metadatos en el HTML servido por Blade. |
| Colas con driver `database` y cron | El hosting compartido no permite procesos permanentes (sin `queue:work` ni `schedule:work` persistentes). |

## Guía rápida

- **Super admin** entra en `/admin`, ve y modifica cualquier negocio, y puede suplantar a un negocio para ver su panel tal como lo ve su dueño.
- **Dueño, recepción y profesional** de un negocio entran en `/app`, cada uno con los permisos de su rol.
- **Cliente final** reserva desde la página pública en `/{slug}`, sin crear cuenta.

## Plan de construcción (MVP)

- [x] T1. Proyecto base: Filament 5, React con Vite, Pest, Pint, Larastan, GitHub Actions, README.
- [x] T2. Multi-negocio, roles, suscripción del negocio y suplantación del super admin.
- [x] T3. Catálogo: categorías, servicios, empleados.
- [ ] T4. Disponibilidad: horarios, bloqueos, festivos y `ServicioDisponibilidad`.
- [ ] T5. Reservas: `ServicioReservas`, estados, concurrencia, agenda en Filament.
- [ ] T6. API pública y flujo de reserva en React.
- [ ] T7. Notificaciones por correo y colas.
- [ ] T8. Página pública por bloques, pagos manuales, tablero del dueño y despliegue.
- [ ] T9. Observabilidad.
