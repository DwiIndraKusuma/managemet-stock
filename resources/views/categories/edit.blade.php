@extends('layouts.app')

@section('title', 'Edit Category')
@section('page-title', 'Edit Category')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
        <form id="category-form" class="space-y-6">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Name *</label>
                <input type="text" id="name" name="name" required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md">
            </div>
            <div>
                <label for="code" class="block text-sm font-medium text-gray-700">Code</label>
                <input type="text" id="code" name="code"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md">
            </div>
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                <textarea id="description" name="description" rows="3"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md"></textarea>
            </div>
            <div class="flex justify-end gap-4">
                <a href="/categories" class="px-4 py-2 border rounded-md hover:bg-gray-50 transition">Cancel</a>
                <button type="submit" id="submit-btn" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                    <span id="submit-text">Update</span>
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
document.addEventListener('DOMContentLoaded', function () {

    const categoryId = {{ $id }};
    const form = document.getElementById('category-form');
    if (!form) return;

    const submitBtn = document.getElementById('submit-btn');
    const submitText = document.getElementById('submit-text');
    const submitSpinner = document.getElementById('submit-spinner');

    let isSubmitting = false;

    // =========================
    // Load Category Data
    // =========================
    async function loadCategory() {
        try {
            if (!window.api) {
                throw new Error('API client not available');
            }

            const response = await window.api.get(`/categories/${categoryId}`);
            const cat = response.data?.data || response.data;

            if (!cat) {
                throw new Error('Category not found');
            }

            document.getElementById('name').value = cat.name || '';
            document.getElementById('code').value = cat.code || '';
            document.getElementById('description').value = cat.description || '';

        } catch (error) {
            console.error('Load error:', error);

            const errorMsg =
                error.response?.data?.message ||
                error.message ||
                'Failed to load category';

            if (window.showAlert) {
                window.showAlert(errorMsg, 'error');
            }

            if (error.response?.status === 404) {
                setTimeout(() => {
                    window.location.href = '/categories';
                }, 1500);
            }
        }
    }

    // =========================
    // Submit Form
    // =========================
    form.addEventListener('submit', async function (e) {
        e.preventDefault();

        if (isSubmitting) return;

        isSubmitting = true;
        submitBtn.disabled = true;
        submitText.textContent = 'Updating...';
        submitSpinner.classList.remove('hidden');

        // Clear old errors
        form.querySelectorAll('.error-message').forEach(el => el.remove());

        try {
            const name = document.getElementById('name').value.trim();
            const code = document.getElementById('code').value.trim();
            const description = document.getElementById('description').value.trim();

            if (!name) {
                throw new Error('Name is required');
            }

            const payload = {
                name: name,
                code: code || null,
                description: description || ''
            };

            if (!window.api) {
                throw new Error('API client not available');
            }

            const response = await window.api.put(`/categories/${categoryId}`, payload);

            if (response.status >= 200 && response.status < 300) {

                const message = response.data?.message || 'Category updated successfully';

                if (window.showAlert) {
                    window.showAlert(message, 'success');
                }

                submitText.textContent = 'Success! Redirecting...';

                setTimeout(() => {
                    window.location.href = '/categories';
                }, 800);

            } else {
                throw new Error('Unexpected response');
            }

        } catch (error) {

            isSubmitting = false;
            submitBtn.disabled = false;
            submitText.textContent = 'Update';
            submitSpinner.classList.add('hidden');

            let errorMsg = 'Failed to update category';
            let errors = {};

            if (error.response) {
                errorMsg = error.response.data?.message || errorMsg;
                errors = error.response.data?.errors || {};
            } else if (error.message) {
                errorMsg = error.message;
            }

            // Show field validation errors
            Object.keys(errors).forEach(field => {
                const input = document.getElementById(field);
                if (input) {
                    // Remove existing error for this field first
                    const existingError = input.parentElement.querySelector(`.error-message[data-field="${field}"]`);
                    if (existingError) {
                        existingError.remove();
                    }
                    
                    // Create new error message
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'error-message text-red-500 text-sm mt-1';
                    errorDiv.setAttribute('data-field', field);
                    errorDiv.textContent = Array.isArray(errors[field])
                        ? errors[field][0]
                        : errors[field];
                    
                    // Insert after input
                    input.parentElement.appendChild(errorDiv);
                    
                    console.log(`Error message added for field: ${field}`, errorDiv.textContent);
                } else {
                    console.warn(`Input field not found: ${field}`);
                }
            });
            
            console.log('Errors object:', errors);

            if (window.showAlert) {
                window.showAlert(errorMsg, 'error');
            } else {
                alert("Failed to update category");
            }
        }
    });

    // Initial Load
    loadCategory();

});
</script>
@endsection
