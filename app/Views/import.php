<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'Import Data' ?> | SecureNote Pro</title>
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
        .file-drop-zone {
            border: 2px dashed #cbd5e1;
            border-radius: 1rem;
            padding: 2rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .file-drop-zone.dragover {
            background-color: #e0f2fe;
            border-color: #38bdf8;
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
        .import-card {
            transition: all 0.3s ease;
        }
        .import-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
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
        $page_title = "Import Data";
        include __DIR__ . '/partials/navbar.php'; 
        ?>

        <main class="flex-1 overflow-y-auto p-6">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800 mb-2">Import Data</h1>
                    <p class="text-gray-600">Import your notes and tasks from various file formats</p>
                </div>
                <div class="flex items-center gap-3">
                    <button onclick="showImportHistory()" class="px-4 py-2 text-gray-600 hover:text-gray-800 transition-colors">
                        <i class="fas fa-history mr-2"></i>Import History
                    </button>
                    <button onclick="showHelp()" class="px-4 py-2 text-gray-600 hover:text-gray-800 transition-colors">
                        <i class="fas fa-question-circle mr-2"></i>Help
                    </button>
                </div>
            </div>

            <!-- Import Form -->
            <div class="glassmorphism rounded-2xl p-6 mb-8">
                <h3 class="text-xl font-semibold text-gray-800 mb-6">Upload File</h3>
                
                <form id="importForm" enctype="multipart/form-data" class="space-y-6">
                    <input type="hidden" name="csrf_token" value="<?= \Core\CSRF::generate() ?>">
                    
                    <!-- File Upload Zone -->
                    <div class="file-drop-zone" id="fileDropZone" onclick="document.getElementById('import_file').click()">
                        <div class="text-center">
                            <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-cloud-upload-alt text-4xl text-gray-400"></i>
                            </div>
                            <h4 class="text-lg font-semibold text-gray-700 mb-2">Drop files here or click to browse</h4>
                            <p class="text-sm text-gray-500 mb-4">Supported formats: JSON, CSV, TXT, DOCX (up to 10MB)</p>
                            <button type="button" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                <i class="fas fa-folder-open mr-2"></i>Choose Files
                            </button>
                        </div>
                    </div>
                    
                    <input type="file" id="import_file" name="import_file" class="hidden" accept=".json,.csv,.txt,.docx" onchange="handleFileSelect(event)">
                    
                    <!-- File Info -->
                    <div id="fileInfo" class="hidden bg-gray-50 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="font-semibold text-gray-800" id="fileName"></h4>
                                <p class="text-sm text-gray-600" id="fileDetails"></p>
                            </div>
                            <button type="button" onclick="clearFile()" class="text-red-500 hover:text-red-700">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Import Options -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="format" class="block text-sm font-medium text-gray-700 mb-2">
                                File Format
                            </label>
                            <select id="format" name="format" class="w-full px-4 py-3 bg-white bg-opacity-50 border border-white border-opacity-30 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="auto">Auto-detect</option>
                                <option value="json">JSON</option>
                                <option value="csv">CSV</option>
                                <option value="txt">Text</option>
                                <option value="docx">Word Document</option>
                            </select>
                        </div>

                        <div>
                            <label for="import_type" class="block text-sm font-medium text-gray-700 mb-2">
                                Import Type
                            </label>
                            <select id="import_type" name="import_type" class="w-full px-4 py-3 bg-white bg-opacity-50 border border-white border-opacity-30 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="auto">Auto-detect (Notes & Tasks)</option>
                                <option value="notes">Notes Only</option>
                                <option value="tasks">Tasks Only</option>
                            </select>
                        </div>

                        <div>
                            <label for="merge_mode" class="block text-sm font-medium text-gray-700 mb-2">
                                Duplicate Handling
                            </label>
                            <select id="merge_mode" name="merge_mode" class="w-full px-4 py-3 bg-white bg-opacity-50 border border-white border-opacity-30 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="skip">Skip duplicates</option>
                                <option value="overwrite">Overwrite duplicates</option>
                                <option value="rename">Rename duplicates</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Additional Options
                            </label>
                            <div class="space-y-2">
                                <label class="flex items-center">
                                    <input type="checkbox" id="preserve_ids" name="preserve_ids" class="mr-3">
                                    <span class="text-sm text-gray-700">Preserve original IDs</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" id="import_attachments" name="import_attachments" class="mr-3" disabled>
                                    <span class="text-sm text-gray-700">Import attachments (coming soon)</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center gap-4">
                        <button type="button" onclick="validateFile()" id="validateBtn" class="px-6 py-3 bg-gray-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300">
                            <i class="fas fa-check-circle mr-2"></i>Validate File
                        </button>
                        <button type="submit" id="importBtn" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300">
                            <i class="fas fa-upload mr-2"></i>Import Data
                        </button>
                    </div>
                </form>
            </div>

            <!-- Import Templates -->
            <div class="glassmorphism rounded-2xl p-6 mb-8">
                <h3 class="text-xl font-semibold text-gray-800 mb-6">Download Templates</h3>
                <p class="text-gray-600 mb-6">Download sample templates to understand the expected file format</p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="import-card bg-white bg-opacity-50 border border-white border-opacity-30 rounded-xl p-6">
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                            <i class="fas fa-file-code text-2xl text-blue-600"></i>
                        </div>
                        <h4 class="font-semibold text-gray-800 mb-2">JSON Template</h4>
                        <p class="text-sm text-gray-600 mb-4">Structured data format with full metadata support</p>
                        <button onclick="downloadTemplate('json')" class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700 transition-colors">
                            <i class="fas fa-download mr-2"></i>Download
                        </button>
                    </div>
                    
                    <div class="import-card bg-white bg-opacity-50 border border-white border-opacity-30 rounded-xl p-6">
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mb-4">
                            <i class="fas fa-file-csv text-2xl text-green-600"></i>
                        </div>
                        <h4 class="font-semibold text-gray-800 mb-2">CSV Template</h4>
                        <p class="text-sm text-gray-600 mb-4">Spreadsheet format for bulk data import</p>
                        <button onclick="downloadTemplate('csv')" class="w-full bg-green-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-green-700 transition-colors">
                            <i class="fas fa-download mr-2"></i>Download
                        </button>
                    </div>
                    
                    <div class="import-card bg-white bg-opacity-50 border border-white border-opacity-30 rounded-xl p-6">
                        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mb-4">
                            <i class="fas fa-file-alt text-2xl text-purple-600"></i>
                        </div>
                        <h4 class="font-semibold text-gray-800 mb-2">Text Template</h4>
                        <p class="text-sm text-gray-600 mb-4">Simple text format with structured headers</p>
                        <button onclick="downloadTemplate('txt')" class="w-full bg-purple-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-purple-700 transition-colors">
                            <i class="fas fa-download mr-2"></i>Download
                        </button>
                    </div>
                    
                    <div class="import-card bg-white bg-opacity-50 border border-white border-opacity-30 rounded-xl p-6">
                        <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center mb-4">
                            <i class="fas fa-file-word text-2xl text-orange-600"></i>
                        </div>
                        <h4 class="font-semibold text-gray-800 mb-2">Word Template</h4>
                        <p class="text-sm text-gray-600 mb-4">Microsoft Word document format</p>
                        <button onclick="downloadTemplate('docx')" class="w-full bg-orange-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-orange-700 transition-colors">
                            <i class="fas fa-download mr-2"></i>Download
                        </button>
                    </div>
                </div>
            </div>

            <!-- Import History -->
            <div class="glassmorphism rounded-2xl p-6">
                <h3 class="text-xl font-semibold text-gray-800 mb-6">Recent Imports</h3>
                <div id="importHistory" class="space-y-4">
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-history text-4xl mb-4"></i>
                        <p>No import history available</p>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Progress Modal -->
<div id="progressModal" class="fixed inset-0 bg-black bg-opacity-50 modal-backdrop flex items-center justify-center hidden z-50">
    <div class="modal-content rounded-2xl shadow-2xl w-11/12 max-w-md slide-in">
        <div class="p-6">
            <div class="text-center">
                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-spinner text-blue-600 text-2xl animate-spin"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 mb-2" id="progressTitle">Processing...</h3>
                <p class="text-gray-600 mb-6" id="progressDescription">Please wait while we process your request.</p>
                
                <div class="w-full bg-gray-200 rounded-full h-2 mb-4">
                    <div id="progressBar" class="progress-bar h-2 rounded-full" style="width: 0%"></div>
                </div>
                
                <p class="text-sm text-gray-500" id="progressText">0%</p>
            </div>
        </div>
    </div>
</div>

<!-- Help Modal -->
<div id="helpModal" class="fixed inset-0 bg-black bg-opacity-50 modal-backdrop flex items-center justify-center hidden z-50">
    <div class="modal-content rounded-2xl shadow-2xl w-11/12 max-w-4xl max-h-[90vh] overflow-hidden">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-2xl font-semibold text-gray-800">Import Help & Guidelines</h3>
                <button onclick="closeHelpModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>
            
            <div class="overflow-y-auto max-h-[70vh] space-y-6">
                <div>
                    <h4 class="text-lg font-semibold text-gray-800 mb-3">Supported File Formats</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <h5 class="font-semibold text-blue-800 mb-2">JSON Format</h5>
                            <p class="text-sm text-blue-700">Structured data with full metadata support. Best for complex imports with tags, priorities, and custom fields.</p>
                        </div>
                        <div class="bg-green-50 p-4 rounded-lg">
                            <h5 class="font-semibold text-green-800 mb-2">CSV Format</h5>
                            <p class="text-sm text-green-700">Spreadsheet format for bulk imports. Use headers to define columns and data types.</p>
                        </div>
                        <div class="bg-purple-50 p-4 rounded-lg">
                            <h5 class="font-semibold text-purple-800 mb-2">Text Format</h5>
                            <p class="text-sm text-purple-700">Simple text format with structured headers. Good for basic note imports.</p>
                        </div>
                        <div class="bg-orange-50 p-4 rounded-lg">
                            <h5 class="font-semibold text-orange-800 mb-2">Word Format</h5>
                            <p class="text-sm text-orange-700">Microsoft Word documents. Content will be extracted and imported as notes.</p>
                        </div>
                    </div>
                </div>

                <div>
                    <h4 class="text-lg font-semibold text-gray-800 mb-3">Import Options</h4>
                    <ul class="space-y-2 text-sm text-gray-700">
                        <li><strong>Auto-detect:</strong> Automatically determines file format and content type</li>
                        <li><strong>Skip duplicates:</strong> Ignores items that already exist</li>
                        <li><strong>Overwrite duplicates:</strong> Replaces existing items with imported data</li>
                        <li><strong>Rename duplicates:</strong> Adds a suffix to duplicate items</li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-lg font-semibold text-gray-800 mb-3">Best Practices</h4>
                    <ul class="space-y-2 text-sm text-gray-700">
                        <li>• Always validate your file before importing</li>
                        <li>• Use templates to ensure proper formatting</li>
                        <li>• Start with small files to test the import process</li>
                        <li>• Keep backups of your original data</li>
                        <li>• Check import history for troubleshooting</li>
                    </ul>
                </div>
            </div>
            
            <div class="flex items-center justify-end gap-3 mt-6 pt-6 border-t border-gray-200">
                <button onclick="closeHelpModal()" class="px-6 py-3 bg-gray-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300">
                    <i class="fas fa-times mr-2"></i>Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    let selectedFile = null;

    document.addEventListener('DOMContentLoaded', function() {
        setupEventListeners();
        loadImportHistory();
    });

    function setupEventListeners() {
        const dropZone = document.getElementById('fileDropZone');
        dropZone.addEventListener('dragover', function(e) {
            e.preventDefault();
            dropZone.classList.add('dragover');
        });
        dropZone.addEventListener('dragleave', function(e) {
            e.preventDefault();
            dropZone.classList.remove('dragover');
        });
        dropZone.addEventListener('drop', function(e) {
            e.preventDefault();
            dropZone.classList.remove('dragover');
            handleFileDrop(e.dataTransfer.files);
        });

        // Form submission
        document.getElementById('importForm').addEventListener('submit', function(e) {
            e.preventDefault();
            startImport();
        });
    }

    function handleFileSelect(event) {
        selectedFile = event.target.files[0];
        if (selectedFile) {
            displayFileInfo(selectedFile);
        }
    }

    function handleFileDrop(files) {
        selectedFile = files[0];
        if (selectedFile) {
            displayFileInfo(selectedFile);
        }
    }

    function displayFileInfo(file) {
        document.getElementById('fileName').textContent = file.name;
        document.getElementById('fileDetails').textContent = `${(file.size / 1024 / 1024).toFixed(2)} MB • ${file.type || 'Unknown type'}`;
        document.getElementById('fileInfo').classList.remove('hidden');
        showToast(`File selected: ${file.name}`, 'info');
    }

    function clearFile() {
        selectedFile = null;
        document.getElementById('import_file').value = '';
        document.getElementById('fileInfo').classList.add('hidden');
    }

    function validateFile() {
        if (!selectedFile) {
            showToast('Please select a file first', 'error');
            return;
        }

        showProgressModal('Validating File', 'Checking file structure and content...', 0);

        const formData = new FormData();
        formData.append('import_file', selectedFile);
        formData.append('format', document.getElementById('format').value);
        formData.append('csrf_token', document.querySelector('input[name="csrf_token"]').value);

        fetch('/import/validate', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateProgress(100, 'File is valid!');
                setTimeout(() => {
                    hideProgressModal();
                    showToast(`File is valid! Found ${data.data?.item_count || 'unknown'} items.`, 'success');
                }, 1000);
            } else {
                hideProgressModal();
                showToast(`Validation failed: ${data.message}`, 'error');
            }
        })
        .catch(error => {
            hideProgressModal();
            showToast('Validation failed: ' + error.message, 'error');
        });
    }

    function startImport() {
        if (!selectedFile) {
            showToast('Please select a file first', 'error');
            return;
        }

        showProgressModal('Importing Data', 'Uploading and processing your data...', 0);

        const formData = new FormData();
        formData.append('import_file', selectedFile);
        formData.append('format', document.getElementById('format').value);
        formData.append('import_type', document.getElementById('import_type').value);
        formData.append('merge_mode', document.getElementById('merge_mode').value);
        formData.append('preserve_ids', document.getElementById('preserve_ids').checked);
        formData.append('import_attachments', document.getElementById('import_attachments').checked);
        formData.append('csrf_token', document.querySelector('input[name="csrf_token"]').value);

        fetch('/import/import', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateProgress(100, 'Import completed successfully!');
                setTimeout(() => {
                    hideProgressModal();
                    showToast(data.message, 'success');
                    clearFile();
                    loadImportHistory();
                }, 1000);
            } else {
                hideProgressModal();
                showToast(data.message, 'error');
            }
        })
        .catch(error => {
            hideProgressModal();
            showToast('Import failed: ' + error.message, 'error');
        });
    }

    function downloadTemplate(format) {
        window.location.href = `/import/template?format=${format}`;
    }

    function loadImportHistory() {
        fetch('/import/history')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayImportHistory(data.data.history);
                } else {
                    console.error('Failed to load import history:', data.message);
                }
            })
            .catch(error => {
                console.error('Error loading import history:', error);
            });
    }

    function displayImportHistory(history) {
        const historyContainer = document.getElementById('importHistory');
        
        if (!history || history.length === 0) {
            historyContainer.innerHTML = `
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-history text-4xl mb-4"></i>
                    <p>No import history available</p>
                </div>
            `;
            return;
        }

        historyContainer.innerHTML = history.map(item => `
            <div class="bg-white bg-opacity-50 border border-white border-opacity-30 rounded-xl p-4">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center ${item.status === 'success' ? 'bg-green-100' : 'bg-red-100'}">
                            <i class="fas fa-${item.status === 'success' ? 'check' : 'times'} text-${item.status === 'success' ? 'green' : 'red'}-600"></i>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-800">${item.filename}</h4>
                            <p class="text-sm text-gray-600">${item.format.toUpperCase()} • ${new Date(item.created_at).toLocaleString()}</p>
                        </div>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-medium ${item.status === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                        ${item.status}
                    </span>
                </div>
                <div class="flex items-center gap-4 text-sm text-gray-600">
                    <span><i class="fas fa-plus text-green-600 mr-1"></i>${item.items_imported} imported</span>
                    <span><i class="fas fa-skip text-yellow-600 mr-1"></i>${item.items_skipped} skipped</span>
                    ${item.items_failed > 0 ? `<span><i class="fas fa-times text-red-600 mr-1"></i>${item.items_failed} failed</span>` : ''}
                </div>
            </div>
        `).join('');
    }

    function showImportHistory() {
        loadImportHistory();
        showToast('Import history loaded', 'info');
    }

    function showHelp() {
        document.getElementById('helpModal').classList.remove('hidden');
    }

    function closeHelpModal() {
        document.getElementById('helpModal').classList.add('hidden');
    }

    function showProgressModal(title, description, progress) {
        document.getElementById('progressTitle').textContent = title;
        document.getElementById('progressDescription').textContent = description;
        document.getElementById('progressBar').style.width = progress + '%';
        document.getElementById('progressText').textContent = progress + '%';
        document.getElementById('progressModal').classList.remove('hidden');
    }

    function updateProgress(progress, description) {
        document.getElementById('progressBar').style.width = progress + '%';
        document.getElementById('progressText').textContent = progress + '%';
        if (description) {
            document.getElementById('progressDescription').textContent = description;
        }
    }

    function hideProgressModal() {
        document.getElementById('progressModal').classList.add('hidden');
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

    // Close modals when clicking outside
    document.addEventListener('click', function(event) {
        const helpModal = document.getElementById('helpModal');
        if (event.target === helpModal) {
            closeHelpModal();
        }
    });

    // Close modals with Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeHelpModal();
        }
    });
</script>
</body>
</html>