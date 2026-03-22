<template>
  <div class="auth-page d-flex align-items-center justify-content-center">
    <!-- Immersive Background Overlay -->
    <div class="auth-bg-overlay"></div>

    <!-- Back to Home Button -->
    <router-link to="/" class="position-absolute top-0 start-0 m-4 text-white text-decoration-none z-3 d-flex align-items-center gap-2 hover-opacity">
      <i class="bi bi-arrow-left fs-4 bg-white-glass p-2 rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;"></i>
      <span class="fw-bold text-uppercase small tracking-wider d-none d-md-inline text-shadow-sm">Back to Home</span>
    </router-link>
    
    <div class="auth-card-container animate-fade-in">
      <div class="auth-card glass-card shadow-2xl overflow-hidden">
        <div class="row g-0">
          <!-- Left Branding Section -->
          <div class="col-lg-5 d-none d-lg-flex bg-gold-gradient p-4 flex-column justify-content-between text-white">
            <div class="brand">
              <img src="/images/EMES logo.png" alt="EME's Apartelle" class="auth-logo mb-3 shadow-sm">
              <h4 class="serif-font fw-bold mb-0 fs-4">EME's</h4>
              <p class="small text-uppercase tracking-widest opacity-75" style="font-size: 0.65rem;">Apartelle</p>
            </div>
            
            <div class="branding-content">
              <h2 class="serif-font fs-2 fw-bold mb-3">Welcome Back</h2>
              <p class="opacity-90 pe-lg-4 small" style="line-height: 1.6;">Sign in to your account to manage bookings, track payments, and access exclusive member privileges.</p>
              
              <div class="brand-stats mt-4 d-flex gap-4">
                <div class="stat-item">
                  <h4 class="mb-0 fw-bold fs-4 serif-font">24/7</h4>
                  <span class="small opacity-75 text-uppercase tracking-widest" style="font-size: 0.6rem;">Front Desk</span>
                </div>
                <div class="stat-item">
                  <h4 class="mb-0 fw-bold fs-4 serif-font">4.9</h4>
                  <span class="small opacity-75 text-uppercase tracking-widest" style="font-size: 0.6rem;">Guest Rating</span>
                </div>
              </div>
            </div>

            <div class="footer-note opacity-60 small letter-spacing-wide" style="font-size: 0.7rem;">
              &copy; 2026 EME's Apartelle.
            </div>
          </div>

          <!-- Right Form Section -->
          <div class="col-lg-7 p-4 bg-white-glass">
            <div class="form-header mb-4">
              <h2 class="serif-font fw-bold text-secondary-dark mb-1 fs-3">Account Sign In</h2>
              <p class="text-muted small">Enter your credentials below to access your dashboard.</p>
            </div>

            <form @submit.prevent="handleLogin">
              <div class="mb-3 custom-input-group">
                <label class="form-label text-secondary-dark opacity-75">Email Address</label>
                <div class="input-modern-wrapper">
                  <i class="bi bi-envelope icon-prefix"></i>
                  <input v-model="form.email" type="email" class="form-control-modern ps-5 py-2" placeholder="name@example.com" required>
                </div>
              </div>

              <div class="mb-3 custom-input-group">
                <div class="d-flex justify-content-between align-items-center mb-1">
                  <label class="form-label text-secondary-dark opacity-75">Password</label>
                  <router-link to="/forgot-password" class="text-gold small fw-bold text-decoration-none hover-underline" style="font-size: 0.75rem;">Forgotten Password?</router-link>
                </div>
                <div class="input-modern-wrapper">
                  <i class="bi bi-shield-lock icon-prefix"></i>
                  <input :type="showPassword ? 'text' : 'password'" v-model="form.password" class="form-control-modern ps-5 pe-5 py-2" placeholder="••••••••" required>
                  <button type="button" @click="showPassword = !showPassword" class="btn-toggle-pass">
                    <i class="bi" :class="showPassword ? 'bi-eye-slash' : 'bi-eye'"></i>
                  </button>
                </div>
              </div>

              <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check custom-check">
                  <input class="form-check-input" type="checkbox" id="remember" v-model="form.remember">
                  <label class="form-check-label small text-muted pt-1" style="font-size: 0.8rem;" for="remember">Remember me</label>
                </div>
              </div>

              <button 
                type="submit" 
                class="btn btn-gold-modern w-100 py-2 mb-4 d-flex align-items-center justify-content-center gap-2 shadow-lg"
                :disabled="loading"
              >
                <span v-if="loading" class="spinner-border spinner-border-sm"></span>
                <span class="fw-bold text-uppercase tracking-widest small">{{ loading ? 'Authenticating...' : 'Sign In To Account' }}</span>
              </button>

              <!-- Social Login Placeholder -->
              <div class="separator mb-4">
                <span class="small text-muted px-2 serif-font fst-italic bg-white position-relative z-1" style="font-size: 0.8rem;">Or continue with</span>
              </div>

              <div class="row g-2 mb-4">
                <div class="col-6">
                  <button @click="loginWithGoogle" type="button" class="btn btn-outline-modern w-100 py-2 d-flex align-items-center justify-content-center gap-2 hover-lift">
                    <img src="https://www.google.com/favicon.ico" width="16" alt="Google"> <span class="small fw-bold">Google</span>
                  </button>
                </div>
                <div class="col-6">
                  <button type="button" class="btn btn-outline-modern w-100 py-2 d-flex align-items-center justify-content-center gap-2 hover-lift">
                    <i class="bi bi-apple fs-6"></i> <span class="small fw-bold">Apple</span>
                  </button>
                </div>
              </div>

              <p class="text-center text-muted small mb-0" style="font-size: 0.8rem;">
                New to EME's Apartelle? 
                <router-link to="/register" class="text-gold fw-bold text-decoration-none hover-underline">Create an Account</router-link>
              </p>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuth } from '../../store/auth';
