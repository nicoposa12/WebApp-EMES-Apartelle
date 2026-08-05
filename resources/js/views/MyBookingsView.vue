<template>
  <div>
  <div class="my-bookings-page py-5">
    <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-10">
        <h2 class="serif-font mb-4">My Bookings</h2>
        
        <!-- Skeleton Loading State -->
        <div v-if="loading" class="d-flex flex-column gap-4">
          <div v-for="n in 3" :key="'skel-' + n" class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-body p-0">
              <div class="row g-0">
                <div class="col-md-4">
                  <div class="skeleton-image skeleton-shimmer" style="min-height: 200px;"></div>
                </div>
                <div class="col-md-8 p-4">
                  <div class="d-flex justify-content-between mb-3">
                    <div>
                      <div class="skeleton-line skeleton-shimmer mb-2" style="width: 80px; height: 14px;"></div>
                      <div class="skeleton-line skeleton-shimmer" style="width: 140px; height: 20px;"></div>
                    </div>
                    <div class="text-end">
                      <div class="skeleton-line skeleton-shimmer mb-2" style="width: 40px; height: 10px; margin-left: auto;"></div>
                      <div class="skeleton-line skeleton-shimmer" style="width: 100px; height: 22px; margin-left: auto;"></div>
                    </div>
                  </div>
                  <div class="row g-3 my-3 border-top border-bottom py-3">
                    <div class="col-4">
                      <div class="skeleton-line skeleton-shimmer mb-2" style="width: 70%; height: 8px;"></div>
                      <div class="skeleton-line skeleton-shimmer" style="width: 90%; height: 14px;"></div>
                    </div>
                    <div class="col-4">
                      <div class="skeleton-line skeleton-shimmer mb-2" style="width: 70%; height: 8px;"></div>
                      <div class="skeleton-line skeleton-shimmer" style="width: 90%; height: 14px;"></div>
                    </div>
                    <div class="col-4">
                      <div class="skeleton-line skeleton-shimmer mb-2" style="width: 60%; height: 8px;"></div>
                      <div class="skeleton-line skeleton-shimmer" style="width: 80px; height: 22px; border-radius: 50px;"></div>
                    </div>
                  </div>
                  <div class="d-flex gap-2 mt-2">
                    <div class="skeleton-line skeleton-shimmer" style="width: 110px; height: 34px; border-radius: 50px;"></div>
                    <div class="skeleton-line skeleton-shimmer" style="width: 110px; height: 34px; border-radius: 50px;"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else-if="bookings.length === 0" class="card border-0 shadow-sm rounded-4 mb-4">
          <div class="card-body p-5 text-center">
            <div class="mb-4">
              <i class="bi bi-calendar-x display-1 text-muted opacity-25"></i>
            </div>
            <h4 class="fw-bold mb-2">No Bookings Yet</h4>
            <p class="text-muted mb-4">You haven't made any reservations yet. Ready to experience EME's Apartelle?</p>
            <router-link to="/rooms" class="btn btn-gold rounded-pill px-4 py-2 fw-bold text-uppercase">Explore Rooms</router-link>
          </div>
        </div>

        <!-- Bookings List -->
        <div v-else class="d-flex flex-column gap-4">
          <div v-for="booking in bookings" :key="booking.id" class="card border-0 shadow-sm rounded-4 overflow-hidden animate-fade-up">
            <div class="card-body p-0">
              <div class="row g-0">
                <!-- Room Image -->
                <div class="col-md-4 position-relative">
                   <div class="h-100 bg-secondary" style="min-height: 200px;">
                      <img 
                        :src="getRoomImage(booking.room)" 
                        class="w-100 h-100 object-fit-cover position-absolute top-0 start-0" 
                        alt="Room Image"
                        loading="lazy"
                      >
                   </div>
                </div>
                
                <!-- Booking Details -->
                <div class="col-md-8 p-4 d-flex flex-column justify-content-center">
                  <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                      <span class="badge bg-gold-subtle text-gold text-uppercase tracking-widest small fw-bold mb-2 d-inline-block">
                        {{ booking.room?.room_type || 'Room' }}
                      </span>
                      <h4 class="serif-font fw-bold mb-1">Room #{{ booking.room?.room_number }}</h4>
                    </div>
                    <div class="text-end">
                      <span class="d-block small text-muted text-uppercase fw-bold">Total</span>
                      <span class="h4 text-gold serif-font fw-bold">₱{{ formatPrice(booking.total_amount) }}</span>
                    </div>
                  </div>

                  <div class="row g-3 my-3 border-top border-bottom py-3">
                    <div class="col-6 col-sm-4">
                      <small class="text-muted d-block text-uppercase fw-bold x-small">Check-in</small>
                      <span class="fw-semibold">{{ formatDate(booking.check_in) }}</span>
                    </div>
                    <div class="col-6 col-sm-4">
                      <small class="text-muted d-block text-uppercase fw-bold x-small">Check-out</small>
                      <span class="fw-semibold">{{ formatDate(booking.check_out) }}</span>
                    </div>
                    <div class="col-6 col-sm-4">
                      <small class="text-muted d-block text-uppercase fw-bold x-small">Status</small>
                      <span :class="getStatusBadgeClass(booking.status)">
                        {{ capitalize(booking.status) }}
                      </span>
                    </div>
                    <!-- Cancellation Reason -->
                    <div v-if="booking.status === 'cancelled' && booking.cancellation_reason" class="col-12 mt-2 pt-2 border-top">
                      <small class="text-danger d-block text-uppercase fw-bold x-small mb-1">
                        <i class="bi bi-info-circle me-1"></i>Cancellation Reason
                      </small>
                      <p class="text-danger mb-0 small bg-danger-subtle p-2 rounded border border-danger-subtle" style="word-break: break-word;">
                        {{ booking.cancellation_reason }}
                      </p>
                    </div>
                    <!-- Cancellation Pending -->
                    <div v-if="booking.status === 'cancellation_pending'" class="col-12 mt-2 pt-2 border-top">
                      <small class="text-warning d-block text-uppercase fw-bold x-small mb-1">
                        <i class="bi bi-hourglass-split me-1"></i>Cancellation Pending Approval
                      </small>
                      <p class="text-warning-emphasis mb-0 small bg-warning-subtle p-2 rounded border border-warning-subtle" style="word-break: break-word;" v-if="booking.cancellation_reason">
                        Reason: {{ booking.cancellation_reason }}
                      </p>
                    </div>
                  </div>

                  <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mt-auto gap-2">
                    <span class="small text-muted mb-2 mb-md-0">
                      <i class="bi bi-clock me-1"></i> Booked on {{ formatDateTime(booking.created_at) }}
                    </span>
                    
                    <!-- Action Buttons -->
                    <div class="d-flex gap-2 flex-wrap justify-content-start justify-content-md-end align-items-center mt-2 mt-md-0">
                        <!-- Extend Stay Button -->
                        <button 
                            v-if="isExtendable(booking)" 
                            @click="handleExtendStay(booking)" 
                            class="btn btn-sm btn-gold rounded-pill px-3 py-1 fw-bold x-small text-uppercase text-white border-0 shadow-sm"
                            :disabled="loading || state.user?.is_suspended"
                            :title="state.user?.is_suspended ? 'Restricted due to account suspension' : 'Extend your stay'"
                            style="font-size: 0.7rem; font-weight: 700; height: 32px;"
                        >
                            <i class="bi bi-calendar-plus me-1"></i>
                            Extend Stay
                        </button>

                        <!-- Write Review Button -->
                        <button 
                            v-if="booking.status === 'completed' && !booking.review" 
                            @click="handleWriteReview(booking)" 
                            class="btn btn-sm btn-gold rounded-pill px-3 py-1 fw-bold x-small text-uppercase text-white border-0 shadow-sm d-inline-flex align-items-center justify-content-center"
                            style="font-size: 0.7rem; font-weight: 700; height: 32px;"
                        >
                            <i class="bi bi-star-fill me-1"></i>
                            Write a Review
                        </button>

                        <!-- Already Reviewed Badge -->
                        <span 
                            v-if="booking.status === 'completed' && booking.review" 
                            class="badge bg-light text-success border border-success-subtle rounded-pill px-3 py-2 fw-bold d-inline-flex align-items-center justify-content-center"
                            style="font-size: 0.7rem; height: 32px;"
                        >
                            <i class="bi bi-check-circle-fill me-1"></i>
                            Reviewed ({{ booking.review.rating }} ★)
                        </span>

                        <!-- Rebook Now Button -->
                        <router-link 
                            v-if="isPast(booking) && booking.room?.id" 
                            :to="`/rooms/${booking.room.id}`" 
                            class="btn btn-sm btn-gold rounded-pill px-3 py-1 fw-bold x-small text-uppercase text-white border-0 shadow-sm d-inline-flex align-items-center justify-content-center"
                            :title="'Book Room #' + booking.room?.room_number + ' again'"
                            style="font-size: 0.7rem; font-weight: 700; height: 32px; text-decoration: none;"
                        >
                            <i class="bi bi-arrow-repeat me-1"></i>
                            Rebook Now
                        </router-link>

                        <!-- E-Receipt Button -->
                        <button 
                            v-if="booking.payment_status === 'paid' || booking.payment_status === 'partially_paid' || ['confirmed', 'checked-in', 'completed'].includes(booking.status)"
                            @click="handleViewReceipt(booking)" 
                            class="btn btn-sm btn-outline-gold rounded-pill px-3 py-1 fw-bold x-small text-uppercase d-inline-flex align-items-center justify-content-center"
                            style="font-size: 0.7rem; font-weight: 700; height: 32px;"
                            title="View your official E-Receipt"
                        >
                            <i class="bi bi-receipt me-1"></i>
                            E-Receipt
                        </button>

                        <!-- File Dispute Button -->
                        <button 
                            v-if="!getDisputeForBooking(booking.id) && ['confirmed', 'checked-in', 'completed'].includes(booking.status)" 
                            @click="handleFileDispute(booking)" 
                            class="btn btn-sm btn-outline-gold rounded-pill px-3 py-1 fw-bold x-small text-uppercase d-inline-flex align-items-center justify-content-center"
                            :disabled="loading || state.user?.is_suspended"
                            :title="state.user?.is_suspended ? 'Restricted due to account suspension' : 'File a dispute regarding this booking'"
                            style="font-size: 0.7rem; font-weight: 700; height: 32px;"
                        >
                            <i class="bi bi-exclamation-octagon me-1"></i>
                            File Dispute
                        </button>

                        <!-- Clickable Dispute Status Badge -->
                        <span 
                            v-if="getDisputeForBooking(booking.id)" 
                            @click="handleViewDisputeDetails(getDisputeForBooking(booking.id))" 
                            :class="getDisputeStatusClass(getDisputeForBooking(booking.id).status)"
                            class="d-inline-flex align-items-center justify-content-center cursor-pointer hover-shadow transition-all"
                            style="cursor: pointer; height: 32px; font-size: 0.7rem; border-radius: 50rem;"
                            title="Click to view dispute details"
                        >
                            <i class="bi bi-exclamation-triangle-fill me-1"></i>
                            {{ formatDisputeStatusLabel(getDisputeForBooking(booking.id).status) }}
                        </span>

                        <!-- Cancel Booking Button -->
                        <button 
                            v-if="isCancellable(booking)"
                            @click="handleCancelBooking(booking)" 
                            class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1 fw-bold x-small text-uppercase d-inline-flex align-items-center justify-content-center"
                            :disabled="cancelling === booking.id || state.user?.is_suspended"
                            :title="state.user?.is_suspended ? 'Restricted due to account suspension' : 'Cancel this booking'"
                            style="font-size: 0.7rem; font-weight: 700; height: 32px;"
                        >
                            <span v-if="cancelling === booking.id" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                            <i v-else class="bi bi-x-circle me-1"></i>
                            {{ state.user?.is_suspended ? 'Suspended' : 'Cancel Booking' }}
                        </button>
                    </div>
                  </div>
                  <p v-if="state.user?.is_suspended" class="text-danger x-small mt-2 mb-0 fw-bold">
                    <i class="bi bi-lock-fill me-1"></i> Actions restricted due to account suspension.
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>

    <!-- ========== EXTEND STAY MODAL ========== -->
    <Teleport to="body">
      <div v-if="showExtendModal" class="modal-backdrop fade show" style="z-index: 1050;" @click="showExtendModal = false"></div>
      <div v-if="showExtendModal" class="modal fade show d-block" tabindex="-1" style="z-index: 1055;" @click.self="showExtendModal = false">
        <div class="modal-dialog modal-dialog-centered modal-lg">
          <div class="modal-content border-0 rounded-5 overflow-hidden shadow-2xl">
            <!-- Modal Header -->
            <div class="modal-header bg-white border-bottom p-4 d-flex justify-content-between align-items-center">
              <div>
                <h3 class="modal-title serif-font text-secondary-dark mb-1">Extend Your Stay</h3>
                <p class="text-muted small mb-0">
                  <i class="bi bi-door-open text-gold"></i> Room #{{ extendingBooking?.room?.room_number }}
                </p>
              </div>
              <button type="button" class="btn-close bg-light rounded-circle p-2" @click="showExtendModal = false" aria-label="Close"></button>
            </div>
            
            <!-- Modal Body -->
            <div class="modal-body bg-cream p-4">
              <div v-if="loadingBookedDates" class="text-center py-5">
                <span class="spinner-border text-gold me-2"></span>
                <span class="text-muted">Loading room schedule...</span>
              </div>
              
              <div v-else class="row g-4">
                <!-- Calendar Section -->
                <div class="col-md-6 d-flex flex-column align-items-center">
                  <label class="form-label small fw-bold text-muted text-uppercase mb-3 align-self-start">Select New Checkout Date</label>
                  <CalendarPicker 
                    v-model="newCheckOut" 
                    :min-date="minExtendCheckout" 
                    :max-date="maxExtendCheckout"
                    :disabled-dates="extendingBookedDates"
                    :is-checkout="true"
                    class="w-100"
                  />
                </div>
                
                <!-- Details & Cost Section -->
                <div class="col-md-6 d-flex flex-column justify-content-between">
                  <div>
                    <h5 class="serif-font fw-bold text-secondary-dark mb-3">Extension Details</h5>
                    
                    <!-- Current Dates -->
                    <div class="mb-3 bg-white p-3 rounded-4 border shadow-sm">
                      <div class="d-flex justify-content-between mb-2">
                        <span class="small text-muted fw-semibold">Current Checkout:</span>
                        <span class="small fw-bold text-dark">{{ formatDate(extendingBooking?.check_out) }}</span>
                      </div>
                      <div class="d-flex justify-content-between">
                        <span class="small text-muted fw-semibold">New Checkout:</span>
                        <span class="small fw-bold text-gold">{{ formatDate(newCheckOut) || 'Select date' }}</span>
                      </div>
                    </div>
                    
                    <!-- Rate Summary -->
                    <div class="alert alert-info border-0 shadow-sm d-flex align-items-start gap-2 rounded-4 py-3 px-3 mb-3">
                      <i class="bi bi-info-circle-fill text-info fs-5 mt-0.5"></i>
                      <span class="small text-muted leading-relaxed" style="font-size: 0.75rem;">
                        Additional nights charged at <strong>₱{{ formatPrice((extendingBooking?.room?.room_type === 'Family Room' || extendingBooking?.room?.room_type === 'Barkadahan Room') ? extendingBooking?.room?.price_per_head : extendingBooking?.room?.price_per_night) }}</strong> 
                        {{ (extendingBooking?.room?.room_type === 'Family Room' || extendingBooking?.room?.room_type === 'Barkadahan Room') ? 'per head per night' : 'per night' }}.
                      </span>
                    </div>

                    <!-- Live cost calculation -->
                    <div v-if="additionalNights > 0" class="bg-white p-3 rounded-4 border shadow-sm">
                      <div class="d-flex justify-content-between mb-2">
                        <span class="small text-muted fw-semibold">Additional Nights:</span>
                        <span class="small fw-bold text-dark">{{ additionalNights }} {{ additionalNights === 1 ? 'night' : 'nights' }}</span>
                      </div>
                      <div class="d-flex justify-content-between mb-2">
                        <span class="small text-muted fw-semibold">Extension Cost:</span>
                        <span class="small fw-bold text-dark">₱{{ formatPrice(additionalCost) }}</span>
                      </div>
                      <hr class="my-2">
                      <div class="d-flex justify-content-between align-items-center">
                        <span class="small fw-bold text-secondary-dark">New Total Booking Amount:</span>
                        <span class="h5 serif-font text-gold fw-bold mb-0">₱{{ formatPrice(newTotalAmount) }}</span>
                      </div>
                    </div>
                  </div>
                  
                  <div class="mt-4 pt-3 border-top d-flex gap-3">
                    <button type="button" class="btn btn-secondary px-4 py-2 rounded-pill flex-grow-1 small text-uppercase fw-bold" @click="showExtendModal = false">Cancel</button>
                    <button 
                      type="button" 
                      class="btn btn-gold px-4 py-2 rounded-pill flex-grow-2 w-100 small text-uppercase fw-bold text-white border-0 shadow-sm"
                      :disabled="submittingExtension || additionalNights <= 0"
                      @click="submitExtension"
                    >
                      <span v-if="submittingExtension" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                      Submit Extension
                    </button>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Modal Footer -->
            <div class="modal-footer bg-white border-top p-3 d-flex justify-content-center">
              <span class="x-small text-muted"><i class="bi bi-clock-history text-gold me-1"></i> Extension requests are updated in real-time</span>
            </div>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';
