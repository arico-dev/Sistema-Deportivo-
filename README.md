<div align="center">

# 🏃 Sistema de Gestión Deportiva SportTrack

### Plataforma integral para la administración de actividades deportivas universitarias

[![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-8.0%2B-4479A1?logo=mysql&logoColor=white)](https://www.mysql.com/)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

[Descripción](#-descripción) •
[Instalación](#-instalación) •
[Roles](#-roles-de-usuario) •
[Estructura](#-estructura-del-proyecto) •
[Documentación](#-documentación)

</div>

---

## 📖 Descripción

Sistema web completo para la gestión de actividades deportivas en instituciones educativas. Permite administrar disciplinas deportivas, estudiantes, entrenadores, inscripciones, asistencias y evaluaciones de desempeño con un sistema de roles diferenciado.

### Problema que resuelve

Las instituciones educativas necesitan una forma eficiente de:

- Gestionar múltiples disciplinas deportivas
- Controlar la asistencia de estudiantes
- Evaluar el desempeño deportivo
- Generar reportes y estadísticas
- Coordinar entrenadores y estudiantes

### Solución

Plataforma web centralizada que digitaliza y automatiza todos los procesos de gestión deportiva, proporcionando interfaces específicas para coordinadores, entrenadores y estudiantes.

---

## ✨ Características principales

### Coordinadores

- Gestión completa de disciplinas deportivas
- Administración de entrenadores y estudiantes
- Control de inscripciones y cupos
- Generación de reportes globales
- Dashboard con estadísticas institucionales

### Entrenadores

- Visualización de estudiantes asignados
- Registro de asistencia por sesión
- Sistema de evaluación de desempeño
- Comentarios y seguimiento individual
- Reportes de sus disciplinas

### Estudiantes

- Consulta de actividades inscritas
- Historial de asistencias
- Visualización de evaluaciones
- Estadísticas personales y gráficos de progreso

### Características técnicas

- Sistema de autenticación seguro (`password_hash`)
- Control de acceso basado en roles (RBAC)
- Prepared statements para prevenir SQL Injection
- Escape de salida con `htmlspecialchars()` contra XSS
- Interfaz responsive con Tailwind CSS
- Visualización de datos con gráficos (Chart.js / Lucide)

---

## 🛠 Tecnologías

### Backend

- **PHP 7.4+** — Lenguaje de programación
- **PDO** — Capa de abstracción de base de datos
- **MySQL 8.0+** — Sistema de gestión de base de datos

### Frontend

- **HTML5** — Estructura
- **CSS3 / Tailwind CSS (CDN)** — Estilos
- **JavaScript** — Interactividad
- **Lucide** — Iconografía
- **Chart.js** — Visualización de datos

### Herramientas

- **Apache JMeter** — Pruebas de rendimiento
- **Git** — Control de versiones

---

## 📦 Requisitos previos

```bash
PHP >= 7.4
MySQL >= 8.0
Apache >= 2.4 o Nginx >= 1.18
Git
```

### Extensiones PHP requeridas

- `pdo_mysql`
- `session`
- `json`
- `mbstring`

---

## 🚀 Instalación

### 1. Clonar el repositorio

```bash
git clone https://github.com/arico-dev/Sistema-Deportivo-.git
cd Sistema-Deportivo-
```

### 2. Crear la base de datos

```bash
mysql -u root -p < database/schema.sql
mysql -u root -p sports_management < database/seed_data.sql
```

Esto crea la base `sports_management`, las tablas necesarias y datos de ejemplo (disciplinas, usuarios y registros de prueba).

### 3. Configurar la conexión

Las credenciales de MySQL están en `src/config/database.php` (por defecto `root` sin contraseña). Ajustalas según tu entorno.

### 4. Levantar el servidor

```bash
php -S localhost:8000 -t src/pages
```

O apuntá Apache/Nginx a la carpeta `src/pages` del proyecto y entrá desde el navegador.

> Las páginas necesitan conexión a internet en tiempo de ejecución porque Tailwind y Lucide se cargan desde CDN.

---

## 👥 Roles de usuario

| Rol | Acceso |
| --- | --- |
| `coordinador` | Gestión de disciplinas, estudiantes, entrenadores e inscripciones; reportes globales |
| `entrenador` | Asistencia, evaluaciones y reportes de sus estudiantes |
| `estudiante` | Sus actividades, asistencias y rendimiento personal |

### Usuarios de prueba

Se crean al ejecutar `schema.sql` y `seed_data.sql`: `admin` (coordinador), `entrenador1` / `trainer1` / `trainer2` (entrenadores) y `estudiante1` / `student1`–`student4` (estudiantes). Todos usan la contraseña `password`.

---

## 🗂 Estructura del proyecto

```
sistema-deportivo/
├── src/
│   ├── components/   # Componentes PHP reutilizables (header, sidebar)
│   ├── config/       # Conexión a base de datos y sesiones
│   ├── pages/        # Páginas PHP (controladores + vistas)
│   └── styles/       # Hojas de estilo globales
├── database/         # Esquema, seed de datos y migraciones SQL
├── docs/             # Documentación del proyecto
├── tests/jmeter/     # Planes de prueba de rendimiento
├── LICENSE
└── README.md
```

Ver [estructura-carpetas.md](docs/estructura-carpetas.md) para el detalle completo.

---

## 📚 Documentación

- [Requerimientos](docs/documentacion-requerimientos.md) — Documentación funcional y no funcional
- [Diagramas](docs/diagramas.md) — Casos de uso, clases, secuencia y entidad-relación
- [UML](docs/diagrama-clases-uml.md) — Diagrama de clases detallado
- [Estructura](docs/estructura-carpetas.md) — Organización del proyecto
- [Resultados de pruebas](docs/resultados-pruebas.md) — Pruebas de rendimiento con JMeter
- [Recomendaciones y errores](docs/recomendaciones-y-errores.md) — Análisis técnico y plan de mejora

---

## 📄 Licencia

Distribuido bajo la licencia [MIT](LICENSE).

## ✒️ Autores

- **Anthony Rico** - *Desarrollo* - [arico-dev](https://github.com/arico-dev)
- **Felipe Zurita** - *Documentación* - [fzurita12](https://github.com/fzurita12)
- **Matías Cerda** - *Documentación* - [Mvtiass14](https://github.com/Mvtiass14)