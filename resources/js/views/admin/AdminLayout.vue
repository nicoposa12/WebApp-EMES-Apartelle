<template>
  <div class="admin-layout d-flex">
    <!-- Sidebar -->
    <div 
      class="sidebar border-0" 
      :class="{ 'collapsed': collapsed }"
      @mouseenter="collapsed = false"
      @mouseleave="collapsed = true"
    >
      <div class="sidebar-header d-flex align-items-center gap-3 p-3" :class="collapsed ? 'justify-content-center' : 'ps-4'">
        <div class="logo-box rounded-3 overflow-hidden d-flex align-items-center justify-content-center shadow-sm bg-white" style="width: 44px; height: 44px; min-width: 44px;">
          <img src="/images/eme-logo.jpg" alt="EME's" class="w-100 h-100 object-fit-cover">
        </div>
        <div v-if="!collapsed" class="logo-text animate-fade-in">
          <h6 class="mb-0 fw-bold serif-font text-white" style="letter-spacing: 0.5px; white-space: nowrap;">EME's Apartelle</h6>
          <small class="text-white-50 text-uppercase letter-spacing-wide" style="font-size: 0.65rem; white-space: nowrap;">{{ state.user?.role === 'admin' ? 'Administrator' : 'Staff' }}</small>
        </div>
      </div>

      <nav class="sidebar-nav px-3 mt-2 flex-grow-1 overflow-y-auto custom-sidebar-scroll">
        <div class="nav-section-label mb-2 px-3 text-uppercase fw-bold" v-if="!collapsed">
          Menu
        </div>
        <ul class="nav flex-column gap-1 list-unstyled mb-0">
            <li v-for="item in menuItems" :key="item.path" class="nav-item">
              <router-link :to="item.path" 
                           class="nav-link d-flex align-items-center gap-3 rounded-3 py-2.5 position-relative" 
                           :class="collapsed ? 'justify-content-center px-0' : 'px-3'"
                           exact-active-class="active">
                <div class="nav-icon-wrapper d-flex align-items-center justify-content-center" style="width: 24px; min-width: 24px;">
                  <i :class="['bi', formatIconClass(item.icon), 'fs-5']"></i>
                </div>
                <span v-if="!collapsed" class="fw-medium animate-fade-in">{{ item.name }}</span>
                
                <span v-if="item.name === 'Messages' && unreadMessagesCount > 0" 
                      class="position-absolute badge rounded-pill bg-danger shadow-sm" 
                      :class="collapsed ? 'top-0 end-0 mt-1 me-1' : 'end-0 me-3'"
                      style="font-size: 0.6rem; padding: 0.3em 0.5em;">
                   {{ unreadMessagesCount }}
                </span>
              </router-link>
            </li>
        </ul>

        <div class="nav-section-label mt-4 mb-2 px-3 text-uppercase fw-bold" v-if="!collapsed">
          System
        </div>
        <li class="nav-item list-unstyled">
          <router-link to="/" class="nav-link d-flex align-items-center gap-3 rounded-3 py-2.5" :class="collapsed ? 'justify-content-center px-0' : 'px-3'">
            <div class="nav-icon-wrapper d-flex align-items-center justify-content-center" style="width: 24px; min-width: 24px;">
              <i class="bi bi-box-arrow-left fs-5"></i>
            </div>
            <span v-if="!collapsed" class="fw-medium animate-fade-in">Back to Website</span>
          </router-link>
        </li>
      </nav>

      <div class="collapse-btn border-0 bg-transparent p-4 mt-auto w-100 d-flex align-items-center cursor-default shadow-none" :class="collapsed ? 'justify-content-center px-0' : 'px-4'">
        <i class="bi" :class="collapsed ? 'bi-chevron-right' : 'bi-chevron-left'"></i>
        <span v-if="!collapsed" class="ms-3 small fw-bold animate-fade-in">Auto-Hide Menu</span>
      </div>
    </div>

    <!-- Main Content -->
    <div class="main-content-wrapper flex-grow-1 d-flex flex-column vh-100 overflow-hidden bg-cream-light">
      <!-- Top Header -->
      <header class="main-header bg-white border-bottom px-4 py-2 d-flex align-items-center justify-content-between sticky-top shadow-sm" style="z-index: 1010; height: 65px; min-height: 65px;">
        <div class="header-left">
          <h4 class="mb-0 serif-font text-secondary-dark">{{ currentRouteName }}</h4>
          <small class="text-muted">Manage your building</small>
        </div>
        <div class="header-right d-flex align-items-center gap-4">
          <!-- Notification Bell -->
          <div class="dropdown">
            <button class="btn btn-icon position-relative shadow-none" type="button" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="bi bi-bell fs-5 text-secondary-dark"></i>
              <span v-if="unreadNotificationCount > 0" 
                    class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-2 border-white shadow-sm animate-pulse-slow" 
                    style="font-size: 0.6rem; padding: 0.35em 0.6em;">
                {{ unreadNotificationCount }}
              </span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-premium border-0 mt-3 p-0 rounded-4 animate-scale-up overflow-hidden" style="width: 360px;">
              <li class="p-3 border-bottom bg-white d-flex align-items-center justify-content-between">
                <div>
                  <h6 class="mb-0 fw-bold text-secondary-dark">Notifications</h6>
                  <span v-if="unreadNotificationCount > 0" class="x-small text-gold fw-bold">{{ unreadNotificationCount }} new updates</span>
                  <span v-else class="x-small text-muted">All caught up</span>
                </div>
                <div class="d-flex gap-2">
                  <button v-if="unreadNotificationCount > 0" @click="markNotificationsAsRead" class="btn-action-icon rounded-circle" title="Mark all as read">
                    <i class="bi bi-check-all"></i>
                  </button>
                  <button v-if="notifications.length > 0" @click="clearAllNotifications" class="btn-action-icon rounded-circle text-danger" title="Clear all">
                    <i class="bi bi-trash"></i>
                  </button>
                </div>
              </li>
              
              <div class="custom-scrollbar" style="max-height: 380px; overflow-y: auto;">
                <li v-for="notification in notifications" :key="notification.id" class="notif-wrapper">
                  <div @click="handleNotificationClick(notification)" 
                       class="notif-item p-3 d-flex align-items-start gap-3 cursor-pointer transition-all position-relative" 
                       :class="{ 'unread': !notification.read_at, 'read': !!notification.read_at }">
                    <div class="notif-icon-box rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm" 
                         :class="[getNotificationColorClass(notification), { 'opacity-75': notification.read_at }]">
                      <i :class="notification.data.icon || 'bi-bell'" style="font-size: 1.1rem;"></i>
                    </div>
                    <div class="overflow-hidden flex-grow-1">
                      <div class="d-flex justify-content-between align-items-center mb-1">
                        <p class="mb-0 text-dark small fw-bold text-truncate">{{ notification.data.title }}</p>
                        <small class="notif-time text-muted">{{ formatTimeAgo(notification.created_at) }}</small>
                      </div>
                      <p class="mb-0 text-muted x-small lh-base text-truncate-2">{{ notification.data.message }}</p>
                    </div>
                    <div v-if="!notification.read_at" class="unread-dot"></div>
                  </div>
                </li>
                
                <li v-if="notifications.length === 0" class="p-5 text-center text-muted animate-fade-in">
                  <div class="empty-notif-icon mb-3 mx-auto rounded-circle bg-light d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                    <i class="bi bi-bell-slash fs-3 opacity-25"></i>
                  </div>
                  <h6 class="fw-bold text-secondary-dark mb-1">No Notifications</h6>
                  <p class="x-small text-muted mb-0">We'll let you know when something important happens.</p>
                </li>
              </div>
              
              <li class="p-2 text-center bg-light-soft border-top">
                <router-link to="/notifications" class="notif-footer-link d-block py-2">
                  View Notification History <i class="bi bi-arrow-right ms-1"></i>
                </router-link>
              </li>
            </ul>
          </div>


          <div class="dropdown">
            <button class="btn border-0 d-flex align-items-center gap-3 dropdown-toggle shadow-none" type="button" data-bs-toggle="dropdown">
              <div class="admin-avatar-wrapper rounded-circle overflow-hidden shadow-sm" style="width: 40px; height: 40px;">
                <img v-if="state.user?.profile_photo_url" :src="state.user.profile_photo_url" :alt="state.user?.name" class="w-100 h-100 object-fit-cover">
                <div v-else class="w-100 h-100 bg-gold text-white d-flex align-items-center justify-content-center fw-bold">
                  {{ state.user?.name?.charAt(0) || 'A' }}
                </div>
              </div>
              <div class="admin-info d-none d-md-block text-start">
                <h6 class="mb-0 fw-bold small text-secondary-dark">{{ state.user?.name }}</h6>
                <small class="text-muted" style="font-size: 0.65rem;">{{ state.user?.role === 'admin' ? 'Administrator' : 'Staff' }}</small>
              </div>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-3 p-2 rounded-4 animate-fade-up">
              <li><h6 class="dropdown-header small text-uppercase tracking-wider text-muted py-2">{{ state.user?.role === 'admin' ? 'System Admin' : 'Staff Portal' }}</h6></li>
              <li><router-link class="dropdown-item rounded-3 py-2 small fw-bold" to="/profile"><i class="bi bi-person me-2 text-gold"></i> My Profile</router-link></li>
              <li v-if="state.user?.role === 'admin'"><router-link class="dropdown-item rounded-3 py-2 small fw-bold" to="/admin/settings"><i class="bi bi-gear me-2 text-gold"></i> Settings</router-link></li>
              <li><hr class="dropdown-divider bg-light my-2"></li>
              <li><button @click="handleLogout" class="dropdown-item rounded-3 py-2 small fw-bold text-danger"><i class="bi bi-box-arrow-right me-2"></i> Sign Out</button></li>
            </ul>
          </div>
        </div>
      </header>
      
      <main class="p-4 flex-grow-1 overflow-y-auto d-flex flex-column custom-main-scroll">
        <router-view v-slot="{ Component }">
          <transition name="fade" mode="out-in">
            <component :is="Component" />
          </transition>
        </router-view>
      </main>
    </div>

    <!-- New Reservation Modal Removed -->
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuth } from '../../store/auth';
import axios from 'axios';
import Swal from 'sweetalert2';

