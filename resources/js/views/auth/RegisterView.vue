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
          <div class="col-lg-4 d-none d-lg-flex branding-panel flex-column justify-content-between">
            <div class="branding-inner">
              <div class="brand mb-auto">
                <img src="/images/EMES logo.png" alt="EME's Apartelle" class="auth-logo mb-3">
                <h4 class="serif-font fw-bold mb-0 text-white fs-4">EME's</h4>
                <p class="brand-sub text-uppercase">Apartelle</p>
              </div>
              
              <div class="branding-content mt-auto">
                <h2 class="serif-font fs-2 fw-bold mb-4 text-white">Join Us</h2>
                <div class="step-indicators">
                  <div v-for="n in 3" :key="n" class="step-item" :class="{ 'active': currentStep >= n-1, 'current': currentStep === n-1 }">
                    <div class="step-dot"></div>
                    <span class="step-label">{{ n === 1 ? 'Personal' : n === 2 ? 'Contact' : 'Security' }}</span>
                  </div>
                </div>
              </div>
            </div>

            <div class="branding-footer">&copy; 2026 EME's Apartelle.</div>
          </div>

          <!-- Right Form Section -->
          <div class="col-lg-8 form-section">
            <div class="form-inner">
              <div class="form-header mb-4">
                <div class="d-flex justify-content-between align-items-center mb-1">
                  <h2 class="serif-font fw-bold mb-0 fs-3 form-title">Create Account</h2>
                  <span class="step-badge">Step {{ currentStep + 1 }}/3</span>
                </div>
                <p class="form-subtitle">Please fill in your details to get started.</p>
              </div>

            <form @submit.prevent="handleRegister">
              <!-- Transition Group for Steps -->
              <transition name="slide-fade" mode="out-in">
                <!-- Step 0: Personal Details -->
                <div v-if="currentStep === 0" key="step0">
                  <div class="row g-3">
                    <div class="col-md-6 field-group">
                      <label class="field-label">First Name</label>
                      <input v-model="form.first_name" type="text" class="field-input" placeholder="John" required>
                    </div>
                    <div class="col-md-6 field-group">
                      <label class="field-label">Last Name</label>
                      <input v-model="form.last_name" type="text" class="field-input" placeholder="Doe" required>
                    </div>
                    <div class="col-md-6 field-group">
                      <label class="field-label">Birthdate</label>
                      <input v-model="form.birthdate" type="date" class="field-input" required>
                    </div>
                    <div class="col-md-6 field-group">
                      <label class="field-label">Gender</label>
                      <select v-model="form.gender" class="field-input field-select" required>
                        <option value="" disabled selected>Select Gender</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="other">Other</option>
                      </select>
                    </div>
                  </div>
                </div>

                <!-- Step 1: Contact Details -->
                <div v-else-if="currentStep === 1" key="step1">
                  <div class="row g-3">
                    <div class="col-md-6 field-group">
                      <label class="field-label">Email Address</label>
                      <input v-model="form.email" type="email" class="field-input" placeholder="john@example.com" required>
                    </div>
                    <div class="col-md-6 field-group">
                      <label class="field-label">Phone Number</label>
                      <input v-model="form.phone" type="tel" class="field-input" placeholder="+63 9xx xxx xxxx" required>
                    </div>
                    <div class="col-12 field-group">
                      <label class="field-label">Full Address</label>
                      <input v-model="form.address" type="text" class="field-input" placeholder="House #, Street, Barangay" required>
                    </div>
                    <div class="col-md-6 field-group">
                      <label class="field-label">City</label>
                      <input v-model="form.city" type="text" class="field-input" placeholder="San Pablo City" required>
                    </div>
                    <div class="col-md-6 field-group">
                      <label class="field-label">Zip Code</label>
                      <input v-model="form.zip_code" type="text" class="field-input" placeholder="4000" required>
                    </div>
                  </div>
                </div>

                <!-- Step 2: Security & Submit -->
                <div v-else-if="currentStep === 2" key="step2">
                  <div class="row g-3">
                    <div class="col-12 field-group">
                      <label class="field-label">Create Password</label>
                      <input v-model="form.password" type="password" class="field-input" placeholder="••••••••" required>
                      <div class="password-strength mt-2">
                        <div class="strength-bar" :class="passwordStrengthClass"></div>
                        <span class="strength-label">{{ passwordStrengthLabel }}</span>
                      </div>
                    </div>
                    <div class="col-12 field-group">
                      <label class="field-label">Confirm Password</label>
                      <input v-model="form.password_confirmation" type="password" class="field-input" placeholder="••••••••" required>
                    </div>
                    <div class="col-12">
                      <label class="check-group" for="terms">
                        <input class="check-input" type="checkbox" id="terms" v-model="terms" required>
                        <span class="check-mark"></span>
                        <span class="check-label">I agree to the <a href="#" class="action-link">Terms of Service</a>.</span>
                      </label>
                    </div>
                  </div>
                </div>
              </transition>

              <!-- Footer Navigation -->
              <div class="auth-navigation mt-4 d-flex gap-2">
                <button 
                  v-if="currentStep > 0" 
                  type="button" 
                  @click="currentStep--" 
                  class="btn-secondary-action flex-grow-1"
                >
                  <i class="bi bi-arrow-left me-2"></i> Back
                </button>
                
                <button 
                  v-if="currentStep < 2" 
                  type="button" 
                  @click="validateAndNext" 
                  class="btn-primary-action flex-grow-1"
                >
                  Next Step <i class="bi bi-arrow-right ms-2"></i>
                </button>
                
                <button 
                  v-else 
                  type="submit" 
                  class="btn-primary-action flex-grow-1" 
                  :disabled="loading"
                  :class="{ 'is-loading': loading }"
                >
                  <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                  {{ loading ? 'Finalizing...' : 'Create Account' }}
                </button>
              </div>

              <!-- Social Register -->
              <div class="divider my-3">
                <span class="divider-text">Or join with</span>
              </div>

              <div class="row g-2">
                <div class="col-12">
                  <button @click="loginWithGoogle" type="button" class="btn-social w-100">
                    <img src="/images/google-icon.ico" width="16" alt="Google"> <span>Continue with Google</span>
                  </button>
                </div>
              </div>

              <p class="text-center mt-3 footer-text">
                Already have an account? 
                <router-link to="/login" class="action-link">Sign In Here</router-link>
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
import { ref, reactive, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useAuth } from '../../store/auth';
import Swal from 'sweetalert2';
import axios from 'axios';

