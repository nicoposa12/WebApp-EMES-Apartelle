<template>
  <div class="admin-amenities">
    <div class="d-flex justify-content-between align-items-center mb-4 pb-2">
      <div>
        <h2 class="serif-font fw-bold mb-1">Features</h2>
        <p class="text-muted small mb-0">Manage what's inside the rooms.</p>
      </div>
      <div class="d-flex gap-3">
        <div class="input-group input-group-sm" style="width: 250px;">
          <span class="input-group-text bg-light border-0"><i class="bi bi-search text-muted"></i></span>
          <input type="text" class="form-control bg-light border-0" placeholder="Search features..." v-model="searchQuery">
        </div>
        <button @click="openCreateModal" class="btn btn-gold d-flex align-items-center gap-2 px-4 shadow-sm py-2">
            <i class="bi bi-plus-lg fs-6"></i>
            <span>Add Feature</span>
        </button>
      </div>
    </div>

    <!-- Stats Row -->
    <div class="row g-4 mb-5">
      <div class="col-md-3">
        <div class="stats-card">
          <div class="d-flex align-items-center gap-3">
            <div class="stats-icon bg-gold-subtle text-gold">
              <i class="bi bi-stars"></i>
            </div>
            <div>
              <h4 class="mb-0 fw-bold">{{ amenities.length }}</h4>
              <p class="text-muted small mb-0">Total Features</p>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="stats-card">
          <div class="d-flex align-items-center gap-3">
            <div class="stats-icon bg-success-subtle text-success">
              <i class="bi bi-check-circle"></i>
            </div>
            <div>
              <h4 class="mb-0 fw-bold">{{ activeAmenitiesCount }}</h4>
              <p class="text-muted small mb-0">Active Features</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Amenities Grid/Table -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
      <div class="table-responsive p-0">
        <table class="table table-hover align-middle mb-0">
          <thead class="bg-light border-bottom">
            <tr>
              <th class="ps-4 py-3 text-uppercase small tracking-wider text-muted fw-bold">Icon</th>
              <th class="py-3 text-uppercase small tracking-wider text-muted fw-bold">Amenity Name</th>
              <th class="py-3 text-uppercase small tracking-wider text-muted fw-bold">Description</th>
              <th class="py-3 text-uppercase small tracking-wider text-muted fw-bold">Status</th>
              <th class="py-3 text-uppercase small tracking-wider text-muted fw-bold text-end pe-4">Actions</th>
            </tr>
          </thead>
          <tbody class="border-0">
            <tr v-if="loading">
              <td colspan="5" class="text-center py-5">
                <span class="spinner-border spinner-border-sm text-gold me-2"></span>
                <span class="text-muted">Loading amenities...</span>
              </td>
            </tr>
            <tr v-else-if="filteredAmenities.length === 0">
              <td colspan="5" class="text-center py-5">
                <i class="bi bi-stars fs-1 text-muted opacity-25 d-block mb-3"></i>
                <p class="text-muted mb-0">No features found.</p>
              </td>
            </tr>
            <tr v-for="amenity in paginatedAmenities" :key="amenity.id" v-else>
              <td class="ps-4">
                <div class="amenity-icon-box shadow-sm">
                  <i :class="['bi', formatIconClass(amenity.icon)]"></i>
                </div>
              </td>
              <td class="fw-bold text-dark">{{ amenity.name }}</td>
              <td class="text-muted small w-40">{{ amenity.description }}</td>
              <td>
                <span :class="['badge rounded-pill px-3 py-2', amenity.is_active ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary']">
                  {{ amenity.is_active ? 'On' : 'Off' }}
                </span>
              </td>
              <td class="text-end pe-4">
                <div class="d-flex justify-content-end gap-2">
                  <button @click="openEditModal(amenity)" class="btn btn-icon-sm btn-light-gold" title="Edit">
                    <i class="bi bi-pencil-square"></i>
                  </button>
                  <button @click="confirmDelete(amenity.id)" class="btn btn-icon-sm btn-light-danger" title="Delete">
                    <i class="bi bi-trash"></i>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <AdminPagination 
        :current-page="currentPage" 
        :total-items="filteredAmenities.length" 
        :page-size="pageSize"
        @change="page => currentPage = page"
        @page-size-change="size => { pageSize = size; currentPage = 1; }"
      />
    </div>

    <!-- Modal for Create/Edit -->
    <div v-if="showModal" class="modal-overlay d-flex align-items-center justify-content-center p-3">
      <div class="modal-card bg-white rounded-4 shadow-xl w-100 max-w-lg overflow-hidden animate-fade-up">
        <div class="modal-header border-bottom p-4 d-flex justify-content-between align-items-center bg-light">
          <h5 class="serif-font fw-bold mb-0">{{ editMode ? 'Edit Amenity' : 'New Amenity' }}</h5>
          <button @click="closeModal" class="btn-close shadow-none"></button>
        </div>
        <form @submit.prevent="saveAmenity">
          <div class="modal-body p-4">
            <div class="mb-4">
              <label class="form-label-custom small fw-bold text-uppercase d-block mb-2">Amenity Name</label>
              <input v-model="form.name" type="text" class="form-control-modern" placeholder="e.g., Free High-Speed WiFi" required>
            </div>
            
            <div class="mb-4">
              <label class="form-label-custom small fw-bold text-uppercase d-block mb-2">Icon (Bootstrap Icon Class)</label>
              <div class="input-group">
                <span class="input-group-text bg-light border-end-0 rounded-start-3 d-flex align-items-center justify-content-center" style="width: 50px;">
                  <i :class="['bi', formatIconClass(form.icon) || 'bi-stars', 'fs-4']"></i>
                </span>
                <input v-model="form.icon" type="text" class="form-control-modern border-start-0 rounded-end-0" placeholder="e.g., wifi or bi-wifi" required>
                <button type="button" @click="showIconPicker = true" class="btn btn-gold rounded-end-3 px-3">
                  <i class="bi bi-grid-3x3-gap-fill"></i>
                </button>
              </div>
              <div class="d-flex justify-content-between mt-2">
                <small class="text-muted">Use <a href="https://icons.getbootstrap.com/" target="_blank" class="text-gold fw-bold">Bootstrap Icons</a> classes.</small>
                <button type="button" @click="showIconPicker = true" class="btn btn-link p-0 text-gold small text-decoration-none fw-bold">Browse Icons <i class="bi bi-chevron-right small"></i></button>
              </div>
            </div>

            <div class="mb-4">
              <label class="form-label-custom small fw-bold text-uppercase d-block mb-2">Description</label>
              <textarea v-model="form.description" class="form-control-modern" rows="3" placeholder="Briefly describe the amenity or service..."></textarea>
            </div>

            <div class="mb-2">
              <div class="form-check form-switch ps-5">
                <input v-model="form.is_active" class="form-check-input" type="checkbox" id="isActiveSwitch">
                <label class="form-check-label fw-semibold" for="isActiveSwitch">Set as Active</label>
              </div>
              <small class="text-muted ps-2">Inactive amenities won't be shown on the homepage.</small>
            </div>
          </div>
          <div class="modal-footer border-top p-4 d-flex gap-3 justify-content-end bg-light">
            <button type="button" @click="closeModal" class="btn btn-outline-modern px-4 py-2">Cancel</button>
            <button type="submit" class="btn btn-gold px-4 py-2" :disabled="submitting">
              <span v-if="submitting" class="spinner-border spinner-border-sm me-2"></span>
              {{ editMode ? 'Update Amenity' : 'Save Amenity' }}
            </button>
          </div>
        </form>
      </div>
    </div>
    <!-- Icon Picker Modal -->
    <div v-if="showIconPicker" class="modal-overlay d-flex align-items-center justify-content-center p-3" style="z-index: 1060;">
      <div class="modal-card bg-white rounded-5 shadow-premium w-100 max-w-xl animate-scale-up border-0 overflow-hidden">
        <div class="modal-header border-bottom p-4 bg-light d-flex justify-content-between align-items-center">
          <div>
            <h5 class="serif-font fw-bold mb-0">Select Feature Icon</h5>
            <p class="text-muted x-small mb-0">Pick an icon that best represents this feature</p>
          </div>
          <button @click="showIconPicker = false" class="btn-close shadow-none"></button>
        </div>
        <div class="modal-body p-4">
           <div class="input-group mb-4 shadow-sm rounded-pill overflow-hidden border">
              <span class="input-group-text bg-white border-0 ps-3 text-muted"><i class="bi bi-search"></i></span>
              <input type="text" v-model="iconSearch" class="form-control border-0 py-2.5 shadow-none" placeholder="Search icons (e.g., wifi, bed, pool)...">
           </div>

           <div class="icon-grid custom-scrollbar overflow-y-auto" style="height: 320px;">
              <div v-if="filteredIcons.length === 0" class="text-center py-5">
                 <i class="bi bi-emoji-expressionless fs-1 text-muted opacity-25"></i>
                 <p class="text-muted small mt-2">No matching icons found.</p>
              </div>
              <div v-else class="row g-2">
                 <div v-for="icon in filteredIcons" :key="icon" class="col-3 col-sm-2">
                    <button @click="selectIcon(icon)" type="button" class="icon-picker-btn transition-all" :class="{'active': form.icon === icon || form.icon === `bi-${icon}`}">
                       <i :class="['bi', `bi-${icon}`]"></i>
                       <span class="icon-picker-label">{{ icon }}</span>
                    </button>
                 </div>
              </div>
           </div>
        </div>
        <div class="modal-footer bg-light p-3 d-flex justify-content-center">
           <button @click="showIconPicker = false" class="btn btn-gold rounded-pill px-5 fw-bold text-uppercase">Close Selection</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed, watch } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';
