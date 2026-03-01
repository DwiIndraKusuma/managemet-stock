@extends('layouts.auth')

@section('title', 'Login')
@section('subtitle', 'Sign in to your account')

@section('content')
<div class="bg-white py-8 px-6 shadow rounded-lg">
    <form id="login-form" class="space-y-6" onsubmit="return false;">
        @csrf

        <!-- Email -->
        <div>
            <label class="block text-sm font-medium text-gray-700">Email</label>
            <input id="email" type="email" required
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
            <p id="email-error" class="mt-1 text-sm text-red-600 hidden"></p>
        </div>

        <!-- Password -->
        <div>
            <label class="block text-sm font-medium text-gray-700">Password</label>
            <input id="password" type="password" required
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
            <p id="password-error" class="mt-1 text-sm text-red-600 hidden"></p>
        </div>

        <!-- General Error -->
        <div id="general-error" class="hidden bg-red-50 border border-red-400 text-red-700 px-4 py-3 rounded">
            <strong>Login Failed:</strong>
            <span id="general-error-message"></span>
        </div>

        <!-- Button -->
        <button id="login-btn" type="button"
            class="w-full py-2 px-4 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
            Sign in
        </button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {

    const btn = document.getElementById('login-btn');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');

    // function showError(message) {
    //     const box = document.getElementById('general-error');
    //     const msg = document.getElementById('general-error-message');
    //     msg.textContent = message;
    //     box.classList.remove('hidden');
    //     box.scrollIntoView({ behavior: 'smooth', block: 'center' });
    // }

    function clearErrors() {
        document.getElementById('general-error').classList.add('hidden');
        document.getElementById('email-error').classList.add('hidden');
        document.getElementById('password-error').classList.add('hidden');
        emailInput.classList.remove('border-red-500');
        passwordInput.classList.remove('border-red-500');
    }

    async function handleLogin() {

        clearErrors();

        const email = emailInput.value.trim();
        const password = passwordInput.value.trim();

        // if (!email || !password) {
        //     showError('Email dan password wajib diisi.');
        //     return;
        // }

        btn.disabled = true;
        const originalText = btn.innerHTML;
        btn.innerHTML = 'Signing in...';

        try {

            if (!window.api) {
                throw new Error('API tidak tersedia.');
            }

            const response = await window.api.post('/auth/login', {
                email,
                password
            });

            if (response.data && response.data.success) {

                const { token, user } = response.data.data;

                localStorage.setItem('auth_token', token);
                localStorage.setItem('user', JSON.stringify(user));

                // Set session cookie for web routes
                try {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                    const setSessionResponse = await fetch('/auth/set-session', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken || '',
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify({ token: token })
                    });

                    const sessionResult = await setSessionResponse.json();

                    if (sessionResult.success) {
                        // Redirect to dashboard after session is set
                        window.location.replace('/dashboard');
                    } else {
                        throw new Error(sessionResult.message || 'Failed to set session');
                    }
                } catch (sessionError) {
                    console.error('Error setting session:', sessionError);
                    // Even if session setup fails, try to redirect
                    // The dashboard might authenticate via token in localStorage
                    window.location.replace('/dashboard');
                }

            } else {
                throw { response: response };
            }

        } catch (error) {

            let message = 'Login gagal.';

            if (error.response) {
                const status = error.response.status;
                const data = error.response.data || {};

                if (status === 401) {
                    message = data.message || 'Email atau password salah.';
                } 
                else if (status === 403) {
                    message = data.message || 'User tidak aktif.';
                } 
                else if (status === 422 && data.errors) {

                    if (data.errors.email) {
                        document.getElementById('email-error').textContent = data.errors.email[0];
                        document.getElementById('email-error').classList.remove('hidden');
                        emailInput.classList.add('border-red-500');
                    }

                    if (data.errors.password) {
                        document.getElementById('password-error').textContent = data.errors.password[0];
                        document.getElementById('password-error').classList.remove('hidden');
                        passwordInput.classList.add('border-red-500');
                    }

                    message = data.message || 'Data tidak valid.';
                } 
                else {
                    message = data.message || message;
                }

            } else if (error.message) {
                message = error.message;
            }

            // showError(message);

            if (window.showAlert) {
                window.showAlert(message, 'error');
            }

        } finally {
            btn.disabled = false;
            btn.innerHTML = originalText;

            return false;
        }
    }

    // Click login
    btn.addEventListener('click', handleLogin);

    // Support tekan Enter
    passwordInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            handleLogin();
        }
    });

});
</script>
@endsection