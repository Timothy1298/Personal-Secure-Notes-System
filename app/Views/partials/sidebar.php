<?php
use Core\Session;
use App\Models\User;
use App\Models\TasksModel;
use Core\Database;

// Get user data for display
$userId = Session::get('user_id');
$user = $userId ? User::findById($userId) : null;
$username = $user['username'] ?? Session::get('username') ?? 'Secure User';
$firstName = $user['first_name'] ?? null;
$displayName = $firstName ?: $username;
$initials = strtoupper(substr($displayName, 0, 1));

// Get trash count for indicator
$trashCount = 0;
if ($userId) {
    try {
        $db = Database::getInstance();
        $tasksModel = new TasksModel($db);
        $trashStats = $tasksModel->getTrashStats($userId);
        $trashCount = $trashStats['total_deleted'] ?? 0;
    } catch (Exception $e) {
        // Silently fail - don't break the sidebar
        $trashCount = 0;
    }
}

// Helper function to get the current URI path
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$currentPath = rtrim($currentPath, '/'); 
if (empty($currentPath)) {
    $currentPath = '/';
}

// Function to check if the current path matches the target path
function isActive($path, $currentPath) {
    if ($path === '/dashboard' && ($currentPath === '/' || $currentPath === '/dashboard')) {
        return true;
    }
    return $currentPath === $path;
}

// Function to check if path starts with a given prefix (for sub-pages)
function isActivePrefix($prefix, $currentPath) {
    return strpos($currentPath, $prefix) === 0;
}
?>

