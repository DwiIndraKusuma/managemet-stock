@extends('layouts.app')

@section('title', 'Create Receiving')
@section('page-title', 'Create Receiving')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
        <form id="receiving-form" class="space-y-6">
            <!-- Purchase Order -->
            <div>
                <label for="purchase_order_id" class="block text-sm font-medium text-gray-700">Purchase Order *</label>
                <select id="purchase_order_id" name="purchase_order_id" required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Loading...</option>
                </select>
                <p class="mt-1 text-sm text-red-600 hidden" id="purchase_order_id-error"></p>
            </div>

            <!-- Received Date -->
            <div>
                <label for="received_date" class="block text-sm font-medium text-gray-700">Received Date *</label>
                <input type="date" id="received_date" name="received_date" required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                    value="{{ date('Y-m-d') }}">
                <p class="mt-1 text-sm text-red-600 hidden" id="received_date-error"></p>
            </div>

            <!-- Notes -->
            <div>
                <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                <textarea id="notes" name="notes" rows="3"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                <p class="mt-1 text-sm text-red-600 hidden" id="notes-error"></p>
            </div>

            <!-- Items Received -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-4">Items Received</label>
                <div id="items-container" class="space-y-4">
                    <p class="text-center text-gray-500 py-4">Select a Purchase Order to load items</p>
                </div>
                <p class="mt-1 text-sm text-red-600 hidden" id="items-error"></p>
            </div>

            <!-- Buttons -->
            <div class="flex justify-end gap-4">
                <a href="/receivings" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition">
                    Cancel
                </a>
                <button type="submit" id="submit-btn" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                    <span id="submit-text">Create Receiving</span>
                    <span id="submit-spinner" class="hidden">
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('receiving-form');
    if (!form) return;

    const submitBtn = document.getElementById('submit-btn');
    const submitText = document.getElementById('submit-text');
    const submitSpinner = document.getElementById('submit-spinner');
    let isSubmitting = false;

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
                const select = document.getElementById('purchase_order_id');
                if (select) {
                    select.innerHTML = '<option value="">Error: API not available</option>';
                    select.disabled = true;
                }
                if (window.showAlert) {
                    window.showAlert('Failed to load form options. Please refresh the page.', 'error');
                }
            }
            attempts++;
        }, 100);
    }

    // Load Purchase Orders
    async function loadPOs() {
        try {
            if (!window.api) throw new Error('API client not available');

            // Load POs with status approved or confirmed (for receiving)
            const response = await window.api.get('/purchase-orders?status=approved,confirmed');
            console.log('POs response:', response.data);
            
            const select = document.getElementById('purchase_order_id');
            
            // Handle different response structures
            let pos = [];
            if (response.data?.data) {
                if (response.data.data.data && Array.isArray(response.data.data.data)) {
                    pos = response.data.data.data; // Paginated response
                } else if (Array.isArray(response.data.data)) {
                    pos = response.data.data;
                }
            } else if (Array.isArray(response.data)) {
                pos = response.data;
            }
            
            console.log('Parsed POs:', pos);
            
            select.innerHTML = '<option value="">Select Purchase Order</option>';
            if (pos && pos.length > 0) {
                pos.forEach(po => {
                    const option = document.createElement('option');
                    option.value = po.id;
                    option.textContent = `${po.po_number || 'N/A'} - ${po.vendor_name || 'N/A'} (${po.status || 'N/A'})`;
                    select.appendChild(option);
                });
            } else {
                select.innerHTML = '<option value="">No approved POs available</option>';
                console.warn('No approved POs found. Make sure you have POs with status "approved" or "confirmed"');
            }
            
            // Add change event listener (only once)
            if (!select.hasAttribute('data-listener-attached')) {
                select.setAttribute('data-listener-attached', 'true');
                select.addEventListener('change', async (e) => {
                    if (e.target.value) {
                        await loadPOItems(e.target.value);
                    } else {
                        document.getElementById('items-container').innerHTML = '<p class="text-center text-gray-500 py-4">Select a Purchase Order to load items</p>';
                    }
                });
            }
        } catch (error) {
            console.error('Error loading POs:', error);
            const msg = error.response?.data?.message || error.message || 'Failed to load purchase orders';
            if (window.showAlert) window.showAlert(msg, 'error');
            const select = document.getElementById('purchase_order_id');
            if (select) {
                select.innerHTML = '<option value="">Error loading POs</option>';
                select.disabled = true;
            }
        }
    }

    // Load PO Items
    async function loadPOItems(poId) {
        const container = document.getElementById('items-container');
        container.innerHTML = '<p class="text-center text-gray-500 py-4">Loading items...</p>';
        
        try {
            if (!window.api) throw new Error('API client not available');

            const response = await window.api.get(`/purchase-orders/${poId}`);
            const po = response.data?.data || response.data;
            
            if (po.purchase_order_items && Array.isArray(po.purchase_order_items) && po.purchase_order_items.length > 0) {
                container.innerHTML = po.purchase_order_items.map((item, index) => `
                    <div class="border border-gray-200 p-4 rounded-lg bg-gray-50" data-item-row="${index}">
                        <div class="mb-3">
                            <p class="font-medium text-gray-900">${escapeHtml(item.item?.name || '-')}</p>
                            <p class="text-sm text-gray-600">Ordered: ${item.quantity || 0} ${item.item?.unit?.name || ''}</p>
                        </div>
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Received *</label>
                                <input type="number" class="quantity-received mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" 
                                    data-po-item-id="${item.id}" 
                                    data-ordered="${item.quantity || 0}"
                                    min="0" 
                                    max="${item.quantity || 0}" 
                                    value="${item.quantity || 0}"
                                    required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Accepted</label>
                                <input type="number" class="quantity-accepted mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" 
                                    data-po-item-id="${item.id}" 
                                    min="0" 
                                    value="${item.quantity || 0}">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Rejected</label>
                                <input type="number" class="quantity-rejected mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" 
                                    data-po-item-id="${item.id}" 
                                    min="0" 
                                    value="0">
                            </div>
                        </div>
                        <div class="mt-2">
                            <label class="block text-sm font-medium text-gray-700">Notes</label>
                            <input type="text" class="item-notes mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" 
                                data-po-item-id="${item.id}" 
                                placeholder="Optional notes">
                        </div>
                    </div>
                `).join('');

                // Add event listeners for quantity validation
                container.querySelectorAll('.quantity-received, .quantity-accepted, .quantity-rejected').forEach(input => {
                    input.addEventListener('input', function() {
                        validateItemQuantities(this.dataset.poItemId);
                    });
                });
            } else {
                container.innerHTML = '<p class="text-gray-500 text-center py-4">No items found in this purchase order</p>';
            }
        } catch (error) {
            console.error('Error loading PO items:', error);
            const msg = error.response?.data?.message || error.message || 'Failed to load PO items';
            container.innerHTML = `<p class="text-red-500 text-center py-4">Error: ${escapeHtml(msg)}</p>`;
            if (window.showAlert) window.showAlert(msg, 'error');
        }
    }

    // Validate item quantities (accepted + rejected should equal received)
    function validateItemQuantities(poItemId) {
        const receivedInput = document.querySelector(`.quantity-received[data-po-item-id="${poItemId}"]`);
        const acceptedInput = document.querySelector(`.quantity-accepted[data-po-item-id="${poItemId}"]`);
        const rejectedInput = document.querySelector(`.quantity-rejected[data-po-item-id="${poItemId}"]`);
        
        if (!receivedInput || !acceptedInput || !rejectedInput) return;

        const received = parseInt(receivedInput.value) || 0;
        const accepted = parseInt(acceptedInput.value) || 0;
        const rejected = parseInt(rejectedInput.value) || 0;

        // Auto-adjust accepted if received changes
        if (accepted + rejected !== received) {
            acceptedInput.value = Math.max(0, received - rejected);
        }
    }

    // Form submission
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        if (isSubmitting) return;

        isSubmitting = true;
        submitBtn.disabled = true;
        submitText.textContent = 'Creating...';
        submitSpinner.classList.remove('hidden');

        // Clear previous errors
        ['purchase_order_id', 'received_date', 'notes', 'items'].forEach(field => {
            const el = document.getElementById(field);
            const err = document.getElementById(`${field}-error`);
            if (el) el.classList.remove('border-red-500');
            if (err) { err.textContent = ''; err.classList.add('hidden'); }
        });

        try {
            const purchaseOrderId = document.getElementById('purchase_order_id').value.trim();
            const receivedDate = document.getElementById('received_date').value.trim();
            const notes = document.getElementById('notes').value.trim();

            // Basic validation
            if (!purchaseOrderId) throw { field: 'purchase_order_id', message: 'Purchase Order is required' };
            if (!receivedDate) throw { field: 'received_date', message: 'Received Date is required' };

            // Collect items
            const items = [];
            document.querySelectorAll('.quantity-received').forEach(input => {
                const poItemId = input.dataset.poItemId;
                const received = parseInt(input.value) || 0;
                const acceptedInput = document.querySelector(`.quantity-accepted[data-po-item-id="${poItemId}"]`);
                const rejectedInput = document.querySelector(`.quantity-rejected[data-po-item-id="${poItemId}"]`);
                const notesInput = document.querySelector(`.item-notes[data-po-item-id="${poItemId}"]`);
                
                const accepted = acceptedInput ? parseInt(acceptedInput.value) || 0 : 0;
                const rejected = rejectedInput ? parseInt(rejectedInput.value) || 0 : 0;
                const itemNotes = notesInput ? notesInput.value.trim() : null;
                
                if (received > 0) {
                    items.push({
                        purchase_order_item_id: parseInt(poItemId),
                        quantity_received: received,
                        quantity_accepted: accepted,
                        quantity_rejected: rejected,
                        notes: itemNotes || null
                    });
                }
            });
            
            if (items.length === 0) {
                throw { field: 'items', message: 'Please fill in at least one item received quantity' };
            }

            const payload = {
                purchase_order_id: purchaseOrderId,
                received_date: receivedDate,
                items: items
            };

            if (notes) payload.notes = notes;

            const response = await window.api.post('/receivings', payload);

            if (response.status >= 200 && response.status < 300) {
                const message = response.data?.message || 'Receiving created successfully';
                if (window.showAlert) window.showAlert(message, 'success');
                submitText.textContent = 'Success! Redirecting...';
                setTimeout(() => window.location.href = '/receivings', 800);
            } else {
                throw new Error('Unexpected response status');
            }
        } catch (error) {
            console.error('Submit error:', error);
            let errors = {};
            let msg = 'Failed to create receiving';

            // Laravel validation errors
            if (error.response && error.response.status === 422 && error.response.data.errors) {
                errors = error.response.data.errors;
                msg = error.response.data.message || msg;
            } else if (error.field && error.message) {
                errors[error.field] = [error.message];
            } else if (error.response?.data?.message) {
                msg = error.response.data.message;
            } else if (error.message) {
                msg = error.message;
            }

            // Show field errors
            Object.keys(errors).forEach(field => {
                const el = document.getElementById(field);
                const err = document.getElementById(`${field}-error`);
                if (el) el.classList.add('border-red-500');
                if (err) {
                    err.textContent = Array.isArray(errors[field]) ? errors[field][0] : errors[field];
                    err.classList.remove('hidden');
                }
            });

            if (window.showAlert) window.showAlert(msg, 'error');
            isSubmitting = false;
            submitBtn.disabled = false;
            submitText.textContent = 'Create Receiving';
            submitSpinner.classList.add('hidden');
        }
    });

    // Initial load
    setTimeout(() => {
        waitForAPI(loadPOs);
    }, 100);
});
</script>
@endsection
