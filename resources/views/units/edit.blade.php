@extends('layouts.app')

@section('title', 'Edit Unit')
@section('page-title', 'Edit Unit')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
        <form id="unit-form" class="space-y-6">

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">
                    Name *
                </label>
                <input type="text" id="name" name="name" required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md">
            </div>

            <div>
                <label for="code" class="block text-sm font-medium text-gray-700">
                    Code
                </label>
                <input type="text" id="code" name="code"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md">
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700">
                    Description
                </label>
                <textarea id="description" name="description" rows="3"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md"></textarea>
            </div>

            <div class="flex justify-end gap-4">
                <a href="/units"
                   class="px-4 py-2 border rounded-md hover:bg-gray-50 transition">
                   Cancel
                </a>

                <button type="submit"
                        id="submit-btn"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-md
                               hover:bg-indigo-700 transition
                               disabled:opacity-50 disabled:cursor-not-allowed
                               flex items-center gap-2">

                    <span id="submit-text">Update</span>

                    <span id="submit-spinner" class="hidden">
                        <svg class="animate-spin h-4 w-4 text-white"
                             xmlns="http://www.w3.org/2000/svg"
                             fill="none"
                             viewBox="0 0 24 24">
                            <circle class="opacity-25"
                                    cx="12"
                                    cy="12"
                                    r="10"
                                    stroke="currentColor"
                                    stroke-width="4"></circle>
                            <path class="opacity-75"
                                  fill="currentColor"
                                  d="M4 12a8 8 0 018-8V0C5.373
                                  0 0 5.373 0 12h4zm2
                                  5.291A7.962 7.962 0 014
                                  12H0c0 3.042 1.135
                                  5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </span>

                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const unitId = {{ $id }};
    const form = document.getElementById('unit-form');
    if (!form) return;

    const submitBtn = document.getElementById('submit-btn');
    const submitText = document.getElementById('submit-text');
    const submitSpinner = document.getElementById('submit-spinner');

    let isSubmitting = false;

    // =========================
    // Load Unit Data
    // =========================
    async function loadUnit() {
        try {

            if (!window.api) {
                throw new Error('API client not available');
            }

            const response = await window.api.get(`/units/${unitId}`);
            const unit = response.data?.data || response.data;

            if (!unit) {
                throw new Error('Unit not found');
            }

            document.getElementById('name').value = unit.name || '';
            document.getElementById('code').value = unit.code || '';
            document.getElementById('description').value = unit.description || '';

        } catch (error) {

            const errorMsg =
                error.response?.data?.message ||
                error.message ||
                'Failed to load unit';

            if (window.showAlert) {
                window.showAlert(errorMsg, 'error');
            }

            if (error.response?.status === 404) {
                setTimeout(() => {
                    window.location.href = '/units';
                }, 1500);
            }
        }
    }

    // =========================
    // Remove error on typing
    // =========================
    ['name','code','description'].forEach(id => {
        const input = document.getElementById(id);
        if (!input) return;

        input.addEventListener('input', function () {
            input.classList.remove('border-red-500');
            const error = input.parentElement.querySelector('.error-message');
            if (error) error.remove();
        });
    });

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

        // Remove old errors
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

            const response = await window.api.put(`/units/${unitId}`, payload);

            if (response.status >= 200 && response.status < 300) {

                const message =
                    response.data?.message ||
                    'Unit updated successfully';

                if (window.showAlert) {
                    window.showAlert(message, 'success');
                }

                submitText.textContent = 'Success! Redirecting...';

                setTimeout(() => {
                    window.location.href = '/units';
                }, 800);

            } else {
                throw new Error('Unexpected response');
            }

        } catch (error) {

            isSubmitting = false;
            submitBtn.disabled = false;
            submitText.textContent = 'Update';
            submitSpinner.classList.add('hidden');

            let errorMsg = 'Failed to update unit';
            let validationErrors = {};

            if (error.response) {
                const status = error.response.status;
                const data = error.response.data || {};

                console.log('Error response:', { status, data });

                if (status === 422) {
                    // Validation error
                    if (data.errors) {
                        validationErrors = data.errors;
                        errorMsg = data.message || 'Validation error';
                    } else if (data.message) {
                        // If message contains field name, try to extract it
                        errorMsg = data.message;
                        
                        // Try to map common validation messages to fields
                        if (data.message.toLowerCase().includes('code')) {
                            validationErrors.code = [data.message];
                        } else if (data.message.toLowerCase().includes('name')) {
                            validationErrors.name = [data.message];
                        }
                    }
                } else {
                    errorMsg = data.message || errorMsg;
                }
            } else if (error.message) {
                errorMsg = error.message;
            }

            console.log('Validation errors:', validationErrors);

            // Show validation errors
            Object.keys(validationErrors).forEach(field => {
                const input = document.getElementById(field);
                if (!input) {
                    console.warn(`Field not found: ${field}`);
                    return;
                }

                const message = Array.isArray(validationErrors[field])
                    ? validationErrors[field][0]
                    : validationErrors[field];

                input.classList.add('border-red-500');

                // Remove existing error message for this field
                const existingError = input.parentElement.querySelector('.error-message');
                if (existingError) {
                    existingError.remove();
                }

                const errorDiv = document.createElement('div');
                errorDiv.className = 'error-message text-red-500 text-sm mt-1';
                errorDiv.textContent = message;

                input.parentElement.appendChild(errorDiv);
                console.log(`Error message added for field: ${field}`, message);
            });

            if (window.showAlert) {
                window.showAlert(errorMsg, 'error');
            } else {
                alert(errorMsg);
            }
        }
    });

    // Initial Load
    loadUnit();

});
</script>
@endsection