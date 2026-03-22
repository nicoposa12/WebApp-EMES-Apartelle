<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import axios from 'axios';

const rooms = ref([]);
const loading = ref(true);
const error = ref(null);
const filterType = ref('all');
const systemSettings = ref({
    maintenance_mode: false,
    online_booking: true
});

const fetchRooms = async () => {
    loading.value = true;
    try {
        const [roomsRes, settingsRes] = await Promise.all([
            axios.get('/api/rooms'),
            axios.get('/api/settings/public')
        ]);
        rooms.value = roomsRes.data;
        systemSettings.value = settingsRes.data;
    } catch (err) {
        console.error("API Error:", err);
        error.value = "Failed to load rooms. Please try again later.";
    } finally {
        loading.value = false;
    }
};

// Dynamically get unique room types for filtering
const roomTypes = computed(() => {
    const types = new Set(rooms.value.map(r => r.room_type));
    return ['all', ...Array.from(types)];
});

const filteredRooms = computed(() => {
    if (filterType.value === 'all') return rooms.value;
    return rooms.value.filter(room => room.room_type === filterType.value);
});

const formatPrice = (price) => {
    return parseFloat(price || 0).toLocaleString();
};

const getStatusBadge = (status) => {
    switch (status) {
        case 'available': return { class: 'badge-available', text: 'Available', icon: 'bi-check-circle-fill' };
        case 'unavailable': return { class: 'badge-unavailable', text: 'Booked', icon: 'bi-x-circle-fill' };
        case 'maintenance': return { class: 'badge-maintenance', text: 'Maintenance', icon: 'bi-tools' };
        default: return { class: 'badge-available', text: 'Available', icon: 'bi-check-circle-fill' };
    }
};

const getRoomImage = (room) => {
    if (room.image) return room.image;
    
    // Fallback based on type keywords
    const type = room.room_type.toLowerCase();
    if (type.includes('suite')) return 'https://images.unsplash.com/photo-1566665797739-1674de7a421a?auto=format&fit=crop&w=800&q=80';
    if (type.includes('deluxe')) return 'https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=800&q=80';
    return 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?auto=format&fit=crop&w=800&q=80';
};

onMounted(fetchRooms);
</script>