import Swal from 'sweetalert2';
import axios from 'axios';

const router = useRouter();
const route = useRoute();
const { login, setToken, fetchUser } = useAuth();

const form = reactive({
  email: '',
  password: '',
  remember: false
});

const showPassword = ref(false);
const loading = ref(false);

onMounted(async () => {
  // Check if there's a token in the URL (from social callback redirect)
  const urlToken = route.query.token;
  if (urlToken) {
    loading.value = true;
    try {
      setToken(urlToken);
      await fetchUser();
      
      Swal.fire({
        icon: 'success',
        title: 'Social Login Successful',
        text: 'Welcome to EME\'s Apartelle!',
        timer: 2000,
        toast: true,
        position: 'top-end',
        showConfirmButton: false
      });
      
      router.push('/rooms');
    } catch (e) {
      console.error(e);
      Swal.fire({ icon: 'error', title: 'Auth Failed', text: 'Social login was unsuccessful.' });
    } finally {
      loading.value = false;
    }
  }

  // Handle errors from callback
  if (route.query.error) {
    Swal.fire({
      icon: 'error',
      title: 'Authentication Error',
      text: 'The social login process failed. Please try again or use your email.',
      confirmButtonColor: '#BC9151'
    });
  }
});

const loginWithGoogle = async () => {
  try {
    const response = await axios.get('/api/auth/google');
    // The response is the Google OAuth URL
    window.location.href = response.data;
  } catch (error) {
    Swal.fire({
      icon: 'error',
      title: 'Provider Error',
      text: 'Could not connect to Google at this moment.',
      confirmButtonColor: '#BC9151'
    });
  }
};

const handleLogin = async () => {
  loading.value = true;
  try {
    const response = await login({
      email: form.email,
      password: form.password
    });
    
    // Check if the user is an admin trying to use the guest portal
    if (response.user && response.user.role === 'admin') {
      // Log them out immediately
      const { logout } = useAuth();
      await logout();
      throw new Error('Administrators must use the dedicated Admin Portal to sign in.');
    }
    
    Swal.fire({
      icon: 'success',
      title: 'Authenticated',
      text: 'Redirecting to your dashboard...',
      showConfirmButton: false,
      timer: 1500,
      toast: true,
      position: 'top-end'
    });

    router.push('/rooms');
  } catch (error) {
    Swal.fire({
      icon: 'error',
      title: 'Access Denied',
      text: error.response?.data?.message || 'Invalid credentials. Please try again.',
      confirmButtonColor: '#BC9151'
    });
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
.auth-page {
  min-height: 100vh;
  position: relative;
  overflow: hidden;
  background: #0f172a;
}

.auth-bg-overlay {
  position: absolute;
  top: 0; left: 0; right: 0; bottom: 0;
  background: radial-gradient(circle at top right, rgba(188, 145, 81, 0.2), transparent 40%),
              url('https://images.unsplash.com/photo-1566665797739-1674de7a421a?auto=format&fit=crop&q=80&w=2000') center/cover;
  filter: brightness(0.5);
  z-index: 1;
}

.auth-card-container {
  position: relative;
  z-index: 2;
  width: 100%;
  max-width: 800px;
  padding: 1.5rem;
}

.glass-card {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(12px);
  border-radius: 20px;
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.bg-gold-gradient {
  background: linear-gradient(135deg, #BC9151 0%, #9A7640 100%);
  position: relative;
}

.auth-logo {
  width: 44px;
  height: 44px;
  object-fit: contain;
  border-radius: 10px;
  background: white;
  padding: 3px;
}

/* Modern Inputs */
.input-modern-wrapper {
  position: relative;
}

.icon-prefix {
  position: absolute;
  left: 1.25rem;
  top: 50%;
  transform: translateY(-50%);
  color: #94a3b8;
  font-size: 1.1rem;
}

.form-control-modern {
  width: 100%;
  padding: 0.65rem 1.25rem;
  background: #f8fafc;
  border: 1.5px solid #e2e8f0;
  border-radius: 10px;
  color: #1e293b;
  font-weight: 500;
  font-size: 0.95rem;
  transition: all 0.3s ease;
}

.form-control-modern:focus {
  background: white;
  border-color: #BC9151;
  box-shadow: 0 0 0 4px rgba(188, 145, 81, 0.1);
  outline: none;
}

.btn-toggle-pass {
  position: absolute;
  right: 1rem;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  color: #94a3b8;
  padding: 0.25rem;
}

.form-label {
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  color: #64748b;
  letter-spacing: 0.5px;
  margin-bottom: 0.6rem;
}

/* Buttons */
.btn-gold-modern {
  background: #BC9151;
  color: white;
  border-radius: 14px;
  font-weight: 700;
  border: none;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.btn-gold-modern:hover {
  background: #9A7640;
  transform: translateY(-2px);
  box-shadow: 0 10px 25px rgba(188, 145, 81, 0.3);
}

.btn-outline-modern {
  background: white;
  border: 1.5px solid #e2e8f0;
  color: #475569;
  border-radius: 12px;
  font-weight: 600;
  transition: all 0.3s ease;
}

.btn-outline-modern:hover {
  background: #f1f5f9;
  border-color: #cbd5e1;
}

/* UI Elements */
.separator {
  display: flex;
  align-items: center;
  text-align: center;
}

.separator::before, .separator::after {
  content: '';
  flex: 1;
  border-bottom: 1.5px solid #f1f5f9;
}

.hover-underline:hover {
  text-decoration: underline !important;
}

/* Animations */
.animate-fade-in {
  animation: fadeIn 0.8s ease-out;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