import AdminPagination from '../../components/AdminPagination.vue';

const amenities = ref([]);
const loading = ref(false);
const submitting = ref(false);
const showModal = ref(false);
const showIconPicker = ref(false);
const iconSearch = ref('');
const editMode = ref(false);
const selectedId = ref(null);

const searchQuery = ref('');
const currentPage = ref(1);
const pageSize = ref(5);

const form = reactive({
  name: '',
  icon: '',
  description: '',
  is_active: true
});

const activeAmenitiesCount = computed(() => {
  return amenities.value.filter(a => a.is_active).length;
});

const filteredAmenities = computed(() => {
  if (!searchQuery.value) return amenities.value;
  const q = searchQuery.value.toLowerCase();
  return amenities.value.filter(a => 
    a.name.toLowerCase().includes(q) || 
    (a.description && a.description.toLowerCase().includes(q))
  );
});

const paginatedAmenities = computed(() => {
  const start = (currentPage.value - 1) * pageSize.value;
  const end = start + pageSize.value;
  return filteredAmenities.value.slice(start, end);
});

watch(searchQuery, () => {
  currentPage.value = 1;
});

const formatIconClass = (icon) => {
  if (!icon) return '';
  // Check if it already has bi bi- prefix
  if (icon.includes('bi-')) {
    // If it has multiple classes, just returned as is
    return icon;
  }
  // Otherwise ensure bi- prefix
  return `bi-${icon}`;
};

