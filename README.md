# Control de Asistencia

Sistema web para registrar la entrada y salida del personal, calcular tardanzas y generar reportes por área. Tiene una API REST en **Laravel** y un frontend en **Vue 3**, en carpetas separadas.

| Parte | Tecnologías |
|---|---|
| `api/` | PHP 8.3+, Laravel 13, Sanctum, Eloquent, MySQL (SQLite para desarrollo y pruebas), PHPUnit |
| `web/` | Vue 3 (Composition API), TypeScript, Vue Router, Pinia, axios, Tailwind CSS, Vitest |

## Funcionalidades

- **Login con token** (Laravel Sanctum) y dos roles: `admin` gestiona empleados; `supervisor` registra asistencia y ve reportes.
- **Empleados:** listado paginado con búsqueda por nombre o DNI, filtros por área y estado, alta, edición y eliminación.
- **Marcar asistencia:** entrada y salida con la hora de Lima; calcula los minutos de tardanza con 10 minutos de tolerancia.
- **Inicio:** resumen del día (activos, presentes, tardanzas, sin marcar) y últimas entradas.
- **Reportes:** tardanzas por área y los 5 empleados con más tardanzas en un rango de fechas.

### Reglas de negocio

| Regla | Respuesta |
|---|---|
| Una sola entrada por empleado y por día (también con índice único en la base) | 422 |
| Un empleado inactivo no puede marcar | 422 |
| No se registran entradas en el futuro | 422 |
| La salida debe ser posterior a la entrada y se registra una sola vez | 422 |
| Un empleado con asistencias no se elimina: se desactiva | 422 |
| Solo `admin` crea, edita o elimina empleados | 403 |
| Sin token o con token vencido (8 horas) | 401 |
| Máximo 5 intentos de login por minuto | 429 |
| Un reporte abarca como máximo 93 días | 422 |

## Arquitectura de la API

```
Ruta (routes/api.php, prefijo /api/v1)
  └─ Middleware: auth:sanctum, rol:admin, throttle
       └─ Form Request: valida la entrada (mensajes en español)
            └─ Controller: delgado, solo delega y responde
                 └─ Service: reglas de negocio (AsistenciaService, ReporteService)
                      └─ Eloquent / Query Builder → MySQL
                 └─ API Resource: da forma al JSON de salida
```

- Las reglas de negocio lanzan `ReglaNegocioException`, que responde 422 con un mensaje claro.
- El reporte por área usa `LEFT JOIN` + `GROUP BY` para incluir las áreas sin asistencias.
- El listado de empleados carga el área con `with('area')` para evitar el problema N+1.

## Cómo ejecutarlo

Requisitos: PHP 8.3+, Composer, Node 22+ y MySQL 8 (opcional: sin MySQL funciona con SQLite).

### 1. API

```bash
cd api
composer install
cp .env.example .env
php artisan key:generate
```

Con **SQLite** (lo más rápido) el `.env` ya viene listo. Con **MySQL**, crea la base `control_asistencia` y cambia en `.env`:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=control_asistencia
DB_USERNAME=root
DB_PASSWORD=tu_contraseña
```

Luego crea las tablas con datos de prueba y levanta el servidor:

```bash
php artisan migrate --seed
php artisan serve
```

El seeder crea 5 áreas, 30 empleados, asistencias de los últimos 10 días hábiles y dos usuarios: `admin@asistencia.test` y `supervisor@asistencia.test`. Su contraseña es el valor de `DEMO_PASSWORD` en `.env`.

### 2. Frontend

```bash
cd web
npm install --legacy-peer-deps
npm run dev
```

Abre http://localhost:5173. En desarrollo, Vite redirige `/api` a `http://127.0.0.1:8000`.

## Pruebas

```bash
cd api && php artisan test        # 31 pruebas: login, roles, validaciones, reglas y reportes
cd web && npx vitest run           # 9 pruebas: formulario, errores 422 y store de sesión
```

Las pruebas de la API usan SQLite en memoria y fijan la fecha y hora con `travelTo()` para que los cálculos de tardanza sean reproducibles.

## Endpoints

| Método | Ruta | Rol |
|---|---|---|
| POST | `/api/v1/login` | público |
| GET | `/api/v1/yo` · POST `/api/v1/logout` | autenticado |
| GET | `/api/v1/areas` | autenticado |
| GET | `/api/v1/empleados?buscar=&area_id=&activo=&page=` | autenticado |
| GET | `/api/v1/empleados/{id}` | autenticado |
| POST · PUT · DELETE | `/api/v1/empleados[/{id}]` | admin |
| GET | `/api/v1/asistencias?fecha=&area_id=` | autenticado |
| POST | `/api/v1/asistencias/entrada` | autenticado |
| PATCH | `/api/v1/asistencias/{id}/salida` | autenticado |
| GET | `/api/v1/reportes/tardanzas?desde=&hasta=` | autenticado |
| GET | `/api/v1/reportes/resumen-del-dia` | autenticado |
