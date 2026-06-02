<template>
  <div class="auth-page d-flex align-items-center justify-content-center">
    <!-- Immersive Background Overlay -->
    <div class="auth-bg-overlay"></div>

    <!-- Back to Home Button -->
    <router-link to="/" class="back-home-link z-3">
      <i class="bi bi-arrow-left back-icon"></i>
      <span class="back-label d-none d-md-inline">Back to Home</span>
    </router-link>
    
    <div class="auth-card-container animate-fade-in">
      <div class="auth-card overflow-hidden">
        <div class="row g-0">
          <!-- Left Branding Section -->
          <div class="col-lg-5 d-none d-lg-flex branding-panel flex-column justify-content-between">
            <div class="branding-inner">
              <div class="brand mb-auto">
                <img src="/images/emes-logo.png" alt="EME's Apartelle" class="auth-logo mb-3">
                <h4 class="serif-font fw-bold mb-0 text-white fs-4">EME's</h4>
                <p class="brand-sub text-uppercase">Apartelle</p>
              </div>
              
              <div class="branding-content mt-auto">
                <h2 class="serif-font fs-2 fw-bold mb-3 text-white">Welcome Back</h2>
                <p class="branding-desc">Sign in to your account to manage bookings, track payments, and access exclusive member privileges.</p>
                
                <div class="brand-stats d-flex gap-4 mt-4">
                  <div class="stat-item">
                    <h4 class="mb-0 fw-bold fs-4 serif-font text-white">24/7</h4>
                    <span class="stat-label">Front Desk</span>
                  </div>
                  <div class="stat-item">
                    <h4 class="mb-0 fw-bold fs-4 serif-font text-white">4.9</h4>
                    <span class="stat-label">Guest Rating</span>
                  </div>
                </div>
              </div>
            </div>

            <div class="branding-footer">&copy; 2026 EME's Apartelle.</div>
          </div>

          <!-- Right Form Section -->
          <div class="col-lg-7 form-section">
            <div class="form-inner">
              <div class="form-header mb-4 animate-fade-in" v-if="!mfaRequired">
                <h2 class="serif-font fw-bold mb-1 fs-3 form-title">Account Sign In</h2>
                <p class="form-subtitle">Enter your credentials below to access your dashboard.</p>
              </div>

              <!-- Standard Login Form -->
              <form v-if="!mfaRequired" @submit.prevent="handleLogin" class="animate-fade-in">
                <div class="mb-3 field-group">
                  <label class="field-label" for="login-email">Email Address</label>
                  <div class="input-wrapper">
                    <i class="bi bi-envelope field-icon"></i>
                    <input 
                      v-model="form.email" 
                      type="email" 
                      id="login-email"
                      class="field-input has-icon" 
                      placeholder="name@example.com" 
                      required
                      autocomplete="email"
                    >
                  </div>
                </div>

                <div class="mb-3 field-group">
                  <div class="d-flex justify-content-between align-items-center">
                    <label class="field-label" for="login-password">Password</label>
                    <router-link to="/forgot-password" class="forgot-link">Forgot Password?</router-link>
                  </div>
                  <div class="input-wrapper">
                    <i class="bi bi-shield-lock field-icon"></i>
                    <input 
                      :type="showPassword ? 'text' : 'password'" 
                      v-model="form.password" 
                      id="login-password"
                      class="field-input has-icon has-suffix" 
                      placeholder="••••••••" 
                      required
                      autocomplete="current-password"
                    >
                    <button type="button" @click="showPassword = !showPassword" class="field-suffix-btn" aria-label="Toggle password visibility">
                      <i class="bi" :class="showPassword ? 'bi-eye-slash' : 'bi-eye'"></i>
                    </button>
                  </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4">
                  <label class="check-group" for="remember">
                    <input class="check-input" type="checkbox" id="remember" v-model="form.remember">
                    <span class="check-mark"></span>
                    <span class="check-label">Remember me</span>
                  </label>
                </div>

                <button 
                  type="submit" 
                  class="btn-primary-action w-100 mb-4"
                  :disabled="loading"
                  :class="{ 'is-loading': loading }"
                >
                  <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                  <span>{{ loading ? 'Authenticating...' : 'Sign In To Account' }}</span>
                </button>

                <!-- Social Login -->
                <div class="divider mb-4">
                  <span class="divider-text">Or continue with</span>
                </div>

                <div class="row g-2 mb-4">
                  <div class="col-6">
                    <button @click="loginWithGoogle" type="button" class="btn-social w-100">
                      <img src="/images/google-icon.ico" width="16" alt="Google"> 
                      <span>Google</span>
                    </button>
                  </div>
                  <div class="col-6">
                    <button type="button" class="btn-social w-100">
                      <i class="bi bi-apple fs-6"></i> 
                      <span>Apple</span>
                    </button>
                  </div>
                </div>

                <p class="text-center small mb-0 footer-text">
                  New to EME's Apartelle? 
                  <router-link to="/register" class="action-link">Create an Account</router-link>
                </p>
              </form>

              <!-- MFA OTP Verification Form -->
              <form v-else @submit.prevent="handleVerifyOtp" class="animate-fade-in text-center py-3">
                <div class="mb-4">
                  <div class="d-inline-flex align-items-center justify-content-center bg-gold-subtle rounded-circle p-3 mb-3" style="width: 60px; height: 60px; color: var(--primary-gold);">
                    <i class="bi bi-shield-lock-fill fs-3"></i>
                  </div>
                  <h4 class="serif-font fw-bold text-secondary-dark">Two-Factor Authentication</h4>
                  <p class="text-muted small">We've sent a 6-digit verification code to <strong class="text-dark">{{ form.email }}</strong>. Please enter the code below to sign in.</p>
                </div>

                <!-- OTP Countdown Timer -->
                <div class="mb-3">
                  <div v-if="countdownSeconds > 0" class="d-inline-flex align-items-center justify-content-center px-3 py-2 rounded-pill" style="background: rgba(188, 145, 81, 0.1); color: var(--primary-gold); border: 1px solid rgba(188, 145, 81, 0.2); font-size: 0.85rem; font-weight: 600;">
                    <i class="bi bi-clock me-2 text-gold"></i> Code expires in: <span class="font-monospace ms-1" style="font-weight: 700;">{{ countdownSeconds }}s</span>
                  </div>
                  <div v-else class="d-inline-flex align-items-center justify-content-center px-3 py-2 rounded-pill" style="background: rgba(220, 53, 69, 0.1); color: #dc3545; border: 1px solid rgba(220, 53, 69, 0.2); font-size: 0.85rem; font-weight: 600;">
                    <i class="bi bi-exclamation-circle me-2"></i> Code has expired
                  </div>
                </div>

                <div class="mb-4 field-group text-center">
                  <label class="field-label d-block text-center mb-2" for="otp-code">Enter Verification Code</label>
                  <div class="input-wrapper d-flex justify-content-center">
                    <input 
                      v-model="otpForm.code" 
                      type="text" 
                      id="otp-code"
                      class="field-input text-center font-monospace fw-bold tracking-widest fs-4" 
                      placeholder="000000" 
                      maxlength="6"
                      required
                      autocomplete="one-time-code"
                      :disabled="countdownSeconds === 0"
                      style="letter-spacing: 0.35em; max-width: 240px; font-size: 1.5rem !important;"
                    >
                  </div>
                </div>

                <button 
                  type="submit" 
                  class="btn-primary-action w-100 mb-3"
                  :disabled="loading || countdownSeconds === 0"
                  :class="{ 'is-loading': loading }"
                >
                  <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                  <span>{{ loading ? 'Verifying...' : 'Verify & Sign In' }}</span>
                </button>

                <button 
                  type="button" 
                  class="btn btn-outline-light border-light-subtle text-muted w-100 py-2.5 small fw-bold text-uppercase d-flex align-items-center justify-content-center"
                  @click="cancelMfa"
                  style="border-radius: 12px; font-size: 0.72rem; letter-spacing: 0.08em; height: 42px;"
                >
                  Back to Sign In
                </button>

                <p class="text-center small mt-3 mb-0" v-if="countdownSeconds === 0">
                  Didn't receive the code or it expired? 
                  <a href="#" @click.prevent="handleResendOtp" class="action-link" style="color: var(--primary-gold); font-weight: 700; text-decoration: none;">Resend Code</a>
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
import { ref, reactive, onMounted, onUnmounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuth } from '../../store/auth';
import Swal from 'sweetalert2';
import axios from 'axios';