const { state, logout } = useAuth();

const collapsed = ref(true);
const route = useRoute();
const router = useRouter();
const unreadMessagesCount = ref(0);
let unreadPollingInterval = null;

const prevChatbotCount = ref(0);
const notifications = ref([]);
const unreadNotificationCount = ref(0);
const lastSeenNotificationId = ref(null);

const triggerNewReservationAlert = (notif) => {
    Swal.fire({
        title: 'New Room Reservation!',
        text: notif.data.message,
        icon: 'success',
        iconColor: '#BC9151',
        showCancelButton: true,
        confirmButtonColor: '#BC9151',
        cancelButtonColor: '#718096',
        confirmButtonText: 'View Reservations',
        cancelButtonText: 'Later',
        background: '#fcfaf7',
    }).then((result) => {
        if (result.isConfirmed) {
            if (notif.data && notif.data.reservation_id) {
                router.push({ path: '/admin/reservations', query: { id: notif.data.reservation_id } });
            } else {
                router.push('/admin/reservations');
            }
        }
    });
};

const triggerNewDisputeAlert = (notif) => {
    Swal.fire({
        title: 'New Dispute Filed!',
        text: notif.data.message,
        icon: 'warning',
        iconColor: '#BC9151',
        showCancelButton: true,
        confirmButtonColor: '#BC9151',
        cancelButtonColor: '#718096',
        confirmButtonText: 'Investigate Now',
        cancelButtonText: 'Later',
        background: '#fcfaf7',
    }).then((result) => {
        if (result.isConfirmed) {
            router.push('/admin/disputes');
        }
    });
};

