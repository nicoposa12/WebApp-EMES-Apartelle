<template>
  <div class="result-page bg-cream py-5">
    <div class="container py-4">
      
      <!-- Loading State -->
      <div v-if="loading" class="text-center py-5">
        <div class="spinner-border text-gold" role="status" style="width: 3rem; height: 3rem;">
          <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-3 text-muted fw-bold">Verifying your payment...</p>
      </div>

      <!-- Success Content Grid -->
      <div v-else class="row g-4 justify-content-center align-items-stretch">
        
        <!-- Left Column: Success Confirmation Card -->
        <div :class="reservation ? 'col-lg-5 col-md-6 d-flex' : 'col-md-8 col-lg-6 d-flex'">
          <div class="result-card animate-fade-up w-100 d-flex flex-column justify-content-between">
            <div class="flex-grow-1 d-flex flex-column justify-content-center">
              <!-- Confirmed State -->
              <template v-if="isPaymentConfirmed">
                <div class="result-icon success">
                  <i class="bi bi-check-lg"></i>
                </div>
                <h1 class="result-title serif-font">Booking Confirmed!</h1>
                <p class="result-message mb-4">
                  Thank you for choosing EME's Apartelle. Your reservation has been successfully confirmed.<br>
                  A confirmation email will be sent to you shortly with all the details.
                </p>
              </template>

              <!-- Syncing State -->
              <template v-else-if="syncState === 'syncing'">
                <div class="result-icon syncing">
                  <div class="spinner-border text-gold" role="status" style="width: 2.5rem; height: 2.5rem;">
                    <span class="visually-hidden">Syncing...</span>
                  </div>
                </div>
                <h1 class="result-title serif-font" style="font-size: 1.8rem;">Verifying Payment...</h1>
                <p class="result-message mb-4">
                  We're confirming your payment with our payment provider. 
                  This usually takes a few seconds.<br>
                  <small class="text-muted">Attempt {{ syncAttempts }} of {{ maxSyncAttempts }}</small>
                </p>
              </template>

              <!-- Sync Failed / Pending State -->
              <template v-else>
                <div class="result-icon pending">
                  <i class="bi bi-clock-history"></i>
                </div>
                <h1 class="result-title serif-font" style="font-size: 1.8rem;">Booking Received</h1>
                <p class="result-message mb-3">
                  Your reservation has been created. We couldn't automatically verify the payment right now — 
                  this can happen if the payment is still being processed.
                </p>
                <button @click="retrySync" class="btn btn-gold rounded-pill px-4 py-2 fw-bold text-uppercase small mx-auto d-flex align-items-center gap-2 mb-3" :disabled="syncState === 'syncing'">
                  <i class="bi bi-arrow-repeat"></i> Retry Payment Verification
                </button>
                <p class="small text-muted text-center mb-0">
                  Don't worry — if you've already paid, the admin will confirm your booking shortly.
                </p>
              </template>
            </div>
            
            <div class="result-actions pt-4 border-top mt-auto">
              <router-link to="/" class="btn btn-outline-dark-custom rounded-pill w-100 mb-2.5 py-3 fw-bold text-uppercase small d-flex align-items-center justify-content-center gap-2">
                <i class="bi bi-house"></i> Go to Home
              </router-link>
              <router-link to="/rooms" class="btn btn-gold rounded-pill w-100 py-3 fw-bold text-uppercase text-white border-0 shadow-sm d-flex align-items-center justify-content-center gap-2">
                <i class="bi bi-calendar-plus"></i> Book Another Room
              </router-link>
            </div>
          </div>
        </div>

        <!-- Right Column: Premium E-Receipt Card -->
        <div v-if="reservation" class="col-lg-6 col-md-6 d-flex">
          <div class="receipt-card animate-fade-up delay-1 w-100 d-flex flex-column justify-content-between">
            <div>
              <!-- Receipt Header -->
              <div class="receipt-header">
                <div class="d-flex justify-content-between align-items-start">
                  <div>
                    <h4 class="serif-font fw-bold text-gold mb-0">EME's Apartelle</h4>
                    <span class="receipt-subtitle">Official E-Receipt</span>
                  </div>
                  <div class="text-end">
                    <span class="badge" :class="getPaymentBadgeClass(reservation.payment_status)">
                      {{ reservation.payment_status?.toUpperCase() }}
                    </span>
                  </div>
                </div>
                <div class="receipt-meta row g-2 mt-3 pt-3 border-top border-light">
                  <div class="col-6">
                    <span class="meta-label">Receipt No.</span>
                    <span class="meta-value">#EME-{{ String(reservation.id).padStart(5, '0') }}</span>
                  </div>
                  <div class="col-6 text-end">
                    <span class="meta-label">Date Issued</span>
                    <span class="meta-value">{{ formatDate(reservation.created_at || new Date()) }}</span>
                  </div>
                </div>
              </div>

              <!-- Receipt Body -->
              <div class="receipt-body">
                <!-- Guest Info -->
                <div class="receipt-section">
                  <h6 class="section-title"><i class="bi bi-person me-1.5 text-gold"></i> Guest Details</h6>
                  <div class="row g-2">
                    <div class="col-6">
                      <span class="field-label">Name</span>
                      <span class="field-value">{{ reservation.user?.name || 'Guest' }}</span>
                    </div>
                    <div class="col-6 text-end" v-if="reservation.user?.email">
                      <span class="field-label">Email</span>
                      <span class="field-value text-truncate d-block">{{ reservation.user?.email }}</span>
                    </div>
                  </div>
                </div>

                <!-- Reservation Info -->
                <div class="receipt-section mt-4 pt-3 border-top border-dashed">
                  <h6 class="section-title"><i class="bi bi-key me-1.5 text-gold"></i> Booking Details</h6>
                  <div class="row g-3">
                    <div class="col-6">
                      <span class="field-label">Room Type & Number</span>
                      <span class="field-value fw-bold">Room #{{ reservation.room?.room_number }} ({{ reservation.room?.room_type }})</span>
                    </div>
                    <div class="col-6 text-end">
                      <span class="field-label">Nights</span>
                      <span class="field-value">{{ totalNights }} Night{{ totalNights > 1 ? 's' : '' }}</span>
                    </div>
                    <div class="col-6">
                      <span class="field-label">Check-In</span>
                      <span class="field-value">{{ formatDate(reservation.check_in) }} (12:00 PM)</span>
                    </div>
                    <div class="col-6 text-end">
                      <span class="field-label">Check-Out</span>
                      <span class="field-value">{{ formatDate(reservation.check_out) }} (12:00 PM)</span>
                    </div>
                  </div>
                </div>

                <!-- Financial Breakdown -->
                <div class="receipt-section mt-4 pt-3 border-top border-dashed">
                  <h6 class="section-title"><i class="bi bi-receipt me-1.5 text-gold"></i> Charges Details</h6>
                  <div class="price-row d-flex justify-content-between mb-2">
                    <span class="price-label">Room Rate ({{ totalNights }} Nights)</span>
                    <span class="price-val">₱{{ formatPrice(reservation.room?.price_per_night) }} / night</span>
                  </div>
                  <div class="price-row d-flex justify-content-between mb-2">
                    <span class="price-label">Subtotal</span>
                    <span class="price-val">₱{{ formatPrice(reservation.total_amount) }}</span>
                  </div>
                  <div class="price-row d-flex justify-content-between text-success">
                    <span class="price-label">Taxes & Service Fees</span>
                    <span class="price-val fw-bold">INCLUDED</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Receipt Footer / Total -->
            <div class="receipt-footer mt-4 pt-3 border-top border-3">
              <div v-if="reservation.payment_option === 'half'" class="d-flex justify-content-between align-items-center mb-2">
                <span class="small fw-semibold text-muted mb-0">Total Room Rate</span>
                <span class="fw-bold text-dark mb-0">₱{{ formatPrice(reservation.total_amount) }}</span>
              </div>
              <div v-if="reservation.payment_option === 'half'" class="d-flex justify-content-between align-items-center mb-2 text-muted">
                <span class="small fw-semibold mb-0">Remaining Balance (Pay at Hotel)</span>
                <span class="fw-bold mb-0">₱{{ formatPrice(reservation.total_amount / 2) }}</span>
              </div>
              <div class="d-flex justify-content-between align-items-center mb-4 pt-2" :class="reservation.payment_option === 'half' ? 'border-top border-dashed' : ''">
                <span class="total-label serif-font h5 fw-bold text-dark mb-0">
                  {{ reservation.payment_option === 'half' ? 'Amount Paid (Downpayment)' : 'Total Amount Paid' }}
                </span>
                <span class="total-amount text-gold serif-font h3 fw-bold mb-0">
                  ₱{{ formatPrice(reservation.payment_option === 'half' ? reservation.downpayment_amount : reservation.total_amount) }}
                </span>
              </div>
              <div class="text-center pt-2">
                <p class="receipt-thankyou mb-3 italic-text">Thank you for staying at EME's Apartelle!</p>
                <div class="d-flex gap-2 justify-content-center no-print">
                  <button @click="printReceipt" class="btn btn-sm btn-outline-dark-custom rounded-pill px-4 py-2 fw-bold small d-flex align-items-center gap-2 transition-all">
                    <i class="bi bi-printer"></i> Print E-Receipt
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';

