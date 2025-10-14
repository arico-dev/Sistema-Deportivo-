# Documentación de Requerimientos - Sistema de Gestión Deportiva

## 1. Introducción

El Sistema de Gestión Deportiva es una aplicación web diseñada para administrar actividades deportivas en una institución educativa. Permite la gestión de estudiantes, entrenadores, disciplinas deportivas, inscripciones, asistencias y evaluaciones de rendimiento.

## 2. Usuarios del Sistema

El sistema cuenta con tres tipos de usuarios, cada uno con diferentes roles y permisos:

1. **Coordinador**: Administrador general del sistema con acceso completo.
2. **Entrenador**: Gestiona disciplinas asignadas y estudiantes inscritos.
3. **Estudiante**: Accede a sus actividades, asistencias y evaluaciones.

## 3. Módulos Principales

### 3.1 Gestión de Usuarios

#### Requerimientos Funcionales:
- Registro, modificación y eliminación de usuarios (estudiantes y entrenadores)
- Autenticación y control de acceso según tipo de usuario
- Gestión de perfiles de usuario

### 3.2 Gestión de Disciplinas Deportivas

#### Requerimientos Funcionales:
- Creación, edición y eliminación de disciplinas deportivas
- Asignación de entrenadores a disciplinas
- Visualización de disciplinas disponibles

### 3.3 Gestión de Inscripciones

#### Requerimientos Funcionales:
- Inscripción de estudiantes en disciplinas deportivas
- Asignación de entrenadores a estudiantes
- Activación/desactivación de inscripciones
- Consulta de inscripciones por disciplina, entrenador o estudiante

### 3.4 Control de Asistencia

#### Requerimientos Funcionales:
- Registro de asistencia de estudiantes por disciplina
- Marcado de presencia, ausencia o llegada tarde
- Consulta de historial de asistencia
- Generación de reportes de asistencia

### 3.5 Evaluación de Rendimiento

#### Requerimientos Funcionales:
- Registro de evaluaciones de rendimiento de estudiantes
- Calificación por sesión o actividad
- Comentarios y retroalimentación
- Consulta de historial de evaluaciones

### 3.6 Reportes y Estadísticas

#### Requerimientos Funcionales:
- Generación de reportes de asistencia
- Estadísticas de rendimiento por estudiante o disciplina
- Dashboard con indicadores clave según tipo de usuario

## 4. Modelo de Datos

### 4.1 Entidades Principales

1. **Usuarios (users)**
   - Almacena información de todos los usuarios del sistema
   - Atributos: id, username, password, email, full_name, user_type, created_at, updated_at

2. **Disciplinas (disciplines)**
   - Almacena las disciplinas deportivas disponibles
   - Atributos: id, name, description, created_by, created_at

3. **Asignación de Entrenadores (trainer_disciplines)**
   - Relaciona entrenadores con disciplinas
   - Atributos: id, trainer_id, discipline_id, assigned_at

4. **Inscripciones (student_enrollments)**
   - Registra inscripciones de estudiantes en disciplinas
   - Atributos: id, student_id, discipline_id, trainer_id, enrolled_at, status

5. **Asistencia (attendance)**
   - Registra la asistencia de estudiantes a sesiones
   - Atributos: id, student_id, discipline_id, trainer_id, attendance_date, status, notes, recorded_at

6. **Evaluaciones (evaluations)**
   - Almacena evaluaciones de rendimiento de estudiantes
   - Atributos: id, student_id, discipline_id, trainer_id, evaluation_date, score, max_score, comments, session_type, created_at

## 5. Flujos de Usuario

### 5.1 Coordinador

1. **Gestión de Estudiantes**
   - Registro de nuevos estudiantes
   - Edición de información de estudiantes
   - Eliminación de estudiantes (con validación de dependencias)

2. **Gestión de Entrenadores**
   - Registro de nuevos entrenadores
   - Asignación de disciplinas a entrenadores
   - Edición y eliminación de entrenadores

3. **Gestión de Disciplinas**
   - Creación de nuevas disciplinas
   - Edición de información de disciplinas
   - Eliminación de disciplinas (con validación de inscripciones)

4. **Reportes Generales**
   - Visualización de estadísticas generales
   - Generación de reportes de asistencia y rendimiento

### 5.2 Entrenador

1. **Gestión de Estudiantes Asignados**
   - Visualización de estudiantes inscritos en sus disciplinas
   - Registro de asistencia
   - Evaluación de rendimiento

2. **Reportes de Entrenador**
   - Visualización de estadísticas de sus estudiantes
   - Generación de reportes de asistencia y rendimiento

### 5.3 Estudiante

1. **Mis Actividades**
   - Visualización de disciplinas inscritas
   - Consulta de entrenadores asignados

2. **Mi Asistencia**
   - Visualización de historial de asistencia

3. **Mi Rendimiento**
   - Visualización de evaluaciones y calificaciones

## 6. Requerimientos No Funcionales

### 6.1 Seguridad
- Autenticación de usuarios
- Control de acceso basado en roles
- Protección de datos sensibles

### 6.2 Usabilidad
- Interfaz intuitiva y responsiva
- Navegación clara entre módulos
- Mensajes de confirmación y error informativos

### 6.3 Rendimiento
- Tiempos de respuesta rápidos
- Optimización de consultas a la base de datos
- Manejo eficiente de sesiones

### 6.4 Escalabilidad
- Diseño modular para facilitar expansión
- Estructura de base de datos normalizada
- Separación de lógica de negocio y presentación

## 7. Tecnologías Utilizadas

- **Backend**: PHP
- **Base de Datos**: MySQL
- **Frontend**: HTML, CSS (con estilos globales)
- **Componentes**: Sistema modular con header y sidebar

## 8. Conclusiones

El Sistema de Gestión Deportiva proporciona una solución integral para la administración de actividades deportivas en instituciones educativas, facilitando la gestión de estudiantes, entrenadores, disciplinas, asistencias y evaluaciones de rendimiento. Su diseño modular y basado en roles permite una experiencia personalizada según el tipo de usuario, optimizando los flujos de trabajo para cada perfil.
