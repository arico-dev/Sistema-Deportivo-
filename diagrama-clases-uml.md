# Diagrama de Clases UML - Sistema de Gestión de Actividades Deportivas

## Descripción General
Este diagrama representa el diseño de la solución del Sistema de Gestión de Actividades Deportivas a nivel de clases, mostrando las entidades principales, sus atributos, métodos y las relaciones entre ellas.

## Diagrama de Clases UML

\`\`\`mermaid
classDiagram
    class User {
        -int id
        -string username
        -string password
        -string email
        -string full_name
        -string user_type
        -datetime created_at
        -datetime updated_at
        +login(username, password) bool
        +logout() void
        +updateProfile(data) bool
        +changePassword(oldPassword, newPassword) bool
        +validateCredentials() bool
    }

    class Coordinator {
        +createDiscipline(name, description) Discipline
        +updateDiscipline(id, data) bool
        +deleteDiscipline(id) bool
        +createTrainer(data) Trainer
        +createStudent(data) Student
        +assignTrainerToDiscipline(trainerId, disciplineId) bool
        +viewAllReports() array
        +manageUsers() array
    }

    class Trainer {
        +viewAssignedDisciplines() array
        +enrollStudent(studentId, disciplineId) Enrollment
        +recordAttendance(studentId, disciplineId, status, notes) Attendance
        +createEvaluation(studentId, disciplineId, score, comments) Evaluation
        +viewMyStudents() array
        +generateReports() array
    }

    class Student {
        +viewMyActivities() array
        +viewMyAttendance() array
        +viewMyEvaluations() array
        +viewMyPerformance() array
        +getEnrollmentStatus(disciplineId) string
    }

    class Discipline {
        -int id
        -string name
        -string description
        -int created_by
        -datetime created_at
        +create(name, description, createdBy) bool
        +update(data) bool
        +delete() bool
        +getTrainers() array
        +getEnrolledStudents() array
        +hasActiveEnrollments() bool
    }

    class TrainerDiscipline {
        -int id
        -int trainer_id
        -int discipline_id
        -datetime assigned_at
        +assign(trainerId, disciplineId) bool
        +remove() bool
        +getAssignmentDetails() array
    }

    class Enrollment {
        -int id
        -int student_id
        -int discipline_id
        -int trainer_id
        -datetime enrolled_at
        -string status
        +enroll(studentId, disciplineId, trainerId) bool
        +unenroll() bool
        +reactivate() bool
        +updateStatus(status) bool
        +isActive() bool
    }

    class Attendance {
        -int id
        -int student_id
        -int discipline_id
        -int trainer_id
        -date attendance_date
        -string status
        -string notes
        -datetime recorded_at
        +record(studentId, disciplineId, status, notes) bool
        +update(data) bool
        +delete() bool
        +getAttendanceRate(studentId, disciplineId) float
    }

    class Evaluation {
        -int id
        -int student_id
        -int discipline_id
        -int trainer_id
        -date evaluation_date
        -decimal score
        -decimal max_score
        -string comments
        -string session_type
        -datetime created_at
        +create(studentId, disciplineId, score, comments) bool
        +update(data) bool
        +delete() bool
        +getAverageScore(studentId, disciplineId) float
        +getPerformanceHistory(studentId) array
    }

    class Database {
        -PDO connection
        -string host
        -string dbname
        -string username
        -string password
        +getConnection() PDO
        +closeConnection() void
        +executeQuery(query, params) array
        +beginTransaction() void
        +commit() void
        +rollback() void
    }

    class SessionManager {
        +isLoggedIn() bool
        +requireLogin() void
        +getUserType() string
        +requireUserType(allowedTypes) void
        +getCurrentUser() array
        +setUserSession(userData) void
        +destroySession() void
    }

    %% Relaciones de Herencia
    User <|-- Coordinator : extends
    User <|-- Trainer : extends
    User <|-- Student : extends

    %% Relaciones de Asociación
    Coordinator "1"  "*" Discipline : creates/manages
    Coordinator "1"  "*" Trainer : creates/manages
    Coordinator "1"  "*" Student : creates/manages
    
    Trainer "*"  "*" Discipline : assigned to
    Trainer "1"  "*" Enrollment : manages
    Trainer "1"  "*" Attendance : records
    Trainer "1"  "*" Evaluation : creates
    
    Student "1"  "*" Enrollment : enrolled in
    Student "1"  "*" Attendance : has
    Student "1"  "*" Evaluation : receives
    
    Discipline "1"  "*" Enrollment : has
    Discipline "1"  "*" Attendance : tracks
    Discipline "1"  "*" Evaluation : contains
    
    %% Relaciones de Composición
    TrainerDiscipline "*"  "1" Trainer : belongs to
    TrainerDiscipline "*"  "1" Discipline : belongs to
    
    %% Relaciones de Dependencia
    User ..> Database : uses
    User ..> SessionManager : uses
    Discipline ..> Database : uses
    Enrollment ..> Database : uses
    Attendance ..> Database : uses
    Evaluation ..> Database : uses
\`\`\`

## Descripción de las Clases

### 1. **User (Clase Base)**
Clase abstracta que representa a todos los usuarios del sistema. Contiene los atributos y métodos comunes para autenticación y gestión de perfil.

**Atributos:**
- `id`: Identificador único del usuario
- `username`: Nombre de usuario único
- `password`: Contraseña cifrada con hash
- `email`: Correo electrónico único
- `full_name`: Nombre completo del usuario
- `user_type`: Tipo de usuario (coordinador, entrenador, estudiante)
- `created_at`: Fecha de creación
- `updated_at`: Fecha de última actualización

**Métodos:**
- `login()`: Autenticación del usuario
- `logout()`: Cierre de sesión
- `updateProfile()`: Actualización de datos personales
- `changePassword()`: Cambio de contraseña
- `validateCredentials()`: Validación de credenciales

### 2. **Coordinator (Coordinador)**
Hereda de User. Responsable de la administración completa del sistema.

**Responsabilidades:**
- Gestión de disciplinas deportivas
- Gestión de usuarios (entrenadores y estudiantes)
- Asignación de entrenadores a disciplinas
- Visualización de reportes generales

### 3. **Trainer (Entrenador)**
Hereda de User. Gestiona las actividades de sus disciplinas asignadas.

**Responsabilidades:**
- Inscripción de estudiantes en disciplinas
- Registro de asistencia
- Creación de evaluaciones
- Generación de reportes de rendimiento

### 4. **Student (Estudiante)**
Hereda de User. Consulta su información académica deportiva.

**Responsabilidades:**
- Visualización de actividades inscritas
- Consulta de asistencia
- Consulta de evaluaciones
- Visualización de rendimiento

### 5. **Discipline (Disciplina)**
Representa las actividades deportivas ofrecidas.

**Atributos:**
- `id`: Identificador único
- `name`: Nombre de la disciplina
- `description`: Descripción detallada
- `created_by`: ID del coordinador que la creó
- `created_at`: Fecha de creación

**Métodos:**
- Operaciones CRUD
- Consulta de entrenadores asignados
- Consulta de estudiantes inscritos
- Validación de inscripciones activas

### 6. **TrainerDiscipline (Asignación)**
Tabla intermedia que relaciona entrenadores con disciplinas (relación muchos a muchos).

### 7. **Enrollment (Inscripción)**
Registra la inscripción de estudiantes en disciplinas.

**Atributos:**
- `status`: Estado de la inscripción (activo/inactivo)
- Relaciones con estudiante, disciplina y entrenador

### 8. **Attendance (Asistencia)**
Registra la asistencia de estudiantes a las sesiones.

**Atributos:**
- `attendance_date`: Fecha de la sesión
- `status`: Estado (presente, ausente, tarde)
- `notes`: Observaciones adicionales

### 9. **Evaluation (Evaluación)**
Registra las evaluaciones de rendimiento de los estudiantes.

**Atributos:**
- `score`: Puntaje obtenido
- `max_score`: Puntaje máximo posible
- `comments`: Comentarios del entrenador
- `session_type`: Tipo de sesión evaluada

### 10. **Database**
Clase de utilidad para gestionar la conexión a la base de datos.

**Responsabilidades:**
- Establecer conexión PDO
- Ejecutar consultas preparadas
- Gestionar transacciones

### 11. **SessionManager**
Clase de utilidad para gestionar sesiones de usuario.

**Responsabilidades:**
- Validación de autenticación
- Control de acceso por roles
- Gestión de datos de sesión

## Relaciones entre Clases

### Herencia
- **Coordinator**, **Trainer** y **Student** heredan de **User**
- Implementan el patrón de herencia para reutilizar código común

### Asociación
- Un **Coordinator** crea y gestiona múltiples **Disciplines**
- Un **Trainer** puede estar asignado a múltiples **Disciplines**
- Un **Student** puede estar inscrito en múltiples **Disciplines**

### Composición
- **TrainerDiscipline** es una tabla de unión que conecta **Trainer** y **Discipline**

### Dependencia
- Todas las clases de entidad dependen de **Database** para persistencia
- Todas las clases de usuario dependen de **SessionManager** para autenticación

## Patrones de Diseño Aplicados

1. **Herencia (Inheritance)**: User como clase base para los tres tipos de usuarios
2. **Separación de Responsabilidades**: Cada clase tiene una responsabilidad única y bien definida
3. **Singleton (implícito)**: Database mantiene una única conexión
4. **Repository Pattern (implícito)**: Cada clase maneja su propia persistencia

## Cardinalidad de Relaciones

- **User → Discipline**: 1 coordinador puede crear N disciplinas
- **Trainer → Discipline**: N entrenadores pueden estar en N disciplinas (N:N)
- **Student → Discipline**: N estudiantes pueden inscribirse en N disciplinas (N:N)
- **Trainer → Enrollment**: 1 entrenador gestiona N inscripciones
- **Student → Enrollment**: 1 estudiante tiene N inscripciones
- **Discipline → Enrollment**: 1 disciplina tiene N inscripciones
- **Student → Attendance**: 1 estudiante tiene N registros de asistencia
- **Student → Evaluation**: 1 estudiante tiene N evaluaciones

---

**Nota**: Este diagrama representa el diseño lógico del sistema. La implementación actual utiliza PHP procedimental con funciones, pero este diagrama muestra la estructura orientada a objetos que subyace en el modelo de datos y la lógica de negocio.
