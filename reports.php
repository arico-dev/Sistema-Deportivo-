<?php
require_once 'config/session.php';
require_once 'config/database.php';

// Verificar que sea coordinador
if (getUserType() !== 'coordinador') {
    header('Location: index.php');
    exit();
}

$database = new Database();
$pdo = $database->getConnection();

// Obtener estadísticas generales
$stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE user_type = 'estudiante'");
$totalStudents = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE user_type = 'entrenador'");
$totalTrainers = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) as total FROM disciplines");
$totalDisciplines = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) as total FROM student_enrollments");
$totalEnrollments = $stmt->fetchColumn();

// Obtener inscripciones por disciplina
$stmt = $pdo->query("
    SELECT d.name, COUNT(e.id) as enrollments
    FROM disciplines d
    LEFT JOIN student_enrollments e ON d.id = e.discipline_id
    GROUP BY d.id, d.name
    ORDER BY enrollments DESC
");
$disciplineStats = $stmt->fetchAll();
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
    <div class="flex h-screen">
        <?php include 'components/sidebar.php'; ?>
        
        <div class="flex-1 flex flex-col overflow-hidden">
            <?php include 'components/header.php'; ?>
            
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">
                <div class="max-w-7xl mx-auto">
                    <div class="mb-6">
                        <h1 class="text-3xl font-bold text-gray-900">Reportes y Estadísticas</h1>
                        <p class="text-gray-600">Análisis detallado del sistema deportivo</p>
                    </div>

                    <!-- Estadísticas generales -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                        <div class="bg-white rounded-lg shadow-md p-6">
                            <div class="flex items-center">
                                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                    <i data-lucide="users" class="w-6 h-6 text-blue-600"></i>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-600">Total Estudiantes</p>
                                    <p class="text-2xl font-bold text-gray-900"><?php echo $totalStudents; ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-lg shadow-md p-6">
                            <div class="flex items-center">
                                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                                    <i data-lucide="user-check" class="w-6 h-6 text-green-600"></i>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-600">Total Entrenadores</p>
                                    <p class="text-2xl font-bold text-gray-900"><?php echo $totalTrainers; ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-lg shadow-md p-6">
                            <div class="flex items-center">
                                <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                                    <i data-lucide="activity" class="w-6 h-6 text-orange-600"></i>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-600">Disciplinas</p>
                                    <p class="text-2xl font-bold text-gray-900"><?php echo $totalDisciplines; ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-lg shadow-md p-6">
                            <div class="flex items-center">
                                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                                    <i data-lucide="user-plus" class="w-6 h-6 text-purple-600"></i>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-600">Inscripciones</p>
                                    <p class="text-2xl font-bold text-gray-900"><?php echo $totalEnrollments; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Gráfico de inscripciones por disciplina -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                        <div class="bg-white rounded-lg shadow-md p-6">
                            <h3 class="text-lg font-semibold mb-4">Inscripciones por Disciplina</h3>
                            <!-- Contenedor con altura fija para el gráfico -->
                            <div style="height: 300px; position: relative;">
                                <canvas id="disciplineChart"></canvas>
                            </div>
                        </div>

                        <div class="bg-white rounded-lg shadow-md p-6">
                            <h3 class="text-lg font-semibold mb-4">Ranking de Disciplinas</h3>
                            <div class="space-y-3">
                                <?php foreach ($disciplineStats as $index => $stat): ?>
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                    <div class="flex items-center">
                                        <span class="w-6 h-6 bg-orange-500 text-white text-xs rounded-full flex items-center justify-center mr-3">
                                            <?php echo $index + 1; ?>
                                        </span>
                                        <span class="font-medium"><?php echo htmlspecialchars($stat['name']); ?></span>
                                    </div>
                                    <span class="text-sm text-gray-600"><?php echo $stat['enrollments']; ?> estudiantes</span>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        lucide.createIcons();

        const ctx = document.getElementById('disciplineChart').getContext('2d');
        const disciplineChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_column($disciplineStats, 'name')); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_column($disciplineStats, 'enrollments')); ?>,
                    backgroundColor: [
                        '#f97316', '#3b82f6', '#10b981', '#8b5cf6', '#f59e0b', '#ef4444'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true
                        }
                    }
                },
                animation: {
                    duration: 1000,
                    easing: 'easeOutQuart'
                }
            }
        });

        let refreshTimeout;
        function resetRefreshTimer() {
            if (refreshTimeout) {
                clearTimeout(refreshTimeout);
            }
            // Solo refrescar si el usuario está inactivo por más de 10 minutos
            refreshTimeout = setTimeout(() => {
                if (confirm('¿Deseas actualizar los datos del reporte?')) {
                    location.reload();
                }
            }, 600000); // 10 minutos
        }

        // Resetear timer en interacciones del usuario
        document.addEventListener('click', resetRefreshTimer);
        document.addEventListener('keypress', resetRefreshTimer);
        
        // Inicializar timer
        resetRefreshTimer();
    </script>
</body>
</html>
