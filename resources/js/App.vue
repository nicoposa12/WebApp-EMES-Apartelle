<template>
  <div class="app-container">
    <!-- Header Wrapper (Sticky/Fixed) -->
    <header 
      v-if="!isAdminRoute && !isAuthRoute" 
      class="fixed-top transition-all"
    >
      <!-- Suspension Banner -->
      <div v-if="state.isAuthenticated && state.user?.is_suspended" 
           class="suspension-banner bg-danger p-3 text-white animate-fade-in shadow-lg"
      >
        <div class="container d-flex align-items-center justify-content-center gap-3">
          <i class="bi bi-exclamation-octagon fs-4"></i>
          <div class="text-start">
            <p class="mb-0 fw-bold small text-uppercase tracking-wider">Account Suspended</p>
            <p class="mb-0 x-small opacity-90">Reason: {{ state.user.suspension_reason || 'Violation of house rules.' }} | Please contact support for assistance.</p>
          </div>
        </div>
      </div>

      <!-- Navigation Bar -->
      <nav 
        class="navbar navbar-expand-lg transition-all"
        :class="{ 'navbar-scrolled shadow-sm': isNavbarScrolled, 'navbar-transparent': !isNavbarScrolled }"
      >
        <div class="container">
          <!-- Logo -->
          <router-link class="navbar-brand d-flex align-items-center gap-3" to="/">
            <div class="brand-logo-wrapper p-1 rounded-3 bg-white shadow-sm">
               <img src="/images/emes-logo.png" alt="EME's Apartelle" class="brand-logo" width="42" height="42">
            </div>
            <div class="d-flex flex-column lh-1">
              <span class="brand-name" :class="isNavbarScrolled ? 'text-dark' : 'text-white text-shadow-sm'">EME's</span>
              <span class="brand-tagline" :class="isNavbarScrolled ? 'text-muted' : 'text-white-50'">Apartelle</span>
            </div>
          </router-link>
          
          <button 
            class="navbar-toggler border-0 shadow-none" 
            type="button" 
            data-bs-toggle="collapse" 
            data-bs-target="#navbarNav"
            :class="isNavbarScrolled ? 'text-dark' : 'text-white navbar-dark'"
          >
            <span class="navbar-toggler-icon"></span>
          </button>
          
          <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mx-auto mb-2 mb-lg-0 gap-lg-4">
              <li class="nav-item" v-for="link in navLinks" :key="link.path">
                <router-link 
                  class="nav-link nav-link-custom text-uppercase tracking-wider small fw-bold" 
                  :class="[isNavbarScrolled ? 'text-dark' : 'text-white opacity-90', { 'active': $route.path === link.path }]" 
                  :to="link.path"
                >
                  {{ link.name }}
                </router-link>
              </li>
            </ul>
            
            <div class="d-flex align-items-center gap-3">
              <template v-if="!state.isAuthenticated">
                <router-link to="/login" class="nav-admin-link fw-bold small text-uppercase" :class="isNavbarScrolled ? 'text-dark' : 'text-white'">
                  Sign In
                </router-link>
                <router-link to="/register" class="btn btn-outline-gold d-flex align-items-center gap-2 rounded-pill px-4 small fw-bold text-uppercase">
                  Join Now
                </router-link>
              </template>
              <template v-else>
                <!-- Notifications Dropdown -->
                <div class="dropdown me-2">
                  <button class="btn border-0 position-relative p-2 shadow-none" type="button" data-bs-toggle="dropdown" aria-expanded="false" :class="isNavbarScrolled ? 'text-dark' : 'text-white'">
                    <i class="bi bi-bell fs-5"></i>
                    <span v-if="unreadCount > 0" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger shadow-sm" style="font-size: 0.6rem; padding: 0.25em 0.5em;">
                      {{ unreadCount > 9 ? '9+' : unreadCount }}
                    </span>
                  </button>
                  <div class="dropdown-menu dropdown-menu-end shadow-xl border-0 mt-3 p-0 rounded-4 animate-fade-up overflow-hidden" style="width: 320px; max-height: 450px;">
                    <div class="p-3 border-bottom d-flex justify-content-between align-items-center bg-light-subtle">
                      <h6 class="mb-0 fw-bold serif-font">Notifications</h6>
                      <button v-if="unreadCount > 0" @click.stop="markAllNotificationsAsRead" class="btn btn-link p-0 text-gold x-small fw-bold text-decoration-none">
                        Mark all as read
                      </button>
                    </div>
                    <div class="notification-list custom-scrollbar" style="max-height: 350px; overflow-y: auto;">
                      <div v-if="notifications.length === 0" class="p-5 text-center text-muted">
                        <i class="bi bi-bell-slash fs-2 d-block mb-2 opacity-25"></i>
                        <p class="small mb-0">No notifications yet</p>
                      </div>
                      <template v-else>
                        <div v-for="notif in notifications" :key="notif.id" 
                             class="notification-item p-3 border-bottom transition-all cursor-pointer" 
                             :class="{ 'unread bg-gold-light-5': !notif.read_at, 'read': !!notif.read_at }"
                             @click="handleNotificationClick(notif)">
                          <div class="d-flex gap-3">
                            <div class="notif-icon-circle rounded-circle flex-shrink-0 d-flex align-items-center justify-content-center" 
                                 :class="[getNotificationColorClass(notif), { 'opacity-75': notif.read_at }]"
                                 style="width: 36px; height: 36px;">
                              <i :class="notif.data.icon || 'bi bi-info-circle'"></i>
                            </div>
                            <div class="flex-grow-1 overflow-hidden">
                              <p class="mb-1 small fw-bold text-dark text-truncate">{{ notif.data.title }}</p>
                              <p class="mb-1 x-small text-muted line-clamp-2">{{ notif.data.message }}</p>
                              <span class="x-small text-muted opacity-75">{{ formatNotifTime(notif.created_at) }}</span>
                            </div>
                          </div>
                        </div>
                      </template>
                    </div>
                    <div class="p-2 border-top bg-light-subtle text-center">
                      <router-link to="/notifications" class="x-small text-gold fw-bold text-decoration-none">View All Notifications</router-link>
                    </div>
                  </div>
                </div>

                <div class="dropdown">
                  <button class="nav-admin-link btn border-0 d-flex align-items-center gap-2 dropdown-toggle shadow-none" type="button" data-bs-toggle="dropdown" :class="isNavbarScrolled ? 'text-dark' : 'text-white'">
                    <div class="avatar-circle bg-gold text-white d-flex align-items-center justify-content-center rounded-circle overflow-hidden position-relative" style="width: 32px; height: 32px; font-size: 0.8rem;">
                      <img 
                        v-if="state.user?.profile_photo_url" 
                        :src="state.user.profile_photo_url" 
                        class="w-100 h-100 object-fit-cover position-absolute top-0 start-0"
                        alt="Avatar"
                      >
                      <span v-else>{{ state.user?.first_name?.charAt(0) || 'U' }}</span>
                    </div>
                    <span class="d-none d-sm-inline small fw-bold text-uppercase">{{ state.user?.first_name || 'My Account' }}</span>
                  </button>
                  <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-3 p-2 rounded-4 animate-fade-up">
                    <li><h6 class="dropdown-header small text-uppercase tracking-wider text-muted py-2">Account</h6></li>
                    <li><router-link class="dropdown-item rounded-3 py-2 small fw-bold" to="/profile"><i class="bi bi-person me-2 text-gold"></i> My Profile</router-link></li>
                    <li><router-link class="dropdown-item rounded-3 py-2 small fw-bold" to="/my-bookings"><i class="bi bi-calendar-event me-2 text-gold"></i> My Bookings</router-link></li>
                    <li v-if="state.user?.role === 'admin' || state.user?.role === 'staff'"><hr class="dropdown-divider bg-light my-2"></li>
                    <li v-if="state.user?.role === 'admin' || state.user?.role === 'staff'"><router-link class="dropdown-item rounded-3 py-2 small fw-bold text-primary" to="/admin"><i class="bi bi-speedometer2 me-2"></i> {{ state.user?.role === 'admin' ? 'Admin Panel' : 'Staff Panel' }}</router-link></li>
                    <li><hr class="dropdown-divider bg-light my-2"></li>
                    <li><button @click="handleLogout" class="dropdown-item rounded-3 py-2 small fw-bold text-danger"><i class="bi bi-box-arrow-right me-2"></i> Sign Out</button></li>
                  </ul>
                </div>
              </template>
              <router-link to="/book-now" class="btn btn-gold d-flex align-items-center gap-2 ms-lg-2 rounded-pill px-4 small fw-bold text-uppercase shadow-gold">
                <i class="bi bi-calendar-check text-white"></i> <span class="d-none d-md-inline">Reserve</span>
              </router-link>
            </div>
          </div>
        </div>
      </nav>
    </header>

    <!-- Main Content -->
    <main :style="{ marginTop: (state.isAuthenticated && state.user?.is_suspended && !isAuthRoute && !isAdminRoute) ? '68px' : '0px' }">
      <router-view v-slot="{ Component }">
        <transition name="fade" mode="out-in">
          <component :is="Component" />
        </transition>
      </router-view>
    </main>

    <ChatWidget v-if="state.user?.role !== 'admin'" />

    <!-- Footer -->
    <footer v-if="!isAdminRoute && !isAuthRoute" class="footer-section position-relative overflow-hidden">
      <!-- Decorative Overlay -->
      <div class="footer-overlay"></div>
      
      <div class="container position-relative z-1">
        <div class="row g-5 py-5">
          <!-- Brand Column -->
          <div class="col-lg-4 col-md-6">
            <div class="d-flex align-items-center gap-3 mb-4">
              <div class="bg-white p-2 rounded-3 d-inline-block">
                <img src="/images/emes-logo.png" alt="EME's Apartelle" width="40" height="40" class="d-block">
              </div>
              <div class="d-flex flex-column lh-1">
                <span class="brand-name text-white fs-4">EME's</span>
                <span class="brand-tagline text-white-50 small tracking-wider">Apartelle</span>
              </div>
            </div>
            <p class="text-white-50 mb-4 pe-lg-4 lead fs-6">
              Experience the warmth of Filipino hospitality combined with modern comfort. Your sanctuary in the heart of the city.
            </p>
            <div class="d-flex gap-3">
              <a href="#" class="social-icon"><i class="bi bi-facebook"></i></a>
              <a href="#" class="social-icon"><i class="bi bi-instagram"></i></a>
              <a href="#" class="social-icon"><i class="bi bi-twitter-x"></i></a>
            </div>
          </div>
          
          <!-- Quick Links -->
          <div class="col-lg-2 col-md-6">
            <h6 class="footer-heading text-gold mb-4">Explore</h6>
            <ul class="footer-links">
              <li v-for="link in navLinks" :key="link.path">
                <router-link :to="link.path" class="text-decoration-none small text-white-50 hover-text-white transition-all d-inline-block py-1">
                  {{ link.name }}
                </router-link>
              </li>
            </ul>
          </div>
          
          <!-- Contact Us -->
          <div class="col-lg-3 col-md-6">
            <h6 class="footer-heading text-gold mb-4">Contact</h6>
            <ul class="footer-contact list-unstyled">
              <li class="mb-3 d-flex gap-3">
                <i class="bi bi-geo-alt text-gold mt-1"></i>
                <span class="text-white-50 small">Poblacion, Purok 1, General Luna Surigao del Norte, Philippines</span>
              </li>
              <li class="mb-3 d-flex gap-3">
                <i class="bi bi-telephone text-gold mt-1"></i>
                <span class="text-white-50 small">+63 950 560 2175</span>
              </li>
              <li class="mb-3 d-flex gap-3">
                <i class="bi bi-envelope text-gold mt-1"></i>
                <span class="text-white-50 small">info@emesapartelle.com</span>
              </li>
            </ul>
          </div>
          
          <!-- Front Desk Hours -->
          <div class="col-lg-3 col-md-6">
            <h6 class="footer-heading text-gold mb-4">Reception</h6>
            <div class="bg-white-10 p-4 rounded-4 border border-white-10">
              <div class="d-flex align-items-center gap-3 mb-3">
                <i class="bi bi-clock-history fs-3 text-gold"></i>
                <div>
                   <span class="d-block text-white fw-bold small text-uppercase">Open 24/7</span>
                   <span class="text-white-50 small">Always here for you</span>
                </div>
              </div>
              <div class="divider bg-white-20 my-3" style="height: 1px;"></div>
              <div class="d-flex justify-content-between text-white-50 small mb-1">
                <span>Check-in</span>
                <span class="text-white fw-bold">2:00 PM</span>
              </div>
              <div class="d-flex justify-content-between text-white-50 small">
                <span>Check-out</span>
                <span class="text-white fw-bold">12:00 PM</span>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Footer Bottom -->
        <div class="footer-bottom border-top border-white-10 py-4 mt-2">
          <div class="row align-items-center">
            <div class="col-md-6 text-center text-md-start">
              <p class="mb-0 text-white-50 small">&copy; 2026 EME's Apartelle. All rights reserved.</p>
            </div>
            <div class="col-md-6 text-center text-md-end mt-3 mt-md-0">
              <a href="#" class="text-white-50 small text-decoration-none me-3 hover-text-white">Privacy Policy</a>
              <a href="#" class="text-white-50 small text-decoration-none hover-text-white">Terms of Service</a>
            </div>
          </div>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuth } from './store/auth';
