<?php
require_once 'config/session.php';
require_once 'config/database.php';

requireLogin();
requireUserType(['estudiante']);

$database = new Database();
$pdo = $database->getConnection();

$user = getCurrentUser();
$userId = $user['id'];

// Obtener las evaluaciones del estudiante
$stmt = $pdo->prepare("
    SELECT 
        e.evaluation_date,
        e.score,
        e.max_score,
        e.comments,
        e.session_type,
        d.name as discipline_name,
        u.full_name as trainer_name
    FROM evaluations e
    JOIN disciplines d ON e.discipline_id = d.id
    JOIN users u ON e.trainer_id = u.id
    WHERE e.student_id = ?
    ORDER BY e.evaluation_date DESC
    LIMIT 50
");
$stmt->execute([$userId]);
$evaluations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calcular estadísticas de rendimiento
$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_evaluations,
        AVG(score) as average_score,
        MAX(score) as best_score,
        MIN(score) as lowest_score,
        d.name as discipline_name
    FROM evaluations e
    JOIN disciplines d ON e.discipline_id = d.id
    WHERE e.student_id = ?
    GROUP BY e.discipline_id, d.name
");
$stmt->execute([$userId]);
$performanceStats = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calcular promedio general
$stmt = $pdo->prepare("
    SELECT AVG(score) as overall_average
    FROM evaluations 
    WHERE student_id = ?
");
$stmt->execute([$userId]);
$overallStats = $stmt->fetch(PDO::FETCH_ASSOC);
$overallAverage = $overallStats['overall_average'] ? round($overallStats['overall_average'], 1) : 0;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Rendimiento - SportTrack</title>
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
                        <h1 class="text-3xl font-bold text-gray-800">Mi Rendimiento</h1>
                        <p class="text-gray-600 mt-2">Evaluaciones y progreso en las actividades deportivas</p>
                    </div>

                     Promedio general 
                    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-2xl font-bold text-gray-800">Promedio General</h2>
                                <p class="text-gray-600">Tu rendimiento promedio en todas las disciplinas</p>
                            </div>
                            <div class="text-right">
                                <div class="text-4xl font-bold text-orange-600"><?php echo $overallAverage; ?></div>
                                <div class="text-sm text-gray-500">de 100 puntos</div>
                            </div>
                        </div>
                    </div>

                     Estadísticas por disciplina 
                    <?php if (!empty($performanceStats)): ?>
                        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3 mb-8">
                            <?php foreach ($performanceStats as $stat): ?>
                                <div class="bg-white rounded-lg shadow-md p-6">
                                    <h3 class="text-lg font-semibold text-gray-800 mb-4"><?php echo htmlspecialchars($stat['discipline_name']); ?></h3>
                                    <div class="space-y-3">
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Promedio:</span>
                                            <span class="font-semibold text-orange-600"><?php echo round($stat['average_score'], 1); ?></span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Mejor puntuación:</span>
                                            <span class="font-semibold text-green-600"><?php echo $stat['best_score']; ?></span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Evaluaciones:</span>
                                            <span class="font-semibold"><?php echo $stat['total_evaluations']; ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                     Historial de evaluaciones 
                    <div class="bg-white rounded-lg shadow-md">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-xl font-semibold text-gray-800">Historial de Evaluaciones</h2>
                        </div>
                        
                        <?php if (empty($evaluations)): ?>
                            <div class="p-8 text-center">
                                <div class="text-gray-400 mb-4">
                                    <i data-lucide="star" class="w-16 h-16 mx-auto"></i>
                                </div>
                                <h3 class="text-xl font-semibold text-gray-700 mb-2">No hay evaluaciones registradas</h3>
                                <p class="text-gray-500">Aún no tienes evaluaciones en el sistema.</p>
                            </div>
                        <?php else: ?>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Disciplina</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Puntuación</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Entrenador</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Comentarios</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <?php foreach ($evaluations as $evaluation): ?>
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    <?php echo date('d/m/Y', strtotime($evaluation['evaluation_date'])); ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    <?php echo htmlspecialchars($evaluation['discipline_name']); ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    <?php echo htmlspecialchars($evaluation['session_type'] ?? 'Evaluación'); ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <?php
                                                    $percentage = ($evaluation['score'] / $evaluation['max_score']) * 100;
                                                    $scoreClass = '';
                                                    if ($percentage >= 90) $scoreClass = 'text-green-600';
                                                    elseif ($percentage >= 70) $scoreClass = 'text-orange-600';
                                                    else $scoreClass = 'text-red-600';
                                                    ?>
                                                    <span class="text-sm font-semibold <?php echo $scoreClass; ?>">
                                                        <?php echo $evaluation['score']; ?>/<?php echo $evaluation['max_score']; ?>
                                                        (<?php echo round($percentage, 1); ?>%)
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    <?php echo htmlspecialchars($evaluation['trainer_name']); ?>
                                                </td>
                                                <td class="px-6 py-4 text-sm text-gray-900">
                                                    <?php echo htmlspecialchars($evaluation['comments'] ?? '-'); ?>
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
