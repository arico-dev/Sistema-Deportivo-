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

// Procesar registro de asistencia
if (isset($_POST['action']) && $_POST['action'] === 'mark_attendance') {
    $discipline_id = $_POST['discipline_id'];
    $date = $_POST['date'];
    $attendance_data = $_POST['attendance'] ?? [];
    
    try {
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("DELETE FROM attendance WHERE discipline_id = ? AND attendance_date = ?");
        $stmt->execute([$discipline_id, $date]);
        
        // Insertar nuevos registros
        foreach ($attendance_data as $student_id => $status) {
            $stmt = $pdo->prepare("INSERT INTO attendance (student_id, discipline_id, trainer_id, attendance_date, status, recorded_at) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$student_id, $discipline_id, $user['id'], $date, $status]);
        }
        
        $pdo->commit();
        $success = "Asistencia registrada exitosamente";
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Error al registrar asistencia: " . $e->getMessage();
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

$stmt = $pdo->prepare("
    SELECT 
        u.full_name as student_name,
        d.name as discipline_name,
        a.attendance_date,
        a.status,
        a.recorded_at
    FROM attendance a
    JOIN users u ON a.student_id = u.id
    JOIN disciplines d ON a.discipline_id = d.id
    JOIN trainer_disciplines td ON d.id = td.discipline_id
    WHERE td.trainer_id = ?
    ORDER BY a.recorded_at DESC
    LIMIT 20
");
$stmt->execute([$user['id']]);
$recent_attendance = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asistencia - SportTrack</title>
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
                    <h3 class="text-gray-700 text-3xl font-medium">Control de Asistencia</h3>
                    
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
                    
                    <!-- Formulario de asistencia -->
                    <div class="mt-8 bg-white shadow-md rounded-lg p-6">
                        <h4 class="text-lg font-medium text-gray-900 mb-4">Registrar Asistencia</h4>
                        
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
                        <form method="POST">
                            <input type="hidden" name="action" value="mark_attendance">
                            <input type="hidden" name="discipline_id" value="<?php echo $selected_discipline; ?>">
                            
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700">Fecha</label>
                                <input type="date" name="date" value="<?php echo date('Y-m-d'); ?>" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            
                            <div class="space-y-4">
                                <h5 class="font-medium text-gray-900">Estudiantes</h5>
                                <?php foreach ($students as $student): ?>
                                <div class="flex items-center justify-between p-3 border rounded-lg">
                                    <span class="font-medium"><?php echo htmlspecialchars($student['full_name']); ?></span>
                                    <div class="flex space-x-4">
                                        <!-- Corrigiendo valores de status para coincidir con ENUM del esquema -->
                                        <label class="flex items-center">
                                            <input type="radio" name="attendance[<?php echo $student['id']; ?>]" value="present" class="mr-2">
                                            <span class="text-green-600">Presente</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="radio" name="attendance[<?php echo $student['id']; ?>]" value="absent" class="mr-2">
                                            <span class="text-red-600">Ausente</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="radio" name="attendance[<?php echo $student['id']; ?>]" value="late" class="mr-2">
                                            <span class="text-yellow-600">Tardanza</span>
                                        </label>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <div class="mt-6">
                                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                    Registrar Asistencia
                                </button>
                            </div>
                        </form>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Asistencia reciente -->
                    <div class="mt-8 bg-white shadow-md rounded-lg p-6">
                        <h4 class="text-lg font-medium text-gray-900 mb-4">Asistencia Reciente</h4>
                        
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estudiante</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Disciplina</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($recent_attendance as $record): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            <?php echo htmlspecialchars($record['student_name']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo htmlspecialchars($record['discipline_name']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php
                                            $status_colors = [
                                                'present' => 'bg-green-100 text-green-800',
                                                'absent' => 'bg-red-100 text-red-800',
                                                'late' => 'bg-yellow-100 text-yellow-800'
                                            ];
                                            $color_class = $status_colors[$record['status']] ?? 'bg-gray-100 text-gray-800';
                                            ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $color_class; ?>">
                                                <?php echo ucfirst($record['status']); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo date('d/m/Y', strtotime($record['attendance_date'])); ?>
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
