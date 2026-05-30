<template>
  <div class="dashboard-view">
    <!-- Welcome Banner -->
    <div class="welcome-banner bg-white rounded-4 shadow-sm p-4 mb-4 d-flex align-items-center justify-content-between position-relative overflow-hidden">
      <div class="position-relative z-1">
        <h2 class="serif-font fw-bold text-secondary-dark mb-1">Welcome back, {{ state.user?.role === 'admin' ? 'Administrator' : 'Staff' }}</h2>
        <p class="text-muted mb-0 small">Here's what's happening at EME's Apartelle today.</p>
        <div class="d-flex align-items-center gap-3 mt-3">
          <div class="d-flex align-items-center gap-2 text-gold fw-bold small text-uppercase letter-spacing-wide">
            <i class="bi bi-calendar-event"></i>
            <span>{{ currentDate }}</span>
          </div>
          <div class="vr text-muted opacity-25"></div>
          <div class="d-flex align-items-center gap-2 text-dark fw-bold small">
            <i class="bi bi-clock-fill text-gold"></i>
            <span class="font-monospace fw-bold">{{ currentTime }}</span>
          </div>
          <div class="vr text-muted opacity-25"></div>
          <div class="d-flex align-items-center gap-2 text-muted small">
            <i class="bi bi-broadcast text-success"></i>
            <span>System: <span class="text-success fw-bold">Live</span></span>
          </div>
        </div>
      </div>
      <!-- Decorative Element -->
      <div class="banner-decoration position-absolute end-0 bottom-0 opacity-10">
        <i class="bi bi-building-fill" style="font-size: 10rem; transform: translate(20%, 20%); color: var(--primary-gold);"></i>
      </div>
    </div>
 
    <!-- Stats Cards (Loading State) -->
    <div v-if="loading" class="row g-4 mb-4 animate-pulse">
      <div v-for="i in 4" :key="i" class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-4 h-100 p-3 stat-card skeleton-card">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4">
              <div class="skeleton-icon-placeholder rounded-3"></div>
              <div class="skeleton-badge-placeholder rounded-pill"></div>
            </div>
            <div class="skeleton-line-placeholder w-50 mb-2"></div>
            <div class="skeleton-line-placeholder w-75 display-placeholder"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Stats Cards (Loaded State) -->
    <div v-else class="row g-4 mb-4 animate-fade-up">
      <div v-for="(stat, index) in summaryStats" :key="index" class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-4 h-100 p-3 stat-card transition-hover">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-4">
              <div class="stat-icon-box shadow-sm" :style="{ backgroundColor: stat.bg || '#F8F7F4', color: stat.color }">
                <i :class="['bi', stat.icon, 'fs-4']"></i>
              </div>
              <span v-if="stat.trend" :class="['badge rounded-pill', stat.trend > 0 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger']">
                <i :class="stat.trend > 0 ? 'bi-arrow-up-short' : 'bi-arrow-down-short'"></i>
                {{ Math.abs(stat.trend) }}%
              </span>
            </div>
            <div>
              <h6 class="text-muted small fw-bold text-uppercase letter-spacing-wide mb-2 opacity-75">{{ stat.label }}</h6>
              <h3 class="mb-0 fw-bold serif-font text-secondary-dark display-6">{{ stat.prefix }}{{ stat.value }}</h3>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content Area -->
    <div class="row g-4">
      <!-- Recent Reservations -->
      <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 h-100 animate-fade-up delay-1">
          <div class="card-header bg-white py-4 px-4 border-bottom border-light d-flex justify-content-between align-items-center">
            <div>
              <h5 class="serif-font fw-bold mb-1 text-secondary-dark">Recent Bookings</h5>
              <p class="mb-0 text-muted small">Most recent guest activities</p>
            </div>
            <router-link to="/admin/reservations" class="btn btn-outline-light text-muted border-light btn-sm fw-bold small px-3 text-uppercase">View All</router-link>
          </div>
          <div class="card-body px-0 pt-0">
            <!-- Loading Table Skeleton -->
            <div v-if="loading" class="table-responsive">
              <table class="table align-middle mb-0">
                <thead class="bg-light">
                  <tr>
                    <th class="ps-4 text-muted small fw-bold text-uppercase py-3">Booking ID</th>
                    <th class="text-muted small fw-bold text-uppercase py-3">Guest</th>
                    <th class="text-muted small fw-bold text-uppercase py-3">Room</th>
                    <th class="text-muted small fw-bold text-uppercase py-3">Check-in</th>
                    <th class="text-muted small fw-bold text-uppercase py-3">Status</th>
                    <th class="pe-4 text-end text-muted small fw-bold text-uppercase py-3">Amount</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="i in 5" :key="i">
                    <td class="ps-4 py-3"><div class="skeleton-line-placeholder w-50"></div></td>
                    <td>
                      <div class="d-flex align-items-center gap-3">
                        <div class="skeleton-avatar-placeholder rounded-circle"></div>
                        <div class="flex-grow-1" style="max-width: 150px;">
                          <div class="skeleton-line-placeholder w-75 mb-1"></div>
                          <div class="skeleton-line-placeholder w-50"></div>
                        </div>
                      </div>
                    </td>
                    <td><div class="skeleton-line-placeholder w-75"></div></td>
                    <td><div class="skeleton-line-placeholder w-60"></div></td>
                    <td><div class="skeleton-badge-placeholder rounded-pill" style="width: 70px;"></div></td>
                    <td class="pe-4 text-end"><div class="skeleton-line-placeholder w-50 ms-auto"></div></td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Loaded Table -->
            <div v-else class="table-responsive">
              <table class="table table-hover align-middle mb-0 custom-table">
                <thead class="bg-light">
                  <tr>
                    <th class="ps-4 text-muted small fw-bold text-uppercase py-3">Booking ID</th>
                    <th class="text-muted small fw-bold text-uppercase py-3">Guest</th>
                    <th class="text-muted small fw-bold text-uppercase py-3">Room</th>
                    <th class="text-muted small fw-bold text-uppercase py-3">Check-in</th>
                    <th class="text-muted small fw-bold text-uppercase py-3">Status</th>
                    <th class="pe-4 text-end text-muted small fw-bold text-uppercase py-3">Amount</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="res in recentReservations" :key="res.id" class="cursor-pointer transition-row">
                    <td class="ps-4 py-3"><span class="fw-bold font-monospace text-primary">#{{ res.id.toString().padStart(4, '0') }}</span></td>
                    <td>
                      <div class="d-flex align-items-center gap-3">
                        <div class="avatar-sm rounded-circle d-flex align-items-center justify-content-center fw-bold text-white shadow-sm overflow-hidden" 
                             style="width: 35px; height: 35px; font-size: 0.85rem; background: linear-gradient(135deg, #BC9151, #9A7640);">
                          <img v-if="res.user?.profile_photo_url" :src="res.user.profile_photo_url" :alt="res.user?.name" class="w-100 h-100 object-fit-cover">
                          <span v-else>{{ (res.user?.name || 'G').charAt(0) }}</span>
                        </div>
                        <div class="d-flex flex-column">
                          <span class="fw-bold text-dark small">{{ res.user?.name || 'Guest User' }}</span>
                          <span class="text-muted" style="font-size: 0.7rem;">Verified Guest</span>
                        </div>
                      </div>
                    </td>
                    <td><span class="small fw-medium">{{ res.room?.room_type }}</span></td>
                    <td>
                      <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-calendar2-event text-gold opacity-50"></i>
                        <span class="small text-muted fw-medium">{{ formatDate(res.check_in) }}</span>
                      </div>
                    </td>
                    <td>
                      <span class="badge rounded-pill fw-bold text-uppercase px-3 py-2" :class="statusBadgeClass(res.status)" style="font-size: 0.65rem; letter-spacing: 0.5px;">
                        {{ res.status }}
                      </span>
                    </td>
                    <td class="pe-4 text-end"><span class="fw-bold text-secondary-dark">₱{{ formatPrice(res.total_amount) }}</span></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- Room Status QuickView -->
      <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 h-100 animate-fade-up delay-2">
          <div class="card-header bg-white py-4 px-4 border-bottom border-light">
            <h5 class="serif-font fw-bold mb-1 text-secondary-dark">Room Availability</h5>
            <p class="mb-0 text-muted small">Real-time status overview</p>
          </div>
          <div class="card-body pt-0">
            <!-- Loading Room List Skeleton -->
            <div v-if="loading" class="list-group list-group-flush">
              <div v-for="i in 5" :key="i" class="list-group-item px-0 py-3 border-light d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3 flex-grow-1">
                  <div class="skeleton-room-img-placeholder rounded-3"></div>
                  <div class="flex-grow-1" style="max-width: 150px;">
                    <div class="skeleton-line-placeholder w-75 mb-2"></div>
                    <div class="skeleton-line-placeholder w-40"></div>
                  </div>
                </div>
                <div class="skeleton-badge-placeholder rounded-pill" style="width: 60px;"></div>
              </div>
            </div>

            <!-- Loaded Room List -->
            <div v-else class="list-group list-group-flush">
              <div v-for="room in roomsList.slice(0, 5)" :key="room.id" class="list-group-item px-0 py-3 border-light d-flex align-items-center justify-content-between transition-hover-item">
                <div class="d-flex align-items-center gap-3">
                  <div class="room-img rounded-3 overflow-hidden shadow-sm position-relative bg-light border d-flex align-items-center justify-content-center" style="width: 56px; height: 56px; min-width: 56px;">
                    <img :src="getRoomImage(room)" class="w-100 h-100 object-fit-cover position-absolute top-0 start-0" style="z-index: 2;" alt="room" @error="handleImageError">
                    <i class="bi bi-door-open text-gold opacity-50 fs-4"></i>
                  </div>
                  <div>
                    <h6 class="mb-1 small fw-bold text-secondary-dark">{{ room.room_type }}</h6>
                    <div class="d-flex align-items-center gap-2">
                      <small class="text-muted font-monospace">#{{ room.room_number }}</small>
                    </div>
                  </div>
                </div>
                <div class="text-end">
                   <span class="badge rounded-pill" :class="getRoomStatusClass(room.status)" style="font-size: 0.6rem;">
                    {{ room.status.toUpperCase() }}
                  </span>
                </div>
              </div>
            </div>
            <router-link to="/admin/rooms" class="btn btn-gold w-100 mt-4 py-2.5 small fw-bold text-uppercase letter-spacing-wide shadow-sm">
              Manage All Rooms
            </router-link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import axios from 'axios';
