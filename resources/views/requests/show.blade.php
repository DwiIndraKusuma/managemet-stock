@extends('layouts.app')

@section('title', 'Request Details')
@section('page-title', 'Request Details')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
        <div id="request-details" class="space-y-6">
            <p class="text-center text-gray-500">Loading...</p>
        </div>
    </div>
</div>

<script >
const requestId = {{ $id }};

// Helper function to escape HTML
function escapeHtml(text) {
    if (text === null || text === undefined) return '';
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
            const container = document.getElementById('request-details');
            if (container) {
                container.innerHTML = '<p class="text-center text-red-500">Error: API service not available. Please refresh.</p>';
            }
        }
        attempts++;
    }, 100);
}

async function loadRequest() {
    try {
        const response = await window.api.get(`/requests/${requestId}`);
        const req = response.data?.data || response.data;
        const user = window.AuthService?.getCurrentUser();
        
        if (!req) {
            throw new Error('Request not found');
        }
        
        const container = document.getElementById('request-details');
        container.innerHTML = `
            <div class="mb-4">
                <a href="/requests" class="text-indigo-600 hover:text-indigo-900 text-sm">← Back to Requests</a>
            </div>
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h2 class="text-2xl font-bold">${escapeHtml(req.request_number || '-')}</h2>
                    <p class="text-gray-600 mt-1">Status: <span class="px-2 py-1 rounded ${
                        req.status === 'approved' ? 'bg-green-100 text-green-800' :
                        req.status === 'rejected' ? 'bg-red-100 text-red-800' :
                        req.status === 'submitted' ? 'bg-blue-100 text-blue-800' :
                        'bg-yellow-100 text-yellow-800'
                    }">${escapeHtml(req.status || '-')}</span></p>
                </div>
                <div class="flex gap-2 flex-wrap">
                    ${req.status === 'draft' ? `<a href="/requests/${req.id}/edit" class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition">Edit</a>` : ''}
                    ${req.status === 'draft' ? `<button onclick="submitRequest()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">Submit</button>` : ''}
                    ${req.status === 'submitted' && (user?.role?.name === 'spv' || user?.role?.name === 'admin_gudang') ? `
                        <button onclick="approveRequest()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">Approve</button>
                        <button onclick="rejectRequest()" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">Reject</button>
                    ` : ''}
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Requested By</label>
                    <p class="mt-1 text-gray-900">${escapeHtml(req.user?.name || '-')}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Date</label>
                    <p class="mt-1 text-gray-900">${new Date(req.created_at).toLocaleString('id-ID')}</p>
                </div>
            </div>
            
            ${req.notes ? `
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700">Notes</label>
                <p class="mt-1 text-gray-900">${escapeHtml(req.notes)}</p>
            </div>
            ` : ''}
            
            ${req.rejection_reason ? `
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 text-red-600">Rejection Reason</label>
                <p class="mt-1 text-red-600">${escapeHtml(req.rejection_reason)}</p>
            </div>
            ` : ''}
            
            <div>
                <h3 class="text-lg font-semibold mb-4">Items</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Notes</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            ${req.request_items && Array.isArray(req.request_items) && req.request_items.length > 0
                                ? req.request_items.map(item => `
                                    <tr>
                                        <td class="px-4 py-3 text-sm">${escapeHtml(item.item?.name || '-')}</td>
                                        <td class="px-4 py-3 text-sm">${item.quantity || 0}</td>
                                        <td class="px-4 py-3 text-sm">${escapeHtml(item.notes || '-')}</td>
                                    </tr>
                                `).join('')
                                : '<tr><td colspan="3" class="px-4 py-3 text-center text-gray-500">No items found</td></tr>'
                            }
                        </tbody>
                    </table>
                </div>
            </div>
        `;
    } catch (error) {
        console.error('Error loading request:', error);
        const errorMsg = error.response?.data?.message || error.message || 'Failed to load request details';
        if (window.showAlert) {
            window.showAlert(errorMsg, 'error');
        }
        const container = document.getElementById('request-details');
        if (container) {
            container.innerHTML = `<p class="text-center text-red-500">Error: ${escapeHtml(errorMsg)}. <a href="/requests" class="text-indigo-600 hover:underline">Go back to requests list</a></p>`;
        }
        if (error.response && error.response.status === 404) {
            setTimeout(() => {
                window.location.href = '/requests';
            }, 2000);
        } else if (error.response && error.response.status === 401) {
            window.location.href = '/login';
        }
    }
}

window.submitRequest = async function() {
    if (!confirm('Submit this request?')) return;
    try {
        await window.api.post(`/requests/${requestId}/submit`);
        if (window.showAlert) {
            window.showAlert('Request submitted', 'success');
        }
        loadRequest();
    } catch (error) {
        const errorMsg = error.response?.data?.message || 'Failed to submit';
        if (window.showAlert) {
            window.showAlert(errorMsg, 'error');
        } else {
            alert(errorMsg);
        }
    }
};

window.approveRequest = async function() {
    if (!confirm('Approve this request?')) return;
    try {
        await window.api.post(`/requests/${requestId}/approve`);
        if (window.showAlert) {
            window.showAlert('Request approved', 'success');
        }
        loadRequest();
    } catch (error) {
        const errorMsg = error.response?.data?.message || 'Failed to approve';
        if (window.showAlert) {
            window.showAlert(errorMsg, 'error');
        } else {
            alert(errorMsg);
        }
    }
};

window.rejectRequest = async function() {
    const reason = prompt('Rejection reason:');
    if (!reason) {
        if (window.showAlert) window.showAlert('Rejection cancelled or no reason provided.', 'info');
        return;
    }
    try {
        await window.api.post(`/requests/${requestId}/reject`, { reason });
        if (window.showAlert) {
            window.showAlert('Request rejected', 'success');
        }
        loadRequest();
    } catch (error) {
        const errorMsg = error.response?.data?.message || 'Failed to reject';
        if (window.showAlert) {
            window.showAlert(errorMsg, 'error');
        } else {
            alert(errorMsg);
        }
    }
};

document.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => {
        waitForAPI(loadRequest);
    }, 100);
});
</script>
@endsection