const route = useRoute();
const reservation = ref(null);
const loading = ref(true);

// Sync state tracking: 'idle' | 'syncing' | 'synced' | 'failed'
const syncState = ref('idle');
const syncAttempts = ref(0);
const maxSyncAttempts = 8;
let syncTimer = null;

/**
 * Ensure the auth token from localStorage is set on axios headers.
 * After a full-page redirect from Xendit, the SPA re-initializes and the
 * auth store module may not have run yet when this component mounts.
 */
const ensureAuthToken = () => {
  const token = localStorage.getItem('token');
  if (token && !axios.defaults.headers.common['Authorization']) {
    axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
  }
};

const fetchReservation = async (resId) => {
  try {
    ensureAuthToken();
    const response = await axios.get(`/api/reservations/${resId}`);
    reservation.value = response.data;
  } catch (error) {
    console.error('Error fetching reservation details:', error);
  }
};

/**
 * Attempt to sync payment with Xendit and verify the result.
 * Returns true if payment is confirmed (paid/partially_paid), false otherwise.
 */
const attemptSync = async (resId) => {
  ensureAuthToken();
  try {
    await axios.post(`/api/reservations/${resId}/sync-payment`);
  } catch (error) {
    console.warn(`Sync attempt ${syncAttempts.value} failed:`, error?.response?.status || error.message);
  }

  // Regardless of sync result, fetch the reservation to check current status
  await fetchReservation(resId);

  const status = reservation.value?.payment_status;
  return status === 'paid' || status === 'partially_paid';
};

