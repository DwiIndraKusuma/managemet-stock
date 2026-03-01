@extends('layouts.app')

@section('title', 'Edit Item')
@section('page-title', 'Edit Item')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
        <form id="item-form" class="space-y-6">
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Item Name *</label>
                    <input type="text" id="name" name="name" required
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    <p class="mt-1 text-sm text-red-600 hidden" id="name-error"></p>
                </div>

                <div>
                    <label for="code" class="block text-sm font-medium text-gray-700">Item Code</label>
                    <input type="text" id="code" name="code"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label for="category_id" class="block text-sm font-medium text-gray-700">Category *</label>
                    <select id="category_id" name="category_id" required
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Select Category</option>
                    </select>
                    <p class="mt-1 text-sm text-red-600 hidden" id="category_id-error"></p>
                </div>

                <div>
                    <label for="unit_id" class="block text-sm font-medium text-gray-700">Unit *</label>
                    <select id="unit_id" name="unit_id" required
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Select Unit</option>
                    </select>
                    <p class="mt-1 text-sm text-red-600 hidden" id="unit_id-error"></p>
                </div>
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                <textarea id="description" name="description" rows="3"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"></textarea>
            </div>

            <div>
                <label for="min_stock" class="block text-sm font-medium text-gray-700">Minimum Stock</label>
                <input type="number" id="min_stock" name="min_stock" min="0"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <div class="flex justify-end gap-4">
                <a href="/items" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition">
                    Cancel
                </a>
                <button type="submit" id="submit-btn" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                    <span id="submit-text">Update Item</span>
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
    const itemId = {{ $id }};
    const form = document.getElementById('item-form');
    if (!form) return;

    const submitBtn = document.getElementById('submit-btn');
    const submitText = document.getElementById('submit-text');
    const submitSpinner = document.getElementById('submit-spinner');

    let isSubmitting = false;

    // Wait for API to be available
    function waitForAPI(callback, maxAttempts = 50) {
        let attempts = 0;
        const interval = setInterval(() => {
            if (window.api && window.showAlert) {
                clearInterval(interval);
                callback();
            } else if (attempts >= maxAttempts) {
                clearInterval(interval);
                console.error('API or showAlert not available after multiple attempts.');
            }
            attempts++;
        }, 100);
    }

    // Remove error on typing
    ['name', 'code', 'category_id', 'unit_id', 'description', 'min_stock'].forEach(id => {
        const input = document.getElementById(id);
        if (!input) return;

        input.addEventListener('input', function () {
            input.classList.remove('border-red-500');
            const error = document.getElementById(`${id}-error`);
            if (error) {
                error.classList.add('hidden');
                error.textContent = '';
            }
        });
    });

    // Load item data and options
    async function loadData() {
        try {
            if (!window.api) {
                throw new Error('API client not available');
            }

            const [itemRes, categoriesRes, unitsRes] = await Promise.all([
                window.api.get(`/items/${itemId}`),
                window.api.get('/categories'),
                window.api.get('/units')
            ]);

            const item = itemRes.data?.data || itemRes.data;

            if (!item) {
                throw new Error('Item not found');
            }

            // Populate form
            document.getElementById('name').value = item.name || '';
            document.getElementById('code').value = item.code || '';
            document.getElementById('description').value = item.description || '';
            document.getElementById('min_stock').value = item.min_stock || 0;

            // Populate categories
            const categorySelect = document.getElementById('category_id');
            const categories = categoriesRes.data?.data || categoriesRes.data || [];
            if (Array.isArray(categories)) {
                categories.forEach(cat => {
                    const option = document.createElement('option');
                    option.value = cat.id;
                    option.textContent = cat.name || '';
                    option.selected = cat.id == item.category_id;
                    categorySelect.appendChild(option);
                });
            }

            // Populate units
            const unitSelect = document.getElementById('unit_id');
            const units = unitsRes.data?.data || unitsRes.data || [];
            if (Array.isArray(units)) {
                units.forEach(unit => {
                    const option = document.createElement('option');
                    option.value = unit.id;
                    option.textContent = unit.name || '';
                    option.selected = unit.id == item.unit_id;
                    unitSelect.appendChild(option);
                });
            }
        } catch (error) {
            console.error('Error loading data:', error);
            const errorMsg = error.response?.data?.message || error.message || 'Failed to load item data';
            if (window.showAlert) {
                window.showAlert(errorMsg, 'error');
            }
            if (error.response?.status === 404) {
                setTimeout(() => {
                    window.location.href = '/items';
                }, 1500);
            }
        }
    }

    // Form submission
    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        if (isSubmitting) return;

        isSubmitting = true;
        submitBtn.disabled = true;
        submitText.textContent = 'Updating...';
        submitSpinner.classList.remove('hidden');
        
        // Clear errors and remove red border
        ['name', 'code', 'category_id', 'unit_id', 'description', 'min_stock'].forEach(field => {
            const input = document.getElementById(field);
            const errorEl = document.getElementById(`${field}-error`);
            
            if (input) {
                input.classList.remove('border-red-500');
            }
            
            if (errorEl) {
                errorEl.classList.add('hidden');
                errorEl.textContent = '';
            }
        });

        try {
            const formData = {
                name: document.getElementById('name').value.trim(),
                code: document.getElementById('code').value.trim() || null,
                category_id: document.getElementById('category_id').value,
                unit_id: document.getElementById('unit_id').value,
                description: document.getElementById('description').value.trim() || null,
                min_stock: parseInt(document.getElementById('min_stock').value) || 0
            };

            if (!formData.name) {
                throw new Error('Item name is required');
            }
            if (!formData.category_id) {
                throw new Error('Category is required');
            }
            if (!formData.unit_id) {
                throw new Error('Unit is required');
            }

            if (!window.api) {
                throw new Error('API client not available');
            }

            const response = await window.api.put(`/items/${itemId}`, formData);
            
            if (response.status >= 200 && response.status < 300) {
                const message = response.data?.message || 'Item updated successfully';
                
                if (window.showAlert) {
                    window.showAlert(message, 'success');
                }

                submitText.textContent = 'Success! Redirecting...';

                setTimeout(() => {
                    window.location.href = '/items';
                }, 800);
            } else {
                throw new Error('Unexpected response status');
            }
        } catch (error) {
            console.error('Update error:', error);

            isSubmitting = false;
            submitBtn.disabled = false;
            submitText.textContent = 'Update Item';
            submitSpinner.classList.add('hidden');

            let errorMsg = 'Failed to update item';
            let errors = {};

            if (error.response) {
                const status = error.response.status;
                const data = error.response.data || {};
                
                console.log('Error response:', { status, data });
                
                if (status === 422 && data.errors) {
                    errors = data.errors;
                    errorMsg = data.message || 'Validation error';
                } else {
                    errorMsg = data.message || errorMsg;
                }
            } else if (error.message) {
                errorMsg = error.message;
            }

            // Show field errors
            Object.keys(errors).forEach(field => {
                const input = document.getElementById(field);
                const errorEl = document.getElementById(`${field}-error`);
                
                if (input) {
                    input.classList.add('border-red-500');
                }
                
                if (errorEl) {
                    errorEl.textContent = Array.isArray(errors[field]) ? errors[field][0] : errors[field];
                    errorEl.classList.remove('hidden');
                    console.log(`Error message shown for field: ${field}`, errorEl.textContent);
                } else if (input) {
                    // If error element doesn't exist, create one
                    const errorDiv = document.createElement('p');
                    errorDiv.id = `${field}-error`;
                    errorDiv.className = 'mt-1 text-sm text-red-600';
                    errorDiv.textContent = Array.isArray(errors[field]) ? errors[field][0] : errors[field];
                    input.parentElement.appendChild(errorDiv);
                    console.log(`Error message created for field: ${field}`, errorDiv.textContent);
                } else {
                    console.warn(`Field not found: ${field}`);
                }
            });
            
            console.log('Validation errors:', errors);

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