import { useAuth } from '../../store/auth';

const { state } = useAuth();

const stats = ref({
  today_check_ins: 0,
  today_check_outs: 0,
  available_rooms: 0,
  active_reservations: 0,
  total_revenue: 0,
  total_reservations: 0
});

const recentMessagesCount = ref(0);
const recentReservations = ref([]);
const roomsList = ref([]);
const currentTime = ref('');
const loading = ref(true);

let clockInterval = null;

const updateClock = () => {
    const now = new Date();
    currentTime.value = now.toLocaleTimeString('en-US', { 
        hour: '2-digit', 
        minute: '2-digit', 
        second: '2-digit',
        hour12: true 
    });
};

const currentDate = computed(() => {
  return new Date().toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' });
});

const summaryStats = computed(() => {
  const list = [];
  if (state.user?.role !== 'staff') {
    list.push({ label: 'Sales', value: formatPrice(stats.value.total_revenue), prefix: '₱', icon: 'bi-currency-dollar', color: '#BC9151', bg: '#FFF8E1' });
  }
  list.push(
    { label: 'Current Bookings', value: stats.value.active_reservations, prefix: '', icon: 'bi-calendar-check', color: '#4F46E5', bg: '#EEF2FF' },
    { label: 'Rooms Booked', value: stats.value.total_reservations, prefix: '', icon: 'bi-pie-chart', color: '#10B981', bg: '#D1FAE5' },
    { label: 'Checking In', value: stats.value.today_check_ins, prefix: '', icon: 'bi-people', color: '#F59E0B', bg: '#FEF3C7' }
  );
  return list;
});

