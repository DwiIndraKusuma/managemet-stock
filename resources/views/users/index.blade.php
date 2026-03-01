@extends('layouts.app')

@section('title', 'Users')
@section('page-title', 'User Management')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Users</h1>
        </div>
        <a href="/users/create" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
            + Add User
        </a>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody id="users-table-body" class="bg-white divide-y divide-gray-200">
                <tr><td colspan="5" class="px-6 py-4 text-center">Loading...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<script>
// Helper function to escape HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Wait for API to be available
function waitForAPI(callback, maxAttempts = 50) {
    let attempts = 0;
    const interval = setInterval(() => {
        if (window.api) {
            clearInterval(interval);
            callback();
        } else if (attempts >= maxAttempts) {
            clearInterval(interval);
            console.error('API not available after multiple attempts.');
            const tbody = document.getElementById('users-table-body');
            if (tbody) {
                tbody.innerHTML = '<tr><td colspan="5" class="px-6 py-4 text-center text-red-500">Error: API service not available</td></tr>';
            }
        }
        attempts++;
    }, 100);
}

async function loadUsers() {
    const tbody = document.getElementById('users-table-body');
    
    try {
        if (!window.api) {
            throw new Error('API client not available');
        }

        const response = await window.api.get('/users?per_page=15');
        console.log('Users response:', response.data);
        
        // Handle different response structures
        const users = response.data?.data?.data || response.data?.data || response.data || [];
        
        if (Array.isArray(users) && users.length > 0) {
            tbody.innerHTML = users.map(user => {
                const statusClass = user.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
                return `
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">${escapeHtml(user.name || '')}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">${escapeHtml(user.email || '')}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">${escapeHtml(user.role?.display_name || '-')}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs rounded-full ${statusClass}">${escapeHtml(user.status || '-')}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <a href="/users/${user.id}" class="text-indigo-600 hover:text-indigo-900 mr-3">View</a>
                            <a href="/users/${user.id}/edit" class="text-yellow-600 hover:text-yellow-900 mr-3">Edit</a>
                            ${user.status === 'active' ? 
                                `<button onclick="deactivateUser(${user.id})" class="text-red-600 hover:text-red-900 mr-3">Deactivate</button>` :
                                `<button onclick="activateUser(${user.id})" class="text-green-600 hover:text-green-900 mr-3">Activate</button>`
                            }
                        </td>
                    </tr>
                `;
            }).join('');
        } else {
            tbody.innerHTML = '<tr><td colspan="5" class="px-6 py-4 text-center text-gray-500">No users found</td></tr>';
        }
    } catch (error) {
        console.error('Error loading users:', error);
        const errorMsg = error.response?.data?.message || error.message || 'Failed to load users';
        tbody.innerHTML = `<tr><td colspan="5" class="px-6 py-4 text-center text-red-500">Error: ${escapeHtml(errorMsg)}</td></tr>`;
        if (window.showAlert) {
            window.showAlert(errorMsg, 'error');
        }
    }
}

async function activateUser(userId) {
    if (!confirm('Activate this user?')) return;
    try {
        await window.api.post(`/users/${userId}/activate`);
        if (window.showAlert) {
            window.showAlert('User activated successfully', 'success');
        }
        loadUsers();
    } catch (error) {
        const errorMsg = error.response?.data?.message || 'Failed to activate user';
        if (window.showAlert) {
            window.showAlert(errorMsg, 'error');
        }
    }
}

async function deactivateUser(userId) {
    if (!confirm('Deactivate this user? The user will not be able to login.')) return;
    try {
        await window.api.post(`/users/${userId}/deactivate`);
        if (window.showAlert) {
            window.showAlert('User deactivated successfully', 'success');
        }
        loadUsers();
    } catch (error) {
        const errorMsg = error.response?.data?.message || 'Failed to deactivate user';
        if (window.showAlert) {
            window.showAlert(errorMsg, 'error');
        }
    }
}

// Make functions globally available
window.activateUser = activateUser;
window.deactivateUser = deactivateUser;

waitForAPI(() => {
    loadUsers();
});
</script>
@endsection
