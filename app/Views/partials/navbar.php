<?php
use Core\Session;
use App\Models\User;

// Get user data for display
$userId = Session::get('user_id');
$user = $userId ? User::findById($userId) : null;
$username = $user['username'] ?? Session::get('username') ?? 'Secure User';
$firstName = $user['first_name'] ?? null;
$displayName = $firstName ?: $username;
$initials = strtoupper(substr($displayName, 0, 1));

// Get current page title
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$page_title = $page_title ?? 'Secure Notes';

// Get current time for greeting
$currentHour = (int)date('H');
$greeting = $currentHour < 12 ? 'Good Morning' : ($currentHour < 18 ? 'Good Afternoon' : 'Good Evening');
?>

<header class="h-16 bg-white/95 backdrop-blur-md shadow-lg border-b border-gray-200/50 sticky top-0 z-50">
    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            
            <!-- Left Section: Page Title & Breadcrumb -->
            <div class="flex items-center space-x-4">
                <!-- Mobile Menu Button -->
                <button id="mobile-menu-btn" class="lg:hidden p-2 rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-100 transition-colors duration-200">
                    <i class="fas fa-bars text-lg"></i>
                </button>
                
                <!-- Page Title with Breadcrumb -->
                <div class="flex items-center space-x-3">
                    <div class="flex items-center space-x-2">
                        <div class="w-8 h-8 bg-gradient-to-br from-[#4C51BF] to-[#667eea] rounded-lg flex items-center justify-center">
                            <i class="fas fa-lock text-white text-sm"></i>
                        </div>
                        <div>
                            <h1 class="text-xl font-bold text-gray-900 tracking-tight">
                                <?= htmlspecialchars($page_title) ?>
                            </h1>
                            <p class="text-xs text-gray-500 hidden sm:block">
                                <?= $greeting ?>, <?= htmlspecialchars($displayName) ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Center Section: Global Search -->
            <div class="flex-1 max-w-2xl mx-8 hidden md:block">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400 text-sm"></i>
                    </div>
                    <input 
                        type="text" 
                        id="global-search"
                        placeholder="Search notes, tasks, and more..." 
                        class="block w-full pl-10 pr-4 py-2.5 text-sm bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#4C51BF]/20 focus:border-[#4C51BF] transition-all duration-200 placeholder-gray-400"
                        aria-label="Global Search">
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                        <kbd class="hidden lg:inline-flex items-center px-2 py-1 text-xs font-medium text-gray-500 bg-gray-100 border border-gray-200 rounded">⌘K</kbd>
                    </div>
                </div>
            </div>

            <!-- Right Section: Actions & Profile -->
            <div class="flex items-center space-x-3">
                
                <!-- Quick Actions -->
                <div class="hidden lg:flex items-center space-x-2">
                    <!-- Theme Toggle -->
                    <button id="theme-toggle" class="p-2 rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-100 transition-colors duration-200" title="Toggle Theme">
                        <i class="fas fa-moon text-lg"></i>
                    </button>
                    
                    <!-- Fullscreen Toggle -->
                    <button id="fullscreen-toggle" class="p-2 rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-100 transition-colors duration-200" title="Toggle Fullscreen">
                        <i class="fas fa-expand text-lg"></i>
                    </button>
                </div>

                <!-- Notifications -->
                <div class="relative">
                    <button id="notifications-btn" class="relative p-2 rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-100 transition-colors duration-200" title="Notifications">
                        <i class="fas fa-bell text-lg"></i>
                        <span class="absolute -top-1 -right-1 h-5 w-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center font-bold border-2 border-white">
                            3
                        </span>
                    </button>
                    
                    <!-- Notifications Dropdown -->
                    <div id="notifications-menu" class="absolute right-0 mt-2 w-80 bg-white border border-gray-200 rounded-xl shadow-2xl opacity-0 pointer-events-none transition-all duration-300 z-50">
                        <div class="p-4 border-b border-gray-100">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-gray-900">Notifications</h3>
                                <button class="text-sm text-[#4C51BF] hover:text-[#343D9B] font-medium">Mark all read</button>
                            </div>
                        </div>
                        <div class="max-h-80 overflow-y-auto">
                            <div class="p-3 border-b border-gray-50 hover:bg-gray-50 transition-colors">
                                <div class="flex items-start space-x-3">
                                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-sticky-note text-blue-600 text-sm"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900">New note created</p>
                                        <p class="text-xs text-gray-500">2 minutes ago</p>
                                    </div>
                                </div>
                            </div>
                            <div class="p-3 border-b border-gray-50 hover:bg-gray-50 transition-colors">
                                <div class="flex items-start space-x-3">
                                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-check text-green-600 text-sm"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900">Task completed</p>
                                        <p class="text-xs text-gray-500">1 hour ago</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="p-3 border-t border-gray-100">
                            <a href="/notifications" class="block text-center text-sm text-[#4C51BF] hover:text-[#343D9B] font-medium">View all notifications</a>
                        </div>
                    </div>
                </div>

                <!-- Security Status -->
                <div class="hidden lg:flex items-center space-x-2 px-3 py-1.5 bg-green-50 border border-green-200 rounded-lg">
                    <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                    <span class="text-xs font-medium text-green-700">Secure</span>
                </div>

                <!-- Profile Menu -->
                <div class="relative">
                    <button id="profile-btn" class="flex items-center space-x-3 p-2 rounded-xl hover:bg-gray-100 transition-colors duration-200">
                        <div class="w-8 h-8 bg-gradient-to-br from-[#4C51BF] to-[#667eea] rounded-full flex items-center justify-center text-white font-semibold text-sm">
                            <?= $initials ?>
                        </div>
                        <div class="hidden sm:block text-left">
                            <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($displayName) ?></p>
                            <p class="text-xs text-gray-500">Online</p>
                        </div>
                        <i class="fas fa-chevron-down text-gray-400 text-xs transition-transform duration-200"></i>
                    </button>

                    <!-- Profile Dropdown -->
                    <div id="profile-menu" class="absolute right-0 mt-2 w-64 bg-white border border-gray-200 rounded-xl shadow-2xl opacity-0 pointer-events-none transition-all duration-300 z-50">
                        <div class="p-4 border-b border-gray-100">
                            <div class="flex items-center space-x-3">
                                <div class="w-12 h-12 bg-gradient-to-br from-[#4C51BF] to-[#667eea] rounded-full flex items-center justify-center text-white font-semibold">
                                    <?= $initials ?>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900"><?= htmlspecialchars($displayName) ?></p>
                                    <p class="text-xs text-gray-500"><?= htmlspecialchars($user['email'] ?? '') ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="py-2">
                            <a href="/profile" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                <i class="fas fa-user mr-3 w-4 text-gray-400"></i>
                                Profile
                            </a>
                            <a href="/security" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                <i class="fas fa-shield-alt mr-3 w-4 text-gray-400"></i>
                                Security & Keys
                            </a>
                            <a href="/settings" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                <i class="fas fa-cog mr-3 w-4 text-gray-400"></i>
                                Settings
                            </a>
                            <a href="/backup" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                <i class="fas fa-download mr-3 w-4 text-gray-400"></i>
                                Backup & Export
                            </a>
                        </div>
                        <div class="border-t border-gray-100 py-2">
                            <a href="/logout" class="flex items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                <i class="fas fa-sign-out-alt mr-3 w-4"></i>
                                Sign Out
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- Mobile Search Overlay -->
<div id="mobile-search-overlay" class="fixed inset-0 bg-black/50 z-50 hidden">
    <div class="bg-white p-4">
        <div class="flex items-center space-x-3 mb-4">
            <button id="close-mobile-search" class="p-2 rounded-lg text-gray-600 hover:bg-gray-100">
                <i class="fas fa-times"></i>
            </button>
            <div class="flex-1 relative">
                <input 
                    type="text" 
                    placeholder="Search everything..." 
                    class="w-full pl-10 pr-4 py-3 text-sm bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#4C51BF]/20 focus:border-[#4C51BF]"
                    id="mobile-search-input">
                <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
            </div>
        </div>
    </div>
