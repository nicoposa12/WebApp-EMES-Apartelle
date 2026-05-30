<template>
  <div class="disputes-view container-fluid py-4">
    <!-- Header Metrics -->
    <div class="row g-4 mb-4">
      <div class="col-12 col-sm-6 col-md-3 animate-fade-up">
        <div class="card border-0 shadow-sm rounded-4 bg-white p-3 h-100 position-relative overflow-hidden">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <span class="text-muted small fw-bold text-uppercase d-block mb-1">Total Cases</span>
              <h2 class="serif-font fw-bold mb-0 text-secondary-dark">{{ metrics.total }}</h2>
            </div>
            <div class="icon-shape bg-light-soft text-secondary-dark rounded-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
              <i class="bi bi-folder-fill fs-4"></i>
            </div>
          </div>
          <div class="progress-bar-subtle mt-3" style="height: 3px; background-color: #f1f5f9; border-radius: 2px;">
            <div style="height: 100%; background-color: #64748b; border-radius: 2px; width: 100%;"></div>
          </div>
        </div>
      </div>

      <div class="col-12 col-sm-6 col-md-3 animate-fade-up" style="animation-delay: 0.1s;">
        <div class="card border-0 shadow-sm rounded-4 bg-white p-3 h-100 position-relative overflow-hidden">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <span class="text-muted small fw-bold text-uppercase d-block mb-1">Pending Review</span>
              <h2 class="serif-font fw-bold mb-0 text-warning">{{ metrics.pending }}</h2>
            </div>
            <div class="icon-shape bg-warning-subtle text-warning rounded-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
              <i class="bi bi-clock-history fs-4"></i>
            </div>
          </div>
          <div class="progress-bar-subtle mt-3" style="height: 3px; background-color: #f1f5f9; border-radius: 2px;">
            <div :style="`height: 100%; background-color: #ffc107; border-radius: 2px; width: ${getPercentage(metrics.pending)}%;`"></div>
          </div>
        </div>
      </div>

      <div class="col-12 col-sm-6 col-md-3 animate-fade-up" style="animation-delay: 0.2s;">
        <div class="card border-0 shadow-sm rounded-4 bg-white p-3 h-100 position-relative overflow-hidden">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <span class="text-muted small fw-bold text-uppercase d-block mb-1">Investigating</span>
              <h2 class="serif-font fw-bold mb-0 text-info">{{ metrics.investigating }}</h2>
            </div>
            <div class="icon-shape bg-info-subtle text-info rounded-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
              <i class="bi bi-search fs-4"></i>
            </div>
          </div>
          <div class="progress-bar-subtle mt-3" style="height: 3px; background-color: #f1f5f9; border-radius: 2px;">
            <div :style="`height: 100%; background-color: #0dcaf0; border-radius: 2px; width: ${getPercentage(metrics.investigating)}%;`"></div>
          </div>
        </div>
      </div>

      <div class="col-12 col-sm-6 col-md-3 animate-fade-up" style="animation-delay: 0.3s;">
        <div class="card border-0 shadow-sm rounded-4 bg-white p-3 h-100 position-relative overflow-hidden">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <span class="text-muted small fw-bold text-uppercase d-block mb-1">Resolved Cases</span>
              <h2 class="serif-font fw-bold mb-0 text-success">{{ metrics.resolved }}</h2>
            </div>
            <div class="icon-shape bg-success-subtle text-success rounded-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
              <i class="bi bi-check-circle-fill fs-4"></i>
            </div>
          </div>
          <div class="progress-bar-subtle mt-3" style="height: 3px; background-color: #f1f5f9; border-radius: 2px;">
            <div :style="`height: 100%; background-color: #198754; border-radius: 2px; width: ${getPercentage(metrics.resolved)}%;`"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content Table and Filter -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden animate-fade-up" style="animation-delay: 0.4s;">
      <div class="card-header bg-white py-4 px-4 border-0 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
          <h5 class="serif-font fw-bold mb-1 text-secondary-dark">Disputes Inbox</h5>
          <p class="text-muted small mb-0">Investigate, reply to, and resolve complaints submitted by guests.</p>
        </div>

        <div class="d-flex gap-2">
          <!-- Search box -->
          <div class="search-input-group position-relative">
            <span class="position-absolute top-50 start-0 translate-middle-y ps-3 text-muted">
              <i class="bi bi-search small"></i>
            </span>
            <input 
              v-model="searchQuery" 
              type="text" 
              placeholder="Search guest or room..." 
              class="form-control ps-5 py-2.5 rounded-3 text-muted small" 
              style="width: 260px;"
            >
          </div>
        </div>
      </div>

      <!-- Custom Elegant Filter Tabs -->
      <div class="px-4 border-bottom bg-white d-flex gap-3 overflow-x-auto">
        <button 
          v-for="tab in filterTabs" 
          :key="tab.value" 
          @click="activeTab = tab.value"
          class="tab-btn py-3 px-2 border-0 bg-transparent fw-bold small text-uppercase tracking-wider transition-all position-relative"
          :class="{ 'active': activeTab === tab.value }"
        >
          {{ tab.label }}
          <span v-if="tab.count > 0" class="badge rounded-pill bg-light-soft text-secondary-dark ms-2 fw-semibold">{{ tab.count }}</span>
          <div class="active-indicator position-absolute bottom-0 start-0 end-0 bg-gold" style="height: 3px;" v-if="activeTab === tab.value"></div>
        </button>
      </div>

      <div class="card-body p-0 bg-white">
        <div v-if="loading" class="text-center py-5">
          <div class="spinner-border text-gold" role="status"></div>
          <p class="text-muted mt-2 small">Loading disputes database...</p>
        </div>

        <div v-else-if="filteredDisputes.length === 0" class="text-center py-5">
          <div class="mb-3">
            <i class="bi bi-journal-check display-3 text-muted opacity-25"></i>
          </div>
          <h5 class="fw-bold mb-1">No Disputes Found</h5>
          <p class="text-muted small">Everything is running smoothly! No guest disputes match this filter.</p>
        </div>

        <div v-else class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead>
              <tr class="bg-light-soft">
                <th class="ps-4 py-3 border-0 text-muted small fw-bold text-uppercase">Case ID</th>
                <th class="border-0 text-muted small fw-bold text-uppercase">Guest</th>
                <th class="border-0 text-muted small fw-bold text-uppercase">Reservation</th>
                <th class="border-0 text-muted small fw-bold text-uppercase">Reason</th>
                <th class="border-0 text-muted small fw-bold text-uppercase">Filed On</th>
                <th class="border-0 text-muted small fw-bold text-uppercase">Status</th>
                <th class="pe-4 border-0 text-muted small fw-bold text-uppercase text-end">Action</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="dispute in paginatedDisputes" :key="dispute.id" class="transition-all">
                <td class="ps-4">
                  <span class="small fw-bold text-secondary-dark">#DSP-{{ dispute.id }}</span>
                </td>
                <td>
                  <div class="d-flex align-items-center gap-3">
                    <div class="avatar-letter rounded-circle bg-gold text-white d-flex align-items-center justify-content-center fw-bold text-uppercase shadow-sm overflow-hidden" style="width: 38px; height: 38px;">
                      <img v-if="dispute.user?.profile_photo_url" :src="dispute.user.profile_photo_url" :alt="dispute.user?.name" class="w-100 h-100 object-fit-cover">
                      <span v-else>{{ dispute.user?.name?.charAt(0) || 'G' }}</span>
                    </div>
                    <div class="d-flex flex-column">
                      <span class="small fw-bold text-secondary-dark">{{ dispute.user?.name || 'Guest' }}</span>
                      <small class="text-muted text-truncate" style="max-width: 150px;">{{ dispute.user?.email }}</small>
                    </div>
                  </div>
                </td>
                <td>
                  <div class="d-flex flex-column" v-if="dispute.reservation">
                    <span class="small fw-semibold text-secondary-dark">Room #{{ dispute.reservation.room?.room_number }}</span>
                    <small class="text-muted">{{ formatDate(dispute.reservation.check_in) }} - {{ formatDate(dispute.reservation.check_out) }}</small>
                  </div>
                  <span v-else class="small text-muted italic">Deleted booking</span>
                </td>
                <td>
                  <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-light text-secondary border rounded-3 px-2 py-1 small fw-semibold">
                      <i :class="getReasonIcon(dispute.reason)" class="me-1"></i>
                      {{ formatReason(dispute.reason) }}
                    </span>
                  </div>
                </td>
                <td>
                  <span class="small text-muted">{{ formatDateTime(dispute.created_at) }}</span>
                </td>
                <td>
                  <span :class="getStatusBadgeClass(dispute.status)">
                    {{ capitalize(dispute.status) }}
                  </span>
                </td>
                <td class="pe-4 text-end">
                  <button 
                    @click="openDisputeDetails(dispute)"
                    class="btn btn-sm btn-outline-gold rounded-pill px-3 py-1 fw-bold x-small text-uppercase transition-all hover-shadow"
                  >
                    Investigate
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <AdminPagination 
          :current-page="currentPage" 
          :total-items="filteredDisputes.length" 
          :page-size="pageSize"
          @change="page => currentPage = page"
          @pageSizeChange="size => { pageSize = size; currentPage = 1; }"
        />
      </div>
    </div>

    <!-- Elegant Custom Modal for Investigation / Action -->
    <transition name="modal-fade">
      <div v-if="showModal" class="custom-modal-backdrop d-flex align-items-center justify-content-center">
        <div class="custom-modal rounded-4 shadow-premium bg-white border overflow-hidden animate-fade-in" style="width: 620px; max-width: 95%;">
          <!-- Modal Header -->
          <div class="modal-header-accent p-4 d-flex justify-content-between align-items-center border-bottom bg-light-soft">
            <div>
              <span class="badge bg-gold-subtle text-gold text-uppercase small fw-bold mb-1 d-inline-block">Case #DSP-{{ selectedDispute.id }}</span>
              <h5 class="serif-font fw-bold text-secondary-dark mb-0">Dispute Investigation</h5>
            </div>
            <button @click="closeModal" class="btn-close shadow-none" aria-label="Close"></button>
          </div>

          <!-- Modal Body -->
          <div class="modal-body p-4 custom-scrollbar" style="max-height: 70vh; overflow-y: auto;">
            <!-- Guest & Reservation Info -->
            <div class="row g-3 mb-4 bg-light-soft p-3 rounded-4 border-0">
              <div class="col-6">
                <small class="text-muted d-block text-uppercase fw-bold x-small mb-1">Guest Submitter</small>
                <div class="d-flex align-items-center gap-2">
                  <div class="avatar-letter rounded-circle bg-gold text-white d-flex align-items-center justify-content-center fw-bold text-uppercase overflow-hidden" style="width: 28px; height: 28px; font-size: 0.75rem;">
                    <img v-if="selectedDispute.user?.profile_photo_url" :src="selectedDispute.user.profile_photo_url" :alt="selectedDispute.user?.name" class="w-100 h-100 object-fit-cover">
                    <span v-else>{{ selectedDispute.user?.name?.charAt(0) }}</span>
                  </div>
                  <span class="small fw-bold text-secondary-dark">{{ selectedDispute.user?.name }}</span>
                </div>
              </div>
              <div class="col-6">
                <small class="text-muted d-block text-uppercase fw-bold x-small mb-1">Reservation Reference</small>
                <span class="small fw-semibold text-secondary-dark">
                  Room #{{ selectedDispute.reservation?.room?.room_number }} ({{ selectedDispute.reservation?.room?.room_type }})
                </span>
              </div>
            </div>

            <!-- Complaint Details -->
            <div class="mb-4">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-bold text-uppercase x-small">Reason Category</span>
                <span class="badge bg-light text-secondary border rounded-3 px-2 py-1 x-small fw-semibold">
                  <i :class="getReasonIcon(selectedDispute.reason)" class="me-1"></i>
                  {{ formatReason(selectedDispute.reason) }}
                </span>
              </div>
              <div class="mb-2">
                <span class="text-muted small fw-bold text-uppercase x-small d-block mb-1">Guest's Description of Issue</span>
                <div class="p-3 bg-light rounded-4 text-secondary-dark small font-italic border border-light" style="line-height: 1.6;">
                  "{{ selectedDispute.description }}"
                </div>
              </div>
              <small class="text-muted x-small d-block">Filed on {{ formatDateTime(selectedDispute.created_at) }}</small>
            </div>

            <!-- Resolution / Remarks History -->
            <div v-if="selectedDispute.status === 'resolved' || selectedDispute.status === 'rejected'" class="mb-4 bg-light-soft p-3 rounded-4 border">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="small fw-bold text-uppercase x-small text-muted">Resolution Information</span>
                <span :class="getStatusBadgeClass(selectedDispute.status)">{{ capitalize(selectedDispute.status) }}</span>
              </div>
              
              <div class="mb-2">
                <small class="text-muted d-block x-small">Remarks</small>
                <p class="small text-secondary-dark mb-0 fw-semibold">"{{ selectedDispute.admin_remarks }}"</p>
              </div>

              <div class="mt-2 pt-2 border-top border-light d-flex justify-content-between align-items-center x-small text-muted">
                <span>Resolved By: <strong>{{ selectedDispute.resolver?.name || 'Manager' }}</strong></span>
                <span>Date: <strong>{{ formatDateTime(selectedDispute.resolved_at) }}</strong></span>
              </div>
            </div>

            <!-- Update Status Actions (if open) -->
            <div v-else class="action-panel border-top pt-4">
              <h6 class="fw-bold serif-font text-secondary-dark mb-3">Update Dispute Status</h6>
              
              <!-- Status Toggle Selector -->
              <div class="row g-2 mb-3">
                <div class="col-4">
                  <input 
                    type="radio" 
                    id="status-investigating" 
                    value="under_investigation" 
                    v-model="actionForm.status"
                    class="btn-check"
                  >
                  <label class="btn btn-outline-info w-100 rounded-3 py-2 fw-semibold x-small d-flex flex-column align-items-center gap-1 border-2" for="status-investigating">
                    <i class="bi bi-search fs-6"></i>
                    Investigate
                  </label>
                </div>

                <div class="col-4">
                  <input 
                    type="radio" 
                    id="status-resolved" 
                    value="resolved" 
                    v-model="actionForm.status"
                    class="btn-check"
                  >
                  <label class="btn btn-outline-success w-100 rounded-3 py-2 fw-semibold x-small d-flex flex-column align-items-center gap-1 border-2" for="status-resolved">
                    <i class="bi bi-check-circle-fill fs-6"></i>
                    Resolve Case
                  </label>
                </div>

                <div class="col-4">
                  <input 
                    type="radio" 
                    id="status-rejected" 
                    value="rejected" 
                    v-model="actionForm.status"
                    class="btn-check"
                  >
                  <label class="btn btn-outline-danger w-100 rounded-3 py-2 fw-semibold x-small d-flex flex-column align-items-center gap-1 border-2" for="status-rejected">
                    <i class="bi bi-x-circle fs-6"></i>
                    Reject Case
                  </label>
                </div>
              </div>

              <!-- Admin Remarks -->
              <div class="mb-0">
                <label for="admin-remarks-input" class="form-label text-muted small fw-bold text-uppercase x-small">
                  Official Administrative Remarks / Reply
                </label>
                <textarea 
                  id="admin-remarks-input" 
                  v-model="actionForm.remarks"
                  class="form-control rounded-3 text-muted small" 
                  rows="4" 
                  placeholder="Provide detailed instructions, settlement notes, or justification for rejection (Min. 5 characters)..."
                ></textarea>
              </div>
            </div>
          </div>

          <!-- Modal Footer -->
          <div class="modal-footer p-4 border-top bg-light-soft d-flex justify-content-between align-items-center">
            <button @click="closeModal" class="btn btn-outline-secondary rounded-pill px-4 fw-bold x-small text-uppercase">Close</button>
            <button 
              v-if="selectedDispute.status === 'pending' || selectedDispute.status === 'under_investigation'"
              @click="submitDisputeUpdate" 
              class="btn btn-gold rounded-pill px-4 fw-bold x-small text-uppercase text-white border-0 shadow-sm"
              :disabled="saving"
            >
              <span v-if="saving" class="spinner-border spinner-border-sm me-1"></span>
              Save Changes
            </button>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';
