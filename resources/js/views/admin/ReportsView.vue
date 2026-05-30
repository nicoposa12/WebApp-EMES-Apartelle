<template>
  <div class="reports-view p-1">
    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border text-gold" role="status"></div>
      <p class="text-muted mt-2">Generating your analytical reports...</p>
    </div>
    
    <div v-else>
      <div class="row g-4 mb-4">
        <div v-for="(stat, index) in summaryStats" :key="index" class="col-md-3">
          <div class="card border-0 shadow-sm rounded-4 animate-fade-up" :style="{ animationDelay: (index * 0.1) + 's' }">
            <div class="card-body p-4">
              <div class="d-flex align-items-center mb-2">
                <div class="icon-box rounded-3 me-3" :class="stat.bgClass">
                  <i :class="['bi', stat.icon]"></i>
                </div>
                <span class="text-muted small fw-bold text-uppercase">{{ stat.label }}</span>
              </div>
              <h3 class="mb-0 fw-bold">{{ stat.value }}</h3>
            </div>
          </div>
        </div>
      </div>

      <div class="row g-4">
        <!-- Monthly Revenue Chart -->
        <div class="col-lg-8">
          <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
            <div class="card-header bg-white py-4 px-4 border-0">
              <h5 class="serif-font fw-bold mb-0 text-secondary-dark">Financial Performance</h5>
            <p class="text-muted small mb-0">Historical revenue growth since start of operations</p>
          </div>
          <div class="card-body p-4 pt-0">
             <apexchart v-if="reportsData.monthly_revenue.length > 0" type="area" height="350" :options="revenueChartOptions" :series="revenueChartSeries"></apexchart>
             <div v-else class="text-center py-5 text-muted small">No financial data recorded yet.</div>
          </div>
          </div>
        </div>

        <!-- Booking Distribution -->
        <div class="col-lg-4">
          <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
            <div class="card-header bg-white py-4 px-4 border-0">
              <h5 class="serif-font fw-bold mb-0 text-secondary-dark">Booking Status</h5>
              <p class="text-muted small mb-0">Lifetime distribution of all reservations</p>
            </div>
            <div class="card-body p-4 pt-0">
               <apexchart v-if="reportsData.status_distribution.length > 0" type="donut" height="350" :options="statusChartOptions" :series="statusChartSeries"></apexchart>
               <div v-else class="text-center py-5 text-muted small">No status data available.</div>
            </div>
          </div>
        </div>

        <!-- Weekly Trends -->
        <div class="col-lg-6">
          <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white py-4 px-4 border-0">
              <h5 class="serif-font fw-bold mb-0 text-secondary-dark">Weekly Booking Trends</h5>
              <p class="text-muted small mb-0">Bookings created in the last 7 days</p>
            </div>
            <div class="card-body p-4 pt-0">
               <apexchart type="bar" height="300" :options="weeklyChartOptions" :series="weeklyChartSeries"></apexchart>
            </div>
          </div>
        </div>

        <!-- Room Revenue Distribution -->
        <div class="col-lg-6">
          <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white py-4 px-4 border-0">
              <h5 class="serif-font fw-bold mb-0 text-secondary-dark">Revenue by Room Type</h5>
              <p class="text-muted small mb-0">Financial contribution per category</p>
            </div>
            <div class="card-body p-4 pt-0">
               <apexchart type="pie" height="300" :options="roomRevenueChartOptions" :series="roomRevenueChartSeries"></apexchart>
            </div>
          </div>
        </div>

        <!-- Room Performance Table -->
        <div class="col-lg-12">
          <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
             <div class="card-header bg-white py-4 px-4 border-0">
               <h5 class="serif-font fw-bold mb-0 text-secondary-dark">Room Performance Analysis</h5>
               <p class="text-muted small mb-0">Detailed list of bookings and revenue per room type</p>
            </div>
            <div class="card-body p-0">
              <div class="table-responsive">
                 <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                      <tr>
                        <th class="ps-4 border-0 text-muted small fw-bold text-uppercase">Room Type</th>
                        <th class="border-0 text-muted small fw-bold text-uppercase text-center">Total Bookings</th>
                        <th class="border-0 text-muted small fw-bold text-uppercase text-end pe-4">Total Revenue</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="room in reportsData.room_performance" :key="room.room_type">
                        <td class="ps-4 py-3"><span class="fw-bold">{{ room.room_type }}</span></td>
                        <td class="text-center">{{ room.count }}</td>
                        <td class="text-end pe-4"><span class="fw-bold text-gold">₱{{ (parseFloat(room.total_revenue) || 0).toLocaleString() }}</span></td>
                      </tr>
                    </tbody>
                 </table>
              </div>
            </div>
          </div>
        </div>

        <!-- Detailed Breakdown Section -->
        <div class="col-lg-12 pb-5">
           <div class="d-flex align-items-center justify-content-between mb-3">
              <h4 class="serif-font fw-bold mb-0 text-secondary-dark">Detailed Data Breakdown</h4>
              <button @click="downloadCSV" class="btn btn-gold btn-sm rounded-pill px-3">
                <i class="bi bi-download me-2"></i>Export CSV
              </button>
           </div>

           <div class="row g-4">
              <!-- Monthly Revenue Table -->
              <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
                  <div class="card-header bg-white py-3 px-4 border-0 border-bottom">
                    <h6 class="fw-bold mb-0">Monthly Revenue History</h6>
                  </div>
                  <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                      <thead class="table-light">
                        <tr class="small text-uppercase text-muted">
                           <th class="ps-4">Month</th>
                           <th class="text-end pe-4">Revenue</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="m in reportsData.monthly_revenue" :key="m.month">
                          <td class="ps-4 py-2">{{ m.month }}</td>
                          <td class="text-end pe-4">₱{{ m.revenue.toLocaleString() }}</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>

              <!-- Status Count Table -->
              <div class="col-md-6">
                 <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
                  <div class="card-header bg-white py-3 px-4 border-0 border-bottom">
                    <h6 class="fw-bold mb-0">Status Summary</h6>
                  </div>
                  <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                      <thead class="table-light">
                        <tr class="small text-uppercase text-muted">
                           <th class="ps-4">Status</th>
                           <th class="text-end pe-4">Booking Count</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="s in reportsData.status_distribution" :key="s.status">
                          <td class="ps-4 py-2">
                            <span class="badge rounded-pill px-2" :class="getStatusBadgeClass(s.status)">{{ s.status.toUpperCase() }}</span>
                          </td>
                          <td class="text-end pe-4">{{ s.count }}</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
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
import VueApexCharts from "vue3-apexcharts";

