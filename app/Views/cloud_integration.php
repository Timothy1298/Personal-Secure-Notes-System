<?php
$pageTitle = "Cloud Integration";
include __DIR__ . '/partials/header.php';
?>

<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="py-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Cloud Integration</h1>
                        <p class="mt-2 text-gray-600">Connect your cloud storage and sync your data</p>
                    </div>
                    <a href="/integrations" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                        <i class="fas fa-cog mr-2"></i>Manage Integrations
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Google Drive Integration -->
            <div class="bg-white rounded-lg shadow-sm border">
                <div class="p-6 border-b">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-blue-600 rounded-lg flex items-center justify-center">
                            <i class="fab fa-google-drive text-white text-xl"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-semibold text-gray-900">Google Drive</h2>
                            <p class="text-gray-600">Sync your notes and tasks with Google Drive</p>
                        </div>
                    </div>
                </div>
                
                <div class="p-6">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-700">Status</span>
                            <span id="google-status" class="bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded-full">
                                Not Connected
                            </span>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-700">Auto Sync</span>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" id="google-sync" class="sr-only peer" disabled>
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>
                        
                        <div class="pt-4">
                            <button id="google-connect" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                                <i class="fab fa-google mr-2"></i>Connect Google Drive
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Microsoft OneDrive Integration -->
            <div class="bg-white rounded-lg shadow-sm border">
                <div class="p-6 border-b">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center">
                            <i class="fab fa-microsoft text-white text-xl"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-semibold text-gray-900">Microsoft OneDrive</h2>
                            <p class="text-gray-600">Sync your notes and tasks with OneDrive</p>
                        </div>
                    </div>
                </div>
                
                <div class="p-6">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-700">Status</span>
                            <span id="microsoft-status" class="bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded-full">
                                Not Connected
                            </span>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-700">Auto Sync</span>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" id="microsoft-sync" class="sr-only peer" disabled>
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>
                        
                        <div class="pt-4">
                            <button id="microsoft-connect" class="w-full bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors">
                                <i class="fab fa-microsoft mr-2"></i>Connect OneDrive
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dropbox Integration -->
            <div class="bg-white rounded-lg shadow-sm border">
                <div class="p-6 border-b">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-blue-700 rounded-lg flex items-center justify-center">
                            <i class="fab fa-dropbox text-white text-xl"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-semibold text-gray-900">Dropbox</h2>
                            <p class="text-gray-600">Sync your notes and tasks with Dropbox</p>
                        </div>
                    </div>
                </div>
                
                <div class="p-6">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-700">Status</span>
                            <span id="dropbox-status" class="bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded-full">
                                Not Connected
                            </span>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-700">Auto Sync</span>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" id="dropbox-sync" class="sr-only peer" disabled>
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>
                        
                        <div class="pt-4">
                            <button id="dropbox-connect" class="w-full bg-blue-700 hover:bg-blue-800 text-white px-4 py-2 rounded-lg transition-colors">
                                <i class="fab fa-dropbox mr-2"></i>Connect Dropbox
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Slack Integration -->
            <div class="bg-white rounded-lg shadow-sm border">
                <div class="p-6 border-b">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-purple-600 rounded-lg flex items-center justify-center">
                            <i class="fab fa-slack text-white text-xl"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-semibold text-gray-900">Slack</h2>
                            <p class="text-gray-600">Send notifications and share content via Slack</p>
                        </div>
                    </div>
                </div>
                
                <div class="p-6">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-700">Status</span>
                            <span id="slack-status" class="bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded-full">
                                Not Connected
                            </span>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-700">Notifications</span>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" id="slack-notifications" class="sr-only peer" disabled>
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>
                        
                        <div class="pt-4">
                            <button id="slack-connect" class="w-full bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition-colors">
                                <i class="fab fa-slack mr-2"></i>Connect Slack
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sync Status -->
        <div class="mt-8 bg-white rounded-lg shadow-sm border">
            <div class="p-6 border-b">
                <h2 class="text-xl font-semibold text-gray-900">Sync Status</h2>
                <p class="text-gray-600 mt-1">Monitor your cloud sync activities</p>
            </div>
            
            <div class="p-6">
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div class="flex items-center space-x-3">
                            <i class="fas fa-sync text-blue-600"></i>
                            <div>
                                <p class="font-medium text-gray-900">Last Sync</p>
                                <p class="text-sm text-gray-600">Never synced</p>
                            </div>
                        </div>
                        <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                            <i class="fas fa-sync mr-2"></i>Sync Now
                        </button>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="text-center p-4 bg-gray-50 rounded-lg">
                            <p class="text-2xl font-bold text-gray-900">0</p>
                            <p class="text-sm text-gray-600">Files Synced</p>
                        </div>
                        <div class="text-center p-4 bg-gray-50 rounded-lg">
                            <p class="text-2xl font-bold text-gray-900">0</p>
                            <p class="text-sm text-gray-600">Notes Synced</p>
                        </div>
                        <div class="text-center p-4 bg-gray-50 rounded-lg">
                            <p class="text-2xl font-bold text-gray-900">0</p>
                            <p class="text-sm text-gray-600">Tasks Synced</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Load integration status
    loadIntegrationStatus();
    
    // Connect buttons
    document.getElementById('google-connect').addEventListener('click', function() {
        connectIntegration('google');
    });
    
    document.getElementById('microsoft-connect').addEventListener('click', function() {
        connectIntegration('microsoft');
    });
    
    document.getElementById('dropbox-connect').addEventListener('click', function() {
        connectIntegration('dropbox');
    });
    
    document.getElementById('slack-connect').addEventListener('click', function() {
        connectIntegration('slack');
    });
});

function loadIntegrationStatus() {
    // This would typically fetch from the integrations API
    // For now, we'll simulate the status
    const integrations = ['google', 'microsoft', 'dropbox', 'slack'];
    
    integrations.forEach(integration => {
        // Simulate checking status
        setTimeout(() => {
            const statusElement = document.getElementById(`${integration}-status`);
            if (statusElement) {
                // In a real implementation, this would come from the API
                statusElement.textContent = 'Not Connected';
                statusElement.className = 'bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded-full';
            }
        }, 500);
    });
}

function connectIntegration(provider) {
    // Redirect to the integration OAuth flow
    window.location.href = `/integrations/${provider}/connect`;
}
</script>

<?php include __DIR__ . '/partials/footer.php'; ?>