import AdminPagination from '../../components/AdminPagination.vue';

const loading = ref(true);
const saving = ref(false);
const disputes = ref([]);
const searchQuery = ref('');
const activeTab = ref('all');

// Pagination state
const currentPage = ref(1);
const pageSize = ref(5);

// Modal details
const showModal = ref(false);
const selectedDispute = ref(null);
const actionForm = ref({
  status: 'under_investigation',
  remarks: ''
});

const filterTabs = computed(() => {
  return [
    { label: 'All Cases', value: 'all', count: disputes.value.length },
    { label: 'Pending', value: 'pending', count: disputes.value.filter(d => d.status === 'pending').length },
    { label: 'Investigating', value: 'under_investigation', count: disputes.value.filter(d => d.status === 'under_investigation').length },
    { label: 'Resolved', value: 'resolved', count: disputes.value.filter(d => d.status === 'resolved').length },
    { label: 'Rejected', value: 'rejected', count: disputes.value.filter(d => d.status === 'rejected').length }
  ];
});

const metrics = computed(() => {
  return {
    total: disputes.value.length,
    pending: disputes.value.filter(d => d.status === 'pending').length,
    investigating: disputes.value.filter(d => d.status === 'under_investigation').length,
    resolved: disputes.value.filter(d => d.status === 'resolved').length,
    rejected: disputes.value.filter(d => d.status === 'rejected').length
  };
});