import Swal from 'sweetalert2';
import ChatWidget from './components/ChatWidget.vue';

const route = useRoute();
const router = useRouter();
const { state, logout, fetchUser } = useAuth();
const isAdminRoute = computed(() => route.path.startsWith('/admin'));
const isAuthRoute = computed(() => ['/login', '/register'].includes(route.path));
const isScrolled = ref(false);
const isNavbarScrolled = computed(() => {
  return isScrolled.value || (state.isAuthenticated && state.user?.is_suspended);
});
const notifications = ref([]);
const unreadCount = ref(0);
const lastSeenNotificationId = ref(null);
const lastNotifHash = ref(null);
let notifInterval = null;

const triggerNewDisputeUpdateAlert = (notif) => {
    Swal.fire({
        title: 'Dispute Update!',
        text: notif.data.message,
        icon: 'info',
        iconColor: '#BC9151',
        showCancelButton: true,
        confirmButtonColor: '#BC9151',
        cancelButtonColor: '#718096',
        confirmButtonText: 'View Resolution',
        cancelButtonText: 'Dismiss',
        background: '#fcfaf7',
    }).then((result) => {
        if (result.isConfirmed) {
            router.push('/my-bookings');
        }
    });
};

const fetchNotifications = async () => {
    if (!state.isAuthenticated) return;
    // Skip polling when tab is hidden (save bandwidth on slow connections)
    if (document.visibilityState === 'hidden') return;

    try {
        const response = await axios.get('/api/notifications');
        const newNotifications = response.data.notifications;

        // Detect new dispute notification for guest
        if (newNotifications.length > 0) {
            const latest = newNotifications[0];
            if (latest.id !== lastSeenNotificationId.value) {
                // If it's a dispute type or title contains "Dispute"
                if (latest.data.type === 'dispute' || latest.data.title.includes('Dispute')) {
                    // Only alert if we've already initialized (not on first load)
                    if (lastSeenNotificationId.value !== null) {
                        triggerNewDisputeUpdateAlert(latest);
                    }
                }
                lastSeenNotificationId.value = latest.id;
            }
        } else {
            lastSeenNotificationId.value = 'none';
        }

        notifications.value = newNotifications;
        unreadCount.value = response.data.unread_count;

        // Only refresh user status if notification data actually changed
        // This avoids redundant /api/user calls on every poll cycle
        const newHash = response.data.unread_count + '_' + (newNotifications[0]?.id || 'none');
        if (newHash !== lastNotifHash.value) {
            lastNotifHash.value = newHash;
            try {
                await fetchUser();
            } catch (e) {
                // Silently ignore — fetchUser() already handles 401 internally.
                // Don't let a transient error crash the notification poll.
            }
        }
    } catch (err) {
        console.error('Failed to fetch notifications/user status', err);
    }
};

