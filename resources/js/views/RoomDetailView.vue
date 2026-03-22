<script setup>
import { ref, reactive, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import Swal from 'sweetalert2';
import { useAuth } from '../store/auth';
import CalendarPicker from '../components/CalendarPicker.vue';

const route = useRoute();
const router = useRouter();
const { state } = useAuth();

const room = ref(null);
const loading = ref(true);
const bookingLoading = ref(false);
const error = ref(null);
const activeImage = ref(-1); // Track active image, -1 is for Main Photo
const systemSettings = ref({ online_booking: true, maintenance_mode: false });

const booking = reactive({
  check_in: '',
  check_out: '',
  guests: 1
});

const showCheckInCalendar = ref(false);
const showCheckOutCalendar = ref(false);

const toggleCheckIn = () => {
  showCheckInCalendar.value = !showCheckInCalendar.value;
  if (showCheckInCalendar.value) showCheckOutCalendar.value = false;
};

const toggleCheckOut = () => {
  showCheckOutCalendar.value = !showCheckOutCalendar.value;
  if (showCheckOutCalendar.value) showCheckInCalendar.value = false;
};

const todayDate = new Date().toISOString().split('T')[0];

const totalNights = computed(() => {
  if (!booking.check_in || !booking.check_out) return 0;
  const start = new Date(booking.check_in);
  const end = new Date(booking.check_out);
  const diff = end - start;
  const nights = diff / (1000 * 60 * 60 * 24);
  return nights > 0 ? nights : 0;
});

const subtotal = computed(() => {
  if (!room.value) return 0;
  return totalNights.value * room.value.price_per_night;
});

const fetchRoom = async () => {
  try {
    loading.value = true;
    const [roomRes, settingsRes] = await Promise.all([
      axios.get(`/api/rooms/${route.params.id}`),
      axios.get('/api/settings/public')
    ]);
    room.value = roomRes.data;
    systemSettings.value = settingsRes.data;
  } catch (err) {
    console.error("API Fetch Error", err);
    error.value = "We couldn't retrieve the details for this unit. It may have been decommissioned.";
  } finally {
    loading.value = false;
  }
};

const formatPrice = (price) => {
  return parseFloat(price || 0).toLocaleString();
};

const getStatusBadge = (status) => {
  switch (status) {
    case 'available': return { class: 'badge-available', text: 'Available' };
    case 'unavailable': return { class: 'badge-unavailable', text: 'Booked' };
    case 'maintenance': return { class: 'badge-maintenance', text: 'Maintenance' };
    default: return { class: 'badge-available', text: 'Available' };
  }
};

const getRoomImage = (room) => {
  if (room.image) return room.image;
  
  // Fallback images based on room type
  const type = room.room_type.toLowerCase();
  if (type.includes('suite')) return 'https://images.unsplash.com/photo-1566665797739-1674de7a421a?auto=format&fit=crop&w=1200&q=80';
  if (type.includes('deluxe')) return 'https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=1200&q=80';
  return 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?auto=format&fit=crop&w=1200&q=80';
};

const handleBooking = async () => {
  if (!booking.check_in || !booking.check_out) {
    Swal.fire({ icon: 'warning', title: 'Dates Required', text: 'Please select both check-in and check-out dates.' });
    return;
  }

  if (totalNights.value <= 0) {
    Swal.fire({ icon: 'warning', title: 'Invalid Dates', text: 'Check-out date must be after check-in date.' });
    return;
  }

  if (!state.isAuthenticated) {
    Swal.fire({
      icon: 'info',
      title: 'Sign In Required',
      text: 'Please sign in to complete your booking.',
      showCancelButton: true,
      confirmButtonText: 'Go to Login',
      confirmButtonColor: '#BC9151'
    }).then((result) => {
      if (result.isConfirmed) {
        router.push(`/login?redirect=${route.fullPath}`);
      }
    });
    return;
  }

  try {
    bookingLoading.value = true;
    const response = await axios.post('/api/reservations', {
      room_id: room.value.id,
      check_in: booking.check_in,
      check_out: booking.check_out,
      guests: booking.guests
    });

    if (response.data.checkout_url) {
      window.location.href = response.data.checkout_url;
    } else {
      Swal.fire({
        icon: 'error',
        title: 'Payment Unavailable',
        text: response.data.message || 'Could not initiate payment. Please try again or contact support.',
        confirmButtonColor: '#BC9151'
      });
    }
  } catch (err) {
    console.error(err);
    Swal.fire({
      icon: 'error',
      title: 'Booking Failed',
      text: err.response?.data?.message || 'Failed to process booking. Please try again.'
    });
  } finally {
    bookingLoading.value = false;
  }
};

const formatDateRange = (start, end) => {
  const options = { month: 'short', day: 'numeric' };
  const startDate = new Date(start).toLocaleDateString('en-US', options);
  const endDate = new Date(end).toLocaleDateString('en-US', options);
  const year = new Date(start).getFullYear();
  return `${startDate} - ${endDate}, ${year}`;
};

onMounted(fetchRoom);
</script>

<template>
  <div class="room-detail-page bg-cream pb-5">
    <div class="container py-5">
      <!-- Back Link -->
      <router-link to="/rooms" class="back-link animate-fade-up border-0 shadow-sm mb-4">
        <i class="bi bi-chevron-left small fw-bold"></i> Back to Rooms
      </router-link>

      <!-- Loading State -->
      <div v-if="loading" class="loading-state py-5 text-center">
        <div class="spinner-border text-gold" style="width: 3rem; height: 3rem;" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-3 text-muted fw-bold">Loading Room Details...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="error-state py-5 text-center">
        <i class="bi bi-exclamation-octagon fs-1 text-danger mb-3"></i>
        <h3 class="fw-bold">{{ error }}</h3>
        <router-link to="/rooms" class="btn btn-gold rounded-pill px-4 mt-3">Back to Rooms</router-link>
      </div>

      <!-- Room Content -->
      <div v-else-if="room" class="row g-5 mt-2">
        <!-- Left Column - Room Details -->
        <div class="col-lg-7 animate-fade-up">
          <!-- Main Image and Gallery -->
          <div class="room-gallery-container mb-4">
            <div class="room-gallery rounded-5 overflow-hidden shadow-gold-lg position-relative mb-3">
              <img :src="activeImage === -1 ? getRoomImage(room) : (room.images[activeImage]?.image_path || getRoomImage(room))" :alt="room.room_type" class="main-image w-100 h-100 object-fit-cover transition-all">
              
              <div class="gallery-overlay p-4 d-flex align-items-end">
                  <span class="status-badge px-4 py-2" :class="getStatusBadge(room.status).class">
                    <i class="bi bi-stars me-2"></i>
                    {{ getStatusBadge(room.status).text }}
                  </span>
              </div>
            </div>

            <!-- Thumbnails if multiple images exist -->
            <div v-if="room.images && room.images.length > 0" class="gallery-thumbnails d-flex gap-2 overflow-x-auto pb-2 custom-scrollbar">
               <!-- Main Image Thumbnail -->
               <div class="thumb-wrapper rounded-4 overflow-hidden flex-shrink-0 cursor-pointer border-3 transition-all p-1"
                    :class="activeImage === -1 ? 'border-gold shadow-gold-sm opacity-100' : 'border-light-subtle opacity-60 hover-opacity-100'"
                    style="width: 100px; height: 75px;"
                    @click="activeImage = -1">
                  <img :src="getRoomImage(room)" class="w-100 h-100 object-fit-cover rounded-3">
               </div>

               <div v-for="(img, idx) in room.images" :key="img.id" 
                    class="thumb-wrapper rounded-4 overflow-hidden flex-shrink-0 cursor-pointer border-3 transition-all p-1"
                    :class="activeImage === idx ? 'border-gold shadow-gold-sm opacity-100' : 'border-light-subtle opacity-60 hover-opacity-100'"
                    style="width: 100px; height: 75px;"
                    @click="activeImage = idx">
                  <img :src="img.image_path" class="w-100 h-100 object-fit-cover rounded-3">
               </div>
            </div>
          </div>

          <!-- Room Info Header -->
          <div class="room-info mb-5">
             <div class="d-flex align-items-center gap-2 mb-3">
                 <span class="badge bg-gold-subtle text-gold text-uppercase tracking-widest px-3 py-2 small fw-bold">{{ room.room_type }}</span>
                 <span class="text-muted small fw-bold" v-if="room.room_size"><i class="bi bi-arrows-fullscreen me-1"></i> {{ room.room_size }} m²</span>
             </div>
             <h1 class="room-title serif-font display-5 fw-bold mb-3 text-secondary-dark">Room #{{ room.room_number }}</h1>
             
             <div class="room-specs-row d-flex gap-3 flex-wrap">
                 <div class="spec-item d-flex align-items-center gap-2 bg-white px-3 py-2 rounded-3 shadow-sm">
                    <i class="bi bi-people-fill text-gold fs-5"></i>
                    <span class="small fw-bold text-dark">Up to {{ room.max_occupancy }} Guests</span>
                 </div>
                 <div class="spec-item d-flex align-items-center gap-2 bg-white px-3 py-2 rounded-3 shadow-sm" v-if="room.bed_type">
                    <i class="bi bi-door-open-fill text-gold fs-5"></i>
                    <span class="small fw-bold text-dark">{{ room.bed_type }}</span>
                 </div>
                 <div class="spec-item d-flex align-items-center gap-2 bg-white px-3 py-2 rounded-3 shadow-sm">
                    <i class="bi bi-award-fill text-gold fs-5"></i>
                    <span class="small fw-bold text-dark">Verified Quality</span>
                 </div>
             </div>
          </div>

          <!-- Description -->
          <div class="room-section mb-4">
            <h4 class="section-title serif-font fw-bold mb-3 text-secondary-dark">Room Details</h4>
            <p class="room-description text-muted lh-lg">{{ room.description || 'Enjoy a comfortable stay in this well-appointed room. We offer a blend of modern comfort and warm hospitality.' }}</p>
          </div>

          <!-- Amenities (Real from DB) -->
          <div class="room-section mb-4">
            <h4 class="section-title serif-font fw-bold mb-4 text-secondary-dark">Amenities Included</h4>
            <div v-if="room.amenities && room.amenities.length > 0" class="amenities-grid-modern">
              <div v-for="amenity in room.amenities" :key="amenity.id" class="amenity-card-detail p-3 rounded-4 bg-white shadow-sm d-flex align-items-center gap-3 transition-all">
                <div class="amenity-icon-box-detail rounded-circle shadow-sm d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px; background-color: var(--primary-gold); color: white;">
                  <i :class="amenity.icon.startsWith('bi-') ? ['bi', amenity.icon] : ['bi', `bi-${amenity.icon}`]"></i>
                </div>
                <div>
                  <h6 class="mb-0 fw-bold text-dark small">{{ amenity.name }}</h6>
                </div>
              </div>
            </div>
            <div v-else class="p-5 bg-white-glass rounded-4 text-center border border-dashed border-gold">
               <i class="bi bi-stars fs-1 text-gold opacity-50 mb-2 d-block"></i>
               <p class="text-muted mb-0">Standard amenities included with this room.</p>
            </div>
          </div>
        </div>

        <!-- Right Column - Booking Sidebar -->
        <div class="col-lg-5 animate-fade-up" style="animation-delay: 0.2s">
          <div class="booking-sidebar sticky-top" style="top: 100px; z-index: 100;">
            <div class="booking-card-premium rounded-4 bg-white shadow-gold-xl border-0">
               <div class="booking-card-header-premium p-3 rounded-top-4 bg-secondary-dark text-white">
                  <h5 class="mb-1 serif-font fw-bold">Book This Room</h5>
                  <p class="x-small text-black-50 mb-0 tracking-wider text-uppercase fw-bold">Instant Confirmation</p>
               </div>
               
               <div class="booking-card-body-premium p-4">
                  <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                     <div>
                        <span class="d-block x-small text-muted text-uppercase fw-bold tracking-widest mb-1">Price per Night</span>
                        <div class="h4 mb-0 fw-bold text-dark serif-font"><span class="text-gold">₱</span>{{ formatPrice(room.price_per_night) }}</div>
                     </div>
                     <div class="text-end">
                        <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill small fw-bold">
                           <i class="bi bi-clock-history me-1"></i> Best Rate
                        </span>
                     </div>
                  </div>

                  <!-- Booking Form -->
                  <form @submit.prevent="handleBooking">
                      <div class="col-12 mb-3">
                        <label class="form-label x-small fw-bold text-uppercase text-muted mb-2 tracking-wider">Select Dates</label>
                        <div class="row g-2">
                          <div class="col-6">
                            <span class="d-block xx-small text-muted text-uppercase fw-bold mb-1">Check-in</span>
                            <div class="position-relative">
                              <div @click="toggleCheckIn" 
                                   class="form-input-premium w-100 p-2 text-center cursor-pointer d-flex align-items-center justify-content-center gap-2"
                                   :class="{'border-gold shadow-gold-sm': showCheckInCalendar}">
                                <i class="bi bi-calendar-date text-gold"></i>
                                <span>{{ booking.check_in || 'mm/dd/yyyy' }}</span>
                              </div>
                              <div v-if="showCheckInCalendar" class="calendar-popup position-absolute start-0 mt-2 z-3 animate-fade-in shadow-lg">
                                <CalendarPicker 
                                  v-model="booking.check_in" 
                                  :min-date="todayDate" 
                                  :disabled-dates="room.reservations || []"
                                  @update:modelValue="showCheckInCalendar = false"
                                />
                              </div>
                            </div>
                          </div>
                          <div class="col-6">
                            <span class="d-block xx-small text-muted text-uppercase fw-bold mb-1">Check-out</span>
                            <div class="position-relative">
                              <div @click="toggleCheckOut" 
                                   class="form-input-premium w-100 p-2 text-center cursor-pointer d-flex align-items-center justify-content-center gap-2"
                                   :class="{'border-gold shadow-gold-sm': showCheckOutCalendar}">
                                <i class="bi bi-calendar-check text-gold"></i>
                                <span>{{ booking.check_out || 'mm/dd/yyyy' }}</span>
                              </div>
                              <div v-if="showCheckOutCalendar" class="calendar-popup position-absolute end-0 mt-2 z-3 animate-fade-in shadow-lg">
                                <CalendarPicker 
                                  v-model="booking.check_out" 
                                  :min-date="booking.check_in || todayDate" 
                                  :disabled-dates="room.reservations || []"
                                  @update:modelValue="showCheckOutCalendar = false"
                                />
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="col-12 mt-2">
                        <label class="form-label admin-label mb-1 small fw-bold text-muted text-uppercase tracking-wider">Number of Guests</label>
                        <select class="form-input-premium w-100 px-3 py-2" v-model="booking.guests">
                          <option v-for="n in room.max_occupancy" :key="n" :value="n">{{ n }} Guest{{ n > 1 ? 's' : '' }}</option>
                        </select>
                      </div>
                    
                    <!-- Financial Summary -->
                    <div v-if="totalNights > 0" class="financial-summary mb-4 p-4 rounded-4 bg-gold-lightest border border-gold-light">
                      <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small fw-semibold">Room Price × {{ totalNights }} Nights</span>
                        <span class="text-dark small fw-bold">₱{{ formatPrice(subtotal) }}</span>
                      </div>
                      <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted small fw-semibold">Service & Tourism Fee</span>
                        <span class="text-success small fw-bold">INCLUDED</span>
                      </div>
                      <div class="d-flex justify-content-between pt-3 border-top border-gold-light">
                        <span class="h5 mb-0 fw-bold text-dark serif-font">Total Price</span>
                        <span class="h5 mb-0 fw-bold text-gold serif-font">₱{{ formatPrice(subtotal) }}</span>
                      </div>
                    </div>

                    <div v-else class="text-center p-4 rounded-4 bg-light border border-dashed mb-4">
                      <p class="small text-muted mb-0"><i class="bi bi-info-circle me-1"></i> Enter your dates to calculate your stay</p>
                    </div>

                    <button 
                      type="submit" 
                      class="btn btn-gold btn-lg w-100 py-3 rounded-pill fw-bold text-uppercase tracking-widest shadow-gold transition-all"
                      :disabled="room.status !== 'available' || bookingLoading || !systemSettings.online_booking || systemSettings.maintenance_mode"
                    >
                      <span v-if="bookingLoading" class="spinner-border spinner-border-sm me-2"></span>
                      <template v-if="systemSettings.maintenance_mode">Maintenance</template>
                      <template v-else-if="!systemSettings.online_booking">Online Disabled</template>
                      <template v-else>{{ room.status === 'available' ? 'Book Now' : 'Unavailable' }}</template>
                    </button>
                  </form>

                  <!-- Booked Dates Section -->
                  <div v-if="room.reservations && room.reservations.length > 0" class="booked-dates-section mt-4 animate-fade-up">
                    <div class="d-flex align-items-center gap-2 mb-3">
                      <div class="p-1 bg-danger-subtle rounded-circle">
                         <i class="bi bi-calendar-x text-danger small"></i>
                      </div>
                      <h6 class="mb-0 fw-bold text-dark small text-uppercase tracking-wider">Booked Dates</h6>
                    </div>
                    <div class="booked-dates-list p-3 rounded-4 bg-light border">
                      <div v-for="res in room.reservations" :key="res.id" class="booked-date-item d-flex justify-content-between align-items-center mb-2 last-mb-0">
                        <div class="d-flex align-items-center gap-2">
                           <span class="dot-indicator bg-danger"></span>
                           <span class="small fw-semibold text-muted">{{ formatDateRange(res.check_in, res.check_out) }}</span>
                        </div>
                        <span class="badge rounded-pill bg-white text-danger border border-danger-subtle x-small fw-bold text-uppercase px-2 py-1">Booked</span>
                      </div>
                    </div>
                    <p class="x-small text-muted mt-2 text-center"><i class="bi bi-info-circle me-1"></i> These dates are currently unavailable for booking.</p>
                  </div>

                  <div class="trust-footer mt-4 pt-3 border-top text-center d-flex justify-content-center gap-4">
                     <span class="xx-small text-muted fw-bold"><i class="bi bi-shield-check text-success me-1"></i> SECURE SSL</span>
                     <span class="xx-small text-muted fw-bold"><i class="bi bi-credit-card text-gold me-1"></i> NO BOOKING FEES</span>
                  </div>
               </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.room-detail-page {
  min-height: 100vh;
  padding-top: 100px;
}

.back-link {
  display: inline-flex;
  align-items: center;
  gap: 0.75rem;
  color: var(--secondary-dark);
  text-decoration: none;
  font-weight: 700;
  padding: 0.75rem 1.5rem;
  background: white;
  border-radius: 50px;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.back-link:hover {
  color: var(--primary-gold);
  transform: translateX(-5px);
  box-shadow: 0 5px 15px rgba(0,0,0,0.08) !important;
}

/* Room Gallery */
.room-gallery-container {
    perspective: 1000px;
}

.room-gallery {
  height: 420px;
  background-color: #f8fafc;
}

.main-image {
    transition: transform 0.6s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.4s ease;
}

.gallery-thumbnails {
    padding: 0.5rem 0.25rem;
}

.thumb-wrapper {
    background: white;
}

.border-gold {
    border-color: var(--primary-gold) !important;
}

.shadow-gold-sm {
    box-shadow: 0 4px 12px rgba(188, 145, 81, 0.25);
}

.hover-opacity-100:hover {
    opacity: 1 !important;
}

.border-light-subtle {
    border-color: #f1f5f9 !important;
}

.shadow-gold-lg {
    box-shadow: 0 20px 50px rgba(188, 145, 81, 0.15);
}

.gallery-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(to top, rgba(15, 23, 42, 0.6), transparent 40%);
}

.status-badge {
  border-radius: 50px;
  font-size: 0.9rem;
  font-weight: 800;
  text-transform: uppercase;
  backdrop-filter: blur(10px);
}

.badge-available { background: rgba(255, 255, 255, 0.95); color: #10B981; }
.badge-unavailable { background: rgba(239, 68, 68, 0.95); color: white; }
.badge-maintenance { background: rgba(245, 158, 11, 0.95); color: white; }

/* Amenities Grid Detail */
.amenities-grid-modern {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
  gap: 1rem;
}

.amenity-card-detail:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.05) !important;
}