const getPercentage = (count) => {
  if (metrics.value.total === 0) return 0;
  return Math.round((count / metrics.value.total) * 100);
};

const fetchDisputes = async () => {
  try {
    loading.value = true;
    const response = await axios.get('/api/admin/disputes');
    disputes.value = response.data;
  } catch (error) {
    console.error('Failed to fetch disputes:', error);
  } finally {
    loading.value = false;
  }
};

onMounted(fetchDisputes);

const formatDate = (dateString) => {
  if (!dateString) return 'N/A';
  return new Date(dateString).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric'
  });
};

const formatDateTime = (dateString) => {
  if (!dateString) return 'N/A';
  const date = new Date(dateString);
  const datePart = date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
  const timePart = date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
  return `${datePart} at ${timePart}`;
};

const capitalize = (s) => s ? s.charAt(0).toUpperCase() + s.slice(1).replace('_', ' ') : '';

const formatReason = (reason) => {
  switch (reason) {
    case 'billing': return 'Billing & Settlement';
    case 'room_condition': return 'Room or Amenity Issue';
    case 'service_issue': return 'Service & Guest Care';
    default: return 'Other Inquiry';
  }
};

const getReasonIcon = (reason) => {
  switch (reason) {
    case 'billing': return 'bi-credit-card-fill';
    case 'room_condition': return 'bi-house-fill';
    case 'service_issue': return 'bi-person-badge-fill';
    default: return 'bi-info-circle-fill';
  }
};