const router = useRouter();
const { register } = useAuth();

const currentStep = ref(0);
const loading = ref(false);
const terms = ref(false);

const loginWithGoogle = async () => {
  try {
    const response = await axios.get('/api/auth/google');
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

const form = reactive({
  first_name: '',
  last_name: '',
  email: '',
  phone: '',
  address: '',
  city: '',
  zip_code: '',
  birthdate: '',
  gender: '',
  password: '',
  password_confirmation: ''
});

// Computed password strength
const passwordStrength = computed(() => {
  const p = form.password;
  if (!p) return 0;
  let s = 0;
  if (p.length > 7) s++;
  if (/[A-Z]/.test(p)) s++;
  if (/[0-9]/.test(p)) s++;
  if (/[^A-Za-z0-9]/.test(p)) s++;
  return s;
});

const passwordStrengthClass = computed(() => {
  const s = passwordStrength.value;
  if (s === 0) return '';
  if (s <= 1) return 'weak';
  if (s <= 3) return 'medium';
  return 'strong';
});

const passwordStrengthLabel = computed(() => {
  const s = passwordStrength.value;
  if (s === 0) return 'Enter a password';
  if (s <= 1) return 'Weak password';
  if (s <= 3) return 'Almost there...';
  return 'Excellent password';
});

const validateAndNext = () => {
  // Simple field validation before moving to next step
  const stepFields = {
    0: ['first_name', 'last_name', 'birthdate', 'gender'],
    1: ['email', 'phone', 'address', 'city', 'zip_code']
  };

  const fields = stepFields[currentStep.value];
  const invalid = fields.find(f => !form[f]);
  
  if (invalid) {
    Swal.fire({
      icon: 'warning',
      title: 'Missing Information',
      text: 'Please fill in all fields before moving to the next step.',
      toast: true,
      position: 'top-end',
      showConfirmButton: false,
      timer: 3000
    });
    return;
  }
  
  currentStep.value++;
};

const handleRegister = async () => {
  if (form.password !== form.password_confirmation) {
    Swal.fire({ icon: 'error', title: 'Error', text: 'Passwords do not match.' });
    return;
  }

  loading.value = true;
  try {
    await register(form);
    Swal.fire({
      icon: 'success',
      title: 'Welcome!',
      text: 'Your account has been created successfully.',
      showConfirmButton: false,
      timer: 2000
    });
    router.push('/rooms');
  } catch (error) {
    const message = error.response?.data?.message || 'Registration failed.';
    Swal.fire({ icon: 'error', title: 'Registration Failed', text: message });
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
    radial-gradient(circle at 80% 20%, rgba(188, 145, 81, 0.12), transparent 50%),
    url('/images/unsplash/hotel-lobby.jpg') center/cover;
  filter: brightness(0.45) saturate(1.1);
  z-index: 1;
}

.auth-card-container {
  position: relative;
  z-index: 2;
  width: 100%;
  max-width: 920px;
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
  top: 0; left: 0;
  margin: 1.25rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  text-decoration: none;
  color: rgba(255, 255, 255, 0.85);
  transition: all 0.3s ease;
}
.back-home-link:hover { color: #fff; transform: translateX(-2px); }

.back-icon {
  width: 36px; height: 36px;
  display: flex; align-items: center; justify-content: center;
  background: rgba(255, 255, 255, 0.12);
  backdrop-filter: blur(8px);
  border: 1px solid rgba(255, 255, 255, 0.15);
  border-radius: 50%;
  font-size: 1rem;
  transition: all 0.3s ease;
}
.back-home-link:hover .back-icon { background: rgba(255, 255, 255, 0.2); }

.back-label {
  font-size: 0.72rem; font-weight: 700;
  text-transform: uppercase; letter-spacing: 0.08em;
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
  position: absolute; inset: 0;
  background: 
    radial-gradient(circle at 30% 20%, rgba(255,255,255,0.08), transparent 50%),
    radial-gradient(circle at 70% 80%, rgba(0,0,0,0.12), transparent 50%);
  pointer-events: none;
}

.branding-inner {
  position: relative; z-index: 1;
  padding: 2rem 1.75rem;
  display: flex; flex-direction: column; height: 100%;
}

.auth-logo {
  width: 48px; height: 48px;
  object-fit: contain; border-radius: 12px;
  background: white; padding: 4px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.brand-sub {
  font-size: 0.6rem; letter-spacing: 0.25em;
  color: rgba(255,255,255,0.65); margin-bottom: 0; font-weight: 600;
}

.branding-footer {
  position: relative; z-index: 1;
  padding: 0 1.75rem 1.5rem;
  font-size: 0.68rem; color: rgba(255,255,255,0.45); letter-spacing: 0.03em;
}

/* Step Indicators */
.step-indicators {
  display: flex; flex-direction: column;
  gap: 1rem; margin-top: 0.5rem;
  padding-left: 0.5rem;
  border-left: 1px solid rgba(255,255,255,0.2);
}

.step-item {
  display: flex; align-items: center;
  gap: 0.75rem; opacity: 0.4;
  transition: all 0.4s ease;
}
.step-item.active { opacity: 0.7; }
.step-item.current { opacity: 1; transform: translateX(8px); }

.step-dot {
  width: 8px; height: 8px; border-radius: 50%;
  background: rgba(255,255,255,0.7);
  transition: all 0.4s ease;
  flex-shrink: 0;
}
.step-item.current .step-dot {
  width: 10px; height: 10px;
  background: white;
  box-shadow: 0 0 8px rgba(255,255,255,0.6);
}

.step-label {
  font-size: 0.62rem; font-weight: 700;
  text-transform: uppercase; letter-spacing: 0.15em;
  color: rgba(255,255,255,0.85);
}

/* ==========================================
   FORM SECTION (RIGHT)
   ========================================== */
.form-section { background: #fff; }

.form-inner { padding: 2rem 2rem; }

.form-title { color: #1A2634; }

.form-subtitle {
  color: #8896A6; font-size: 0.85rem; margin-bottom: 0;
}

.step-badge {
  font-size: 0.68rem; font-weight: 700;
  text-transform: uppercase; letter-spacing: 0.08em;
  color: #BC9151; background: rgba(188,145,81,0.1);
  padding: 0.3rem 0.75rem; border-radius: 20px;
}

/* ==========================================
   FIELD STYLING
   ========================================== */
.field-label {
  display: block;
  font-size: 0.7rem; font-weight: 700;
  text-transform: uppercase; letter-spacing: 0.06em;
  color: #1A2634; margin-bottom: 0.5rem;
  transition: color 0.3s ease;
}
.field-group:focus-within .field-label { color: #BC9151; }

.field-input {
  width: 100%; padding: 0.7rem 1rem;
  background: #FAFAF8;
  border: 1.5px solid #E8E3DB;
  border-radius: 10px;
  color: #1A2634; font-weight: 500;
  font-size: 0.9rem; font-family: inherit;
  transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
  outline: none;
}
.field-input::placeholder { color: #C4BAA8; font-weight: 400; }
.field-input:hover { border-color: #D4CCBF; }
.field-input:focus {
  background: #fff; border-color: #BC9151;
  box-shadow: 0 0 0 3px rgba(188, 145, 81, 0.1);
}

.field-select {
  appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23BC9151' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 1rem center;
  padding-right: 2.5rem;
}

/* ==========================================
   PASSWORD STRENGTH
   ========================================== */
.strength-bar {
  height: 4px; border-radius: 2px;
  background: #EBE6DE;
  width: 100%; position: relative; overflow: hidden;
}
.strength-bar::after {
  content: ''; position: absolute;
  left: 0; top: 0; height: 100%; width: 0;
  transition: all 0.5s ease; border-radius: 2px;
}
.strength-bar.weak::after { width: 33%; background: #ef4444; }
.strength-bar.medium::after { width: 66%; background: #f59e0b; }
.strength-bar.strong::after { width: 100%; background: #10b981; }

.strength-label {
  font-size: 0.68rem; font-weight: 600;
  color: #8896A6; margin-top: 0.35rem; display: block;
}

/* ==========================================
   CUSTOM CHECKBOX
   ========================================== */
.check-group {
  display: flex; align-items: center;
  gap: 0.5rem; cursor: pointer; user-select: none;
}
.check-input { position: absolute; opacity: 0; width: 0; height: 0; }
.check-mark {
  width: 18px; height: 18px;
  border: 1.5px solid #D4CCBF; border-radius: 4px;
  display: flex; align-items: center; justify-content: center;
  transition: all 0.2s ease; flex-shrink: 0; background: #FAFAF8;
}
.check-input:checked + .check-mark { background: #BC9151; border-color: #BC9151; }
.check-input:checked + .check-mark::after {
  content: ''; width: 5px; height: 9px;
  border: solid white; border-width: 0 2px 2px 0;
  transform: rotate(45deg) translateY(-1px);
}
.check-input:focus + .check-mark {
  box-shadow: 0 0 0 3px rgba(188, 145, 81, 0.12);
  border-color: #BC9151;
}
.check-group:hover .check-mark { border-color: #BC9151; }
.check-label { font-size: 0.8rem; color: #5A6673; font-weight: 500; }

/* ==========================================
   BUTTONS
   ========================================== */
.btn-primary-action {
  display: flex; align-items: center; justify-content: center; gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  background: linear-gradient(135deg, #BC9151 0%, #A57F47 100%);
  color: white; border: none; border-radius: 12px;
  font-size: 0.78rem; font-weight: 700; font-family: inherit;
  text-transform: uppercase; letter-spacing: 0.1em;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.btn-primary-action:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px -4px rgba(188, 145, 81, 0.4);
}
.btn-primary-action:active:not(:disabled) { transform: translateY(0); }
.btn-primary-action:disabled { opacity: 0.65; cursor: not-allowed; }
.btn-primary-action.is-loading { pointer-events: none; }

.btn-secondary-action {
  display: flex; align-items: center; justify-content: center; gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  background: #fff; border: 1.5px solid #EBE6DE;
  border-radius: 12px; color: #1A2634;
  font-size: 0.78rem; font-weight: 700; font-family: inherit;
  cursor: pointer; transition: all 0.25s ease;
}
.btn-secondary-action:hover {
  border-color: #D4CCBF; background: #FDFCFA;
  transform: translateY(-1px);
}

/* ==========================================
   DIVIDER
   ========================================== */
.divider {
  display: flex; align-items: center; gap: 1rem;
}
.divider::before, .divider::after {
  content: ''; flex: 1; height: 1px; background: #EBE6DE;
}
.divider-text {
  font-size: 0.75rem; color: #A09585;
  font-family: var(--font-serif); font-style: italic;
  font-weight: 400; white-space: nowrap;
}

/* ==========================================
   SOCIAL BUTTONS
   ========================================== */
.btn-social {
  display: flex; align-items: center; justify-content: center; gap: 0.5rem;
  padding: 0.7rem 1rem; background: #fff;
  border: 1.5px solid #EBE6DE; border-radius: 10px;
  font-size: 0.82rem; font-weight: 600; font-family: inherit;
  color: #1A2634; cursor: pointer; transition: all 0.25s ease;
}
.btn-social:hover {
  border-color: #D4CCBF; background: #FDFCFA;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px -2px rgba(0,0,0,0.06);
}

/* ==========================================
   FOOTER TEXT & LINKS
   ========================================== */
.footer-text { color: #8896A6; font-size: 0.82rem; }
.action-link {
  color: #BC9151; font-weight: 700; text-decoration: none;
  transition: all 0.2s ease;
}
.action-link:hover { color: #9A7640; text-decoration: underline; }

/* ==========================================
   ANIMATIONS
   ========================================== */
.animate-fade-in { animation: fadeIn 0.7s cubic-bezier(0.2, 0.8, 0.2, 1); }
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(16px); }
  to { opacity: 1; transform: translateY(0); }
}

.slide-fade-enter-active { transition: all 0.4s ease-out; }
.slide-fade-leave-active { transition: all 0.3s cubic-bezier(1, 0.5, 0.8, 1); }
.slide-fade-enter-from { transform: translateX(20px); opacity: 0; }
.slide-fade-leave-to { transform: translateX(-20px); opacity: 0; }

/* ==========================================
   RESPONSIVE
   ========================================== */
@media (max-width: 991.98px) {
  .form-inner { padding: 2rem 1.5rem; }
  .auth-card-container { padding: 1rem; }
}
@media (max-width: 575.98px) {
  .form-inner { padding: 1.5rem 1.25rem; }
}
</style>
