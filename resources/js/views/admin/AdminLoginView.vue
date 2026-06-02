<template>
  <div class="admin-login-page d-flex align-items-center justify-content-center">
    <!-- Sophisticated Professional Background -->
    <div class="admin-bg-overlay"></div>
    
    <div class="admin-auth-container animate-fade-up">
      <div class="admin-login-card shadow-2xl">
        <div class="card-header-accent"></div>
        
        <div class="p-5">
          <div class="text-center mb-5">
            <div class="admin-logo-badge mb-3 mx-auto shadow-lg">
              <img src="/images/emes-logo.png" alt="Logo" class="w-100 h-100 object-fit-contain">
            </div>
            <h2 class="serif-font fw-bold text-white mb-1">Administrative Control</h2>
            <p class="text-white-50 small text-uppercase tracking-widest">Secure Access Gateway</p>
          </div>

          <form @submit.prevent="handleAdminLogin">
            <div class="mb-4">
              <label class="form-label text-gold-muted small fw-bold text-uppercase tracking-wider">Admin ID / Email</label>
              <div class="admin-input-group">
                <i class="bi bi-person-badge admin-icon"></i>
                <input v-model="form.email" type="email" class="admin-input" placeholder="administrator@emes.com" required>
              </div>
            </div>

            <div class="mb-5">
              <label class="form-label text-gold-muted small fw-bold text-uppercase tracking-wider">Secret Key / Password</label>
              <div class="admin-input-group">
                <i class="bi bi-shield-lock-fill admin-icon"></i>
                <input :type="showPassword ? 'text' : 'password'" v-model="form.password" class="admin-input" placeholder="••••••••" required>
                <button type="button" @click="showPassword = !showPassword" class="btn-toggle-admin-pass">
                  <i class="bi" :class="showPassword ? 'bi-eye-slash' : 'bi-eye'"></i>
                </button>
              </div>
            </div>

            <button 
              type="submit" 
              class="btn-admin-submit w-100 py-3 d-flex align-items-center justify-content-center gap-3"
              :disabled="loading"
            >
              <span v-if="loading" class="spinner-border spinner-border-sm" role="status"></span>
              <span class="text-uppercase fw-bold tracking-widest small">{{ loading ? 'Verifying Credentials...' : 'Access Admin Panel' }}</span>
              <i v-if="!loading" class="bi bi-arrow-right fs-5"></i>
            </button>
          </form>

          <div class="mt-5 text-center">
            <router-link to="/" class="text-white-50 text-decoration-none small hover-text-gold transition-all">
              <i class="bi bi-house-door me-2"></i> Return to Main Website
            </router-link>
          </div>
        </div>
      </div>
      
      <div class="text-center mt-4 text-white-50 x-small tracking-widest opacity-50">
        LEVEL 4 SECURED INTERFACE &bull; AUTHORIZED PERSONNEL ONLY
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { useRouter } from 'vue-router';
import { useAuth } from '../../store/auth';
import Swal from 'sweetalert2';

const router = useRouter();
const { login } = useAuth();

const form = reactive({
  email: '',
  password: ''
});

const showPassword = ref(false);
const loading = ref(false);

const handleAdminLogin = async () => {
  loading.value = true;
  try {
    const response = await login({
      email: form.email,
      password: form.password
    });
    
    if (response.user && !['admin', 'staff'].includes(response.user.role)) {
      // If a guest accidentally tries to log in here, log them out immediately
      const { logout: forceLogout } = useAuth();
      await forceLogout();
      throw new Error('Access denied. This portal is for administrative and staff use only.');
    }

    Swal.fire({
      icon: 'success',
      title: 'Access Granted',
      text: 'Admin session initialized.',
      toast: true,
      position: 'top-end',
      timer: 1500,
      showConfirmButton: false,
      background: '#1A2634',
      color: '#fff'
    });

    router.push('/admin');
  } catch (error) {
    Swal.fire({
      icon: 'error',
      title: 'Authentication Failed',
      text: error.message || error.response?.data?.message || 'Invalid administrator credentials.',
      confirmButtonColor: '#BC9151',
      background: '#1A2634',
      color: '#fff'
    });
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
.admin-login-page {
  min-height: 100vh;
  background-color: #0c121d;
  position: relative;
  overflow: hidden;
}

.admin-bg-overlay {
  position: absolute;
  top: 0; left: 0; right: 0; bottom: 0;
  background: 
    radial-gradient(circle at 10% 20%, rgba(188, 145, 81, 0.05) 0%, transparent 40%),
    radial-gradient(circle at 90% 80%, rgba(188, 145, 81, 0.05) 0%, transparent 40%),
    linear-gradient(45deg, #0c121d 0%, #1a2634 100%);
  z-index: 1;
}

.admin-auth-container {
  position: relative;
  z-index: 2;
  width: 100%;
  max-width: 480px;
  padding: 1.5rem;
}

.admin-login-card {
  background: rgba(26, 38, 52, 0.85);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: 24px;
  overflow: hidden;
  position: relative;
}

.card-header-accent {
  height: 6px;
  background: linear-gradient(90deg, #BC9151, #9A7640, #BC9151);
  width: 100%;
}

.admin-logo-badge {
  width: 64px;
  height: 64px;
  background: white;
  padding: 10px;
  border-radius: 18px;
}

.admin-input-group {
  position: relative;
  margin-top: 0.5rem;
}

.admin-icon {
  position: absolute;
  left: 1.25rem;
  top: 50%;
  transform: translateY(-50%);
  color: #BC9151;
  font-size: 1.2rem;
  opacity: 0.8;
}

.admin-input {
  width: 100%;
  background: rgba(12, 18, 29, 0.6);
  border: 1.5px solid rgba(255, 255, 255, 0.1);
  padding: 0.85rem 1.25rem 0.85rem 3.5rem;
  border-radius: 12px;
  color: white;
  font-size: 0.95rem;
  transition: all 0.3s ease;
}

.admin-input:focus {
  outline: none;
  border-color: #BC9151;
  background: rgba(12, 18, 29, 0.9);
  box-shadow: 0 0 0 4px rgba(188, 145, 81, 0.1);
}

.text-gold-muted {
  color: rgba(188, 145, 81, 0.7);
}

.btn-toggle-admin-pass {
  position: absolute;
  right: 1.25rem;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  color: rgba(255, 255, 255, 0.3);
  transition: color 0.3s;
}

.btn-toggle-admin-pass:hover {
  color: white;
}

.btn-admin-submit {
  background: linear-gradient(135deg, #BC9151 0%, #9A7640 100%);
  border: none;
  border-radius: 12px;
  color: white;
  transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
}

.btn-admin-submit:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 10px 30px rgba(188, 145, 81, 0.3);
  filter: brightness(1.1);
}

.btn-admin-submit:active {
  transform: translateY(0);
}

.hover-text-gold:hover {
  color: #BC9151 !important;
}

.animate-fade-up {
  animation: fadeUp 0.6s cubic-bezier(0.165, 0.84, 0.44, 1) forwards;
}

@keyframes fadeUp {
  from { opacity: 0; transform: translateY(30px); }
  to { opacity: 1; transform: translateY(0); }
}

.x-small {
  font-size: 0.65rem;
}
</style>