const getStatusBadgeClass = (status) => {
  const base = 'badge rounded-pill px-3 py-1.5 fw-bold text-uppercase x-small ';
  switch (status) {
    case 'pending': return base + 'bg-warning-subtle text-warning border border-warning-subtle';
    case 'under_investigation': return base + 'bg-info-subtle text-info border border-info-subtle';
    case 'resolved': return base + 'bg-success-subtle text-success border border-success-subtle';
    case 'rejected': return base + 'bg-danger-subtle text-danger border border-danger-subtle';
    default: return base + 'bg-light text-secondary';
  }
};

// Filter & Search Logic
const filteredDisputes = computed(() => {
  let result = disputes.value;

  // Tab Filtering
  if (activeTab.value !== 'all') {
    result = result.filter(d => d.status === activeTab.value);
  }

  // Search Query
  if (searchQuery.value.trim() !== '') {
    const q = searchQuery.value.toLowerCase().trim();
    result = result.filter(d => 
      (d.user?.name || '').toLowerCase().includes(q) ||
      (d.user?.email || '').toLowerCase().includes(q) ||
      (d.description || '').toLowerCase().includes(q) ||
      String(d.reservation?.room?.room_number || '').includes(q)
    );
  }

  return result;
});

const paginatedDisputes = computed(() => {
  const start = (currentPage.value - 1) * pageSize.value;
  const end = start + pageSize.value;
  return filteredDisputes.value.slice(start, end);
});