const apexchart = VueApexCharts;

const reportsData = ref({
  monthly_revenue: [],
  status_distribution: [],
  room_performance: [],
  weekly_trends: [],
  summary: {
    total_revenue: 0,
    total_bookings: 0,
    avg_booking_value: 0,
    cancellation_rate: 0
  }
});

const loading = ref(true);

const fetchReports = async () => {
  try {
    loading.value = true;
    const response = await axios.get('/api/admin/reports');
    if (response.data && typeof response.data === 'object' && response.data.summary) {
        reportsData.value = response.data;
    } else {
        console.error('Malformed reports data received:', response.data);
    }
  } catch (error) {
    console.error('Failed to fetch reports', error);
  } finally {
    loading.value = false;
  }
};

onMounted(fetchReports);

const summaryStats = computed(() => {
  const sum = reportsData.value?.summary || {};
  return [
    { label: 'Total Revenue', value: '₱' + Number(sum.total_revenue || 0).toLocaleString(), icon: 'bi-currency-dollar', bgClass: 'bg-primary-subtle text-primary' },
    { label: 'Total Bookings', value: Number(sum.total_bookings || 0), icon: 'bi-calendar-check', bgClass: 'bg-success-subtle text-success' },
    { label: 'Avg Value', value: '₱' + Math.round(Number(sum.avg_booking_value || 0)).toLocaleString(), icon: 'bi-graph-up', bgClass: 'bg-info-subtle text-info' },
    { label: 'Cancel Rate', value: Number(sum.cancellation_rate || 0).toFixed(1) + '%', icon: 'bi-x-circle', bgClass: 'bg-danger-subtle text-danger' }
  ];
});

