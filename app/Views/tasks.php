<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Tasks | Secure Notes</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
  <style>
    .loader-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(255, 255, 255, 0.8);
      display: flex;
      justify-content: center;
      align-items: center;
      z-index: 9999;
      backdrop-filter: blur(5px);
    }
    .spinner {
      border: 4px solid rgba(0, 0, 0, 0.1);
      width: 36px;
      height: 36px;
      border-radius: 50%;
      border-left-color: #1e40af;
      animation: spin 1s ease infinite;
    }
    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }
    .modal-active { overflow: hidden; }
    .active-filter { background-color: #1e40af; color: white; font-weight: 600; }
    .task-completed { background-color: #f3f4f6; color: #9ca3af; border: 1px dashed #d1d5db; }
    .task-completed h4, .task-completed p, .task-completed span { color: #9ca3af; }
  </style>
</head>
<body class="bg-gray-100 h-screen flex font-sans antialiased">
<?php 
if (empty($tags)) {
    $tags = [
        ['id' => 1, 'name' => 'Work'],
        ['id' => 2, 'name' => 'Personal'],
        ['id' => 3, 'name' => 'Urgent'],
    ];
}
?>
<?php include __DIR__ . '/partials/sidebar.php'; ?>

<div class="flex-1 flex flex-col">
  <?php include __DIR__ . '/partials/navbar.php'; ?>

  <main class="p-6 flex-1 overflow-y-auto">
    <div class="flex items-center justify-between mb-8 pb-4 border-b border-gray-200">
      <h2 class="text-3xl font-extrabold text-gray-800 tracking-tight">My Tasks</h2>
      <button onclick="openAddTaskModal()" class="px-6 py-3 bg-blue-600 text-white font-semibold rounded-full shadow-lg hover:bg-blue-700 transition-all duration-200 transform hover:scale-105 active:scale-95">
        <i class="fas fa-plus mr-2"></i>Add Task
      </button>
    </div>

    <div class="flex flex-col md:flex-row items-center justify-between gap-4 mb-6">
        <div class="relative w-full md:w-1/2">
            <input type="search" id="searchInput" oninput="filterTasks()" placeholder="Search tasks..." class="w-full pl-10 pr-4 py-3 rounded-lg border-2 border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors duration-200">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-search text-gray-400"></i>
            </div>
        </div>
        <div class="flex flex-col md:flex-row gap-4 w-full md:w-auto">
            <div class="flex space-x-2 bg-white rounded-lg p-1.5 shadow-sm">
                <button data-filter="status" data-value="all" onclick="setFilter(this)" class="px-4 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100 transition-colors duration-200">All</button>
                <button data-filter="status" data-value="active" onclick="setFilter(this)" class="px-4 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100 transition-colors duration-200 active-filter">Active</button>
                <button data-filter="status" data-value="completed" onclick="setFilter(this)" class="px-4 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100 transition-colors duration-200">Completed</button>
                <button data-filter="status" data-value="archived" onclick="setFilter(this)" class="px-4 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100 transition-colors duration-200">Archived</button>
            </div>
            <div class="relative w-full md:w-auto">
              <select id="tagFilter" onchange="filterTasks()" class="w-full px-4 py-2.5 rounded-lg border-2 border-gray-300 bg-white text-sm font-medium text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors duration-200 appearance-none">
                  <option value="all">All Tags</option>
                  <?php foreach ($tags as $tag): ?>
                    <option value="<?= htmlspecialchars($tag['id']) ?>"><?= htmlspecialchars($tag['name']) ?></option>
                  <?php endforeach; ?>
              </select>
              <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                  <i class="fas fa-chevron-down text-gray-400"></i>
              </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6" id="tasksGrid">
      <?php if (!empty($tasks)): ?>
        <?php foreach ($tasks as $task): ?>
          <?php
            $taskTags = explode(',', $task['tags'] ?? '');
            $tagIds = explode(',', $task['tag_ids'] ?? '');
          ?>
          <div 
            class="task-card bg-white rounded-xl shadow-lg ring-1 ring-gray-200 hover:shadow-2xl hover:ring-blue-500 transition-all duration-300 p-6 flex flex-col <?= isset($task['is_completed']) && $task['is_completed'] ? 'task-completed' : '' ?>"
            data-task-id="<?= htmlspecialchars($task['id']) ?>"
            data-task-title="<?= htmlspecialchars($task['title']) ?>"
            data-task-description="<?= htmlspecialchars($task['description']) ?>"
            data-is-completed="<?= isset($task['is_completed']) ? htmlspecialchars($task['is_completed']) : 0 ?>"
            data-is-archived="<?= isset($task['is_archived']) ? htmlspecialchars($task['is_archived']) : 0 ?>"
            data-task-tags="<?= htmlspecialchars($task['tags'] ?? '') ?>"
            data-tag-ids="<?= htmlspecialchars($task['tag_ids'] ?? '') ?>"
            data-updated-at="<?= htmlspecialchars($task['updated_at']) ?>"
            data-completed="<?= isset($task['completed']) ? htmlspecialchars($task['completed']) : 0 ?>"
      data-due-date="<?= isset($task['due_date']) ? htmlspecialchars($task['due_date']) : '' ?>"
          >
            <h4 class="text-xl font-extrabold text-gray-900 mb-2 border-b border-gray-100 pb-2"><?= htmlspecialchars($task['title']) ?></h4>
            <p class="text-gray-700 text-sm leading-relaxed flex-1 overflow-hidden" style="display: -webkit-box; -webkit-line-clamp: 5; -webkit-box-orient: vertical;"><?= htmlspecialchars($task['description']) ?></p>
                <?php if (!empty($task['due_date'])): ?>
            <div class="mt-4 pt-2 border-t border-gray-100 flex items-center justify-between text-sm text-gray-600">
                <span><i class="fas fa-calendar-alt mr-2"></i>Due: <?= date('M d, Y', strtotime($task['due_date'])) ?></span>
            </div>
        <?php endif; ?>
            <div class="flex items-center mt-4 pt-2 border-t border-gray-100">
              <span class="text-xs text-gray-400" data-timestamp="<?= htmlspecialchars($task['updated_at']) ?>"></span>
              <div class="flex flex-wrap gap-2 ml-auto">
                <?php if (!empty($taskTags)): ?>
                  <?php foreach ($taskTags as $tag): ?>
                    <?php if (!empty($tag)): ?>
                      <span class="tag-badge text-xs font-semibold px-2 py-1 rounded-full text-white" style="background-color: var(--tag-color-<?= str_replace(' ', '-', strtolower($tag)) ?>)">
                        <?= htmlspecialchars($tag) ?>
                      </span>
                    <?php endif; ?>
                  <?php endforeach; ?>
                <?php endif; ?>
              </div>
            </div>

            <div class="flex justify-end space-x-2 mt-auto pt-4 border-t border-gray-100">
              <?php if (isset($task['is_completed']) && $task['is_completed']): ?>
                <form action="/tasks/uncomplete" method="POST" class="confirm-action-form" data-message="Are you sure you want to mark this task as not completed?">
                    <input type="hidden" name="task_id" value="<?= htmlspecialchars($task['id']) ?>">
                    <button type="submit" class="p-3 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition duration-300" title="Mark as Not Complete">
                        <i class="fas fa-times"></i>
                    </button>
                </form>
              <?php else: ?>
                <form action="/tasks/complete" method="POST" class="confirm-action-form" data-message="Are you sure you want to mark this task as completed?">
                    <input type="hidden" name="task_id" value="<?= htmlspecialchars($task['id']) ?>">
                    <button type="submit" class="p-3 bg-green-500 hover:bg-green-600 text-white rounded-lg transition duration-300" title="Mark as Complete">
                        <i class="fas fa-check"></i>
                    </button>
                </form>
              <?php endif; ?>

              <button onclick="openEditTaskModal(this)" class="p-3 bg-yellow-500 text-white rounded-full shadow-md hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-400 transition-all duration-200 transform hover:scale-110 active:scale-95" title="Edit">
                  <i class="fas fa-edit"></i>
              </button>
              <form action="/tasks/delete" method="POST" class="confirm-action-form" data-message="Are you sure you want to delete this task?">
                  <input type="hidden" name="task_id" value="<?= htmlspecialchars($task['id']) ?>">
                  <button type="submit" class="p-3 bg-red-500 hover:bg-red-600 text-white rounded-lg transition duration-300">
                      <i class="fas fa-trash"></i>
                  </button>
              </form>
              <form action="/tasks/archive" method="POST" class="confirm-action-form" data-message="Are you sure you want to archive this task?">
                  <input type="hidden" name="task_id" value="<?= htmlspecialchars($task['id']) ?>">
                  <button type="submit" class="p-3 bg-gray-500 hover:bg-gray-600 text-white rounded-lg transition duration-300">
                      <i class="fas fa-archive"></i>
                  </button>
              </form>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="sm:col-span-2 lg:col-span-3 text-center py-12 bg-white rounded-xl shadow-md" id="noTasksMessage">
          <i class="fas fa-tasks text-6xl text-gray-300 mb-4"></i>
          <p class="text-gray-500 text-lg font-medium">You don't have any tasks yet. Click the "Add Task" button to create one.</p>
        </div>
      <?php endif; ?>
    </div>
  </main>
<?php include __DIR__ . '/partials/footer.php'; ?>
</div>

<div id="loader" class="loader-overlay hidden">
    <div class="spinner"></div>
</div>

<div id="confirmationModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
  <div class="bg-white rounded-xl shadow-2xl p-8 w-11/12 md:w-1/4 relative transition-all duration-300 transform scale-95 opacity-0">
    <h3 class="text-xl font-bold text-gray-800 mb-4 border-b border-gray-200 pb-2">Confirm Action</h3>
    <p id="confirmationMessage" class="text-gray-700 mb-6"></p>
    <div class="flex justify-end space-x-2">
      <button type="button" onclick="closeModal('confirmationModal')" class="px-6 py-2 rounded-lg bg-gray-300 text-gray-800 font-medium hover:bg-gray-400 transition-colors duration-200">Cancel</button>
      <button type="button" id="confirmActionButton" class="px-6 py-2 rounded-lg bg-blue-600 text-white font-medium hover:bg-blue-700 transition-colors duration-200">Confirm</button>
    </div>
  </div>
</div>

<div id="addTaskModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
  <div class="bg-white rounded-xl shadow-2xl p-8 w-11/12 md:w-1/3 relative transition-all duration-300 transform scale-95 opacity-0">
    <h3 class="text-2xl font-bold text-gray-800 mb-6 border-b border-gray-200 pb-2">Add New Task</h3>
    <form id="addTaskForm" method="POST" action="/tasks/store">
      <label for="add-title" class="block mb-2 text-sm font-semibold text-gray-700">Task Title</label>
      <input type="text" id="add-title" name="title" class="w-full p-3 border border-gray-300 rounded-lg mb-4 focus:outline-none focus:ring-2 focus:ring-blue-500" required>

      <label for="add-description" class="block mb-2 text-sm font-semibold text-gray-700">Description</label>
      <textarea id="add-description" name="description" class="w-full p-3 border border-gray-300 rounded-lg mb-6 focus:outline-none focus:ring-2 focus:ring-blue-500" rows="8"></textarea>
      
      <label for="add-tags" class="block mb-2 text-sm font-semibold text-gray-700">Tags</label>
      <select id="add-tags" name="tags[]" multiple class="w-full p-3 border border-gray-300 rounded-lg mb-6 focus:outline-none focus:ring-2 focus:ring-blue-500">
        <?php foreach ($tags as $tag): ?>
          <option value="<?= htmlspecialchars($tag['id']) ?>"><?= htmlspecialchars($tag['name']) ?></option>
        <?php endforeach; ?>
      </select>

      <div class="flex justify-end space-x-2">
        <button type="button" onclick="closeModal('addTaskModal')" class="px-6 py-2 rounded-lg bg-gray-300 text-gray-800 font-medium hover:bg-gray-400 transition-colors duration-200">Cancel</button>
        <button type="submit" class="px-6 py-2 rounded-lg bg-blue-600 text-white font-medium hover:bg-blue-700 transition-colors duration-200">Save</button>
      </div>
    </form>
  </div>
</div>

<div id="editTaskModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
  <div class="bg-white rounded-xl shadow-2xl p-8 w-11/12 md:w-1/3 relative transition-all duration-300 transform scale-95 opacity-0">
    <h3 class="text-2xl font-bold text-gray-800 mb-6 border-b border-gray-200 pb-2">Edit Task</h3>
    <form id="editTaskForm" method="POST" action="/tasks/update">
      <input type="hidden" id="edit-task-id" name="id">
      <label for="edit-title" class="block mb-2 text-sm font-semibold text-gray-700">Task Title</label>
      <input type="text" id="edit-title" name="title" class="w-full p-3 border border-gray-300 rounded-lg mb-4 focus:outline-none focus:ring-2 focus:ring-blue-500" required>

      <label for="edit-description" class="block mb-2 text-sm font-semibold text-gray-700">Description</label>
      <textarea id="edit-description" name="description" class="w-full p-3 border border-gray-300 rounded-lg mb-6 focus:outline-none focus:ring-2 focus:ring-blue-500" rows="8"></textarea>

      <label for="edit-tags" class="block mb-2 text-sm font-semibold text-gray-700">Tags</label>
      <select id="edit-tags" name="tags[]" multiple class="w-full p-3 border border-gray-300 rounded-lg mb-6 focus:outline-none focus:ring-2 focus:ring-blue-500">
        <?php foreach ($tags as $tag): ?>
          <option value="<?= htmlspecialchars($tag['id']) ?>"><?= htmlspecialchars($tag['name']) ?></option>
        <?php endforeach; ?>
      </select>

      <div class="flex justify-end space-x-2">
        <button type="button" onclick="closeModal('editTaskModal')" class="px-6 py-2 rounded-lg bg-gray-300 text-gray-800 font-medium hover:bg-gray-400 transition-colors duration-200">Cancel</button>
        <button type="submit" class="px-6 py-2 rounded-lg bg-blue-600 text-white font-medium hover:bg-blue-700 transition-colors duration-200">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<script>
let pendingForm = null;
let currentFilters = { status: 'active' };
const allTags = <?= json_encode($tags) ?>;
const tagColors = {};

document.addEventListener('DOMContentLoaded', (event) => {
  generateTagColors();
  applyTagColorsToDOM();
  formatAllTimestamps();
  filterTasks();

  document.getElementById('addTaskForm').addEventListener('submit', function(e) {
      e.preventDefault();
      submitFormWithAjax(this);
  });

  document.getElementById('editTaskForm').addEventListener('submit', function(e) {
      e.preventDefault();
      submitFormWithAjax(this);
  });

  document.querySelectorAll('.confirm-action-form').forEach(form => {
      form.addEventListener('submit', function(e) {
          e.preventDefault();
          pendingForm = this;
          const message = this.dataset.message || "Are you sure?";
          document.getElementById('confirmationMessage').textContent = message;
          showModal('confirmationModal');
      });
  });

  document.getElementById('confirmActionButton').addEventListener('click', () => {
      if (pendingForm) {
          showLoader();
          submitFormWithAjax(pendingForm);
          closeModal('confirmationModal');
          pendingForm = null;
      }
  });
});

function setFilter(button) {
  const filterType = button.dataset.filter;
  const filterValue = button.dataset.value;
  
  document.querySelectorAll(`[data-filter="${filterType}"]`).forEach(btn => {
    btn.classList.remove('active-filter');
  });

  button.classList.add('active-filter');

  currentFilters[filterType] = filterValue;
  filterTasks();
}

function filterTasks() {
  const searchTerm = document.getElementById('searchInput').value.toLowerCase();
  const tagFilterValue = document.getElementById('tagFilter').value;
  const statusFilterValue = currentFilters.status;
  
  const allTasks = document.querySelectorAll('.task-card');
  const noTasksMessage = document.getElementById('noTasksMessage');
  let tasksFound = false;

  allTasks.forEach(task => {
    const title = task.dataset.taskTitle.toLowerCase();
    const description = task.dataset.taskDescription.toLowerCase();
    const isCompleted = task.dataset.isCompleted === '1';
    const isArchived = task.dataset.isArchived === '1';
    const taskTagIds = task.dataset.tagIds.split(',');

    const matchesSearch = title.includes(searchTerm) || description.includes(searchTerm);
    const matchesTag = tagFilterValue === 'all' || taskTagIds.includes(tagFilterValue);

    let matchesStatus = false;
    if (statusFilterValue === 'all') {
      matchesStatus = true;
    } else if (statusFilterValue === 'active') {
      matchesStatus = !isCompleted && !isArchived;
    } else if (statusFilterValue === 'completed') {
      matchesStatus = isCompleted;
    } else if (statusFilterValue === 'archived') {
      matchesStatus = isArchived;
    }

    if (matchesSearch && matchesStatus && matchesTag) {
      task.style.display = 'flex';
      tasksFound = true;
    } else {
      task.style.display = 'none';
    }
  });

  if (noTasksMessage) {
    noTasksMessage.style.display = tasksFound ? 'none' : 'block';
  }
}

function showModal(modalId) {
  const modal = document.getElementById(modalId);
  modal.classList.remove('hidden');
  modal.classList.add('flex');
  setTimeout(() => {
    modal.querySelector('div').classList.remove('scale-95', 'opacity-0');
    modal.querySelector('div').classList.add('scale-100', 'opacity-100');
  }, 50);
  document.body.classList.add('modal-active');
}

function closeModal(modalId) {
  const modal = document.getElementById(modalId);
  modal.querySelector('div').classList.add('scale-95', 'opacity-0');
  modal.querySelector('div').classList.remove('scale-100', 'opacity-100');
  setTimeout(() => {
    modal.classList.remove('flex');
    modal.classList.add('hidden');
    document.body.classList.remove('modal-active');
  }, 300);
  pendingForm = null;
}

function openAddTaskModal() {
  document.getElementById('addTaskForm').reset();
  showModal('addTaskModal');
}

function openEditTaskModal(button) {
  const taskCard = button.closest('[data-task-id]');
  const taskId = taskCard.dataset.taskId;
  const taskTitle = taskCard.dataset.taskTitle;
  const taskDescription = taskCard.dataset.taskDescription;
  const tagIds = taskCard.dataset.tagIds.split(',').filter(id => id.length > 0);

  document.getElementById('edit-task-id').value = taskId;
  document.getElementById('edit-title').value = taskTitle;
  document.getElementById('edit-description').value = taskDescription;

  const selectElement = document.getElementById('edit-tags');
  for (let i = 0; i < selectElement.options.length; i++) {
    const option = selectElement.options[i];
    option.selected = tagIds.includes(option.value);
  }

  showModal('editTaskModal');
}

function submitFormWithAjax(form) {
    const formData = new FormData(form);
    const action = form.getAttribute('action');
    
    showLoader();

    fetch(action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            alert('Error: ' + data.message);
            hideLoader();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An unexpected error occurred. Please try again.');
        hideLoader();
    });
}

function showLoader() {
    document.getElementById('loader').classList.remove('hidden');
}

function hideLoader() {
    document.getElementById('loader').classList.add('hidden');
}

function generateTagColors() {
    const colors = [
        '#EF4444', '#F97316', '#F59E0B', '#EAB308', '#84CC16', '#22C55E',
        '#10B981', '#14B8A6', '#06B6D4', '#0EA5E9', '#3B82F6', '#6366F1',
        '#8B5CF6', '#A855F7', '#D946EF', '#EC4899', '#F43F5E'
    ];
    allTags.forEach((tag, index) => {
        tagColors[tag.name.toLowerCase()] = colors[index % colors.length];
        document.documentElement.style.setProperty(`--tag-color-${tag.name.toLowerCase().replace(' ', '-')}`, colors[index % colors.length]);
    });
}

function applyTagColorsToDOM() {
  document.querySelectorAll('.tag-badge').forEach(badge => {
    const tagName = badge.textContent.trim().toLowerCase();
    const color = tagColors[tagName];
    if (color) {
      badge.style.backgroundColor = color;
    }
  });
}

function timeAgo(dateString) {
    const now = new Date();
    const past = new Date(dateString);
    const diffInSeconds = Math.floor((now - past) / 1000);

    const minutes = Math.floor(diffInSeconds / 60);
    const hours = Math.floor(minutes / 60);
    const days = Math.floor(hours / 24);
    const months = Math.floor(days / 30);
    const years = Math.floor(days / 365);

    if (diffInSeconds < 60) {
        return "just now";
    } else if (minutes < 60) {
        return `${minutes} minute${minutes > 1 ? 's' : ''} ago`;
    } else if (hours < 24) {
        return `${hours} hour${hours > 1 ? 's' : ''} ago`;
    } else if (days < 30) {
        return `${days} day${days > 1 ? 's' : ''} ago`;
    } else if (months < 12) {
        return `${months} month${months > 1 ? 's' : ''} ago`;
    } else {
        return `${years} year${years > 1 ? 's' : ''} ago`;
    }
}

function formatAllTimestamps() {
    document.querySelectorAll('[data-timestamp]').forEach(span => {
        const timestamp = span.dataset.timestamp;
        span.textContent = 'Updated: ' + timeAgo(timestamp);
    });
}
</script>
</body>
</html>