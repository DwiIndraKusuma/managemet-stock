@extends('layouts.app')

@section('title', 'Create Item')
@section('page-title', 'Create New Item')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
        <form id="item-form" class="space-y-6">
            <div class="grid grid-cols-2 gap-6">
                <!-- Item Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Item Name *</label>
                    <input type="text" id="name" name="name" required
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    <p class="mt-1 text-sm text-red-600 hidden" id="name-error"></p>
                </div>

                <!-- Item Code -->
                <div>
                    <label for="code" class="block text-sm font-medium text-gray-700">Item Code</label>
                    <input type="text" id="code" name="code"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    <p class="mt-1 text-xs text-gray-500">Leave empty to auto-generate</p>
                    <p class="mt-1 text-sm text-red-600 hidden" id="code-error"></p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-6">
                <!-- Category -->
                <div>
                    <label for="category_id" class="block text-sm font-medium text-gray-700">Category *</label>
                    <select id="category_id" name="category_id" required
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Loading...</option>
                    </select>
                    <p class="mt-1 text-sm text-red-600 hidden" id="category_id-error"></p>
                </div>

                <!-- Unit -->
                <div>
                    <label for="unit_id" class="block text-sm font-medium text-gray-700">Unit *</label>
                    <select id="unit_id" name="unit_id" required
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Loading...</option>
                    </select>
                    <p class="mt-1 text-sm text-red-600 hidden" id="unit_id-error"></p>
                </div>
            </div>

            <!-- Description -->
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                <textarea id="description" name="description" rows="3"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                <p class="mt-1 text-sm text-red-600 hidden" id="description-error"></p>
            </div>

            <!-- Minimum Stock -->
            <div>
                <label for="min_stock" class="block text-sm font-medium text-gray-700">Minimum Stock</label>
                <input type="number" id="min_stock" name="min_stock" value="0" min="0"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                <p class="mt-1 text-sm text-red-600 hidden" id="min_stock-error"></p>
            </div>

            <!-- Buttons -->
            <div class="flex justify-end gap-4">
                <a href="/items" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition">
                    Cancel
                </a>
                <button type="submit" id="submit-btn" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                    <span id="submit-text">Create Item</span>
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
    const form = document.getElementById('item-form');
    if (!form) return;

    const submitBtn = document.getElementById('submit-btn');
    const submitText = document.getElementById('submit-text');
    const submitSpinner = document.getElementById('submit-spinner');
    let isSubmitting = false;

    // =========================
    // Load categories & units
    // =========================
    async function loadOptions() {
        try {
            if (!window.api) throw new Error('API client not available');

            const [categoriesRes, unitsRes] = await Promise.all([
                window.api.get('/categories'),
                window.api.get('/units')
            ]);

            const categories = categoriesRes.data?.data || categoriesRes.data || [];
            const units = unitsRes.data?.data || unitsRes.data || [];

            // Populate Category
            const categorySelect = document.getElementById('category_id');
            categorySelect.innerHTML = '<option value="">Select Category</option>';
            categories.forEach(cat => {
                const option = document.createElement('option');
                option.value = cat.id;
                option.textContent = cat.name;
                categorySelect.appendChild(option);
            });

            // Populate Unit
            const unitSelect = document.getElementById('unit_id');
            unitSelect.innerHTML = '<option value="">Select Unit</option>';
            units.forEach(unit => {
                const option = document.createElement('option');
                option.value = unit.id;
                option.textContent = unit.name;
                unitSelect.appendChild(option);
            });

        } catch (error) {
            console.error('Error loading options:', error);
            const msg = error.response?.data?.message || error.message || 'Failed to load categories/units';
            if (window.showAlert) window.showAlert(msg, 'error');
        }
    }

    // =========================
    // Form submit
    // =========================
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        if (isSubmitting) return;

        isSubmitting = true;
        submitBtn.disabled = true;
        submitText.textContent = 'Creating...';
        submitSpinner.classList.remove('hidden');

        // Clear previous errors
        ['name','code','category_id','unit_id','description','min_stock'].forEach(f => {
            const el = document.getElementById(f);
            const err = document.getElementById(`${f}-error`);
            el?.classList.remove('border-red-500');
            if (err) { err.textContent = ''; err.classList.add('hidden'); }
        });

        try {
            const payload = {
                name: document.getElementById('name').value.trim(),
                code: document.getElementById('code').value.trim() || null,
                category_id: document.getElementById('category_id').value,
                unit_id: document.getElementById('unit_id').value,
                description: document.getElementById('description').value.trim() || null,
                min_stock: parseInt(document.getElementById('min_stock').value) || 0
            };

            // Basic client-side validation
            if (!payload.name) throw { field: 'name', message: 'Item name is required' };
            if (!payload.category_id) throw { field: 'category_id', message: 'Category is required' };
            if (!payload.unit_id) throw { field: 'unit_id', message: 'Unit is required' };

            const response = await window.api.post('/items', payload);

            if (response.status >= 200 && response.status < 300) {
                const message = response.data?.message || 'Item created successfully';
                if (window.showAlert) window.showAlert(message, 'success');
                submitText.textContent = 'Success! Redirecting...';
                setTimeout(() => window.location.href = '/items', 800);
            } else {
                throw new Error('Unexpected response status');
            }
        } catch (error) {
            console.error('Submit error:', error);
            let errors = {};
            let msg = 'Failed to create item';

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
            Object.keys(errors).forEach(f => {
                const el = document.getElementById(f);
                const err = document.getElementById(`${f}-error`);
                if (el) el.classList.add('border-red-500');
                if (err) { err.textContent = Array.isArray(errors[f]) ? errors[f][0] : errors[f]; err.classList.remove('hidden'); }
            });

            if (window.showAlert) window.showAlert(msg, 'error');
            isSubmitting = false;
            submitBtn.disabled = false;
            submitText.textContent = 'Create Item';
            submitSpinner.classList.add('hidden');
        }
    });

    // Initial load - wait for API to be available
    function waitForAPI(callback, maxAttempts = 100) {
        let attempts = 0;
        const interval = setInterval(() => {
            if (window.api) {
                clearInterval(interval);
                console.log('API is available, loading options...');
                callback();
            } else if (attempts >= maxAttempts) {
                clearInterval(interval);
                console.error('API not available after multiple attempts.');
                const categorySelect = document.getElementById('category_id');
                const unitSelect = document.getElementById('unit_id');
                if (categorySelect) {
                    categorySelect.innerHTML = '<option value="">Error: API not available</option>';
                    categorySelect.disabled = true;
                }
                if (unitSelect) {
                    unitSelect.innerHTML = '<option value="">Error: API not available</option>';
                    unitSelect.disabled = true;
                }
                // Show error message
                if (window.showAlert) {
                    window.showAlert('Failed to load form options. Please refresh the page.', 'error');
                } else {
                    alert('Failed to load form options. Please refresh the page.');
                }
            }
            attempts++;
        }, 100);
    }
    
    // Wait a bit for Vite to load app.js, then wait for API
    setTimeout(() => {
        waitForAPI(loadOptions);
    }, 100);
});
</script>
@endsection