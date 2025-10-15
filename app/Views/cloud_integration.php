<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'Cloud Integration' ?> | SecureNote Pro</title>
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
        .modal-backdrop { backdrop-filter: blur(8px); }
        .modal-content {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        .progress-bar {
            background: linear-gradient(90deg, #38bdf8, #0ea5e9);
            transition: width 0.3s ease-in-out;
        }
        .cloud-card {
            background: linear-gradient(135deg, rgba(255,255,255,0.9) 0%, rgba(255,255,255,0.7) 100%);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.3);
            transition: all 0.3s ease;
        }
        .cloud-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 min-h-screen">

<div id="toast-container"></div>

<input type="hidden" name="csrf_token" value="<?= \Core\CSRF::generate() ?>">

<div class="flex h-screen">
    <?php include __DIR__ . '/partials/sidebar.php'; ?>

    <div class="flex-1 flex flex-col overflow-hidden">
        <?php 
        $page_title = "Cloud Integration";
        include __DIR__ . '/partials/navbar.php'; 
        ?>

        <main class="flex-1 overflow-y-auto p-6">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800 mb-2">Cloud Integration</h1>
                    <p class="text-gray-600">Connect your cloud storage services for automatic backup sync</p>
                </div>
            </div>

            <!-- Google Drive Integration -->
            <div class="cloud-card rounded-2xl p-6 mb-8">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center mr-4">
                            <i class="fab fa-google-drive text-white text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-gray-800">Google Drive</h3>
                            <p class="text-gray-600">Sync your backups to Google Drive</p>
                        </div>
                    </div>
                    <div id="googleDriveStatus">
                        <?php if ($googleDriveConnected): ?>
                            <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-medium">
                                <i class="fas fa-check-circle mr-1"></i>Connected
                            </span>
                        <?php else: ?>
                            <span class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-sm font-medium">
                                <i class="fas fa-times-circle mr-1"></i>Not Connected
                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="text-lg font-semibold text-gray-800 mb-4">Features</h4>
                        <ul class="space-y-2 text-sm text-gray-600">
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-2"></i>
                                Automatic backup upload
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-2"></i>
                                Secure file storage
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-2"></i>
                                Cross-device access
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-2"></i>
                                Version history
                            </li>
                        </ul>
                    </div>

                    <div>
                        <h4 class="text-lg font-semibold text-gray-800 mb-4">Actions</h4>
                        <div class="space-y-3">
                            <?php if ($googleDriveConnected): ?>
                                <button onclick="disconnectGoogleDrive()" class="w-full px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                                    <i class="fas fa-unlink mr-2"></i>Disconnect
                                </button>
                                <button onclick="listGoogleDriveFiles()" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                    <i class="fas fa-list mr-2"></i>View Files
                                </button>
                            <?php else: ?>
                                <button onclick="connectGoogleDrive()" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                    <i class="fab fa-google-drive mr-2"></i>Connect Google Drive
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dropbox Integration -->
            <div class="cloud-card rounded-2xl p-6 mb-8">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-400 to-blue-500 rounded-xl flex items-center justify-center mr-4">
                            <i class="fab fa-dropbox text-white text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-gray-800">Dropbox</h3>
                            <p class="text-gray-600">Sync your backups to Dropbox</p>
                        </div>
                    </div>
                    <div id="dropboxStatus">
                        <?php if ($dropboxConnected): ?>
                            <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-medium">
                                <i class="fas fa-check-circle mr-1"></i>Connected
                            </span>
                        <?php else: ?>
                            <span class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-sm font-medium">
                                <i class="fas fa-times-circle mr-1"></i>Not Connected
                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="text-lg font-semibold text-gray-800 mb-4">Features</h4>
                        <ul class="space-y-2 text-sm text-gray-600">
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-2"></i>
                                Automatic backup upload
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-2"></i>
                                Secure file storage
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-2"></i>
                                Cross-device access
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-2"></i>
                                Version history
                            </li>
                        </ul>
                    </div>

                    <div>
                        <h4 class="text-lg font-semibold text-gray-800 mb-4">Actions</h4>
                        <div class="space-y-3">
                            <?php if ($dropboxConnected): ?>
                                <button onclick="disconnectDropbox()" class="w-full px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                                    <i class="fas fa-unlink mr-2"></i>Disconnect
                                </button>
                                <button onclick="listDropboxFiles()" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                    <i class="fas fa-list mr-2"></i>View Files
                                </button>
                            <?php else: ?>
                                <button onclick="connectDropbox()" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                    <i class="fab fa-dropbox mr-2"></i>Connect Dropbox
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- OneDrive Integration (Coming Soon) -->
            <div class="cloud-card rounded-2xl p-6 mb-8 opacity-60">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-600 to-blue-700 rounded-xl flex items-center justify-center mr-4">
                            <i class="fab fa-microsoft text-white text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-gray-800">Microsoft OneDrive</h3>
                            <p class="text-gray-600">Sync your backups to OneDrive</p>
                        </div>
                    </div>
                    <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm font-medium">
                        <i class="fas fa-clock mr-1"></i>Coming Soon
                    </span>
                </div>
                <p class="text-gray-500 text-sm">OneDrive integration will be available in a future update.</p>
            </div>

            <!-- Google Drive Files Modal -->
            <div id="filesModal" class="fixed inset-0 bg-black bg-opacity-50 modal-backdrop flex items-center justify-center hidden z-50">
                <div class="modal-content rounded-2xl shadow-2xl w-11/12 max-w-4xl max-h-[80vh] overflow-hidden">
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h3 class="text-xl font-semibold text-gray-800">Google Drive Files</h3>
                            <button onclick="closeFilesModal()" class="text-gray-400 hover:text-gray-600">
                                <i class="fas fa-times text-xl"></i>
                            </button>
                        </div>
                    </div>
                    <div class="p-6 overflow-y-auto max-h-[60vh]">
                        <div id="filesList" class="space-y-3">
                            <!-- Files will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
    function connectGoogleDrive() {
        const csrfToken = document.querySelector('input[name="csrf_token"]').value;
        
        fetch('/cloud-integration/connect-google-drive', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `csrf_token=${csrfToken}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = data.auth_url;
            } else {
                showToast(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred while connecting to Google Drive', 'error');
        });
    }

    function disconnectGoogleDrive() {
        if (!confirm('Are you sure you want to disconnect Google Drive? This will remove access to your files.')) {
            return;
        }

        const csrfToken = document.querySelector('input[name="csrf_token"]').value;
        
        fetch('/cloud-integration/disconnect-google-drive', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `csrf_token=${csrfToken}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                showToast(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred while disconnecting Google Drive', 'error');
        });
    }

    function listGoogleDriveFiles() {
        fetch('/cloud-integration/list-google-drive-files')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayFiles(data.files);
                document.getElementById('filesModal').classList.remove('hidden');
            } else {
                showToast(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred while loading files', 'error');
        });
    }

    function displayFiles(files) {
        const filesList = document.getElementById('filesList');
        
        if (files.length === 0) {
            filesList.innerHTML = '<p class="text-gray-500 text-center py-8">No files found in Google Drive</p>';
            return;
        }

        filesList.innerHTML = files.map(file => `
            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                <div class="flex items-center">
                    <i class="fas fa-file-archive text-blue-500 mr-3"></i>
                    <div>
                        <h4 class="font-medium text-gray-800">${file.name}</h4>
                        <p class="text-sm text-gray-500">
                            ${formatFileSize(file.size)} • ${formatDate(file.createdTime)}
                        </p>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <button onclick="downloadFile('${file.id}')" class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors">
                        <i class="fas fa-download mr-1"></i>Download
                    </button>
                    <button onclick="deleteFile('${file.id}')" class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700 transition-colors">
                        <i class="fas fa-trash mr-1"></i>Delete
                    </button>
                </div>
            </div>
        `).join('');
    }

    function downloadFile(fileId) {
        window.open(`/cloud-integration/download-google-drive-file?file_id=${fileId}`, '_blank');
    }

    function deleteFile(fileId) {
        if (!confirm('Are you sure you want to delete this file from Google Drive?')) {
            return;
        }

        const csrfToken = document.querySelector('input[name="csrf_token"]').value;
        
        fetch('/cloud-integration/delete-google-drive-file', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `csrf_token=${csrfToken}&file_id=${fileId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                listGoogleDriveFiles(); // Refresh the list
            } else {
                showToast(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred while deleting the file', 'error');
        });
    }

    function closeFilesModal() {
        document.getElementById('filesModal').classList.add('hidden');
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
    }

    // Dropbox Functions
    function connectDropbox() {
        const csrfToken = document.querySelector('input[name="csrf_token"]').value;
        
        fetch('/cloud-integration/connect-dropbox', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `csrf_token=${csrfToken}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = data.auth_url;
            } else {
                showToast(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred while connecting to Dropbox', 'error');
        });
    }

    function disconnectDropbox() {
        if (!confirm('Are you sure you want to disconnect Dropbox? This will remove access to your files.')) {
            return;
        }

        const csrfToken = document.querySelector('input[name="csrf_token"]').value;
        
        fetch('/cloud-integration/disconnect-dropbox', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `csrf_token=${csrfToken}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                showToast(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred while disconnecting Dropbox', 'error');
        });
    }

    function listDropboxFiles() {
        fetch('/cloud-integration/list-dropbox-files')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayDropboxFiles(data.files);
                document.getElementById('filesModal').classList.remove('hidden');
            } else {
                showToast(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred while loading files', 'error');
        });
    }

    function displayDropboxFiles(files) {
        const filesList = document.getElementById('filesList');
        
        if (files.length === 0) {
            filesList.innerHTML = '<p class="text-gray-500 text-center py-8">No files found in Dropbox</p>';
            return;
        }

        filesList.innerHTML = files.map(file => `
            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                <div class="flex items-center">
                    <i class="fas fa-file-archive text-blue-500 mr-3"></i>
                    <div>
                        <h4 class="font-medium text-gray-800">${file.name}</h4>
                        <p class="text-sm text-gray-500">
                            ${formatFileSize(file.size || 0)} • ${formatDate(file.client_modified || file.server_modified)}
                        </p>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <button onclick="downloadDropboxFile('${file.path_display}')" class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors">
                        <i class="fas fa-download mr-1"></i>Download
                    </button>
                    <button onclick="deleteDropboxFile('${file.path_display}')" class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700 transition-colors">
                        <i class="fas fa-trash mr-1"></i>Delete
                    </button>
                </div>
            </div>
        `).join('');
    }

    function downloadDropboxFile(filePath) {
        window.open(`/cloud-integration/download-dropbox-file?file_path=${encodeURIComponent(filePath)}`, '_blank');
    }

    function deleteDropboxFile(filePath) {
        if (!confirm('Are you sure you want to delete this file from Dropbox?')) {
            return;
        }

        const csrfToken = document.querySelector('input[name="csrf_token"]').value;
        
        fetch('/cloud-integration/delete-dropbox-file', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `csrf_token=${csrfToken}&file_path=${encodeURIComponent(filePath)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                listDropboxFiles(); // Refresh the list
            } else {
                showToast(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred while deleting the file', 'error');
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
</script>
</body>
</html>

