@extends('layouts.app')

@section('title', 'Requests')
@section('page-title', 'Request Management')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Requests</h1>
        </div>
        <a id="create-request-btn" href="/requests/create" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
            + Create Request
        </a>
    </div>

    <div class="bg-white rounded-lg shadow p-4">
        <div class="flex gap-4">
            <select id="status-filter" class="px-4 py-2 border rounded-lg">
                <option value="">All Status</option>
                <option value="draft">Draft</option>
                <option value="submitted">Submitted</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
            </select>
            <button id="filter-btn" class="px-6 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">Filter</button>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Request Number</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody id="requests-table-body" class="bg-white divide-y divide-gray-200">
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
            const tbody = document.getElementById('requests-table-body');
            if (tbody) {
                tbody.innerHTML = '<tr><td colspan="5" class="px-6 py-4 text-center text-red-500">Error: API service not available</td></tr>';
            }
        }
        attempts++;
    }, 100);
}

let currentPage = 1;
let statusFilter = '';

async function loadRequests(page = 1) {
    const tbody = document.getElementById('requests-table-body');
    
    try {
        if (!window.api) {
            throw new Error('API client not available');
        }

        const params = new URLSearchParams({ page, per_page: 15 });
        if (statusFilter) params.append('status', statusFilter);
        
        const response = await window.api.get(`/requests?${params}`);
        console.log('Requests response:', response.data);
        
        // Handle different response structures
        let data = null;
        if (response.data?.data) {
            if (response.data.data.data) {
                data = response.data.data; // Paginated response
            } else if (Array.isArray(response.data.data)) {
                data = {
                    data: response.data.data,
                    current_page: 1,
                    last_page: 1
                };
            } else {
                data = response.data.data;
            }
        } else if (response.data) {
            data = response.data;
        }
        
        if (data && data.data && Array.isArray(data.data) && data.data.length > 0) {
            tbody.innerHTML = data.data.map(req => {
                const statusClass = req.status === 'approved' ? 'bg-green-100 text-green-800' :
                                   req.status === 'rejected' ? 'bg-red-100 text-red-800' :
                                   req.status === 'submitted' ? 'bg-yellow-100 text-yellow-800' :
                                   'bg-gray-100 text-gray-800';
                const date = req.created_at ? new Date(req.created_at).toLocaleDateString() : '-';
                
                return `
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">${escapeHtml(req.request_number || '-')}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">${escapeHtml(req.user?.name || '-')}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs rounded-full ${statusClass}">${escapeHtml(req.status || '-')}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">${escapeHtml(date)}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <a href="/requests/${req.id}" class="text-indigo-600 hover:text-indigo-900 mr-3">View</a>
                            ${req.status === 'draft' && (getUserRole() === 'technician' || getUserRole() === 'admin_gudang') ? `<a href="/requests/${req.id}/edit" class="text-yellow-600 hover:text-yellow-900 mr-3">Edit</a>` : ''}
                        </td>
                    </tr>
                `;
            }).join('');
        } else {
            tbody.innerHTML = '<tr><td colspan="5" class="px-6 py-4 text-center text-gray-500">No requests found</td></tr>';
        }
        currentPage = page;
    } catch (error) {
        console.error('Error loading requests:', error);
        const errorMsg = error.response?.data?.message || error.message || 'Failed to load requests';
        tbody.innerHTML = `<tr><td colspan="5" class="px-6 py-4 text-center text-red-500">Error: ${escapeHtml(errorMsg)}</td></tr>`;
        if (window.showAlert) {
            window.showAlert(errorMsg, 'error');
        }
    }
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
    document.getElementById('filter-btn').addEventListener('click', () => {
        statusFilter = document.getElementById('status-filter').value;
        loadRequests(1);
    });

    loadRequests();
});
</script>
@endsection
