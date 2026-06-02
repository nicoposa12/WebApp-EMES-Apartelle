import { reactive, readonly } from 'vue';
import axios from 'axios';
import { generateDeviceFingerprint } from '../utils/deviceFingerprint';

const state = reactive({
    user: JSON.parse(localStorage.getItem('user')) || null,
    token: localStorage.getItem('token') || null,
    isAuthenticated: !!localStorage.getItem('token'),
});

const setToken = (token) => {
    state.token = token;
    state.isAuthenticated = true;
    localStorage.setItem('token', token);
    axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
};

const setUser = (user) => {
    state.user = user;
    localStorage.setItem('user', JSON.stringify(user));
};

const clearAuth = () => {
    state.user = null;
    state.token = null;
    state.isAuthenticated = false;
    localStorage.removeItem('token');
    localStorage.removeItem('user');
    delete axios.defaults.headers.common['Authorization'];
};

// Initialize axios header if token exists
if (state.token) {
    axios.defaults.headers.common['Authorization'] = `Bearer ${state.token}`;
}

export const useAuth = () => {
    const login = async (credentials) => {
        const fingerprint = await generateDeviceFingerprint();
        const response = await axios.post('/api/login', {
            ...credentials,
            device_fingerprint: fingerprint,
        });
        if (response.data.mfa_required) {
            return response.data;
        }
        setToken(response.data.access_token);
        setUser(response.data.user);
        return response.data;
    };

    const verifyOtp = async (data) => {
        const fingerprint = await generateDeviceFingerprint();
        const response = await axios.post('/api/login/verify-otp', {
            ...data,
            device_fingerprint: fingerprint,
        });
        setToken(response.data.access_token);
        setUser(response.data.user);
        return response.data;
    };

    const register = async (userData) => {
        const response = await axios.post('/api/register', userData);
        setToken(response.data.access_token);
        setUser(response.data.user);
        return response.data;
    };

    const logout = async () => {
        try {
            await axios.post('/api/logout');
        } finally {
            clearAuth();
        }
    };

    const fetchUser = async () => {
        try {
            const response = await axios.get('/api/user');
            setUser(response.data);
        } catch (error) {
            clearAuth();
            throw error;
        }
    };

    return {
        state: readonly(state),
        login,
        verifyOtp,
        register,
        logout,
        fetchUser,
        setToken,
        setUser,
    };
};
