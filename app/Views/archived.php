<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Archived Items | Secure Notes</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
</head>
<body class="bg-gray-100 h-screen flex">
  <?php include __DIR__ . '/partials/sidebar.php'; ?>
  <div class="flex-1 flex flex-col">
    <?php include __DIR__ . '/partials/navbar.php'; ?>
    <main class="p-6 flex-1 overflow-y-auto">
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
                      <form action="/archived/note/unarchive" method="POST" onsubmit="return confirm('Are you sure you want to unarchive this note?');">
                          <input type="hidden" name="note_id" value="<?= $note['id'] ?>">
                          <button type="submit" class="text-sm px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 transition-colors duration-200">
                              <i class="fas fa-undo-alt mr-1"></i> Unarchive
                          </button>
                      </form>
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
                        <form action="/archived/task/unarchive" method="POST" onsubmit="return confirm('Are you sure you want to unarchive this task?');">
                            <input type="hidden" name="task_id" value="<?= $task['id'] ?>">
                            <button type="submit" class="text-sm px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 transition-colors duration-200">
                                <i class="fas fa-undo-alt mr-1"></i> Unarchive
                            </button>
                        </form>
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
  </script>
</body>
</html>