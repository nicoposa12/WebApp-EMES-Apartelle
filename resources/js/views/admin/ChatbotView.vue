<template>
  <div class="chatbot-view animate-fade-up">
    <!-- Header Section -->
    <div class="d-flex align-items-center justify-content-between mb-4">
      <div>
        <h4 class="serif-font fw-bold mb-1 text-secondary-dark">Chatbot Automation</h4>
        <div class="d-flex align-items-center gap-2">
          <span class="badge bg-gold-subtle text-gold rounded-pill px-3 py-1 shadow-sm fw-bold" style="font-size: 0.7rem;">{{ rules.length }} RULES ACTIVE</span>
          <p class="text-muted small mb-0">Set up automated responses based on guest keywords.</p>
        </div>
      </div>
      <button @click="openModal()" class="btn btn-gold rounded-pill px-4 py-2 shadow-sm transition-all hover-scale d-flex align-items-center gap-2">
        <i class="bi bi-plus-lg fs-5"></i> 
        <span class="fw-bold">Add New Rule</span>
      </button>
    </div>

    <!-- Main Content Card -->
    <div class="card border-0 shadow-lg rounded-4 overflow-hidden bg-white">
      <div class="card-body p-0 d-flex flex-column" style="min-height: 480px;">
        <div class="table-responsive flex-grow-1 custom-scrollbar" style="max-height: 600px; overflow-y: auto;">
          <table class="table table-hover align-middle mb-0">
            <thead>
              <tr class="bg-light-soft border-bottom">
                <th class="ps-4 py-4 text-uppercase x-small fw-bold text-muted" style="width: 200px;">Trigger Keyword</th>
                <th class="py-4 text-uppercase x-small fw-bold text-muted">Automated Response</th>
                <th class="py-4 text-uppercase x-small fw-bold text-muted text-center" style="width: 150px;">Match Type</th>
                <th class="py-4 text-uppercase x-small fw-bold text-muted text-center" style="width: 100px;">Status</th>
                <th class="pe-4 py-4 text-end text-uppercase x-small fw-bold text-muted" style="width: 120px;">Actions</th>
              </tr>
            </thead>
            <tbody class="bg-dots">
              <tr v-for="rule in paginatedRules" :key="rule.id" class="rule-row transition-all bg-white-hover">
                <td class="ps-4 py-4">
                  <div class="trigger-badge">
                    <span class="badge bg-premium-dark text-white rounded-pill px-3 py-2 fw-bold shadow-sm">
                       <i class="bi bi-tag-fill me-1 opacity-50"></i> {{ rule.trigger }}
                    </span>
                  </div>
                </td>
                <td class="py-4">
                  <div class="response-cell bg-light-soft p-3 rounded-4 shadow-sm border border-light"> 
                    <p class="mb-0 text-secondary-dark small fw-bold" style="line-height: 1.6;">
                      {{ rule.response }}
                    </p>
                    <div v-if="rule.suggested_triggers" class="mt-2">
                       <div class="d-flex flex-wrap gap-1">
                          <span v-for="tag in rule.suggested_triggers.split(',')" :key="tag" class="badge rounded-pill bg-white text-gold border border-gold-subtle x-small fw-normal">
                             {{ tag.trim() }}
                          </span>
                       </div>
                    </div>
                  </div>
                </td>
                <td class="py-4 text-center">
                  <span class="badge rounded-pill px-3 py-2 text-capitalize fw-bold shadow-sm" 
                        :class="rule.match_type === 'exact' ? 'bg-gold-subtle text-gold' : 'bg-primary-subtle text-primary'">
                    <i :class="rule.match_type === 'exact' ? 'bi bi-bullseye' : 'bi bi-search'" class="me-1"></i>
                    {{ rule.match_type }}
                  </span>
                </td>
                <td class="py-4 text-center">
                  <div class="form-check form-switch d-flex justify-content-center">
                    <input class="form-check-input switch-premium" type="checkbox" role="switch" 
                           :checked="rule.is_active" 
                           @change="toggleStatus(rule)">
                  </div>
                </td>
                <td colspan="5" class="pe-4 py-4 text-end">
                  <div class="d-flex justify-content-end gap-2">
                    <button @click="openModal(rule)" class="btn btn-icon-action bg-gold-subtle text-gold" title="Edit">
                      <i class="bi bi-pencil-square"></i>
                    </button>
                    <button @click="confirmDelete(rule)" class="btn btn-icon-action bg-danger-subtle text-danger" title="Delete">
                      <i class="bi bi-trash3-fill"></i>
                    </button>
                  </div>
                </td>
              </tr>

              <!-- Empty State -->
              <tr v-if="rules.length === 0 && !loading">
                <td colspan="5" class="text-center py-5 bg-white">
                  <div class="empty-state-container p-5">
                    <div class="bg-light-soft d-inline-block p-5 rounded-circle mb-4 shadow-sm border border-light animate-float">
                      <i class="bi bi-robot fs-1 text-gold opacity-75"></i>
                    </div>
                    <h5 class="text-secondary-dark fw-bold serif-font mb-2">No chatbot rules found</h5>
                    <p class="text-muted small max-w-sm mx-auto">Create your first automated response to get started with seamless guest communication.</p>
                    <button @click="openModal()" class="btn btn-gold rounded-pill px-4 py-2 mt-3 shadow-sm fw-bold">
                        Create Your First Rule
                    </button>
                  </div>
                </td>
              </tr>

              <!-- Loading State -->
              <tr v-if="loading">
                 <td colspan="5" class="text-center py-5 bg-white">
                    <div class="spinner-border text-gold" style="width: 3rem; height: 3rem;" role="status"></div>
                    <p class="mt-3 text-muted fw-bold x-small tracking-widest text-uppercase">Analyzing responses...</p>
                 </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <AdminPagination 
          v-if="rules.length > 0"
          :current-page="currentPage"
          :total-items="rules.length"
          :page-size="pageSize"
          @change="currentPage = $event"
          @page-size-change="(s) => { pageSize = s; currentPage = 1; }"
        />
      </div>
    </div>

    <!-- Rule Modal -->
    <transition name="fade">
      <div v-if="showModal" class="modal-backdrop-custom d-flex align-items-center justify-content-center" @click.self="showModal = false">
        <div class="modal-content-custom bg-white rounded-4 shadow-xl p-0 overflow-hidden animate-scale-up" style="max-width: 550px; width: 90%;">
          <!-- Modal Header -->
          <div class="bg-premium-dark p-4 text-white d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-3">
              <div class="bg-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 40px; height: 40px;">
                <i class="bi bi-robot fs-5" style="color: #0f172a;"></i>
              </div>
              <h5 class="serif-font fw-bold mb-0 text-white">{{ editingRule ? 'Edit Chatbot Rule' : 'New Chatbot Rule' }}</h5>
            </div>
            <button @click="showModal = false" class="btn btn-link text-white p-0 opacity-75 hover-opacity-100 transition-all">
              <i class="bi bi-x-lg"></i>
            </button>
          </div>

          <!-- Modal Body -->
          <div class="p-4 bg-dots custom-scrollbar" style="max-height: 60vh; overflow-y: auto;">
            <form @submit.prevent="saveRule">
              <div class="row g-3">
                <div class="col-md-7">
                  <label class="form-label small fw-bold text-secondary-dark mb-2">Trigger Keyword</label>
                  <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 rounded-start-4 ps-3 text-muted">
                      <i class="bi bi-tag-fill"></i>
                    </span>
                    <input v-model="form.trigger" type="text" 
                           class="form-control border-start-0 rounded-end-4 py-2.5 ps-1 focus-within-premium shadow-none" 
                           placeholder="e.g. WiFi, Price, Location" required>
                  </div>
                </div>

                <div class="col-md-5">
                  <label class="form-label small fw-bold text-secondary-dark mb-2">Match Type</label>
                  <select v-model="form.match_type" class="form-select rounded-4 py-2.5 shadow-none focus-within-premium">
                    <option value="contains">Contains Word</option>
                    <option value="exact">Exact Match</option>
                  </select>
                </div>

                <div class="col-12 mt-4">
                  <label class="form-label small fw-bold text-secondary-dark mb-2 d-flex align-items-center justify-content-between">
                    <span>Automated Response</span>
                    <span v-if="detectedKeywordsInResponse.length > 0" class="badge bg-gold-subtle text-gold rounded-pill px-2 py-1 x-small fw-bold animate-pulse-slow">
                       <i class="bi bi-lightning-fill me-1"></i> {{ detectedKeywordsInResponse.length }} Triggers Detected
                    </span>
                  </label>
                  <textarea v-model="form.response" 
                            class="form-control rounded-4 py-3 shadow-none focus-within-premium" 
                            :class="{'border-gold shadow-gold-sm': detectedKeywordsInResponse.length > 0}"
                            rows="4" 
                            placeholder="Type the automated reply here..." required></textarea>
                  
                  <!-- Detected Keywords Display -->
                  <div v-if="detectedKeywordsInResponse.length > 0" class="mt-2 pt-2 border-top border-light-soft animate-fade-in">
                    <div class="d-flex flex-wrap gap-1">
                       <span v-for="keyword in detectedKeywordsInResponse" :key="keyword" 
                             class="keyword-chip badge rounded-pill bg-gold text-white px-2 py-1 x-small fw-bold shadow-sm d-flex align-items-center gap-1">
                          {{ keyword }}
                          <button type="button" @click="addDetectedToSuggestions(keyword)" class="btn btn-link p-0 text-white leading-none hover-scale-lg" title="Add to suggestions">
                             <i class="bi bi-plus-circle-fill"></i>
                          </button>
                       </span>
                    </div>
                  </div>
                </div>

                <div class="col-12 mt-3">
                  <label class="form-label small fw-bold text-secondary-dark mb-2">Suggested Triggers (Optional)</label>
                  <input v-model="form.suggested_triggers" type="text" 
                         class="form-control rounded-4 py-2.5 focus-within-premium shadow-none" 
                         placeholder="e.g. WiFi, Location, Menu">
                  <div class="form-text x-small text-muted mt-1">Comma-separated keywords to suggest as clickable buttons.</div>
                </div>

                <div class="col-12 mt-4">
                  <div class="bg-white p-3 rounded-4 border d-flex align-items-center justify-content-between shadow-sm">
                    <div class="d-flex align-items-center gap-2">
                      <i class="bi bi-power text-gold"></i>
                      <span class="small fw-bold text-secondary-dark">Active Status</span>
                    </div>
                    <div class="form-check form-switch mb-0">
                      <input class="form-check-input switch-premium" type="checkbox" v-model="form.is_active" style="width: 3em; height: 1.5em;">
                    </div>
                  </div>
                </div>

                <div class="col-12 mt-4 d-flex gap-3">
                  <button type="button" @click="showModal = false" class="btn btn-light rounded-pill px-4 py-2.5 flex-grow-1 fw-bold transition-all shadow-sm">
                    Cancel
                  </button>
                  <button type="submit" class="btn btn-gold rounded-pill px-4 py-2.5 flex-grow-2 fw-bold transition-all shadow-gold d-flex align-items-center justify-content-center gap-2" :disabled="saving">
                    <span v-if="saving" class="spinner-border spinner-border-sm"></span>
                    <i v-else class="bi bi-check2-circle fs-5"></i>
                    {{ editingRule ? 'Update Rule' : 'Create Rule' }}
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';
import AdminPagination from '../../components/AdminPagination.vue';