/**
 * Retry sync with exponential backoff.
 * Starts at 2s, then 3s, 4s, 5s, etc.
 */
const syncWithRetry = async (resId) => {
  syncState.value = 'syncing';
  syncAttempts.value = 0;

  // First attempt immediately
  syncAttempts.value++;
  const confirmed = await attemptSync(resId);
  if (confirmed) {
    syncState.value = 'synced';
    return;
  }

  // Schedule retries with backoff
  const scheduleRetry = () => {
    if (syncAttempts.value >= maxSyncAttempts) {
      syncState.value = 'failed';
      return;
    }

    const delay = (syncAttempts.value + 1) * 1500; // 3s, 4.5s, 6s, ...
    syncTimer = setTimeout(async () => {
      syncAttempts.value++;
      const ok = await attemptSync(resId);
      if (ok) {
        syncState.value = 'synced';
      } else {
        scheduleRetry();
      }
    }, delay);
  };

  scheduleRetry();
};

/** Manual retry button handler */
const retrySync = () => {
  const resId = route.query.res_id;
  if (resId) {
    syncWithRetry(resId);
  }
};

onMounted(async () => {
  ensureAuthToken();
  const resId = route.query.res_id;
  if (resId) {
    loading.value = true;

    // Start sync with retry logic
    await syncWithRetry(resId);

    // If first sync already got the data, loading is done
    // If retries are still running, we at least show what we have
    loading.value = false;
  } else {
    loading.value = false;
  }
});

onUnmounted(() => {
  if (syncTimer) clearTimeout(syncTimer);
});

const isPaymentConfirmed = computed(() => {
  const status = reservation.value?.payment_status;
  return status === 'paid' || status === 'partially_paid';
});

const totalNights = computed(() => {
  if (!reservation.value?.check_in || !reservation.value?.check_out) return 0;
  const start = new Date(reservation.value.check_in);
  const end = new Date(reservation.value.check_out);
  const diff = end - start;
  const nights = diff / (1000 * 60 * 60 * 24);
  return nights > 0 ? nights : 0;
});

const formatDate = (dateString) => {
  if (!dateString) return '';
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
};

const formatPrice = (price) => {
  return parseFloat(price || 0).toLocaleString();
};

const getPaymentBadgeClass = (paymentStatus) => {
  switch (paymentStatus?.toLowerCase()) {
    case 'paid':
      return 'bg-success text-white';
    case 'partially_paid':
      return 'bg-warning text-dark';
    case 'unpaid':
      return 'bg-warning text-dark';
    case 'refunded':
      return 'bg-info text-white';
    default:
      return 'bg-secondary text-white';
  }
};

const printReceipt = () => {
  window.print();
};
</script>

<style scoped>
.result-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 100px 1rem 3rem 1rem !important; /* Top padding to clear header */
  background-color: var(--bg-cream);
}

