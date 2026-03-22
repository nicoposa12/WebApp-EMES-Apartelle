<template>
  <div class="guests-view">
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden animate-fade-up">
      <div class="card-header bg-white py-4 px-4 border-0 d-flex justify-content-between align-items-center">
        <h5 class="serif-font fw-bold mb-0 text-secondary-dark">All Guests</h5>
        <div class="input-group input-group-sm" style="width: 250px;">
          <span class="input-group-text bg-light border-0"><i class="bi bi-search"></i></span>
          <input type="text" class="form-control bg-light border-0" placeholder="Search guests..." v-model="searchQuery">
        </div>
      </div>
      <div class="card-body p-0">
        <div v-if="loading" class="text-center py-5">
          <div class="spinner-border text-gold" role="status"></div>
          <p class="text-muted mt-2 small">Loading guests...</p>
        </div>
        <div v-else class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead>
              <tr>
                <th class="ps-4 border-0 text-muted small fw-bold text-uppercase">Guest</th>
                <th class="border-0 text-muted small fw-bold text-uppercase">Contact Info</th>
                <th class="border-0 text-muted small fw-bold text-uppercase">Joined</th>
                <th class="border-0 text-muted small fw-bold text-uppercase text-center">Bookings</th>
                <th class="border-0 text-muted small fw-bold text-uppercase">Status</th>
                <th class="pe-4 border-0 text-muted small fw-bold text-uppercase text-end">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="guest in paginatedGuests" :key="guest.id">
                <td class="ps-4 py-3">
                  <div class="d-flex align-items-center gap-3">
                    <div class="avatar bg-gold-subtle rounded-circle d-flex align-items-center justify-content-center fw-bold text-gold overflow-hidden" style="width: 40px; height: 40px;">
                      <img v-if="guest.profile_photo_url" :src="guest.profile_photo_url" :alt="guest.name" class="w-100 h-100 object-fit-cover">
                      <span v-else>{{ guest.name?.charAt(0) || guest.first_name?.charAt(0) || '?' }}</span>
                    </div>
                    <span class="fw-bold text-secondary-dark">{{ guest.name || `${guest.first_name} ${guest.last_name}` }}</span>
                  </div>
                </td>
                <td>
                  <small class="d-block fw-medium">{{ guest.email }}</small>
                  <small class="text-muted">{{ guest.phone || 'No phone' }}</small>
                </td>
                <td>
                  <span class="small text-muted">{{ formatDate(guest.created_at) }}</span>
                </td>
                <td class="text-center"><span class="badge bg-light text-dark fw-bold px-3 border shadow-sm">{{ guest.reservations_count || 0 }}</span></td>
                <td>
                  <span v-if="guest.is_suspended" class="badge bg-danger-subtle text-danger fw-bold text-uppercase" style="font-size: 0.65rem;">Suspended</span>
                  <span v-else class="badge bg-success-subtle text-success fw-bold text-uppercase" style="font-size: 0.65rem;">Active Member</span>
                </td>
                <td class="pe-4 text-end">
                    <div class="d-flex justify-content-end gap-2">
                      <button class="btn btn-action-view" title="View Profile" @click="viewGuest(guest)">
                        <i class="bi bi-person-badge"></i>
                      </button>
                      <button class="btn btn-action-history" title="Booking History" @click="fetchGuestHistory(guest)">
                        <i class="bi bi-clock-history"></i>
                      </button>
                      <button v-if="!guest.is_suspended" class="btn btn-action-suspend" title="Suspend Guest" @click="suspendGuestAction(guest)">
                        <i class="bi bi-person-x"></i>
                      </button>
                      <button v-else class="btn btn-action-unsuspend" title="Unsuspend Guest" @click="unsuspendGuestAction(guest)">
                        <i class="bi bi-person-check"></i>
                      </button>
                    </div>
                </td>
              </tr>
              <tr v-if="filteredGuests.length === 0">
                <td colspan="5" class="text-center py-5 text-muted">No guests found.</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <AdminPagination 
          :current-page="currentPage" 
          :total-items="filteredGuests.length" 
          :page-size="pageSize"
          @change="page => currentPage = page"
          @page-size-change="size => { pageSize = size; currentPage = 1; }"
        />
      </div>
    </div>

    <!-- Guest Details Modal -->
    <div v-if="showModal" class="modal-overlay d-flex align-items-center justify-content-center p-3">
      <div class="modal-card bg-white rounded-4 shadow-xl w-100 max-w-lg overflow-hidden animate-fade-up">
        <div class="modal-header border-bottom p-4 d-flex justify-content-between align-items-center bg-light">
          <h5 class="serif-font fw-bold mb-0">Guest Details</h5>
          <button @click="showModal = false" class="btn-close shadow-none"></button>
        </div>
        <div class="modal-body p-4">
          <div class="text-center mb-4">
            <div class="avatar bg-gold text-white rounded-circle d-flex align-items-center justify-content-center fw-bold mx-auto mb-3 shadow overflow-hidden" style="width: 80px; height: 80px; font-size: 2rem;">
              <img v-if="selectedGuest?.profile_photo_url" :src="selectedGuest.profile_photo_url" :alt="selectedGuest?.name" class="w-100 h-100 object-fit-cover">
              <span v-else>{{ selectedGuest?.name?.charAt(0) || selectedGuest?.first_name?.charAt(0) || '?' }}</span>
            </div>
            <h4 class="serif-font fw-bold text-secondary-dark mb-1">{{ selectedGuest?.name || `${selectedGuest?.first_name} ${selectedGuest?.last_name}` }}</h4>
            <span v-if="selectedGuest?.is_suspended" class="badge bg-danger-subtle text-danger fw-bold text-uppercase">Suspended</span>
            <span v-else class="badge bg-success-subtle text-success fw-bold text-uppercase">Active Member</span>
          </div>

          <div class="row g-4 pt-2">
            <div class="col-6">
              <label class="small text-muted text-uppercase fw-bold d-block mb-1">Email Address</label>
              <div class="fw-bold text-secondary-dark">{{ selectedGuest.email }}</div>
            </div>
            <div class="col-6">
              <label class="small text-muted text-uppercase fw-bold d-block mb-1">Phone Number</label>
              <div class="fw-bold text-secondary-dark">{{ selectedGuest?.phone || 'Not provided' }}</div>
            </div>
            <div class="col-6">
              <label class="small text-muted text-uppercase fw-bold d-block mb-1">Total Stay</label>
              <div class="fw-bold text-secondary-dark">{{ selectedGuest?.reservations_count || 0 }} Times</div>
            </div>
            <div class="col-6">
              <label class="small text-muted text-uppercase fw-bold d-block mb-1">Joined Date</label>
              <div class="fw-bold text-secondary-dark">{{ formatDate(selectedGuest.created_at) }}</div>
            </div>
            <div class="col-6">
              <label class="small text-muted text-uppercase fw-bold d-block mb-1">Guest ID</label>
              <div class="fw-bold text-gold">#GST-{{ selectedGuest.id.toString().padStart(4, '0') }}</div>
            </div>
          </div>
          
          <div class="mt-4 p-3 rounded-3" :class="selectedGuest?.is_suspended ? 'bg-danger-subtle border border-danger' : 'bg-light'">
            <div class="d-flex align-items-center gap-2" :class="selectedGuest?.is_suspended ? 'text-danger' : 'text-muted small'">
              <i :class="selectedGuest?.is_suspended ? 'bi bi-shield-exclamation' : 'bi bi-info-circle'"></i>
              <span v-if="selectedGuest?.is_suspended"><strong>SUSPENDED:</strong> {{ selectedGuest?.suspension_reason || 'Violation of house rules.' }}</span>
              <span v-else>This guest is a regular visitor at EME's Apartelle.</span>
            </div>
          </div>
        </div>
        <div class="modal-footer border-top p-4 d-flex justify-content-end bg-light">
          <button type="button" @click="showModal = false" class="btn btn-gold px-5 py-2 rounded-pill shadow-gold fw-bold text-uppercase">Close</button>
        </div>
      </div>
    </div>
    <!-- Booking History Modal -->
    <div v-if="showHistoryModal" class="modal-overlay d-flex align-items-center justify-content-center p-3" style="z-index: 1060;">
      <div class="modal-card bg-white rounded-4 shadow-xl w-100 max-w-xl overflow-hidden animate-fade-up">
        <div class="modal-header border-bottom p-4 d-flex justify-content-between align-items-center bg-light">
          <div>
            <h5 class="serif-font fw-bold mb-0">Booking History</h5>
            <small class="text-muted">History for {{ selectedGuest?.name }}</small>
          </div>
          <button @click="showHistoryModal = false" class="btn-close shadow-none"></button>
        </div>
        <div class="modal-body p-4" style="max-height: 450px; overflow-y: auto;">
          <div v-if="loadingHistory" class="text-center py-5">
            <div class="spinner-border text-gold spinner-border-sm me-2"></div>
            <span class="text-muted small">Fetching records...</span>
          </div>
          <div v-else-if="guestHistory.length === 0" class="text-center py-5">
            <i class="bi bi-calendar-x fs-1 text-muted opacity-25 d-block mb-3"></i>
            <p class="text-muted mb-0">No booking records found for this guest.</p>
          </div>
          <div v-else class="history-timeline">
            <div v-for="booking in guestHistory" :key="booking.id" class="history-card border rounded-4 p-3 mb-3 hover-lift bg-white shadow-sm transition-all">
              <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                  <span class="badge bg-gold-subtle text-gold fw-bold text-uppercase mb-2" style="font-size: 0.6rem;">#RES-{{ booking.id.toString().padStart(3, '0') }}</span>
                  <h6 class="fw-bold text-secondary-dark mb-1">{{ booking.room?.room_type }} #{{ booking.room?.room_number }}</h6>
                </div>
                <span class="badge rounded-pill fw-bold text-uppercase" :class="statusBadgeClass(booking.status)" style="font-size: 0.6rem;">
                  {{ booking.status }}
                </span>
              </div>
              <div class="d-flex gap-3 text-muted small">
                <span><i class="bi bi-calendar3 me-1 text-gold"></i>{{ formatDate(booking.check_in) }} - {{ formatDate(booking.check_out) }}</span>
                <span class="fw-bold text-secondary-dark ms-auto">₱{{ formatPrice(booking.total_amount) }}</span>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer border-top p-4 d-flex justify-content-end bg-light">
          <button type="button" @click="showHistoryModal = false" class="btn btn-gold px-5 py-2 rounded-pill shadow-gold fw-bold text-uppercase">Back to list</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import AdminPagination from '../../components/AdminPagination.vue';
