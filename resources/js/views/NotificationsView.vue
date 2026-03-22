<template>
  <div class="notifications-page py-5">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-md-8">
          <div class="d-flex align-items-center justify-content-between mb-4">
            <h2 class="serif-font mb-0">Notifications</h2>
            <button v-if="unreadCount > 0" @click="markAllAsRead" class="btn btn-outline-gold rounded-pill px-4 small fw-bold text-uppercase">
              Mark All as Read
            </button>
          </div>

          <div v-if="loading" class="text-center py-5">
            <div class="spinner-border text-gold" style="width: 3rem; height: 3rem;" role="status"></div>
            <p class="mt-3 text-muted fw-bold">Gathering updates...</p>
          </div>

          <div v-else-if="notifications.length === 0" class="empty-state shadow-premium rounded-5 text-center p-5 bg-white border-0">
            <div class="py-5">
              <div class="empty-icon-wrapper mx-auto mb-4 rounded-circle d-flex align-items-center justify-content-center bg-light">
                <i class="bi bi-bell-slash display-4 text-muted opacity-50"></i>
              </div>
              <h4 class="fw-bold text-secondary-dark mb-2">No updates yet</h4>
              <p class="text-muted mb-4 mx-auto" style="max-width: 400px;">We'll notify you about your bookings, messages, and important system updates right here.</p>
              <router-link to="/admin" class="btn btn-gold rounded-pill px-5 py-3 fw-bold text-uppercase tracking-wider shadow-gold">
                Back to Dashboard
              </router-link>
            </div>
          </div>

          <div v-else class="d-flex flex-column gap-4">
            <div v-for="notif in notifications" :key="notif.id" 
                 class="notif-card p-0 border-0 shadow-premium rounded-4 overflow-hidden transition-all hover-lift position-relative"
                 :class="{ 'unread': !notif.read_at, 'cursor-pointer': notif.data.action_url }"
                 @click="notif.data.action_url ? handleAction(notif) : null">
              <div class="card-body p-4">
                <div class="d-flex gap-4">
                  <div class="notif-icon-box rounded-circle d-flex align-items-center justify-content-center shadow-sm flex-shrink-0"
                       :class="notif.read_at ? 'bg-light text-muted' : 'bg-gold-glass text-gold'"
                       style="width: 64px; height: 64px;">
                    <i :class="(notif.data.icon || 'bi-info-circle')" style="font-size: 1.5rem;"></i>
                  </div>
                  <div class="flex-grow-1 overflow-hidden">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                      <div>
                        <h5 class="fw-bold mb-1 text-secondary-dark">{{ notif.data.title }}</h5>
                        <span class="badge rounded-pill bg-light text-muted fw-bold x-small border fw-bold text-uppercase px-2" v-if="notif.data.category">
                          {{ notif.data.category }}
                        </span>
                      </div>
                      <span class="text-muted opacity-75 fw-medium" style="font-size: 0.75rem;">{{ formatFullDate(notif.created_at) }}</span>
                    </div>
                    <p class="text-muted mb-3 lh-base">{{ notif.data.message }}</p>
                    <div class="d-flex align-items-center gap-3">
                      <button v-if="notif.data.action_url" @click.stop="handleAction(notif)" class="btn btn-gold-subtle btn-sm rounded-pill px-4 fw-bold text-uppercase x-small">
                        {{ notif.data.action_text || 'View Details' }}
                      </button>
                      <button v-if="!notif.read_at" @click.stop="markAsRead(notif)" class="btn btn-link text-gold btn-sm p-0 fw-bold text-decoration-none x-small text-uppercase tracking-wider">
                         Mark as read
                      </button>
                      <button @click.stop="deleteNotification(notif.id)" class="btn btn-action-circle text-danger ms-auto" title="Delete notification">
                        <i class="bi bi-trash"></i>
                      </button>
                    </div>
                  </div>
                </div>
              </div>
              <div v-if="!notif.read_at" class="unread-indicator-bar"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';
import { useAuth } from '../store/auth';

const router = useRouter();
const { state } = useAuth();
const notifications = ref([]);
const loading = ref(true);

const unreadCount = computed(() => notifications.value.filter(n => !n.read_at).length);

const fetchNotifications = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/notifications');
        notifications.value = response.data.notifications;
    } catch (err) {
        console.error('Failed to fetch notifications', err);
    } finally {
        loading.value = false;
    }
};

const markAsRead = async (notif) => {
    try {
        await axios.put(`/api/notifications/${notif.id}/read`);
        notif.read_at = new Date().toISOString();
    } catch (err) {
        console.error(err);
    }
};

const markAllAsRead = async () => {
    try {
        await axios.put('/api/notifications/read-all');
        notifications.value.forEach(n => n.read_at = new Date().toISOString());
    } catch (err) {
        console.error(err);
    }
};

const deleteNotification = async (id) => {
    try {
        await axios.delete(`/api/notifications/${id}`);
        notifications.value = notifications.value.filter(n => n.id !== id);
    } catch (err) {
        console.error(err);
    }
};

const handleAction = (notif) => {
    if (!notif.read_at) markAsRead(notif);
    if (notif.data.action_url) {
        router.push(notif.data.action_url);
    }
};

const formatFullDate = (dateStr) => {
    return new Date(dateStr).toLocaleString([], { 
        month: 'short', 
        day: 'numeric', 
        year: 'numeric',
        hour: 'numeric',
        minute: '2-digit'
    });
};

onMounted(fetchNotifications);
</script>

<style scoped>
.notifications-page {
    padding-top: 50px !important;
    min-height: 100vh;
    background-color: #F8F7F4;
}

.shadow-premium {
    box-shadow: 0 10px 40px -10px rgba(0,0,0,0.1), 
                0 20px 25px -5px rgba(0,0,0,0.05),
                0 0 1px 0 rgba(0,0,0,0.1);
}

.notif-card {
    background: white;
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

.notif-card.unread {
    background: rgba(188, 145, 81, 0.02);
}

.unread-indicator-bar {
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 6px;
    background: linear-gradient(to bottom, var(--primary-gold), #9A7640);
}

.notif-icon-box {
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

.bg-gold-glass {
    background: rgba(188, 145, 81, 0.1);
    color: var(--primary-gold);
}

.btn-gold-subtle {
    background: rgba(188, 145, 81, 0.1);
    color: var(--primary-gold);
    border: none;
    transition: all 0.2s;
}

.btn-gold-subtle:hover {
    background: var(--primary-gold);
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(188, 145, 81, 0.2);
}

.btn-action-circle {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: transparent;
    border: none;
    transition: all 0.2s;
    color: #64748b;
}

.btn-action-circle:hover {
    background: #fee2e2;
    color: #ef4444;
}

.empty-icon-wrapper {
    width: 120px;
    height: 120px;
}

.hover-lift:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 40px -15px rgba(0,0,0,0.12) !important;
}

.x-small {
    font-size: 0.75rem;
}

.cursor-pointer {
    cursor: pointer;
}
</style>