const rules = ref([]);
const loading = ref(false);
const saving = ref(false);
const showModal = ref(false);
const editingRule = ref(null);

// Pagination State
const currentPage = ref(1);
const pageSize = ref(5);

const paginatedRules = computed(() => {
  const start = (currentPage.value - 1) * pageSize.value;
  const end = start + pageSize.value;
  return rules.value.slice(start, end);
});

const form = ref({
  trigger: '',
  response: '',
  suggested_triggers: '',
  match_type: 'contains',
  is_active: true
});

// Real-time Keyword Detection
const allTriggerKeywords = computed(() => {
  const set = new Set();
  rules.value.forEach(r => {
    r.trigger.split(',').map(t => t.trim().toLowerCase()).forEach(t => {
      if (t) set.add(t);
    });
  });
  return Array.from(set);
});

const detectedKeywordsInResponse = computed(() => {
  const msg = (form.value.response || "").toLowerCase();
  if (!msg) return [];
  return allTriggerKeywords.value.filter(keyword => {
    const pattern = new RegExp(`\\b${keyword}\\b`, 'i');
    return pattern.test(msg);
  });
});

const addDetectedToSuggestions = (keyword) => {
  const current = (form.value.suggested_triggers || "").split(',').map(t => t.trim().toLowerCase());
  if (!current.includes(keyword.toLowerCase())) {
     const newValue = form.value.suggested_triggers ? `${form.value.suggested_triggers}, ${keyword}` : keyword;
     form.value.suggested_triggers = newValue;
  }
};

