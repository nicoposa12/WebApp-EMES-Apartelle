<template>
  <div class="reservations-management-view">
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden animate-fade-up">
      <div class="card-header bg-white py-4 px-4 border-0 d-flex justify-content-between align-items-center">
        <h5 class="serif-font fw-bold mb-0 text-secondary-dark">Manage Bookings</h5>
        <div class="d-flex gap-2">
           <div class="input-group input-group-sm" style="width: 280px;">
             <span class="input-group-text bg-light border-0"><i class="bi bi-search"></i></span>
             <input type="text" class="form-control bg-light border-0" placeholder="Search ID, guest or room..." v-model="searchQuery">
           </div>
           <button class="btn btn-gold btn-sm px-3 fw-bold text-uppercase shadow-sm" @click="openNewBookingModal">
             <i class="bi bi-plus-lg me-1"></i> New Booking
           </button>
        </div>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead>
              <tr>
                <th class="ps-4 border-0 text-muted small fw-bold text-uppercase">Booking #</th>
                <th class="border-0 text-muted small fw-bold text-uppercase">Guest Info</th>
                <th class="border-0 text-muted small fw-bold text-uppercase">Room & Date</th>
                <th class="border-0 text-muted small fw-bold text-uppercase">Price</th>
                <th class="border-0 text-muted small fw-bold text-uppercase">Status</th>
                <th class="pe-4 border-0 text-muted small fw-bold text-uppercase text-end">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="res in paginatedReservations" :key="res.id">
                <td class="ps-4 py-3">
                  <span class="small fw-bold text-secondary-dark">#RES-{{ res.id.toString().padStart(3, '0') }}</span>
                </td>
                <td>
                  <div class="d-flex align-items-center gap-3">
                    <div class="avatar-md bg-gold-subtle rounded-circle d-flex align-items-center justify-content-center fw-bold text-gold overflow-hidden" style="width: 40px; height: 40px;">
                      <img v-if="res.user?.profile_photo_url" :src="res.user.profile_photo_url" :alt="res.user?.name" class="w-100 h-100 object-fit-cover">
                      <span v-else>{{ (res.user?.name || 'G').charAt(0) }}</span>
                    </div>
                    <div>
                      <span class="small fw-bold text-secondary-dark d-block">{{ res.user?.name || 'Guest Name' }}</span>
                      <small class="text-muted">{{ res.user?.email }}</small>
                    </div>
                  </div>
                </td>
                <td>
                  <div class="d-flex flex-column gap-1">
                    <span class="small fw-medium">{{ res.room?.room_type }} #{{ res.room?.room_number }}</span>
                    <small class="text-muted"><i class="bi bi-calendar3 me-1"></i>{{ formatDate(res.check_in) }} - {{ formatDate(res.check_out) }}</small>
                  </div>
                </td>
                <td><span class="fw-bold text-secondary-dark">₱{{ formatPrice(res.total_amount) }}</span></td>
                <td>
                  <select 
                    class="form-select form-select-sm rounded-pill w-auto fw-bold text-uppercase border-0 shadow-sm px-3" 
                    :class="statusBadgeClass(res.status)"
                    v-model="res.status"
                    @change="updateStatus(res)"
                    style="font-size: 0.65rem;"
                  >
                    <option value="pending">PENDING</option>
                    <option value="confirmed">CONFIRMED</option>
                    <option value="completed">COMPLETED</option>
                    <option value="cancelled">CANCELLED</option>
                  </select>
                </td>
                <td class="pe-4 text-end">
                  <div class="d-flex justify-content-end gap-2">
                    <a :href="'/invoices/' + res.id + '/download'" class="btn btn-action-invoice" title="Download Invoice">
                      <i class="bi bi-file-earmark-pdf"></i>
                    </a>
                    <button class="btn btn-action-delete" title="Delete Booking" @click="deleteReservation(res.id)">
                      <i class="bi bi-trash"></i>
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="filteredReservations.length === 0">
                <td colspan="6" class="text-center py-5 text-muted">No reservations found.</td>
              </tr>
            </tbody>
          </table>
        </div>
        
        <!-- Pagination -->
        <AdminPagination 
          :current-page="currentPage" 
          :total-items="filteredReservations.length" 
          :page-size="pageSize"
          @change="page => currentPage = page"
          @page-size-change="size => { pageSize = size; currentPage = 1; }"
        />
      </div>
    </div>

    <!-- New Booking Modal -->
    <Teleport to="body">
       <div v-if="showNewBookingModal" class="modal-backdrop fade show" style="z-index: 1050;"></div>
       <div v-if="showNewBookingModal" class="modal fade show d-block" tabindex="-1" style="z-index: 1055;" @click.self="closeNewBookingModal">
         <div class="modal-dialog modal-dialog-centered modal-lg">
           <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
             <div class="modal-header border-bottom px-4 py-3 align-items-center">
               <h5 class="modal-title serif-font fw-bold text-secondary-dark">Add New Booking</h5>
               <button type="button" class="btn-close shadow-none" @click="closeNewBookingModal"></button>
             </div>
             <div class="modal-body p-4 bg-light-subtle">
                <div class="alert alert-info border-0 shadow-sm d-flex align-items-center gap-3 rounded-3 mb-4">
                  <i class="bi bi-info-circle-fill text-info fs-5"></i>
                  <span class="small text-muted">Use this form for walk-in guests or phone reservations.</span>
                </div>
                
                <form @submit.prevent="createBooking">
                  <div class="row g-3">
                    <!-- Guest Selection -->
                    <div class="col-12">
                      <label class="form-label-custom small fw-bold text-uppercase text-muted mb-1">Select Guest</label>
                      <select class="form-select form-control-custom py-2" v-model="newBooking.user_id" required>
                        <option value="" disabled>Choose a guest...</option>
                        <option v-for="user in users" :key="user.id" :value="user.id">
                          {{ user.name || (user.first_name + ' ' + user.last_name) }} ({{ user.email }})
                        </option>
                      </select>
                      <div class="form-text text-end"><a href="#" class="text-gold text-decoration-none small fw-bold" @click.prevent="showRegisterGuestModal = true">+ Register New Guest</a></div>
                    </div>

                    <!-- Dates -->
                    <div class="col-md-6">
                       <label class="form-label-custom small fw-bold text-uppercase text-muted mb-1">Check-in Date</label>
                       <input type="date" class="form-control form-control-custom py-2" v-model="newBooking.check_in" :min="todayStr" required @change="validateDates">
                    </div>
                    <div class="col-md-6">
                       <label class="form-label-custom small fw-bold text-uppercase text-muted mb-1">Check-out Date</label>
                       <input type="date" class="form-control form-control-custom py-2" v-model="newBooking.check_out" :min="newBooking.check_in || todayStr" required @change="validateDates">
                    </div>

                    <!-- Room Selection -->
                    <div class="col-12">
                       <label class="form-label-custom small fw-bold text-uppercase text-muted mb-1">Select Room</label>
                       <div class="input-group">
                         <select class="form-select form-control-custom py-2" v-model="newBooking.room_id" required style="border-top-right-radius: 0; border-bottom-right-radius: 0;">
                           <option value="" disabled>Choose a room...</option>
                           <option v-for="room in availableRooms" :key="room.id" :value="room.id">
                             {{ room.room_type }} #{{ room.room_number }} - ₱{{ formatPrice(room.price_per_night) }}/night
                           </option>
                         </select>
                         <button 
                            class="btn btn-outline-secondary px-3 bg-white" 
                            type="button" 
                            :disabled="!newBooking.room_id"
                            @click="viewRoomDetails"
                            title="View Room"
                            style="border-color: #dee2e6; border-left: 0;"
                          >
                            <i class="bi bi-eye text-primary"></i>
                         </button>
                       </div>
                    </div>
                  </div>
                  
                  <div class="d-flex justify-content-end gap-3 mt-5 pt-3 border-top">
                    <button type="button" class="btn btn-light px-4 fw-bold text-muted" @click="closeNewBookingModal">Cancel</button>
                    <button type="submit" class="btn btn-gold px-4 fw-bold text-uppercase shadow-sm" :disabled="isSubmitting">
                      <span v-if="isSubmitting" class="spinner-border spinner-border-sm me-2"></span>
                      {{ isSubmitting ? 'Creating...' : 'Create Booking' }}
                    </button>
                  </div>
                </form>
             </div>
           </div>
         </div>
       </div>

       <!-- Room Detail Modal (Nested) -->
       <div v-if="showRoomDetailModal" class="modal-backdrop fade show" style="z-index: 1060;"></div>
       <div v-if="showRoomDetailModal" class="modal fade show d-block" tabindex="-1" style="z-index: 1065;" @click.self="showRoomDetailModal = false">
         <div class="modal-dialog modal-dialog-centered">
           <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
             <div class="modal-header border-0 position-absolute end-0 top-0 z-2 p-3">
                <button type="button" class="btn-close bg-white opacity-75 shadow-sm rounded-circle p-2" @click="showRoomDetailModal = false"></button>
             </div>
             <div class="modal-body p-0" v-if="selectedRoomDetail">
                <div class="position-relative" style="height: 250px;">
                  <img :src="getRoomImage(selectedRoomDetail)" class="w-100 h-100 object-fit-cover" :alt="selectedRoomDetail.room_type">
                  <div class="position-absolute bottom-0 start-0 w-100 p-3 bg-gradient-dark text-white">
                    <h5 class="mb-0 fw-bold serif-font">{{ selectedRoomDetail.room_type }} #{{ selectedRoomDetail.room_number }}</h5>
                  </div>
                </div>
                <div class="p-4">
                  <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="badge bg-gold-subtle text-gold px-3 py-2 rounded-pill text-uppercase fw-bold" style="font-size: 0.7rem;">
                       {{ selectedRoomDetail.status }}
                    </span>
                    <span class="h4 serif-font text-secondary-dark mb-0">₱{{ formatPrice(selectedRoomDetail.price_per_night) }}</span>
                  </div>
                  <p class="text-muted small mb-3">{{ selectedRoomDetail.description }}</p>
                  
                  <div class="row g-2 mb-3">
                     <div class="col-6" v-if="selectedRoomDetail.room_size">
                       <small class="text-muted d-flex align-items-center gap-2"><i class="bi bi-arrows-fullscreen"></i> {{ selectedRoomDetail.room_size }} m²</small>
                     </div>
                     <div class="col-6">
                       <small class="text-muted d-flex align-items-center gap-2"><i class="bi bi-people"></i> Max {{ selectedRoomDetail.max_occupancy }} Guests</small>
                     </div>
                  </div>

                  <div class="d-flex flex-wrap gap-2 mt-3" v-if="selectedRoomDetail.amenities?.length">
                    <span v-for="amenity in selectedRoomDetail.amenities" :key="amenity.id" class="badge bg-light text-muted border fw-normal">
                      {{ amenity.name }}
                    </span>
                  </div>
                </div>
             </div>
           </div>
         </div>
       </div>
       <!-- Register New Guest Modal -->
       <div v-if="showRegisterGuestModal" class="modal-backdrop fade show" style="z-index: 1070;"></div>
       <div v-if="showRegisterGuestModal" class="modal fade show d-block" tabindex="-1" style="z-index: 1075;" @click.self="showRegisterGuestModal = false">
         <div class="modal-dialog modal-dialog-centered">
           <div class="modal-content border-0 rounded-4 shadow-lg">
             <div class="modal-header border-bottom px-4 py-3">
               <h5 class="modal-title serif-font fw-bold text-secondary-dark">Register New Guest</h5>
               <button type="button" class="btn-close shadow-none" @click="showRegisterGuestModal = false"></button>
             </div>
             <div class="modal-body p-4 bg-light-subtle">
                <form @submit.prevent="registerGuest">
                  <div class="row g-3">
                    <div class="col-md-6">
                      <label class="form-label-custom small fw-bold text-uppercase text-muted mb-1">First Name</label>
                      <input type="text" class="form-control form-control-custom" v-model="guestForm.first_name" required>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label-custom small fw-bold text-uppercase text-muted mb-1">Last Name</label>
                      <input type="text" class="form-control form-control-custom" v-model="guestForm.last_name" required>
                    </div>
                    <div class="col-12">
                      <label class="form-label-custom small fw-bold text-uppercase text-muted mb-1">Email Address</label>
                      <input type="email" class="form-control form-control-custom" v-model="guestForm.email" required>
                    </div>
                    <div class="col-12">
                      <label class="form-label-custom small fw-bold text-uppercase text-muted mb-1">Phone Number</label>
                      <input type="tel" class="form-control form-control-custom" v-model="guestForm.phone">
                    </div>
                  </div>
                  <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                    <button type="button" class="btn btn-light px-4" @click="showRegisterGuestModal = false">Cancel</button>
                    <button type="submit" class="btn btn-gold px-4 fw-bold" :disabled="isRegistering">
                      <span v-if="isRegistering" class="spinner-border spinner-border-sm me-2"></span>
                      Register Guest
                    </button>
                  </div>
                </form>
             </div>
           </div>
         </div>
       </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import axios from 'axios';
