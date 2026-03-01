// API Service dengan Axios
import axios from 'axios';

// Setup Axios instance
const api = axios.create({
    baseURL: '/api',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    },
});

// Request interceptor - Add token to headers
api.interceptors.request.use(
    (config) => {
        const token = localStorage.getItem('auth_token');
        if (token) {
            config.headers.Authorization = `Bearer ${token}`;
        }
        return config;
    },
    (error) => {
        return Promise.reject(error);
    }
);

// Response interceptor - Handle errors
api.interceptors.response.use(
    (response) => {
        return response;
    },
    (error) => {
        if (error.response) {
            // Unauthorized - redirect to login
            if (error.response.status === 401) {
                localStorage.removeItem('auth_token');
                localStorage.removeItem('user');
                // window.location.href = '/login';
            }
        }
        return Promise.reject(error);
    }
);

export default api;
