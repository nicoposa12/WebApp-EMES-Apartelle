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
          <div class="col-lg-4 d-none d-lg-flex bg-gold-gradient p-4 flex-column justify-content-between text-white">
            <div class="brand">
              <img src="/images/EMES logo.png" alt="EME's Apartelle" class="auth-logo mb-3 shadow-sm">
              <h4 class="serif-font fw-bold mb-0 fs-4">EME's</h4>
              <p class="small text-uppercase tracking-widest opacity-75" style="font-size: 0.65rem;">Apartelle</p>
            </div>
            
            <div class="branding-content">
              <h2 class="serif-font fs-2 fw-bold mb-4">Join Us</h2>
              <div class="step-indicators ps-2 border-start border-white border-opacity-25">
                <div v-for="n in 3" :key="n" class="step-item mb-3" :class="{ 'active': currentStep >= n-1, 'current': currentStep === n-1 }">
                  <div class="step-dot transition-all"></div>
                  <span class="step-label small fw-bold text-uppercase tracking-widest" style="letter-spacing: 2px; font-size: 0.65rem;">
                    {{ n === 1 ? 'Personal' : n === 2 ? 'Contact' : 'Security' }}
                  </span>
                </div>
              </div>
            </div>

            <div class="footer-note opacity-60 small letter-spacing-wide" style="font-size: 0.7rem;">
              &copy; 2026 EME's Apartelle.
            </div>
          </div>

          <!-- Right Form Section -->
          <div class="col-lg-8 p-4 bg-white-glass">
            <div class="form-header mb-4 border-bottom pb-3">
              <div class="d-flex justify-content-between align-items-center mb-1">
                <h2 class="serif-font fw-bold text-secondary-dark mb-0 fs-3">Create Account</h2>
                <span class="badge bg-gold-subtle text-gold px-3 py-1 rounded-pill small fw-bold text-uppercase tracking-wider" style="font-size: 0.7rem;">Step {{ currentStep + 1 }}/3</span>
              </div>
              <p class="text-muted small">Please fill in your details to get started.</p>
            </div>

            <form @submit.prevent="handleRegister">
              <!-- Transition Group for Steps -->
              <transition name="slide-fade" mode="out-in">
                <!-- Step 0: Personal Details -->
                <div v-if="currentStep === 0" key="step0">
                  <div class="row g-3">
                    <div class="col-md-6 custom-input-group">
                      <label class="form-label text-secondary-dark opacity-75">First Name</label>
                      <input v-model="form.first_name" type="text" class="form-control-modern py-2" placeholder="John" required>
                    </div>
                    <div class="col-md-6 custom-input-group">
                      <label class="form-label text-secondary-dark opacity-75">Last Name</label>
                      <input v-model="form.last_name" type="text" class="form-control-modern py-2" placeholder="Doe" required>
                    </div>
                    <div class="col-md-6 custom-input-group">
                      <label class="form-label text-secondary-dark opacity-75">Birthdate</label>
                      <input v-model="form.birthdate" type="date" class="form-control-modern py-2" required>
                    </div>
                    <div class="col-md-6 custom-input-group">
                      <label class="form-label text-secondary-dark opacity-75">Gender</label>
                      <select v-model="form.gender" class="form-select-modern py-2" required>
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
                    <div class="col-md-6 custom-input-group">
                      <label class="form-label text-secondary-dark opacity-75">Email Address</label>
                      <input v-model="form.email" type="email" class="form-control-modern py-2" placeholder="john@example.com" required>
                    </div>
                    <div class="col-md-6 custom-input-group">
                      <label class="form-label text-secondary-dark opacity-75">Phone Number</label>
                      <input v-model="form.phone" type="tel" class="form-control-modern py-2" placeholder="+63 9xx xxx xxxx" required>
                    </div>
                    <div class="col-12 custom-input-group">
                      <label class="form-label text-secondary-dark opacity-75">Full Address</label>
                      <input v-model="form.address" type="text" class="form-control-modern py-2" placeholder="House #, Street, Barangay" required>
                    </div>
                    <div class="col-md-6 custom-input-group">
                      <label class="form-label text-secondary-dark opacity-75">City</label>
                      <input v-model="form.city" type="text" class="form-control-modern py-2" placeholder="San Pablo City" required>
                    </div>
                    <div class="col-md-6 custom-input-group">
                      <label class="form-label text-secondary-dark opacity-75">Zip Code</label>
                      <input v-model="form.zip_code" type="text" class="form-control-modern py-2" placeholder="4000" required>
                    </div>
                  </div>
                </div>

                <!-- Step 2: Security & Submit -->
                <div v-else-if="currentStep === 2" key="step2">
                  <div class="row g-3">
                    <div class="col-12 custom-input-group">
                      <label class="form-label text-secondary-dark opacity-75">Create Password</label>
                      <input v-model="form.password" type="password" class="form-control-modern py-2" placeholder="••••••••" required>
                      <div class="password-strength mt-2">
                        <div class="strength-bar" :class="passwordStrengthClass"></div>
                        <span class="small text-muted mt-1 d-block fw-bold" style="font-size: 0.7rem;">{{ passwordStrengthLabel }}</span>
                      </div>
                    </div>
                    <div class="col-12 custom-input-group">
                      <label class="form-label text-secondary-dark opacity-75">Confirm Password</label>
                      <input v-model="form.password_confirmation" type="password" class="form-control-modern py-2" placeholder="••••••••" required>
                    </div>
                    <div class="col-12">
                      <div class="form-check custom-check py-1">
                        <input class="form-check-input" type="checkbox" id="terms" v-model="terms" required>
                        <label class="form-check-label small text-muted pt-1" style="font-size: 0.8rem;" for="terms">
                          I agree to the <a href="#" class="text-gold fw-bold text-decoration-none">Terms of Service</a>.
                        </label>
                      </div>
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
                  class="btn btn-outline-modern flex-grow-1 py-2 px-3 fw-bold hover-lift small"
                >
                  <i class="bi bi-arrow-left me-2"></i> Back
                </button>
                
                <button 
                  v-if="currentStep < 2" 
                  type="button" 
                  @click="validateAndNext" 
                  class="btn btn-gold-modern flex-grow-2 py-2 px-3 shadow-sm small"
                >
                  Next Step <i class="bi bi-arrow-right ms-2"></i>
                </button>
                
                <button 
                  v-else 
                  type="submit" 
                  class="btn btn-gold-modern flex-grow-2 py-2 px-3 shadow-lg text-uppercase tracking-widest small" 
                  :disabled="loading"
                >
                  <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                  {{ loading ? 'Finalizing...' : 'Create Account' }}
                </button>
              </div>

              <!-- Social Register -->
              <div class="separator my-3 text-center">
                <span class="small text-muted px-2 bg-white serif-font fst-italic position-relative z-1" style="font-size: 0.8rem;">Or join with</span>
              </div>

              <div class="row g-2">
                <div class="col-12">
                  <button @click="loginWithGoogle" type="button" class="btn btn-outline-modern w-100 py-2 d-flex align-items-center justify-content-center gap-2 hover-lift">
                    <img src="https://www.google.com/favicon.ico" width="16" alt="Google"> <span class="small fw-bold">Continue with Google</span>
                  </button>
                </div>
              </div>

              <p class="text-center mt-3 text-muted small" style="font-size: 0.8rem;">
                Already have an account? 
                <router-link to="/login" class="text-gold fw-bold text-decoration-none hover-underline">Sign In Here</router-link>
              </p>
            </form>
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
.auth-page {
  min-height: 100vh;
  position: relative;
  overflow: hidden;
  background: #0f172a; /* Dark base for contrast */
}

