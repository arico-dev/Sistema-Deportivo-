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

// Obtener ID de evaluación
$evaluation_id = $_GET['id'] ?? 0;

// Obtener detalles de la evaluación
$stmt = $pdo->prepare("
    SELECT e.*, 
           u.full_name as student_name, 
           u.email as student_email,
           d.name as discipline_name,
           t.full_name as trainer_name
    FROM evaluations e
    JOIN users u ON e.student_id = u.id
    JOIN disciplines d ON e.discipline_id = d.id
    JOIN users t ON e.trainer_id = t.id
    WHERE e.id = ? AND e.trainer_id = ?
");
$stmt->execute([$evaluation_id, $user['id']]);
$evaluation = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$evaluation) {
    header('Location: evaluations.php');
    exit();
}

$percentage = ($evaluation['score'] / $evaluation['max_score']) * 100;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle de Evaluación - SportTrack</title>
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
                    <div class="flex items-center mb-6">
                        <a href="evaluations.php" class="text-blue-600 hover:text-blue-800 mr-4">
                            <i data-lucide="arrow-left" class="w-6 h-6"></i>
                        </a>
                        <h3 class="text-gray-700 text-3xl font-medium">Detalle de Evaluación</h3>
                    </div>
                    
                    <div class="bg-white shadow-md rounded-lg p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Información del estudiante -->
                            <div>
                                <h4 class="text-lg font-medium text-gray-900 mb-4">Información del Estudiante</h4>
                                <div class="space-y-3">
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Nombre</label>
                                        <p class="text-gray-900"><?php echo htmlspecialchars($evaluation['student_name']); ?></p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Email</label>
                                        <p class="text-gray-900"><?php echo htmlspecialchars($evaluation['student_email']); ?></p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Disciplina</label>
                                        <p class="text-gray-900"><?php echo htmlspecialchars($evaluation['discipline_name']); ?></p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Información de la evaluación -->
                            <div>
                                <h4 class="text-lg font-medium text-gray-900 mb-4">Detalles de la Evaluación</h4>
                                <div class="space-y-3">
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Tipo de Evaluación</label>
                                        <p class="text-gray-900"><?php echo ucfirst($evaluation['session_type']); ?></p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Fecha de Evaluación</label>
                                        <p class="text-gray-900"><?php echo date('d/m/Y', strtotime($evaluation['evaluation_date'])); ?></p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Entrenador</label>
                                        <p class="text-gray-900"><?php echo htmlspecialchars($evaluation['trainer_name']); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Puntuación -->
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <h4 class="text-lg font-medium text-gray-900 mb-4">Puntuación</h4>
                            <div class="flex items-center space-x-4">
                                <div class="flex-1">
                                    <div class="flex justify-between mb-2">
                                        <span class="text-sm font-medium text-gray-700">Resultado</span>
                                        <span class="text-sm font-medium text-gray-700"><?php echo number_format($percentage, 1); ?>%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-4">
                                        <div class="<?php echo $percentage >= 80 ? 'bg-green-600' : ($percentage >= 60 ? 'bg-yellow-600' : 'bg-red-600'); ?> h-4 rounded-full" style="width: <?php echo $percentage; ?>%"></div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-3xl font-bold <?php echo $percentage >= 80 ? 'text-green-600' : ($percentage >= 60 ? 'text-yellow-600' : 'text-red-600'); ?>">
                                        <?php echo $evaluation['score']; ?>/<?php echo $evaluation['max_score']; ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Comentarios -->
                        <?php if ($evaluation['comments']): ?>
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <h4 class="text-lg font-medium text-gray-900 mb-4">Comentarios</h4>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-gray-700"><?php echo nl2br(htmlspecialchars($evaluation['comments'])); ?></p>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Acciones -->
                        <div class="mt-6 pt-6 border-t border-gray-200 flex justify-end space-x-3">
                            <a href="evaluations.php" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">
                                Volver
                            </a>
                        </div>
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
