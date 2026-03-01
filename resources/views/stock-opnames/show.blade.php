@extends('layouts.app')

@section('title', 'Opname Details')
@section('page-title', 'Stock Opname Details')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
        <div id="opname-details" class="space-y-6">
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
            const container = document.getElementById('opname-details');
            if (container) {
                container.innerHTML = '<p class="text-center text-red-500">Error: API service not available</p>';
            }
        }
        attempts++;
    }, 100);
}

const opnameId = {{ $id }};

async function loadOpname() {
    const container = document.getElementById('opname-details');
    
    try {
        if (!window.api) {
            throw new Error('API client not available');
        }

        const response = await window.api.get(`/stock-opnames/${opnameId}`);
        console.log('Opname response:', response.data);
        
        const opn = response.data?.data || response.data;
        const user = window.AuthService?.getCurrentUser();
        
        if (!opn) {
            throw new Error('Stock opname not found');
        }
        
        container.innerHTML = `
            <div class="mb-4">
                <a href="/stock-opnames" class="text-indigo-600 hover:text-indigo-900 text-sm">← Back to Stock Opnames</a>
            </div>
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h2 class="text-2xl font-bold">${escapeHtml(opn.opname_number || '-')}</h2>
                    <div class="mt-2 grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Opname Date</p>
                            <p class="text-gray-900 font-medium">${opn.opname_date ? new Date(opn.opname_date).toLocaleDateString('id-ID') : '-'}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Status</p>
                            <span class="px-2 py-1 text-xs rounded ${
                                opn.status === 'adjusted' ? 'bg-purple-100 text-purple-800' :
                                opn.status === 'approved' ? 'bg-green-100 text-green-800' :
                                opn.status === 'submitted' ? 'bg-blue-100 text-blue-800' :
                                opn.status === 'draft' ? 'bg-yellow-100 text-yellow-800' :
                                'bg-gray-100 text-gray-800'
                            }">${escapeHtml(opn.status || '-')}</span>
                        </div>
                    </div>
                </div>
                <div class="flex gap-2">
                    ${opn.status === 'draft' ? `<button onclick="submitOpname()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">Submit</button>` : ''}
                    ${opn.status === 'submitted' && (user?.role?.name === 'spv' || user?.role?.name === 'admin_gudang') ? `
                        <button onclick="approveOpname()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">Approve</button>
                    ` : ''}
                    ${opn.status === 'approved' && (user?.role?.name === 'spv' || user?.role?.name === 'admin_gudang') ? `
                        <button onclick="applyAdjustment()" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">Apply Adjustment</button>
                    ` : ''}
                </div>
            </div>
            
            ${opn.notes ? `
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700">Notes</label>
                <p class="mt-1 text-gray-900">${escapeHtml(opn.notes)}</p>
            </div>
            ` : ''}
            
            <div>
                <h3 class="text-lg font-semibold mb-4">Items Comparison</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">System Qty</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Physical Qty</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Difference</th>
                                ${opn.stock_opname_items && opn.stock_opname_items.some(item => item.notes) ? '<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Notes</th>' : ''}
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            ${opn.stock_opname_items && Array.isArray(opn.stock_opname_items) && opn.stock_opname_items.length > 0
                                ? opn.stock_opname_items.map(item => {
                                    const difference = (item.physical_quantity || 0) - (item.system_quantity || 0);
                                    return `
                                        <tr>
                                            <td class="px-4 py-3 text-sm">${escapeHtml(item.item?.name || '-')}</td>
                                            <td class="px-4 py-3 text-sm">${item.system_quantity || 0}</td>
                                            <td class="px-4 py-3 text-sm font-medium">${item.physical_quantity || 0}</td>
                                            <td class="px-4 py-3 text-sm font-semibold ${
                                                difference > 0 ? 'text-green-600' : 
                                                difference < 0 ? 'text-red-600' : 
                                                'text-gray-600'
                                            }">
                                                ${difference > 0 ? '+' : ''}${difference}
                                            </td>
                                            ${item.notes ? `<td class="px-4 py-3 text-sm">${escapeHtml(item.notes)}</td>` : ''}
                                        </tr>
                                    `;
                                }).join('')
                                : '<tr><td colspan="4" class="px-4 py-3 text-center text-gray-500">No items found</td></tr>'
                            }
                        </tbody>
                    </table>
                </div>
            </div>
        `;
    } catch (error) {
        console.error('Error loading opname:', error);
        const errorMsg = error.response?.data?.message || error.message || 'Failed to load stock opname';
        
        // Handle 404 - redirect to list
        if (error.response?.status === 404) {
            if (window.showAlert) {
                window.showAlert('Stock opname not found', 'error');
            }
            setTimeout(() => {
                window.location.href = '/stock-opnames';
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
                <a href="/stock-opnames" class="text-indigo-600 hover:text-indigo-900 text-sm">← Back to Stock Opnames</a>
            </div>
            <p class="text-center text-red-500">Error: ${escapeHtml(errorMsg)}</p>
        `;
        
        if (window.showAlert) {
            window.showAlert(errorMsg, 'error');
        }
    }
}

window.submitOpname = async function() {
    if (!confirm('Submit this opname?')) return;
    try {
        await window.api.post(`/stock-opnames/${opnameId}/submit`);
        if (window.showAlert) {
            window.showAlert('Opname submitted', 'success');
        }
        loadOpname();
    } catch (error) {
        const errorMsg = error.response?.data?.message || 'Failed to submit';
        if (window.showAlert) {
            window.showAlert(errorMsg, 'error');
        } else {
            alert(errorMsg);
        }
    }
};

window.approveOpname = async function() {
    if (!confirm('Approve this stock opname?')) return;
    try {
        await window.api.post(`/stock-opnames/${opnameId}/approve`);
        if (window.showAlert) {
            window.showAlert('Opname approved successfully', 'success');
        }
        loadOpname();
    } catch (error) {
        const errorMsg = error.response?.data?.message || 'Failed to approve';
        if (window.showAlert) {
            window.showAlert(errorMsg, 'error');
        } else {
            alert(errorMsg);
        }
    }
};

window.applyAdjustment = async function() {
    if (!confirm('Apply adjustments to inventory? This will update stock quantities.')) return;
    try {
        await window.api.post(`/stock-opnames/${opnameId}/apply-adjustment`);
        if (window.showAlert) {
            window.showAlert('Adjustment applied successfully', 'success');
        }
        loadOpname();
    } catch (error) {
        const errorMsg = error.response?.data?.message || 'Failed to apply adjustment';
        if (window.showAlert) {
            window.showAlert(errorMsg, 'error');
        } else {
            alert(errorMsg);
        }
    }
};

// Wait for API then load opname
setTimeout(() => {
    waitForAPI(loadOpname);
}, 100);
</script>
@endsection