.result-card {
  background: white;
  border-radius: 24px;
  padding: 3rem 2.5rem;
  text-align: center;
  box-shadow: var(--shadow-xl);
  border: 1px solid rgba(0,0,0,0.02);
}

.result-icon {
  width: 90px;
  height: 90px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 2rem;
}

.result-icon.success {
  background: #ECFDF5;
  color: #22C55E;
}

.result-icon.syncing {
  background: #FFFBEB;
  color: #BC9151;
}

.result-icon.pending {
  background: #EFF6FF;
  color: #3B82F6;
}

.result-icon i {
  font-size: 2.8rem;
}

.result-title {
  font-family: var(--font-serif);
  font-weight: 700;
  font-size: 2.3rem;
  margin-bottom: 1.25rem;
  color: var(--text-dark);
}

.result-message {
  color: var(--text-muted);
  font-size: 1rem;
  line-height: 1.7;
}

.result-actions {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.result-actions .btn {
  font-size: 0.8rem;
  letter-spacing: 0.5px;
}

/* Premium Receipt Card */
.receipt-card {
  background: #ffffff;
  border-radius: 24px;
  padding: 2.5rem;
  box-shadow: 0 15px 35px rgba(188, 145, 81, 0.1);
  border: 1px solid rgba(188, 145, 81, 0.15);
  position: relative;
  overflow: hidden;
}

/* Subtle decorative paper strip at the top of the receipt */
.receipt-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 6px;
  background: linear-gradient(90deg, #BC9151 0%, #D4AF37 50%, #BC9151 100%);
}

.receipt-header {
  margin-bottom: 1.5rem;
}

.receipt-subtitle {
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 2px;
  color: #718096;
  font-weight: 700;
  display: block;
  margin-top: 2px;
}

.receipt-meta {
  font-size: 0.85rem;
}

.meta-label {
  display: block;
  color: #a0aec0;
  font-weight: 600;
  font-size: 0.7rem;
  text-transform: uppercase;
  letter-spacing: 1px;
}

.meta-value {
  font-weight: 700;
  color: #2d3748;
}

.section-title {
  font-size: 0.75rem;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 1.5px;
  color: #BC9151;
  margin-bottom: 1rem;
  display: flex;
  align-items: center;
}

.field-label {
  display: block;
  font-size: 0.7rem;
  font-weight: 700;
  text-transform: uppercase;
  color: #a0aec0;
  letter-spacing: 0.5px;
  margin-bottom: 2px;
}

.field-value {
  font-size: 0.9rem;
  font-weight: 600;
  color: #2d3748;
}

.price-row {
  font-size: 0.9rem;
  font-weight: 600;
}

.price-label {
  color: #718096;
}

.price-val {
  color: #2d3748;
}

.total-label {
  font-size: 1.1rem;
}

.total-amount {
  font-size: 1.8rem;
  letter-spacing: -0.5px;
}

.receipt-thankyou {
  font-size: 0.9rem;
  color: #718096;
  font-style: italic;
}

.border-dashed {
  border-top-style: dashed !important;
  border-top-width: 2px !important;
  border-top-color: #e2e8f0 !important;
}

/* Animations */
.animate-fade-up {
  animation: fadeUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
  opacity: 0;
  transform: translateY(30px);
}

.delay-1 {
  animation-delay: 0.15s;
}

@keyframes fadeUp {
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Print Styles */
@media print {
  /* Hide navbar, chatbot widget, success message card, and action buttons during print */
  header, nav, .result-card, .no-print, .btn, button, .chat-widget, .chatbot-button, iframe {
    display: none !important;
  }
  
  .result-page {
    padding: 0 !important;
    margin: 0 !important;
    background: white !important;
    display: block !important;
    min-height: auto !important;
  }
  
  .container {
    max-width: 100% !important;
    width: 100% !important;
    padding: 0 !important;
    margin: 0 !important;
  }
  
  .receipt-card {
    border: none !important;
    box-shadow: none !important;
    padding: 0 !important;
    margin: 0 auto !important;
    max-width: 600px !important;
  }
  
  .receipt-card::before {
    display: none !important;
  }
  
  .border-dashed {
    border-top-color: #718096 !important;
  }
}

@media (max-width: 991.98px) {
  .result-page {
    padding-top: 90px !important;
  }
  .result-card {
    padding: 2.5rem 1.5rem;
  }
  .receipt-card {
    padding: 2rem 1.5rem;
  }
}

@media (max-width: 575.98px) {
  .result-title {
    font-size: 1.8rem;
  }
}
</style>
