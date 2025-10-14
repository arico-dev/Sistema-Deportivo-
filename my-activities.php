<?php
require_once 'config/session.php';
require_once 'config/database.php';

requireLogin();
requireUserType(['estudiante']);

$database = new Database();
$pdo = $database->getConnection();

$user = getCurrentUser();
$userId = $user['id'];

// Obtener las actividades/disciplinas del estudiante
$stmt = $pdo->prepare("
    SELECT 
        d.name as discipline_name,
        d.description,
        u.full_name as trainer_name,
        se.enrolled_at,
        se.status,
        se.discipline_id
    FROM student_enrollments se
    JOIN disciplines d ON se.discipline_id = d.id
    LEFT JOIN users u ON se.trainer_id = u.id
    WHERE se.student_id = ? AND se.status = 'activo'
    ORDER BY se.enrolled_at DESC
");
$stmt->execute([$userId]);
$activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Actividades - SportTrack</title>
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
                        <h1 class="text-3xl font-bold text-gray-800">Mis Actividades</h1>
                        <p class="text-gray-600 mt-2">Disciplinas deportivas en las que estás inscrito</p>
                    </div>

                    <?php if (empty($activities)): ?>
                        <div class="bg-white rounded-lg shadow-md p-8 text-center">
                            <div class="text-gray-400 mb-4">
                                <i data-lucide="activity" class="w-16 h-16 mx-auto"></i>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-700 mb-2">No tienes actividades registradas</h3>
                            <p class="text-gray-500">Contacta al coordinador deportivo para inscribirte en disciplinas.</p>
                        </div>
                    <?php else: ?>
                        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                            <?php foreach ($activities as $activity): ?>
                                <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
                                    <div class="flex items-center justify-between mb-4">
                                        <h3 class="text-xl font-semibold text-gray-800"><?php echo htmlspecialchars($activity['discipline_name']); ?></h3>
                                        <span class="px-3 py-1 bg-green-100 text-green-800 text-sm rounded-full">
                                            <?php echo ucfirst($activity['status']); ?>
                                        </span>
                                    </div>
                                    
                                    <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($activity['description']); ?></p>
                                    
                                    <div class="space-y-2 text-sm text-gray-500">
                                        <div class="flex items-center">
                                            <i data-lucide="user" class="w-4 h-4 mr-2"></i>
                                            <span>Entrenador: <?php echo htmlspecialchars($activity['trainer_name'] ?? 'Sin asignar'); ?></span>
                                        </div>
                                        <div class="flex items-center">
                                            <i data-lucide="calendar" class="w-4 h-4 mr-2"></i>
                                            <span>Inscrito: <?php echo date('d/m/Y', strtotime($activity['enrolled_at'])); ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