import { useAuth } from '../store/auth';
import CalendarPicker from '../components/CalendarPicker.vue';

const { state } = useAuth();

const bookings = ref([]);
const disputes = ref([]);
const loading = ref(true);
const cancelling = ref(null);

// Extend Stay Modal State
const showExtendModal = ref(false);
const extendingBooking = ref(null);
const newCheckOut = ref('');
const extendingBookedDates = ref([]);
const loadingBookedDates = ref(false);
const submittingExtension = ref(false);

const minExtendCheckout = computed(() => {
    if (!extendingBooking.value) return '';
    const currentCheckout = extendingBooking.value.check_out;
    return (currentCheckout || '').split(' ')[0] || (currentCheckout || '').split('T')[0];
});

const maxExtendCheckout = computed(() => {
    if (!extendingBooking.value || !extendingBookedDates.value.length) return null;
    const currentCheckoutDate = new Date(minExtendCheckout.value);
    currentCheckoutDate.setHours(0, 0, 0, 0);

    let firstNextDate = null;
    extendingBookedDates.value.forEach(range => {
        const start = new Date(range.check_in || range.start);
        start.setHours(0, 0, 0, 0);
        if (start >= currentCheckoutDate) {
            if (!firstNextDate || start < new Date(firstNextDate)) {
                firstNextDate = range.check_in || range.start;
            }
        }
    });

    return firstNextDate ? firstNextDate.split(' ')[0] : null;
});

