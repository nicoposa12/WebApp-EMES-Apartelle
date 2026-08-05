<template>
  <div class="reports-view p-1">
    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border text-gold" role="status"></div>
      <p class="text-muted mt-2">Generating your analytical reports...</p>
    </div>
    
    <div v-else>
      <div class="row g-4 mb-4">
        <div v-for="(stat, index) in summaryStats" :key="index" class="col-md-3">
          <div class="stats-card-premium bg-white animate-fade-up" :style="{ animationDelay: (index * 0.1) + 's' }">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <span class="text-muted small fw-bold text-uppercase tracking-wider mb-2 d-block">{{ stat.label }}</span>
                <h3 class="mb-0 fw-bold text-secondary-dark serif-font">{{ stat.value }}</h3>
              </div>
              <div class="stats-icon-premium" :class="stat.bgClass">
                <i :class="['bi', stat.icon]"></i>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="row g-4">
        <!-- Monthly Revenue Chart -->
        <div class="col-lg-8">
          <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
            <div class="card-header bg-white py-4 px-4 border-0 d-flex justify-content-between align-items-center">
              <div>
                <h5 class="serif-font fw-bold mb-0 text-secondary-dark">Financial Performance</h5>
                <p class="text-muted small mb-0">Historical revenue growth since start of operations</p>
              </div>
              <select v-model="financialTimeframe" class="form-select form-select-sm border-light-subtle rounded-3 shadow-none fw-bold text-muted transition-all" style="width: auto; min-width: 110px; cursor: pointer; font-size: 0.8rem;">
                <option value="weekly">Weekly</option>
                <option value="monthly">Monthly</option>
                <option value="yearly">Yearly</option>
              </select>
            </div>
            <div class="card-body p-4 pt-0">
               <apexchart v-if="currentFinancialData.length > 0" type="area" height="350" :options="revenueChartOptions" :series="revenueChartSeries"></apexchart>
               <div v-else class="text-center py-5 text-muted small">No financial data recorded yet.</div>
            </div>
          </div>
        </div>

        <!-- Booking Distribution -->
        <div class="col-lg-4">
          <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
            <div class="card-header bg-white py-4 px-4 border-0 d-flex justify-content-between align-items-center">
              <div>
                <h5 class="serif-font fw-bold mb-0 text-secondary-dark">Booking Status</h5>
                <p class="text-muted small mb-0">Lifetime distribution of all reservations</p>
              </div>
              <select v-model="statusTimeframe" class="form-select form-select-sm border-light-subtle rounded-3 shadow-none fw-bold text-muted transition-all" style="width: auto; min-width: 110px; cursor: pointer; font-size: 0.8rem;">
                <option value="weekly">Weekly</option>
                <option value="monthly">Monthly</option>
                <option value="yearly">Yearly</option>
              </select>
            </div>
            <div class="card-body p-4 pt-0">
               <apexchart v-if="currentStatusData.length > 0" type="donut" height="350" :options="statusChartOptions" :series="statusChartSeries"></apexchart>
               <div v-else class="text-center py-5 text-muted small">No status data available.</div>
            </div>
          </div>
        </div>

        <!-- Weekly Trends -->
        <div class="col-lg-6">
          <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white py-4 px-4 border-0 d-flex justify-content-between align-items-center">
              <div>
                <h5 class="serif-font fw-bold mb-0 text-secondary-dark">Booking Trends</h5>
                <p class="text-muted small mb-0">Bookings created in the selected period</p>
              </div>
              <select v-model="trendsTimeframe" class="form-select form-select-sm border-light-subtle rounded-3 shadow-none fw-bold text-muted transition-all" style="width: auto; min-width: 110px; cursor: pointer; font-size: 0.8rem;">
                <option value="weekly">Weekly</option>
                <option value="monthly">Monthly</option>
                <option value="yearly">Yearly</option>
              </select>
            </div>
            <div class="card-body p-4 pt-0">
               <apexchart v-if="currentTrendsData.length > 0" type="bar" height="300" :options="weeklyChartOptions" :series="weeklyChartSeries"></apexchart>
               <div v-else class="text-center py-5 text-muted small">No trend data available.</div>
            </div>
          </div>
        </div>

        <!-- Room Revenue Distribution -->
        <div class="col-lg-6">
          <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white py-4 px-4 border-0 d-flex justify-content-between align-items-center">
              <div>
                <h5 class="serif-font fw-bold mb-0 text-secondary-dark">Revenue by Room Type</h5>
                <p class="text-muted small mb-0">Financial contribution per category</p>
              </div>
              <select v-model="roomTimeframe" class="form-select form-select-sm border-light-subtle rounded-3 shadow-none fw-bold text-muted transition-all" style="width: auto; min-width: 110px; cursor: pointer; font-size: 0.8rem;">
                <option value="weekly">Weekly</option>
                <option value="monthly">Monthly</option>
                <option value="yearly">Yearly</option>
              </select>
            </div>
            <div class="card-body p-4 pt-0">
               <apexchart v-if="currentRoomPerformance.length > 0" type="pie" height="300" :options="roomRevenueChartOptions" :series="roomRevenueChartSeries"></apexchart>
               <div v-else class="text-center py-5 text-muted small">No performance data available.</div>
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
                      <tr v-for="room in currentRoomPerformance" :key="room.room_type">
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
              <!-- Revenue Table -->
              <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
                  <div class="card-header bg-white py-3 px-4 border-0 border-bottom">
                    <h6 class="fw-bold mb-0">Revenue History ({{ financialTimeframe.toUpperCase() }})</h6>
                  </div>
                  <div class="card-body p-0" style="max-height: 300px; overflow-y: auto;">
                    <table class="table table-sm mb-0">
                      <thead class="table-light">
                        <tr class="small text-uppercase text-muted">
                           <th class="ps-4">Period</th>
                           <th class="text-end pe-4">Revenue</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="m in currentFinancialData" :key="m.month">
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
                    <h6 class="fw-bold mb-0">Status Summary ({{ statusTimeframe.toUpperCase() }})</h6>
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
                        <tr v-for="s in currentStatusData" :key="s.status">
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
  },
  timeframes: {
    financial_performance: { weekly: [], monthly: [], yearly: [] },
    booking_status: { weekly: [], monthly: [], yearly: [] },
    booking_trends: { weekly: [], monthly: [], yearly: [] },
    room_performance: { weekly: [], monthly: [], yearly: [] }
  }
});

