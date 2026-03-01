@extends('layouts.app')

@section('title', 'Edit Request')
@section('page-title', 'Edit Request')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
        <form id="request-form" class="space-y-6">
            <div>
                <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                <textarea id="notes" name="notes" rows="3"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md"></textarea>
            </div>

            <div>
                <div class="flex justify-between items-center mb-4">
                    <label class="block text-sm font-medium text-gray-700">Items *</label>
                    <button type="button" onclick="addItemRow()" class="px-3 py-1 bg-gray-600 text-white text-sm rounded hover:bg-gray-700">
                        + Add Item
                    </button>
                </div>
                <div id="items-container" class="space-y-4">
                    <!-- Items will be loaded here -->
                </div>
            </div>

            <div class="flex justify-end gap-4">
                <a href="/requests" class="px-4 py-2 border rounded-md hover:bg-gray-50 transition">Cancel</a>
                <button type="submit" id="submit-btn" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                    <span id="submit-text">Update Request</span>
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
    const requestId = {{ $id }};
    const form = document.getElementById('request-form');
    if (!form) return;

    const submitBtn = document.getElementById('submit-btn');
    const submitText = document.getElementById('submit-text');
    const submitSpinner = document.getElementById('submit-spinner');

    let isSubmitting = false;
    let itemRowCount = 0;
    let items = [];

    // Wait for API
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

    async function loadData() {
        try {
            if (!window.api) {
                throw new Error('API client not available');
            }

            const [requestRes, itemsRes] = await Promise.all([
                window.api.get(`/requests/${requestId}`),
                window.api.get('/items')
            ]);
            
            const req = requestRes.data?.data || requestRes.data;
            const itemsData = itemsRes.data?.data?.data || itemsRes.data?.data || itemsRes.data || [];
            items = Array.isArray(itemsData) ? itemsData : [];
            
            if (!req) {
                throw new Error('Request not found');
            }

            document.getElementById('notes').value = req.notes || '';
            
            const container = document.getElementById('items-container');
            if (req.request_items && Array.isArray(req.request_items) && req.request_items.length > 0) {
                req.request_items.forEach(item => {
                    addItemRow(item);
                });
            } else {
                addItemRow(); // Add empty row if no items
            }
        } catch (error) {
            console.error('Load error:', error);
            const errorMsg = error.response?.data?.message || error.message || 'Failed to load request';
            if (window.showAlert) {
                window.showAlert(errorMsg, 'error');
            }
            if (error.response?.status === 404) {
                setTimeout(() => {
                    window.location.href = '/requests';
                }, 1500);
            }
        }
    }

    window.addItemRow = function(existingItem = null) {
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
            <div class="w-48">
                <label class="block text-sm font-medium text-gray-700">Notes</label>
                <input type="text" class="item-notes mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md">
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
            if (existingItem && item.id == existingItem.item_id) {
                option.selected = true;
                row.querySelector('.item-quantity').value = existingItem.quantity || '';
                row.querySelector('.item-notes').value = existingItem.notes || '';
            }
            select.appendChild(option);
        });
        
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

        try {
            const formItems = [];
            document.querySelectorAll('[id^="item-row-"]').forEach(row => {
                const itemId = row.querySelector('.item-select')?.value;
                const quantity = row.querySelector('.item-quantity')?.value;
                const notes = row.querySelector('.item-notes')?.value || '';
                if (itemId && quantity) {
                    formItems.push({ 
                        item_id: parseInt(itemId), 
                        quantity: parseInt(quantity), 
                        notes: notes.trim() || null
                    });
                }
            });
            
            if (formItems.length === 0) {
                throw new Error('Please add at least one item');
            }

            if (!window.api) {
                throw new Error('API client not available');
            }

            const response = await window.api.put(`/requests/${requestId}`, {
                notes: document.getElementById('notes').value.trim() || null,
                items: formItems
            });

            if (response.status >= 200 && response.status < 300) {
                const message = response.data?.message || 'Request updated successfully';
                
                if (window.showAlert) {
                    window.showAlert(message, 'success');
                }

                submitText.textContent = 'Success! Redirecting...';

                setTimeout(() => {
                    window.location.href = '/requests';
                }, 800);
            } else {
                throw new Error('Unexpected response status');
            }
        } catch (error) {
            console.error('Update error:', error);

            isSubmitting = false;
            submitBtn.disabled = false;
            submitText.textContent = 'Update Request';
            submitSpinner.classList.add('hidden');

            let errorMsg = 'Failed to update request';
            let errors = {};

            if (error.response) {
                errorMsg = error.response.data?.message || errorMsg;
                errors = error.response.data?.errors || {};
            } else if (error.message) {
                errorMsg = error.message;
            }

            if (window.showAlert) {
                window.showAlert(errorMsg, 'error');
            } else {
                alert(errorMsg);
            }
        }
    });

    waitForAPI(() => {
        loadData();
    });
});
</script>
@endsection
