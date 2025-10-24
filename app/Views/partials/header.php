<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'Personal Notes System'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        :root {
            --color-primary: #3b82f6;
            --color-secondary: #64748b;
            --color-background: #ffffff;
            --color-surface: #f8fafc;
            --color-text: #1e293b;
            --color-textSecondary: #64748b;
            --color-border: #e2e8f0;
            --color-success: #10b981;
            --color-warning: #f59e0b;
            --color-error: #ef4444;
            --color-info: #06b6d4;
        }
        
        .dark-mode {
            --color-primary: #60a5fa;
            --color-secondary: #94a3b8;
            --color-background: #0f172a;
            --color-surface: #1e293b;
            --color-text: #f1f5f9;
            --color-textSecondary: #94a3b8;
            --color-border: #334155;
            --color-success: #34d399;
            --color-warning: #fbbf24;
            --color-error: #f87171;
            --color-info: #22d3ee;
        }
        
        body {
            background-color: var(--color-background);
            color: var(--color-text);
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        
        .glassmorphism {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .dark-mode .glassmorphism {
            background: rgba(30, 41, 59, 0.3);
            border: 1px solid rgba(51, 65, 85, 0.3);
        }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900">
    <!-- Navigation -->
    <nav class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="/dashboard" class="text-xl font-bold text-gray-900 dark:text-white">
                        <i class="fas fa-sticky-note mr-2"></i>Personal Notes
                    </a>
                </div>
                <div class="flex items-center space-x-4">
                    <button onclick="toggleDarkMode()" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                        <i class="fas fa-moon dark:hidden"></i>
                        <i class="fas fa-sun hidden dark:inline"></i>
                    </button>
                    <a href="/dashboard" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
                        <i class="fas fa-home mr-2"></i>Dashboard
                    </a>
                    <a href="/logout" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
                        <i class="fas fa-sign-out-alt mr-2"></i>Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <script>
        // Theme management
        function initializeTheme() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            applyTheme(savedTheme);
        }

        function toggleDarkMode() {
            const currentTheme = document.documentElement.classList.contains('dark-mode') ? 'dark' : 'light';
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            applyTheme(newTheme);
            localStorage.setItem('theme', newTheme);
        }

        function applyTheme(theme) {
            if (theme === 'dark') {
                document.documentElement.classList.add('dark-mode');
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark-mode');
                document.documentElement.classList.remove('dark');
            }
        }

        // Initialize theme on page load
        document.addEventListener('DOMContentLoaded', initializeTheme);
    </script>
