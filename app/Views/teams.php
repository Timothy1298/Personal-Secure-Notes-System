<?php include __DIR__ . '/partials/header.php'; ?>

<!-- Main Content -->
<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Teams</h1>
        <p class="text-gray-600 dark:text-gray-300 mt-2">Collaborate with your team members on notes and tasks</p>
    </div>

    <!-- Create Team -->
    <div class="mb-8 bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Create New Team</h2>
        
        <form id="createTeamForm">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="teamName" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Team Name</label>
                    <input type="text" id="teamName" name="name" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white" placeholder="Enter team name" required>
                </div>
                
                <div>
                    <label for="teamDescription" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Description</label>
                    <input type="text" id="teamDescription" name="description" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white" placeholder="Enter team description">
                </div>
            </div>
            
            <div class="mt-4">
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg">
                    <i class="fas fa-plus mr-2"></i>Create Team
                </button>
            </div>
        </form>
    </div>

    <!-- Teams List -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Your Teams</h2>
        
        <div id="teamsList" class="space-y-4">
            <!-- Teams will be loaded here -->
        </div>
    </div>

    <!-- Team Members Modal -->
    <div id="teamMembersModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Team Members</h3>
                    
                    <div id="teamMembersList" class="space-y-3 mb-4">
                        <!-- Team members will be loaded here -->
                    </div>
                    
                    <div class="flex justify-end space-x-3">
                        <button id="closeMembersModal" class="px-4 py-2 text-gray-600 dark:text-gray-300 hover:text-gray-800 dark:hover:text-white">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Member Modal -->
    <div id="addMemberModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Add Team Member</h3>
                    
                    <form id="addMemberForm">
                        <input type="hidden" id="teamId" name="team_id">
                        
                        <div class="mb-4">
                            <label for="memberEmail" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Member Email</label>
                            <input type="email" id="memberEmail" name="email" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white" placeholder="Enter member email" required>
                        </div>
                        
                        <div class="mb-4">
                            <label for="memberRole" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Role</label>
                            <select id="memberRole" name="role" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                                <option value="member">Member</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        
                        <div class="flex justify-end space-x-3">
                            <button type="button" id="closeAddMemberModal" class="px-4 py-2 text-gray-600 dark:text-gray-300 hover:text-gray-800 dark:hover:text-white">
                                Cancel
                            </button>
                            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                                Add Member
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Create Team Form
    document.getElementById('createTeamForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        fetch('/teams/create', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Team created successfully!');
                this.reset();
                loadTeams();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while creating the team');
        });
    });

    // Add Member Form
    document.getElementById('addMemberForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        fetch('/teams/add-member', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Member added successfully!');
                this.reset();
                document.getElementById('addMemberModal').classList.add('hidden');
                loadTeamMembers(document.getElementById('teamId').value);
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while adding the member');
        });
    });

    // Modal controls
    document.getElementById('closeMembersModal').addEventListener('click', function() {
        document.getElementById('teamMembersModal').classList.add('hidden');
    });

    document.getElementById('closeAddMemberModal').addEventListener('click', function() {
        document.getElementById('addMemberModal').classList.add('hidden');
    });

    // Load teams on page load
    loadTeams();
});

function loadTeams() {
    fetch('/teams')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.teams) {
                displayTeams(data.teams);
            }
        })
        .catch(error => {
            console.error('Error loading teams:', error);
        });
}

function displayTeams(teams) {
    const container = document.getElementById('teamsList');
    container.innerHTML = '';
    
    if (teams.length === 0) {
        container.innerHTML = `
            <div class="text-center py-8">
                <i class="fas fa-users text-gray-400 text-4xl mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No teams yet</h3>
                <p class="text-gray-600 dark:text-gray-300">Create your first team to start collaborating!</p>
            </div>
        `;
        return;
    }
    
    teams.forEach(team => {
        container.innerHTML += `
            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">${team.name}</h3>
                        <p class="text-gray-600 dark:text-gray-300">${team.description || 'No description'}</p>
                        <p class="text-sm text-gray-500">${team.member_count} members</p>
                    </div>
                    <div class="flex space-x-2">
                        <button onclick="viewTeamMembers(${team.id})" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm">
                            <i class="fas fa-users mr-1"></i>Members
                        </button>
                        <button onclick="addTeamMember(${team.id})" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-sm">
                            <i class="fas fa-plus mr-1"></i>Add Member
                        </button>
                        <button onclick="deleteTeam(${team.id})" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm">
                            <i class="fas fa-trash mr-1"></i>Delete
                        </button>
                    </div>
                </div>
            </div>
        `;
    });
}

function viewTeamMembers(teamId) {
    loadTeamMembers(teamId);
    document.getElementById('teamMembersModal').classList.remove('hidden');
}

function addTeamMember(teamId) {
    document.getElementById('teamId').value = teamId;
    document.getElementById('addMemberModal').classList.remove('hidden');
}

function loadTeamMembers(teamId) {
    fetch(`/teams/${teamId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.members) {
                displayTeamMembers(data.members);
            }
        })
        .catch(error => {
            console.error('Error loading team members:', error);
        });
}

function displayTeamMembers(members) {
    const container = document.getElementById('teamMembersList');
    container.innerHTML = '';
    
    members.forEach(member => {
        container.innerHTML += `
            <div class="flex justify-between items-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                <div>
                    <p class="font-medium text-gray-900 dark:text-white">${member.email}</p>
                    <p class="text-sm text-gray-600 dark:text-gray-300">${member.role}</p>
                </div>
                <div class="flex space-x-2">
                    <button onclick="updateMemberRole(${member.id}, '${member.role === 'admin' ? 'member' : 'admin'}')" class="text-blue-500 hover:text-blue-700 text-sm">
                        ${member.role === 'admin' ? 'Make Member' : 'Make Admin'}
                    </button>
                    <button onclick="removeMember(${member.id})" class="text-red-500 hover:text-red-700 text-sm">
                        Remove
                    </button>
                </div>
            </div>
        `;
    });
}

function updateMemberRole(memberId, newRole) {
    fetch('/teams/update-member-role', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            member_id: memberId,
            role: newRole
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Member role updated successfully!');
            loadTeamMembers(document.getElementById('teamId').value);
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating member role');
    });
}

function removeMember(memberId) {
    if (!confirm('Are you sure you want to remove this member?')) {
        return;
    }
    
    fetch('/teams/remove-member', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            member_id: memberId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Member removed successfully!');
            loadTeamMembers(document.getElementById('teamId').value);
            loadTeams();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while removing the member');
    });
}

function deleteTeam(teamId) {
    if (!confirm('Are you sure you want to delete this team? This action cannot be undone.')) {
        return;
    }
    
    fetch('/teams/delete', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            team_id: teamId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Team deleted successfully!');
            loadTeams();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while deleting the team');
    });
}
</script>

<?php include __DIR__ . '/partials/footer.php'; ?>