const commonIcons = [
  'wifi', 'snow', 'tv', 'thermometer-half', 'water', 'shield-check', 'clock-history', 'door-open', 'cup-hot', 'egg-fried',
  'wind', 'p-circle', 'bicycle', 'car-front', 'bag-heart', 'box-seam', 'camera-video', 'display', 'fan', 'fire',
  'gift', 'house-heart', 'key', 'laptop', 'lightbulb', 'mic', 'music-note', 'phone', 'reception-4', 'router',
  'safe', 'signpost-split', 'speaker', 'telephone', 'tools', 'umbrella', 'wallet2', 'zoom-in', 'stars', 'lightning-charge',
  'gem', 'briefcase', 'building', 'calendar-check', 'chat-dots', 'credit-card', 'envelope', 'gear', 'grid', 'heart',
  'info-circle', 'link-45deg', 'list', 'map', 'person', 'search', 'trash', 'alarm', 'arrow-right-circle', 'bell',
  'bookmark-star', 'brightness-high', 'camera', 'cart-check', 'check2-all', 'cloud-check', 'collection', 'command', 'compass', 'cpu',
  'database', 'egg', 'eye', 'file-earmark-text', 'flag', 'folder', 'grid-3x3-gap', 'headset', 'image', 'inbox', 'infinity',
  'joystick', 'kanban', 'layers', 'magic', 'megaphone', 'minecart', 'modem', 'moon-stars', 'mouse2', 'node-plus', 'outlet',
  'palette', 'paint-bucket', 'peace', 'pencil', 'pills', 'plug', 'printer', 'puzzle', 'qr-code', 'quote', 'rainbow',
  'receipt', 'record-circle', 'scissors', 'sort-alpha-down', 'speedometer', 'spellcheck', 'stack', 'stickies', 'stopwatch', 'suit-spade',
  'tag', 'terminal', 'ticket-perforated', 'translate', 'trophy', 'type-bold', 'vector-pen', 'view-list', 'voicemail', 'watch',
  'webcam', 'window', 'wrench', 'xbox', 'youtube', 'archive', 'arrow-bar-up', 'balloon', 'bank', 'bookshelf', 'box'
];

const filteredIcons = computed(() => {
  if (!iconSearch.value) return commonIcons;
  const q = iconSearch.value.toLowerCase();
  return commonIcons.filter(icon => icon.includes(q));
});

const selectIcon = (iconClass) => {
  form.icon = `bi-${iconClass}`;
  showIconPicker.value = false;
};

const fetchAmenities = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/api/amenities');
    amenities.value = response.data;
  } catch (error) {
    console.error('Failed to fetch amenities:', error);
    Swal.fire({
      icon: 'error',
      title: 'Fetch Error',
      text: 'Could not load amenities list.'
    });
  } finally {
    loading.value = false;
  }
};

const openCreateModal = () => {
  editMode.value = false;
  selectedId.value = null;
  form.name = '';
  form.icon = 'bi-stars';
  form.description = '';
  form.is_active = true;
  showModal.value = true;
};

