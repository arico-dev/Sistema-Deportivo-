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
    SELECT COUNT(DISTINCT se.student_id) as total_students,
           COUNT(DISTINCT se.discipline_id) as total_disciplines
    FROM student_enrollments se
    WHERE se.trainer_id = ? AND se.status = 'activo'
");
$stmt->execute([$user['id']]);
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("
    SELECT COUNT(a.id) as total_attendance_records,
           COUNT(CASE WHEN a.status = 'present' THEN 1 END) as present_count
    FROM attendance a
    JOIN student_enrollments se ON a.student_id = se.student_id AND a.discipline_id = se.discipline_id
    WHERE se.trainer_id = ?
");
$stmt->execute([$user['id']]);
$attendance_stats = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("
    SELECT AVG(e.score) as avg_score
    FROM evaluations e
    JOIN student_enrollments se ON e.student_id = se.student_id AND e.discipline_id = se.discipline_id
    WHERE se.trainer_id = ?
");
$stmt->execute([$user['id']]);
$eval_stats = $stmt->fetch(PDO::FETCH_ASSOC);

// Calcular porcentaje de asistencia
$attendance_percentage = ($attendance_stats['total_attendance_records'] ?? 0) > 0 ? 
    (($attendance_stats['present_count'] ?? 0) / $attendance_stats['total_attendance_records']) * 100 : 0;

$stmt = $pdo->prepare("
    SELECT 
        d.name as discipline_name,
        COUNT(DISTINCT se.student_id) as student_count,
        COUNT(a.id) as attendance_records,
        COUNT(CASE WHEN a.status = 'present' THEN 1 END) as present_records,
        COUNT(e.id) as evaluation_count,
        AVG(e.score) as avg_score
    FROM student_enrollments se
    JOIN disciplines d ON se.discipline_id = d.id
    LEFT JOIN attendance a ON se.student_id = a.student_id AND se.discipline_id = a.discipline_id
    LEFT JOIN evaluations e ON se.student_id = e.student_id AND e.discipline_id = d.id
    WHERE se.trainer_id = ? AND se.status = 'activo'
    GROUP BY d.id, d.name
    ORDER BY d.name
");
$stmt->execute([$user['id']]);
$discipline_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("
    SELECT 
        u.full_name,
        d.name as discipline_name,
        COUNT(CASE WHEN a.status = 'present' THEN 1 END) as present_count,
        COUNT(a.id) as total_attendance,
        AVG(e.score) as avg_score,
        COUNT(e.id) as evaluation_count
    FROM users u
    JOIN student_enrollments se ON u.id = se.student_id
    JOIN disciplines d ON se.discipline_id = d.id
    LEFT JOIN attendance a ON u.id = a.student_id AND a.discipline_id = d.id
    LEFT JOIN evaluations e ON u.id = e.student_id AND e.discipline_id = d.id
    WHERE se.trainer_id = ? AND u.user_type = 'estudiante' AND se.status = 'activo'
    GROUP BY u.id, d.id
    HAVING evaluation_count > 0
    ORDER BY avg_score DESC
    LIMIT 10
");
$stmt->execute([$user['id']]);
$top_students = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("
    SELECT 
        DATE_FORMAT(a.attendance_date, '%Y-%m') as month,
        COUNT(CASE WHEN a.status = 'present' THEN 1 END) as present_count,
        COUNT(a.id) as total_count
    FROM attendance a
    JOIN student_enrollments se ON a.student_id = se.student_id AND a.discipline_id = se.discipline_id
    WHERE se.trainer_id = ? AND a.attendance_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(a.attendance_date, '%Y-%m')
    ORDER BY month ASC
");
$stmt->execute([$user['id']]);
$monthly_attendance = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes - SportTrack</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen bg-gray-100">
        <?php include 'components/sidebar.php'; ?>
        
        <div class="flex-1 flex flex-col overflow-hidden">
            <?php include 'components/header.php'; ?>
            
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100">
                <div class="container mx-auto px-6 py-8">
                    <h3 class="text-gray-700 text-3xl font-medium">Reportes de Entrenador</h3>
                    
                    <!-- Estadísticas generales -->
                    <div class="mt-4">
                        <div class="flex flex-wrap -mx-6">
                            <div class="w-full px-6 sm:w-1/2 xl:w-1/4">
                                <div class="flex items-center px-5 py-6 shadow-sm rounded-md bg-white">
                                    <div class="p-3 rounded-full bg-indigo-600 bg-opacity-75">
                                        <i data-lucide="users" class="h-8 w-8 text-white"></i>
                                    </div>
                                    <div class="mx-5">
                                        <h4 class="text-2xl font-semibold text-gray-700"><?php echo $stats['total_students'] ?? 0; ?></h4>
                                        <div class="text-gray-500">Estudiantes</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="w-full mt-6 px-6 sm:w-1/2 xl:w-1/4 sm:mt-0">
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
                            
                            <div class="w-full mt-6 px-6 sm:w-1/2 xl:w-1/4 xl:mt-0">
                                <div class="flex items-center px-5 py-6 shadow-sm rounded-md bg-white">
                                    <div class="p-3 rounded-full bg-blue-600 bg-opacity-75">
                                        <i data-lucide="percent" class="h-8 w-8 text-white"></i>
                                    </div>
                                    <div class="mx-5">
                                        <h4 class="text-2xl font-semibold text-gray-700"><?php echo number_format($attendance_percentage, 1); ?>%</h4>
                                        <div class="text-gray-500">Asistencia</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="w-full mt-6 px-6 sm:w-1/2 xl:w-1/4 xl:mt-0">
                                <div class="flex items-center px-5 py-6 shadow-sm rounded-md bg-white">
                                    <div class="p-3 rounded-full bg-yellow-600 bg-opacity-75">
                                        <i data-lucide="star" class="h-8 w-8 text-white"></i>
                                    </div>
                                    <div class="mx-5">
                                        <h4 class="text-2xl font-semibold text-gray-700"><?php echo number_format($eval_stats['avg_score'] ?? 0, 1); ?></h4>
                                        <div class="text-gray-500">Promedio Evaluaciones</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Gráfico de asistencia mensual -->
                    <div class="mt-8 bg-white shadow-md rounded-lg p-6">
                        <h4 class="text-lg font-medium text-gray-900 mb-4">Asistencia por Mes</h4>
                        <canvas id="attendanceChart" width="400" height="200"></canvas>
                    </div>
                    
                    <!-- Rendimiento por disciplina -->
                    <div class="mt-8 bg-white shadow-md rounded-lg p-6">
                        <h4 class="text-lg font-medium text-gray-900 mb-4">Rendimiento por Disciplina</h4>
                        
                        <?php if (empty($discipline_stats)): ?>
                        <div class="text-center py-8 text-gray-500">
                            <i data-lucide="info" class="w-12 h-12 mx-auto mb-3 text-gray-400"></i>
                            <p>No hay datos de disciplinas disponibles</p>
                        </div>
                        <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Disciplina</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estudiantes</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Asistencia</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Evaluaciones</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Promedio</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($discipline_stats as $discipline): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            <?php echo htmlspecialchars($discipline['discipline_name']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo $discipline['student_count']; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php 
                                            $attendance_rate = $discipline['attendance_records'] > 0 ? 
                                                ($discipline['present_records'] / $discipline['attendance_records']) * 100 : 0;
                                            echo number_format($attendance_rate, 1) . '%';
                                            ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo $discipline['evaluation_count']; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo number_format($discipline['avg_score'] ?? 0, 1); ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Mejores estudiantes -->
                    <div class="mt-8 bg-white shadow-md rounded-lg p-6">
                        <h4 class="text-lg font-medium text-gray-900 mb-4">Estudiantes Destacados</h4>
                        
                        <?php if (empty($top_students)): ?>
                        <div class="text-center py-8 text-gray-500">
                            <i data-lucide="award" class="w-12 h-12 mx-auto mb-3 text-gray-400"></i>
                            <p>No hay evaluaciones registradas aún</p>
                        </div>
                        <?php else: ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <?php foreach (array_slice($top_students, 0, 6) as $student): ?>
                            <div class="border rounded-lg p-4">
                                <h5 class="font-medium text-gray-900"><?php echo htmlspecialchars($student['full_name']); ?></h5>
                                <p class="text-sm text-gray-500"><?php echo htmlspecialchars($student['discipline_name']); ?></p>
                                <div class="mt-2">
                                    <div class="flex justify-between text-sm">
                                        <span>Promedio:</span>
                                        <span class="font-medium text-green-600"><?php echo number_format($student['avg_score'], 1); ?></span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span>Asistencia:</span>
                                        <span><?php echo $student['total_attendance'] > 0 ? number_format(($student['present_count'] / $student['total_attendance']) * 100, 1) : 0; ?>%</span>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script>
        lucide.createIcons();
        
        const ctx = document.getElementById('attendanceChart').getContext('2d');
        const attendanceData = <?php echo json_encode($monthly_attendance); ?>;
        
        const labels = attendanceData.map(item => {
            const date = new Date(item.month + '-01');
            return date.toLocaleDateString('es-ES', { year: 'numeric', month: 'short' });
        });
        
        const presentData = attendanceData.map(item => 
            item.total_count > 0 ? (item.present_count / item.total_count * 100) : 0
        );
        
        const totalData = attendanceData.map(item => parseInt(item.total_count));
        
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        type: 'line',
                        label: 'Porcentaje de Asistencia (%)',
                        data: presentData,
                        borderColor: 'rgb(59, 130, 246)',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        borderWidth: 3,
                        tension: 0.4,
                        fill: false,
                        pointRadius: 6,
                        pointHoverRadius: 8,
                        pointBackgroundColor: 'rgb(59, 130, 246)',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        yAxisID: 'y-percentage',
                        order: 1
                    },
                    {
                        type: 'bar',
                        label: 'Total de Asistencias',
                        data: totalData,
                        backgroundColor: 'rgba(34, 197, 94, 0.6)',
                        borderColor: 'rgb(34, 197, 94)',
                        borderWidth: 1,
                        yAxisID: 'y-count',
                        order: 2
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.dataset.yAxisID === 'y-percentage') {
                                    label += context.parsed.y.toFixed(1) + '%';
                                } else {
                                    label += context.parsed.y;
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    'y-percentage': {
                        type: 'linear',
                        position: 'left',
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        title: {
                            display: true,
                            text: 'Porcentaje (%)'
                        }
                    },
                    'y-count': {
                        type: 'linear',
                        position: 'right',
                        beginAtZero: true,
                        grid: {
                            drawOnChartArea: false
                        },
                        title: {
                            display: true,
                            text: 'Total de Asistencias'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                },
                interaction: {
                    mode: 'index',
                    intersect: false
                }
            }
        });
    </script>
</body>
</html>
