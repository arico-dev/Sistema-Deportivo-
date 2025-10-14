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

if (isset($_POST['action']) && $_POST['action'] === 'edit_trainer') {
    $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, username = ? WHERE id = ? AND user_type = 'entrenador'");
    $stmt->execute([$_POST['full_name'], $_POST['email'], $_POST['username'], $_POST['trainer_id']]);
    $success = "Entrenador actualizado exitosamente";
}

if (isset($_POST['action']) && $_POST['action'] === 'delete_trainer') {
    try {
        $stmt = $pdo->prepare("UPDATE student_enrollments SET trainer_id = NULL WHERE trainer_id = ?");
        $stmt->execute([$_POST['trainer_id']]);
        
        // Delete trainer's discipline assignments
        $stmt = $pdo->prepare("DELETE FROM trainer_disciplines WHERE trainer_id = ?");
        $stmt->execute([$_POST['trainer_id']]);
        
        $stmt = $pdo->prepare("UPDATE attendance SET trainer_id = NULL WHERE trainer_id = ?");
        $stmt->execute([$_POST['trainer_id']]);
        
        $stmt = $pdo->prepare("UPDATE evaluations SET trainer_id = NULL WHERE trainer_id = ?");
        $stmt->execute([$_POST['trainer_id']]);
        
        // Then delete the trainer
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND user_type = 'entrenador'");
        $stmt->execute([$_POST['trainer_id']]);
        $success = "Entrenador eliminado exitosamente";
    } catch (PDOException $e) {
        $error = "Error al eliminar entrenador: " . $e->getMessage();
    }
}

if (isset($_POST['action']) && $_POST['action'] === 'assign_discipline') {
    $trainer_id = $_POST['trainer_id'];
    $discipline_id = $_POST['discipline_id'];
    
    try {
        $stmt = $pdo->prepare("INSERT INTO trainer_disciplines (trainer_id, discipline_id) VALUES (?, ?)");
        $stmt->execute([$trainer_id, $discipline_id]);
        $success = "Disciplina asignada exitosamente";
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) { // Duplicate entry
            $error = "El entrenador ya tiene asignada esta disciplina";
        } else {
            $error = "Error al asignar disciplina";
        }
    }
}

if (isset($_POST['action']) && $_POST['action'] === 'remove_assignment') {
    $trainer_id = $_POST['trainer_id'];
    $discipline_id = $_POST['discipline_id'];
    
    $stmt = $pdo->prepare("DELETE FROM trainer_disciplines WHERE trainer_id = ? AND discipline_id = ?");
    $stmt->execute([$trainer_id, $discipline_id]);
    $success = "Asignación eliminada exitosamente";
}

// Procesar formulario de nuevo entrenador
if (isset($_POST['action']) && $_POST['action'] === 'add_trainer') {
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users WHERE username = ?");
    $stmt->execute([$_POST['username']]);
    $usernameExists = $stmt->fetch(PDO::FETCH_ASSOC)['count'] > 0;
    
    if ($usernameExists) {
        $error = "El nombre de usuario '{$_POST['username']}' ya está en uso. Por favor elige otro.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO users (username, password, full_name, email, user_type) VALUES (?, ?, ?, ?, 'entrenador')");
        $stmt->execute([$_POST['username'], password_hash($_POST['password'], PASSWORD_DEFAULT), $_POST['full_name'], $_POST['email']]);
        $success = "Entrenador registrado exitosamente";
    }
}

