<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voice Notes - Personal Notes System</title>
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
                        <i class="fas fa-microphone mr-2"></i>Voice Notes
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
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Voice Notes</h1>
            <p class="text-gray-600 dark:text-gray-300 mt-2">Record, transcribe, and convert voice notes to text</p>
        </div>

        <!-- Voice Notes Interface -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
            <div class="mb-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Record Voice Note</h2>
                
                <!-- Recording Interface -->
                <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-8 text-center">
                    <div id="recordingStatus" class="mb-4">
                        <i class="fas fa-microphone text-4xl text-gray-400 mb-4"></i>
                        <p class="text-gray-600 dark:text-gray-300">Click the microphone to start recording</p>
                    </div>
                    
                    <div class="flex justify-center space-x-4">
                        <button id="startRecording" class="bg-red-500 hover:bg-red-600 text-white px-6 py-3 rounded-lg flex items-center space-x-2">
                            <i class="fas fa-microphone"></i>
                            <span>Start Recording</span>
                        </button>
                        <button id="stopRecording" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg flex items-center space-x-2" disabled>
                            <i class="fas fa-stop"></i>
                            <span>Stop Recording</span>
                        </button>
                    </div>
                    
                    <div id="recordingTimer" class="mt-4 text-lg font-mono text-gray-600 dark:text-gray-300 hidden">
                        00:00
                    </div>
                </div>
                
                <!-- Upload Form -->
                <form id="voiceNoteForm" class="mt-6" enctype="multipart/form-data">
                    <div class="mb-4">
                        <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Title</label>
                        <input type="text" id="title" name="title" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white" placeholder="Enter voice note title">
                    </div>
                    
                    <div class="mb-4">
                        <label for="audioFile" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Or Upload Audio File</label>
                        <input type="file" id="audioFile" name="audio_file" accept="audio/*" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                    </div>
                    
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg">
                        <i class="fas fa-save mr-2"></i>Save Voice Note
                    </button>
                </form>
            </div>
            
            <!-- Voice Notes List -->
            <div>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Your Voice Notes</h2>
                <div id="voiceNotesList" class="space-y-4">
                    <!-- Voice notes will be loaded here -->
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <?php if (isset($stats) && $stats): ?>
        <div class="mt-8 grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow">
                <div class="flex items-center">
                    <div class="p-2 bg-blue-100 dark:bg-blue-900 rounded-lg">
                        <i class="fas fa-microphone text-blue-600 dark:text-blue-400"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-300">Total Notes</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white"><?= $stats['total_notes'] ?></p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow">
                <div class="flex items-center">
                    <div class="p-2 bg-green-100 dark:bg-green-900 rounded-lg">
                        <i class="fas fa-clock text-green-600 dark:text-green-400"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-300">Total Duration</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                            <?= $stats['total_duration'] ? gmdate('H:i:s', $stats['total_duration']) : '0:00:00' ?>
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow">
                <div class="flex items-center">
                    <div class="p-2 bg-purple-100 dark:bg-purple-900 rounded-lg">
                        <i class="fas fa-file-alt text-purple-600 dark:text-purple-400"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-300">Processed</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white"><?= $stats['processed_notes'] ?></p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow">
                <div class="flex items-center">
                    <div class="p-2 bg-orange-100 dark:bg-orange-900 rounded-lg">
                        <i class="fas fa-exchange-alt text-orange-600 dark:text-orange-400"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-300">Converted</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white"><?= $stats['converted_notes'] ?></p>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Recent Voice Notes -->
        <div class="mt-8">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Recent Voice Notes</h2>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                <div class="p-6">
                    <?php if (empty($voiceNotes)): ?>
                        <div class="text-center py-12">
                            <i class="fas fa-microphone text-gray-400 text-4xl mb-4"></i>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No voice notes yet</h3>
                            <p class="text-gray-600 dark:text-gray-300">Start recording your first voice note above!</p>
                        </div>
                    <?php else: ?>
                        <div class="space-y-4">
                            <?php foreach ($voiceNotes as $note): ?>
                            <div class="flex items-center justify-between p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                                <div class="flex items-center space-x-4">
                                    <button class="w-10 h-10 bg-blue-600 text-white rounded-full flex items-center justify-center hover:bg-blue-700">
                                        <i class="fas fa-play"></i>
                                    </button>
                                    <div>
                                        <h4 class="font-medium text-gray-900 dark:text-white"><?= htmlspecialchars($note['original_filename']) ?></h4>
                                        <p class="text-sm text-gray-600 dark:text-gray-300">
                                            <?= $note['duration'] ? gmdate('i:s', $note['duration']) : 'Unknown duration' ?> • 
                                            <?= date('M j, Y g:i A', strtotime($note['created_at'])) ?>
                                        </p>
                                        <?php if ($note['transcription']): ?>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                            <?= htmlspecialchars(substr($note['transcription'], 0, 100)) ?>...
                                        </p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <?php if (!$note['is_processed']): ?>
                                    <button class="px-3 py-1 text-sm bg-yellow-100 text-yellow-800 rounded-full">
                                        <i class="fas fa-clock mr-1"></i>Processing
                                    </button>
                                    <?php endif; ?>
                                    <button class="px-3 py-1 text-sm bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-full hover:bg-gray-200 dark:hover:bg-gray-600">
                                        <i class="fas fa-ellipsis-h"></i>
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
