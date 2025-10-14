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

// Procesar formulario de nuevo estudiante
if (isset($_POST['action']) && $_POST['action'] === 'add_student') {
    // Check if username already exists
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users WHERE username = ?");
    $stmt->execute([$_POST['username']]);
    $usernameExists = $stmt->fetch(PDO::FETCH_ASSOC)['count'] > 0;
    
    if ($usernameExists) {
        $error = "El nombre de usuario '{$_POST['username']}' ya está en uso. Por favor elige otro.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO users (username, password, full_name, email, user_type) VALUES (?, ?, ?, ?, 'estudiante')");
        $stmt->execute([$_POST['username'], password_hash($_POST['password'], PASSWORD_DEFAULT), $_POST['full_name'], $_POST['email']]);
        $success = "Estudiante registrado exitosamente";
    }
}

// Procesar formulario de edición de estudiante
if (isset($_POST['action']) && $_POST['action'] === 'edit_student') {
    $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, username = ? WHERE id = ? AND user_type = 'estudiante'");
    $stmt->execute([$_POST['full_name'], $_POST['email'], $_POST['username'], $_POST['student_id']]);
    $success = "Estudiante actualizado exitosamente";
}

// Procesar formulario de eliminación de estudiante
if (isset($_POST['action']) && $_POST['action'] === 'delete_student') {
    try {
        // Delete related records first to avoid foreign key constraint violations
        
        // Delete attendance records
        $stmt = $pdo->prepare("DELETE FROM attendance WHERE student_id = ?");
        $stmt->execute([$_POST['student_id']]);
        
        // Delete evaluations
        $stmt = $pdo->prepare("DELETE FROM evaluations WHERE student_id = ?");
        $stmt->execute([$_POST['student_id']]);
        
        // Delete enrollments
        $stmt = $pdo->prepare("DELETE FROM student_enrollments WHERE student_id = ?");
        $stmt->execute([$_POST['student_id']]);
        
        // Finally delete the student
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND user_type = 'estudiante'");
        $stmt->execute([$_POST['student_id']]);
        
        $success = "Estudiante eliminado exitosamente";
    } catch (PDOException $e) {
        $error = "Error al eliminar estudiante: " . $e->getMessage();
    }
}