const additionalNights = computed(() => {
    if (!extendingBooking.value || !newCheckOut.value) return 0;
    const start = new Date(minExtendCheckout.value);
    const end = new Date(newCheckOut.value);
    const diff = end - start;
    return Math.max(0, Math.ceil(diff / (1000 * 60 * 60 * 24)));
});

const additionalCost = computed(() => {
    if (!extendingBooking.value || additionalNights.value <= 0) return 0;
    const room = extendingBooking.value.room;
    if (!room) return 0;
    
    const isPerHead = room.room_type === 'Family Room' || room.room_type === 'Barkadahan Room';
    const nightlyRate = isPerHead ? (room.price_per_head || 0) : (room.price_per_night || 0);
    
    if (isPerHead) {
        return nightlyRate * (extendingBooking.value.guests || 1) * additionalNights.value;
    }
    return nightlyRate * additionalNights.value;
});

const newTotalAmount = computed(() => {
    if (!extendingBooking.value) return 0;
    return parseFloat(extendingBooking.value.total_amount || 0) + additionalCost.value;
});

const fetchBookings = async () => {
    loading.value = true;
    try {
        const [bookingsRes, disputesRes] = await Promise.all([
            axios.get('/api/reservations'),
            axios.get('/api/disputes')
        ]);
        bookings.value = bookingsRes.data;
        disputes.value = disputesRes.data;
    } catch (error) {
        console.error('Error fetching data:', error);
    } finally {
        loading.value = false;
    }
};

