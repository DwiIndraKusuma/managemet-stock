@extends('layouts.app')

@section('title', 'User Details')
@section('page-title', 'User Details')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
        <div id="user-details" class="space-y-6">
            <p class="text-center text-gray-500">Loading...</p>
        </div>
    </div>
</div>

<script >
const userId = {{ $id }};

// Helper function to escape HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Wait for API to be available
function waitForAPI(callback, maxAttempts = 100) {
    let attempts = 0;
    const interval = setInterval(() => {
        if (window.api && window.showAlert) {
            clearInterval(interval);
            callback();
        } else if (attempts >= maxAttempts) {
            clearInterval(interval);
            console.error('API or showAlert not available after multiple attempts.');
            const container = document.getElementById('user-details');
            if (container) {
                container.innerHTML = '<p class="text-center text-red-500">Error: API service not available. Please refresh.</p>';
            }
        }
        attempts++;
    }, 100);
}

async function loadUser() {
    try {
        const response = await window.api.get(`/users/${userId}`);
        const user = response.data?.data || response.data;
        
        const container = document.getElementById('user-details');
        container.innerHTML = `
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h2 class="text-2xl font-bold">${escapeHtml(user.name || '')}</h2>
                    <p class="text-gray-600">${escapeHtml(user.email || '')}</p>
                </div>
                <a href="/users/${user.id}/edit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Edit User</a>
            </div>
            
            <div class="grid grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Role</label>
                    <p class="mt-1">${escapeHtml(user.role?.display_name || '-')}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Status</label>
                    <p class="mt-1">
                        <span class="px-2 py-1 rounded ${user.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                            ${escapeHtml(user.status || '-')}
                        </span>
                    </p>
                </div>
            </div>
            
            <div class="flex gap-2">
                ${user.status === 'active' ? 
                    `<button onclick="deactivateUser()" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Deactivate User</button>` :
                    `<button onclick="activateUser()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">Activate User</button>`
                }
                <a href="/users" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">Back to Users</a>
            </div>
        `;
    } catch (error) {
        console.error('Error loading user:', error);
        const errorMsg = error.response?.data?.message || error.message || 'Failed to load user details';
        if (window.showAlert) {
            window.showAlert(errorMsg, 'error');
        }
        const container = document.getElementById('user-details');
        if (container) {
            container.innerHTML = `<p class="text-center text-red-500">Error: ${escapeHtml(errorMsg)}. <a href="/users" class="text-indigo-600 hover:underline">Go back to users list</a></p>`;
        }
        if (error.response && error.response.status === 404) {
            setTimeout(() => {
                window.location.href = '/users';
            }, 1500);
        } else if (error.response && error.response.status === 401) {
            window.location.href = '/login';
        }
    }
}

window.activateUser = async function() {
    if (!confirm('Activate this user?')) return;
    try {
        await window.api.post(`/users/${userId}/activate`);
        if (window.showAlert) {
            window.showAlert('User activated successfully', 'success');
        }
        loadUser();
    } catch (error) {
        const errorMsg = error.response?.data?.message || 'Failed to activate user';
        if (window.showAlert) {
            window.showAlert(errorMsg, 'error');
        } else {
            alert(errorMsg);
        }
    }
};

window.deactivateUser = async function() {
    if (!confirm('Deactivate this user? The user will not be able to login.')) return;
    try {
        await window.api.post(`/users/${userId}/deactivate`);
        if (window.showAlert) {
            window.showAlert('User deactivated successfully', 'success');
        }
        loadUser();
    } catch (error) {
        const errorMsg = error.response?.data?.message || 'Failed to deactivate user';
        if (window.showAlert) {
            window.showAlert(errorMsg, 'error');
        } else {
            alert(errorMsg);
        }
    }
};

document.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => {
        waitForAPI(loadUser);
    }, 100);
});
</script>
@endsection