const openEditModal = (amenity) => {
  editMode.value = true;
  selectedId.value = amenity.id;
  form.name = amenity.name;
  form.icon = amenity.icon;
  form.description = amenity.description;
  form.is_active = !!amenity.is_active;
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
};

const saveAmenity = async () => {
  submitting.value = true;
  try {
    if (editMode.value) {
      await axios.put(`/api/amenities/${selectedId.value}`, form);
      Swal.fire({
        icon: 'success',
        title: 'Updated',
        text: 'Amenity updated successfully.',
        timer: 1500,
        showConfirmButton: false,
        toast: true,
        position: 'top-end'
      });
    } else {
      await axios.post('/api/amenities', form);
      Swal.fire({
        icon: 'success',
        title: 'Added',
        text: 'New amenity created successfully.',
        timer: 1500,
        showConfirmButton: false,
        toast: true,
        position: 'top-end'
      });
    }
    fetchAmenities();
    closeModal();
  } catch (error) {
    console.error('Save failed:', error);
    Swal.fire({
      icon: 'error',
      title: 'Save Failed',
      text: error.response?.data?.message || 'Something went wrong.'
    });
  } finally {
    submitting.value = false;
  }
};

const confirmDelete = (id) => {
  Swal.fire({
    title: 'Delete Feature?',
    text: "Are you sure you want to delete this feature?",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#dc3545',
    cancelButtonColor: '#718096',
    confirmButtonText: 'Yes, Delete'
  }).then(async (result) => {
    if (result.isConfirmed) {
      try {
        await axios.delete(`/api/amenities/${id}`);
        Swal.fire('Deleted!', 'Amenity has been removed.', 'success');
        fetchAmenities();
      } catch (error) {
        Swal.fire('Error', 'Could not delete amenity.', 'error');
      }
    }
  });
};

onMounted(fetchAmenities);
</script>

<style scoped>
.stats-card {
  background: white;
  padding: 1.5rem;
  border-radius: 1.25rem;
  box-shadow: 0 4px 12px rgba(0,0,0,0.03);
}

.stats-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
}

.amenity-icon-box {
  width: 40px;
  height: 40px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.25rem;
  color: var(--primary-gold);
}

.w-40 { width: 40%; }

.btn-icon-sm {
  width: 32px;
  height: 32px;
  padding: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 8px;
  font-size: 0.9rem;
}

.btn-light-gold {
  background-color: var(--primary-gold-subtle);
  color: var(--primary-gold);
}

.btn-light-gold:hover {
  background-color: var(--primary-gold);
  color: white;
}

.btn-light-danger {
  background-color: rgba(239, 68, 68, 0.1);
  color: #ef4444;
}

.btn-light-danger:hover {
  background-color: #ef4444;
  color: white;
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

.form-control-modern {
  width: 100%;
  padding: 0.75rem 1rem;
  background: #f8fafc;
  border: 1.5px solid #e2e8f0;
  border-radius: 10px;
  transition: all 0.3s ease;
}

.form-control-modern:focus {
  background: white;
  border-color: #BC9151;
  box-shadow: 0 0 0 4px rgba(188, 145, 81, 0.1);
  outline: none;
}

.max-w-lg { max-width: 500px; }

.animate-fade-up {
  animation: fadeUp 0.3s ease-out forwards;
}

@keyframes fadeUp {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

.x-small { font-size: 0.75rem; }

/* Icon Picker Styles */
.icon-grid {
  margin: 0 -0.5rem;
  padding: 0.5rem;
}

.icon-picker-btn {
  width: 100%;
  aspect-ratio: 1;
  border-radius: 12px;
  border: 1px solid #f1f5f9;
  background: white;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.5rem;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

.icon-picker-btn i {
  font-size: 1.25rem;
  color: #64748b;
}

.icon-picker-label {
  font-size: 0.6rem;
  color: #94a3b8;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  width: 100%;
  text-align: center;
  font-weight: 600;
}

.icon-picker-btn:hover {
  border-color: var(--primary-gold);
  background-color: var(--primary-gold-subtle);
  transform: translateY(-3px);
  box-shadow: 0 4px 12px rgba(188, 145, 81, 0.15);
}

.icon-picker-btn:hover i {
  color: var(--primary-gold);
}

.icon-picker-btn.active {
  background: var(--primary-gold);
  border-color: var(--primary-gold);
}

.icon-picker-btn.active i,
.icon-picker-btn.active .icon-picker-label {
  color: white;
}

.animate-scale-up {
  animation: scaleUp 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

@keyframes scaleUp {
  from { opacity: 0; transform: scale(0.9); }
  to { opacity: 1; transform: scale(1); }
}

.shadow-premium {
  box-shadow: 0 20px 50px -12px rgba(0, 0, 0, 0.15);
}
</style>
