<template>
  <div class="settings-view">
    <div class="row g-4 animate-fade-up">
      <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 p-4">
          <h6 class="serif-font fw-bold mb-4 text-secondary-dark">Store Profile</h6>
          <form @submit.prevent="saveSettings">
            <div class="mb-3">
              <label class="form-label small fw-bold text-muted text-uppercase">Apartelle Name</label>
              <input type="text" class="form-control bg-light border-0 px-3 py-2" v-model="settings.store_name">
            </div>
            <div class="mb-3">
              <label class="form-label small fw-bold text-muted text-uppercase">Contact Email</label>
              <input type="email" class="form-control bg-light border-0 px-3 py-2" v-model="settings.email">
            </div>
            <div class="mb-3">
              <label class="form-label small fw-bold text-muted text-uppercase">Contact Phone</label>
              <input type="text" class="form-control bg-light border-0 px-3 py-2" v-model="settings.phone">
            </div>
            <button class="btn btn-gold w-100 py-2 mt-2 fw-bold text-uppercase shadow-sm" :disabled="saving">
              <span v-if="saving" class="spinner-border spinner-border-sm me-2"></span>
              {{ saving ? 'Saving...' : 'Save Changes' }}
            </button>
          </form>
        </div>
      </div>
      <div class="col-md-8">
        <div class="card border-0 shadow-sm rounded-4 p-4">
          <h6 class="serif-font fw-bold mb-4 text-secondary-dark">Availability Settings</h6>
          <div class="d-flex align-items-center justify-content-between py-3 border-bottom">
            <div>
              <p class="mb-0 fw-bold small text-secondary-dark">Online Booking</p>
              <small class="text-muted">Allow guests to book rooms online via the website</small>
            </div>
            <div class="form-check form-switch">
              <input class="form-check-input custom-switch" type="checkbox" v-model="settings.online_booking">
            </div>
          </div>
          <div class="d-flex align-items-center justify-content-between py-3 border-bottom">
            <div>
              <p class="mb-0 fw-bold small text-secondary-dark">Maintenance Mode</p>
              <small class="text-muted">Temporarily disable website booking for all rooms</small>
            </div>
            <div class="form-check form-switch">
              <input class="form-check-input custom-switch" type="checkbox" v-model="settings.maintenance_mode">
            </div>
          </div>
          <div class="d-flex align-items-center justify-content-between py-3">
            <div>
              <p class="mb-0 fw-bold small text-secondary-dark">Email Notifications</p>
              <small class="text-muted">Receive email updates for every new reservation</small>
            </div>
            <div class="form-check form-switch">
              <input class="form-check-input custom-switch" type="checkbox" v-model="settings.email_notifications">
            </div>
          </div>
          <div class="mt-4 pt-3 border-top">
            <button @click="saveSettings" class="btn btn-gold px-4 py-2 fw-bold text-uppercase shadow-sm" :disabled="saving">
              <span v-if="saving" class="spinner-border spinner-border-sm me-2"></span>
              {{ saving ? 'Updating...' : 'Save Availability' }}
            </button>
          </div>
        </div>

        <div class="mt-4 p-3 bg-white-50 border rounded-4 d-flex align-items-center gap-3 text-muted">
          <i class="bi bi-info-circle fs-5"></i>
          <p class="mb-0 small">Last updated: {{ lastUpdated }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import axios from 'axios';
import { notify } from '../../utils/sweetalert';

const saving = ref(false);
const lastUpdated = ref('---');

const settings = reactive({
  store_name: "",
  email: "",
  phone: "",
  online_booking: true,
  maintenance_mode: false,
  email_notifications: true
});

const fetchSettings = async () => {
  try {
    const response = await axios.get('/api/admin/settings');
    Object.assign(settings, response.data);
    lastUpdated.value = new Date().toLocaleString();
  } catch (err) {
    console.error('Failed to fetch settings', err);
  }
};

const saveSettings = async () => {
  saving.value = true;
  try {
    await axios.post('/api/admin/settings', settings);
    lastUpdated.value = new Date().toLocaleString();
    notify.success('Settings Saved', 'System configuration has been updated.');
  } catch (err) {
    notify.error('Update Failed', 'Failed to save settings.');
  } finally {
    saving.value = false;
  }
};

onMounted(fetchSettings);
</script>

<style scoped>
.btn-gold {
  background-color: var(--primary-gold) !important;
  color: white !important;
  border: none;
  transition: all 0.3s;
}
.btn-gold:hover:not(:disabled) {
  background-color: #A67C41 !important;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(188, 145, 81, 0.3);
}
.custom-switch {
  cursor: pointer;
  width: 2.5rem;
  height: 1.25rem;
}
.custom-switch:checked {
  background-color: var(--primary-gold);
  border-color: var(--primary-gold);
}
</style>
