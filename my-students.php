<?php
require_once 'config/session.php';
require_once 'config/database.php';

// Verificar que el usuario sea entrenador
if (getUserType() !== 'entrenador') {
    header('Location: index.php');
    exit();
}

$user = getCurrentUser();
$database = new Database();
$pdo = $database->getConnection();

$stmt = $pdo->prepare("
    SELECT DISTINCT u.id, u.full_name, u.email, 
           d.name as discipline_name,
           d.id as discipline_id,
           COUNT(DISTINCT a.id) as total_classes,
           COUNT(DISTINCT CASE WHEN a.status = 'present' THEN a.id END) as attended_classes
    FROM users u
    JOIN student_enrollments se ON u.id = se.student_id
    JOIN disciplines d ON se.discipline_id = d.id
    LEFT JOIN attendance a ON u.id = a.student_id AND a.discipline_id = d.id
    WHERE se.trainer_id = ? AND u.user_type = 'estudiante' AND se.status = 'activo'
    GROUP BY u.id, d.id, u.full_name, u.email, d.name
    ORDER BY u.full_name
");
$stmt->execute([$user['id']]);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("
    SELECT COUNT(DISTINCT se.student_id) as total_students,
           COUNT(DISTINCT se.discipline_id) as total_disciplines
    FROM student_enrollments se
    WHERE se.trainer_id = ? AND se.status = 'activo'
");
$stmt->execute([$user['id']]);
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("
    SELECT COUNT(a.id) as total_attendance,
           COUNT(CASE WHEN a.status = 'present' THEN 1 END) as present_count
    FROM attendance a
    JOIN student_enrollments se ON a.student_id = se.student_id AND a.discipline_id = se.discipline_id
    WHERE se.trainer_id = ?
");
$stmt->execute([$user['id']]);
$attendance_data = $stmt->fetch(PDO::FETCH_ASSOC);

$avg_attendance = ($attendance_data['total_attendance'] ?? 0) > 0 ? 
    (($attendance_data['present_count'] ?? 0) / $attendance_data['total_attendance']) * 100 : 0;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Estudiantes - SportTrack</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen bg-gray-100">
        <?php include 'components/sidebar.php'; ?>
        
        <div class="flex-1 flex flex-col overflow-hidden">
            <?php include 'components/header.php'; ?>
            
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100">
                <div class="container mx-auto px-6 py-8">
                    <h3 class="text-gray-700 text-3xl font-medium">Mis Estudiantes</h3>
                    
                    <!-- Estadísticas -->
                    <div class="mt-4">
                        <div class="flex flex-wrap -mx-6">
                            <div class="w-full px-6 sm:w-1/2 xl:w-1/3">
                                <div class="flex items-center px-5 py-6 shadow-sm rounded-md bg-white">
                                    <div class="p-3 rounded-full bg-indigo-600 bg-opacity-75">
                                        <i data-lucide="users" class="h-8 w-8 text-white"></i>
                                    </div>
                                    <div class="mx-5">
                                        <h4 class="text-2xl font-semibold text-gray-700"><?php echo $stats['total_students'] ?? 0; ?></h4>
                                        <div class="text-gray-500">Total Estudiantes</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="w-full mt-6 px-6 sm:w-1/2 xl:w-1/3 sm:mt-0">
                                <div class="flex items-center px-5 py-6 shadow-sm rounded-md bg-white">
                                    <div class="p-3 rounded-full bg-green-600 bg-opacity-75">
                                        <i data-lucide="activity" class="h-8 w-8 text-white"></i>
                                    </div>
                                    <div class="mx-5">
                                        <h4 class="text-2xl font-semibold text-gray-700"><?php echo $stats['total_disciplines'] ?? 0; ?></h4>
                                        <div class="text-gray-500">Disciplinas</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="w-full mt-6 px-6 sm:w-1/2 xl:w-1/3 xl:mt-0">
                                <div class="flex items-center px-5 py-6 shadow-sm rounded-md bg-white">
                                    <div class="p-3 rounded-full bg-blue-600 bg-opacity-75">
                                        <i data-lucide="percent" class="h-8 w-8 text-white"></i>
                                    </div>
                                    <div class="mx-5">
                                        <h4 class="text-2xl font-semibold text-gray-700"><?php echo number_format($avg_attendance, 1); ?>%</h4>
                                        <div class="text-gray-500">Asistencia Promedio</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Lista de estudiantes -->
                    <div class="mt-8">
                        <?php if (empty($students)): ?>
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
                            <i data-lucide="info" class="h-12 w-12 text-yellow-500 mx-auto mb-3"></i>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No hay estudiantes asignados</h3>
                            <p class="text-gray-600">Aún no tienes estudiantes inscritos en tus disciplinas.</p>
                        </div>
                        <?php else: ?>
                        <div class="flex flex-col mt-8">
                            <div class="-my-2 py-2 overflow-x-auto sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
                                <div class="align-middle inline-block min-w-full shadow overflow-hidden bg-white shadow-dashboard px-8 pt-3 rounded-bl-lg rounded-br-lg">
                                    <table class="min-w-full">
                                        <thead>
                                            <tr>
                                                <th class="px-6 py-3 border-b-2 border-gray-300 text-left text-sm leading-4 text-blue-500 tracking-wider">Estudiante</th>
                                                <th class="px-6 py-3 border-b-2 border-gray-300 text-left text-sm leading-4 text-blue-500 tracking-wider">Disciplina</th>
                                                <th class="px-6 py-3 border-b-2 border-gray-300 text-left text-sm leading-4 text-blue-500 tracking-wider">Contacto</th>
                                                <th class="px-6 py-3 border-b-2 border-gray-300 text-left text-sm leading-4 text-blue-500 tracking-wider">Asistencia</th>
                                                <th class="px-6 py-3 border-b-2 border-gray-300 text-left text-sm leading-4 text-blue-500 tracking-wider">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white">
                                            <?php foreach ($students as $student): ?>
                                            <tr>
                                                <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-500">
                                                    <div class="flex items-center">
                                                        <div class="flex-shrink-0 h-10 w-10">
                                                            <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                                                <i data-lucide="user" class="h-6 w-6 text-gray-600"></i>
                                                            </div>
                                                        </div>
                                                        <div class="ml-4">
                                                            <div class="text-sm leading-5 font-medium text-gray-900"><?php echo htmlspecialchars($student['full_name']); ?></div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-500">
                                                    <div class="text-sm leading-5 text-gray-900"><?php echo htmlspecialchars($student['discipline_name']); ?></div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-500">
                                                    <div class="text-sm leading-5 text-gray-900"><?php echo htmlspecialchars($student['email']); ?></div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-500">
                                                    <?php 
                                                    $attendance_rate = $student['total_classes'] > 0 ? ($student['attended_classes'] / $student['total_classes']) * 100 : 0;
                                                    $color_class = $attendance_rate >= 80 ? 'text-green-600' : ($attendance_rate >= 60 ? 'text-yellow-600' : 'text-red-600');
                                                    ?>
                                                    <div class="text-sm leading-5 <?php echo $color_class; ?> font-medium">
                                                        <?php echo number_format($attendance_rate, 1); ?>%
                                                    </div>
                                                    <div class="text-xs text-gray-500">
                                                        <?php echo $student['attended_classes']; ?>/<?php echo $student['total_classes']; ?> clases
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-500 text-sm leading-5 font-medium">
                                                    <a href="attendance.php?student_id=<?php echo $student['id']; ?>&discipline_id=<?php echo $student['discipline_id']; ?>" class="text-indigo-600 hover:text-indigo-900 mr-3">Asistencia</a>
                                                    <a href="evaluations.php?student_id=<?php echo $student['id']; ?>&discipline_id=<?php echo $student['discipline_id']; ?>" class="text-green-600 hover:text-green-900">Evaluar</a>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script>
        lucide.createIcons();
    </script>
</body>
</html>