const fetchRules = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/api/admin/chatbot-responses');
    rules.value = response.data;
  } catch (err) {
    console.error('Failed to fetch rules', err);
  } finally {
    loading.value = false;
  }
};

const openModal = (rule = null) => {
  if (rule) {
    editingRule.value = rule;
    form.value = { ...rule, is_active: !!rule.is_active };
  } else {
    editingRule.value = null;
    form.value = {
      trigger: '',
      response: '',
      suggested_triggers: '',
      match_type: 'contains',
      is_active: true
    };
  }
  showModal.value = true;
};

const saveRule = async () => {
  saving.value = true;
  try {
    if (editingRule.value) {
      await axios.put(`/api/admin/chatbot-responses/${editingRule.value.id}`, form.value);
      Swal.fire({ 
        icon: 'success', 
        title: 'Updated', 
        text: 'Chatbot rule updated successfully.', 
        timer: 1500, 
        showConfirmButton: false,
        background: '#f8fafc',
        iconColor: '#bc9151'
      });
    } else {
      await axios.post('/api/admin/chatbot-responses', form.value);
      Swal.fire({ 
        icon: 'success', 
        title: 'Created', 
        text: 'New chatbot rule created.', 
        timer: 1500, 
        showConfirmButton: false,
        background: '#f8fafc',
        iconColor: '#bc9151'
      });
    }
    showModal.value = false;
    fetchRules();
  } catch (err) {
    console.error('Failed to save rule', err);
    Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to save rule. Please try again.' });
  } finally {
    saving.value = false;
  }
};

