@extends('layouts.app')

@section('title', 'Edit Purchase Order')
@section('page-title', 'Edit Purchase Order')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
        <form id="po-form" class="space-y-6">
            <div>
                <label for="vendor_name" class="block text-sm font-medium text-gray-700">Vendor Name *</label>
                <input type="text" id="vendor_name" name="vendor_name" required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md">
                <p id="vendor_name-error" class="mt-1 text-sm text-red-600 hidden"></p>
            </div>
            <div>
                <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                <textarea id="notes" name="notes" rows="3"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md"></textarea>
                <p id="notes-error" class="mt-1 text-sm text-red-600 hidden"></p>
            </div>
            <div>
                <div class="flex justify-between items-center mb-4">
                    <label class="block text-sm font-medium text-gray-700">Items *</label>
                    <button type="button" onclick="addItemRow()" class="px-3 py-1 bg-gray-600 text-white text-sm rounded hover:bg-gray-700">
                        + Add Item
                    </button>
                </div>
                <div id="items-container" class="space-y-4"></div>
                <p id="items-error" class="mt-1 text-sm text-red-600 hidden"></p>
            </div>
            <div class="flex justify-end gap-4">
                <a href="/purchase-orders" class="px-4 py-2 border rounded-md hover:bg-gray-50 transition">Cancel</a>
                <button type="submit" id="submit-btn" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                    <span id="submit-text">Update PO</span>
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
    const poId = {{ $id }};
    const form = document.getElementById('po-form');
    if (!form) return;

    const submitBtn = document.getElementById('submit-btn');
    const submitText = document.getElementById('submit-text');
    const submitSpinner = document.getElementById('submit-spinner');

    let isSubmitting = false;
    let itemRowCount = 0;
    let items = [];
    let currentPO = null;

    function waitForAPI(callback, maxAttempts = 50) {
        let attempts = 0;
        const interval = setInterval(() => {
            if (window.api && window.showAlert) {
                clearInterval(interval);
                callback();
            } else if (attempts >= maxAttempts) {
                clearInterval(interval);
                console.error('API or showAlert not available.');
            }
            attempts++;
        }, 100);
    }

    async function loadItems() {
        try {
            if (!window.api) throw new Error('API client not available');
            const response = await window.api.get('/items');
            const itemsData = response.data?.data?.data || response.data?.data || response.data || [];
            items = Array.isArray(itemsData) ? itemsData : [];
        } catch (error) {
            console.error('Error loading items:', error);
            if (window.showAlert) window.showAlert('Failed to load items', 'error');
        }
    }

    async function loadPO() {
        try {
            if (!window.api) throw new Error('API client not available');
            const response = await window.api.get(`/purchase-orders/${poId}`);
            currentPO = response.data?.data || response.data;
            
            if (!currentPO) throw new Error('Purchase order not found');
            if (currentPO.status !== 'draft') {
                throw new Error('Only draft purchase orders can be edited');
            }

            // Populate form
            document.getElementById('vendor_name').value = currentPO.vendor_name || '';
            document.getElementById('notes').value = currentPO.notes || '';

            // Populate items
            if (currentPO.purchase_order_items && Array.isArray(currentPO.purchase_order_items)) {
                currentPO.purchase_order_items.forEach(item => {
                    addItemRow(item);
                });
            } else {
                addItemRow();
            }
        } catch (error) {
            console.error('Error loading PO:', error);
            const errorMsg = error.response?.data?.message || error.message || 'Failed to load purchase order';
            if (window.showAlert) window.showAlert(errorMsg, 'error');
            if (error.response?.status === 404 || error.response?.status === 403) {
                setTimeout(() => window.location.href = '/purchase-orders', 2000);
            }
        }
    }

    window.addItemRow = function(itemData = null) {
        const container = document.getElementById('items-container');
        const row = document.createElement('div');
        row.className = 'flex gap-4 items-end border p-4 rounded-lg';
        row.id = `item-row-${itemRowCount}`;
        
        row.innerHTML = `
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700">Item</label>
                <select class="item-select mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md" required>
                    <option value="">Select Item</option>
                </select>
            </div>
            <div class="w-32">
                <label class="block text-sm font-medium text-gray-700">Quantity</label>
                <input type="number" class="item-quantity mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md" min="1" required>
            </div>
            <div class="w-32">
                <label class="block text-sm font-medium text-gray-700">Unit Price</label>
                <input type="number" class="item-price mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md" min="0" step="0.01" required>
            </div>
            <button type="button" onclick="removeItemRow(${itemRowCount})" class="px-3 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                Remove
            </button>
        `;
        
        const select = row.querySelector('.item-select');
        items.forEach(item => {
            const option = document.createElement('option');
            option.value = item.id;
            option.textContent = `${item.code || ''} - ${item.name || ''}`;
            if (itemData && itemData.item_id == item.id) option.selected = true;
            select.appendChild(option);
        });

        if (itemData) {
            row.querySelector('.item-quantity').value = itemData.quantity || '';
            row.querySelector('.item-price').value = itemData.unit_price || '';
        }
        
        container.appendChild(row);
        itemRowCount++;
    };

    window.removeItemRow = function(id) {
        const row = document.getElementById(`item-row-${id}`);
        if (row) row.remove();
    };

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        if (isSubmitting) return;

        isSubmitting = true;
        submitBtn.disabled = true;
        submitText.textContent = 'Updating...';
        submitSpinner.classList.remove('hidden');

        // Clear errors
        ['vendor_name', 'notes', 'items'].forEach(f => {
            const el = document.getElementById(`${f}-error`);
            if (el) { el.textContent = ''; el.classList.add('hidden'); }
            document.getElementById(f === 'items' ? 'items-container' : f)?.classList.remove('border-red-500');
        });

        try {
            const formItems = [];
            document.querySelectorAll('[id^="item-row-"]').forEach(row => {
                const itemId = row.querySelector('.item-select')?.value;
                const quantity = row.querySelector('.item-quantity')?.value;
                const price = row.querySelector('.item-price')?.value;
                if (itemId && quantity && price) {
                    formItems.push({ 
                        item_id: parseInt(itemId), 
                        quantity: parseInt(quantity), 
                        unit_price: parseFloat(price)
                    });
                }
            });
            
            if (formItems.length === 0) {
                throw { field: 'items', message: 'Please add at least one item' };
            }

            if (!window.api) throw new Error('API client not available');

            const response = await window.api.put(`/purchase-orders/${poId}`, {
                vendor_name: document.getElementById('vendor_name').value.trim(),
                notes: document.getElementById('notes').value.trim() || null,
                items: formItems
            });

            if (response.status >= 200 && response.status < 300) {
                const message = response.data?.message || 'Purchase order updated successfully';
                if (window.showAlert) window.showAlert(message, 'success');
                submitText.textContent = 'Success! Redirecting...';
                setTimeout(() => window.location.href = '/purchase-orders', 800);
            } else {
                throw new Error('Unexpected response status');
            }
        } catch (error) {
            console.error('Update error:', error);
            let errors = {};
            let msg = 'Failed to update purchase order';

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

            Object.keys(errors).forEach(f => {
                const el = document.getElementById(`${f}-error`);
                const input = document.getElementById(f) || (f === 'items' ? document.getElementById('items-container') : null);
                if (el) { el.textContent = Array.isArray(errors[f]) ? errors[f][0] : errors[f]; el.classList.remove('hidden'); }
                if (input) input.classList.add('border-red-500');
            });

            if (window.showAlert) window.showAlert(msg, 'error');
            isSubmitting = false;
            submitBtn.disabled = false;
            submitText.textContent = 'Update PO';
            submitSpinner.classList.add('hidden');
        }
    });

    waitForAPI(() => {
        Promise.all([loadItems(), loadPO()]);
    });
});
</script>
@endsection
