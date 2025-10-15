<?php
use Core\Session;
use Core\CSRF;
use App\Models\User;

// Get user data
$userId = Session::get('user_id');
$user = $userId ? User::findById($userId) : null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - SecureNotes</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#4C51BF',
                        secondary: '#1F2937'
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <?php include __DIR__ . '/partials/sidebar.php'; ?>
        
        <div class="flex-1 flex flex-col overflow-hidden">
            <?php include __DIR__ . '/partials/navbar.php'; ?>
            
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">
                <div class="max-w-4xl mx-auto">
                    <!-- Header -->
                    <div class="mb-8">
                        <h1 class="text-3xl font-bold text-gray-900 mb-2">Profile</h1>
                        <p class="text-gray-600">Manage your account information and preferences</p>
                    </div>

                    <!-- Profile Overview -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                        <!-- Profile Card -->
                        <div class="lg:col-span-1">
                            <div class="bg-white rounded-xl shadow-lg p-6 text-center">
                                <div class="w-24 h-24 bg-gradient-to-br from-[#4C51BF] to-[#667eea] rounded-full flex items-center justify-center text-white text-3xl font-bold mx-auto mb-4">
                                    <?= strtoupper(substr($user['first_name'] ?? $user['username'], 0, 1)) ?>
                                </div>
                                <h2 class="text-xl font-semibold text-gray-900 mb-1">
                                    <?= htmlspecialchars($user['first_name'] ?? $user['username']) ?>
                                </h2>
                                <p class="text-gray-600 mb-4"><?= htmlspecialchars($user['email'] ?? '') ?></p>
                                <div class="flex items-center justify-center space-x-2 text-sm text-green-600">
                                    <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                                    <span>Active</span>
                                </div>
                            </div>
                        </div>

                        <!-- Account Stats -->
                        <div class="lg:col-span-2">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="bg-white rounded-xl shadow-lg p-6">
                                    <div class="flex items-center">
                                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-sticky-note text-blue-600 text-xl"></i>
                                        </div>
                                        <div class="ml-4">
                                            <p class="text-2xl font-bold text-gray-900">0</p>
                                            <p class="text-sm text-gray-600">Total Notes</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-white rounded-xl shadow-lg p-6">
                                    <div class="flex items-center">
                                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-check-square text-green-600 text-xl"></i>
                                        </div>
                                        <div class="ml-4">
                                            <p class="text-2xl font-bold text-gray-900">0</p>
                                            <p class="text-sm text-gray-600">Completed Tasks</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-white rounded-xl shadow-lg p-6">
                                    <div class="flex items-center">
                                        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-calendar text-purple-600 text-xl"></i>
                                        </div>
                                        <div class="ml-4">
                                            <p class="text-2xl font-bold text-gray-900">0</p>
                                            <p class="text-sm text-gray-600">Days Active</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Account Information -->
                    <div class="bg-white rounded-xl shadow-lg">
                        <div class="p-6 border-b border-gray-200">
                            <h2 class="text-xl font-semibold text-gray-900">Account Information</h2>
                        </div>
                        
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Username</label>
                                    <input type="text" value="<?= htmlspecialchars($user['username'] ?? '') ?>" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#4C51BF]/20 focus:border-[#4C51BF]" 
                                           readonly>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                    <input type="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#4C51BF]/20 focus:border-[#4C51BF]" 
                                           readonly>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">First Name</label>
                                    <input type="text" value="<?= htmlspecialchars($user['first_name'] ?? '') ?>" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#4C51BF]/20 focus:border-[#4C51BF]" 
                                           readonly>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Last Name</label>
                                    <input type="text" value="<?= htmlspecialchars($user['last_name'] ?? '') ?>" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#4C51BF]/20 focus:border-[#4C51BF]" 
                                           readonly>
                                </div>
                            </div>
                            
                            <div class="mt-6 pt-6 border-t border-gray-200">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Member Since</label>
                                        <input type="text" value="<?= date('F j, Y', strtotime($user['created_at'] ?? '')) ?>" 
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#4C51BF]/20 focus:border-[#4C51BF]" 
                                               readonly>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Last Login</label>
                                        <input type="text" value="<?= $user['last_login'] ? date('F j, Y g:i A', strtotime($user['last_login'])) : 'Never' ?>" 
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#4C51BF]/20 focus:border-[#4C51BF]" 
                                               readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                        <a href="/settings" class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow duration-200">
                            <div class="flex items-center">
                                <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-cog text-gray-600 text-xl"></i>
                                </div>
                                <div class="ml-4">
                                    <h3 class="font-semibold text-gray-900">Settings</h3>
                                    <p class="text-sm text-gray-600">Manage preferences</p>
                                </div>
                            </div>
                        </a>
                        
                        <a href="/security" class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow duration-200">
                            <div class="flex items-center">
                                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-shield-alt text-red-600 text-xl"></i>
                                </div>
                                <div class="ml-4">
                                    <h3 class="font-semibold text-gray-900">Security</h3>
                                    <p class="text-sm text-gray-600">Security settings</p>
                                </div>
                            </div>
                        </a>
                        
                        <a href="/backup" class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow duration-200">
                            <div class="flex items-center">
                                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-download text-green-600 text-xl"></i>
                                </div>
                                <div class="ml-4">
                                    <h3 class="font-semibold text-gray-900">Backup</h3>
                                    <p class="text-sm text-gray-600">Export data</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
