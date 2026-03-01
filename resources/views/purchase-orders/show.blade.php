@extends('layouts.app')

@section('title', 'PO Details')
@section('page-title', 'Purchase Order Details')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
        <div id="po-details" class="space-y-6">
            <p class="text-center text-gray-500">Loading...</p>
        </div>
    </div>
</div>

<script>
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
        if (window.api) {
            clearInterval(interval);
            callback();
        } else if (attempts >= maxAttempts) {
            clearInterval(interval);
            console.error('API not available after multiple attempts.');
            const container = document.getElementById('po-details');
            if (container) {
                container.innerHTML = '<p class="text-center text-red-500">Error: API service not available</p>';
            }
        }
        attempts++;
    }, 100);
}

const poId = {{ $id }};

async function loadPO() {
    const container = document.getElementById('po-details');
    
    try {
        if (!window.api) {
            throw new Error('API client not available');
        }

        const response = await window.api.get(`/purchase-orders/${poId}`);
        console.log('PO response:', response.data);
        
        const po = response.data?.data || response.data;
        const user = window.AuthService?.getCurrentUser();
        
        if (!po) {
            throw new Error('Purchase order not found');
        }
        
        container.innerHTML = `
            <div class="mb-4">
                <a href="/purchase-orders" class="text-indigo-600 hover:text-indigo-900 text-sm">← Back to Purchase Orders</a>
            </div>
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h2 class="text-2xl font-bold">${escapeHtml(po.po_number || '-')}</h2>
                    <p class="text-gray-600 mt-1">
                        Status: <span class="px-2 py-1 rounded ${
                            po.status === 'approved' || po.status === 'confirmed' ? 'bg-green-100 text-green-800' :
                            po.status === 'sent_to_vendor' ? 'bg-blue-100 text-blue-800' :
                            po.status === 'rejected' ? 'bg-red-100 text-red-800' :
                            po.status === 'pending_approval' ? 'bg-orange-100 text-orange-800' :
                            'bg-yellow-100 text-yellow-800'
                        }">${escapeHtml(po.status || '-')}</span>
                    </p>
                </div>
                <div class="flex gap-2 flex-wrap">
                    ${po.status === 'draft' ? `<a href="/purchase-orders/${po.id}/edit" class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition">Edit</a>` : ''}
                    ${po.status === 'draft' ? `<button onclick="submitPO()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">Submit</button>` : ''}
                    ${po.status === 'pending_approval' && (user?.role?.name === 'spv' || user?.role?.name === 'admin_gudang') ? `<button onclick="approvePO()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">Approve</button>` : ''}
                    ${po.status === 'approved' ? `<button onclick="sendToVendor()" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">Send to Vendor</button>` : ''}
                    ${po.status === 'sent_to_vendor' ? `<button onclick="confirmPO()" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">Confirm</button>` : ''}
                    ${po.status !== 'confirmed' && po.status !== 'rejected' ? `<button onclick="cancelPO()" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">Cancel</button>` : ''}
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Vendor</label>
                    <p class="mt-1 text-gray-900">${escapeHtml(po.vendor_name || '-')}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Total Amount</label>
                    <p class="mt-1 text-gray-900 font-semibold">Rp ${parseFloat(po.total_amount || 0).toLocaleString('id-ID')}</p>
                </div>
            </div>
            
            ${po.notes ? `
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700">Notes</label>
                <p class="mt-1 text-gray-900">${escapeHtml(po.notes)}</p>
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
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unit Price</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            ${po.purchase_order_items && Array.isArray(po.purchase_order_items) && po.purchase_order_items.length > 0
                                ? po.purchase_order_items.map(item => `
                                    <tr>
                                        <td class="px-4 py-3 text-sm">${escapeHtml(item.item?.name || '-')}</td>
                                        <td class="px-4 py-3 text-sm">${item.quantity || 0}</td>
                                        <td class="px-4 py-3 text-sm">Rp ${parseFloat(item.unit_price || 0).toLocaleString('id-ID')}</td>
                                        <td class="px-4 py-3 text-sm font-medium">Rp ${parseFloat(item.subtotal || 0).toLocaleString('id-ID')}</td>
                                    </tr>
                                `).join('')
                                : '<tr><td colspan="4" class="px-4 py-3 text-center text-gray-500">No items found</td></tr>'
                            }
                        </tbody>
                    </table>
                </div>
            </div>
        `;
    } catch (error) {
        console.error('Error loading PO:', error);
        const errorMsg = error.response?.data?.message || error.message || 'Failed to load purchase order';
        
        // Handle 404 - redirect to list
        if (error.response?.status === 404) {
            if (window.showAlert) {
                window.showAlert('Purchase order not found', 'error');
            }
            setTimeout(() => {
                window.location.href = '/purchase-orders';
            }, 2000);
            return;
        }
        
        // Handle 401 - redirect to login
        if (error.response?.status === 401) {
            if (window.showAlert) {
                window.showAlert('Session expired. Please login again.', 'error');
            }
            setTimeout(() => {
                window.location.href = '/login';
            }, 2000);
            return;
        }
        
        container.innerHTML = `
            <div class="mb-4">
                <a href="/purchase-orders" class="text-indigo-600 hover:text-indigo-900 text-sm">← Back to Purchase Orders</a>
            </div>
            <p class="text-center text-red-500">Error: ${escapeHtml(errorMsg)}</p>
        `;
        
        if (window.showAlert) {
            window.showAlert(errorMsg, 'error');
        }
    }
}

window.submitPO = async function() {
    if (!confirm('Submit this PO for approval?')) return;
    try {
        await window.api.post(`/purchase-orders/${poId}/submit`);
        if (window.showAlert) {
            window.showAlert('PO submitted for approval', 'success');
        }
        loadPO();
    } catch (error) {
        const errorMsg = error.response?.data?.message || 'Failed to submit';
        if (window.showAlert) {
            window.showAlert(errorMsg, 'error');
        } else {
            alert(errorMsg);
        }
    }
};

window.approvePO = async function() {
    if (!confirm('Approve this PO?')) return;
    try {
        await window.api.post(`/purchase-orders/${poId}/approve`);
        if (window.showAlert) {
            window.showAlert('PO approved', 'success');
        }
        loadPO();
    } catch (error) {
        const errorMsg = error.response?.data?.message || 'Failed to approve';
        if (window.showAlert) {
            window.showAlert(errorMsg, 'error');
        } else {
            alert(errorMsg);
        }
    }
};

window.sendToVendor = async function() {
    if (!confirm('Send this PO to vendor?')) return;
    try {
        await window.api.post(`/purchase-orders/${poId}/send-to-vendor`);
        if (window.showAlert) {
            window.showAlert('PO sent to vendor', 'success');
        }
        loadPO();
    } catch (error) {
        const errorMsg = error.response?.data?.message || 'Failed to send';
        if (window.showAlert) {
            window.showAlert(errorMsg, 'error');
        } else {
            alert(errorMsg);
        }
    }
};

window.confirmPO = async function() {
    if (!confirm('Confirm this PO?')) return;
    try {
        await window.api.post(`/purchase-orders/${poId}/confirm`);
        if (window.showAlert) {
            window.showAlert('PO confirmed', 'success');
        }
        loadPO();
    } catch (error) {
        const errorMsg = error.response?.data?.message || 'Failed to confirm';
        if (window.showAlert) {
            window.showAlert(errorMsg, 'error');
        } else {
            alert(errorMsg);
        }
    }
};

window.cancelPO = async function() {
    const reason = prompt('Reason for cancellation (optional):');
    if (reason === null) return; // User cancelled
    try {
        await window.api.post(`/purchase-orders/${poId}/cancel`, { reason: reason || null });
        if (window.showAlert) {
            window.showAlert('PO cancelled', 'success');
        }
        loadPO();
    } catch (error) {
        const errorMsg = error.response?.data?.message || 'Failed to cancel';
        if (window.showAlert) {
            window.showAlert(errorMsg, 'error');
        } else {
            alert(errorMsg);
        }
    }
};

// Wait for API then load PO
setTimeout(() => {
    waitForAPI(loadPO);
}, 100);
</script>
@endsection
