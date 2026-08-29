-- Insertar datos de prueba para el sistema deportivo

-- Insertar disciplinas de ejemplo
INSERT IGNORE INTO disciplines (name, description, created_by) VALUES
('Fútbol', 'Entrenamiento de fútbol para todas las edades', 1),
('Baloncesto', 'Desarrollo de habilidades en baloncesto', 1),
('Natación', 'Clases de natación y técnicas acuáticas', 1),
('Atletismo', 'Entrenamiento de pista y campo', 1),
('Voleibol', 'Técnicas y estrategias de voleibol', 1);

-- Corregir 'role' por 'user_type' según el esquema real
-- Obtener IDs de usuarios entrenadores (asumiendo que ya existen)
SET @trainer1_id = (SELECT id FROM users WHERE user_type = 'entrenador' LIMIT 1);
SET @trainer2_id = (SELECT id FROM users WHERE user_type = 'entrenador' LIMIT 1 OFFSET 1);

-- Si no hay entrenadores, crear algunos de ejemplo
INSERT IGNORE INTO users (username, email, password, full_name, user_type, created_at) VALUES
('trainer1', 'trainer1@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Carlos Rodríguez', 'entrenador', NOW()),
('trainer2', 'trainer2@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'María González', 'entrenador', NOW());

-- Actualizar variables con los IDs correctos
SET @trainer1_id = (SELECT id FROM users WHERE username = 'trainer1');
SET @trainer2_id = (SELECT id FROM users WHERE username = 'trainer2');

-- Asignar disciplinas a entrenadores
INSERT IGNORE INTO trainer_disciplines (trainer_id, discipline_id, assigned_at) VALUES
(@trainer1_id, (SELECT id FROM disciplines WHERE name = 'Fútbol'), NOW()),
(@trainer1_id, (SELECT id FROM disciplines WHERE name = 'Atletismo'), NOW()),
(@trainer2_id, (SELECT id FROM disciplines WHERE name = 'Baloncesto'), NOW()),
(@trainer2_id, (SELECT id FROM disciplines WHERE name = 'Natación'), NOW()),
(@trainer2_id, (SELECT id FROM disciplines WHERE name = 'Voleibol'), NOW());

-- Crear algunos estudiantes de ejemplo si no existen
INSERT IGNORE INTO users (username, email, password, full_name, user_type, created_at) VALUES
('student1', 'student1@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ana Martínez', 'estudiante', NOW()),
('student2', 'student2@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Luis Pérez', 'estudiante', NOW()),
('student3', 'student3@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Carmen Silva', 'estudiante', NOW()),
('student4', 'student4@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Diego Torres', 'estudiante', NOW());

-- Inscribir estudiantes en disciplinas
INSERT IGNORE INTO student_enrollments (student_id, discipline_id, trainer_id, enrolled_at) VALUES
((SELECT id FROM users WHERE username = 'student1'), (SELECT id FROM disciplines WHERE name = 'Fútbol'), @trainer1_id, NOW()),
((SELECT id FROM users WHERE username = 'student2'), (SELECT id FROM disciplines WHERE name = 'Fútbol'), @trainer1_id, NOW()),
((SELECT id FROM users WHERE username = 'student3'), (SELECT id FROM disciplines WHERE name = 'Baloncesto'), @trainer2_id, NOW()),
((SELECT id FROM users WHERE username = 'student4'), (SELECT id FROM disciplines WHERE name = 'Natación'), @trainer2_id, NOW()),
((SELECT id FROM users WHERE username = 'student1'), (SELECT id FROM disciplines WHERE name = 'Atletismo'), @trainer1_id, NOW()),
((SELECT id FROM users WHERE username = 'student2'), (SELECT id FROM disciplines WHERE name = 'Baloncesto'), @trainer2_id, NOW());

-- Corregir nombres de columnas según el esquema real
-- Insertar algunos registros de asistencia de ejemplo
INSERT IGNORE INTO attendance (student_id, discipline_id, trainer_id, attendance_date, status, recorded_at) VALUES
((SELECT id FROM users WHERE username = 'student1'), (SELECT id FROM disciplines WHERE name = 'Fútbol'), @trainer1_id, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'present', DATE_SUB(NOW(), INTERVAL 1 DAY)),
((SELECT id FROM users WHERE username = 'student2'), (SELECT id FROM disciplines WHERE name = 'Fútbol'), @trainer1_id, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'present', DATE_SUB(NOW(), INTERVAL 1 DAY)),
((SELECT id FROM users WHERE username = 'student1'), (SELECT id FROM disciplines WHERE name = 'Fútbol'), @trainer1_id, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 'absent', DATE_SUB(NOW(), INTERVAL 2 DAY)),
((SELECT id FROM users WHERE username = 'student2'), (SELECT id FROM disciplines WHERE name = 'Fútbol'), @trainer1_id, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 'late', DATE_SUB(NOW(), INTERVAL 2 DAY)),
((SELECT id FROM users WHERE username = 'student3'), (SELECT id FROM disciplines WHERE name = 'Baloncesto'), @trainer2_id, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'present', DATE_SUB(NOW(), INTERVAL 1 DAY)),
((SELECT id FROM users WHERE username = 'student4'), (SELECT id FROM disciplines WHERE name = 'Natación'), @trainer2_id, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'present', DATE_SUB(NOW(), INTERVAL 1 DAY));

-- Agregando session_type a las evaluaciones para coincidir con el esquema
-- Insertar algunas evaluaciones de ejemplo
INSERT IGNORE INTO evaluations (student_id, discipline_id, trainer_id, score, max_score, session_type, comments, evaluation_date) VALUES
((SELECT id FROM users WHERE username = 'student1'), (SELECT id FROM disciplines WHERE name = 'Fútbol'), @trainer1_id, 85.5, 100.0, 'tecnica', 'Buen progreso en técnica de pase', DATE_SUB(CURDATE(), INTERVAL 3 DAY)),
((SELECT id FROM users WHERE username = 'student2'), (SELECT id FROM disciplines WHERE name = 'Fútbol'), @trainer1_id, 78.0, 100.0, 'fisica', 'Necesita mejorar la precisión en tiros', DATE_SUB(CURDATE(), INTERVAL 3 DAY)),
((SELECT id FROM users WHERE username = 'student3'), (SELECT id FROM disciplines WHERE name = 'Baloncesto'), @trainer2_id, 92.0, 100.0, 'tecnica', 'Excelente técnica de dribleo', DATE_SUB(CURDATE(), INTERVAL 2 DAY)),
((SELECT id FROM users WHERE username = 'student4'), (SELECT id FROM disciplines WHERE name = 'Natación'), @trainer2_id, 88.5, 100.0, 'general', 'Muy buena técnica de crol', DATE_SUB(CURDATE(), INTERVAL 2 DAY)),
((SELECT id FROM users WHERE username = 'student1'), (SELECT id FROM disciplines WHERE name = 'Atletismo'), @trainer1_id, 80.0, 100.0, 'fisica', 'Buen tiempo en 100 metros', DATE_SUB(CURDATE(), INTERVAL 1 DAY));

SELECT 'Datos de prueba insertados correctamente' as resultado;