const toggleStatus = async (rule) => {
  try {
    await axios.put(`/api/admin/chatbot-responses/${rule.id}`, {
      ...rule,
      is_active: !rule.is_active
    });
    fetchRules();
  } catch (err) {
    console.error('Failed to toggle status', err);
  }
};

const confirmDelete = async (rule) => {
  const result = await Swal.fire({
    title: 'Are you sure?',
    text: `Delete the rule for "${rule.trigger}"?`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#dc3545',
    cancelButtonColor: '#718096',
    confirmButtonText: 'Yes, delete it!',
    background: '#f8fafc',
    iconColor: '#dc3545'
  });

  if (result.isConfirmed) {
    try {
      await axios.delete(`/api/admin/chatbot-responses/${rule.id}`);
      fetchRules();
      Swal.fire({
        title: 'Deleted!',
        text: 'Rule has been deleted.',
        icon: 'success',
        timer: 1500,
        showConfirmButton: false
      });
    } catch (err) {
      console.error('Failed to delete', err);
    }
  }
};

onMounted(fetchRules);
</script>

<style scoped>
.chatbot-view {
  min-height: calc(100vh - 150px);
}

.bg-premium-dark {
  background-color: #0f172a !important;
}

.bg-light-soft {
  background-color: #f8fafc;
}

.bg-dots {
  background-image: radial-gradient(#e2e8f0 1.5px, transparent 1.5px);
  background-size: 24px 24px;
}

.modal-backdrop-custom {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100vh;
  background: rgba(15, 23, 42, 0.4);
  backdrop-filter: blur(8px);
  z-index: 1050;
  display: flex;
  align-items: center; /* Vertical center */
  justify-content: center; /* Horizontal center */
  padding: 2rem;
  overflow-y: auto;
}

.bg-gold-subtle {
  background-color: rgba(188, 145, 81, 0.1);
}

.x-small {
  font-size: 0.72rem;
  letter-spacing: 0.8px;
}

.rule-row {
  border-bottom: 1px solid #f1f5f9;
}

.bg-white-hover:hover {
  background-color: #f8fafc !important;
}

.btn-icon-action {
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 12px;
  border: none;
  transition: all 0.2s;
}

.btn-icon-action:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.switch-premium:checked {
  background-color: #bc9151;
  border-color: #bc9151;
}

.focus-within-premium:focus {
  border-color: #bc9151 !important;
  box-shadow: 0 0 0 4px rgba(188, 145, 81, 0.1) !important;
}

.animate-float {
  animation: float 4s ease-in-out infinite;
}

@keyframes float {
  0% { transform: translateY(0px); }
  50% { transform: translateY(-15px); }
  100% { transform: translateY(0px); }
}

.animate-scale-up {
  animation: scaleUp 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}

@keyframes scaleUp {
  from { opacity: 0; transform: scale(0.9) translateY(20px); }
  to { opacity: 1; transform: scale(1) translateY(0px); }
}

.hover-scale:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(15, 23, 42, 0.2) !important;
}

.flex-grow-2 {
  flex-grow: 2;
}

.shadow-gold {
  box-shadow: 0 4px 12px rgba(188, 145, 81, 0.2);
}

.shadow-gold-sm {
  box-shadow: 0 4px 12px rgba(188, 145, 81, 0.1);
}

.border-gold {
  border-color: #bc9151 !important;
}

.animate-pulse-slow {
   animation: pulse-slow 3s infinite;
}

@keyframes pulse-slow {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.6; }
}

.hover-scale-lg:hover {
  transform: scale(1.2);
}

.max-w-sm {
  max-width: 450px;
}

.transition-all {
  transition: all 0.3s ease;
}

.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}

/* Custom Table Scrollbar */
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background-color: #cbd5e1;
  border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background-color: #f8fafc;
}

/* Gold Button Styles */
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

.btn-gold:disabled {
  background: #cbd5e1 !important;
  transform: none;
  box-shadow: none;
}
</style>