const fetchNotifications = async () => {
    try {
        const response = await axios.get('/api/notifications');
        const newNotifications = response.data.notifications;
        
        // Detect new reservation or dispute notification
        if (newNotifications.length > 0) {
            const latest = newNotifications[0];
            if (latest.id !== lastSeenNotificationId.value) {
                // If it's a reservation type or title contains "New Reservation"
                if (latest.data.type === 'reservation' || latest.data.title.includes('New Reservation')) {
                    // Only alert if we've already initialized (not on first load)
                    if (lastSeenNotificationId.value !== null) {
                        triggerNewReservationAlert(latest);
                    }
                } else if (latest.data.type === 'dispute' || latest.data.title.includes('Dispute')) {
                    // Only alert if we've already initialized (not on first load)
                    if (lastSeenNotificationId.value !== null) {
                        triggerNewDisputeAlert(latest);
                    }
                }
                lastSeenNotificationId.value = latest.id;
            }
        } else {
             lastSeenNotificationId.value = 'none';
        }
        
        notifications.value = newNotifications;
        unreadNotificationCount.value = response.data.unread_count;
    } catch (err) {
        console.error('Failed to fetch notifications', err);
    }
};

const markNotificationsAsRead = async () => {
    if (unreadNotificationCount.value === 0) return;
    try {
        await axios.put('/api/notifications/read-all');
        unreadNotificationCount.value = 0;
        // Optionally update the list locally instead of re-fetching everything
        notifications.value.forEach(n => n.read_at = n.read_at || new Date().toISOString());
    } catch (err) {
        console.error('Failed to mark notifications as read', err);
    }
};

