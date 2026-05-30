<template>
  <div class="auth-page d-flex align-items-center justify-content-center">
    <div class="auth-bg-overlay"></div>

    <router-link to="/login" class="back-home-link z-3">
      <i class="bi bi-arrow-left back-icon"></i>
      <span class="back-label d-none d-md-inline">Back to Login</span>
    </router-link>
    
    <div class="auth-card-container animate-fade-in">
      <div class="auth-card overflow-hidden">
        <div class="row g-0">
          <!-- Left Side Branding -->
          <div class="col-lg-5 d-none d-lg-flex branding-panel flex-column justify-content-between">
            <div class="branding-inner">
              <div class="brand mb-auto">
                <div class="logo-box mb-3">E</div>
                <h4 class="serif-font fw-bold mb-0 text-white">EME's</h4>
                <p class="brand-sub text-uppercase">Apartelle</p>
              </div>
              
              <div class="branding-content mt-auto">
                <h2 class="serif-font fs-2 fw-bold mb-3 text-white">Recover Access</h2>
                <p class="branding-desc">Enter your email address and we'll send you a secure link to reset your password and regain access to your account.</p>
              </div>
            </div>

            <div class="branding-footer">&copy; 2026 EME's Apartelle. Security first.</div>
          </div>

          <!-- Right Side Form -->
          <div class="col-lg-7 form-section">
            <div class="form-inner">
              <div class="form-header mb-5">
                <h3 class="serif-font fw-bold mb-1 fs-3 form-title">Forgot Password</h3>
                <p class="form-subtitle">We'll send a password recovery link to your inbox.</p>
              </div>

              <form @submit.prevent="handleSubmit">
                <div class="mb-4 field-group">
                  <label class="field-label" for="reset-email">Email Address</label>
                  <div class="input-wrapper">
                    <i class="bi bi-envelope field-icon"></i>
                    <input v-model="email" type="email" id="reset-email" class="field-input has-icon" placeholder="your-email@gmail.com" required :disabled="loading" autocomplete="email">
                  </div>
                </div>

                <button 
                  type="submit" 
                  class="btn-primary-action w-100 mb-4"
                  :disabled="loading"
                  :class="{ 'is-loading': loading }"
                >
                  <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                  <span>{{ loading ? 'Sending...' : 'Send Reset Link' }}</span>
                </button>

                <p class="text-center small mb-0 footer-text">
                  Remembered your password? 
                  <router-link to="/login" class="action-link">Back to Login</router-link>
                </p>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';

const email = ref('');
const loading = ref(false);

