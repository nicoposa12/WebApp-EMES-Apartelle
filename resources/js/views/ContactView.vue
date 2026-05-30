<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';

const settings = ref({
  hotel_address: 'Purok 1, Bacong, Negros Oriental, Philippines',
  hotel_latitude: '9.2458',
  hotel_longitude: '123.2954',
  phone: '+63 912 345 6789',
  email: 'info@emesapartelle.com'
});

const mapUrl = computed(() => {
  const q = encodeURIComponent(`${settings.value.hotel_latitude},${settings.value.hotel_longitude}`);
  return `https://maps.google.com/maps?q=${q}&t=&z=15&ie=UTF8&iwloc=&output=embed`;
});

onMounted(async () => {
  window.scrollTo(0, 0);
  try {
    const response = await axios.get('/api/settings/public');
    Object.assign(settings.value, response.data);
  } catch (err) {
    console.error('Failed to load map settings', err);
  }
});

const contactInfo = computed(() => [
  {
    icon: 'bi-geo-alt',
    title: 'Address',
    value: settings.value.hotel_address
  },
  {
    icon: 'bi-telephone',
    title: 'Phone',
    value: settings.value.phone
  },
  {
    icon: 'bi-envelope',
    title: 'Email',
    value: settings.value.email
  },
  {
    icon: 'bi-clock',
    title: 'Front Desk',
    value: '24/7 Available'
  }
]);

const form = ref({
  name: '',
  email: '',
  subject: '',
  message: ''
});

const handleSubmit = () => {
  console.log('Form submitted:', form.value);
};
</script>

<template>
  <div class="contact-page bg-cream min-vh-100">
    <!-- Hero Section -->
    <section class="page-header contrast-overlay">
      <div class="container position-relative z-1 text-center animate-fade-up">
        <div class="designer-label mx-auto mb-3 justify-content-center">
          <span class="label-dot bg-white"></span>
          <span class="label-text text-white">EME'S APARTELLE</span>
          <span class="label-dot bg-white"></span>
        </div>
        <h1 class="display-3 fw-bold serif-font mb-4 text-white">Contact Us</h1>
        <p class="section-description mx-auto text-white-50 fs-5 mb-0" style="max-width: 600px;">
          Have questions? We're here to help you anytime.
        </p>
      </div>
    </section>

    <div class="container pb-7 mt-n6 position-relative z-2">
      <div class="row g-5">
        <!-- Contact Information -->
        <div class="col-lg-5 animate-fade-up delay-1">
          <div class="contact-card-info bg-white rounded-5 shadow-lg p-5 h-100">
            <h2 class="serif-font h3 mb-4 text-secondary-dark border-bottom pb-3">Contact Details</h2>
            <div class="contact-info-list">
              <div v-for="(info, index) in contactInfo" :key="index" class="contact-info-item d-flex align-items-center gap-4 mb-4">
                <div class="info-icon-box shadow-gold-sm">
                  <i :class="['bi', info.icon]"></i>
                </div>
                <div class="info-details">
                  <span class="info-title d-block text-muted small fw-bold text-uppercase tracking-wider">{{ info.title }}</span>
                  <span class="info-value fs-6 text-secondary-dark fw-medium lh-sm d-block mt-1">{{ info.value }}</span>
                </div>
              </div>
            </div>

            <div class="divider my-5 bg-light" style="height: 1px;"></div>

            <!-- Interactive Map -->
            <div class="map-wrapper rounded-4 overflow-hidden position-relative shadow-sm border">
               <iframe 
                width="100%" 
                height="300" 
                frameborder="0" 
                scrolling="no" 
                marginheight="0" 
                marginwidth="0" 
                :src="mapUrl">
              </iframe>
            </div>
          </div>
        </div>

        <!-- Contact Form -->
        <div class="col-lg-7 animate-fade-up delay-2">
          <div class="card border-0 shadow-lg rounded-5 p-4 p-md-5 bg-white">
            <div class="mb-5">
              <h2 class="serif-font h3 mb-2 text-secondary-dark">Send a Message</h2>
              <p class="text-muted small">We'll get back to you within 24 hours.</p>
            </div>
            
            <form @submit.prevent="handleSubmit">
              <div class="row g-4">
                <div class="col-md-6">
                  <label class="form-label-premium">Full Name</label>
                  <div class="input-wrapper-premium">
                    <i class="bi bi-person input-icon"></i>
                    <input type="text" class="form-control-premium" v-model="form.name" placeholder="John Doe">
                  </div>
                </div>
                <div class="col-md-6">
                  <label class="form-label-premium">Email Address</label>
                  <div class="input-wrapper-premium">
                    <i class="bi bi-envelope input-icon"></i>
                    <input type="email" class="form-control-premium" v-model="form.email" placeholder="john@example.com">
                  </div>
                </div>
                <div class="col-12">
                  <label class="form-label-premium">Subject of Inquiry</label>
                  <div class="input-wrapper-premium">
                    <i class="bi bi-chat-dots input-icon"></i>
                    <input type="text" class="form-control-premium" v-model="form.subject" placeholder="Reservation Inquiry">
                  </div>
                </div>
                <div class="col-12">
                  <label class="form-label-premium">Message Body</label>
                  <div class="input-wrapper-premium align-items-start pt-2">
                    <i class="bi bi-pencil input-icon mt-2"></i>
                    <textarea class="form-control-premium" rows="5" v-model="form.message" placeholder="How can we assist you today?"></textarea>
                  </div>
                </div>
                <div class="col-12 pt-3">
                  <button type="submit" class="btn btn-gold-premium w-100 py-3 fw-bold text-uppercase tracking-widest d-flex align-items-center justify-content-center gap-3">
                    <i class="bi bi-send-fill"></i>
                    <span>Send Message</span>
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* ========== DESIGNER LABEL ========== */
.designer-label {
  display: flex;
  align-items: center;
  gap: 1rem;
  width: fit-content;
}

