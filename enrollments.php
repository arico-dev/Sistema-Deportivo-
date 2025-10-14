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

if (isset($_POST['action']) && $_POST['action'] === 'unenroll_student') {
    $stmt = $pdo->prepare("UPDATE student_enrollments SET status = 'inactivo' WHERE id = ?");
    $stmt->execute([$_POST['enrollment_id']]);
    $success = "Estudiante dado de baja exitosamente";
}

if (isset($_POST['action']) && $_POST['action'] === 'reactivate_enrollment') {
    $stmt = $pdo->prepare("UPDATE student_enrollments SET status = 'activo' WHERE id = ?");
    $stmt->execute([$_POST['enrollment_id']]);
    $success = "Inscripción reactivada exitosamente";
}

// Procesar inscripción
if (isset($_POST['action']) && $_POST['action'] === 'enroll_student') {
    $stmt = $pdo->prepare("INSERT INTO student_enrollments (student_id, discipline_id, trainer_id, enrolled_at) VALUES (?, ?, ?, NOW())");
    $stmt->execute([$_POST['student_id'], $_POST['discipline_id'], $_POST['trainer_id']]);
    $success = "Estudiante inscrito exitosamente";
}

// Obtener estudiantes
$stmt = $pdo->query("SELECT * FROM users WHERE user_type = 'estudiante' ORDER BY full_name");
$students = $stmt->fetchAll();

// Obtener disciplinas
$stmt = $pdo->query("SELECT * FROM disciplines ORDER BY name");
$disciplines = $stmt->fetchAll();

$stmt = $pdo->query("SELECT id, full_name FROM users WHERE user_type = 'entrenador' ORDER BY full_name");
$trainers = $stmt->fetchAll();

