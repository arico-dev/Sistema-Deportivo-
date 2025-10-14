<?php
require_once 'config/session.php';
require_once 'config/database.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$user = getCurrentUser();
$userType = getUserType();

// Obtener estadísticas según el tipo de usuario
$database = new Database();
$db = $database->getConnection();

$stats = [];
if ($userType === 'coordinador') {
    // Estadísticas para coordinador
    $stmt = $db->query("SELECT COUNT(*) as total_students FROM users WHERE user_type = 'estudiante'");
    $totalStudents = $stmt->fetch(PDO::FETCH_ASSOC)['total_students'];
    
    $stmt = $db->query("SELECT COUNT(*) as total_disciplines FROM disciplines");
    $totalDisciplines = $stmt->fetch(PDO::FETCH_ASSOC)['total_disciplines'];
    
    $stmt = $db->query("SELECT COUNT(*) as total_trainers FROM users WHERE user_type = 'entrenador'");
    $totalTrainers = $stmt->fetch(PDO::FETCH_ASSOC)['total_trainers'];
    
    $stmt = $db->query("SELECT COUNT(*) as total_enrollments FROM student_enrollments WHERE status = 'activo'");
    $totalEnrollments = $stmt->fetch(PDO::FETCH_ASSOC)['total_enrollments'];
    
    $stats = [
        ['name' => 'Total Estudiantes', 'value' => $totalStudents, 'icon' => 'users', 'color' => 'bg-blue-500'],
        ['name' => 'Disciplinas', 'value' => $totalDisciplines, 'icon' => 'activity', 'color' => 'bg-green-500'],
        ['name' => 'Entrenadores', 'value' => $totalTrainers, 'icon' => 'user-check', 'color' => 'bg-purple-500'],
        ['name' => 'Inscripciones Activas', 'value' => $totalEnrollments, 'icon' => 'trending-up', 'color' => 'bg-orange-500']
    ];
} elseif ($userType === 'entrenador') {
    $stmt = $db->prepare("
        SELECT COUNT(DISTINCT se.student_id) as my_students 
        FROM student_enrollments se 
        WHERE se.trainer_id = ? AND se.status = 'activo'
    ");
    $stmt->execute([$user['id']]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $myStudents = $result['my_students'] ?? 0;
    
    $stmt = $db->prepare("
        SELECT COUNT(DISTINCT se.discipline_id) as my_disciplines 
        FROM student_enrollments se 
        WHERE se.trainer_id = ? AND se.status = 'activo'
    ");
    $stmt->execute([$user['id']]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $myDisciplines = $result['my_disciplines'] ?? 0;
    
    $stats = [
        ['name' => 'Mis Estudiantes', 'value' => $myStudents, 'icon' => 'users', 'color' => 'bg-blue-500'],
        ['name' => 'Disciplinas', 'value' => $myDisciplines, 'icon' => 'activity', 'color' => 'bg-green-500']
    ];
    
    $stmt = $db->prepare("
        SELECT DISTINCT u.id, u.full_name, u.email, d.name as discipline_name, se.enrolled_at
        FROM users u
        JOIN student_enrollments se ON u.id = se.student_id
        JOIN disciplines d ON se.discipline_id = d.id
        WHERE se.trainer_id = ? AND se.status = 'activo' AND u.user_type = 'estudiante'
        ORDER BY se.enrolled_at DESC
        LIMIT 5
    ");
    $stmt->execute([$user['id']]);
    $recentStudents = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $db->prepare("
        SELECT 'attendance' as activity_type, a.attendance_date as activity_date, u.full_name as student_name, d.name as discipline_name
        FROM attendance a
        JOIN users u ON a.student_id = u.id
        JOIN disciplines d ON a.discipline_id = d.id
        JOIN student_enrollments se ON a.student_id = se.student_id AND a.discipline_id = se.discipline_id
        WHERE se.trainer_id = ? AND a.status = 'present'
        ORDER BY a.attendance_date DESC, a.recorded_at DESC
        LIMIT 5
    ");
    $stmt->execute([$user['id']]);
    $recentActivities = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    // Estadísticas para estudiante
    $stmt = $db->prepare("SELECT COUNT(*) as my_enrollments FROM student_enrollments WHERE student_id = ? AND status = 'activo'");
    $stmt->execute([$user['id']]);
    $myEnrollments = $stmt->fetch(PDO::FETCH_ASSOC)['my_enrollments'];
    
    $stmt = $db->prepare("SELECT COUNT(*) as total_attendance FROM attendance WHERE student_id = ? AND status = 'present'");
    $stmt->execute([$user['id']]);
    $totalAttendance = $stmt->fetch(PDO::FETCH_ASSOC)['total_attendance'];
    
    $stats = [
        ['name' => 'Mis Actividades', 'value' => $myEnrollments, 'icon' => 'activity', 'color' => 'bg-blue-500'],
        ['name' => 'Asistencias', 'value' => $totalAttendance, 'icon' => 'check-circle', 'color' => 'bg-green-500']
    ];
    
    $stmt = $db->prepare("
        SELECT 'attendance' as activity_type, a.attendance_date as activity_date, d.name as discipline_name, a.status
        FROM attendance a
        JOIN disciplines d ON a.discipline_id = d.id
        WHERE a.student_id = ?
        ORDER BY a.attendance_date DESC, a.recorded_at DESC
        LIMIT 5
    ");
    $stmt->execute([$user['id']]);
    $recentActivities = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SportTrack - Sistema de Gestión Deportiva</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/lucide/0.263.1/lucide.min.css" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
         
        <?php include 'components/sidebar.php'; ?>
        
         
        <div class="flex-1 flex flex-col overflow-hidden">
             
            <?php include 'components/header.php'; ?>
            
             
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100">
                <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
                    <h1 class="text-3xl font-bold text-gray-900 mb-6">Dashboard</h1>
                    
                     Estadísticas
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4 mb-8">
                        <?php foreach ($stats as $stat): ?>
                        <div class="bg-white overflow-hidden shadow rounded-lg">
                            <div class="p-5">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 <?php echo $stat['color']; ?> rounded-md p-3">
                                        <i data-lucide="<?php echo $stat['icon']; ?>" class="h-6 w-6 text-white"></i>
                                    </div>
                                    <div class="ml-5 w-0 flex-1">
                                        <dl>
                                            <dt class="text-sm font-medium text-gray-500 truncate"><?php echo $stat['name']; ?></dt>
                                            <dd>
                                                <div class="text-lg font-medium text-gray-900"><?php echo $stat['value']; ?></div>
                                            </dd>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <?php if ($userType === 'entrenador' && !empty($recentStudents)): ?>
                     
                    <div class="bg-white shadow rounded-lg mb-8">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Estudiantes Recientes</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estudiante</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Disciplina</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Inscrito</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <?php foreach ($recentStudents as $student): ?>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                                        <i data-lucide="user" class="w-4 h-4 text-blue-600"></i>
                                                    </div>
                                                    <div class="ml-3">
                                                        <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($student['full_name']); ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php echo htmlspecialchars($student['email']); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php echo htmlspecialchars($student['discipline_name']); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php echo date('d/m/Y', strtotime($student['enrolled_at'])); ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-4">
                                <a href="my-students.php" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                    Ver todos los estudiantes →
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                     
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Actividad Reciente</h3>
                            <div class="flow-root">
                                <ul class="-mb-8">
                                    <?php if ($userType === 'coordinador'): ?>
                                    <li>
                                        <div class="relative pb-8">
                                            <div class="relative flex space-x-3">
                                                <div class="flex-shrink-0">
                                                    <div class="h-8 w-8 rounded-full bg-green-500 flex items-center justify-center">
                                                        <i data-lucide="plus" class="h-4 w-4 text-white"></i>
                                                    </div>
                                                </div>
                                                <div class="min-w-0 flex-1">
                                                    <div>
                                                        <p class="text-sm text-gray-500">Nueva disciplina registrada</p>
                                                        <p class="text-sm font-medium text-gray-900">Hace 2 horas</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                    <?php elseif ($userType === 'entrenador'): ?>
                                        <!-- Display actual recent activities for trainers -->
                                        <?php if (!empty($recentActivities)): ?>
                                            <?php foreach ($recentActivities as $index => $activity): ?>
                                            <li>
                                                <div class="relative <?php echo $index < count($recentActivities) - 1 ? 'pb-8' : ''; ?>">
                                                    <?php if ($index < count($recentActivities) - 1): ?>
                                                    <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                                    <?php endif; ?>
                                                    <div class="relative flex space-x-3">
                                                        <div class="flex-shrink-0">
                                                            <div class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center">
                                                                <i data-lucide="check" class="h-4 w-4 text-white"></i>
                                                            </div>
                                                        </div>
                                                        <div class="min-w-0 flex-1">
                                                            <div>
                                                                <p class="text-sm text-gray-500">
                                                                    Asistencia registrada: <?php echo htmlspecialchars($activity['student_name']); ?> - <?php echo htmlspecialchars($activity['discipline_name']); ?>
                                                                </p>
                                                                <p class="text-sm font-medium text-gray-900">
                                                                    <?php 
                                                                    $date = new DateTime($activity['activity_date']);
                                                                    $now = new DateTime();
                                                                    $diff = $now->diff($date);
                                                                    
                                                                    if ($diff->days == 0) {
                                                                        echo "Hoy";
                                                                    } elseif ($diff->days == 1) {
                                                                        echo "Ayer";
                                                                    } else {
                                                                        echo "Hace " . $diff->days . " días";
                                                                    }
                                                                    ?>
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                        <li>
                                            <div class="text-center py-4 text-gray-500">
                                                <p class="text-sm">No hay actividad reciente</p>
                                            </div>
                                        </li>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <!-- Display actual recent activities for students -->
                                        <?php if (!empty($recentActivities)): ?>
                                            <?php foreach ($recentActivities as $index => $activity): ?>
                                            <li>
                                                <div class="relative <?php echo $index < count($recentActivities) - 1 ? 'pb-8' : ''; ?>">
                                                    <?php if ($index < count($recentActivities) - 1): ?>
                                                    <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                                    <?php endif; ?>
                                                    <div class="relative flex space-x-3">
                                                        <div class="flex-shrink-0">
                                                            <div class="h-8 w-8 rounded-full <?php echo $activity['status'] == 'present' ? 'bg-green-500' : 'bg-red-500'; ?> flex items-center justify-center">
                                                                <i data-lucide="<?php echo $activity['status'] == 'present' ? 'check' : 'x'; ?>" class="h-4 w-4 text-white"></i>
                                                            </div>
                                                        </div>
                                                        <div class="min-w-0 flex-1">
                                                            <div>
                                                                <p class="text-sm text-gray-500">
                                                                    <?php echo $activity['status'] == 'present' ? 'Asistencia registrada' : 'Ausencia registrada'; ?>: <?php echo htmlspecialchars($activity['discipline_name']); ?>
                                                                </p>
                                                                <p class="text-sm font-medium text-gray-900">
                                                                    <?php 
                                                                    $date = new DateTime($activity['activity_date']);
                                                                    $now = new DateTime();
                                                                    $diff = $now->diff($date);
                                                                    
                                                                    if ($diff->days == 0) {
                                                                        echo "Hoy";
                                                                    } elseif ($diff->days == 1) {
                                                                        echo "Ayer";
                                                                    } else {
                                                                        echo "Hace " . $diff->days . " días";
                                                                    }
                                                                    ?>
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                        <li>
                                            <div class="text-center py-4 text-gray-500">
                                                <p class="text-sm">No hay actividad reciente</p>
                                            </div>
                                        </li>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        // Initialize Lucide icons
        lucide.createIcons();
    </script>
</body>
</html>