const formatDate = (dateString) => {
    if (!dateString) return '';
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

const formatDateTime = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    const datePart = date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
    const timePart = date.toLocaleTimeString('en-US', {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
    });
    return `${datePart} at ${timePart}`;
};

const formatPrice = (price) => {
    return parseFloat(price || 0).toLocaleString();
};

const capitalize = (s) => {
    if (!s) return '';
    return s.replace(/[-_]/g, ' ').split(' ').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');
};

const getStatusBadgeClass = (status) => {
    const base = 'badge rounded-pill px-3 py-1 fw-bold small ';
    switch(status) {
        case 'pending': return base + 'bg-warning text-dark';
        case 'confirmed': return base + 'bg-success text-white';
        case 'checked-in': return base + 'bg-info text-white';
        case 'cancelled': return base + 'bg-danger text-white';
        case 'cancellation_pending': return base + 'bg-warning text-dark';
        case 'completed': return base + 'bg-secondary text-white';
        default: return base + 'bg-secondary text-white';
    }
};

const getRoomImage = (room) => {
    if (room?.image) return room.image;
    // Fallback logic matches other views
    const type = (room?.room_type || '').toLowerCase();
    if (type.includes('suite')) return '/images/unsplash/suite-room.jpg';
    return '/images/unsplash/standard-room.jpg';
};

const isCancellable = (booking) => {
    // Cancellable only within 1 hour of booking
    if (booking.created_at) {
        const createdAt = new Date(booking.created_at);
        const now = new Date();
        const diffInHours = (now - createdAt) / (1000 * 60 * 60);
        if (diffInHours > 1) {
            return false;
        }
    }

    if (booking.status === 'pending') return true;
    
    if (booking.status === 'confirmed') {
        const checkInDate = new Date(booking.check_in);
        const now = new Date();
        const diffInHours = (checkInDate - now) / (1000 * 60 * 60);
        return diffInHours > 24; // 24-hour cutoff
    }
    
    return false;
};