$stmt = $pdo->query("
    SELECT e.*, 
           u.full_name as student_name, 
           d.name as discipline_name, 
           d.description,
           t.full_name as trainer_name
    FROM student_enrollments e
    JOIN users u ON e.student_id = u.id
    JOIN disciplines d ON e.discipline_id = d.id
    LEFT JOIN users t ON e.trainer_id = t.id
    WHERE e.status = 'activo'
    ORDER BY e.enrolled_at DESC
");
$enrollments = $stmt->fetchAll();

$stmt = $pdo->query("
    SELECT e.*, 
           u.full_name as student_name, 
           d.name as discipline_name, 
           d.description,
           t.full_name as trainer_name
    FROM student_enrollments e
    JOIN users u ON e.student_id = u.id
    JOIN disciplines d ON e.discipline_id = d.id
    LEFT JOIN users t ON e.trainer_id = t.id
    WHERE e.status = 'inactivo'
    ORDER BY e.enrolled_at DESC
");
$inactive_enrollments = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscripciones - SportTrack</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <?php include 'components/sidebar.php'; ?>
        
        <div class="flex-1 flex flex-col overflow-hidden">
            <?php include 'components/header.php'; ?>
            
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">
                <div class="max-w-7xl mx-auto">
                    <div class="mb-6">
                        <h1 class="text-3xl font-bold text-gray-900">Inscripciones</h1>
                        <p class="text-gray-600">Gestiona las inscripciones de estudiantes en disciplinas deportivas</p>
                    </div>

                    <?php if (isset($success)): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                        <?php echo $success; ?>
                    </div>
                    <?php endif; ?>

                    <!-- Formulario de inscripción -->
                    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                        <h2 class="text-xl font-semibold mb-4">Nueva Inscripción</h2>
                        <!-- Improved form layout to align trainer selector properly -->
                        <form method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <input type="hidden" name="action" value="enroll_student">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Estudiante</label>
                                <select name="student_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500">
                                    <option value="">Seleccionar estudiante</option>
                                    <?php foreach ($students as $student): ?>
                                    <option value="<?php echo $student['id']; ?>"><?php echo htmlspecialchars($student['full_name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Disciplina</label>
                                <select name="discipline_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500">
                                    <option value="">Seleccionar disciplina</option>
                                    <?php foreach ($disciplines as $discipline): ?>
                                    <option value="<?php echo $discipline['id']; ?>"><?php echo htmlspecialchars($discipline['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Entrenador</label>
                                <select name="trainer_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500">
                                    <option value="">Seleccionar entrenador</option>
                                    <?php foreach ($trainers as $trainer): ?>
                                    <option value="<?php echo $trainer['id']; ?>"><?php echo htmlspecialchars($trainer['full_name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="md:col-span-3">
                                <button type="submit" class="bg-orange-500 text-white px-6 py-2 rounded-md hover:bg-orange-600 transition-colors">
                                    <i data-lucide="user-plus" class="inline-block w-4 h-4 mr-2"></i>
                                    Inscribir
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Lista de inscripciones activas -->
                    <div class="bg-white rounded-lg shadow-md">
                        <div class="p-6 border-b border-gray-200">
                            <h2 class="text-xl font-semibold">Inscripciones Activas</h2>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estudiante</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Disciplina</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Entrenador</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha Inscripción</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($enrollments as $enrollment): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                                    <i data-lucide="user" class="w-5 h-5 text-green-600"></i>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($enrollment['student_name']); ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($enrollment['discipline_name']); ?></div>
                                            <div class="text-sm text-gray-500"><?php echo htmlspecialchars($enrollment['description']); ?></div>
                                        </td>
                                        <!-- Display trainer name -->
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo htmlspecialchars($enrollment['trainer_name'] ?? 'Sin asignar'); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo date('d/m/Y', strtotime($enrollment['enrolled_at'])); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full"><?php echo ucfirst($enrollment['status']); ?></span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <!-- Added onclick handler for unenroll button -->
                                            <button onclick="unenrollStudent(<?php echo $enrollment['id']; ?>, '<?php echo htmlspecialchars($enrollment['student_name'], ENT_QUOTES); ?>')" class="text-red-600 hover:text-red-900">Dar de baja</button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Nueva sección para inscripciones inactivas -->
                    <?php if (count($inactive_enrollments) > 0): ?>
                    <div class="bg-white rounded-lg shadow-md mt-6">
                        <div class="p-6 border-b border-gray-200">
                            <h2 class="text-xl font-semibold">Inscripciones Inactivas</h2>
                            <p class="text-sm text-gray-600 mt-1">Estudiantes dados de baja que pueden ser reactivados</p>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estudiante</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Disciplina</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Entrenador</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha Inscripción</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($inactive_enrollments as $enrollment): ?>
                                    <tr class="bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center">
                                                    <i data-lucide="user" class="w-5 h-5 text-gray-500"></i>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-700"><?php echo htmlspecialchars($enrollment['student_name']); ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-700"><?php echo htmlspecialchars($enrollment['discipline_name']); ?></div>
                                            <div class="text-sm text-gray-500"><?php echo htmlspecialchars($enrollment['description']); ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo htmlspecialchars($enrollment['trainer_name'] ?? 'Sin asignar'); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo date('d/m/Y', strtotime($enrollment['enrolled_at'])); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <button onclick="reactivateEnrollment(<?php echo $enrollment['id']; ?>, '<?php echo htmlspecialchars($enrollment['student_name'], ENT_QUOTES); ?>')" class="text-green-600 hover:text-green-900">
                                                <i data-lucide="rotate-ccw" class="inline-block w-4 h-4 mr-1"></i>
                                                Reactivar
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>

    <!-- Added hidden unenroll form -->
    <form id="unenrollForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="unenroll_student">
        <input type="hidden" name="enrollment_id" id="unenrollEnrollmentId">
    </form>

    <!-- Agregar formulario para reactivar inscripciones -->
    <form id="reactivateForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="reactivate_enrollment">
        <input type="hidden" name="enrollment_id" id="reactivateEnrollmentId">
    </form>

    <script>
        lucide.createIcons();

        function unenrollStudent(enrollmentId, studentName) {
            if (confirm('¿Estás seguro de que quieres dar de baja al estudiante "' + studentName + '"?')) {
                document.getElementById('unenrollEnrollmentId').value = enrollmentId;
                document.getElementById('unenrollForm').submit();
            }
        }

        function reactivateEnrollment(enrollmentId, studentName) {
            if (confirm('¿Estás seguro de que quieres reactivar la inscripción de "' + studentName + '"?')) {
                document.getElementById('reactivateEnrollmentId').value = enrollmentId;
                document.getElementById('reactivateForm').submit();
            }
        }
    </script>
</body>
</html>
