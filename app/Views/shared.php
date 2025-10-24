<?php
$pageTitle = "Shared Content";
include __DIR__ . '/partials/header.php';
?>

<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="py-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Shared Content</h1>
                        <p class="mt-2 text-gray-600">Manage your shared notes, tasks, and public links</p>
                    </div>
                    <button id="create-link-btn" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                        <i class="fas fa-link mr-2"></i>Create Share Link
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="space-y-8">
            <!-- Team Shared Content -->
            <div class="bg-white rounded-lg shadow-sm border">
                <div class="p-6 border-b">
                    <h2 class="text-xl font-semibold text-gray-900">Team Shared Content</h2>
                    <p class="text-gray-600 mt-1">Content shared within your teams</p>
                </div>
                
                <div class="p-6">
                    <?php if (empty($sharedNotes) && empty($sharedTasks)): ?>
                        <div class="text-center py-8">
                            <i class="fas fa-share-alt text-4xl text-gray-300 mb-4"></i>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No shared content</h3>
                            <p class="text-gray-600">Content shared by your team members will appear here</p>
                        </div>
                    <?php else: ?>
                        <div class="space-y-6">
                            <!-- Shared Notes -->
                            <?php if (!empty($sharedNotes)): ?>
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">Shared Notes</h3>
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                        <?php foreach ($sharedNotes as $note): ?>
                                            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                                                <div class="flex items-start justify-between mb-2">
                                                    <h4 class="font-medium text-gray-900 truncate"><?= htmlspecialchars($note['title']) ?></h4>
                                                    <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">
                                                        <?= ucfirst($note['permission']) ?>
                                                    </span>
                                                </div>
                                                <p class="text-sm text-gray-600 mb-2"><?= htmlspecialchars(substr($note['content'], 0, 100)) ?>...</p>
                                                <div class="flex items-center justify-between text-xs text-gray-500">
                                                    <span>Team: <?= htmlspecialchars($note['team_name']) ?></span>
                                                    <span><?= date('M j, Y', strtotime($note['shared_at'])) ?></span>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Shared Tasks -->
                            <?php if (!empty($sharedTasks)): ?>
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">Shared Tasks</h3>
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                        <?php foreach ($sharedTasks as $task): ?>
                                            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                                                <div class="flex items-start justify-between mb-2">
                                                    <h4 class="font-medium text-gray-900 truncate"><?= htmlspecialchars($task['title']) ?></h4>
                                                    <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">
                                                        <?= ucfirst($task['permission']) ?>
                                                    </span>
                                                </div>
                                                <p class="text-sm text-gray-600 mb-2"><?= htmlspecialchars(substr($task['description'], 0, 100)) ?>...</p>
                                                <div class="flex items-center justify-between text-xs text-gray-500">
                                                    <span>Team: <?= htmlspecialchars($task['team_name']) ?></span>
                                                    <span><?= date('M j, Y', strtotime($task['shared_at'])) ?></span>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Public Share Links -->
            <div class="bg-white rounded-lg shadow-sm border">
                <div class="p-6 border-b">
                    <h2 class="text-xl font-semibold text-gray-900">Public Share Links</h2>
                    <p class="text-gray-600 mt-1">Links you've created for sharing content publicly</p>
                </div>
                
                <div class="p-6">
                    <?php if (empty($sharedLinks)): ?>
                        <div class="text-center py-8">
                            <i class="fas fa-link text-4xl text-gray-300 mb-4"></i>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No share links</h3>
                            <p class="text-gray-600">Create share links to make your content accessible to anyone with the link</p>
                        </div>
                    <?php else: ?>
                        <div class="space-y-4">
                            <?php foreach ($sharedLinks as $link): ?>
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <div class="flex items-center justify-between mb-2">
                                        <div class="flex items-center space-x-3">
                                            <i class="fas fa-<?= $link['resource_type'] === 'note' ? 'sticky-note' : 'check-square' ?> text-blue-600"></i>
                                            <div>
                                                <h4 class="font-medium text-gray-900"><?= htmlspecialchars($link['resource_title']) ?></h4>
                                                <p class="text-sm text-gray-600"><?= ucfirst($link['resource_type']) ?> • <?= ucfirst($link['permission']) ?> access</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <span class="bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded-full">
                                                <?= $link['access_count'] ?> views
                                            </span>
                                            <button onclick="copyLink('<?= $this->getShareUrl($link['share_token']) ?>')" class="text-blue-600 hover:text-blue-800">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                            <button onclick="revokeLink(<?= $link['id'] ?>)" class="text-red-600 hover:text-red-800">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between text-xs text-gray-500">
                                        <span>Created <?= date('M j, Y', strtotime($link['created_at'])) ?></span>
                                        <?php if ($link['expires_at']): ?>
                                            <span>Expires <?= date('M j, Y', strtotime($link['expires_at'])) ?></span>
                                        <?php else: ?>
                                            <span>Never expires</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Share Link Modal -->