// Obtener lista de estudiantes
$stmt = $pdo->query("SELECT * FROM users WHERE user_type = 'estudiante' ORDER BY full_name");
$students = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Estudiantes - SportTrack</title>
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
                        <h1 class="text-3xl font-bold text-gray-900">Gestión de Estudiantes</h1>
                        <p class="text-gray-600">Administra los estudiantes registrados en el sistema</p>
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

                    <!-- Formulario para agregar estudiante -->
                    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                        <h2 class="text-xl font-semibold mb-4">Registrar Nuevo Estudiante</h2>
                        <form method="POST" id="addStudentForm" class="grid grid-cols-1 md:grid-cols-2 gap-4" novalidate>
                            <input type="hidden" name="action" value="add_student">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nombre Completo</label>
                                <input 
                                    type="text" 
                                    name="full_name" 
                                    id="full_name"
                                    required 
                                    pattern="^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{2,50}$"
                                    title="Solo letras y espacios (2-50 caracteres)"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500">
                                <span class="error-message" id="full_name_error">Solo letras y espacios (2-50 caracteres)</span>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                <input 
                                    type="email" 
                                    name="email" 
                                    id="email"
                                    required 
                                    pattern="^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.(com|cl|net|org|edu|gov|mil|info|biz)$"
                                    title="Formato: ejemplo@correo.com"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500">
                                <span class="error-message" id="email_error">Formato válido: ejemplo@correo.com</span>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Usuario</label>
                                <input 
                                    type="text" 
                                    name="username" 
                                    id="username"
                                    required 
                                    pattern="^[a-zA-Z0-9_]{4,20}$"
                                    title="Solo letras, números y guión bajo (4-20 caracteres)"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500">
                                <span class="error-message" id="username_error">Solo letras, números y _ (4-20 caracteres)</span>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Contraseña</label>
                                <input 
                                    type="password" 
                                    name="password" 
                                    id="password"
                                    required 
                                    pattern="^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{8,}$"
                                    title="Mínimo 8 caracteres, incluir letras, números y símbolos"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500">
                                <span class="error-message" id="password_error">Mínimo 8 caracteres con letras, números y símbolos (@$!%*#?&)</span>
                            </div>
                            <div class="md:col-span-2">
                                <button type="submit" class="bg-orange-500 text-white px-6 py-2 rounded-md hover:bg-orange-600 transition-colors">
                                    <i data-lucide="user-plus" class="inline-block w-4 h-4 mr-2"></i>
                                    Registrar Estudiante
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Lista de estudiantes -->
                    <div class="bg-white rounded-lg shadow-md">
                        <div class="p-6 border-b border-gray-200">
                            <h2 class="text-xl font-semibold">Estudiantes Registrados</h2>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estudiante</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha Registro</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($students as $student): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="w-10 h-10 bg-orange-100 rounded-full flex items-center justify-center">
                                                    <i data-lucide="user" class="w-5 h-5 text-orange-600"></i>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($student['full_name']); ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($student['email']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($student['username']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo date('d/m/Y', strtotime($student['created_at'])); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <!-- Added onclick handlers for edit and delete buttons -->
                                            <button onclick="openEditModal(<?php echo $student['id']; ?>, '<?php echo htmlspecialchars($student['full_name'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($student['email'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($student['username'], ENT_QUOTES); ?>')" class="text-orange-600 hover:text-orange-900 mr-3">Editar</button>
                                            <button onclick="deleteStudent(<?php echo $student['id']; ?>, '<?php echo htmlspecialchars($student['full_name'], ENT_QUOTES); ?>')" class="text-red-600 hover:text-red-900">Eliminar</button>
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

    <!-- Added edit modal -->
    <div id="editModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Editar Estudiante</h3>
                    <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600">
                        <i data-lucide="x" class="w-6 h-6"></i>
                    </button>
                </div>
                
                <form id="editForm" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <input type="hidden" name="action" value="edit_student">
                    <input type="hidden" name="student_id" id="editStudentId">
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nombre Completo</label>
                        <input type="text" name="full_name" id="editFullName" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                        <input type="email" name="email" id="editEmail" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Usuario</label>
                        <input type="text" name="username" id="editUsername" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500">
                    </div>
                    
                    <div class="md:col-span-2 flex justify-end space-x-3">
                        <button type="button" onclick="closeEditModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">
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

    <!-- Added hidden delete form -->
    <form id="deleteForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="delete_student">
        <input type="hidden" name="student_id" id="deleteStudentId">
    </form>

    <script>
        lucide.createIcons();

        function openEditModal(id, fullName, email, username) {
            document.getElementById('editStudentId').value = id;
            document.getElementById('editFullName').value = fullName;
            document.getElementById('editEmail').value = email;
            document.getElementById('editUsername').value = username;
            document.getElementById('editModal').classList.remove('hidden');
            document.getElementById('editModal').classList.add('flex');
            lucide.createIcons();
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
            document.getElementById('editModal').classList.remove('flex');
            document.getElementById('editForm').reset();
        }

        function deleteStudent(id, name) {
            if (confirm('¿Estás seguro de que quieres eliminar al estudiante "' + name + '"? Esta acción no se puede deshacer.')) {
                document.getElementById('deleteStudentId').value = id;
                document.getElementById('deleteForm').submit();
            }
        }

        // Close modal when clicking outside
        document.getElementById('editModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeEditModal();
            }
        });

        const addForm = document.getElementById('addStudentForm');
        const fields = ['full_name', 'email', 'username', 'password'];

        fields.forEach(fieldName => {
            const input = document.getElementById(fieldName);
            const errorMsg = document.getElementById(fieldName + '_error');

            input.addEventListener('input', function() {
                validateField(input, errorMsg);
            });

            input.addEventListener('blur', function() {
                validateField(input, errorMsg);
            });
        });

        function validateField(input, errorMsg) {
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

        addForm.addEventListener('submit', function(e) {
            let isValid = true;
            fields.forEach(fieldName => {
                const input = document.getElementById(fieldName);
                const errorMsg = document.getElementById(fieldName + '_error');
                if (!validateField(input, errorMsg)) {
                    isValid = false;
                }
            });

            if (!isValid) {
                e.preventDefault();
                alert('Por favor corrige los errores en el formulario antes de continuar.');
            }
        });
    </script>
</body>
</html>