.label-dot {
  width: 5px;
  height: 5px;
  border-radius: 50%;
  border: 1px solid white;
}

.label-text {
  font-size: 0.75rem;
  font-weight: 800;
  letter-spacing: 5px;
  text-transform: uppercase;
}

/* ========== PAGE HEADER ========== */
.page-header {
  background: url('/images/unsplash/contact-bg.jpg') center/cover no-repeat;
  position: relative;
  padding: 12rem 0 10rem;
  background-attachment: fixed;
}

.contrast-overlay::before {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(rgba(26, 38, 52, 0.8), rgba(26, 38, 52, 0.95));
}

/* ========== CONTACT SPECIFIC ========== */
.mt-n6 {
  margin-top: -8rem;
}

.pb-7 {
  padding-bottom: 8rem;
}

.info-icon-box {
  width: 54px;
  height: 54px;
  background-color: var(--primary-gold-subtle);
  color: var(--primary-gold);
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.35rem;
  flex-shrink: 0;
  transition: all 0.3s ease;
}

.contact-info-item:hover .info-icon-box {
  background-color: var(--primary-gold);
  color: white;
  transform: scale(1.1);
}

.shadow-gold-sm {
  box-shadow: 0 10px 20px rgba(188, 145, 81, 0.15);
}

.map-wrapper {
  height: 300px;
}

/* ========== FORM PREMIUM STYLES ========== */
.form-label-premium {
  display: block;
  font-size: 0.75rem;
  font-weight: 800;
  text-transform: uppercase;
  color: #64748b;
  letter-spacing: 1.5px;
  margin-bottom: 0.75rem;
}

.input-wrapper-premium {
  position: relative;
  display: flex;
  align-items: center;
}

.input-icon {
  position: absolute;
  left: 1.25rem;
  color: var(--primary-gold);
  font-size: 1.1rem;
  pointer-events: none;
}

.form-control-premium {
  width: 100%;
  padding: 1.1rem 1rem 1.1rem 3.25rem;
  background-color: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 1.1rem;
  font-size: 0.95rem;
  font-weight: 500;
  transition: all 0.3s ease;
  color: #1e293b;
}

.form-control-premium:focus {
  background-color: white;
  border-color: var(--primary-gold);
  box-shadow: 0 10px 25px rgba(188, 145, 81, 0.12);
  outline: none;
}

textarea.form-control-premium {
  padding-top: 1.1rem;
}

.btn-gold-premium {
  background: linear-gradient(135deg, var(--primary-gold) 0%, #a37d45 100%);
  color: white;
  border: none;
  border-radius: 1.1rem;
  transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}

.btn-gold-premium:hover {
  transform: translateY(-4px);
  box-shadow: 0 20px 40px rgba(188, 145, 81, 0.4);
  filter: brightness(1.1);
}

@media (max-width: 991.98px) {
  .mt-n6 { margin-top: -4rem; }
  .page-header { padding: 10rem 0 6rem; }
}

@media (max-width: 767.98px) {
  .pb-7 { padding-bottom: 4rem; }
}
</style>
