@extends('layouts.app')

@section('title', 'Create User')
@section('page-title', 'Create User')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
        <form id="user-form" class="space-y-6">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Name *</label>
                <input type="text" id="name" name="name" required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md">
            </div>
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email *</label>
                <input type="email" id="email" name="email" required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md">
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Password *</label>
                <input type="password" id="password" name="password" required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md">
            </div>
            <div>
                <label for="role_id" class="block text-sm font-medium text-gray-700">Role *</label>
                <select id="role_id" name="role_id" required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md">
                    <option value="">Select Role</option>
                </select>
            </div>
            <div class="flex justify-end gap-4">
                <a href="/users" class="px-4 py-2 border rounded-md hover:bg-gray-50 transition">Cancel</a>
                <button type="submit" id="submit-btn" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                    <span id="submit-text">Create User</span>
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
    const form = document.getElementById('user-form');
    if (!form) return;

    const submitBtn = document.getElementById('submit-btn');
    const submitText = document.getElementById('submit-text');
    const submitSpinner = document.getElementById('submit-spinner');

    let isSubmitting = false;

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

    async function loadRoles() {
        try {
            if (!window.api) {
                throw new Error('API client not available');
            }

            // Load roles from API
            const response = await window.api.get('/roles');
            const roles = response.data?.data || response.data || [];
            
            const select = document.getElementById('role_id');
            select.innerHTML = '<option value="">Select Role</option>';
            
            if (Array.isArray(roles) && roles.length > 0) {
                roles.forEach(role => {
                    const option = document.createElement('option');
                    option.value = role.id;
                    option.textContent = role.display_name || role.name;
                    select.appendChild(option);
                });
            } else {
                // Fallback to hardcoded if API fails
                const fallbackRoles = [
                    { id: 1, name: 'admin_gudang', display_name: 'Admin Gudang' },
                    { id: 2, name: 'spv', display_name: 'Supervisor' },
                    { id: 3, name: 'technician', display_name: 'Technician' }
                ];
                fallbackRoles.forEach(role => {
                    const option = document.createElement('option');
                    option.value = role.id;
                    option.textContent = role.display_name;
                    select.appendChild(option);
                });
            }
        } catch (error) {
            console.error('Error loading roles:', error);
            // Fallback to hardcoded
            const select = document.getElementById('role_id');
            const fallbackRoles = [
                { id: 1, name: 'admin_gudang', display_name: 'Admin Gudang' },
                { id: 2, name: 'spv', display_name: 'Supervisor' },
                { id: 3, name: 'technician', display_name: 'Technician' }
            ];
            fallbackRoles.forEach(role => {
                const option = document.createElement('option');
                option.value = role.id;
                option.textContent = role.display_name;
                select.appendChild(option);
            });
            if (window.showAlert) {
                window.showAlert('Failed to load roles from API, using default roles', 'warning');
            }
        }
    }

    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        if (isSubmitting) return;

        isSubmitting = true;
        submitBtn.disabled = true;
        submitText.textContent = 'Creating...';
        submitSpinner.classList.remove('hidden');

        form.querySelectorAll('.error-message').forEach(el => el.remove());

        try {
            const name = document.getElementById('name').value.trim();
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            const roleId = document.getElementById('role_id').value;

            if (!name) throw new Error('Name is required');
            if (!email) throw new Error('Email is required');
            if (!password) throw new Error('Password is required');
            if (!roleId) throw new Error('Role is required');

            if (!window.api) {
                throw new Error('API client not available');
            }

            const response = await window.api.post('/users', {
                name,
                email,
                password,
                role_id: parseInt(roleId)
            });

            if (response.status >= 200 && response.status < 300) {
                const message = response.data?.message || 'User created successfully';
                
                if (window.showAlert) {
                    window.showAlert(message, 'success');
                }

                submitText.textContent = 'Success! Redirecting...';

                setTimeout(() => {
                    window.location.href = '/users';
                }, 800);
            } else {
                throw new Error('Unexpected response status');
            }
        } catch (error) {
            console.error('Create error:', error);

            isSubmitting = false;
            submitBtn.disabled = false;
            submitText.textContent = 'Create User';
            submitSpinner.classList.add('hidden');

            let errorMsg = 'Failed to create user';
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
                alert(errorMsg);
            }
        }
    });

    waitForAPI(() => {
        loadRoles();
    });
});
</script>
@endsection
