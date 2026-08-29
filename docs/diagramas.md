# Diagramas del Sistema de Gestión Deportiva

## Diagrama de Clases

```mermaid
classDiagram
    class Usuario {
        +int id
        +string nombre_usuario
        +string contraseña
        +string correo
        +string nombre_completo
        +enum tipo_usuario
        +timestamp creado_en
        +timestamp actualizado_en
        +iniciarSesion()
        +cerrarSesion()
        +actualizarPerfil()
    }
    
    class Disciplina {
        +int id
        +string nombre
        +string descripcion
        +int creado_por
        +timestamp creado_en
        +agregarEntrenador()
        +eliminarEntrenador()
    }
    
    class EntrenadorDisciplina {
        +int id
        +int entrenador_id
        +int disciplina_id
        +timestamp asignado_en
    }
    
    class InscripcionEstudiante {
        +int id
        +int estudiante_id
        +int disciplina_id
        +int entrenador_id
        +timestamp inscrito_en
        +enum estado
        +activar()
        +desactivar()
    }
    
    class Asistencia {
        +int id
        +int estudiante_id
        +int disciplina_id
        +int entrenador_id
        +date fecha_asistencia
        +enum estado
        +string notas
        +timestamp registrado_en
        +marcarPresente()
        +marcarAusente()
        +marcarTarde()
    }
    
    class Evaluacion {
        +int id
        +int estudiante_id
        +int disciplina_id
        +int entrenador_id
        +date fecha_evaluacion
        +decimal puntuacion
        +decimal puntuacion_maxima
        +string comentarios
        +string tipo_sesion
        +timestamp creado_en
        +calcularRendimiento()
    }
    
    Usuario "1" -- "0..*" Disciplina : crea
    Usuario "1" -- "0..*" EntrenadorDisciplina : asignado a
    Usuario "1" -- "0..*" InscripcionEstudiante : inscribe
    Usuario "1" -- "0..*" Asistencia : registra
    Usuario "1" -- "0..*" Evaluacion : evalúa
    
    Disciplina "1" -- "0..*" EntrenadorDisciplina : tiene
    Disciplina "1" -- "0..*" InscripcionEstudiante : ofrece
    Disciplina "1" -- "0..*" Asistencia : rastrea
    Disciplina "1" -- "0..*" Evaluacion : mide
    
    EntrenadorDisciplina "0..*" -- "1" Usuario : pertenece a
    InscripcionEstudiante "0..*" -- "1" Usuario : pertenece a
```

## Diagramas de Casos de Uso

### Casos de Uso - Coordinador

```mermaid
graph TD
    Coordinador((Coordinador))
    
    %% Gestión de Usuarios
    GU[Gestión de Usuarios]
    GU1[Registrar Usuario]
    GU2[Editar Usuario]
    GU3[Eliminar Usuario]
    
    %% Gestión de Disciplinas
    GD[Gestión de Disciplinas]
    GD1[Crear Disciplina]
    GD2[Editar Disciplina]
    GD3[Eliminar Disciplina]
    GD4[Asignar Entrenador]
    
    %% Gestión de Inscripciones
    GI[Gestión de Inscripciones]
    GI1[Inscribir Estudiante]
    GI2[Cambiar Estado Inscripción]
    
    %% Reportes
    R[Reportes y Estadísticas]
    R1[Ver Estadísticas Generales]
    R2[Generar Reportes]
    
    Coordinador --> GU
    GU --> GU1
    GU --> GU2
    GU --> GU3
    
    Coordinador --> GD
    GD --> GD1
    GD --> GD2
    GD --> GD3
    GD --> GD4
    
    Coordinador --> GI
    GI --> GI1
    GI --> GI2
    
    Coordinador --> R
    R --> R1
    R --> R2
```

### Casos de Uso - Entrenador

```mermaid
graph TD
    Entrenador((Entrenador))
    
    %% Gestión de Estudiantes
    GE[Gestión de Estudiantes]
    GE1[Ver Estudiantes Asignados]
    
    %% Gestión de Asistencia
    GA[Gestión de Asistencia]
    GA1[Registrar Asistencia]
    GA2[Ver Historial de Asistencia]
    
    %% Gestión de Evaluaciones
    GEV[Gestión de Evaluaciones]
    GEV1[Registrar Evaluación]
    GEV2[Ver Historial de Evaluaciones]
    
    %% Reportes
    R[Reportes]
    R1[Ver Estadísticas de Estudiantes]
    R2[Generar Reportes de Rendimiento]
    
    Entrenador --> GE
    GE --> GE1
    
    Entrenador --> GA
    GA --> GA1
    GA --> GA2
    
    Entrenador --> GEV
    GEV --> GEV1
    GEV --> GEV2
    
    Entrenador --> R
    R --> R1
    R --> R2
```

### Casos de Uso - Estudiante

```mermaid
graph TD
    Estudiante((Estudiante))
    
    %% Mis Actividades
    MA[Mis Actividades]
    MA1[Ver Disciplinas Inscritas]
    MA2[Ver Entrenadores Asignados]
    
    %% Mi Asistencia
    MAS[Mi Asistencia]
    MAS1[Ver Historial de Asistencia]
    
    %% Mi Rendimiento
    MR[Mi Rendimiento]
    MR1[Ver Evaluaciones]
    MR2[Ver Calificaciones]
    
    Estudiante --> MA
    MA --> MA1
    MA --> MA2
    
    Estudiante --> MAS
    MAS --> MAS1
    
    Estudiante --> MR
    MR --> MR1
    MR --> MR2
```

## Diagrama de Relaciones entre Entidades

```mermaid
erDiagram
    USERS ||--o{ DISCIPLINES : creates
    USERS ||--o{ TRAINER_DISCIPLINES : is_assigned
    USERS ||--o{ STUDENT_ENROLLMENTS : enrolls
    USERS ||--o{ ATTENDANCE : records
    USERS ||--o{ EVALUATIONS : evaluates
    
    DISCIPLINES ||--o{ TRAINER_DISCIPLINES : has
    DISCIPLINES ||--o{ STUDENT_ENROLLMENTS : offers
    DISCIPLINES ||--o{ ATTENDANCE : tracks
    DISCIPLINES ||--o{ EVALUATIONS : measures
    
    USERS {
        int id PK
        string username
        string password
        string email
        string full_name
        enum user_type
        timestamp created_at
        timestamp updated_at
    }
    
    DISCIPLINES {
        int id PK
        string name
        string description
        int created_by FK
        timestamp created_at
    }
    
    TRAINER_DISCIPLINES {
        int id PK
        int trainer_id FK
        int discipline_id FK
        timestamp assigned_at
    }
    
    STUDENT_ENROLLMENTS {
        int id PK
        int student_id FK
        int discipline_id FK
        int trainer_id FK
        timestamp enrolled_at
        enum status
    }
    
    ATTENDANCE {
        int id PK
        int student_id FK
        int discipline_id FK
        int trainer_id FK
        date attendance_date
        enum status
        string notes
        timestamp recorded_at
    }
    
    EVALUATIONS {
        int id PK
        int student_id FK
        int discipline_id FK
        int trainer_id FK
        date evaluation_date
        decimal score
        decimal max_score
        string comments
        string session_type
        timestamp created_at
    }
```