const markAllNotificationsAsRead = async () => {
    try {
        await axios.put('/api/notifications/read-all');
        notifications.value.forEach(n => n.read_at = new Date().toISOString());
        unreadCount.value = 0;
    } catch (err) {
        console.error(err);
    }
};

const handleNotificationClick = async (notif) => {
    if (!notif.read_at) {
        try {
            await axios.put(`/api/notifications/${notif.id}/read`);
            notif.read_at = new Date().toISOString();
            unreadCount.value = Math.max(0, unreadCount.value - 1);
        } catch (err) {
            console.error(err);
        }
    }
    
    if (notif.data.action_url) {
        fetchUser().catch(() => {}); // Refresh state just in case it was a restoration notification
        router.push(notif.data.action_url);
    }
};

const formatNotifTime = (dateStr) => {
    const date = new Date(dateStr);
    const now = new Date();
    const diff = (now - date) / 1000;
    
    if (diff < 60) return 'Just now';
    if (diff < 3600) return Math.floor(diff / 60) + 'm ago';
    if (diff < 86400) return Math.floor(diff / 3600) + 'h ago';
    return date.toLocaleDateString([], { month: 'short', day: 'numeric' });
};

const getNotificationColorClass = (notif) => {
    const type = notif.data?.type;
    switch (type) {
        case 'booking_confirmed':
        case 'account_restored':
            return 'notif-theme-confirmed';
        case 'booking_cancelled':
        case 'cancellation_request':
        case 'cancellation_rejected':
            return 'notif-theme-cancelled';
        case 'dispute':
        case 'dispute_update':
            return 'notif-theme-dispute';
        case 'new_message':
            return 'notif-theme-message';
        case 'account_suspended':
            return 'notif-theme-account';
        case 'new_booking':
        case 'booking_created':
        case 'stay_extended':
        default:
            return 'notif-theme-booking';
    }
};

