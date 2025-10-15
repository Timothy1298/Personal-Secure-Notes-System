<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Archived Items | Secure Notes</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    // Suppress Tailwind production warning for development
    tailwind.config = {
      theme: {
        extend: {}
      }
    }
  </script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
    
    * {
      font-family: 'Inter', sans-serif;
    }
    
    .toast {
      position: fixed;
      top: 20px;
      right: 20px;
      z-index: 10000;
      padding: 16px 20px;
      border-radius: 12px;
      color: white;
      font-weight: 500;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
      transform: translateX(400px);
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      max-width: 400px;
      display: flex;
      align-items: center;
      gap: 12px;
    }
    
    .toast.show {
      transform: translateX(0);
    }
    
    .toast.success {
      background: linear-gradient(135deg, #10b981, #059669);
    }
    
    .toast.error {
      background: linear-gradient(135deg, #ef4444, #dc2626);
    }
    
    .toast.warning {
      background: linear-gradient(135deg, #f59e0b, #d97706);
    }
    
    .toast.info {
      background: linear-gradient(135deg, #3b82f6, #2563eb);
    }
    
    .toast-close {
      background: none;
      border: none;
      color: white;
      cursor: pointer;
      padding: 4px;
      border-radius: 4px;
      opacity: 0.8;
      transition: opacity 0.2s;
    }
    
    .toast-close:hover {
      opacity: 1;
    }
    
    .confirmation-modal {
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, 0.5);
      backdrop-filter: blur(4px);
      z-index: 10000;
      display: flex;
      align-items: center;
      justify-content: center;
      opacity: 0;
      visibility: hidden;
      transition: all 0.3s ease;
    }
    
    .confirmation-modal.show {
      opacity: 1;
      visibility: visible;
    }
    
    .confirmation-content {
      background: white;
      border-radius: 16px;
      padding: 24px;
      max-width: 400px;
      width: 90%;
      box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
      transform: scale(0.9);
      transition: transform 0.3s ease;
    }
    
    .confirmation-modal.show .confirmation-content {
      transform: scale(1);
    }
  </style>
</head>
<body class="bg-gray-100 h-screen flex">
  <?php include __DIR__ . '/partials/sidebar.php'; ?>
  <div class="flex-1 flex flex-col">
    <?php include __DIR__ . '/partials/navbar.php'; ?>
    <main class="p-6 flex-1 overflow-y-auto">
      <!-- Hidden CSRF token for AJAX requests -->
      <input type="hidden" name="csrf_token" value="<?= \Core\CSRF::generate() ?>">
      
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-3xl font-bold text-gray-800">Archived Items</h2>
      </div>

      <?php if (isset($_SESSION['success'])): ?>
          <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
              <span class="block sm:inline"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></span>
          </div>
      <?php endif; ?>
      <?php if (isset($_SESSION['errors'])): ?>
          <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
              <?php foreach ($_SESSION['errors'] as $error): ?>
                  <span class="block sm:inline"><?= htmlspecialchars($error); ?></span>
              <?php endforeach; unset($_SESSION['errors']); ?>
          </div>
      <?php endif; ?>

      <div class="mb-8 p-4 bg-white rounded-lg shadow-md flex flex-col md:flex-row items-center gap-4">
        <div class="relative w-full md:w-1/2">
          <input type="text" id="searchInput" placeholder="Search archived items..." class="w-full pl-10 pr-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-700">
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <i class="fas fa-search text-gray-400"></i>
          </div>
        </div>
        <div class="w-full md:w-1/2 flex items-center gap-4">
          <label for="filterSelect" class="text-gray-600 font-medium whitespace-nowrap">Filter by:</label>
          <select id="filterSelect" class="w-full md:w-auto px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-700">
            <option value="all">All Items</option>
            <option value="notes">Notes</option>
            <option value="tasks">Tasks</option>
          </select>
        </div>
      </div>
      <div class="space-y-8">
        <div id="notesContainer">
          <h3 class="text-xl font-semibold text-gray-700 mb-4">
            <i class="fas fa-sticky-note mr-2 text-blue-500"></i>Archived Notes
          </h3>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php if (!empty($notes)): ?>
              <?php foreach ($notes as $note): ?>
                <div class="archived-item note-item bg-white rounded-lg shadow-md p-6 border-l-4 border-gray-400">
                  <h4 class="text-lg font-bold text-gray-800 mb-2"><?= htmlspecialchars($note['title']) ?></h4>
                  <p class="text-gray-600 mb-4 text-sm"><?= nl2br(htmlspecialchars(substr($note['content'], 0, 150))) ?><?php if (strlen($note['content']) > 150) echo '...'; ?></p>
                  <div class="flex justify-end">
                      <button onclick="confirmUnarchiveNote(<?= $note['id'] ?>)" class="text-sm px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 transition-colors duration-200">
                          <i class="fas fa-undo-alt mr-1"></i> Unarchive
                      </button>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <p class="text-gray-500 empty-message note-item">No notes have been archived yet.</p>
            <?php endif; ?>
          </div>
        </div>

        <div id="tasksContainer">
          <h3 class="text-xl font-semibold text-gray-700 mb-4">
            <i class="fas fa-check-circle mr-2 text-blue-500"></i>Archived Tasks
          </h3>
          <div class="space-y-4">
            <?php if (!empty($tasks)): ?>
              <?php foreach ($tasks as $task): ?>
                <div class="archived-item task-item bg-white rounded-lg shadow-md p-4 border-l-4 border-gray-400">
                  <div class="flex justify-between items-center">
                    <p class="text-lg text-gray-800 font-semibold"><?= htmlspecialchars($task['description']) ?></p>
                    <div class="flex space-x-2">
                        <button onclick="confirmUnarchiveTask(<?= $task['id'] ?>)" class="text-sm px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 transition-colors duration-200">
                            <i class="fas fa-undo-alt mr-1"></i> Unarchive
                        </button>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <p class="text-gray-500 empty-message task-item">No tasks have been archived yet.</p>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </main>
    <?php include __DIR__ . '/partials/footer.php'; ?>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const searchInput = document.getElementById('searchInput');
      const filterSelect = document.getElementById('filterSelect');
      const allItems = document.querySelectorAll('.archived-item');
      const notesContainer = document.getElementById('notesContainer');
      const tasksContainer = document.getElementById('tasksContainer');
      
      const noteEmptyMessage = notesContainer.querySelector('.empty-message');
      const taskEmptyMessage = tasksContainer.querySelector('.empty-message');

      function updateDisplay() {
        const searchTerm = searchInput.value.toLowerCase();
        const filterType = filterSelect.value;
        let notesFound = false;
        let tasksFound = false;

        allItems.forEach(item => {
          const content = item.innerText.toLowerCase();
          const matchesSearch = content.includes(searchTerm);
          const isNote = item.classList.contains('note-item');
          const isTask = item.classList.contains('task-item');

          let matchesFilter = false;
          if (filterType === 'all') {
            matchesFilter = true;
          } else if (filterType === 'notes' && isNote) {
            matchesFilter = true;
          } else if (filterType === 'tasks' && isTask) {
            matchesFilter = true;
          }

          if (matchesSearch && matchesFilter) {
            item.style.display = '';
            if (isNote) notesFound = true;
            if (isTask) tasksFound = true;
          } else {
            item.style.display = 'none';
          }
        });

        // Toggle container visibility based on filter
        notesContainer.style.display = (filterType === 'tasks') ? 'none' : '';
        tasksContainer.style.display = (filterType === 'notes') ? 'none' : '';

        // Show/hide "No items" messages
        if (noteEmptyMessage) {
          noteEmptyMessage.style.display = (filterType !== 'tasks' && !notesFound) ? '' : 'none';
        }
        if (taskEmptyMessage) {
          taskEmptyMessage.style.display = (filterType !== 'notes' && !tasksFound) ? '' : 'none';
        }
      }

      searchInput.addEventListener('input', updateDisplay);
      filterSelect.addEventListener('change', updateDisplay);
      
      // Initial display update on page load
      updateDisplay();
    });

    // Toast message system
    function showToast(message, type = 'info', duration = 4000) {
      const toast = document.createElement('div');
      toast.className = `toast ${type}`;
      toast.innerHTML = `
        <i class="fas fa-${getToastIcon(type)}"></i>
        <span>${message}</span>
        <button class="toast-close" onclick="closeToast(this)">
          <i class="fas fa-times"></i>
        </button>
      `;
      
      document.body.appendChild(toast);
      
      // Show toast
      setTimeout(() => toast.classList.add('show'), 100);
      
      // Auto hide
      setTimeout(() => closeToast(toast), duration);
    }
    
    function getToastIcon(type) {
      const icons = {
        success: 'check-circle',
        error: 'exclamation-circle',
        warning: 'exclamation-triangle',
        info: 'info-circle'
      };
      return icons[type] || 'info-circle';
    }
    
    function closeToast(toast) {
      if (typeof toast === 'object' && toast.classList) {
        toast.classList.remove('show');
        setTimeout(() => {
          if (toast.parentNode) {
            toast.remove();
          }
        }, 300);
      } else {
        const toastElement = toast.parentNode;
        toastElement.classList.remove('show');
        setTimeout(() => {
          if (toastElement.parentNode) {
            toastElement.remove();
          }
        }, 300);
      }
    }
    
    // Confirmation modal system
    function showConfirmationModal(title, message, onConfirm, onCancel = null) {
      const modal = document.createElement('div');
      modal.className = 'confirmation-modal';
      modal.innerHTML = `
        <div class="confirmation-content">
          <div class="text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 mb-4">
              <i class="fas fa-exclamation-triangle text-yellow-600 text-xl"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">${title}</h3>
            <p class="text-sm text-gray-500 mb-6">${message}</p>
            <div class="flex gap-3 justify-center">
              <button onclick="closeConfirmationModal(false)" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500">
                Cancel
              </button>
              <button onclick="closeConfirmationModal(true)" class="px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                Confirm
              </button>
            </div>
          </div>
        </div>
      `;
      
      document.body.appendChild(modal);
      
      // Store callbacks
      modal._onConfirm = onConfirm;
      modal._onCancel = onCancel;
      
      // Show modal
      setTimeout(() => modal.classList.add('show'), 10);
    }
    
    function closeConfirmationModal(confirmed) {
      const modal = document.querySelector('.confirmation-modal');
      if (!modal) return;
      
      modal.classList.remove('show');
      setTimeout(() => {
        if (modal.parentNode) {
          modal.remove();
        }
      }, 300);
      
      if (confirmed && modal._onConfirm) {
        modal._onConfirm();
      } else if (!confirmed && modal._onCancel) {
        modal._onCancel();
      }
    }
    
    // Unarchive functions
    function confirmUnarchiveNote(noteId) {
      showConfirmationModal(
        'Unarchive Note',
        'Are you sure you want to unarchive this note? It will be moved back to your notes.',
        () => unarchiveNote(noteId)
      );
    }
    
    function confirmUnarchiveTask(taskId) {
      showConfirmationModal(
        'Unarchive Task',
        'Are you sure you want to unarchive this task? It will be moved back to your tasks.',
        () => unarchiveTask(taskId)
      );
    }
    
    function unarchiveNote(noteId) {
      fetch('/archived/note/unarchive', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
          note_id: noteId,
          csrf_token: document.querySelector('input[name="csrf_token"]').value
        })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          showToast('Note unarchived successfully!', 'success');
          // Remove the note from the page
          const noteElement = document.querySelector(`button[onclick="confirmUnarchiveNote(${noteId})"]`).closest('.archived-item');
          if (noteElement) {
            noteElement.style.transition = 'all 0.3s ease';
            noteElement.style.opacity = '0';
            noteElement.style.transform = 'translateX(-100%)';
            setTimeout(() => {
              if (noteElement.parentNode) {
                noteElement.remove();
              }
            }, 300);
          }
        } else {
          showToast(data.message || 'Failed to unarchive note', 'error');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred while unarchiving the note', 'error');
      });
    }
    
    function unarchiveTask(taskId) {
      fetch('/archived/task/unarchive', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
          task_id: taskId,
          csrf_token: document.querySelector('input[name="csrf_token"]').value
        })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          showToast('Task unarchived successfully!', 'success');
          // Remove the task from the page
          const taskElement = document.querySelector(`button[onclick="confirmUnarchiveTask(${taskId})"]`).closest('.archived-item');
          if (taskElement) {
            taskElement.style.transition = 'all 0.3s ease';
            taskElement.style.opacity = '0';
            taskElement.style.transform = 'translateX(-100%)';
            setTimeout(() => {
              if (taskElement.parentNode) {
                taskElement.remove();
              }
            }, 300);
          }
        } else {
          showToast(data.message || 'Failed to unarchive task', 'error');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred while unarchiving the task', 'error');
      });
    }
  </script>
</body>
</html>