<template>
  <div class="rooms-page">
    <section class="page-header contrast-overlay">
      <div class="container position-relative z-1 text-center animate-fade-up">
        <div class="designer-label mx-auto mb-3 justify-content-center">
          <span class="label-dot bg-white"></span>
          <span class="label-text text-white">EME'S APARTELLE</span>
          <span class="label-dot bg-white"></span>
        </div>
        <h1 class="display-3 fw-bold serif-font mb-4 text-white">Our Rooms</h1>
        <p class="section-description mx-auto text-white-50 fs-5 mb-0" style="max-width: 700px;">
          Explore our comfortable rooms designed for your stay.
        </p>
      </div>
    </section>

    <!-- Rooms Content -->
    <section class="rooms-content py-7 bg-cream">
      <div class="container">
        <!-- Dynamic Filter Tabs -->
        <div class="filter-tabs animate-fade-up delay-1 mb-7" v-if="roomTypes.length > 1">
          <button 
            v-for="type in roomTypes"
            :key="type"
            class="filter-tab" 
            :class="{ 'active': filterType === type }" 
            @click="filterType = type"
          >
            {{ type === 'all' ? 'All Rooms' : type }}
          </button>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="loading-state glass-panel py-7 rounded-5 d-flex flex-column align-items-center justify-content-center text-center">
          <div class="spinner-border text-gold mb-3" style="width: 3rem; height: 3rem;" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
          <p class="text-muted fw-bold tracking-widest small text-uppercase mb-0">Loading rooms...</p>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="error-state glass-panel py-7 rounded-5 border-danger d-flex flex-column align-items-center text-center">
          <i class="bi bi-exclamation-triangle fs-1 text-danger mb-3"></i>
          <p class="h5 serif-font">{{ error }}</p>
          <button class="btn btn-gold rounded-pill px-4 mt-3" @click="fetchRooms">Retry Connection</button>
        </div>

        <!-- Maintenance Mode Alert -->
        <div v-else-if="systemSettings.maintenance_mode" class="maintenance-state glass-panel py-7 rounded-5 text-center mb-5 animate-fade-up">
          <div class="mb-4">
            <i class="bi bi-tools fs-1 text-gold opacity-50"></i>
          </div>
          <h2 class="serif-font mb-3">System Maintenance</h2>
          <p class="text-muted mb-4 max-w-600 mx-auto">We're currently performing scheduled maintenance to improve your experience. Online reservations are temporarily unavailable. Please contact us via phone for urgent bookings.</p>
          <router-link to="/contact" class="btn btn-gold rounded-pill px-5 py-2 fw-bold text-uppercase">Contact Support</router-link>
        </div>

        <!-- Online Booking Disabled Notice -->
        <div v-if="!systemSettings.online_booking && !systemSettings.maintenance_mode" class="alert alert-warning rounded-4 border-0 shadow-sm p-4 mb-5 animate-fade-up d-flex align-items-center gap-3">
           <i class="bi bi-calendar-x fs-3"></i>
           <div>
              <h6 class="mb-1 fw-bold">Online Reservations are Currently Restricted</h6>
              <p class="mb-0 small opacity-75">You can still browse our rooms, but online booking is temporarily disabled. Please reach out to our staff to reserve a room.</p>
           </div>
        </div>

        <!-- Empty State -->
        <div v-else-if="filteredRooms.length === 0" class="empty-state glass-panel py-7 rounded-5 d-flex flex-column align-items-center text-center">
          <div class="mb-4">
            <i class="bi bi-search fs-1 text-muted opacity-50"></i>
          </div>
          <h3 class="serif-font mb-3">No Rooms Found</h3>
          <p class="text-muted mb-4">Try selecting a different category or check back later.</p>
          <button class="btn btn-gold rounded-pill px-5 py-2 fw-bold text-uppercase" @click="filterType = 'all'">View All Rooms</button>
        </div>

        <!-- Rooms Grid -->
        <div v-else class="row g-4 g-lg-5">
          <div 
            v-for="(room, index) in filteredRooms" 
            :key="room.id" 
            class="col-lg-4 col-md-6 animate-fade-up"
            :style="{ animationDelay: `${index * 0.1}s` }"
          >
            <div class="room-card card-hover shadow-gold-sm border-0">
              <div class="room-card-image">
                <img :src="getRoomImage(room)" :alt="room.room_type">
                <div class="room-overlay"></div>
                <span class="room-badge" :class="getStatusBadge(room.status).class">
                  <i :class="getStatusBadge(room.status).icon" class="me-1"></i>
                  {{ getStatusBadge(room.status).text }}
                </span>
                <div class="room-size-tag" v-if="room.room_size">
                   <i class="bi bi-arrows-fullscreen small me-1"></i>
                   {{ room.room_size }} m²
                </div>
              </div>

              <div class="room-card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="room-type-label text-gold">{{ room.room_type }}</span>
                    <div class="room-bed-type" v-if="room.bed_type">
                        <i class="bi bi-door-open-fill small me-1 text-muted"></i>
                        <span class="small text-muted fw-semibold">{{ room.bed_type }}</span>
                    </div>
                </div>
                
                <h4 class="room-card-title serif-font">Room #{{ room.room_number }}</h4>
                
                <div class="room-card-price mb-3">
                  <span class="currency">₱</span>
                  <span class="price">{{ formatPrice(room.price_per_night) }}</span>
                  <span class="period">/ night</span>
                </div>
                
                <!-- Inclusion Icons (Real Amenities if available, otherwise defaults) -->
                <div class="room-features mb-3">
                  <div class="feature-pill" title="Max Occupancy">
                    <i class="bi bi-people-fill"></i> 
                    <span>{{ room.max_occupancy }} Guests</span>
                  </div>
                  <div class="feature-pill" v-for="amenity in (room.amenities ? room.amenities.slice(0, 2) : [])" :key="amenity.id" :title="amenity.name">
                    <i :class="['bi', amenity.icon]"></i>
                    <span>{{ amenity.name }}</span>
                  </div>
                </div>
                
                <p class="room-card-description mb-3 text-truncate-2">{{ room.description }}</p>
                
                <router-link 
                  :to="{ name: 'room-detail', params: { id: room.id }}" 
                  class="btn btn-gold-outline w-100 py-2 rounded-pill fw-bold text-uppercase d-flex align-items-center justify-content-center gap-2 transition-all small"
                  :class="{ 'op-50 pointer-none': room.status !== 'available' || systemSettings.maintenance_mode }"
                >
                  {{ (room.status === 'available' && !systemSettings.maintenance_mode) ? 'View Details' : 'Not Available' }}
                  <i class="bi bi-chevron-right small"></i>
                </router-link>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
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
  display: inline-block;
}

.label-text {
  font-size: 0.75rem;
  font-weight: 800;
  letter-spacing: 5px;
  text-transform: uppercase;
}

/* Page Header */
.page-header {
  background: url('https://images.unsplash.com/photo-1578683010236-d716f9a3f461?auto=format&fit=crop&w=1920&q=80') center/cover no-repeat;
  position: relative;
  padding: 12rem 0 10rem; /* Increased for navbar offset + breathing room */
  background-attachment: fixed;
}

.contrast-overlay::before {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(rgba(26, 38, 52, 0.8), rgba(26, 38, 52, 0.95));
}

/* Rooms Content */
.py-7 {
  padding-top: 8rem;
  padding-bottom: 8rem;
}

.mb-7 {
  margin-bottom: 8rem;
}

/* Filter Tabs */
.filter-tabs {
  display: flex;
  justify-content: center;
  gap: 1.5rem;
  flex-wrap: wrap;
}

