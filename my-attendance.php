<?php
require_once 'config/session.php';
require_once 'config/database.php';

requireLogin();
requireUserType(['estudiante']);

$database = new Database();
$pdo = $database->getConnection();

$user = getCurrentUser();
$userId = $user['id'];

// Obtener el historial de asistencia del estudiante
$stmt = $pdo->prepare("
    SELECT 
        a.attendance_date,
        a.status,
        a.notes,
        d.name as discipline_name,
        u.full_name as trainer_name
    FROM attendance a
    JOIN disciplines d ON a.discipline_id = d.id
    JOIN users u ON a.trainer_id = u.id
    WHERE a.student_id = ?
    ORDER BY a.attendance_date DESC
    LIMIT 50
");
$stmt->execute([$userId]);
$attendanceRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calcular estadísticas de asistencia
$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_sessions,
        SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_count,
        SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_count,
        SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late_count
    FROM attendance 
    WHERE student_id = ?
");
$stmt->execute([$userId]);
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

$attendanceRate = $stats['total_sessions'] > 0 ? round(($stats['present_count'] / $stats['total_sessions']) * 100, 1) : 0;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Asistencia - SportTrack</title>
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
                    <div class="mb-6">
                        <h1 class="text-3xl font-bold text-gray-800">Mi Asistencia</h1>
                        <p class="text-gray-600 mt-2">Historial de asistencia a las actividades deportivas</p>
                    </div>

                     Estadísticas de asistencia 
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                        <div class="bg-white rounded-lg shadow-md p-6">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                                    <i data-lucide="calendar" class="w-6 h-6"></i>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-600">Total Sesiones</p>
                                    <p class="text-2xl font-bold text-gray-900"><?php echo $stats['total_sessions']; ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-lg shadow-md p-6">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-green-100 text-green-600">
                                    <i data-lucide="check-circle" class="w-6 h-6"></i>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-600">Presentes</p>
                                    <p class="text-2xl font-bold text-gray-900"><?php echo $stats['present_count']; ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-lg shadow-md p-6">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                                    <i data-lucide="clock" class="w-6 h-6"></i>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-600">Tardanzas</p>
                                    <p class="text-2xl font-bold text-gray-900"><?php echo $stats['late_count']; ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-lg shadow-md p-6">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-orange-100 text-orange-600">
                                    <i data-lucide="percent" class="w-6 h-6"></i>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-600">% Asistencia</p>
                                    <p class="text-2xl font-bold text-gray-900"><?php echo $attendanceRate; ?>%</p>
                                </div>
                            </div>
                        </div>
                    </div>

                     Historial de asistencia 
                    <div class="bg-white rounded-lg shadow-md">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-xl font-semibold text-gray-800">Historial de Asistencia</h2>
                        </div>
                        
                        <?php if (empty($attendanceRecords)): ?>
                            <div class="p-8 text-center">
                                <div class="text-gray-400 mb-4">
                                    <i data-lucide="calendar-x" class="w-16 h-16 mx-auto"></i>
                                </div>
                                <h3 class="text-xl font-semibold text-gray-700 mb-2">No hay registros de asistencia</h3>
                                <p class="text-gray-500">Aún no tienes registros de asistencia en el sistema.</p>
                            </div>
                        <?php else: ?>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Disciplina</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Entrenador</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notas</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <?php foreach ($attendanceRecords as $record): ?>
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    <?php echo date('d/m/Y', strtotime($record['attendance_date'])); ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    <?php echo htmlspecialchars($record['discipline_name']); ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    <?php echo htmlspecialchars($record['trainer_name']); ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <?php
                                                    $statusClass = '';
                                                    $statusText = '';
                                                    switch ($record['status']) {
                                                        case 'present':
                                                            $statusClass = 'bg-green-100 text-green-800';
                                                            $statusText = 'Presente';
                                                            break;
                                                        case 'absent':
                                                            $statusClass = 'bg-red-100 text-red-800';
                                                            $statusText = 'Ausente';
                                                            break;
                                                        case 'late':
                                                            $statusClass = 'bg-yellow-100 text-yellow-800';
                                                            $statusText = 'Tardanza';
                                                            break;
                                                    }
                                                    ?>
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $statusClass; ?>">
                                                        <?php echo $statusText; ?>
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 text-sm text-gray-900">
                                                    <?php echo htmlspecialchars($record['notes'] ?? '-'); ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
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