$stmt = $pdo->query("
    SELECT u.*, 
           GROUP_CONCAT(d.name SEPARATOR ', ') as disciplines,
           GROUP_CONCAT(CONCAT(d.id, ':', d.name) SEPARATOR '|') as discipline_details
    FROM users u
    LEFT JOIN trainer_disciplines td ON u.id = td.trainer_id
    LEFT JOIN disciplines d ON td.discipline_id = d.id
    WHERE u.user_type = 'entrenador'
    GROUP BY u.id
    ORDER BY u.full_name
");
$trainers = $stmt->fetchAll();

// Obtener disciplinas para asignación
$stmt = $pdo->query("SELECT * FROM disciplines ORDER BY name");
$disciplines = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Entrenadores - SportTrack</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <style>
        .error-message {
            color: #dc2626;
            font-size: 0.875rem;
            margin-top: 0.25rem;
            display: none;
        }
        .error-message.show {
            display: block;
        }
        input.invalid {
            border-color: #dc2626;
        }
        input.valid {
            border-color: #16a34a;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <?php include 'components/sidebar.php'; ?>
        
        <div class="flex-1 flex flex-col overflow-hidden">
            <?php include 'components/header.php'; ?>
            
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">
                <div class="max-w-7xl mx-auto">
                    <div class="mb-6">
                        <h1 class="text-3xl font-bold text-gray-900">Gestión de Entrenadores</h1>
                        <p class="text-gray-600">Administra los entrenadores y sus asignaciones</p>
                    </div>

                    <?php if (isset($success)): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                        <?php echo $success; ?>
                    </div>
                    <?php endif; ?>

                    <?php if (isset($error)): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                        <?php echo $error; ?>
                    </div>
                    <?php endif; ?>

                    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                        <h2 class="text-xl font-semibold mb-4">Registrar Nuevo Entrenador</h2>
                        <form method="POST" id="addTrainerForm" class="grid grid-cols-1 md:grid-cols-2 gap-4" novalidate>
                            <input type="hidden" name="action" value="add_trainer">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nombre Completo</label>
                                <input 
                                    type="text" 
                                    name="full_name" 
                                    id="trainer_full_name"
                                    required 
                                    pattern="^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{2,50}$"
                                    title="Solo letras y espacios (2-50 caracteres)"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500">
                                <span class="error-message" id="trainer_full_name_error">Solo letras y espacios (2-50 caracteres)</span>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                <input 
                                    type="email" 
                                    name="email" 
                                    id="trainer_email"
                                    required 
                                    pattern="^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.(com|cl|net|org|edu|gov|mil|info|biz)$"
                                    title="Formato: ejemplo@correo.com"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500">
                                <span class="error-message" id="trainer_email_error">Formato válido: ejemplo@correo.com</span>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Usuario</label>
                                <input 
                                    type="text" 
                                    name="username" 
                                    id="trainer_username"
                                    required 
                                    pattern="^[a-zA-Z0-9_]{4,20}$"
                                    title="Solo letras, números y guión bajo (4-20 caracteres)"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500">
                                <span class="error-message" id="trainer_username_error">Solo letras, números y _ (4-20 caracteres)</span>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Contraseña</label>
                                <input 
                                    type="password" 
                                    name="password" 
                                    id="trainer_password"
                                    required 
                                    pattern="^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{8,}$"
                                    title="Mínimo 8 caracteres, incluir letras, números y símbolos"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500">
                                <span class="error-message" id="trainer_password_error">Mínimo 8 caracteres con letras, números y símbolos (@$!%*#?&)</span>
                            </div>
                            <div class="md:col-span-2">
                                <button type="submit" class="bg-orange-500 text-white px-6 py-2 rounded-md hover:bg-orange-600 transition-colors">
                                    <i data-lucide="user-check" class="inline-block w-4 h-4 mr-2"></i>
                                    Registrar Entrenador
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="bg-white rounded-lg shadow-md">
                        <div class="p-6 border-b border-gray-200">
                            <h2 class="text-xl font-semibold">Entrenadores Registrados</h2>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Entrenador</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Disciplinas</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($trainers as $trainer): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                                    <i data-lucide="user-check" class="w-5 h-5 text-blue-600"></i>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($trainer['full_name']); ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($trainer['email']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($trainer['username']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php if ($trainer['disciplines']): ?>
                                                <div class="flex flex-wrap gap-1">
                                                    <?php 
                                                    $disciplineDetails = explode('|', $trainer['discipline_details']);
                                                    foreach ($disciplineDetails as $detail): 
                                                        if (empty($detail)) continue;
                                                        list($disciplineId, $disciplineName) = explode(':', $detail);
                                                    ?>
                                                    <span class="inline-flex items-center px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full">
                                                        <?php echo htmlspecialchars($disciplineName); ?>
                                                        <button onclick="removeAssignment(<?php echo $trainer['id']; ?>, <?php echo $disciplineId; ?>)" 
                                                                class="ml-1 text-blue-600 hover:text-blue-800">
                                                            <i data-lucide="x" class="w-3 h-3"></i>
                                                        </button>
                                                    </span>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php else: ?>
                                                <span class="px-2 py-1 text-xs bg-gray-100 rounded-full">Sin asignar</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <button onclick="openAssignModal(<?php echo $trainer['id']; ?>, '<?php echo htmlspecialchars($trainer['full_name'], ENT_QUOTES); ?>')" 
                                                    class="text-orange-600 hover:text-orange-900 mr-3">Asignar</button>
                                            <!-- Added onclick handlers for edit and delete buttons -->
                                            <button onclick="openEditTrainerModal(<?php echo $trainer['id']; ?>, '<?php echo htmlspecialchars($trainer['full_name'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($trainer['email'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($trainer['username'], ENT_QUOTES); ?>')" class="text-blue-600 hover:text-blue-900 mr-3">Editar</button>
                                            <button onclick="deleteTrainer(<?php echo $trainer['id']; ?>, '<?php echo htmlspecialchars($trainer['full_name'], ENT_QUOTES); ?>')" class="text-red-600 hover:text-red-900">Eliminar</button>
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

    <!-- Modal para asignar disciplinas -->
    <div id="assignModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Asignar Disciplina</h3>
                    <button onclick="closeAssignModal()" class="text-gray-400 hover:text-gray-600">
                        <i data-lucide="x" class="w-6 h-6"></i>
                    </button>
                </div>
                
                <form id="assignForm" method="POST">
                    <input type="hidden" name="action" value="assign_discipline">
                    <input type="hidden" name="trainer_id" id="modalTrainerId">
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Entrenador</label>
                        <input type="text" id="modalTrainerName" readonly 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50">
                    </div>
                    
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Disciplina</label>
                        <select name="discipline_id" required 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500">
                            <option value="">Seleccionar disciplina</option>
                            <?php foreach ($disciplines as $discipline): ?>
                            <option value="<?php echo $discipline['id']; ?>">
                                <?php echo htmlspecialchars($discipline['name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeAssignModal()" 
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">
                            Cancelar
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 text-sm font-medium text-white bg-orange-500 rounded-md hover:bg-orange-600">
                            Asignar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para editar entrenador -->
    <div id="editTrainerModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Editar Entrenador</h3>
                    <button onclick="closeEditTrainerModal()" class="text-gray-400 hover:text-gray-600">
                        <i data-lucide="x" class="w-6 h-6"></i>
                    </button>
                </div>
                
                <form id="editTrainerForm" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <input type="hidden" name="action" value="edit_trainer">
                    <input type="hidden" name="trainer_id" id="editTrainerId">
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nombre Completo</label>
                        <input type="text" name="full_name" id="editTrainerFullName" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                        <input type="email" name="email" id="editTrainerEmail" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Usuario</label>
                        <input type="text" name="username" id="editTrainerUsername" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500">
                    </div>
                    
                    <div class="md:col-span-2 flex justify-end space-x-3">
                        <button type="button" onclick="closeEditTrainerModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">
                            Cancelar
                        </button>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-orange-500 rounded-md hover:bg-orange-600">
                            Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Formulario oculto para eliminar asignaciones -->
    <form id="removeForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="remove_assignment">
        <input type="hidden" name="trainer_id" id="removeTrainerId">
        <input type="hidden" name="discipline_id" id="removeDisciplineId">
    </form>

    <!-- Formulario oculto para eliminar entrenador -->
    <form id="deleteTrainerForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="delete_trainer">
        <input type="hidden" name="trainer_id" id="deleteTrainerId">
    </form>

    <script>
        lucide.createIcons();

        const addTrainerForm = document.getElementById('addTrainerForm');
        const trainerFields = ['trainer_full_name', 'trainer_email', 'trainer_username', 'trainer_password'];

        trainerFields.forEach(fieldName => {
            const input = document.getElementById(fieldName);
            const errorMsg = document.getElementById(fieldName + '_error');

            input.addEventListener('input', function() {
                validateTrainerField(input, errorMsg);
            });

            input.addEventListener('blur', function() {
                validateTrainerField(input, errorMsg);
            });
        });

        function validateTrainerField(input, errorMsg) {
            if (input.validity.valid) {
                input.classList.remove('invalid');
                input.classList.add('valid');
                errorMsg.classList.remove('show');
                return true;
            } else {
                input.classList.remove('valid');
                input.classList.add('invalid');
                errorMsg.classList.add('show');
                return false;
            }
        }

        addTrainerForm.addEventListener('submit', function(e) {
            let isValid = true;
            trainerFields.forEach(fieldName => {
                const input = document.getElementById(fieldName);
                const errorMsg = document.getElementById(fieldName + '_error');
                if (!validateTrainerField(input, errorMsg)) {
                    isValid = false;
                }
            });

            if (!isValid) {
                e.preventDefault();
                alert('Por favor corrige los errores en el formulario antes de continuar.');
            }
        });

        function openAssignModal(trainerId, trainerName) {
            document.getElementById('modalTrainerId').value = trainerId;
            document.getElementById('modalTrainerName').value = trainerName;
            document.getElementById('assignModal').classList.remove('hidden');
            document.getElementById('assignModal').classList.add('flex');
        }

        function closeAssignModal() {
            document.getElementById('assignModal').classList.add('hidden');
            document.getElementById('assignModal').classList.remove('flex');
            document.getElementById('assignForm').reset();
        }

        function openEditTrainerModal(id, fullName, email, username) {
            document.getElementById('editTrainerId').value = id;
            document.getElementById('editTrainerFullName').value = fullName;
            document.getElementById('editTrainerEmail').value = email;
            document.getElementById('editTrainerUsername').value = username;
            document.getElementById('editTrainerModal').classList.remove('hidden');
            document.getElementById('editTrainerModal').classList.add('flex');
            lucide.createIcons();
        }

        function closeEditTrainerModal() {
            document.getElementById('editTrainerModal').classList.add('hidden');
            document.getElementById('editTrainerModal').classList.remove('flex');
            document.getElementById('editTrainerForm').reset();
        }

        function deleteTrainer(id, name) {
            if (confirm('¿Estás seguro de que quieres eliminar al entrenador "' + name + '"? Esta acción no se puede deshacer.')) {
                document.getElementById('deleteTrainerId').value = id;
                document.getElementById('deleteTrainerForm').submit();
            }
        }

        function removeAssignment(trainerId, disciplineId) {
            if (confirm('¿Estás seguro de que quieres eliminar esta asignación?')) {
                document.getElementById('removeTrainerId').value = trainerId;
                document.getElementById('removeDisciplineId').value = disciplineId;
                document.getElementById('removeForm').submit();
            }
        }

        // Cerrar modal al hacer clic fuera
        document.getElementById('assignModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeAssignModal();
            }
        });

        document.getElementById('editTrainerModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeEditTrainerModal();
            }
        });
    </script>
</body>
</html>