import axios from 'axios';
import Swal from 'sweetalert2';

const searchQuery = ref('');
const currentPage = ref(1);
const pageSize = ref(5);
const showModal = ref(false);
const showHistoryModal = ref(false);
const loadingHistory = ref(false);
const loading = ref(true);
const guestHistory = ref([]);
const selectedGuest = ref(null);
const guests = ref([]);

const fetchGuests = async () => {
  try {
    loading.value = true;
    const response = await axios.get('/api/admin/guests');
    guests.value = response.data;
  } catch (error) {
    console.error('Failed to fetch guests', error);
  } finally {
    loading.value = false;
  }
};

onMounted(fetchGuests);

const filteredGuests = computed(() => {
  if (!searchQuery.value) return guests.value;
  const q = searchQuery.value.toLowerCase();
  return guests.value.filter(g => 
    (g.name && g.name.toLowerCase().includes(q)) || 
    (g.email && g.email.toLowerCase().includes(q)) ||
    (g.phone && g.phone.includes(q))
  );
});

const paginatedGuests = computed(() => {
  const start = (currentPage.value - 1) * pageSize.value;
  const end = start + pageSize.value;
  return filteredGuests.value.slice(start, end);
});

watch(searchQuery, () => {
  currentPage.value = 1;
});

const viewGuest = (guest) => {
  selectedGuest.value = guest;
  showModal.value = true;
};

