<template>
  <div class="auth-page d-flex align-items-center justify-content-center">
    <div class="auth-bg-overlay"></div>
    
    <div class="auth-card-container animate-fade-in">
      <div class="auth-card glass-card shadow-2xl overflow-hidden">
        <div class="row g-0">
          <div class="col-lg-5 d-none d-lg-flex bg-gold-gradient p-5 flex-column justify-content-between text-white">
            <div class="brand">
              <div class="logo-box-white mb-3">E</div>
              <h4 class="serif-font fw-bold mb-0">EME's</h4>
              <p class="small text-uppercase tracking-widest opacity-75">Apartelle</p>
            </div>
            
            <div class="branding-content">
              <h2 class="serif-font display-5 fw-bold mb-3">Set New Password</h2>
              <p class="lead opacity-80 small">You're almost there. Create a strong, unique password to secure your account going forward.</p>
            </div>

            <div class="footer-note opacity-50 small">
              &copy; 2026 EME's Apartelle. Security first.
            </div>
          </div>

          <div class="col-lg-7 p-4 p-md-5 bg-white-glass">
            <div class="form-header mb-5">
              <h3 class="fw-bold text-dark mb-1">Reset Password</h3>
              <p class="text-muted small">Enter your new credentials below.</p>
            </div>

            <form @submit.prevent="handleSubmit">
              <div class="mb-4 custom-input-group">
                <label class="form-label">New Password</label>
                <div class="input-modern-wrapper">
                  <i class="bi bi-shield-lock icon-prefix"></i>
                  <input v-model="form.password" type="password" class="form-control-modern ps-5" placeholder="••••••••" required :disabled="loading">
                </div>
              </div>

              <div class="mb-4 custom-input-group">
                <label class="form-label">Confirm New Password</label>
                <div class="input-modern-wrapper">
                  <i class="bi bi-shield-check icon-prefix"></i>
                  <input v-model="form.password_confirmation" type="password" class="form-control-modern ps-5" placeholder="••••••••" required :disabled="loading">
                </div>
              </div>

              <button 
                type="submit" 
                class="btn btn-gold-modern w-100 py-3 mb-4 d-flex align-items-center justify-content-center gap-2"
                :disabled="loading"
              >
                <span v-if="loading" class="spinner-border spinner-border-sm"></span>
                <span class="fw-bold text-uppercase tracking-wider">{{ loading ? 'Updating...' : 'Reset Password' }}</span>
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import Swal from 'sweetalert2';

const route = useRoute();
const router = useRouter();

const form = reactive({
  email: '',
  token: '',
  password: '',
  password_confirmation: ''
});

const loading = ref(false);

onMounted(() => {
  form.email = route.query.email || '';
  form.token = route.params.token || '';
  
  if (!form.token || !form.email) {
    Swal.fire({
      icon: 'error',
      title: 'Invalid Link',
      text: 'This password reset link is invalid or has expired.',
      confirmButtonColor: '#BC9151'
    }).then(() => router.push('/login'));
  }
});

const handleSubmit = async () => {
  if (form.password !== form.password_confirmation) {
    Swal.fire({ icon: 'error', title: 'Error', text: 'Passwords do not match.' });
    return;
  }

  loading.value = true;
  try {
    await axios.post('/api/reset-password', form);
    Swal.fire({
      icon: 'success',
      title: 'Password Updated',
      text: 'Your password has been reset successfully. You can now login.',
      confirmButtonColor: '#BC9151'
    }).then(() => router.push('/login'));
  } catch (error) {
    Swal.fire({
      icon: 'error',
      title: 'Reset Failed',
      text: error.response?.data?.message || 'The link may have expired. Please request a new one.',
      confirmButtonColor: '#BC9151'
    });
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
/* Reuse styles from ForgotPasswordView */
.auth-page { min-height: 100vh; position: relative; overflow: hidden; background: #0f172a; }
.auth-bg-overlay { position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: radial-gradient(circle at top right, rgba(188, 145, 81, 0.2), transparent 40%), url('https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?auto=format&fit=crop&q=80&w=2000') center/cover; filter: brightness(0.5); z-index: 1; }
.auth-card-container { position: relative; z-index: 2; width: 100%; max-width: 900px; padding: 1.5rem; }
.glass-card { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(12px); border-radius: 28px; border: 1px solid rgba(255, 255, 255, 0.2); }
.bg-gold-gradient { background: linear-gradient(135deg, #BC9151 0%, #9A7640 100%); position: relative; }
.logo-box-white { width: 44px; height: 44px; background: white; color: #BC9151; display: flex; align-items: center; justify-content: center; border-radius: 12px; font-weight: 900; font-size: 1.5rem; }
.input-modern-wrapper { position: relative; }
.icon-prefix { position: absolute; left: 1.25rem; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 1.1rem; }
.form-control-modern { width: 100%; padding: 0.875rem 1.25rem; background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 14px; color: #1e293b; font-weight: 500; transition: all 0.3s ease; }
.form-control-modern:focus { background: white; border-color: #BC9151; box-shadow: 0 0 0 4px rgba(188, 145, 81, 0.1); outline: none; }
.form-label { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: 0.5px; margin-bottom: 0.6rem; }
.btn-gold-modern { background: #BC9151; color: white; border-radius: 14px; font-weight: 700; border: none; transition: all 0.3s ease; }
.btn-gold-modern:hover { background: #9A7640; transform: translateY(-2px); box-shadow: 0 10px 25px rgba(188, 145, 81, 0.3); }
.animate-fade-in { animation: fadeIn 0.8s ease-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
</style>