.bg-gold-subtle { background-color: rgba(188, 145, 81, 0.1); }
.bg-gold-lightest { background-color: rgba(188, 145, 81, 0.03); }
.border-gold-light { border-color: rgba(188, 145, 81, 0.1) !important; }

/* Form Elements Premium */
.form-input-premium {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    font-weight: 600;
    font-size: 0.9rem;
    transition: all 0.3s;
    outline: none;
}

.form-input-premium:focus {
    background: white;
    border-color: var(--primary-gold);
    box-shadow: 0 0 0 4px rgba(188, 145, 81, 0.1);
}

.shadow-gold-xl {
    box-shadow: 0 30px 60px -12px rgba(188, 145, 81, 0.2);
}

.x-small { font-size: 0.75rem; }
.xx-small { font-size: 0.65rem; }

.cursor-pointer { cursor: pointer; }

.calendar-popup {
    box-shadow: 0 15px 30px rgba(0,0,0,0.1);
    min-width: 280px;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.animate-fade-in {
    animation: fadeIn 0.3s ease-out forwards;
}

@media (max-width: 991.98px) {
  .room-gallery { height: 400px; }
  .room-title { font-size: 2.5rem; }
}

@media (max-width: 767.98px) {
  .room-gallery { height: 300px; }
  .amenities-grid-modern { grid-template-columns: 1fr; }
}
.dot-indicator {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  display: inline-block;
}

.last-mb-0:last-child {
  margin-bottom: 0 !important;
}

.booked-dates-list {
  max-height: 200px;
  overflow-y: auto;
}

.booked-dates-list::-webkit-scrollbar {
  width: 4px;
}

.booked-dates-list::-webkit-scrollbar-thumb {
  background: #e2e8f0;
  border-radius: 10px;
}
</style>
