<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Global Search | SecureNote Pro</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
    
    * {
      font-family: 'Inter', sans-serif;
    }
    
    .glassmorphism {
      background: rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(20px);
      border: 1px solid rgba(255, 255, 255, 0.2);
    }
    
    .search-result-card {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.3);
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      transform-style: preserve-3d;
    }
    
    .search-result-card:hover {
      transform: translateY(-4px) rotateX(2deg);
      box-shadow: 0 20px 40px -12px rgba(0, 0, 0, 0.15);
    }
    
    .search-highlight {
      background: rgba(255, 235, 59, 0.3);
      padding: 0.125rem 0.25rem;
      border-radius: 0.25rem;
      font-weight: 500;
    }
    
    .filter-chip {
      background: rgba(59, 130, 246, 0.1);
      border: 1px solid rgba(59, 130, 246, 0.2);
      transition: all 0.2s ease;
    }
    
    .filter-chip:hover {
      background: rgba(59, 130, 246, 0.2);
      transform: scale(1.05);
    }
    
    .filter-chip.active {
      background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
      color: white;
      border-color: #1d4ed8;
    }
    
    .search-input {
      background: rgba(255, 255, 255, 0.9);
      backdrop-filter: blur(10px);
      border: 2px solid rgba(59, 130, 246, 0.2);
      transition: all 0.3s ease;
    }
    
    .search-input:focus {
      border-color: #3b82f6;
      box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
      background: rgba(255, 255, 255, 0.95);
    }
    
    .pulse-glow {
      animation: pulse-glow 2s ease-in-out infinite alternate;
    }
    
    @keyframes pulse-glow {
      from { box-shadow: 0 0 20px rgba(59, 130, 246, 0.4); }
      to { box-shadow: 0 0 30px rgba(59, 130, 246, 0.8); }
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
    
    .result-type-badge {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 0.25rem 0.75rem;
      border-radius: 9999px;
      font-size: 0.75rem;
      font-weight: 500;
    }
    
    .result-type-note {
      background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    }
    
    .result-type-task {
      background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    }
    
    .result-type-tag {
      background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    }
    
    .result-type-attachment {
      background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
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
    
    .search-suggestion {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.3);
      transition: all 0.2s ease;
    }
    
    .search-suggestion:hover {
      background: rgba(59, 130, 246, 0.1);
      transform: translateX(4px);
    }
    
    .recent-search-item {
      background: rgba(255, 255, 255, 0.7);
      backdrop-filter: blur(5px);
      border: 1px solid rgba(255, 255, 255, 0.2);
      transition: all 0.2s ease;
    }
    
    .recent-search-item:hover {
      background: rgba(59, 130, 246, 0.1);
      transform: translateX(2px);
    }
  </style>
</head>
<body class="bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 min-h-screen">
  
  <!-- Toast Notifications -->
  <div id="toast-container"></div>
  
  <!-- CSRF Token -->
  <input type="hidden" name="csrf_token" value="<?= \Core\CSRF::generate() ?>">

  <!-- Main Container -->
  <div class="flex h-screen">
    <!-- Sidebar -->
    <?php include __DIR__ . '/partials/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden">
      <!-- Header -->
      <?php 
        $page_title = "Global Search";
        include __DIR__ . '/partials/navbar.php'; 
      ?>

      <!-- Content -->
      <main class="flex-1 overflow-y-auto p-6">
        
        <!-- Header Section -->
        <div class="flex items-center justify-between mb-8">
          <div>
            <h1 class="text-4xl font-bold text-gray-800 mb-2">Global Search</h1>
            <p class="text-gray-600">Search across all your notes, tasks, and content</p>
          </div>
          <div class="flex items-center gap-3">
            <button onclick="clearSearchHistory()" class="px-4 py-2 text-gray-600 hover:text-gray-800 transition-colors">
              <i class="fas fa-history mr-2"></i>Clear History
            </button>
            <button onclick="exportSearchResults()" class="px-4 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors">
              <i class="fas fa-download mr-2"></i>Export Results
            </button>
          </div>
        </div>

        <!-- Search Section -->
        <div class="glassmorphism rounded-2xl p-6 mb-8">
          <div class="relative">
            <div class="flex items-center">
              <div class="relative flex-1">
                <input type="text" id="globalSearchInput" placeholder="Search everything..." 
                       class="search-input w-full pl-12 pr-16 py-4 rounded-xl text-lg focus:outline-none"
                       autocomplete="off">
                <i class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400 text-lg"></i>
                <div id="searchSpinner" class="loading-spinner absolute right-12 top-1/2 transform -translate-y-1/2 hidden"></div>
                <button onclick="clearSearch()" id="clearSearchBtn" class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 hidden">
                  <i class="fas fa-times text-lg"></i>
                </button>
              </div>
              <button onclick="performGlobalSearch()" class="ml-4 px-6 py-4 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 pulse-glow">
                <i class="fas fa-search mr-2"></i>Search
              </button>
            </div>
            
            <!-- Search Suggestions Dropdown -->
            <div id="searchSuggestions" class="absolute top-full left-0 right-0 mt-2 bg-white rounded-xl shadow-xl border border-gray-200 hidden z-50 max-h-80 overflow-y-auto">
              <!-- Suggestions will be populated here -->
            </div>
          </div>
          
          <!-- Recent Searches -->
          <div id="recentSearches" class="mt-6">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Recent Searches</h3>
            <div id="recentSearchesList" class="flex flex-wrap gap-2">
              <!-- Recent searches will be populated here -->
            </div>
          </div>
        </div>

        <!-- Filters Section -->
        <div class="glassmorphism rounded-2xl p-6 mb-8">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Filters</h3>
            <button onclick="clearAllFilters()" class="text-sm text-gray-600 hover:text-gray-800">
              Clear All
            </button>
          </div>
          
          <div class="flex flex-wrap gap-3">
            <!-- Content Type Filters -->
            <div class="flex bg-white bg-opacity-50 rounded-xl p-1">
              <button data-filter="type" data-value="all" onclick="toggleFilter(this)" class="filter-chip active px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200">
                <i class="fas fa-globe mr-2"></i>All
              </button>
              <button data-filter="type" data-value="notes" onclick="toggleFilter(this)" class="filter-chip px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200">
                <i class="fas fa-sticky-note mr-2"></i>Notes
              </button>
              <button data-filter="type" data-value="tasks" onclick="toggleFilter(this)" class="filter-chip px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200">
                <i class="fas fa-tasks mr-2"></i>Tasks
              </button>
              <button data-filter="type" data-value="tags" onclick="toggleFilter(this)" class="filter-chip px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200">
                <i class="fas fa-tags mr-2"></i>Tags
              </button>
              <button data-filter="type" data-value="attachments" onclick="toggleFilter(this)" class="filter-chip px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200">
                <i class="fas fa-paperclip mr-2"></i>Files
              </button>
            </div>
            
            <!-- Date Range Filter -->
            <div class="flex items-center gap-2 bg-white bg-opacity-50 rounded-xl px-4 py-2">
              <i class="fas fa-calendar text-gray-500"></i>
              <select id="dateRangeFilter" onchange="applyFilters()" class="bg-transparent border-0 text-sm font-medium focus:outline-none">
                <option value="all">All Time</option>
                <option value="today">Today</option>
                <option value="week">This Week</option>
                <option value="month">This Month</option>
                <option value="year">This Year</option>
                <option value="custom">Custom Range</option>
              </select>
            </div>
            
            <!-- Priority Filter -->
            <div class="flex items-center gap-2 bg-white bg-opacity-50 rounded-xl px-4 py-2">
              <i class="fas fa-flag text-gray-500"></i>
              <select id="priorityFilter" onchange="applyFilters()" class="bg-transparent border-0 text-sm font-medium focus:outline-none">
                <option value="all">All Priorities</option>
                <option value="urgent">Urgent</option>
                <option value="high">High</option>
                <option value="medium">Medium</option>
                <option value="low">Low</option>
              </select>
            </div>
          </div>
          
          <!-- Custom Date Range (Hidden by default) -->
          <div id="customDateRange" class="mt-4 hidden">
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">From</label>
                <input type="date" id="dateFrom" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">To</label>
                <input type="date" id="dateTo" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
              </div>
            </div>
          </div>
        </div>

        <!-- Search Results -->
        <div id="searchResults" class="hidden">
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-semibold text-gray-800">
              Search Results
              <span id="resultsCount" class="text-sm font-normal text-gray-600 ml-2"></span>
            </h3>
            <div class="flex items-center gap-3">
              <select id="sortResults" onchange="sortSearchResults()" class="px-4 py-2 bg-white bg-opacity-50 border border-white border-opacity-30 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="relevance">Relevance</option>
                <option value="date_newest">Date (Newest)</option>
                <option value="date_oldest">Date (Oldest)</option>
                <option value="title_asc">Title (A-Z)</option>
                <option value="title_desc">Title (Z-A)</option>
              </select>
              <button onclick="toggleViewMode()" id="viewModeBtn" class="px-4 py-2 bg-white bg-opacity-50 border border-white border-opacity-30 rounded-xl text-sm font-medium hover:bg-opacity-70 transition-all duration-200">
                <i class="fas fa-th-large mr-2"></i>Grid
              </button>
            </div>
          </div>
          
          <div id="resultsContainer" class="space-y-4">
            <!-- Search results will be populated here -->
          </div>
          
          <!-- Load More Button -->
          <div id="loadMoreContainer" class="text-center mt-8 hidden">
            <button onclick="loadMoreResults()" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300">
              <i class="fas fa-plus mr-2"></i>Load More Results
            </button>
          </div>
        </div>

        <!-- No Results State -->
        <div id="noResults" class="text-center py-12 hidden">
          <div class="floating-animation mb-6">
            <i class="fas fa-search text-6xl text-gray-300"></i>
          </div>
          <h3 class="text-xl font-semibold text-gray-600 mb-2">No results found</h3>
          <p class="text-gray-500 mb-6">Try adjusting your search terms or filters</p>
          <button onclick="clearAllFilters()" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300">
            <i class="fas fa-refresh mr-2"></i>Clear Filters
          </button>
        </div>

        <!-- Search Tips -->
        <div id="searchTips" class="glassmorphism rounded-2xl p-6">
          <h3 class="text-lg font-semibold text-gray-800 mb-4">Search Tips</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-2">
              <h4 class="font-medium text-gray-700">Search Operators</h4>
              <ul class="text-sm text-gray-600 space-y-1">
                <li><code class="bg-gray-100 px-2 py-1 rounded">"exact phrase"</code> - Search for exact phrases</li>
                <li><code class="bg-gray-100 px-2 py-1 rounded">tag:work</code> - Search by specific tags</li>
                <li><code class="bg-gray-100 px-2 py-1 rounded">priority:high</code> - Filter by priority</li>
                <li><code class="bg-gray-100 px-2 py-1 rounded">type:note</code> - Search specific content types</li>
              </ul>
            </div>
            <div class="space-y-2">
              <h4 class="font-medium text-gray-700">Quick Actions</h4>
              <ul class="text-sm text-gray-600 space-y-1">
                <li><i class="fas fa-keyboard text-gray-400 mr-2"></i>Press <kbd class="bg-gray-100 px-2 py-1 rounded">Enter</kbd> to search</li>
                <li><i class="fas fa-keyboard text-gray-400 mr-2"></i>Press <kbd class="bg-gray-100 px-2 py-1 rounded">Esc</kbd> to clear</li>
                <li><i class="fas fa-mouse text-gray-400 mr-2"></i>Click result to open</li>
                <li><i class="fas fa-filter text-gray-400 mr-2"></i>Use filters to narrow results</li>
              </ul>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>

  <script>
    // Global variables
    let currentSearchQuery = '';
    let currentFilters = {
      type: 'all',
      dateRange: 'all',
      priority: 'all',
      customDateFrom: '',
      customDateTo: ''
    };
    let searchResults = [];
    let currentPage = 1;
    let hasMoreResults = false;
    let isGridView = true;
    let searchTimeout;
    let recentSearches = JSON.parse(localStorage.getItem('recentSearches') || '[]');

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
      initializeSearch();
      loadRecentSearches();
      setupEventListeners();
    });

    function initializeSearch() {
      const searchInput = document.getElementById('globalSearchInput');
      
      // Focus search input
      searchInput.focus();
      
      // Load search suggestions
      loadSearchSuggestions();
    }

    function setupEventListeners() {
      const searchInput = document.getElementById('globalSearchInput');
      
      // Search input events
      searchInput.addEventListener('input', handleSearchInput);
      searchInput.addEventListener('keydown', handleSearchKeydown);
      searchInput.addEventListener('focus', showSearchSuggestions);
      searchInput.addEventListener('blur', hideSearchSuggestions);
      
      // Custom date range toggle
      document.getElementById('dateRangeFilter').addEventListener('change', function() {
        const customRange = document.getElementById('customDateRange');
        if (this.value === 'custom') {
          customRange.classList.remove('hidden');
        } else {
          customRange.classList.add('hidden');
        }
        applyFilters();
      });
      
      // Custom date inputs
      document.getElementById('dateFrom').addEventListener('change', applyFilters);
      document.getElementById('dateTo').addEventListener('change', applyFilters);
    }

    function handleSearchInput(event) {
      const query = event.target.value.trim();
      
      // Clear previous timeout
      clearTimeout(searchTimeout);
      
      if (query.length > 0) {
        // Show clear button
        document.getElementById('clearSearchBtn').classList.remove('hidden');
        
        // Debounce search suggestions
        searchTimeout = setTimeout(() => {
          loadSearchSuggestions(query);
        }, 300);
      } else {
        // Hide clear button and suggestions
        document.getElementById('clearSearchBtn').classList.add('hidden');
        hideSearchSuggestions();
      }
    }

    function handleSearchKeydown(event) {
      if (event.key === 'Enter') {
        event.preventDefault();
        performGlobalSearch();
      } else if (event.key === 'Escape') {
        clearSearch();
      }
    }

    function performGlobalSearch() {
      const query = document.getElementById('globalSearchInput').value.trim();
      
      if (!query) {
        showToast('Please enter a search term', 'error');
        return;
      }
      
      // Add to recent searches
      addToRecentSearches(query);
      
      // Show loading spinner
      document.getElementById('searchSpinner').classList.remove('hidden');
      
      // Hide suggestions
      hideSearchSuggestions();
      
      // Perform search
      currentSearchQuery = query;
      currentPage = 1;
      searchResults = [];
      
      fetch('/search/api', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
          query: query,
          filters: currentFilters,
          page: currentPage,
          csrf_token: document.querySelector('input[name="csrf_token"]')?.value || ''
        })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          searchResults = data.results;
          hasMoreResults = data.has_more;
          displaySearchResults();
          showToast(`Found ${data.total} results`, 'success');
        } else {
          showToast(data.message, 'error');
        }
      })
      .catch(error => {
        console.error('Search error:', error);
        showToast('An error occurred during search', 'error');
      })
      .finally(() => {
        document.getElementById('searchSpinner').classList.add('hidden');
      });
    }

        function displaySearchResults() {
            const resultsContainer = document.getElementById('resultsContainer');
            const searchResultsDiv = document.getElementById('searchResults');
            const noResultsDiv = document.getElementById('noResults');
            const resultsCount = document.getElementById('resultsCount');
            const loadMoreContainer = document.getElementById('loadMoreContainer');
            const searchTips = document.getElementById('searchTips');
            
            if (searchResults.length === 0) {
                searchResultsDiv.classList.add('hidden');
                noResultsDiv.classList.remove('hidden');
                if (searchTips) searchTips.classList.remove('hidden');
                return;
            }
            
            searchResultsDiv.classList.remove('hidden');
            noResultsDiv.classList.add('hidden');
            if (searchTips) searchTips.classList.add('hidden');
            
            // Ensure the results container is visible
            resultsContainer.style.display = 'block';
            resultsContainer.style.visibility = 'visible';
            
            resultsCount.textContent = `(${searchResults.length} results)`;
            
            if (isGridView) {
                displayGridView();
            } else {
                displayListView();
            }
            
            // Show/hide load more button
            if (hasMoreResults) {
                loadMoreContainer.classList.remove('hidden');
            } else {
                loadMoreContainer.classList.add('hidden');
            }
            
            // Scroll to results if they're not visible
            setTimeout(() => {
                searchResultsDiv.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 100);
        }

    function displayGridView() {
      const resultsContainer = document.getElementById('resultsContainer');
      resultsContainer.className = 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6';
      
      resultsContainer.innerHTML = searchResults.map(result => createResultCard(result)).join('');
    }

    function displayListView() {
      const resultsContainer = document.getElementById('resultsContainer');
      resultsContainer.className = 'space-y-4';
      
      resultsContainer.innerHTML = searchResults.map(result => createResultListItem(result)).join('');
    }

    function createResultCard(result) {
      const highlightedTitle = highlightSearchTerms(result.title, currentSearchQuery);
      const highlightedContent = highlightSearchTerms(result.content.substring(0, 150), currentSearchQuery);
      const typeIcon = getTypeIcon(result.type);
      const typeColor = getTypeColor(result.type);
      
      return `
        <div class="search-result-card rounded-xl p-6 cursor-pointer slide-in" onclick="openResult('${result.type}', ${result.id})">
          <div class="flex items-start justify-between mb-4">
            <div class="flex items-center gap-3">
              <div class="result-type-badge result-type-${result.type}">
                <i class="${typeIcon} mr-2"></i>${result.type.charAt(0).toUpperCase() + result.type.slice(1)}
              </div>
              ${result.priority ? `<span class="text-xs px-2 py-1 rounded-full bg-gray-100 text-gray-600">${result.priority}</span>` : ''}
            </div>
            <div class="text-xs text-gray-500">
              ${new Date(result.updated_at).toLocaleDateString()}
            </div>
          </div>
          
          <h3 class="text-lg font-semibold text-gray-800 mb-3 line-clamp-2">
            ${highlightedTitle}
          </h3>
          
          <p class="text-sm text-gray-600 mb-4 line-clamp-3">
            ${highlightedContent}...
          </p>
          
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
              ${result.tags && result.tags.length > 0 ? result.tags.map(tag => 
                `<span class="text-xs px-2 py-1 rounded-full bg-blue-100 text-blue-600">${tag}</span>`
              ).join('') : ''}
            </div>
            <div class="text-xs text-gray-400">
              <i class="fas fa-eye mr-1"></i>View
            </div>
          </div>
        </div>
      `;
    }

    function createResultListItem(result) {
      const highlightedTitle = highlightSearchTerms(result.title, currentSearchQuery);
      const highlightedContent = highlightSearchTerms(result.content.substring(0, 200), currentSearchQuery);
      const typeIcon = getTypeIcon(result.type);
      
      return `
        <div class="search-result-card rounded-xl p-4 cursor-pointer slide-in" onclick="openResult('${result.type}', ${result.id})">
          <div class="flex items-start gap-4">
            <div class="flex-shrink-0">
              <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white">
                <i class="${typeIcon}"></i>
              </div>
            </div>
            
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-3 mb-2">
                <h3 class="text-lg font-semibold text-gray-800 truncate">
                  ${highlightedTitle}
                </h3>
                <span class="result-type-badge result-type-${result.type} text-xs">
                  ${result.type.charAt(0).toUpperCase() + result.type.slice(1)}
                </span>
                ${result.priority ? `<span class="text-xs px-2 py-1 rounded-full bg-gray-100 text-gray-600">${result.priority}</span>` : ''}
              </div>
              
              <p class="text-sm text-gray-600 mb-3 line-clamp-2">
                ${highlightedContent}...
              </p>
              
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                  ${result.tags && result.tags.length > 0 ? result.tags.map(tag => 
                    `<span class="text-xs px-2 py-1 rounded-full bg-blue-100 text-blue-600">${tag}</span>`
                  ).join('') : ''}
                </div>
                <div class="text-xs text-gray-500">
                  ${new Date(result.updated_at).toLocaleDateString()}
                </div>
              </div>
            </div>
          </div>
        </div>
      `;
    }

    function highlightSearchTerms(text, query) {
      if (!query) return text;
      
      const terms = query.toLowerCase().split(' ').filter(term => term.length > 0);
      let highlightedText = text;
      
      terms.forEach(term => {
        const regex = new RegExp(`(${term})`, 'gi');
        highlightedText = highlightedText.replace(regex, '<span class="search-highlight">$1</span>');
      });
      
      return highlightedText;
    }

    function getTypeIcon(type) {
      const icons = {
        note: 'fas fa-sticky-note',
        task: 'fas fa-tasks',
        tag: 'fas fa-tags',
        attachment: 'fas fa-paperclip'
      };
      return icons[type] || 'fas fa-file';
    }

    function getTypeColor(type) {
      const colors = {
        note: 'from-green-500 to-green-600',
        task: 'from-blue-500 to-blue-600',
        tag: 'from-yellow-500 to-yellow-600',
        attachment: 'from-purple-500 to-purple-600'
      };
      return colors[type] || 'from-gray-500 to-gray-600';
    }

    function openResult(type, id) {
      // Find the result data
      const result = searchResults.find(r => r.id == id && r.type === type);
      if (!result) {
        showToast('Result not found', 'error');
        return;
      }
      
      // For now, redirect to the appropriate page
      // TODO: Implement modal view like in search_enhanced.php
      switch (type) {
        case 'note':
          window.location.href = `/notes`;
          break;
        case 'task':
          window.location.href = `/tasks`;
          break;
        case 'tag':
          window.location.href = `/tags`;
          break;
        case 'attachment':
          window.location.href = `/attachments/download/${id}`;
          break;
        default:
          showToast('Unknown result type', 'error');
      }
    }

    function toggleFilter(button) {
      const filterType = button.dataset.filter;
      const filterValue = button.dataset.value;
      
      // Remove active class from all buttons of same type
      document.querySelectorAll(`[data-filter="${filterType}"]`).forEach(btn => {
        btn.classList.remove('active');
      });
      
      // Add active class to clicked button
      button.classList.add('active');
      
      // Update current filters
      currentFilters[filterType] = filterValue;
      
      // Apply filters
      applyFilters();
    }

    function applyFilters() {
      currentFilters.dateRange = document.getElementById('dateRangeFilter').value;
      currentFilters.priority = document.getElementById('priorityFilter').value;
      currentFilters.customDateFrom = document.getElementById('dateFrom').value;
      currentFilters.customDateTo = document.getElementById('dateTo').value;
      
      // If we have a current search, re-run it with new filters
      if (currentSearchQuery) {
        performGlobalSearch();
      }
    }

    function clearAllFilters() {
      // Reset filter buttons
      document.querySelectorAll('.filter-chip').forEach(btn => {
        btn.classList.remove('active');
      });
      document.querySelector('[data-filter="type"][data-value="all"]').classList.add('active');
      
      // Reset filter selects
      document.getElementById('dateRangeFilter').value = 'all';
      document.getElementById('priorityFilter').value = 'all';
      document.getElementById('customDateRange').classList.add('hidden');
      
      // Reset current filters
      currentFilters = {
        type: 'all',
        dateRange: 'all',
        priority: 'all',
        customDateFrom: '',
        customDateTo: ''
      };
      
      // Re-run search if we have a query
      if (currentSearchQuery) {
        performGlobalSearch();
      }
    }

    function sortSearchResults() {
      const sortBy = document.getElementById('sortResults').value;
      
      switch (sortBy) {
        case 'relevance':
          // Keep original order (already sorted by relevance from server)
          break;
        case 'date_newest':
          searchResults.sort((a, b) => new Date(b.updated_at) - new Date(a.updated_at));
          break;
        case 'date_oldest':
          searchResults.sort((a, b) => new Date(a.updated_at) - new Date(b.updated_at));
          break;
        case 'title_asc':
          searchResults.sort((a, b) => a.title.localeCompare(b.title));
          break;
        case 'title_desc':
          searchResults.sort((a, b) => b.title.localeCompare(a.title));
          break;
      }
      
      displaySearchResults();
    }

    function toggleViewMode() {
      isGridView = !isGridView;
      const viewModeBtn = document.getElementById('viewModeBtn');
      
      if (isGridView) {
        viewModeBtn.innerHTML = '<i class="fas fa-th-large mr-2"></i>Grid';
      } else {
        viewModeBtn.innerHTML = '<i class="fas fa-list mr-2"></i>List';
      }
      
      displaySearchResults();
    }

    function loadMoreResults() {
      if (!hasMoreResults) return;
      
      currentPage++;
      
      fetch('/search/api', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
          query: currentSearchQuery,
          filters: currentFilters,
          page: currentPage,
          csrf_token: document.querySelector('input[name="csrf_token"]')?.value || ''
        })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          searchResults = [...searchResults, ...data.results];
          hasMoreResults = data.has_more;
          displaySearchResults();
        } else {
          showToast(data.message, 'error');
        }
      })
      .catch(error => {
        console.error('Load more error:', error);
        showToast('An error occurred loading more results', 'error');
      });
    }

    function clearSearch() {
      document.getElementById('globalSearchInput').value = '';
      document.getElementById('clearSearchBtn').classList.add('hidden');
      document.getElementById('searchResults').classList.add('hidden');
      document.getElementById('noResults').classList.add('hidden');
      const searchTips = document.getElementById('searchTips');
      if (searchTips) searchTips.classList.remove('hidden');
      hideSearchSuggestions();
      currentSearchQuery = '';
      searchResults = [];
    }

    function loadSearchSuggestions(query = '') {
      if (query.length < 2) {
        displaySearchSuggestions([]);
        return;
      }
      
      fetch(`/search/suggestions?q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            displaySearchSuggestions(data.suggestions);
          } else {
            displaySearchSuggestions([]);
          }
        })
        .catch(error => {
          console.error('Suggestions error:', error);
          displaySearchSuggestions([]);
        });
    }

    function displaySearchSuggestions(suggestions) {
      const suggestionsContainer = document.getElementById('searchSuggestions');
      
      if (suggestions.length === 0) {
        suggestionsContainer.classList.add('hidden');
        return;
      }
      
      suggestionsContainer.innerHTML = suggestions.map(suggestion => `
        <div class="search-suggestion px-4 py-3 cursor-pointer" onclick="selectSuggestion('${suggestion}')">
          <i class="fas fa-search text-gray-400 mr-3"></i>
          <span class="text-gray-700">${suggestion}</span>
        </div>
      `).join('');
      
      suggestionsContainer.classList.remove('hidden');
    }

    function selectSuggestion(suggestion) {
      document.getElementById('globalSearchInput').value = suggestion;
      document.getElementById('clearSearchBtn').classList.remove('hidden');
      hideSearchSuggestions();
      performGlobalSearch();
    }

    function showSearchSuggestions() {
      const query = document.getElementById('globalSearchInput').value.trim();
      if (query.length > 0) {
        loadSearchSuggestions(query);
      }
    }

    function hideSearchSuggestions() {
      // Delay hiding to allow clicking on suggestions
      setTimeout(() => {
        document.getElementById('searchSuggestions').classList.add('hidden');
      }, 200);
    }

    function loadRecentSearches() {
      const recentSearchesList = document.getElementById('recentSearchesList');
      
      if (recentSearches.length === 0) {
        document.getElementById('recentSearches').classList.add('hidden');
        return;
      }
      
      recentSearchesList.innerHTML = recentSearches.slice(0, 5).map(search => `
        <div class="recent-search-item px-3 py-2 rounded-lg cursor-pointer" onclick="selectSuggestion('${search}')">
          <i class="fas fa-history text-gray-400 mr-2"></i>
          <span class="text-sm text-gray-600">${search}</span>
        </div>
      `).join('');
    }

    function addToRecentSearches(query) {
      // Remove if already exists
      recentSearches = recentSearches.filter(search => search !== query);
      
      // Add to beginning
      recentSearches.unshift(query);
      
      // Keep only last 10
      recentSearches = recentSearches.slice(0, 10);
      
      // Save to localStorage
      localStorage.setItem('recentSearches', JSON.stringify(recentSearches));
      
      // Update display
      loadRecentSearches();
    }

    function clearSearchHistory() {
      recentSearches = [];
      localStorage.removeItem('recentSearches');
      loadRecentSearches();
      showToast('Search history cleared', 'success');
    }

    function exportSearchResults() {
      if (searchResults.length === 0) {
        showToast('No results to export', 'error');
        return;
      }
      
      const exportData = {
        query: currentSearchQuery,
        timestamp: new Date().toISOString(),
        results: searchResults.map(result => ({
          type: result.type,
          title: result.title,
          content: result.content.substring(0, 500),
          updated_at: result.updated_at,
          tags: result.tags,
          priority: result.priority
        }))
      };
      
      const blob = new Blob([JSON.stringify(exportData, null, 2)], { type: 'application/json' });
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = `search-results-${new Date().toISOString().split('T')[0]}.json`;
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);
      URL.revokeObjectURL(url);
      
      showToast('Search results exported successfully', 'success');
    }

    function showToast(message, type = 'info') {
      const toast = document.createElement('div');
      toast.className = `toast ${type}`;
      toast.innerHTML = `
        <div class="flex items-center">
          <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'} mr-2"></i>
          ${message}
        </div>
      `;
      
      document.getElementById('toast-container').appendChild(toast);
      
      setTimeout(() => toast.classList.add('show'), 100);
      
      setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
      }, 3000);
    }

    // Close suggestions when clicking outside
    document.addEventListener('click', function(e) {
      if (!e.target.closest('#searchSuggestions') && !e.target.closest('#globalSearchInput')) {
        hideSearchSuggestions();
      }
    });
  </script>
</body>
</html>
