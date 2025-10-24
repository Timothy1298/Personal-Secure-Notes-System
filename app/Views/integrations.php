<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Integrations - Personal Notes System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex justify-between items-center">
                <h1 class="text-3xl font-bold text-gray-800">Third-Party Integrations</h1>
                <a href="/dashboard" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition duration-200">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
                </a>
            </div>
        </div>

        <!-- Success/Error Messages -->
        <?php if (Session::get('success')): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?= htmlspecialchars(Session::get('success')) ?>
                <?php Session::delete('success'); ?>
            </div>
        <?php endif; ?>

        <?php if (Session::get('error')): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?= htmlspecialchars(Session::get('error')) ?>
                <?php Session::delete('error'); ?>
            </div>
        <?php endif; ?>

        <!-- Integration Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Google Integration -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center mr-4">
                        <i class="fab fa-google text-red-600 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">Google</h3>
                        <p class="text-sm text-gray-600">Drive, Calendar, Gmail</p>
                    </div>
                </div>
                
                <div class="mb-4">
                    <div class="flex items-center mb-2">
                        <span class="text-sm text-gray-600">Status:</span>
                        <span class="ml-2 px-2 py-1 rounded-full text-xs font-medium <?= $googleConnected ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' ?>">
                            <?= $googleConnected ? 'Connected' : 'Not Connected' ?>
                        </span>
                    </div>
                </div>

                <div class="space-y-2">
                    <?php if ($googleConnected): ?>
                        <button onclick="getGoogleProfile()" class="w-full bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded text-sm transition duration-200">
                            <i class="fas fa-user mr-2"></i>View Profile
                        </button>
                        <button onclick="disconnectGoogle()" class="w-full bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded text-sm transition duration-200">
                            <i class="fas fa-unlink mr-2"></i>Disconnect
                        </button>
                    <?php else: ?>
                        <a href="<?= $this->googleIntegration->getAuthUrl(Session::get('user_id')) ?>" class="w-full bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded text-sm transition duration-200 inline-block text-center">
                            <i class="fab fa-google mr-2"></i>Connect Google
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Microsoft Integration -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mr-4">
                        <i class="fab fa-microsoft text-blue-600 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">Microsoft</h3>
                        <p class="text-sm text-gray-600">OneDrive, Outlook, Teams</p>
                    </div>
                </div>
                
                <div class="mb-4">
                    <div class="flex items-center mb-2">
                        <span class="text-sm text-gray-600">Status:</span>
                        <span class="ml-2 px-2 py-1 rounded-full text-xs font-medium <?= $microsoftConnected ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' ?>">
                            <?= $microsoftConnected ? 'Connected' : 'Not Connected' ?>
                        </span>
                    </div>
                </div>

                <div class="space-y-2">
                    <?php if ($microsoftConnected): ?>
                        <button onclick="getMicrosoftProfile()" class="w-full bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded text-sm transition duration-200">
                            <i class="fas fa-user mr-2"></i>View Profile
                        </button>
                        <button onclick="disconnectMicrosoft()" class="w-full bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded text-sm transition duration-200">
                            <i class="fas fa-unlink mr-2"></i>Disconnect
                        </button>
                    <?php else: ?>
                        <a href="<?= $this->microsoftIntegration->getAuthUrl(Session::get('user_id')) ?>" class="w-full bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded text-sm transition duration-200 inline-block text-center">
                            <i class="fab fa-microsoft mr-2"></i>Connect Microsoft
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Slack Integration -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mr-4">
                        <i class="fab fa-slack text-purple-600 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">Slack</h3>
                        <p class="text-sm text-gray-600">Messages, Files, Reminders</p>
                    </div>
                </div>
                
                <div class="mb-4">
                    <div class="flex items-center mb-2">
                        <span class="text-sm text-gray-600">Status:</span>
                        <span class="ml-2 px-2 py-1 rounded-full text-xs font-medium <?= $slackConnected ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' ?>">
                            <?= $slackConnected ? 'Connected' : 'Not Connected' ?>
                        </span>
                    </div>
                </div>

                <div class="space-y-2">
                    <?php if ($slackConnected): ?>
                        <button onclick="getSlackTeamInfo()" class="w-full bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded text-sm transition duration-200">
                            <i class="fas fa-users mr-2"></i>View Team
                        </button>
                        <button onclick="getSlackChannels()" class="w-full bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded text-sm transition duration-200">
                            <i class="fas fa-comments mr-2"></i>View Channels
                        </button>
                        <button onclick="disconnectSlack()" class="w-full bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded text-sm transition duration-200">
                            <i class="fas fa-unlink mr-2"></i>Disconnect
                        </button>
                    <?php else: ?>
                        <a href="<?= $this->slackIntegration->getAuthUrl(Session::get('user_id')) ?>" class="w-full bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded text-sm transition duration-200 inline-block text-center">
                            <i class="fab fa-slack mr-2"></i>Connect Slack
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Integration Features -->
        <div class="mt-8 bg-white rounded-lg shadow-md p-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Integration Features</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Google Features -->
                <div class="border border-gray-200 rounded-lg p-4">
                    <h3 class="text-lg font-semibold text-gray-800 mb-3">
                        <i class="fab fa-google text-red-600 mr-2"></i>Google
                    </h3>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Upload files to Google Drive</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Create calendar events</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Access user profile</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Sync with Gmail</li>
                    </ul>
                </div>

                <!-- Microsoft Features -->
                <div class="border border-gray-200 rounded-lg p-4">
                    <h3 class="text-lg font-semibold text-gray-800 mb-3">
                        <i class="fab fa-microsoft text-blue-600 mr-2"></i>Microsoft
                    </h3>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Upload files to OneDrive</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Create Outlook events</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Send emails via Outlook</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Access user profile</li>
                    </ul>
                </div>

                <!-- Slack Features -->
                <div class="border border-gray-200 rounded-lg p-4">
                    <h3 class="text-lg font-semibold text-gray-800 mb-3">
                        <i class="fab fa-slack text-purple-600 mr-2"></i>Slack
                    </h3>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Send messages to channels</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Upload files to Slack</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Create reminders</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Access team information</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="mt-8 bg-white rounded-lg shadow-md p-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Quick Actions</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <button onclick="showUploadModal()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-3 rounded-lg transition duration-200">
                    <i class="fas fa-upload mr-2"></i>Upload File
                </button>
                
                <button onclick="showCalendarModal()" class="bg-green-500 hover:bg-green-600 text-white px-4 py-3 rounded-lg transition duration-200">
                    <i class="fas fa-calendar-plus mr-2"></i>Create Event
                </button>
                
                <button onclick="showSlackModal()" class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-3 rounded-lg transition duration-200">
                    <i class="fab fa-slack mr-2"></i>Send Message
                </button>
                
                <button onclick="showEmailModal()" class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-3 rounded-lg transition duration-200">
                    <i class="fas fa-envelope mr-2"></i>Send Email
                </button>
            </div>
        </div>
    </div>

    <!-- Upload File Modal -->
    <div id="upload-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Upload File</h3>
                    <form id="upload-form">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Provider</label>
                                <select name="provider" required class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                                    <option value="google">Google Drive</option>
                                    <option value="microsoft">OneDrive</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">File</label>
                                <input type="file" name="file" required class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">File Name</label>
                                <input type="text" name="file_name" required class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                            </div>
                        </div>
                        <div class="flex justify-end space-x-3 mt-6">
                            <button type="button" onclick="closeModal('upload-modal')" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                                Cancel
                            </button>
                            <button type="submit" class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-md">
                                Upload
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Calendar Event Modal -->
    <div id="calendar-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Create Calendar Event</h3>
                    <form id="calendar-form">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Provider</label>
                                <select name="provider" required class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                                    <option value="google">Google Calendar</option>
                                    <option value="microsoft">Outlook Calendar</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Event Title</label>
                                <input type="text" name="title" required class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Start Time</label>
                                <input type="datetime-local" name="start_time" required class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">End Time</label>
                                <input type="datetime-local" name="end_time" required class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Description</label>
                                <textarea name="description" rows="3" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2"></textarea>
                            </div>
                        </div>
                        <div class="flex justify-end space-x-3 mt-6">
                            <button type="button" onclick="closeModal('calendar-modal')" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                                Cancel
                            </button>
                            <button type="submit" class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-md">
                                Create Event
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Slack Message Modal -->
    <div id="slack-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Send Slack Message</h3>
                    <form id="slack-form">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Channel</label>
                                <input type="text" name="channel" placeholder="#general" required class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Message</label>
                                <textarea name="message" rows="4" required class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2"></textarea>
                            </div>
                        </div>
                        <div class="flex justify-end space-x-3 mt-6">
                            <button type="button" onclick="closeModal('slack-modal')" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                                Cancel
                            </button>
                            <button type="submit" class="px-4 py-2 bg-purple-500 hover:bg-purple-600 text-white rounded-md">
                                Send Message
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Modal functions
        function showUploadModal() {
            document.getElementById('upload-modal').classList.remove('hidden');
        }

        function showCalendarModal() {
            document.getElementById('calendar-modal').classList.remove('hidden');
        }

        function showSlackModal() {
            document.getElementById('slack-modal').classList.remove('hidden');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.add('hidden');
        }

        // Integration functions
        function getGoogleProfile() {
            fetch('/integrations/google/profile')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Google Profile: ' + JSON.stringify(data.profile, null, 2));
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        function getMicrosoftProfile() {
            fetch('/integrations/microsoft/profile')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Microsoft Profile: ' + JSON.stringify(data.profile, null, 2));
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        function getSlackTeamInfo() {
            fetch('/integrations/slack/team')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Slack Team: ' + JSON.stringify(data.team, null, 2));
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        function getSlackChannels() {
            fetch('/integrations/slack/channels')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Slack Channels: ' + JSON.stringify(data.channels, null, 2));
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        function disconnectGoogle() {
            if (confirm('Are you sure you want to disconnect Google integration?')) {
                fetch('/integrations/google/disconnect', { method: 'POST' })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert('Error: ' + data.message);
                        }
                    })
                    .catch(error => console.error('Error:', error));
            }
        }

        function disconnectMicrosoft() {
            if (confirm('Are you sure you want to disconnect Microsoft integration?')) {
                fetch('/integrations/microsoft/disconnect', { method: 'POST' })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert('Error: ' + data.message);
                        }
                    })
                    .catch(error => console.error('Error:', error));
            }
        }

        function disconnectSlack() {
            if (confirm('Are you sure you want to disconnect Slack integration?')) {
                fetch('/integrations/slack/disconnect', { method: 'POST' })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert('Error: ' + data.message);
                        }
                    })
                    .catch(error => console.error('Error:', error));
            }
        }

        // Form submissions
        document.getElementById('upload-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(e.target);
            
            // This would need to be implemented with actual file upload logic
            alert('File upload functionality would be implemented here');
            closeModal('upload-modal');
        });

        document.getElementById('calendar-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(e.target);
            const eventData = {
                provider: formData.get('provider'),
                event_data: {
                    summary: formData.get('title'),
                    start: { dateTime: formData.get('start_time') },
                    end: { dateTime: formData.get('end_time') },
                    description: formData.get('description')
                }
            };

            fetch('/integrations/calendar/event', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(eventData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Calendar event created successfully!');
                    closeModal('calendar-modal');
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        });

        document.getElementById('slack-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(e.target);
            const messageData = {
                channel: formData.get('channel'),
                message: formData.get('message')
            };

            fetch('/integrations/slack/message', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(messageData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Slack message sent successfully!');
                    closeModal('slack-modal');
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        });
    </script>
</body>
</html>
