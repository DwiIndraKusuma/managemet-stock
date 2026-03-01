@extends('layouts.app')

@section('title', 'Create Unit')
@section('page-title', 'Create Unit')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
        <form id="unit-form" class="space-y-6">
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
                <a href="/units" class="px-4 py-2 border rounded-md hover:bg-gray-50 transition">Cancel</a>
                <button type="submit" id="submit-btn" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                    <span id="submit-text">Create</span>
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
    const form = document.getElementById('unit-form');
    if (!form) return;

    let isSubmitting = false; // Flag to prevent double submit

    form.addEventListener('submit', async function (e) {
        e.preventDefault();

        // Prevent double submit
        if (isSubmitting) {
            console.log('Form is already submitting, ignoring...');
            return;
        }

        const submitBtn = document.getElementById('submit-btn');
        const submitText = document.getElementById('submit-text');
        const submitSpinner = document.getElementById('submit-spinner');

        // Set submitting state
        isSubmitting = true;
        submitBtn.disabled = true;
        submitText.textContent = 'Creating...';
        submitSpinner.classList.remove('hidden');

        // Clear previous errors
        form.querySelectorAll('.error-message').forEach(el => el.remove());

        try {
            const name = document.getElementById('name').value.trim();
            const code = document.getElementById('code').value.trim();
            const description = document.getElementById('description').value.trim();

            if (!name) {
                throw new Error('Name is required');
            }

            const formData = {
                name: name,
                code: code || null,
                description: description || ''
            };

            // Pastikan window.api tersedia
            if (!window.api) {
                throw new Error('API client not available');
            }

            const response = await window.api.post('/units', formData);

            if (response.status >= 200 && response.status < 300) {
                const message = response.data?.message || 'Unit created successfully';

                if (window.showAlert) {
                    window.showAlert(message, 'success');
                }

                // Keep loading state during redirect
                submitText.textContent = 'Success! Redirecting...';
                
                setTimeout(() => {
                    window.location.href = '/units';
                }, 800);
            } else {
                throw new Error('Unexpected response status');
            }

        } catch (error) {
            console.error(error);

            // Reset submitting state on error
            isSubmitting = false;
            submitBtn.disabled = false;
            submitText.textContent = 'Create';
            submitSpinner.classList.add('hidden');

            let errorMsg = 'Failed to create unit';
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
                alert("Failed to create unit");
            }

        }
        // Note: We don't reset in finally because we want to keep loading state during redirect
    });

});
</script>
@endsection
