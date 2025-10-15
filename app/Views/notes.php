<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Notes | SecureNote Pro</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    // Suppress Tailwind production warning for development
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            primary: {
              50: '#f0f9ff',
              100: '#e0f2fe',
              200: '#bae6fd',
              300: '#7dd3fc',
              400: '#38bdf8',
              500: '#0ea5e9',
              600: '#0284c7',
              700: '#0369a1',
              800: '#075985',
              900: '#0c4a6e',
            }
          },
          animation: {
            'fade-in': 'fadeIn 0.5s ease-in-out',
            'slide-up': 'slideUp 0.3s ease-out',
            'bounce-gentle': 'bounceGentle 2s infinite',
            'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
            'shimmer': 'shimmer 2s linear infinite',
          }
        }
      }
    }
  </script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
  <script src="https://cdn.tiny.cloud/1/o13b1lftlt4y79dkmt4217ugczof9pmm10hcvroqexss304x/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
  <script>
    // Configure TinyMCE with core plugins only (guaranteed to work)
    tinymce.init({
      selector: '#noteContent',
      height: 400,
      menubar: true,
      plugins: [
        'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
        'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
        'insertdatetime', 'media', 'table', 'help', 'wordcount', 'emoticons',
        'nonbreaking', 'pagebreak', 'save', 'directionality', 'visualchars',
        'codesample'
      ],
      toolbar: 'undo redo | blocks fontfamily fontsize | ' +
        'bold italic underline strikethrough | alignleft aligncenter ' +
        'alignright alignjustify | outdent indent |  numlist bullist | ' +
        'forecolor backcolor removeformat | pagebreak | charmap emoticons | ' +
        'fullscreen preview save | insertfile image media link anchor codesample | ' +
        'ltr rtl',
      content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif; font-size: 14px; }',
      branding: false,
      promotion: false,
      readonly: false,
      paste_data_images: true,
      automatic_uploads: true,
      file_picker_types: 'image',
      file_picker_callback: function (cb, value, meta) {
        var input = document.createElement('input');
        input.setAttribute('type', 'file');
        input.setAttribute('accept', 'image/*');
        input.onchange = function () {
          var file = this.files[0];
          var reader = new FileReader();
          reader.onload = function () {
            var id = 'blobid' + (new Date()).getTime();
            var blobCache = tinymce.activeEditor.editorUpload.blobCache;
            var base64 = reader.result.split(',')[1];
            var blobInfo = blobCache.create(id, file, base64);
            blobCache.add(blobInfo);
            cb(blobInfo.blobUri(), { title: file.name });
          };
          reader.readAsDataURL(file);
        };
        input.click();
      }
    });
  </script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
    
    * {
      font-family: 'Inter', sans-serif;
    }
    
    .glassmorphism {
      background: rgba(255, 255, 255, 0.8);
      backdrop-filter: blur(8px);
      border: 1px solid rgba(255, 255, 255, 0.3);
    }
    
    .note-card {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.3);
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      transform-style: preserve-3d;
      position: relative;
      z-index: 1;
      height: auto;
      min-height: 320px;
      overflow: visible;
      display: flex;
      flex-direction: column;
    }
    
    .note-card.menu-open {
      z-index: 1000;
      transform: translateY(-4px);
      box-shadow: 0 20px 40px -12px rgba(0, 0, 0, 0.15);
      border: 2px solid rgba(59, 130, 246, 0.3);
    }
    
    .note-card.menu-open .note-menu {
      animation: menuSlideIn 0.2s ease-out;
    }
    
    @keyframes menuSlideIn {
      from {
        opacity: 0;
        transform: translateY(-10px) scale(0.95);
      }
      to {
        opacity: 1;
        transform: translateY(0) scale(1);
      }
    }
    
    .note-card:hover {
      transform: translateY(-8px) rotateX(5deg);
      box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
      z-index: 10;
    }
    
    .note-menu {
      position: fixed !important;
      z-index: 10000 !important;
      box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04) !important;
      border: 1px solid rgba(0, 0, 0, 0.1) !important;
      backdrop-filter: blur(10px) !important;
      background: rgba(255, 255, 255, 0.98) !important;
      min-width: 200px !important;
      max-width: 250px !important;
      border-radius: 12px !important;
    }
    
    .menu-overlay {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      z-index: 9999;
      background: rgba(0, 0, 0, 0.1);
      backdrop-filter: blur(2px);
      opacity: 0;
      visibility: hidden;
      transition: all 0.2s ease;
    }
    
    .menu-overlay.active {
      opacity: 1;
      visibility: visible;
    }
    
    .note-actions {
      position: relative;
      z-index: 100;
      margin-top: auto;
      padding-top: 0.5rem;
    }
    
    .note-card.pinned {
      border-left: 4px solid #f59e0b;
      background: linear-gradient(135deg, rgba(245, 158, 11, 0.1) 0%, rgba(255, 255, 255, 0.95) 100%);
    }
    
    .priority-urgent {
      border-left: 4px solid #ef4444;
    }
    
    .priority-high {
      border-left: 4px solid #f97316;
    }
    
    .priority-medium {
      border-left: 4px solid #3b82f6;
    }
    
    .priority-low {
      border-left: 4px solid #10b981;
    }
    
    .floating-animation {
      animation: float 6s ease-in-out infinite;
    }
    
    @keyframes float {
      0%, 100% { transform: translateY(0px); }
      50% { transform: translateY(-10px); }
    }
    
    .pulse-glow {
      animation: pulse-glow 2s ease-in-out infinite alternate;
    }
    
    @keyframes pulse-glow {
      from { box-shadow: 0 0 20px rgba(59, 130, 246, 0.4); }
      to { box-shadow: 0 0 30px rgba(59, 130, 246, 0.8); }
    }
    
    .btn-3d {
      position: relative;
      transform-style: preserve-3d;
      transition: all 0.3s ease;
    }
    
    .btn-3d:hover {
      transform: translateY(-2px) rotateX(5deg);
      box-shadow: 0 10px 25px rgba(59, 130, 246, 0.4);
    }
    
    .btn-3d:active {
      transform: translateY(0px) rotateX(2deg);
    }
    
    .loading-spinner {
      border: 3px solid rgba(59, 130, 246, 0.3);
      border-top: 3px solid #3b82f6;
      border-radius: 50%;
      width: 20px;
      height: 20px;
      animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }
    
    .slide-in {
      animation: slideIn 0.8s ease-out;
    }
    
    @keyframes slideIn {
      from { 
        opacity: 0; 
        transform: translateY(30px) scale(0.95); 
      }
      to { 
        opacity: 1; 
        transform: translateY(0) scale(1); 
      }
    }
    
    .notes-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
      gap: 2rem;
      padding: 1.5rem 0;
      align-items: start;
    }
    
    @media (min-width: 640px) {
      .notes-grid {
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 2rem;
      }
    }
    
    @media (min-width: 1024px) {
      .notes-grid {
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 2.5rem;
      }
    }
    
    @media (min-width: 1280px) {
      .notes-grid {
        grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
        gap: 3rem;
      }
    }
    
    .note-item {
      width: 100%;
      height: 100%;
    }
    
    .tag-badge {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 0.25rem 0.75rem;
      border-radius: 9999px;
      font-size: 0.75rem;
      font-weight: 500;
      display: inline-block;
      margin: 0.125rem;
      transition: all 0.2s ease;
    }
    
    .tag-badge:hover {
      transform: scale(1.05);
      box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    }
    
    .attachment-item {
      background: rgba(59, 130, 246, 0.1);
      border: 1px solid rgba(59, 130, 246, 0.2);
      border-radius: 0.5rem;
      padding: 0.75rem;
      margin: 0.25rem 0;
      transition: all 0.2s ease;
    }
    
    .attachment-item:hover {
      background: rgba(59, 130, 246, 0.2);
      transform: translateX(4px);
    }
    
    .color-picker {
      display: grid;
      grid-template-columns: repeat(8, 1fr);
      gap: 0.5rem;
      padding: 1rem;
    }
    
    .color-option {
      width: 2rem;
      height: 2rem;
      border-radius: 50%;
      border: 2px solid transparent;
      cursor: pointer;
      transition: all 0.2s ease;
    }
    
    .color-option:hover {
      transform: scale(1.1);
      border-color: #374151;
    }
    
    .color-option.selected {
      border-color: #1f2937;
      transform: scale(1.2);
    }
    
    .search-highlight {
      background: rgba(255, 235, 59, 0.3);
      padding: 0.125rem 0.25rem;
      border-radius: 0.25rem;
    }
    
    .line-clamp-6 {
      display: -webkit-box;
      -webkit-line-clamp: 6;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }
    
    .line-clamp-2 {
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }
    
    .modal-backdrop {
      backdrop-filter: blur(8px);
    }
    
    .modal-content {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(20px);
      border: 1px solid rgba(255, 255, 255, 0.3);
    }
    
    .rich-text-editor {
      border: 2px solid #e5e7eb;
      border-radius: 0.75rem;
      overflow: hidden;
      transition: all 0.3s ease;
      padding: 1 rem;
      margin-bottom: 1rem;
    }
    
    .rich-text-editor:focus-within {
      border-color: #3b82f6;
      box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }
    
    .toast {
      position: fixed;
      top: 1rem;
      right: 1rem;
      z-index: 9999;
      padding: 1rem 1.5rem;
      border-radius: 0.75rem;
      color: white;
      font-weight: 500;
      transform: translateX(100%);
      transition: transform 0.3s ease;
    }
    
    .toast.show {
      transform: translateX(0);
    }
    
    .toast.success {
      background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    }
    
    .toast.error {
      background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    }
    
    .toast.info {
      background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    }
    
    /* Bulk Actions Styles */
    .note-checkbox {
      transition: all 0.2s ease;
    }
    
    .note-checkbox:checked {
      background-color: #3b82f6;
      border-color: #3b82f6;
    }
    
    /* Template Cards */
    .template-card {
      transition: all 0.3s ease;
      cursor: pointer;
    }
    
    .template-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }
    
    /* View Mode Toggle */
    .view-mode-toggle {
      transition: all 0.3s ease;
    }
    
    /* Enhanced Search */
    .search-container {
      position: relative;
    }
    
    .search-container .advanced-search-btn {
      transition: all 0.2s ease;
    }
    
    .search-container .advanced-search-btn:hover {
      background-color: rgba(59, 130, 246, 0.1);
      color: #3b82f6;
    }
    
    /* Statistics Cards */
    .stat-card {
      background: linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, rgba(255, 255, 255, 0.7) 100%);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.3);
      transition: all 0.3s ease;
    }
    
    .stat-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }
    
    
    /* Drag and Drop Styles */
    .note-card.dragging {
      opacity: 0.5;
      transform: rotate(5deg);
      z-index: 1000;
    }
    
    .note-card.drag-over {
      border: 2px dashed #3b82f6;
      background-color: rgba(59, 130, 246, 0.1);
    }
    
    .drop-zone {
      min-height: 200px;
      border: 2px dashed #d1d5db;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #6b7280;
      font-size: 14px;
      transition: all 0.3s ease;
    }
    
    .drop-zone.drag-over {
      border-color: #3b82f6;
      background-color: rgba(59, 130, 246, 0.05);
      color: #3b82f6;
    }
    
    
    /* Responsive Design */
    @media (max-width: 768px) {
      .bulk-actions {
        flex-direction: column;
        gap: 0.5rem;
      }
      
      .bulk-actions button {
        width: 100%;
        justify-content: center;
      }
      
      .template-grid {
        grid-template-columns: 1fr;
      }
      
      .notes-grid {
        grid-template-columns: 1fr;
        gap: 1.5rem;
        padding: 1rem 0;
      }
      
      .note-card {
        min-height: 300px;
      }
    }
    
    @media (max-width: 480px) {
      .notes-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
        padding: 0.75rem 0;
      }
      
      .note-card {
        padding: 1rem;
        min-height: 280px;
      }
      
      .note-card h3 {
        font-size: 1.125rem;
      }
    }
    
    /* Animation for new features */
    .feature-fade-in {
      animation: featureFadeIn 0.5s ease-out;
    }
    
    @keyframes featureFadeIn {
      from {
        opacity: 0;
        transform: translateY(20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
  </style>
</head>
<body class="bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 min-h-screen">
  
  <!-- Notes Loader System -->
  <div id="notesLoader" class="fixed inset-0 bg-black bg-opacity-30 z-50 hidden">
    <div class="flex items-center justify-center min-h-screen">
      <div class="text-center text-white">
        <div class="w-12 h-12 border-4 border-white border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
        <p id="notesLoaderMessage">Loading...</p>
      </div>
    </div>
  </div>

  <!-- Notes Toast Container -->
  <div id="notesToastContainer" class="fixed top-4 right-4 z-50 space-y-2"></div>

  <!-- Main Container -->

  <div class="flex h-screen">
    <!-- Sidebar -->
    <?php include __DIR__ . '/partials/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden">
      <!-- Header -->
      <?php 
        $page_title = "My Notes";
        include __DIR__ . '/partials/navbar.php'; 
      ?>

      <!-- Content -->
      <main class="flex-1 overflow-y-auto p-6">
        
        <!-- Header Section -->
        <div class="mb-8">
          <div class="mb-4">
            <h1 class="text-4xl font-bold text-gray-800 mb-2">My Notes</h1>
            <p class="text-gray-600">Organize your thoughts with advanced features</p>
          </div>
          
          <!-- Add Note Button -->
          <div class="flex justify-end mb-6">
            <button onclick="openAddNoteModal()" class="btn-3d px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300">
              <i class="fas fa-plus mr-2"></i>Add Note
            </button>
          </div>
        </div>

        <!-- Search and Filters -->
        <div class="glassmorphism rounded-2xl p-6 mb-8">
          <div class="flex flex-col lg:flex-row gap-4">
            <!-- Search -->
            <div class="flex-1">
              <div class="relative">
                <input type="text" id="searchInput" placeholder="Search notes by title or content..." 
                       class="w-full pl-12 pr-20 py-3 bg-white bg-opacity-50 border border-white border-opacity-30 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-all duration-300">
                <i class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                <button onclick="openAdvancedSearchModal()" class="absolute right-2 top-1/2 transform -translate-y-1/2 p-2 text-gray-400 hover:text-gray-600 transition-colors">
                  <i class="fas fa-sliders-h"></i>
                </button>
              </div>
            </div>
            
            <!-- Filters -->
            <div class="flex flex-wrap gap-3">
              <!-- Status Filter -->
              <div class="flex bg-white bg-opacity-50 rounded-xl p-1">
                <button data-filter="status" data-value="all" onclick="setFilter(this)" class="px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200">All</button>
                <button data-filter="status" data-value="active" onclick="setFilter(this)" class="px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 active-filter">Active</button>
                <button data-filter="status" data-value="pinned" onclick="setFilter(this)" class="px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200">Pinned</button>
                <button data-filter="status" data-value="archived" onclick="setFilter(this)" class="px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200">Archived</button>
              </div>
              
              <!-- Priority Filter -->
              <select id="priorityFilter" onchange="filterNotes()" class="px-4 py-2 bg-white bg-opacity-50 border border-white border-opacity-30 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="all">All Priorities</option>
                <option value="urgent">Urgent</option>
                <option value="high">High</option>
                <option value="medium">Medium</option>
                <option value="low">Low</option>
              </select>
              
              <!-- Tag Filter -->
              <select id="tagFilter" onchange="filterNotes()" class="px-4 py-2 bg-white bg-opacity-50 border border-white border-opacity-30 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="all">All Tags</option>
                <?php foreach ($tags as $tag): ?>
                  <option value="<?= htmlspecialchars($tag['id']) ?>"><?= htmlspecialchars($tag['name']) ?></option>
                <?php endforeach; ?>
              </select>
              
              <select id="categoryFilter" onchange="filterNotes()" class="px-4 py-2 bg-white bg-opacity-50 border border-white border-opacity-30 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="all">All Categories</option>
                <option value="general">General</option>
                <option value="work">Work</option>
                <option value="personal">Personal</option>
                <option value="study">Study</option>
                <option value="ideas">Ideas</option>
                <option value="meetings">Meetings</option>
                <option value="projects">Projects</option>
                <option value="research">Research</option>
                <option value="journal">Journal</option>
                <option value="other">Other</option>
              </select>
              
              <!-- Sort Options -->
              <select id="sortBy" onchange="sortNotes()" class="px-4 py-2 bg-white bg-opacity-50 border border-white border-opacity-30 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="updated_desc">Recently Updated</option>
                <option value="created_desc">Recently Created</option>
                <option value="title_asc">Title A-Z</option>
                <option value="title_desc">Title Z-A</option>
                <option value="word_count_desc">Most Words</option>
                <option value="word_count_asc">Least Words</option>
                <option value="priority">Priority</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Quick Actions Section -->
        <div class="glassmorphism rounded-2xl p-6 mb-8">
          <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <!-- Bulk Actions (Hidden by default) -->
            <div id="bulkActions" class="hidden flex items-center gap-2">
              <span id="selectedCount" class="text-sm text-gray-600">0 selected</span>
              <button onclick="bulkArchive()" class="px-3 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition-colors">
                <i class="fas fa-archive mr-1"></i>Archive
              </button>
              <button onclick="bulkDelete()" class="px-3 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                <i class="fas fa-trash mr-1"></i>Delete
              </button>
              <button onclick="bulkPin()" class="px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                <i class="fas fa-thumbtack mr-1"></i>Pin
              </button>
              <button onclick="bulkTag()" class="px-3 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                <i class="fas fa-tags mr-1"></i>Tag
              </button>
              <button onclick="clearSelection()" class="px-3 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                <i class="fas fa-times mr-1"></i>Clear
              </button>
            </div>
            
            <!-- Quick Actions -->
            <div class="flex items-center gap-2 flex-wrap">
              <div class="flex items-center gap-2">
                <button onclick="showNoteStatistics()" class="px-3 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors text-sm">
                  <i class="fas fa-chart-bar mr-1"></i>Stats
                </button>
                <button onclick="openTemplatesModal()" class="px-3 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-sm">
                  <i class="fas fa-file-alt mr-1"></i>Templates
                </button>
                <button onclick="openExportModal()" class="px-3 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors text-sm">
                  <i class="fas fa-download mr-1"></i>Export
                </button>
              </div>
              <div class="flex items-center gap-2">
                <button onclick="toggleViewMode()" class="px-3 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors text-sm">
                  <i class="fas fa-th mr-1" id="viewModeIcon"></i><span id="viewModeText">Grid</span>
                </button>
                <button onclick="showKeyboardShortcuts()" class="px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm">
                  <i class="fas fa-keyboard mr-1"></i>Shortcuts
                </button>
                <button onclick="showHelp()" class="px-3 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition-colors text-sm">
                  <i class="fas fa-question-circle mr-1"></i>Help
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Notes Grid -->
        <div class="notes-grid" id="notesGrid">
          <?php if (!empty($notes)): ?>
            <?php foreach ($notes as $note): ?>
              <?php
                $noteTags = explode(',', $note['tags'] ?? '');
                $tagIds = explode(',', $note['tag_ids'] ?? '');
                $isArchived = isset($note['is_archived']) && $note['is_archived'];
                $isPinned = isset($note['is_pinned']) && $note['is_pinned'];
                $priority = $note['priority'] ?? 'medium';
                $color = $note['color'] ?? '#ffffff';
              ?>
              <div class="note-item">
                <div class="note-card rounded-2xl shadow-lg p-6 <?= $isPinned ? 'pinned' : '' ?> priority-<?= $priority ?>" 
                     style="border-left-color: <?= $color ?>; position: relative; z-index: 1;"
                     data-note-id="<?= htmlspecialchars($note['id']) ?>"
                     data-note-title="<?= htmlspecialchars($note['title']) ?>"
                     data-note-content="<?= htmlspecialchars($note['content']) ?>"
                     data-category="<?= htmlspecialchars($note['category'] ?? 'general') ?>"
                     data-is-archived="<?= htmlspecialchars($isArchived ? 1 : 0) ?>"
                     draggable="true"
                     ondragstart="handleDragStart(event)"
                     ondragend="handleDragEnd(event)"
                     ondragover="handleDragOver(event)"
                     ondrop="handleDrop(event)"
                     data-is-pinned="<?= htmlspecialchars($isPinned ? 1 : 0) ?>"
                     data-priority="<?= htmlspecialchars($priority) ?>"
                     data-color="<?= htmlspecialchars($color) ?>"
                     data-note-tags="<?= htmlspecialchars($note['tags'] ?? '') ?>"
                     data-tag-ids="<?= htmlspecialchars($note['tag_ids'] ?? '') ?>"
                     data-updated-at="<?= htmlspecialchars($note['updated_at']) ?>">
                  
                  <!-- Note Header -->
                  <div class="flex items-start justify-between mb-4">
                    <!-- Bulk Selection Checkbox -->
                    <div class="flex items-center">
                      <input type="checkbox" class="note-checkbox w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2" 
                             data-note-id="<?= htmlspecialchars($note['id']) ?>" 
                             onchange="updateBulkActions()">
                    </div>
                    <div class="flex-1">
                      <h3 class="text-xl font-bold text-gray-800 mb-2 line-clamp-2"><?= htmlspecialchars($note['title']) ?></h3>
                      <div class="flex items-center gap-2 text-sm text-gray-500">
                        <?php if ($isPinned): ?>
                          <i class="fas fa-thumbtack text-amber-500"></i>
                        <?php endif; ?>
                        <span><?= date('M j, Y', strtotime($note['updated_at'])) ?></span>
                        <span>•</span>
                        <span><?= $note['word_count'] ?? 0 ?> words</span>
                      </div>
                    </div>
                    
                    <!-- Actions Menu -->
                    <div class="note-actions relative z-[100]">
                      <button onclick="toggleNoteMenu(this)" class="p-2 text-gray-400 hover:text-gray-600 transition-colors">
                        <i class="fas fa-ellipsis-v"></i>
                      </button>
                      
                      <div class="note-menu hidden absolute right-0 top-full mt-2 w-48 bg-white rounded-xl shadow-xl border border-gray-200 py-2 z-[9999]">
                        <button onclick="openViewNoteModal(this)" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center">
                          <i class="fas fa-eye mr-3 text-blue-500"></i>View
                        </button>
                        <button onclick="openEditNoteModal(this)" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center">
                          <i class="fas fa-edit mr-3 text-yellow-500"></i>Edit
                        </button>
                        <button onclick="togglePin(<?= $note['id'] ?>)" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center">
                          <i class="fas fa-thumbtack mr-3 text-amber-500"></i><?= $isPinned ? 'Unpin' : 'Pin' ?>
                        </button>
                        <button onclick="openColorPicker(<?= $note['id'] ?>)" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center">
                          <i class="fas fa-palette mr-3 text-purple-500"></i>Change Color
                        </button>
                        <button onclick="openVersionHistory(<?= $note['id'] ?>)" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center">
                          <i class="fas fa-history mr-3 text-indigo-500"></i>Version History
                        </button>
                        <button onclick="openShareModal(<?= $note['id'] ?>)" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center">
                          <i class="fas fa-share mr-3 text-green-500"></i>Share
                        </button>
                        <hr class="my-2">
                        <button onclick="archiveNote(<?= $note['id'] ?>)" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center">
                          <i class="fas fa-archive mr-3 text-gray-500"></i><?= $isArchived ? 'Unarchive' : 'Archive' ?>
                        </button>
                        <button onclick="deleteNote(<?= $note['id'] ?>)" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 flex items-center">
                          <i class="fas fa-trash mr-3"></i>Delete
                        </button>
                      </div>
                    </div>
                  </div>
                  
                  <!-- Note Content -->
                  <div class="text-gray-700 text-sm leading-relaxed mb-4 line-clamp-6 flex-1 overflow-hidden">
                    <div class="max-h-32 overflow-hidden">
                      <?= nl2br(htmlspecialchars(substr($note['content'], 0, 300))) ?>
                      <?php if (strlen($note['content']) > 300): ?>
                        <span class="text-blue-500 text-xs">...read more</span>
                      <?php endif; ?>
                    </div>
                  </div>
                  
                  <!-- Tags -->
                  <?php if (!empty($noteTags)): ?>
                    <div class="mb-4">
                      <?php foreach ($noteTags as $tag): ?>
                        <?php if (!empty($tag)): ?>
                          <span class="tag-badge"><?= htmlspecialchars($tag) ?></span>
                        <?php endif; ?>
                      <?php endforeach; ?>
                    </div>
                  <?php endif; ?>
                  
                  <!-- Note Footer -->
                  <div class="flex items-center justify-between pt-4 border-t border-gray-100 mt-auto">
                    <div class="flex items-center gap-2">
                      <span class="text-xs text-gray-400"><?= date('H:i', strtotime($note['updated_at'])) ?></span>
                      <span class="text-xs text-gray-400">•</span>
                      <span class="text-xs text-gray-400"><?= $note['word_count'] ?? 0 ?> words</span>
                    </div>
                    <div class="flex items-center gap-2">
                      <span class="text-xs px-2 py-1 rounded-full bg-gray-100 text-gray-600"><?= ucfirst($priority) ?></span>
                    </div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="col-span-full text-center py-12">
              <div class="floating-animation mb-6">
                <i class="fas fa-sticky-note text-6xl text-gray-300"></i>
              </div>
              <h3 class="text-xl font-semibold text-gray-600 mb-2">No notes yet</h3>
              <p class="text-gray-500 mb-6">Start creating your first note to get organized</p>
              <button onclick="openAddNoteModal()" class="btn-3d px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300">
                <i class="fas fa-plus mr-2"></i>Create Your First Note
              </button>
            </div>
          <?php endif; ?>
        </div>
      </main>
    </div>
  </div>

  <!-- Add/Edit Note Modal -->
  <div id="noteModal" class="fixed inset-0 bg-black bg-opacity-50 modal-backdrop flex items-center justify-center hidden z-50">
    <div class="modal-content rounded-2xl shadow-2xl w-11/12 max-w-4xl max-h-[90vh] overflow-hidden slide-in">
      <div class="p-6 border-b border-gray-200">
        <div class="flex items-center justify-between">
          <h3 id="modalTitle" class="text-2xl font-bold text-gray-800">Add New Note</h3>
          <button onclick="closeNoteModal()" class="p-2 text-gray-400 hover:text-gray-600 transition-colors">
            <i class="fas fa-times text-xl"></i>
          </button>
        </div>
      </div>
      
      <div class="p-6 overflow-y-auto max-h-[calc(90vh-140px)]">
        <form id="noteForm" method="POST" enctype="multipart/form-data">
          <input type="hidden" name="csrf_token" value="<?= \Core\CSRF::generate() ?>">
          <input type="hidden" id="noteId" name="id">
          
          <!-- Note Title -->
          <div class="mb-6">
            <label for="noteTitle" class="block text-sm font-semibold text-gray-700 mb-2">Title</label>
            <input type="text" id="noteTitle" name="title" required
                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-300"
                   placeholder="Enter note title...">
          </div>
          
          <!-- Note Summary -->
          <div class="mb-6">
            <label for="noteSummary" class="block text-sm font-semibold text-gray-700 mb-2">Summary (Optional)</label>
            <textarea id="noteSummary" name="summary" rows="3"
                      class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-300"
                      placeholder="Brief summary of your note..."></textarea>
          </div>
          
          <!-- Rich Text Editor -->
          <div class="mb-6">
            <label for="noteContent" class="block text-sm font-semibold text-gray-700 mb-2">Content</label>
            <div class="rich-text-editor">
              <textarea id="noteContent" name="content" rows="15" required
                        class="w-full p-4 border-0 focus:outline-none resize-none"
                        placeholder="Start writing your note..."></textarea>
            </div>
          </div>
          
          <!-- Note Options -->
          <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
            <!-- Priority -->
            <div>
              <label for="notePriority" class="block text-sm font-semibold text-gray-700 mb-2">Priority</label>
              <select id="notePriority" name="priority" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="low">Low</option>
                <option value="medium" selected>Medium</option>
                <option value="high">High</option>
                <option value="urgent">Urgent</option>
              </select>
            </div>
            
            <!-- Category -->
            <div>
              <label for="noteCategory" class="block text-sm font-semibold text-gray-700 mb-2">Category</label>
              <select id="noteCategory" name="category" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="general">General</option>
                <option value="work">Work</option>
                <option value="personal">Personal</option>
                <option value="study">Study</option>
                <option value="ideas">Ideas</option>
                <option value="meetings">Meetings</option>
                <option value="projects">Projects</option>
                <option value="research">Research</option>
                <option value="journal">Journal</option>
                <option value="other">Other</option>
              </select>
            </div>
            
            <!-- Pin Note -->
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Options</label>
              <div class="flex items-center space-x-3">
                <label class="flex items-center">
                  <input type="checkbox" id="notePinned" name="is_pinned" value="1" 
                         class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2">
                  <span class="ml-2 text-sm text-gray-700">Pin Note</span>
                </label>
              </div>
            </div>
            
            <!-- Color -->
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Color</label>
              <div class="flex gap-2">
                <input type="color" id="noteColor" name="color" value="#ffffff" 
                       class="w-12 h-12 border border-gray-300 rounded-lg cursor-pointer">
                <div class="flex-1 px-4 py-3 border border-gray-300 rounded-xl bg-gray-50 text-gray-600 text-sm flex items-center">
                  Choose a color for your note
                </div>
              </div>
            </div>
            
            <!-- Tags -->
            <div>
              <label for="noteTags" class="block text-sm font-semibold text-gray-700 mb-2">Tags</label>
              <select id="noteTags" name="tags[]" multiple class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                <?php foreach ($tags as $tag): ?>
                  <option value="<?= htmlspecialchars($tag['id']) ?>"><?= htmlspecialchars($tag['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          
          <!-- Enhanced File Attachments -->
          <div class="mb-6">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Attachments & Images</label>
            
            <!-- Drag & Drop Zone -->
            <div id="dropZone" class="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center hover:border-blue-400 transition-colors cursor-pointer">
              <div class="space-y-4">
                <div class="mx-auto w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center">
                  <i class="fas fa-cloud-upload-alt text-2xl text-blue-600"></i>
                </div>
                <div>
                  <p class="text-lg font-medium text-gray-700">Drag & drop files here</p>
                  <p class="text-sm text-gray-500">or click to browse</p>
                </div>
                <div class="flex justify-center gap-4">
                  <button type="button" onclick="document.getElementById('noteAttachments').click()" 
                          class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-file-upload mr-2"></i>Upload Files
                  </button>
                  <button type="button" onclick="document.getElementById('imageUpload').click()" 
                          class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                    <i class="fas fa-image mr-2"></i>Upload Images
                  </button>
                </div>
              </div>
            </div>
            
            <!-- Hidden file inputs -->
            <input type="file" id="noteAttachments" name="attachments[]" multiple class="hidden"
                   accept=".pdf,.doc,.docx,.txt,.zip,.rar,.xlsx,.xls,.ppt,.pptx">
            <input type="file" id="imageUpload" name="images[]" multiple class="hidden"
                   accept="image/*">
            
            <p class="text-xs text-gray-500 mt-2">
              <strong>Supported formats:</strong><br>
              📄 Documents: PDF, DOC, DOCX, TXT, ZIP, RAR, XLSX, XLS, PPT, PPTX<br>
              📸 Images: JPG, JPEG, PNG, GIF, WEBP, SVG<br>
              <strong>Max size:</strong> 10MB per file | <strong>Encryption:</strong> All files are encrypted
            </p>
          </div>
          
          <!-- Enhanced Attachments Preview -->
          <div id="attachmentsPreview" class="mb-6 hidden">
            <div class="flex items-center justify-between mb-4">
              <h4 class="text-sm font-semibold text-gray-700">Selected Files & Images</h4>
              <button type="button" onclick="clearAllAttachments()" class="text-red-600 hover:text-red-800 text-sm">
                <i class="fas fa-trash mr-1"></i>Clear All
              </button>
            </div>
            
            <!-- Files List -->
            <div id="filesList" class="space-y-2 mb-4"></div>
            
            <!-- Images Grid -->
            <div id="imagesGrid" class="grid grid-cols-2 md:grid-cols-4 gap-4"></div>
            
            <!-- Upload Progress -->
            <div id="uploadProgress" class="hidden mt-4">
              <div class="bg-gray-200 rounded-full h-2">
                <div id="progressBar" class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
              </div>
              <p id="progressText" class="text-sm text-gray-600 mt-1">Uploading...</p>
            </div>
          </div>
        </form>
      </div>
      
      <div class="p-6 pb-8 border-t border-gray-200 flex justify-end gap-3">
        <button onclick="closeNoteModal()" class="px-6 py-2 text-gray-600 hover:text-gray-800 transition-colors">
          Cancel
        </button>
        <button onclick="saveNote()" class="btn-3d px-6 py-2 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300">
          <span id="saveButtonText">Save Note</span>
          <div id="saveSpinner" class="loading-spinner ml-2 hidden"></div>
        </button>
      </div>
    </div>
  </div>

  <!-- Color Picker Modal -->
  <div id="colorPickerModal" class="fixed inset-0 bg-black bg-opacity-50 modal-backdrop flex items-center justify-center hidden z-50">
    <div class="modal-content rounded-2xl shadow-2xl w-96 p-6 slide-in">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-xl font-bold text-gray-800">Choose Note Color</h3>
        <button onclick="closeColorPicker()" class="p-2 text-gray-400 hover:text-gray-600">
          <i class="fas fa-times"></i>
        </button>
      </div>
      
      <div class="color-picker">
        <div class="color-option" style="background-color: #ffffff;" data-color="#ffffff"></div>
        <div class="color-option" style="background-color: #fef3c7;" data-color="#fef3c7"></div>
        <div class="color-option" style="background-color: #fde68a;" data-color="#fde68a"></div>
        <div class="color-option" style="background-color: #fbbf24;" data-color="#fbbf24"></div>
        <div class="color-option" style="background-color: #f59e0b;" data-color="#f59e0b"></div>
        <div class="color-option" style="background-color: #d97706;" data-color="#d97706"></div>
        <div class="color-option" style="background-color: #b45309;" data-color="#b45309"></div>
        <div class="color-option" style="background-color: #92400e;" data-color="#92400e"></div>
        <div class="color-option" style="background-color: #dbeafe;" data-color="#dbeafe"></div>
        <div class="color-option" style="background-color: #bfdbfe;" data-color="#bfdbfe"></div>
        <div class="color-option" style="background-color: #93c5fd;" data-color="#93c5fd"></div>
        <div class="color-option" style="background-color: #60a5fa;" data-color="#60a5fa"></div>
        <div class="color-option" style="background-color: #3b82f6;" data-color="#3b82f6"></div>
        <div class="color-option" style="background-color: #2563eb;" data-color="#2563eb"></div>
        <div class="color-option" style="background-color: #1d4ed8;" data-color="#1d4ed8"></div>
        <div class="color-option" style="background-color: #1e40af;" data-color="#1e40af"></div>
        <div class="color-option" style="background-color: #fce7f3;" data-color="#fce7f3"></div>
        <div class="color-option" style="background-color: #fbcfe8;" data-color="#fbcfe8"></div>
        <div class="color-option" style="background-color: #f9a8d4;" data-color="#f9a8d4"></div>
        <div class="color-option" style="background-color: #f472b6;" data-color="#f472b6"></div>
        <div class="color-option" style="background-color: #ec4899;" data-color="#ec4899"></div>
        <div class="color-option" style="background-color: #db2777;" data-color="#db2777"></div>
        <div class="color-option" style="background-color: #be185d;" data-color="#be185d"></div>
        <div class="color-option" style="background-color: #9d174d;" data-color="#9d174d"></div>
        <div class="color-option" style="background-color: #dcfce7;" data-color="#dcfce7"></div>
        <div class="color-option" style="background-color: #bbf7d0;" data-color="#bbf7d0"></div>
        <div class="color-option" style="background-color: #86efac;" data-color="#86efac"></div>
        <div class="color-option" style="background-color: #4ade80;" data-color="#4ade80"></div>
        <div class="color-option" style="background-color: #22c55e;" data-color="#22c55e"></div>
        <div class="color-option" style="background-color: #16a34a;" data-color="#16a34a"></div>
        <div class="color-option" style="background-color: #15803d;" data-color="#15803d"></div>
        <div class="color-option" style="background-color: #166534;" data-color="#166534"></div>
        <div class="color-option" style="background-color: #fef2f2;" data-color="#fef2f2"></div>
        <div class="color-option" style="background-color: #fecaca;" data-color="#fecaca"></div>
        <div class="color-option" style="background-color: #fca5a5;" data-color="#fca5a5"></div>
        <div class="color-option" style="background-color: #f87171;" data-color="#f87171"></div>
        <div class="color-option" style="background-color: #ef4444;" data-color="#ef4444"></div>
        <div class="color-option" style="background-color: #dc2626;" data-color="#dc2626"></div>
        <div class="color-option" style="background-color: #b91c1c;" data-color="#b91c1c"></div>
        <div class="color-option" style="background-color: #991b1b;" data-color="#991b1b"></div>
      </div>
      
      <div class="flex justify-end gap-3 mt-6">
        <button onclick="closeColorPicker()" class="px-4 py-2 text-gray-600 hover:text-gray-800">
          Cancel
        </button>
        <button onclick="applyColor()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
          Apply Color
        </button>
      </div>
    </div>
  </div>

  <!-- Templates Modal -->
  <div id="templatesModal" class="fixed inset-0 bg-black bg-opacity-50 modal-backdrop flex items-center justify-center hidden z-50">
    <div class="modal-content rounded-2xl shadow-2xl w-11/12 max-w-4xl max-h-[90vh] overflow-hidden slide-in">
      <div class="p-6 border-b border-gray-200">
        <div class="flex items-center justify-between">
          <h3 class="text-2xl font-bold text-gray-800">Note Templates</h3>
          <button onclick="closeTemplatesModal()" class="p-2 text-gray-400 hover:text-gray-600 transition-colors">
            <i class="fas fa-times text-xl"></i>
          </button>
        </div>
      </div>
      
      <div class="p-6 overflow-y-auto max-h-[calc(90vh-140px)]">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <!-- Meeting Notes Template -->
          <div class="template-card bg-white rounded-xl p-4 border border-gray-200 hover:shadow-lg transition-shadow cursor-pointer" onclick="useTemplate('meeting')">
            <div class="flex items-center mb-3">
              <i class="fas fa-users text-blue-600 text-xl mr-3"></i>
              <h4 class="font-semibold text-gray-800">Meeting Notes</h4>
            </div>
            <p class="text-sm text-gray-600 mb-3">Structured template for meeting notes with agenda, attendees, and action items.</p>
            <div class="text-xs text-gray-500">Includes: Date, Attendees, Agenda, Notes, Action Items</div>
          </div>
          
          <!-- Project Planning Template -->
          <div class="template-card bg-white rounded-xl p-4 border border-gray-200 hover:shadow-lg transition-shadow cursor-pointer" onclick="useTemplate('project')">
            <div class="flex items-center mb-3">
              <i class="fas fa-project-diagram text-green-600 text-xl mr-3"></i>
              <h4 class="font-semibold text-gray-800">Project Planning</h4>
            </div>
            <p class="text-sm text-gray-600 mb-3">Comprehensive project planning template with goals, timeline, and resources.</p>
            <div class="text-xs text-gray-500">Includes: Goals, Timeline, Resources, Milestones, Risks</div>
          </div>
          
          <!-- Daily Journal Template -->
          <div class="template-card bg-white rounded-xl p-4 border border-gray-200 hover:shadow-lg transition-shadow cursor-pointer" onclick="useTemplate('journal')">
            <div class="flex items-center mb-3">
              <i class="fas fa-book text-purple-600 text-xl mr-3"></i>
              <h4 class="font-semibold text-gray-800">Daily Journal</h4>
            </div>
            <p class="text-sm text-gray-600 mb-3">Personal journal template for daily reflection and goal tracking.</p>
            <div class="text-xs text-gray-500">Includes: Date, Highlights, Challenges, Goals, Gratitude</div>
          </div>
          
          <!-- Research Notes Template -->
          <div class="template-card bg-white rounded-xl p-4 border border-gray-200 hover:shadow-lg transition-shadow cursor-pointer" onclick="useTemplate('research')">
            <div class="flex items-center mb-3">
              <i class="fas fa-search text-orange-600 text-xl mr-3"></i>
              <h4 class="font-semibold text-gray-800">Research Notes</h4>
            </div>
            <p class="text-sm text-gray-600 mb-3">Academic research template with sources, findings, and citations.</p>
            <div class="text-xs text-gray-500">Includes: Topic, Sources, Key Findings, Citations, Questions</div>
          </div>
          
          <!-- Recipe Template -->
          <div class="template-card bg-white rounded-xl p-4 border border-gray-200 hover:shadow-lg transition-shadow cursor-pointer" onclick="useTemplate('recipe')">
            <div class="flex items-center mb-3">
              <i class="fas fa-utensils text-red-600 text-xl mr-3"></i>
              <h4 class="font-semibold text-gray-800">Recipe</h4>
            </div>
            <p class="text-sm text-gray-600 mb-3">Cooking recipe template with ingredients, instructions, and notes.</p>
            <div class="text-xs text-gray-500">Includes: Ingredients, Instructions, Prep Time, Cook Time, Notes</div>
          </div>
          
          <!-- Blank Template -->
          <div class="template-card bg-white rounded-xl p-4 border border-gray-200 hover:shadow-lg transition-shadow cursor-pointer" onclick="useTemplate('blank')">
            <div class="flex items-center mb-3">
              <i class="fas fa-file-alt text-gray-600 text-xl mr-3"></i>
              <h4 class="font-semibold text-gray-800">Blank Note</h4>
            </div>
            <p class="text-sm text-gray-600 mb-3">Start with a clean slate and create your own structure.</p>
            <div class="text-xs text-gray-500">No predefined structure</div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Export Modal -->
  <div id="exportModal" class="fixed inset-0 bg-black bg-opacity-50 modal-backdrop flex items-center justify-center hidden z-50">
    <div class="modal-content rounded-2xl shadow-2xl w-11/12 max-w-2xl slide-in">
      <div class="p-6 border-b border-gray-200">
        <div class="flex items-center justify-between">
          <h3 class="text-2xl font-bold text-gray-800">Export Notes</h3>
          <button onclick="closeExportModal()" class="p-2 text-gray-400 hover:text-gray-600 transition-colors">
            <i class="fas fa-times text-xl"></i>
          </button>
        </div>
      </div>
      
      <div class="p-6">
        <div class="space-y-4">
          <!-- Export Format -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Export Format</label>
            <div class="grid grid-cols-2 gap-3">
              <label class="flex items-center p-3 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50">
                <input type="radio" name="exportFormat" value="docx" class="mr-3" checked>
                <i class="fas fa-file-word text-blue-600 mr-2"></i>
                <span>Word Document (DOCX)</span>
              </label>
              <label class="flex items-center p-3 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50">
                <input type="radio" name="exportFormat" value="pdf" class="mr-3">
                <i class="fas fa-file-pdf text-red-600 mr-2"></i>
                <span>PDF Document (Coming Soon)</span>
              </label>
              <label class="flex items-center p-3 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50">
                <input type="radio" name="exportFormat" value="markdown" class="mr-3">
                <i class="fas fa-file-alt text-gray-600 mr-2"></i>
                <span>Markdown</span>
              </label>
              <label class="flex items-center p-3 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50">
                <input type="radio" name="exportFormat" value="json" class="mr-3">
                <i class="fas fa-code text-green-600 mr-2"></i>
                <span>JSON Data</span>
              </label>
            </div>
          </div>
          
          <!-- Export Options -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Export Options</label>
            <div class="space-y-2">
              <label class="flex items-center">
                <input type="checkbox" id="includeTags" class="mr-3" checked>
                <span>Include tags</span>
              </label>
              <label class="flex items-center">
                <input type="checkbox" id="includeMetadata" class="mr-3" checked>
                <span>Include creation date and metadata</span>
              </label>
              <label class="flex items-center">
                <input type="checkbox" id="includeAttachments" class="mr-3">
                <span>Include attachments (if any)</span>
              </label>
            </div>
          </div>
          
          <!-- Export Scope -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Export Scope</label>
            <div class="space-y-2">
              <label class="flex items-center">
                <input type="radio" name="exportScope" value="all" class="mr-3" checked>
                <span>All notes</span>
              </label>
              <label class="flex items-center">
                <input type="radio" name="exportScope" value="selected" class="mr-3">
                <span>Selected notes only</span>
              </label>
              <label class="flex items-center">
                <input type="radio" name="exportScope" value="filtered" class="mr-3">
                <span>Currently filtered notes</span>
              </label>
            </div>
          </div>
        </div>
      </div>
      
      <div class="p-6 border-t border-gray-200 flex justify-end gap-3">
        <button onclick="closeExportModal()" class="px-6 py-2 text-gray-600 hover:text-gray-800 transition-colors">
          Cancel
        </button>
        <button onclick="exportNotes()" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
          <i class="fas fa-download mr-2"></i>Export Notes
        </button>
      </div>
    </div>
  </div>

  <!-- Bulk Tag Modal -->
  <div id="bulkTagModal" class="fixed inset-0 bg-black bg-opacity-50 modal-backdrop flex items-center justify-center hidden z-50">
    <div class="modal-content rounded-2xl shadow-2xl w-96 slide-in">
      <div class="p-6 border-b border-gray-200">
        <div class="flex items-center justify-between">
          <h3 class="text-xl font-bold text-gray-800">Add Tags to Selected Notes</h3>
          <button onclick="closeBulkTagModal()" class="p-2 text-gray-400 hover:text-gray-600 transition-colors">
            <i class="fas fa-times"></i>
          </button>
        </div>
      </div>
      
      <div class="p-6">
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Select Tags</label>
            <select id="bulkTagSelect" multiple class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
              <?php foreach ($tags as $tag): ?>
                <option value="<?= htmlspecialchars($tag['id']) ?>"><?= htmlspecialchars($tag['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Or Create New Tag</label>
            <input type="text" id="newBulkTag" placeholder="Enter new tag name" 
                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
          </div>
        </div>
      </div>
      
      <div class="p-6 border-t border-gray-200 flex justify-end gap-3">
        <button onclick="closeBulkTagModal()" class="px-4 py-2 text-gray-600 hover:text-gray-800">
          Cancel
        </button>
        <button onclick="applyBulkTags()" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
          Apply Tags
        </button>
      </div>
    </div>
  </div>

  <!-- Share Modal -->
  <div id="shareModal" class="fixed inset-0 bg-black bg-opacity-50 modal-backdrop flex items-center justify-center hidden z-50">
    <div class="modal-content rounded-2xl shadow-2xl w-96 slide-in">
      <div class="p-6 border-b border-gray-200">
        <div class="flex items-center justify-between">
          <h3 class="text-xl font-bold text-gray-800">Share Note</h3>
          <button onclick="closeShareModal()" class="p-2 text-gray-400 hover:text-gray-600 transition-colors">
            <i class="fas fa-times"></i>
          </button>
        </div>
      </div>
      
      <div class="p-6">
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Share Link</label>
            <div class="flex">
              <input type="text" id="shareLink" readonly 
                     class="flex-1 px-4 py-3 border border-gray-300 rounded-l-xl focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50">
              <button onclick="copyShareLink()" class="px-4 py-3 bg-blue-600 text-white rounded-r-xl hover:bg-blue-700 transition-colors">
                <i class="fas fa-copy"></i>
              </button>
            </div>
          </div>
          
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Share Options</label>
            <div class="space-y-2">
              <label class="flex items-center">
                <input type="checkbox" id="allowComments" class="mr-3">
                <span>Allow comments</span>
              </label>
              <label class="flex items-center">
                <input type="checkbox" id="allowEdit" class="mr-3">
                <span>Allow editing</span>
              </label>
              <label class="flex items-center">
                <input type="checkbox" id="expireLink" class="mr-3">
                <span>Expire link in 7 days</span>
              </label>
            </div>
          </div>
          
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Share via</label>
            <div class="flex gap-2">
              <button onclick="shareViaEmail()" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                <i class="fas fa-envelope mr-2"></i>Email
              </button>
              <button onclick="shareViaTwitter()" class="flex-1 px-4 py-2 bg-blue-400 text-white rounded-lg hover:bg-blue-500 transition-colors">
                <i class="fab fa-twitter mr-2"></i>Twitter
              </button>
              <button onclick="shareViaLinkedIn()" class="flex-1 px-4 py-2 bg-blue-700 text-white rounded-lg hover:bg-blue-800 transition-colors">
                <i class="fab fa-linkedin mr-2"></i>LinkedIn
              </button>
            </div>
          </div>
        </div>
      </div>
      
      <div class="p-6 border-t border-gray-200 flex justify-end gap-3">
        <button onclick="closeShareModal()" class="px-4 py-2 text-gray-600 hover:text-gray-800">
          Close
        </button>
        <button onclick="generateShareLink()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
          Generate Link
        </button>
      </div>
    </div>
  </div>

  
  <script>
    // Notes Loader and Toast System
    function showNotesLoader(message = 'Loading...') {
      const loader = document.getElementById('notesLoader');
      const messageEl = document.getElementById('notesLoaderMessage');
      if (messageEl) messageEl.textContent = message;
      if (loader) loader.classList.remove('hidden');
    }

    function hideNotesLoader() {
      const loader = document.getElementById('notesLoader');
      if (loader) loader.classList.add('hidden');
    }

    function showNotesToast(message, type = 'info') {
      const container = document.getElementById('notesToastContainer');
      const toast = document.createElement('div');
      
      const colors = {
        success: 'bg-green-500',
        error: 'bg-red-500',
        warning: 'bg-yellow-500',
        info: 'bg-blue-500'
      };
      
      toast.className = `${colors[type]} text-white px-6 py-3 rounded-lg shadow-lg transform translate-x-full transition-transform duration-300`;
      toast.textContent = message;
      
      container.appendChild(toast);
      
      setTimeout(() => toast.classList.remove('translate-x-full'), 100);
      setTimeout(() => {
        toast.classList.add('translate-x-full');
        setTimeout(() => toast.remove(), 300);
      }, 3000);
    }

    // Global variables
    let currentNoteId = null;
    let currentColor = '#ffffff';
    let allNotes = <?= json_encode($notes ?? []) ?>;
    let allTags = <?= json_encode($tags ?? []) ?>;
    let currentFilters = { status: 'active', priority: 'all', tag: 'all' };
    let selectedNotes = new Set();
    let currentViewMode = 'grid'; // 'grid' or 'list'
    let currentSort = 'updated_desc';
    let selectedFiles = [];
    let selectedImages = [];

    // Initialize TinyMCE
    tinymce.init({
      selector: '#noteContent',
      height: 300,
      menubar: false,
      plugins: [
        'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
        'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
        'insertdatetime', 'media', 'table', 'code', 'help', 'wordcount'
      ],
      toolbar: 'undo redo | blocks | ' +
        'bold italic forecolor | alignleft aligncenter ' +
        'alignright alignjustify | bullist numlist outdent indent | ' +
        'removeformat | help',
      content_style: 'body { font-family: Inter, sans-serif; font-size: 14px; }',
      branding: false,
      promotion: false
    });

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
      initializeFilters();
      setupEventListeners();
      initializeFileUpload();
    });

    function initializeFilters() {
      // Set active filter
      document.querySelector('[data-filter="status"][data-value="active"]').classList.add('active-filter');
    }

    function setupEventListeners() {
      // Search input
      document.getElementById('searchInput').addEventListener('input', debounce(filterNotes, 300));
      
      // File input
      document.getElementById('noteAttachments').addEventListener('change', handleFileSelection);
      
      // Color picker
      document.querySelectorAll('.color-option').forEach(option => {
        option.addEventListener('click', function() {
          document.querySelectorAll('.color-option').forEach(opt => opt.classList.remove('selected'));
          this.classList.add('selected');
          currentColor = this.dataset.color;
        });
      });
    }

    // Filter functions
    function setFilter(button) {
      const filterType = button.dataset.filter;
      const filterValue = button.dataset.value;
      
      // Remove active class from all buttons of same type
      document.querySelectorAll(`[data-filter="${filterType}"]`).forEach(btn => {
        btn.classList.remove('active-filter');
      });
      
      // Add active class to clicked button
      button.classList.add('active-filter');
      
      // Update current filters
      currentFilters[filterType] = filterValue;
      
      // Apply filters
      filterNotes();
    }

    function filterNotes() {
      const searchQuery = document.getElementById('searchInput').value.toLowerCase();
      const priorityFilter = document.getElementById('priorityFilter').value;
      const tagFilter = document.getElementById('tagFilter').value;
      const categoryFilter = document.getElementById('categoryFilter').value;
      
      const notes = document.querySelectorAll('.note-item');
      let visibleCount = 0;
      
      notes.forEach(note => {
        const noteCard = note.querySelector('.note-card');
        const title = noteCard.dataset.noteTitle.toLowerCase();
        const content = noteCard.dataset.noteContent.toLowerCase();
        const isArchived = noteCard.dataset.isArchived === '1';
        const isPinned = noteCard.dataset.isPinned === '1';
        const priority = noteCard.dataset.priority;
        const category = noteCard.dataset.category || 'general';
        const tagIds = noteCard.dataset.tagIds.split(',').filter(id => id.length > 0);
        
        // Search filter
        const searchMatch = !searchQuery || title.includes(searchQuery) || content.includes(searchQuery);
        
        // Status filter
        let statusMatch = false;
        switch (currentFilters.status) {
          case 'all':
            statusMatch = true;
            break;
          case 'active':
            statusMatch = !isArchived;
            break;
          case 'pinned':
            statusMatch = isPinned && !isArchived;
            break;
          case 'archived':
            statusMatch = isArchived;
            break;
        }
        
        // Priority filter
        const priorityMatch = priorityFilter === 'all' || priority === priorityFilter;
        
        // Tag filter
        const tagMatch = tagFilter === 'all' || tagIds.includes(tagFilter);
        
        // Category filter
        const categoryMatch = categoryFilter === 'all' || category === categoryFilter;
        
        // Show/hide note
        if (searchMatch && statusMatch && priorityMatch && tagMatch && categoryMatch) {
          note.style.display = 'block';
          visibleCount++;
        } else {
          note.style.display = 'none';
        }
      });
      
      // Show no results message if needed
      showNoResultsMessage(visibleCount === 0);
    }

    function showNoResultsMessage(show) {
      let noResultsMsg = document.getElementById('noResultsMessage');
      
      if (show && !noResultsMsg) {
        noResultsMsg = document.createElement('div');
        noResultsMsg.id = 'noResultsMessage';
        noResultsMsg.className = 'col-span-full text-center py-12';
        noResultsMsg.innerHTML = `
          <div class="floating-animation mb-6">
            <i class="fas fa-search text-6xl text-gray-300"></i>
          </div>
          <h3 class="text-xl font-semibold text-gray-600 mb-2">No notes found</h3>
          <p class="text-gray-500">Try adjusting your search or filters</p>
        `;
        document.getElementById('notesGrid').appendChild(noResultsMsg);
      } else if (!show && noResultsMsg) {
        noResultsMsg.remove();
      }
    }

    // Modal functions
    function openAddNoteModal() {
      currentNoteId = null;
      document.getElementById('modalTitle').textContent = 'Add New Note';
      document.getElementById('noteForm').reset();
      document.getElementById('noteId').value = '';
      document.getElementById('attachmentsPreview').classList.add('hidden');
      
      // Clear TinyMCE
      tinymce.get('noteContent').setContent('');
      
      showModal('noteModal');
    }

    function openEditNoteModal(button) {
      const noteCard = button.closest('.note-card');
      currentNoteId = noteCard.dataset.noteId;
      
      document.getElementById('modalTitle').textContent = 'Edit Note';
      document.getElementById('noteId').value = currentNoteId;
      document.getElementById('noteTitle').value = noteCard.dataset.noteTitle;
      document.getElementById('noteSummary').value = noteCard.dataset.noteSummary || '';
      document.getElementById('notePriority').value = noteCard.dataset.priority;
      document.getElementById('noteColor').value = noteCard.dataset.color;
      document.getElementById('notePinned').checked = noteCard.dataset.isPinned === '1';
      
      // Set TinyMCE content
      tinymce.get('noteContent').setContent(noteCard.dataset.noteContent);
      
      // Set tags
      const tagIds = noteCard.dataset.tagIds.split(',').filter(id => id.length > 0);
      const tagSelect = document.getElementById('noteTags');
      Array.from(tagSelect.options).forEach(option => {
        option.selected = tagIds.includes(option.value);
      });
      
      showModal('noteModal');
    }

    function closeNoteModal() {
      hideModal('noteModal');
    }

    // Enhanced File Upload Functions
    function initializeFileUpload() {
      const dropZone = document.getElementById('dropZone');
      const fileInput = document.getElementById('noteAttachments');
      const imageInput = document.getElementById('imageUpload');
      
      // Drag and drop functionality
      dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.classList.add('border-blue-400', 'bg-blue-50');
      });
      
      dropZone.addEventListener('dragleave', (e) => {
        e.preventDefault();
        dropZone.classList.remove('border-blue-400', 'bg-blue-50');
      });
      
      dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.classList.remove('border-blue-400', 'bg-blue-50');
        
        const files = Array.from(e.dataTransfer.files);
        handleFileSelection(files);
      });
      
      // File input change handlers
      fileInput.addEventListener('change', (e) => {
        const files = Array.from(e.target.files);
        handleFileSelection(files, 'file');
      });
      
      imageInput.addEventListener('change', (e) => {
        const files = Array.from(e.target.files);
        handleFileSelection(files, 'image');
      });
    }
    
    function handleFileSelection(files, type = 'auto') {
      files.forEach(file => {
        const fileType = type === 'auto' ? getFileType(file) : type;
        
        if (fileType === 'image') {
          selectedImages.push(file);
          displayImagePreview(file);
        } else {
          selectedFiles.push(file);
          displayFilePreview(file);
        }
      });
      
      updateAttachmentsPreview();
    }
    
    function getFileType(file) {
      if (file.type.startsWith('image/')) {
        return 'image';
      }
      return 'file';
    }
    
    function displayFilePreview(file) {
      const filesList = document.getElementById('filesList');
      const fileItem = document.createElement('div');
      fileItem.className = 'flex items-center justify-between p-3 bg-gray-50 rounded-lg';
      
      const fileIcon = getFileIcon(file.type);
      const fileSize = formatFileSize(file.size);
      
      fileItem.innerHTML = `
        <div class="flex items-center space-x-3">
          <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
            <i class="${fileIcon} text-blue-600"></i>
          </div>
          <div>
            <p class="text-sm font-medium text-gray-800">${file.name}</p>
            <p class="text-xs text-gray-500">${fileSize}</p>
          </div>
        </div>
        <button onclick="removeFile('${file.name}', 'file')" class="text-red-500 hover:text-red-700">
          <i class="fas fa-times"></i>
        </button>
      `;
      
      filesList.appendChild(fileItem);
    }
    
    function displayImagePreview(file) {
      const imagesGrid = document.getElementById('imagesGrid');
      const imageItem = document.createElement('div');
      imageItem.className = 'relative group';
      
      const reader = new FileReader();
      reader.onload = (e) => {
        imageItem.innerHTML = `
          <div class="relative">
            <img src="${e.target.result}" alt="${file.name}" class="w-full h-24 object-cover rounded-lg">
            <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-50 transition-all duration-200 rounded-lg flex items-center justify-center">
              <button onclick="removeFile('${file.name}', 'image')" class="opacity-0 group-hover:opacity-100 text-white bg-red-500 rounded-full p-1 transition-all duration-200">
                <i class="fas fa-times text-xs"></i>
              </button>
            </div>
          </div>
          <p class="text-xs text-gray-600 mt-1 truncate">${file.name}</p>
        `;
      };
      reader.readAsDataURL(file);
      
      imagesGrid.appendChild(imageItem);
    }
    
    function getFileIcon(mimeType) {
      const iconMap = {
        'application/pdf': 'fas fa-file-pdf',
        'application/msword': 'fas fa-file-word',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document': 'fas fa-file-word',
        'text/plain': 'fas fa-file-alt',
        'application/zip': 'fas fa-file-archive',
        'application/x-rar-compressed': 'fas fa-file-archive',
        'application/vnd.ms-excel': 'fas fa-file-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet': 'fas fa-file-excel',
        'application/vnd.ms-powerpoint': 'fas fa-file-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation': 'fas fa-file-powerpoint'
      };
      
      return iconMap[mimeType] || 'fas fa-file';
    }
    
    function formatFileSize(bytes) {
      if (bytes === 0) return '0 Bytes';
      const k = 1024;
      const sizes = ['Bytes', 'KB', 'MB', 'GB'];
      const i = Math.floor(Math.log(bytes) / Math.log(k));
      return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
    
    function removeFile(fileName, type) {
      if (type === 'image') {
        selectedImages = selectedImages.filter(file => file.name !== fileName);
      } else {
        selectedFiles = selectedFiles.filter(file => file.name !== fileName);
      }
      updateAttachmentsPreview();
    }
    
    function clearAllAttachments() {
      selectedFiles = [];
      selectedImages = [];
      updateAttachmentsPreview();
    }
    
    function updateAttachmentsPreview() {
      const preview = document.getElementById('attachmentsPreview');
      const filesList = document.getElementById('filesList');
      const imagesGrid = document.getElementById('imagesGrid');
      
      // Clear existing previews
      filesList.innerHTML = '';
      imagesGrid.innerHTML = '';
      
      // Show/hide preview section
      if (selectedFiles.length > 0 || selectedImages.length > 0) {
        preview.classList.remove('hidden');
      } else {
        preview.classList.add('hidden');
      }
    }

    function showModal(modalId) {
      const modal = document.getElementById(modalId);
      modal.classList.remove('hidden');
      modal.classList.add('flex');
      
      // Focus first input
      setTimeout(() => {
        const firstInput = modal.querySelector('input, textarea, select');
        if (firstInput) firstInput.focus();
      }, 100);
    }

    function hideModal(modalId) {
      const modal = document.getElementById(modalId);
      modal.classList.add('hidden');
      modal.classList.remove('flex');
    }

    // Note actions
    function saveNote() {
      const form = document.getElementById('noteForm');
      const formData = new FormData(form);
      
      // Get TinyMCE content
      formData.set('content', tinymce.get('noteContent').getContent());
      
      const saveButton = document.querySelector('#saveButtonText');
      const action = currentNoteId ? '/notes/update' : '/notes/store';
      const actionText = currentNoteId ? 'Updating note...' : 'Creating note...';
      
      showNotesLoader(actionText);
      
      fetch(action, {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          showNotesToast(data.message, 'success');
          closeNoteModal();
          // Refresh notes grid
          setTimeout(() => window.location.reload(), 1000);
        } else {
          showNotesToast(data.message, 'error');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        showNotesToast('An error occurred while saving the note', 'error');
      })
      .finally(() => {
        hideNotesLoader();
      });
    }

    function togglePin(noteId) {
      // Show a simple toast instead of full loader
      showNotesToast('Updating note...', 'info');
      
      fetch('/notes/toggle-pin', {
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
          showNotesToast(data.message, 'success');
          setTimeout(() => window.location.reload(), 1000);
        } else {
          showNotesToast(data.message, 'error');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        showNotesToast('An error occurred', 'error');
      });
    }

    function archiveNote(noteId) {
      // Show confirmation toast instead of alert
      showNotesToast('Archiving note...', 'warning');
      
      setTimeout(() => {
        fetch('/notes/archive', {
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
            showNotesToast(data.message, 'success');
            setTimeout(() => window.location.reload(), 1000);
          } else {
            showNotesToast(data.message, 'error');
          }
        })
        .catch(error => {
          console.error('Error:', error);
          showNotesToast('An error occurred', 'error');
        });
      }, 2000);
    }

    function deleteNote(noteId) {
      if (confirm('Are you sure you want to move this note to trash? You can restore it later from the trash page.')) {
        fetch('/notes/delete', {
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
            showNotesToast(data.message, 'success');
            setTimeout(() => window.location.reload(), 1000);
          } else {
            showNotesToast(data.message, 'error');
          }
        })
        .catch(error => {
          console.error('Error:', error);
          showNotesToast('An error occurred', 'error');
        });
      }
    }

    // Color picker functions
    function openColorPicker(noteId) {
      currentNoteId = noteId;
      showModal('colorPickerModal');
    }

    function closeColorPicker() {
      hideModal('colorPickerModal');
    }

    function applyColor() {
      if (currentNoteId && currentColor) {
        fetch('/notes/update-color', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: JSON.stringify({
            note_id: currentNoteId,
            color: currentColor,
            csrf_token: document.querySelector('input[name="csrf_token"]').value
          })
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            showNotesToast('Note color updated successfully', 'success');
            closeColorPicker();
            setTimeout(() => window.location.reload(), 1000);
          } else {
            showNotesToast(data.message, 'error');
          }
        })
        .catch(error => {
          console.error('Error:', error);
          showNotesToast('An error occurred', 'error');
        });
      }
    }

    // File handling
    function handleFileSelection(event) {
      const files = event.target.files;
      const preview = document.getElementById('attachmentsPreview');
      const list = document.getElementById('attachmentsList');
      
      if (files.length > 0) {
        preview.classList.remove('hidden');
        list.innerHTML = '';
        
        Array.from(files).forEach(file => {
          const item = document.createElement('div');
          item.className = 'attachment-item flex items-center justify-between';
          item.innerHTML = `
            <div class="flex items-center">
              <i class="fas fa-file mr-3 text-blue-500"></i>
              <span class="text-sm font-medium">${file.name}</span>
              <span class="text-xs text-gray-500 ml-2">(${(file.size / 1024 / 1024).toFixed(2)} MB)</span>
            </div>
            <button type="button" onclick="removeFile(this)" class="text-red-500 hover:text-red-700">
              <i class="fas fa-times"></i>
            </button>
          `;
          list.appendChild(item);
        });
      } else {
        preview.classList.add('hidden');
      }
    }

    function removeFile(button) {
      button.closest('.attachment-item').remove();
      
      // Update file input
      const fileInput = document.getElementById('noteAttachments');
      const dt = new DataTransfer();
      
      // Re-add remaining files
      const remainingItems = document.querySelectorAll('#attachmentsList .attachment-item');
      if (remainingItems.length === 0) {
        document.getElementById('attachmentsPreview').classList.add('hidden');
      }
      
      fileInput.files = dt.files;
    }

    // Menu functions
    function toggleNoteMenu(button) {
      const noteCard = button.closest('.note-card');
      const noteId = noteCard.dataset.noteId;
      const isOpen = document.querySelector('.menu-overlay.active') !== null;
      
      // Close any existing menu
      closeNoteMenu();
      
      if (!isOpen) {
        // Create overlay
        const overlay = document.createElement('div');
        overlay.className = 'menu-overlay';
        overlay.onclick = closeNoteMenu;
        
        // Create menu
        const menu = document.createElement('div');
        menu.className = 'note-menu';
        menu.innerHTML = `
          <div class="flex justify-between items-center px-4 py-2 border-b border-gray-200">
            <span class="text-sm font-semibold text-gray-700">Note Actions</span>
            <button onclick="closeNoteMenu()" class="text-gray-400 hover:text-gray-600 transition-colors">
              <i class="fas fa-times"></i>
            </button>
          </div>
          <button onclick="openViewNoteModal(this)" class="w-full text-left px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 flex items-center border-b border-gray-100">
            <i class="fas fa-eye mr-3 text-blue-500"></i>View Note
          </button>
          <button onclick="openEditNoteModal(this)" class="w-full text-left px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 flex items-center border-b border-gray-100">
            <i class="fas fa-edit mr-3 text-yellow-500"></i>Edit Note
          </button>
          <button onclick="togglePin(${noteId})" class="w-full text-left px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 flex items-center border-b border-gray-100">
            <i class="fas fa-thumbtack mr-3 text-amber-500"></i>Pin Note
          </button>
          <button onclick="openColorPicker(${noteId})" class="w-full text-left px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 flex items-center border-b border-gray-100">
            <i class="fas fa-palette mr-3 text-purple-500"></i>Change Color
          </button>
          <button onclick="shareNote(${noteId})" class="w-full text-left px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 flex items-center border-b border-gray-100">
            <i class="fas fa-share mr-3 text-green-500"></i>Share Note
          </button>
          <button onclick="archiveNote(${noteId})" class="w-full text-left px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 flex items-center border-b border-gray-100">
            <i class="fas fa-archive mr-3 text-orange-500"></i>Archive Note
          </button>
          <button onclick="deleteNote(${noteId})" class="w-full text-left px-4 py-3 text-sm text-red-600 hover:bg-red-50 flex items-center">
            <i class="fas fa-trash mr-3 text-red-500"></i>Delete Note
          </button>
        `;
        
        // Position menu near the button
        const rect = button.getBoundingClientRect();
        const menuWidth = 200;
        const menuHeight = 280;
        
        let left = rect.right - menuWidth;
        let top = rect.bottom + 10;
        
        // Adjust if menu goes off screen
        if (left < 10) left = 10;
        if (left + menuWidth > window.innerWidth - 10) left = window.innerWidth - menuWidth - 10;
        if (top + menuHeight > window.innerHeight - 10) top = rect.top - menuHeight - 10;
        
        menu.style.left = left + 'px';
        menu.style.top = top + 'px';
        
        // Add to document
        document.body.appendChild(overlay);
        document.body.appendChild(menu);
        
        // Show with animation
        setTimeout(() => {
          overlay.classList.add('active');
          menu.style.opacity = '0';
          menu.style.transform = 'translateY(-10px) scale(0.95)';
          menu.style.transition = 'all 0.2s ease-out';
          
          setTimeout(() => {
            menu.style.opacity = '1';
            menu.style.transform = 'translateY(0) scale(1)';
          }, 10);
        }, 10);
      }
    }
    
    function closeNoteMenu() {
      const overlay = document.querySelector('.menu-overlay');
      const menu = document.querySelector('.note-menu');
      
      if (overlay) {
        overlay.classList.remove('active');
        setTimeout(() => {
          if (overlay.parentNode) {
            overlay.remove();
          }
        }, 200);
      }
      
      if (menu) {
        menu.style.opacity = '0';
        menu.style.transform = 'translateY(-10px) scale(0.95)';
        setTimeout(() => {
          if (menu.parentNode) {
            menu.remove();
          }
        }, 200);
      }
      
      // Remove menu-open class from all cards
      document.querySelectorAll('.note-card').forEach(card => card.classList.remove('menu-open'));
    }

    // Close menus when clicking outside
    document.addEventListener('click', function(e) {
      if (!e.target.closest('.note-menu') && !e.target.closest('button[onclick*="toggleNoteMenu"]')) {
        closeNoteMenu();
      }
    });
    
    // Close menu with Escape key
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') {
        closeNoteMenu();
      }
    });
    
    // Make closeNoteMenu globally accessible
    window.closeNoteMenu = closeNoteMenu;
    
    // Debug function to check menu state
    window.debugMenu = function() {
      console.log('Overlay:', document.querySelector('.menu-overlay'));
      console.log('Menu:', document.querySelector('.note-menu'));
      console.log('Active overlay:', document.querySelector('.menu-overlay.active'));
    };

    // Utility functions
    function debounce(func, wait) {
      let timeout;
      return function executedFunction(...args) {
        const later = () => {
          clearTimeout(timeout);
          func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
      };
    }


    // Version history functions
    function openVersionHistory(noteId) {
      fetch(`/notes/versions/${noteId}`, {
        method: 'GET',
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        }
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          showVersionHistoryModal(data.versions);
        } else {
          showNotesToast(data.message, 'error');
        }
      })
      .catch(error => {
        console.error('Error loading version history:', error);
        showNotesToast('Error loading version history', 'error');
      });
    }

    function showVersionHistoryModal(versions) {
      const modal = document.createElement('div');
      modal.className = 'fixed inset-0 bg-black bg-opacity-50 modal-backdrop flex items-center justify-center z-50';
      modal.innerHTML = `
        <div class="modal-content rounded-2xl shadow-2xl w-11/12 max-w-4xl max-h-[90vh] overflow-hidden slide-in">
          <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
              <h3 class="text-2xl font-bold text-gray-800">Version History</h3>
              <button onclick="this.closest('.fixed').remove()" class="p-2 text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
              </button>
            </div>
          </div>
          
          <div class="p-6 overflow-y-auto max-h-[calc(90vh-140px)]">
            <div class="space-y-4">
              ${versions.map(version => `
                <div class="border border-gray-200 rounded-xl p-4 hover:shadow-md transition-shadow">
                  <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-3">
                      <span class="text-sm font-medium text-gray-600">Version ${version.version_number}</span>
                      <span class="text-xs text-gray-500">${new Date(version.created_at).toLocaleString()}</span>
                    </div>
                    <button onclick="restoreVersion(${version.id})" class="px-3 py-1 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700">
                      Restore
                    </button>
                  </div>
                  <div class="text-sm text-gray-700 line-clamp-3">
                    ${version.content.replace(/<[^>]*>/g, '').substring(0, 200)}...
                  </div>
                </div>
              `).join('')}
            </div>
          </div>
        </div>
      `;
      
      document.body.appendChild(modal);
    }

    function restoreVersion(versionId) {
      if (confirm('Are you sure you want to restore this version? This will replace your current note content.')) {
        fetch('/notes/restore-version', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: JSON.stringify({
            version_id: versionId,
            csrf_token: document.querySelector('input[name="csrf_token"]').value
          })
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            showNotesToast('Version restored successfully', 'success');
            document.querySelector('.fixed.inset-0').remove();
            setTimeout(() => window.location.reload(), 1000);
          } else {
            showNotesToast(data.message, 'error');
          }
        })
        .catch(error => {
          console.error('Error:', error);
          showNotesToast('An error occurred', 'error');
        });
      }
    }

    // Auto-save functionality (moved to global scope)
    function enableAutoSave() {
      const titleInput = document.getElementById('noteTitle');
      const contentEditor = tinymce.get('noteContent');
      
      if (titleInput) {
        titleInput.addEventListener('input', scheduleAutoSave);
      }
      
      if (contentEditor) {
        contentEditor.on('input', scheduleAutoSave);
      }
    }

    function scheduleAutoSave() {
      clearTimeout(autoSaveTimeout);
      autoSaveTimeout = setTimeout(autoSaveNote, 2000); // Auto-save after 2 seconds of inactivity
    }

    function autoSaveNote() {
      if (!currentNoteId) return; // Only auto-save existing notes
      
      const formData = new FormData();
      formData.append('id', currentNoteId);
      formData.append('title', document.getElementById('noteTitle').value);
      formData.append('content', tinymce.get('noteContent').getContent());
      formData.append('csrf_token', document.querySelector('input[name="csrf_token"]').value);
      
      fetch('/notes/auto-save', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          console.log('Note auto-saved');
        }
      })
      .catch(error => {
        console.error('Auto-save error:', error);
      });
    }

    // Advanced search modal
    function openAdvancedSearchModal() {
      const modal = document.createElement('div');
      modal.className = 'fixed inset-0 bg-black bg-opacity-50 modal-backdrop flex items-center justify-center z-50';
      modal.innerHTML = `
        <div class="modal-content rounded-2xl shadow-2xl w-11/12 max-w-2xl slide-in">
          <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
              <h3 class="text-xl font-bold text-gray-800">Advanced Search</h3>
              <button onclick="this.closest('.fixed').remove()" class="p-2 text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
              </button>
            </div>
          </div>
          
          <div class="p-6">
            <form id="advancedSearchForm">
              <div class="space-y-4">
                <div>
                  <label class="block text-sm font-semibold text-gray-700 mb-2">Search Terms</label>
                  <input type="text" id="searchTerms" placeholder="Enter search terms..." 
                         class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Date From</label>
                    <input type="date" id="dateFrom" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                  </div>
                  <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Date To</label>
                    <input type="date" id="dateTo" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                  </div>
                </div>
                
                <div>
                  <label class="block text-sm font-semibold text-gray-700 mb-2">Priority</label>
                  <select id="searchPriority" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Priorities</option>
                    <option value="urgent">Urgent</option>
                    <option value="high">High</option>
                    <option value="medium">Medium</option>
                    <option value="low">Low</option>
                  </select>
                </div>
                
                <div>
                  <label class="block text-sm font-semibold text-gray-700 mb-2">Tags</label>
                  <select id="searchTags" multiple class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                    ${allTags.map(tag => '<option value="' + tag.id + '">' + tag.name + '</option>').join('')}
                  </select>
                </div>
              </div>
            </form>
          </div>
          
          <div class="p-6 border-t border-gray-200 flex justify-end gap-3">
            <button onclick="this.closest('.fixed').remove()" class="px-4 py-2 text-gray-600 hover:text-gray-800">
              Cancel
            </button>
            <button onclick="performAdvancedSearch()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
              Search
            </button>
          </div>
        </div>
      `;
      
      document.body.appendChild(modal);
    }

    function performAdvancedSearch() {
      const form = document.getElementById('advancedSearchForm');
      const formData = new FormData(form);
      
      const searchParams = {
        terms: document.getElementById('searchTerms').value,
        date_from: document.getElementById('dateFrom').value,
        date_to: document.getElementById('dateTo').value,
        priority: document.getElementById('searchPriority').value,
        tags: Array.from(document.getElementById('searchTags').selectedOptions).map(option => option.value),
        csrf_token: document.querySelector('input[name="csrf_token"]').value
      };
      
      fetch('/notes/advanced-search', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(searchParams)
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          // Close modal
          document.querySelector('.fixed.inset-0').remove();
          
          // Update notes grid with search results
          updateNotesGrid(data.notes);
          showNotesToast(`Found ${data.notes.length} notes`, 'success');
        } else {
          showNotesToast(data.message, 'error');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        showNotesToast('An error occurred during search', 'error');
      });
    }

    function updateNotesGrid(notes) {
      const grid = document.getElementById('notesGrid');
      grid.innerHTML = '';
      
      if (notes.length === 0) {
        grid.innerHTML = `
          <div class="col-span-full text-center py-12">
            <div class="floating-animation mb-6">
              <i class="fas fa-search text-6xl text-gray-300"></i>
            </div>
            <h3 class="text-xl font-semibold text-gray-600 mb-2">No notes found</h3>
            <p class="text-gray-500">Try adjusting your search criteria</p>
          </div>
        `;
        return;
      }
      
      notes.forEach(note => {
        const noteElement = createNoteElement(note);
        grid.appendChild(noteElement);
      });
    }

    function createNoteElement(note) {
      const div = document.createElement('div');
      div.className = 'note-item';
      
      const noteTags = note.tags && note.tags.length > 0 ? note.tags.split(',').filter(tag => tag && tag.trim()).map(tag => tag.trim()) : [];
      const tagIds = note.tag_ids && note.tag_ids.length > 0 ? note.tag_ids.split(',').filter(id => id && id.trim()).map(id => id.trim()) : [];
      const isArchived = note.is_archived || false;
      const isPinned = note.is_pinned || false;
      const priority = note.priority || 'medium';
      const color = note.color || '#ffffff';
      
      div.innerHTML = `
        <div class="note-card rounded-2xl shadow-lg p-6 ${isPinned ? 'pinned' : ''} priority-${priority}" 
             style="border-left-color: ${color}; position: relative; z-index: 1;"
             data-note-id="${note.id || ''}"
             data-note-title="${note.title || ''}"
             data-note-content="${note.content || ''}"
             data-note-summary="${note.summary || ''}"
             data-category="${note.category || 'general'}"
             data-is-archived="${isArchived ? 1 : 0}"
             data-is-pinned="${isPinned ? 1 : 0}"
             data-priority="${priority}"
             data-color="${color}"
             data-note-tags="${note.tags || ''}"
             data-tag-ids="${note.tag_ids || ''}"
             data-updated-at="${note.updated_at}">
          
          <div class="flex items-start justify-between mb-4">
            <div class="flex-1">
              <h3 class="text-xl font-bold text-gray-800 mb-2 line-clamp-2">${note.title || 'Untitled'}</h3>
              <div class="flex items-center gap-2 text-sm text-gray-500">
                ${isPinned ? '<i class="fas fa-thumbtack text-amber-500"></i>' : ''}
                <span>${note.updated_at ? new Date(note.updated_at).toLocaleDateString() : 'Unknown'}</span>
                <span>•</span>
                <span>${note.word_count || 0} words</span>
              </div>
            </div>
            
            <div class="note-actions relative z-[100]">
              <button onclick="toggleNoteMenu(this)" class="p-2 text-gray-400 hover:text-gray-600 transition-colors">
                <i class="fas fa-ellipsis-v"></i>
              </button>
              
              <div class="note-menu hidden absolute right-0 top-full mt-2 w-48 bg-white rounded-xl shadow-xl border border-gray-200 py-2 z-[9999]">
                <button onclick="openViewNoteModal(this)" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center">
                  <i class="fas fa-eye mr-3 text-blue-500"></i>View
                </button>
                <button onclick="openEditNoteModal(this)" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center">
                  <i class="fas fa-edit mr-3 text-yellow-500"></i>Edit
                </button>
                <button onclick="togglePin('${note.id || ''}')" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center">
                  <i class="fas fa-thumbtack mr-3 text-amber-500"></i>${isPinned ? 'Unpin' : 'Pin'}
                </button>
                <button onclick="openColorPicker('${note.id || ''}')" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center">
                  <i class="fas fa-palette mr-3 text-purple-500"></i>Change Color
                </button>
                <button onclick="openVersionHistory('${note.id || ''}')" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center">
                  <i class="fas fa-history mr-3 text-indigo-500"></i>Version History
                </button>
                <hr class="my-2">
                <button onclick="archiveNote('${note.id || ''}')" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center">
                  <i class="fas fa-archive mr-3 text-gray-500"></i>${isArchived ? 'Unarchive' : 'Archive'}
                </button>
                <button onclick="deleteNote('${note.id || ''}')" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 flex items-center">
                  <i class="fas fa-trash mr-3"></i>Delete
                </button>
              </div>
            </div>
          </div>
          
          <div class="text-gray-700 text-sm leading-relaxed mb-4 line-clamp-6">
            ${(note.content || '').replace(/<[^>]*>/g, '').substring(0, 200)}...
          </div>
          
          ${noteTags && noteTags.length > 0 ? `
            <div class="mb-4">
              ${noteTags.map(tag => '<span class="tag-badge">' + (tag || '') + '</span>').join('')}
            </div>
          ` : ''}
          
          <div class="flex items-center justify-between pt-4 border-t border-gray-100">
            <div class="flex items-center gap-2">
              <span class="text-xs text-gray-400">${note.updated_at ? new Date(note.updated_at).toLocaleTimeString() : 'Unknown'}</span>
            </div>
            <div class="flex items-center gap-2">
              <span class="text-xs px-2 py-1 rounded-full bg-gray-100 text-gray-600">${priority ? priority.charAt(0).toUpperCase() + priority.slice(1) : 'Medium'}</span>
            </div>
          </div>
        </div>
      `;
      
      return div;
    }

    // View note modal
    function openViewNoteModal(button) {
      const noteCard = button.closest('.note-card');
      const noteId = noteCard.dataset.noteId;
      const title = noteCard.dataset.noteTitle;
      const content = noteCard.dataset.noteContent;
      
      const modal = document.createElement('div');
      modal.className = 'fixed inset-0 bg-black bg-opacity-50 modal-backdrop flex items-center justify-center z-50';
      modal.innerHTML = `
        <div class="modal-content rounded-2xl shadow-2xl w-11/12 max-w-4xl max-h-[90vh] overflow-hidden slide-in">
          <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
              <h3 class="text-2xl font-bold text-gray-800">${title}</h3>
              <button onclick="this.closest('.fixed').remove()" class="p-2 text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
              </button>
            </div>
          </div>
          
          <div class="p-6 overflow-y-auto max-h-[calc(90vh-140px)]">
            <div class="prose max-w-none">
              ${content}
            </div>
          </div>
          
          <div class="p-6 border-t border-gray-200 flex justify-end gap-3">
            <button onclick="this.closest('.fixed').remove()" class="px-4 py-2 text-gray-600 hover:text-gray-800">
              Close
            </button>
            <button onclick="openEditNoteModal(document.querySelector('[data-note-id=\\"${noteId}\\"] .note-menu button'))" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
              Edit Note
            </button>
          </div>
        </div>
      `;
      
      document.body.appendChild(modal);
    }

    // Initialize auto-save when modal opens
    document.addEventListener('DOMContentLoaded', function() {
      // Enable auto-save for existing notes
      const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
          if (mutation.type === 'childList') {
            const modals = document.querySelectorAll('#noteModal');
            modals.forEach(modal => {
              if (!modal.classList.contains('hidden')) {
                enableAutoSave();
              }
            });
          }
        });
      });
      
      observer.observe(document.body, {
        childList: true,
        subtree: true
      });
    });

    // ========================================
    // BULK ACTIONS FUNCTIONALITY
    // ========================================
    
    function updateBulkActions() {
      const checkboxes = document.querySelectorAll('.note-checkbox:checked');
      selectedNotes.clear();
      checkboxes.forEach(checkbox => {
        selectedNotes.add(checkbox.dataset.noteId);
      });
      
      const bulkActions = document.getElementById('bulkActions');
      const selectedCount = document.getElementById('selectedCount');
      
      if (selectedNotes.size > 0) {
        bulkActions.classList.remove('hidden');
        selectedCount.textContent = `${selectedNotes.size} selected`;
      } else {
        bulkActions.classList.add('hidden');
      }
    }
    
    function clearSelection() {
      document.querySelectorAll('.note-checkbox').forEach(checkbox => {
        checkbox.checked = false;
      });
      selectedNotes.clear();
      updateBulkActions();
    }
    
    function bulkArchive() {
      if (selectedNotes.size === 0) return;
      
      showNotesToast(`Archiving ${selectedNotes.size} notes...`, 'info');
      
      const promises = Array.from(selectedNotes).map(noteId => {
        return fetch('/notes/archive', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: JSON.stringify({
            note_id: noteId,
            csrf_token: document.querySelector('input[name="csrf_token"]').value
          })
        });
      });
      
      Promise.all(promises).then(() => {
        showNotesToast(`Successfully archived ${selectedNotes.size} notes`, 'success');
        clearSelection();
        setTimeout(() => window.location.reload(), 1000);
      }).catch(error => {
        showNotesToast('Error archiving notes', 'error');
      });
    }
    
    function bulkDelete() {
      if (selectedNotes.size === 0) return;
      
      if (confirm(`Are you sure you want to permanently delete ${selectedNotes.size} notes? This action cannot be undone.`)) {
        showNotesToast(`Deleting ${selectedNotes.size} notes...`, 'warning');
        
        const promises = Array.from(selectedNotes).map(noteId => {
          return fetch('/notes/delete', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
              note_id: noteId,
              csrf_token: document.querySelector('input[name="csrf_token"]').value
            })
          });
        });
        
        Promise.all(promises).then(() => {
          showNotesToast(`Successfully deleted ${selectedNotes.size} notes`, 'success');
          clearSelection();
          setTimeout(() => window.location.reload(), 1000);
        }).catch(error => {
          showNotesToast('Error deleting notes', 'error');
        });
      }
    }
    
    function bulkPin() {
      if (selectedNotes.size === 0) return;
      
      showNotesToast(`Pinning ${selectedNotes.size} notes...`, 'info');
      
      const promises = Array.from(selectedNotes).map(noteId => {
        return fetch('/notes/toggle-pin', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: JSON.stringify({
            note_id: noteId,
            csrf_token: document.querySelector('input[name="csrf_token"]').value
          })
        });
      });
      
      Promise.all(promises).then(() => {
        showNotesToast(`Successfully pinned ${selectedNotes.size} notes`, 'success');
        clearSelection();
        setTimeout(() => window.location.reload(), 1000);
      }).catch(error => {
        showNotesToast('Error pinning notes', 'error');
      });
    }
    
    function bulkTag() {
      if (selectedNotes.size === 0) return;
      showModal('bulkTagModal');
    }
    
    function closeBulkTagModal() {
      hideModal('bulkTagModal');
    }
    
    function applyBulkTags() {
      const selectedTags = Array.from(document.getElementById('bulkTagSelect').selectedOptions).map(option => option.value);
      const newTag = document.getElementById('newBulkTag').value.trim();
      
      if (selectedTags.length === 0 && !newTag) {
        showNotesToast('Please select tags or enter a new tag', 'warning');
        return;
      }
      
      showNotesToast(`Adding tags to ${selectedNotes.size} notes...`, 'info');
      
      // This would need backend implementation for bulk tag operations
      showNotesToast('Bulk tagging feature will be implemented in the backend', 'info');
      closeBulkTagModal();
    }

    // ========================================
    // TEMPLATES FUNCTIONALITY
    // ========================================
    
    function openTemplatesModal() {
      showModal('templatesModal');
    }
    
    function closeTemplatesModal() {
      hideModal('templatesModal');
    }
    
    function useTemplate(templateType) {
      const templates = {
        meeting: {
          title: 'Meeting Notes',
          content: `<h2>Meeting: [Meeting Title]</h2>
<p><strong>Date:</strong> ${new Date().toLocaleDateString()}</p>
<p><strong>Attendees:</strong></p>
<ul>
  <li>[Name 1]</li>
  <li>[Name 2]</li>
</ul>

<h3>Agenda</h3>
<ol>
  <li>[Agenda Item 1]</li>
  <li>[Agenda Item 2]</li>
</ol>

<h3>Discussion Notes</h3>
<p>[Key discussion points and decisions]</p>

<h3>Action Items</h3>
<ul>
  <li>[ ] [Action Item 1] - [Assignee] - [Due Date]</li>
  <li>[ ] [Action Item 2] - [Assignee] - [Due Date]</li>
</ul>`
        },
        project: {
          title: 'Project Planning',
          content: `<h2>Project: [Project Name]</h2>
<p><strong>Start Date:</strong> ${new Date().toLocaleDateString()}</p>
<p><strong>Target Completion:</strong> [Date]</p>

<h3>Project Goals</h3>
<ul>
  <li>[Goal 1]</li>
  <li>[Goal 2]</li>
</ul>

<h3>Timeline & Milestones</h3>
<ul>
  <li><strong>Phase 1:</strong> [Description] - [Due Date]</li>
  <li><strong>Phase 2:</strong> [Description] - [Due Date]</li>
</ul>

<h3>Resources Needed</h3>
<ul>
  <li>[Resource 1]</li>
  <li>[Resource 2]</li>
</ul>

<h3>Risks & Mitigation</h3>
<ul>
  <li><strong>Risk:</strong> [Description] - <strong>Mitigation:</strong> [Plan]</li>
</ul>`
        },
        journal: {
          title: 'Daily Journal',
          content: `<h2>Daily Journal - ${new Date().toLocaleDateString()}</h2>

<h3>Today's Highlights</h3>
<p>[What went well today?]</p>

<h3>Challenges Faced</h3>
<p>[What difficulties did you encounter?]</p>

<h3>Goals for Today</h3>
<ul>
  <li>[ ] [Goal 1]</li>
  <li>[ ] [Goal 2]</li>
</ul>

<h3>Lessons Learned</h3>
<p>[What did you learn today?]</p>

<h3>Gratitude</h3>
<p>[What are you grateful for today?]</p>

<h3>Tomorrow's Focus</h3>
<p>[What will you focus on tomorrow?]</p>`
        },
        research: {
          title: 'Research Notes',
          content: `<h2>Research: [Topic]</h2>
<p><strong>Research Date:</strong> ${new Date().toLocaleDateString()}</p>

<h3>Research Question</h3>
<p>[What are you trying to find out?]</p>

<h3>Sources</h3>
<ul>
  <li>[Source 1] - [URL/Reference]</li>
  <li>[Source 2] - [URL/Reference]</li>
</ul>

<h3>Key Findings</h3>
<ul>
  <li>[Finding 1]</li>
  <li>[Finding 2]</li>
</ul>

<h3>Citations</h3>
<p>[Proper citations in your preferred format]</p>

<h3>Questions for Further Research</h3>
<ul>
  <li>[Question 1]</li>
  <li>[Question 2]</li>
</ul>`
        },
        recipe: {
          title: 'Recipe',
          content: `<h2>[Recipe Name]</h2>
<p><strong>Prep Time:</strong> [Time] | <strong>Cook Time:</strong> [Time] | <strong>Servings:</strong> [Number]</p>

<h3>Ingredients</h3>
<ul>
  <li>[Ingredient 1] - [Amount]</li>
  <li>[Ingredient 2] - [Amount]</li>
</ul>

<h3>Instructions</h3>
<ol>
  <li>[Step 1]</li>
  <li>[Step 2]</li>
</ol>

<h3>Notes</h3>
<p>[Cooking tips, variations, or additional notes]</p>`
        },
        blank: {
          title: 'New Note',
          content: ''
        }
      };
      
      const template = templates[templateType];
      if (template) {
        document.getElementById('noteTitle').value = template.title;
        tinymce.get('noteContent').setContent(template.content);
        closeTemplatesModal();
        showModal('noteModal');
      }
    }

    // ========================================
    // EXPORT FUNCTIONALITY
    // ========================================
    
    function openExportModal() {
      showModal('exportModal');
    }
    
    function closeExportModal() {
      hideModal('exportModal');
    }
    
    function exportNotes() {
      const format = document.querySelector('input[name="exportFormat"]:checked').value;
      const scope = document.querySelector('input[name="exportScope"]:checked').value;
      const includeTags = document.getElementById('includeTags').checked;
      const includeMetadata = document.getElementById('includeMetadata').checked;
      const includeAttachments = document.getElementById('includeAttachments').checked;
      
      showNotesToast(`Exporting notes as ${format.toUpperCase()}...`, 'info');
      
      const exportData = {
        format: format,
        scope: scope,
        include_tags: includeTags,
        include_metadata: includeMetadata,
        include_attachments: includeAttachments,
        csrf_token: document.querySelector('input[name="csrf_token"]').value
      };
      
      // Add selected note IDs if scope is 'selected'
      if (scope === 'selected' && selectedNotes.size > 0) {
        exportData.note_ids = Array.from(selectedNotes);
      }
      
      fetch('/notes/export', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(exportData)
      })
      .then(response => {
        if (response.ok) {
          // For file downloads, the response will be the file itself
          return response.blob();
        } else {
          return response.json().then(data => {
            throw new Error(data.message || 'Export failed');
          });
        }
      })
      .then(blob => {
        // Create download link
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        
        // Set appropriate file extension
        let extension = 'md';
        if (format === 'json') extension = 'json';
        else if (format === 'docx' || format === 'word') extension = 'docx';
        else if (format === 'pdf') extension = 'pdf';
        
        a.download = `notes_export_${new Date().toISOString().slice(0, 19).replace(/:/g, '-')}.${extension}`;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);
        
        showNotesToast('Notes exported successfully!', 'success');
        closeExportModal();
      })
      .catch(error => {
        console.error('Export error:', error);
        showNotesToast('Export failed: ' + error.message, 'error');
      });
    }

    // ========================================
    // VIEW MODE TOGGLE
    // ========================================
    
    function toggleViewMode() {
      currentViewMode = currentViewMode === 'grid' ? 'list' : 'grid';
      const grid = document.getElementById('notesGrid');
      const icon = document.getElementById('viewModeIcon');
      const text = document.getElementById('viewModeText');
      
      if (currentViewMode === 'list') {
        grid.className = 'space-y-4';
        icon.className = 'fas fa-list mr-2';
        text.textContent = 'List';
      } else {
        grid.className = 'notes-grid';
        icon.className = 'fas fa-th-large mr-2';
        text.textContent = 'Grid';
      }
      
      showNotesToast(`Switched to ${currentViewMode} view`, 'info');
    }


    // ========================================
    // SORTING FUNCTIONALITY
    // ========================================
    
    function sortNotes() {
      const sortBy = document.getElementById('sortBy').value;
      currentSort = sortBy;
      
      const notes = Array.from(document.querySelectorAll('.note-item'));
      
      notes.sort((a, b) => {
        const noteA = a.querySelector('.note-card');
        const noteB = b.querySelector('.note-card');
        
        switch (sortBy) {
          case 'title_asc':
            return noteA.dataset.noteTitle.localeCompare(noteB.dataset.noteTitle);
          case 'title_desc':
            return noteB.dataset.noteTitle.localeCompare(noteA.dataset.noteTitle);
          case 'updated_desc':
            return new Date(noteB.dataset.updatedAt) - new Date(noteA.dataset.updatedAt);
          case 'created_desc':
            return new Date(noteB.dataset.updatedAt) - new Date(noteA.dataset.updatedAt);
          case 'word_count_desc':
            return (parseInt(noteB.dataset.wordCount) || 0) - (parseInt(noteA.dataset.wordCount) || 0);
          case 'word_count_asc':
            return (parseInt(noteA.dataset.wordCount) || 0) - (parseInt(noteB.dataset.wordCount) || 0);
          case 'priority':
            const priorityOrder = { urgent: 4, high: 3, medium: 2, low: 1 };
            return priorityOrder[noteB.dataset.priority] - priorityOrder[noteA.dataset.priority];
          default:
            return 0;
        }
      });
      
      const grid = document.getElementById('notesGrid');
      notes.forEach(note => grid.appendChild(note));
      
      showNotesToast(`Notes sorted by ${document.getElementById('sortBy').selectedOptions[0].text}`, 'info');
    }

    // ========================================
    // KEYBOARD SHORTCUTS
    // ========================================
    
    document.addEventListener('keydown', function(e) {
      // Ctrl/Cmd + N: New note
      if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
        e.preventDefault();
        openAddNoteModal();
      }
      
      // Ctrl/Cmd + F: Focus search
      if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
        e.preventDefault();
        document.getElementById('searchInput').focus();
      }
      
      // Ctrl/Cmd + T: Templates
      if ((e.ctrlKey || e.metaKey) && e.key === 't') {
        e.preventDefault();
        openTemplatesModal();
      }
      
      // Ctrl/Cmd + E: Export
      if ((e.ctrlKey || e.metaKey) && e.key === 'e') {
        e.preventDefault();
        openExportModal();
      }
      
      
      // Escape: Close modals
      if (e.key === 'Escape') {
        const openModals = document.querySelectorAll('.fixed.inset-0:not(.hidden)');
        openModals.forEach(modal => {
          if (modal.id) {
            hideModal(modal.id);
          }
        });
      }
    });

    // ========================================
    // AUTO-SAVE FUNCTIONALITY
    // ========================================
    
    let autoSaveTimeout;
    function enableAutoSave() {
      const titleInput = document.getElementById('noteTitle');
      const contentEditor = tinymce.get('noteContent');
      
      if (titleInput) {
        titleInput.addEventListener('input', scheduleAutoSave);
      }
      
      if (contentEditor) {
        contentEditor.on('input', scheduleAutoSave);
      }
    }
    
    function scheduleAutoSave() {
      clearTimeout(autoSaveTimeout);
      autoSaveTimeout = setTimeout(autoSaveNote, 2000);
    }
    
    function autoSaveNote() {
      if (!currentNoteId) return;
      
      const formData = new FormData();
      formData.append('id', currentNoteId);
      formData.append('title', document.getElementById('noteTitle').value);
      formData.append('content', tinymce.get('noteContent').getContent());
      formData.append('csrf_token', document.querySelector('input[name="csrf_token"]').value);
      
      fetch('/notes/auto-save', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          console.log('Note auto-saved');
        }
      })
      .catch(error => {
        console.error('Auto-save error:', error);
      });
    }

    // ========================================
    // STATISTICS DASHBOARD
    // ========================================
    
    function showNoteStatistics() {
      const totalNotes = allNotes.length;
      const pinnedNotes = allNotes.filter(note => note.is_pinned).length;
      const archivedNotes = allNotes.filter(note => note.is_archived).length;
      const totalWords = allNotes.reduce((sum, note) => sum + (note.word_count || 0), 0);
      
      const statsModal = document.createElement('div');
      statsModal.className = 'fixed inset-0 bg-black bg-opacity-50 modal-backdrop flex items-center justify-center z-50';
      statsModal.innerHTML = `
        <div class="modal-content rounded-2xl shadow-2xl w-96 slide-in">
          <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
              <h3 class="text-xl font-bold text-gray-800">Note Statistics</h3>
              <button onclick="this.closest('.fixed').remove()" class="p-2 text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
              </button>
            </div>
          </div>
          
          <div class="p-6">
            <div class="grid grid-cols-2 gap-4">
              <div class="text-center">
                <div class="text-2xl font-bold text-blue-600">${totalNotes}</div>
                <div class="text-sm text-gray-600">Total Notes</div>
              </div>
              <div class="text-center">
                <div class="text-2xl font-bold text-yellow-600">${pinnedNotes}</div>
                <div class="text-sm text-gray-600">Pinned</div>
              </div>
              <div class="text-center">
                <div class="text-2xl font-bold text-gray-600">${archivedNotes}</div>
                <div class="text-sm text-gray-600">Archived</div>
              </div>
              <div class="text-center">
                <div class="text-2xl font-bold text-green-600">${totalWords.toLocaleString()}</div>
                <div class="text-sm text-gray-600">Total Words</div>
              </div>
            </div>
          </div>
        </div>
      `;
      
      document.body.appendChild(statsModal);
    }
    
    // Share functionality
    let currentShareNoteId = null;
    
    function openShareModal(noteId) {
      currentShareNoteId = noteId;
      document.getElementById('shareModal').classList.remove('hidden');
      document.getElementById('shareLink').value = '';
    }
    
    function closeShareModal() {
      document.getElementById('shareModal').classList.add('hidden');
      currentShareNoteId = null;
    }
    
    function generateShareLink() {
      if (!currentShareNoteId) return;
      
      const allowComments = document.getElementById('allowComments').checked;
      const allowEdit = document.getElementById('allowEdit').checked;
      const expireLink = document.getElementById('expireLink').checked;
      
      // Generate a share token (in a real app, this would be stored in the database)
      const shareToken = btoa(JSON.stringify({
        noteId: currentShareNoteId,
        allowComments,
        allowEdit,
        expires: expireLink ? Date.now() + (7 * 24 * 60 * 60 * 1000) : null
      }));
      
      const shareUrl = `${window.location.origin}/shared/${shareToken}`;
      document.getElementById('shareLink').value = shareUrl;
      
      showNotesToast('Share link generated!', 'success');
    }
    
    function copyShareLink() {
      const shareLink = document.getElementById('shareLink');
      if (!shareLink.value) {
        showNotesToast('Please generate a share link first', 'warning');
        return;
      }
      
      shareLink.select();
      document.execCommand('copy');
      showNotesToast('Share link copied to clipboard!', 'success');
    }
    
    function shareViaEmail() {
      const shareLink = document.getElementById('shareLink').value;
      if (!shareLink) {
        showNotesToast('Please generate a share link first', 'warning');
        return;
      }
      
      const subject = encodeURIComponent('Check out this note');
      const body = encodeURIComponent(`I wanted to share this note with you: ${shareLink}`);
      window.open(`mailto:?subject=${subject}&body=${body}`);
    }
    
    function shareViaTwitter() {
      const shareLink = document.getElementById('shareLink').value;
      if (!shareLink) {
        showNotesToast('Please generate a share link first', 'warning');
        return;
      }
      
      const text = encodeURIComponent('Check out this note I shared');
      window.open(`https://twitter.com/intent/tweet?text=${text}&url=${encodeURIComponent(shareLink)}`);
    }
    
    function shareViaLinkedIn() {
      const shareLink = document.getElementById('shareLink').value;
      if (!shareLink) {
        showNotesToast('Please generate a share link first', 'warning');
        return;
      }
      
      const title = encodeURIComponent('Shared Note');
      const summary = encodeURIComponent('Check out this note I shared');
      window.open(`https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(shareLink)}&title=${title}&summary=${summary}`);
    }
    
    // Drag and Drop functionality
    let draggedElement = null;
    
    function handleDragStart(e) {
      draggedElement = e.target;
      e.target.classList.add('dragging');
      e.dataTransfer.effectAllowed = 'move';
      e.dataTransfer.setData('text/html', e.target.outerHTML);
    }
    
    function handleDragEnd(e) {
      e.target.classList.remove('dragging');
      draggedElement = null;
    }
    
    function handleDragOver(e) {
      e.preventDefault();
      e.dataTransfer.dropEffect = 'move';
      
      // Add visual feedback
      const noteCard = e.target.closest('.note-card');
      if (noteCard && noteCard !== draggedElement) {
        noteCard.classList.add('drag-over');
      }
    }
    
    function handleDrop(e) {
      e.preventDefault();
      
      // Remove visual feedback
      document.querySelectorAll('.note-card').forEach(card => {
        card.classList.remove('drag-over');
      });
      
      if (draggedElement && e.target.closest('.note-card')) {
        const targetCard = e.target.closest('.note-card');
        const draggedId = draggedElement.dataset.noteId;
        const targetId = targetCard.dataset.noteId;
        
        if (draggedId !== targetId) {
          // Reorder notes (in a real app, this would update the database)
          const draggedNote = draggedElement.parentElement;
          const targetNote = targetCard.parentElement;
          
          if (draggedNote && targetNote) {
            const container = draggedNote.parentElement;
            const draggedIndex = Array.from(container.children).indexOf(draggedNote);
            const targetIndex = Array.from(container.children).indexOf(targetNote);
            
            if (draggedIndex < targetIndex) {
              container.insertBefore(draggedNote, targetNote.nextSibling);
            } else {
              container.insertBefore(draggedNote, targetNote);
            }
            
            showNotesToast('Note reordered successfully!', 'success');
          }
        }
      }
    }
    
    // Add drag and drop to the notes container
    document.addEventListener('DOMContentLoaded', function() {
      const notesContainer = document.getElementById('notesContainer');
      if (notesContainer) {
        notesContainer.addEventListener('dragover', function(e) {
          e.preventDefault();
          e.dataTransfer.dropEffect = 'move';
        });
        
        notesContainer.addEventListener('drop', function(e) {
          e.preventDefault();
          // Handle dropping in empty space if needed
        });
      }
    });
    
    // Help and Keyboard Shortcuts functionality
    function showKeyboardShortcuts() {
      const shortcutsModal = document.createElement('div');
      shortcutsModal.className = 'fixed inset-0 bg-black bg-opacity-50 modal-backdrop flex items-center justify-center z-50';
      shortcutsModal.innerHTML = `
        <div class="modal-content rounded-2xl shadow-2xl w-96 slide-in">
          <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
              <h3 class="text-xl font-bold text-gray-800">Keyboard Shortcuts</h3>
              <button onclick="this.closest('.modal-backdrop').remove()" class="p-2 text-gray-400 hover:text-gray-600 transition-colors">
                <i class="fas fa-times"></i>
              </button>
            </div>
          </div>
          
          <div class="p-6">
            <div class="space-y-4">
              <div class="flex justify-between items-center">
                <span class="text-gray-700">New Note</span>
                <kbd class="px-2 py-1 bg-gray-200 rounded text-sm">Ctrl/Cmd + N</kbd>
              </div>
              <div class="flex justify-between items-center">
                <span class="text-gray-700">Focus Search</span>
                <kbd class="px-2 py-1 bg-gray-200 rounded text-sm">Ctrl/Cmd + F</kbd>
              </div>
              <div class="flex justify-between items-center">
                <span class="text-gray-700">Templates</span>
                <kbd class="px-2 py-1 bg-gray-200 rounded text-sm">Ctrl/Cmd + T</kbd>
              </div>
              <div class="flex justify-between items-center">
                <span class="text-gray-700">Export</span>
                <kbd class="px-2 py-1 bg-gray-200 rounded text-sm">Ctrl/Cmd + E</kbd>
              </div>
              <div class="flex justify-between items-center">
                <span class="text-gray-700">Close Modal</span>
                <kbd class="px-2 py-1 bg-gray-200 rounded text-sm">Escape</kbd>
              </div>
            </div>
          </div>
        </div>
      `;
      
      document.body.appendChild(shortcutsModal);
    }
    
    function showHelp() {
      const helpModal = document.createElement('div');
      helpModal.className = 'fixed inset-0 bg-black bg-opacity-50 modal-backdrop flex items-center justify-center z-50';
      helpModal.innerHTML = `
        <div class="modal-content rounded-2xl shadow-2xl w-96 slide-in">
          <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
              <h3 class="text-xl font-bold text-gray-800">Help & Tips</h3>
              <button onclick="this.closest('.modal-backdrop').remove()" class="p-2 text-gray-400 hover:text-gray-600 transition-colors">
                <i class="fas fa-times"></i>
              </button>
            </div>
          </div>
          
          <div class="p-6">
            <div class="space-y-4">
              <div>
                <h4 class="font-semibold text-gray-800 mb-2">Getting Started</h4>
                <ul class="text-sm text-gray-600 space-y-1">
                  <li>• Click "Add Note" to create your first note</li>
                  <li>• Use templates for quick note creation</li>
                  <li>• Drag and drop notes to reorder them</li>
                  <li>• Use bulk actions for multiple notes</li>
                </ul>
              </div>
              
              <div>
                <h4 class="font-semibold text-gray-800 mb-2">Features</h4>
                <ul class="text-sm text-gray-600 space-y-1">
                  <li>• Rich text editing with TinyMCE</li>
                  <li>• Tag organization system</li>
                  <li>• Priority and color coding</li>
                  <li>• Export to multiple formats</li>
                </ul>
              </div>
              
              <div>
                <h4 class="font-semibold text-gray-800 mb-2">Tips</h4>
                <ul class="text-sm text-gray-600 space-y-1">
                  <li>• Use keyboard shortcuts for faster workflow</li>
                  <li>• Pin important notes for quick access</li>
                  <li>• Archive old notes to keep workspace clean</li>
                  <li>• Use search and filters to find notes quickly</li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      `;
      
      document.body.appendChild(helpModal);
    }
  </script>
</body>
</html>