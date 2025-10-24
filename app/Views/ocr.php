<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OCR - Personal Notes System</title>
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
                        <i class="fas fa-eye mr-2"></i>OCR
                    </a>
                </div>
                <div class="flex items-center space-x-4">
                    <button onclick="toggleDarkMode()" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                        <i class="fas fa-moon dark:hidden"></i>
                        <i class="fas fa-sun hidden dark:inline"></i>
                    </button>
                    <a href="/dashboard" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Optical Character Recognition</h1>
            <p class="text-gray-600 dark:text-gray-300 mt-2">Extract text from images and convert to notes</p>
        </div>

        <!-- OCR Interface -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
            <div class="mb-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Upload Image for OCR</h2>
                
                <!-- Upload Interface -->
                <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-8 text-center">
                    <div class="mb-4">
                        <i class="fas fa-image text-4xl text-gray-400 mb-4"></i>
                        <p class="text-gray-600 dark:text-gray-300">Upload an image to extract text</p>
                    </div>
                    
                    <form id="ocrForm" enctype="multipart/form-data">
                        <div class="mb-4">
                            <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Title</label>
                            <input type="text" id="title" name="title" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white" placeholder="Enter document title">
                        </div>
                        
                        <div class="mb-4">
                            <label for="imageFile" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Select Image</label>
                            <input type="file" id="imageFile" name="image_file" accept="image/*" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        </div>
                        
                        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg">
                            <i class="fas fa-search mr-2"></i>Extract Text
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- OCR Results List -->
            <div>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">OCR Results</h2>
                <div id="ocrResultsList" class="space-y-4">
                    <!-- OCR results will be loaded here -->
                </div>
            </div>
        </div>

        <!-- Recent OCR Results -->
        <div class="mt-8">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Recent OCR Results</h2>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                <div class="p-6">
                    <?php if (empty($ocrResults)): ?>
                        <div class="text-center py-12">
                            <i class="fas fa-image text-gray-400 text-4xl mb-4"></i>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No OCR results yet</h3>
                            <p class="text-gray-600 dark:text-gray-300">Upload an image above to extract text!</p>
                        </div>
                    <?php else: ?>
                        <div class="space-y-4">
                            <?php foreach ($ocrResults as $result): ?>
                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                                <div class="flex items-center justify-between mb-3">
                                    <h4 class="font-medium text-gray-900 dark:text-white"><?= htmlspecialchars($result['original_filename']) ?></h4>
                                    <div class="flex items-center space-x-2">
                                        <span class="px-2 py-1 text-xs rounded-full <?= $result['confidence'] > 0.8 ? 'bg-green-100 text-green-800' : ($result['confidence'] > 0.5 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') ?>">
                                            <?= round($result['confidence'] * 100) ?>% confidence
                                        </span>
                                        <span class="text-sm text-gray-500 dark:text-gray-400">
                                            <?= date('M j, Y g:i A', strtotime($result['created_at'])) ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <?php if ($result['extracted_text']): ?>
                                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 mb-3">
                                    <p class="text-sm text-gray-700 dark:text-gray-300 font-mono">
                                        <?= htmlspecialchars(substr($result['extracted_text'], 0, 200)) ?>
                                        <?php if (strlen($result['extracted_text']) > 200): ?>...<?php endif; ?>
                                    </p>
                                </div>
                                <?php endif; ?>
                                
                                <div class="flex items-center space-x-2">
                                    <button class="px-3 py-1 text-sm bg-blue-100 text-blue-800 rounded-full hover:bg-blue-200">
                                        <i class="fas fa-file-alt mr-1"></i>Convert to Note
                                    </button>
                                    <button class="px-3 py-1 text-sm bg-gray-100 text-gray-800 rounded-full hover:bg-gray-200">
                                        <i class="fas fa-copy mr-1"></i>Copy Text
                                    </button>
                                    <button class="px-3 py-1 text-sm bg-red-100 text-red-800 rounded-full hover:bg-red-200">
                                        <i class="fas fa-trash mr-1"></i>Delete
                                    </button>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- CSRF Token -->
    <input type="hidden" name="csrf_token" value="<?= CSRF::generate() ?>">

    <script>
        // Theme Management
        function initializeTheme() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            applyTheme(savedTheme);
        }

        function toggleDarkMode() {
            const currentTheme = localStorage.getItem('theme') || 'light';
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            applyTheme(newTheme);
            localStorage.setItem('theme', newTheme);
        }

        function applyTheme(theme) {
            document.documentElement.classList.toggle('dark-mode', theme === 'dark');
        }

        // Initialize theme on page load
        document.addEventListener('DOMContentLoaded', function() {
            initializeTheme();
        });
    </script>
</body>
</html>