const handleCancelBooking = async (booking) => {
    let reason = null;

    if (booking.status === 'confirmed') {
        const { value: text, isDismissed } = await Swal.fire({
            title: 'Reason for Cancelling',
            html: `
                <div class="text-start">
                    <p class="small text-muted mb-3">Please let us know why you are cancelling your paid reservation. Refund will be processed according to our policy.</p>
                    <div class="mb-3">
                        <label for="swal-cancellation-select" class="form-label small fw-bold text-muted text-uppercase">Cancellation Reason</label>
                        <select id="swal-cancellation-select" class="form-select py-2">
                            <option value="" disabled selected>Select a reason...</option>
                            <option value="Change of travel plans">Change of travel plans</option>
                            <option value="Travel dates changed">Travel dates changed</option>
                            <option value="Personal emergency / Medical reasons">Personal emergency / Medical reasons</option>
                            <option value="Found better accommodation price/location">Found better accommodation price/location</option>
                            <option value="Other reason">Other reason (please describe below)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="swal-cancellation-details" class="form-label small fw-bold text-muted text-uppercase">Additional Details</label>
                        <textarea id="swal-cancellation-details" class="form-control" rows="3" placeholder="Please provide details (minimum 5 characters if selecting 'Other reason')..."></textarea>
                    </div>
                </div>
            `,
            focusConfirm: false,
            showCancelButton: true,
            confirmButtonText: 'Confirm Cancellation',
            cancelButtonText: 'Keep Booking',
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#718096',
            reverseButtons: true,
            preConfirm: () => {
                const selectVal = document.getElementById('swal-cancellation-select').value;
                const detailsVal = document.getElementById('swal-cancellation-details').value.trim();

                if (!selectVal) {
                    Swal.showValidationMessage('Please select a cancellation reason.');
                    return false;
                }

                if (selectVal === 'Other reason' && detailsVal.length < 5) {
                    Swal.showValidationMessage('Please provide cancellation details (minimum 5 characters) for "Other reason".');
                    return false;
                }

                if (detailsVal && detailsVal.length > 0 && detailsVal.length < 5) {
                    Swal.showValidationMessage('Additional details must be at least 5 characters if provided.');
                    return false;
                }

                return detailsVal ? `${selectVal}: ${detailsVal}` : selectVal;
            }
        });

        if (isDismissed) {
            return;
        }
        reason = text;
    } else {
        const result = await Swal.fire({
            title: 'Cancel Reservation?',
            text: "Are you sure you want to cancel this reservation?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#718096',
            confirmButtonText: 'Yes, Cancel Now',
            cancelButtonText: 'Keep Booking',
            reverseButtons: true
        });

        if (!result.isConfirmed) {
            return;
        }
    }

    cancelling.value = booking.id;
    try {
        const response = await axios.post(`/api/reservations/${booking.id}/cancel`, {
            reason: reason
        });
        await Swal.fire({
            icon: 'success',
            title: 'Cancelled',
            text: response.data.message || 'Your reservation has been cancelled.',
            timer: 2000,
            timerProgressBar: true,
            showConfirmButton: false
        });
        await fetchBookings();
    } catch (error) {
        console.error('Error cancelling booking:', error);
        Swal.fire({
            icon: 'error',
            title: 'Cancellation Failed',
            text: error.response?.data?.message || 'Something went wrong. Please try again or contact management.'
        });
    } finally {
        cancelling.value = null;
    }
};

const isPast = (booking) => {
    if (!booking.check_out) return false;
    const checkout = new Date(booking.check_out);
    const now = new Date();
    return now > checkout;
};

const isExtendable = (booking) => {
    return (booking.status === 'confirmed' || booking.status === 'checked-in') && !isPast(booking);
};

const handleExtendStay = async (booking) => {
    extendingBooking.value = booking;
    newCheckOut.value = '';
    extendingBookedDates.value = [];
    loadingBookedDates.value = true;
    showExtendModal.value = true;

    try {
        const response = await axios.get(`/api/rooms/${booking.room.id}/booked-dates`);
        extendingBookedDates.value = response.data;
        
        // Pre-fill next day after current checkout as default new checkout
        const minCheckoutStr = (booking.check_out || '').split(' ')[0] || (booking.check_out || '').split('T')[0];
        const nextDay = new Date(minCheckoutStr);
        nextDay.setDate(nextDay.getDate() + 1);
        const defaultDate = nextDay.toISOString().split('T')[0];
        
        // Only default if it doesn't exceed next booking start date
        if (!maxExtendCheckout.value || defaultDate <= maxExtendCheckout.value) {
            newCheckOut.value = defaultDate;
        } else if (maxExtendCheckout.value) {
            newCheckOut.value = maxExtendCheckout.value;
        }
    } catch (error) {
        console.error('Failed to fetch booked dates:', error);
    } finally {
        loadingBookedDates.value = false;
    }
};

const submitExtension = async () => {
    if (!newCheckOut.value || additionalNights.value <= 0) return;
    
    submittingExtension.value = true;
    try {
        const checkOutWithTime = `${newCheckOut.value}T12:00`;
        const response = await axios.put(`/api/reservations/${extendingBooking.value.id}`, {
            check_out: checkOutWithTime
        });

        showExtendModal.value = false;

        if (response.data.checkout_url) {
            // Redirect to Xendit for payment
            await Swal.fire({
                icon: 'info',
                title: 'Redirecting to Payment',
                text: 'Please complete the payment for your stay extension.',
                timer: 2000,
                timerProgressBar: true,
                showConfirmButton: false
            });
            window.location.href = response.data.checkout_url;
        } else {
            await Swal.fire({
                icon: 'success',
                title: 'Stay Extended!',
                html: `
                    <div class="small text-muted">
                        Your stay in Room #${extendingBooking.value.room?.room_number} has been extended.<br>
                        New Checkout: <strong>${formatDate(response.data.check_out)}</strong>.<br>
                        Total Amount: <strong>₱${formatPrice(response.data.total_amount)}</strong>.<br><br>
                        <span class="text-danger fw-bold">Please settle the additional amount at the hotel upon checkout.</span>
                    </div>
                `,
                confirmButtonColor: '#BC9151'
            });
            await fetchBookings();
        }
    } catch (error) {
        console.error('Error extending stay:', error);
        Swal.fire({
            icon: 'error',
            title: 'Extension Failed',
            text: error.response?.data?.message || 'Could not extend stay. The room might be booked by another guest for those dates.'
        });
    } finally {
        submittingExtension.value = false;
    }
};