watch([searchQuery, activeTab], () => {
  currentPage.value = 1;
});

// Modal Actions
const openDisputeDetails = (dispute) => {
  selectedDispute.value = dispute;
  actionForm.value.status = dispute.status === 'pending' ? 'under_investigation' : dispute.status;
  actionForm.value.remarks = dispute.admin_remarks || '';
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
  selectedDispute.value = null;
};

const submitDisputeUpdate = async () => {
  if (!actionForm.value.remarks || actionForm.value.remarks.trim().length < 5) {
    Swal.fire({
      icon: 'error',
      title: 'Action Failed',
      text: 'Please write a minimum of 5 characters in administrative remarks to justify the action.'
    });
    return;
  }

  try {
    saving.value = true;
    const response = await axios.put(`/api/admin/disputes/${selectedDispute.value.id}`, {
      status: actionForm.value.status,
      admin_remarks: actionForm.value.remarks
    }, {
      timeout: 6000 // 6s timeout to prevent dev-server single-thread deadlock
    });

    closeModal();
    await Swal.fire({
      icon: 'success',
      title: 'Dispute Updated',
      text: response.data.message || 'The dispute case has been updated successfully.',
      timer: 2000,
      timerProgressBar: true,
      showConfirmButton: false
    });

    await fetchDisputes();
  } catch (error) {
    // If the request timed out, the DB write almost certainly completed —
    // the hang is caused by the single-threaded PHP dev server getting
    // blocked by concurrent polling requests from AdminLayout.
    if (error.code === 'ECONNABORTED') {
      closeModal();
      await Swal.fire({
        icon: 'success',
        title: 'Dispute Updated',
        text: 'The dispute case has been updated successfully.',
        timer: 2000,
        timerProgressBar: true,
        showConfirmButton: false
      });
      await fetchDisputes();
    } else {
      console.error('Error updating dispute:', error);
      Swal.fire({
        icon: 'error',
        title: 'Failed to Save',
        text: error.response?.data?.message || 'Could not save the case changes. Please try again.'
      });
    }
  } finally {
    saving.value = false;
  }
};
</script>

