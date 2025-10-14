-- Base de datos para Sistema de Gestión de Actividades Deportivas
CREATE DATABASE IF NOT EXISTS sports_management;
USE sports_management;

-- Tabla de usuarios (coordinadores, entrenadores, estudiantes)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    user_type ENUM('coordinador', 'entrenador', 'estudiante') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabla de disciplinas deportivas
CREATE TABLE disciplines (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Tabla de entrenadores asignados a disciplinas
CREATE TABLE trainer_disciplines (
    id INT AUTO_INCREMENT PRIMARY KEY,
    trainer_id INT,
    discipline_id INT,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (trainer_id) REFERENCES users(id),
    FOREIGN KEY (discipline_id) REFERENCES disciplines(id),
    UNIQUE KEY unique_assignment (trainer_id, discipline_id)
);

-- Tabla de inscripciones de estudiantes
CREATE TABLE student_enrollments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT,
    discipline_id INT,
    trainer_id INT,
    enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('activo', 'inactivo') DEFAULT 'activo',
    FOREIGN KEY (student_id) REFERENCES users(id),
    FOREIGN KEY (discipline_id) REFERENCES disciplines(id),
    FOREIGN KEY (trainer_id) REFERENCES users(id)
);

-- Tabla de asistencia
CREATE TABLE attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT,
    discipline_id INT,
    trainer_id INT,
    attendance_date DATE,
    status ENUM('present', 'absent', 'late') DEFAULT 'present',
    notes TEXT,
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id),
    FOREIGN KEY (discipline_id) REFERENCES disciplines(id),
    FOREIGN KEY (trainer_id) REFERENCES users(id)
);

-- Tabla de evaluaciones/puntajes
CREATE TABLE evaluations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT,
    discipline_id INT,
    trainer_id INT,
    evaluation_date DATE,
    score DECIMAL(5,2),
    max_score DECIMAL(5,2) DEFAULT 100.00,
    comments TEXT,
    session_type VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id),
    FOREIGN KEY (discipline_id) REFERENCES disciplines(id),
    FOREIGN KEY (trainer_id) REFERENCES users(id)
);

-- Insertar datos de ejemplo
INSERT INTO users (username, password, email, full_name, user_type) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@universidad.edu', 'Coordinador Deportivo', 'coordinador'),
('entrenador1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'entrenador1@universidad.edu', 'Carlos Martínez', 'entrenador'),
('estudiante1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'estudiante1@universidad.edu', 'Ana García', 'estudiante');

INSERT INTO disciplines (name, description, created_by) VALUES
('Fútbol', 'Disciplina de fútbol para estudiantes', 1),
('Baloncesto', 'Disciplina de baloncesto para estudiantes', 1),
('Natación', 'Disciplina de natación para estudiantes', 1);
