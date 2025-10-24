<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Automation - Personal Notes System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex justify-between items-center">
                <h1 class="text-3xl font-bold text-gray-800">Automation Center</h1>
                <div class="flex space-x-4">
                    <button onclick="showCreateWorkflowModal()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition duration-200">
                        Create Workflow
                    </button>
                    <button onclick="showCreateTaskModal()" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg transition duration-200">
                        Schedule Task
                    </button>
                    <button onclick="showCreateWebhookModal()" class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded-lg transition duration-200">
                        Create Webhook
                    </button>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="bg-white rounded-lg shadow-md mb-6">
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8 px-6">
                    <button onclick="showTab('workflows')" id="workflows-tab" class="tab-button py-4 px-1 border-b-2 border-blue-500 text-blue-600 font-medium">
                        Workflows
                    </button>
                    <button onclick="showTab('tasks')" id="tasks-tab" class="tab-button py-4 px-1 border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium">
                        Scheduled Tasks
                    </button>
                    <button onclick="showTab('webhooks')" id="webhooks-tab" class="tab-button py-4 px-1 border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium">
                        Webhooks
                    </button>
                </nav>
            </div>
        </div>

        <!-- Workflows Tab -->
        <div id="workflows-content" class="tab-content">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">Workflows</h2>
                <div id="workflows-list" class="space-y-4">
                    <!-- Workflows will be loaded here -->
                </div>
            </div>
        </div>

        <!-- Scheduled Tasks Tab -->
        <div id="tasks-content" class="tab-content hidden">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">Scheduled Tasks</h2>
                <div id="tasks-list" class="space-y-4">
                    <!-- Tasks will be loaded here -->
                </div>
            </div>
        </div>

        <!-- Webhooks Tab -->
        <div id="webhooks-content" class="tab-content hidden">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">Webhooks</h2>
                <div id="webhooks-list" class="space-y-4">
                    <!-- Webhooks will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Create Workflow Modal -->
    <div id="create-workflow-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-screen overflow-y-auto">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Create Workflow</h3>
                    <form id="create-workflow-form">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Name</label>
                                <input type="text" name="name" required class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Description</label>
                                <textarea name="description" rows="3" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2"></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Steps</label>
                                <div id="workflow-steps" class="space-y-2">
                                    <!-- Steps will be added here -->
                                </div>
                                <button type="button" onclick="addWorkflowStep()" class="mt-2 bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm">
                                    Add Step
                                </button>
                            </div>
                        </div>
                        <div class="flex justify-end space-x-3 mt-6">
                            <button type="button" onclick="closeModal('create-workflow-modal')" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                                Cancel
                            </button>
                            <button type="submit" class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-md">
                                Create Workflow
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Task Modal -->
    <div id="create-task-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-lg w-full">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Schedule Task</h3>
                    <form id="create-task-form">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Name</label>
                                <input type="text" name="name" required class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Description</label>
                                <textarea name="description" rows="3" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2"></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Task Type</label>
                                <select name="task_type" required class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                                    <option value="workflow">Workflow</option>
                                    <option value="email_reminder">Email Reminder</option>
                                    <option value="data_cleanup">Data Cleanup</option>
                                    <option value="backup">Backup</option>
                                    <option value="report_generation">Report Generation</option>
                                    <option value="api_sync">API Sync</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Schedule Type</label>
                                <select name="schedule_type" required class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                                    <option value="once">Once</option>
                                    <option value="daily">Daily</option>
                                    <option value="weekly">Weekly</option>
                                    <option value="monthly">Monthly</option>
                                    <option value="interval">Interval</option>
                                </select>
                            </div>
                        </div>
                        <div class="flex justify-end space-x-3 mt-6">
                            <button type="button" onclick="closeModal('create-task-modal')" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                                Cancel
                            </button>
                            <button type="submit" class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-md">
                                Schedule Task
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Webhook Modal -->
    <div id="create-webhook-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-lg w-full">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Create Webhook</h3>
                    <form id="create-webhook-form">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Name</label>
                                <input type="text" name="name" required class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Description</label>
                                <textarea name="description" rows="3" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2"></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">URL</label>
                                <input type="url" name="url" required class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Method</label>
                                <select name="method" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                                    <option value="POST">POST</option>
                                    <option value="GET">GET</option>
                                    <option value="PUT">PUT</option>
                                    <option value="DELETE">DELETE</option>
                                    <option value="PATCH">PATCH</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Authentication Type</label>
                                <select name="authentication_type" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                                    <option value="none">None</option>
                                    <option value="bearer">Bearer Token</option>
                                    <option value="basic">Basic Auth</option>
                                    <option value="api_key">API Key</option>
                                </select>
                            </div>
                        </div>
                        <div class="flex justify-end space-x-3 mt-6">
                            <button type="button" onclick="closeModal('create-webhook-modal')" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                                Cancel
                            </button>
                            <button type="submit" class="px-4 py-2 bg-purple-500 hover:bg-purple-600 text-white rounded-md">
                                Create Webhook
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentTab = 'workflows';

        // Tab functionality
        function showTab(tabName) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.add('hidden');
            });

            // Remove active class from all tabs
            document.querySelectorAll('.tab-button').forEach(button => {
                button.classList.remove('border-blue-500', 'text-blue-600');
                button.classList.add('border-transparent', 'text-gray-500');
            });

            // Show selected tab content
            document.getElementById(tabName + '-content').classList.remove('hidden');

            // Add active class to selected tab
            const activeTab = document.getElementById(tabName + '-tab');
            activeTab.classList.remove('border-transparent', 'text-gray-500');
            activeTab.classList.add('border-blue-500', 'text-blue-600');

            currentTab = tabName;

            // Load data for the selected tab
            loadTabData(tabName);
        }

        // Load data for the selected tab
        function loadTabData(tabName) {
            switch (tabName) {
                case 'workflows':
                    loadWorkflows();
                    break;
                case 'tasks':
                    loadScheduledTasks();
                    break;
                case 'webhooks':
                    loadWebhooks();
                    break;
            }
        }

        // Load workflows
        function loadWorkflows() {
            fetch('/automation/workflows')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayWorkflows(data.workflows);
                    }
                })
                .catch(error => console.error('Error loading workflows:', error));
        }

        // Display workflows
        function displayWorkflows(workflows) {
            const container = document.getElementById('workflows-list');
            if (workflows.length === 0) {
                container.innerHTML = '<p class="text-gray-500">No workflows found. Create your first workflow to get started.</p>';
                return;
            }

            container.innerHTML = workflows.map(workflow => `
                <div class="border border-gray-200 rounded-lg p-4">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">${workflow.name}</h3>
                            <p class="text-gray-600">${workflow.description || 'No description'}</p>
                            <p class="text-sm text-gray-500">Created: ${new Date(workflow.created_at).toLocaleDateString()}</p>
                        </div>
                        <div class="flex space-x-2">
                            <button onclick="executeWorkflow('${workflow.id}')" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-sm">
                                Execute
                            </button>
                            <button onclick="editWorkflow('${workflow.id}')" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm">
                                Edit
                            </button>
                            <button onclick="deleteWorkflow('${workflow.id}')" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm">
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        // Load scheduled tasks
        function loadScheduledTasks() {
            fetch('/automation/tasks')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayScheduledTasks(data.tasks);
                    }
                })
                .catch(error => console.error('Error loading scheduled tasks:', error));
        }

        // Display scheduled tasks
        function displayScheduledTasks(tasks) {
            const container = document.getElementById('tasks-list');
            if (tasks.length === 0) {
                container.innerHTML = '<p class="text-gray-500">No scheduled tasks found. Create your first scheduled task to get started.</p>';
                return;
            }

            container.innerHTML = tasks.map(task => `
                <div class="border border-gray-200 rounded-lg p-4">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">${task.name}</h3>
                            <p class="text-gray-600">${task.description || 'No description'}</p>
                            <p class="text-sm text-gray-500">
                                Type: ${task.task_type} | 
                                Schedule: ${task.schedule_type} | 
                                Next: ${task.next_execution ? new Date(task.next_execution).toLocaleString() : 'N/A'}
                            </p>
                        </div>
                        <div class="flex space-x-2">
                            <button onclick="toggleTask('${task.id}', ${task.is_active})" class="bg-${task.is_active ? 'yellow' : 'green'}-500 hover:bg-${task.is_active ? 'yellow' : 'green'}-600 text-white px-3 py-1 rounded text-sm">
                                ${task.is_active ? 'Pause' : 'Resume'}
                            </button>
                            <button onclick="editTask('${task.id}')" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm">
                                Edit
                            </button>
                            <button onclick="deleteTask('${task.id}')" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm">
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        // Load webhooks
        function loadWebhooks() {
            fetch('/automation/webhooks')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayWebhooks(data.webhooks);
                    }
                })
                .catch(error => console.error('Error loading webhooks:', error));
        }

        // Display webhooks
        function displayWebhooks(webhooks) {
            const container = document.getElementById('webhooks-list');
            if (webhooks.length === 0) {
                container.innerHTML = '<p class="text-gray-500">No webhooks found. Create your first webhook to get started.</p>';
                return;
            }

            container.innerHTML = webhooks.map(webhook => `
                <div class="border border-gray-200 rounded-lg p-4">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">${webhook.name}</h3>
                            <p class="text-gray-600">${webhook.description || 'No description'}</p>
                            <p class="text-sm text-gray-500">
                                URL: ${webhook.url} | 
                                Method: ${webhook.method} | 
                                Status: ${webhook.is_active ? 'Active' : 'Inactive'}
                            </p>
                        </div>
                        <div class="flex space-x-2">
                            <button onclick="testWebhook('${webhook.webhook_id}')" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-sm">
                                Test
                            </button>
                            <button onclick="viewWebhookStats('${webhook.webhook_id}')" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm">
                                Stats
                            </button>
                            <button onclick="editWebhook('${webhook.webhook_id}')" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm">
                                Edit
                            </button>
                            <button onclick="deleteWebhook('${webhook.webhook_id}')" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm">
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        // Modal functions
        function showCreateWorkflowModal() {
            document.getElementById('create-workflow-modal').classList.remove('hidden');
        }

        function showCreateTaskModal() {
            document.getElementById('create-task-modal').classList.remove('hidden');
        }

        function showCreateWebhookModal() {
            document.getElementById('create-webhook-modal').classList.remove('hidden');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.add('hidden');
        }

        // Form submissions
        document.getElementById('create-workflow-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(e.target);
            const workflowData = {
                name: formData.get('name'),
                description: formData.get('description'),
                steps: [] // This would be populated from the steps UI
            };

            fetch('/automation/workflows', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(workflowData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeModal('create-workflow-modal');
                    loadWorkflows();
                } else {
                    alert('Error creating workflow: ' + data.message);
                }
            })
            .catch(error => console.error('Error creating workflow:', error));
        });

        document.getElementById('create-task-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(e.target);
            const taskData = {
                name: formData.get('name'),
                description: formData.get('description'),
                task_type: formData.get('task_type'),
                schedule_type: formData.get('schedule_type'),
                task_data: {},
                schedule_data: {}
            };

            fetch('/automation/tasks', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(taskData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeModal('create-task-modal');
                    loadScheduledTasks();
                } else {
                    alert('Error creating task: ' + data.message);
                }
            })
            .catch(error => console.error('Error creating task:', error));
        });

        document.getElementById('create-webhook-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(e.target);
            const webhookData = {
                name: formData.get('name'),
                description: formData.get('description'),
                url: formData.get('url'),
                method: formData.get('method'),
                authentication_type: formData.get('authentication_type'),
                authentication_data: {}
            };

            fetch('/automation/webhooks', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(webhookData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeModal('create-webhook-modal');
                    loadWebhooks();
                } else {
                    alert('Error creating webhook: ' + data.message);
                }
            })
            .catch(error => console.error('Error creating webhook:', error));
        });

        // Action functions
        function executeWorkflow(workflowId) {
            fetch('/automation/workflows/execute', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ workflow_id: workflowId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Workflow executed successfully!');
                } else {
                    alert('Error executing workflow: ' + data.error);
                }
            })
            .catch(error => console.error('Error executing workflow:', error));
        }

        function testWebhook(webhookId) {
            fetch(`/automation/webhooks/${webhookId}/test`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Webhook test successful!');
                    } else {
                        alert('Webhook test failed: ' + data.error);
                    }
                })
                .catch(error => console.error('Error testing webhook:', error));
        }

        function deleteWorkflow(workflowId) {
            if (confirm('Are you sure you want to delete this workflow?')) {
                // Implementation for deleting workflow
                console.log('Delete workflow:', workflowId);
            }
        }

        function deleteTask(taskId) {
            if (confirm('Are you sure you want to delete this scheduled task?')) {
                fetch(`/automation/tasks/${taskId}`, {
                    method: 'DELETE'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        loadScheduledTasks();
                    } else {
                        alert('Error deleting task: ' + data.message);
                    }
                })
                .catch(error => console.error('Error deleting task:', error));
            }
        }

        function deleteWebhook(webhookId) {
            if (confirm('Are you sure you want to delete this webhook?')) {
                fetch(`/automation/webhooks/${webhookId}`, {
                    method: 'DELETE'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        loadWebhooks();
                    } else {
                        alert('Error deleting webhook: ' + data.message);
                    }
                })
                .catch(error => console.error('Error deleting webhook:', error));
            }
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            loadTabData(currentTab);
        });
    </script>
</body>
</html>