import { notify, confirm } from '../../utils/sweetalert';
import AdminPagination from '../../components/AdminPagination.vue';

const reservations = ref([]);
const searchQuery = ref('');
const currentPage = ref(1);
const pageSize = ref(5);

// New Booking State
const showNewBookingModal = ref(false);
const showRoomDetailModal = ref(false);
const showRegisterGuestModal = ref(false);
const isSubmitting = ref(false);
const isRegistering = ref(false);
const users = ref([]);
const availableRooms = ref([]);
const selectedRoomDetail = ref(null);
const newBooking = ref({
  user_id: '',
  room_id: '',
  check_in: '',
  check_out: ''
});

const guestForm = ref({
  first_name: '',
  last_name: '',
  email: '',
  phone: ''
});

const todayStr = computed(() => {
  const today = new Date();
  return today.toISOString().split('T')[0];
});

const fetchReservations = async () => {
  try {
    const response = await axios.get('/api/reservations');
    reservations.value = response.data;
  } catch (err) {
    console.error('Failed to fetch reservations', err);
    reservations.value = [];
  }
};

const fetchUsers = async () => {
  try {
    const response = await axios.get('/api/admin/guests');
    users.value = response.data;
  } catch (err) {
    console.error('Failed to fetch users', err);
  }
};

