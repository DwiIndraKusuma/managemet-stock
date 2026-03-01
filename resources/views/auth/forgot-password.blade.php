@extends('layouts.auth')

@section('title', 'Forgot Password')

@section('subtitle', 'Enter your email to receive a password reset link')

@section('content')
<div class="bg-white py-8 px-6 shadow rounded-lg">
    <form id="forgot-password-form" class="space-y-6">
        @csrf
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Email address</label>
            <input id="email" name="email" type="email" required 
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            <p class="mt-1 text-sm text-red-600 hidden" id="email-error"></p>
        </div>

        <div>
            <button type="submit" id="submit-btn" 
                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Send Reset Link
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

document.getElementById('forgot-password-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const email = document.getElementById('email').value;
    const btn = document.getElementById('submit-btn');
    const originalText = btn.innerHTML;
    
    // Clear previous errors
    document.getElementById('email-error').classList.add('hidden');
    
    // Show loading
    btn.disabled = true;
    btn.innerHTML = 'Sending...';
    
    try {
        const result = await window.AuthService.forgotPassword(email);
        
        if (result.success) {
            if (window.showAlert) {
                window.showAlert(result.message || 'Reset link sent to your email', 'success');
            } else {
                alert(result.message || 'Reset link sent to your email');
            }
            setTimeout(() => {
                window.location.href = '/login';
            }, 2000);
        } else {
            const errorMsg = result.message || 'Failed to send reset link';
            if (window.showAlert) {
                window.showAlert(errorMsg, 'error');
            } else {
                alert(errorMsg);
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