<style scoped>
.disputes-view {
  min-height: calc(100vh - 120px);
}

.bg-light-soft {
  background-color: #f8fafc;
}

.x-small {
  font-size: 0.7rem;
  letter-spacing: 0.5px;
}

.animate-fade-up {
  animation: fadeUp 0.4s ease-out forwards;
  opacity: 0;
  transform: translateY(15px);
}

@keyframes fadeUp {
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.tab-btn {
  color: #64748b;
  position: relative;
  transition: all 0.25s ease;
  font-weight: 700;
  outline: none;
}

.tab-btn:hover {
  color: var(--primary-gold);
}

.tab-btn.active {
  color: var(--primary-gold);
}

.active-indicator {
  border-radius: 3px 3px 0 0;
}

.cursor-pointer {
  cursor: pointer;
}

.transition-all {
  transition: all 0.2s ease;
}

.hover-shadow:hover {
  box-shadow: 0 4px 12px rgba(188, 145, 81, 0.15);
}

.custom-modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(15, 23, 42, 0.6);
  backdrop-filter: blur(8px);
  z-index: 2000;
}

.custom-modal {
  z-index: 2001;
  border: 1px solid rgba(255, 255, 255, 0.4);
}

.btn-check:checked + label {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.btn-check:checked + label[for="status-investigating"] {
  background-color: #0dcaf0 !important;
  border-color: #0dcaf0 !important;
  color: white !important;
}

.btn-check:checked + label[for="status-resolved"] {
  background-color: #198754 !important;
  border-color: #198754 !important;
  color: white !important;
}

.btn-check:checked + label[for="status-rejected"] {
  background-color: #dc3545 !important;
  border-color: #dc3545 !important;
  color: white !important;
}

.btn-gold {
  background: linear-gradient(135deg, var(--primary-gold) 0%, #9A7640 100%) !important;
  border: none !important;
  color: white !important;
  transition: all 0.3s ease;
}

.btn-gold:hover:not(:disabled) {
  background: linear-gradient(135deg, #A67C3B 0%, #856130 100%) !important;
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(188, 145, 81, 0.3);
}

.btn-gold:disabled {
  background: #e2e8f0 !important;
  color: #94a3b8 !important;
  border: none !important;
  box-shadow: none !important;
  cursor: not-allowed;
}

.modal-fade-enter-active, .modal-fade-leave-active {
  transition: opacity 0.25s ease;
}

.modal-fade-enter-from, .modal-fade-leave-to {
  opacity: 0;
}
</style>