const router = useRouter();
const route = useRoute();
const { login, verifyOtp, setToken, fetchUser } = useAuth();

const form = reactive({
  email: '',
  password: '',
  remember: false
});

const otpForm = reactive({
  code: ''
});

const mfaRequired = ref(false);
const showPassword = ref(false);
const loading = ref(false);

const countdownSeconds = ref(60);
let countdownInterval = null;

const startCountdown = () => {
  stopCountdown();
  countdownSeconds.value = 60;
  countdownInterval = setInterval(() => {
    if (countdownSeconds.value > 0) {
      countdownSeconds.value--;
    } else {
      stopCountdown();
    }
  }, 1000);
};

const stopCountdown = () => {
  if (countdownInterval) {
    clearInterval(countdownInterval);
    countdownInterval = null;
  }
};

const cancelMfa = () => {
  mfaRequired.value = false;
  stopCountdown();
};

onUnmounted(() => {
  stopCountdown();
});

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
      
      const redirectPath = route.query.redirect || '/rooms';
      router.push(redirectPath);
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
    
    // Check if OTP was sent successfully and requires verification
    if (response.mfa_required) {
      mfaRequired.value = true;
      otpForm.code = ''; // Reset code
      startCountdown(); // Start countdown timer
      Swal.fire({
        icon: 'info',
        title: 'OTP Verification Required',
        text: 'We have emailed a 6-digit authentication code to you.',
        timer: 3500,
        toast: true,
        position: 'top-end',
        showConfirmButton: false
      });
      return;
    }
    
    // Check if the user is administrative or staff trying to use the guest portal
    if (response.user && ['admin', 'staff'].includes(response.user.role)) {
      const { logout } = useAuth();
      await logout();
      throw new Error('Staff and Administrators must use the dedicated Admin Portal to sign in.');
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

    const redirectPath = route.query.redirect || '/rooms';
    router.push(redirectPath);
  } catch (error) {
    Swal.fire({
      icon: 'error',
      title: 'Access Denied',
      text: error.message || error.response?.data?.message || 'Invalid credentials. Please try again.',
      confirmButtonColor: '#BC9151'
    });
  } finally {
    loading.value = false;
  }
};