.filter-tab {
  padding: 1.1rem 2.75rem;
  border: 1px solid rgba(0,0,0,0.05);
  background: white;
  color: #64748b;
  font-weight: 700;
  border-radius: 50px;
  cursor: pointer;
  transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
  box-shadow: 0 4px 20px rgba(0,0,0,0.03);
  text-transform: uppercase;
  font-size: 0.75rem;
  letter-spacing: 2px;
}

.filter-tab:hover {
  transform: translateY(-5px);
  box-shadow: 0 15px 30px rgba(188, 145, 81, 0.1);
  color: var(--primary-gold);
}

.filter-tab.active {
  background: white;
  border-color: var(--primary-gold);
  color: var(--primary-gold);
  box-shadow: 0 15px 35px rgba(188, 145, 81, 0.2);
}

/* Room Card Premium */
.room-card {
  background: white;
  border-radius: 32px;
  overflow: hidden;
  transition: all 0.6s cubic-bezier(0.16, 1, 0.3, 1);
  display: flex;
  flex-direction: column;
  height: 100%;
}

.shadow-gold-sm {
    box-shadow: 0 15px 40px rgba(188, 145, 81, 0.06);
}

.room-card:hover {
    transform: translateY(-15px);
    box-shadow: 0 25px 55px rgba(188, 145, 81, 0.15);
}

.room-card-image {
  position: relative;
  height: 220px;
  overflow: hidden;
}

.room-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(to bottom, transparent 0%, rgba(15, 23, 42, 0.4) 100%);
}

.room-card-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 1.5s cubic-bezier(0.16, 1, 0.3, 1);
}

.room-card:hover .room-card-image img {
  transform: scale(1.1);
}

.room-badge {
  position: absolute;
  top: 1.75rem;
  left: 1.75rem;
  padding: 0.5rem 1.5rem;
  border-radius: 50px;
  font-size: 0.7rem;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 1px;
  backdrop-filter: blur(10px);
  z-index: 2;
}

.room-size-tag {
    position: absolute;
    bottom: 1.75rem;
    right: 1.75rem;
    background: rgba(255,255,255,0.95);
    padding: 0.5rem 1.25rem;
    border-radius: 50px;
    font-size: 0.75rem;
    font-weight: 800;
    color: var(--secondary-dark);
    backdrop-filter: blur(5px);
    z-index: 2;
}

.badge-available { background: rgba(255, 255, 255, 0.9); color: #10B981; }
.badge-unavailable { background: rgba(239, 68, 68, 0.9); color: white; }
.badge-maintenance { background: rgba(245, 158, 11, 0.9); color: white; }

.room-card-body {
  padding: 1.75rem;
  display: flex;
  flex-direction: column;
  flex: 1;
}

.room-type-label {
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 3px;
    font-weight: 800;
    margin-bottom: 0.5rem;
}

.room-card-title {
  font-weight: 700;
  font-size: 1.5rem;
  margin-bottom: 0.5rem;
  color: var(--secondary-dark);
}

.room-card-price {
    display: flex;
    align-items: baseline;
    gap: 0.5rem;
}

.room-card-price .currency {
    font-size: 1.1rem;
    font-weight: 800;
    color: var(--text-dark);
}

.room-card-price .price {
  font-family: var(--font-serif);
  font-weight: 700;
  font-size: 1.8rem;
  color: var(--primary-gold);
}

.room-card-price .period {
  font-size: 0.9rem;
  color: #94a3b8;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 1px;
}

.room-features {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
}

.feature-pill {
    background: #f8fafc;
    border: 1px solid #f1f5f9;
    padding: 0.4rem 0.9rem;
    border-radius: 14px;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.75rem;
    color: #64748b;
    font-weight: 700;
}

.feature-pill i {
    color: var(--primary-gold);
    font-size: 1.1rem;
}

.room-card-description {
  font-size: 0.85rem;
  color: #64748b;
  line-height: 1.6;
  margin-bottom: 1.5rem !important;
}

.text-truncate-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.btn-gold-outline {
    background: transparent;
    border: 2px solid var(--primary-gold);
    color: var(--primary-gold);
    transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}

.btn-gold-outline:hover {
    background: var(--primary-gold);
    color: white;
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(188, 145, 81, 0.3);
}

.glass-panel {
  background: rgba(255, 255, 255, 0.9);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.5);
}

.op-50 { opacity: 0.5; }
.pointer-none { pointer-events: none; }

@media (max-width: 991.98px) {
  .page-header { padding: 10rem 0 6rem; }
  .room-card-body { padding: 2rem; }
  .room-card-title { font-size: 1.75rem; }
}

@media (max-width: 767.98px) {
  .py-7 { padding-top: 5rem; padding-bottom: 5rem; }
  .mb-7 { margin-bottom: 4rem; }
  .filter-tab { padding: 0.8rem 1.5rem; font-size: 0.7rem; }
}
</style>
