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

        <div class="card border-0 shadow-sm rounded-4 p-4 mt-4">
          <h6 class="serif-font fw-bold mb-4 text-secondary-dark">Property Location</h6>
          <form @submit.prevent="saveSettings">
            <div class="mb-3">
              <label class="form-label small fw-bold text-muted text-uppercase">Full Address</label>
              <textarea class="form-control bg-light border-0 px-3 py-2" rows="2" v-model="settings.hotel_address"></textarea>
            </div>
            <div class="row g-2">
              <div class="col-6 mb-3">
                <label class="form-label small fw-bold text-muted text-uppercase">Latitude</label>
                <input type="text" class="form-control bg-light border-0 px-3 py-2" v-model="settings.hotel_latitude" placeholder="9.7811">
              </div>
              <div class="col-6 mb-3">
                <label class="form-label small fw-bold text-muted text-uppercase">Longitude</label>
                <input type="text" class="form-control bg-light border-0 px-3 py-2" v-model="settings.hotel_longitude" placeholder="126.1512">
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label small fw-bold text-muted text-uppercase d-flex justify-content-between">
                <span>Preview</span>
                <a :href="'https://www.google.com/maps/search/?api=1&query=' + settings.hotel_latitude + ',' + settings.hotel_longitude" target="_blank" class="text-gold text-decoration-none">
                  <i class="bi bi-box-arrow-up-right me-1"></i>Open Full Map
                </a>
              </label>
              <div class="rounded-3 overflow-hidden border bg-light" style="height: 180px;">
                <iframe 
                  v-if="settings.hotel_latitude && settings.hotel_longitude"
                  width="100%" 
                  height="100%" 
                  frameborder="0" 
                  style="border:0"
                  :src="'https://maps.google.com/maps?q=' + settings.hotel_latitude + ',' + settings.hotel_longitude + '&t=&z=14&ie=UTF8&iwloc=&output=embed'">
                </iframe>
                <div v-else class="h-100 d-flex align-items-center justify-content-center text-muted small">
                  Enter coordinates to preview map
                </div>
              </div>
            </div>
            <div class="mb-1">
              <small class="text-muted italic"><i class="bi bi-info-circle me-1"></i>Tip: You can find these coordinates by right-clicking any spot on Google Maps and selecting the numbers.</small>
            </div>
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

        <!-- Staff Management Card -->
        <div class="card border-0 shadow-sm rounded-4 p-4 mt-4 animate-fade-up">
          <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom border-light">
            <div>
              <h5 class="serif-font fw-bold mb-1 text-secondary-dark">Staff Accounts</h5>
              <p class="mb-0 text-muted small">Authorized personnel permitted to manage reservations and billing</p>
            </div>
            <button v-if="!showStaffForm" @click="showStaffForm = true" class="btn btn-gold btn-sm px-3 py-2 fw-bold text-uppercase letter-spacing-wide shadow-sm">
              <i class="bi bi-person-plus-fill me-1"></i> Add Staff Account
            </button>
          </div>

          <!-- Add Staff Form (Collapsible Card) -->
          <div v-if="showStaffForm" class="p-4 bg-light-soft rounded-4 mb-4 animate-fade-in border border-gold-soft">
            <h6 class="serif-font fw-bold mb-3 text-secondary-dark">Register New Staff Member</h6>
            <form @submit.prevent="saveStaff">
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label small fw-bold text-muted text-uppercase">First Name</label>
                  <input type="text" class="form-control bg-white border-light px-3 py-2 rounded-3" v-model="staffForm.first_name" placeholder="John" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label small fw-bold text-muted text-uppercase">Last Name</label>
                  <input type="text" class="form-control bg-white border-light px-3 py-2 rounded-3" v-model="staffForm.last_name" placeholder="Doe" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label small fw-bold text-muted text-uppercase">Email Address</label>
                  <input type="email" class="form-control bg-white border-light px-3 py-2 rounded-3" v-model="staffForm.email" placeholder="john.doe@emes.com" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label small fw-bold text-muted text-uppercase">Phone Number</label>
                  <input type="text" class="form-control bg-white border-light px-3 py-2 rounded-3" v-model="staffForm.phone" placeholder="09171234567" required>
                </div>
                <div class="col-md-12">
                  <label class="form-label small fw-bold text-muted text-uppercase">Temporary Password</label>
                  <input type="password" class="form-control bg-white border-light px-3 py-2 rounded-3" v-model="staffForm.password" placeholder="•••••••• (min 8 chars)" required minlength="8">
                </div>
              </div>
              <div class="d-flex gap-2 justify-content-end mt-4">
                <button type="button" @click="cancelStaffForm" class="btn btn-light px-3 py-2 small fw-bold text-uppercase rounded-3">
                  Cancel
                </button>
                <button type="submit" class="btn btn-gold px-4 py-2 small fw-bold text-uppercase shadow-sm rounded-3" :disabled="creatingStaff">
                  <span v-if="creatingStaff" class="spinner-border spinner-border-sm me-2"></span>
                  Register Staff
                </button>
              </div>
            </form>
          </div>

          <!-- Staff Table -->
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 custom-table">
              <thead class="bg-light">
                <tr>
                  <th class="ps-3 text-muted small fw-bold text-uppercase py-3">Staff Name</th>
                  <th class="text-muted small fw-bold text-uppercase py-3">Email</th>
                  <th class="text-muted small fw-bold text-uppercase py-3">Phone</th>
                  <th class="text-muted small fw-bold text-uppercase py-3">Joined Date</th>
                  <th class="pe-3 text-end text-muted small fw-bold text-uppercase py-3">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="staff in staffList" :key="staff.id" class="transition-row">
                  <td class="ps-3 py-3">
                    <div class="d-flex align-items-center gap-3">
                      <div class="avatar-sm rounded-circle d-flex align-items-center justify-content-center fw-bold text-white shadow-sm overflow-hidden" 
                           style="width: 32px; height: 32px; font-size: 0.8rem; background: linear-gradient(135deg, #BC9151, #9A7640);">
                        <img v-if="staff.profile_photo_url" :src="staff.profile_photo_url" :alt="staff.name" class="w-100 h-100 object-fit-cover">
                        <span v-else>{{ staff.first_name?.charAt(0) || 'S' }}</span>
                      </div>
                      <span class="fw-bold text-secondary-dark small">{{ staff.name }}</span>
                    </div>
                  </td>
                  <td><span class="small fw-medium">{{ staff.email }}</span></td>
                  <td><span class="small text-muted fw-medium">{{ staff.phone || 'N/A' }}</span></td>
                  <td><span class="small text-muted">{{ formatDate(staff.created_at) }}</span></td>
                  <td class="pe-3 text-end">
                    <button @click="confirmDeleteStaff(staff)" class="btn btn-outline-danger btn-sm rounded-3 px-2 py-1 shadow-none border-0 hover-bg-danger-soft" title="Remove Account">
                      <i class="bi bi-trash-fill fs-6"></i>
                    </button>
                  </td>
                </tr>
                <tr v-if="staffList.length === 0">
                  <td colspan="5" class="text-center py-5 text-muted">
                    <div class="mb-2 fs-3 opacity-25"><i class="bi bi-people"></i></div>
                    <span class="small fw-medium">No registered staff members found.</span>
                  </td>
                </tr>
              </tbody>
            </table>
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
import Swal from 'sweetalert2';
import { notify } from '../../utils/sweetalert';