.auth-bg-overlay {
  position: absolute;
  top: 0; left: 0; right: 0; bottom: 0;
  background: radial-gradient(circle at top right, rgba(188, 145, 81, 0.15), transparent 40%),
              radial-gradient(circle at bottom left, rgba(188, 145, 81, 0.1), transparent 40%),
              url('https://images.unsplash.com/photo-1571896349842-33c89424de2d?auto=format&fit=crop&q=80&w=2000') center/cover;
  filter: brightness(0.6) saturate(1.2);
  z-index: 1;
}

.auth-card-container {
  position: relative;
  z-index: 2;
  width: 100%;
  max-width: 900px;
  padding: 1.5rem;
}

.glass-card {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  border-radius: 20px;
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.bg-gold-gradient {
  background: linear-gradient(135deg, #BC9151 0%, #9A7640 100%);
  position: relative;
}

.bg-gold-gradient::before {
  content: '';
  position: absolute;
  top: 0; left: 0; right: 0; bottom: 0;
  background: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 86c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zm66-3c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zm-46-45c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1zm26 18c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1zm16 18c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1zM24 62c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1zm44-53c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1zM42 1c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1zm-4 44c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1z' fill='%23ffffff' fill-opacity='0.05' fill-rule='evenodd'/%3E%3C/svg%3E");
}

.auth-logo {
  width: 44px;
  height: 44px;
  object-fit: contain;
  border-radius: 10px;
  background: white;
  padding: 3px;
}

/* Step Indicators */
.step-indicators {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
  margin-top: 1.5rem;
}

.step-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  opacity: 0.4;
  transition: all 0.4s ease;
}

.step-item.active { opacity: 0.7; }
.step-item.current { opacity: 1; transform: translateX(10px); }

.step-dot {
  width: 8px; height: 8px;
  border-radius: 50%;
  background: white;
  box-shadow: 0 0 10px rgba(255, 255, 255, 0.5);
}

.step-item.current .step-dot {
  width: 12px; height: 12px;
  border: 2px solid #BC9151;
  background: white;
}

/* Form Styling */
.form-control-modern, .form-select-modern {
  width: 100%;
  padding: 0.65rem 1.25rem;
  background: #f8fafc;
  border: 1.5px solid #e2e8f0;
  border-radius: 10px;
  color: #1e293b;
  font-weight: 500;
  font-size: 0.95rem;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.form-control-modern:focus, .form-select-modern:focus {
  background: white;
  border-color: #BC9151;
  box-shadow: 0 0 0 4px rgba(188, 145, 81, 0.1);
  outline: none;
}

.form-label {
  font-size: 0.7rem;
  font-weight: 700;
  text-transform: uppercase;
  color: #64748b;
  margin-bottom: 0.5rem;
  letter-spacing: 0.5px;
}

/* Password Strength Bar */
.strength-bar {
  height: 4px; border-radius: 2px;
  background: #e2e8f0;
  width: 100%; position: relative;
  overflow: hidden;
}

.strength-bar::after {
  content: ''; position: absolute;
  left: 0; top: 0; height: 100%; width: 0;
  transition: all 0.5s ease;
}

.strength-bar.weak::after { width: 33%; background: #ef4444; }
.strength-bar.medium::after { width: 66%; background: #f59e0b; }
.strength-bar.strong::after { width: 100%; background: #10b981; }

/* Buttons */
.btn-gold-modern {
  background: #BC9151;
  color: white;
  border-radius: 12px;
  font-weight: 700;
  letter-spacing: 0.5px;
  transition: all 0.3s ease;
  border: none;
}

.btn-gold-modern:hover {
  background: #9A7640;
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(188, 145, 81, 0.3);
}

.btn-outline-modern {
  background: transparent;
  border: 1.5px solid #e2e8f0;
  color: #64748b;
  border-radius: 12px;
  font-weight: 600;
  transition: all 0.3s ease;
}

.btn-outline-modern:hover {
  background: #f8fafc;
  border-color: #cbd5e1;
  color: #334155;
}

/* Animations */
.animate-fade-in {
  animation: fadeIn 0.8s ease-out;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

.slide-fade-enter-active {
  transition: all 0.4s ease-out;
}
.slide-fade-leave-active {
  transition: all 0.3s cubic-bezier(1, 0.5, 0.8, 1);
}
.slide-fade-enter-from {
  transform: translateX(20px);
  opacity: 0;
}
.slide-fade-leave-to {
  transform: translateX(-20px);
  opacity: 0;
}
</style>
