// Auth Service
import api from './api.js';

const AuthService = {
    // Login
    async login(email, password) {
        try {
            const response = await api.post('/auth/login', { email, password });
            if (response.data.success) {
                const { user, token } = response.data.data;
                localStorage.setItem('auth_token', token);
                localStorage.setItem('user', JSON.stringify(user));
                return { success: true, data: response.data.data };
            }
            return { success: false, message: response.data.message };
        } catch (error) {
            return {
                success: false,
                message: error.response?.data?.message || 'Login failed',
                errors: error.response?.data?.errors || {}
            };
        }
    },

    // Logout
    async logout() {
        try {
            // Call API logout to revoke token
            try {
                await api.post('/auth/logout');
            } catch (error) {
                console.warn('API logout error (may be expected):', error);
            }
            
            // Call web logout to destroy session
            try {
                await fetch('/auth/logout', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin'
                });
            } catch (error) {
                console.warn('Web logout error (may be expected):', error);
            }
        } catch (error) {
            console.error('Logout error:', error);
        } finally {
            // Always clear localStorage and redirect
            localStorage.removeItem('auth_token');
            localStorage.removeItem('user');
            // Use replace to prevent back button issues
            window.location.replace('/login');
        }
    },

    // Get current user
    getCurrentUser() {
        const userStr = localStorage.getItem('user');
        return userStr ? JSON.parse(userStr) : null;
    },

    // Check if authenticated
    isAuthenticated() {
        return !!localStorage.getItem('auth_token');
    },

    // Get token
    getToken() {
        return localStorage.getItem('auth_token');
    },

    // Forgot password
    async forgotPassword(email) {
        try {
            const response = await api.post('/auth/forgot-password', { email });
            return { success: true, message: response.data.message };
        } catch (error) {
            return {
                success: false,
                message: error.response?.data?.message || 'Failed to send reset link'
            };
        }
    },

    // Reset password
    async resetPassword(data) {
        try {
            const response = await api.post('/auth/reset-password', data);
            return { success: true, message: response.data.message };
        } catch (error) {
            return {
                success: false,
                message: error.response?.data?.message || 'Failed to reset password',
                errors: error.response?.data?.errors || {}
            };
        }
    }
};

export default AuthService;
