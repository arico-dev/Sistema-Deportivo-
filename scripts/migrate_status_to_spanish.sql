-- Script para migrar los valores de status de inglés a español
-- Ejecutar este script si ya tienes datos en la base de datos

USE sports_management;

-- Primero, agregar los nuevos valores al ENUM
ALTER TABLE student_enrollments 
MODIFY COLUMN status ENUM('active', 'inactive', 'activo', 'inactivo') DEFAULT 'activo';

-- Actualizar los valores existentes
UPDATE student_enrollments SET status = 'activo' WHERE status = 'active';
UPDATE student_enrollments SET status = 'inactivo' WHERE status = 'inactive';

-- Finalmente, remover los valores antiguos del ENUM
ALTER TABLE student_enrollments 
MODIFY COLUMN status ENUM('activo', 'inactivo') DEFAULT 'activo';