<aside id="sidebar" class="w-64 bg-[#1F2937] text-white flex flex-col h-screen shadow-2xl transition-all duration-300 ease-in-out border-r border-[#374151]">
    
    <!-- Header: Logo/App Name -->
    <div class="p-6 text-2xl font-extrabold text-[#4C51BF] tracking-wide border-b border-[#374151] flex items-center justify-between">
        <div class="flex items-center">
            <i class="fas fa-lock mr-2 text-xl"></i>
            <span class="sidebar-text">SecureNotes</span>
        </div>
        <button id="sidebar-toggle" class="text-gray-400 hover:text-white transition-colors duration-200">
            <i class="fas fa-bars"></i>
        </button>
    </div>
    
    <!-- User Profile Section -->
    <div class="p-4 border-b border-[#374151] flex items-center space-x-3">
        <div class="w-10 h-10 bg-[#4C51BF] rounded-full flex items-center justify-center text-lg font-semibold border-2 border-white">
            <?= $initials ?>
        </div>
        <div class="user-info">
            <p class="text-sm font-medium leading-none truncate"><?= htmlspecialchars($displayName) ?></p>
            <span class="text-xs text-gray-400">Personal Vault</span>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="p-4 border-b border-[#374151]">
        <a href="/search" class="flex items-center p-2 rounded-lg bg-[#374151] text-gray-300 hover:bg-[#4B5563] transition-colors duration-200 <?= isActive('/search', $currentPath) ? 'active-link' : '' ?>">
            <i class="fas fa-search w-4 mr-3 text-sm"></i>
            <span class="text-sm search-text">Search everything...</span>
        </a>
        <a href="/import" class="flex items-center p-2 rounded-lg bg-[#374151] text-gray-300 hover:bg-[#4B5563] transition-colors duration-200 mt-2 <?= isActive('/import', $currentPath) ? 'active-link' : '' ?>">
            <i class="fas fa-upload w-4 mr-3 text-sm"></i>
            <span class="text-sm import-text">Import Data</span>
        </a>
    </div>

    <!-- Primary Navigation (Core Functionality) -->
    <nav class="flex-1 px-4 pt-4 pb-2 overflow-y-auto space-y-1">
        <ul class="space-y-1">
            <li class="nav-item">
                <a href="/dashboard" class="group flex items-center p-3 rounded-xl text-sm font-semibold text-gray-300 hover:bg-[#374151] hover:text-white transition-colors duration-200 <?= isActive('/dashboard', $currentPath) ? 'active-link' : '' ?>">
                    <i class="fas fa-th-large w-5 mr-4 text-lg"></i>
                    <span class="nav-text">Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="/notes" class="group flex items-center p-3 rounded-xl text-sm font-semibold text-gray-300 hover:bg-[#374151] hover:text-white transition-colors duration-200 <?= isActive('/notes', $currentPath) ? 'active-link' : '' ?>">
                    <i class="fas fa-sticky-note w-5 mr-4 text-lg"></i>
                    <span class="nav-text">Secure Notes</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="/tasks" class="group flex items-center p-3 rounded-xl text-sm font-semibold text-gray-300 hover:bg-[#374151] hover:text-white transition-colors duration-200 <?= isActive('/tasks', $currentPath) ? 'active-link' : '' ?>">
                    <i class="fas fa-check-square w-5 mr-4 text-lg"></i>
                    <span class="nav-text">Tasks & To-Dos</span>
                </a>
            </li>
        </ul>
        
        <!-- Divider for Organization -->
        <hr class="my-4 border-t border-[#374151]">
        
        <p class="text-xs text-gray-500 uppercase px-3 py-1 font-bold tracking-wider section-title">Organization</p>
        <ul class="space-y-1">
            <li class="nav-item">
                <a href="/tags" class="group flex items-center p-3 rounded-xl text-sm font-semibold text-gray-300 hover:bg-[#374151] hover:text-white transition-colors duration-200 <?= isActive('/tags', $currentPath) ? 'active-link' : '' ?>">
                    <i class="fas fa-tags w-5 mr-4 text-lg"></i>
                    <span class="nav-text">Tags & Labels</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="/archived" class="group flex items-center p-3 rounded-xl text-sm font-semibold text-gray-300 hover:bg-[#374151] hover:text-white transition-colors duration-200 <?= isActive('/archived', $currentPath) ? 'active-link' : '' ?>">
                    <i class="fas fa-archive w-5 mr-4 text-lg"></i>
                    <span class="nav-text">Archive</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="/trash" class="group flex items-center p-3 rounded-xl text-sm font-semibold text-gray-300 hover:bg-[#374151] hover:text-white transition-colors duration-200 <?= isActive('/trash', $currentPath) ? 'active-link' : '' ?>">
                    <i class="fas fa-trash-alt w-5 mr-4 text-lg"></i>
                    <span class="nav-text">Trash</span>
                    <?php if ($trashCount > 0): ?>
                        <span class="ml-auto bg-red-500 text-white text-xs rounded-full px-2 py-1 min-w-[20px] text-center">
                            <?= $trashCount ?>
                        </span>
                    <?php endif; ?>
                </a>
            </li>
        </ul>

        <!-- Divider for Tools -->
        <hr class="my-4 border-t border-[#374151]">
        
        <p class="text-xs text-gray-500 uppercase px-3 py-1 font-bold tracking-wider section-title">Tools & Utilities</p>
        <ul class="space-y-1">
            <li class="nav-item">
                <a href="/backup" class="group flex items-center p-3 rounded-xl text-sm font-semibold text-gray-300 hover:bg-[#374151] hover:text-white transition-colors duration-200 <?= isActive('/backup', $currentPath) ? 'active-link' : '' ?>">
                    <i class="fas fa-download w-5 mr-4 text-lg"></i>
                    <span class="nav-text">Backup & Export</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="/cloud-integration" class="group flex items-center p-3 rounded-xl text-sm font-semibold text-gray-300 hover:bg-[#374151] hover:text-white transition-colors duration-200 <?= isActive('/cloud-integration', $currentPath) ? 'active-link' : '' ?>">
                    <i class="fas fa-cloud w-5 mr-4 text-lg"></i>
                    <span class="nav-text">Cloud Integration</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="/audit-logs" class="group flex items-center p-3 rounded-xl text-sm font-semibold text-gray-300 hover:bg-[#374151] hover:text-white transition-colors duration-200 <?= isActive('/audit-logs', $currentPath) ? 'active-link' : '' ?>">
                    <i class="fas fa-history w-5 mr-4 text-lg"></i>
                    <span class="nav-text">Audit Logs</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="/offline" class="group flex items-center p-3 rounded-xl text-sm font-semibold text-gray-300 hover:bg-[#374151] hover:text-white transition-colors duration-200 <?= isActive('/offline', $currentPath) ? 'active-link' : '' ?>">
                    <i class="fas fa-wifi-slash w-5 mr-4 text-lg"></i>
                    <span class="nav-text">Offline Mode</span>
                </a>
            </li>
        </ul>
    </nav>

    <!-- Footer: Settings and Logout -->
    <div class="p-4 border-t border-[#374151] space-y-1">
        
        <!-- Security Tab -->
        <a href="/security" class="flex items-center p-3 rounded-xl text-sm font-semibold text-red-300 hover:bg-red-900 hover:text-white transition-colors duration-200 <?= isActive('/security', $currentPath) ? 'active-link-security' : '' ?>">
            <i class="fas fa-shield-alt w-5 mr-4 text-lg"></i>
            <span class="nav-text">Security & Keys</span>
        </a>

        <a href="/settings" class="flex items-center p-3 rounded-xl text-sm font-semibold text-gray-300 hover:bg-[#374151] hover:text-white transition-colors duration-200 <?= isActive('/settings', $currentPath) ? 'active-link' : '' ?>">
            <i class="fas fa-cog w-5 mr-4 text-lg"></i>
            <span class="nav-text">Settings</span>
        </a>
        
        <a href="/logout" class="flex items-center p-3 rounded-xl text-sm font-semibold text-gray-300 hover:bg-red-800 hover:text-white transition-colors duration-200">
            <i class="fas fa-sign-out-alt w-5 mr-4 text-lg"></i>
            <span class="nav-text">Logout</span>
        </a>
    </div>
