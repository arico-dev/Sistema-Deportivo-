<?php
require_once 'config/session.php';
require_once 'config/database.php';

$user = getCurrentUser();
$database = new Database();
$pdo = $database->getConnection();

// Procesar actualización de perfil
if (isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ? WHERE id = ?");
    $stmt->execute([$_POST['full_name'], $_POST['email'], $user['id']]);
    $success = "Perfil actualizado exitosamente";
    
    // Actualizar datos en sesión
    $_SESSION['user']['full_name'] = $_POST['full_name'];
    $_SESSION['user']['email'] = $_POST['email'];
    $user = getCurrentUser();
}

// Procesar cambio de contraseña
if (isset($_POST['action']) && $_POST['action'] === 'change_password') {
    // Obtener password actual de la base de datos
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$user['id']]);
    $currentUser = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($currentUser && password_verify($_POST['current_password'], $currentUser['password'])) {
        if ($_POST['new_password'] === $_POST['confirm_password']) {
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([password_hash($_POST['new_password'], PASSWORD_DEFAULT), $user['id']]);
            $success = "Contraseña actualizada exitosamente";
        } else {
            $error = "Las contraseñas nuevas no coinciden";
        }
    } else {
        $error = "La contraseña actual es incorrecta";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración - SportTrack</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <?php include 'components/sidebar.php'; ?>
        
        <div class="flex-1 flex flex-col overflow-hidden">
            <?php include 'components/header.php'; ?>
            
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">
                <div class="max-w-4xl mx-auto">
                    <div class="mb-6">
                        <h1 class="text-3xl font-bold text-gray-900">Configuración</h1>
                        <p class="text-gray-600">Administra tu perfil y preferencias</p>
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

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Información del perfil -->
                        <div class="lg:col-span-2">
                            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                                <h2 class="text-xl font-semibold mb-4">Información del Perfil</h2>
                                <form method="POST">
                                    <input type="hidden" name="action" value="update_profile">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Nombre Completo</label>
                                            <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Usuario</label>
                                            <input type="text" value="<?php echo htmlspecialchars($user['username']); ?>" disabled class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Usuario</label>
                                            <input type="text" value="<?php echo ucfirst($user['user_type']); ?>" disabled class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100">
                                        </div>
                                    </div>
                                    <div class="mt-6">
                                        <button type="submit" class="bg-orange-500 text-white px-6 py-2 rounded-md hover:bg-orange-600 transition-colors">
                                            <i data-lucide="save" class="inline-block w-4 h-4 mr-2"></i>
                                            Guardar Cambios
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <!-- Cambiar contraseña -->
                            <div class="bg-white rounded-lg shadow-md p-6">
                                <h2 class="text-xl font-semibold mb-4">Cambiar Contraseña</h2>
                                <form method="POST">
                                    <input type="hidden" name="action" value="change_password">
                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Contraseña Actual</label>
                                            <input type="password" name="current_password" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Nueva Contraseña</label>
                                            <input type="password" name="new_password" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Confirmar Nueva Contraseña</label>
                                            <input type="password" name="confirm_password" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500">
                                        </div>
                                    </div>
                                    <div class="mt-6">
                                        <button type="submit" class="bg-red-500 text-white px-6 py-2 rounded-md hover:bg-red-600 transition-colors">
                                            <i data-lucide="lock" class="inline-block w-4 h-4 mr-2"></i>
                                            Cambiar Contraseña
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Panel lateral -->
                        <div>
                            <div class="bg-white rounded-lg shadow-md p-6">
                                <h3 class="text-lg font-semibold mb-4">Información de la Cuenta</h3>
                                <div class="space-y-3">
                                    <div class="flex items-center justify-center w-20 h-20 bg-orange-100 rounded-full mx-auto mb-4">
                                        <i data-lucide="user" class="w-10 h-10 text-orange-600"></i>
                                    </div>
                                    <div class="text-center">
                                        <p class="font-medium text-gray-900"><?php echo htmlspecialchars($user['full_name']); ?></p>
                                        <p class="text-sm text-gray-500 capitalize"><?php echo htmlspecialchars($user['user_type']); ?></p>
                                    </div>
                                    <div class="border-t pt-3 mt-3">
                                        <div class="text-sm text-gray-600">
                                            <p><strong>Miembro desde:</strong></p>
                                            <p><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
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