const getRoomImage = (room) => {
  if (room && room.image) {
    return room.image;
  }
  
  // High-quality fallback images based on type keywords
  const type = (room?.room_type || '').toLowerCase();
  if (type.includes('suite')) {
    return '/images/unsplash/suite-room.jpg';
  }
  if (type.includes('deluxe')) {
    return '/images/unsplash/deluxe-room.jpg';
  }
  return '/images/unsplash/standard-room.jpg';
};

const handleImageError = (e) => {
  e.target.src = '/images/unsplash/standard-room.jpg';
};

const fetchDashboardData = async () => {
  loading.value = true;
  try {
    const [statsRes, recentRes, roomsRes] = await Promise.all([
      axios.get('/api/admin/stats'),
      axios.get('/api/admin/recent-reservations'),
      axios.get('/api/rooms')
    ]);
    stats.value = {
      ...stats.value,
      ...statsRes.data
    };
    recentReservations.value = recentRes.data;
    roomsList.value = roomsRes.data;
  } catch (err) {
    console.error('Failed to fetch dashboard data', err);
  } finally {
    loading.value = false;
  }
};

const formatPrice = (price) => {
  return parseFloat(price || 0).toLocaleString();
};

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
};

const statusBadgeClass = (status) => {
  switch (status) {
    case 'confirmed': return 'bg-success-subtle text-success border border-success-subtle';
    case 'pending': return 'bg-warning-subtle text-warning border border-warning-subtle';
    case 'cancellation_pending': return 'bg-warning-subtle text-warning border border-warning-subtle';
    case 'cancelled': return 'bg-danger-subtle text-danger border border-danger-subtle';
    default: return 'bg-secondary-subtle text-secondary';
  }
};

