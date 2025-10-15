<?php
use Core\Session;
use Core\CSRF;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trash - SecureNotes</title>
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
    <style>
        .trash-item {
            transition: all 0.3s ease;
        }
        .trash-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }
        .glass-effect {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.1);
            border-radius: 3px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(0, 0, 0, 0.3);
            border-radius: 3px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: rgba(0, 0, 0, 0.5);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 to-gray-100 min-h-screen">
    <div class="flex h-screen">
        <?php include __DIR__ . '/partials/sidebar.php'; ?>
        
        <div class="flex-1 flex flex-col overflow-hidden">
            <?php include __DIR__ . '/partials/navbar.php'; ?>
            
            <main class="flex-1 overflow-x-hidden overflow-y-auto p-6 custom-scrollbar">
                <div class="max-w-7xl mx-auto">
                    <!-- Header -->
                    <div class="mb-8">
                        <div class="flex items-center justify-between">
                            <div>
                                <h1 class="text-3xl font-bold text-gray-900 mb-2">
                                    <i class="fas fa-trash-alt text-red-500 mr-3"></i>
                                    Trash
                                </h1>
                                <p class="text-gray-600">Manage your deleted notes and tasks</p>
                            </div>
                            <?php if (isset($trashStats) && $trashStats['total_deleted'] > 0): ?>
                                <div class="glass-effect rounded-lg p-4">
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                                        <div>
                                            <div class="text-2xl font-bold text-red-600"><?= $trashStats['total_deleted'] ?></div>
                                            <div class="text-sm text-gray-600">Total Deleted</div>
                                        </div>
                                        <div>
                                            <div class="text-xl font-bold text-orange-600"><?= $trashStats['tasks_deleted'] ?? 0 ?></div>
                                            <div class="text-sm text-gray-600">Tasks</div>
                                        </div>
                                        <div>
                                            <div class="text-xl font-bold text-blue-600"><?= $trashStats['notes_deleted'] ?? 0 ?></div>
                                            <div class="text-sm text-gray-600">Notes</div>
                                        </div>
                                        <div>
                                            <div class="text-xl font-bold text-purple-600"><?= $trashStats['deleted_last_week'] ?? 0 ?></div>
                                            <div class="text-sm text-gray-600">This Week</div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Success/Error Messages -->
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                            <?= htmlspecialchars($_SESSION['success']) ?>
                            <?php unset($_SESSION['success']); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['errors'])): ?>
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                            <?php foreach ($_SESSION['errors'] as $error): ?>
                                <p><?= htmlspecialchars($error) ?></p>
                            <?php endforeach; ?>
                            <?php unset($_SESSION['errors']); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Search and Filter -->
                    <div class="mb-6 glass-effect rounded-lg p-4">
                        <div class="flex flex-wrap gap-4 items-center">
                            <div class="flex-1 min-w-64">
                                <input type="text" 
                                       id="trashSearch" 
                                       placeholder="Search deleted items..." 
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            <div class="flex gap-2">
                                <select id="trashFilter" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                    <option value="all">All Items</option>
                                    <option value="notes">Notes Only</option>
                                    <option value="tasks">Tasks Only</option>
                                </select>
                                <select id="trashSort" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                    <option value="newest">Newest First</option>
                                    <option value="oldest">Oldest First</option>
                                    <option value="title">By Title</option>
                                    <option value="type">By Type</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="mb-6 flex flex-wrap gap-3">
                        <form method="POST" action="/trash/empty" class="inline">
                            <input type="hidden" name="csrf_token" value="<?= \Core\CSRF::generate() ?>">
                            <button type="submit" 
                                    class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition-all duration-200 shadow-lg hover:shadow-xl"
                                    onclick="return confirm('Are you sure you want to permanently delete all items in trash? This action cannot be undone.')">
                                <i class="fas fa-trash mr-2"></i>
                                Empty Trash
                            </button>
                        </form>
                        
                        <form method="POST" action="/trash/auto-cleanup" class="inline">
                            <input type="hidden" name="csrf_token" value="<?= \Core\CSRF::generate() ?>">
                            <input type="hidden" name="days_old" value="30">
                            <button type="submit" 
                                    class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg transition-all duration-200 shadow-lg hover:shadow-xl"
                                    onclick="return confirm('This will permanently delete items older than 30 days. Continue?')">
                                <i class="fas fa-broom mr-2"></i>
                                Auto-Cleanup (30+ days)
                            </button>
                        </form>
                        
                        <button onclick="selectAll()" 
                                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-all duration-200 shadow-lg hover:shadow-xl">
                            <i class="fas fa-check-square mr-2"></i>
                            Select All
                        </button>
                        
                        <button onclick="bulkRestore()" 
                                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-all duration-200 shadow-lg hover:shadow-xl">
                            <i class="fas fa-undo mr-2"></i>
                            Restore Selected
                        </button>
                        
                        <button onclick="bulkDelete()" 
                                class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition-all duration-200 shadow-lg hover:shadow-xl">
                            <i class="fas fa-times mr-2"></i>
                            Delete Selected
                        </button>
                        
                        <button onclick="exportTrash()" 
                                class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition-all duration-200 shadow-lg hover:shadow-xl">
                            <i class="fas fa-download mr-2"></i>
                            Export Trash
                        </button>
                    </div>

                    <!-- Deleted Notes -->
                    <div class="mb-8">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">
                            <i class="fas fa-sticky-note mr-2"></i>
                            Deleted Notes (<?= count($deletedNotes ?? []) ?>)
                        </h2>
                        
                        <?php if (empty($deletedNotes)): ?>
                            <div class="glass-effect rounded-lg shadow-lg p-8 text-center text-gray-500">
                                <i class="fas fa-sticky-note text-6xl mb-4 text-gray-300"></i>
                                <p class="text-lg">No deleted notes found</p>
                                <p class="text-sm mt-2">Deleted notes will appear here</p>
                            </div>
                        <?php else: ?>
                            <div class="grid gap-4">
                                <?php foreach ($deletedNotes as $note): ?>
                                    <div class="trash-item glass-effect rounded-lg shadow-lg p-4 border-l-4 border-red-500">
                                        <div class="flex items-start gap-4">
                                            <input type="checkbox" 
                                                   class="note-checkbox mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                                   value="<?= $note['id'] ?>">
                                            
                                            <div class="flex-1">
                                                <div class="flex items-center justify-between mb-2">
                                                    <h3 class="font-semibold text-gray-900 text-lg">
                                                        <?= htmlspecialchars($note['title']) ?>
                                                    </h3>
                                                    <div class="flex gap-2">
                                                        <form method="POST" action="/trash/restore-note" class="inline">
                                                            <input type="hidden" name="csrf_token" value="<?= \Core\CSRF::generate() ?>">
                                                            <input type="hidden" name="note_id" value="<?= $note['id'] ?>">
                                                            <button type="submit" 
                                                                    class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-sm transition-colors duration-200"
                                                                    title="Restore Note">
                                                                <i class="fas fa-undo"></i>
                                                            </button>
                                                        </form>
                                                        
                                                        <form method="POST" action="/trash/permanent-delete-note" class="inline">
                                                            <input type="hidden" name="csrf_token" value="<?= \Core\CSRF::generate() ?>">
                                                            <input type="hidden" name="note_id" value="<?= $note['id'] ?>">
                                                            <button type="submit" 
                                                                    class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm transition-colors duration-200"
                                                                    title="Permanently Delete"
                                                                    onclick="return confirm('Are you sure you want to permanently delete this note? This action cannot be undone.')">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                                
                                                <div class="flex items-center gap-4 text-sm text-gray-600 mb-3">
                                                    <span>
                                                        <i class="fas fa-calendar-times mr-1"></i>
                                                        Deleted: <?= date('M j, Y g:i A', strtotime($note['deleted_at'])) ?>
                                                    </span>
                                                    <?php if ($note['priority']): ?>
                                                        <span class="px-2 py-1 bg-gray-100 rounded-full text-xs">
                                                            Priority: <?= ucfirst($note['priority']) ?>
                                                        </span>
                                                    <?php endif; ?>
                                                    <?php if ($note['word_count']): ?>
                                                        <span class="px-2 py-1 bg-gray-100 rounded-full text-xs">
                                                            <?= $note['word_count'] ?> words
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                                
                                                <?php if (!empty($note['summary'])): ?>
                                                    <div class="text-gray-700 bg-gray-50 rounded p-3 mb-3">
                                                        <p class="text-sm font-medium mb-1">Summary:</p>
                                                        <p class="text-sm"><?= htmlspecialchars($note['summary']) ?></p>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <?php if (!empty($note['content'])): ?>
                                                    <div class="text-gray-700 bg-gray-50 rounded p-3">
                                                        <p class="text-sm font-medium mb-1">Content Preview:</p>
                                                        <p class="text-sm"><?= htmlspecialchars(substr(strip_tags($note['content']), 0, 200)) ?><?= strlen(strip_tags($note['content'])) > 200 ? '...' : '' ?></p>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Deleted Tasks -->
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">
                            <i class="fas fa-check-square mr-2"></i>
                            Deleted Tasks (<?= count($deletedTasks ?? []) ?>)
                        </h2>
                        
                        <?php if (empty($deletedTasks)): ?>
                            <div class="glass-effect rounded-lg shadow-lg p-8 text-center text-gray-500">
                                <i class="fas fa-check-square text-6xl mb-4 text-gray-300"></i>
                                <p class="text-lg">No deleted tasks found</p>
                                <p class="text-sm mt-2">Deleted tasks will appear here</p>
                            </div>
                        <?php else: ?>
                            <div class="grid gap-4">
                                <?php foreach ($deletedTasks as $task): ?>
                                    <div class="trash-item glass-effect rounded-lg shadow-lg p-4 border-l-4 border-red-500">
                                        <div class="flex items-start gap-4">
                                            <input type="checkbox" 
                                                   class="task-checkbox mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                                   value="<?= $task['id'] ?>">
                                            
                                            <div class="flex-1">
                                                <div class="flex items-center justify-between mb-2">
                                                    <h3 class="font-semibold text-gray-900 text-lg">
                                                        <?= htmlspecialchars($task['title']) ?>
                                                    </h3>
                                                    <div class="flex gap-2">
                                                        <form method="POST" action="/trash/restore-task" class="inline">
                                                            <input type="hidden" name="csrf_token" value="<?= \Core\CSRF::generate() ?>">
                                                            <input type="hidden" name="task_id" value="<?= $task['id'] ?>">
                                                            <button type="submit" 
                                                                    class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-sm transition-colors duration-200"
                                                                    title="Restore Task">
                                                                <i class="fas fa-undo"></i>
                                                            </button>
                                                        </form>
                                                        
                                                        <form method="POST" action="/trash/permanent-delete-task" class="inline">
                                                            <input type="hidden" name="csrf_token" value="<?= \Core\CSRF::generate() ?>">
                                                            <input type="hidden" name="task_id" value="<?= $task['id'] ?>">
                                                            <button type="submit" 
                                                                    class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm transition-colors duration-200"
                                                                    title="Permanently Delete"
                                                                    onclick="return confirm('Are you sure you want to permanently delete this task? This action cannot be undone.')">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                                
                                                <div class="flex items-center gap-4 text-sm text-gray-600 mb-3">
                                                    <span>
                                                        <i class="fas fa-calendar-times mr-1"></i>
                                                        Deleted: <?= date('M j, Y g:i A', strtotime($task['deleted_at'])) ?>
                                                    </span>
                                                    <?php if ($task['status']): ?>
                                                        <span class="px-2 py-1 bg-gray-100 rounded-full text-xs">
                                                            Status: <?= ucfirst($task['status']) ?>
                                                        </span>
                                                    <?php endif; ?>
                                                    <?php if ($task['priority']): ?>
                                                        <span class="px-2 py-1 bg-gray-100 rounded-full text-xs">
                                                            Priority: <?= ucfirst($task['priority']) ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                                
                                                <?php if (!empty($task['description'])): ?>
                                                    <div class="text-gray-700 bg-gray-50 rounded p-3">
                                                        <p class="text-sm"><?= htmlspecialchars(substr($task['description'], 0, 200)) ?><?= strlen($task['description']) > 200 ? '...' : '' ?></p>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        // Select all checkboxes
        function selectAll() {
            const taskCheckboxes = document.querySelectorAll('.task-checkbox');
            const noteCheckboxes = document.querySelectorAll('.note-checkbox');
            const allCheckboxes = [...taskCheckboxes, ...noteCheckboxes];
            const allChecked = allCheckboxes.every(cb => cb.checked);
            
            allCheckboxes.forEach(checkbox => {
                checkbox.checked = !allChecked;
            });
            
            updateBulkButtons();
        }

        // Update bulk action buttons state
        function updateBulkButtons() {
            const checkedTaskBoxes = document.querySelectorAll('.task-checkbox:checked');
            const checkedNoteBoxes = document.querySelectorAll('.note-checkbox:checked');
            const totalChecked = checkedTaskBoxes.length + checkedNoteBoxes.length;
            const bulkButtons = document.querySelectorAll('button[onclick^="bulk"]');
            
            bulkButtons.forEach(button => {
                button.disabled = totalChecked === 0;
                button.classList.toggle('opacity-50', totalChecked === 0);
                button.classList.toggle('cursor-not-allowed', totalChecked === 0);
            });
        }

        // Bulk restore selected items
        function bulkRestore() {
            const checkedTaskBoxes = document.querySelectorAll('.task-checkbox:checked');
            const checkedNoteBoxes = document.querySelectorAll('.note-checkbox:checked');
            const totalChecked = checkedTaskBoxes.length + checkedNoteBoxes.length;
            
            if (totalChecked === 0) {
                alert('Please select items to restore.');
                return;
            }

            if (confirm(`Are you sure you want to restore ${totalChecked} selected item(s)?`)) {
                // Restore tasks
                if (checkedTaskBoxes.length > 0) {
                    const taskForm = document.createElement('form');
                    taskForm.method = 'POST';
                    taskForm.action = '/trash/bulk-restore';
                    
                    const csrfToken = document.createElement('input');
                    csrfToken.type = 'hidden';
                    csrfToken.name = 'csrf_token';
                    csrfToken.value = '<?= \Core\CSRF::generate() ?>';
                    taskForm.appendChild(csrfToken);

                    checkedTaskBoxes.forEach(checkbox => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'task_ids[]';
                        input.value = checkbox.value;
                        taskForm.appendChild(input);
                    });

                    document.body.appendChild(taskForm);
                    taskForm.submit();
                }
                
                // Restore notes
                if (checkedNoteBoxes.length > 0) {
                    const noteForm = document.createElement('form');
                    noteForm.method = 'POST';
                    noteForm.action = '/trash/bulk-restore-notes';
                    
                    const csrfToken = document.createElement('input');
                    csrfToken.type = 'hidden';
                    csrfToken.name = 'csrf_token';
                    csrfToken.value = '<?= \Core\CSRF::generate() ?>';
                    noteForm.appendChild(csrfToken);

                    checkedNoteBoxes.forEach(checkbox => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'note_ids[]';
                        input.value = checkbox.value;
                        noteForm.appendChild(input);
                    });

                    document.body.appendChild(noteForm);
                    noteForm.submit();
                }
            }
        }

        // Bulk delete selected items
        function bulkDelete() {
            const checkedTaskBoxes = document.querySelectorAll('.task-checkbox:checked');
            const checkedNoteBoxes = document.querySelectorAll('.note-checkbox:checked');
            const totalChecked = checkedTaskBoxes.length + checkedNoteBoxes.length;
            
            if (totalChecked === 0) {
                alert('Please select items to delete.');
                return;
            }

            if (confirm(`Are you sure you want to permanently delete ${totalChecked} selected item(s)? This action cannot be undone.`)) {
                // Delete tasks
                if (checkedTaskBoxes.length > 0) {
                    const taskForm = document.createElement('form');
                    taskForm.method = 'POST';
                    taskForm.action = '/trash/bulk-delete';
                    
                    const csrfToken = document.createElement('input');
                    csrfToken.type = 'hidden';
                    csrfToken.name = 'csrf_token';
                    csrfToken.value = '<?= \Core\CSRF::generate() ?>';
                    taskForm.appendChild(csrfToken);

                    checkedTaskBoxes.forEach(checkbox => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'task_ids[]';
                        input.value = checkbox.value;
                        taskForm.appendChild(input);
                    });

                    document.body.appendChild(taskForm);
                    taskForm.submit();
                }
                
                // Delete notes
                if (checkedNoteBoxes.length > 0) {
                    const noteForm = document.createElement('form');
                    noteForm.method = 'POST';
                    noteForm.action = '/trash/bulk-delete-notes';
                    
                    const csrfToken = document.createElement('input');
                    csrfToken.type = 'hidden';
                    csrfToken.name = 'csrf_token';
                    csrfToken.value = '<?= \Core\CSRF::generate() ?>';
                    noteForm.appendChild(csrfToken);

                    checkedNoteBoxes.forEach(checkbox => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'note_ids[]';
                        input.value = checkbox.value;
                        noteForm.appendChild(input);
                    });

                    document.body.appendChild(noteForm);
                    noteForm.submit();
                }
            }
        }

        // Export trash functionality
        function exportTrash() {
            const format = prompt('Choose export format:\n1. JSON\n2. CSV\n3. Text\n\nEnter 1, 2, or 3:');
            
            if (!format || !['1', '2', '3'].includes(format)) {
                return;
            }
            
            const formatMap = {
                '1': 'json',
                '2': 'csv', 
                '3': 'txt'
            };
            
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/trash/export';
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = 'csrf_token';
            csrfToken.value = '<?= \Core\CSRF::generate() ?>';
            form.appendChild(csrfToken);
            
            const formatInput = document.createElement('input');
            formatInput.type = 'hidden';
            formatInput.name = 'format';
            formatInput.value = formatMap[format];
            form.appendChild(formatInput);
            
            document.body.appendChild(form);
            form.submit();
        }

        // Search and filter functionality
        function filterTrashItems() {
            const searchTerm = document.getElementById('trashSearch').value.toLowerCase();
            const filterType = document.getElementById('trashFilter').value;
            const sortBy = document.getElementById('trashSort').value;
            
            const allItems = document.querySelectorAll('.trash-item');
            let visibleItems = [];
            
            allItems.forEach(item => {
                const title = item.querySelector('h3').textContent.toLowerCase();
                const content = item.textContent.toLowerCase();
                const isNote = item.querySelector('.note-checkbox') !== null;
                const isTask = item.querySelector('.task-checkbox') !== null;
                
                // Filter by search term
                const matchesSearch = searchTerm === '' || title.includes(searchTerm) || content.includes(searchTerm);
                
                // Filter by type
                let matchesType = true;
                if (filterType === 'notes' && !isNote) matchesType = false;
                if (filterType === 'tasks' && !isTask) matchesType = false;
                
                if (matchesSearch && matchesType) {
                    item.style.display = 'block';
                    visibleItems.push(item);
                } else {
                    item.style.display = 'none';
                }
            });
            
            // Sort visible items
            visibleItems.sort((a, b) => {
                const titleA = a.querySelector('h3').textContent.toLowerCase();
                const titleB = b.querySelector('h3').textContent.toLowerCase();
                const dateA = new Date(a.querySelector('[class*="calendar-times"]').parentElement.textContent.split(': ')[1]);
                const dateB = new Date(b.querySelector('[class*="calendar-times"]').parentElement.textContent.split(': ')[1]);
                
                switch (sortBy) {
                    case 'oldest':
                        return dateA - dateB;
                    case 'title':
                        return titleA.localeCompare(titleB);
                    case 'type':
                        const typeA = a.querySelector('.note-checkbox') ? 'note' : 'task';
                        const typeB = b.querySelector('.note-checkbox') ? 'note' : 'task';
                        return typeA.localeCompare(typeB);
                    default: // newest
                        return dateB - dateA;
                }
            });
            
            // Reorder items in DOM
            const containers = document.querySelectorAll('.grid.gap-4');
            containers.forEach(container => {
                visibleItems.forEach(item => {
                    if (container.contains(item)) {
                        container.appendChild(item);
                    }
                });
            });
        }

        // Add event listeners to checkboxes
        document.addEventListener('DOMContentLoaded', function() {
            const taskCheckboxes = document.querySelectorAll('.task-checkbox');
            const noteCheckboxes = document.querySelectorAll('.note-checkbox');
            
            taskCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', updateBulkButtons);
            });
            
            noteCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', updateBulkButtons);
            });
            
            // Add search and filter event listeners
            document.getElementById('trashSearch').addEventListener('input', filterTrashItems);
            document.getElementById('trashFilter').addEventListener('change', filterTrashItems);
            document.getElementById('trashSort').addEventListener('change', filterTrashItems);
            
            updateBulkButtons();
        });

        // Auto-hide success/error messages after 5 seconds
        setTimeout(() => {
            const messages = document.querySelectorAll('.bg-green-100, .bg-red-100');
            messages.forEach(msg => {
                msg.style.transition = 'opacity 0.5s ease';
                msg.style.opacity = '0';
                setTimeout(() => msg.remove(), 500);
            });
        }, 5000);
    </script>
</body>
</html>
