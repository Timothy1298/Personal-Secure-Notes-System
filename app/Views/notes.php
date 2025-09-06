<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Notes | Secure Notes</title>
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
      border-left-color: #1e40af; /* Blue-800 */
      animation: spin 1s ease infinite;
    }
    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }
    .modal-active {
        overflow: hidden;
    }
    .active-filter {
      background-color: #1e40af; /* Blue-800 */
      color: white;
      font-weight: 600;
    }
    .note-archived {
      background-color: #f3f4f6;
      color: #9ca3af;
      border: 1px dashed #d1d5db;
    }
    .note-archived h4, .note-archived p, .note-archived span {
      color: #9ca3af;
    }
  </style>
</head>
<body class="bg-gray-100 h-screen flex font-sans antialiased">
     <?php 
    // Check if the tags array is empty and add some defaults for a better user experience
    if (empty($tags)) {
        $tags = [
            ['id' => 1, 'name' => 'Work'],
            ['id' => 2, 'name' => 'Personal'],
            ['id' => 3, 'name' => 'Important'],
        ];
    }
  ?>
  <?php include __DIR__ . '/partials/sidebar.php'; ?>

  <div class="flex-1 flex flex-col">
    <?php include __DIR__ . '/partials/navbar.php'; ?>

    <main class="p-6 flex-1 overflow-y-auto">
      <div class="flex items-center justify-between mb-8 pb-4 border-b border-gray-200">
        <h2 class="text-3xl font-extrabold text-gray-800 tracking-tight">My Notes</h2>
        <button onclick="openAddNoteModal()" class="px-6 py-3 bg-blue-600 text-white font-semibold rounded-full shadow-lg hover:bg-blue-700 transition-all duration-200 transform hover:scale-105 active:scale-95">
          <i class="fas fa-plus mr-2"></i>Add Note
        </button>
      </div>

      <!-- Search and Filter Section -->
      <div class="flex flex-col md:flex-row items-center justify-between gap-4 mb-6">
          <div class="relative w-full md:w-1/2">
              <input type="search" id="searchInput" oninput="filterNotes()" placeholder="Search notes..." class="w-full pl-10 pr-4 py-3 rounded-lg border-2 border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors duration-200">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <i class="fas fa-search text-gray-400"></i>
              </div>
          </div>
          <div class="flex flex-col md:flex-row gap-4 w-full md:w-auto">
              <!-- Status Filter Buttons -->
              <div class="flex space-x-2 bg-white rounded-lg p-1.5 shadow-sm">
                  <button data-filter="status" data-value="all" onclick="setFilter(this)" class="px-4 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100 transition-colors duration-200">All</button>
                  <button data-filter="status" data-value="active" onclick="setFilter(this)" class="px-4 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100 transition-colors duration-200 active-filter">Active</button>
                  <button data-filter="status" data-value="archived" onclick="setFilter(this)" class="px-4 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100 transition-colors duration-200">Archived</button>
              </div>
              <!-- Tag Filter Dropdown -->
              <div class="relative w-full md:w-auto">
                <select id="tagFilter" onchange="filterNotes()" class="w-full px-4 py-2.5 rounded-lg border-2 border-gray-300 bg-white text-sm font-medium text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors duration-200 appearance-none">
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

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6" id="notesGrid">
        <?php if (!empty($notes)): ?>
          <?php foreach ($notes as $note): ?>
            <?php
              $noteTags = explode(',', $note['tags'] ?? '');
              $tagIds = explode(',', $note['tag_ids'] ?? '');
            ?>
            <div 
              class="note-card bg-white rounded-xl shadow-lg ring-1 ring-gray-200 hover:shadow-2xl hover:ring-blue-500 transition-all duration-300 p-6 flex flex-col <?= isset($note['is_archived']) && $note['is_archived'] ? 'note-archived' : '' ?>"
              data-note-id="<?= htmlspecialchars($note['id']) ?>"
              data-note-title="<?= htmlspecialchars($note['title']) ?>"
              data-note-content="<?= htmlspecialchars($note['content']) ?>"
              data-is-archived="<?= isset($note['is_archived']) ? htmlspecialchars($note['is_archived']) : 0 ?>"
              data-note-tags="<?= htmlspecialchars($note['tags'] ?? '') ?>"
              data-tag-ids="<?= htmlspecialchars($note['tag_ids'] ?? '') ?>"
              data-updated-at="<?= htmlspecialchars($note['updated_at']) ?>"
            >
              <h4 class="text-xl font-extrabold text-gray-900 mb-2 border-b border-gray-100 pb-2"><?= htmlspecialchars($note['title']) ?></h4>
              <p class="text-gray-700 text-sm leading-relaxed flex-1 overflow-hidden" style="display: -webkit-box; -webkit-line-clamp: 5; -webkit-box-orient: vertical;"><?= htmlspecialchars($note['content']) ?></p>
              
              <div class="flex items-center mt-4 pt-2 border-t border-gray-100">
                <span class="text-xs text-gray-400" data-timestamp="<?= htmlspecialchars($note['updated_at']) ?>"></span>
                <div class="flex flex-wrap gap-2 ml-auto">
                  <?php if (!empty($noteTags)): ?>
                    <?php foreach ($noteTags as $tag): ?>
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
                <button onclick="openEditNoteModal(this)" class="p-3 bg-yellow-500 text-white rounded-full shadow-md hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-400 transition-all duration-200 transform hover:scale-110 active:scale-95" title="Edit">
                    <i class="fas fa-edit"></i>
                </button>
               <form action="/notes/delete" method="POST" class="confirm-action-form" data-message="Are you sure you want to delete this note?">
    <input type="hidden" name="note_id" value="<?= htmlspecialchars($note['id']) ?>">
    <button type="submit" class="p-3 bg-red-500 hover:bg-red-600 text-white rounded-lg transition duration-300">
        <i class="fas fa-trash"></i>
    </button>