const financialTimeframe = ref('monthly');
const statusTimeframe = ref('yearly');
const trendsTimeframe = ref('weekly');
const roomTimeframe = ref('yearly');

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

// FINANCIAL PERFORMANCE SELECTOR
const currentFinancialData = computed(() => {
  return reportsData.value?.timeframes?.financial_performance?.[financialTimeframe.value] || [];
});

// Revenue Chart
const revenueChartOptions = computed(() => ({
  chart: { toolbar: { show: false } },
  stroke: { curve: 'smooth', width: 3 },
  xaxis: { categories: currentFinancialData.value.map(m => m.month) },
  colors: ['#D4AF37'],
  fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.45, opacityTo: 0.05, stops: [20, 100] } },
  dataLabels: { enabled: false }
}));

const revenueChartSeries = computed(() => [
  { name: 'Revenue', data: currentFinancialData.value.map(m => m.revenue) }
]);

// BOOKING STATUS SELECTOR
const currentStatusData = computed(() => {
  return reportsData.value?.timeframes?.booking_status?.[statusTimeframe.value] || [];
});

// Status Chart
const statusChartOptions = computed(() => ({
  labels: currentStatusData.value.map(s => s.status.toUpperCase()),
  chart: { toolbar: { show: false } },
  plotOptions: { pie: { donut: { size: '75%' } } },
  colors: ['#D4AF37', '#22C55E', '#EF4444', '#3B82F6'],
  legend: { position: 'bottom' }
}));

const statusChartSeries = computed(() => 
  currentStatusData.value.map(s => s.count)
);