const registerGuest = async () => {
  isRegistering.value = true;
  try {
    const response = await axios.post('/api/admin/guests', guestForm.value);
    notify.success('Guest Registered', 'The guest has been successfully registered.');
    
    // Refresh user list and select the new user
    await fetchUsers();
    newBooking.value.user_id = response.data.id;
    
    // Reset and close modal
    guestForm.value = { first_name: '', last_name: '', email: '', phone: '' };
    showRegisterGuestModal.value = false;
  } catch (err) {
    console.error('Registration failed', err);
    notify.error('Register Failed', err.response?.data?.message || 'Could not register guest.');
  } finally {
    isRegistering.value = false;
  }
};

const fetchAvailableRooms = async () => {
  try {
    const params = {};
    if (newBooking.value.check_in && newBooking.value.check_out) {
       params.check_in = newBooking.value.check_in;
       params.check_out = newBooking.value.check_out;
    }
    const response = await axios.get('/api/rooms', { params });
    // Filter out only available rooms generally, or specific logic if backend handles it
    availableRooms.value = response.data.filter(r => r.status === 'available');
  } catch (err) {
    console.error('Failed to fetch rooms', err);
  }
};

const openNewBookingModal = () => {
  showNewBookingModal.value = true;
  fetchUsers();
  fetchAvailableRooms();
  // Reset form
  newBooking.value = { user_id: '', room_id: '', check_in: '', check_out: '' };
};

