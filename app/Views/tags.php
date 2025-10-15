<?php
use Core\CSRF;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tag Manager | SecureNote Pro</title>
  <script src="https://cdn.tailwindcss.com"></script>
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
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
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
    
    .tag-card {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(4px);
      border: 1px solid rgba(255, 255, 255, 0.3);
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      transform-style: preserve-3d;
    }
    
    .tag-card:hover {
      transform: translateY(-4px) rotateX(2deg);
      box-shadow: 0 20px 40px -12px rgba(0, 0, 0, 0.15);
    }
    
    .tag-badge {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 0.5rem 1rem;
      border-radius: 9999px;
      font-size: 0.875rem;
      font-weight: 500;
      display: inline-block;
      margin: 0.25rem;
      transition: all 0.2s ease;
      position: relative;
      overflow: hidden;
    }
    
    .tag-badge:hover {
      transform: scale(1.05);
      box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    }
    
    .tag-badge::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
      transition: left 0.5s;
    }
    
    .tag-badge:hover::before {
      left: 100%;
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
      position: relative;
    }
    
    .color-option:hover {
      transform: scale(1.1);
      border-color: #374151;
    }
    
    .color-option.selected {
      border-color: #1f2937;
      transform: scale(1.2);
    }
    
    .color-option.selected::after {
      content: '✓';
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      color: white;
      font-weight: bold;
      text-shadow: 0 0 2px rgba(0, 0, 0, 0.5);
    }
    
    .usage-bar {
      background: linear-gradient(90deg, #3b82f6 0%, #1d4ed8 100%);
      border-radius: 9999px;
      transition: width 0.3s ease;
      height: 0.5rem;
    }
    
    .modal-backdrop {
      backdrop-filter: blur(8px);
    }
    
    .modal-content {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(20px);
      border: 1px solid rgba(255, 255, 255, 0.3);
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
    
    .tag-cloud {
      display: flex;
      flex-wrap: wrap;
      gap: 0.5rem;
      align-items: center;
      justify-content: center;
      min-height: 200px;
    }
    
    .tag-cloud-item {
      transition: all 0.3s ease;
      cursor: pointer;
    }
    
    .tag-cloud-item:hover {
      transform: scale(1.1);
    }
    
    .stats-card {
      background: rgba(255, 255, 255, 0.9);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.3);
      transition: all 0.3s ease;
    }
    
    .stats-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }
    
    .bulk-actions {
      background: rgba(59, 130, 246, 0.1);
      border: 1px solid rgba(59, 130, 246, 0.2);
      transition: all 0.3s ease;
    }
    
    .bulk-actions.show {
      transform: translateY(0);
      opacity: 1;
    }
    
    .bulk-actions.hidden {
      transform: translateY(-100%);
      opacity: 0;
    }
  </style>
