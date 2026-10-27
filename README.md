# MediCitas

Aplicación Laravel para listar, filtrar, agendar y gestionar citas médicas desde una interfaz web y una API autenticada con Laravel Sanctum.

## Requisitos

- PHP 8.3 o superior
- Composer
- Node.js y npm

## Instalación local

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
npm install
npm run build
php artisan serve
```

La aplicación estará disponible en `http://localhost:8000`. El seeder crea un paciente, un médico y una cita de ejemplo. En desarrollo local, las notificaciones usan el mailer `log` y se escriben en `storage/logs/laravel.log`, sin requerir un servidor SMTP.

Si ejecutas la aplicación con Laravel Sail / Docker Compose, puedes usar Mailpit configurando `MAIL_MAILER=smtp`, `MAIL_HOST=mailpit` y `MAIL_PORT=1025` en `.env`. Si ejecutas `php artisan serve` directamente en tu equipo y Mailpit está publicado por Docker en el puerto 1025, usa `MAIL_HOST=127.0.0.1` en lugar de `mailpit`. Después de cambiar la configuración de correo, ejecuta `php artisan config:clear`.

## API

Las rutas `GET /api/citas` y `POST /api/citas` requieren un token Bearer de Sanctum. Para crear un token de desarrollo para un usuario sembrado:

```bash
php artisan tinker
```

```php
$user = App\Models\User::where('email', 'paciente@example.com')->firstOrFail();
$token = $user->createToken('cliente-local')->plainTextToken;
```

Consulta las citas:

```bash
curl -H "Accept: application/json" \
     -H "Authorization: Bearer TU_TOKEN" \
     "http://localhost:8000/api/citas?estado=confirmada"
```

Agenda una cita; el paciente se toma del usuario autenticado:

```bash
curl -X POST "http://localhost:8000/api/citas" \
     -H "Accept: application/json" \
     -H "Authorization: Bearer TU_TOKEN" \
     -H "Content-Type: application/json" \
     -d '{"doctor_id":2,"especialidad":"Cardiología","fecha_hora":"2030-05-20 10:30:00","motivo":"Control preventivo"}'
```

## Pruebas

Las pruebas usan SQLite en memoria y no modifican la base local:

```bash
php artisan test
```

La suite verifica filtrado por estado, autenticación, persistencia, notificación al médico, vistas web, transición de citas pendientes a confirmadas o canceladas y un máximo de tres consultas `SELECT` para listar citas con sus pacientes y médicos.

## Confirmar o cancelar citas

En la lista web, las citas pendientes tienen acciones para aprobar (confirmar) o cancelar. Las citas que ya se confirmaron o cancelaron no se pueden volver a modificar. Para el alcance de este taller, las acciones de la interfaz web están disponibles en modo demo, sin cuentas ni roles. La API ofrece `PATCH /api/citas/{cita}/estado` con `{"estado":"confirmada"}` o `{"estado":"cancelada"}` y requiere autenticación con Sanctum.