const handleNotificationClick = async (notif) => {
    // Mark as read if it's unread
    if (!notif.read_at) {
        try {
            await axios.put(`/api/notifications/${notif.id}/read`);
            notif.read_at = new Date().toISOString();
            unreadNotificationCount.value = Math.max(0, unreadNotificationCount.value - 1);
        } catch (err) {
            console.error('Failed to mark notification as read', err);
        }
    }

    // Redirect if there's an action_url
    if (notif.data && notif.data.action_url) {
        if (notif.data.type === 'reservation' && notif.data.reservation_id) {
            router.push({ path: '/admin/reservations', query: { id: notif.data.reservation_id } });
        } else {
            router.push(notif.data.action_url);
        }
    }
};

const clearAllNotifications = async () => {
    try {
        await axios.delete('/api/notifications');
        notifications.value = [];
        unreadNotificationCount.value = 0;
    } catch (err) {
        console.error('Failed to clear notifications', err);
    }
};

const formatTimeAgo = (dateStr) => {
    const date = new Date(dateStr);
    const now = new Date();
    const diffInSeconds = Math.floor((now - date) / 1000);
    
    if (diffInSeconds < 60) return 'Just now';
    if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)}m ago`;
    if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)}h ago`;
    return `${Math.floor(diffInSeconds / 86400)}d ago`;
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
        default:
            return 'notif-theme-booking';
    }
};

const fetchUnreadCount = async () => {
  try {
    const response = await axios.get('/api/messages/unread');
    const newChatbotCount = response.data.chatbot || 0;
    
    // Notify if new chatbot messages arrive and we're not on the messages page
    if (newChatbotCount > prevChatbotCount.value && route.path !== '/admin/messages') {
        Swal.fire({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 4000,
            timerProgressBar: true,
            icon: 'info',
            title: 'Bot Activity',
            text: 'Someone is chatting with the bot.',
            color: '#0f172a',
            iconColor: '#BC9151'
        });
        
        // Also refresh notifications list
        fetchNotifications();
    }
    
    prevChatbotCount.value = newChatbotCount;
    unreadMessagesCount.value = response.data.count || 0;
  } catch (err) {
    console.error('Failed to fetch unread count', err);
  }
};

onMounted(() => {
  fetchUnreadCount();
  fetchNotifications();
  unreadPollingInterval = setInterval(() => {
      fetchUnreadCount();
      fetchNotifications();
  }, 5000);
});