const navLinks = [
  { name: 'Home', path: '/' },
  { name: 'Our Rooms', path: '/rooms' },
  { name: 'Book Now', path: '/book-now' },
  { name: 'About', path: '/about' },
  { name: 'Contact', path: '/contact' },
];

const handleScroll = () => {
  isScrolled.value = window.scrollY > 50;
};

// Smart polling: starts/stops based on tab visibility
const startPolling = () => {
  if (notifInterval) return;
  fetchNotifications(); // Immediate fetch on resume
  notifInterval = setInterval(fetchNotifications, 10000); // 10s interval
};

const stopPolling = () => {
  if (notifInterval) {
    clearInterval(notifInterval);
    notifInterval = null;
  }
};

const handleVisibilityChange = () => {
  if (document.visibilityState === 'visible') {
    startPolling();
  } else {
    stopPolling();
  }
};

onMounted(() => {
  window.addEventListener('scroll', handleScroll);
  if (state.isAuthenticated) {
    startPolling();
    document.addEventListener('visibilitychange', handleVisibilityChange);
  }
});

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll);
  document.removeEventListener('visibilitychange', handleVisibilityChange);
  stopPolling();
});

const handleLogout = async () => {
    const result = await Swal.fire({
        title: 'Sign Out?',
        text: "Are you sure you want to end your session?",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#dc3545', // Danger red
        cancelButtonColor: '#718096', // Secondary muted
        confirmButtonText: 'Yes, Sign Out',
        cancelButtonText: 'Cancel',
        reverseButtons: true
    });

    if (result.isConfirmed) {
        try {
            await logout();
            Swal.fire({
                icon: 'success',
                title: 'Signed Out',
                text: 'You have been successfully logged out.',
                timer: 1500,
                showConfirmButton: false
            });
            router.push('/login');
        } catch (error) {
            console.error('Logout failed:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to sign out. Please try again.',
                confirmButtonColor: '#dc3545'
            });
        }
    }
};
</script>

