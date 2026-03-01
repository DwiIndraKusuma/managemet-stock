@extends('layouts.app')

@section('title', 'Units')
@section('page-title', 'Units Management')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Units</h1>
        </div>
        <a id="create-unit-btn" href="/units/create" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
            + Add Unit
        </a>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody id="units-table-body" class="bg-white divide-y divide-gray-200">
                <tr><td colspan="3" class="px-6 py-4 text-center">Loading...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<script>
// api is available via window.api

async function loadUnits() {
    const tbody = document.getElementById('units-table-body');
    
    try {
        const response = await window.api.get('/units');
        console.log('Units response:', response.data);
        
        // Check response structure
        const units = response.data?.data || response.data || [];
        
        if (Array.isArray(units) && units.length > 0) {
            tbody.innerHTML = units.map(unit => `
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${escapeHtml(unit.name || '')}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${escapeHtml(unit.code || '-')}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        ${getUserRole() === 'admin_gudang' ? `
                            <a href="/units/${unit.id}/edit" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
                            <button onclick="deleteUnit(${unit.id})" class="text-red-600 hover:text-red-900">Delete</button>
                        ` : ''}
                    </td>
                </tr>
            `).join('');
        } else {
            tbody.innerHTML = '<tr><td colspan="3" class="px-6 py-4 text-center text-gray-500">No units found</td></tr>';
        }
    } catch (error) {
        console.error('Error loading units:', error);
        const errorMsg = error.response?.data?.message || error.message || 'Failed to load units';
        tbody.innerHTML = `<tr><td colspan="3" class="px-6 py-4 text-center text-red-500">Error: ${escapeHtml(errorMsg)}</td></tr>`;
        if (window.showAlert) {
            window.showAlert(errorMsg, 'error');
        }
    }
}

window.deleteUnit = async function(id) {
    if (!confirm('Are you sure you want to delete this unit?')) return;
    
    try {
        const response = await window.api.delete(`/units/${id}`);
        const message = response.data?.message || 'Unit deleted successfully';
        if (window.showAlert) {
            window.showAlert(message, 'success');
        }
        loadUnits();
    } catch (error) {
        console.error('Delete error:', error);
        const errorMsg = error.response?.data?.message || error.message || 'Failed to delete unit';
        if (window.showAlert) {
            window.showAlert(errorMsg, 'error');
        }
    }
};

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
            const tbody = document.getElementById('units-table-body');
            if (tbody) {
                tbody.innerHTML = '<tr><td colspan="3" class="px-6 py-4 text-center text-red-500">Error: API service not available</td></tr>';
            }
        }
        attempts++;
    }, 100);
}

// Helper function to get user role
function getUserRole() {
    try {
        const userStr = localStorage.getItem('user');
        if (userStr) {
            const user = JSON.parse(userStr);
            return user.role?.name || user.role_name || null;
        }
    } catch (error) {
        console.error('Error getting user role:', error);
    }
    return null;
}
window.getUserRole = getUserRole;

waitForAPI(() => {
    loadUnits();
});
</script>
@endsection
