<template>
  <div class="payments-view">
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden animate-fade-up">
      <div class="card-header bg-white py-4 px-4 border-0 d-flex justify-content-between align-items-center">
        <h5 class="serif-font fw-bold mb-0 text-secondary-dark">Payment Transactions</h5>
      </div>
      <div class="card-body p-0">
        <div v-if="loading" class="text-center py-5">
          <div class="spinner-border text-gold" role="status"></div>
          <p class="text-muted mt-2 small">Loading transactions...</p>
        </div>
        <div v-else class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead>
              <tr>
                <th class="ps-4 border-0 text-muted small fw-bold text-uppercase">Transaction ID</th>
                <th class="border-0 text-muted small fw-bold text-uppercase">Guest</th>
                <th class="border-0 text-muted small fw-bold text-uppercase">Method</th>
                <th class="border-0 text-muted small fw-bold text-uppercase">Amount</th>
                <th class="border-0 text-muted small fw-bold text-uppercase">Date</th>
                <th class="pe-4 border-0 text-muted small fw-bold text-uppercase text-end">Status</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="pay in paginatedPayments" :key="pay.id">
                <td class="ps-4 py-3"><span class="small fw-bold text-secondary-dark">#TXN-{{ pay.id }}</span></td>
                <td>
                  <div class="d-flex flex-column" v-if="pay.reservation?.user">
                    <span class="small fw-bold text-secondary-dark">{{ pay.reservation.user.name }}</span>
                    <small class="text-muted" v-if="pay.reservation.user.phone">
                      <i class="bi bi-telephone small me-1"></i>{{ pay.reservation.user.phone }}
                    </small>
                  </div>
                  <span v-else class="small text-muted italic">No Guest Info</span>
                </td>
                <td>
                  <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-credit-card-2-back text-muted opacity-50"></i>
                    <span class="small fw-medium">{{ mapPaymentMethod(pay.method) }}</span>
                  </div>
                </td>
                <td><span class="fw-bold text-secondary-dark">₱{{ formatAmount(pay.amount) }}</span></td>
                <td><span class="small text-muted">{{ formatDate(pay.created_at) }}</span></td>
                <td class="pe-4 text-end">
                   <span class="badge rounded-pill fw-bold text-uppercase" :class="getStatusClass(pay.status)" style="font-size: 0.65rem;">
                     {{ pay.status }}
                   </span>
                </td>
              </tr>
              <tr v-if="filteredPayments.length === 0">
                <td colspan="5" class="text-center py-5 text-muted">No transactions found for this filter.</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <AdminPagination 
          :current-page="currentPage" 
          :total-items="filteredPayments.length" 
          :page-size="pageSize"
          @change="page => currentPage = page"
          @page-size-change="size => { pageSize = size; currentPage = 1; }"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import AdminPagination from '../../components/AdminPagination.vue';
import axios from 'axios';

const filter = ref('all'); 
const searchQuery = ref('');
const currentPage = ref(1);
const pageSize = ref(5);
const loading = ref(true);
const payments = ref([]);

const fetchPayments = async () => {
  try {
    loading.value = true;
    const response = await axios.get('/api/admin/payments');
    payments.value = response.data;
  } catch (error) {
    console.error('Failed to fetch payments', error);
  } finally {
    loading.value = false;
  }
};

onMounted(fetchPayments);

const formatAmount = (amount) => {
  return parseFloat(amount || 0).toLocaleString();
};

const formatDate = (dateString) => {
  if (!dateString) return 'N/A';
  const date = new Date(dateString);
  const datePart = date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
  const timePart = date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
  return `${datePart} ${timePart}`;
};

const getStatusClass = (status) => {
  const s = String(status).toLowerCase();
  if (s === 'succeeded' || s === 'successful' || s === 'paid') {
    return 'bg-success-subtle text-success';
  }
  if (s === 'failed' || s === 'cancelled') {
    return 'bg-danger-subtle text-danger';
  }
  return 'bg-warning-subtle text-warning';
};

const mapPaymentMethod = (method) => {
  if (!method) return 'N/A';
  const m = method.toLowerCase();
  const map = {
    'gcash': 'GCash (E-Wallet)',
    'grab_pay': 'GrabPay (E-Wallet)',
    'maya': 'Maya / PayMaya',
    'paymaya': 'Maya / PayMaya',
    'card': 'Credit/Debit Card',
    'credit_card': 'Credit/Debit Card',
    'qrph': 'QRPh / QR Code',
    'qr_code': 'QR Code',
    'ewallet': 'E-Wallet',
    'dob': 'Direct Online Banking',
    'dob_ubp': 'UnionBank (Online)',
    'cash/manual': 'Cash (Walk-in)',
    'checkout_session': 'Online Payment',
    'xendit': 'Online Settlement (Xendit)'
  };
  return map[m] || method.toUpperCase();
};

const filteredPayments = computed(() => {
  return payments.value;
});

const paginatedPayments = computed(() => {
  const start = (currentPage.value - 1) * pageSize.value;
  const end = start + pageSize.value;
  return filteredPayments.value.slice(start, end);
});

// Reset to page 1 when searching or filtering
watch([searchQuery, filter], () => {
  currentPage.value = 1;
});
</script>

<style scoped>
.btn-white {
  background: white !important;
  color: var(--primary-gold) !important;
  font-weight: 700;
}
.btn-transparent {
  background: transparent !important;
  color: #64748B !important;
  font-weight: 500;
}
.table th {
  background-color: transparent !important;
}
</style>
