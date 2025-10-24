<?php
$pageTitle = "Teams";
include __DIR__ . '/partials/header.php';
?>

<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="py-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Teams</h1>
                        <p class="mt-2 text-gray-600">Collaborate with your team members on notes and tasks</p>
                    </div>
                    <button id="create-team-btn" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                        <i class="fas fa-plus mr-2"></i>Create Team
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <?php if (empty($teams)): ?>
            <!-- Empty State -->
            <div class="text-center py-12">
                <i class="fas fa-users text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">No teams yet</h3>
                <p class="text-gray-600 mb-6">Create your first team to start collaborating with others</p>
                <button id="create-first-team-btn" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg transition-colors">
                    <i class="fas fa-plus mr-2"></i>Create Your First Team
                </button>
            </div>
        <?php else: ?>
            <!-- Teams Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($teams as $team): ?>
                    <div class="bg-white rounded-lg shadow-sm border hover:shadow-md transition-shadow">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-900"><?= htmlspecialchars($team['name']) ?></h3>
                                <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">
                                    <?= ucfirst($team['role']) ?>
                                </span>
                            </div>
                            
                            <p class="text-gray-600 text-sm mb-4"><?= htmlspecialchars($team['description'] ?? 'No description') ?></p>
                            
                            <div class="flex items-center justify-between text-sm text-gray-500 mb-4">
                                <span>Created <?= date('M j, Y', strtotime($team['created_at'])) ?></span>
                                <span>Joined <?= date('M j, Y', strtotime($team['joined_at'])) ?></span>
                            </div>
                            
                            <div class="flex space-x-2">
                                <a href="/teams/<?= $team['id'] ?>" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-center px-3 py-2 rounded text-sm transition-colors">
                                    <i class="fas fa-eye mr-1"></i>View
                                </a>
                                <?php if ($team['role'] === 'admin'): ?>
                                    <button onclick="deleteTeam(<?= $team['id'] ?>)" class="bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded text-sm transition-colors">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Create Team Modal -->
<div id="create-team-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Create New Team</h3>
                    <button id="close-modal" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <form id="create-team-form">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Team Name</label>
                        <input type="text" id="team-name" name="name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Enter team name">
                    </div>
                    
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea id="team-description" name="description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Enter team description (optional)"></textarea>
                    </div>
                    
                    <div class="flex space-x-3">
                        <button type="button" id="cancel-create" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg transition-colors">
                            Cancel
                        </button>
                        <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                            Create Team
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const createTeamBtn = document.getElementById('create-team-btn');
    const createFirstTeamBtn = document.getElementById('create-first-team-btn');
    const createTeamModal = document.getElementById('create-team-modal');
    const closeModal = document.getElementById('close-modal');
    const cancelCreate = document.getElementById('cancel-create');
    const createTeamForm = document.getElementById('create-team-form');

    // Open modal
    [createTeamBtn, createFirstTeamBtn].forEach(btn => {
        btn.addEventListener('click', function() {
            createTeamModal.classList.remove('hidden');
        });
    });

    // Close modal
    [closeModal, cancelCreate].forEach(btn => {
        btn.addEventListener('click', function() {
            createTeamModal.classList.add('hidden');
            createTeamForm.reset();
        });
    });

    // Close modal on outside click
    createTeamModal.addEventListener('click', function(e) {
        if (e.target === createTeamModal) {
            createTeamModal.classList.add('hidden');
            createTeamForm.reset();
        }
    });

    // Create team form submission
    createTeamForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(createTeamForm);
        
        fetch('/teams/create', {
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
            alert('Error creating team');
        });
    });
});

function deleteTeam(teamId) {
    if (confirm('Are you sure you want to delete this team? This action cannot be undone.')) {
        const formData = new FormData();
        formData.append('team_id', teamId);
        
        fetch('/teams/delete', {
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
            alert('Error deleting team');
        });
    }
}
</script>

<?php include __DIR__ . '/partials/footer.php'; ?>
