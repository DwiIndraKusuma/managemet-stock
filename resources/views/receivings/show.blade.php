@extends('layouts.app')

@section('title', 'Receiving Details')
@section('page-title', 'Receiving Details')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
        <div id="receiving-details" class="space-y-6">
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
            const container = document.getElementById('receiving-details');
            if (container) {
                container.innerHTML = '<p class="text-center text-red-500">Error: API service not available</p>';
            }
        }
        attempts++;
    }, 100);
}

const receivingId = {{ $id }};

async function loadReceiving() {
    const container = document.getElementById('receiving-details');
    
    try {
        if (!window.api) {
            throw new Error('API client not available');
        }

        const response = await window.api.get(`/receivings/${receivingId}`);
        console.log('Receiving response:', response.data);
        
        const rec = response.data?.data || response.data;
        
        if (!rec) {
            throw new Error('Receiving not found');
        }
        
        container.innerHTML = `
            <div class="mb-4">
                <a href="/receivings" class="text-indigo-600 hover:text-indigo-900 text-sm">← Back to Receivings</a>
            </div>
            <div class="mb-6">
                <h2 class="text-2xl font-bold">${escapeHtml(rec.receiving_number || '-')}</h2>
                <div class="mt-2 grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-600">PO Number</p>
                        <p class="text-gray-900 font-medium">${escapeHtml(rec.purchase_order?.po_number || '-')}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Received Date</p>
                        <p class="text-gray-900 font-medium">${rec.received_date ? new Date(rec.received_date).toLocaleDateString('id-ID') : '-'}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Status</p>
                        <span class="px-2 py-1 text-xs rounded ${
                            rec.status === 'completed' ? 'bg-green-100 text-green-800' :
                            rec.status === 'partial' ? 'bg-yellow-100 text-yellow-800' :
                            rec.status === 'open' ? 'bg-blue-100 text-blue-800' :
                            'bg-gray-100 text-gray-800'
                        }">${escapeHtml(rec.status || '-')}</span>
                    </div>
                </div>
            </div>
            
            ${rec.notes ? `
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700">Notes</label>
                <p class="mt-1 text-gray-900">${escapeHtml(rec.notes)}</p>
            </div>
            ` : ''}
            
            <div>
                <h3 class="text-lg font-semibold mb-4">Items Received</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Received</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Accepted</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rejected</th>
                                ${rec.receiving_items && rec.receiving_items.some(item => item.notes) ? '<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Notes</th>' : ''}
                                ${(rec.status === 'completed' || rec.status === 'partial') ? '<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>' : ''}
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            ${rec.receiving_items && Array.isArray(rec.receiving_items) && rec.receiving_items.length > 0
                                ? rec.receiving_items.map(item => `
                                    <tr>
                                        <td class="px-4 py-3 text-sm">${escapeHtml(item.purchase_order_item?.item?.name || '-')}</td>
                                        <td class="px-4 py-3 text-sm">${item.quantity_received || 0}</td>
                                        <td class="px-4 py-3 text-sm text-green-600 font-medium">${item.quantity_accepted || 0}</td>
                                        <td class="px-4 py-3 text-sm text-red-600 font-medium">${item.quantity_rejected || 0}</td>
                                        ${item.notes ? `<td class="px-4 py-3 text-sm">${escapeHtml(item.notes)}</td>` : ''}
                                        ${(rec.status === 'completed' || rec.status === 'partial') && item.quantity_accepted > 0 ? `
                                            <td class="px-4 py-3 text-sm">
                                                <button onclick="openReturnModal(${item.id}, '${escapeHtml(item.purchase_order_item?.item?.name || 'Item')}', ${item.quantity_accepted})" 
                                                    class="px-3 py-1 bg-red-600 text-white text-xs rounded hover:bg-red-700 transition">
                                                    Return Item
                                                </button>
                                            </td>
                                        ` : (rec.status === 'completed' || rec.status === 'partial') ? '<td class="px-4 py-3 text-sm">-</td>' : ''}
                                    </tr>
                                `).join('')
                                : '<tr><td colspan="4" class="px-4 py-3 text-center text-gray-500">No items found</td></tr>'
                            }
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Return Item Modal -->
            <div id="return-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
                <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                    <div class="mt-3">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Return Item</h3>
                        <form id="return-form" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Item</label>
                                <p id="return-item-name" class="mt-1 text-gray-900 font-medium"></p>
                            </div>
                            <div>
                                <label for="return-quantity" class="block text-sm font-medium text-gray-700">Quantity *</label>
                                <input type="number" id="return-quantity" name="quantity" min="1" required
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md">
                                <p id="return-quantity-hint" class="mt-1 text-xs text-gray-500"></p>
                                <p id="return-quantity-error" class="mt-1 text-sm text-red-600 hidden"></p>
                            </div>
                            <div>
                                <label for="return-reason" class="block text-sm font-medium text-gray-700">Reason *</label>
                                <textarea id="return-reason" name="reason" rows="3" required
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md"
                                    placeholder="Enter reason for return..."></textarea>
                                <p id="return-reason-error" class="mt-1 text-sm text-red-600 hidden"></p>
                            </div>
                            <div class="flex justify-end gap-3 pt-4">
                                <button type="button" onclick="closeReturnModal()" 
                                    class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                                    Cancel
                                </button>
                                <button type="submit" id="return-submit-btn"
                                    class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                                    <span id="return-submit-text">Return Item</span>
                                    <span id="return-submit-spinner" class="hidden">
                                        <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        `;
    } catch (error) {
        console.error('Error loading receiving:', error);
        const errorMsg = error.response?.data?.message || error.message || 'Failed to load receiving';
        
        // Handle 404 - redirect to list
        if (error.response?.status === 404) {
            if (window.showAlert) {
                window.showAlert('Receiving not found', 'error');
            }
            setTimeout(() => {
                window.location.href = '/receivings';
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
                <a href="/receivings" class="text-indigo-600 hover:text-indigo-900 text-sm">← Back to Receivings</a>
            </div>
            <p class="text-center text-red-500">Error: ${escapeHtml(errorMsg)}</p>
        `;
        
        if (window.showAlert) {
            window.showAlert(errorMsg, 'error');
        }
    }
}

let currentReturnItemId = null;
let currentMaxReturnable = 0;

window.openReturnModal = function(itemId, itemName, maxReturnable) {
    currentReturnItemId = itemId;
    currentMaxReturnable = maxReturnable;
    
    document.getElementById('return-item-name').textContent = itemName;
    document.getElementById('return-quantity').value = '';
    document.getElementById('return-quantity').max = maxReturnable;
    document.getElementById('return-quantity-hint').textContent = `Maximum returnable: ${maxReturnable}`;
    document.getElementById('return-reason').value = '';
    
    // Clear errors
    document.getElementById('return-quantity-error').classList.add('hidden');
    document.getElementById('return-reason-error').classList.add('hidden');
    document.getElementById('return-quantity').classList.remove('border-red-500');
    document.getElementById('return-reason').classList.remove('border-red-500');
    
    document.getElementById('return-modal').classList.remove('hidden');
};

window.closeReturnModal = function() {
    document.getElementById('return-modal').classList.add('hidden');
    currentReturnItemId = null;
    currentMaxReturnable = 0;
};

// Handle return form submission
document.addEventListener('DOMContentLoaded', function() {
    const returnForm = document.getElementById('return-form');
    if (returnForm) {
        returnForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitBtn = document.getElementById('return-submit-btn');
            const submitText = document.getElementById('return-submit-text');
            const submitSpinner = document.getElementById('return-submit-spinner');
            const quantityInput = document.getElementById('return-quantity');
            const reasonInput = document.getElementById('return-reason');
            const quantityError = document.getElementById('return-quantity-error');
            const reasonError = document.getElementById('return-reason-error');
            
            // Clear previous errors
            quantityError.classList.add('hidden');
            reasonError.classList.add('hidden');
            quantityInput.classList.remove('border-red-500');
            reasonInput.classList.remove('border-red-500');
            
            const quantity = parseInt(quantityInput.value);
            const reason = reasonInput.value.trim();
            
            // Client-side validation
            if (!quantity || quantity <= 0) {
                quantityError.textContent = 'Quantity must be greater than 0';
                quantityError.classList.remove('hidden');
                quantityInput.classList.add('border-red-500');
                return;
            }
            
            if (quantity > currentMaxReturnable) {
                quantityError.textContent = `Quantity cannot exceed maximum returnable (${currentMaxReturnable})`;
                quantityError.classList.remove('hidden');
                quantityInput.classList.add('border-red-500');
                return;
            }
            
            if (!reason) {
                reasonError.textContent = 'Reason is required';
                reasonError.classList.remove('hidden');
                reasonInput.classList.add('border-red-500');
                return;
            }
            
            // Disable submit button
            submitBtn.disabled = true;
            submitText.textContent = 'Returning...';
            submitSpinner.classList.remove('hidden');
            
            try {
                if (!window.api) {
                    throw new Error('API client not available');
                }
                
                const response = await window.api.post(`/receivings/${receivingId}/return-item`, {
                    receiving_item_id: currentReturnItemId,
                    quantity: quantity,
                    reason: reason
                });
                
                if (response.status >= 200 && response.status < 300) {
                    if (window.showAlert) {
                        window.showAlert('Item returned successfully', 'success');
                    }
                    
                    closeReturnModal();
                    loadReceiving(); // Reload receiving data
                } else {
                    throw new Error('Unexpected response status');
                }
            } catch (error) {
                console.error('Return item error:', error);
                
                let errorMsg = 'Failed to return item';
                let errors = {};
                
                if (error.response) {
                    errorMsg = error.response.data?.message || errorMsg;
                    errors = error.response.data?.errors || {};
                } else if (error.message) {
                    errorMsg = error.message;
                }
                
                // Show field errors
                if (errors.quantity) {
                    quantityError.textContent = Array.isArray(errors.quantity) ? errors.quantity[0] : errors.quantity;
                    quantityError.classList.remove('hidden');
                    quantityInput.classList.add('border-red-500');
                }
                
                if (errors.reason) {
                    reasonError.textContent = Array.isArray(errors.reason) ? errors.reason[0] : errors.reason;
                    reasonError.classList.remove('hidden');
                    reasonInput.classList.add('border-red-500');
                }
                
                if (errors.receiving_item_id) {
                    if (window.showAlert) {
                        window.showAlert(Array.isArray(errors.receiving_item_id) ? errors.receiving_item_id[0] : errors.receiving_item_id, 'error');
                    }
                } else if (errorMsg) {
                    if (window.showAlert) {
                        window.showAlert(errorMsg, 'error');
                    }
                }
            } finally {
                submitBtn.disabled = false;
                submitText.textContent = 'Return Item';
                submitSpinner.classList.add('hidden');
            }
        });
    }
});

// Wait for API then load receiving
setTimeout(() => {
    waitForAPI(loadReceiving);
}, 100);
</script>
@endsection