const closeNewBookingModal = () => {
  showNewBookingModal.value = false;
};

const validateDates = () => {
  if (newBooking.value.check_in && newBooking.value.check_out) {
     if (newBooking.value.check_out <= newBooking.value.check_in) {
        newBooking.value.check_out = '';
        notify.warning('Invalid Date', 'Check-out date must be after check-in date.');
     } else {
        fetchAvailableRooms(); // Refresh available rooms based on dates
     }
  }
};

const viewRoomDetails = () => {
  const room = availableRooms.value.find(r => r.id === newBooking.value.room_id);
  if (room) {
    selectedRoomDetail.value = room;
    showRoomDetailModal.value = true;
  }
};

const getRoomImage = (room) => {
    if (!room) return '';
    if (room.image) return room.image;
    
    // Fallback based on type keywords
    const type = room.room_type.toLowerCase();
    if (type.includes('suite')) return 'https://images.unsplash.com/photo-1566665797739-1674de7a421a?auto=format&fit=crop&w=800&q=80';
    if (type.includes('deluxe')) return 'https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=800&q=80';
    return 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?auto=format&fit=crop&w=800&q=80';
};

const createBooking = async () => {
   isSubmitting.value = true;
   try {
     await axios.post('/api/reservations', {
       ...newBooking.value,
       created_by_admin: true // Flag to backend if needed
     });
     notify.success('Success', 'Booking created successfully.');
     closeNewBookingModal();
     fetchReservations();
   } catch (err) {
     console.error('Create booking failed', err);
     notify.error('Failed', err.response?.data?.message || 'Could not create booking.');
   } finally {
     isSubmitting.value = false;
   }
};