const handleWriteReview = async (booking) => {
    let selectedRating = 5;
    
    const { value: formValues } = await Swal.fire({
        title: 'Review Your Stay',
        html: `
            <div class="text-start">
                <p class="small text-muted mb-3">We hope you had a wonderful stay in Room #${booking.room?.room_number}! Share your experience with us and future guests.</p>
                <div class="mb-4 text-center">
                    <label class="form-label d-block small fw-bold text-muted text-uppercase mb-2">Overall Rating</label>
                    <div class="star-rating-selector fs-3 d-flex justify-content-center gap-2">
                        <span class="star-btn cursor-pointer text-gold text-warning" data-val="1" style="cursor: pointer;"><i class="bi bi-star-fill text-warning"></i></span>
                        <span class="star-btn cursor-pointer text-gold text-warning" data-val="2" style="cursor: pointer;"><i class="bi bi-star-fill text-warning"></i></span>
                        <span class="star-btn cursor-pointer text-gold text-warning" data-val="3" style="cursor: pointer;"><i class="bi bi-star-fill text-warning"></i></span>
                        <span class="star-btn cursor-pointer text-gold text-warning" data-val="4" style="cursor: pointer;"><i class="bi bi-star-fill text-warning"></i></span>
                        <span class="star-btn cursor-pointer text-gold text-warning" data-val="5" style="cursor: pointer;"><i class="bi bi-star-fill text-warning"></i></span>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="swal-input-comment" class="form-label small fw-bold text-muted text-uppercase">Your Review</label>
                    <textarea id="swal-input-comment" class="form-control" rows="4" placeholder="How was the bed? The cleanliness? The service? (Min. 5 characters)"></textarea>
                </div>
            </div>
        `,
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonColor: '#BC9151',
        cancelButtonColor: '#718096',
        confirmButtonText: 'Submit Review',
        didOpen: () => {
            const stars = Swal.getHtmlContainer().querySelectorAll('.star-btn');
            stars.forEach(star => {
                star.addEventListener('click', (e) => {
                    const val = parseInt(star.getAttribute('data-val'));
                    selectedRating = val;
                    // Update star colors
                    stars.forEach(s => {
                        const sVal = parseInt(s.getAttribute('data-val'));
                        const icon = s.querySelector('i');
                        if (sVal <= val) {
                            icon.className = 'bi bi-star-fill text-warning';
                        } else {
                            icon.className = 'bi bi-star text-muted';
                        }
                    });
                });
            });
        },
        preConfirm: () => {
            const comment = document.getElementById('swal-input-comment').value;
            if (!comment || comment.trim().length < 5) {
                Swal.showValidationMessage('Please write a review comment (minimum 5 characters).');
                return false;
            }
            return {
                rating: selectedRating,
                comment: comment
            };
        }
    });

    if (formValues) {
        loading.value = true;
        try {
            await axios.post('/api/reviews', {
                room_id: booking.room.id,
                rating: formValues.rating,
                comment: formValues.comment
            });
            
            await Swal.fire({
                icon: 'success',
                title: 'Review Submitted!',
                text: 'Thank you for your feedback! It has been successfully posted.',
                iconColor: '#BC9151',
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true
            });
            
            await fetchBookings();
        } catch (error) {
            console.error('Error submitting review:', error);
            Swal.fire({
                icon: 'error',
                title: 'Submission Failed',
                text: error.response?.data?.message || 'Could not submit your review. Please try again later.'
            });
        } finally {
            loading.value = false;
        }
    }
};

const getDisputeForBooking = (bookingId) => {
    return disputes.value.find(d => d.reservation_id === bookingId);
};

const getDisputeStatusClass = (status) => {
    const base = 'badge rounded-pill px-3 py-1 fw-bold small ';
    switch(status) {
        case 'pending': return base + 'bg-warning text-dark border border-warning-subtle';
        case 'under_investigation': return base + 'bg-info text-white border border-info-subtle';
        case 'resolved': return base + 'bg-success text-white border border-success-subtle';
        case 'rejected': return base + 'bg-danger text-white border border-danger-subtle';
        default: return base + 'bg-secondary text-white';
    }
};

const formatDisputeStatusLabel = (status) => {
    switch(status) {
        case 'pending': return 'Dispute: Pending Review';
        case 'under_investigation': return 'Dispute: Under Investigation';
        case 'resolved': return 'Dispute: Resolved';
        case 'rejected': return 'Dispute: Rejected';
        default: return 'Dispute';
    }
};

const handleViewDisputeDetails = (dispute) => {
    Swal.fire({
        title: `Dispute Details`,
        html: `
            <div class="text-start">
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted text-uppercase mb-1">Reason</label>
                    <div class="fw-semibold text-secondary-dark">${capitalize(dispute.reason.replace('_', ' '))}</div>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted text-uppercase mb-1">Description</label>
                    <p class="text-muted small bg-light p-2.5 rounded-3 font-italic mb-0">"${dispute.description}"</p>
                </div>
                ${dispute.admin_remarks ? `
                <div class="alert alert-success border-0 shadow-sm rounded-4 p-3 mb-0">
                    <h6 class="fw-bold mb-1"><i class="bi bi-award-fill me-1"></i> Management Response</h6>
                    <p class="small text-muted mb-0 font-italic">"${dispute.admin_remarks}"</p>
                </div>
                ` : `
                <div class="alert alert-warning border-0 shadow-sm rounded-4 p-3 mb-0">
                    <p class="small text-muted mb-0"><i class="bi bi-clock-history me-1"></i> Our management is currently reviewing your dispute. We will respond with remarks shortly.</p>
                </div>
                `}
            </div>
        `,
        confirmButtonColor: '#BC9151',
        confirmButtonText: 'Got It'
    });
};

