<?php
$pageTitle = "Team: " . htmlspecialchars($team['name']);
include __DIR__ . '/partials/header.php';
?>

<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="py-6">
                <div class="flex items-center justify-between">
                    <div>
                        <nav class="flex items-center space-x-2 text-sm text-gray-500 mb-2">
                            <a href="/teams" class="hover:text-gray-700">Teams</a>
                            <i class="fas fa-chevron-right text-xs"></i>
                            <span class="text-gray-900"><?= htmlspecialchars($team['name']) ?></span>
                        </nav>
                        <h1 class="text-3xl font-bold text-gray-900"><?= htmlspecialchars($team['name']) ?></h1>
                        <p class="mt-2 text-gray-600"><?= htmlspecialchars($team['description'] ?? 'No description') ?></p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <button id="add-member-btn" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                            <i class="fas fa-user-plus mr-2"></i>Add Member
                        </button>
                        <?php if ($membership['role'] === 'admin'): ?>
                            <button id="delete-team-btn" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition-colors">
                                <i class="fas fa-trash mr-2"></i>Delete Team
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Team Members -->
                <div class="bg-white rounded-lg shadow-sm border">
                    <div class="p-6 border-b">
                        <h2 class="text-xl font-semibold text-gray-900">Team Members</h2>
                        <p class="text-gray-600 mt-1">Manage team members and their roles</p>
                    </div>
                    
                    <div class="p-6">
                        <div class="space-y-4">
                            <?php foreach ($members as $member): ?>
                                <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center text-white font-semibold">
                                            <?= strtoupper(substr($member['first_name'] ?: $member['username'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <h4 class="font-medium text-gray-900">
                                                <?= htmlspecialchars($member['first_name'] . ' ' . $member['last_name'] ?: $member['username']) ?>
                                            </h4>
                                            <p class="text-sm text-gray-600"><?= htmlspecialchars($member['email']) ?></p>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-3">
                                        <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">
                                            <?= ucfirst($member['role']) ?>
                                        </span>
                                        <?php if ($membership['role'] === 'admin' && $member['id'] != Session::get('user_id')): ?>
                                            <div class="flex space-x-1">
                                                <button onclick="updateMemberRole(<?= $member['id'] ?>, 'admin')" class="text-blue-600 hover:text-blue-800 text-sm">
                                                    <i class="fas fa-crown"></i>
                                                </button>
                                                <button onclick="updateMemberRole(<?= $member['id'] ?>, 'member')" class="text-gray-600 hover:text-gray-800 text-sm">
                                                    <i class="fas fa-user"></i>
                                                </button>
                                                <button onclick="removeMember(<?= $member['id'] ?>)" class="text-red-600 hover:text-red-800 text-sm">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Shared Content -->
                <div class="bg-white rounded-lg shadow-sm border">
                    <div class="p-6 border-b">
                        <h2 class="text-xl font-semibold text-gray-900">Shared Content</h2>
                        <p class="text-gray-600 mt-1">Content shared within this team</p>
                    </div>
                    
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Shared Notes -->
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Shared Notes</h3>
                                <?php if (empty($sharedNotes)): ?>
                                    <p class="text-gray-500 text-sm">No shared notes yet</p>
                                <?php else: ?>
                                    <div class="space-y-3">
                                        <?php foreach ($sharedNotes as $note): ?>
                                            <div class="border border-gray-200 rounded-lg p-3">
                                                <h4 class="font-medium text-gray-900 truncate"><?= htmlspecialchars($note['title']) ?></h4>
                                                <p class="text-sm text-gray-600 mt-1"><?= htmlspecialchars(substr($note['content'], 0, 100)) ?>...</p>
                                                <div class="flex items-center justify-between mt-2">
                                                    <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">
                                                        <?= ucfirst($note['permission']) ?>
                                                    </span>
                                                    <span class="text-xs text-gray-500"><?= date('M j, Y', strtotime($note['shared_at'])) ?></span>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Shared Tasks -->
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Shared Tasks</h3>
                                <?php if (empty($sharedTasks)): ?>
                                    <p class="text-gray-500 text-sm">No shared tasks yet</p>
                                <?php else: ?>
                                    <div class="space-y-3">
                                        <?php foreach ($sharedTasks as $task): ?>
                                            <div class="border border-gray-200 rounded-lg p-3">
                                                <h4 class="font-medium text-gray-900 truncate"><?= htmlspecialchars($task['title']) ?></h4>
                                                <p class="text-sm text-gray-600 mt-1"><?= htmlspecialchars(substr($task['description'], 0, 100)) ?>...</p>
                                                <div class="flex items-center justify-between mt-2">
                                                    <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">
                                                        <?= ucfirst($task['permission']) ?>
                                                    </span>
                                                    <span class="text-xs text-gray-500"><?= date('M j, Y', strtotime($task['shared_at'])) ?></span>
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

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Team Info -->
                <div class="bg-white rounded-lg shadow-sm border">
                    <div class="p-6 border-b">
                        <h3 class="text-lg font-semibold text-gray-900">Team Information</h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div>
                            <p class="text-sm font-medium text-gray-700">Created</p>
                            <p class="text-sm text-gray-600"><?= date('M j, Y', strtotime($team['created_at'])) ?></p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-700">Members</p>
                            <p class="text-sm text-gray-600"><?= count($members) ?> members</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-700">Your Role</p>
                            <p class="text-sm text-gray-600"><?= ucfirst($membership['role']) ?></p>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="bg-white rounded-lg shadow-sm border">
                    <div class="p-6 border-b">
                        <h3 class="text-lg font-semibold text-gray-900">Recent Activity</h3>
                    </div>
                    <div class="p-6">
                        <?php if (empty($activity)): ?>
                            <p class="text-gray-500 text-sm">No recent activity</p>
                        <?php else: ?>
                            <div class="space-y-3">
                                <?php foreach ($activity as $item): ?>
                                    <div class="flex items-start space-x-3">
                                        <div class="w-2 h-2 bg-blue-600 rounded-full mt-2"></div>
                                        <div>
                                            <p class="text-sm text-gray-900">
                                                <span class="font-medium"><?= htmlspecialchars($item['user']) ?></span>
                                                shared <?= $item['type'] === 'note_shared' ? 'note' : 'task' ?>
                                                <span class="font-medium"><?= htmlspecialchars($item['title']) ?></span>
                                            </p>
                                            <p class="text-xs text-gray-500"><?= date('M j, Y g:i A', strtotime($item['created_at'])) ?></p>
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
</div>

<!-- Add Member Modal -->
<div id="add-member-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Add Team Member</h3>
                    <button id="close-add-member-modal" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <form id="add-member-form">
                    <input type="hidden" name="team_id" value="<?= $team['id'] ?>">
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                        <input type="email" name="email" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Enter member's email">
                    </div>
                    
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                        <select name="role" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="member">Member</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    
                    <div class="flex space-x-3">
                        <button type="button" id="cancel-add-member" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg transition-colors">
                            Cancel
                        </button>
                        <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                            Add Member
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const addMemberBtn = document.getElementById('add-member-btn');
    const addMemberModal = document.getElementById('add-member-modal');
    const closeAddMemberModal = document.getElementById('close-add-member-modal');
    const cancelAddMember = document.getElementById('cancel-add-member');
    const addMemberForm = document.getElementById('add-member-form');

    // Open modal
    addMemberBtn.addEventListener('click', function() {
        addMemberModal.classList.remove('hidden');
    });

    // Close modal
    [closeAddMemberModal, cancelAddMember].forEach(btn => {
        btn.addEventListener('click', function() {
            addMemberModal.classList.add('hidden');
            addMemberForm.reset();
        });
    });

    // Close modal on outside click
    addMemberModal.addEventListener('click', function(e) {
        if (e.target === addMemberModal) {
            addMemberModal.classList.add('hidden');
            addMemberForm.reset();
        }
    });

    // Add member form submission
    addMemberForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(addMemberForm);
        
        fetch('/teams/add-member', {
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
            alert('Error adding member');
        });
    });
});

function updateMemberRole(userId, role) {
    const formData = new FormData();
    formData.append('team_id', <?= $team['id'] ?>);
    formData.append('user_id', userId);
    formData.append('role', role);
    
    fetch('/teams/update-member-role', {
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
        alert('Error updating member role');
    });
}

function removeMember(userId) {
    if (confirm('Are you sure you want to remove this member from the team?')) {
        const formData = new FormData();
        formData.append('team_id', <?= $team['id'] ?>);
        formData.append('user_id', userId);
        
        fetch('/teams/remove-member', {
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
            alert('Error removing member');
        });
    }
}
</script>

<?php include __DIR__ . '/partials/footer.php'; ?>
