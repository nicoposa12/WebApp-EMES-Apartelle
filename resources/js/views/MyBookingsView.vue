<template>
  <div class="my-bookings-page py-5">
    <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-10">
        <h2 class="serif-font mb-4">My Bookings</h2>
        
        <!-- Loading State -->
        <div v-if="loading" class="text-center py-5">
          <div class="spinner-border text-gold" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
          <p class="mt-3 text-muted">Loading your reservations...</p>
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
                  </div>

                  <div class="d-flex justify-content-between align-items-center mt-auto">
                    <span class="small text-muted">
                      <i class="bi bi-clock me-1"></i> Booked on {{ formatDateTime(booking.created_at) }}
                    </span>
                    
                    <!-- Action Buttons -->
                    <div v-if="isCancellable(booking)" class="d-flex gap-2">
                        <button 
                            @click="handleCancelBooking(booking)" 
                            class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1 fw-bold x-small text-uppercase"
                            :disabled="cancelling === booking.id || state.user?.is_suspended"
                            :title="state.user?.is_suspended ? 'Restricted due to account suspension' : 'Cancel this booking'"
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
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';
import { useAuth } from '../store/auth';

const { state } = useAuth();

const bookings = ref([]);
const loading = ref(true);
const cancelling = ref(null);

const fetchBookings = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/reservations');
        bookings.value = response.data;
    } catch (error) {
        console.error('Error fetching bookings:', error);
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

const capitalize = (s) => s ? s.charAt(0).toUpperCase() + s.slice(1) : '';

const getStatusBadgeClass = (status) => {
    const base = 'badge rounded-pill px-3 py-1 fw-bold small ';
    switch(status) {
        case 'pending': return base + 'bg-warning text-dark';
        case 'confirmed': return base + 'bg-success text-white';
        case 'checked-in': return base + 'bg-info text-white';
        case 'cancelled': return base + 'bg-danger text-white';
        case 'completed': return base + 'bg-secondary text-white';
        default: return base + 'bg-secondary text-white';
    }
};

const getRoomImage = (room) => {
    if (room?.image) return room.image;
    // Fallback logic matches other views
    const type = (room?.room_type || '').toLowerCase();
    if (type.includes('suite')) return 'https://images.unsplash.com/photo-1566665797739-1674de7a421a?auto=format&fit=crop&w=600&q=80';
    return 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?auto=format&fit=crop&w=600&q=80';
};

const isCancellable = (booking) => {
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
    const result = await Swal.fire({
        title: 'Cancel Reservation?',
        text: booking.status === 'confirmed' 
            ? "Your refund will be processed according to our policy. Are you sure?" 
            : "Are you sure you want to cancel this reservation?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, Cancel Now',
        cancelButtonText: 'Keep Booking',
        reverseButtons: true
    });

    if (result.isConfirmed) {
        cancelling.value = booking.id;
        try {
            const response = await axios.post(`/api/reservations/${booking.id}/cancel`);
            await Swal.fire({
                icon: 'success',
                title: 'Cancelled',
                text: response.data.message || 'Your reservation has been cancelled.',
                timer: 3000
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
    }
};

onMounted(fetchBookings);
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
</style>
