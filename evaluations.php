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

$success = '';
$error = '';

// Procesar nueva evaluación
if (isset($_POST['action']) && $_POST['action'] === 'add_evaluation') {
    $student_id = $_POST['student_id'];
    $discipline_id = $_POST['discipline_id'];
    $session_type = $_POST['evaluation_type']; // Corrigiendo variable name
    $score = $_POST['score'];
    $max_score = $_POST['max_score'];
    $comments = $_POST['comments'];
    $evaluation_date = $_POST['evaluation_date'];
    
    $stmt = $pdo->prepare("INSERT INTO evaluations (student_id, discipline_id, trainer_id, session_type, score, max_score, comments, evaluation_date, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    
    if ($stmt->execute([$student_id, $discipline_id, $user['id'], $session_type, $score, $max_score, $comments, $evaluation_date])) {
        $success = "Evaluación registrada exitosamente";
    } else {
        $error = "Error al registrar la evaluación";
    }
}

// Obtener disciplinas del entrenador
$stmt = $pdo->prepare("
    SELECT d.id, d.name 
    FROM disciplines d
    JOIN trainer_disciplines td ON d.id = td.discipline_id
    WHERE td.trainer_id = ?
");
$stmt->execute([$user['id']]);
$disciplines = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener estudiantes de una disciplina específica
$selected_discipline = $_GET['discipline_id'] ?? '';
$students = [];
if ($selected_discipline) {
    $stmt = $pdo->prepare("
        SELECT u.id, u.full_name 
        FROM users u
        JOIN student_enrollments se ON u.id = se.student_id
        WHERE se.discipline_id = ? AND u.user_type = 'estudiante'
        ORDER BY u.full_name
    ");
    $stmt->execute([$selected_discipline]);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Obtener evaluaciones recientes
$stmt = $pdo->prepare("
    SELECT e.*, u.full_name, d.name as discipline_name
    FROM evaluations e
    JOIN users u ON e.student_id = u.id
    JOIN disciplines d ON e.discipline_id = d.id
    WHERE e.trainer_id = ?
    ORDER BY e.created_at DESC
    LIMIT 20
");
$stmt->execute([$user['id']]);
$recent_evaluations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evaluaciones - SportTrack</title>
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
                    <h3 class="text-gray-700 text-3xl font-medium">Evaluaciones</h3>
                    
                    <?php if ($success): ?>
                    <div class="mt-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                        <?php echo $success; ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($error): ?>
                    <div class="mt-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                        <?php echo $error; ?>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Formulario de evaluación -->
                    <div class="mt-8 bg-white shadow-md rounded-lg p-6">
                        <h4 class="text-lg font-medium text-gray-900 mb-4">Nueva Evaluación</h4>
                        
                        <form method="GET" class="mb-6">
                            <div class="flex items-center space-x-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Disciplina</label>
                                    <select name="discipline_id" onchange="this.form.submit()" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">Seleccionar disciplina</option>
                                        <?php foreach ($disciplines as $discipline): ?>
                                        <option value="<?php echo $discipline['id']; ?>" <?php echo $selected_discipline == $discipline['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($discipline['name']); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </form>
                        
                        <?php if ($selected_discipline && !empty($students)): ?>
                        <form method="POST" class="space-y-4">
                            <input type="hidden" name="action" value="add_evaluation">
                            <input type="hidden" name="discipline_id" value="<?php echo $selected_discipline; ?>">
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Estudiante</label>
                                    <select name="student_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">Seleccionar estudiante</option>
                                        <?php foreach ($students as $student): ?>
                                        <option value="<?php echo $student['id']; ?>"><?php echo htmlspecialchars($student['full_name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Tipo de Evaluación</label>
                                    <select name="evaluation_type" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">Seleccionar tipo</option>
                                        <option value="tecnica">Técnica</option>
                                        <option value="fisica">Física</option>
                                        <option value="tactica">Táctica</option>
                                        <option value="mental">Mental</option>
                                        <option value="general">General</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Puntuación</label>
                                    <input type="number" name="score" min="0" step="0.1" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Puntuación Máxima</label>
                                    <input type="number" name="max_score" min="1" step="0.1" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Fecha de Evaluación</label>
                                    <input type="date" name="evaluation_date" value="<?php echo date('Y-m-d'); ?>" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Comentarios</label>
                                <textarea name="comments" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                            </div>
                            
                            <div>
                                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                    Registrar Evaluación
                                </button>
                            </div>
                        </form>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Evaluaciones recientes -->
                    <div class="mt-8 bg-white shadow-md rounded-lg p-6">
                        <h4 class="text-lg font-medium text-gray-900 mb-4">Evaluaciones Recientes</h4>
                        
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estudiante</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Disciplina</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Puntuación</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($recent_evaluations as $evaluation): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            <?php echo htmlspecialchars($evaluation['full_name']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo htmlspecialchars($evaluation['discipline_name']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo ucfirst($evaluation['session_type']); ?> <!-- Corrigiendo variable name -->
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php 
                                            $percentage = ($evaluation['score'] / $evaluation['max_score']) * 100;
                                            $color_class = $percentage >= 80 ? 'text-green-600' : ($percentage >= 60 ? 'text-yellow-600' : 'text-red-600');
                                            ?>
                                            <span class="text-sm font-medium <?php echo $color_class; ?>">
                                                <?php echo $evaluation['score']; ?>/<?php echo $evaluation['max_score']; ?>
                                                (<?php echo number_format($percentage, 1); ?>%)
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo date('d/m/Y', strtotime($evaluation['evaluation_date'])); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <!-- Added proper link to view evaluation details -->
                                            <a href="evaluation-detail.php?id=<?php echo $evaluation['id']; ?>" class="text-indigo-600 hover:text-indigo-900">Ver Detalle</a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
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
