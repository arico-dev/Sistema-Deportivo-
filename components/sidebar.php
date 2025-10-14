<?php
$user = getCurrentUser();
$userType = getUserType();

// Definir menús según tipo de usuario
$menuItems = [];

if ($userType === 'coordinador') {
    $menuItems = [
        ['icon' => 'home', 'text' => 'Dashboard', 'href' => 'index.php'],
        ['icon' => 'users', 'text' => 'Estudiantes', 'href' => 'students.php'],
        ['icon' => 'user-check', 'text' => 'Entrenadores', 'href' => 'trainers.php'],
        ['icon' => 'activity', 'text' => 'Disciplinas', 'href' => 'disciplines.php'],
        ['icon' => 'user-plus', 'text' => 'Inscripciones', 'href' => 'enrollments.php'],
        ['icon' => 'bar-chart', 'text' => 'Reportes', 'href' => 'reports.php'],
        ['icon' => 'settings', 'text' => 'Configuración', 'href' => 'settings.php']
    ];
} elseif ($userType === 'entrenador') {
    $menuItems = [
        ['icon' => 'home', 'text' => 'Dashboard', 'href' => 'index.php'],
        ['icon' => 'users', 'text' => 'Mis Estudiantes', 'href' => 'my-students.php'],
        ['icon' => 'check-circle', 'text' => 'Asistencia', 'href' => 'attendance.php'],
        ['icon' => 'star', 'text' => 'Evaluaciones', 'href' => 'evaluations.php'],
        ['icon' => 'bar-chart', 'text' => 'Reportes', 'href' => 'trainer-reports.php'],
        ['icon' => 'settings', 'text' => 'Configuración', 'href' => 'settings.php']
    ];
} else { // estudiante
    $menuItems = [
        ['icon' => 'home', 'text' => 'Dashboard', 'href' => 'index.php'],
        ['icon' => 'activity', 'text' => 'Mis Actividades', 'href' => 'my-activities.php'],
        ['icon' => 'check-circle', 'text' => 'Mi Asistencia', 'href' => 'my-attendance.php'],
        ['icon' => 'star', 'text' => 'Mi Rendimiento', 'href' => 'my-performance.php'],
        ['icon' => 'settings', 'text' => 'Configuración', 'href' => 'settings.php']
    ];
}
?>

<div class="bg-gray-800 text-white w-64 space-y-6 py-7 px-2 absolute inset-y-0 left-0 transform -translate-x-full md:relative md:translate-x-0 transition duration-200 ease-in-out">
    <a href="index.php" class="text-white flex items-center space-x-2 px-4">
        <i data-lucide="activity" class="w-8 h-8"></i>
        <span class="text-2xl font-extrabold">SportTrack</span>
    </a>
    <nav>
        <?php foreach ($menuItems as $item): ?>
        <a href="<?php echo $item['href']; ?>" class="block py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700 hover:text-white">
            <i data-lucide="<?php echo $item['icon']; ?>" class="inline-block w-6 h-6 mr-2 -mt-1"></i>
            <?php echo $item['text']; ?>
        </a>
        <?php endforeach; ?>
    </nav>
    
      
    <div class="px-4 pt-4 border-t border-gray-700">
        <div class="flex items-center space-x-2">
            <div class="w-8 h-8 bg-gray-600 rounded-full flex items-center justify-center">
                <i data-lucide="user" class="w-4 h-4"></i>
            </div>
            <div>
                <p class="text-sm font-medium"><?php echo htmlspecialchars($user['full_name']); ?></p>
                <p class="text-xs text-gray-400 capitalize"><?php echo htmlspecialchars($user['user_type']); ?></p>
            </div>
        </div>
    </div>
</div>