const handleVerifyOtp = async () => {
  loading.value = true;
  try {
    const response = await verifyOtp({
      email: form.email,
      otp: otpForm.code
    });

    // Check if the user is administrative or staff trying to use the guest portal
    if (response.user && ['admin', 'staff'].includes(response.user.role)) {
      const { logout } = useAuth();
      await logout();
      throw new Error('Staff and Administrators must use the dedicated Admin Portal to sign in.');
    }

    stopCountdown(); // Stop countdown timer on success

    Swal.fire({
      icon: 'success',
      title: 'Sign In Successful',
      text: 'Welcome to EME\'s Apartelle!',
      showConfirmButton: false,
      timer: 2000,
      toast: true,
      position: 'top-end'
    });

    const redirectPath = route.query.redirect || '/rooms';
    router.push(redirectPath);
  } catch (error) {
    Swal.fire({
      icon: 'error',
      title: 'Verification Failed',
      text: error.response?.data?.message || 'Invalid or expired verification code. Please try again.',
      confirmButtonColor: '#BC9151'
    });
  } finally {
    loading.value = false;
  }
};

const handleResendOtp = async () => {
  loading.value = true;
  try {
    const response = await login({
      email: form.email,
      password: form.password
    });
    
    if (response.mfa_required) {
      otpForm.code = ''; // Reset code
      startCountdown(); // Restart the countdown timer
      Swal.fire({
        icon: 'success',
        title: 'New Code Sent',
        text: 'A new 6-digit verification code has been sent to your email.',
        timer: 3000,
        toast: true,
        position: 'top-end',
        showConfirmButton: false
      });
    }
  } catch (error) {
    Swal.fire({
      icon: 'error',
      title: 'Resend Failed',
      text: error.response?.data?.message || 'Could not resend verification code. Please try again.',
      confirmButtonColor: '#BC9151'
    });
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
/* ==========================================
   AUTH PAGE — LAYOUT
   ========================================== */
.auth-page {
  min-height: 100vh;
  position: relative;
  overflow: hidden;
  background: #0f172a;
}

.auth-bg-overlay {
  position: absolute;
  inset: 0;
  background: 
    radial-gradient(circle at 20% 80%, rgba(188, 145, 81, 0.12), transparent 50%),
    url('/images/unsplash/suite-room.jpg') center/cover;
  filter: brightness(0.45) saturate(1.1);
  z-index: 1;
}

.auth-card-container {
  position: relative;
  z-index: 2;
  width: 100%;
  max-width: 820px;
  padding: 1.25rem;
}

.auth-card {
  background: #fff;
  border-radius: 20px;
  box-shadow: 
    0 25px 60px -12px rgba(0, 0, 0, 0.35),
    0 0 0 1px rgba(255, 255, 255, 0.08);
}

/* ==========================================
   BACK BUTTON
   ========================================== */
.back-home-link {
  position: absolute;
  top: 0;
  left: 0;
  margin: 1.25rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  text-decoration: none;
  color: rgba(255, 255, 255, 0.85);
  transition: all 0.3s ease;
}

.back-home-link:hover {
  color: #fff;
  transform: translateX(-2px);
}

.back-icon {
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(255, 255, 255, 0.12);
  backdrop-filter: blur(8px);
  border: 1px solid rgba(255, 255, 255, 0.15);
  border-radius: 50%;
  font-size: 1rem;
  transition: all 0.3s ease;
}

.back-home-link:hover .back-icon {
  background: rgba(255, 255, 255, 0.2);
}

.back-label {
  font-size: 0.72rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  text-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
}

/* ==========================================
   BRANDING PANEL (LEFT)
   ========================================== */
.branding-panel {
  background: linear-gradient(160deg, #BC9151 0%, #8B6A3D 100%);
  position: relative;
  overflow: hidden;
}

.branding-panel::before {
  content: '';
  position: absolute;
  inset: 0;
  background: 
    radial-gradient(circle at 30% 20%, rgba(255, 255, 255, 0.08), transparent 50%),
    radial-gradient(circle at 70% 80%, rgba(0, 0, 0, 0.12), transparent 50%);
  pointer-events: none;
}

.branding-inner {
  position: relative;
  z-index: 1;
  padding: 2rem 1.75rem;
  display: flex;
  flex-direction: column;
  height: 100%;
}

.auth-logo {
  width: 48px;
  height: 48px;
  object-fit: contain;
  border-radius: 12px;
  background: white;
  padding: 4px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.brand-sub {
  font-size: 0.6rem;
  letter-spacing: 0.25em;
  color: rgba(255, 255, 255, 0.65);
  margin-bottom: 0;
  font-weight: 600;
}

.branding-desc {
  color: rgba(255, 255, 255, 0.8);
  font-size: 0.85rem;
  line-height: 1.65;
  margin-bottom: 0;
}

.stat-label {
  font-size: 0.58rem;
  text-transform: uppercase;
  letter-spacing: 0.18em;
  color: rgba(255, 255, 255, 0.6);
  font-weight: 700;
}

.branding-footer {
  position: relative;
  z-index: 1;
  padding: 0 1.75rem 1.5rem;
  font-size: 0.68rem;
  color: rgba(255, 255, 255, 0.45);
  letter-spacing: 0.03em;
}

/* ==========================================
   FORM SECTION (RIGHT)
   ========================================== */
.form-section {
  background: #fff;
}

.form-inner {
  padding: 2.25rem 2rem;
}

.form-title {
  color: #1A2634;
}

.form-subtitle {
  color: #8896A6;
  font-size: 0.85rem;
  margin-bottom: 0;
}

/* ==========================================
   FIELD STYLING
   ========================================== */
.field-group {
  margin-bottom: 0;
}

.field-label {
  display: block;
  font-size: 0.7rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: #1A2634;
  margin-bottom: 0.5rem;
  transition: color 0.3s ease;
}

.field-group:focus-within .field-label {
  color: #BC9151;
}

.input-wrapper {
  position: relative;
}

.field-icon {
  position: absolute;
  left: 1rem;
  top: 50%;
  transform: translateY(-50%);
  color: #BC9151;
  font-size: 1rem;
  transition: all 0.3s ease;
  pointer-events: none;
  z-index: 1;
}

.field-group:focus-within .field-icon {
  color: #9A7640;
}

.field-input {
  width: 100%;
  padding: 0.75rem 1rem;
  background: #FAFAF8;
  border: 1.5px solid #E8E3DB;
  border-radius: 10px;
  color: #1A2634;
  font-weight: 500;
  font-size: 0.9rem;
  font-family: inherit;
  transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
  outline: none;
}

.field-input.has-icon {
  padding-left: 2.75rem;
}

.field-input.has-suffix {
  padding-right: 2.75rem;
}

.field-input::placeholder {
  color: #C4BAA8;
  font-weight: 400;
}

.field-input:hover {
  border-color: #D4CCBF;
}

.field-input:focus {
  background: #fff;
  border-color: #BC9151;
  box-shadow: 0 0 0 3px rgba(188, 145, 81, 0.1);
}

.field-suffix-btn {
  position: absolute;
  right: 0.75rem;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  color: #BC9151;
  padding: 0.25rem;
  cursor: pointer;
  transition: color 0.2s ease;
  font-size: 1rem;
  display: flex;
  align-items: center;
  justify-content: center;
}

.field-suffix-btn:hover {
  color: #9A7640;
}

/* ==========================================
   FORGOT LINK
   ========================================== */
.forgot-link {
  font-size: 0.72rem;
  font-weight: 600;
  color: #BC9151;
  text-decoration: none;
  transition: color 0.2s ease;
}

.forgot-link:hover {
  color: #9A7640;
  text-decoration: underline;
}

/* ==========================================
   CUSTOM CHECKBOX
   ========================================== */
.check-group {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
  user-select: none;
}

.check-input {
  position: absolute;
  opacity: 0;
  width: 0;
  height: 0;
}

.check-mark {
  width: 18px;
  height: 18px;
  border: 1.5px solid #D4CCBF;
  border-radius: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
  flex-shrink: 0;
  background: #FAFAF8;
}

.check-input:checked + .check-mark {
  background: #BC9151;
  border-color: #BC9151;
}

.check-input:checked + .check-mark::after {
  content: '';
  width: 5px;
  height: 9px;
  border: solid white;
  border-width: 0 2px 2px 0;
  transform: rotate(45deg) translateY(-1px);
}

.check-input:focus + .check-mark {
  box-shadow: 0 0 0 3px rgba(188, 145, 81, 0.12);
  border-color: #BC9151;
}

.check-group:hover .check-mark {
  border-color: #BC9151;
}

.check-label {
  font-size: 0.8rem;
  color: #5A6673;
  font-weight: 500;
}

/* ==========================================
   PRIMARY ACTION BUTTON
   ========================================== */
.btn-primary-action {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.8rem 1.5rem;
  background: linear-gradient(135deg, #BC9151 0%, #A57F47 100%);
  color: white;
  border: none;
  border-radius: 12px;
  font-size: 0.78rem;
  font-weight: 700;
  font-family: inherit;
  text-transform: uppercase;
  letter-spacing: 0.12em;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  overflow: hidden;
}

.btn-primary-action::before {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, transparent 100%);
  opacity: 0;
  transition: opacity 0.3s ease;
}

.btn-primary-action:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px -4px rgba(188, 145, 81, 0.4);
}

.btn-primary-action:hover::before {
  opacity: 1;
}

.btn-primary-action:active:not(:disabled) {
  transform: translateY(0);
  box-shadow: 0 4px 12px -2px rgba(188, 145, 81, 0.3);
}

.btn-primary-action:disabled {
  opacity: 0.65;
  cursor: not-allowed;
}

.btn-primary-action.is-loading {
  pointer-events: none;
}

/* ==========================================
   DIVIDER
   ========================================== */
.divider {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.divider::before,
.divider::after {
  content: '';
  flex: 1;
  height: 1px;
  background: #EBE6DE;
}

.divider-text {
  font-size: 0.75rem;
  color: #A09585;
  font-family: var(--font-serif);
  font-style: italic;
  font-weight: 400;
  white-space: nowrap;
}

/* ==========================================
   SOCIAL BUTTONS
   ========================================== */
.btn-social {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.7rem 1rem;
  background: #fff;
  border: 1.5px solid #EBE6DE;
  border-radius: 10px;
  font-size: 0.82rem;
  font-weight: 600;
  font-family: inherit;
  color: #1A2634;
  cursor: pointer;
  transition: all 0.25s ease;
}

.btn-social:hover {
  border-color: #D4CCBF;
  background: #FDFCFA;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px -2px rgba(0, 0, 0, 0.06);
}

.btn-social:active {
  transform: translateY(0);
}

/* ==========================================
   FOOTER TEXT & LINKS
   ========================================== */
.footer-text {
  color: #8896A6;
  font-size: 0.82rem;
}

.action-link {
  color: #BC9151;
  font-weight: 700;
  text-decoration: none;
  transition: all 0.2s ease;
}

.action-link:hover {
  color: #9A7640;
  text-decoration: underline;
}

/* ==========================================
   ANIMATIONS
   ========================================== */
.animate-fade-in {
  animation: fadeIn 0.7s cubic-bezier(0.2, 0.8, 0.2, 1);
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(16px); }
  to { opacity: 1; transform: translateY(0); }
}

/* ==========================================
   RESPONSIVE
   ========================================== */
@media (max-width: 991.98px) {
  .form-inner {
    padding: 2rem 1.5rem;
  }
  
  .auth-card-container {
    padding: 1rem;
  }
}

@media (max-width: 575.98px) {
  .form-inner {
    padding: 1.5rem 1.25rem;
  }
}
</style>