<style scoped>
.app-container {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

main {
  flex: 1;
}

/* Navbar Transitions */
.navbar {
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  padding: 1.5rem 0;
}

.navbar-scrolled {
  background-color: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(12px);
  padding: 1rem 0;
}

.navbar-transparent {
  background-color: transparent;
  background: linear-gradient(180deg, rgba(0,0,0,0.6) 0%, transparent 100%);
}

.brand-name {
  font-family: var(--font-serif);
  font-weight: 700;
  font-size: 1.25rem;
  letter-spacing: -0.5px;
}

.brand-tagline {
  font-size: 0.65rem;
  text-transform: uppercase;
  letter-spacing: 2px;
  font-weight: 600;
}

.text-shadow-sm {
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}

/* Nav Links */
.nav-link-custom {
  position: relative;
  letter-spacing: 1px;
  transition: all 0.3s ease;
}

.nav-link-custom::after {
  content: '';
  position: absolute;
  bottom: -4px;
  left: 50%;
  width: 0;
  height: 2px;
  background-color: var(--primary-gold);
  transition: all 0.3s ease;
  transform: translateX(-50%);
}

.nav-link-custom:hover::after,
.nav-link-custom.active::after {
  width: 100%;
}

.nav-link-custom:hover {
  color: var(--primary-gold) !important;
  opacity: 1 !important;
}

.nav-admin-link {
  text-decoration: none;
  transition: opacity 0.3s;
}

.nav-admin-link:hover {
  opacity: 0.8;
}

/* Footer Styles */
.footer-section {
  background-color: #0f172a;
  color: white;
  margin-top: auto;
}

.bg-white-10 {
  background-color: rgba(255, 255, 255, 0.05);
}

.bg-white-20 {
  background-color: rgba(255, 255, 255, 0.1);
}

.border-white-10 {
  border-color: rgba(255, 255, 255, 0.1) !important;
}

.hover-text-white:hover {
  color: white !important;
}

.social-icon {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background-color: rgba(255, 255, 255, 0.05);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  text-decoration: none;
  transition: all 0.3s ease;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.social-icon:hover {
  background-color: var(--primary-gold);
  border-color: var(--primary-gold);
  transform: translateY(-3px);
}

.footer-heading {
  font-size: 0.8rem;
  letter-spacing: 2px;
  text-transform: uppercase;
  font-weight: 700;
}

/* Transitions */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.btn-outline-gold {
  border: 1px solid var(--primary-gold);
  color: var(--primary-gold);
  transition: all 0.3s;
}

.btn-outline-gold:hover {
  background: var(--primary-gold);
  color: white;
}

.bg-gold-light-5 {
  background-color: rgba(188, 145, 81, 0.05);
}

.notification-item.read {
  background-color: #f8fafc;
  opacity: 0.75;
}

.notification-item:hover {
  background-color: rgba(0, 0, 0, 0.02);
}

.notification-item.read:hover {
  background-color: #f1f5f9;
  opacity: 0.9;
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Suspension Banner Styles */
.suspension-banner {
  position: relative;
  z-index: 1060;
  margin-top: 0;
}

.x-small {
  font-size: 0.75rem;
}

/* Mobile responsive tweak */
@media (max-width: 991.98px) {
  .navbar-collapse {
    background: white;
    padding: 1.5rem;
    border-radius: 1rem;
    margin-top: 1rem;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
  }
  
  .nav-link-custom {
    color: var(--text-dark) !important;
    padding: 0.75rem 0;
  }
  
  /* Force dark text on mobile menu items even if unscrolled, because the menu bg is white */
  .navbar-collapse .nav-link-custom,
  .navbar-collapse .nav-admin-link {
    color: var(--text-dark) !important;
  }
}
</style>