const handleViewReceipt = (booking) => {
    const checkInDate = new Date(booking.check_in);
    const checkOutDate = new Date(booking.check_out);
    const totalNights = Math.max(1, Math.round((checkOutDate - checkInDate) / (1000 * 60 * 60 * 24)));
    
    // Status Badge Class
    let badgeClass = 'bg-secondary text-white';
    const paymentStatus = booking.payment_status?.toLowerCase() || 'unpaid';
    if (paymentStatus === 'paid') badgeClass = 'bg-success text-white';
    else if (paymentStatus === 'partially_paid') badgeClass = 'bg-warning text-dark';
    else if (paymentStatus === 'unpaid') badgeClass = 'bg-warning text-dark';
    else if (paymentStatus === 'refunded') badgeClass = 'bg-info text-white';

    const formattedCheckIn = formatDate(booking.check_in);
    const formattedCheckOut = formatDate(booking.check_out);
    const formattedCreated = formatDate(booking.created_at || new Date());
    const receiptNo = `#EME-${String(booking.id).padStart(5, '0')}`;
    
    const isPerHead = booking.room?.room_type === 'Family Room' || booking.room?.room_type === 'Barkadahan Room';
    const nightlyRate = isPerHead ? (booking.room?.price_per_head || 0) : (booking.room?.price_per_night || 0);
    const subtotal = booking.total_amount || 0;
    
    let balanceHtml = '';
    let totalPaidHtml = '';
    if (booking.payment_option === 'half') {
        const remainingBalance = subtotal / 2;
        const paidAmount = booking.downpayment_amount || (subtotal / 2);
        balanceHtml = `
            <div class="d-flex justify-content-between align-items-center mb-2 text-muted">
                <span class="small fw-semibold mb-0">Remaining Balance (Pay at Hotel)</span>
                <span class="fw-bold mb-0">₱${formatPrice(remainingBalance)}</span>
            </div>
        `;
        totalPaidHtml = `
            <span class="total-label serif-font h5 fw-bold text-dark mb-0" style="font-size: 0.95rem;">Amount Paid (Downpayment)</span>
            <span class="total-amount text-gold serif-font h3 fw-bold mb-0" style="font-size: 1.5rem;">₱${formatPrice(paidAmount)}</span>
        `;
    } else {
        totalPaidHtml = `
            <span class="total-label serif-font h5 fw-bold text-dark mb-0" style="font-size: 0.95rem;">Total Amount Paid</span>
            <span class="total-amount text-gold serif-font h3 fw-bold mb-0" style="font-size: 1.5rem;">₱${formatPrice(subtotal)}</span>
        `;
    }

    Swal.fire({
        title: '',
        html: `
            <div class="receipt-popup-container text-start">
                <!-- Receipt Header -->
                <div class="receipt-header">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h4 class="serif-font fw-bold text-gold mb-0" style="font-size: 1.5rem;">EME's Apartelle</h4>
                            <span class="receipt-subtitle">Official E-Receipt</span>
                        </div>
                        <div class="text-end">
                            <span class="badge ${badgeClass} px-3 py-1 rounded-pill fw-bold small">
                                ${paymentStatus.replace('_', ' ').toUpperCase()}
                            </span>
                        </div>
                    </div>
                    <div class="receipt-meta row g-2 mt-3 pt-3 border-top border-light">
                        <div class="col-6">
                            <span class="meta-label">Receipt No.</span>
                            <span class="meta-value">${receiptNo}</span>
                        </div>
                        <div class="col-6 text-end">
                            <span class="meta-label">Date Issued</span>
                            <span class="meta-value">${formattedCreated}</span>
                        </div>
                    </div>
                </div>

                <!-- Receipt Body -->
                <div class="receipt-body">
                    <!-- Guest Info -->
                    <div class="receipt-section">
                        <h6 class="section-title mb-2" style="font-size: 0.75rem;"><i class="bi bi-person me-1.5 text-gold"></i> Guest Details</h6>
                        <div class="row g-2">
                            <div class="col-6">
                                <span class="field-label">Name</span>
                                <span class="field-value">${state.user?.name || 'Guest'}</span>
                            </div>
                            <div class="col-6 text-end">
                                <span class="field-label">Email</span>
                                <span class="field-value text-truncate d-block">${state.user?.email || ''}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Booking Info -->
                    <div class="receipt-section mt-4 pt-3 border-top border-dashed">
                        <h6 class="section-title mb-2" style="font-size: 0.75rem;"><i class="bi bi-key me-1.5 text-gold"></i> Booking Details</h6>
                        <div class="row g-3">
                            <div class="col-6">
                                <span class="field-label">Room Type & Number</span>
                                <span class="field-value fw-bold">Room #${booking.room?.room_number} (${booking.room?.room_type})</span>
                            </div>
                            <div class="col-6 text-end">
                                <span class="field-label">Nights</span>
                                <span class="field-value">${totalNights} Night${totalNights > 1 ? 's' : ''}</span>
                            </div>
                            <div class="col-6">
                                <span class="field-label">Check-In</span>
                                <span class="field-value">${formattedCheckIn} (12:00 PM)</span>
                            </div>
                            <div class="col-6 text-end">
                                <span class="field-label">Check-Out</span>
                                <span class="field-value">${formattedCheckOut} (12:00 PM)</span>
                            </div>
                        </div>
                    </div>

                    <!-- Financial Breakdown -->
                    <div class="receipt-section mt-4 pt-3 border-top border-dashed">
                        <h6 class="section-title mb-2" style="font-size: 0.75rem;"><i class="bi bi-receipt me-1.5 text-gold"></i> Charges Details</h6>
                        <div class="price-row d-flex justify-content-between mb-2">
                            <span class="price-label">${isPerHead ? 'Per Head Rate' : 'Room Rate'}</span>
                            <span class="price-val">₱${formatPrice(nightlyRate)} ${isPerHead ? '/ head / night' : '/ night'}</span>
                        </div>
                        ${isPerHead ? `
                        <div class="price-row d-flex justify-content-between mb-2">
                            <span class="price-label">Guests</span>
                            <span class="price-val">${booking.guests || 1} Guest${(booking.guests || 1) > 1 ? 's' : ''}</span>
                        </div>
                        ` : ''}
                        <div class="price-row d-flex justify-content-between mb-2">
                            <span class="price-label">Nights</span>
                            <span class="price-val">${totalNights} Night${totalNights > 1 ? 's' : ''}</span>
                        </div>
                        <div class="price-row d-flex justify-content-between mb-2 border-top border-light pt-2">
                            <span class="price-label">Breakdown</span>
                            <span class="price-val text-muted small" style="font-style: italic;">
                                ${isPerHead ? `((₱${formatPrice(nightlyRate)} × ${booking.guests || 1} guests) × ${totalNights} nights)` : `(₱${formatPrice(nightlyRate)} × ${totalNights} nights)`}
                            </span>
                        </div>
                        <div class="price-row d-flex justify-content-between mb-2">
                            <span class="price-label fw-bold">Subtotal</span>
                            <span class="price-val fw-bold">₱${formatPrice(subtotal)}</span>
                        </div>
                        <div class="price-row d-flex justify-content-between text-success">
                            <span class="price-label">Taxes & Service Fees</span>
                            <span class="price-val fw-bold">INCLUDED</span>
                        </div>
                    </div>
                </div>

                <!-- Receipt Footer / Total -->
                <div class="receipt-footer mt-4 pt-3 border-top border-3">
                    ${booking.payment_option === 'half' ? `
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small fw-semibold text-muted mb-0">Total Room Rate</span>
                        <span class="fw-bold text-dark mb-0">₱${formatPrice(subtotal)}</span>
                    </div>
                    ` : ''}
                    ${balanceHtml}
                    <div class="d-flex justify-content-between align-items-center mb-4 pt-2 ${booking.payment_option === 'half' ? 'border-top border-dashed' : ''}">
                        ${totalPaidHtml}
                    </div>
                    <div class="text-center pt-2">
                        <p class="receipt-thankyou mb-3 italic-text">Thank you for staying at EME's Apartelle!</p>
                    </div>
                </div>
            </div>
        `,
        showCancelButton: true,
        showConfirmButton: true,
        confirmButtonText: '<i class="bi bi-printer me-1"></i> Print',
        cancelButtonText: '<i class="bi bi-file-earmark-pdf me-1"></i> Download PDF',
        denyButtonText: 'Close',
        showDenyButton: true,
        confirmButtonColor: '#BC9151',
        cancelButtonColor: '#1A2634',
        denyButtonColor: '#718096',
        customClass: {
            popup: 'premium-swal-popup receipt-popup',
            actions: 'premium-swal-actions d-flex flex-wrap gap-2 justify-content-center'
        },
        width: '550px'
    }).then((result) => {
        if (result.isConfirmed) {
            window.open(`/invoices/${booking.id}/view?print=true`, '_blank');
        } else if (result.dismiss === Swal.DismissReason.cancel) {
            window.open(`/invoices/${booking.id}/download`, '_blank');
        }
    });
};

