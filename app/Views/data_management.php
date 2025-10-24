<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Management - Personal Notes System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        .tab-button {
            transition: all 0.3s ease;
        }
        .tab-button.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .progress-bar {
            transition: width 0.3s ease;
        }
        .file-drop-zone {
            border: 2px dashed #cbd5e0;
            transition: all 0.3s ease;
        }
        .file-drop-zone.dragover {
            border-color: #667eea;
            background-color: #f7fafc;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow-sm border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-4">
                    <div class="flex items-center">
                        <h1 class="text-2xl font-bold text-gray-900">Data Management</h1>
                    </div>
                    <div class="flex items-center space-x-4">
                        <a href="/dashboard" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors">
                            Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Tabs -->
            <div class="bg-white rounded-lg shadow mb-8">
                <div class="border-b border-gray-200">
                    <nav class="-mb-px flex space-x-8 px-6">
                        <button class="tab-button active py-4 px-1 border-b-2 border-transparent font-medium text-sm" data-tab="export">
                            Export Data
                        </button>
                        <button class="tab-button py-4 px-1 border-b-2 border-transparent font-medium text-sm" data-tab="import">
                            Import Data
                        </button>
                        <button class="tab-button py-4 px-1 border-b-2 border-transparent font-medium text-sm" data-tab="migrations">
                            Migrations
                        </button>
                        <button class="tab-button py-4 px-1 border-b-2 border-transparent font-medium text-sm" data-tab="history">
                            History
                        </button>
                    </nav>
                </div>

                <!-- Export Tab -->
                <div id="export" class="tab-content active p-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <!-- Export Options -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Export Your Data</h3>
                            
                            <form id="exportForm" class="space-y-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Export Format</label>
                                    <select name="format" id="exportFormat" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="json">JSON (Complete Data)</option>
                                        <option value="csv">CSV (Spreadsheet)</option>
                                        <option value="xml">XML (Structured Data)</option>
                                        <option value="zip">ZIP (Complete Backup)</option>
                                    </select>
                                </div>

                                <div id="csvDataType" class="hidden">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Data Type</label>
                                    <select name="data_type" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="notes">Notes</option>
                                        <option value="tasks">Tasks</option>
                                        <option value="tags">Tags</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Include Options</label>
                                    <div class="space-y-2">
                                        <label class="flex items-center">
                                            <input type="checkbox" name="options[include_notes]" checked class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                            <span class="ml-2 text-sm text-gray-700">Notes</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="checkbox" name="options[include_tasks]" checked class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                            <span class="ml-2 text-sm text-gray-700">Tasks</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="checkbox" name="options[include_tags]" checked class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                            <span class="ml-2 text-sm text-gray-700">Tags</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="checkbox" name="options[include_settings]" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                            <span class="ml-2 text-sm text-gray-700">Settings</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="checkbox" name="options[include_analytics]" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                            <span class="ml-2 text-sm text-gray-700">Analytics Data</span>
                                        </label>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Date Range (Optional)</label>
                                    <input type="date" name="options[date_range]" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>

                                <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition-colors">
                                    Export Data
                                </button>
                            </form>
                        </div>

                        <!-- Export Progress -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Export Progress</h3>
                            <div id="exportProgress" class="hidden">
                                <div class="bg-gray-200 rounded-full h-2 mb-4">
                                    <div class="progress-bar bg-blue-600 h-2 rounded-full" style="width: 0%"></div>
                                </div>
                                <p id="exportStatus" class="text-sm text-gray-600">Preparing export...</p>
                            </div>
                            
                            <div id="exportResult" class="hidden">
                                <div class="bg-green-50 border border-green-200 rounded-md p-4">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <h3 class="text-sm font-medium text-green-800">Export Completed</h3>
                                            <div class="mt-2 text-sm text-green-700">
                                                <p id="exportResultText"></p>
                                                <a id="downloadLink" href="#" class="font-medium underline hover:text-green-600">Download File</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Import Tab -->
                <div id="import" class="tab-content p-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <!-- Import Options -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Import Data</h3>
                            
                            <form id="importForm" class="space-y-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Import Format</label>
                                    <select name="format" id="importFormat" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="json">JSON</option>
                                        <option value="csv">CSV</option>
                                        <option value="xml">XML</option>
                                        <option value="zip">ZIP Backup</option>
                                    </select>
                                </div>

                                <div id="csvImportDataType" class="hidden">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Data Type</label>
                                    <select name="data_type" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="notes">Notes</option>
                                        <option value="tasks">Tasks</option>
                                        <option value="tags">Tags</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">File Upload</label>
                                    <div class="file-drop-zone border-2 border-dashed border-gray-300 rounded-lg p-6 text-center">
                                        <input type="file" id="importFile" name="import_file" class="hidden" accept=".json,.csv,.xml,.zip">
                                        <div id="dropZoneContent">
                                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                            <div class="mt-4">
                                                <label for="importFile" class="cursor-pointer">
                                                    <span class="mt-2 block text-sm font-medium text-gray-900">Drop files here or click to upload</span>
                                                    <span class="mt-1 block text-sm text-gray-500">JSON, CSV, XML, or ZIP files</span>
                                                </label>
                                            </div>
                                        </div>
                                        <div id="selectedFile" class="hidden">
                                            <p class="text-sm text-gray-600">Selected: <span id="fileName"></span></p>
                                            <button type="button" id="removeFile" class="mt-2 text-sm text-red-600 hover:text-red-500">Remove</button>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Import Options</label>
                                    <div class="space-y-2">
                                        <label class="flex items-center">
                                            <input type="checkbox" name="options[import_notes]" checked class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                            <span class="ml-2 text-sm text-gray-700">Import Notes</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="checkbox" name="options[import_tasks]" checked class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                            <span class="ml-2 text-sm text-gray-700">Import Tasks</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="checkbox" name="options[import_tags]" checked class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                            <span class="ml-2 text-sm text-gray-700">Import Tags</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="checkbox" name="options[import_settings]" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                            <span class="ml-2 text-sm text-gray-700">Import Settings</span>
                                        </label>
                                    </div>
                                </div>

                                <button type="submit" class="w-full bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700 transition-colors">
                                    Import Data
                                </button>
                            </form>
                        </div>

                        <!-- Import Progress -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Import Progress</h3>
                            <div id="importProgress" class="hidden">
                                <div class="bg-gray-200 rounded-full h-2 mb-4">
                                    <div class="progress-bar bg-green-600 h-2 rounded-full" style="width: 0%"></div>
                                </div>
                                <p id="importStatus" class="text-sm text-gray-600">Preparing import...</p>
                            </div>
                            
                            <div id="importResult" class="hidden">
                                <div class="bg-green-50 border border-green-200 rounded-md p-4">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <h3 class="text-sm font-medium text-green-800">Import Completed</h3>
                                            <div class="mt-2 text-sm text-green-700">
                                                <p id="importResultText"></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Migrations Tab -->
                <div id="migrations" class="tab-content p-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <!-- Migration Status -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Migration Status</h3>
                            <div id="migrationStatus" class="space-y-4">
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-medium text-gray-700">Total Migrations</span>
                                        <span id="totalMigrations" class="text-sm text-gray-900">-</span>
                                    </div>
                                    <div class="flex items-center justify-between mt-2">
                                        <span class="text-sm font-medium text-gray-700">Completed</span>
                                        <span id="completedMigrations" class="text-sm text-green-600">-</span>
                                    </div>
                                    <div class="flex items-center justify-between mt-2">
                                        <span class="text-sm font-medium text-gray-700">Pending</span>
                                        <span id="pendingMigrations" class="text-sm text-yellow-600">-</span>
                                    </div>
                                </div>
                                
                                <button id="runMigrations" class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition-colors">
                                    Run Pending Migrations
                                </button>
                                
                                <button id="backupDatabase" class="w-full bg-yellow-600 text-white py-2 px-4 rounded-md hover:bg-yellow-700 transition-colors">
                                    Backup Database
                                </button>
                            </div>
                        </div>

                        <!-- Create Migration -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Create New Migration</h3>
                            <form id="createMigrationForm" class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Migration Name</label>
                                    <input type="text" name="name" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="add_new_table">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                                    <textarea name="description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Describe what this migration does"></textarea>
                                </div>
                                
                                <button type="submit" class="w-full bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700 transition-colors">
                                    Create Migration
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- History Tab -->
                <div id="history" class="tab-content p-6">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        <!-- Export History -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Export History</h3>
                            <div id="exportHistory" class="space-y-2">
                                <!-- Export history will be loaded here -->
                            </div>
                        </div>

                        <!-- Import History -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Import History</h3>
                            <div id="importHistory" class="space-y-2">
                                <!-- Import history will be loaded here -->
                            </div>
                        </div>

                        <!-- Migration History -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Migration History</h3>
                            <div id="migrationHistory" class="space-y-2">
                                <!-- Migration history will be loaded here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Tab functionality
        document.querySelectorAll('.tab-button').forEach(button => {
            button.addEventListener('click', () => {
                const tabId = button.dataset.tab;
                
                // Update button states
                document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
                button.classList.add('active');
                
                // Update content visibility
                document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
                document.getElementById(tabId).classList.add('active');
                
                // Load data for specific tabs
                if (tabId === 'history') {
                    loadHistory();
                } else if (tabId === 'migrations') {
                    loadMigrationStatus();
                }
            });
        });

        // Export format change handler
        document.getElementById('exportFormat').addEventListener('change', (e) => {
            const csvDataType = document.getElementById('csvDataType');
            if (e.target.value === 'csv') {
                csvDataType.classList.remove('hidden');
            } else {
                csvDataType.classList.add('hidden');
            }
        });

        // Import format change handler
        document.getElementById('importFormat').addEventListener('change', (e) => {
            const csvImportDataType = document.getElementById('csvImportDataType');
            if (e.target.value === 'csv') {
                csvImportDataType.classList.remove('hidden');
            } else {
                csvImportDataType.classList.add('hidden');
            }
        });

        // File upload handling
        const fileInput = document.getElementById('importFile');
        const dropZone = document.querySelector('.file-drop-zone');
        const dropZoneContent = document.getElementById('dropZoneContent');
        const selectedFile = document.getElementById('selectedFile');
        const fileName = document.getElementById('fileName');
        const removeFile = document.getElementById('removeFile');

        fileInput.addEventListener('change', handleFileSelect);
        dropZone.addEventListener('dragover', handleDragOver);
        dropZone.addEventListener('dragleave', handleDragLeave);
        dropZone.addEventListener('drop', handleDrop);
        removeFile.addEventListener('click', clearFile);

        function handleFileSelect(e) {
            const file = e.target.files[0];
            if (file) {
                showSelectedFile(file);
            }
        }

        function handleDragOver(e) {
            e.preventDefault();
            dropZone.classList.add('dragover');
        }

        function handleDragLeave(e) {
            e.preventDefault();
            dropZone.classList.remove('dragover');
        }

        function handleDrop(e) {
            e.preventDefault();
            dropZone.classList.remove('dragover');
            const file = e.dataTransfer.files[0];
            if (file) {
                fileInput.files = e.dataTransfer.files;
                showSelectedFile(file);
            }
        }

        function showSelectedFile(file) {
            fileName.textContent = file.name;
            dropZoneContent.classList.add('hidden');
            selectedFile.classList.remove('hidden');
        }

        function clearFile() {
            fileInput.value = '';
            dropZoneContent.classList.remove('hidden');
            selectedFile.classList.add('hidden');
        }

        // Export form submission
        document.getElementById('exportForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            const progressDiv = document.getElementById('exportProgress');
            const resultDiv = document.getElementById('exportResult');
            const progressBar = progressDiv.querySelector('.progress-bar');
            const statusText = document.getElementById('exportStatus');
            
            progressDiv.classList.remove('hidden');
            resultDiv.classList.add('hidden');
            
            try {
                // Simulate progress
                let progress = 0;
                const progressInterval = setInterval(() => {
                    progress += 10;
                    progressBar.style.width = progress + '%';
                    if (progress >= 90) {
                        clearInterval(progressInterval);
                    }
                }, 200);
                
                const response = await fetch('/data-management/export', {
                    method: 'POST',
                    body: formData
                });
                
                clearInterval(progressInterval);
                progressBar.style.width = '100%';
                statusText.textContent = 'Export completed!';
                
                const result = await response.json();
                
                if (result.success) {
                    setTimeout(() => {
                        progressDiv.classList.add('hidden');
                        resultDiv.classList.remove('hidden');
                        document.getElementById('exportResultText').textContent = 
                            `Exported ${result.data.filename} (${formatFileSize(result.data.size)})`;
                        document.getElementById('downloadLink').href = 
                            `/data-management/download-export?filename=${result.data.filename}`;
                    }, 1000);
                } else {
                    throw new Error(result.message);
                }
                
            } catch (error) {
                clearInterval(progressInterval);
                progressDiv.classList.add('hidden');
                alert('Export failed: ' + error.message);
            }
        });

        // Import form submission
        document.getElementById('importForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            if (!fileInput.files[0]) {
                alert('Please select a file to import');
                return;
            }
            
            const formData = new FormData(e.target);
            const progressDiv = document.getElementById('importProgress');
            const resultDiv = document.getElementById('importResult');
            const progressBar = progressDiv.querySelector('.progress-bar');
            const statusText = document.getElementById('importStatus');
            
            progressDiv.classList.remove('hidden');
            resultDiv.classList.add('hidden');
            
            try {
                // Simulate progress
                let progress = 0;
                const progressInterval = setInterval(() => {
                    progress += 10;
                    progressBar.style.width = progress + '%';
                    if (progress >= 90) {
                        clearInterval(progressInterval);
                    }
                }, 200);
                
                const response = await fetch('/data-management/import', {
                    method: 'POST',
                    body: formData
                });
                
                clearInterval(progressInterval);
                progressBar.style.width = '100%';
                statusText.textContent = 'Import completed!';
                
                const result = await response.json();
                
                if (result.success) {
                    setTimeout(() => {
                        progressDiv.classList.add('hidden');
                        resultDiv.classList.remove('hidden');
                        document.getElementById('importResultText').textContent = 
                            `Imported ${result.data.imported || result.data.imported.notes + result.data.imported.tasks + result.data.imported.tags} items successfully`;
                    }, 1000);
                } else {
                    throw new Error(result.message);
                }
                
            } catch (error) {
                clearInterval(progressInterval);
                progressDiv.classList.add('hidden');
                alert('Import failed: ' + error.message);
            }
        });

        // Migration functions
        document.getElementById('runMigrations').addEventListener('click', async () => {
            try {
                const response = await fetch('/data-management/run-migrations', {
                    method: 'POST'
                });
                const result = await response.json();
                
                if (result.success) {
                    alert(`Migrations completed! ${result.migrations_run} migrations were run.`);
                    loadMigrationStatus();
                } else {
                    alert('Migration failed: ' + (result.error || 'Unknown error'));
                }
            } catch (error) {
                alert('Migration failed: ' + error.message);
            }
        });

        document.getElementById('backupDatabase').addEventListener('click', async () => {
            try {
                const response = await fetch('/data-management/backup-database', {
                    method: 'POST'
                });
                const result = await response.json();
                
                if (result.success) {
                    alert(`Database backup completed! Size: ${formatFileSize(result.size)}`);
                } else {
                    alert('Backup failed: ' + (result.error || 'Unknown error'));
                }
            } catch (error) {
                alert('Backup failed: ' + error.message);
            }
        });

        // Create migration form
        document.getElementById('createMigrationForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            
            try {
                const response = await fetch('/data-management/create-migration', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                
                if (result.success) {
                    alert(`Migration created: ${result.filename}`);
                    e.target.reset();
                } else {
                    alert('Failed to create migration: ' + (result.error || 'Unknown error'));
                }
            } catch (error) {
                alert('Failed to create migration: ' + error.message);
            }
        });

        // Load functions
        async function loadMigrationStatus() {
            try {
                const response = await fetch('/data-management/migration-status');
                const result = await response.json();
                
                if (result.success) {
                    const data = result.data;
                    document.getElementById('totalMigrations').textContent = data.total_migrations;
                    document.getElementById('completedMigrations').textContent = data.completed_migrations;
                    document.getElementById('pendingMigrations').textContent = data.pending_migrations.length;
                }
            } catch (error) {
                console.error('Error loading migration status:', error);
            }
        }

        async function loadHistory() {
            // Load export history
            try {
                const response = await fetch('/data-management/export-history');
                const result = await response.json();
                
                if (result.success) {
                    const historyDiv = document.getElementById('exportHistory');
                    historyDiv.innerHTML = result.data.map(item => `
                        <div class="bg-gray-50 rounded p-3">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium">${item.filename}</span>
                                <span class="text-xs text-gray-500">${formatFileSize(item.file_size)}</span>
                            </div>
                            <div class="text-xs text-gray-500 mt-1">
                                ${new Date(item.created_at).toLocaleDateString()}
                            </div>
                        </div>
                    `).join('');
                }
            } catch (error) {
                console.error('Error loading export history:', error);
            }

            // Load import history
            try {
                const response = await fetch('/data-management/import-history');
                const result = await response.json();
                
                if (result.success) {
                    const historyDiv = document.getElementById('importHistory');
                    historyDiv.innerHTML = result.data.map(item => `
                        <div class="bg-gray-50 rounded p-3">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium">${item.filename}</span>
                                <span class="text-xs text-gray-500">${item.imported_count} items</span>
                            </div>
                            <div class="text-xs text-gray-500 mt-1">
                                ${new Date(item.created_at).toLocaleDateString()}
                            </div>
                        </div>
                    `).join('');
                }
            } catch (error) {
                console.error('Error loading import history:', error);
            }

            // Load migration history
            try {
                const response = await fetch('/data-management/migration-history');
                const result = await response.json();
                
                if (result.success) {
                    const historyDiv = document.getElementById('migrationHistory');
                    historyDiv.innerHTML = result.data.map(item => `
                        <div class="bg-gray-50 rounded p-3">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium">${item.migration_file}</span>
                                <span class="text-xs ${item.status === 'success' ? 'text-green-600' : 'text-red-600'}">${item.status}</span>
                            </div>
                            <div class="text-xs text-gray-500 mt-1">
                                ${new Date(item.created_at).toLocaleDateString()}
                            </div>
                        </div>
                    `).join('');
                }
            } catch (error) {
                console.error('Error loading migration history:', error);
            }
        }

        // Utility functions
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', () => {
            loadMigrationStatus();
        });
    </script>
</body>
</html>