</head>
<body class="bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 min-h-screen">
  
  <!-- Tags Loader System -->
  <div id="tagsLoader" class="fixed inset-0 bg-black bg-opacity-30 z-50 hidden">
    <div class="flex items-center justify-center min-h-screen">
      <div class="text-center text-white">
        <div class="w-12 h-12 border-4 border-white border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
        <p id="tagsLoaderMessage">Loading...</p>
      </div>
    </div>
  </div>

  <!-- Tags Toast Container -->
  <div id="tagsToastContainer" class="fixed top-4 right-4 z-50 space-y-2"></div>

  <!-- Main Container -->

  <div class="flex h-screen">
    <!-- Sidebar -->
    <?php include __DIR__ . '/partials/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden">
      <!-- Header -->
      <?php 
        $page_title = "Tag Manager";
        include __DIR__ . '/partials/navbar.php'; 
      ?>

      <!-- Content -->
      <main class="flex-1 overflow-y-auto p-6">
        
        <!-- Header Section -->
        <div class="flex items-center justify-between mb-8">
          <div>
            <h1 class="text-4xl font-bold text-gray-800 mb-2">Tag Manager</h1>
            <p class="text-gray-600">Organize and manage your tags across all content</p>
          </div>
          <div class="flex items-center gap-3">
            <button onclick="openBulkActions()" class="px-4 py-2 text-gray-600 hover:text-gray-800 transition-colors">
              <i class="fas fa-tasks mr-2"></i>Bulk Actions
            </button>
            <button onclick="openAddTagModal()" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300">
              <i class="fas fa-plus mr-2"></i>Add Tag
            </button>
          </div>
        </div>

        <!-- Bulk Actions Bar -->
        <div id="bulkActionsBar" class="bulk-actions fixed top-20 left-0 right-0 z-40 p-4 hidden">
          <div class="max-w-7xl mx-auto">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-4">
                <span class="text-sm font-medium text-gray-700">
                  <span id="selectedCount">0</span> tags selected
                </span>
                <div class="flex items-center gap-2">
                  <button onclick="bulkDeleteTags()" class="px-3 py-1 bg-red-600 text-white text-sm rounded-lg hover:bg-red-700">
                    <i class="fas fa-trash mr-1"></i>Delete
                  </button>
                  <button onclick="bulkMergeTags()" class="px-3 py-1 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700">
                    <i class="fas fa-compress mr-1"></i>Merge
                  </button>
                  <button onclick="bulkChangeColor()" class="px-3 py-1 bg-purple-600 text-white text-sm rounded-lg hover:bg-purple-700">
                    <i class="fas fa-palette mr-1"></i>Change Color
                  </button>
                </div>
              </div>
              <button onclick="clearSelection()" class="text-gray-600 hover:text-gray-800">
                <i class="fas fa-times"></i>
              </button>
            </div>
          </div>
        </div>

        <!-- Stats Overview -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
          <div class="stats-card rounded-xl p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600">Total Tags</p>
                <p class="text-2xl font-bold text-gray-900" id="totalTags">0</p>
              </div>
              <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-tags text-blue-600 text-xl"></i>
              </div>
            </div>
          </div>
          
          <div class="stats-card rounded-xl p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600">Most Used</p>
                <p class="text-2xl font-bold text-gray-900" id="mostUsedTag">-</p>
              </div>
              <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-chart-line text-green-600 text-xl"></i>
              </div>
            </div>
          </div>
          
          <div class="stats-card rounded-xl p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600">Unused Tags</p>
                <p class="text-2xl font-bold text-gray-900" id="unusedTags">0</p>
              </div>
              <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-exclamation-triangle text-yellow-600 text-xl"></i>
              </div>
            </div>
          </div>
          
          <div class="stats-card rounded-xl p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600">Recent Tags</p>
                <p class="text-2xl font-bold text-gray-900" id="recentTags">0</p>
              </div>
              <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-clock text-purple-600 text-xl"></i>
              </div>
            </div>
          </div>
        </div>

        <!-- Search and Filters -->
        <div class="glassmorphism rounded-2xl p-6 mb-8">
          <div class="flex flex-col lg:flex-row gap-4">
            <!-- Search -->
            <div class="flex-1">
              <div class="relative">
                <input type="text" id="tagSearchInput" placeholder="Search tags..." 
                       class="w-full pl-12 pr-4 py-3 bg-white bg-opacity-50 border border-white border-opacity-30 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-all duration-300">
                <i class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
              </div>
            </div>
            
            <!-- Filters -->
            <div class="flex flex-wrap gap-3">
              <select id="sortTags" onchange="sortTags()" class="px-4 py-2 bg-white bg-opacity-50 border border-white border-opacity-30 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="name">Sort by Name</option>
                <option value="usage">Sort by Usage</option>
                <option value="created">Sort by Created</option>
                <option value="color">Sort by Color</option>
              </select>
              
              <select id="filterTags" onchange="filterTags()" class="px-4 py-2 bg-white bg-opacity-50 border border-white border-opacity-30 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="all">All Tags</option>
                <option value="used">Used Tags</option>
                <option value="unused">Unused Tags</option>
                <option value="recent">Recent Tags</option>
              </select>
              
              <button onclick="toggleTagCloud()" class="px-4 py-2 bg-white bg-opacity-50 border border-white border-opacity-30 rounded-xl text-sm font-medium hover:bg-opacity-70 transition-all duration-200">
                <i class="fas fa-cloud mr-2"></i>Tag Cloud
              </button>
            </div>
          </div>
        </div>

        <!-- Tag Cloud View -->
        <div id="tagCloudView" class="glassmorphism rounded-2xl p-6 mb-8 hidden">
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-semibold text-gray-800">Tag Cloud</h3>
            <button onclick="toggleTagCloud()" class="text-gray-600 hover:text-gray-800">
              <i class="fas fa-times"></i>
            </button>
          </div>
          <div id="tagCloud" class="tag-cloud">
            <!-- Tag cloud will be populated here -->
          </div>
        </div>

        <!-- Tags Grid -->
        <div id="tagsGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
          <!-- Tags will be populated here -->
        </div>

        <!-- No Tags State -->
        <div id="noTags" class="text-center py-12 hidden">
          <div class="floating-animation mb-6">
            <i class="fas fa-tags text-6xl text-gray-300"></i>
          </div>
          <h3 class="text-xl font-semibold text-gray-600 mb-2">No tags found</h3>
          <p class="text-gray-500 mb-6">Create your first tag to start organizing your content</p>
          <button onclick="openAddTagModal()" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300">
            <i class="fas fa-plus mr-2"></i>Create Your First Tag
          </button>
        </div>
      </main>
    </div>
  </div>

  <!-- Add/Edit Tag Modal -->
  <div id="tagModal" class="fixed inset-0 bg-black bg-opacity-50 modal-backdrop flex items-center justify-center hidden z-50">
    <div class="modal-content rounded-2xl shadow-2xl w-11/12 max-w-2xl slide-in">
      <div class="p-6 border-b border-gray-200">
        <div class="flex items-center justify-between">
          <h3 id="modalTitle" class="text-2xl font-bold text-gray-800">Add New Tag</h3>
          <button onclick="closeTagModal()" class="p-2 text-gray-400 hover:text-gray-600 transition-colors">
            <i class="fas fa-times text-xl"></i>
          </button>
        </div>
      </div>
      
      <div class="p-6">
        <form id="tagForm" method="POST">
          <input type="hidden" name="csrf_token" value="<?= \Core\CSRF::generate() ?>">
          <input type="hidden" id="tagId" name="id">
          
          <!-- Tag Name -->
          <div class="mb-6">
            <label for="tagName" class="block text-sm font-semibold text-gray-700 mb-2">Tag Name</label>
            <input type="text" id="tagName" name="name" required
                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-300"
                   placeholder="Enter tag name...">
          </div>
          
          <!-- Tag Description -->
          <div class="mb-6">
            <label for="tagDescription" class="block text-sm font-semibold text-gray-700 mb-2">Description (Optional)</label>
            <textarea id="tagDescription" name="description" rows="3"
                      class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-300"
                      placeholder="Enter tag description..."></textarea>
          </div>
          
          <!-- Tag Color -->
          <div class="mb-6">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Tag Color</label>
            <div class="color-picker">
              <div class="color-option selected" style="background-color: #3b82f6;" data-color="#3b82f6"></div>
              <div class="color-option" style="background-color: #10b981;" data-color="#10b981"></div>
              <div class="color-option" style="background-color: #f59e0b;" data-color="#f59e0b"></div>
              <div class="color-option" style="background-color: #ef4444;" data-color="#ef4444"></div>
              <div class="color-option" style="background-color: #8b5cf6;" data-color="#8b5cf6"></div>
              <div class="color-option" style="background-color: #06b6d4;" data-color="#06b6d4"></div>
              <div class="color-option" style="background-color: #84cc16;" data-color="#84cc16"></div>
              <div class="color-option" style="background-color: #f97316;" data-color="#f97316"></div>
              <div class="color-option" style="background-color: #ec4899;" data-color="#ec4899"></div>
              <div class="color-option" style="background-color: #6366f1;" data-color="#6366f1"></div>
              <div class="color-option" style="background-color: #14b8a6;" data-color="#14b8a6"></div>
              <div class="color-option" style="background-color: #eab308;" data-color="#eab308"></div>
              <div class="color-option" style="background-color: #dc2626;" data-color="#dc2626"></div>
              <div class="color-option" style="background-color: #7c3aed;" data-color="#7c3aed"></div>
              <div class="color-option" style="background-color: #0891b2;" data-color="#0891b2"></div>
              <div class="color-option" style="background-color: #65a30d;" data-color="#65a30d"></div>
              <div class="color-option" style="background-color: #ea580c;" data-color="#ea580c"></div>
              <div class="color-option" style="background-color: #db2777;" data-color="#db2777"></div>
              <div class="color-option" style="background-color: #4f46e5;" data-color="#4f46e5"></div>
              <div class="color-option" style="background-color: #0d9488;" data-color="#0d9488"></div>
              <div class="color-option" style="background-color: #ca8a04;" data-color="#ca8a04"></div>
              <div class="color-option" style="background-color: #b91c1c;" data-color="#b91c1c"></div>
              <div class="color-option" style="background-color: #6d28d9;" data-color="#6d28d9"></div>
              <div class="color-option" style="background-color: #0e7490;" data-color="#0e7490"></div>
              <div class="color-option" style="background-color: #4d7c0f;" data-color="#4d7c0f"></div>
              <div class="color-option" style="background-color: #c2410c;" data-color="#c2410c"></div>
              <div class="color-option" style="background-color: #be185d;" data-color="#be185d"></div>
              <div class="color-option" style="background-color: #3730a3;" data-color="#3730a3"></div>
              <div class="color-option" style="background-color: #0f766e;" data-color="#0f766e"></div>
              <div class="color-option" style="background-color: #a16207;" data-color="#a16207"></div>
              <div class="color-option" style="background-color: #991b1b;" data-color="#991b1b"></div>
              <div class="color-option" style="background-color: #581c87;" data-color="#581c87"></div>
              <div class="color-option" style="background-color: #155e75;" data-color="#155e75"></div>
              <div class="color-option" style="background-color: #365314;" data-color="#365314"></div>
              <div class="color-option" style="background-color: #9a3412;" data-color="#9a3412"></div>
              <div class="color-option" style="background-color: #9d174d;" data-color="#9d174d"></div>
              <div class="color-option" style="background-color: #312e81;" data-color="#312e81"></div>
              <div class="color-option" style="background-color: #134e4a;" data-color="#134e4a"></div>
              <div class="color-option" style="background-color: #713f12;" data-color="#713f12"></div>
              <div class="color-option" style="background-color: #7f1d1d;" data-color="#7f1d1d"></div>
              <div class="color-option" style="background-color: #4c1d95;" data-color="#4c1d95"></div>
              <div class="color-option" style="background-color: #0c4a6e;" data-color="#0c4a6e"></div>
              <div class="color-option" style="background-color: #1a2e05;" data-color="#1a2e05"></div>
              <div class="color-option" style="background-color: #431407;" data-color="#431407"></div>
              <div class="color-option" style="background-color: #500724;" data-color="#500724"></div>
              <div class="color-option" style="background-color: #1e1b4b;" data-color="#1e1b4b"></div>
              <div class="color-option" style="background-color: #042f2e;" data-color="#042f2e"></div>
              <div class="color-option" style="background-color: #365314;" data-color="#365314"></div>
              <div class="color-option" style="background-color: #292524;" data-color="#292524"></div>
            </div>
            <input type="hidden" id="tagColor" name="color" value="#3b82f6">
          </div>
        </form>
      </div>
      
      <div class="p-6 border-t border-gray-200 flex justify-end gap-3">
        <button onclick="closeTagModal()" class="px-6 py-2 text-gray-600 hover:text-gray-800 transition-colors">
          Cancel
        </button>
        <button onclick="saveTag()" class="px-6 py-2 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300">
          <span id="saveButtonText">Save Tag</span>
          <div id="saveSpinner" class="loading-spinner ml-2 hidden"></div>
        </button>
      </div>
    </div>
  </div>

  <!-- Bulk Merge Modal -->
  <div id="mergeModal" class="fixed inset-0 bg-black bg-opacity-50 modal-backdrop flex items-center justify-center hidden z-50">
    <div class="modal-content rounded-2xl shadow-2xl w-11/12 max-w-2xl slide-in">
      <div class="p-6 border-b border-gray-200">
        <div class="flex items-center justify-between">
          <h3 class="text-2xl font-bold text-gray-800">Merge Tags</h3>
          <button onclick="closeMergeModal()" class="p-2 text-gray-400 hover:text-gray-600">
            <i class="fas fa-times text-xl"></i>
          </button>
        </div>
      </div>
      
      <div class="p-6">
        <p class="text-gray-600 mb-4">Select a target tag to merge the selected tags into:</p>
        <div id="mergeTargets" class="space-y-2">
          <!-- Merge targets will be populated here -->
        </div>
      </div>
      
      <div class="p-6 border-t border-gray-200 flex justify-end gap-3">
        <button onclick="closeMergeModal()" class="px-6 py-2 text-gray-600 hover:text-gray-800">
          Cancel
        </button>
        <button onclick="confirmMerge()" class="px-6 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700">
          Merge Tags
        </button>
      </div>
    </div>
  </div>

  <script>
    // Tags Loader and Toast System
    function showTagsLoader(message = 'Loading...') {
      const loader = document.getElementById('tagsLoader');
      const messageEl = document.getElementById('tagsLoaderMessage');
      if (messageEl) messageEl.textContent = message;
      if (loader) loader.classList.remove('hidden');
    }

    function hideTagsLoader() {
      const loader = document.getElementById('tagsLoader');
      if (loader) loader.classList.add('hidden');
    }

    function showTagsToast(message, type = 'info') {
      const container = document.getElementById('tagsToastContainer');
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
    let allTags = <?= json_encode($tags ?? []) ?>;
    let filteredTags = [];
    let selectedTags = new Set();
    let currentTagId = null;
    let isTagCloudVisible = false;
    let currentSort = 'name';
    let currentFilter = 'all';
    
    // Ensure allTags is always an array
    if (!Array.isArray(allTags)) {
      allTags = [];
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
      loadTags();
      setupEventListeners();
    });

    function setupEventListeners() {
      // Search input
      document.getElementById('tagSearchInput').addEventListener('input', debounce(filterTags, 300));
      
      // Color picker
      document.querySelectorAll('.color-option').forEach(option => {
        option.addEventListener('click', function() {
          document.querySelectorAll('.color-option').forEach(opt => opt.classList.remove('selected'));
          this.classList.add('selected');
          document.getElementById('tagColor').value = this.dataset.color;
        });
      });
    }

    function loadTags() {
      showTagsLoader('Loading tags...');
      
      fetch('/tags/api/get-all', {
        method: 'GET',
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        }
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          allTags = data.data || [];
          filteredTags = [...allTags];
          updateStats();
          displayTags();
        }
      })
      .catch(error => {
        console.error('Error loading tags:', error);
        showTagsToast('Error loading tags', 'error');
      })
      .finally(() => {
        hideTagsLoader();
      });
    }

    function updateStats() {
      if (!Array.isArray(allTags)) {
        allTags = [];
      }
      
      document.getElementById('totalTags').textContent = allTags.length;
      
      const mostUsed = allTags.length > 0 ? allTags.reduce((max, tag) => (tag.usage_count || 0) > (max.usage_count || 0) ? tag : max, allTags[0]) : null;
      document.getElementById('mostUsedTag').textContent = mostUsed ? mostUsed.name : '-';
      
      const unused = allTags.filter(tag => (tag.usage_count || 0) === 0).length;
      document.getElementById('unusedTags').textContent = unused;
      
      const recent = allTags.filter(tag => {
        if (!tag.created_at) return false;
        const createdDate = new Date(tag.created_at);
        const weekAgo = new Date(Date.now() - 7 * 24 * 60 * 60 * 1000);
        return createdDate > weekAgo;
      }).length;
      document.getElementById('recentTags').textContent = recent;
    }

    function displayTags() {
      const tagsGrid = document.getElementById('tagsGrid');
      const noTags = document.getElementById('noTags');
      
      if (filteredTags.length === 0) {
        tagsGrid.classList.add('hidden');
        noTags.classList.remove('hidden');
        return;
      }
      
      tagsGrid.classList.remove('hidden');
      noTags.classList.add('hidden');
      
      tagsGrid.innerHTML = filteredTags.map(tag => createTagCard(tag)).join('');
    }

    function createTagCard(tag) {
      if (!Array.isArray(allTags)) {
        allTags = [];
      }
      const usagePercentage = allTags.length > 0 ? ((tag.usage_count || 0) / Math.max(...allTags.map(t => t.usage_count || 0))) * 100 : 0;
      
      return `
        <div class="tag-card rounded-xl p-6 slide-in" data-tag-id="${tag.id}">
          <div class="flex items-start justify-between mb-4">
            <div class="flex items-center gap-3">
              <input type="checkbox" class="tag-checkbox" data-tag-id="${tag.id}" onchange="toggleTagSelection(${tag.id})">
              <div class="w-4 h-4 rounded-full" style="background-color: ${tag.color}"></div>
            </div>
            <div class="relative">
              <button onclick="toggleTagMenu(this)" class="p-2 text-gray-400 hover:text-gray-600">
                <i class="fas fa-ellipsis-v"></i>
              </button>
              <div class="tag-menu hidden absolute right-0 top-full mt-2 w-48 bg-white rounded-xl shadow-xl border border-gray-200 py-2 z-10">
                <button onclick="openEditTagModal(${tag.id})" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center">
                  <i class="fas fa-edit mr-3 text-blue-500"></i>Edit
                </button>
                <button onclick="viewTagUsage(${tag.id})" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center">
                  <i class="fas fa-chart-bar mr-3 text-green-500"></i>View Usage
                </button>
                <button onclick="renameTag(${tag.id})" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center">
                  <i class="fas fa-tag mr-3 text-purple-500"></i>Rename
                </button>
                <hr class="my-2">
                <button onclick="deleteTag(${tag.id})" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 flex items-center">
                  <i class="fas fa-trash mr-3"></i>Delete
                </button>
              </div>
            </div>
          </div>
          
          <h3 class="text-lg font-semibold text-gray-800 mb-2">${tag.name}</h3>
          <p class="text-sm text-gray-600 mb-4 line-clamp-2">${tag.description || 'No description'}</p>
          
          <div class="mb-4">
            <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
              <span>Usage</span>
              <span>${tag.usage_count} items</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
              <div class="usage-bar rounded-full" style="width: ${usagePercentage}%"></div>
            </div>
          </div>
          
          <div class="flex items-center justify-between text-xs text-gray-500">
            <span>Created ${new Date(tag.created_at).toLocaleDateString()}</span>
            <span class="px-2 py-1 rounded-full bg-gray-100">${tag.type || 'general'}</span>
          </div>
        </div>
      `;
    }

    function filterTags() {
      if (!Array.isArray(allTags)) {
        allTags = [];
      }
      
      const searchQuery = document.getElementById('tagSearchInput').value.toLowerCase();
      const filter = document.getElementById('filterTags').value;
      
      filteredTags = allTags.filter(tag => {
        const matchesSearch = !searchQuery || tag.name.toLowerCase().includes(searchQuery) || 
                             (tag.description && tag.description.toLowerCase().includes(searchQuery));
        
        let matchesFilter = true;
        switch (filter) {
          case 'used':
            matchesFilter = tag.usage_count > 0;
            break;
          case 'unused':
            matchesFilter = tag.usage_count === 0;
            break;
          case 'recent':
            const createdDate = new Date(tag.created_at);
            const weekAgo = new Date(Date.now() - 7 * 24 * 60 * 60 * 1000);
            matchesFilter = createdDate > weekAgo;
            break;
        }
        
        return matchesSearch && matchesFilter;
      });
      
      sortTags();
    }

    function sortTags() {
      const sortBy = document.getElementById('sortTags').value;
      
      switch (sortBy) {
        case 'name':
          filteredTags.sort((a, b) => a.name.localeCompare(b.name));
          break;
        case 'usage':
          filteredTags.sort((a, b) => b.usage_count - a.usage_count);
          break;
        case 'created':
          filteredTags.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
          break;
        case 'color':
          filteredTags.sort((a, b) => a.color.localeCompare(b.color));
          break;
      }
      
      displayTags();
    }

    function toggleTagCloud() {
      const tagCloudView = document.getElementById('tagCloudView');
      isTagCloudVisible = !isTagCloudVisible;
      
      if (isTagCloudVisible) {
        tagCloudView.classList.remove('hidden');
        generateTagCloud();
      } else {
        tagCloudView.classList.add('hidden');
      }
    }

    function generateTagCloud() {
      if (!Array.isArray(allTags)) {
        allTags = [];
      }
      
      const tagCloud = document.getElementById('tagCloud');
      const maxUsage = allTags.length > 0 ? Math.max(...allTags.map(tag => tag.usage_count || 0)) : 1;
      
      tagCloud.innerHTML = allTags.map(tag => {
        const size = Math.max(12, ((tag.usage_count || 0) / maxUsage) * 24 + 12);
        return `
          <div class="tag-cloud-item" style="font-size: ${size}px;" onclick="filterByTag('${tag.name}')">
            <span class="tag-badge" style="background-color: ${tag.color};">
              ${tag.name}
            </span>
          </div>
        `;
      }).join('');
    }

    function filterByTag(tagName) {
      document.getElementById('tagSearchInput').value = tagName;
      filterTags();
      toggleTagCloud();
    }

    function toggleTagSelection(tagId) {
      if (selectedTags.has(tagId)) {
        selectedTags.delete(tagId);
      } else {
        selectedTags.add(tagId);
      }
      
      updateBulkActionsBar();
    }

    function updateBulkActionsBar() {
      const bulkActionsBar = document.getElementById('bulkActionsBar');
      const selectedCount = document.getElementById('selectedCount');
      
      selectedCount.textContent = selectedTags.size;
      
      if (selectedTags.size > 0) {
        bulkActionsBar.classList.remove('hidden');
        bulkActionsBar.classList.add('show');
      } else {
        bulkActionsBar.classList.add('hidden');
        bulkActionsBar.classList.remove('show');
      }
    }

    function clearSelection() {
      selectedTags.clear();
      document.querySelectorAll('.tag-checkbox').forEach(checkbox => {
        checkbox.checked = false;
      });
      updateBulkActionsBar();
    }

    function openBulkActions() {
      if (selectedTags.size === 0) {
        showToast('Please select tags first', 'error');
        return;
      }
      updateBulkActionsBar();
    }

    function bulkDeleteTags() {
      if (selectedTags.size === 0) return;
      
      if (confirm(`Are you sure you want to delete ${selectedTags.size} tags? This action cannot be undone.`)) {
        const tagIds = Array.from(selectedTags);
        
        fetch('/tags/bulk-delete', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: JSON.stringify({
            tag_ids: tagIds,
            csrf_token: document.querySelector('input[name="csrf_token"]').value
          })
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            showToast(`${tagIds.length} tags deleted successfully`, 'success');
            clearSelection();
            loadTags();
          } else {
            showToast(data.message, 'error');
          }
        })
        .catch(error => {
          console.error('Error:', error);
          showToast('An error occurred', 'error');
        });
      }
    }

    function bulkMergeTags() {
      if (selectedTags.size < 2) {
        showToast('Please select at least 2 tags to merge', 'error');
        return;
      }
      
      if (!Array.isArray(allTags)) {
        allTags = [];
      }
      const selectedTagObjects = allTags.filter(tag => selectedTags.has(tag.id));
      const mergeTargets = document.getElementById('mergeTargets');
      
      mergeTargets.innerHTML = selectedTagObjects.map(tag => `
        <label class="flex items-center p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
          <input type="radio" name="mergeTarget" value="${tag.id}" class="mr-3">
          <div class="flex items-center gap-3">
            <div class="w-4 h-4 rounded-full" style="background-color: ${tag.color}"></div>
            <span class="font-medium">${tag.name}</span>
            <span class="text-sm text-gray-500">(${tag.usage_count} items)</span>
          </div>
        </label>
      `).join('');
      
      showModal('mergeModal');
    }

    function confirmMerge() {
      const targetId = document.querySelector('input[name="mergeTarget"]:checked')?.value;
      
      if (!targetId) {
        showToast('Please select a target tag', 'error');
        return;
      }
      
      const sourceIds = Array.from(selectedTags).filter(id => id != targetId);
      
      fetch('/tags/bulk-merge', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
          source_ids: sourceIds,
          target_id: targetId,
          csrf_token: document.querySelector('input[name="csrf_token"]').value
        })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          showToast('Tags merged successfully', 'success');
          closeMergeModal();
          clearSelection();
          loadTags();
        } else {
          showToast(data.message, 'error');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred', 'error');
      });
    }

    function bulkChangeColor() {
      if (selectedTags.size === 0) return;
      
      const newColor = prompt('Enter new color (hex code):', '#3b82f6');
      if (!newColor) return;
      
      const tagIds = Array.from(selectedTags);
      
      fetch('/tags/bulk-change-color', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
          tag_ids: tagIds,
          color: newColor,
          csrf_token: document.querySelector('input[name="csrf_token"]').value
        })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          showToast('Tag colors updated successfully', 'success');
          clearSelection();
          loadTags();
        } else {
          showToast(data.message, 'error');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred', 'error');
      });
    }

    // Modal functions
    function openAddTagModal() {
      currentTagId = null;
      document.getElementById('modalTitle').textContent = 'Add New Tag';
      document.getElementById('tagForm').reset();
      document.getElementById('tagId').value = '';
      document.querySelector('.color-option').classList.add('selected');
      document.getElementById('tagColor').value = '#3b82f6';
      showModal('tagModal');
    }

    function openEditTagModal(tagId) {
      if (!Array.isArray(allTags)) {
        allTags = [];
      }
      const tag = allTags.find(t => t.id == tagId);
      if (!tag) return;
      
      currentTagId = tagId;
      document.getElementById('modalTitle').textContent = 'Edit Tag';
      document.getElementById('tagId').value = tagId;
      document.getElementById('tagName').value = tag.name;
      document.getElementById('tagDescription').value = tag.description || '';
      document.getElementById('tagColor').value = tag.color;
      
      // Select the correct color
      document.querySelectorAll('.color-option').forEach(opt => opt.classList.remove('selected'));
      const colorOption = document.querySelector(`[data-color="${tag.color}"]`);
      if (colorOption) {
        colorOption.classList.add('selected');
      }
      
      showModal('tagModal');
    }

    function closeTagModal() {
      hideModal('tagModal');
    }

    function closeMergeModal() {
      hideModal('mergeModal');
    }

    function saveTag() {
      const form = document.getElementById('tagForm');
      const formData = new FormData(form);
      
      const saveButton = document.querySelector('#saveButtonText');
      const saveSpinner = document.querySelector('#saveSpinner');
      
      saveButton.textContent = 'Saving...';
      saveSpinner.classList.remove('hidden');
      
      const action = currentTagId ? '/tags/update' : '/tags/store';
      
      fetch(action, {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          showToast(data.message, 'success');
          closeTagModal();
          loadTags();
        } else {
          showToast(data.message, 'error');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred while saving the tag', 'error');
      })
      .finally(() => {
        saveButton.textContent = 'Save Tag';
        saveSpinner.classList.add('hidden');
      });
    }

    function deleteTag(tagId) {
      if (confirm('Are you sure you want to delete this tag? This action cannot be undone.')) {
        fetch('/tags/delete', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: JSON.stringify({
            tag_id: tagId,
            csrf_token: document.querySelector('input[name="csrf_token"]').value
          })
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            showToast('Tag deleted successfully', 'success');
            loadTags();
          } else {
            showToast(data.message, 'error');
          }
        })
        .catch(error => {
          console.error('Error:', error);
          showToast('An error occurred', 'error');
        });
      }
    }

    function viewTagUsage(tagId) {
      // Navigate to tag usage page or show modal
      window.location.href = `/tags/usage/${tagId}`;
    }

    function renameTag(tagId) {
      if (!Array.isArray(allTags)) {
        allTags = [];
      }
      const tag = allTags.find(t => t.id == tagId);
      if (!tag) return;
      
      const newName = prompt('Enter new tag name:', tag.name);
      if (!newName || newName.trim() === '') return;
      
      fetch('/tags/rename', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
          tag_id: tagId,
          new_name: newName.trim(),
          csrf_token: document.querySelector('input[name="csrf_token"]').value
        })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          showToast('Tag renamed successfully', 'success');
          loadTags();
        } else {
          showToast(data.message, 'error');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred', 'error');
      });
    }

    function toggleTagMenu(button) {
      const menu = button.nextElementSibling;
      const isOpen = !menu.classList.contains('hidden');
      
      // Close all other menus
      document.querySelectorAll('.tag-menu').forEach(m => m.classList.add('hidden'));
      
      if (!isOpen) {
        menu.classList.remove('hidden');
      }
    }

    // Utility functions
    function showModal(modalId) {
      const modal = document.getElementById(modalId);
      modal.classList.remove('hidden');
      modal.classList.add('flex');
    }

    function hideModal(modalId) {
      const modal = document.getElementById(modalId);
      modal.classList.add('hidden');
      modal.classList.remove('flex');
    }

    // Toast function now uses the global system
    function showToast(message, type = 'info') {
      showTagsToast(message, type);
    }

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

    // Close menus when clicking outside
    document.addEventListener('click', function(e) {
      if (!e.target.closest('.tag-menu') && !e.target.closest('button[onclick*="toggleTagMenu"]')) {
        document.querySelectorAll('.tag-menu').forEach(menu => menu.classList.add('hidden'));
      }
    });
  </script>
</body>
</html>
