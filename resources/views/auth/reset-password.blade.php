@extends('layouts.auth')

@section('title', 'Reset Password')

@section('subtitle', 'Enter your new password')

@section('content')
<div class="bg-white py-8 px-6 shadow rounded-lg">
    <form id="reset-password-form" class="space-y-6">
        @csrf
        <input type="hidden" id="token" value="{{ $token ?? '' }}">
        
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Email address</label>
            <input id="email" name="email" type="email" required 
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            <p class="mt-1 text-sm text-red-600 hidden" id="email-error"></p>
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-gray-700">New Password</label>
            <input id="password" name="password" type="password" required 
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            <p class="mt-1 text-sm text-red-600 hidden" id="password-error"></p>
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm Password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required 
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            <p class="mt-1 text-sm text-red-600 hidden" id="password_confirmation-error"></p>
        </div>

        <div>
            <button type="submit" id="submit-btn" 
                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Reset Password
            </button>
        </div>

        <div class="text-center">
            <a href="/login" class="text-sm text-indigo-600 hover:text-indigo-500">
                Back to login
            </a>
        </div>
    </form>
</div>

<script >
// AuthService is available via window.AuthService

document.getElementById('reset-password-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const token = document.getElementById('token').value;
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    const passwordConfirmation = document.getElementById('password_confirmation').value;
    const btn = document.getElementById('submit-btn');
    const originalText = btn.innerHTML;
    
    // Clear previous errors
    ['email', 'password', 'password_confirmation'].forEach(field => {
        const errorEl = document.getElementById(`${field}-error`);
        if (errorEl) errorEl.classList.add('hidden');
    });
    
    // Show loading
    btn.disabled = true;
    btn.innerHTML = 'Resetting...';
    
    try {
        const result = await window.AuthService.resetPassword({
            token,
            email,
            password,
            password_confirmation: passwordConfirmation
        });
        
        if (result.success) {
            if (window.showAlert) {
                window.showAlert(result.message || 'Password reset successful', 'success');
            } else {
                alert(result.message || 'Password reset successful');
            }
            setTimeout(() => {
                window.location.href = '/login';
            }, 2000);
        } else {
            const errorMsg = result.message || 'Failed to reset password';
            if (window.showAlert) {
                window.showAlert(errorMsg, 'error');
            } else {
                alert(errorMsg);
            }
            
            // Show field errors
            if (result.errors) {
                Object.keys(result.errors).forEach(field => {
                    const errorEl = document.getElementById(`${field}-error`);
                    if (errorEl) {
                        errorEl.textContent = result.errors[field][0];
                        errorEl.classList.remove('hidden');
                    }
                });
            }
        }
    } catch (error) {
        const errorMsg = error.response?.data?.message || 'An error occurred. Please try again.';
        if (window.showAlert) {
            window.showAlert(errorMsg, 'error');
        } else {
            alert(errorMsg);
        }
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
});
</script>
@endsection
