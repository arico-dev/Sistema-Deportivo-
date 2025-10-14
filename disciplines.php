<?php
require_once 'config/session.php';
require_once 'config/database.php';

requireUserType(['coordinador']);

$database = new Database();
$db = $database->getConnection();

// Manejar creación de nueva disciplina
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'create') {
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    
    if ($name) {
        $stmt = $db->prepare("INSERT INTO disciplines (name, description, created_by) VALUES (?, ?, ?)");
        $stmt->execute([$name, $description, $_SESSION['user_id']]);
        $success = "Disciplina creada exitosamente";
    }
}

// Manejar edición de disciplina
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id = $_POST['discipline_id'] ?? '';
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    
    if ($id && $name) {
        $stmt = $db->prepare("UPDATE disciplines SET name = ?, description = ? WHERE id = ?");
        $stmt->execute([$name, $description, $id]);
        $success = "Disciplina actualizada exitosamente";
    }
}

// Manejar eliminación de disciplina
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id = $_POST['discipline_id'] ?? '';
    
    if ($id) {
        try {
            // Check if there are active enrollments
            $stmt = $db->prepare("SELECT COUNT(*) as count FROM student_enrollments WHERE discipline_id = ?");
            $stmt->execute([$id]);
            $enrollmentCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            if ($enrollmentCount > 0) {
                $error = "No se puede eliminar la disciplina porque tiene $enrollmentCount estudiante(s) inscrito(s). Primero debes dar de baja a todos los estudiantes de esta disciplina.";
            } else {
                // Delete trainer assignments first
                $stmt = $db->prepare("DELETE FROM trainer_disciplines WHERE discipline_id = ?");
                $stmt->execute([$id]);
                
                // Now delete the discipline
                $stmt = $db->prepare("DELETE FROM disciplines WHERE id = ?");
                $stmt->execute([$id]);
                $success = "Disciplina eliminada exitosamente";
            }
        } catch (PDOException $e) {
            $error = "Error al eliminar disciplina: " . $e->getMessage();
        }
    }
}

// Obtener todas las disciplinas
$stmt = $db->query("SELECT d.*, u.full_name as created_by_name FROM disciplines d LEFT JOIN users u ON d.created_by = u.id ORDER BY d.created_at DESC");
$disciplines = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Disciplinas - SportTrack</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <?php include 'components/sidebar.php'; ?>
        
        <div class="flex-1 flex flex-col overflow-hidden">
            <?php include 'components/header.php'; ?>
            
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100">
                <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
                    <div class="flex justify-between items-center mb-6">
                        <h1 class="text-3xl font-bold text-gray-900">Gestión de Disciplinas</h1>
                        <button onclick="openModal()" class="bg-gray-800 hover:bg-gray-700 text-white px-4 py-2 rounded-md flex items-center">
                            <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
                            Nueva Disciplina
                        </button>
                    </div>

                    <?php if (isset($success)): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        <?php echo $success; ?>
                    </div>
                    <?php endif; ?>

                    <?php if (isset($error)): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <?php echo $error; ?>
                    </div>
                    <?php endif; ?>

                    <!-- Lista de Disciplinas -->
                    <div class="bg-white shadow overflow-hidden sm:rounded-md">
                        <ul class="divide-y divide-gray-200">
                            <?php foreach ($disciplines as $discipline): ?>
                            <li>
                                <div class="px-4 py-4 sm:px-6">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0">
                                                <div class="h-10 w-10 bg-gray-800 rounded-full flex items-center justify-center">
                                                    <i data-lucide="activity" class="h-6 w-6 text-white"></i>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    <?php echo htmlspecialchars($discipline['name']); ?>
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    <?php echo htmlspecialchars($discipline['description']); ?>
                                                </div>
                                                <div class="text-xs text-gray-400">
                                                    Creado por: <?php echo htmlspecialchars($discipline['created_by_name']); ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <button onclick="openEditModal(<?php echo $discipline['id']; ?>, '<?php echo htmlspecialchars($discipline['name'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($discipline['description'], ENT_QUOTES); ?>')" class="text-indigo-600 hover:text-indigo-900 text-sm">Editar</button>
                                            <button onclick="deleteDiscipline(<?php echo $discipline['id']; ?>, '<?php echo htmlspecialchars($discipline['name'], ENT_QUOTES); ?>')" class="text-red-600 hover:text-red-900 text-sm">Eliminar</button>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal para Nueva Disciplina -->
    <div id="disciplineModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Nueva Disciplina</h3>
                <form method="POST">
                    <input type="hidden" name="action" value="create">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nombre</label>
                        <input type="text" name="name" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Descripción</label>
                        <textarea name="description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                    </div>
                    <div class="flex justify-end space-x-2">
                        <button type="button" onclick="closeModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">
                            Cancelar
                        </button>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-gray-800 rounded-md hover:bg-gray-700">
                            Crear Disciplina
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para Editar Disciplina -->
    <div id="editDisciplineModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Editar Disciplina</h3>
                    <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600">
                        <i data-lucide="x" class="w-6 h-6"></i>
                    </button>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="discipline_id" id="editDisciplineId">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nombre</label>
                        <input type="text" name="name" id="editDisciplineName" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Descripción</label>
                        <textarea name="description" id="editDisciplineDescription" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                    </div>
                    <div class="flex justify-end space-x-2">
                        <button type="button" onclick="closeEditModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">
                            Cancelar
                        </button>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-gray-800 rounded-md hover:bg-gray-700">
                            Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Formulario oculto para Eliminar Disciplina -->
    <form id="deleteDisciplineForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="discipline_id" id="deleteDisciplineId">
    </form>

    <script>
        lucide.createIcons();
        
        function openModal() {
            document.getElementById('disciplineModal').classList.remove('hidden');
        }
        
        function closeModal() {
            document.getElementById('disciplineModal').classList.add('hidden');
        }

        // Funciones para el modal de edición
        function openEditModal(id, name, description) {
            document.getElementById('editDisciplineId').value = id;
            document.getElementById('editDisciplineName').value = name;
            document.getElementById('editDisciplineDescription').value = description;
            document.getElementById('editDisciplineModal').classList.remove('hidden');
            lucide.createIcons();
        }

        function closeEditModal() {
            document.getElementById('editDisciplineModal').classList.add('hidden');
        }

        // Función para eliminar disciplina
        function deleteDiscipline(id, name) {
            if (confirm('¿Estás seguro de que quieres eliminar la disciplina "' + name + '"? Esta acción no se puede deshacer.')) {
                document.getElementById('deleteDisciplineId').value = id;
                document.getElementById('deleteDisciplineForm').submit();
            }
        }
    </script>
</body>
</html>
