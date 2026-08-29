<?php
$user = getCurrentUser();
?>

<header class="bg-white shadow-sm border-b border-gray-200">
    <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center">
            <div class="flex items-center">
                <button class="md:hidden p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100">
                    <i data-lucide="menu" class="h-6 w-6"></i>
                </button>
                <h2 class="ml-2 text-lg font-semibold text-gray-900">
                    Bienvenido, <?php echo htmlspecialchars($user['full_name']); ?>
                </h2>
            </div>
            
            <div class="flex items-center space-x-4">
                <div class="relative">
                    <button class="p-2 text-gray-400 hover:text-gray-500">
                        <i data-lucide="bell" class="h-6 w-6"></i>
                    </button>
                </div>
                
                <div class="relative">
                    <div class="flex items-center space-x-2">
                        <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center">
                            <i data-lucide="user" class="w-4 h-4 text-gray-600"></i>
                        </div>
                        <span class="text-sm font-medium text-gray-700"><?php echo htmlspecialchars($user['username']); ?></span>
                        <a href="logout.php" class="text-sm text-red-600 hover:text-red-800 ml-2">Cerrar Sesión</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