</form>

             <form action="/notes/archive" method="POST" class="confirm-action-form" data-message="Are you sure you want to archive this note?">
    <input type="hidden" name="note_id" value="<?= htmlspecialchars($note['id']) ?>">
    <button type="submit" class="p-3 bg-gray-500 hover:bg-gray-600 text-white rounded-lg transition duration-300">
        <i class="fas fa-archive"></i>
    </button>
</form>


              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="sm:col-span-2 lg:col-span-3 text-center py-12 bg-white rounded-xl shadow-md" id="noNotesMessage">
            <i class="fas fa-lightbulb text-6xl text-gray-300 mb-4"></i>
            <p class="text-gray-500 text-lg font-medium">You don't have any notes yet. Click the "Add Note" button to create one.</p>
          </div>
        <?php endif; ?>
      </div>
    </main>
     <?php include __DIR__ . '/partials/footer.php'; ?>
  </div>

  <!-- Loading Overlay -->
  <div id="loader" class="loader-overlay hidden">
      <div class="spinner"></div>
  </div>

  <!-- Confirmation Modal -->
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

  <!-- Add Note Modal -->
  <div id="addNoteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
    <div class="bg-white rounded-xl shadow-2xl p-8 w-11/12 md:w-1/3 relative transition-all duration-300 transform scale-95 opacity-0">
      <h3 class="text-2xl font-bold text-gray-800 mb-6 border-b border-gray-200 pb-2">Add New Note</h3>
      <form id="addNoteForm" method="POST" action="/notes/store">
        <label for="add-title" class="block mb-2 text-sm font-semibold text-gray-700">Note Title</label>
        <input type="text" id="add-title" name="title" class="w-full p-3 border border-gray-300 rounded-lg mb-4 focus:outline-none focus:ring-2 focus:ring-blue-500" required>

        <label for="add-content" class="block mb-2 text-sm font-semibold text-gray-700">Content</label>
        <textarea id="add-content" name="content" class="w-full p-3 border border-gray-300 rounded-lg mb-6 focus:outline-none focus:ring-2 focus:ring-blue-500" rows="8"></textarea>
        
        <label for="add-tags" class="block mb-2 text-sm font-semibold text-gray-700">Tags</label>
        <select id="add-tags" name="tags[]" multiple class="w-full p-3 border border-gray-300 rounded-lg mb-6 focus:outline-none focus:ring-2 focus:ring-blue-500">
          <?php foreach ($tags as $tag): ?>
            <option value="<?= htmlspecialchars($tag['id']) ?>"><?= htmlspecialchars($tag['name']) ?></option>
          <?php endforeach; ?>
        </select>

        <div class="flex justify-end space-x-2">
          <button type="button" onclick="closeModal('addNoteModal')" class="px-6 py-2 rounded-lg bg-gray-300 text-gray-800 font-medium hover:bg-gray-400 transition-colors duration-200">Cancel</button>
          <button type="submit" class="px-6 py-2 rounded-lg bg-blue-600 text-white font-medium hover:bg-blue-700 transition-colors duration-200">Save</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Edit Note Modal -->
  <div id="editNoteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
    <div class="bg-white rounded-xl shadow-2xl p-8 w-11/12 md:w-1/3 relative transition-all duration-300 transform scale-95 opacity-0">
      <h3 class="text-2xl font-bold text-gray-800 mb-6 border-b border-gray-200 pb-2">Edit Note</h3>
      <form id="editNoteForm" method="POST" action="/notes/update">
        <input type="hidden" id="edit-note-id" name="id">
        <label for="edit-title" class="block mb-2 text-sm font-semibold text-gray-700">Note Title</label>
        <input type="text" id="edit-title" name="title" class="w-full p-3 border border-gray-300 rounded-lg mb-4 focus:outline-none focus:ring-2 focus:ring-blue-500" required>

        <label for="edit-content" class="block mb-2 text-sm font-semibold text-gray-700">Content</label>
        <textarea id="edit-content" name="content" class="w-full p-3 border border-gray-300 rounded-lg mb-6 focus:outline-none focus:ring-2 focus:ring-blue-500" rows="8"></textarea>

        <label for="edit-tags" class="block mb-2 text-sm font-semibold text-gray-700">Tags</label>
        <select id="edit-tags" name="tags[]" multiple class="w-full p-3 border border-gray-300 rounded-lg mb-6 focus:outline-none focus:ring-2 focus:ring-blue-500">
          <?php foreach ($tags as $tag): ?>
            <option value="<?= htmlspecialchars($tag['id']) ?>"><?= htmlspecialchars($tag['name']) ?></option>
          <?php endforeach; ?>
        </select>

        <div class="flex justify-end space-x-2">
          <button type="button" onclick="closeModal('editNoteModal')" class="px-6 py-2 rounded-lg bg-gray-300 text-gray-800 font-medium hover:bg-gray-400 transition-colors duration-200">Cancel</button>
          <button type="submit" class="px-6 py-2 rounded-lg bg-blue-600 text-white font-medium hover:bg-blue-700 transition-colors duration-200">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
  
  <script>
    let pendingForm = null;
    const allTags = <?= json_encode($tags) ?>;
    const tagColors = {};

    document.addEventListener('DOMContentLoaded', (event) => {
      generateTagColors();
      applyTagColorsToDOM();
      formatAllTimestamps();
      filterNotes();

      // Handle form submissions with AJAX
      document.getElementById('addNoteForm').addEventListener('submit', function(e) {
          e.preventDefault();
          submitFormWithAjax(this);
      });

      document.getElementById('editNoteForm').addEventListener('submit', function(e) {
          e.preventDefault();
          submitFormWithAjax(this);
      });

      // Handle confirmation modal button clicks
      document.getElementById('confirmActionButton').addEventListener('click', () => {
        if (pendingForm) {
          showLoader();
          submitFormWithAjax(pendingForm);
          closeModal('confirmationModal');
        }
      });
    });
    
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

    function openConfirmationModal(e, formElement, message) {
  e.preventDefault(); // ✅ prevent default submission here
  pendingForm = formElement;
  document.getElementById('confirmationMessage').textContent = message;
  showModal('confirmationModal');
}

    function openAddNoteModal() {
      // Reset form and open modal
      document.getElementById('addNoteForm').reset();
      showModal('addNoteModal');
    }

    function openEditNoteModal(button) {
      const noteCard = button.closest('[data-note-id]');
      const noteId = noteCard.dataset.noteId;
      const noteTitle = noteCard.dataset.noteTitle;
      const noteContent = noteCard.dataset.noteContent;
      const tagIds = noteCard.dataset.tagIds.split(',').filter(id => id.length > 0);

      document.getElementById('edit-note-id').value = noteId;
      document.getElementById('edit-title').value = noteTitle;
      document.getElementById('edit-content').value = noteContent;

      // Select the correct tags in the multiselect
      const selectElement = document.getElementById('edit-tags');
      for (let i = 0; i < selectElement.options.length; i++) {
        const option = selectElement.options[i];
        if (tagIds.includes(option.value)) {
          option.selected = true;
        } else {
          option.selected = false;
        }
      }

      showModal('editNoteModal');
    }
    
    function showLoader() {
        document.getElementById('loader').classList.remove('hidden');
    }

    function hideLoader() {
        document.getElementById('loader').classList.add('hidden');
    }

    // --- AJAX and UI Refreshing ---
    async function submitFormWithAjax(form) {
        showLoader();
        const formData = new FormData(form);
        const action = form.getAttribute('action');
        
        try {
            const response = await fetch(action, {
                method: 'POST',
                body: formData
            });

            if (!response.ok) {
                throw new Error('Network response was not ok');
            }

            // On success, refresh the note grid
            await refreshNotesGrid();
            
            // Display success message from the server if available
            const text = await response.text();
            if (text.includes("Note updated successfully")) {
                 showStatusMessage("Note updated successfully!", "success");
            } else if (text.includes("Note added successfully")) {
                 showStatusMessage("Note added successfully!", "success");
            } else if (text.includes("Note deleted successfully")) {
                 showStatusMessage("Note deleted successfully!", "success");
            } else if (text.includes("Note archived successfully")) {
                 showStatusMessage("Note archived successfully!", "success");
            } else {
                 // Fallback message
                 showStatusMessage("Action completed successfully!", "success");
            }
        } catch (error) {
            console.error('Submission error:', error);
            showStatusMessage("An error occurred. Please try again.", "error");
        } finally {
            hideLoader();
            // Close any open modals
            const openModals = document.querySelectorAll('.fixed.flex');
            openModals.forEach(modal => modal.classList.add('hidden'));
        }
    }

    async function refreshNotesGrid() {
        try {
            const response = await fetch('/notes'); // Fetch the whole notes page
            const html = await response.text();

            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newGrid = doc.getElementById('notesGrid');
            const newTags = doc.getElementById('tagFilter');
            
            // Replace the current grid and tag filter
            document.getElementById('notesGrid').innerHTML = newGrid.innerHTML;
            document.getElementById('tagFilter').innerHTML = newTags.innerHTML;

            // Re-apply event listeners and formatting
            applyTagColorsToDOM();
            formatAllTimestamps();
            filterNotes(); // Re-apply current filters
        } catch (error) {
            console.error('Failed to refresh notes grid:', error);
            showStatusMessage("Failed to load notes. Please refresh the page.", "error");
        }
    }
    
    function showStatusMessage(message, type) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `bg-white p-4 rounded-lg shadow-md mb-4 text-center ${type === 'success' ? 'text-green-700' : 'text-red-700'}`;
        messageDiv.textContent = message;

        const main = document.querySelector('main');
        main.insertBefore(messageDiv, main.firstChild);

        setTimeout(() => {
            messageDiv.remove();
        }, 5000); // Remove after 5 seconds
    }

    // --- Dynamic Tag Styling ---
    function generateTagColors() {
      allTags.forEach(tag => {
        const name = tag.name;
        // Simple hash function to generate a color
        let hash = 0;
        for (let i = 0; i < name.length; i++) {
          hash = name.charCodeAt(i) + ((hash << 5) - hash);
        }
        let color = '#';
        for (let i = 0; i < 3; i++) {
          const value = (hash >> (i * 8)) & 0xFF;
          color += ('00' + value.toString(16)).substr(-2);
        }
        tagColors[name] = color;
      });
    }

    function applyTagColorsToDOM() {
      const styleSheet = document.styleSheets[0];
      for (const name in tagColors) {
        const cssVarName = `--tag-color-${name.replace(' ', '-').toLowerCase()}`;
        styleSheet.insertRule(`:root { ${cssVarName}: ${tagColors[name]}; }`, 0);
      }
    }

    // --- Relative Date Formatting ---
    function timeAgo(dateString) {
      const now = new Date();
      const past = new Date(dateString);
      const diffInSeconds = Math.floor((now - past) / 1000);
      const secondsInMinute = 60;
      const secondsInHour = secondsInMinute * 60;
      const secondsInDay = secondsInHour * 24;

      if (diffInSeconds < 60) {
        return "Just now";
      } else if (diffInSeconds < secondsInHour) {
        const minutes = Math.floor(diffInSeconds / secondsInMinute);
        return `${minutes} min ago`;
      } else if (diffInSeconds < secondsInDay) {
        const hours = Math.floor(diffInSeconds / secondsInHour);
        return `${hours} hr ago`;
      } else if (diffInSeconds < secondsInDay * 2) {
        return "Yesterday";
      } else {
        const days = Math.floor(diffInSeconds / secondsInDay);
        return `${days} days ago`;
      }
    }

    function formatAllTimestamps() {
      const timestampSpans = document.querySelectorAll('[data-timestamp]');
      timestampSpans.forEach(span => {
        span.textContent = "Last updated: " + timeAgo(span.dataset.timestamp);
      });
    }

    // --- Search and Filter Logic ---
    let currentStatusFilter = 'active';

    function setFilter(button) {
      document.querySelectorAll(`[data-filter="${button.dataset.filter}"]`).forEach(btn => {
        btn.classList.remove('active-filter');
      });
      button.classList.add('active-filter');
      currentStatusFilter = button.dataset.value;
      filterNotes();
    }

    function filterNotes() {
      const searchQuery = document.getElementById('searchInput').value.toLowerCase();
      const selectedTagId = document.getElementById('tagFilter').value;
      const notes = document.querySelectorAll('.note-card');
      const notesGrid = document.getElementById('notesGrid');
      
      let hasVisibleNotes = false;

      notes.forEach(note => {
        const title = note.dataset.noteTitle.toLowerCase();
        const content = note.dataset.noteContent.toLowerCase();
        const isArchived = note.dataset.isArchived === '1';
        const tagIds = note.dataset.tagIds.split(',');

        const searchMatch = title.includes(searchQuery) || content.includes(searchQuery);

        const statusMatch = (currentStatusFilter === 'all') || 
                           (currentStatusFilter === 'active' && !isArchived) || 
                           (currentStatusFilter === 'archived' && isArchived);
        
        const tagMatch = (selectedTagId === 'all') || tagIds.includes(selectedTagId);

        if (searchMatch && statusMatch && tagMatch) {
          note.style.display = 'flex';
          hasVisibleNotes = true;
        } else {
          note.style.display = 'none';
        }
      });
      
      const noNotesMessage = document.getElementById('noNotesFoundMessage');
      if (!hasVisibleNotes) {
          if (!noNotesMessage) {
              const messageDiv = document.createElement('div');
              messageDiv.id = 'noNotesFoundMessage';
              messageDiv.className = 'sm:col-span-2 lg:col-span-3 text-center py-12 text-gray-500 text-lg font-medium';
              messageDiv.innerHTML = '<i class="fas fa-search-minus text-6xl text-gray-300 mb-4"></i><p>No notes match your search or filter criteria.</p>';
              notesGrid.appendChild(messageDiv);
          }
      } else {
          if (noNotesMessage) {
              noNotesMessage.remove();
          }
      }
    }
   

document.querySelectorAll('.confirm-action-form').forEach(form => {
  form.addEventListener('submit', function(e) {
    e.preventDefault(); // stop form submission
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

  </script>
</body>
</html>