const fetchGuestHistory = async (guest) => {
  selectedGuest.value = guest;
  showHistoryModal.value = true;
  loadingHistory.value = true;
  guestHistory.value = [];
  
  try {
    const response = await axios.get('/api/admin/guest-history', {
      params: { email: guest.email }
    });
    guestHistory.value = response.data;
  } catch (error) {
    console.error('Failed to fetch history', error);
  } finally {
    loadingHistory.value = false;
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
    case 'confirmed': return 'text-success bg-success-subtle';
    case 'pending': return 'text-warning bg-warning-subtle';
    case 'cancelled': return 'text-danger bg-danger-subtle';
    case 'completed': return 'text-info bg-info-subtle';
    default: return 'text-secondary bg-secondary-subtle';
  }
};

const suspendGuestAction = (guest) => {
  Swal.fire({
    title: '✅ Suspend the Guest',
    html: `
      <div class="text-start small">
        <p class="mb-2 fw-bold text-premium-dark">Use suspension when the guest:</p>
        <ul class="text-muted ps-3 mb-0">
          <li class="mb-1">Violates house rules (noise, smoking, damage, abuse).</li>
          <li class="mb-1">Cancels repeatedly to abuse the system.</li>
          <li class="mb-1">Uses fake info.</li>
          <li class="mb-1">Doesn’t pay or attempts fraud.</li>
          <li>Harasses staff or other guests.</li>
        </ul>
      </div>
    `,
    icon: 'warning',
    input: 'select',
    inputOptions: {
      'Violation of House Rules': 'Violation of House Rules',
      'System Abuse (Cancellations)': 'System Abuse (Cancellations)',
      'Fake Information': 'Fake Information',
      'Fraud/Non-payment': 'Fraud/Non-payment',
      'Harassment': 'Harassment',
      'Other': 'Other'
    },
    inputPlaceholder: 'Select a reason for suspension',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#718096',
    confirmButtonText: 'Suspend Now',
    inputValidator: (value) => {
      if (!value) {
        return 'You need to select a reason!'
      }
    }
  }).then(async (result) => {
    if (result.isConfirmed) {
      try {
        await axios.post(`/api/admin/guests/${guest.id}/suspend`, {
          reason: result.value
        });
        
        Swal.fire({
          icon: 'success',
          title: 'Guest Suspended',
          text: `Account for ${guest.name || guest.first_name} has been suspended.`,
          timer: 2000,
          showConfirmButton: false
        });
        
        fetchGuests();
        if (selectedGuest.value?.id === guest.id) {
           selectedGuest.value.is_suspended = true;
           selectedGuest.value.suspension_reason = result.value;
        }
      } catch (err) {
        console.error('Failed to suspend guest', err);
        Swal.fire('Error', 'Failed to suspend guest. Please try again.', 'error');
      }
    }
  });
};

