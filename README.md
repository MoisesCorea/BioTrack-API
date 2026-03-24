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
- **Sistema:** RBAC (Role-Based Access Control).
- **Middleware:** `verify.rol:role1,role2,...`
- **Roles principales:** `Admin`, `Admin-1`, `Admin-2`.
- **Nuevas capacidades:** Ahora puedes asignar permisos granulares a cada administrador usando los métodos nativos de Spatie (`$user->givePermissionTo()`).

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

## 🛡️ Niveles de Acceso
La API utiliza middleware de roles (`verify.rol`):
- **Admin**: Acceso total.
- **Admin-1 / Admin-2**: Acceso restringido a reportes y visualización según configuración.

---

## 📁 Estructura de Archivos (Clave)
- `app/Http/Controllers/`: Lógica de los endpoints.
- `app/Models/`: Modelos de Eloquent.
- `routes/api.php`: Definición de rutas.
- `storage/app/public/images/profiles/`: Almacenamiento de imágenes de perfil.