onUnmounted(() => {
  if (unreadPollingInterval) clearInterval(unreadPollingInterval);
});

const menuItems = computed(() => {
  const allItems = [
    { name: 'Dashboard', path: '/admin', icon: 'bi-grid-fill' },
    { name: 'Reservations', path: '/admin/reservations', icon: 'bi-calendar-date' },
    { name: 'Rooms', path: '/admin/rooms', icon: 'bi-door-closed' },
    { name: 'Amenities', path: '/admin/amenities', icon: 'bi-stars' },
    { name: 'Guests', path: '/admin/guests', icon: 'bi-people' },
    { name: 'Messages', path: '/admin/messages', icon: 'bi-chat-dots' },
    { name: 'Chatbot', path: '/admin/chatbot', icon: 'bi-robot' },
    { name: 'Payments', path: '/admin/payments', icon: 'bi-credit-card' },
    { name: 'Disputes', path: '/admin/disputes', icon: 'bi-exclamation-triangle' },
    { name: 'Reports', path: '/admin/reports', icon: 'bi-bar-chart-line-fill' },
  ];
  
  if (state.user?.role === 'staff') {
    return allItems.filter(item => ['Dashboard', 'Reservations', 'Payments', 'Disputes'].includes(item.name));
  }
  return allItems;
});

const formatIconClass = (icon) => {
  if (!icon) return '';
  return icon.startsWith('bi-') ? icon : `bi-${icon}`;
};

const currentRouteName = computed(() => {
  const current = menuItems.value.find(item => item.path === route.path);
  return current ? current.name : 'Dashboard';
});

const handleLogout = async () => {
    const result = await Swal.fire({
        title: 'Sign Out?',
        text: "Are you sure you want to end your admin session?",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#718096',
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
            router.push('/admin/login');
        } catch (error) {
            console.error('Logout failed:', error);
        }
    }
};
</script>

<style scoped>
.admin-layout {
  min-height: 100vh;
  background-color: #F8F7F4;
}

.sidebar {
  width: 280px; /* Slightly wider for better breathing room */
  min-width: 280px;
  flex-shrink: 0;
  height: 100vh;
  position: sticky;
  top: 0;
  background-color: var(--secondary-dark); /* Deep Navy Background */
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  display: flex;
  flex-direction: column;
  z-index: 1020;
  box-shadow: 4px 0 24px rgba(26, 38, 52, 0.15); /* Subtle shadow for depth */
}

.sidebar.collapsed {
  width: 90px;
  min-width: 90px;
}

/* Logo Area */
.logo-box.bg-gold {
  background: var(--primary-gold) !important;
  box-shadow: 0 4px 12px rgba(188, 145, 81, 0.3);
}

/* Navigation Links */
.nav-link {
  color: rgba(255, 255, 255, 0.6); /* Muted white */
  font-weight: 500;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  border: 1px solid transparent;
  margin-bottom: 4px;
}

.nav-link:hover {
  background-color: rgba(255, 255, 255, 0.08);
  color: white;
  transform: translateX(4px); /* Subtle slide effect */
}