const unsuspendGuestAction = async (guest) => {
  const result = await Swal.fire({
    title: 'Unsuspend Account?',
    text: `Restore account access for ${guest.name || guest.first_name}? Once resolved, the guest will be able to book and message normally.`,
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#BC9151',
    cancelButtonColor: '#718096',
    confirmButtonText: 'Yes, Unsuspend Now'
  });

  if (result.isConfirmed) {
    try {
      await axios.post(`/api/admin/guests/${guest.id}/unsuspend`);
      
      Swal.fire({
        icon: 'success',
        title: 'Account Restored',
        text: 'The guest account has been unsuspended.',
        timer: 1500,
        showConfirmButton: false
      });
      
      fetchGuests();
      if (selectedGuest.value?.id === guest.id) {
         selectedGuest.value.is_suspended = false;
         selectedGuest.value.suspension_reason = null;
      }
    } catch (err) {
      console.error('Failed to unsuspend guest', err);
      Swal.fire('Error', 'Failed to unsuspend guest account.', 'error');
    }
  }
};
</script>

<script>
export default {
  name: 'GuestsView'
}
</script>

<style scoped>
.bg-gold-subtle { 
  background-color: var(--primary-gold-subtle) !important; 
}
.bg-gold {
  background: var(--primary-gold) !important;
}
.text-gold {
  color: var(--primary-gold) !important;
}
.table th {
  background-color: transparent !important;
}

.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.6);
  backdrop-filter: blur(4px);
  z-index: 1050;
}

.modal-card {
  max-width: 500px;
}

.shadow-gold {
  box-shadow: 0 8px 16px rgba(188, 145, 81, 0.3);
}

.animate-fade-up {
  animation: fadeUp 0.3s ease-out forwards;
}

@keyframes fadeUp {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

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
.btn-action-view, .btn-action-history {
  width: 38px;
  height: 38px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  border: none;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  font-size: 1.1rem;
}

.btn-action-view {
  background-color: var(--primary-gold-subtle);
  color: var(--primary-gold);
}

.btn-action-view:hover {
  background-color: var(--primary-gold);
  color: white;
  transform: translateY(-3px) rotate(8deg);
  box-shadow: 0 5px 15px rgba(188, 145, 81, 0.3);
}

.btn-action-history {
  background-color: #f1f5f9;
  color: #64748b;
}

.btn-action-history:hover {
  background-color: #0f172a;
  color: white;
  transform: translateY(-3px) rotate(-8deg);
  box-shadow: 0 5px 15px rgba(15, 23, 42, 0.2);
}

.btn-action-suspend, .btn-action-unsuspend {
  width: 38px;
  height: 38px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  border: none;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  font-size: 1.1rem;
}

.btn-action-suspend {
  background-color: #fee2e2;
  color: #ef4444;
}

.btn-action-suspend:hover {
  background-color: #ef4444;
  color: white;
  transform: translateY(-3px) rotate(8deg);
  box-shadow: 0 5px 15px rgba(239, 68, 68, 0.3);
}

.btn-action-unsuspend {
  background-color: #dcfce7;
  color: #22c55e;
}

.btn-action-unsuspend:hover {
  background-color: #22c55e;
  color: white;
  transform: translateY(-3px) rotate(-8deg);
  box-shadow: 0 5px 15px rgba(34, 197, 94, 0.3);
}
</style>