const getRoomStatusClass = (status) => {
  switch (status) {
    case 'available': return 'bg-success-subtle text-success';
    case 'occupied': return 'bg-primary-subtle text-primary';
    case 'maintenance': return 'bg-warning-subtle text-warning';
    case 'reserved': return 'bg-info-subtle text-info';
    default: return 'bg-secondary-subtle text-secondary';
  }
};

onMounted(() => {
    fetchDashboardData();
    updateClock();
    clockInterval = setInterval(updateClock, 1000);
});

onUnmounted(() => {
    if (clockInterval) clearInterval(clockInterval);
});
</script>

<style scoped>
.stat-icon-box {
  width: 52px;
  height: 52px;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
}

.transition-hover {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.transition-hover:hover {
  transform: translateY(-5px);
  box-shadow: 0 15px 30px -5px rgba(0, 0, 0, 0.1) !important;
}

.welcome-banner {
  background: radial-gradient(circle at top right, #FDFBF7, #FFFFFF);
  border: 1px solid rgba(0,0,0,0.02);
}

.badge {
  font-weight: 600;
  letter-spacing: 0.3px;
}

.custom-table th {
  font-weight: 600;
  letter-spacing: 0.5px;
  font-size: 0.7rem;
}

.transition-row {
  transition: background-color 0.2s ease;
}

.transition-row:hover {
  background-color: #F8F9FA;
}

.transition-hover-item:hover {
  background-color: #F8F9FA;
  padding-left: 0.5rem;
  padding-right: 0.5rem;
  border-radius: 8px;
}

.transition-hover-item {
  transition: all 0.2s ease;
}

.letter-spacing-wide {
  letter-spacing: 1px;
}

/* Skeleton Loader Styles */
.skeleton-card {
  background-color: #ffffff;
  pointer-events: none;
}

.skeleton-icon-placeholder {
  width: 52px;
  height: 52px;
  background-color: #f3f4f6;
  border-radius: 14px;
}

.skeleton-badge-placeholder {
  width: 60px;
  height: 22px;
  background-color: #f3f4f6;
}

.skeleton-line-placeholder {
  height: 16px;
  background-color: #f3f4f6;
  border-radius: 4px;
}

.display-placeholder {
  height: 40px;
  margin-top: 8px;
}

.skeleton-avatar-placeholder {
  width: 35px;
  height: 35px;
  background-color: #f3f4f6;
}

.skeleton-room-img-placeholder {
  width: 56px;
  height: 56px;
  background-color: #f3f4f6;
}

/* Shimmer animation pulse */
.animate-pulse .skeleton-icon-placeholder,
.animate-pulse .skeleton-badge-placeholder,
.animate-pulse .skeleton-line-placeholder,
.animate-pulse .skeleton-avatar-placeholder,
.animate-pulse .skeleton-room-img-placeholder,
td div.skeleton-line-placeholder,
td div.skeleton-avatar-placeholder,
td div.skeleton-badge-placeholder,
.list-group-item div.skeleton-room-img-placeholder,
.list-group-item div.skeleton-line-placeholder,
.list-group-item div.skeleton-badge-placeholder {
  background: linear-gradient(90deg, #f3f4f6 25%, #e5e7eb 50%, #f3f4f6 75%);
  background-size: 200% 100%;
  animation: skeleton-shimmer 1.5s infinite linear;
}

@keyframes skeleton-shimmer {
  0% {
    background-position: -200% 0;
  }
  100% {
    background-position: 200% 0;
  }
}
</style>