</aside>
<style>
/* Custom CSS to define the active link look for the purple accent */
.active-link {
    background-color: #4C51BF; /* Accent color */
    color: white !important;
    box-shadow: 0 4px 6px -1px rgba(76, 81, 191, 0.4);
}
.active-link:hover {
    background-color: #343D9B !important; /* Slightly darker hover */
}
.active-link-security {
    background-color: #b91c1c; /* Red-700 */
    color: white !important;
    box-shadow: 0 4px 6px -1px rgba(185, 28, 28, 0.4);
}

/* Collapsible sidebar styles */
.sidebar-collapsed {
    width: 4rem !important;
}

.sidebar-collapsed .sidebar-text {
    display: none;
}

.sidebar-collapsed .nav-text {
    display: none;
}

.sidebar-collapsed .user-info {
    display: none;
}

.sidebar-collapsed .search-text {
    display: none;
}

.sidebar-collapsed .section-title {
    display: none;
}

/* Hover tooltip for collapsed sidebar */
.sidebar-collapsed .nav-item {
    position: relative;
}

.sidebar-collapsed .nav-item:hover::after {
    content: attr(data-tooltip);
    position: absolute;
    left: 100%;
    top: 50%;
    transform: translateY(-50%);
    background: #1F2937;
    color: white;
    padding: 8px 12px;
    border-radius: 6px;
    font-size: 14px;
    white-space: nowrap;
    z-index: 1000;
    margin-left: 8px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

/* Responsive design */
@media (max-width: 768px) {
    #sidebar {
        position: fixed;
        left: -16rem;
        z-index: 1000;
    }
    
    #sidebar.sidebar-open {
        left: 0;
    }
    
    .sidebar-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 999;
        display: none;
    }
    
    .sidebar-overlay.active {
        display: block;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('sidebar-toggle');
    const sidebarTexts = document.querySelectorAll('.sidebar-text, .nav-text, .user-info, .search-text, .import-text, .section-title');
    
    // Toggle sidebar collapse
    toggleBtn.addEventListener('click', function() {
        sidebar.classList.toggle('sidebar-collapsed');
        
        // Update toggle button icon
        const icon = toggleBtn.querySelector('i');
        if (sidebar.classList.contains('sidebar-collapsed')) {
            icon.className = 'fas fa-chevron-right';
        } else {
            icon.className = 'fas fa-bars';
        }
        
        // Save state to localStorage
        localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('sidebar-collapsed'));
    });
    
    // Restore sidebar state from localStorage
    const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
    if (isCollapsed) {
        sidebar.classList.add('sidebar-collapsed');
        const icon = toggleBtn.querySelector('i');
        icon.className = 'fas fa-chevron-right';
    }
    
    // Add tooltip data attributes for collapsed state
    const navItems = document.querySelectorAll('.nav-item a');
    navItems.forEach(item => {
        const text = item.textContent.trim();
        item.closest('.nav-item').setAttribute('data-tooltip', text);
    });
    
    // Mobile sidebar handling
    if (window.innerWidth <= 768) {
        const overlay = document.createElement('div');
        overlay.className = 'sidebar-overlay';
        document.body.appendChild(overlay);
        
        toggleBtn.addEventListener('click', function() {
            sidebar.classList.toggle('sidebar-open');
            overlay.classList.toggle('active');
        });
        
        overlay.addEventListener('click', function() {
            sidebar.classList.remove('sidebar-open');
            overlay.classList.remove('active');
        });
    }
    
    // Keyboard shortcut for sidebar toggle (Ctrl/Cmd + B)
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'b') {
            e.preventDefault();
            toggleBtn.click();
        }
    });
});
</script>