const saving = ref(false);
const lastUpdated = ref('---');

const settings = reactive({
  store_name: "",
  email: "",
  phone: "",
  hotel_address: "",
  hotel_latitude: "",
  hotel_longitude: "",
  online_booking: true,
  maintenance_mode: false,
  email_notifications: true
});

const staffList = ref([]);
const showStaffForm = ref(false);
const creatingStaff = ref(false);
const staffForm = reactive({
  first_name: "",
  last_name: "",
  email: "",
  phone: "",
  password: ""
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

const fetchStaff = async () => {
  try {
    const response = await axios.get('/api/admin/staff');
    staffList.value = response.data;
  } catch (err) {
    console.error('Failed to fetch staff list', err);
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

const cancelStaffForm = () => {
  showStaffForm.value = false;
  staffForm.first_name = '';
  staffForm.last_name = '';
  staffForm.email = '';
  staffForm.phone = '';
  staffForm.password = '';
};

const saveStaff = async () => {
  creatingStaff.value = true;
  try {
    await axios.post('/api/admin/staff', staffForm);
    notify.success('Staff Registered', `${staffForm.first_name} has been successfully added to EME's staff.`);
    cancelStaffForm();
    await fetchStaff();
  } catch (err) {
    notify.error('Registration Failed', err.response?.data?.message || 'Failed to create staff account.');
  } finally {
    creatingStaff.value = false;
  }
};

const confirmDeleteStaff = async (staff) => {
  const result = await Swal.fire({
    title: 'Delete Staff Account?',
    text: `Are you sure you want to delete ${staff.name}'s account? This action is permanent and will revoke all access.`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#dc3545',
    cancelButtonColor: '#718096',
    confirmButtonText: 'Yes, Delete Account',
    cancelButtonText: 'Cancel',
    reverseButtons: true,
    background: '#fcfaf7'
  });

  if (result.isConfirmed) {
    try {
      await axios.delete(`/api/admin/staff/${staff.id}`);
      notify.success('Account Deleted', 'The staff account has been deleted.');
      await fetchStaff();
    } catch (err) {
      notify.error('Deletion Failed', 'Failed to delete staff account.');
    }
  }
};

const formatDate = (dateStr) => {
  if (!dateStr) return 'N/A';
  return new Date(dateStr).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
};

onMounted(async () => {
  await fetchSettings();
  await fetchStaff();
});
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
.border-gold-soft {
  border: 1px solid rgba(188, 145, 81, 0.25) !important;
}
.hover-bg-danger-soft {
  background: transparent;
  color: #ef4444;
  transition: all 0.2s;
}
.hover-bg-danger-soft:hover {
  background-color: rgba(239, 68, 68, 0.08) !important;
  color: #dc2626 !important;
}
.custom-table th {
  font-size: 0.75rem;
  letter-spacing: 0.5px;
}
.transition-row {
  transition: background-color 0.2s ease;
}
.transition-row:hover {
  background-color: #f8fafc;
}
.bg-light-soft {
  background-color: #FAF9F6 !important;
}
</style>
