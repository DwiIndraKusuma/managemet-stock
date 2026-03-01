import './bootstrap';
import '../css/app.css';
import api from './api.js';
import AuthService from './auth.js';

// Make services available globally
window.api = api;
window.AuthService = AuthService;

// Alert/Toast notification function
window.showAlert = function(message, type = 'info') {
    // Try to find alert container, create if not exists
    let alertContainer = document.getElementById('alert-container');
    if (!alertContainer) {
        // Try to find main element and prepend alert container
        const main = document.querySelector('main');
        if (main) {
            alertContainer = document.createElement('div');
            alertContainer.id = 'alert-container';
            main.insertBefore(alertContainer, main.firstChild);
        } else {
            // Fallback to body
            alertContainer = document.createElement('div');
            alertContainer.id = 'alert-container';
            alertContainer.className = 'fixed top-4 right-4 z-50';
            document.body.appendChild(alertContainer);
        }
    }

    const bgColor = {
        success: 'bg-green-500',
        error: 'bg-red-500',
        warning: 'bg-yellow-500',
        info: 'bg-blue-500'
    }[type] || 'bg-blue-500';

    const alertDiv = document.createElement('div');
    alertDiv.className = `${bgColor} text-white px-6 py-4 rounded-lg shadow-lg mb-4 flex items-center justify-between`;
    alertDiv.style.animation = 'slideIn 0.3s ease-out';
    alertDiv.innerHTML = `
        <span>${message}</span>
        <button onclick="this.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    `;

    alertContainer.appendChild(alertDiv);

    // Auto remove after timeout (longer for errors)
    const timeout = type === 'error' ? 10000 : 5000; // 10 seconds for errors, 5 seconds for others
    setTimeout(() => {
        if (alertDiv.parentElement) {
            alertDiv.style.animation = 'slideOut 0.3s ease-out';
            setTimeout(() => alertDiv.remove(), 300);
        }
    }, timeout);
};

// Add CSS animation if not exists
if (!document.getElementById('alert-animations')) {
    const style = document.createElement('style');
    style.id = 'alert-animations';
    style.textContent = `
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
    `;
    document.head.appendChild(style);
}

// Initialize logout button handler
document.addEventListener('DOMContentLoaded', function() {
    const logoutBtn = document.getElementById('logout-btn');
    if (logoutBtn && !logoutBtn.hasAttribute('data-listener-attached')) {
        logoutBtn.setAttribute('data-listener-attached', 'true');
        logoutBtn.addEventListener('click', async function(e) {
            e.preventDefault();
            if (confirm('Are you sure you want to logout?')) {
                try {
                    if (window.AuthService) {
                        await window.AuthService.logout();
                    } else {
                        // Fallback: clear storage and redirect
                        localStorage.removeItem('auth_token');
                        localStorage.removeItem('user');
                        window.location.href = '/login';
                    }
                } catch (error) {
                    console.error('Logout error:', error);
                    // Force logout even if API call fails
                    localStorage.removeItem('auth_token');
                    localStorage.removeItem('user');
                    window.location.href = '/login';
                }
            }
        });
    }
});