<div id="create-link-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Create Share Link</h3>
                    <button id="close-link-modal" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <form id="create-link-form">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Resource Type</label>
                        <select id="resource-type" name="resource_type" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select resource type</option>
                            <option value="note">Note</option>
                            <option value="task">Task</option>
                        </select>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Resource</label>
                        <select id="resource-id" name="resource_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select resource</option>
                        </select>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Permission</label>
                        <select name="permission" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="read">Read Only</option>
                            <option value="write">Read & Write</option>
                            <option value="admin">Full Access</option>
                        </select>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Expiration (Optional)</label>
                        <input type="datetime-local" name="expires_at" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Password Protection (Optional)</label>
                        <input type="password" name="password" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Enter password">
                    </div>
                    
                    <div class="flex space-x-3">
                        <button type="button" id="cancel-link" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg transition-colors">
                            Cancel
                        </button>
                        <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                            Create Link
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const createLinkBtn = document.getElementById('create-link-btn');
    const createLinkModal = document.getElementById('create-link-modal');
    const closeLinkModal = document.getElementById('close-link-modal');
    const cancelLink = document.getElementById('cancel-link');
    const createLinkForm = document.getElementById('create-link-form');
    const resourceType = document.getElementById('resource-type');
    const resourceId = document.getElementById('resource-id');

    // Open modal
    createLinkBtn.addEventListener('click', function() {
        createLinkModal.classList.remove('hidden');
    });

    // Close modal
    [closeLinkModal, cancelLink].forEach(btn => {
        btn.addEventListener('click', function() {
            createLinkModal.classList.add('hidden');
            createLinkForm.reset();
        });
    });

    // Close modal on outside click
    createLinkModal.addEventListener('click', function(e) {
        if (e.target === createLinkModal) {
            createLinkModal.classList.add('hidden');
            createLinkForm.reset();
        }
    });

    // Load resources when type changes
    resourceType.addEventListener('change', function() {
        const type = this.value;
        resourceId.innerHTML = '<option value="">Select resource</option>';
        
        if (type) {
            loadResources(type);
        }
    });

    // Create link form submission
    createLinkForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(createLinkForm);
        
        fetch('/shared/create-link', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error creating share link');
        });
    });
});

function loadResources(type) {
    fetch(`/api/resources/${type}`)
    .then(response => response.json())
    .then(data => {
        const resourceId = document.getElementById('resource-id');
        resourceId.innerHTML = '<option value="">Select resource</option>';
        
        data.forEach(resource => {
            const option = document.createElement('option');
            option.value = resource.id;
            option.textContent = resource.title;
            resourceId.appendChild(option);
        });
    })
    .catch(error => {
        console.error('Error loading resources:', error);
    });
}

function copyLink(url) {
    navigator.clipboard.writeText(url).then(() => {
        alert('Link copied to clipboard');
    });
}

function revokeLink(linkId) {
    if (confirm('Are you sure you want to revoke this share link?')) {
        const formData = new FormData();
        formData.append('link_id', linkId);
        
        fetch('/shared/revoke-link', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error revoking link');
        });
    }
}
</script>

<?php include __DIR__ . '/partials/footer.php'; ?>