// BOOKING TRENDS SELECTOR
const currentTrendsData = computed(() => {
  return reportsData.value?.timeframes?.booking_trends?.[trendsTimeframe.value] || [];
});

// Weekly Trends Chart
const weeklyChartOptions = computed(() => ({
  chart: { toolbar: { show: false } },
  plotOptions: { bar: { borderRadius: 6, columnWidth: '45%' } },
  colors: ['#D4AF37'],
  xaxis: { categories: currentTrendsData.value.map(d => d.date) },
  dataLabels: { enabled: false }
}));

const weeklyChartSeries = computed(() => [
  { name: 'New Bookings', data: currentTrendsData.value.map(d => d.count) }
]);

// ROOM PERFORMANCE SELECTOR
const currentRoomPerformance = computed(() => {
  return reportsData.value?.timeframes?.room_performance?.[roomTimeframe.value] || [];
});

// Room Revenue Chart (Pie)
const roomRevenueChartOptions = computed(() => ({
  labels: currentRoomPerformance.value.map(r => r.room_type),
  chart: { toolbar: { show: false } },
  colors: ['#D4AF37', '#1A2634', '#9A7640', '#718096'],
  legend: { position: 'bottom' }
}));

const roomRevenueChartSeries = computed(() => 
  currentRoomPerformance.value.map(r => parseFloat(r.total_revenue) || 0)
);

const getStatusBadgeClass = (status) => {
  const s = status.toLowerCase();
  if (s === 'confirmed' || s === 'completed') return 'bg-success-subtle text-success';
  if (s === 'cancelled') return 'bg-danger-subtle text-danger';
  if (s === 'pending') return 'bg-warning-subtle text-warning';
  return 'bg-secondary-subtle text-secondary';
};

const downloadCSV = () => {
    // Basic CSV Generator exporting currently selected data views
    let csv = 'Report Category,Key,Value\n';
    
    // Summary
    csv += `Summary,Total Revenue,${reportsData.value.summary.total_revenue}\n`;
    csv += `Summary,Total Bookings,${reportsData.value.summary.total_bookings}\n`;
    csv += `Summary,Average Booking Value,${reportsData.value.summary.avg_booking_value}\n`;
    
    // Revenue
    currentFinancialData.value.forEach(m => {
        csv += `Financial Performance (${financialTimeframe.value}),${m.month},${m.revenue}\n`;
    });
    
    // Status
    currentStatusData.value.forEach(s => {
        csv += `Booking Status (${statusTimeframe.value}),${s.status},${s.count}\n`;
    });

    // Trends
    currentTrendsData.value.forEach(t => {
        csv += `Booking Trends (${trendsTimeframe.value}),${t.date},${t.count}\n`;
    });
    
    // Room Performance
    currentRoomPerformance.value.forEach(r => {
        csv += `Room Performance (${roomTimeframe.value}),${r.room_type},${r.total_revenue}\n`;
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
.stats-card-premium {
  background: white;
  padding: 1.75rem;
  border-radius: 1.5rem;
  border: 1px solid rgba(226, 232, 240, 0.8);
  box-shadow: 0 10px 30px -5px rgba(0,0,0,0.02), 0 5px 15px -3px rgba(0,0,0,0.01);
  transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
  position: relative;
  overflow: hidden;
}

.stats-card-premium:hover {
  transform: translateY(-5px);
  box-shadow: 0 20px 40px -5px rgba(188, 145, 81, 0.1), 0 10px 20px -3px rgba(188, 145, 81, 0.05);
  border-color: rgba(188, 145, 81, 0.3);
}

.stats-card-premium::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 4px;
  height: 100%;
  background: #BC9151;
  opacity: 0;
  transition: opacity 0.3s ease;
}

.stats-card-premium:hover::before {
  opacity: 1;
}

.stats-icon-premium {
  width: 54px;
  height: 54px;
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.6rem;
  transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}

.stats-card-premium:hover .stats-icon-premium {
  transform: scale(1.1) rotate(5deg);
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
