<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advanced Search | SecureNote Pro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f0f9ff', 100: '#e0f2fe', 200: '#bae6fd', 300: '#7dd3fc', 400: '#38bdf8',
                            500: '#0ea5e9', 600: '#0284c7', 700: '#0369a1', 800: '#075985', 900: '#0c4a6e',
                        },
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.5s ease-in-out',
                        'slide-up': 'slideUp 0.3s ease-out',
                        'bounce-gentle': 'bounceGentle 2s infinite',
                        'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                        'shimmer': 'shimmer 2s linear infinite',
                    },
                },
            },
        };
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
        
        .glassmorphism {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.3);
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
        
        .toast.show { transform: translateX(0); }
        .toast.success { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
        .toast.error { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); }
        .toast.info { background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); }
        
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
        
        .result-type-note { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
        .result-type-task { background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); }
        .result-type-tag { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); }
        .result-type-attachment { background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); }
        
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
        
        .advanced-search-panel {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        .search-operator {
            background: rgba(139, 92, 246, 0.1);
            border: 1px solid rgba(139, 92, 246, 0.2);
            color: #7c3aed;
            padding: 0.25rem 0.5rem;
            border-radius: 0.375rem;
            font-size: 0.75rem;
            font-weight: 500;
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
                $page_title = "Advanced Search";
                include __DIR__ . '/partials/navbar.php'; 
            ?>

            <!-- Content -->
            <main class="flex-1 overflow-y-auto p-6">
                
                <!-- Header Section -->
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h1 class="text-4xl font-bold text-gray-800 mb-2">Advanced Search</h1>
                        <p class="text-gray-600">Powerful search across all your content with advanced filters</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <button onclick="toggleAdvancedPanel()" class="px-4 py-2 text-gray-600 hover:text-gray-800 transition-colors">
                            <i class="fas fa-cog mr-2"></i>Advanced Options
                        </button>
                        <button onclick="clearSearchHistory()" class="px-4 py-2 text-gray-600 hover:text-gray-800 transition-colors">
                            <i class="fas fa-history mr-2"></i>Clear History
                        </button>
                        <button onclick="exportSearchResults()" class="px-4 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors">
                            <i class="fas fa-download mr-2"></i>Export Results
                        </button>
                    </div>
                </div>

                <!-- Advanced Search Panel -->
                <div id="advancedPanel" class="advanced-search-panel rounded-2xl p-6 mb-8 hidden">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Advanced Search Options</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <!-- Search Operators -->
                        <div>
                            <h4 class="font-medium text-gray-700 mb-3">Search Operators</h4>
                            <div class="space-y-2">
                                <div class="flex items-center gap-2">
                                    <span class="search-operator">"exact phrase"</span>
                                    <span class="text-sm text-gray-600">Search for exact phrases</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="search-operator">tag:work</span>
                                    <span class="text-sm text-gray-600">Search by specific tags</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="search-operator">priority:high</span>
                                    <span class="text-sm text-gray-600">Filter by priority</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="search-operator">type:note</span>
                                    <span class="text-sm text-gray-600">Search specific content types</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="search-operator">date:2024-01-01</span>
                                    <span class="text-sm text-gray-600">Filter by date</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Content Type Filters -->
                        <div>
                            <h4 class="font-medium text-gray-700 mb-3">Content Types</h4>
                            <div class="space-y-2">
                                <label class="flex items-center">
                                    <input type="checkbox" id="searchNotes" class="mr-3" checked>
                                    <span class="text-sm text-gray-700">Notes</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" id="searchTasks" class="mr-3" checked>
                                    <span class="text-sm text-gray-700">Tasks</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" id="searchTags" class="mr-3" checked>
                                    <span class="text-sm text-gray-700">Tags</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" id="searchAttachments" class="mr-3">
                                    <span class="text-sm text-gray-700">File Attachments</span>
                                </label>
                            </div>
                        </div>
                        
                        <!-- Date Range -->
                        <div>
                            <h4 class="font-medium text-gray-700 mb-3">Date Range</h4>
                            <div class="space-y-3">
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
                </div>

                <!-- Search Section -->
                <div class="glassmorphism rounded-2xl p-6 mb-8">
                    <div class="relative">
                        <div class="flex items-center">
                            <div class="relative flex-1">
                                <input type="text" id="globalSearchInput" placeholder="Search everything with advanced operators..." 
                                       class="search-input w-full pl-12 pr-16 py-4 rounded-xl text-lg focus:outline-none"
                                       autocomplete="off">
                                <i class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400 text-lg"></i>
                                <div id="searchSpinner" class="loading-spinner absolute right-12 top-1/2 transform -translate-y-1/2 hidden"></div>
                                <button onclick="clearSearch()" id="clearSearchBtn" class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 hidden">
                                    <i class="fas fa-times text-lg"></i>
                                </button>
                            </div>
                            <button onclick="performAdvancedSearch()" class="ml-4 px-6 py-4 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300">
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

                <!-- Search Results -->
                <div id="searchResults" class="hidden mb-8">
                    <div class="glassmorphism rounded-2xl p-6">
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
                                <button onclick="toggleSearchTips()" id="showTipsBtn" class="px-4 py-2 bg-white bg-opacity-50 border border-white border-opacity-30 rounded-xl text-sm font-medium hover:bg-opacity-70 transition-all duration-200">
                                    <i class="fas fa-lightbulb mr-2"></i>Show Tips
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
                </div>

                <!-- No Results State -->
                <div id="noResults" class="glassmorphism rounded-2xl p-12 text-center hidden mb-8">
                    <div class="mb-6">
                        <i class="fas fa-search text-6xl text-gray-300"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-600 mb-2">No results found</h3>
                    <p class="text-gray-500 mb-6">Try adjusting your search terms or using advanced operators</p>
                    <button onclick="clearAllFilters()" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300">
                        <i class="fas fa-refresh mr-2"></i>Clear Filters
                    </button>
                </div>

                <!-- Search Tips -->
                <div id="searchTips" class="glassmorphism rounded-2xl p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Search Tips & Examples</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-3">
                            <h4 class="font-medium text-gray-700">Basic Search</h4>
                            <ul class="text-sm text-gray-600 space-y-2">
                                <li><code class="bg-gray-100 px-2 py-1 rounded">meeting notes</code> - Find notes about meetings</li>
                                <li><code class="bg-gray-100 px-2 py-1 rounded">urgent tasks</code> - Find urgent tasks</li>
                                <li><code class="bg-gray-100 px-2 py-1 rounded">project ideas</code> - Find project-related content</li>
                            </ul>
                        </div>
                        <div class="space-y-3">
                            <h4 class="font-medium text-gray-700">Advanced Operators</h4>
                            <ul class="text-sm text-gray-600 space-y-2">
                                <li><code class="bg-gray-100 px-2 py-1 rounded">"exact phrase"</code> - Search for exact phrases</li>
                                <li><code class="bg-gray-100 px-2 py-1 rounded">tag:work priority:high</code> - Combine filters</li>
                                <li><code class="bg-gray-100 px-2 py-1 rounded">type:note date:2024-01-01</code> - Filter by type and date</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Result Detail Modal -->
    <div id="resultModal" class="fixed inset-0 bg-black bg-opacity-50 modal-backdrop flex items-center justify-center hidden z-50">
        <div class="modal-content rounded-2xl shadow-2xl w-11/12 max-w-4xl max-h-[90vh] overflow-hidden">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-2xl font-semibold text-gray-800" id="modalTitle">Result Details</h3>
                    <button onclick="closeResultModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <i class="fas fa-times text-2xl"></i>
                    </button>
                </div>
                
                <div class="overflow-y-auto max-h-[70vh]">
                    <div id="modalContent" class="space-y-4">
                        <!-- Modal content will be populated here -->
                    </div>
                </div>
                
                <div class="flex items-center justify-end gap-3 mt-6 pt-6 border-t border-gray-200">
                    <button onclick="closeResultModal()" class="px-6 py-3 bg-gray-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300">
                        <i class="fas fa-times mr-2"></i>Close
                    </button>
                    <button onclick="openInApp()" id="openInAppBtn" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300">
                        <i class="fas fa-external-link-alt mr-2"></i>Open in App
                    </button>
                </div>
            </div>
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
            searchInput.focus();
            loadSearchSuggestions();
        }

        function setupEventListeners() {
            const searchInput = document.getElementById('globalSearchInput');
            
            searchInput.addEventListener('input', handleSearchInput);
            searchInput.addEventListener('keydown', handleSearchKeydown);
            searchInput.addEventListener('focus', showSearchSuggestions);
            searchInput.addEventListener('blur', hideSearchSuggestions);
        }

        function handleSearchInput(event) {
            const query = event.target.value.trim();
            
            clearTimeout(searchTimeout);
            
            if (query.length > 0) {
                document.getElementById('clearSearchBtn').classList.remove('hidden');
                
                searchTimeout = setTimeout(() => {
                    loadSearchSuggestions(query);
                }, 300);
            } else {
                document.getElementById('clearSearchBtn').classList.add('hidden');
                hideSearchSuggestions();
            }
        }

        function handleSearchKeydown(event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                performAdvancedSearch();
            } else if (event.key === 'Escape') {
                clearSearch();
            }
        }

        function performAdvancedSearch() {
            const query = document.getElementById('globalSearchInput').value.trim();
            
            if (!query) {
                showToast('Please enter a search term', 'error');
                return;
            }
            
            addToRecentSearches(query);
            document.getElementById('searchSpinner').classList.remove('hidden');
            hideSearchSuggestions();
            
            currentSearchQuery = query;
            currentPage = 1;
            searchResults = [];
            
            // Get advanced filters
            const advancedFilters = {
                ...currentFilters,
                searchNotes: document.getElementById('searchNotes').checked,
                searchTasks: document.getElementById('searchTasks').checked,
                searchTags: document.getElementById('searchTags').checked,
                searchAttachments: document.getElementById('searchAttachments').checked,
                dateFrom: document.getElementById('dateFrom').value,
                dateTo: document.getElementById('dateTo').value
            };
            
            fetch('/search/api', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    query: query,
                    filters: advancedFilters,
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
            
            // Ensure all elements are properly visible/hidden
            if (searchResults.length === 0) {
                searchResultsDiv.classList.add('hidden');
                noResultsDiv.classList.remove('hidden');
                searchTips.classList.remove('hidden');
                return;
            }
            
            // Show search results and hide other elements
            searchResultsDiv.classList.remove('hidden');
            noResultsDiv.classList.add('hidden');
            searchTips.classList.add('hidden');
            
            // Ensure the results container is visible
            resultsContainer.style.display = 'block';
            resultsContainer.style.visibility = 'visible';
            
            resultsCount.textContent = `(${searchResults.length} results)`;
            
            if (isGridView) {
                displayGridView();
            } else {
                displayListView();
            }
            
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

        function openResult(type, id) {
            // Find the result data
            const result = searchResults.find(r => r.id == id && r.type === type);
            if (!result) {
                showToast('Result not found', 'error');
                return;
            }
            
            // Store current result for modal
            window.currentResult = result;
            
            // Show modal with result details
            showResultModal(result);
        }
        
        function showResultModal(result) {
            const modal = document.getElementById('resultModal');
            const modalTitle = document.getElementById('modalTitle');
            const modalContent = document.getElementById('modalContent');
            const openInAppBtn = document.getElementById('openInAppBtn');
            
            // Set modal title
            modalTitle.textContent = `${result.type.charAt(0).toUpperCase() + result.type.slice(1)}: ${result.title}`;
            
            // Set modal content based on result type
            let content = '';
            switch (result.type) {
                case 'note':
                    content = `
                        <div class="space-y-4">
                            <div class="flex items-center gap-4">
                                <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-medium">Note</span>
                                <span class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-sm">${result.priority || 'Medium'} Priority</span>
                                ${result.is_pinned ? '<span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm">Pinned</span>' : ''}
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-800 mb-2">Content:</h4>
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <p class="text-gray-700 whitespace-pre-wrap">${result.content || 'No content available'}</p>
                                </div>
                            </div>
                            ${result.tags && result.tags.length > 0 ? `
                                <div>
                                    <h4 class="font-semibold text-gray-800 mb-2">Tags:</h4>
                                    <div class="flex flex-wrap gap-2">
                                        ${result.tags.map(tag => `<span class="px-2 py-1 bg-purple-100 text-purple-800 rounded text-sm">${tag}</span>`).join('')}
                                    </div>
                                </div>
                            ` : ''}
                            <div class="text-sm text-gray-500">
                                Created: ${new Date(result.created_at).toLocaleString()}
                                ${result.updated_at ? `<br>Updated: ${new Date(result.updated_at).toLocaleString()}` : ''}
                            </div>
                        </div>
                    `;
                    break;
                case 'task':
                    content = `
                        <div class="space-y-4">
                            <div class="flex items-center gap-4">
                                <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-medium">Task</span>
                                <span class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-sm">${result.status || 'Pending'}</span>
                                <span class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-sm">${result.priority || 'Medium'} Priority</span>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-800 mb-2">Description:</h4>
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <p class="text-gray-700 whitespace-pre-wrap">${result.description || 'No description available'}</p>
                                </div>
                            </div>
                            ${result.due_date ? `
                                <div>
                                    <h4 class="font-semibold text-gray-800 mb-2">Due Date:</h4>
                                    <p class="text-gray-700">${new Date(result.due_date).toLocaleString()}</p>
                                </div>
                            ` : ''}
                            ${result.category ? `
                                <div>
                                    <h4 class="font-semibold text-gray-800 mb-2">Category:</h4>
                                    <p class="text-gray-700">${result.category}</p>
                                </div>
                            ` : ''}
                            ${result.tags && result.tags.length > 0 ? `
                                <div>
                                    <h4 class="font-semibold text-gray-800 mb-2">Tags:</h4>
                                    <div class="flex flex-wrap gap-2">
                                        ${result.tags.map(tag => `<span class="px-2 py-1 bg-purple-100 text-purple-800 rounded text-sm">${tag}</span>`).join('')}
                                    </div>
                                </div>
                            ` : ''}
                            <div class="text-sm text-gray-500">
                                Created: ${new Date(result.created_at).toLocaleString()}
                                ${result.updated_at ? `<br>Updated: ${new Date(result.updated_at).toLocaleString()}` : ''}
                            </div>
                        </div>
                    `;
                    break;
                case 'tag':
                    content = `
                        <div class="space-y-4">
                            <div class="flex items-center gap-4">
                                <span class="px-3 py-1 bg-purple-100 text-purple-800 rounded-full text-sm font-medium">Tag</span>
                                <div class="w-6 h-6 rounded-full" style="background-color: ${result.color || '#6B7280'}"></div>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-800 mb-2">Tag Name:</h4>
                                <p class="text-gray-700 text-lg">${result.name}</p>
                            </div>
                            <div class="text-sm text-gray-500">
                                Created: ${new Date(result.created_at).toLocaleString()}
                            </div>
                        </div>
                    `;
                    break;
                default:
                    content = `
                        <div class="space-y-4">
                            <div class="flex items-center gap-4">
                                <span class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-sm font-medium">${result.type}</span>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-800 mb-2">Title:</h4>
                                <p class="text-gray-700">${result.title}</p>
                            </div>
                            ${result.content ? `
                                <div>
                                    <h4 class="font-semibold text-gray-800 mb-2">Content:</h4>
                                    <div class="bg-gray-50 p-4 rounded-lg">
                                        <p class="text-gray-700 whitespace-pre-wrap">${result.content}</p>
                                    </div>
                                </div>
                            ` : ''}
                        </div>
                    `;
            }
            
            modalContent.innerHTML = content;
            
            // Set up the "Open in App" button
            openInAppBtn.onclick = () => openInApp(result);
            
            // Show modal
            modal.classList.remove('hidden');
        }
        
        function closeResultModal() {
            const modal = document.getElementById('resultModal');
            modal.classList.add('hidden');
            window.currentResult = null;
        }
        
        // Close modal when clicking outside
        document.addEventListener('click', function(event) {
            const modal = document.getElementById('resultModal');
            if (event.target === modal) {
                closeResultModal();
            }
        });
        
        // Close modal with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeResultModal();
            }
        });
        
        function openInApp(result) {
            closeResultModal();
            switch (result.type) {
                case 'note':
                    window.location.href = `/notes`;
                    break;
                case 'task':
                    window.location.href = `/tasks`;
                    break;
                case 'tag':
                    window.location.href = `/tags`;
                    break;
                default:
                    showToast('Cannot open this type in app', 'error');
            }
        }

        function toggleAdvancedPanel() {
            const panel = document.getElementById('advancedPanel');
            panel.classList.toggle('hidden');
        }

        function toggleSearchTips() {
            const searchTips = document.getElementById('searchTips');
            const showTipsBtn = document.getElementById('showTipsBtn');
            
            if (searchTips.classList.contains('hidden')) {
                searchTips.classList.remove('hidden');
                showTipsBtn.innerHTML = '<i class="fas fa-eye-slash mr-2"></i>Hide Tips';
            } else {
                searchTips.classList.add('hidden');
                showTipsBtn.innerHTML = '<i class="fas fa-lightbulb mr-2"></i>Show Tips';
            }
        }

        function clearSearch() {
            document.getElementById('globalSearchInput').value = '';
            document.getElementById('clearSearchBtn').classList.add('hidden');
            document.getElementById('searchResults').classList.add('hidden');
            document.getElementById('noResults').classList.add('hidden');
            document.getElementById('searchTips').classList.remove('hidden');
            hideSearchSuggestions();
            currentSearchQuery = '';
            searchResults = [];
            
            // Ensure proper visibility state
            const resultsContainer = document.getElementById('resultsContainer');
            resultsContainer.style.display = '';
            resultsContainer.style.visibility = '';
        }

        function clearAllFilters() {
            document.getElementById('searchNotes').checked = true;
            document.getElementById('searchTasks').checked = true;
            document.getElementById('searchTags').checked = true;
            document.getElementById('searchAttachments').checked = false;
            document.getElementById('dateFrom').value = '';
            document.getElementById('dateTo').value = '';
            
            if (currentSearchQuery) {
                performAdvancedSearch();
            }
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
            performAdvancedSearch();
        }

        function showSearchSuggestions() {
            const query = document.getElementById('globalSearchInput').value.trim();
            if (query.length > 0) {
                loadSearchSuggestions(query);
            }
        }

        function hideSearchSuggestions() {
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
            recentSearches = recentSearches.filter(search => search !== query);
            recentSearches.unshift(query);
            recentSearches = recentSearches.slice(0, 10);
            localStorage.setItem('recentSearches', JSON.stringify(recentSearches));
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

        function sortSearchResults() {
            const sortBy = document.getElementById('sortResults').value;
            
            switch (sortBy) {
                case 'relevance':
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