// Revenue Chart
const revenueChartOptions = computed(() => ({
  chart: { toolbar: { show: false } },
  stroke: { curve: 'smooth', width: 3 },
  xaxis: { categories: (reportsData.value?.monthly_revenue || []).map(m => m.month) },
  colors: ['#D4AF37'],
  fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.45, opacityTo: 0.05, stops: [20, 100] } },
  dataLabels: { enabled: false }
}));

const revenueChartSeries = computed(() => [
  { name: 'Revenue', data: (reportsData.value?.monthly_revenue || []).map(m => m.revenue) }
]);

// Status Chart
const statusChartOptions = computed(() => ({
  labels: (reportsData.value?.status_distribution || []).map(s => s.status.toUpperCase()),
  chart: { toolbar: { show: false } },
  plotOptions: { pie: { donut: { size: '75%' } } },
  colors: ['#D4AF37', '#22C55E', '#EF4444', '#3B82F6'],
  legend: { position: 'bottom' }
}));

const statusChartSeries = computed(() => 
  (reportsData.value?.status_distribution || []).map(s => s.count)
);

// Weekly Trends Chart
const weeklyChartOptions = computed(() => ({
  chart: { toolbar: { show: false } },
  plotOptions: { bar: { borderRadius: 6, columnWidth: '45%' } },
  colors: ['#D4AF37'],
  xaxis: { categories: (reportsData.value?.weekly_trends || []).map(d => d.date) },
  dataLabels: { enabled: false }
}));

const weeklyChartSeries = computed(() => [
  { name: 'New Bookings', data: (reportsData.value?.weekly_trends || []).map(d => d.count) }
]);

// Room Revenue Chart (Pie)
const roomRevenueChartOptions = computed(() => ({
  labels: (reportsData.value?.room_performance || []).map(r => r.room_type),
  chart: { toolbar: { show: false } },
  colors: ['#D4AF37', '#1A2634', '#9A7640', '#718096'],
  legend: { position: 'bottom' }
}));

const roomRevenueChartSeries = computed(() => 
  (reportsData.value?.room_performance || []).map(r => parseFloat(r.total_revenue) || 0)
);

const getStatusBadgeClass = (status) => {
  const s = status.toLowerCase();
  if (s === 'confirmed' || s === 'completed') return 'bg-success-subtle text-success';
  if (s === 'cancelled') return 'bg-danger-subtle text-danger';
  if (s === 'pending') return 'bg-warning-subtle text-warning';
  return 'bg-secondary-subtle text-secondary';
};

const downloadCSV = () => {
    // Basic CSV Generator
    let csv = 'Report Category,Key,Value\n';
    
    // Summary
    csv += `Summary,Total Revenue,${reportsData.value.summary.total_revenue}\n`;
    csv += `Summary,Total Bookings,${reportsData.value.summary.total_bookings}\n`;
    csv += `Summary,Average Booking Value,${reportsData.value.summary.avg_booking_value}\n`;
    
    // Revenue
    reportsData.value.monthly_revenue.forEach(m => {
        csv += `Monthly Revenue,${m.month},${m.revenue}\n`;
    });
    
    // Room Performance
    reportsData.value.room_performance.forEach(r => {
        csv += `Room Performance,${r.room_type},${r.total_revenue}\n`;
    });

    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement("a");
    const url = URL.createObjectURL(blob);
    link.setAttribute("href", url);
    link.setAttribute("download", `eme_apartelle_report_${new Date().toISOString().split('T')[0]}.csv`);
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
};
</script>

<style scoped>
.icon-box {
  padding: 0.6rem;
  font-size: 1.25rem;
  line-height: 1;
}
.text-gold {
  color: #D4AF37;
}
.animate-fade-up {
  animation: fadeUp 0.5s ease forwards;
  opacity: 0;
  transform: translateY(20px);
}
@keyframes fadeUp {
  to { opacity: 1; transform: translateY(0); }
}
</style>
