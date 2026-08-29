# Estructura de Carpetas del Sistema Deportivo

Este documento describe la organización real del repositorio.

## Estructura General

```
sistema-deportivo/
├── src/                          # Código fuente de la aplicación
│   ├── components/                # Componentes PHP reutilizables
│   │   ├── header.php            # Encabezado con usuario y cierre de sesión
│   │   └── sidebar.php           # Menú lateral según rol del usuario
│   ├── config/                    # Configuración de la aplicación
│   │   ├── database.php          # Conexión PDO a MySQL (credenciales)
│   │   └── session.php           # Sesiones y helpers de autenticación
│   ├── pages/                     # Páginas PHP (controlador + vista)
│   │   ├── index.php             # Dashboard según el rol
│   │   ├── login.php             # Inicio de sesión
│   │   ├── logout.php            # Cierre de sesión
│   │   ├── attendance.php        # Registro de asistencias (entrenador)
│   │   ├── disciplines.php       # Gestión de disciplinas
│   │   ├── enrollments.php       # Inscripciones de estudiantes
│   │   ├── evaluations.php       # Evaluaciones de desempeño
│   │   ├── evaluation-detail.php # Detalle de una evaluación
│   │   ├── my-activities.php     # Actividades del estudiante
│   │   ├── my-attendance.php     # Asistencias del estudiante
│   │   ├── my-performance.php    # Rendimiento del estudiante
│   │   ├── my-students.php       # Estudiantes del entrenador
│   │   ├── reports.php           # Reportes administrativos
│   │   ├── settings.php          # Configuración del sistema
│   │   ├── students.php          # Gestión de estudiantes
│   │   ├── trainers.php          # Gestión de entrenadores
│   │   └── trainer-reports.php   # Reportes del entrenador
│   └── styles/
│       └── globals.css           # Estilos globales (no referenciado aún)
├── database/                      # Scripts de base de datos
│   ├── schema.sql                # Esquema completo + usuarios de ejemplo
│   ├── seed_data.sql             # Datos de prueba (idempotente)
│   └── migrations/
│       └── migrate_status_to_spanish.sql  # Migración de estados
├── docs/                          # Documentación del proyecto (español)
│   ├── diagrama-clases-uml.md
│   ├── diagramas.md
│   ├── documentacion-requerimientos.md
│   ├── estructura-carpetas.md    # Este documento
│   ├── recomendaciones-y-errores.md
│   └── resultados-pruebas.md
├── tests/
│   └── jmeter/
│       └── login_test_plan_corrected.jmx  # Plan de prueba de rendimiento
├── AGENTS.md
├── LICENSE                        # MIT
├── README.md
└── .gitignore
```

## Descripción de Carpetas

### `/src`
Código de la aplicación.

- **`src/pages`**: cada archivo PHP es a la vez controlador y vista: primero hace autenticación y consultas, luego imprime HTML. Los enlaces entre páginas usan nombres de archivo simples (misma carpeta).
- **`src/config/database.php`**: parámetros de conexión MySQL (host, usuario, contraseña, base de datos).
- **`src/config/session.php`**: helpers `isLoggedIn()`, `requireLogin()`, `getUserType()`, `requireUserType()` y `getCurrentUser()`.
- **`src/components`**: `header.php` (barra superior) y `sidebar.php` (menú dinámico según rol). Las páginas los incluyen con `include '../components/...'`.
- **`src/styles`**: hojas de estilo globales.

### `/database`
Scripts SQL. No hay framework de migraciones: el SQL se aplica a mano.

### `/docs`
Toda la documentación del proyecto, en español.

### `/tests`
Planes de prueba de rendimiento (Apache JMeter).

> **Nota:** los archivos `.md` de documentación pueden describir mejoras o estructura que no existen aún en el código (por ejemplo, refactors MVC, `.env`, carpeta `public/`). Ante la duda, la fuente de verdad es el código.

## Flujo de Archivos

1. **Punto de entrada**: `src/pages/login.php` → valida credenciales → inicia sesión
2. **Dashboard**: `src/pages/index.php` → muestra contenido según rol
3. **Navegación**: `src/components/sidebar.php` → menú dinámico según permisos
4. **Módulos**: cada archivo en `src/pages` maneja su propia lógica y presentación
5. **Cierre**: `src/pages/logout.php` → destruye sesión y redirige al login