const filteredReservations = computed(() => {
  if (!searchQuery.value) return reservations.value;
  const q = searchQuery.value.toLowerCase();
  return reservations.value.filter(res => 
    res.user?.name?.toLowerCase().includes(q) || 
    res.room?.room_number?.toString().includes(q) ||
    res.id.toString().includes(q)
  );
});

const paginatedReservations = computed(() => {
  const start = (currentPage.value - 1) * pageSize.value;
  const end = start + pageSize.value;
  return filteredReservations.value.slice(start, end);
});

// Reset to page 1 when searching
watch(searchQuery, () => {
  currentPage.value = 1;
});

const updateStatus = async (res) => {
  try {
    await axios.put(`/api/reservations/${res.id}`, { status: res.status });
    notify.success('Status Updated', `Reservation #RES-${res.id.toString().padStart(3, '0')} is now ${res.status.toUpperCase()}.`);
  } catch (err) {
    console.error('Failed to update status', err);
    notify.error('Update Failed', 'Could not save status change to the server.');
    // Refresh to revert to actual database state
    fetchReservations();
  }
};

const deleteReservation = async (id) => {
  const res = await confirm({
    title: 'Delete Booking?',
    text: 'Are you sure you want to delete this booking?',
    confirmText: 'Yes, Delete'
  });

  if (res.isConfirmed) {
    try {
      await axios.delete(`/api/reservations/${id}`);
      reservations.value = reservations.value.filter(r => r.id !== id);
      notify.success('Deleted', 'Booking removed successfully.');
    } catch (err) {
      console.error('Failed to delete reservation', err);
      notify.error('Error', 'Could not delete booking from the server.');
    }
  }
};

const formatPrice = (price) => {
  return parseFloat(price || 0).toLocaleString();
};

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
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

onMounted(fetchReservations);
</script>

<style scoped>
.bg-gold-subtle { 
  background-color: var(--primary-gold-subtle) !important; 
}
.bg-light-danger {
  background-color: #FFF5F5;
}

.table th {
  background-color: transparent !important;
}

.form-select-sm:focus {
  box-shadow: none;
}

.btn-action-invoice, .btn-action-delete {
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

.btn-action-invoice {
  background-color: var(--primary-gold-subtle);
  color: var(--primary-gold);
}

.btn-action-invoice:hover {
  background-color: var(--primary-gold);
  color: white;
  transform: translateY(-3px) rotate(8deg);
  box-shadow: 0 5px 15px rgba(188, 145, 81, 0.3);
}

.btn-action-delete {
  background-color: #fee2e2;
  color: #ef4444;
}

.btn-action-delete:hover {
  background-color: #ef4444;
  color: white;
  transform: translateY(-3px) rotate(-8deg);
  box-shadow: 0 5px 15px rgba(239, 68, 68, 0.3);
}

/* Modal Styles */
.form-label-custom {
  font-size: 0.75rem;
  letter-spacing: 0.5px;
}

.form-control-custom {
  border-color: #eee;
  font-size: 0.9rem;
}

.form-control-custom:focus {
  border-color: var(--primary-gold);
  box-shadow: 0 0 0 4px var(--primary-gold-subtle);
}

.bg-gradient-dark {
  background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
}
</style>
