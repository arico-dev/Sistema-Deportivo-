# Estructura de Carpetas del Sistema Deportivo

Este documento describe la organización y estructura de carpetas del proyecto Sistema Deportivo.

## Estructura General

\`\`\`
sistemadeportivo/
├── components/                   # Componentes PHP reutilizables
│   ├── header.php               # Encabezado de la aplicación
│   └── sidebar.php              # Barra lateral de navegación
├── config/                       # Archivos de configuración
│   ├── database.php             # Configuración de conexión a base de datos
│   └── session.php              # Configuración de sesiones PHP
├── database/                     # Scripts de base de datos
│   └── schema.sql               # Esquema de la base de datos
├── public/                       # Archivos públicos estáticos
│   ├── placeholder-logo.png
│   ├── placeholder-user.jpg
│   └── placeholder.jpg
├── reporte-jmeter/              # Reportes de pruebas de rendimiento
│   ├── jmeter-generate.log
│   └── login_test_plan_corrected.jmx
├── scripts/                      # Scripts SQL adicionales
│   ├── migrate_status_to_spanish.sql
│   └── seed_data.sql
├── styles/                       # Estilos globales
│   └── globals.css
└── [archivos raíz]              # Páginas PHP principales
    ├── index.php                # Página de inicio/dashboard
    ├── login.php                # Página de inicio de sesión
    ├── logout.php               # Cierre de sesión
    ├── attendance.php           # Gestión de asistencias
    ├── disciplines.php          # Gestión de disciplinas
    ├── enrollments.php          # Gestión de inscripciones
    ├── evaluations.php          # Gestión de evaluaciones
    ├── evaluation-detail.php    # Detalle de evaluaciones
    ├── my-activities.php        # Actividades del estudiante
    ├── my-attendance.php        # Asistencias del estudiante
    ├── my-performance.php       # Rendimiento del estudiante
    ├── my-students.php          # Estudiantes del entrenador
    ├── reports.php              # Reportes administrativos
    ├── settings.php             # Configuración del sistema
    ├── students.php             # Gestión de estudiantes
    ├── trainers.php             # Gestión de entrenadores
    └── trainer-reports.php      # Reportes del entrenador
\`\`\`

## Descripción de Carpetas

### `/components`
Contiene componentes PHP reutilizables de la aplicación:
- **header.php**: Barra de navegación superior con logo y menú de usuario
- **sidebar.php**: Menú lateral con navegación según el rol del usuario (Administrador, Estudiante, Entrenador)

### `/config`
Archivos de configuración del sistema:
- **database.php**: Parámetros de conexión a MySQL (host, usuario, contraseña, base de datos)
- **session.php**: Configuración de sesiones PHP y verificación de autenticación

### `/database`
Scripts relacionados con la base de datos:
- **schema.sql**: Definición completa del esquema de la base de datos (tablas, relaciones, índices)

### `/scripts`
Scripts SQL adicionales para mantenimiento y población de datos:
- **seed_data.sql**: Datos iniciales para poblar la base de datos con usuarios, disciplinas y datos de ejemplo
- **migrate_status_to_spanish.sql**: Migración de estados a español

### `/public`
Recursos estáticos accesibles públicamente:
- Imágenes placeholder para logos y usuarios
- Archivos de imagen en formato PNG y JPG

### `/reporte-jmeter`
Archivos de pruebas de rendimiento con Apache JMeter:
- Planes de prueba y logs de ejecución para validar el rendimiento del sistema

### `/styles`
Hojas de estilo globales de la aplicación:
- **globals.css**: Estilos CSS globales para toda la aplicación

## Páginas Principales (Archivos PHP en Raíz)

### Autenticación
- **login.php**: Formulario de inicio de sesión con validación de credenciales
- **logout.php**: Cierre de sesión y destrucción de variables de sesión

### Dashboard
- **index.php**: Página principal con estadísticas y resumen según el rol del usuario

### Módulos Administrativos
- **students.php**: CRUD completo de estudiantes (crear, leer, actualizar, eliminar)
- **trainers.php**: CRUD completo de entrenadores
- **disciplines.php**: CRUD de disciplinas deportivas (fútbol, baloncesto, natación, etc.)
- **enrollments.php**: Gestión de inscripciones de estudiantes a disciplinas
- **attendance.php**: Registro y control de asistencias de estudiantes
- **evaluations.php**: Gestión de evaluaciones y pruebas deportivas
- **evaluation-detail.php**: Detalle y calificaciones individuales de evaluaciones
- **reports.php**: Reportes y estadísticas generales del sistema
- **settings.php**: Configuración del sistema y parámetros generales

### Módulos de Estudiante
- **my-activities.php**: Vista de actividades y disciplinas inscritas
- **my-attendance.php**: Historial personal de asistencias
- **my-performance.php**: Rendimiento, calificaciones y progreso personal

### Módulos de Entrenador
- **my-students.php**: Lista de estudiantes asignados al entrenador
- **trainer-reports.php**: Reportes específicos del entrenador sobre sus grupos

## Documentación

- **diagramas.md**: Diagramas del sistema (casos de uso, clases, secuencia, entidad-relación)
- **documentacion-requerimientos.md**: Documentación completa de requerimientos funcionales y no funcionales
- **resultados-pruebas.md**: Resultados de pruebas de rendimiento con JMeter
- **estructura-carpetas.md**: Este documento

## Archivos de Configuración del Proyecto

- **.gitignore**: Archivos y carpetas ignorados por Git (node_modules, archivos de configuración local, etc.)
- **jmeter.log**: Log de ejecución de pruebas JMeter

## Notas Importantes

1. El proyecto está desarrollado completamente en **PHP** con arquitectura tradicional
2. La estructura sigue un patrón donde las páginas PHP en la raíz actúan como controladores y vistas
3. Los componentes reutilizables (header y sidebar) están centralizados en `/components`
4. La configuración está separada en `/config` para facilitar el mantenimiento y despliegue
5. Los scripts de base de datos están organizados en `/database` y `/scripts` para una gestión ordenada
6. El sistema implementa control de acceso basado en roles (RBAC) con tres tipos de usuario: Administrador, Estudiante y Entrenador
7. Todas las páginas verifican la sesión activa mediante `config/session.php`

## Flujo de Archivos

1. **Punto de entrada**: `login.php` → valida credenciales → inicia sesión
2. **Dashboard**: `index.php` → muestra contenido según rol
3. **Navegación**: `components/sidebar.php` → menú dinámico según permisos
4. **Módulos**: Cada archivo PHP maneja su propia lógica de negocio y presentación
5. **Cierre**: `logout.php` → destruye sesión y redirige al login
