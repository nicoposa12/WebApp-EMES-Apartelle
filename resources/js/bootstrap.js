import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Global response interceptor to handle authentication errors (401 Unauthorized)
window.axios.interceptors.response.use(
    response => response,
    error => {
        if (error.response && error.response.status === 401) {
            // Clear invalid local auth state
            localStorage.removeItem('token');
            localStorage.removeItem('user');
            delete axios.defaults.headers.common['Authorization'];
            
            // Redirect to login if on a protected route
            const path = window.location.pathname;
            if (path.startsWith('/admin') && path !== '/admin/login') {
                window.location.href = '/admin/login';
            } else if (['/profile', '/my-bookings', '/notifications', '/book-now'].some(route => path.startsWith(route))) {
                window.location.href = '/login';
            }
        }
        return Promise.reject(error);
    }
);
