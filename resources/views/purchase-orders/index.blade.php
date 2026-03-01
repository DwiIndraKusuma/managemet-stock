@extends('layouts.app')

@section('title', 'Purchase Orders')
@section('page-title', 'Purchase Orders')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Purchase Orders</h1>
        </div>
        <a id="create-po-btn" href="/purchase-orders/create" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
            + Create PO
        </a>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">PO Number</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vendor</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody id="po-table-body" class="bg-white divide-y divide-gray-200">
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
            const tbody = document.getElementById('po-table-body');
            if (tbody) {
                tbody.innerHTML = '<tr><td colspan="5" class="px-6 py-4 text-center text-red-500">Error: API service not available</td></tr>';
            }
        }
        attempts++;
    }, 100);
}

async function loadPOs() {
    const tbody = document.getElementById('po-table-body');
    
    try {
        if (!window.api) {
            throw new Error('API client not available');
        }

        const response = await window.api.get('/purchase-orders?per_page=15');
        console.log('POs response:', response.data);
        
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
            tbody.innerHTML = data.data.map(po => {
                const amount = po.total_amount ? parseFloat(po.total_amount).toLocaleString() : '0';
                return `
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">${escapeHtml(po.po_number || '-')}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">${escapeHtml(po.vendor_name || '-')}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">Rp ${amount}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs rounded-full ${
                                po.status === 'approved' || po.status === 'confirmed' ? 'bg-green-100 text-green-800' :
                                po.status === 'sent_to_vendor' ? 'bg-blue-100 text-blue-800' :
                                po.status === 'rejected' ? 'bg-red-100 text-red-800' :
                                po.status === 'pending_approval' ? 'bg-orange-100 text-orange-800' :
                                'bg-yellow-100 text-yellow-800'
                            }">${escapeHtml(po.status || '-')}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <a href="/purchase-orders/${po.id}" class="text-indigo-600 hover:text-indigo-900 mr-3">View</a>
                            ${po.status === 'draft' && getUserRole() === 'admin_gudang' ? `<a href="/purchase-orders/${po.id}/edit" class="text-yellow-600 hover:text-yellow-900">Edit</a>` : ''}
                        </td>
                    </tr>
                `;
            }).join('');
        } else {
            tbody.innerHTML = '<tr><td colspan="5" class="px-6 py-4 text-center text-gray-500">No purchase orders found</td></tr>';
        }
    } catch (error) {
        console.error('Error loading POs:', error);
        const errorMsg = error.response?.data?.message || error.message || 'Failed to load purchase orders';
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
    loadPOs();
});
</script>
@endsection
