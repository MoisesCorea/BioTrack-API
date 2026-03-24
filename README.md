# BioTrack QR - Pulse Track API

Sistema de gestión de asistencia basado en escaneo de códigos QR, construido con Laravel 11.

## 🚀 Inicio Rápido

### Requisitos
- PHP >= 8.1
- Composer
- MySQL/MariaDB

### Instalación
1. Clonar el repositorio.
2. Ejecutar `composer install`.
3. Copiar `.env.example` a `.env` y configurar la base de datos.
4. Ejecutar `php artisan key:generate`.
5. Ejecutar `php artisan migrate --seed`.
6. Enlazar el storage: `php artisan storage:link`.
7. Iniciar el servidor: `php artisan serve`.

---

## 🔐 Autenticación y Autorización

La API utiliza **Laravel Sanctum** para la autenticación y **Spatie Laravel Permission** para la gestión de roles y permisos.

### Roles y Permisos
- **Sistema:** RBAC Granular (Role-Based Access Control) mediante Spatie.
- **Middleware:** `can:nombre_permiso` (ej: `can:manage_users`).
- **Roles principales:** `Admin`, `Admin-1`, `Admin-2`.
- **Estandarización:** En lugar de hardcodear roles en las rutas, ahora usamos **permisos**. Puedes asignar o revocar capacidades a un rol desde la DB sin tocar el código.

### Endpoints de Autenticación
| Método | Endpoint | Descripción |
| :--- | :--- | :--- |
| `POST` | `/api/login` | Inicia sesión y devuelve un token Bearer y el rol del usuario. |
| `POST` | `/api/change-password` | Cambia la contraseña (requiere token). |
| `GET` | `/api/logout` | Revoca el token actual. |

---

## 📊 Formato de Respuesta General
Todas las respuestas exitosas siguen este formato estándar:
```json
{
    "message": "Operación exitosa",
    "statusCode": 200,
    "data": { ... }
}
```
---

## 📡 Endpoints Principales

### 👤 Usuarios (`/api/users`)
- `GET /` - Lista todos los usuarios.
- `GET /{id}` - Detalles de un usuario.
- `POST /` - Crea un usuario (incluye `profile_image`).
- `PATCH /{id}` - Actualización de datos.
- `DELETE /{id}` - Eliminación lógica y de archivos.

### 📅 Eventos (`/api/events`)
- `GET /` - Lista de eventos.
- `POST /` - Registro de nuevo evento.
- `PATCH /{id}/status` - Activar/Desactivar evento (solo uno activo a la vez).

### ⏱️ Asistencia y Reportes (`/api/attendance`)
- `POST /api/users/{id}/attendance` - Escaneo QR (Entrada/Salida automática).
- `GET /api/reports/user` - Reporte PDF/Excel de un usuario.
- `GET /api/reports/users` - Reporte por departamento.

### 📝 Justificaciones (`/api/justifications`)
Permite justificar inasistencias por permisos, incapacidades o vacaciones.
- `GET /` - Listar todas las justificaciones (Admin).
- `POST /` - Registrar una nueva justificación (incluye carga de evidencia/comprobante).
- `PATCH /{id}/status` - Aprobar o rechazar una justificación. *Solo las aprobadas eliminan la penalización en los reportes.*

### 🛠️ Configuración de Roles (`/api/roles`)
Spatie maneja los roles internamente, pero el `RolesController` permite gestionarlos:
- `GET /` - Listar roles (incluye el campo `description`).
- `POST /` - Crear nuevo rol de Spatie con descripción personalizada.

---

## 🛡️ Niveles de Acceso (Permisos)
La API utiliza middleware de permisos nativo de Laravel (`can:`):
- **view_reports**: Permite visualizar y generar reportes.
- **manage_users**: Permite el CRUD completo de personal.
- **manage_admins**: Acceso exclusivo para gestionar el staff administrativo.
- **manage_justifications**: Permite aprobar o rechazar inasistencias.

*Nota: El rol `Admin` posee automáticamente todos los permisos.*

---

## 📁 Estructura de Archivos (Clave)
- `app/Http/Controllers/`: Lógica de los endpoints.
- `app/Models/`: Modelos de Eloquent.
- `routes/api.php`: Definición de rutas.
- `storage/app/public/images/profiles/`: Almacenamiento de imágenes de perfil.