.nav-link.active {
  background: linear-gradient(135deg, var(--primary-gold) 0%, #9A7640 100%);
  color: white !important;
  box-shadow: 0 4px 15px rgba(188, 145, 81, 0.25);
  border: none;
}

/* Section Labels */
.nav-section-label {
  color: rgba(255, 255, 255, 0.4);
  letter-spacing: 1.5px;
  font-size: 0.7rem;
}

/* Header & Content */
.main-header {
  background: rgba(255, 255, 255, 0.9);
  backdrop-filter: blur(12px);
}

.bg-cream-light {
  background-color: #F8F7F4;
}

.letter-spacing-wide {
  letter-spacing: 1px;
}

.main-content-wrapper {
  height: 100vh;
  overflow: hidden;
  background-color: #F8F7F4;
}

.custom-sidebar-scroll::-webkit-scrollbar {
  width: 4px;
}
.custom-sidebar-scroll::-webkit-scrollbar-track {
  background: transparent;
}
.custom-sidebar-scroll::-webkit-scrollbar-thumb {
  background-color: rgba(255, 255, 255, 0.1);
  border-radius: 10px;
}
.custom-sidebar-scroll:hover::-webkit-scrollbar-thumb,
.custom-main-scroll:hover::-webkit-scrollbar-thumb {
  background-color: rgba(255, 255, 255, 0.2);
}

.custom-main-scroll::-webkit-scrollbar {
  width: 6px;
}
.custom-main-scroll::-webkit-scrollbar-track {
  background: transparent;
}
.custom-main-scroll::-webkit-scrollbar-thumb {
  background-color: rgba(0, 0, 0, 0.05);
  border-radius: 10px;
}
.custom-main-scroll:hover::-webkit-scrollbar-thumb {
  background-color: rgba(0, 0, 0, 0.1) !important;
}

/* Buttons */
.btn-gold {
  background: linear-gradient(135deg, var(--primary-gold) 0%, #9A7640 100%);
  border: none !important;
  color: white !important;
  transition: all 0.3s ease;
}

.btn-gold:hover {
  background: linear-gradient(135deg, #A67C3B 0%, #856130 100%) !important;
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(188, 145, 81, 0.3);
}

/* Collapse Button */
.collapse-btn {
  color: rgba(255, 255, 255, 0.5);
  border-top: 1px solid rgba(255, 255, 255, 0.1) !important;
}

.collapse-btn:hover {
  color: white;
  background-color: rgba(255, 255, 255, 0.05);
}

/* Modal */
.custom-modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(26, 38, 52, 0.6); /* Navy tinted overlay */
  backdrop-filter: blur(8px);
  z-index: 9999;
}

.custom-modal {
  background: white;
  z-index: 10000;
  border: 1px solid rgba(255, 255, 255, 0.4);
}

.animate-fade-in {
  animation: fadeIn 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes fadeIn {
  from { opacity: 0; transform: scale(0.95) translateY(-10px); }
  to { opacity: 1; transform: scale(1) translateY(0); }
}

.shadow-premium {
    box-shadow: 0 10px 40px -10px rgba(0,0,0,0.1), 
                0 20px 25px -5px rgba(0,0,0,0.05),
                0 0 1px 0 rgba(0,0,0,0.1);
}

.btn-action-icon {
    width: 32px;
    height: 32px;
    border: none;
    background: transparent;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #64748b;
    transition: all 0.2s;
}

.btn-action-icon:hover {
    background-color: #f1f5f9;
    color: var(--secondary-dark);
}

.notif-item {
    border-bottom: 1px solid #f8fafc;
    background: white;
}

.notif-item.unread {
    background: rgba(188, 145, 81, 0.05);
}

.notif-item.read {
    background: #f8fafc;
    opacity: 0.75;
}

.notif-item:hover {
    background: #f8fafc;
}

.notif-item.read:hover {
    background: #f1f5f9;
    opacity: 0.9;
}

.notif-icon-box {
    width: 44px;
    height: 44px;
}

.bg-gold-glass {
    background: rgba(188, 145, 81, 0.1);
    color: var(--primary-gold);
}

.notif-time {
    font-size: 0.65rem;
    font-weight: 600;
}

.unread-dot {
    width: 6px;
    height: 6px;
    background-color: var(--primary-gold);
    border-radius: 50%;
    margin-top: 6px;
    box-shadow: 0 0 10px rgba(188, 145, 81, 0.5);
}

.notif-footer-link {
    font-size: 0.8rem;
    font-weight: 700;
    color: var(--primary-gold);
    text-decoration: none;
    transition: all 0.2s;
}

.notif-footer-link:hover {
    color: #9A7640;
    transform: translateX(3px);
}

.animate-scale-up {
    animation: scaleUpOpen 0.25s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

@keyframes scaleUpOpen {
    from { opacity: 0; transform: scale(0.9) translateY(-10px); }
    to { opacity: 1; transform: scale(1) translateY(0); }
}

.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