</div>

<style>
/* Custom animations and styles */
@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.dropdown-enter {
    animation: slideIn 0.2s ease-out;
}

/* Glassmorphism effect */
.backdrop-blur-md {
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
}

/* Custom scrollbar for notifications */
#notifications-menu .max-h-80::-webkit-scrollbar {
    width: 4px;
}

#notifications-menu .max-h-80::-webkit-scrollbar-track {
    background: #f1f5f9;
}

#notifications-menu .max-h-80::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 2px;
}

#notifications-menu .max-h-80::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* Pulse animation for security status */
@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.5;
    }
}

.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu toggle
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const sidebar = document.getElementById('sidebar');
    
    if (mobileMenuBtn && sidebar) {
        mobileMenuBtn.addEventListener('click', function() {
            sidebar.classList.toggle('sidebar-open');
        });
    }

    // Profile menu toggle
    const profileBtn = document.getElementById('profile-btn');
    const profileMenu = document.getElementById('profile-menu');
    
    if (profileBtn && profileMenu) {
        profileBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            profileMenu.classList.toggle('opacity-0');
            profileMenu.classList.toggle('pointer-events-none');
            profileMenu.classList.toggle('dropdown-enter');
        });
    }

    // Notifications menu toggle
    const notificationsBtn = document.getElementById('notifications-btn');
    const notificationsMenu = document.getElementById('notifications-menu');
    
    if (notificationsBtn && notificationsMenu) {
        notificationsBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            notificationsMenu.classList.toggle('opacity-0');
            notificationsMenu.classList.toggle('pointer-events-none');
            notificationsMenu.classList.toggle('dropdown-enter');
        });
    }

    // Close dropdowns when clicking outside
    document.addEventListener('click', function() {
        if (profileMenu) {
            profileMenu.classList.add('opacity-0', 'pointer-events-none');
            profileMenu.classList.remove('dropdown-enter');
        }
        if (notificationsMenu) {
            notificationsMenu.classList.add('opacity-0', 'pointer-events-none');
            notificationsMenu.classList.remove('dropdown-enter');
        }
    });

    // Global search functionality
    const globalSearch = document.getElementById('global-search');
    const mobileSearchOverlay = document.getElementById('mobile-search-overlay');
    const mobileSearchInput = document.getElementById('mobile-search-input');
    const closeMobileSearch = document.getElementById('close-mobile-search');

    if (globalSearch) {
        globalSearch.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                const query = this.value.trim();
                if (query) {
                    window.location.href = `/search?q=${encodeURIComponent(query)}`;
                }
            }
        });
    }

    // Mobile search overlay
    if (mobileSearchOverlay && mobileSearchInput && closeMobileSearch) {
        // Show mobile search on mobile devices
        if (window.innerWidth <= 768) {
            const searchIcon = document.createElement('button');
            searchIcon.className = 'md:hidden p-2 rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-100';
            searchIcon.innerHTML = '<i class="fas fa-search"></i>';
            searchIcon.addEventListener('click', function() {
                mobileSearchOverlay.classList.remove('hidden');
                mobileSearchInput.focus();
            });
            
            // Add search icon to navbar
            const rightSection = document.querySelector('.flex.items-center.space-x-3');
            if (rightSection) {
                rightSection.insertBefore(searchIcon, rightSection.firstChild);
            }
        }

        closeMobileSearch.addEventListener('click', function() {
            mobileSearchOverlay.classList.add('hidden');
        });

        mobileSearchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                const query = this.value.trim();
                if (query) {
                    window.location.href = `/search?q=${encodeURIComponent(query)}`;
                }
            }
        });
    }

    // Theme toggle
    const themeToggle = document.getElementById('theme-toggle');
    if (themeToggle) {
        themeToggle.addEventListener('click', function() {
            const icon = this.querySelector('i');
            if (icon.classList.contains('fa-moon')) {
                icon.className = 'fas fa-sun text-lg';
                document.documentElement.classList.add('dark');
                localStorage.setItem('theme', 'dark');
            } else {
                icon.className = 'fas fa-moon text-lg';
                document.documentElement.classList.remove('dark');
                localStorage.setItem('theme', 'light');
            }
        });

        // Load saved theme
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'dark') {
            document.documentElement.classList.add('dark');
            themeToggle.querySelector('i').className = 'fas fa-sun text-lg';
        }
    }

    // Fullscreen toggle
    const fullscreenToggle = document.getElementById('fullscreen-toggle');
    if (fullscreenToggle) {
        fullscreenToggle.addEventListener('click', function() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen();
                this.querySelector('i').className = 'fas fa-compress text-lg';
            } else {
                document.exitFullscreen();
                this.querySelector('i').className = 'fas fa-expand text-lg';
            }
        });
    }

    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Cmd/Ctrl + K for search
        if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
            e.preventDefault();
            if (window.innerWidth > 768) {
                globalSearch?.focus();
            } else {
                mobileSearchOverlay?.classList.remove('hidden');
                mobileSearchInput?.focus();
            }
        }
    });
});
</script>