const handleFileDispute = async (booking) => {
    const { value: formValues } = await Swal.fire({
        title: 'File a Dispute',
        html: `
            <div class="text-start">
                <p class="small text-muted mb-3">Please provide the details of your dispute. Our support and management team will investigate immediately.</p>
                <div class="mb-3">
                    <label for="swal-input-reason" class="form-label small fw-bold text-muted text-uppercase">Dispute Reason</label>
                    <select id="swal-input-reason" class="form-select py-2">
                        <option value="" disabled selected>Select a reason...</option>
                        <option value="billing">Billing or Payment Issue</option>
                        <option value="room_condition">Room or Amenity Condition</option>
                        <option value="service_issue">Customer Service or Guest Care</option>
                        <option value="other">Other Issue</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="swal-input-description" class="form-label small fw-bold text-muted text-uppercase">Describe Your Issue</label>
                    <textarea id="swal-input-description" class="form-control" rows="5" placeholder="Please provide specific details so we can investigate accurately (Min. 10 characters)..."></textarea>
                </div>
            </div>
        `,
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonColor: '#BC9151',
        cancelButtonColor: '#718096',
        confirmButtonText: 'Submit Dispute',
        preConfirm: () => {
            const reason = document.getElementById('swal-input-reason').value;
            const description = document.getElementById('swal-input-description').value;
            if (!reason) {
                Swal.showValidationMessage('Please select a dispute reason.');
                return false;
            }
            if (!description || description.trim().length < 10) {
                Swal.showValidationMessage('Please provide a description (minimum 10 characters).');
                return false;
            }
            return { reason, description };
        }
    });

    if (formValues) {
        loading.value = true;
        try {
            const response = await axios.post('/api/disputes', {
                reservation_id: booking.id,
                reason: formValues.reason,
                description: formValues.description
            });

            await Swal.fire({
                icon: 'success',
                title: 'Dispute Filed',
                text: response.data.message || 'Your dispute has been successfully recorded. We will review it shortly.',
                timer: 2000,
                timerProgressBar: true,
                showConfirmButton: false
            });

            await fetchBookings();
        } catch (error) {
            console.error('Error filing dispute:', error);
            Swal.fire({
                icon: 'error',
                title: 'Filing Failed',
                text: error.response?.data?.message || 'Could not register your dispute. Please try again.'
            });
        } finally {
            loading.value = false;
        }
    }
};

/**
 * Auto-sync payment status for reservations that are partially_paid but have a Xendit invoice.
 * This handles cases where the Xendit webhook doesn't fire (e.g. localhost).
 * Called silently in background after page load.
 */
const autoSyncPendingPayments = async (reservationList) => {
    const toSync = reservationList.filter(b =>
        b.xendit_invoice_id &&
        b.payment_status === 'partially_paid' &&
        ['pending', 'confirmed', 'checked-in'].includes(b.status)
    );
    if (!toSync.length) return;

    await Promise.all(toSync.map(b =>
        axios.post(`/api/reservations/${b.id}/sync-payment`)
            .then(res => {
                // Update the local booking state without a full page reload
                if (res.data.payment_status) {
                    b.payment_status = res.data.payment_status;
                }
                if (res.data.status) {
                    b.status = res.data.status;
                }
            })
            .catch(() => { /* Silently ignore individual sync failures */ })
    ));
};

onMounted(async () => {
    await fetchBookings();
    // Auto-sync any reservations that returned from a Xendit payment
    // but whose webhook hasn't fired yet (common on localhost)
    await autoSyncPendingPayments(bookings.value);
});
</script>

<style scoped>
.my-bookings-page {
    padding-top: 100px !important;
}
.bg-gold-subtle {
    background-color: rgba(188, 145, 81, 0.1);
}
.x-small {
    font-size: 0.7rem;
    letter-spacing: 0.5px;
}
.animate-fade-up {
    animation: fadeUp 0.5s ease-out forwards;
}
@keyframes fadeUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

/* ========== SKELETON LOADING ========== */
.skeleton-image {
    background: #e2e8f0;
}

.skeleton-line {
    background: #e2e8f0;
    border-radius: 6px;
}

.skeleton-shimmer {
    position: relative;
    overflow: hidden;
}

.skeleton-shimmer::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(
        90deg,
        transparent 0%,
        rgba(255, 255, 255, 0.5) 50%,
        transparent 100%
    );
    animation: shimmer-sweep 1.5s ease-in-out infinite;
}

@keyframes shimmer-sweep {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(100%); }
}
</style>