const handleSubmit = async () => {
  loading.value = true;
  try {
    const response = await axios.post('/api/forgot-password', { email: email.value });
    Swal.fire({
      icon: 'success',
      title: 'Email Sent!',
      text: response.data.message || 'Check your Gmail for the reset link.',
      confirmButtonColor: '#BC9151'
    });
  } catch (error) {
    Swal.fire({
      icon: 'error',
      title: 'Action Failed',
      text: error.response?.data?.message || 'We could not find an account with that email.',
      confirmButtonColor: '#BC9151'
    });
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
.auth-page { min-height: 100vh; position: relative; overflow: hidden; background: #0f172a; }
.auth-bg-overlay { position: absolute; inset: 0; background: radial-gradient(circle at 20% 80%, rgba(188,145,81,0.12), transparent 50%), url('/images/unsplash/hero-resort.jpg') center/cover; filter: brightness(0.45) saturate(1.1); z-index: 1; }
.auth-card-container { position: relative; z-index: 2; width: 100%; max-width: 820px; padding: 1.25rem; }
.auth-card { background: #fff; border-radius: 20px; box-shadow: 0 25px 60px -12px rgba(0,0,0,0.35), 0 0 0 1px rgba(255,255,255,0.08); }

.back-home-link { position: absolute; top: 0; left: 0; margin: 1.25rem; display: flex; align-items: center; gap: 0.5rem; text-decoration: none; color: rgba(255,255,255,0.85); transition: all 0.3s ease; }
.back-home-link:hover { color: #fff; transform: translateX(-2px); }
.back-icon { width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; background: rgba(255,255,255,0.12); backdrop-filter: blur(8px); border: 1px solid rgba(255,255,255,0.15); border-radius: 50%; font-size: 1rem; transition: all 0.3s ease; }
.back-home-link:hover .back-icon { background: rgba(255,255,255,0.2); }
.back-label { font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; text-shadow: 0 1px 3px rgba(0,0,0,0.3); }

.branding-panel { background: linear-gradient(160deg, #BC9151 0%, #8B6A3D 100%); position: relative; overflow: hidden; }
.branding-panel::before { content: ''; position: absolute; inset: 0; background: radial-gradient(circle at 30% 20%, rgba(255,255,255,0.08), transparent 50%), radial-gradient(circle at 70% 80%, rgba(0,0,0,0.12), transparent 50%); pointer-events: none; }
.branding-inner { position: relative; z-index: 1; padding: 2rem 1.75rem; display: flex; flex-direction: column; height: 100%; }
.logo-box { width: 48px; height: 48px; background: white; color: #BC9151; display: flex; align-items: center; justify-content: center; border-radius: 12px; font-weight: 900; font-size: 1.5rem; box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
.brand-sub { font-size: 0.6rem; letter-spacing: 0.25em; color: rgba(255,255,255,0.65); margin-bottom: 0; font-weight: 600; }
.branding-desc { color: rgba(255,255,255,0.8); font-size: 0.85rem; line-height: 1.65; margin-bottom: 0; }
.branding-footer { position: relative; z-index: 1; padding: 0 1.75rem 1.5rem; font-size: 0.68rem; color: rgba(255,255,255,0.45); letter-spacing: 0.03em; }

.form-section { background: #fff; }
.form-inner { padding: 2.25rem 2rem; }
.form-title { color: #1A2634; }
.form-subtitle { color: #8896A6; font-size: 0.85rem; margin-bottom: 0; }

.field-label { display: block; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: #1A2634; margin-bottom: 0.5rem; transition: color 0.3s ease; }
.field-group:focus-within .field-label { color: #BC9151; }
.input-wrapper { position: relative; }
.field-icon { position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #BC9151; font-size: 1rem; transition: all 0.3s ease; pointer-events: none; z-index: 1; }
.field-group:focus-within .field-icon { color: #9A7640; }
.field-input { width: 100%; padding: 0.75rem 1rem; background: #FAFAF8; border: 1.5px solid #E8E3DB; border-radius: 10px; color: #1A2634; font-weight: 500; font-size: 0.9rem; font-family: inherit; transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1); outline: none; }
.field-input.has-icon { padding-left: 2.75rem; }
.field-input::placeholder { color: #C4BAA8; font-weight: 400; }
.field-input:hover { border-color: #D4CCBF; }
.field-input:focus { background: #fff; border-color: #BC9151; box-shadow: 0 0 0 3px rgba(188, 145, 81, 0.1); }

.btn-primary-action { display: flex; align-items: center; justify-content: center; gap: 0.5rem; padding: 0.8rem 1.5rem; background: linear-gradient(135deg, #BC9151 0%, #A57F47 100%); color: white; border: none; border-radius: 12px; font-size: 0.78rem; font-weight: 700; font-family: inherit; text-transform: uppercase; letter-spacing: 0.12em; cursor: pointer; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
.btn-primary-action:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 8px 24px -4px rgba(188, 145, 81, 0.4); }
.btn-primary-action:disabled { opacity: 0.65; cursor: not-allowed; }
.btn-primary-action.is-loading { pointer-events: none; }

.footer-text { color: #8896A6; font-size: 0.82rem; }
.action-link { color: #BC9151; font-weight: 700; text-decoration: none; transition: all 0.2s ease; }
.action-link:hover { color: #9A7640; text-decoration: underline; }

.animate-fade-in { animation: fadeIn 0.7s cubic-bezier(0.2, 0.8, 0.2, 1); }
@keyframes fadeIn { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: translateY(0); } }

@media (max-width: 991.98px) { .form-inner { padding: 2rem 1.5rem; } }
@media (max-width: 575.98px) { .form-inner { padding: 1.5rem 1.25rem; } }
</style>
