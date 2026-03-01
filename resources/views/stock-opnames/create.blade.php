@extends('layouts.app')

@section('title', 'Create Stock Opname')
@section('page-title', 'Create Stock Opname')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
        <form id="opname-form" class="space-y-6">
            <div>
                <label for="opname_date" class="block text-sm font-medium text-gray-700">Opname Date *</label>
                <input type="date" id="opname_date" name="opname_date" required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md"
                    value="{{ date('Y-m-d') }}">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-4">Items</label>
                <div class="mb-4">
                    <button type="button" onclick="addItemRow()" class="px-3 py-1 bg-gray-600 text-white text-sm rounded hover:bg-gray-700">
                        + Add Item
                    </button>
                </div>
                <div id="items-container" class="space-y-4"></div>
            </div>
            <div class="flex justify-end gap-4">
                <a href="/stock-opnames" class="px-4 py-2 border rounded-md hover:bg-gray-50 transition">Cancel</a>
                <button type="submit" id="submit-btn" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                    <span id="submit-text">Create Opname</span>
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
    const form = document.getElementById('opname-form');
    if (!form) return;

    const submitBtn = document.getElementById('submit-btn');
    const submitText = document.getElementById('submit-text');
    const submitSpinner = document.getElementById('submit-spinner');

    let isSubmitting = false;
    let itemRowCount = 0;
    let items = [];

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
            if (!window.api) {
                throw new Error('API client not available');
            }

            const response = await window.api.get('/items');
            const itemsData = response.data?.data?.data || response.data?.data || response.data || [];
            items = Array.isArray(itemsData) ? itemsData : [];
        } catch (error) {
            console.error('Error loading items:', error);
            if (window.showAlert) {
                window.showAlert('Failed to load items', 'error');
            }
        }
    }

    window.addItemRow = function() {
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
                <label class="block text-sm font-medium text-gray-700">System Qty</label>
                <input type="number" class="system-quantity mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50" readonly>
            </div>
            <div class="w-32">
                <label class="block text-sm font-medium text-gray-700">Physical Qty *</label>
                <input type="number" class="physical-quantity mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md" min="0" required>
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
            option.dataset.stock = item.inventory?.quantity_available || 0;
            select.appendChild(option);
        });
        
        select.addEventListener('change', (e) => {
            const selectedOption = e.target.options[e.target.selectedIndex];
            row.querySelector('.system-quantity').value = selectedOption.dataset.stock || 0;
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
        submitText.textContent = 'Creating...';
        submitSpinner.classList.remove('hidden');

        try {
            const formItems = [];
            document.querySelectorAll('[id^="item-row-"]').forEach(row => {
                const itemId = row.querySelector('.item-select')?.value;
                const physicalQty = row.querySelector('.physical-quantity')?.value;
                if (itemId && physicalQty !== null && physicalQty !== '') {
                    formItems.push({ 
                        item_id: parseInt(itemId), 
                        physical_quantity: parseInt(physicalQty)
                    });
                }
            });
            
            if (formItems.length === 0) {
                throw new Error('Please add at least one item');
            }

            if (!window.api) {
                throw new Error('API client not available');
            }

            const response = await window.api.post('/stock-opnames', {
                opname_date: document.getElementById('opname_date').value,
                items: formItems
            });

            if (response.status >= 200 && response.status < 300) {
                const message = response.data?.message || 'Stock opname created successfully';
                
                if (window.showAlert) {
                    window.showAlert(message, 'success');
                }

                submitText.textContent = 'Success! Redirecting...';

                setTimeout(() => {
                    window.location.href = '/stock-opnames';
                }, 800);
            } else {
                throw new Error('Unexpected response status');
            }
        } catch (error) {
            console.error('Create error:', error);

            isSubmitting = false;
            submitBtn.disabled = false;
            submitText.textContent = 'Create Opname';
            submitSpinner.classList.add('hidden');

            let errorMsg = 'Failed to create stock opname';
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
        loadItems().then(() => {
            addItemRow();
        });
    });
});
</script>
@endsection
